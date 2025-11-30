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
		'render_callback' => 'cc_fragment_render_callback',
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
function cc_fragment_render_callback( $attributes, $content, $block ) {
	$hide_logged_in = cc_hide_logged_in( $attributes );
	$hide_guest     = cc_hide_guest( $attributes );

	if ( ( isset( $attributes['hideConditionsToggle'] ) && $attributes['hideConditionsToggle'] ) || ( $hide_logged_in && $hide_guest && cc_conditions_maker( $attributes, $block ) ) ) {
		ob_start();
		if ( isset( $attributes['fragment'] ) && $attributes['fragment'] ) {
			$containers = Cwicly\Themer::get_fragment( $attributes['fragment'] );
			if ( isset( $containers ) && is_array( $containers ) ) {
				foreach ( $containers as $templates ) {
					foreach ( $templates as $template ) {
						if ( isset( $template->slug ) && isset( $template->theme ) ) {
							Cwicly\Themer::add_template_styles( $template->theme, $template->slug, 'tp' );
						}
						echo do_blocks( $template->content );
					}
				}
			}
		}
		return ob_get_clean();
	}
}
