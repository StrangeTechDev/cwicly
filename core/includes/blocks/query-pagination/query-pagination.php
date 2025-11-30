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
function cwicly_querypagination_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_query_pagination_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_querypagination_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_query_pagination_render_callback( $attributes, $content, $block ) {
	if ( isset( $attributes['queryPaginationAjax'] ) && $attributes['queryPaginationAjax'] && ! is_admin() ) {
		wp_enqueue_script( 'pjax', CWICLY_DIR_URL . 'assets/js/pjax.js', null, CWICLY_VERSION, true );
		wp_enqueue_script( 'cc-pagination', CWICLY_DIR_URL . 'assets/js/cc-pagination.min.js', array( 'pjax' ), CWICLY_VERSION, true );
	}
	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}

	$conditions = \Cwicly\Helpers::block_conditions_check( $attributes, $block );

	if ( $conditions ) {
		if ( isset( $block->context['frontendRendering'] ) && $block->context['frontendRendering'] ) {
			$inner_blocks          = ( new \Cwicly\Block_Template() )->template( $block, true );
			$skeleton_html_no_anim = ( new Cwicly_Skeleton() )->cc_skeleton_block( $block );
			$skeleton_html         = ( new Cwicly_Skeleton() )->cc_skeleton_block( $block, true );
			$skeleton              = array( 'html' => $skeleton_html );

			// FILTER.
			$repeated = false;
			if ( isset( $block->context['rendered'] ) ) {
				$repeated = true;
			}
			if ( ! $repeated ) {
				if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
					$action = 'wp_head';
				} else {
					$action = 'wp_footer';
				}
				add_action(
					$action,
					function () use ( $inner_blocks, $attributes ) {
						echo '<script id="' . esc_attr( $attributes['id'] ) . '-qpt-json" type="application/json">' . wp_json_encode( $inner_blocks, JSON_UNESCAPED_SLASHES ) . '</script>';
					}
				);
				add_action(
					$action,
					function () use ( $skeleton, $attributes ) {
						echo '<script id="' . esc_attr( $attributes['id'] ) . '-qpt-skeleton" type="application/json">' . wp_json_encode( $skeleton, JSON_UNESCAPED_SLASHES ) . '</script>';
					}
				);
			}
			// FILTER.
		}

		return cc_render( $content, $attributes, $block );
	}
}
