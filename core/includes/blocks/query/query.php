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
function cwicly_query_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_query_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_query_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_query_render_callback( $attributes, $content, $block ) {

	if ( isset( $attributes['infiniteLoad'] ) && $attributes['infiniteLoad'] && ! is_admin() ) {
		wp_enqueue_script( 'cc-infinite', CWICLY_DIR_URL . 'assets/js/infinite-scroll.js', null, CWICLY_VERSION, true );
		wp_enqueue_script( 'cc-query-infinite', CWICLY_DIR_URL . 'assets/js/cc-query-infinite.min.js', null, CWICLY_VERSION, true );
		wp_enqueue_style( 'cc-loaders', CWICLY_DIR_URL . 'assets/css/loaders.min.css', null, CWICLY_VERSION );
	}

	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}

	if ( isset( $attributes['queryType'] ) && 'comments' === $attributes['queryType'] ) {
		wp_enqueue_script( 'comment-reply' );
	} elseif ( isset( $attributes['queryType'] ) && 'products' === $attributes['queryType'] ) {
		wp_enqueue_script( 'CCWoo', CWICLY_DIR_URL . 'assets/js/cc-woocommerce.min.js', null, CWICLY_VERSION, true );
	}

	$conditions = \Cwicly\Helpers::block_conditions_check( $attributes, $block );

	if ( $conditions ) {
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			$action = 'wp_head';
		} else {
			$action = 'wp_footer';
		}
		if ( isset( $attributes['frontendRendering'] ) && $attributes['frontendRendering'] && ! is_admin() ) {
			$query_params = \Cwicly\Query::fr_args( $attributes, $block );

			// FRONTEND RENDERING.
			if ( ! is_admin() ) {
				wp_enqueue_script( 'CCdyn', CWICLY_DIR_URL . 'assets/js/fr/dist/main-qHBKdA_h.js', null, null, true );
				wp_enqueue_style( 'CCdyn', CWICLY_DIR_URL . 'assets/js/fr/dist/main-qHBKdA_h.css', null, CWICLY_VERSION );
			}

			if ( isset( $query_params['postsPerPage'] ) && $query_params['postsPerPage'] ) {
				$attributes['postsPerPage'] = $query_params['postsPerPage'];
			}

			$query_type = '';
			if ( isset( $attributes['queryInherit'] ) && $attributes['queryInherit'] ) {
				$query_type = get_post_type();
				if ( 'product' === $query_type ) {
					$query_type = 'products';
				} elseif ( 'user' === $query_type ) {
					$query_type = 'users';
				} elseif ( 'term' === $query_type ) {
					$query_type = 'terms';
				} else {
					$query_type = 'posts';
				}
			} else {
				$query_type = $attributes['queryType'];
			}

			add_action(
				$action,
				function () use ( $attributes, $query_params, $query_type ) {
					echo '<script id="' . esc_attr( $attributes['id'] ) . '-args" type="application/json">' . wp_json_encode(
						array(
							'queryargs'      => $query_params['query_args'],
							'queryargsnoget' => $query_params['all_query_args_no_get'],
							'querytype'      => $query_type,
							'params'         => $query_params['params'],
							'postsperpage'   => $query_params['postsPerPage'],
						),
						JSON_UNESCAPED_SLASHES
					) . '</script>' . PHP_EOL;
				}
			);
			// FRONTEND RENDERING.
		}
		$args = array();

		if ( isset( $attributes['hideConditionsType'] ) && $attributes['hideConditionsType'] && '&&' === $attributes['hideConditionsType'] ) {
			$is_checking = false;
			if ( isset( $attributes['hideConditions'] ) && $attributes['hideConditions'] ) {
				foreach ( $attributes['hideConditions'] as $condition ) {
					if ( 'queryhasitems' === $condition['condition'] && ! $is_checking ) {
						if ( 'true' === $condition['operator'] ) {
							$args['hasToHaveItems'] = true;
						} else {
							$args['hasToHaveItems'] = false;
						}
						$is_checking = true;
					}
				}
			}
		}
		if ( isset( $attributes['frontendRendering'] ) && $attributes['frontendRendering'] ) {
			$query_template = null;
			if ( isset( $block->parsed_block['innerBlocks'] ) && $block->parsed_block['innerBlocks'] ) {
				foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
					if ( 'cwicly/query-template' === $inner_block['blockName'] ) {
						$query_template = $inner_block;
						break;
					}
				}
			}

			$inner_blocks  = ( new \Cwicly\Block_Template() )->template( $block, true );
			$skeleton_html = ( new Cwicly_Skeleton() )->cc_skeleton_block( $query_template, true, true );
			$skeleton      = array( 'html' => $skeleton_html );

			// FILTER.
			$repeated = false;
			if ( isset( $block->context['rendered'] ) ) {
				$repeated = true;
			}
			if ( ! $repeated ) {
				add_action(
					$action,
					function () use ( $inner_blocks, $attributes ) {
						echo '<script id="' . esc_attr( $attributes['id'] ) . '-qt-json" type="application/json">' . wp_json_encode( $inner_blocks, JSON_UNESCAPED_SLASHES ) . '</script>';
					}
				);
				add_action(
					$action,
					function () use ( $skeleton, $attributes ) {
						echo '<script id="' . esc_attr( $attributes['id'] ) . '-qt-skeleton" type="application/json">' . wp_json_encode( $skeleton, JSON_UNESCAPED_SLASHES ) . '</script>';
					}
				);
			}
			// FILTER.
		}

		return cc_render( $content, $attributes, $block, $args );
	}
}
