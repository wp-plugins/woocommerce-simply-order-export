<?php

if( !defined('ABSPATH') ) exit;

if( !class_exists('order_export_process') ) {

	class order_export_process {

		static $delimiter;

		/**
		 * Tells which fields to export
		 */
		static function export_options() {

			global $wpg_order_columns;
			$fields = array();

			foreach( $wpg_order_columns as $key=>$val ) {

				$retireve = get_option( $key, 'no' );
				$fields[$key] = ( strtolower($retireve) === 'yes' ) ? true : false;
			}
			
			return $fields;
		}

		/**
		 * Returns order details
		 */
		static function get_orders() {

			$fields		=	self::export_options();
			$headings	=	self::csv_heading($fields);

			$delimiter	=	( empty( $_POST['wpg_delimiter'] ) || ( gettype( $_POST['wpg_delimiter'] ) !== 'string' ) ) ? ',' : $_POST['wpg_delimiter'][0];

			/**
			 * Filter : wpg_delimiter
			 * Filters the delimiter for exported csv file. Override user defined
			 * delimiter by using this filter.
			 */
			self::$delimiter = apply_filters( 'wpg_delimiter', $delimiter );

			/* Check which order statuses to export. */
			$order_statuses	=	( !empty( $_POST['order_status'] ) && is_array( $_POST['order_status'] ) ) ? $_POST['order_status'] : array_keys( wc_get_order_statuses() );

			$args = array( 'post_type'=>'shop_order', 'posts_per_page'=>-1, 'post_status'=> apply_filters( 'wpg_order_statuses', $order_statuses ) );
			$args['date_query'] = array( array( 'after'=>  filter_input( INPUT_POST, 'start_date', FILTER_DEFAULT ), 'before'=> filter_input( INPUT_POST, 'end_date', FILTER_DEFAULT ), 'inclusive' => true ) );

			$orders = new WP_Query( $args );

			if( $orders->have_posts() ) {

				/**
				 * This will be file pointer
				 */
				$csv_file = self::create_csv_file();
				
				if( empty($csv_file) ) {
					return new WP_Error( 'not_writable', __( 'Unable to create csv file, upload folder not writable', 'woocommerce-simply-order-export' ) );
				}

				fputcsv( $csv_file, $headings, self::$delimiter );

				while( $orders->have_posts() ) {

					$csv_values = array();

					$orders->the_post();
					$order_details = new WC_Order( get_the_ID() );

					/**
					 * Check if we need customer name.
					 */
					if( !empty( $fields['wc_settings_tab_customer_name'] ) && $fields['wc_settings_tab_customer_name'] === true )
						array_push( $csv_values, self::customer_name( get_the_ID() ) );

					/**
					 * Check if we need product info.
					 */
					if( !empty( $fields['wc_settings_tab_product_info'] ) && $fields['wc_settings_tab_product_info'] === true )
						array_push( $csv_values, self::product_info( $order_details ) );

					/**
					 * Check if we need order amount.
					 */
					if( !empty( $fields['wc_settings_tab_amount'] ) && $fields['wc_settings_tab_amount'] === true )
						array_push( $csv_values, $order_details->get_total() );

					/**
					 * Check if we need customer email.
					 */
					if( !empty( $fields['wc_settings_tab_customer_email'] ) && $fields['wc_settings_tab_customer_email'] === true )
						array_push( $csv_values, self::customer_meta( get_the_ID(), '_billing_email' ) );

					/**
					 * Check if we need customer phone.
					 */
					if( !empty( $fields['wc_settings_tab_customer_phone'] ) && $fields['wc_settings_tab_customer_phone'] === true )
						array_push( $csv_values, self::customer_meta( get_the_ID(), '_billing_phone' ) );

					/**
					 * Check if we need order status.
					 */
					if( !empty( $fields['wc_settings_tab_order_status'] ) && $fields['wc_settings_tab_order_status'] === true ){
						array_push( $csv_values, ucwords($order_details->get_status()) );
					}

					/**
					 * Perform some action before writing to csv.
					 * Callback functions hooked to this action should accept a reference pointer to $csv_values.
					 */
					do_action_ref_array( 'wpg_before_csv_write', array( &$csv_values, $order_details, $fields ) );

					fputcsv( $csv_file, $csv_values, self::$delimiter );
				}
				wp_reset_postdata();

			}else {

				return new WP_Error( 'no_orders', __( 'No orders for specified duration.', 'woocommerce-simply-order-export' ) );
			}
		}

		/**
		 * Returns customer related meta.
		 * Basically it is just get_post_meta() function wrapper.
		 */
		static function customer_meta( $order_id , $meta = '' ) {
			
			if( empty( $order_id ) || empty( $meta ) )
				return '';
			
			return get_post_meta( $order_id, $meta, true );
		}

		/**
		 * Returns list of product names for an order
		 * @param type $order_details
		 * @return string.
		 */
		static function product_info( $order_details ) {
			
			if( !is_a( $order_details, 'WC_Order' ) ){
				return '';
			}

			global $wpdb;

			$items_list = array();
			$items = $order_details->get_items();

			if ( !empty( $items ) ) {

				foreach( $items as $key=>$item ) {

					$metadata = $order_details->has_meta( $key );
					$exclude_meta = apply_filters( 'woocommerce_hidden_order_itemmeta', array(
							'_qty',
							'_tax_class',
							'_product_id',
							'_variation_id',
							'_line_subtotal',
							'_line_subtotal_tax',
							'_line_total',
							'_line_tax',
						) );
					
					$item_name = (string)$item['qty']. ' '.$item['name'];
					
					$variation_details = array();


					foreach( $metadata as $k => $meta ) {

						if( in_array( $meta['meta_key'], $exclude_meta ) ){
							continue;
						}

						// Skip serialised meta
						if ( is_serialized( $meta['meta_value'] ) ) {
							continue;
						}
						
						// Get attribute data
						if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta['meta_key'] ) ) ) {

							$term               = get_term_by( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
							$meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
							$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
							//array_push( $variation_details, wp_kses_post( urldecode( $meta['meta_key'] ) ) .': '.wp_kses_post( urldecode( $meta['meta_value'] ) ) );
						}else {
							$meta['meta_key']   = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $meta['meta_key'], $_product ), $meta['meta_key'] );
						}

						array_push( $variation_details, wp_kses_post( urldecode( $meta['meta_key'] ) ) .': '.wp_kses_post( urldecode( $meta['meta_value'] ) ) );
					}

					if( !empty( $variation_details ) ) {
						$variation_details = implode( ', ', $variation_details );
						$item_name .= '( '. $variation_details .' )';
					}

					array_push($items_list, $item_name);
				}
			}
			
			return $items_list = implode( ', ', $items_list);
		}

		/**
		 * Returns customer name for particular order
		 * @param type $order_id
		 * @return string
		 */
		static function customer_name( $order_id ) {

			if( empty( $order_id ) ){
				return '';
			}

			$firstname = get_post_meta( $order_id, '_billing_first_name', true );
			$lastname  = get_post_meta( $order_id, '_billing_last_name', true );

			return trim( $firstname.' '. $lastname );			
		}

		/**
		 * Makes first row for csv
		 */
		static function csv_heading( $fields ) {

			if( !is_array( $fields ) ){
				return false;
			}

			global $wpg_order_columns;
			$headings = array();

			foreach( $fields as $key=>$val ) {

				if( $val === true && array_key_exists( $key, $wpg_order_columns ) ){
					array_push( $headings, $wpg_order_columns[$key] );
				}
			}

			return $headings;

		}

		/**
		 * Creates csb file in upload directory.
		 */
		static function create_csv_file() {

			$upload_dir = wp_upload_dir();
			return $csv_file = fopen( $upload_dir['basedir']. '/order_export.csv', 'w+');
		}
	}
}
