<?php

/*
 * Plugin Name: Hyyan WooCommerce Polylang Integration
 * Plugin URI: https://github.com/hyyan/woo-poly-integration/
 * Description: Integrates Woocommerce with Polylang
 * Author: Hyyan Abo Fakher
 * Author URI: https://github.com/hyyan
 * Text Domain: woo-poly-integration
 * Domain Path: /languages
 * GitHub Plugin URI: hyyan/woo-poly-integration
 * License: MIT License
 * Version: 0.26 (Not Released)
 */

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!defined('ABSPATH')) {
    exit('restricted access');
}

define('Hyyan_WPI_DIR', __FILE__);
define('Hyyan_WPI_URL', plugin_dir_url(__FILE__));

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once __DIR__ . '/vendor/class.settings-api.php';
require_once __DIR__ . '/src/Hyyan/WPI/Autoloader.php';

/* register the autoloader */
new Hyyan\WPI\Autoloader(__DIR__ . '/src/');

/*****************************************************************************************************************/

// Example with "fr" as default language and "en" as second language. 
// The polylang string translation may contain in "Woocommerce Mails" group :
//
// woocommerce_customer_processing_order_subject : Reçu de votre commande du {order_date} sur {site_title}
//                                                 Your {site_title} order receipt from {order_date}
// woocommerce_customer_processing_order_heading : Merci pour votre commande
//                                                 Thank you for your order
//
// woocommerce_customer_completed_order_subject : Votre commande du {order_date} sur {site_title} a été expédiée
//                                                Your {site_title} order from {order_date} has been shipped
// woocommerce_customer_completed_order_heading : Votre commande est terminée et a été expédiée
//                                                Your order is complete and has been shipped
//
// woocommerce_customer_note_subject : Note ajoutée à votre commande du {order_date} sur {site_title}
//                                     Note added to your {site_title} order from {order_date}
// woocommerce_customer_note_heading : Une note a été ajoutée à votre commande
//                                     A note has been added to your order
//
// woocommerce_customer_new_account_subject : Votre compte sur {site_title}
//                                            Your account on {site_title}
// woocommerce_customer_new_account_heading : Bienvenue sur {site_title}
//                                            Welcome to {site_title}
//
// woocommerce_customer_refunded_order_subject_full : Votre commande du {order_date} sur {site_title} a été remboursée
//                                                    Your {site_title} order from {order_date} has been refunded
// woocommerce_customer_refunded_order_heading_full : Votre commande a été totalement remboursée
//                                                    Your order has been fully refunded
// woocommerce_customer_refunded_order_subject_partial : Votre commande du {order_date} sur {site_title} a été partiellement remboursée
//                                                       Your {site_title} order from {order_date} has been partially refunded
// woocommerce_customer_refunded_order_heading_partial : Votre commande a été partiellement remboursée
//                                                       Your order has been partially refunded
//
// woocommerce_customer_invoice_subject : Facture de la commande {order_number} du {order_date}
//                                        Invoice for order {order_number} from {order_date}
// woocommerce_customer_invoice_heading : Facture de la commande {order_number}
//                                        Invoice for order {order_number}
// woocommerce_customer_invoice_subject_paid : Votre commande sur {site_title} du {order_date}
//                                             Your {site_title} order from {order_date}
// woocommerce_customer_invoice_heading_paid : Détails de la commande {order_number}
//                                             Order {order_number} details 
//
// woocommerce_customer_reset_password_subject : Réinitialisation du mot de passe pour {site_title}
//                                               Password Reset for {site_title}
// woocommerce_customer_reset_password_heading : Instructions de réinitialisation de mot de passe
//                                               Password Reset Instructions
// 
// The default language strings are automically picked from the woocommerce emails settings, and used to build the polylang strings.
// The other languages have to be update in the strings polylang settings
//


if (!defined('ABSPATH')) exit;
if (!is_plugin_active('polylang/polylang.php')) exit;
if (!is_plugin_active('woocommerce/woocommerce.php')) exit;

