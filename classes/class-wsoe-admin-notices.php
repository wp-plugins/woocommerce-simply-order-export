<?php
/**
 * Class to handle admin notices
 */
if( !class_exists('wsoe_admin_notices') ) {

	class wsoe_admin_notices {

		function __construct() {

			add_action( 'admin_print_styles', array( $this, 'check_wsoe_messages' ) );
			add_action( 'wp_loaded', array( $this, 'wsoe_hide_notices' ) );
		}

		static function update_notices() {

			$wsoe_messages = array();
			if( !in_array( 'woocommerce-simply-order-export-add-on/main.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )) ) ) {
				$wsoe_messages['wsoe_addon_installed'] = false;
				$wsoe_messages['wsoe_addon_notice_display'] = true;
			}else {
				$wsoe_messages['wsoe_addon_installed'] = true;
				$wsoe_messages['wsoe_addon_notice_display'] = false;
			}

			/**
			 * This option will be utilized in admin_notices
			 */
			update_option( 'wsoe_messages', $wsoe_messages );
		}

		function check_wsoe_messages() {

			$wsoe_messages = get_option('wsoe_messages', array());
			$wsoe_messages = wp_parse_args( $wsoe_messages, array('wsoe_addon_installed'=>false, 'wsoe_addon_notice_display'=>true ) );
			if ( ( !$wsoe_messages['wsoe_addon_installed'] ) &&  ( $wsoe_messages['wsoe_addon_notice_display'] ) ) {
				add_action( 'admin_notices', array( $this, 'install_addon' ) );
			}
		}

		function install_addon() {

			include WSOE_BASE. 'views/html-notice-addon-support.php';
		}

		function wsoe_hide_notices() {
			if ( isset( $_GET['wsoe-hide-notice'] ) ) {
				$hide_notice = sanitize_text_field( $_GET['wsoe-hide-notice'] );
				self::remove_notice( $hide_notice );
				do_action( 'wsoe_hide_' . $hide_notice . '_notice' );
			}
		}

		static function remove_notice($notice) {

			switch($notice) {

				case 'wsoe_addon_notice':
					self::hide_wsoe_addon_notice();
				break;
			
				default :
					do_action('wsoe_hide_notice_'.$notice);
					break;
			}
		}
		
		static function hide_wsoe_addon_notice() {
			$wsoe_messages = get_option('wsoe_messages', array());
			$wsoe_messages['wsoe_addon_notice_display'] = false;
			update_option( 'wsoe_messages', $wsoe_messages );

			wp_schedule_event(  time()+30, 'daily', 'wsoe_call_notices' );
		}
	}

	new wsoe_admin_notices();

}