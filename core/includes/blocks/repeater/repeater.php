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
function cwicly_repeater_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_repeater_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_repeater_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_repeater_render_callback( $attributes, $content, $block ) {
	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}
	if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
		wp_enqueue_script( 'cc-splide', CWICLY_DIR_URL . 'assets/js/splide.min.js', null, CWICLY_VERSION, true );
		wp_enqueue_script( 'cc-slider-nav', CWICLY_DIR_URL . 'assets/js/cc-slider-nav.min.js', null, CWICLY_VERSION, true );
		wp_enqueue_style( 'cc-splide', CWICLY_DIR_URL . '/assets/css/splide.css', array(), filemtime( CWICLY_DIR_PATH . 'assets/css/splide.css' ) );
	}

	$conditions = \Cwicly\Helpers::block_conditions_check( $attributes, $block );

	if ( $conditions ) {
		return cc_render( $content, $attributes, $block );
	}
}
