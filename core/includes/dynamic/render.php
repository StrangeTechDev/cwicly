<?php
/**
 * Render dynamic blocks.
 *
 * @since 1.0
 * @package Cwicly
 */

use enshrined\svgSanitize\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render dynamic blocks.
 *
 * @since 1.0
 * @param string  $content    The content.
 * @param array   $attributes The attributes.
 * @param object  $block      The block.
 * @param array   $args       The args.
 * @param boolean $component  The component.
 */
function cc_render( $content, $attributes, $block, $args = array(), $component = false ) {
	$pattern = array(
		'![start]!',
		'![end]!',
		'<ccdyn>',
		'</ccdyn>',
	);

	if ( isset( $attributes['tooltipActive'] ) && $attributes['tooltipActive'] ) {
		wp_enqueue_script( 'cc-popper', CWICLY_DIR_URL . 'assets/js/popper.js', null, CWICLY_VERSION, false );
		wp_enqueue_script( 'cc-tooltip', CWICLY_DIR_URL . 'assets/js/tooltip.js', array( 'cc-popper' ), CWICLY_VERSION, true );

		if ( isset( $attributes['tooltipAnimation'] ) && $attributes['tooltipAnimation'] ) {
			if ( 'material' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-shift-away', CWICLY_DIR_URL . 'assets/css/tooltip/shift-away.css', array(), CWICLY_VERSION );
				wp_enqueue_style( 'tooltip-backdrop', CWICLY_DIR_URL . 'assets/css/tooltip/backdrop.css', array(), CWICLY_VERSION );
				$div_additions[] = 'data-tooltipanimationfill="true"';
			} else {
				$div_additions[] = 'data-tooltipanimation="' . $attributes['tooltipAnimation'] . '"';
			}
			if ( 'scale' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-scale', CWICLY_DIR_URL . 'assets/css/tooltip/scale.css', array(), CWICLY_VERSION );
			}
			if ( 'scale-subtle' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-scale-subtle', CWICLY_DIR_URL . 'assets/css/tooltip/scale-subtle.css', array(), CWICLY_VERSION );
			}
			if ( 'scale-extreme' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-scale-subtle', CWICLY_DIR_URL . 'assets/css/tooltip/scale-extreme.css', array(), CWICLY_VERSION );
			}
			if ( 'shift-toward' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-shift-toward', CWICLY_DIR_URL . 'assets/css/tooltip/shift-toward.css', array(), CWICLY_VERSION );
			}
			if ( 'shift-toward-subtle' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-shift-toward-subtle', CWICLY_DIR_URL . 'assets/css/tooltip/shift-toward-subtle.css', array(), CWICLY_VERSION );
			}
			if ( 'shift-toward-extreme' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-shift-toward-subtle', CWICLY_DIR_URL . 'assets/css/tooltip/shift-toward-extreme.css', array(), CWICLY_VERSION );
			}
			if ( 'shift-away' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-shift-away', CWICLY_DIR_URL . 'assets/css/tooltip/shift-away.css', array(), CWICLY_VERSION );
			}
			if ( 'shift-away-subtle' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-shift-away-subtle', CWICLY_DIR_URL . 'assets/css/tooltip/shift-away-subtle.css', array(), CWICLY_VERSION );
			}
			if ( 'shift-away-extreme' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-shift-away-subtle', CWICLY_DIR_URL . 'assets/css/tooltip/shift-away-extreme.css', array(), CWICLY_VERSION );
			}
			if ( 'perspective' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-perspective', CWICLY_DIR_URL . 'assets/css/tooltip/perspective.css', array(), CWICLY_VERSION );
			}
			if ( 'perspective-subtle' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-perspective-subtle', CWICLY_DIR_URL . 'assets/css/tooltip/perspective-subtle.css', array(), CWICLY_VERSION );
			}
			if ( 'perspective-extreme' === $attributes['tooltipAnimation'] ) {
				wp_enqueue_style( 'tooltip-perspective-subtle', CWICLY_DIR_URL . 'assets/css/tooltip/perspective-extreme.css', array(), CWICLY_VERSION );
			}
		}
		if ( isset( $attributes['tooltipTheme'] ) && $attributes['tooltipTheme'] ) {
			if ( 'light' === $attributes['tooltipTheme'] ) {
				wp_enqueue_style( 'tooltip-theme-light', CWICLY_DIR_URL . 'assets/css/tooltip/themes/light.css', array(), CWICLY_VERSION );
			}
			if ( 'light-border' === $attributes['tooltipTheme'] ) {
				wp_enqueue_style( 'tooltip-theme-light-border', CWICLY_DIR_URL . 'assets/css/tooltip/themes/light-border.css', array(), CWICLY_VERSION );
			}
			if ( 'material' === $attributes['tooltipTheme'] ) {
				wp_enqueue_style( 'tooltip-theme-material', CWICLY_DIR_URL . 'assets/css/tooltip/themes/material.css', array(), CWICLY_VERSION );
			}
			if ( 'translucent' === $attributes['tooltipTheme'] ) {
				wp_enqueue_style( 'tooltip-theme-translucent', CWICLY_DIR_URL . 'assets/css/tooltip/themes/translucent.css', array(), CWICLY_VERSION );
			}
		}
	}

	if ( isset( $attributes['scrollDirectionActive'] ) && $attributes['scrollDirectionActive'] ) {
		wp_enqueue_script( 'cc-scrolld', CWICLY_DIR_URL . 'assets/js/cc-scrolld.min.js', null, CWICLY_VERSION, true );
	}
	if ( isset( $attributes['linkWrapperActive'] ) && $attributes['linkWrapperActive'] && isset( $attributes['linkWrapperType'] ) && 'action' === $attributes['linkWrapperType'] && isset( $attributes['linkWrapperAction'] ) && 'scrolltotop' === $attributes['linkWrapperAction'] ) {
		wp_enqueue_script( 'cc-backtop', CWICLY_DIR_URL . 'assets/js/cc-backtop.min.js', null, CWICLY_VERSION, true );
	}
	if ( isset( $attributes['dynamic'] ) && 'wordpress' === $attributes['dynamic'] && isset( $attributes['dynamicWordPressType'] ) && 'readtime' === $attributes['dynamicWordPressType'] ) { // phpcs:ignore WordPress.WP.CapitalPDangit
		wp_enqueue_script( 'cc-readtime', CWICLY_DIR_URL . 'assets/js/cc-readtime.min.js', null, CWICLY_VERSION, true );
	}
	if ( isset( $attributes['interactions'] ) && $attributes['interactions'] ) {
		foreach ( $attributes['interactions'] as $key => $value ) {
			if ( preg_match( '/click|dbclick|mousedown|mouseenter|mouseleave|mouseout|mouseover|mouseup|scrollinview|urlHash/', $key ) && count( $value ) > 0 ) {
				wp_enqueue_script( 'cc-interactions', CWICLY_DIR_URL . 'assets/js/cc-interactions.min.js', null, CWICLY_VERSION, true );
				break;
			}
		}
	}

	if ( isset( $attributes['customCSS'] ) && $attributes['customCSS'] ) {
		$scss = get_option( 'cwicly_scss_compiler' );

		$css = $attributes['customCSS'];

		if ( $scss && isset( $attributes['customSCSS'] ) && $attributes['customSCSS'] ) {
			$css = $attributes['customSCSS'];
		}

		$repeated = false;
		if ( isset( $block->context['rendered'] ) ) {
			$repeated = true;
		}

		if ( ! $repeated ) {
			$option      = get_option( 'cwicly_breakpoints_list' );
			$breakpoints = json_decode( $option, true );

			if ( ! $breakpoints ) {
				$breakpoints = array();
			}

			$main_breakpoint = 'lg';
			foreach ( $breakpoints as $key => $breakpoint ) {
				if ( isset( $breakpoint['isMain'] ) && $breakpoint['isMain'] ) {
					$main_breakpoint = $key;
				}
			}

			$is_main_index = array_search( $main_breakpoint, array_keys( $breakpoints ), true );

			$customcss = str_replace( 'blockclass', $attributes['classID'], $css );
			$customcss = str_replace( 'blockid', $attributes['id'], $customcss );
			$customcss = str_replace( array( "\r", "\n" ), '', $customcss );
			$customcss = preg_replace( '!\s+!', ' ', $customcss );

			if ( strpos( $customcss, 'breakpoint-' ) !== false || strpos( $customcss, 'media-breakpoint-' ) !== false ) {
				foreach ( $breakpoints as $key => $breakpoint ) {

					if ( isset( $breakpoint['isMain'] ) && $breakpoint['isMain'] ) {
						continue;
					}

					$type = 'max';

					if ( array_search( $key, array_keys( $breakpoints ), true ) < $is_main_index ) {
						$type = 'min';
					}

					$customcss = str_replace( 'media-breakpoint-' . $key, '' . $type . '-width: ' . $breakpoint['width'] . 'px', $customcss );
					$customcss = str_replace( 'breakpoint-' . $key, $breakpoint['width'] . 'px', $customcss );
				}
			}

			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				add_action(
					'wp_head',
					function () use ( $customcss, $attributes ) {
						echo '<style id="custom-css-' . esc_attr( $attributes['id'] ) . '">' . $customcss . '</style>' . PHP_EOL;
					}
				);
			} else {
				add_action(
					'wp_footer',
					function () use ( $customcss, $attributes ) {
						echo '<style id="custom-css-' . esc_attr( $attributes['id'] ) . '">' . $customcss . '</style>' . PHP_EOL;
					}
				);
			}
		}
	}

	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, false );
	}

	if ( isset( $attributes['animateOnScrollType'] ) && $attributes['animateOnScrollType'] ) {
		wp_enqueue_script( 'cc-aos', CWICLY_DIR_URL . 'assets/js/aos.js', null, CWICLY_VERSION, false );
		wp_enqueue_style( 'cc-aos', CWICLY_DIR_URL . 'assets/css/aos.css', array(), CWICLY_VERSION );
	}

	if ( isset( $attributes['interactions'] ) && $attributes['interactions'] ) {
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			add_action( 'wp_head', array( 'Cwicly\Helpers', 'add_global_interactions_inline_script' ) );
		} else {
			add_action( 'wp_footer', array( 'Cwicly\Helpers', 'add_global_interactions_inline_script' ) );
		}
	}

	if ( isset( $attributes['linkWrapperActive'] ) && $attributes['linkWrapperActive'] ) {
		if ( isset( $attributes['linkWrapperType'] ) && 'action' === $attributes['linkWrapperType'] && isset( $attributes['linkWrapperAction'] ) && 'lightbox' === $attributes['linkWrapperAction'] ) {
			wp_enqueue_style( 'cc-lightbox', CWICLY_DIR_URL . 'assets/css/lightbox.css', array(), CWICLY_VERSION );
			wp_enqueue_script( 'cc-lightbox', CWICLY_DIR_URL . 'assets/js/lightbox.js', null, CWICLY_VERSION, true );
		}
	}

	if ( $component ) {
		return cc_parser( $content, $attributes, $block );
	} elseif ( 'cwicly/query' === $block->parsed_block['blockName'] ) {
		if ( isset( $attributes['frontendRendering'] ) && $attributes['frontendRendering'] ) {
			$skeleton_html_no_anim = ( new Cwicly_Skeleton() )->cc_skeleton_block( $block );
			$content               = cc_parser( $content, $attributes, $block );
			if ( strpos( $content, '![start]!' ) !== false ) {
				$re = '/!\[start\]!<div>(.*?)<\/div>!\[end\]!/s';
			} else {
				$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
			}
			$content = preg_replace( $re, $skeleton_html_no_anim, $content );
		} else {
			$content   = cc_parser( $content, $attributes, $block );
			$value     = \Cwicly\Query::front_prep( $attributes, $block );
			$has_posts = $value['hasPosts'];

			if ( ( ! $has_posts && isset( $args['hasToHaveItems'] ) && $args['hasToHaveItems'] ) || ( $has_posts && isset( $args['hasToHaveItems'] ) && ! $args['hasToHaveItems'] ) ) {
				$content = '';
			} else {
				if ( $value['content'] ) {
					$value = $value['content'];
				} else {
					$value = '';
				}

				if ( strpos( $content, '![start]!' ) !== false ) {
					$re = '/!\[start\]!(.*?)!\[end\]!/s';
				} else {
					$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
				}
				$value   = addcslashes( $value, '$' );
				$content = preg_replace( $re, $value, $content );
			}
		}

		return $content;
	} elseif ( 'cwicly/query-template' === $block->parsed_block['blockName'] ) {
		if ( isset( $block->context['frontendRendering'] ) && $block->context['frontendRendering'] ) {
			return null;
		} else {
			$content = cc_parser( $content, $attributes, $block );
			$value   = \Cwicly\Query::front_maker( $attributes, $block );
			$value   = addcslashes( $value, '$' );
			if ( strpos( $content, '![start]!' ) !== false ) {
				$re = '/!\[start\]!<div>(.*?)<\/div>!\[end\]!/s';
			} else {
				$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
			}
			$content = preg_replace( $re, $value, $content );
		}

		return $content;
	} elseif ( 'cwicly/filter' === $block->parsed_block['blockName'] ) {
		$content = cc_parser( $content, $attributes, $block );
		if ( strpos( $content, '![start]!' ) !== false ) {
			$re = '/!\[start\]!<div>(.*?)<\/div>!\[end\]!/s';
		} else {
			$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
		}
		if ( isset( $attributes['filterType'] ) && ( 'userselection' === $attributes['filterType'] || 'clearselection' === $attributes['filterType'] ) ) {
			$content = preg_replace( $re, '', $content );
		} else {
			$skeleton_html_no_anim = ( new Cwicly_Skeleton() )->cc_skeleton_block( $block );
			$value                 = '<div class="cc-loading-skeleton">' . $skeleton_html_no_anim . '</div>';
			$content               = preg_replace( $re, $value, $content );
		}

		return $content;
	} elseif ( 'cwicly/repeater' === $block->parsed_block['blockName'] ) {
		$content = cc_parser( $content, $attributes, $block );
		$value   = cc_repeater_maker( $attributes, $block );
		if ( strpos( $content, '![start]!' ) !== false ) {
			$re = '/!\[start\]!(.*?)!\[end\]!/s';
		} else {
			$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
		}
		$content = preg_replace( $re, $value, $content );

		return $content;
	} elseif ( 'cwicly/slider' === $block->parsed_block['blockName'] ) {
		$content  = cc_parser( $content, $attributes, $block );
		$content .= cc_slider_maker( $attributes, $block );

		return $content;
	} elseif ( 'cwicly/taxonomyterms' === $block->parsed_block['blockName'] ) {
		$content = cc_parser( $content, $attributes, $block );
		$value   = cc_taxonomyterms_maker( $attributes, $block );
		if ( strpos( $content, '![start]!' ) !== false ) {
			$re = '/!\[start\]!(.*?)!\[end\]!/s';
		} else {
			$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
		}
		$content = preg_replace( $re, $value, $content );

		return $content;
	} elseif ( 'cwicly/video' === $block->parsed_block['blockName'] ) {
		if ( isset( $attributes['videoImageOverlayComp'] ) && $attributes['videoImageOverlayComp'] ) {
			if ( strpos( $attributes['videoImageOverlayComp'], '!ref=' ) !== false ) {
				preg_match( '/!ref=([\w-]+)!/', $attributes['videoImageOverlayComp'], $ref );
				if ( isset( $ref[1] ) ) {
					$ref   = $ref[1];
					$value = \Cwicly\Helpers::get_component_value( $ref, $attributes, $block );

					if ( 'true' === $value ) {
						$content = str_replace( $pattern, '', $content );

					} else {
						if ( strpos( $content, '![start]!' ) !== false ) {
							$re = '/!\[start\]!(.*?)!\[end\]!/s';
						} else {
							$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
						}
						$content = preg_replace( $re, '', $content );
					}
				} else {
					if ( strpos( $content, '![start]!' ) !== false ) {
						$re = '/!\[start\]!(.*?)!\[end\]!/s';
					} else {
						$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
					}
					$content = preg_replace( $re, '', $content );
				}
			}
		} elseif ( isset( $attributes['videoImageOverlay'] ) && $attributes['videoImageOverlay'] ) {
			$content = str_replace( $pattern, '', $content );
		} else {
			if ( strpos( $content, '![start]!' ) !== false ) {
				$re = '/!\[start\]!(.*?)!\[end\]!/s';
			} else {
				$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
			}
			$content = preg_replace( $re, '', $content );
		}

			$content = cc_parser( $content, $attributes, $block );

		return $content;
	} elseif ( 'cwicly/button' === $block->parsed_block['blockName'] || 'cwicly/icon' === $block->parsed_block['blockName'] ) {
		if ( isset( $block->context['isComponent'] ) && $block->context['isComponent'] ) {
			$content = cc_parser( $content, $attributes, $block );
			$pattern = '/!\[componentView=(.*?)\]!/s';
			$match   = '';
			if ( preg_match( $pattern, $content, $matches ) ) {
				$match = $matches[1];
			}
			if ( $match ) {
				$ref = $match;
				if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
					$icon = $block->context['componentProperties'][ $ref ];
					if ( isset( $icon['parent'] ) && isset( $icon['value'] ) ) {
						$link_parent   = \Cwicly\Helpers::get_parent_property( $icon['value'], $block );
						$icon          = array();
						$icon['value'] = $link_parent;
					}

					if ( isset( $icon['value'] ) && 'true' === $icon['value'] ) {
						$re      = '/!\[componentView=' . $match . '\]!(.*?)!\[endComponentView=' . $match . '\]!/s';
						$content = preg_replace( $re, '$1', $content );

						return $content;
					} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) && 'true' === $block->context['componentMetaProperties'][ $ref ]['default'] ) {
						$re      = '/!\[componentView=' . $match . '\]!(.*?)!\[endComponentView=' . $match . '\]!/s';
						$content = preg_replace( $re, '$1', $content );

						return $content;
					} else {
						$re      = '/!\[componentView=' . $match . '\]!(.*?)!\[endComponentView=' . $match . '\]!/s';
						$content = preg_replace( $re, '', $content );

						return $content;
					}
				} else {
					$re      = '/!\[componentView=' . $match . '\]!(.*?)!\[endComponentView=' . $match . '\]!/s';
					$content = preg_replace( $re, '', $content );

					return $content;
				}
			} else {
				return cc_parser( $content, $attributes, $block );
			}
		} else {
			return cc_parser( $content, $attributes, $block );
		}
	} elseif ( isset( $attributes['dynamicContext'] ) && 'woocart' === $attributes['dynamicContext'] ) {
		$skeleton_html_no_anim = ( new Cwicly_Skeleton() )->cc_skeleton_block( $block );
		$content               = cc_parser( $content, $attributes, $block );
		if ( strpos( $content, '![start]!' ) !== false ) {
			$re = '/!\[start\]!<div>(.*?)<\/div>!\[end\]!/s';
		} else {
			$re = '/<ccdyn>(.*?)<\/ccdyn>/s';
		}
		$value   = $skeleton_html_no_anim;
		$content = preg_replace( $re, $value, $content );

		return $content;
	} else {
		return cc_parser( $content, $attributes, $block );
	}
}

