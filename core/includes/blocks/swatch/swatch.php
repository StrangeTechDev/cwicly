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
		'render_callback' => 'cc_swatch_render_callback',
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
function cc_swatch_render_callback( $attributes, $content, $block ) {
	$hide_logged_in = cc_hide_logged_in( $attributes );
	$hide_guest     = cc_hide_guest( $attributes );

	if ( isset( $block->context['wooVariable'] ) && $block->context['wooVariable'] && isset( $attributes['swatchSlug'] ) && $attributes['swatchSlug'] && strpos( $attributes['swatchSlug'], 'g_' ) === false && isset( $block->context['wooVariable']['terms'] ) && $attributes['swatchType'] && $block->context['wooVariable']['type'] === $attributes['swatchType'] ) {
		if ( ( isset( $attributes['hideConditionsToggle'] ) && $attributes['hideConditionsToggle'] ) || ( $hide_guest && $hide_logged_in && cc_conditions_maker( $attributes, $block ) ) ) {
			$final = cc_render( $content, $attributes, $block );
			return $final;
		}
	} elseif ( ! isset( $block->context['isRepeater'] ) || ( isset( $block->context['isRepeater'] ) && $block->context['isRepeater'] && isset( $attributes['swatchType'] ) && ( ( isset( $block->context['wooVariable']['type'] ) && $block->context['wooVariable']['type'] === $attributes['swatchType'] ) || ( ( ! isset( $block->context['wooVariable']['type'] ) || ( isset( $block->context['wooVariable']['type'] ) && ! $block->context['wooVariable']['type'] ) ) && 'select' === $attributes['swatchType'] ) ) && isset( $attributes['swatchSlug'] ) && $attributes['swatchSlug'] && strpos( $attributes['swatchSlug'], 'g_' ) !== false ) ) {
		if ( ( isset( $attributes['hideConditionsToggle'] ) && $attributes['hideConditionsToggle'] ) || ( $hide_guest && $hide_logged_in && cc_conditions_maker( $attributes, $block ) ) ) {
			$final = cc_render( $content, $attributes, $block );
			return $final;
		}
	}
}
