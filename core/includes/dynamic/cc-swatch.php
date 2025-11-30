<?php
/**
 * Cwicly Swatch
 *
 * Functions for creating and managing Swatches
 *
 * @package Cwicly\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Swatch Maker
 *
 * Functions for creating dynamic swatches based on Repeater or Variable Slug
 *
 * @package Cwicly\Functions
 * @version 1.1
 *
 * @param array  $attributes - The attributes of the block.
 * @param object $block - The block object.
 * @param array  $classes - The classes of the block.
 * @param array  $div_additions - The div additions of the block.
 * @param array  $html_attributes - The html attributes of the block.
 */
function cc_swatch_maker( $attributes, $block, $classes, $div_additions, $html_attributes ) {
	$content = '';
	global $post;
	if ( isset( $block->context['postId'] ) && $block->context['postId'] ) {
		$post = get_post( $block->context['postId'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
	if ( CC_WOOCOMMERCE ) {
		global $product;
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( get_the_ID() );
		}

		$old_product = $product;
		if ( isset( $block->context['product'] ) && $block->context['product'] ) {
			$product = $block->context['product'];
		}

		$defaults = array();
		if ( $product ) {
			$defaults = $product->get_default_attributes();
		}
		$tag = 'div';
		if ( isset( $attributes['containerLayoutTag'] ) && $attributes['containerLayoutTag'] ) {
			$tag = $attributes['containerLayoutTag'];
		}

		if ( isset( $block->context['wooVariable'] ) && $block->context['wooVariable'] && isset( $attributes['swatchSlug'] ) && false === strpos( $attributes['swatchSlug'], 'g_' ) && isset( $block->context['wooVariable']['terms'] ) && $attributes['swatchType'] && $block->context['wooVariable']['type'] === $attributes['swatchType'] ) {
			$context = $block->context['wooVariable'];
			if ( isset( $context['terms'] ) ) {
				if ( $attributes['swatchSlug'] ) {
					if ( 'select' === $attributes['swatchType'] ) {
						$content .= '<option value="">' . __( 'Choose an option', 'woocommerce' ) . '</option>';
					}
					foreach ( $context['terms'] as $swatcher ) {
						$content .= cc_swatch_helper( $attributes, $block, $swatcher['type'], $swatcher['name'], $swatcher['slug'], $defaults, $classes, $tag, $div_additions, $html_attributes, true, $block->context['wooVariable']['slug'] );
					}
				}
			}
		} elseif ( isset( $attributes['swatchType'] ) && ( ( isset( $block->context['wooVariable']['type'] ) && $block->context['wooVariable']['type'] === $attributes['swatchType'] ) || ( 'select' === $attributes['swatchType'] ) ) && isset( $attributes['swatchSlug'] ) && $attributes['swatchSlug'] && false !== strpos( $attributes['swatchSlug'], 'g_' ) ) {
			if ( $product && 'variable' === $product->get_type() ) {
				if ( $attributes['swatchSlug'] ) {
					if ( 'select' === $attributes['swatchType'] ) {
						$content .= '<option value="">' . __( 'Choose an option', 'woocommerce' ) . '</option>';
					}

					foreach ( $product->get_variation_attributes() as $taxonomy => $terms_slug ) {
						// Setting some data in an array.
						$type = wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) ) ? wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) )->type : 'select';
						$slug = $taxonomy;
						if ( isset( $block->context['wooVariable'] ) && isset( $block->context['wooVariable']['slug'] ) && $block->context['wooVariable']['slug'] ) {
							if ( $block->context['wooVariable']['slug'] !== $slug ) {
								continue;
							}
						}

						if ( $type === $attributes['swatchType'] ) {
							if ( taxonomy_exists( $taxonomy ) ) {
								$terms = wc_get_product_terms(
									$product->get_id(),
									$taxonomy,
									array(
										'fields' => 'all',
									)
								);
								foreach ( $terms as $term ) {
									$terms = wc_get_product_terms(
										$product->get_id(),
										$taxonomy,
										array(
											'fields' => 'all',
										)
									);

									$term_id   = $term->term_id; // The ID.
									$term_name = $term->name; // The Name.
									$term_slug = $term->slug; // The Slug.

									$content .= cc_swatch_helper( $attributes, $block, $term_id, $term_name, $term_slug, $defaults, $classes, $tag, $div_additions, $html_attributes, true, isset( $block->context['wooVariable']['slug'] ) ? $block->context['wooVariable']['slug'] : '' );
								}
							} else {
								foreach ( $terms_slug as $term ) {
									$content .= cc_swatch_helper( $attributes, $block, 'select', $term, $term, $defaults, $classes, $tag, $div_additions, $html_attributes, true, isset( $block->context['wooVariable']['slug'] ) ? $block->context['wooVariable']['slug'] : '' );
								}
							}
						}
					}
				}
			}
		}

		$product = $old_product;

		return $content;
	}
}

/**
 * Helper function for the swatch block.
 *
 * @param array  $attributes The attributes of the block.
 * @param object $block The block object.
 * @param int    $swatcher_id The id of the swatcher.
 * @param string $swatcher_name The name of the swatcher.
 * @param string $swatcher_slug The slug of the swatcher.
 * @param array  $defaults The default attributes.
 * @param array  $classes The classes of the block.
 * @param string $tag The tag of the block.
 * @param string $div_additions The div additions of the block.
 * @param string $html_attributes The html attributes of the block.
 * @param bool   $repeater If the block is a repeater.
 * @param string $repeater_id The id of the repeater.
 */