/**
 * Parse the content and replace the dynamic content with the actual content
 *
 * @param string $content The content to parse.
 * @param array  $attributes The attributes of the block.
 * @param object $block The block object.
 * @param int    $manual_post_id The post id if forced.
 *
 * @return mixed
 */
function cc_parser( $content, $attributes, $block, $manual_post_id = null ) {
	$regex = '/(?!{}){(?!"|&quot;)(.*?)}|<ccd>(.*?)<\/ccd>/';
	$final = preg_replace_callback(
		$regex,
		function ( $matches ) use ( $attributes, $block, $manual_post_id ) {
			if ( ( isset( $matches[1] ) && $matches[1] ) || ( isset( $matches[2] ) && $matches[2] ) ) {
				$args = strpos( $matches[1] ?: $matches[2], '=' ) > 0 ? explode( '=', $matches[1] ?: $matches[2] ) : array();

				$match = $matches[1] ?: $matches[2];
				if ( ! empty( $args ) ) {
					$match = array_shift( $args );
				}

				$transformed_content = cc_get_dyn( $match, $args, $attributes, $block, $matches, $manual_post_id );

				return $transformed_content;
			}
		},
		$content
	);

	return $final;
}

/**
 * Get the dynamic content
 *
 * @param string $dyn The dynamic content to get.
 * @param array  $args The arguments of the dynamic content.
 * @param array  $attributes The attributes of the block.
 * @param object $block The block object.
 * @param string $match The match of the dynamic content.
 * @param int    $manual_post_id The post id if forced.
 *
 * @return string
 */
