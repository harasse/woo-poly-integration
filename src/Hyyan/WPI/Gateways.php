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
    /**
     * Construct object
     */
    public function __construct() {
        // Set the PayPal checkout locale code
        add_filter('woocommerce_paypal_args', array($this, 'setPaypalLocaleCode'));

        // Handle BACS intructions translation in the Thank You page and emails
        new Gateways\GatewayBACS();

        add_action( 'init', array( $this, 'remove_actions_instructions_bacs_gateway' ), 100 );

        // Payment gateway title and respective description
        add_filter( 'woocommerce_gateway_title', array( $this, 'translate_payment_gateway_title' ), 10, 2 );
        add_filter( 'woocommerce_gateway_description', array( $this, 'translate_payment_gateway_description' ), 10, 2 );

        // Payment method in Thank You and Order View pages
        add_filter( 'woocommerce_get_order_item_totals', array( $this, 'translate_woocommerce_order_payment_method' ), 10, 2 );
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
            $total_rows['payment_method']['value'] = __( $total_rows['payment_method']['value'], 'woocommerce' );
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
     * Remove the add BACS gateway actions to avoid duplication when we instanciate
     * the multi-language class Gateways\GatewayBACS class that doesn't have a
     * __construct function and will use the parent function and set all these
     * actions again.
     */
    public function remove_actions_instructions_bacs_gateway() {

        $gateways = \WC_Payment_Gateways::instance();

        $available_gateways = $gateways->get_available_payment_gateways();

        if ( isset( $available_gateways['bacs'] ) ) {
            remove_action( 'woocommerce_email_before_order_table', array( $available_gateways['bacs'], 'email_instructions' ) );
            remove_action( 'woocommerce_thankyou_bacs', array( $available_gateways['bacs'], 'thankyou_page' ) );
            remove_action( 'woocommerce_update_options_payment_gateways_' . $available_gateways['bacs']->id, array( $available_gateways['bacs'], 'process_admin_options' ) );
            remove_action( 'woocommerce_update_options_payment_gateways_' . $available_gateways['bacs']->id, array( $available_gateways['bacs'], 'save_account_details' ) );
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
        return __( $title, 'woocommerce' );
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
        return __( $description, 'woocommerce' );
    }

}
