<?php
/**
 * Cwicly Repeater
 *
 * Functions for creating and managing the repeaters
 *
 * @package Cwicly\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Dynamic Repeater
 *
 * Function for creating the repeater depending on the source
 *
 * @package Cwicly\Functions
 * @version 1.1
 *
 * @param array  $attributes Repeater block attributes.
 * @param object $block Repeater block.
 */
function cc_repeater_maker( $attributes, $block ) {

	$content     = '';
	$mason_class = '';
	if ( isset( $attributes['repeaterMasonry'] ) && $attributes['repeaterMasonry'] ) {
		$mason_class = ' class="cc-masonry-item"';
	}

	// WOOCOMMERCE.
	if ( isset( $attributes['dynamic'] ) && 'woovariable' === $attributes['dynamic'] ) {
		if ( CC_WOOCOMMERCE ) {
			global $product;
			if ( isset( $block->context['product'] ) && $block->context['product'] ) {
				$product = $block->context['product'];
			}
			if ( ! is_object( $product ) ) {
				$product = wc_get_product( get_the_ID() );
			}

			if ( $product && $product->get_type() === 'variable' ) {
				$variations = $product->get_variation_attributes();
				$counter    = 0;
				foreach ( $variations as $taxonomy => $terms_slug ) {
					$counter = ++$counter;
					// To get the attribute label (in WooCommerce 3+).
					$taxonomy_label = wc_attribute_label( $taxonomy, $product );
					// Setting some data in an array.
					$variations_attributes_and_values[ $taxonomy ] = array( 'label' => $taxonomy_label );
					if ( isset( wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) )->type ) ) {
						$variations_attributes_and_values[ $taxonomy ]['type'] = wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) )->type;
					}
					$variations_attributes_and_values[ $taxonomy ]['slug'] = $taxonomy;

					if ( $product && taxonomy_exists( $taxonomy ) ) {
						// Get terms if this is a taxonomy - ordered. We need the names too.
						$terms = wc_get_product_terms(
							$product->get_id(),
							$taxonomy,
							array(
								'fields' => 'all',
							)
						);

						foreach ( $terms as $term ) {
							if ( in_array( $term->slug, $terms_slug, true ) ) {
								$term_id   = $term->term_id; // The ID.
								$term_name = $term->name; // The Name.
								$term_slug = $term->slug; // The Slug.
								$term_type = '';
								if ( $variations_attributes_and_values[ $taxonomy ]['type'] && 'color' === $variations_attributes_and_values[ $taxonomy ]['type'] ) {
									$term_type = get_term_meta( $term_id, '_cwicly_color', true );
								}
								if ( $variations_attributes_and_values[ $taxonomy ]['type'] && 'image' === $variations_attributes_and_values[ $taxonomy ]['type'] ) {
									$term_type = wp_get_attachment_url( get_term_meta( $term_id, '_cwicly_image_id', true ) );
								}

								// Setting the terms ID and values in the array.
								$variations_attributes_and_values[ $taxonomy ]['terms'][ $term_id ] = array(
									'name' => $term_name,
									'slug' => $term_slug,
									'type' => $term_type,
								);
							}
						}
					}
					if ( $block->parsed_block ) {
						$block_content = '';
						foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
							$block_content .= ( new WP_Block(
								$inner_block,
								array(
									'postType'     => get_post_type(),
									'postId'       => get_the_ID(),
									'wooVariable'  => $variations_attributes_and_values[ $taxonomy ],
									'repeater_row' => $counter,
									'isRepeater'   => true,
									'query_index'  => isset( $block->context['query_index'] ) ? $block->context['query_index'] : null,
									'queryId'      => isset( $block->context['queryId'] ) ? $block->context['queryId'] : null,
									'product'      => isset( $block->context['product'] ) ? $block->context['product'] : null,
									'rendered'     => true,
								)
							) )->render( array( 'dynamic' => true ) );
						}
						$content .= "<div $mason_class>$block_content</div>";
					}
				}
			}
		}
	} elseif ( isset( $attributes['dynamic'] ) && 'woogrouped' === $attributes['dynamic'] ) {
		global $post;
		global $product;
		$parent_product = $product;
		if ( isset( $block->context['product'] ) && $block->context['product'] ) {
			$product = $block->context['product'];
		}
		if ( $product && $product->get_type() === 'grouped' ) {

			$products = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );

			$quantites_required = false;
			$previous_post      = $post;

			foreach ( $products as $index => $grouped_product_child ) {
				$post_object = get_post( $grouped_product_child->get_id() );

				$product_id = ' data-cc-id="' . $grouped_product_child->get_id() . '"';
				$type       = ' data-cc-woo-type="' . $grouped_product_child->get_type() . '"';

				$quantites_required = $quantites_required || ( $grouped_product_child->is_purchasable() && ! $grouped_product_child->has_options() );
				$post               = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
				$product = wc_get_product( $grouped_product_child );
				if ( $block->parsed_block ) {
					$block_content = '';
					foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
						$block_content .= ( new WP_Block(
							$inner_block,
							array(
								'postType'       => get_post_type(),
								'postId'         => get_the_ID(),
								'product'        => $product,
								'parent_product' => $parent_product,
								'isWooGrouped'   => true,
								'repeater_row'   => $index + 1,
								'queryId'        => '',
								'rendered'       => true,
							)
						) )->render( array( 'dynamic' => true ) );
					}
					$content .= "<div$mason_class$product_id$type>$block_content</div>";
				}
			}
			$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $post );
			$product = $parent_product;
		}
	} elseif ( isset( $attributes['dynamic'] ) && 'woocartitems' === $attributes['dynamic'] ) {
		if ( CC_WOOCOMMERCE && WC()->cart ) {
			global $post;
			$index = 0;
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$post_object   = get_post( $cart_item['product_id'] );
				$previous_post = $post;
				$post          = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
				if ( $block->parsed_block['innerBlocks'] ) {
					$block_content = '';
					foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
						$block_content .= ( new WP_Block(
							$inner_block,
							array(
								'postType'      => get_post_type(),
								'postId'        => get_the_ID(),
								'product_index' => $index + 1,
								'cart_key'      => $cart_item_key,
								'cart_item'     => $cart_item,
								'rendered'      => true,
							)
						) )->render( array( 'dynamic' => true ) );
					}
					$content .= "<div $mason_class>$block_content</div>";
				}
				++$index;
				$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );
			}
		}
	} elseif ( isset( $attributes['dynamic'] ) && 'woogallery' === $attributes['dynamic'] ) {

		global $product;
		$parent_product = $product;
		if ( isset( $block->context['product'] ) && $block->context['product'] ) {
			$product = $block->context['product'];
		}
		if ( $product ) {
			$attachment_ids = array( get_post_thumbnail_id( $product->get_id() ) );
			$attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );

			$counter = 0;

			foreach ( $attachment_ids as $attachment_id ) {
				$counter   = ++$counter;
				$attribute = ' data-cc-image-id="' . $attachment_id . '"';
				if ( $block->parsed_block ) {
					$block_content = '';
					foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
						$block_content .= ( new WP_Block(
							$inner_block,
							array(
								'postType'         => get_post_type(),
								'postId'           => get_the_ID(),
								'isRepeater'       => true,
								'repeater_row'     => $counter,
								'query_index'      => isset( $block->context['query_index'] ) ? $block->context['query_index'] : null,
								'queryId'          => isset( $block->context['queryId'] ) ? $block->context['queryId'] : null,
								'product'          => isset( $block->context['product'] ) ? $block->context['product'] : null,
								'gallery_image_id' => $attachment_id,
								'rendered'         => true,
							)
						) )->render( array( 'dynamic' => true ) );
					}
					if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
						$content .= '<li class="splide__slide"' . $attribute . '>' . $block_content . '</li>';
					} else {
						$content .= "<div $mason_class>$block_content</div>";
					}
				}
			}
		}
	} elseif ( isset( $attributes['dynamic'] ) && 'woorelatedproducts' === $attributes['dynamic'] ) {

		global $post;
		global $product;
		$parent_product = $product;
		$previous_post  = $post;

		if ( isset( $block->context['product'] ) && $block->context['product'] ) {
			$product = $block->context['product'];
		}
		if ( $product ) {
			$pre = wc_get_related_products( $product->get_id() );

			$counter = 0;

			foreach ( $pre as $id ) {
				$counter = ++$counter;

				$the_product = wc_get_product( $id );
				$post_object = get_post( $id );
				$post        = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );

				if ( $block->parsed_block ) {
					$block_content = '';
					foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
						$block_content .= ( new WP_Block(
							$inner_block,
							array(
								'postType'     => 'product',
								'postId'       => $the_product->get_id(),
								'isRepeater'   => true,
								'repeater_row' => $counter,
								'query_index'  => isset( $block->context['query_index'] ) ? $block->context['query_index'] : null,
								'queryId'      => isset( $block->context['queryId'] ) ? $block->context['queryId'] : null,
								'product'      => $the_product,
								'rendered'     => true,
							)
						) )->render( array( 'dynamic' => true ) );
					}
					if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
						$content .= '<li class="splide__slide">' . $block_content . '</li>';
					} else {
						$content .= "<div $mason_class>$block_content</div>";
					}
				}
			}
			$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $post );
			$product = $parent_product;
		}
	}
	// WOOCOMMERCE.

	if ( class_exists( 'ACF' ) && isset( $attributes['dynamic'] ) && 'repeater' !== $attributes['dynamic'] ) {
		$post_id = get_the_ID();
		$field   = '';
		if ( 'acf' === $attributes['dynamic'] && isset( $attributes['dynamicACFField'] ) && $attributes['dynamicACFField'] ) {
			$field = $attributes['dynamicACFField'];
			if ( isset( $attributes['dynamicACFFieldLocation'] ) && $attributes['dynamicACFFieldLocation'] ) {
				if ( 'postid' === $attributes['dynamicACFFieldLocation'] && isset( $attributes['dynamicACFFieldLocationID'] ) && $attributes['dynamicACFFieldLocationID'] ) {
					$post_id = $attributes['dynamicACFFieldLocationID'];
				} elseif ( 'option' === $attributes['dynamicACFFieldLocation'] ) {
					$post_id = 'option';
				}
			}
		} elseif ( isset( $attributes['dynamic'] ) && strpos( $attributes['dynamic'], '!ref=' ) !== false ) {
			preg_match( '/!ref=([\w-]+)!/', $attributes['dynamic'], $ref );
			if ( isset( $ref[1] ) ) {
				$ref = $ref[1];
				if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
					$parameter = $block->context['componentProperties'][ $ref ];
					if ( isset( $parameter['parent'] ) && isset( $parameter['value'] ) ) {
						$parameter_parent   = \Cwicly\Helpers::get_parent_property( $parameter['value'], $block );
						$parameter          = array();
						$parameter['value'] = $parameter_parent;
					}
					if ( isset( $parameter['value'] ) && $parameter['value'] && isset( $parameter['value']['dynamic'] ) && $parameter['value']['dynamic'] && isset( $parameter['value']['maker'] ) && $parameter['value']['maker'] ) {
							$dynamics = $parameter['value']['dynamic'];
						if ( 'acf' === $dynamics['dynamic'] && isset( $dynamics['dynamicACFField'] ) && $dynamics['dynamicACFField'] ) {
							$field = $dynamics['dynamicACFField'];
							if ( isset( $dynamics['dynamicACFFieldLocation'] ) && $dynamics['dynamicACFFieldLocation'] ) {
								if ( 'postid' === $dynamics['dynamicACFFieldLocation'] && isset( $dynamics['dynamicACFFieldLocationID'] ) && $dynamics['dynamicACFFieldLocationID'] ) {
									$post_id = $dynamics['dynamicACFFieldLocationID'];
								} elseif ( 'option' === $dynamics['dynamicACFFieldLocation'] ) {
									$post_id = 'option';
								} elseif ( 'currentuser' === $dynamics['dynamicACFFieldLocation'] ) {
									$post_id = 'user_' . get_current_user_id();
								}
							}
						}
					}
				}
			}
		}

		if ( have_rows( $field, $post_id ) ) {
			// Loop through rows.
			while ( have_rows( $field, $post_id ) ) :
				the_row();
				$row = get_row_index() - 1;

				if ( $block->parsed_block ) {
					$block_content = '';
					$block_context = \Cwicly\Helpers::extract_necessary_context( $block->context );

					$new_context = array(
						'postType'     => get_post_type(),
						'postId'       => get_the_ID(),
						'repeaters'    => get_field( $field, $post_id ),
						'repeater_row' => $row,
						'query_index'  => isset( $block->context['query_index'] ) ? $block->context['query_index'] : null,
						'queryId'      => isset( $block->context['queryId'] ) ? $block->context['queryId'] : null,
						'product'      => isset( $block->context['product'] ) ? $block->context['product'] : null,
						'rendered'     => true,
					);

					$merged_context = array_merge( $block_context, $new_context );

					foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {

							$block_content .= ( new WP_Block(
								$inner_block,
								$merged_context
							) )->render( array( 'dynamic' => true ) );
					}

					if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
						$content .= '<li class="splide__slide">' . $block_content . '</li>';
					} else {
						$content .= '<div ' . $mason_class . '>' . $block_content . '</div>';
					}
				}
				// End loop.
			endwhile;
			// No value.
		}
	} elseif ( class_exists( 'ACF' ) && isset( $attributes['dynamic'] ) && 'repeater' === $attributes['dynamic'] && isset( $attributes['dynamicRepeaterField'] ) && $attributes['dynamicRepeaterField'] ) {
		$post_id = get_the_ID();

		$sub_repeater = get_sub_field( $attributes['dynamicRepeaterField'] );

		if ( have_rows( $attributes['dynamicRepeaterField'] ) ) {
			// Loop through rows.
			while ( have_rows( $attributes['dynamicRepeaterField'] ) ) :
				the_row();

				$row = get_row_index() - 1;

				if ( $block->parsed_block ) {
					$block_content = '';
					foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
						$block_content .= ( new WP_Block(
							$inner_block,
							array(
								'postType'     => get_post_type(),
								'postId'       => get_the_ID(),
								'repeaters'    => $sub_repeater,
								'repeater_row' => $row,
								'query_index'  => isset( $block->context['query_index'] ) ? $block->context['query_index'] : null,
								'queryId'      => isset( $block->context['queryId'] ) ? $block->context['queryId'] : null,
								'product'      => isset( $block->context['product'] ) ? $block->context['product'] : null,
								'rendered'     => true,
							)
						) )->render( array( 'dynamic' => true ) );
					}
					$content .= "<div $mason_class>$block_content</div>";
				}
				// End loop.
			endwhile;
			// No value.
		}
	}
	return addcslashes( $content, '$' );
}