function cc_get_dyn( $dyn, $args, $attributes, $block, $match = '', $manual_post_id = null ) {
	if ( CC_WOOCOMMERCE ) {
		global $product;
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( get_the_ID() );
		}

		$old_product = $product;
		if ( isset( $block->context['product'] ) && $block->context['product'] ) {
			$product = $block->context['product'];
		}
	}

	$value   = '';
	$post_id = $manual_post_id ? $manual_post_id : get_the_ID();

	switch ( $dyn ) {

		case 'empty':
			$value = '';

			break;

		case 'readtime':
			$value = '<span class="cc-read-time"></span>';

			break;

		case 'postcontent':
			$value = cc_content_maker( $block );

			break;

		case 'idadd':
		case 'loop-id':
			$repeater_id = '';
			$query_id    = '';
			if ( isset( $block->context['product_index'] ) && $block->context['product_index'] ) {
				$repeater_id = '-p-' . $block->context['product_index'] . '';
			}
			if ( isset( $block->context['taxterms_index'] ) && $block->context['taxterms_index'] ) {
				$repeater_id = '-tt-' . $block->context['taxterms_index'] . '';
			}
			if ( isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
				$repeater_id = '-r-' . $block->context['repeater_row'] . '';
			}
			if ( isset( $block->context['query_index'] ) && $block->context['query_index'] && $block->context['query_index'] - 1 ) {
				$query_id = '-q-' . $block->context['query_index'] . '';
			}
			if ( $query_id && ! $repeater_id ) {
				$value = $query_id;
			}
			if ( ! $query_id && $repeater_id ) {
				$value = $repeater_id;
			}
			if ( $query_id && $repeater_id ) {
				$value = '' . $query_id . $repeater_id . '';
			}
			if ( isset( $block->context['componentIndex'] ) && $block->context['componentIndex'] && ( ! isset( $attributes['componentConnectors']['id'] ) || ! $attributes['componentConnectors']['id'] ) ) {
				$value .= '-c-' . $block->context['componentIndex'] . '';
			}

			break;

		case 'loop-index':
			$repeater_id = '';
			$query_id    = '';
			if ( isset( $block->context['product_index'] ) ) {
				$repeater_id = $block->context['product_index'];
			}
			if ( isset( $block->context['taxterms_index'] ) ) {
				$repeater_id = $block->context['taxterms_index'];
			}
			if ( isset( $block->context['repeater_row'] ) ) {
				$repeater_id = $block->context['repeater_row'];
			}
			if ( isset( $block->context['query_index'] ) ) {
				$query_id = $block->context['query_index'];
			}
			if ( $query_id && ! $repeater_id ) {
				$value = $query_id;
			}
			if ( ! $query_id && $repeater_id ) {
				$value = $repeater_id;
			}
			if ( $query_id && $repeater_id ) {
				$value = '' . $query_id . $repeater_id . '';
			}

			break;

		case 'loop-position':
			$query_id = 0;
			if ( isset( $block->context['query_index'] ) ) {
				$query_id = $block->context['query_index'];
			}
			if ( isset( $block->context['queryCount'] ) && isset( $block->context['queryCurrentPage'] ) && isset( $block->context['queryPostPerPage'] ) ) {
				$value = $block->context['queryPostPerPage'] * ( $block->context['queryCurrentPage'] - 1 ) + $query_id;
			}

			break;

		// ADDITIONAL CLASSES.
		case 'class':
			if ( isset( $attributes['classID'] ) && $attributes['classID'] ) {
				$value = $attributes['classID'];
			}

			break;

		case 'acl':
			$value = Cwicly\Helpers::additional_classes( $attributes );

			break;

		case 'sacl':
			$class_wrapper     = '';
			$class_additionals = Cwicly\Helpers::additional_classes( $attributes );
			if ( $class_additionals ) {
				$class_additionals = explode( ' ', $class_additionals );
				if ( 1 === count( $class_additionals ) ) {
					$class_wrapper = '' . implode( '', $class_additionals ) . '-wrapper';
				} else {
					$class_wrapper = '' . implode( '-wrapper ', $class_additionals ) . '-wrapper';
				}
			}
			if ( $class_wrapper ) {
				$value = $class_wrapper;
			}

			break;

		case 'darkmode_force':
			if ( isset( $args[0] ) && $args[0] ) {
				if ( 'dark' === $args[0] ) {
					$cwicly_darkmode_selectors = get_option( 'cwicly_darkmode_selectors' );
					if ( ! $cwicly_darkmode_selectors ) {
						$cwicly_darkmode_selectors = '.dark';
					}

					$value = \Cwicly\Helpers::extract_classes_from_string( $cwicly_darkmode_selectors );
				} elseif ( 'light' === $args[0] ) {
					$cwicly_lightmode_selectors = get_option( 'cwicly_lightmode_selectors' );
					if ( ! $cwicly_lightmode_selectors ) {
						$cwicly_lightmode_selectors = '.light';
					}

					$value = \Cwicly\Helpers::extract_classes_from_string( $cwicly_lightmode_selectors );
				}
			}
			break;

		// GLOBAL CLASSES.
		case 'gcl':
			$value = Cwicly\Helpers::global_classes( $attributes );

			break;

		// IMAGES.
		case 'iwgi':
			if ( CC_WOOCOMMERCE ) {
				global $product;
				if ( isset( $args[0] ) ) {
					if ( 0 === $args[0] && $product ) {
						$main  = $product->get_image_id();
						$value = wp_get_attachment_image_src( $main, 'full' )[0];
					} elseif ( $product ) {
						$gallery_images = $product->get_gallery_image_ids();
						if ( $gallery_images ) {
							$value = wp_get_attachment_image_src( $gallery_images[ $args[0] - 1 ], 'full' )[0];
						}
					}
				}
			}

			break;

		case 'image':
			if ( isset( $args[0] ) && $args[0] ) {
				$value = wp_get_attachment_url( $args[0] );
			}

			break;

		case 'imagealt':
			if ( isset( $args[0] ) && $args[0] ) {
				if ( 'woogallery' === $args[0] && isset( $block->context['gallery_image_id'] ) && $block->context['gallery_image_id'] ) {
					$value = get_post_meta( $block->context['gallery_image_id'], '_wp_attachment_image_alt', true );
				} elseif ( 'attachment' === $args[0] ) {
					$value = get_post_meta( get_the_ID(), '_wp_attachment_image_alt', true );
				} else {
					$value = get_post_meta( $args[0], '_wp_attachment_image_alt', true );
				}
			}

			break;

		case 'wooimage':
			if ( CC_WOOCOMMERCE && $block->context && isset( $block->context['woocommerce'] ) && $block->context['woocommerce'] ) {
				$value = '' . $block->context['woocommerce'][0] . '" srcset="' . $block->context['woocommerce'][1] . '';
			}

			break;

		case 'woo_gallery_id':
			if ( CC_WOOCOMMERCE && $block->context && isset( $block->context['gallery_image_id'] ) && $block->context['gallery_image_id'] ) {
				$value = $block->context['gallery_image_id'];
			}

			break;

		case 'imagesrc':
			if ( isset( $args[0] ) && $args[0] ) {
				$size = '';
				if ( isset( $args[1] ) && $args[1] ) {
					$size = $args[1];
				}
				$image = array();
				if ( 'woogallery' === $args[0] && isset( $block->context['gallery_image_id'] ) && $block->context['gallery_image_id'] ) {
					$image = wp_get_attachment_image_src( $block->context['gallery_image_id'], $size );
				} elseif ( 'attachment' === $args[0] ) {
					$image = wp_get_attachment_image_src( get_the_ID(), $size );
				} else {
					$image = wp_get_attachment_image_src( $args[0], $size );
				}
				if ( is_array( $image ) && $image[0] ) {
					$value = $image[0];
				}
			}

			break;

		case 'imageset':
			if ( isset( $args[0] ) && $args[0] ) {
				if ( 'woogallery' === $args[0] && isset( $block->context['gallery_image_id'] ) && $block->context['gallery_image_id'] ) {
					$value = esc_attr( wp_get_attachment_image_srcset( $block->context['gallery_image_id'], 'full' ) );
				} elseif ( 'attachment' === $args[0] ) {
					$value = esc_attr( wp_get_attachment_image_srcset( get_the_ID(), 'full' ) );
				} else {
					$meta = wp_get_attachment_metadata( $args[0] );
					if ( isset( $meta['width'] ) && $meta['width'] && isset( $meta['height'] ) && $meta['height'] ) {
						$value = esc_attr( wp_get_attachment_image_srcset( $args[0], 'full' ) );
					}
				}
			}

			break;

		case 'imagesizes':
			if ( isset( $args[0] ) && $args[0] ) {
				if ( 'woogallery' === $args[0] && isset( $block->context['gallery_image_id'] ) && $block->context['gallery_image_id'] ) {
					if ( isset( $args[1] ) && $args[1] ) {
						$value = esc_attr( wp_get_attachment_image_sizes( $block->context['gallery_image_id'], $args[1] ) );
					} else {
						$value = esc_attr( wp_get_attachment_image_sizes( $block->context['gallery_image_id'], 'full' ) );
					}
				} elseif ( 'attachment' === $args[0] ) {
					if ( isset( $args[1] ) && $args[1] ) {
						$value = esc_attr( wp_get_attachment_image_sizes( get_the_ID(), $args[1] ) );
					} else {
						$value = esc_attr( wp_get_attachment_image_sizes( get_the_ID(), 'full' ) );
					}
				} elseif ( isset( $args[1] ) && $args[1] && 'false' !== $args[1] ) {
					$value = esc_attr( wp_get_attachment_image_sizes( $args[0], $args[1] ) );
				} else {
					$meta = wp_get_attachment_metadata( $args[0] );
					if ( isset( $meta['width'] ) && $meta['width'] && isset( $meta['height'] ) && $meta['height'] ) {
						$value = esc_attr( wp_get_attachment_image_sizes( $args[0], 'full' ) );
					}
				}
			}

			break;

		case 'imagewidth':
			if ( isset( $args[0] ) && $args[0] ) {
				if ( 'woogallery' === $args[0] && isset( $block->context['gallery_image_id'] ) && $block->context['gallery_image_id'] ) {
					$image = wp_get_attachment_image_src( $block->context['gallery_image_id'], 'full' );
					if ( $image && isset( $image[1] ) ) {
						$value = $image[1];
					}
				} elseif ( 'attachment' === $args[0] ) {
					$image = wp_get_attachment_image_src( get_the_ID(), 'full' );
					if ( $image && isset( $image[1] ) ) {
						$value = $image[1];
					}
				} else {
					if ( isset( $args[1] ) && $args[1] ) {
						$image = wp_get_attachment_image_src( $args[0], $args[1] );
					} else {
						$image = wp_get_attachment_image_src( $args[0], 'full' );
					}
					if ( $image && isset( $image[1] ) ) {
						$meta = wp_get_attachment_metadata( $args[0] );
						if ( isset( $meta['width'] ) && $meta['width'] ) {
							$value = $image[1];
						}
					}
				}
			}

			break;

		case 'imageheight':
			if ( isset( $args[0] ) && $args[0] ) {
				if ( 'woogallery' === $args[0] && isset( $block->context['gallery_image_id'] ) && $block->context['gallery_image_id'] ) {
					$image = wp_get_attachment_image_src( $block->context['gallery_image_id'], 'full' );
					if ( $image && isset( $image[2] ) ) {
						$value = $image[2];
					}
				} elseif ( 'attachment' === $args[0] ) {
					$image = wp_get_attachment_image_src( get_the_ID(), 'full' );
					if ( $image && isset( $image[2] ) ) {
						$value = $image[2];
					}
				} else {
					if ( isset( $args[1] ) && $args[1] ) {
						$image = wp_get_attachment_image_src( $args[0], $args[1] );
					} else {
						$image = wp_get_attachment_image_src( $args[0], 'full' );
					}
					if ( $image && isset( $image[2] ) ) {
						$meta = wp_get_attachment_metadata( $args[0] );
						if ( isset( $meta['height'] ) && $meta['height'] ) {
							$value = $image[2];
						}
					}
				}
			}

			break;

		case 'cartthumbnail':
			if ( CC_WOOCOMMERCE && isset( $block->context['cart_item'] ) && $block->context['cart_item'] && isset( $block->context['cart_key'] ) && $block->context['cart_key'] ) {
				$_product = apply_filters( 'woocommerce_cart_item_product', $block->context['cart_item']['data'], $block->context['cart_item'], $block->context['cart_key'] );
				$value    = wp_get_attachment_url( $_product->get_image_id() );
			}

			break;

		case 'cartthumbnailsrcset':
			if ( CC_WOOCOMMERCE && isset( $block->context['cart_item'] ) && $block->context['cart_item'] && isset( $block->context['cart_key'] ) && $block->context['cart_key'] ) {
				$_product = apply_filters( 'woocommerce_cart_item_product', $block->context['cart_item']['data'], $block->context['cart_item'], $block->context['cart_key'] );
				$value    = wp_get_attachment_image_srcset( $_product->get_image_id() );
			}

			break;

		case 'woocategorythumbnail':
			if ( CC_WOOCOMMERCE ) {
				$id = false;
				if ( isset( $block->context['taxterms'] ) ) {
					$id = $block->context['taxterms']->term_id;
				} elseif ( isset( $block->context['termQuery'] ) ) {
					$id = $block->context['termQuery']->term_id;
				} elseif ( is_product_category() ) {
					global $wp_query;
					$cat = $wp_query->get_queried_object();
					$id  = $cat->term_id;
				}
				if ( $id ) {
					$thumbnail_id = get_term_meta( $id, 'thumbnail_id', true );
					if ( isset( $args[0] ) && $args[0] ) {
						$size = $args[0];
					} else {
						$size = 'full';
					}
					$image = wp_get_attachment_image_url( $thumbnail_id, $size );
					$value = $image;
				}
			}

			break;

		case 'woocategorythumbnailsrcset':
			if ( CC_WOOCOMMERCE ) {
				$id = false;
				if ( isset( $block->context['taxterms'] ) ) {
					$id = $block->context['taxterms']->term_id;
				} elseif ( isset( $block->context['termQuery'] ) ) {
					$id = $block->context['termQuery']->term_id;
				} elseif ( is_product_category() ) {
					global $wp_query;
					$cat = $wp_query->get_queried_object();
					$id  = $cat->term_id;
				}
				if ( $id ) {
					$thumbnail_id = get_term_meta( $id, 'thumbnail_id', true );
					$srcset       = wp_get_attachment_image_srcset( $thumbnail_id, 'full' );
					$value        = $srcset;
				}
			}

			break;

		case 'woocategorythumbnailsizes':
			if ( CC_WOOCOMMERCE ) {
				$id = false;
				if ( isset( $block->context['taxterms'] ) ) {
					$id = $block->context['taxterms']->term_id;
				} elseif ( isset( $block->context['termQuery'] ) ) {
					$id = $block->context['termQuery']->term_id;
				} elseif ( is_product_category() ) {
					global $wp_query;
					$cat = $wp_query->get_queried_object();
					$id  = $cat->term_id;
				}
				if ( $id ) {
					$thumbnail_id = get_term_meta( $id, 'thumbnail_id', true );
					if ( isset( $args[0] ) && $args[0] ) {
						$size = $args[0];
					} else {
						$size = 'full';
					}
					$sizes = wp_get_attachment_image_sizes( $thumbnail_id, $size );
					$value = $sizes;
				}
			}

			break;

		case 'featuredimage':
			if ( ! get_the_post_thumbnail_url() ) {
				if ( isset( $args[4] ) && $args[4] && $args[4] !== 'false' ) {
					if ( is_numeric( $args[4] ) ) {
						$value = wp_get_attachment_url( $args[4], 'full' );
					} else {
						$value = $args[4];
					}
				} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
					$value = $attributes['dynamicStaticFallbackURL'];
				}
			} elseif ( get_the_post_thumbnail_url() ) {
				if ( isset( $args[0] ) && $args[0] ) {
					$post_thumbnail_id = get_post_thumbnail_id();
					$alt               = '';
					if ( isset( $args[3] ) && $args[3] ) {
						$alty = get_post_meta( $post_thumbnail_id, '_wp_attachment_image_alt', true );
						if ( $alty ) {
							$alt = ' alt="' . $alty . '"';
						}
					}
					if ( isset( $args[1] ) && $args[1] ) {
						$image  = wp_get_attachment_image_src( $post_thumbnail_id, $args[1] );
						$width  = $image[1];
						$height = $image[2];
						$value  = '' . $image[0] . '"' . $alt . ' height="' . $height . '" width="' . $width . '';
					} else {
						$image  = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
						$width  = $image[1];
						$height = $image[2];
						$value  = '' . $image[0] . '"' . $alt . ' " height="' . $height . '" width="' . $width . '';
					}
					if ( isset( $args[2] ) && 'false' === $args[2] ) {
						$value .= '" srcset="' . wp_get_attachment_image_srcset( $post_thumbnail_id, 'full' ) . '';
						if ( isset( $args[1] ) && $args[1] ) {
							$value .= '" sizes="' . wp_get_attachment_image_sizes( $post_thumbnail_id, $args[1] ) . '';
						} else {
							$value .= '" sizes="' . wp_get_attachment_image_sizes( $post_thumbnail_id, 'full' ) . '';
						}
					}
				} else {
					$value = get_the_post_thumbnail_url();
				}
			} else {
				$value = CWICLY_URL . 'assets/images/placeholder.jpg';
			}

			break;

		case 'attachmenturl':
		case 'attachment_url':
			if ( isset( $args[0] ) && $args[0] ) {
				$post_thumbnail_id = $args[0];
				$value             = wp_get_attachment_url( $post_thumbnail_id );
			} else {
				$value = wp_get_attachment_url();
			}

			break;

		case 'authorpicture':
		case 'author_avatar':
			$avatar = '';
			if ( ! get_the_author_meta( 'ID' ) ) {
				$author_id = get_post_field( 'post_author', get_the_ID() );
				$avatar    = get_avatar_data( $author_id );
			} else {
				$avatar = get_avatar_data( get_the_author_meta( 'ID' ) );
			}
			if ( ! $avatar['found_avatar'] && isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
				$value = esc_url( $attributes['dynamicStaticFallbackURL'] ); // Sanitize the URL input.
			} elseif ( $avatar['url'] ) {
				$value = esc_url( $avatar['url'] ); // Sanitize and escape the URL.
			} else {
				$value = esc_url( CWICLY_URL . 'assets/images/placeholder.jpg' ); // Sanitize and escape the URL.
			}

			break;

		case 'userpicture':
		case 'user_avatar':
			$avatar = get_avatar_data( get_current_user_id() );
			if ( ! $avatar['found_avatar'] && isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
				$value = $attributes['dynamicStaticFallbackURL'];
			} elseif ( $avatar['url'] ) {
				$value = $avatar['url'];
			} else {
				$value = CWICLY_URL . 'assets/images/placeholder.jpg';
			}

			break;

		case 'bgfeaturedimage':
			if ( ! get_the_post_thumbnail_url() && isset( $attributes['backgroundDynamicStaticFallbackURL'] ) && $attributes['backgroundDynamicStaticFallbackURL'] ) {
				$value = $attributes['backgroundDynamicStaticFallbackURL'];
			} elseif ( get_the_post_thumbnail_url() ) {
				$value = get_the_post_thumbnail_url();
			} else {
				$value = CWICLY_URL . 'assets/images/placeholder.jpg';
			}

			break;

		case 'bgauthorpicture':
			if ( ! get_avatar_url( get_the_author_meta( 'ID' ) ) && isset( $attributes['backgroundDynamicStaticFallbackURL'] ) && $attributes['backgroundDynamicStaticFallbackURL'] ) {
				$value = $attributes['backgroundDynamicStaticFallbackURL'];
			} elseif ( get_avatar_url( get_the_author_meta( 'ID' ) ) ) {
				$value = get_avatar_url( get_the_author_meta( 'ID' ) );
			} else {
				$value = CWICLY_URL . 'assets/images/placeholder.jpg';
			}

			break;

		case 'bguserpicture':
			if ( ! get_avatar_url( get_current_user_id() ) && isset( $attributes['backgroundDynamicStaticFallbackURL'] ) && $attributes['backgroundDynamicStaticFallbackURL'] ) {
				$value = $attributes['backgroundDynamicStaticFallbackURL'];
			} elseif ( get_avatar_url( wp_get_current_user() ) ) {
				$value = get_avatar_url( get_current_user_id() );
			} else {
				$value = CWICLY_URL . 'assets/images/placeholder.jpg';
			}

			break;

		case 'filterimage':
			break;

		case 'acffield':
		case 'acf_field':
			if ( ! class_exists( 'ACF' ) ) {
				break;
			}
			if ( isset( $args[0] ) && $args[0] ) {

				$options = array();
				if ( isset( $args[4] ) && $args[4] ) {
					$options = explode( '-', $args[4] );
				}

				$fallback = '';
				if ( isset( $args[3] ) && $args[3] ) {
					if ( is_numeric( $args[3] ) && ( ( isset( $options[3] ) && 'image' === $options[3] ) || ( isset( $options[0] ) && isset( $options[1] ) && 'background' === $options[0] && 'image' === $options[1] ) ) ) {
						$fallback = wp_get_attachment_url( $args[3] );
					} else {
						$fallback = $args[3];
					}
				} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
					$fallback = $attributes['dynamicStaticFallbackURL'];
				} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
					$fallback = $attributes['dynamicStaticFallback'];
				} elseif ( isset( $attributes['backgroundDynamicStaticFallbackURL'] ) && $attributes['backgroundDynamicStaticFallbackURL'] && isset( $options[0] ) && isset( $options[1] ) && 'background' === $options[0] && 'image' === $options[1] ) {
					$fallback = $attributes['backgroundDynamicStaticFallbackURL'];
				}

				if ( isset( $args[1] ) && 'false' != $args[1] && isset( $args[2] ) && 'false' != $args[2] ) { // LOCATION + SPECIFIC OBJECT.
					$field_object = get_field_object( sanitize_text_field( $args[0] ), $args[1] );
					if ( isset( $field_object['value'] ) ) {
						$field = $field_object['value'];
					} else {
						$field = $field_object;
					}

					if ( $field ) {
						if ( ! is_object( $field ) && ! is_array( $field ) ) {
							$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
						} elseif ( is_object( $field ) ) {
							$itis  = $field->{$args[2]};
							$value = \Cwicly\ACF::processor( $itis, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
						} elseif ( is_array( $field ) ) {
							$itis  = $field[ $args[2] ];
							$value = \Cwicly\ACF::processor( $itis, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
						}
					} elseif ( $fallback ) {
						$value = $fallback;
					}
				} elseif ( isset( $args[1] ) && 'false' != $args[1] ) { // LOCATION ONLY.
					$field = '';
					if ( 'currentuser' === $args[1] ) {
						$field_object = get_field_object( sanitize_text_field( $args[0] ), 'user_' . get_current_user_id() . '' );
						if ( isset( $field_object['value'] ) ) {
							$field = $field_object['value'];
						} else {
							$field = $field_object;
						}
					} elseif ( 'currentauthor' === $args[1] ) {
						$field_object = get_field_object( sanitize_text_field( $args[0] ), 'user_' . get_the_author_meta( 'ID' ) . '' );
						if ( isset( $field_object['value'] ) ) {
							$field = $field_object['value'];
						} else {
							$field = $field_object;
						}
					} elseif ( 'option' === $args[1] ) {
						$field_object = get_field_object( sanitize_text_field( $args[0] ), 'option' );
						if ( isset( $field_object['value'] ) ) {
							$field = $field_object['value'];
						} else {
							$field = $field_object;
						}
					} elseif ( 'taxterm' === $args[1] && isset( $block->context['taxterms'] ) ) {
						$field_object = get_field_object( sanitize_text_field( $args[0] ), $block->context['taxterms'] );
						if ( isset( $field_object['value'] ) ) {
							$field = $field_object['value'];
						} else {
							$field = $field_object;
						}
					} elseif ( 'termquery' === $args[1] && isset( $block->context['termQuery'] ) ) {
						$field_object = get_field_object( sanitize_text_field( $args[0] ), $block->context['termQuery'] );
						if ( isset( $field_object['value'] ) ) {
							$field = $field_object['value'];
						} else {
							$field = $field_object;
						}
					} elseif ( 'userquery' === $args[1] && isset( $block->context['userQuery'] ) ) {
						$field_object = get_field_object( sanitize_text_field( $args[0] ), $block->context['userQuery'] );
						if ( isset( $field_object['value'] ) ) {
							$field = $field_object['value'];
						} else {
							$field = $field_object;
						}
					} elseif ( 'currenttaxonomytermarchive' === $args[1] && get_queried_object() ) {
						$field_object = get_field_object( sanitize_text_field( $args[0] ), get_queried_object() );
						if ( isset( $field_object['value'] ) ) {
							$field = $field_object['value'];
						} else {
							$field = $field_object;
						}
					} else {
						$field_object = get_field_object( sanitize_text_field( $args[0] ), $args[1] );
						if ( isset( $field_object['value'] ) ) {
							$field = $field_object['value'];
						} else {
							$field = $field_object;
						}
					}

					$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options );
				} elseif ( isset( $args[2] ) && 'false' != $args[2] ) { // NO LOCATION + SPECIFIC OBJECT.
					$field_object = get_field_object( sanitize_text_field( $args[0] ) );
					if ( isset( $field_object['value'] ) ) {
						$field = $field_object['value'];
					} else {
						$field = $field_object;
					}

					if ( $field ) {
						if ( ! is_object( $field ) && ! is_array( $field ) ) {
							$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
						} elseif ( is_object( $field ) ) {
							$itis  = $field->{$args[2]};
							$value = \Cwicly\ACF::processor( $itis, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
						} elseif ( is_array( $field ) ) {
							$itis  = $field[ $args[2] ];
							$value = \Cwicly\ACF::processor( $itis, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
						}
					} elseif ( $fallback ) {
						$value = $fallback;
					}
				} else { // NO LOCATION + NO SPECIFIC OBJECT.
					$field_object = get_field_object( sanitize_text_field( $args[0] ) );
					if ( isset( $field_object['value'] ) ) {
						$field = $field_object['value'];
					} else {
						$field = $field_object;
					}

					$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
				}
			}

			break;

		case 'acf_group_field':
			if ( ! class_exists( 'ACF' ) ) {
				break;
			}
			if ( isset( $args[0] ) && $args[0] && isset( $args[1] ) && $args[1] ) {
				$options = array();
				if ( isset( $args[5] ) && $args[5] ) {
					$options = explode( '-', $args[5] );
				}

				if ( isset( $args[2] ) && 'false' != $args[2] && isset( $args[3] ) && 'false' != $args[3] ) { // LOCATION + SPECIFIC OBJECT.
					$field = \Cwicly\Helpers::get_group_field( $args[0], $args[1], $args[2] );

					$fallback = '';
					if ( isset( $args[4] ) && $args[4] ) {
						$fallback = $args[4];
					} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
						$fallback = $attributes['dynamicStaticFallbackURL'];
					} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
						$fallback = $attributes['dynamicStaticFallback'];
					}

					if ( $field ) {
						if ( ! is_object( $field ) && ! is_array( $field ) ) {
							$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options );
						} elseif ( is_object( $field ) ) {
							$itis  = $field->{$args[3]};
							$value = \Cwicly\ACF::processor( $itis, $fallback, $attributes, $block->parsed_block['blockName'], null, $options );
						} elseif ( is_array( $field ) ) {
							$itis  = $field[ $args[3] ];
							$value = \Cwicly\ACF::processor( $itis, $fallback, $attributes, $block->parsed_block['blockName'], null, $options );
						}
					} elseif ( $fallback ) {
						$value = $fallback;
					}
				} elseif ( isset( $args[2] ) && 'false' != $args[2] ) { // LOCATION ONLY.
					$field = '';
					if ( 'currentuser' === $args[2] ) {
						$field = \Cwicly\Helpers::get_group_field( $args[0], $args[1], 'user_' . get_current_user_id() . '' );
					} elseif ( 'currentauthor' === $args[2] ) {
						$field = \Cwicly\Helpers::get_group_field( $args[0], $args[1], 'user_' . get_the_author_meta( 'ID' ) . '' );
					} elseif ( 'option' === $args[2] ) {
						$field = \Cwicly\Helpers::get_group_field( $args[0], $args[1], 'option' );
					} elseif ( 'taxterm' === $args[2] && isset( $block->context['taxterms'] ) ) {
						$field = \Cwicly\Helpers::get_group_field( $args[0], $args[1], $block->context['taxterms'] );
					} elseif ( 'termquery' === $args[2] && isset( $block->context['termQuery'] ) ) {
						$field = \Cwicly\Helpers::get_group_field( $args[0], $args[1], $block->context['termQuery'] );
					} elseif ( 'userquery' === $args[2] && isset( $block->context['userQuery'] ) ) {
						$field = \Cwicly\Helpers::get_group_field( $args[0], $args[1], $block->context['userQuery'] );
					} else {
						$field = \Cwicly\Helpers::get_group_field( $args[0], $args[1], $args[2] );
					}
					$fallback = '';
					if ( isset( $args[4] ) && $args[4] ) {
						$fallback = $args[4];
					} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
						$fallback = $attributes['dynamicStaticFallbackURL'];
					} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
						$fallback = $attributes['dynamicStaticFallback'];
					}
					$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options );
				} elseif ( isset( $args[3] ) && 'false' != $args[3] ) { // NO LOCATION + SPECIFIC OBJECT.
					$field = \Cwicly\Helpers::get_group_field( $args[0], $args[1] );

					$fallback = '';
					if ( isset( $args[4] ) && $args[4] ) {
						$fallback = $args[4];
					} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
						$fallback = $attributes['dynamicStaticFallbackURL'];
					} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
						$fallback = $attributes['dynamicStaticFallback'];
					}

					if ( $field ) {
						if ( ! is_object( $field ) && ! is_array( $field ) ) {
							$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options );
						} elseif ( is_object( $field ) ) {
							$itis  = $field->{$args[3]};
							$value = \Cwicly\ACF::processor( $itis, $fallback, $attributes, $block->parsed_block['blockName'], null, $options );
						} elseif ( is_array( $field ) ) {
							$itis  = $field[ $args[3] ];
							$value = \Cwicly\ACF::processor( $itis, $fallback, $attributes, $block->parsed_block['blockName'], null, $options );
						}
					} elseif ( $fallback ) {
						$value = $fallback;
					}
				} else { // NO LOCATION + NO SPECIFIC OBJECT.
					$field = \Cwicly\Helpers::get_group_field( sanitize_text_field( $args[0] ), sanitize_text_field( $args[1] ) );

					$fallback = '';
					if ( isset( $args[4] ) && $args[4] ) {
						$fallback = $args[4];
					} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
						$fallback = $attributes['dynamicStaticFallbackURL'];
					} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
						$fallback = $attributes['dynamicStaticFallback'];
					}

					$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options );
				}
			}

			break;

		case 'acfrepeater':
		case 'acf_repeater':
			if ( ! class_exists( 'ACF' ) ) {
				break;
			}
			if ( isset( $args[0] ) && $args[0] ) {
				$options = array();
				if ( isset( $args[3] ) && $args[3] ) {
					$options = explode( '-', $args[3] );
				}

				$fallback = '';
				if ( isset( $args[1] ) && $args[1] ) {
					$fallback = $args[1];
				} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
					$fallback = $attributes['dynamicStaticFallbackURL'];
				} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
					$fallback = $attributes['dynamicStaticFallback'];
				}

				$field_object = get_sub_field_object( sanitize_text_field( $args[0] ) );
				if ( $field_object ) {
					if ( isset( $args[2] ) && 'false' !== $args[2] ) {
						if ( isset( $field_object['value'] ) ) {
							$field = $field_object['value'];
						} else {
							$field = $field_object;
						}

						if ( $field ) {
							if ( ! is_object( $field ) && ! is_array( $field ) ) {
								$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
								break;
							} elseif ( is_object( $field ) ) {
								$field = $field->{$args[2]};
								$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
								break;
							} elseif ( is_array( $field ) ) {
								$field = $field[ $args[2] ];
								$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
								break;
							}
						} elseif ( $fallback ) {
							$value = $fallback;
						}
					} elseif ( isset( $field_object['value'] ) ) {
						$field = $field_object['value'];
					} else {
						$field = $field_object;
					}
					$value = \Cwicly\ACF::processor( $field, $fallback, $attributes, $block->parsed_block['blockName'], null, $options, $field_object );
				} elseif ( $fallback ) {
					$value = $fallback;
				}
			}

			break;

		case 'acfvideo':
			$value = cc_video_final_maker( $attributes );

			break;

		case 'acfvideourl':
			if ( isset( $args[0] ) && $args[0] ) {
				$value = cc_video_url( $attributes, $args[0] );
			}

			break;

		// ATTRIBUTES.
		case 'date':
			$value = date_i18n( 'm/d/Y' );

			break;

		case 'dayweek':
		case 'day_week':
			$value = date_i18n( 'l' );

			break;

		case 'daymonth':
		case 'day_month':
			$value = date_i18n( 'd' );

			break;

		case 'settime':
			$value = date_i18n( 'H:i:s' );

			break;

		case 'postparentid':
		case 'post_parent_id':
			$value = wp_get_post_parent_id( get_the_ID() );

			break;

		case 'posttype':
		case 'post_type':
			$value = get_post_type();

			break;

		case 'postcategories':
			$categories    = get_the_category();
			$category_list = array();
			if ( ! empty( $categories ) ) {
				foreach ( $categories as $category ) {
					$category_list[] = $category->name;
				}
			}
			$value = implode( ' ', $category_list );

			break;

		case 'posttags':
		case 'post_tags':
			$post_tags = get_the_tags();
			$tag_list  = array();
			if ( $post_tags ) {
				foreach ( $post_tags as $tag ) {
					$tag_list[] = $tag->name;
				}
			}
			$value = implode( ' ', $tag_list );

			break;

		case 'shortcode':
			if ( isset( $args[0] ) && $args[0] ) {
				$short = do_shortcode( '[' . $args[0] . ']' );
				if ( '[' . $args[0] . ']' != $short ) {
					$value = $short;
				}
			}

			break;

		case 'userid':
		case 'user_id':
			$current_user = wp_get_current_user();
			$value        = strval( $current_user->ID );

			break;

		// WordPress.
		case 'id':
			$value = $post_id;

			break;

		case 'post_title':
		case 'title':
			if ( $post_id ) {
				$value = get_the_title( $post_id );
			} else {
				$value = get_the_title();
			}

			break;

		case 'postexcerpt':
		case 'post_excerpt':
			if ( ! is_admin() && ! \Cwicly\Helpers::is_rest() ) {
				add_filter( 'get_the_excerpt', array( '\Cwicly\Helpers', 'excerpt_gutenberg' ), 10, 2 );
				remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
				$character_limit = '';
				if ( isset( $args[0] ) && $args[0] ) {
					$character_limit = $args[0];
				}

				$excerpt = get_the_excerpt();

				$excerpt = apply_filters( 'cwicly/excerpt', $excerpt );
				if ( $excerpt ) {
					if ( $character_limit ) {
						$excerpt = wp_strip_all_tags( $excerpt );
						if ( strlen( $excerpt ) <= $character_limit ) {
							$value = $excerpt;
						} else {
							$excerpt = substr( $excerpt, 0, $character_limit );
							$value   = substr( $excerpt, 0, strrpos( $excerpt, ' ' ) );
						}
					} else {
						$value = wp_strip_all_tags( $excerpt );
					}
				} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
					$value = $attributes['dynamicStaticFallback'];
				}
				remove_filter( 'get_the_excerpt', array( '\Cwicly\Helpers', 'excerpt_gutenberg' ) );
				add_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
			}

			break;

		case 'pagetitle':
		case 'page_title':
			$value = esc_html( wp_title( '', false ) );

			break;

		case 'archivedescription':
		case 'archive_description':
			if ( get_the_archive_description() ) {
				$value = wpautop( wp_kses_post( get_the_archive_description() ) );
			} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
				$value = $attributes['dynamicStaticFallback'];
			}

			break;

		case 'sitetitle':
		case 'site_title':
			$value = get_bloginfo( 'name', 'display' );

			break;

		case 'sitetagline':
		case 'site_tagline':
			$value = get_bloginfo( 'description', 'display' );

			break;

		case 'siteoption':
		case 'site_option':
			if ( isset( $args[0] ) && $args[0] ) {
				$value = get_option( $args[0] );
			}

			break;

		case 'authorcustomfield':
		case 'author_custom_field':
			if ( isset( $args[0] ) && $args[0] ) {
				$value = get_user_meta( get_the_author_meta( 'ID' ), $args[0], true );
			}

			break;

		case 'usercustomfield':
		case 'user_custom_field':
			if ( isset( $args[0] ) && $args[0] ) {
				$value = get_user_meta( get_current_user_id(), $args[0], true );
			}

			break;

		case 'customfield':
		case 'custom_field':
			if ( isset( $args[0] ) && $args[0] ) {
				$value = get_post_meta( get_the_ID(), $args[0], true );
			}

			break;

		case 'postcategory':
		case 'post_category':
			$categories = get_the_category();
			if ( ! empty( $categories ) ) {
				if ( isset( $attributes['dynamicCategoryIndex'] ) && $attributes['dynamicCategoryIndex'] ) {
					$value = esc_html( $categories[ $attributes['dynamicCategoryIndex'] - 1 ]->name );
				} else {
					$value = esc_html( $categories[0]->name );
				}
			}

			break;

		case 'tag':
			$tags = get_the_tags();
			if ( ! empty( $tags ) ) {
				if ( isset( $attributes['dynamicTagIndex'] ) && $attributes['dynamicTagIndex'] ) {
					$value = esc_html( $tags[ $attributes['dynamicTagIndex'] - 1 ]->name );
				} else {
					$value = esc_html( $tags[0]->name );
				}
			}

			break;

		case 'archivetitle':
		case 'archive_title':
			if ( is_category() ) {
				$value = single_cat_title( '', false );
			} elseif ( is_tag() ) {
				$value = single_tag_title( '', false );
			} elseif ( is_author() ) {
				$value = get_the_author();
			} elseif ( is_year() ) {
				$value = get_the_date( _x( 'Y', 'yearly archives date format' ) );
			} elseif ( is_month() ) {
				$value = get_the_date( _x( 'F Y', 'monthly archives date format' ) );
			} elseif ( is_day() ) {
				$value = get_the_date( _x( 'F j, Y', 'daily archives date format' ) );
			} elseif ( is_tax( 'post_format' ) ) {
				if ( is_tax( 'post_format', 'post-format-aside' ) ) {
					$value = _x( 'Asides', 'post format archive title' );
				} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
					$value = _x( 'Galleries', 'post format archive title' );
				} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
					$value = _x( 'Images', 'post format archive title' );
				} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
					$value = _x( 'Videos', 'post format archive title' );
				} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
					$value = _x( 'Quotes', 'post format archive title' );
				} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
					$value = _x( 'Links', 'post format archive title' );
				} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
					$value = _x( 'Statuses', 'post format archive title' );
				} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
					$value = _x( 'Audio', 'post format archive title' );
				} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
					$value = _x( 'Chats', 'post format archive title' );
				}
			} elseif ( is_post_type_archive() ) {
				$value = post_type_archive_title( '', false );
			} elseif ( is_tax() ) {
				$queried_object = get_queried_object();
				if ( $queried_object ) {
					$value = single_term_title( '', false );
				}
			}
			break;

		case 'tags':
			$post_tags = get_the_tags();
			if ( ! empty( $post_tags ) ) {
				foreach ( $post_tags as $tag ) {
					$value .= $tag->name . $attributes['dynamicTagSeparator'];
				}
				$value = trim( $value, $attributes['dynamicTagSeparator'] );
			} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
				$value = $attributes['dynamicStaticFallback'];
			}

			break;

		case 'postcomments':
		case 'post_comments':
			$comments = strval( get_comments_number() );
			if ( ! $comments ) {
				$comments = '0';
			}
			if ( isset( $args[0] ) && $args[0] && '0' === $comments ) {
				$value = '' . $args[0] . '';
			} elseif ( isset( $args[1] ) && $args[1] && '1' === $comments ) {
				$value = '1 ' . $args[1] . '';
			} elseif ( isset( $args[2] ) && $args[2] && intval( $comments ) > 1 ) {
				$value = '' . $comments . ' ' . $args[2] . '';
			}

			break;

		case 'username':
			$current_user      = get_current_user_id();
			$current_user_meta = get_userdata( $current_user );
			$demand            = 'display_name';
			if ( 0 != $current_user ) {
				$value = esc_html( $current_user_meta->$demand );
			} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
				$value = $attributes['dynamicStaticFallback'];
			}

			break;

		case 'customcurrentdate':
		case 'custom_current_date':
			$custom_format = '';
			if ( isset( $args[0] ) && $args[0] ) {
				$custom_format = $args[0];
			}
			$value = esc_html( date_i18n( $custom_format, current_time( 'timestamp', 0 ) ) );

			break;

		case 'currentdate':
		case 'current_date':
			$time_format = '';
			$date_format = '';
			if ( isset( $args[0] ) && $args[0] ) {
				if ( 'default' === $args[0] ) {
					$time_format .= 'g:i a';
				} elseif ( '1' === $args[0] ) {
					$time_format .= 'g:i a';
				} elseif ( '2' === $args[0] ) {
					$time_format .= 'g:i A';
				} elseif ( '3' === $args[0] ) {
					$time_format .= 'H:i';
				} elseif ( '4' === $args[0] ) {
					$time_format .= '';
				}
			} else {
				$time_format .= 'g:i a';
			}
			if ( isset( $args[1] ) && $args[1] ) {
				if ( 'default' === $args[1] ) {
					$date_format .= 'F j, Y';
				} elseif ( '1' === $args[1] ) {
					$date_format .= 'F j, Y';
				} elseif ( '2' === $args[1] ) {
					$date_format .= 'Y-m-d';
				} elseif ( '3' === $args[1] ) {
					$date_format .= 'm/d/Y';
				} elseif ( '4' === $args[1] ) {
					$date_format .= 'd/m/Y';
				} elseif ( '5' === $args[1] ) {
					$date_format .= '';
				} elseif ( '6' === $args[1] ) {
					$date_format .= 'd.m.y';
				} elseif ( '7' === $args[1] ) {
					$date_format .= 'd.m.Y';
				}
			} else {
				$date_format .= 'F j, Y';
			}
			$value = esc_html( date_i18n( $date_format . ' ' . $time_format, current_time( 'timestamp', 0 ) ) );

			break;

		case 'userinfo':
			$current_user      = get_current_user_id();
			$current_user_meta = get_userdata( $current_user );
			$demand            = $attributes['dynamicWordPressUserInfo'];
			if ( 0 != $current_user ) {
				if ( '' != $attributes['dynamicWordPressUserInfo'] ) {
					$value = nl2br( $current_user_meta->$demand );
				}
			} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
				$value = $attributes['dynamicStaticFallback'];
			}

			break;

		case 'user_info':
			if ( isset( $args[0] ) && $args[0] ) {
				$current_user      = get_current_user_id();
				$current_user_meta = get_userdata( $current_user );
				$demand            = $args[0];
				if ( 0 != $current_user ) {
					$value = nl2br( $current_user_meta->$demand );
				}
			}

			break;

		case 'authorinfo':
		case 'author_info':
			$demand    = $attributes['dynamicWordPressAuthorInfo'];
			$author_id = get_post_field( 'post_author', $post_id );
			$final     = get_the_author_meta( $demand, $author_id );
			if ( $final ) {
				$value = nl2br( $final );
			} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
				$value = $attributes['dynamicStaticFallback'];
			}

			break;

		case 'authorname':
		case 'author_name':
			$author_id   = get_post_field( 'post_author', $post_id );
			$author_name = '';
			if ( $author_id ) {
				$author_name = get_the_author_meta( 'display_name', $author_id );
			}
			if ( $author_name ) {
				$value = $author_name;
			} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
				$value = $attributes['dynamicStaticFallback'];
			}

			break;

		case 'postdate':
		case 'post_date':
			if ( isset( $args[0] ) && 'published' === $args[0] && isset( $args[1] ) ) {
				if ( 'default' === $args[1] ) {
					$value = get_the_date( 'F j, Y' );
				}
				if ( '1' === $args[1] ) {
					$value = get_the_date( 'F j, Y' );
				}
				if ( '2' === $args[1] ) {
					$value = get_the_date( 'Y-m-d' );
				}
				if ( '3' === $args[1] ) {
					$value = get_the_date( 'm/d/Y' );
				}
				if ( '4' === $args[1] ) {
					$value = get_the_date( 'd/m/Y' );
				}
				if ( '5' === $args[1] ) {
					$value = esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ) . ' ago';
				}
				if ( '6' === $args[1] ) {
					$value = get_the_date( 'd.m.y' );
				}
				if ( '7' === $args[1] ) {
					$value = get_the_date( 'd.m.Y' );
				}
				if ( 'custom' === $args[1] && '' != $args[2] ) {
					$value = get_the_date( $args[2] );
				}
			} elseif ( isset( $args[0] ) && 'modified' === $args[0] && isset( $args[1] ) ) {
				if ( 'default' === $args[1] ) {
					$value = get_the_modified_date( 'F j, Y' );
				}
				if ( '1' === $args[1] ) {
					$value = get_the_modified_date( 'F j, Y' );
				}
				if ( '2' === $args[1] ) {
					$value = get_the_modified_date( 'Y-m-d' );
				}
				if ( '3' === $args[1] ) {
					$value = get_the_modified_date( 'm/d/Y' );
				}
				if ( '4' === $args[1] ) {
					$value = get_the_modified_date( 'd/m/Y' );
				}
				if ( '5' === $args[1] ) {
					$value = esc_html( human_time_diff( get_the_modified_date( 'U' ), current_time( 'timestamp' ) ) ) . ' ago';
				}
				if ( '6' === $args[1] ) {
					$value = get_the_modified_date( 'd.m.y' );
				}
				if ( '7' === $args[1] ) {
					$value = get_the_modified_date( 'd.m.Y' );
				}
				if ( 'custom' === $args[1] && '' != $args[2] ) {
					$value = get_the_modified_date( $args[2] );
				}
			} else {
				$value = get_the_date( 'F j, Y' );
			}

			break;

		case 'time':
		case 'post_time':
			if ( isset( $args[0] ) && 'published' === $args[0] && isset( $args[1] ) ) {
				if ( 'default' === $args[1] ) {
					$value = get_the_time( 'g:i a' );
				}
				if ( '1' === $args[1] ) {
					$value = get_the_time( 'g:i a' );
				}
				if ( '2' === $args[1] ) {
					$value = get_the_time( 'g:i A' );
				}
				if ( '3' === $args[1] ) {
					$value = get_the_time( 'H:i' );
				}
				if ( 'custom' === $args[1] && '' != $args[2] ) {
					$value = get_the_time( $attributes['dynamicWordPressTimeCustom'] );
				}
			} elseif ( isset( $args[0] ) && 'modified' === $args[0] && isset( $args[1] ) ) {
				if ( 'default' === $args[1] ) {
					$value = get_the_modified_time( 'g:i a' );
				}
				if ( '1' === $args[1] ) {
					$value = get_the_modified_time( 'g:i a' );
				}
				if ( '2' === $args[1] ) {
					$value = get_the_modified_time( 'g:i A' );
				}
				if ( '3' === $args[1] ) {
					$value = get_the_modified_time( 'H:i' );
				}
				if ( 'custom' === $args[1] && '' != $args[2] ) {
					$value = get_the_modified_time( $attributes['dynamicWordPressTimeCustom'] );
				}
			} else {
				$value = get_the_time( 'g:i a' );
			}

			break;

		// TAX TERMS.
		case 'taxterms':
			if ( isset( $block->context['taxterms'] ) && $block->context['taxterms'] && isset( $args[0] ) && $args[0] ) {
				$value = $block->context['taxterms']->{$args[0]};
			}

			break;

		// TERM QUERY.
		case 'termquery':
			if ( isset( $block->context['termQuery'] ) && $block->context['termQuery'] && isset( $args[0] ) && $args[0] ) {
				$value = $block->context['termQuery']->{$args[0]};
			}

			break;

		// USER QUERY.
		case 'userquery':
			if ( isset( $block->context['userQuery'] ) && $block->context['userQuery'] && isset( $args[0] ) && $args[0] ) {
				$value = $block->context['userQuery']->{$args[0]};
			}

			break;

		// COMMENT QUERY.
		case 'commentquery':
			if ( isset( $block->context['commentQuery'] ) && $block->context['commentQuery'] && isset( $args[0] ) && $args[0] ) {
				if ( 'avatar' === $args[0] ) {
					if ( ! get_avatar_url( $block->context['commentQuery'] ) && isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
						$value = $attributes['dynamicStaticFallbackURL'];
					} elseif ( get_avatar_url( $block->context['commentQuery'] ) ) {
						$value = get_avatar_url( $block->context['commentQuery'] );
					} else {
						$value = CWICLY_URL . 'assets/images/placeholder.jpg';
					}
				} elseif ( 'comment_date' === $args[0] && isset( $args[1] ) ) {
					if ( 'default' === $args[1] ) {
						$value = get_comment_date( 'F j, Y', $block->context['commentQuery'] );
					} elseif ( '1' === $args[1] ) {
						$value = get_comment_date( 'F j, Y', $block->context['commentQuery'] );
					} elseif ( '2' === $args[1] ) {
						$value = get_comment_date( 'Y-m-d', $block->context['commentQuery'] );
					} elseif ( '3' === $args[1] ) {
						$value = get_comment_date( 'm/d/Y', $block->context['commentQuery'] );
					} elseif ( '4' === $args[1] ) {
						$value = get_comment_date( 'd/m/Y', $block->context['commentQuery'] );
					} elseif ( '5' === $args[1] ) {
						$value = esc_html( human_time_diff( get_comment_date( 'U', $block->context['commentQuery'] ), current_time( 'timestamp' ) ) ) . ' ago';
					} elseif ( '6' === $args[1] ) {
						$value = get_comment_date( 'd.m.y', $block->context['commentQuery'] );
					} elseif ( '7' === $args[1] ) {
						$value = get_comment_date( 'd.m.Y', $block->context['commentQuery'] );
					} elseif ( 'custom' === $args[1] && isset( $args[2] ) ) {
						$value = get_comment_date( $args[2], $block->context['commentQuery'] );
					}
				} elseif ( 'comment_time' === $args[0] && isset( $args[1] ) ) {
					if ( 'default' === $args[1] ) {
						$value = get_comment_date( 'g:i a', $block->context['commentQuery'] );
					} elseif ( '1' === $args[1] ) {
						$value = get_comment_date( 'g:i a', $block->context['commentQuery'] );
					} elseif ( '2' === $args[1] ) {
						$value = get_comment_date( 'g:i A', $block->context['commentQuery'] );
					} elseif ( '3' === $args[1] ) {
						$value = get_comment_date( 'H:i', $block->context['commentQuery'] );
					} elseif ( '4' === $args[1] ) {
						$value = human_time_diff( get_comment_date( 'U', $block->context['commentQuery'] ), current_time( 'timestamp' ) );
					}
					if ( 'custom' === $args[1] && isset( $args[2] ) && '' != $args[2] ) {
						$value = get_comment_date( $args[2], $block->context['commentQuery'] );
					}
				} else {
					$value = $block->context['commentQuery']->{$args[0]};
				}
			}

			break;

		case 'commentqueryauthorarchive':
			if ( isset( $block->context['commentQuery']->user_id ) && $block->context['commentQuery']->user_id ) {
				$value = get_author_posts_url( $block->context['commentQuery']->user_id );
			}

			break;

		case 'commenturl':
			if ( isset( $block->context['commentQuery']->comment_ID ) && $block->context['commentQuery']->comment_ID ) {
				$value = get_comment_link( $block->context['commentQuery']->comment_ID );
			}

			break;

		case 'formcomment':
			$final  = '';
			$final .= '" action="' . site_url( '/wp-comments-post.php' ) . '';
			$final .= '" method="post';
			$value  = $final;

			break;

		case 'currentcommenter':
			if ( isset( $args[0] ) && $args[0] ) {
				$value = wp_get_current_commenter()[ $args[0] ];
			}

			break;

		case 'commentreplyurl':
			if ( isset( $block->context['commentQuery']->comment_ID ) && $block->context['commentQuery']->comment_ID ) {
				if ( get_option( 'page_comments' ) ) {
					$permalink = str_replace( '#comment-' . $block->context['commentQuery']->comment_ID, '', get_comment_link( $block->context['commentQuery'] ) );
				} else {
					$permalink = get_permalink( $post_id );
				}
				$value = esc_url(
					add_query_arg(
						array(
							'replytocom'      => $block->context['commentQuery']->comment_ID,
							'unapproved'      => false,
							'moderation-hash' => false,
						),
						$permalink
					)
				) . '#respond';
			}

			break;

		case 'editcommenturl':
			if ( isset( $block->context['commentQuery']->comment_ID ) && $block->context['commentQuery']->comment_ID ) {
				$value = get_edit_comment_link( $block->context['commentQuery']->comment_ID );
			}

			break;

		case 'commentcookiescheck':
			$commenter = wp_get_current_commenter();
			$consent   = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';
			if ( $consent ) {
				$value = '" ' . $consent . '';
			}

			break;
		// COMMENT QUERY.

		// WOOCOMMERCE.
		case 'watc':
			global $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product && 'variable' === $product->get_type() ) {
				$value = 'variations_form cart';
			} elseif ( isset( $product ) && $product && 'grouped' === $product->get_type() ) {
				$value = 'cart grouped_form';
			}

			break;

		case 'woocheckouturl':
			if ( CC_WOOCOMMERCE ) {
				$value = esc_url( wc_get_checkout_url() );
			}

			break;

		case 'woocarturl':
			if ( CC_WOOCOMMERCE ) {
				$value = esc_url( wc_get_cart_url() );
			}

			break;

		case 'carttotal':
			if ( CC_WOOCOMMERCE && WC()->cart ) {
				$value = WC()->cart->get_cart_total();
			}

			break;

		case 'cartitemname':
			if ( CC_WOOCOMMERCE && isset( $block->context['cart_item'] ) && $block->context['cart_item'] && isset( $block->context['cart_key'] ) && $block->context['cart_key'] ) {
				$_product = apply_filters( 'woocommerce_cart_item_product', $block->context['cart_item']['data'], $block->context['cart_item'], $block->context['cart_key'] );
				$name     = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $block->context['cart_item'], $block->context['cart_key'] );
				$value    = $name;
			}

			break;

		case 'cartitemquantity':
			if ( isset( $block->context['cart_item'] ) && $block->context['cart_item'] ) {
				$value = $block->context['cart_item']['quantity'];
			}

			break;

		case 'cartitemprice':
			if ( CC_WOOCOMMERCE && isset( $block->context['cart_item'] ) && $block->context['cart_item'] && isset( $block->context['cart_key'] ) && $block->context['cart_key'] ) {
				$_product      = apply_filters( 'woocommerce_cart_item_product', $block->context['cart_item']['data'], $block->context['cart_item'], $block->context['cart_key'] );
				$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $block->context['cart_item'], $block->context['cart_key'] );
				$value         = $product_price;
			}

			break;

		case 'cartsubtotal':
			if ( CC_WOOCOMMERCE && isset( $block->context['cart_item'] ) && $block->context['cart_item'] && isset( $block->context['cart_key'] ) && $block->context['cart_key'] ) {
				$_product = apply_filters( 'woocommerce_cart_item_product', $block->context['cart_item']['data'], $block->context['cart_item'], $block->context['cart_key'] );
				$subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $block->context['cart_item']['quantity'] ), $block->context['cart_item'], $block->context['cart_key'] );
				$value    = $subtotal;
			}

			break;

		case 'cartitemscount':
			if ( CC_WOOCOMMERCE && WC()->cart ) {
				$value = WC()->cart->get_cart_contents_count();
			}

			break;

		case 'price':
			if ( CC_WOOCOMMERCE ) {
				global $product;
				$old_product = $product;
				if ( isset( $block->context['product'] ) && $block->context['product'] ) {
					$product = $block->context['product'];
				}
				$value   = Cwicly\WooCommerce::price_maker( $product, $args );
				$product = $old_product;
			}

			break;

		case 'saleprice':
			if ( CC_WOOCOMMERCE ) {
				global $product;
				$old_product = $product;
				if ( isset( $block->context['product'] ) && $block->context['product'] ) {
					$product = $block->context['product'];
				}
				if ( isset( $product ) && $product ) {
					if ( isset( $args[0] ) && $args[0] ) {
						$price = $product->get_sale_price();
						$value = Cwicly\WooCommerce::dynamic_price( $price, $args[0] );
					} else {
						$value = $product->get_sale_price();
					}
				}
				$product = $old_product;
			}

			break;

		case 'regularprice':
			if ( CC_WOOCOMMERCE ) {
				global $product;
				$old_product = $product;
				if ( isset( $block->context['product'] ) && $block->context['product'] ) {
					$product = $block->context['product'];
				}
				$value   = Cwicly\WooCommerce::price_maker( $product, $args, 'regular' );
				$product = $old_product;
			}

			break;

		case 'currency':
			if ( CC_WOOCOMMERCE ) {
				$value = get_woocommerce_currency();
			}

			break;

		case 'currencysymbol':
			if ( CC_WOOCOMMERCE && function_exists( 'get_woocommerce_currency_symbol' ) ) {
				$value = html_entity_decode( get_woocommerce_currency_symbol() );

				global $product;
				$old_product = $product;
				if ( isset( $block->context['product'] ) && $block->context['product'] ) {
					$product = $block->context['product'];
				}
				if ( isset( $product ) && $product && isset( $args[0] ) && $args[0] ) {
					if ( $product->get_type() && 'variable' === $product->get_type() ) {
						if ( 'variationnmin' === $dyn ) {
							$price = $product->get_variation_price();
							$value = Cwicly\WooCommerce::dynamic_price( $price, $args[0] );
						} elseif ( 'variationmax' === $dyn ) {
							$price = $product->get_variation_price( 'max' );
							$value = Cwicly\WooCommerce::dynamic_price( $price, $args[0] );
						} elseif ( 'variationregnmin' === $dyn ) {
							$price = $product->get_variation_regular_price();
							$value = Cwicly\WooCommerce::dynamic_price( $price, $args[0] );
						} elseif ( 'variationregnmax' === $dyn ) {
							$price = $product->get_variation_regular_price( 'max' );
							$value = Cwicly\WooCommerce::dynamic_price( $price, $args[0] );
						} elseif ( 'variationsalemin' === $dyn ) {
							$price = $product->get_variation_sale_price();
							$value = Cwicly\WooCommerce::dynamic_price( $price, $args[0] );
						} elseif ( 'variationsalemax' === $dyn ) {
							$price = $product->get_variation_sale_price( 'max' );
							$value = Cwicly\WooCommerce::dynamic_price( $price, $args[0] );
						} elseif ( isset( $block->context['wooVariable'] ) && $block->context['wooVariable'] && isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
							if ( $block->context['wooVariable']['label'] ) {
								$value = $block->context['wooVariable']['label'];
							}
						}
					}
					$product = $old_product;
				}
			}

			break;

		case 'weight':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = '<span class="weight">' . $product->get_weight() . '</span>';
			}
			$product = $old_product;

			break;

		case 'height':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = $product->get_height();
			}
			$product = $old_product;

			break;

		case 'width':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = $product->get_width();
			}
			$product = $old_product;

			break;

		case 'length':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = $product->get_length();
			}
			$product = $old_product;

			break;

		case 'quantity':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = '<span class="availability">' . $product->get_stock_quantity() . '</span>';
			}
			$product = $old_product;

			break;

		case 'description':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = '<span class="description">' . wpautop( $product->get_description() ) . '</span>';
			}
			$product = $old_product;

			break;

		case 'shortdescription':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = wpautop( $product->get_short_description() );
			}
			$product = $old_product;

			break;

		case 'maxpurchasequantity':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = $product->get_max_purchase_quantity();
			}
			$product = $old_product;

			break;

		case 'minpurchasequantity':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = $product->get_min_purchase_quantity();
			}
			$product = $old_product;

			break;

		case 'salefrom':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$date = $args[0];
				if ( isset( $date ) && $date ) {
					if ( 'default' === $date ) {
						$value = gmdate( 'F j, Y', strtotime( $product->get_date_on_sale_from() ) );
					} elseif ( '1' === $date ) {
						$value = gmdate( 'F j, Y', strtotime( $product->get_date_on_sale_from() ) );
					} elseif ( '2' === $date ) {
						$value = gmdate( 'Y-m-d', strtotime( $product->get_date_on_sale_from() ) );
					} elseif ( '3' === $date ) {
						$value = gmdate( 'm/d/Y', strtotime( $product->get_date_on_sale_from() ) );
					} elseif ( '4' === $date ) {
						$value = gmdate( 'd/m/Y', strtotime( $product->get_date_on_sale_from() ) );
					} elseif ( '5' === $date ) {
						$value = esc_html( human_time_diff( strtotime( $product->get_date_on_sale_from() ), current_time( 'timestamp' ) ) ) . ' ago';
					} elseif ( '6' === $date ) {
						$value = gmdate( 'd.m.y', strtotime( $product->get_date_on_sale_from() ) );
					} elseif ( '7' === $date ) {
						$value = gmdate( 'd.m.Y', strtotime( $product->get_date_on_sale_from() ) );
					} elseif ( 'custom' === $date && '' != $args[1] ) {
						$value = gmdate( $args[1], strtotime( $product->get_date_on_sale_from() ) );
					}
				} else {
					$value = $product->get_date_on_sale_from();
				}
			}
			$product = $old_product;

			break;

		case 'saletill':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$date = $args[0];
				if ( isset( $date ) && $date ) {
					if ( 'default' === $date ) {
						$value = gmdate( 'F j, Y', strtotime( $product->get_date_on_sale_to() ) );
					} elseif ( '1' === $date ) {
						$value = gmdate( 'F j, Y', strtotime( $product->get_date_on_sale_to() ) );
					} elseif ( '2' === $date ) {
						$value = gmdate( 'Y-m-d', strtotime( $product->get_date_on_sale_to() ) );
					} elseif ( '3' === $date ) {
						$value = gmdate( 'm/d/Y', strtotime( $product->get_date_on_sale_to() ) );
					} elseif ( '4' === $date ) {
						$value = gmdate( 'd/m/Y', strtotime( $product->get_date_on_sale_to() ) );
					} elseif ( '5' === $date ) {
						$value = esc_html( human_time_diff( strtotime( $product->get_date_on_sale_to() ), current_time( 'timestamp' ) ) ) . ' ago';
					} elseif ( '6' === $date ) {
						$value = gmdate( 'd.m.y', strtotime( $product->get_date_on_sale_to() ) );
					} elseif ( '7' === $date ) {
						$value = gmdate( 'd.m.Y', strtotime( $product->get_date_on_sale_to() ) );
					} elseif ( 'custom' === $date && '' != $args[1] ) {
						$value = gmdate( $args[1], strtotime( $product->get_date_on_sale_to() ) );
					}
				} else {
					$value = $product->get_date_on_sale_to();
				}
			}
			$product = $old_product;

			break;

		case 'sku':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = $product->get_sku();
			}
			$product = $old_product;

			break;

		case 'ratingcount':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = $product->get_rating_count();
			}
			$product = $old_product;

			break;

		case 'reviewcount':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = $product->get_review_count();
			}
			$product = $old_product;

			break;

		case 'averagerating':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = $product->get_average_rating();
			}
			$product = $old_product;

			break;

		case 'salepercentage':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$value = Cwicly\WooCommerce::percentage_calculator( $product );
			}
			$product = $old_product;

			break;

		case 'totalsold':
			global $product;
			$old_product = $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( isset( $product ) && $product ) {
				$total_sold = get_post_meta( $product->id, 'total_sales', true );
				if ( $total_sold ) {
					$value = $total_sold;
				} else {
					$value = 0;
				}
			}
			$product = $old_product;

			break;

		case 'woo_item_min':
			if ( CC_WOOCOMMERCE ) {
				global $product;
				$old_product = $product;
				if ( isset( $block->context['product'] ) && $block->context['product'] ) {
					$product = $block->context['product'];
				}
				if ( is_bool( $product ) ) {
					break;
				} else {
					$value = $product->get_min_purchase_quantity();
				}
			}

			break;

		case 'woo_item_max':
			if ( CC_WOOCOMMERCE ) {
				global $product;
				$old_product = $product;
				if ( isset( $block->context['product'] ) && $block->context['product'] ) {
					$product = $block->context['product'];
				}
				if ( is_bool( $product ) ) {
					break;
				} else {
					$value = -1 === $product->get_max_purchase_quantity() ? 999 : $product->get_max_purchase_quantity();
				}
			}

			break;
		// WOOCOMMERCE.

		// TOOLTIP.
		case 'tooltipacf':
			if ( ! class_exists( 'ACF' ) ) {
				break;
			}
			$field = get_field( $attributes['tooltipACFField'] );
			if ( $field ) {
				$value = wp_filter_post_kses( htmlspecialchars( $field ) );
			}

			break;

		// LINKS.
		case 'taxonomytermsurl':
			if ( isset( $block->context['taxterms'] ) && $block->context['taxterms'] && $block->context['taxterms']->term_id ) {
				$value = get_term_link( $block->context['taxterms']->term_id );
			}

			break;

		case 'taxonomyqueryurl':
			if ( isset( $block->context['termQuery'] ) && $block->context['termQuery'] && $block->context['termQuery']->term_id ) {
				$value = get_term_link( $block->context['termQuery']->term_id );
			}

			break;

		case 'userqueryurl':
			if ( isset( $block->context['userQuery'] ) && $block->context['userQuery'] && $block->context['userQuery']->ID ) {
				$value = get_author_posts_url( $block->context['userQuery']->ID );
			}

			break;

		case 'previouspost':
			$taxonomy       = 'category';
			$in_same_term   = false;
			$excluded_terms = '';
			if ( isset( $args[0] ) && $args[0] ) {
				$taxonomy = $args[0];
			}
			if ( isset( $args[1] ) && 'true' === $args[1] ) {
				$in_same_term = true;
			}
			if ( isset( $args[2] ) && $args[2] ) {
				$excluded_terms = $args[2];
			}
			$value = get_permalink( get_adjacent_post( $in_same_term, $excluded_terms, true, $taxonomy ) );

			break;

		case 'nextpost':
			$taxonomy       = 'category';
			$in_same_term   = false;
			$excluded_terms = '';
			if ( isset( $args[0] ) && $args[0] ) {
				$taxonomy = $args[0];
			}
			if ( isset( $args[1] ) && 'true' === $args[1] ) {
				$in_same_term = true;
			}
			if ( isset( $args[2] ) && $args[2] ) {
				$excluded_terms = $args[2];
			}
			$value = get_permalink( get_adjacent_post( $in_same_term, $excluded_terms, false, $taxonomy ) );

			break;

		case 'pageurl':
		case 'page_url':
			if ( isset( $args[0] ) && $args[0] && 'false' !== $args[0] ) {
				$id = intval( $args[0] );
				if ( isset( $args[1] ) && $args[1] && 'encoded' === $args[1] ) {
					$value = rawurlencode( htmlspecialchars_decode( get_permalink( $id ) ) );
				} else {
					$value = get_permalink( $id );
				}
			} elseif ( isset( $args[1] ) && $args[1] && 'encoded' === $args[1] ) {
				$value = rawurlencode( htmlspecialchars_decode( get_permalink() ) );
			} else {
				$value = get_permalink();
			}

			break;

		case 'attachment_url':
			if ( isset( $args[0] ) && $args[0] ) {
				$id    = intval( $args[0] );
				$value = wp_get_attachment_url( $id );
			} else {
				$value = wp_get_attachment_url();
			}

			break;

		case 'pageobject':
			$id   = '';
			$type = '';
			$kind = '';
			if ( isset( $args[0] ) && $args[0] ) {
				$id = intval( $args[0] );
			}
			if ( isset( $args[1] ) && $args[1] ) {
				$type = $args[1];
			}
			if ( isset( $args[2] ) && $args[2] ) {
				$kind = $args[2];
			}
			$link    = '';
			$current = false;
			switch ( $type ) {
				case 'post':
				case 'page':
					$current_page_id = get_the_ID();
					if ( $current_page_id == $id ) {
						$current = true;
					}

					$link = get_permalink( $id );

					break;

				case 'category':
				case 'tag':
				case 'taxonomy':
					$current_term_id = get_queried_object_id();
					if ( $current_term_id == $id ) {
						$current = true;
					}

					// if invalid term, return empty string.
					if ( is_wp_error( get_term_link( $id ) ) ) {
						$link = '';
						break;
					}

					$link = get_term_link( $id );

					break;

				case 'post_format':
					$current_post_format = get_post_format();
					if ( $current_post_format == $id ) {
						$current = true;
					}

					$link = get_post_format_link( $id );

					break;

				case 'attachment':
					$current_media_id = get_queried_object_id();
					if ( $current_media_id == $id ) {
						$current = true;
					}

					if ( get_option( 'wp_attachment_pages_enabled' ) ) {
						$link = get_attachment_link( $id );

					} else {
						$link = wp_get_attachment_url( $id );
					}

					break;
			}

			if ( ! $link ) {
				switch ( $kind ) {
					case 'taxonomy':
						$current_term_id = get_queried_object_id();
						if ( $current_term_id == $id ) {
							$current = true;
						}

						// if invalid term, return empty string.
						if ( is_wp_error( get_term_link( $id ) ) ) {
							$link = '';
							break;
						}

						$link = get_term_link( $id );

						break;

					case 'post-type':
						$current_page_id = get_the_ID();
						if ( $current_page_id == $id ) {
							$current = true;
						}

						$link = get_permalink( $id );

						break;
				}
			}

			if ( $link ) {
				if ( $current && ( 'cwicly/navlink' === $block->parsed_block['blockName'] || 'cwicly/navdropdown' === $block->parsed_block['blockName'] ) ) {
					$value = "$link\" aria-current=\"page";
				} else {
					$value = $link;
				}
			}

			break;

		case 'currentpageclass':
			if ( isset( $args[0] ) && $args[0] && 'static' === $args[0] && isset( $args[1] ) && $args[1] ) {
				$is_current = \Cwicly\Helpers::is_current_url( $args[1] );
				if ( $is_current ) {
					$value = 'current';
				}

				break;
			}

			if ( isset( $args[0] ) && $args[0] && 'dynamic' === $args[0] && isset( $args[1] ) && $args[1] ) {
				if ( 'homeurl' === $args[1] && is_home() ) {
					$value = 'current';
				} elseif ( 'siteurl' === $args[1] && is_front_page() ) {
					$value = 'current';
				} elseif ( 'posturl' === $args[1] && is_single() ) {
					if ( isset( $block->context['postId'] ) && $block->context['postId'] === get_queried_object_id() ) {
						$value = 'current';
					} elseif ( ! isset( $block->context['postId'] ) ) {
						$value = 'current';
					}
				}

				break;
			}

			$id   = '';
			$type = '';
			$kind = '';
			if ( isset( $args[0] ) && $args[0] ) {
				$id = intval( $args[0] );
			}
			if ( isset( $args[1] ) && $args[1] ) {
				$type = $args[1];
			}
			if ( isset( $args[2] ) && $args[2] ) {
				$kind = $args[2];
			}

			switch ( $type ) {
				case 'post':
				case 'page':
					$current_page_id = get_the_ID();

					if ( $current_page_id == $id ) {
						$value = 'current';
					}

					break;

				case 'category':
				case 'tag':
				case 'taxonomy':
					$current_term_id = get_queried_object_id();

					if ( $current_term_id == $id ) {
						$value = 'current';
					}

					break;

				case 'post_format':
					$current_post_format = get_post_format();

					if ( $current_post_format == $id ) {
						$value = 'current';
					}

					break;

				case 'attachment':
					$current_media_id = get_queried_object_id();

					if ( $current_media_id == $id ) {
						$value = 'current';
					}

					break;
			}

			if ( ! $value ) {
				switch ( $kind ) {
					case 'taxonomy':
						$current_term_id = get_queried_object_id();

						if ( $current_term_id == $id ) {
							$value = 'current';
						}

						break;

					case 'post-type':
						$current_page_id = get_the_ID();

						if ( $current_page_id == $id ) {
							$value = 'current';
						}

						break;
				}
			}

			break;

		case 'archiveurl':
			$post_type = get_post_type();
			$value     = get_post_type_archive_link( $post_type );

			break;

		case 'postarchiveurl':
			$post_type = get_post_type();
			$value     = get_post_type_archive_link( $post_type );

			break;

		case 'homeurl':
			$value = get_home_url();
			if ( is_home() && ( 'cwicly/navlink' === $block->parsed_block['blockName'] || 'cwicly/navdropdown' === $block->parsed_block['blockName'] ) ) {
				$value = "$value\" aria-current=\"page";
			}

			break;

		case 'siteurl':
			$value = get_site_url();
			if ( is_front_page() && ( 'cwicly/navlink' === $block->parsed_block['blockName'] || 'cwicly/navdropdown' === $block->parsed_block['blockName'] ) ) {
				$value = "$value\" aria-current=\"page";
			}

			break;

		case 'directlogout':
			if ( isset( $args[0] ) && $args[0] && 'specific' != $args[0] ) {
				$url = '';
				if ( 'homeurl' === $args[0] ) {
					$url = get_home_url();
				} elseif ( 'siteurl' === $args[0] ) {
					$url = get_site_url();
				} elseif ( 'currentpage' === $args[0] ) {
					$url = get_permalink();
				}
				$value = wp_logout_url( $url );
			} elseif ( isset( $args[0] ) && $args[0] && 'specific' === $args[0] && isset( $args[1] ) && $args[1] ) {
				$url   = $args[1];
				$value = wp_logout_url( $url );
			} else {
				$value = wp_logout_url();
			}

			break;

		case 'loginurl':
			if ( isset( $args[0] ) && $args[0] && 'specific' != $args[0] ) {
				$url = '';
				if ( 'homeurl' === $args[0] ) {
					$url = get_home_url();
				} elseif ( 'siteurl' === $args[0] ) {
					$url = get_site_url();
				} elseif ( 'currentpage' === $args[0] ) {
					$url = get_permalink();
				}
				$value = wp_login_url( $url );
			} elseif ( isset( $args[0] ) && $args[0] && 'specific' === $args[0] && isset( $args[1] ) && $args[1] ) {
				$url   = $args[1];
				$value = wp_login_url( $url );
			} else {
				$value = wp_login_url();
			}

			break;

		case 'authorurl':
			$author_id  = get_post_field( 'post_author', $post_id );
			$author_url = '';
			if ( $author_id ) {
				$author_url = get_author_posts_url( $author_id );
			}
			if ( $author_url ) {
				$value = $author_url;
			}
			break;

		case 'commentsurl':
			$value = '' . get_permalink() . '#respond';

			break;

		case 'removecartitem':
		case 'removecartitemajax':
			if ( isset( $block->context['cart_key'] ) && $block->context['cart_key'] ) {
				$value = esc_url( wc_get_cart_remove_url( $block->context['cart_key'] ) );
			}

			break;

		case 'cartitemkey':
			if ( isset( $block->context['cart_key'] ) && $block->context['cart_key'] ) {
				$value = $block->context['cart_key'];
			}

			break;

		case 'filter':
			if ( isset( $args[0] ) && $args[0] ) {
				if ( isset( $block->context['filterInfo'] ) && $block->context['filterInfo'] && $block->context['filterInfo'][ $args[0] ] ) {
					$value = $block->context['filterInfo'][ $args[0] ];
				}
			}

			break;

		case 'filterlink':
			$final = '';
			if ( isset( $block->context['filterInfo']['plainQueryID'] ) && $block->context['filterInfo']['plainQueryID'] ) {
				$final .= '' . $block->context['filterInfo']['plainQueryID'] . '"';
			} else {
				$final .= '0"';
			}
			if ( isset( $block->context['filterInfo']['plainTarget'] ) && $block->context['filterInfo']['plainTarget'] ) {
				$final .= ' data-filter-target="' . $block->context['filterInfo']['plainTarget'] . '"';
			}
			if ( isset( $block->context['filterInfo']['value'] ) && $block->context['filterInfo']['value'] ) {
				$final .= ' data-filter-value="' . $block->context['filterInfo']['value'] . '"';
			}
			if ( isset( $block->context['filterInfo']['filter_id'] ) && $block->context['filterInfo']['filter_id'] ) {
				$final .= ' data-filter-id="' . $block->context['filterInfo']['filter_id'] . '"';
			}
			if ( isset( $block->context['filterType'] ) && $block->context['filterType'] ) {
				$final .= 'data-filter-type="' . $block->context['filterType'] . '"';
			}
			if ( isset( $block->context['filterInfo'] ) && $block->context['filterInfo'] ) {
				$child_targets = new stdClass();
				if ( isset( $block->context['filterInfo']['children'] ) && $block->context['filterInfo']['children'] ) {
					foreach ( $block->context['filterInfo']['children'] as $index => $child ) {
						if ( isset( $child['target'] ) && $child['target'] && isset( $child['value'] ) && $child['value'] ) {
							$child_targets->{$child['target']} = $child['value'];
						}
					}
				}
				if ( $child_targets ) {
					$final .= ' data-filter-child-target=\'' . wp_json_encode( $child_targets ) . '\'';
				}
			}
			$value = $final;

			break;

		// FILTER.
		case 'urlparam':
			$value = '';
			if ( isset( $args[0] ) && $args[0] && isset( $args[1] ) && $args[1] ) {
				if ( isset( $_GET[ $args[0] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
					$url = sanitize_text_field( wp_unslash( $_GET[ $args[0] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
					if ( $url && $url === $args[1] ) {
						$value = 'selected';
					}
				}
			}

			break;

		case 'filterstatus':
			$value = '';
			if ( isset( $block->context['filterType'] ) && $block->context['filterType'] && 'single' === $block->context['filterType'] ) {
				if ( isset( $_GET[ $block->context['filterInfo']['plainTarget'] ] ) ) {
					$target = sanitize_text_field( wp_unslash( $_GET[ $block->context['filterInfo']['plainTarget'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
					if ( $target == $block->context['filterInfo']['value'] ) {
						$value = 'selected';
					}
				}
			}
			if ( isset( $block->context['filterType'] ) && $block->context['filterType'] && 'multiple' === $block->context['filterType'] ) {
				if ( isset( $_GET[ $block->context['filterInfo']['plainTarget'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
					$get_value = sanitize_text_field( wp_unslash( $_GET[ $block->context['filterInfo']['plainTarget'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
					$get_array = explode( ',', $get_value );
					if ( in_array( $block->context['filterInfo']['value'], $get_array ) ) {
						$value = 'selected';
					}
				}
			}

			break;
		// FILTER.

		// SWATCH.
		case 'swatchclass':
			if ( CC_WOOCOMMERCE && isset( $args[0] ) && $args[0] ) {
				if ( ! isset( $block->context['repeater'] ) ) {
					if ( $product && 'variable' === $product->get_type() ) {
						if ( 'select' === $attributes['swatchType'] ) {
							$value = ' ' . $args[0] . '';
						}
					}
				} elseif ( isset( $block->context['wooVariable'] ) && 'select' === $block->context['wooVariable']['type'] ) {
					$value = ' ' . $args[0] . '';
				}
			}

			break;

		case 'swatchid':
			$final = '';
			if ( isset( $block->context['queryId'] ) && isset( $block->context['query_index'] ) && $block->context['query_index'] ) {
				$final .= '" data-variation_query_id="' . $block->context['queryId'] . '-' . $block->context['query_index'] . '';
			}
			if ( isset( $block->context['wooVariable'] ) && 'select' === $block->context['wooVariable']['type'] ) {
				$final .= '" data-variation_id="' . $block->context['wooVariable']['slug'] . '';
			}
			if ( isset( $attributes['swatchSlug'] ) && $attributes['swatchSlug'] && isset( $attributes['swatchType'] ) && 'select' === $attributes['swatchType'] ) {
				$final .= '" data-variation_id="' . $attributes['swatchSlug'] . '';
			}
			if ( $final ) {
				$value = $final;
			}

			break;

		case 'htmltag':
			if ( ! isset( $block->context['repeater'] ) ) {
				if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
					if ( 'select' === $attributes['swatchType'] ) {
						$value = 'select';
					} else {
						$value = 'div';
					}
				}
			} elseif ( CC_WOOCOMMERCE && isset( $block->context['wooVariable'] ) && 'select' === $block->context['wooVariable']['type'] ) {
				$value = 'select';
			} else {
				$value = 'div';
			}

			if ( ! $value ) {
				$value = 'div';
			}

			break;

		case 'swatch':
			if ( isset( $args[0] ) && $args[0] ) {
				$value = cc_swatch_maker( $attributes, $block, $args[0], '', '' );
			}

			break;
		// SWATCH.

		// WOO ATTRIBUTES.
		case 'woocouponnonce':
			if ( ! Cwicly\Helpers::is_rest() && ! is_admin() && CC_WOOCOMMERCE ) {
				$value = wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' );
			}
			break;

		case 'wooaddtocartajax':
			if ( CC_WOOCOMMERCE ) {
				$value = get_the_ID();
			}
			break;

		case 'wooaddtocart':
			if ( ! is_admin() && CC_WOOCOMMERCE ) {
				global $product;
				$old_product = $product;
				if ( isset( $block->context['product'] ) && $block->context['product'] ) {
					$product = $block->context['product'];
				}

				$pre_rel = '';
				if ( isset( $attributes['linkWrapperRel'] ) && $attributes['linkWrapperRel'] ) {
					$pre_rel = ' ' . $attributes['linkWrapperRel'] . '';
				}
				$rel    = '' . $pre_rel . '';
				$target = '';
				if ( isset( $attributes['linkWrapperNewTab'] ) && $attributes['linkWrapperNewTab'] ) {
					$target = ' target="_blank"';
				} else {
					$target = ' target="_self"';
				}

				if ( isset( $attributes['linkWrapperActionExtra'] ) && $attributes['linkWrapperActionExtra'] && isset( $attributes['linkWrapperActionExtra']['redirectSimple'] ) ) {
					if ( ! is_bool( $product ) ) {
						$value = '" href="' . $product->get_permalink() . '" ' . $target . ' rel="' . $rel . '" data-cc-redirect="';
					}
				} elseif ( isset( $attributes['linkWrapperActionExtra'] ) && $attributes['linkWrapperActionExtra'] && isset( $attributes['linkWrapperActionExtra']['redirectVariable'] ) ) {
					if ( ! is_bool( $product ) ) {
						$value = '" href="' . $product->get_permalink() . '" ' . $target . ' rel="' . $rel . '" data-cc-redirect="';
					}
				} elseif ( isset( $attributes['linkWrapperActionExtra'] ) && $attributes['linkWrapperActionExtra'] && isset( $attributes['linkWrapperActionExtra']['redirectExternal'] ) ) {
					if ( ! is_bool( $product ) ) {
						$value = '" href="' . $product->get_permalink() . '" ' . $target . ' rel="' . $rel . '" data-cc-redirect="';
					}
				} elseif ( isset( $attributes['linkWrapperActionExtra'] ) && $attributes['linkWrapperActionExtra'] && isset( $attributes['linkWrapperActionExtra']['redirectGrouped'] ) ) {
					if ( ! is_bool( $product ) ) {
						$value = '" href="' . $product->get_permalink() . '" ' . $target . ' rel="' . $rel . '" data-cc-redirect="';
					}
				} elseif ( ! is_bool( $product ) && $product && $product->is_in_stock() && $product->is_purchasable() && $product->is_type( 'simple' ) ) {
					break;
				} elseif ( ! is_bool( $product ) && $product->is_type( 'grouped' ) ) {
					break;
				} elseif ( ! is_bool( $product ) && $product->is_type( 'external' ) ) {
					$value = '" href="' . $product->get_product_url() . '" ' . $target . ' rel="' . $rel . '" data-cc-redirect="';
				} else {
					$value = '" disabled="';
				}
			}

			break;

		case 'formwoocoupon':
			$final  = '';
			$final .= '" action="' . esc_url( wc_get_cart_url() ) . '';
			$final .= '" method="post';
			$value  = $final;

			break;

		case 'formaddtocart':
			$final = '';
			if ( $product ) {
				if ( 'variable' === $product->get_type() ) {
					if ( class_exists( 'WC_Frontend_Scripts' ) ) {
						$frontend_scripts = new \WC_Frontend_Scripts();
						$frontend_scripts::load_scripts();
					}
					wp_enqueue_script( 'wc-add-to-cart-variation' );
				}
				if ( $product->is_type( 'simple' ) ) {
					$final .= '" action="' . esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ) . '';
				} elseif ( $product->is_type( 'variable' ) ) {
					if ( isset( $block->context['queryId'] ) && isset( $block->context['query_index'] ) && $block->context['query_index'] ) {
						$final .= '" data-variation_query_id="' . $block->context['queryId'] . '-' . $block->context['query_index'] . '';
					}
					$get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
					$available_variations = $get_variations ? $product->get_available_variations() : false;
					$variations_json      = wp_json_encode( $available_variations );
					$variations_attr      = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
					$final               .= '" action="' . esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ) . '" data-product_id="' . absint( $product->get_id() ) . '" data-product_variations="' . $variations_attr . '';
				} elseif ( $product->is_type( 'grouped' ) ) {
					$final .= '" action="' . esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ) . '';
				} elseif ( $product->is_type( 'external' ) ) {
					$final .= '" action="' . esc_url( $product->add_to_cart_url() ) . '';
				}

				if ( $product->is_type( 'external' ) ) {
					$final .= '" method="get';
				} else {
					$final .= '" method="' . $attributes['formMethod'] . '';
				}

				if ( ! $product->is_type( 'external' ) ) {
					if ( isset( $attributes['formEnctype'] ) && $attributes['formEnctype'] ) {
						$final .= '" enctype="multipart/form-data';
					}
				}
			}
			$value = $final;

			break;

		case 'producttype':
			if ( $product && $product->get_type() ) {
				$value = $product->get_type();
			}

			break;

		case 'forminneraddtocart':
			if ( $product && 'variable' === $product->get_type() ) {
				$get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
				$available_variations = $get_variations ? $product->get_available_variations() : false;
				$attributes           = $product->get_variation_attributes();
				ob_start();
				?>
				<table class="variations" cellspacing="0">
					<tbody>
					<?php foreach ( $attributes as $attribute_name => $options ) : ?>
							<tr>
								<td class="value">
									<?php
									wc_dropdown_variation_attribute_options(
										array(
											'options'   => $options,
											'attribute' => $attribute_name,
											'product'   => $product,
										)
									);
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div class="woocommerce-variation single_variation"></div>
				<div class="single_variation_wrap">
					<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
					<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
					<input type="hidden" name="variation_id" class="variation_id" value="0" />
				</div>
					<?php
					$value = ob_get_clean();
			}

			break;

		case 'variationqueryid':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				if ( isset( $block->context['queryId'] ) && isset( $block->context['query_index'] ) && $block->context['query_index'] ) {
					$value = '" data-variation_query_id="' . $block->context['queryId'] . '-' . $block->context['query_index'] . '';
				}
			}

			break;

		case 'variationprice':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				if ( isset( $args[0] ) && $args[0] ) {
					$value = '" data-variation_price="' . $args[0] . '';
				} else {
					$value = '" data-variation_price="';
				}
			}

			break;

		case 'variationsaleprice':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				if ( isset( $args[0] ) && $args[0] ) {
					$value = '" data-variation_saleprice="' . $args[0] . '';
				} else {
					$value = '" data-variation_saleprice="';
				}
			}

			break;

		case 'variationsalepercentage':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				if ( isset( $args[0] ) && $args[0] ) {
					$value = '" data-variation_salepercentage="' . $args[0] . '';
				} else {
					$value = '" data-variation_salepercentage="';
				}
			}

			break;

		case 'variationregularprice':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				if ( isset( $args[0] ) && $args[0] ) {
					$value = '" data-variation_regularprice="' . $args[0] . '';
				} else {
					$value = '" data-variation_regularprice="';
				}
			}

			break;

		case 'variationquanity':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				$value = '" data-variation_stock="';
			}

			break;

		case 'variationheight':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				$value = '" data-variation_height="';
			}

			break;

		case 'variationwidth':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				$value = '" data-variation_width="';
			}

			break;

		case 'variationlength':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				$value = '" data-variation_length="';
			}

			break;

		case 'variationdescription':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				$value = '" data-variation_description="';
			}

			break;

		case 'variationminpurchasequantity':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				$value = '" data-variation_minpurchasequantity="';
			}

			break;

		case 'variationmaxpurchasequantity':
			if ( CC_WOOCOMMERCE && $product && 'variable' === $product->get_type() ) {
				$value = '" data-variation_maxpurchasequantity="';
			}

			break;

		case 'variationlabel':
			if ( isset( $block->context['wooVariable'] ) && $block->context['wooVariable'] && $block->context['wooVariable']['label'] ) {
				$value = $block->context['wooVariable']['label'];
			}

			break;

		case 'variationtype':
			if ( isset( $block->context['wooVariable'] ) && $block->context['wooVariable'] && $block->context['wooVariable']['type'] ) {
				$value = $block->context['wooVariable']['label'];
			}

			break;

		case 'variationslug':
			if ( isset( $block->context['wooVariable'] ) && $block->context['wooVariable'] && $block->context['wooVariable']['slug'] ) {
				$value = $block->context['wooVariable']['label'];
			}

			break;

		case 'hide':
			if ( isset( $args[0] ) && $args[0] ) {
				if ( 'is_var_is_group' === $args[0] ) {
					if ( $product && ( 'variable' === $product->get_type() ) ) {
						$final  = '';
						$final .= '" hidden="true" cc-hidden="true';
						$value  = $final;
					}
				}
			}

			break;
		// WOO ATTRIBUTES.

		// SVG.
		case 'svg':
			if ( isset( $args[0] ) && $args[0] ) {
				$svg_path = get_attached_file( $args[0] );
				if ( file_exists( $svg_path ) ) {
					$svg_file = file_get_contents( $svg_path );
					if ( ! empty( $svg_file ) ) {
						$svg = \Cwicly\Helpers::get_svg_content( $svg_file );
						if ( isset( $svg['svg'] ) && $svg['svg'] ) {
							$value = $svg['svg'];
						}
					}
				}
			}

			break;

		case 'viewbox':
			if ( isset( $args[0] ) && $args[0] ) {
				if ( 'inline' === $args[0] ) {
					if ( isset( $attributes['uniqueID'] ) && $attributes['uniqueID'] && isset( $attributes['inlineSvg'] ) && $attributes['inlineSvg'] ) {
						$svg_attributes = \Cwicly\Helpers::get_svg_attributes( $attributes['inlineSvg'] );
						if ( $svg_attributes ) {
							$value = $svg_attributes;
						}
					}
				} else {
					$svg_path = get_attached_file( $args[0] );
					if ( file_exists( $svg_path ) ) {
						$svg_file = file_get_contents( $svg_path );
						if ( ! empty( $svg_file ) ) {
							$svg_attributes = \Cwicly\Helpers::get_svg_attributes( $svg_file );
							if ( $svg_attributes ) {
								$value = $svg_attributes;
							}
						}
					}
				}
			}

			break;

		case 'svginline':
			if ( isset( $attributes['uniqueID'] ) && $attributes['uniqueID'] && isset( $attributes['inlineSvg'] ) && $attributes['inlineSvg'] ) {
				$svg = \Cwicly\Helpers::get_svg_content( $attributes['inlineSvg'] );
				if ( isset( $svg['svg'] ) && $svg['svg'] ) {
					$value = $svg['svg'];
				}
			}
			break;
		// SVG.

		// GALLERY.
		case 'acfgallery':
			if ( ! class_exists( 'ACF' ) ) {
				break;
			}
			if ( isset( $args[0] ) && $args[0] ) {
				$content = '';
				// OVERLAY.
				$gallery_overlay = '';
				if ( isset( $attributes['galleryOverlay'] ) && $attributes['galleryOverlay'] ) {
					$gallery_overlay = $attributes['galleryOverlay'];
				}
				// OVERLAY.

				$image_sizes = '';
				if ( isset( $attributes['galleryThumbnailSize'] ) && $attributes['galleryThumbnailSize'] ) {
					if ( strpos( $attributes['galleryThumbnailSize'], '!ref=' ) !== false ) {
						preg_match( '/!ref=([\w-]+)!/', $attributes['galleryThumbnailSize'], $ref );
						if ( isset( $ref[1] ) ) {
							$value     = null;
							$ref       = $ref[1];
							$parameter = isset( $block->context['componentProperties'][ $ref ] ) ? $block->context['componentProperties'][ $ref ] : '';
							if ( isset( $parameter['parent'] ) && isset( $parameter['value'] ) ) {
								$parameter_parent   = \Cwicly\Helpers::get_parent_property( $parameter['value'], $block );
								$parameter          = array();
								$parameter['value'] = $parameter_parent;
							}
							if ( isset( $parameter['value'] ) && $parameter['value'] && isset( $parameter['value']['maker'] ) && $parameter['value']['maker'] ) {
								if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] && ( ( ! isset( $parameter['value']['dynamic'] ) || ! $parameter['value']['dynamic'] ) ) ) {
									$value = \Cwicly\Helpers::get_component_option_value_from_id( $parameter['value']['maker'], $ref, $block );
								} else {
									$value = cc_parser( $parameter['value']['maker'], $attributes, $block );
								}
							} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
								if ( isset( $block->context['componentMetaProperties'][ $ref ]['type'] ) && 'options' === $block->context['componentMetaProperties'][ $ref ]['type'] && ( ! isset( $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) || ! $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) ) {
									$value = \Cwicly\Helpers::get_component_option_value_from_id( $block->context['componentMetaProperties'][ $ref ]['default'], $ref, $block );
								} else {
									$value = $block->context['componentMetaProperties'][ $ref ]['default'];
								}
							}

							if ( $value ) {
								$image_sizes = $value;
							}
						}
					} else {
						$image_sizes = $attributes['galleryThumbnailSize'];
					}
				}

				// IMAGE.

				$gallery_type = '';
				if ( isset( $attributes['galleryType'] ) && $attributes['galleryType'] ) {
					if ( strpos( $attributes['galleryType'], '!ref=' ) !== false ) {
						preg_match( '/!ref=([\w-]+)!/', $attributes['galleryType'], $ref );
						if ( isset( $ref[1] ) ) {
							$ref          = $ref[1];
							$value        = \Cwicly\Helpers::get_component_value_with_options( $ref, $attributes, $block );
							$gallery_type = $value;
						}
					} else {
						$gallery_type = $attributes['galleryType'];
					}
				}

				$image_props = '';
				if ( $gallery_type && 'masonry' === $gallery_type ) {
					$image_props = 'style="width: 100%; height: 100%;"';
				} elseif ( isset( $attributes['galleryOverlay'] ) && 'gallery-sunrise' === $attributes['galleryOverlay'] ) {
					$image_props = 'style="object-fit: cover;"';
				} else {
					$image_props = 'style="width: 100%; height: 100%; object-fit: cover;"';
				}

				$lazy_load = '';
				if ( isset( $attributes['galleryLazyLoadComp'] ) && $attributes['galleryLazyLoadComp'] ) {
					if ( strpos( $attributes['galleryLazyLoadComp'], '!ref=' ) !== false ) {
						preg_match( '/!ref=([\w-]+)!/', $attributes['galleryLazyLoadComp'], $ref );
						if ( isset( $ref[1] ) ) {
							$ref   = $ref[1];
							$value = \Cwicly\Helpers::get_component_value( $ref, $attributes, $block );
							if ( $value && 'true' === $value ) {
								$lazy_load = ' loading="lazy"';
							}
						}
					}
				} elseif ( isset( $attributes['galleryLazyLoad'] ) && $attributes['galleryLazyLoad'] ) {
					$lazy_load = ' loading="lazy"';
				}
				// IMAGE.

					$title = false;
				if ( isset( $attributes['galleryTitleControlComp'] ) && $attributes['galleryTitleControlComp'] ) {
					if ( strpos( $attributes['galleryTitleControlComp'], '!ref=' ) !== false ) {
						preg_match( '/!ref=([\w-]+)!/', $attributes['galleryTitleControlComp'], $ref );
						if ( isset( $ref[1] ) ) {
							$ref   = $ref[1];
							$value = \Cwicly\Helpers::get_component_value( $ref, $attributes, $block );
							if ( $value && 'true' === $value ) {
								$title = true;
							}
						}
					}
				} elseif ( isset( $attributes['galleryTitleControl'] ) && $attributes['galleryTitleControl'] ) {
					$title = true;
				}

					$description = false;
				if ( isset( $attributes['galleryDescriptionControlComp'] ) && $attributes['galleryDescriptionControlComp'] ) {
					if ( strpos( $attributes['galleryDescriptionControlComp'], '!ref=' ) !== false ) {
						preg_match( '/!ref=([\w-]+)!/', $attributes['galleryDescriptionControlComp'], $ref );
						if ( isset( $ref[1] ) ) {
							$ref   = $ref[1];
							$value = \Cwicly\Helpers::get_component_value( $ref, $attributes, $block );
							if ( $value && 'true' === $value ) {
								$description = true;
							}
						}
					}
				} elseif ( isset( $attributes['galleryDescriptionControl'] ) && $attributes['galleryDescriptionControl'] ) {
					$description = true;
				}

				if ( isset( $args[1] ) && $args[1] && $args[1] !== 'false' ) {
					$content .= '<div class="cc-gallery">';
				}

				$gallery = array();

				if ( isset( $args[2] ) && $args[2] ) {
					if ( 'currentpost' === $args[2] ) {
						$gallery = get_field( sanitize_text_field( $args[0] ), get_the_ID() );
					} elseif ( 'currentuser' === $args[2] ) {
						$gallery = get_field( sanitize_text_field( $args[0] ), 'user_' . get_current_user_id() . '' );
					} elseif ( 'currentauthor' === $args[2] ) {
						$gallery = get_field( sanitize_text_field( $args[0] ), 'user_' . get_the_author_meta( 'ID' ) . '' );
					} elseif ( 'option' === $args[2] ) {
						$gallery = get_field( sanitize_text_field( $args[0] ), 'option' );
					} elseif ( 'taxterm' === $args[2] && isset( $block->context['taxterms'] ) ) {
						$gallery = get_field( sanitize_text_field( $args[0] ), $block->context['taxterms'] );
					} elseif ( 'termquery' === $args[2] && isset( $block->context['termQuery'] ) ) {
						$gallery = get_field( sanitize_text_field( $args[0] ), $block->context['termQuery'] );
					} elseif ( 'userquery' === $args[2] && isset( $block->context['userQuery'] ) ) {
						$gallery = get_field( sanitize_text_field( $args[0] ), $block->context['userQuery'] );
					} elseif ( 'currenttaxonomytermarchive' === $args[2] && get_queried_object() ) {
						$gallery = get_field( sanitize_text_field( $args[0] ), get_queried_object() );
					} else {
						$gallery = get_field( sanitize_text_field( $args[0] ), $args[2] );
					}
				} else {
					$gallery = get_field( $args[0] );
				}
				if ( isset( $gallery ) && $gallery ) {
					foreach ( $gallery as $index => $valuer ) {
						$url    = '';
						$srcset = '';
						$sizes  = '';

						if ( is_string( $valuer ) ) {
							$valuer = array( 'url' => $valuer );
						} elseif ( is_numeric( $valuer ) ) {
							$valuer = array( 'ID' => $valuer );

							$valuer['url']    = wp_get_attachment_url( $valuer['ID'] );
							$valuer['alt']    = get_post_meta( $valuer['ID'], '_wp_attachment_image_alt', true );
							$valuer['height'] = get_post_meta( $valuer['ID'], '_wp_attachment_metadata', true )['height'];
							$valuer['width']  = get_post_meta( $valuer['ID'], '_wp_attachment_metadata', true )['width'];
							$valuer['title']  = get_the_title( $valuer['ID'] );
						}

						if ( $image_sizes && $valuer['sizes'] && isset( $valuer['sizes'][ $image_sizes ] ) && $valuer['sizes'][ $image_sizes ] ) {
							$url   = $valuer['sizes'][ $image_sizes ];
							$sizes = 'sizes="' . wp_get_attachment_image_sizes( $valuer['ID'], $url ) . '"';
						} elseif ( isset( $valuer['url'] ) && $valuer['url'] ) {
							$url = $valuer['url'];
							if ( isset( $valuer['ID'] ) && $valuer['ID'] ) {
								$srcset = 'srcset="' . wp_get_attachment_image_srcset( $valuer['ID'], 'full' ) . '"';
								$sizes  = 'sizes="' . wp_get_attachment_image_sizes( $valuer['ID'], 'full' ) . '"';
							}
						}
						$alt    = '';
						$height = '';
						$width  = '';

						if ( isset( $valuer['ID'] ) && $valuer['ID'] ) {
							$alt = 'alt="' . get_post_meta( $valuer['ID'], '_wp_attachment_image_alt', true ) . '"';
						}
						if ( isset( $valuer['height'] ) && $valuer['height'] ) {
							$height = 'height="' . $valuer['height'] . '"';
						}
						if ( isset( $valuer['width'] ) && $valuer['width'] ) {
							$width = 'width="' . $valuer['width'] . '"';
						}

						$urled    = false;
						$content .= '<figure class="cc-gallery-card ' . $gallery_overlay . ' gallery-1" data-ccgalleryname="gallery-1">';
						$content .= '<div class="cc-gallery-lightbox" style="overflow: hidden; width: 100%; position: relative;">';
						if ( isset( $attributes['linkWrapperActive'] ) && $attributes['linkWrapperActive'] && isset( $attributes['linkWrapperType'] ) && 'lightbox' === $attributes['linkWrapperType'] ) {
							$content .= '<a class="cc-lightbox cc-gallery-lightbox" href="' . $valuer['url'] . '">';
							$urled    = true;
						}
						$content .= '<img ' . $image_props . $alt . $srcset . $sizes . $height . $width . ' src=' . $url . $lazy_load . '></img>';
						$content .= '<figcaption>';
						if ( ( $title ) || ( $description ) ) {
							if ( $title && isset( $valuer['title'] ) && $valuer['title'] ) {
								$content .= '<p class="cc-gallery-title">' . $valuer['title'] . '</p>';
							}
							if ( $description && isset( $valuer['caption'] ) && $valuer['caption'] ) {
								$content .= '<p class="cc-gallery-description">' . $valuer['caption'] . '</p>';
							}
						}
						$content .= '</figcaption>';
						if ( $urled ) {
							$content .= '</a>';
						}
						if ( 'active' === $urled ) {
							$content .= '</div>';
						}
						$content .= '</div>';
						$content .= '</figure>';
					}
				}
				if ( isset( $args[1] ) && $args[1] !== 'false' ) {
					$content .= '</div>';
				}
				$value = $content;
			}

			break;

		case 'woogallery':
			if ( isset( $args[0] ) && $args[0] && CC_WOOCOMMERCE ) {
				// OVERLAY.
				$gallery_overlay = '';
				if ( isset( $attributes['galleryOverlay'] ) && $attributes['galleryOverlay'] ) {
					$gallery_overlay = $attributes['galleryOverlay'];
				}
				// OVERLAY.
				// IMAGE.
				$image_props = '';
				if ( isset( $attributes['galleryType'] ) && 'masonry' === $attributes['galleryType'] ) {
					$image_props = 'style="width: 100%;"';
				} elseif ( isset( $attributes['galleryOverlay'] ) && 'gallery-sunrise' === $attributes['galleryOverlay'] ) {
					$image_props = 'style="object-fit: cover;"';
				} else {
					$image_props = 'style="width: 100%; height: 100%; object-fit: cover;"';
				}
				// IMAGE.

				$content = '';
				if ( isset( $product ) && $product ) {
					$original       = array();
					$full_url       = array();
					$medium_url     = array();
					$thumbnail_url  = array();
					$main_image     = array();
					$main_image[]   = $product->get_image_id();
					$gallery_images = $product->get_gallery_image_ids();
					$attachment_ids = array_merge( $main_image, $gallery_images );
					if ( 'wooGallery' === $attributes['galleryDynamicWordpressType'] ) {
						array_splice( $attachment_ids, 0, 1 );
					}
					foreach ( $attachment_ids as $images ) {
						$original[]       = wp_get_attachment_url( $images );
						$originalsrcset[] = wp_get_attachment_image_srcset( $images );
						$full_url[]       = wp_get_attachment_image_src( $images, 'full' )[0];
						$medium_url[]     = wp_get_attachment_image_src( $images, 'medium' )[0];
						$thumbnail_url[]  = wp_get_attachment_image_src( $images, 'thumbnail' )[0];
					}
					foreach ( $original as $key => $value ) {
						$urled    = false;
						$content .= '<figure class="cc-gallery-card ' . $gallery_overlay . ' gallery-1" data-ccgalleryname="gallery-1">';
						$content .= '<div style="overflow: hidden; width: 100%; position: relative;">';
						if ( isset( $attributes['linkWrapperActive'] ) && $attributes['linkWrapperActive'] && isset( $attributes['linkWrapperType'] ) && 'lightbox' === $attributes['linkWrapperType'] ) {
							$content .= '<a class="cc-lightbox cc-gallery-lightbox" href="' . $value . '">';
							$urled    = true;
						}
						$content .= '<img ' . $image_props . ' srcset="' . $originalsrcset[ $key ] . '" src="' . $value . '"></img>';
						$content .= '<figcaption>';
						if ( ( isset( $attributes['galleryTitleControl'] ) && $attributes['galleryTitleControl'] ) || ( isset( $attributes['galleryDescriptionControl'] ) && $attributes['galleryDescriptionControl'] ) ) {
							if ( $attributes['galleryTitleControl'] ) {
								$attachment_title = get_the_title( $attachment_ids[ $key ] );
								if ( $attachment_title ) {
									$content .= '<p class="cc-gallery-title">' . $attachment_title . '</p>';
								}
							}
						}
						$content .= '</figcaption>';
						if ( $urled ) {
							$content .= '</a>';
						}
						if ( 'active' === $urled ) {
							$content .= '</div>';
						}
						$content .= '</div>';
						$content .= '</figure>';
					}
				}
				$value = $content;
			}

			break;
		// GALLERY.

		// MENU.
		case 'menu':
			$value = cc_menu_maker( $attributes, $block );
			break;

		case 'menuname':
			if ( isset( $attributes['menuSelected'] ) && $attributes['menuSelected'] ) {
				if ( isset( $attributes['menuAriaLabel'] ) && $attributes['menuAriaLabel'] ) {
					$value = $attributes['menuAriaLabel'];
				} else {
					$menu = wp_get_nav_menu_object( $attributes['menuSelected'] );
					if ( $menu ) {
						$value = $menu->name;
					}
				}
			}

			break;
		// MENU.

		// SLIDER.
		case 'slider':
			if ( isset( $args[0] ) && $args[0] && isset( $args[1] ) && $args[1] && isset( $args[2] ) && $args[2] ) {
				if ( 'thumbs' === $args[0] ) {
					$control = $args[1];
					$control = str_replace( '!!', '=', $control );
					$control = str_replace( array( '[', ']' ), array( '{', '}' ), $control );
					$control = cc_parser( $control, $attributes, $block );
					if ( $control ) {
						$value = $args[2];
					}
				} elseif ( 'controller' === $args[0] ) {
					$control = $args[1];
					$control = str_replace( '!!', '=', $control );
					$control = str_replace( array( '[', ']' ), array( '{', '}' ), $control );
					$control = cc_parser( $control, $attributes, $block );
					if ( $control ) {
						$value = $args[2];
					}
				}
			}
			break;
		// SLIDER.

		// QUERY.
		case 'postquery':
			if ( isset( $args[0] ) && $args[0] ) {
				switch ( $args[0] ) {
					case 'totalcount':
						if ( isset( $block->context['queryCount'] ) ) {
							$value = $block->context['queryCount'];
						}

						break;

					case 'totalpages':
						if ( isset( $block->context['queryTotal'] ) ) {
							$value = $block->context['queryTotal'];
						}

						break;

					case 'viewed':
						if ( isset( $block->context['queryPostPerPage'] ) && $block->context['queryPostPerPage'] && isset( $block->context['queryCurrentPage'] ) && $block->context['queryCurrentPage'] ) {
							$viewed = $block->context['queryPostPerPage'] * $block->context['queryCurrentPage'];
							if ( isset( $block->context['queryCount'] ) && $block->context['queryCount'] && $viewed > $block->context['queryCount'] ) {
								$viewed = $block->context['queryCount'];
							}
							$value = $viewed;
						}

						break;
				}
			}

			break;
		// QUERY.

		// QUERY PAGINATION NUMBERS.
		case 'pagination':
			if ( isset( $block->context['queryInherit'] ) && $block->context['queryInherit'] ) {
				$paginate_args = array(
					'prev_next' => false,
				);
				$value         = paginate_links( $paginate_args );
			} elseif ( isset( $block->context['paginateArgs'] ) && $block->context['paginateArgs'] ) {
				if ( isset( $block->context['queryInherit'] ) && $block->context['queryInherit'] ) {
					$paginate_args = array(
						'prev_next' => false,
					);
					$value         = paginate_links( $paginate_args );
				} elseif ( 'products' === $block->context['queryType'] ) {
					$value = paginate_links(
						apply_filters(
							'woocommerce_pagination_args',
							$block->context['paginateArgs']
						)
					);
				} else {
					$value = paginate_links( $block->context['paginateArgs'] );
				}
			}

			break;
		// QUERY PAGINATION NUMBERS.

		// QUERY PREV NEXT.
		case 'nextquery':
			if ( isset( $block->context['queryId'] ) && $block->context['queryId'] ) {
				$value = '' . \Cwicly\Helpers::block_query_prev_next( $block, 'next' ) . '" cc-qp-next-button="' . $block->context['queryId'] . '';
			} else {
				$value = \Cwicly\Helpers::block_query_prev_next( $block, 'next' );
			}

			break;
		case 'prevquery':
			if ( isset( $block->context['queryId'] ) && $block->context['queryId'] ) {
				$value = '' . \Cwicly\Helpers::block_query_prev_next( $block, 'prev' ) . '" cc-qp-prev-button="' . $block->context['queryId'] . '';
			} else {
				$value = \Cwicly\Helpers::block_query_prev_next( $block, 'prev' );
			}

			break;

		case 'nextqb':
			if ( isset( $block->context['queryId'] ) && $block->context['queryId'] ) {
				if ( ! \Cwicly\Helpers::block_query_prev_next( $block, 'next' ) ) {
					$value = 'disabled';
				} else {
					$value = 'cc-placeholder';
				}
			} elseif ( ! \Cwicly\Helpers::block_query_prev_next( $block, 'next' ) ) {
				$value = 'disabled';
			} else {
				$value = 'cc-placeholder';
			}

			break;
		case 'prevqb':
			if ( isset( $block->context['queryId'] ) && $block->context['queryId'] ) {
				if ( ! \Cwicly\Helpers::block_query_prev_next( $block, 'prev' ) ) {
					$value = 'disabled';
				} else {
					$value = 'cc-placeholder';
				}
			} elseif ( ! \Cwicly\Helpers::block_query_prev_next( $block, 'prev' ) ) {
				$value = 'disabled';
			} else {
				$value = 'cc-placeholder';
			}

			break;

		// COMPONENTS.
		case 'cs-index':
			if ( isset( $block->context['componentVariantClasses'] ) && $block->context['componentVariantClasses'] ) {
				$value = $block->context['componentVariantClasses'];
			}

			break;

		case 'aclv':
			if ( isset( $block->context['componentVariantStyles'] ) && $block->context['componentVariantStyles'] && isset( $attributes['additionalClassesVariantsR'] ) && $attributes['additionalClassesVariantsR'] ) {
				$final = array();
				foreach ( $block->context['componentVariantStyles'] as $key => $id ) {
					if (
					isset( $attributes['additionalClassesVariantsR'][ $id ] )
					&& $attributes['additionalClassesVariantsR'][ $id ]
					) {
						$final = array_merge( $final, $attributes['additionalClassesVariantsR'][ $id ] );
					}
				}
				$final = array_unique( $final );

				foreach ( $final as $key => $value ) {
					if ( strpos( $value, 'ccshell-' ) !== false ) {
						$shell         = str_replace( 'ccshell-', '', $value );
						$final[ $key ] = \Cwicly\Helpers::get_shell( $shell );
					}
				}

				$final = implode( ' ', $final );
				if ( $final ) {
					if ( isset( $args[0] ) && 'true' === $args[0] ) {
						$value = $final;
					} else {
						$value = ' ' . $final . '';
					}
				}
			}

			break;

		case 'gclv':
			if ( isset( $block->context['componentVariantStyles'] ) && $block->context['componentVariantStyles'] && isset( $attributes['globalClassVariant'] ) && $attributes['globalClassVariant'] ) {
				$global_classes = array();
				$count          = 0;
				foreach ( $block->context['componentVariantStyles'] as $key => $id ) {
					if (
					isset( $attributes['globalClassVariant'] ) &&
					isset( $attributes['globalClassVariant'][ $id ] )
					&& $attributes['globalClassVariant'][ $id ]
					) {
						++$count;
						$global_classes = array_merge( $global_classes, $attributes['globalClassVariant'][ $id ] );
					}
				}
				if ( $count && $global_classes ) {
					$final = Cwicly\Helpers::global_classes(
						array(
							'globalClass' => $global_classes,
						)
					);
					if ( $final ) {
						if ( isset( $args[0] ) && 'true' === $args[0] ) {
							$value = $final;
						} else {
							$value = ' ' . $final . '';
						}
					}
				}
			}
			break;

		// FUNCTION RETURN.
		case 'return':
			if ( $block->name && strpos( $block->name, 'cwicly/' ) !== false && isset( $args[0] ) && $args[0] ) {
				if ( ( isset( $block->parsed_block['innerHTML'] ) &&
				strpos( $block->parsed_block['innerHTML'], $args[0] ) !== false )
				||
				'cwicly/code' === $block->name
				) {
					$function = $args[0];
					$value    = \Cwicly\Helpers::echo( $function );
				} else {
					$value = $args[0];
				}
			}

			break;

		// TABS.
		case 'tab_state':
			if ( isset( $attributes['tabContentActiveN'] ) && $attributes['tabContentActiveN'] ) {
				$value = 'cc-tab-active';
			} else {
				$value = 'cc-tab-hidden';
			}
			break;

		case 'tab_content_state':
			if ( isset( $attributes['tabContentActiveN'] ) && $attributes['tabContentActiveN'] ) {
				$value = 'cc-tab-content-active';
			} else {
				$value = 'cc-tab-content-hidden';
			}
			break;
		// TABS.

		// WOO NOTICES.
		case 'woonotice':
			if ( isset( $block->context['wooNotice'] ) && $block->context['wooNotice'] ) {
				$value = wc_kses_notice( $block->context['wooNotice'] );
			}

			break;

		case 'nav_menu':
			if ( isset( $args[0] ) && $args[0] ) {
				$value = \Cwicly\NavMenu::wp_nav_maker( $args[0], $attributes );
			}

			break;

		case 'component':
			if ( isset( $args[0] ) && $args[0] && isset( $args[1] ) && $args[1] ) {
				if ( 'parameter' === $args[0] ) {
					$ref       = $args[1];
					$pre_value = '';
					if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
						$parameter = $block->context['componentProperties'][ $ref ];
						if ( isset( $parameter['parent'] ) && isset( $parameter['value'] ) ) {
							$parameter_parent   = \Cwicly\Helpers::get_parent_property( $parameter['value'], $block );
							$parameter          = array();
							$parameter['value'] = $parameter_parent;
						}
						if ( isset( $parameter['value'] ) && $parameter['value'] && isset( $parameter['value']['maker'] ) && $parameter['value']['maker'] ) {
							if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] && ( ( ! isset( $parameter['value']['dynamic'] ) || ! $parameter['value']['dynamic'] ) ) ) {
								$pre_value = \Cwicly\Helpers::get_component_option_value_from_id( $parameter['value']['maker'], $ref, $block );
							} else {
								$pre_value = cc_parser( $parameter['value']['maker'], $attributes, $block );
							}
						} elseif ( isset( $parameter['value'] ) && $parameter['value'] && ! isset( $parameter['value']['maker'] ) ) {
							if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] ) {
								$pre_value = \Cwicly\Helpers::get_component_option_value_from_id( $parameter['value'], $ref, $block );
							} else {
								$pre_value = $parameter['value'];
							}
						}
					} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
						if ( isset( $block->context['componentMetaProperties'][ $ref ]['type'] ) && 'options' === $block->context['componentMetaProperties'][ $ref ]['type'] && ( ! isset( $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) || ! $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) ) {
							$pre_value = \Cwicly\Helpers::get_component_option_value_from_id( $block->context['componentMetaProperties'][ $ref ]['default'], $ref, $block );
						} else {
							$pre_value = $block->context['componentMetaProperties'][ $ref ]['default'];
						}
					}
					if ( isset( $args[2] ) && $args[2] && $pre_value ) {
						switch ( $args[2] ) {
							case 'boolean':
								$value = 'true' === $pre_value ? true : false;
								break;

							case 'accordionopen':
								$value = 'true' === $pre_value ? 'cc-accordion-active' : 'cc-accordion-hidden';
								break;

							case 'accordionlinked':
								if ( 'true' === $pre_value ) {
									if ( isset( $attributes['accordionGroupComp'] ) && $attributes['accordionGroupComp'] ) {
										$prep = '';
										preg_match( '/!ref=([\w-]+)!/', $attributes['accordionGroupComp'], $inner_ref );
										if ( isset( $inner_ref[1] ) ) {
											$inner_ref = $inner_ref[1];
											$prep      = \Cwicly\Helpers::get_component_value( $inner_ref, $attributes, $block );
											$value     = $prep;
										}
									}
								}
								break;

							case 'listIconActive':
								if ( 'true' === $pre_value ) {
									$value = 'cc-icon-list';
								}
								break;

							case 'video':
								$value = \Cwicly\Helpers::create_video_component( $pre_value, $attributes, $block );
								break;

							case 'videoembed':
								$value = \Cwicly\Helpers::create_video_component( $pre_value, $attributes, $block, true );
								break;

							case 'lg':
								if ( is_array( $pre_value ) ) {
									if ( isset( $pre_value['lg'] ) ) {
										$value = $pre_value['lg'];
									}
								} elseif ( is_string( $pre_value ) ) {
									$value = $pre_value;
								}
								break;

							case 'md':
								if ( is_array( $pre_value ) ) {
									if ( isset( $pre_value['md'] ) ) {
										$value = $pre_value['md'];
									}
								} elseif ( is_string( $pre_value ) ) {
									$value = $pre_value;
								}
								break;

							case 'sm':
								if ( is_array( $pre_value ) ) {
									if ( isset( $pre_value['sm'] ) ) {
										$value = $pre_value['sm'];
									}
								} elseif ( is_string( $pre_value ) ) {
									$value = $pre_value;
								}
								break;

							case 'mapsAddress':
								if ( isset( $pre_value['gmapAddress'] ) ) {
									$value = $pre_value['gmapAddress'];
								}
								break;

							case 'mapsLatitude':
								if ( isset( $pre_value['gmapLatitude'] ) ) {
									$value = $pre_value['gmapLatitude'];
								}
								break;

							case 'mapsLongitude':
								if ( isset( $pre_value['gmapLongitude'] ) ) {
									$value = $pre_value['gmapLongitude'];
								}
								break;
						}
					} else {
						$value = $pre_value;
					}
				} elseif ( 'class' === $args[0] ) {
					$ref = $args[1];
					if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
						$classes = $block->context['componentProperties'][ $ref ];
						if ( isset( $classes['parent'] ) && isset( $classes['value'] ) ) {
							$classes = \Cwicly\Helpers::get_parent_property( $classes['value'], $block );
						}

						$additionals = array();
						if ( isset( $classes['additionalClass'] ) && $classes['additionalClass'] ) {
							foreach ( $classes['additionalClass'] as $class ) {
								if ( isset( $class['value'] ) && $class['value'] ) {
									if ( isset( $class['isShell'] ) && $class['isShell'] ) {
										$additionals[] = \Cwicly\Helpers::get_shell( $class['value'] );
									} else {
										$additionals[] = $class['value'];
									}
								}
							}
						}
						$value   = implode( ' ', $additionals );
						$globals = '';
						if ( isset( $classes['globalClass'] ) && $classes['globalClass'] ) {
							$globals = Cwicly\Helpers::global_classes( $classes );
							if ( $value ) {
								$value .= ' ';
							}
							$value .= $globals;
						}

						if ( ! $value ) {
							$value = Cwicly\Helpers::get_component_class_defaults( $ref, $block );
						}
					} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
						$value = Cwicly\Helpers::get_component_class_defaults( $ref, $block );
					}
				} elseif ( 'link' === $args[0] ) {
					$ref = $args[1];
					if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
						$link = $block->context['componentProperties'][ $ref ];
						if ( isset( $link['parent'] ) && isset( $link['value'] ) ) {
							$link_parent   = \Cwicly\Helpers::get_parent_property( $link['value'], $block );
							$link          = array();
							$link['value'] = $link_parent;
						}

						if ( isset( $link['value'] ) && $link['value'] && isset( $link['value']['maker'] ) && $link['value']['maker'] ) {
							$link = $link['value']['maker'];

							if ( isset( $link['href'] ) ) {
								$value .= $link['href'];
							}
							$count = 0;
							foreach ( $link as $key => $link_value ) {
								if ( 'href' !== $key ) {
									++$count;
									$value .= '" ' . $key . '="' . $link_value . '';
								}
								if ( $key === 'data-lightbox' ) {
									wp_enqueue_script( 'cc-lightbox', CWICLY_DIR_URL . 'assets/js/lightbox.js', null, CWICLY_VERSION, true );
									wp_enqueue_style( 'cc-lightbox', CWICLY_DIR_URL . 'assets/css/lightbox.css', array(), CWICLY_VERSION );
								}
							}
							if ( $count ) {
								$value .= '"';
							}
						}
					}
				} elseif ( 'image' === $args[0] ) {
					$ref = $args[1];
					if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
						$image = $block->context['componentProperties'][ $ref ];
						if ( isset( $image['parent'] ) && isset( $image['value'] ) ) {
							$link_parent    = \Cwicly\Helpers::get_parent_property( $image['value'], $block );
							$image          = array();
							$image['value'] = $link_parent;
						}

						if ( isset( $image['value'] ) && $image['value'] && isset( $image['value']['maker'] ) && $image['value']['maker'] ) {
							$image = $image['value']['maker'];

							if ( isset( $image['src'] ) ) {
								$value .= $image['src'];
							}

							if ( isset( $block->name ) && $block->name && isset( $attributes['imageAlt'] ) && $attributes['imageAlt'] ) {
								unset( $image['alt'] );
							}

							$count = 0;
							foreach ( $image as $key => $image_value ) {
								if ( 'src' !== $key && $image_value ) {
									++$count;
									$value .= '" ' . $key . '="' . $image_value . '';
								}
							}
						} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
							$value = \Cwicly\Helpers::get_component_image_default( $block, $ref, $attributes );
						}
					} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
						$value = \Cwicly\Helpers::get_component_image_default( $block, $ref, $attributes );
					}
				} elseif ( 'icon' === $args[0] ) {
					$ref = $args[1];
					if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
						$icon = $block->context['componentProperties'][ $ref ];
						if ( isset( $icon['parent'] ) && isset( $icon['value'] ) ) {
							$link_parent   = \Cwicly\Helpers::get_parent_property( $icon['value'], $block );
							$icon          = array();
							$icon['value'] = $link_parent;
						}

						if ( isset( $icon['value'] ) && $icon['value'] && isset( $icon['value']['icon'] ) && $icon['value']['icon'] && isset( $icon['value']['icon']['icon'] ) && $icon['value']['icon']['icon'] ) {
							if ( isset( $icon['value']['icon']['unicode'] ) ) {
								$value = $icon['value']['icon']['unicode'];
							}
						} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) &&
						isset( $block->context['componentMetaProperties'][ $ref ]['default']['icon'] ) && $block->context['componentMetaProperties'][ $ref ]['default']['icon'] && isset( $block->context['componentMetaProperties'][ $ref ]['default']['icon']['icon'] ) && $block->context['componentMetaProperties'][ $ref ]['default']['icon']['icon'] ) {
							if ( isset( $block->context['componentMetaProperties'][ $ref ]['default']['icon']['unicode'] ) ) {
								$value = $block->context['componentMetaProperties'][ $ref ]['default']['icon']['unicode'];
							}
						}
					} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) &&
					isset( $block->context['componentMetaProperties'][ $ref ]['default']['icon'] ) && $block->context['componentMetaProperties'][ $ref ]['default']['icon'] && isset( $block->context['componentMetaProperties'][ $ref ]['default']['icon']['icon'] ) && $block->context['componentMetaProperties'][ $ref ]['default']['icon']['icon'] ) {
						if ( isset( $block->context['componentMetaProperties'][ $ref ]['default']['icon']['unicode'] ) ) {
							$value = $block->context['componentMetaProperties'][ $ref ]['default']['icon']['unicode'];
						}
					}
				} elseif ( 'gallery' === $args[0] ) {
					$ref = $args[1];
					if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
						$parameter = $block->context['componentProperties'][ $ref ];
						if ( isset( $parameter['parent'] ) && isset( $parameter['value'] ) ) {
							$parameter_parent   = \Cwicly\Helpers::get_parent_property( $parameter['value'], $block );
							$parameter          = array();
							$parameter['value'] = $parameter_parent;
						}
						if ( isset( $parameter['value'] ) && $parameter['value'] && isset( $parameter['value']['gallery'] ) && $parameter['value']['gallery'] ) {
							$merged = array_merge( $attributes, $parameter['value']['gallery'] );
							if ( ! isset( $merged['galleryDynamic'] ) || ! $merged['galleryDynamic'] ) {
								$merged['galleryDynamic'] = 'static';
							}
							if ( ! isset( $merged['galleryDynamicType'] ) || ! $merged['galleryDynamicType'] ) {
								$merged['galleryDynamicType'] = 'static';
							}
							$value = \Cwicly\Helpers::create_gallery_component( $merged, $block );
						}
					}
				} elseif ( 'galleryfilter' === $args[0] ) {
					$ref = $args[1];
					if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
						$parameter = $block->context['componentProperties'][ $ref ];
						if ( isset( $parameter['parent'] ) && isset( $parameter['value'] ) ) {
							$parameter_parent   = \Cwicly\Helpers::get_parent_property( $parameter['value'], $block );
							$parameter          = array();
							$parameter['value'] = $parameter_parent;
						}
						if ( isset( $parameter['value'] ) && $parameter['value'] && isset( $parameter['value']['gallery'] ) && $parameter['value']['gallery'] ) {
							$merged = array_merge( $attributes, $parameter['value']['gallery'] );
							$value  = \Cwicly\Helpers::create_gallery_filter_component( $merged, $block );
						}
					}
				} elseif (
				isset( $attributes['componentConnectors'] ) &&
				isset( $attributes['componentConnectors'][ $args[0] ] ) &&
				isset( $attributes['componentConnectors'][ $args[0] ]['ref'] )
				) {
					$ref = $attributes['componentConnectors'][ $args[0] ]['ref'];
					if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
						$content = $block->context['componentProperties'][ $ref ];
						if ( isset( $content['parent'] ) && isset( $content['value'] ) ) {
							$link_parent      = \Cwicly\Helpers::get_parent_property( $content['value'], $block );
							$content          = array();
							$content['value'] = $link_parent;
						}
						if ( isset( $content['value'] ) && $content['value'] && isset( $content['value']['maker'] ) && $content['value']['maker'] ) {
							$value = cc_parser( $content['value']['maker'], $attributes, $block );
						} elseif ( isset( $content['value'] ) && $content['value'] && gettype( $content['value'] ) === 'string' ) {
							$value = $content['value'];
						} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
							$value = $block->context['componentMetaProperties'][ $ref ]['default'];
						}
					} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
						$value = $block->context['componentMetaProperties'][ $ref ]['default'];
					}
					if ( isset( $block->name ) && 'cwicly/list' === $block->name ) {
						if ( strpos( $value, '<ul' ) !== 0 && strpos( $value, '<ol' ) !== 0 ) {
							$value = '<ul>' . $value . '</ul>';
						}
					}
				}
			}

			break;

		case 'cccomp':
			if ( isset( $block->context['componentParentClasses'] ) && $block->context['componentParentClasses'] ) {
				$value = implode( '-', $block->context['componentParentClasses'] );
			}

			break;

		// TAILWIND.
		case 'shell':
			if ( isset( $args[0] ) && $args[0] ) {
				$shell = $args[0];
				$value = \Cwicly\Helpers::get_shell( $shell );
			}
			break;

		default:
			if ( isset( $match[0] ) ) {
				$value = $match[0];
			} else {
				$value = '{' . $dyn . '}';
			}

			break;
	}

	return $value;
}
