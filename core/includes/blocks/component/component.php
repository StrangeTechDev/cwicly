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
function cwicly_component_register() {
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cc_component_render_callback',
		)
	);
}
add_action( 'init', 'cwicly_component_register' );

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_component_render_callback( $attributes, $content, $block ) {

	if ( ! is_admin() && isset( $attributes['effectsTiltControl'] ) && $attributes['effectsTiltControl'] ) {
		wp_enqueue_script( 'cc-tilter', CWICLY_DIR_URL . 'assets/js/tilter.js', null, CWICLY_VERSION, true );
	}

	$hide_logged_in = cc_hide_logged_in( $attributes );
	$hide_guest     = cc_hide_guest( $attributes );

	if ( ( isset( $attributes['hideConditionsToggle'] ) && $attributes['hideConditionsToggle'] ) || ( $hide_guest && $hide_logged_in && cc_conditions_maker( $attributes, $block ) ) ) {
		global $active_components;
		if ( isset( $attributes['ref'] ) && isset( $active_components[ $attributes['ref'] ] ) ) {
			++$active_components[ $attributes['ref'] ];
		} elseif ( isset( $attributes['ref'] ) ) {
			$active_components[ $attributes['ref'] ] = 0;
		}

		static $seen_refs = array();

		if ( empty( $attributes['ref'] ) ) {
			return '';
		}

		$cc_args         = array(
			'posts_per_page' => 1,
			'post_type'      => 'cc_block',
			'meta_key'       => 'reference',
			'meta_value'     => $attributes['ref'],
		);
		$component_block = get_posts( $cc_args );
		if ( isset( $component_block[0] ) ) {
			$component_block = $component_block[0];
		} else {
			$component_block = false;
		}
		if ( ! $component_block ) {
			return '';
		}

		if ( isset( $seen_refs[ $attributes['ref'] ] ) ) {
			// WP_DEBUG_DISPLAY must only be honored when WP_DEBUG. This precedent
			// is set in `wp_debug_mode()`.
			$is_debug = WP_DEBUG && WP_DEBUG_DISPLAY;

			return $is_debug ?
			// translators: Visible only in the front end, this warning takes the place of a faulty block.
			__( '[block rendering halted]' ) :
			'';
		}

		if ( 'publish' !== $component_block->post_status || ! empty( $component_block->post_password ) ) {
			return '';
		}

		$seen_refs[ $attributes['ref'] ] = true;

		// Handle embeds for reusable blocks.
		global $wp_embed;
		$content = $wp_embed->run_shortcode( $component_block->post_content );
		$content = $wp_embed->autoembed( $content );

		$properties_meta    = get_post_meta( $component_block->ID, 'properties', true );
		$variants_meta      = get_post_meta( $component_block->ID, 'variants', true );
		$variant_group_meta = get_post_meta( $component_block->ID, 'variantGroups', true );
		$style_variations   = get_post_meta( $component_block->ID, 'styleVariations', true );

		$blocks = parse_blocks( $content );
		$output = '';

		$variants = false;
		if ( is_array( $variants_meta ) && count( $variants_meta ) > 0 ) {
			$variants = true;
		}

		$used_variations = false;
		$variant_index   = false;

		if ( $variants ) {
			if ( $style_variations && is_array( $style_variations ) && count( $style_variations ) > 0 ) {
				if ( isset( $attributes['variations'] ) && $attributes['variations'] ) {
					$styles = array();

					foreach ( $style_variations as $item ) {
						if ( ! isset( $attributes['variations'][ $item['id'] ] ) ) {
							continue;
						}

						$styles[] = $attributes['variations'][ $item['id'] ];
					}

					$used_variations = $styles;
				}
			} elseif ( isset( $attributes['variant'] ) && $attributes['variant'] ) {
				$variant_index = $attributes['variant'];
			} elseif ( $variants_meta && is_array( $variants_meta ) && isset( $variants_meta[0]['id'] ) ) {
				if ( $variant_group_meta && is_array( $variant_group_meta ) && count( $variant_group_meta ) > 0 ) {
					if ( isset( $variant_group_meta[0]['styles'] ) && is_array( $variant_group_meta[0]['styles'] ) && count( $variant_group_meta[0]['styles'] ) > 0 ) {
						$variant_index = implode( ' cs-', $variant_group_meta[0]['styles'] );
					} else {
						$variant_index = $variants_meta[0]['id'];
					}
				} else {
					$variant_index = $variants_meta[0]['id'];
				}
			}
		}

		$component_parent_meta = array();
		if ( isset( $block->context['componentMetaProperties'] ) ) {
			$component_parent_meta[] = $block->context['componentMetaProperties'];
		}

		$component_parent_properties = array();
		if ( isset( $block->context['componentParentProperties'] ) ) {
			$component_parent_properties = $block->context['componentParentProperties'];
		}
		if ( isset( $attributes['properties'] ) ) {
			$component_parent_properties = array_merge( $component_parent_properties, $attributes['properties'] );
			foreach ( $attributes['properties'] as $key => $value ) {
				if ( ! isset( $component_parent_properties[ $key ]['default'] ) && isset( $properties_meta[ $key ]['default'] ) ) {
					$component_parent_properties[ $key ]['default'] = $properties_meta[ $key ]['default'];
				}
			}
			if ( isset( $properties_meta ) && is_array( $properties_meta ) || is_object( $properties_meta ) ) {
				foreach ( $properties_meta as $key => $value ) {
					if ( ! isset( $component_parent_properties[ $key ] ) && isset( $value['default'] ) ) {
						$component_parent_properties[ $key ] = array(
							'default' => $value['default'],
						);
					}
				}
			}
		}

		$component_parent_classes = array();
		if ( isset( $block->context['componentParentClasses'] ) ) {
			$component_parent_classes = $block->context['componentParentClasses'];
		}
		if ( isset( $attributes['classID'] ) ) {
			$component_parent_classes[] = $attributes['classID'];
		}

		$component_styles = array();

		$component_classes = array();
		if ( $used_variations && is_array( $used_variations ) && count( $used_variations ) > 0 ) {

			foreach ( $used_variations as $key => $value ) {
				$is_group = \Cwicly\Helpers::is_component_variant_group( $value );

				if ( $is_group ) {
					$value = str_replace( 'group-', '', $value );
					if ( isset( $variant_group_meta ) && $variant_group_meta ) {
						foreach ( $variant_group_meta as $key => $group ) {
							if ( isset( $group['id'] ) && $group['id'] && $group['id'] === $value ) {
								$value = $group;
							}
						}

						if ( isset( $value['styles'] ) && $value['styles'] ) {
							$styles           = $value['styles'];
							$component_styles = array_merge( $component_styles, $styles );
							if ( $styles && is_array( $styles ) && count( $styles ) > 0 ) {
								foreach ( $styles as $key => $value ) {
									if ( ! in_array( 'cs-' . $value . '', $component_classes, true ) ) {
										$component_classes[] = 'cs-' . $value . '';
									}
								}
							}
						}
					}
				} elseif ( ! in_array( 'cs-' . $value . '', $component_classes, true ) ) {
					$component_styles    = array_merge( $component_styles, array( $value ) );
					$component_classes[] = 'cs-' . $value . '';
				}
			}

			$component_classes = ' ' . implode( ' ', $component_classes );

		} elseif ( isset( $variant_index ) && $variant_index ) {
			$cs       = $variant_index;
			$is_group = \Cwicly\Helpers::is_component_variant_group( $cs );

			if ( $is_group ) {
				$cs = str_replace( 'group-', '', $cs );
				if ( isset( $variant_group_meta ) && $variant_group_meta ) {
					foreach ( $variant_group_meta as $key => $group ) {
						if ( isset( $group['id'] ) && $group['id'] && $group['id'] === $cs ) {
							$cs = $group;
						}
					}

					if ( isset( $cs['styles'] ) && $cs['styles'] ) {
						$styles           = $cs['styles'];
						$component_styles = $styles;
						$prep             = array();
						if ( $styles && is_array( $styles ) && count( $styles ) > 0 ) {
							foreach ( $styles as $key => $value ) {
								$prep[] = 'cs-' . $value . '';
							}
						}

						$component_classes = ' ' . implode( ' ', $prep );
					}
				}
			} else {
				$component_styles  = array( $cs );
				$component_classes = ' cs-' . $cs . '';
			}
		}

		$block_context = $block->context;

		$new_context = array(
			'isComponent'                   => true,
			'componentProperties'           => isset( $attributes['properties'] ) ? $attributes['properties'] : array(),
			'componentParentProperties'     => $component_parent_properties,
			'componentParentClasses'        => $component_parent_classes,
			'componentVariant'              => $variant_index,
			'componentVariants'             => $variants_meta,
			'componentVariantGroups'        => $variant_group_meta,
			'componentMetaProperties'       => $properties_meta,
			'componentParentMetaProperties' => $component_parent_meta,
			'componentIndex'                => isset( $active_components[ $attributes['ref'] ] ) ? $active_components[ $attributes['ref'] ] : 0,
			'componentVariantClasses'       => $component_classes,
			'componentVariantStyles'        => $component_styles,
			'componentInnerBlocks'          => isset( $attributes['serializedInnerBlocks'] ) ? $attributes['serializedInnerBlocks'] : '',
		);

		if ( isset( $active_components[ $attributes['ref'] ] ) && $active_components[ $attributes['ref'] ] ) {
			$new_context['rendered'] = true;
		}

		$merged_context = array_merge( $block_context, $new_context );

		foreach ( $blocks as $inner_block ) {

				$output .= ( new WP_Block(
					$inner_block,
					$merged_context
				) )->render( array( 'dynamic' => true ) );
		}

		// If there are blocks in this content, we shouldn't run wpautop() on it later.
		$priority = has_filter( 'the_content', 'wpautop' );
		if ( false !== $priority && doing_filter( 'the_content' ) && has_blocks( $content ) ) {
			remove_filter( 'the_content', 'wpautop', $priority );
			add_filter( 'the_content', '_restore_wpautop_hook', $priority + 1 );
		}

		unset( $seen_refs[ $attributes['ref'] ] );

		return cc_render( $output, $attributes, $block, array(), true );
	}
}
