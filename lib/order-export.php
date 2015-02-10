<?php

if( !defined('ABSPATH') ) exit;

if( !class_exists( 'wpg_order_export' ) ){

	class wpg_order_export {

		/**
		 * Bootstraps the class and hooks required actions & filters.
		 *
		 */
		public function __construct() {

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_tabs_order_export', array($this, 'settings_tab') );
			add_action( 'woocommerce_update_options_order_export', array($this, 'update_settings') );
			add_action( 'woocommerce_admin_field_short_desc', array($this, 'short_desc_field') );
			add_action( 'woocommerce_admin_field_advanced_options', array($this, 'advanced_options') );
			add_action( 'admin_enqueue_scripts', array($this, 'scripts') );
			add_action( 'woocommerce_settings_wc_settings_tab_orderexport_section_end_after', array($this, 'section_end'), 999 );
			
			add_action('wp_ajax_wpg_order_export', array($this, 'wpg_order_export'));
			add_action('wp_ajax_nopriv_wpg_order_export', array($this, 'wpg_order_export'));
			add_action( 'admin_init' , array( $this, 'oe_download' ) );
		}

		/**
		 * Runs when plugin is activated.
		 */
		function install() {

			global $wpg_order_columns;

			foreach( $wpg_order_columns as $key=>$val ){
				
				$option = get_option( $key, null );
				if( empty( $option ) ) {
					update_option($key, 'yes');
				}
			}
		}

		public function scripts( $pagehook ) {

			if(  (!empty( $_GET['tab'] )&& $_GET['tab'] === 'order_export') ) {
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_style('jquery-ui-datepicker');
				wp_enqueue_style('wpg-style', OE_CSS.'style.css');
				wp_enqueue_script( 'order-export', OE_JS. 'orderexport.js', array('jquery','jquery-ui-datepicker'), false, true );
			}
		}

		/**
		 * Add a new settings tab to the WooCommerce settings tabs array.
		 *
		 * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
		 * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
		 */
		public function add_settings_tab( $settings_tabs ) {
			$settings_tabs['order_export'] = __( 'Order Export', 'woocommerce-simply-order-export' );
			return $settings_tabs;
		}


		/**
		 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
		 *
		 * @uses woocommerce_admin_fields()
		 * @uses self::get_settings()
		 */
		public function settings_tab() {
			woocommerce_admin_fields( $this->get_settings() );
		}


		/**
		 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
		 *
		 * @uses woocommerce_update_options()
		 * @uses self::get_settings()
		 */
		public function update_settings() {
			woocommerce_update_options( $this->get_settings() );
		}


		/**
		 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
		 *
		 * @return array Array of settings for @see woocommerce_admin_fields() function.
		 */
		public function get_settings() {

			$settings = array(

				'section_title' => array(
					'name'     => __( 'WooCommerce Order Export', 'woocommerce-simply-order-export' ),
					'type'     => 'title',
					'desc'     => '',
					'id'       => 'wc_settings_tab_orderexport_section_title'
				),

				'short_desc' => array(
					'type'     => 'short_desc',
					'desc'     => __( 'Please choose settings for order export.', 'woocommerce-simply-order-export' ),
				),

				'customer_name' => array(
					'name' => __( 'Customer Name', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Customer Name', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_customer_name'
				),

				'product_info' => array(
					'name' => __( 'Items Purchased', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Export product details.', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_product_info'
				),

				'amount' => array(
					'name' => __( 'Amount', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Amount paid by customer', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_amount'
				),

				'email' => array(
					'name' => __( 'Email', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Email of customer', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_customer_email'
				),

				'phone' => array(
					'name' => __( 'Phone', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Phone number of customer', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_customer_phone'
				),			

				'status' => array(
					'name' => __( 'Status', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Order Status', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_order_status'
				),			

				'advanced_options' => array(
					'name' => __( 'Advanced Options', 'woocommerce-simply-order-export' ),
					'type' => 'advanced_options',
					'desc' => __( 'Order Status', 'woocommerce-simply-order-export' )
				),

				'orderexport_section_end' => array(
					 'type' => 'sectionend',
					 'id' => 'wc_settings_tab_orderexport_section_end'
				)

			);

			return apply_filters( 'wc_settings_tab_order_export', $settings );
		}

		/**
		 * Add custom types
		 */
		function short_desc_field( $value ) {

			$value['desc'] = empty($value['desc']) ? '' : $value['desc'];
			echo '<p class="wpg-short-desc">'. $value['desc'] .'</p>';
		}

		function section_end() { ?>

			<h3 class="orderexport-action"><?php _e( 'Select Duration and Export', 'woocommerce-simply-order-export' ) ?></h3>

			<p class="wpg-response-msg"></p>
			<div class="clearfix wpg-inputs">
				<div class="wpg-dateholder">
					<label for="wpg-start-date"><?php _e('Start Date', 'woocommerce-simply-order-export') ?></label>
					<input id="wpg-start-date" type="text" name="start_date" class="wpg-datepicker" value="" />
				</div>
				<div class="wpg-dateholder">
					<label for="wpg-end-date"><?php _e('End Date', 'woocommerce-simply-order-export') ?></label>
					<input id="wpg-end-date" type="text" name="end_date" class="wpg-datepicker" value="" />
				</div>

				<div class="orderexport-button">
					<input type="button" class="button wpg-order-export" value="<?php _e('Export Orders', 'woocommerce-simply-order-export') ?>" />
					<span class="spinner"></span>
				</div>
			</div>
			<input type="hidden" id="wpg_order_export_nonce" name="nonce" value="<?php echo wp_create_nonce('wpg_order_export') ?>" />
			<input type="hidden" name="action" value="wpg_order_export" /><?php
		}

		/**
		 * Advanced options.
		 */
		function advanced_options() { ?>

			<tr valign="top" class="single_select_page">
				<td style="padding-left: 0;" colspan="2">
					<div class="woo-soe">
						<a id="woo-soe-advanced" title="<?php _e('Click to see advanced options', 'woocommerce-simply-order-export') ?>" href="#"><?php _e('Advanced options', 'woocommerce-simply-order-export') ?></a>
						<p><span style="font-style: italic;"><?php _e( 'These are one time use options and will not be saved.', 'woocommerce-simply-order-export' ) ?></span></p>
						<div class="woo-soe-advanced" style="display: none;">
							<table>

								<tr>
									<th>
										<?php _e( 'Order Export Filename', 'woocommerce-simply-order-export' ) ?>
										<img class="help_tip" data-tip="<?php _e('This will be the downloaded csv filename', 'woocommerce-simply-order-export') ?>" src="<?php echo OE_IMG; ?>help.png" height="16" width="16">
									</th>
									<td><input type="text" name="woo_soe_csv_name" value="" /><?php _e('.csv', 'woocommerce-simply-order-export') ?></td>
								</tr>

								<tr>
									<th>
										<?php _e('Order Statuses', 'woocommerce-simply-order-export') ?>
										<img class="help_tip" data-tip="<?php _e('Orders with only selected status will be exported, if none selected then all order status will be exported', 'woocommerce-simply-order-export') ?>" src="<?php echo OE_IMG; ?>help.png" height="16" width="16">
									</th>
									<td>
										<div class="order-statuses"><label><input type="checkbox" value="wc-completed" name="order_status[]" /><?php _e('Completed', 'woocommerce-simply-order-export') ?></label></div>
										<div class="order-statuses"><label><input type="checkbox" value="wc-processing" name="order_status[]" /><?php _e('Processing', 'woocommerce-simply-order-export') ?></label></div>
										<div class="order-statuses"><label><input type="checkbox" value="wc-on-hold" name="order_status[]" /><?php _e('On hold', 'woocommerce-simply-order-export') ?></label></div>
										<div class="order-statuses"><label><input type="checkbox" value="wc-pending" name="order_status[]" /><?php _e('Pending', 'woocommerce-simply-order-export') ?></label></div>
										<div class="order-statuses"><label><input type="checkbox" value="wc-cancelled" name="order_status[]" /><?php _e('Cancelled', 'woocommerce-simply-order-export') ?></label></div>
										<div class="order-statuses"><label><input type="checkbox" value="wc-refunded" name="order_status[]" /><?php _e('Refunded', 'woocommerce-simply-order-export') ?></label></div>
										<div class="order-statuses"><label><input type="checkbox" value="wc-failed" name="order_status[]" /><?php _e('Failed', 'woocommerce-simply-order-export') ?></label></div>
									</td>
								</tr>

							</table>
						</div>
					</div>
				</td>
			</tr><?php
		}

		/**
		 * Validates input
		 */
		static function validate() {

			if( empty( $_POST['start_date'] ) || ( empty( $_POST['end_date'] ) ) ){
				return new WP_Error( 'dates_empty', __( 'Enter both dates', 'woocommerce-simply-order-export' ) );
			}
			
			if( !self::checkdate( $_POST['start_date'] ) ) {
				return new WP_Error( 'invalid_start_date', __( 'Invalid start date.', 'woocommerce-simply-order-export' ) );
			}
			
			if( !self::checkdate( $_POST['end_date'] ) ) {
				return new WP_Error( 'invalid_end_date', __( 'Invalid end date.', 'woocommerce-simply-order-export' ) );
			}
			
			if( empty( $_POST['nonce'] ) ){
				return new WP_Error( 'empty_nonce', __( 'Invalid request', 'woocommerce-simply-order-export' ) );
			}elseif( !wp_verify_nonce( filter_input( INPUT_POST, 'nonce', FILTER_DEFAULT ), 'wpg_order_export') ){
				return new WP_Error( 'invalid_nonce', __( 'Invalid nonce.', 'woocommerce-simply-order-export' ) );
			}

			if( !empty( $_POST['woo_soe_csv_name'] ) && ( preg_match( '/^[a-zA-Z][a-zA-Z0-9\-\_]*\Z/', $_POST['woo_soe_csv_name'] ) === 0 ) ) {
				return new WP_Error( 'invalid_csv_filename', __( 'Invalid CSV filename. Only letters, numbers, dashes and underscore are allowed.' ) );
			}
		}

		/**
		 * Checks if a date is valid or not.
		 * Returns true if valid , false otherwise.
		 */
		static function checkdate( $date ){

			$date = explode( '-', $date );

			if( count( $date ) !== 3 )
				return false;

			if( !is_numeric( $date[0] ) || !is_numeric( $date[1] ) || !is_numeric( $date[2] ) )
				return false;

			return checkdate( $date[1], $date[2], $date[0] );
		}

		/**
		 * Validates input, creates csv file and sends the response to ajax.
		 */
		function wpg_order_export() {

			$response = array( 'error'=>false, 'msg'=>'', 'url'=>'' );

			if( is_wp_error( $validate = self::validate() ) ){
				
				$response = array( 'error'=>true, 'msg'=>$validate->get_error_message(), 'url'=>'' );
				echo json_encode($response);
				die();
			}

			$result = order_export_process::get_orders();

			if( is_wp_error( $result ) ){
				$response['error'] = true;
				$response['msg'] = $result->get_error_message();
			}else{

				$upload_dir = wp_upload_dir();
				$response['url'] = $upload_dir['basedir'].'/order_export.csv';
				$response['msg'] = empty( $_POST['woo_soe_csv_name'] ) ? 'order_export' : sanitize_file_name($_POST['woo_soe_csv_name']);
			}

			echo json_encode( $response );
			die;
		}
		
		/**
		 * 
		 */
		function oe_download(){

            $upload_dir =   wp_upload_dir();
            $filename   =   $upload_dir['basedir']. '/order_export.csv';
			$download_filename = empty($_GET['filename']) ?  'order_export' : $_GET['filename'];

            if( !empty( $_GET['oe'] ) && file_exists( $filename ) && current_user_can('manage_options') ){

                $file = fopen( $filename, 'r' );
                $contents = fread($file, filesize($filename));
                fclose($file);

                unlink($filename);

                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header('Content-Description: File Transfer');
                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=$download_filename.csv");
                header("Expires: 0");
                header("Pragma: public");

                $fh = @fopen( 'php://output', 'w' );
                fwrite( $fh, $contents );
                fclose($fh);
                exit();

            }
        }


	}
}
