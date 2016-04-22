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

        // Handle BACS email intructions translation in processing order emails
        // for orders with 'on-hold' status
        new Gateways\GatewayBACS();

        add_action( 'init', array( $this, 'remove_action_email_instructions_bacs_gateway' ), 100 );

        // Payment gateway title and respective description
        add_filter( 'woocommerce_gateway_title', array( $this, 'translate_payment_gateway_title' ), 10, 2 );
        add_filter( 'woocommerce_gateway_description', array( $this, 'translate_payment_gateway_description' ), 10, 2 );
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
     * Remove the add BACS email intructions content action that is replaced by
     * a multi-language compatible function in the an Gateways\GatewayBACS class
     */
    public function remove_action_email_instructions_bacs_gateway() {
        $gateways = \WC_Payment_Gateways::instance();

        $available_gateways = $gateways->get_available_payment_gateways();

        if ( isset( $available_gateways['bacs'] ) ) {
            remove_action( 'woocommerce_email_before_order_table', array( $available_gateways['bacs'], 'email_instructions' ), 10, 3 );
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
        return __( $title , 'woocommerce' );
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
        return __( $description , 'woocommerce' );
    }

}
