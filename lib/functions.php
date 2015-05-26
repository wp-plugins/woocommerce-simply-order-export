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

function db($data, $exit = false){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
	if( $exit )
	die;
}