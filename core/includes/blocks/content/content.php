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
function cwicly_content_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_content_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_content_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_content_render_callback( $attributes, $content, $block ) {
	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}

	$hide_logged_in = cc_hide_logged_in( $attributes );
	$hide_guest     = cc_hide_guest( $attributes );

	if ( ( isset( $attributes['hideConditionsToggle'] ) && $attributes['hideConditionsToggle'] ) || ( $hide_guest && $hide_logged_in && cc_conditions_maker( $attributes, $block ) ) ) {
		return cc_render( $content, $attributes, $block );
	}
}
