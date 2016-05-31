<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Gateways
 *
 * Handle Payment Gateways
 *
 * @author Nicolas Joannès <nic@cobea.be>
 */
class Gateways
{
    /** @var array Array of enabled gateways */
    public $enabled_gateways;

    /**
     * Construct object
     */
    public function __construct() {
        // Set the PayPal checkout locale code
        add_filter('woocommerce_paypal_args', array($this, 'setPaypalLocaleCode'));

        // Set enabled payment gateways
        $this->enabled_gateways = $this->get_enabled_payment_gateways();

        // Register Woocommerce Payment Gateway custom  titles and descriptions in Polylang's Strings translations table
        add_action( 'wp_loaded', array( $this, 'register_gateway_strings_for_translation' ) ); // called only after Wordpress is loaded

        // Load payment gateways extensions (gateway intructions translation)
        $this->load_payment_gateways_extentions();

        // Payment gateway title and respective description
        add_filter( 'woocommerce_gateway_title', array( $this, 'translate_payment_gateway_title' ), 10, 2 );
        add_filter( 'woocommerce_gateway_description', array( $this, 'translate_payment_gateway_description' ), 10, 2 );

        // Payment method in Thank You and Order View pages
        //add_filter( 'woocommerce_get_order_item_totals', array( $this, 'translate_woocommerce_order_payment_method' ), 10, 2 );
    }

    /**
     * Translate the payment method in Thank You and Order View pages
     *
     * @param array $total_rows Array of the order item totals
     * @param WC_Order $order Order object
     *
     * @return array Order item totals with translated payment method
     */
    public function translate_woocommerce_order_payment_method( $total_rows, $order ) {
        if ( isset( $total_rows['payment_method']['value'] ) ) {
            $total_rows['payment_method']['value'] = function_exists( 'pll__' ) ? pll__( $total_rows['payment_method']['value'] ) : __( $total_rows['payment_method']['value'], 'woocommerce' );
        }

        return $total_rows;
    }

    /**
     * Set the PayPal checkout locale code
     *
     * @param array $args the current paypal request args array
     *
     * @return void
     */
    public function setPaypalLocaleCode($args)
    {
        $lang = pll_current_language('locale');
        $args['locale.x'] = $lang;

        return $args;

    }

    /**
     * Get enabled payment gateways
     *
     * @return array Array of enabled gateways
     */
    public function get_enabled_payment_gateways() {

        $_enabled_gateways = array();

        $gateways = \WC_Payment_Gateways::instance();

        if ( sizeof( $gateways->payment_gateways ) > 0 ) {
            foreach ( $gateways->payment_gateways() as $gateway ) {
                if ( $this->is_enabled( $gateway ) ) {
                    $_enabled_gateways[ $gateway->id ] = $gateway;
                }
            }
        }

        return $_enabled_gateways;
    }

    /**
     * Is payment gateway enabled?
     *
     * @param WC_Payment_Gateway $gateway
     *
     * @return boolean True if gateway enabled, false otherwise
     */
    public function is_enabled( $gateway ) {
        return ( 'yes' === $gateway->enabled );
    }

    /**
     * Load payment gateways extentions
     *
     * Manage the gateways intructions translation in the Thank You page and
     * Order emails. This is required because the strings are defined in the Construct
     * object and no filters are available.
     */
    public function load_payment_gateways_extentions() {

        foreach ( $this->enabled_gateways as $gateway ) {
            switch ( $gateway->id ) {
                case 'bacs':
                    new Gateways\GatewayBACS();
                    break;
                case 'cheque':
                    new Gateways\GatewayCheque();
                    break;
                case 'cod':
                    new Gateways\GatewayCOD();
                    break;
                default:
                    break;
            }

            // Remove the gateway construct actions to avoid duplications
            add_action( 'init', array( $this, 'remove_gateway_actions' ), 100 );

            // Allows other plugins to load payment gateways class extentions or change the gateway object
            do_action( HooksInterface::GATEWAY_LOAD_EXTENTION . $gateway->id, $gateway, $this->enabled_gateways );
        }
    }

    /**
     * Remove the gateway construct actions to avoid duplications when we instanciate
     * the class extentions to add polylang support that doesn't have a __construct
     * function and will use the parent's function and set all these actions again.
     */
    public function remove_gateway_actions() {
        foreach ( $this->enabled_gateways as $gateway ) {
            remove_action( 'woocommerce_email_before_order_table', array( $gateway, 'email_instructions' ) );
            remove_action( 'woocommerce_thankyou_' . $gateway->id, array( $gateway, 'thankyou_page' ) );
            remove_action( 'woocommerce_update_options_payment_gateways_' . $gateway->id, array( $gateway, 'process_admin_options' ) );

            if ( 'bacs' == $gateway->id ) {
                remove_action( 'woocommerce_update_options_payment_gateways_' . $gateway->id, array( $gateway, 'save_account_details' ) );
            }
        }
    }

    /**
     * Register Woocommerce Payment Gateway custom titles, descriptions and
     * instructions in Polylang's Strings translations table.
     */
    public function register_gateway_strings_for_translation() {

        if ( function_exists( 'pll_register_string' ) && ! empty( $this->enabled_gateways ) ) {

            foreach ( $this->enabled_gateways as $gateway ) {
                $settings = get_option( $gateway->plugin_id . $gateway->id . '_settings' );

                if ( ! empty( $settings ) ) {

                    if( isset( $settings['title'] ) ) {
                        pll_register_string( $gateway->plugin_id . $gateway->id . '_gateway_title', $settings['title'], __( 'Woocommerce Payment Gateways', 'woo-poly-integration') );
                    }
                    if( isset( $settings['description'] ) ) {
                        pll_register_string( $gateway->plugin_id . $gateway->id . '_gateway_description', $settings['description'], __( 'Woocommerce Payment Gateways', 'woo-poly-integration') );
                    }
                    if( isset( $settings['instructions'] ) ) {
                        pll_register_string( $gateway->plugin_id . $gateway->id . '_gateway_instructions', $settings['instructions'], __( 'Woocommerce Payment Gateways', 'woo-poly-integration') );
                    }
                }
            }

        }
    }

    /**
     * Translate Payment gateway title
     *
     * @param string     Gateway title
     * @param int        Gateway id
     *
     * @return string   Translated title
     */
    public function translate_payment_gateway_title( $title, $id ) {
        return function_exists( 'pll__' ) ? pll__( $title ) : __( $title, 'woocommerce' );
    }

    /**
     * Translate Payment gateway description
     *
     * @param string     Gateway description
     * @param int        Gateway id
     *
     * @return string   Translated description
     */
    public function translate_payment_gateway_description( $description, $id ) {
        return function_exists( 'pll__' ) ? pll__( $description ) : __( $description, 'woocommerce' );
    }

}
