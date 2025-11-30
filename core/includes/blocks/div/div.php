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
function cwicly_div_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_div_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_div_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param array  $block Block data.
 *
 * @return string
 */
function cc_div_render_callback( $attributes, $content, $block ) {

	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}

	$conditions = \Cwicly\Helpers::block_conditions_check( $attributes, $block );

	if ( $conditions ) {
		if ( ! is_admin() && isset( $attributes['dynamicContext'] ) && 'woocart' === $attributes['dynamicContext'] ) {
			// FRONTEND RENDERING.
			$decoded = ( new \Cwicly\Block_Template() )->single_block( $block, false )[0];
			wp_enqueue_script( 'CCdyn', CWICLY_DIR_URL . 'assets/js/fr/dist/main-qHBKdA_h.js', null, null, true );
			wp_enqueue_style( 'CCdyn', CWICLY_DIR_URL . 'assets/js/fr/dist/main-qHBKdA_h.css', null, CWICLY_VERSION );

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
			// FRONTEND RENDERING.
		}
		return cc_render( $content, $attributes, $block );
	}
}
