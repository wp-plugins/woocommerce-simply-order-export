<?php
/**
 * Plugin Name: WooCommerce Simply Order Export
 * Description: Downloads order details in csv format
 * Version: 1.0.1
 * Author: Ankit Gade
 * Author URI: http://sharethingz.com
 * License: GPL2
 */
define( 'OE_SLASH' , '/' );

/* plugin url */
define( 'OE_URL', plugins_url('', __FILE__) );

/* Define all necessary variables first */
define( 'OE_CSS', OE_URL. "/assets/css/" );
define( 'OE_JS',  OE_URL. "/assets/js/" );
define( 'OE_IMG',  OE_URL. "/assets/img/" );

global $wpg_order_export, $wpg_order_columns;

$wpg_order_columns = array(
						'wc_settings_tab_customer_name'=>__( 'Customer Name', 'OE' ),
						'wc_settings_tab_product_info'=>__( 'Product Information', 'OE' ),
						'wc_settings_tab_amount'=> __( 'Order Amount ( $ )', 'OE' ),
						'wc_settings_tab_customer_email'=> __( 'Customer Email', 'OE' ),
						'wc_settings_tab_customer_phone'=>__( 'Phone Number' ),
						'wc_settings_tab_order_status'=>__( 'Order Status' )
					);

/**
 * Filter order column array to add/remove elements.
 */
$wpg_order_columns = apply_filters( 'wpg_order_columns', $wpg_order_columns );

// Includes PHP files located in 'lib' folder
foreach( glob ( dirname(__FILE__). "/lib/*.php" ) as $lib_filename ) {
//	print_r($lib_filename);
    require_once( $lib_filename );
}

$wpg_order_export = new wpg_order_export();

register_activation_hook( __FILE__, array( 'wpg_order_export', 'install' ) );
