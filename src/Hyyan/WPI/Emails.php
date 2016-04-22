<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings,
    Hyyan\WPI\Admin\Features;

/**
 * Emails
 *
 * Handle woocommerce emails
 *
 * @author Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 */
class Emails
{

    /**
     * Construct object
     */
    public function __construct() {
        if ('on' === Settings::getOption('emails', Features::getID(), 'on')) {
            add_filter('plugin_locale', array($this, 'correctLocal'), 100);

            // Translate Woocommerce email subjects and headings to the order language
            // new order
            add_filter('woocommerce_email_subject_new_order', array( $this, 'translate_woocommerce_email_subject_new_order' ), 10, 2);
            add_filter('woocommerce_email_heading_new_order', array( $this, 'translate_woocommerce_email_heading_new_order' ), 10, 2);
            // processing order
            add_filter('woocommerce_email_subject_customer_processing_order', array( $this, 'translate_woocommerce_email_subject_customer_processing_order' ), 10, 2);
            add_filter('woocommerce_email_heading_customer_processing_order', array( $this, 'translate_woocommerce_email_heading_customer_processing_order' ), 10, 2);
            // refunded order
            add_filter('woocommerce_email_subject_customer_refunded_order', array( $this, 'translate_woocommerce_email_subject_customer_refunded_order'), 10, 2);
            add_filter('woocommerce_email_heading_customer_refunded_order', array( $this, 'translate_woocommerce_email_heading_customer_refunded_order'), 10, 2);
            // customer note
            add_filter('woocommerce_email_subject_customer_note', array( $this, 'translate_woocommerce_email_subject_customer_note' ), 10, 2);
            add_filter('woocommerce_email_heading_customer_note', array( $this, 'translate_woocommerce_email_heading_customer_note' ), 10, 2);
            // customer invoice
            add_filter( 'woocommerce_email_subject_customer_invoice', array( $this, 'translate_woocommerce_email_subject_customer_invoice' ), 10, 2);
            add_filter( 'woocommerce_email_heading_customer_invoice', array( $this, 'translate_woocommerce_email_heading_customer_invoice' ), 10, 2);
            // customer invoice paid
            add_filter('woocommerce_email_subject_customer_invoice_paid', array( $this, 'translate_woocommerce_email_subject_customer_invoice_paid' ), 10, 2);
            add_filter('woocommerce_email_heading_customer_invoice_paid', array( $this, 'translate_woocommerce_email_heading_customer_invoice_paid' ), 10, 2);
            // completed order
            add_filter('woocommerce_email_subject_customer_completed_order', array( $this, 'translate_woocommerce_email_subject_customer_completed_order' ), 10, 2);
            add_filter('woocommerce_email_heading_customer_completed_order', array( $this, 'translate_woocommerce_email_heading_customer_completed_order' ), 10, 2);
            // new account
            add_filter('woocommerce_email_subject_customer_new_account', array( $this, 'translate_woocommerce_email_subject_customer_new_account' ), 10, 2);
            add_filter('woocommerce_email_heading_customer_new_account', array( $this, 'translate_woocommerce_email_heading_customer_new_account' ), 10, 2);
            // reset password
            add_filter('woocommerce_email_subject_customer_reset_password', array( $this, 'translate_woocommerce_email_subject_customer_reset_password' ), 10, 2);
            add_filter('woocommerce_email_heading_customer_reset_password', array( $this, 'translate_woocommerce_email_heading_customer_reset_password' ), 10, 2);


            //remove_action( 'woocommerce_email_before_order_table', 'email_instructions', 10);
            //add_action( 'woocommerce_email_before_order_table', array( $this, 'translated_email_instructions' ), 10, 3 );
        }
    }


    /**
     * Translate to the order language, the email subject of new order email notifications to the admin
     *
     * @param string    Email subject in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated subject
     */
    function translate_woocommerce_email_subject_new_order( $subject, $order ) {
        return $this->translate_woocommerce_email( $subject, $order ,'subject' ,'new_order' );
    }

    /**
     * Translate to the order language, the email heading of new order email notifications to the admin
     *
     * @param string    Email heading in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated heading
     */
    function translate_woocommerce_email_heading_new_order( $heading, $order ) {
        return $this->translate_woocommerce_email( $heading, $order ,'heading' ,'new_order' );
    }

    /**
     * Translate to the order language, the email subject of processing order email notifications to the customer
     *
     * @param string    Email subject in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated subject
     */
    function translate_woocommerce_email_subject_customer_processing_order( $subject, $order ) {
        return $this->translate_woocommerce_email( $subject, $order ,'subject' ,'customer_processing_order' );
    }

    /**
     * Translate to the order language, the email heading of processing order email notifications to the customer
     *
     * @param string    Email heading in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated heading
     */
    function translate_woocommerce_email_heading_customer_processing_order( $heading, $order ) {
        return $this->translate_woocommerce_email( $heading, $order ,'heading' ,'customer_processing_order' );
    }

