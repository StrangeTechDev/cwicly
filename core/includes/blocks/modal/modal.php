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
function cwicly_modal_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_modal_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_modal_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_modal_render_callback( $attributes, $content, $block ) {

	if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
		$header = false;
	} else {
		$header = true;
	}

	if ( ! is_admin() ) {
		wp_enqueue_script( 'cc-modal', CWICLY_DIR_URL . 'assets/js/cc-modal.min.js', null, CWICLY_VERSION, $header );
		wp_enqueue_style( 'cc-modal', CWICLY_DIR_URL . 'assets/css/modal.min.css', null, CWICLY_VERSION );
	}

	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}

	$conditions = \Cwicly\Helpers::block_conditions_check( $attributes, $block );

	if ( $conditions ) {
		return cc_render( $content, $attributes, $block );
	}
}
