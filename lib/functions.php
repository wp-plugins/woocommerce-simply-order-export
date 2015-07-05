<?php

/**
 * Filters elements of array
 * @param bool $value
 * @return boolean
 */
function wpg_array_filter( $value ) {

	if( $value == true ) {
		return true;
	}
	
	return false;
}

/**
 * Hook for wp_schedule event in wsoe_admin_notices class
 */
function wsoe_call_notices_func() {
	wsoe_admin_notices::update_notices();
}
add_action( 'wsoe_call_notices', 'wsoe_call_notices_func' );