    /**
     * Translate to the order language, the email subject of refunded order email notifications to the customer
     *
     * @param string    Email subject in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated subject
     */
    function translate_woocommerce_email_subject_customer_refunded_order( $subject, $order ) {
        if ( $this->is_fully_refunded( $order ) ) {
            return $this->translate_woocommerce_email( $subject, $order ,'subject_full' , 'customer_refunded_order' );
        } else {
            return $this->translate_woocommerce_email( $subject, $order ,'subject_partial' , 'customer_refunded_order' );
        }
    }

    /**
     * Translate to the order language, the email heading of refunded order email notifications to the customer
     *
     * @param string    Email heading in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated heading
     */
    function translate_woocommerce_email_heading_customer_refunded_order( $subject, $order ) {
        if ( $this->is_fully_refunded( $order ) ) {
            return $this->translate_woocommerce_email( $subject, $order ,'heading_full' , 'customer_refunded_order' );
        } else {
            return $this->translate_woocommerce_email( $subject, $order ,'heading_partial' , 'customer_refunded_order' );
        }
    }

    /**
     * Translate to the order language, the email subject of customer note emails
     *
     * @param string    Email subject in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated subject
     */
    function translate_woocommerce_email_subject_customer_note( $subject, $order ) {
        return $this->translate_woocommerce_email( $subject, $order, 'subject', 'note' );
    }

    /**
     * Translate to the order language, the email heading of customer note emails
     *
     * @param string    Email heading in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated heading
     */
    function translate_woocommerce_email_heading_customer_note( $heading, $order ) {
        return $this->translate_woocommerce_email( $heading, $order, 'heading', 'note' );
    }

    /**
     * Translate to the order language, the email subject of order invoice email notifications to the customer
     *
     * @param string    Email subject in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated subject
     */
    function translate_woocommerce_email_subject_customer_invoice( $subject, $order ) {
        return $this->translate_woocommerce_email( $subject, $order ,'subject' ,'invoice' );
    }

    /**
     * Translate to the order language, the email heading of of order invoice email notifications to the customer
     *
     * @param string    Email heading in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated heading
     */
    function translate_woocommerce_email_heading_customer_invoice( $heading, $order ) {
        return $this->translate_woocommerce_email( $heading, $order ,'heading' ,'invoice' );
    }

    /**
     * Translate to the order language, the email subject of order invoice paid email notifications to the customer
     *
     * @param string    Email subject in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated subject
     */
    function translate_woocommerce_email_subject_customer_invoice_paid( $subject, $order ) {
      return $this->translate_woocommerce_email( $subject, $order ,'subject_paid' ,'invoice' );
    }

    /**
     * Translate to the order language, the email heading of of order invoice paid email notifications to the customer
     *
     * @param string    Email heading in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated heading
     */
    function translate_woocommerce_email_heading_customer_invoice_paid( $heading, $order ) {
        return $this->translate_woocommerce_email( $heading, $order ,'heading_paid' ,'invoice' );
    }

    /**
     * Translate to the order language, the email subject of completed order email notifications to the customer
     *
     * @param string    Email subject in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated subject
     */
    function translate_woocommerce_email_subject_customer_completed_order( $subject, $order ) {
        return $this->translate_woocommerce_email( $subject, $order ,'subject' ,'completed_order' );
    }

    /**
     * Translate to the order language, the email heading of completed order email notifications to the customer
     *
     * @param string    Email heading in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated heading
     */
    function translate_woocommerce_email_heading_customer_completed_order( $heading, $order ) {
        return $this->translate_woocommerce_email( $heading, $order ,'heading' ,'completed_order' );
    }

    /**
     * Translate to the order language, the email subject of new account email notifications to the customer
     *
     * @param string    Email subject in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated subject
     */
    function translate_woocommerce_email_subject_customer_new_account( $subject, $order ) {
        return $this->translate_woocommerce_email( $subject, $order ,'subject' ,'new_account' );
    }

    /**
     * Translate to the order language, the email heading of new account email notifications to the customer
     *
     * @param string    Email heading in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated heading
     */
    function translate_woocommerce_email_heading_customer_new_account( $heading, $order ) {
        return $this->translate_woocommerce_email( $heading, $order ,'heading' ,'new_account' );
    }

    /**
     * Translate to the order language, the email subject of password reset email notifications to the customer
     *
     * @param string    Email subject in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated subject
     */
    function translate_woocommerce_email_subject_customer_reset_password( $subject, $order ) {
        return $this->translate_woocommerce_email( $subject, $order ,'subject' ,'reset_password' );
    }

    /**
     * Translate to the order language, the email heading of password reset email notifications to the customer
     *
     * @param string    Email heading in default language
     * @param WC_Order  Order object
     *
     * @return string   Translated heading
     */
    function translate_woocommerce_email_heading_customer_reset_password( $heading, $order ) {
        return $this->translate_woocommerce_email( $heading, $order ,'heading' ,'reset_password' );
    }

