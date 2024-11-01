<?php

function rone_get_options() {
	return get_option( RWSM_PLUGIN_NAME . '-options' );
}

function rone_array_merge_recursive_distinct ( array &$array1, array &$array2 )
{
	$merged = $array1;

	foreach ( $array2 as $key => &$value )
	{
		if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
		{
			$merged [$key] = rone_array_merge_recursive_distinct ( $merged [$key], $value );
		}
		else
		{
			$merged [$key] = $value;
		}
	}

	return $merged;
}


function rone_get_mode_url( $mode ) {
	$url = add_query_arg( array(
		'mode' => $mode,
		'rone_change_mode' => 1
	) );
	return esc_url( $url );
}

function rone_compare_dates( $start, $end ) {
	$store_open = false;
	$current_timestamp = current_time( 'timestamp' );
	$current_time = date('H:i', $current_timestamp );
	if( $current_time > $start && $current_time < $end ) {
		$store_open = true;
	}
	return $store_open;
}

function rone_is_store_open() {
	$store_open = false;
	$options = rone_get_options();
	$days = $options['days'];
	$today = strtolower( date('l', current_time('timestamp' ) ) );
	switch ($days) {
		case 'all':
			foreach( (array) $options['store'][$days] as $key => $value ) {
				$open = $value['open'];
				$close = $value['close'];
				if( rone_compare_dates( $open, $close ) ) {
					$store_open = true;
				}
			}
			break;
		case 'individual':
			foreach( (array) $options['store'][$today] as $key => $value ) {
				$open = $value['open'];
				$close = $value['close'];
				if( rone_compare_dates( $open, $close ) ) {
					$store_open = true;
				}
			}
			break;
	}
	return $store_open;
}

function rone_get_current_mode() {
	$options = rone_get_options();
	foreach ( (array) $options['modes'] as $key => $value) {
		if( $value['status'] != 1 ) {
			continue;
		}
		$open = $value['open'];
		$close = $value['close'];
		if( rone_compare_dates( $open, $close ) ) {
			return $key; 
		}
	}
}

function rone_store_status() {
	return ( rone_is_store_open() ) ? 'Open' : 'Closed';
}

function is_woo_store_options_page() {

	$page_id = 'woocommerce_page_'.RWSM_PLUGIN_NAME;
	$current_screen = get_current_screen();
	if( $page_id == $current_screen->id ) {
		return true; 
	}
	return false;
}

// $color = str_pad( dechex( mt_rand( 10, 230 ) ), 2, '0', STR_PAD_LEFT);

