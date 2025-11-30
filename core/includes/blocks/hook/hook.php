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
		'render_callback' => 'cc_hook_render_callback',
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
function cc_hook_render_callback( $attributes, $content, $block ) {
	$hide_logged_in = cc_hide_logged_in( $attributes );
	$hide_guest     = cc_hide_guest( $attributes );

	if ( is_admin() || \Cwicly\Helpers::is_rest() ) {
		return '';
	}

	if ( ( isset( $attributes['hideConditionsToggle'] ) && $attributes['hideConditionsToggle'] ) || ( $hide_guest && $hide_logged_in && cc_conditions_maker( $attributes, $block ) ) ) {
		if ( isset( $attributes['hook'] ) && $attributes['hook'] ) {
			ob_start();
			do_action( $attributes['hook'] );
			return ob_get_clean();
		}
	}
}
