<?php
/**
 * Cwicly Taxonomy
 *
 * Functions for creating and managing Taxonomy block functions
 *
 * @package Cwicly\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render Taxonomy block
 *
 * @param array  $attributes Array of attributes.
 * @param object $block Block object.
 */
function cc_taxonomyterms_maker( $attributes, $block ) {
	global $post;
	$content     = '';
	$mason_class = '';
	if ( isset( $attributes['repeaterMasonry'] ) && $attributes['repeaterMasonry'] ) {
		$mason_class = ' class="cc-masonry-item"';
	}
	if ( isset( $attributes['taxtermsSource'] ) && $attributes['taxtermsSource'] && 'current' === $attributes['taxtermsSource'] ) {
		if ( ( isset( $block->context['query_index'] ) && $block->context['query_index'] ) || is_singular() ) {
			$excluded     = array();
			$tag_excluded = array();
			$included     = array();
			$tag_included = array();

			if ( isset( $attributes['taxtermsInclude'] ) && $attributes['taxtermsInclude'] ) {
				foreach ( $attributes['taxtermsInclude'] as $includer ) {
					if ( isset( $includer['taxonomy'] ) && $includer['taxonomy'] ) {
						$tag_included[] = $includer['value'];
					} else {
						$included[] = $includer['value'];
					}
				}
			}
			if ( isset( $attributes['taxtermsExclude'] ) && $attributes['taxtermsExclude'] ) {
				foreach ( $attributes['taxtermsExclude'] as $excluder ) {
					if ( isset( $excluder['taxonomy'] ) && $excluder['taxonomy'] ) {
						if ( ! in_array( $excluder['value'], $tag_included ) ) {
							$tag_excluded[] = $excluder['value'];
						}
					} elseif ( ! in_array( $excluder['value'], $included ) ) {
							$excluded[] = $excluder['value'];
					}
				}
			}

			$taxonomies = get_post_taxonomies();
			if ( $tag_excluded ) {
				foreach ( $taxonomies as $key => $value ) {
					if ( in_array( $value, $tag_excluded ) ) {
						unset( $taxonomies[ $key ] );
					}
				}
			}
			if ( $tag_included ) {
				foreach ( $taxonomies as $key => $value ) {
					if ( ! in_array( $value, $tag_included ) ) {
						unset( $taxonomies[ $key ] );
					}
				}
			}

			$arrays = array();
			foreach ( $taxonomies as $tax ) {
				$result = get_the_terms( $post->ID, $tax );
				if ( is_array( $result ) ) {
					if ( isset( $attributes['taxtermsTopParents'] ) && $attributes['taxtermsTopParents'] ) {
						foreach ( $result as $term ) {
							$arrays[] = \Cwicly\Helpers::get_term_top_level_parent( $term->term_id, $tax );
						}
					} else {
						$arrays = array_merge( $arrays, $result );
					}
				}
			}

			if ( $included ) {
				$arrays = array_filter(
					$arrays,
					function ( $array ) use ( $included ) {
						return in_array( $array->term_id, $included );
					}
				);
			}

			$count = 1;
			$limit = null;

			if ( isset( $attributes['taxtermsNumber'] ) && $attributes['taxtermsNumber'] ) {
				$limit = $attributes['taxtermsNumber'];
			}

			foreach ( $arrays as $index => $tax ) {
				if ( isset( $tax ) && $tax ) {
					if ( ! in_array( $tax->term_id, $excluded ) ) {
						if ( ! $limit || $count <= intval( $limit ) ) {
							$count         = ++$count;
							$block_content = '';
							foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
								$block_content .= ( new WP_Block(
									$inner_block,
									array(
										'postType'       => get_post_type(),
										'postId'         => get_the_ID(),
										'taxterms'       => $tax,
										'taxterms_index' => $index + 1,
										'rendered'       => true,
									)
								) )->render( array( 'dynamic' => true ) );
							}

							if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
								$content .= '<li class="splide__slide">' . $block_content . '</li>';
							} else {
								$content .= '<div ' . $mason_class . '>' . $block_content . '</div>';
							}
						}
					}
				}
			}
		} else {
			$excluded = array();
			if ( isset( $attributes['taxtermsExclude'] ) && $attributes['taxtermsExclude'] ) {
				foreach ( $attributes['taxtermsExclude'] as $excluder ) {
					$excluded[] = $excluder['value'];
				}
			}

			$count = 1;
			$limit = null;

			if ( isset( $attributes['taxtermsNumber'] ) && $attributes['taxtermsNumber'] ) {
				$limit = $attributes['taxtermsNumber'];
			}

			$object = get_queried_object();
			if ( isset( $object ) && $object ) {
				if ( ( isset( $object->term_id ) && ! in_array( $object->term_id, $excluded ) ) || ! isset( $object->term_id ) ) {
					if ( ! $limit || $count <= intval( $limit ) ) {
						$count         = ++$count;
						$block_content = '';
						foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
							$block_content .= ( new WP_Block(
								$inner_block,
								array(
									'postType' => get_post_type(),
									'postId'   => get_the_ID(),
									'taxterms' => $object,
									'rendered' => true,
								)
							) )->render( array( 'dynamic' => true ) );
						}
						if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
							$content .= '<li class="splide__slide">' . $block_content . '</li>';
						} else {
							$content .= '<div ' . $mason_class . '>' . $block_content . '</div>';
						}
					}
				}
			}
		}
	} elseif ( isset( $attributes['taxtermsSource'] ) && $attributes['taxtermsSource'] && 'custom' === $attributes['taxtermsSource'] && isset( $attributes['taxtermsPostType'] ) && $attributes['taxtermsPostType'] ) {
		$post_types      = array();
		$post_taxonomies = array();
		$excluded        = array();
		$included        = array();
		if ( isset( $attributes['taxtermsExclude'] ) && $attributes['taxtermsExclude'] ) {
			foreach ( $attributes['taxtermsExclude'] as $excluder ) {
				$excluded[] = $excluder['value'];
			}
		}
		if ( isset( $attributes['taxtermsInclude'] ) && $attributes['taxtermsInclude'] ) {
			$included = array();
			foreach ( $attributes['taxtermsInclude'] as $includer ) {
				$included[] = $includer['value'];
			}
		}

		foreach ( $attributes['taxtermsPostType'] as $type ) {
			$post_types[]            = $type['value'];
			$current_post_taxonomies = get_object_taxonomies( $type['value'] );
			foreach ( $current_post_taxonomies as $each ) {
				if ( ! in_array( $each, $post_taxonomies ) ) {
					$post_taxonomies[] = $each;
				}
			}
		}

		$exclude_current = false;
		if ( ! is_front_page() && ! is_home() && isset( $attributes['taxtermsExcludeCurrent'] ) && $attributes['taxtermsExcludeCurrent'] ) {
			$exclude_current = true;
		}

		if ( isset( $attributes['taxtermsTaxonomies'] ) && $attributes['taxtermsTaxonomies'] ) {
			$selected_tax = array();
			foreach ( $attributes['taxtermsTaxonomies'] as $type ) {
				$selected_tax[] = $type['value'];
			}

			$args = array(
				'taxonomy'   => $selected_tax,
				'orderby'    => ( isset( $attributes['taxtermsOrderBy'] ) && $attributes['taxtermsOrderBy'] ) ? $attributes['taxtermsOrderBy'] : 'name',
				'order'      => ( isset( $attributes['taxtermsOrderDirection'] ) && $attributes['taxtermsOrderDirection'] ) ? $attributes['taxtermsOrderDirection'] : 'ASC',
				'hide_empty' => isset( $attributes['taxtermsHideEmpty'] ) && $attributes['taxtermsHideEmpty'] ? filter_var( $attributes['taxtermsHideEmpty'], FILTER_VALIDATE_BOOLEAN ) : false,
				'exclude'    => $excluded,
				'include'    => $included,
			);

			if ( isset( $attributes['taxtermsExcludeChildren'] ) && $attributes['taxtermsExcludeChildren'] ) {
				$args['parent'] = 0;
			}

			$terms = get_terms( $args );

			$count = 1;
			$limit = null;

			if ( isset( $attributes['taxtermsNumber'] ) && $attributes['taxtermsNumber'] ) {
				$limit = $attributes['taxtermsNumber'];
			}

			foreach ( $terms as $index => $term ) {
				if ( ( $exclude_current && ( ! is_archive() || ( isset( $block->context['query_index'] ) && $block->context['query_index'] ) ) && ! has_term( $term->term_id, $term->taxonomy ) ) || ( $exclude_current && is_archive() && ! isset( $block->context['query_index'] ) && get_queried_object()->term_id != $term->term_id ) || ! $exclude_current ) {
					if ( ! $limit || $count <= intval( $limit ) ) {
						$count         = ++$count;
						$block_content = '';
						foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
							$block_content .= ( new WP_Block(
								$inner_block,
								array(
									'postType'       => get_post_type(),
									'postId'         => get_the_ID(),
									'taxterms'       => $term,
									'taxterms_index' => $index + 1,
									'rendered'       => true,
								)
							) )->render( array( 'dynamic' => true ) );
						}
						if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
							$content .= '<li class="splide__slide">' . $block_content . '</li>';
						} else {
							$content .= '<div ' . $mason_class . '>' . $block_content . '</div>';
						}
					}
				}
			}
		} else {
			$args = array(
				'taxonomy'   => array(),
				'orderby'    => ( isset( $attributes['taxtermsOrderBy'] ) && $attributes['taxtermsOrderBy'] ) ? $attributes['taxtermsOrderBy'] : 'name',
				'order'      => ( isset( $attributes['taxtermsOrderDirection'] ) && $attributes['taxtermsOrderDirection'] ) ? $attributes['taxtermsOrderDirection'] : 'ASC',
				'hide_empty' => isset( $attributes['taxtermsHideEmpty'] ) && $attributes['taxtermsHideEmpty'] ? filter_var( $attributes['taxtermsHideEmpty'], FILTER_VALIDATE_BOOLEAN ) : false,
				'exclude'    => $excluded,
				'include'    => $included,
			);

			if ( isset( $attributes['taxtermsExcludeChildren'] ) && $attributes['taxtermsExcludeChildren'] ) {
				$args['parent'] = 0;
			}

			$terms = get_terms( $args );

			$count = 1;
			$limit = null;

			if ( isset( $attributes['taxtermsNumber'] ) && $attributes['taxtermsNumber'] ) {
				$limit = $attributes['taxtermsNumber'];
			}

			foreach ( $terms as $index => $term ) {
				if ( ( $exclude_current && ( ! is_archive() || ( isset( $block->context['query_index'] ) && $block->context['query_index'] ) ) && ! has_term( $term->term_id, $term->taxonomy ) ) || ( $exclude_current && is_archive() && ! isset( $block->context['query_index'] ) && get_queried_object()->term_id != $term->term_id ) || ! $exclude_current ) {
					if ( in_array( $term->taxonomy, $post_taxonomies ) && ( ! $limit || $count <= intval( $limit ) ) ) {
						$count         = ++$count;
						$block_content = '';
						foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
							$block_content .= ( new WP_Block(
								$inner_block,
								array(
									'postType'       => get_post_type(),
									'postId'         => get_the_ID(),
									'taxterms'       => $term,
									'taxterms_index' => $index + 1,
									'rendered'       => true,
								)
							) )->render( array( 'dynamic' => true ) );
						}
						if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
							$content .= '<li class="splide__slide">' . $block_content . '</li>';
						} else {
							$content .= '<div ' . $mason_class . '>' . $block_content . '</div>';
						}
					}
				}
			}
		}
	}
	return addcslashes( $content, '$' );
}
