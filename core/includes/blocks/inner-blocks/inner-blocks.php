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
function cwicly_inner_blocks_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_inner_blocks_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_inner_blocks_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_inner_blocks_render_callback( $attributes, $content, $block ) {

	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}

	$conditions = \Cwicly\Helpers::block_conditions_check( $attributes, $block );

	if ( $conditions ) {
		$content = '';
		if ( isset( $block->context['componentInnerBlocks'] ) && $block->context['componentInnerBlocks'] ) {
			$serialized = $block->context['componentInnerBlocks'];
			$blocks     = parse_blocks( $serialized );

			foreach ( $blocks as $inner_block ) {

				$block_context = $block->context;

				$content .= ( new WP_Block(
					$inner_block,
					$block_context
				) )->render( array( 'dynamic' => true ) );
			}
		}
		return cc_render( $content, $attributes, $block );
	}
}
