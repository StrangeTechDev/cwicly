<?php
/**
 * Register Cwicly block.
 *
 * @package cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register block render callback.
 */
function cwicly_maps_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_maps_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_maps_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_maps_render_callback( $attributes, $content, $block ) {
	$api_key = get_option( 'cwicly_gmap' );
	if ( $api_key && ! is_admin() ) {
		wp_enqueue_script( 'Maps', CWICLY_DIR_URL . 'assets/js/maps.js', null, CWICLY_VERSION, false );
		wp_enqueue_script( 'CCgmap', 'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&callback=Function.prototype', null, CWICLY_VERSION, false );
	}
	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}

	$conditions = \Cwicly\Helpers::block_conditions_check( $attributes, $block );

	if ( $conditions ) {
		return cc_render( $content, $attributes, $block );
	}
}