    /**
     * Translates Woocommerce email subjects and headings content
     *
     * @param string    Subject or heading to translate
     * @param WC_Order  Order object
     * @param string    Type of string to translate <subject | heading>
     * @param string    Email template
     *
     * @return string   Translated string
     */
    function translate_woocommerce_email( $string, $order, $string_type, $mail_template) {

        $settings = get_option('woocommerce_' .$mail_template . '_settings');
        if ( empty( $settings ) || ! isset( $settings[$string_type] ) || $settings[$string_type] === '' ) {
            return $string;
        }

        $default_string = $settings[$string_type];

        $order_language = pll_get_post_language ( $order->id );
        if ( ! $order_language ) {
            $order_language = pll_current_language();
        }

        $this->switch_woocommerce_emails_language( $order->id );

        $string = __( $default_string, 'woocommerce' );

        $find                     = array();
        $replace                  = array();

        $find['order-date']       = '{order_date}';
        $find['order-number']     = '{order_number}';
        $find['site_title']       = '{site_title}';

        $replace['order-date']    = date_i18n( wc_date_format(), strtotime( $order->order_date ) );
        $replace['order-number']  = $order->get_order_number();
        $replace['site_title']    = get_bloginfo( 'name' );

        $string = str_replace( $find, $replace, $string );

        return $string;
    }

    /**
     * Check whether a refund is made in full
     */
    function is_fully_refunded( $order ) {
        if ( $order->get_remaining_refund_amount() > 0 || ( $order->has_free_item() && $order->get_remaining_refund_items() > 0 ) ) {
                // Order partially refunded
                return false;
        } else {
                // Order fully refunded
                return true;
        }
    }

    /**
     * Reload text domains with order locale
     *
     * @param int   Order ID
     */
    function switch_woocommerce_emails_language( $order_id ) {
        if ( class_exists( 'Polylang' ) ) {
            global $locale, $polylang, $woocommerce;

            $order_language = pll_get_post_language( $order_id, 'locale' );
            if ( $order_language == '' ) {
                    $order_language = pll_default_language( 'locale' );
            }
            $current_language = pll_current_language( 'locale' );

            // unload plugin's textdomains
            unload_textdomain( 'default' );
            unload_textdomain( 'woocommerce' );

            // set locale to order locale
            $locale = apply_filters( 'locale', $order_language );
            $polylang->curlang->locale = $order_language;

            // (re-)load plugin's textdomain with order locale
            load_default_textdomain( $order_language );
            $woocommerce->load_plugin_textdomain();
        }
    }

    /**
     * Correct the locale for orders emails , Othe emails must be handled
     * correctly out of the box
     *
     * @global \Polylang $polylang
     * @global \WooCommerce $woocommerce
     *
     * @param string $locale current locale
     *
     * @return string locale
     */
    public function correctLocal($locale)
    {

        global $polylang, $woocommerce;
        if (!$polylang || !$woocommerce) {
            return $locale;
        }

        $refer = isset($_GET['action']) &&
                esc_attr($_GET['action'] === 'woocommerce_mark_order_status');

/* ******add-on to have multilanguage on note and refund mails ********* */
        if (isset($_POST['note_type']) && $_POST['note_type'] == 'customer') {$refer = true ;}
        if (isset($_POST['refund_amount']) && ($_POST['refund_amount'] > 0)) {$refer = true ;}
/* ******add-on to have multilanguage on note and refund mails ********* */

        if ((!is_admin() && !isset($_REQUEST['ipn_track_id'])) || (defined('DOING_AJAX') && !$refer)) {
            return $locale;
        }

        if ('GET' === filter_input(INPUT_SERVER, 'REQUEST_METHOD') && !$refer) {
            return $locale;
        }

        $ID = false;

        if (!isset($_REQUEST['ipn_track_id'])) {
            $search = array('post', 'post_ID', 'pll_post_id', 'order_id');

            foreach ($search as $value) {
                if (isset($_REQUEST[$value])) {
                    $ID = esc_attr($_REQUEST[$value]);
                    break;
                }
            }
        } else {
            $ID = $this->getOrderIDFromIPNRequest();
        }

        if ((get_post_type($ID) !== 'shop_order') && !$refer) {
            return $locale;
        }

        $orderLanguage = Order::getOrderLangauge($ID);

        if ($orderLanguage) {

            $entity = Utilities::getLanguageEntity($orderLanguage);

            if ($entity) {
                $polylang->curlang = PLL()->model->get_language( // $polylang->model-> deprecated
                        $entity->locale
                );
                $GLOBALS['text_direction'] = $entity->is_rtl ? 'rtl' : 'ltr';
                if (class_exists('WP_Locale')) {
                    $GLOBALS['wp_locale'] = new \WP_Locale();
                }

                return $entity->locale;
            }
        }

        return $locale;
    }

    /**
     * Return the order id associated with the current IPN request
     *
     * @return int the order id if one was found or false
     */
     public function getOrderIDFromIPNRequest()
     {
         if (!empty($_REQUEST)) {

             $posted = wp_unslash($_REQUEST);

             if (empty($posted['custom'])) {
                return false;
            }

             $custom = maybe_unserialize($posted['custom']);

             if (!is_array($custom)) {
                return false;
             }

            list($order_id, $order_key) = $custom;

            return $order_id;
        }

        return false;
    }
}