// Read woocommerce email subjects and headings in options and create polylang strings 
function woocommerce_emails_register_strings() {
  if (function_exists('pll_register_string')) { // test if Polylang is activated
    declare_string ('woocommerce_customer_processing_order', 'subject', 'heading') ;
    declare_string ('woocommerce_customer_completed_order', 'subject', 'heading') ;
    declare_string ('woocommerce_customer_note', 'subject', 'heading') ; 
    declare_string ('woocommerce_customer_new_account', 'subject', 'heading') ; 
    declare_string ('woocommerce_customer_refunded_order', 'subject_full', 'heading_full') ; 
    declare_string ('woocommerce_customer_refunded_order', 'subject_partial', 'heading_partial') ; 
    declare_string ('woocommerce_customer_invoice', 'subject', 'heading') ; 
    declare_string ('woocommerce_customer_invoice', 'subject_paid', 'heading_paid') ; 
    declare_string ('woocommerce_customer_reset_password', 'subject', 'heading') ; 
  }
}
add_action('plugins_loaded', 'woocommerce_emails_register_strings'); // called only after all plugins are loaded
function declare_string ($typemail, $sub, $head) { 
   $array = get_option($typemail . '_settings');
   $subject = $array[$sub] ;
   $heading = $array[$head] ;
   pll_register_string($typemail . '_' . $sub, $subject, 'Woocommerce Mails');
   pll_register_string($typemail . '_' . $head, $heading, 'Woocommerce Mails');
}


// Adapt subject and heading emails depending on order lang
// processing_order
add_filter('woocommerce_email_subject_customer_processing_order', 'change_email_subject_customer_processing_order', 1, 2);
function change_email_subject_customer_processing_order( $subject, $order ) {
  return change_email_customer( $subject, $order ,'subject' ,'processing_order' );
}
add_filter('woocommerce_email_heading_customer_processing_order', 'change_email_heading_customer_processing_order', 1, 2);
function change_email_heading_customer_processing_order( $heading, $order ) {
  return change_email_customer( $heading, $order ,'heading' ,'processing_order' );
}
// note
add_filter('woocommerce_email_subject_customer_note', 'change_email_subject_customer_note', 1, 2);
function change_email_subject_customer_note( $subject, $order ) {
  return change_email_customer( $subject, $order ,'subject' ,'note' );
}
add_filter('woocommerce_email_heading_customer_note', 'change_email_heading_customer_note', 1, 2);
function change_email_heading_customer_note( $heading, $order ) {
  return change_email_customer( $heading, $order ,'heading' ,'note' );
}
// completed_order
add_filter('woocommerce_email_subject_customer_completed_order', 'change_email_subject_customer_completed_order', 1, 2);
function change_email_subject_customer_completed_order( $subject, $order ) {
  return change_email_customer( $subject, $order ,'subject' ,'completed_order' );
}
add_filter('woocommerce_email_heading_customer_completed_order', 'change_email_heading_customer_completed_order', 1, 2);
function change_email_heading_customer_completed_order( $heading, $order ) {
  return change_email_customer( $heading, $order ,'heading' ,'completed_order' );
}
// refunded_order
add_filter('woocommerce_email_subject_customer_refunded_order', 'change_email_subject_customer_refunded_order', 1, 2);
function change_email_subject_customer_refunded_order( $subject, $order ) {
  if (!isitfullrefund ($subject, $order, 'subject')) {
    return change_email_customer( $subject, $order ,'subject_partial' ,'refunded_order' ); 
  }else{
    return change_email_customer( $subject, $order ,'subject_full' ,'refunded_order' );
  }
}
add_filter('woocommerce_email_heading_customer_refunded_order', 'change_email_heading_customer_refunded_order', 1, 2);
function change_email_heading_customer_refunded_order( $heading, $order ) {
  if (!isitfullrefund ($heading, $order, 'heading')) {
    return change_email_customer( $heading, $order ,'heading_partial' ,'refunded_order' );
  }else{
    return change_email_customer( $heading, $order ,'heading_full' ,'refunded_order' );
  }
}
// invoice
add_filter('woocommerce_email_subject_customer_invoice', 'change_email_subject_customer_invoice', 1, 2);
function change_email_subject_customer_invoice( $subject, $order ) {
  return change_email_customer( $subject, $order ,'subject' ,'invoice' );
}
add_filter('woocommerce_email_heading_customer_invoice', 'change_email_heading_customer_invoice', 1, 2);
function change_email_heading_customer_invoice( $heading, $order ) {
  return change_email_customer( $heading, $order ,'heading' ,'invoice' );
}
// invoice_paid
add_filter('woocommerce_email_subject_customer_invoice_paid', 'change_email_subject_customer_invoice_paid', 1, 2);
function change_email_subject_customer_invoice_paid( $subject, $order ) {
  return change_email_customer( $subject, $order ,'subject_paid' ,'invoice' );
}
add_filter('woocommerce_email_heading_customer_invoice_paid', 'change_email_heading_customer_invoice_paid', 1, 2);
function change_email_heading_customer_invoice_paid( $heading, $order ) {
  return change_email_customer( $heading, $order ,'heading_paid' ,'invoice' );
}
// new_account
add_filter('woocommerce_email_subject_customer_new_account', 'change_email_subject_customer_new_account', 1, 2);
function change_email_subject_customer_new_account( $subject, $order ) {
  return change_email_customer( $subject, $order ,'subject' ,'new_account' );
}
add_filter('woocommerce_email_heading_customer_new_account', 'change_email_heading_customer_new_account', 1, 2);
function change_email_heading_customer_new_account( $heading, $order ) {
  return change_email_customer( $heading, $order ,'heading' ,'new_account' );
}
// reset_password
add_filter('woocommerce_email_subject_customer_reset_password', 'change_email_subject_customer_reset_password', 1, 2);
function change_email_subject_customer_reset_password( $subject, $order ) {
  return change_email_customer( $subject, $order ,'subject' ,'reset_password' );
}
add_filter('woocommerce_email_heading_customer_reset_password', 'change_email_heading_customer_reset_password', 1, 2);
function change_email_heading_customer_reset_password( $heading, $order ) {
  return change_email_customer( $heading, $order ,'heading' ,'reset_password' );
}


