<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Taxonomies;

/**
 * ShippingCalss
 *
 * @author Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 */
class ShippingClass implements TaxonomiesInterface
{
    /**
     * Construct object
     */
    public function __construct() {
        // Shipping method in the Cart and Checkout pages
        add_filter( 'woocommerce_shipping_rate_label', array( $this, 'translate_shipping_label' ), 10, 1 );

        // Shipping method in My Accoutn page, Order Emails and Paypal requests
        add_filter( 'woocommerce_order_shipping_method', array( $this, 'translate_order_shipping_method' ), 10, 2 );
    }

    public function translate_shipping_label( $label ) {
        return __( $label , 'woocommerce' );
    }

    public function translate_order_shipping_method( $implode, $instance ) {

        // Convert the imploded array again to an array that is easy to manipulate
        $shipping_methods = explode( ', ', $implode );

        // Array with translated shipping methods
        $translated = array();

        foreach ( $shipping_methods as $shipping ) {
            $translated[] = __( $shipping, 'woocommerce' );
        }

        // Implode array to string again
        $translated_implode = implode( ', ', $translated );

        return $translated_implode;
    }

    /**
     * @{inheritdoc}
     */
    public static function getNames()
    {
        return array('product_shipping_class');
    }

}
