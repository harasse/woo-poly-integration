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
    /** @var array Array of available gateways */
    public $available_gateways;

    /**
     * Construct object
     */
    public function __construct() {
        // Set the PayPal checkout locale code
        add_filter('woocommerce_paypal_args', array($this, 'setPaypalLocaleCode'));

        // Set available payment gateways
        $this->available_gateways = $this->get_available_payment_gateways();

        // Register Woocommerce Payment Gateway custom  titles and descriptions in Polylang's Strings translations table
        add_action( 'wp_loaded', array( $this, 'register_gateway_strings_for_translation' ) ); // called only after Wordpress is loaded

        // Handle BACS intructions translation in the Thank You page and emails
        new Gateways\GatewayBACS();
        add_action( 'init', array( $this, 'remove_actions_instructions_bacs_gateway' ), 100 );
        // TO-DO: do similar approach for Cheque and COD.

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
     * Get available payment gateways
     *
     * @return array Array of available gateways
     */
    public function get_available_payment_gateways() {

        $gateways = \WC_Payment_Gateways::instance();

        return $gateways->get_available_payment_gateways();
    }

    /**
     * Remove the add BACS gateway actions to avoid duplication when we instanciate
     * the multi-language class Gateways\GatewayBACS class that doesn't have a
     * __construct function and will use the parent function and set all these
     * actions again.
     */
    public function remove_actions_instructions_bacs_gateway() {

        $available_gateways = $this->available_gateways;

        if ( ! empty( $available_gateways ) && isset( $available_gateways['bacs'] ) ) {
            remove_action( 'woocommerce_email_before_order_table', array( $available_gateways['bacs'], 'email_instructions' ) );
            remove_action( 'woocommerce_thankyou_bacs', array( $available_gateways['bacs'], 'thankyou_page' ) );
            remove_action( 'woocommerce_update_options_payment_gateways_' . $available_gateways['bacs']->id, array( $available_gateways['bacs'], 'process_admin_options' ) );
            remove_action( 'woocommerce_update_options_payment_gateways_' . $available_gateways['bacs']->id, array( $available_gateways['bacs'], 'save_account_details' ) );
        }
    }

    /**
     * Register Woocommerce Payment Gateway custom titles, descriptions and
     * instructions in Polylang's Strings translations table.
     */
    public function register_gateway_strings_for_translation() {

        if ( function_exists( 'pll_register_string' ) && ! empty( $this->available_gateways ) ) {

            foreach ( $this->available_gateways as $gateway ) {
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
