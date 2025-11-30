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
function cwicly_styler_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_styler_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_styler_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_styler_render_callback( $attributes, $content, $block ) {
	return null;
}
