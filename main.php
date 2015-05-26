<?php
/**
 * Plugin Name: WooCommerce Simply Order Export
 * Description: Downloads order details in csv format
 * Version: 1.2.0
 * Author: Ankit Gade
 * Author URI: http://sharethingz.com
 * License: GPL2
 */

if( !defined('ABSPATH') ){
	exit;
}

/**
 * Check if WooCommerce is already activated.
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	class WooCommerce_simply_order_export {

		/**
		 * @var string
		 */
		public $version = '1.2.0';

		/**
		 * Constructor
		 */
		function __construct() {

			/**
			 * Fires this function when plugin gets activated.
			 */
			register_activation_hook( __FILE__, array( __CLASS__, 'install' ) );
			$this->define_constants();
			$this->includes();
			add_action( 'init', array($this, 'init') );
		}

		/**
		 * Fires at 'init' hook
		 */
		function init() {

			$this->load_plugin_textdomain();
			$this->set_variables();
			$this->instantiate();
		}

		/**
		 * Load locale
		 */
		function load_plugin_textdomain() {

			load_plugin_textdomain( 'woocommerce-simply-order-export', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
		}

		/**
		 * Sets the variables
		 */
		function __set( $name, $value ) {

			/**
			 * Check for valid names
			 */
			if( in_array( $name, array( 'wpg_order_export', 'wpg_order_columns' ) ) ){				
				$GLOBALS[$name] = $value;
			}
		}

		/**
		 * Define all constants
		 */
		function define_constants() {

			define( 'OE_URL', plugins_url('', __FILE__) ); /* plugin url */
			define( 'OE_CSS', OE_URL. "/assets/css/" ); /* Define all necessary variables first */
			define( 'OE_JS',  OE_URL. "/assets/js/" );
			define( 'OE_IMG',  OE_URL. "/assets/img/" );
		}

		/**
		 * Set necessary variables.
		 */
		function set_variables() {

			$this->wpg_order_columns = apply_filters( 'wpg_order_columns', array(
												'wc_settings_tab_order_id'=>__( 'Order ID', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_customer_name'=>__( 'Customer Name', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_product_info'=>__( 'Product Information', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_amount'=> __( 'Order Amount ( $ )', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_customer_email'=> __( 'Customer Email', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_customer_phone'=>__( 'Phone Number', 'woocommerce-simply-order-export' ),
												'wc_settings_tab_order_status'=>__( 'Order Status', 'woocommerce-simply-order-export' )
											)
										);
		}

		/**
		 * Include helper classes
		 */
		function includes() {
			// Includes PHP files located in 'lib' folder
			foreach( glob ( dirname(__FILE__). "/lib/*.php" ) as $lib_filename ) {
				require_once( $lib_filename );
			}
		}

		/**
		 * Runs when plugin is activated.
		 */
		function install() {
			
			ob_start();

			global $wpg_order_columns;

			foreach( $wpg_order_columns as $key=>$val ){

				$option = get_option( $key, null );
				if( empty( $option ) ) {
					update_option($key, 'yes');
				}
			}
			
			ob_end_clean();
		}

		/**
		 * Instantiate necessary classes.
		 */
		function instantiate() {

			$this->wpg_order_export = new wpg_order_export();
		}

	}

	new WooCommerce_simply_order_export();
}

