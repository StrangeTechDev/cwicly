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
register_block_type(
	__DIR__,
	array(
		'render_callback' => 'cc_filter_render_callback',
	)
);

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_filter_render_callback( $attributes, $content, $block ) {
	$hide_logged_in = cc_hide_logged_in( $attributes );
	$hide_guest     = cc_hide_guest( $attributes );

	$decoded = ( new \Cwicly\Block_Template() )->single_block( $block, false )[0];

	if ( ( isset( $attributes['hideConditionsToggle'] ) && $attributes['hideConditionsToggle'] ) || ( $hide_guest && $hide_logged_in && cc_conditions_maker( $attributes, $block ) ) ) {
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			$action = 'wp_head';
		} else {
			$action = 'wp_footer';
		}
		add_action(
			$action,
			function () use ( $decoded, $attributes ) {
				echo '<script id="' . esc_attr( $attributes['id'] ) . '-ft-json" type="application/json">' . wp_json_encode( $decoded, JSON_UNESCAPED_SLASHES ) . '</script>';
			}
		);
		return cc_render( $content, $attributes, $block );
	}
}
