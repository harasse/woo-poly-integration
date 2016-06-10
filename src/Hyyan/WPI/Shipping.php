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
 * Shipping
 *
 * Handle Shipping Methods
 *
 * @author Antonio de Carvalho <decarvalhoaa@gmail.com>
 */
class Shipping
{

    /**
     * Construct object
     */
    public function __construct() {
        // Register woocommerce shipping method custom names in polylang strings translations table
        add_action( 'wp_loaded', array( $this, 'register_shipping_strings_for_translation' ) ); // called only after Wordpress is loaded

        // Shipping method in the Cart and Checkout pages
        add_filter( 'woocommerce_shipping_rate_label', array( $this, 'translate_shipping_label' ), 10, 1 );

        // Shipping method in My Account page, Order Emails and Paypal requests
        add_filter( 'woocommerce_order_shipping_method', array( $this, 'translate_order_shipping_method' ), 10, 2 );

    }

    /**
    * Helper function - Gets the shipping methods enabled in the shop
    *
    * @return array $active_methods The id and respective plugin id of all active methods
    */
    private function get_active_shipping_methods() {

        $shipping_methods = WC()->shipping->load_shipping_methods();
        $active_methods = array();

        foreach ( $shipping_methods as $id => $shipping_method ) {
            if ( isset( $shipping_method->enabled ) && 'yes' === $shipping_method->enabled ) {
                $active_methods[$id] = $shipping_method->plugin_id;
            }
        }

        return $active_methods;
    }

    /**
     * Register shipping method custom titles in Polylang's Strings translations table
     */
    public function register_shipping_strings_for_translation() {

        if ( function_exists( 'pll_register_string' ) ) {

            $shipping_methods = $this->get_active_shipping_methods();

            foreach ( $shipping_methods as $method_id => $plugin_id ) {
                $setting = get_option( $plugin_id . $method_id . '_settings' );

                if ( $setting && isset( $setting['title'] ) ) {
                    pll_register_string( $plugin_id . $method_id . '_shipping_method', $setting['title'], __( 'Woocommerce Shipping Methods', 'woo-poly-integration') );
                }
            }

        }
    }

    /**
     * Translate shipping label in the Cart and Checkout pages
     *
     * @param string $label Shipping method label
     *
     * @return string Translated label
     */
    public function translate_shipping_label( $label ) {
        return function_exists( 'pll__' ) ? pll__( $label ) : __( $label , 'woocommerce' );
    }

    /**
     * Translate shipping method title in My Account page, Order Emails and Paypal requests
     *
     * @param string $implode Comma separated string of shipping methods used in order
     * @param WC_Order $instance Order instance
     *
     * @return string Comma separated string of translated shipping methods' titles
     */
    public function translate_order_shipping_method( $implode, $instance ) {

        // Convert the imploded array again to an array that is easy to manipulate
        $shipping_methods = explode( ', ', $implode );

        // Array with translated shipping methods
        $translated = array();

        foreach ( $shipping_methods as $shipping ) {
            if ( function_exists( 'pll__' ) ) {
                $translated[] = pll__( $shipping );
            } else {
                $translated[] = __( $shipping, 'woocommerce' );
            }
        }

        // Implode array to string again
        $translated_implode = implode( ', ', $translated );

        return $translated_implode;
    }
}
