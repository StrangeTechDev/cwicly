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
function cwicly_navlink_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_navlink_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_navlink_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_navlink_render_callback( $attributes, $content, $block ) {
	if ( isset( $attributes['hoverAnimation'] ) && $attributes['hoverAnimation'] ) {
		wp_enqueue_style( 'cc-hover-animation', CWICLY_DIR_URL . 'assets/css/hover-animation.css', null, CWICLY_VERSION );
	}
	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}

	$conditions = \Cwicly\Helpers::block_conditions_check( $attributes, $block );

	if ( $conditions ) {
		return cc_render( $content, $attributes, $block );
	}
}