function change_email_customer( $string, $order, $suborhead, $typemail) {
  if (!function_exists('pll_register_string')) return $string;
  $array = get_option('woocommerce_customer_' .$typemail . '_settings');
  $stringinit = $array[$suborhead] ;
  $lang = pll_get_post_language ($order->id);
  if (!$lang) {
    $lang = pll_current_language();
  }
  $string = wpm_translate_string ($stringinit, $lang); // private function to replace pll_translate_string
  $string = str_replace('{order_date}',date_i18n( wc_date_format(), strtotime( $order->order_date ) ),$string ); 
  $string = str_replace('{order_number}','#' . $order->get_order_number(),$string ); 
  $string = str_replace('{site_title}',$blog_title=get_bloginfo(),$string ); 
  $string = htmlspecialchars_decode($string);
  return $string;
}


function isitfullrefund ($string, $order, $suborhead) {
  if (!function_exists('pll_register_string')) return $string;
  // not a very good solution but nothing better see in $order to know if full or partial refund
  $array = get_option('woocommerce_customer_refunded_order_settings'); 
  $stringinit = $array[$suborhead . '_full'] ;
  $stringout = str_replace('{order_date}',date_i18n( wc_date_format(), strtotime( $order->order_date ) ),$stringinit ); 
  $stringout = str_replace('{order_number}','#' . $order->get_order_number(),$stringout ); 
  $stringout = str_replace('{site_title}',$blog_title=get_bloginfo(),$stringout );
  $string = htmlspecialchars_decode($string); 
  if ($stringout == $string) {
    return true;
  }else{
    return false;
  }
}


// to replace pll_translate_string witch seems not to work in this case when current lang is the lang requested for translation
function wpm_translate_string($string, $lang) {
	static $cache; // cache object to avoid loading the same translations object several times
	if (empty($cache))
		$cache = new PLL_Cache();
	if (false === $mo = $cache->get($lang)) {
		$mo = new PLL_MO();
		$mo->import_from_db($GLOBALS['polylang']->model->get_language($lang));
		$cache->set($lang, $mo);
	}
	return $mo->translate($string);
}

/*****************************************************************************************************************/

/* bootstrap the plugin */
new Hyyan\WPI\Plugin();