function cc_swatch_helper( $attributes, $block, $swatcher_id, $swatcher_name, $swatcher_slug, $defaults, $classes, $tag, $div_additions, $html_attributes, $repeater = false, $repeater_id = '' ) {
	$content = '';
	// TOOLTIP.
	$tooltip   = false;
	$tooltiper = array();
	// TOOLTIP.
	$selected = '';
	if ( in_array( $swatcher_slug, $defaults, true ) ) {
		$selected = ' selected';
	}
	$style     = '';
	$term_type = '';
	if ( 'image' === $attributes['swatchType'] && $swatcher_id ) {
		if ( ! $repeater || is_int( $swatcher_id ) ) {
			$term_type = wp_get_attachment_url( get_term_meta( $swatcher_id, '_cwicly_image_id', true ) );
		} else {
			$term_type = $swatcher_id;
		}
		$style = 'style="background-image:url(\'' . $term_type . '\')" ';
	}
	if ( 'color' === $attributes['swatchType'] && $swatcher_id ) {
		$term_type = '';
		if ( ! $repeater || is_int( $swatcher_id ) ) {
			$term_type = get_term_meta( $swatcher_id, '_cwicly_color', true );
		} else {
			$term_type = $swatcher_id;
		}
		$style = 'style="background-color:' . $term_type . '" ';
	}
	if ( isset( $attributes['tooltipSource'] ) && $attributes['tooltipSource'] ) {
		if ( 'swatchname' === $attributes['tooltipSource'] && $swatcher_name ) {
			$tooltip     = true;
			$tooltiper[] = 'data-tooltip="' . wp_filter_post_kses( htmlspecialchars( $swatcher_name ) ) . '"';
		}
		if ( 'swatchvalue' === $attributes['tooltipSource'] && $term_type ) {
			$tooltip     = true;
			$tooltiper[] = 'data-tooltip="' . wp_filter_post_kses( htmlspecialchars( $term_type ) ) . '"';
		}
	}
	// TOOLTIP.
	$tooltip_extras = cc_tooltip_extras( $tooltip, $attributes );
	if ( $tooltip_extras ) {
		$tooltiper = array_merge( $tooltiper, $tooltip_extras );
	}
	$tooltiper = implode( ' ', array_filter( $tooltiper ) );
	// TOOLTIP.
	// QUERY ID ADD.
	$query_id = '';
	// QUERY ID ADD.

	$viariation_id = '';
	if ( $repeater_id ) {
		$viariation_id = strtolower( $repeater_id );
	} else {
		$viariation_id = $attributes['swatchSlug'];
	}
	if ( 'select' !== $attributes['swatchType'] && 'button' !== $attributes['swatchType'] ) {
		$content .= '<' . $tag . ' class="' . $classes . $selected . '"' . $query_id . ' data-variation="' . $viariation_id . '" data-value="' . $swatcher_slug . '" ' . $style . $div_additions . $html_attributes . '' . $tooltiper . '>';
		if ( isset( $attributes['swatchText'] ) && $attributes['swatchText'] ) {
			$content .= $swatcher_name;
		}
		$content .= '</' . $tag . '>';
	} elseif ( 'button' === $attributes['swatchType'] ) {
		$content .= '<' . $tag . ' class="' . $classes . $selected . '"' . $query_id . ' data-variation="' . $viariation_id . '" data-value="' . $swatcher_slug . '" ' . $style . $div_additions . $html_attributes . '>';
		$content .= $swatcher_name;
		// }
		$content .= '</' . $tag . '>';
	} else {
		$content .= '<option data-variation="' . $viariation_id . '" value="' . $swatcher_slug . '"' . $selected . '>' . $swatcher_name . '</option>';
	}

	return $content;
}

/**
 * Get the tooltip extras.
 *
 * @param bool  $tooltip If the tooltip is enabled.
 * @param array $attributes The attributes of the block.
 */
function cc_tooltip_extras( $tooltip, $attributes ) {
	$div_additions = array();
	if ( $tooltip ) {
		if ( isset( $attributes['tooltipArrow'] ) && $attributes['tooltipArrow'] ) {
			$div_additions[] = 'data-tooltiparrow="true"';
		}
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
		if ( isset( $attributes['tooltipHideClick'] ) && $attributes['tooltipHideClick'] ) {
			$div_additions[] = 'data-tooltiphideclick="true"';
		}
		if ( isset( $attributes['tooltipPlacement'] ) && $attributes['tooltipPlacement'] ) {
			$div_additions[] = 'data-tooltipplace="' . $attributes['tooltipPlacement'] . '"';
		}
		if ( isset( $attributes['tooltipDuration'] ) && $attributes['tooltipDuration'] ) {
			$div_additions[] = 'data-tooltipduration="' . $attributes['tooltipDuration'] . '"';
		}
		if ( isset( $attributes['tooltipDuration'] ) && $attributes['tooltipDuration'] ) {
			$div_additions[] = 'data-tooltipduration="' . $attributes['tooltipDuration'] . '"';
		}
		if ( isset( $attributes['tooltipFollowCursor'] ) && $attributes['tooltipFollowCursor'] ) {
			$div_additions[] = 'data-tooltipfollow="' . $attributes['tooltipFollowCursor'] . '"';
		}
		if ( isset( $attributes['tooltipTheme'] ) && $attributes['tooltipTheme'] ) {
			$div_additions[] = 'data-theme="' . $attributes['tooltipTheme'] . '"';
		}
	}
	if ( $div_additions ) {
		return $div_additions;
	}
}
