<?php
/**
 * Cwicly Queries
 *
 * Functions for creating and managing queries
 *
 * @package Cwicly\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Dynamic Query Args Maker
 *
 * Returns the specific dynamic query args
 *
 * @package Cwicly\Functions
 * @version 1.1
 */
class Cwicly_Query_Args {

	/**
	 * The dynamic query args
	 *
	 * @var array
	 */
	protected $final;

	/**
	 * The URL params
	 *
	 * @var array
	 */
	protected $urlparams;

	/**
	 * The block context
	 *
	 * @var array
	 */
	protected $block_context = array();

	/**
	 * The block
	 *
	 * @var array
	 */
	protected $block = null;

	/**
	 * Constructor
	 *
	 * @param array $block_context The block context.
	 * @param array $block The block.
	 */
	public function __construct( $block_context, $block = null ) {
		$this->block_context = $block_context;
		$this->block         = $block;
	}

	/**
	 * URL Check
	 *
	 * @param array  $attributes The block attributes.
	 * @param string $property The property to check.
	 */
	private function dynamic_url_checker( $attributes, $property ) {
		if ( isset( $attributes['group'] ) && 'urlparameter' === $attributes['group'] && isset( $attributes['field'] ) && Cwicly\Helpers::check_if_exists( $attributes['field'] ) ) {
			$this->urlparams[ $property ] = $attributes['field'];
		}
	}

	/**
	 * Return type of value
	 *
	 * @param string $type The type to check.
	 * @param string $value The value to check.
	 */
	private function type_maker( $type, $value ) {
		$final = $value;
		if ( 'string' == $type ) {
			$final = $value;
		} elseif ( 'boolean' == $type ) {
			$bool  = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			$final = $bool ? 1 : 0;
		} elseif ( 'integer' == $type ) {
			$final = intval( $value );
		}

		return $final;
	}

	/**
	 * Cwicly Dynamic Query Args Maker
	 *
	 * Returns the specific dynamic query element
	 *
	 * @param array  $attributes The block attributes.
	 * @param string $post_id The post ID.
	 * @param bool   $array Whether to return an array or not.
	 * @param string $type The type of value to return.
	 */
	private function dynamic_maker( $attributes, $post_id, $array = false, $type = '' ) {
		$return = '';
		if ( isset( $attributes['type'] ) && 'wordpress' === $attributes['type'] ) { // phpcs:ignore WordPress.WP.CapitalPDangit
			if ( 'postid' === $attributes['group'] ) {
				if ( isset( $type ) && 'integer' === $type ) {
					$return = intval( $post_id );
				} elseif ( isset( $type ) && 'string' === $type ) {
					$return = '"' . $post_id . '"';
				} else {
					$return = $post_id;
				}
			} elseif ( 'posttype' === $attributes['group'] ) {
				$return = get_post_type( $post_id );
			} elseif ( isset( $attributes['group'] ) && 'posstatus' === $attributes['group'] ) {
				$return = get_post_status( $post_id );
			} elseif ( isset( $attributes['group'] ) && 'customfield' === $attributes['group'] && isset( $attributes['field'] ) && $attributes['field'] ) {
				$return = get_post_field( $attributes['field'], $post_id );
			} elseif ( isset( $attributes['group'] ) && 'featuredimagetitle' === $attributes['group'] ) {
				$return = get_the_title( $post_id );
			} elseif ( isset( $attributes['group'] ) && 'featuredimagealt' === $attributes['group'] ) {
				$return = get_post_meta( get_post_thumbnail_id( $post_id ), '_wp_attachment_image_alt', true );
			} elseif ( isset( $attributes['group'] ) && 'featuredimagecaption' === $attributes['group'] ) {
				$return = wp_get_attachment_caption( $post_id );
			} elseif ( isset( $attributes['group'] ) && 'authorname' === $attributes['group'] ) {
				$return = get_the_author_meta( 'user_login' );
			} elseif ( isset( $attributes['group'] ) && 'authorcustomfield' === $attributes['group'] && isset( $attributes['field'] ) && $attributes['field'] ) {
				$return = get_user_meta( get_the_author_meta( 'ID' ), $attributes['field'] );
			} elseif ( isset( $attributes['group'] ) && 'authorid' === $attributes['group'] ) {
				$return = get_the_author_meta( 'ID' );
			} elseif ( isset( $attributes['group'] ) && 'userid' === $attributes['group'] ) {
				$return = get_current_user_id();
			} elseif ( isset( $attributes['group'] ) && 'username' === $attributes['group'] ) {
				$return = wp_get_current_user()->user_login;
			} elseif ( isset( $attributes['group'] ) && 'usercustomfield' === $attributes['group'] && isset( $attributes['field'] ) && $attributes['field'] ) {
				$return = get_user_meta( get_current_user_id(), $attributes['field'] );
			} elseif ( isset( $attributes['group'] ) && 'shortcode' === $attributes['group'] && isset( $attributes['field'] ) && $attributes['field'] ) {
				$return = do_shortcode( '[' . $attributes['field'] . ']' );
			} elseif ( isset( $attributes['group'] ) && 'siteoption' === $attributes['group'] && isset( $attributes['field'] ) && $attributes['field'] ) {
				$return = get_option( $attributes['field'] );
			} elseif ( isset( $attributes['group'] ) && 'archiveauthorid' === $attributes['group'] ) {
				if ( is_author() ) {
					$return = get_queried_object_id();
				} else {
					$author = get_queried_object();
					$return = $author->post_author;
				}
			} elseif ( isset( $attributes['group'] ) && 'postterms' === $attributes['group'] ) {
				$terms  = self::custom_taxonomies_terms( $post_id );
				$return = $terms;
			} elseif ( isset( $attributes['group'] ) && 'currenttaxonomyarchive' === $attributes['group'] ) {
				$terms = get_queried_object();
				if ( isset( $terms->taxonomy ) ) {
					$return = $terms->taxonomy;
				}
			} elseif ( isset( $attributes['group'] ) && 'currenttaxonomytermarchive' === $attributes['group'] ) {
				$terms = get_queried_object();
				if ( isset( $terms->term_id ) ) {
					$return = $terms->term_id;
				}
			} elseif ( isset( $attributes['group'] ) && 'urlparameter' === $attributes['group'] && isset( $attributes['field'] ) && $attributes['field'] ) {
				if ( isset( $_GET[ $attributes['field'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$field = sanitize_text_field( wp_unslash( $_GET[ $attributes['field'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( Cwicly\Helpers::check_if_exists( $field ) ) {
						if ( ! is_array( $field ) && $array ) {
							$return = explode( ',', $field );
						} elseif ( $type ) {
							$return = $this->type_maker( $type, $field );
						} else {
							$return = $field;
						}
					}
				}
			}
		} elseif ( 'acf' === $attributes['type'] && isset( $attributes['group'] ) && $attributes['group'] && isset( $attributes['field'] ) && $attributes['field'] ) {
			$field = get_field( $attributes['field'], $post_id, false );
			if ( ! is_array( $field ) && $array ) {
				$field = array( $field );
			}
			if ( isset( $type ) && 'integer' === $type ) {
				$return = intval( $field );
			} elseif ( isset( $type ) && 'string' === $type ) {
				$return = implode( ',', $field );
			} else {
				$return = $field;
			}
		} elseif ( isset( $attributes['type'] ) && 'taxterms' === $attributes['type'] ) {
			if ( $this->block_context && isset( $this->block_context['taxterms'] ) && $this->block_context['taxterms'] ) {
				if ( isset( $attributes['group'] ) && $attributes['group'] ) {
					switch ( $attributes['group'] ) {
						case 'taxtermsTaxSlug':
							if ( is_object( $this->block_context['taxterms'] ) ) {
								$return = $this->block_context['taxterms']->taxonomy;
							} elseif ( is_array( $this->block_context['taxterms'] ) ) {
								$return = $this->block_context['taxterms']['taxonomy'];
							}

							break;
						case 'taxtermsTermId':
							if ( is_object( $this->block_context['taxterms'] ) ) {
								$return = $this->block_context['taxterms']->term_id;
							} elseif ( is_array( $this->block_context['taxterms'] ) ) {
								$return = $this->block_context['taxterms']['term_id'];
							}

							break;
						case 'taxtermsTermSlug':
							if ( is_object( $this->block_context['taxterms'] ) ) {
								$return = $this->block_context['taxterms']->slug;
							} elseif ( is_array( $this->block_context['taxterms'] ) ) {
								$return = $this->block_context['taxterms']['slug'];
							}

							break;
					}
				}
			}
		}
		if ( ! $return && isset( $attributes['fallback'] ) && Cwicly\Helpers::check_if_exists( $attributes['fallback'] ) ) {
			if ( ! is_array( $attributes['fallback'] ) && $array ) {
				$return = explode( ',', $attributes['fallback'] );
			} elseif ( $type ) {
				$return = $this->type_maker( $type, $attributes['fallback'] );
			} else {
				$return = $attributes['fallback'];
			}
		}

		if ( $return && $array && ! is_array( $return ) ) {
			$return = array( $return );
		}

		return $return;
	}

	/**
	 * Component value maker.
	 *
	 * @param string $value Value.
	 *
	 * @return string
	 */
	private function component_value_maker( $value ) {
		$prep = '';
		preg_match( '/!ref=([\w-]+)!/', $value, $ref );
		if ( isset( $ref[1] ) ) {
			$ref = $ref[1];
			if ( isset( $this->block_context['componentProperties'] ) && isset( $this->block_context['componentProperties'][ $ref ] ) ) {
				$parameter = $this->block_context['componentProperties'][ $ref ];
				if ( isset( $parameter['parent'] ) && isset( $parameter['value'] ) ) {
					$parameter_parent   = \Cwicly\Helpers::get_parent_property( $parameter['value'], $this->block );
					$parameter          = array();
					$parameter['value'] = $parameter_parent;
				}
				if ( isset( $parameter['value'] ) && $parameter['value'] && isset( $parameter['value']['maker'] ) && $parameter['value']['maker'] ) {
					if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] && ( ( ! isset( $parameter['value']['dynamic'] ) || ! $parameter['value']['dynamic'] ) ) ) {
						$prep = \Cwicly\Helpers::get_component_option_value_from_id( $parameter['value']['maker'], $ref, $this->block );
					} else {
						$prep = cc_parser( $parameter['value']['maker'], array(), $this->block );
					}
				} elseif ( isset( $parameter['value'] ) && $parameter['value'] && ! isset( $parameter['value']['maker'] ) ) {
					if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] ) {
						$prep = \Cwicly\Helpers::get_component_option_value_from_id( $parameter['value'], $ref, $this->block );
					} else {
						$prep = $parameter['value'];
					}
				}
			} elseif ( isset( $this->block_context['componentMetaProperties'] ) && isset( $this->block_context['componentMetaProperties'][ $ref ] ) && isset( $this->block_context['componentMetaProperties'][ $ref ]['default'] ) ) {
				if ( isset( $this->block_context['componentMetaProperties'][ $ref ]['type'] ) && 'options' === $this->block_context['componentMetaProperties'][ $ref ]['type'] && ( ( ! isset( $this->block_context['componentMetaProperties'][ $ref ]['isDynamic'] ) || ! $this->block_context['componentMetaProperties'][ $ref ]['isDynamic'] ) ) ) {
					$prep = \Cwicly\Helpers::get_component_option_value_from_id( $this->block_context['componentMetaProperties'][ $ref ]['default'], $ref, $this->block );
				} else {
					$prep = $this->block_context['componentMetaProperties'][ $ref ]['default'];
				}
			}
		}
		return $prep;
	}

	/**
	 * Boolean maker.
	 *
	 * @param array  $attribute Attribute.
	 * @param int    $post_id   Post ID.
	 * @param string $type      Type.
	 * @param array  $final     Final.
	 * @param bool   $true      True.
	 * @param bool   $false     False.
	 */
	private function boolean_maker( $attribute, $post_id, $type, &$final, $true = true, $false = false ) {
		if ( $attribute && is_array( $attribute ) ) {
			if ( 'static' === $attribute['source'] && $attribute['field'] ) {
				if ( isset( $attribute['field'] ) && is_bool( $attribute['field'] ) ) {
					$final[ $type ] = $attribute['field'] ? $true : $false;
				}
			} elseif ( $attribute && 'dynamic' === $attribute['source'] ) {
				$dynamic = $this->dynamic_maker( $attribute, $post_id, $false );
				$this->dynamic_url_checker( $attribute, $type );
				if ( $dynamic ) {
					$final[ $type ] = $dynamic;
				}
			}
		} elseif ( isset( $attribute ) && is_bool( $attribute ) ) {
			$final[ $type ] = $attribute ? $true : $false;
		}
	}

	/**
	 * Selector maker.
	 *
	 * @param array  $attribute Attribute.
	 * @param int    $post_id   Post ID.
	 * @param string $type      Type.
	 * @param array  $final     Final.
	 * @param bool   $array     Array.
	 */
	private function selector_maker( $attribute, $post_id, $type, &$final, $array = false, $numeric = false ) {
		if ( is_array( $attribute ) ) {
			if ( $attribute && 'static' === $attribute['source'] && isset( $attribute['field'] ) && $attribute['field'] ) {
				$field = $attribute['field'];
				if ( is_string( $field ) && strpos( $field, '!ref=' ) !== false ) {
					$prep = $this->component_value_maker( $field );

					if ( isset( $prep ) && $prep ) {
						if ( $array && ! is_array( $prep ) ) {
							$final[ $type ] = explode( ',', $prep );

							if ( $numeric ) {
								$final[ $type ] = array_map( 'intval', $final[ $type ] );
							}
						} else {
							$final[ $type ] = $prep;
						}
					}
				} elseif ( is_array( $attribute['field'] ) ) {
					$final_post = array();
					foreach ( $attribute['field'] as $index => $element ) {
						$final_post[] = $element['value'];
					}
					$final[ $type ] = $final_post;
				} elseif ( $array && ! is_array( $attribute['field'] ) ) {
					$final[ $type ] = explode( ',', $attribute['field'] );

					if ( $numeric ) {
						$final[ $type ] = array_map( 'intval', $final[ $type ] );
					}
				} else {
					$final[ $type ] = $attribute['field'];
				}
			} elseif ( $attribute && 'dynamic' === $attribute['source'] ) {
				$dynamic = $this->dynamic_maker( $attribute, $post_id, $array );
				$this->dynamic_url_checker( $attribute, $type );
				if ( $dynamic ) {
					$final[ $type ] = $dynamic;
				}
			}
		}
	}

	/**
	 * Single value maker.
	 *
	 * @param array  $attribute Attribute.
	 * @param int    $post_id   Post ID.
	 * @param string $type      Type.
	 * @param array  $final     Final.
	 * @param bool   $is_numeric Is numeric.
	 * @param bool   $group     Group.
	 * @param bool   $is_boolean Is boolean.
	 */
	private function single_value_maker( $attribute, $post_id, $type, &$final, $is_numeric = false, $group = false, $is_boolean = false ) {
		if ( $attribute && 'static' === $attribute['source'] && isset( $attribute[ $group ? 'group' : 'field' ] ) && \Cwicly\Helpers::check_if_exists( $attribute[ $group ? 'group' : 'field' ] ) ) {
			$value = $attribute[ $group ? 'group' : 'field' ];
			if ( is_string( $value ) && strpos( $value, '!ref=' ) !== false ) {
				$prep = $this->component_value_maker( $value );
				if ( isset( $prep ) && $prep ) {
					$final[ $type ] = $prep;
				}
			} elseif ( is_array( $value ) || is_object( $value ) ) {
				if ( isset( $value['value'] ) ) {
					if ( 'current' === $value['value'] ) {
						$final[ $type ] = $post_id;
					} else {
						$final[ $type ] = $value['value'];
					}
				}
			} elseif ( $is_numeric ) {
				if (
					isset( $value ) &&
					is_numeric( $value )
				) {
					$final[ $type ] = absint( $value );
				}
			} elseif ( $is_boolean ) {
				if ( isset( $value ) ) {
						$final[ $type ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
				}
			} else {
				$final[ $type ] = cc_parser( $attribute[ $group ? 'group' : 'field' ], array(), $this->block, $post_id );
			}
		} elseif ( $attribute && 'dynamic' === $attribute['source'] ) {
			$value = $this->dynamic_maker( $attribute, $post_id );
			$this->dynamic_url_checker( $attribute, $type );
			if ( $is_numeric ) {
				if (
					isset( $value ) &&
					is_numeric( $value )
				) {
					$final[ $type ] = absint( $value );
				} elseif ( isset( $value ) ) {
					$value          = filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
					$final[ $type ] = intval( $value );
				}
			} else {
				$final[ $type ] = $value;
			}
		}
	}

	/**
	 * Query Preparation
	 */
    // phpcs:disable Squiz.Commenting.FunctionComment.Missing
	public function query_preparation(
		$query_type,
		$post_id,
		$query_max_items,
		$query_sticky,
		$query_page,
		$query_post_type,
		$query_per_page,
		$query_offset,
		$query_exclude_current,
		$query_meta_key,
		$query_order_by,
		$query_order,
		$query_include,
		$query_exclude,
		$query_post_parent,
		$query_in_parent,
		$query_not_in_parent,
		$query_taxonomy,
		$query_meta,
		$query_taxonomy_relation,
		$query_meta_relation,
		$query_password,
		$query_post_password,
		$query_author,
		$query_author_name,
		$query_author_in,
		$query_author_not_in,
		$query_search,
		$query_post_status,
		$query_comment_count,
		$query_comment_compare,
		$query_perm,
		$query_mime_type,
		$query_date,
		$query_date_relation,
		$query_taxonomies,
		$query_object_i_ds,
		$query_hide_empty,
		$query_count,
		$query_pad_count,
		$query_exclude_tree,
		$query_number,
		$query_fields,
		$query_name,
		$query_slug,
		$query_hierarchical,
		$query_name_like,
		$query_description_like,
		$query_get,
		$query_child_of,
		$query_parent,
		$query_childless,
		$query_role,
		$query_role_in,
		$query_role_not_in,
		$query_blog_id,
		$query_search_column,
		$query_who,
		$query_total_count,
		$query_has_published,
		$query_comment_parent,
		$query_comment_in_parent,
		$query_comment_not_parent,
		$query_comment_post_id,
		$query_comment_id,
		$query_comment_not_id,
		$query_comment_include_unapproved,
		$query_comment_karma,
		$query_author_email,
		$query_author_url,
		$query_comment_author_in,
		$query_comment_author_not_in,
		$query_woo_type,
		$query_woo_parent_exclude,
		$query_woo_sku,
		$query_woo_tag,
		$query_woo_category,
		$query_woo_width,
		$query_woo_height,
		$query_woo_weight,
		$query_woo_length,
		$query_woo_price,
		$query_woo_regular_price,
		$query_woo_sale_price,
		$query_woo_total_sales,
		$query_woo_virtual,
		$query_woo_downloadable,
		$query_woo_featured,
		$query_woo_sold_individually,
		$query_woo_manage_stock,
		$query_woo_reviews_allowed,
		$query_woo_backorders,
		$query_woo_visibility,
		$query_woo_stock_quantity,
		$query_woo_stock_status,
		$query_woo_tax_status,
		$query_woo_tax_class,
		$query_woo_shipping_class,
		$query_woo_download_limit,
		$query_woo_download_expiry,
		$query_woo_average_rating,
		$query_woo_review_count,
		$query_woo_date_created,
		$query_woo_date_modified,
		$query_woo_date_on_sale_from,
		$query_woo_date_on_sale_to,
		$returnparams
	) {
        // phpcs:enable Squiz.Commenting.FunctionComment.Missing
		$this->selector_maker( $query_max_items, $post_id, 'maxitems', $this->final );

		if ( 'posts' === $query_type ) {
			$this->selector_maker( $query_post_type, $post_id, 'post_type', $this->final, true );
			$this->selector_maker( $query_post_status, $post_id, 'post_status', $this->final );
			$this->selector_maker( $query_author_in, $post_id, 'author__in', $this->final );
			$this->selector_maker( $query_author_not_in, $post_id, 'author__not_in', $this->final );
			$this->single_value_maker( $query_post_parent, $post_id, 'post_parent', $this->final, true );
			$this->selector_maker( $query_in_parent, $post_id, 'post_parent__in', $this->final, true );
			$this->selector_maker( $query_not_in_parent, $post_id, 'post_parent__not_in', $this->final, true );
			$this->selector_maker( $query_include, $post_id, 'post__in', $this->final, true, true );
			$this->selector_maker( $query_exclude, $post_id, 'post__not_in', $this->final, true, true );
			$this->single_value_maker( $query_mime_type, $post_id, 'post_mime_type', $this->final );
			$this->single_value_maker( $query_perm, $post_id, 'perm', $this->final );
			$this->single_value_maker( $query_author_name, $post_id, 'author_name', $this->final );
			$this->single_value_maker( $query_author, $post_id, 'author', $this->final );
			$this->single_value_maker( $query_search, $post_id, 's', $this->final );
			$this->single_value_maker( $query_password, $post_id, 'has_password', $this->final, false, false, true );
			$this->single_value_maker( $query_post_password, $post_id, 'post_password', $this->final );
			$this->single_value_maker( $query_per_page, $post_id, 'posts_per_page', $this->final, true );
			$this->final['ignore_sticky_posts'] = $query_sticky ? false : true;
			if ( $query_exclude_current && $post_id ) {
				$this->final['post__not_in'][] = $post_id;
			}
			if ( $query_comment_compare && 'static' === $query_comment_compare['source'] && isset( $query_comment_compare['field'] ) && Cwicly\Helpers::check_if_exists( $query_comment_compare['field'] ) ) {
				$this->final['comment_count']['compare'] = $query_comment_compare['field'];
			} elseif ( $query_comment_compare && 'dynamic' === $query_comment_compare['source'] ) {
				$this->final['comment_count']['compare'] = $this->dynamic_maker( $query_comment_compare, $post_id );
				$this->dynamic_url_checker( $attribute, $type );
			}
			if ( $query_comment_count && 'static' === $query_comment_count['source'] && isset( $query_comment_count['field'] ) && Cwicly\Helpers::check_if_exists( $query_comment_count['field'] ) ) {
				$this->final['comment_count']['value'] = $query_comment_count['field'];
			} elseif ( $query_comment_count && 'dynamic' === $query_comment_count['source'] ) {
				$this->final['comment_count']['value'] = $this->dynamic_maker( $query_comment_count, $post_id );
				$this->dynamic_url_checker( $attribute, $type );
			}
		}

		if ( 'terms' === $query_type ) {
			$this->selector_maker( $query_taxonomies, $post_id, 'taxonomy', $this->final, true );
			$this->selector_maker( $query_exclude_tree, $post_id, 'exclude_tree', $this->final );
			$this->selector_maker( $query_name, $post_id, 'name', $this->final );
			$this->selector_maker( $query_slug, $post_id, 'slug', $this->final );
			$this->selector_maker( $query_include, $post_id, 'include', $this->final );
			$this->selector_maker( $query_exclude, $post_id, 'exclude', $this->final );
			$this->single_value_maker( $query_per_page, $post_id, 'number', $this->final );
			$this->single_value_maker( $query_name_like, $post_id, 'name__like', $this->final );
			$this->single_value_maker( $query_description_like, $post_id, 'description__like', $this->final );
			$this->single_value_maker( $query_search, $post_id, 'search', $this->final );
			$this->single_value_maker( $query_get, $post_id, 'get', $this->final );
			$this->single_value_maker( $query_child_of, $post_id, 'child_of', $this->final );
			$this->single_value_maker( $query_parent, $post_id, 'parent', $this->final, true );
			$this->final['childless']    = $query_childless ? true : false;
			$this->final['hide_empty']   = $query_hide_empty ? true : false;
			$this->final['count']        = $query_count ? true : false;
			$this->final['pad_counts']   = $query_pad_count ? true : false;
			$this->final['hierarchical'] = $query_hierarchical ? true : false;
		}

		if ( 'users' === $query_type ) {
			$this->single_value_maker( $query_search, $post_id, 'search', $this->final );
			$this->selector_maker( $query_role, $post_id, 'role', $this->final );
			$this->selector_maker( $query_role_in, $post_id, 'role__in', $this->final );
			$this->selector_maker( $query_role_not_in, $post_id, 'role__not_in', $this->final );
			$this->selector_maker( $query_include, $post_id, 'include', $this->final );
			$this->selector_maker( $query_exclude, $post_id, 'exclude', $this->final );
			$this->selector_maker( $query_search_column, $post_id, 'search_columns', $this->final );
			$this->single_value_maker( $query_per_page, $post_id, 'number', $this->final );
			$this->single_value_maker( $query_blog_id, $post_id, 'blog_id', $this->final );
			$this->single_value_maker( $query_who, $post_id, 'who', $this->final );
			$this->final['count_total'] = $query_total_count ? true : false;
		}

		if ( 'comments' === $query_type ) {
			$this->single_value_maker( $query_per_page, $post_id, 'number', $this->final );
			$this->single_value_maker( $query_comment_karma, $post_id, 'karma', $this->final );
			$this->selector_maker( $query_comment_id, $post_id, 'comment__in', $this->final );
			$this->selector_maker( $query_comment_not_id, $post_id, 'comment__not_in', $this->final );
			$this->single_value_maker( $query_author_email, $post_id, 'author_email', $this->final );
			$this->single_value_maker( $query_author_url, $post_id, 'author_url', $this->final );
			$this->selector_maker( $query_post_status, $post_id, 'status', $this->final, true );
			$this->single_value_maker( $query_search, $post_id, 'search', $this->final );
			$this->single_value_maker( $query_comment_post_id, $post_id, 'post_id', $this->final );
			$this->boolean_maker( $query_comment_include_unapproved, $post_id, 'include_unapproved', $this->final );
			$this->selector_maker( $query_post_status, $post_id, 'status', $this->final, true );
			$this->single_value_maker( $query_comment_karma, $post_id, 'karma', $this->final );
			$this->selector_maker( $query_comment_parent, $post_id, 'parent', $this->final );
			$this->selector_maker( $query_comment_not_parent, $post_id, 'parent__in', $this->final );
			$this->selector_maker( $query_comment_in_parent, $post_id, 'parent__not_in', $this->final );
			$this->selector_maker( $query_comment_author_in, $post_id, 'author__in', $this->final );
			$this->selector_maker( $query_author_in, $post_id, 'post_author__in', $this->final );
			$this->selector_maker( $query_comment_author_not_in, $post_id, 'author__not_in', $this->final );
			$this->selector_maker( $query_author_not_in, $post_id, 'post_author__not_in', $this->final );
			$this->selector_maker( $query_include, $post_id, 'post__in', $this->final );
			$this->selector_maker( $query_exclude, $post_id, 'post__not_in', $this->final );
			$this->boolean_maker( $query_hierarchical, $post_id, 'hierarchical', $this->final, 'flat', 'threaded' );
		}

		if ( 'products' === $query_type ) {
			$this->selector_maker( $query_post_status, $post_id, 'status', $this->final, true );
			$this->selector_maker( $query_woo_type, $post_id, 'type', $this->final, true );
			$this->selector_maker( $query_include, $post_id, 'include', $this->final, true );
			$this->selector_maker( $query_exclude, $post_id, 'exclude', $this->final, true );
			$this->single_value_maker( $query_parent, $post_id, 'parent', $this->final, true );
			$this->single_value_maker( $query_woo_parent_exclude, $post_id, 'parent_exclude', $this->final, true );
			$this->single_value_maker( $query_per_page, $post_id, 'limit', $this->final, true );
			$this->single_value_maker( $query_woo_sku, $post_id, 'sku', $this->final );
			$this->selector_maker( $query_woo_tag, $post_id, 'tag', $this->final );
			$this->single_value_maker( $query_search, $post_id, 's', $this->final );
			$this->selector_maker( $query_woo_category, $post_id, 'category', $this->final, true );
			$this->single_value_maker( $query_woo_width, $post_id, 'width', $this->final, true );
			$this->single_value_maker( $query_woo_height, $post_id, 'height', $this->final, true );
			$this->single_value_maker( $query_woo_length, $post_id, 'length', $this->final, true );
			$this->single_value_maker( $query_woo_weight, $post_id, 'weight', $this->final, true );
			$this->single_value_maker( $query_woo_price, $post_id, 'price', $this->final, true );
			$this->single_value_maker( $query_woo_regular_price, $post_id, 'regular_price', $this->final, true );
			$this->single_value_maker( $query_woo_sale_price, $post_id, 'sale_price', $this->final, true );
			$this->single_value_maker( $query_woo_total_sales, $post_id, 'total_sales', $this->final, true );
			$this->boolean_maker( $query_woo_virtual, $post_id, 'virtual', $this->final );
			$this->boolean_maker( $query_woo_downloadable, $post_id, 'downloadable', $this->final );
			$this->boolean_maker( $query_woo_featured, $post_id, 'featured', $this->final );
			$this->boolean_maker( $query_woo_sold_individually, $post_id, 'sold_individually', $this->final );
			$this->boolean_maker( $query_woo_manage_stock, $post_id, 'manage_stock', $this->final );
			$this->boolean_maker( $query_woo_reviews_allowed, $post_id, 'reviews_allowed', $this->final );
			$this->single_value_maker( $query_woo_backorders, $post_id, 'backorders', $this->final );
			$this->single_value_maker( $query_woo_visibility, $post_id, 'visibility', $this->final );
			$this->single_value_maker( $query_woo_stock_quantity, $post_id, 'stock_quantity', $this->final, true );
			$this->single_value_maker( $query_woo_stock_status, $post_id, 'stock_status', $this->final );
			$this->single_value_maker( $query_woo_tax_status, $post_id, 'tax_status', $this->final );
			$this->single_value_maker( $query_woo_tax_class, $post_id, 'tax_class', $this->final );
			$this->single_value_maker( $query_woo_shipping_class, $post_id, 'shipping_class', $this->final );
			$this->single_value_maker( $query_woo_download_limit, $post_id, 'download_limit', $this->final, true );
			$this->single_value_maker( $query_woo_download_expiry, $post_id, 'download_expiry', $this->final, true );
			$this->single_value_maker( $query_woo_average_rating, $post_id, 'average_rating', $this->final, true );
			$this->single_value_maker( $query_woo_review_count, $post_id, 'review_count', $this->final, true );
			$this->single_value_maker( $query_woo_date_created, $post_id, 'date_created', $this->final );
			$this->single_value_maker( $query_woo_date_modified, $post_id, 'date_modified', $this->final );
			$this->single_value_maker( $query_woo_date_on_sale_from, $post_id, 'date_on_sale_from', $this->final );
			$this->single_value_maker( $query_woo_date_on_sale_to, $post_id, 'date_on_sale_to', $this->final );
		}

		if ( $query_taxonomy ) {
			foreach ( $query_taxonomy as $index => $tax ) {
				if ( $tax['multiple'] ) {
					if ( $tax['relation'] ) {
						$query_tax_count = count( $tax['tax_query'] );
						if ( $query_tax_count >= 2 ) {
							$this->final['tax_query'][ $index ]['relation'] = $tax['relation'];
						}
					}
					if ( $tax['tax_query'] ) {
						foreach ( $tax['tax_query'] as $indexer => $taxer ) {
							foreach ( $taxer as $key => $tax_valuer ) {
								if ( 'title' != $key ) {
									if ( is_array( $tax_valuer ) && $tax_valuer && 'static' === $tax_valuer['source'] && isset( $tax_valuer['field'] ) && Cwicly\Helpers::check_if_exists( $tax_valuer['field'] ) ) {
										$field = $tax_valuer['field'];
										if ( is_string( $field ) && strpos( $field, '!ref=' ) !== false ) {
											$prep = $this->component_value_maker( $field );

											if ( strpos( $prep, ',' ) !== false ) {
												$prep = explode( ',', $prep );
											}

											$this->final['tax_query'][ $index ][ $indexer ][ $key ] = $prep;
										} elseif ( is_array( $tax_valuer['field'] ) ) {
											$final_post = array();
											foreach ( $tax_valuer['field'] as $indexerer => $post ) {
												if ( is_array( $post ) ) {
													$final_post[] = $post['value'];
												} else {
													$final_post[] = $post;
												}
											}
											$this->final['tax_query'][ $index ][ $indexer ][ $key ] = $final_post;
										} elseif ( is_string( $tax_valuer['field'] ) ) {
											$this->final['tax_query'][ $index ][ $indexer ][ $key ] = $tax_valuer['field'];
										} elseif ( is_bool( $tax_valuer['field'] ) ) {
											$this->final['tax_query'][ $index ][ $indexer ][ $key ] = $tax_valuer['field'] ? true : false;
										}
									} elseif ( is_array( $tax_valuer ) && $tax_valuer && 'dynamic' === $tax_valuer['source'] ) {
										if ( 'terms' === $key ) {
											$this->final['tax_query'][ $index ][ $indexer ][ $key ] = $this->dynamic_maker( $tax_valuer, $post_id, true );
											$this->dynamic_url_checker( $tax_valuer, $key );
										} else {
											$this->final['tax_query'][ $index ][ $indexer ][ $key ] = $this->dynamic_maker( $tax_valuer, $post_id, false );
											$this->dynamic_url_checker( $tax_valuer, $key );
										}
									} elseif ( is_string( $tax_valuer ) && 'multiple' != $key ) {
										$this->final['tax_query'][ $index ][ $indexer ][ $key ] = $tax_valuer;
									} elseif ( is_bool( $tax_valuer ) ) {
										$this->final['tax_query'][ $index ][ $indexer ][ $key ] = $tax_valuer ? 'true' : 'false';
									}
								}
							}
						}
					}
				} else {
					if ( $query_taxonomy_relation ) {
						$query_tax_count = count( $query_taxonomy );
						if ( $query_tax_count >= 2 ) {
							$this->final['tax_query']['relation'] = $query_taxonomy_relation;
						}
					}
					foreach ( $tax as $key => $tax_value ) {
						if ( is_array( $tax_value ) ) {
							if ( $tax_value && isset( $tax_value['source'] ) && 'static' === $tax_value['source'] && isset( $tax_value['field'] ) && Cwicly\Helpers::check_if_exists( $tax_value['field'] ) ) {
								$field = $tax_value['field'];
								if ( is_string( $field ) && strpos( $field, '!ref=' ) !== false ) {
									$prep = $this->component_value_maker( $field );

									if ( 'terms' === $key ) {
										if ( strpos( $prep, ',' ) !== false ) {
											$prep = explode( ',', $prep );
										}
									}

									$this->final['tax_query'][ $index ][ $key ] = $prep;

								} elseif ( is_array( $tax_value['field'] ) ) {
									$final_post = array();
									foreach ( $tax_value['field'] as $indexer => $post ) {
										if ( is_array( $post ) ) {
											$final_post[] = $post['value'];
										} else {
											$final_post[] = $post;
										}
									}
									$this->final['tax_query'][ $index ][ $key ] = $final_post;
								} elseif ( is_string( $tax_value['field'] ) ) {
									$this->final['tax_query'][ $index ][ $key ] = $tax_value['field'];
								} elseif ( is_bool( $tax_value['field'] ) ) {
									$this->final['tax_query'][ $index ][ $key ] = $tax_value['field'] ? true : false;
								}
							} elseif ( $tax_value && isset( $tax_value['source'] ) && 'dynamic' === $tax_value['source'] ) {
								if ( 'terms' === $key ) {
									$dynamic = $this->dynamic_maker( $tax_value, $post_id, true );
									$this->dynamic_url_checker( $tax_value, 'tax_query|' . $index . '|' . $key . '' );
									if ( $dynamic ) {
										$this->final['tax_query'][ $index ][ $key ] = $dynamic;
									}
								} else {
									$dynamic = $this->dynamic_maker( $tax_value, $post_id );
									$this->dynamic_url_checker( $tax_value, 'tax_query|' . $index . '|' . $key . '' );
									if ( $dynamic ) {
										$this->final['tax_query'][ $index ][ $key ] = $dynamic;
									}
								}
							}
						} elseif ( is_string( $tax_value ) && 'multiple' != $key && 'relation' != $key ) {
							$this->final['tax_query'][ $index ][ $key ] = $tax_value;
						} elseif ( is_bool( $tax_value ) && 'multiple' != $key ) {
							$this->final['tax_query'][ $index ][ $key ] = $tax_value ? true : false;
						}
					}
				}
			}
		}
		if ( isset( $this->final['tax_query'] ) && $this->final['tax_query'] ) {
			foreach ( $this->final['tax_query'] as $tax_index => $element ) {
				if ( isset( $element['terms'] ) && $element['terms'] ) {
				} elseif ( isset( $element['removeNoTerms'] ) && $element['removeNoTerms'] ) {
					array_splice( $this->final['tax_query'], $tax_index, 1 );
				}
			}
		}
		if ( $query_meta ) {
			foreach ( $query_meta as $index => $tax ) {
				if ( $tax['relation'] ) {
					$query_tax_count = count( $tax['meta_query'] );
					if ( $query_tax_count >= 2 ) {
						$this->final['meta_query'][ $index ][0]['relation'] = $tax['relation'];
					}
				}
				if ( $tax['meta_query'] ) {
					foreach ( $tax['meta_query'] as $indexer => $taxer ) {
						foreach ( $taxer as $key => $tax_valuer ) {
							if ( 'title' != $key ) {
								if ( $tax_valuer && isset( $tax_valuer['source'] ) && 'static' === $tax_valuer['source'] && isset( $tax_valuer['field'] ) && Cwicly\Helpers::check_if_exists( $tax_valuer['field'] ) ) {
									$field = $tax_valuer['field'];
									if ( is_string( $field ) && strpos( $field, '!ref=' ) !== false ) {
										$prep = $this->component_value_maker( $field );

										if ( strpos( $prep, ',' ) !== false ) {
											$prep = explode( ',', $prep );
										}

										$this->final['meta_query'][ $index ][0][ $indexer ][ $key ] = $prep;
									} elseif ( is_array( $tax_valuer['field'] ) ) {
										foreach ( $tax_valuer['field'] as $index => $post ) {
											$this->final['meta_query'][ $index ][0][ $indexer ][ $key ] = $post['value'];
										}
									} elseif ( is_string( $tax_valuer['field'] ) ) {
										$this->final['meta_query'][ $index ][0][ $indexer ][ $key ] = $tax_valuer['field'];
									} elseif ( is_bool( $tax_valuer['field'] ) ) {
										$this->final['meta_query'][ $index ][0][ $indexer ][ $key ] = $tax_valuer['field'] ? true : false;
									}
								} elseif ( is_array( $tax_valuer ) && $tax_valuer && 'dynamic' === $tax_valuer['source'] ) {
									$this->final['meta_query'][ $index ][0][ $indexer ][ $key ] = $this->dynamic_maker( $tax_valuer, $post_id );
									$this->dynamic_url_checker( $tax_valuer, $key );
								} elseif ( is_string( $tax_valuer ) && 'multiple' != $key ) {
									$this->final['meta_query'][ $index ][0][ $indexer ][ $key ] = $tax_valuer;
								} elseif ( is_bool( $tax_valuer ) ) {
									$this->final['meta_query'][ $index ][0][ $indexer ][ $key ] = $tax_valuer ? 'true' : 'false';
								}
							}
						}
					}
				}
				if ( $query_meta_relation ) {
					$query_tax_count = count( $query_meta );
					if ( $query_tax_count >= 2 ) {
						$this->final['meta_query']['relation'] = $query_meta_relation;
					}
				}
				foreach ( $tax as $key => $tax_value ) {
					if ( is_array( $tax_value ) && isset( $tax_value['source'] ) && $tax_value && 'static' === $tax_value['source'] && isset( $tax_value['field'] ) && Cwicly\Helpers::check_if_exists( $tax_value['field'] ) ) {
						$field = $tax_value['field'];
						if ( isset( $tax_value['formatType'] ) && $tax_value['formatType'] ) {
							$this->final['meta_query'][ $index ][ $key ] = $this->type_maker( $tax_value['formatType'], $tax_value['field'] );
						} elseif ( is_string( $field ) && strpos( $field, '!ref=' ) !== false ) {
							$prep = $this->component_value_maker( $field );

							if ( strpos( $prep, ',' ) !== false ) {
								$prep = explode( ',', $prep );
							}

							$this->final['meta_query'][ $index ][ $key ] = $prep;

						} elseif ( is_array( $tax_value['field'] ) ) {
							foreach ( $tax_value['field'] as $index => $post ) {
								$this->final['meta_query'][ $index ][ $key ] = $post['value'];
							}
						} elseif ( is_string( $tax_value['field'] ) ) {
							$this->final['meta_query'][ $index ][ $key ] = $tax_value['field'];
						} elseif ( is_bool( $tax_value['field'] ) ) {
							$this->final['meta_query'][ $index ][ $key ] = $tax_value['field'] ? true : false;
						}
					} elseif ( is_array( $tax_value ) && isset( $tax_value['source'] ) && $tax_value && 'dynamic' === $tax_value['source'] ) {
						$type = '';
						if ( isset( $tax_value['formatType'] ) && $tax_value['formatType'] ) {
							$type = $tax_value['formatType'];
						}
						$this->final['meta_query'][ $index ][ $key ] = $this->dynamic_maker( $tax_value, $post_id, false, $type );
						$this->dynamic_url_checker( $tax_value, 'meta_query|' . $index . '|' . $key . '' );
					} elseif ( is_string( $tax_value ) && 'multiple' != $key && 'relation' != $key ) {
						$this->final['meta_query'][ $index ][ $key ] = $tax_value;
					} elseif ( is_bool( $tax_value ) && 'multiple' != $key ) {
						$this->final['meta_query'][ $index ][ $key ] = $tax_value ? true : false;
					}
				}
			}
		}
		if ( $query_date ) {
			foreach ( $query_date as $index => $tax ) {
				if ( $tax['multiple'] ) {
					if ( $tax['relation'] ) {
						$query_tax_count = count( $tax['date_query'] );
						if ( $query_tax_count >= 2 ) {
							$this->final['date_query'][ $index ]['relation'] = $tax['relation'];
						}
					}
					if ( $tax['date_query'] ) {
						foreach ( $tax['date_query'] as $indexer => $taxer ) {
							foreach ( $taxer as $key => $tax_valuer ) {
								if ( 'title' != $key ) {
									if ( 'before' === $key || 'after' === $key ) {
										foreach ( $tax_valuer as $ba_key => $ba_value ) {
											if ( is_array( $ba_value ) && $ba_value && 'static' === $ba_value['source'] && isset( $ba_value['field'] ) && Cwicly\Helpers::check_if_exists( $ba_value['field'] ) ) {
												if ( is_array( $ba_value['field'] ) ) {
													foreach ( $ba_value['field'] as $index => $post ) {
														$this->final['date_query'][ $index ][ $indexer ][ $key ] = $post['value'];
													}
												} elseif ( is_string( $ba_value['field'] ) ) {
													$this->final['date_query'][ $index ][ $indexer ][ $key ] = $ba_value['field'];
												} elseif ( is_bool( $ba_value['field'] ) ) {
													$this->final['date_query'][ $index ][ $indexer ][ $key ] = $ba_value['field'] ? true : false;
												}
											} elseif ( is_array( $ba_value ) && $ba_value && 'dynamic' === $ba_value['source'] ) {
												$date_value = $this->dynamic_maker( $ba_value, $post_id );
												if ( is_int( $date_value ) ) {
													$this->final['date_query'][ $index ][ $indexer ][ $key ] = $date_value;
												} else {
													$this->final['date_query'][ $index ][ $indexer ][ $key ] = intval( $date_value );
												}
												$this->dynamic_url_checker( $ba_value, 'date_query|' . $index . '|' . $ba_key . '' );
											} elseif ( is_string( $ba_value ) && 'multiple' != $ba_key && 'relation' != $ba_key ) {
												$this->final['date_query'][ $index ][ $indexer ][ $key ] = $ba_value;
											} elseif ( is_bool( $ba_value ) && 'multiple' != $ba_key ) {
												$this->final['date_query'][ $index ][ $indexer ][ $key ] = $ba_value ? true : false;
											}
										}
									} elseif ( $tax_valuer && isset( $tax_valuer['source'] ) && 'static' === $tax_valuer['source'] && isset( $tax_valuer['field'] ) && Cwicly\Helpers::check_if_exists( $tax_valuer['field'] ) ) {
										if ( is_array( $tax_valuer['field'] ) ) {
											foreach ( $tax_valuer['field'] as $index => $post ) {
												$this->final['date_query'][ $index ][ $indexer ][ $key ] = $post['value'];
											}
										} elseif ( is_string( $tax_valuer['field'] ) ) {
											$this->final['date_query'][ $index ][ $indexer ][ $key ] = $tax_valuer['field'];
										} elseif ( is_bool( $tax_valuer['field'] ) ) {
											$this->final['date_query'][ $index ][ $indexer ][ $key ] = $tax_valuer['field'] ? true : false;
										}
									} elseif ( is_array( $tax_valuer ) && $tax_valuer && 'dynamic' === $tax_valuer['source'] ) {
										$date_value = $this->dynamic_maker( $tax_valuer, $post_id );
										if ( is_int( $date_value ) ) {
											$this->final['date_query'][ $index ][ $indexer ][ $key ] = $date_value;
										} else {
											$this->final['date_query'][ $index ][ $indexer ][ $key ] = intval( $date_value );
										}
										$this->dynamic_url_checker( $tax_value, 'date_query|' . $index . '|' . $key . '' );
									} elseif ( is_string( $tax_valuer ) && 'multiple' != $key ) {
										$this->final['date_query'][ $index ][ $indexer ][ $key ] = $tax_valuer;
									} elseif ( is_bool( $tax_valuer ) ) {
										$this->final['date_query'][ $index ][ $indexer ][ $key ] = $tax_valuer ? 'true' : 'false';
									}
								}
							}
						}
					}
				} else {
					if ( $query_date_relation ) {
						$query_tax_count = count( $query_date );
						if ( $query_tax_count >= 2 ) {
							$this->final['date_query']['relation'] = $query_date_relation;
						}
					}
					foreach ( $tax as $key => $tax_value ) {
						if ( 'before' === $key || 'after' === $key ) {
							foreach ( $tax_value as $ba_key => $ba_value ) {
								if ( is_array( $ba_value ) && $ba_value && 'static' === $ba_value['source'] && isset( $ba_value['field'] ) && Cwicly\Helpers::check_if_exists( $ba_value['field'] ) ) {
									if ( is_array( $ba_value['field'] ) ) {
										foreach ( $ba_value['field'] as $index => $post ) {
											$this->final['date_query'][ $index ][ $key ][ $ba_key ] = $post['value'];
										}
									} elseif ( is_string( $ba_value['field'] ) ) {
										$this->final['date_query'][ $index ][ $key ][ $ba_key ] = $ba_value['field'];
									} elseif ( is_bool( $ba_value['field'] ) ) {
										$this->final['date_query'][ $index ][ $key ][ $ba_key ] = $ba_value['field'] ? true : false;
									}
								} elseif ( is_array( $ba_value ) && $ba_value && 'dynamic' === $ba_value['source'] ) {
									$date_value = $this->dynamic_maker( $ba_value, $post_id );
									if ( is_int( $date_value ) ) {
										$this->final['date_query'][ $index ][ $key ][ $ba_key ] = $date_value;
									} else {
										$this->final['date_query'][ $index ][ $key ][ $ba_key ] = intval( $date_value );
									}
									$this->dynamic_url_checker( $ba_value, 'date_query|' . $index . '|' . $ba_key . '' );
								} elseif ( is_string( $ba_value ) && 'multiple' != $ba_key && 'relation' != $ba_key ) {
									$this->final['date_query'][ $index ][ $key ][ $ba_key ] = $ba_value;
								} elseif ( is_bool( $ba_value ) && 'multiple' != $ba_key ) {
									$this->final['date_query'][ $index ][ $key ][ $ba_key ] = $ba_value ? true : false;
								}
							}
						} elseif ( is_array( $tax_value ) && $tax_value && 'static' === $tax_value['source'] && isset( $tax_value['field'] ) && Cwicly\Helpers::check_if_exists( $tax_value['field'] ) ) {
							if ( is_array( $tax_value['field'] ) ) {
								foreach ( $tax_value['field'] as $index => $post ) {
									$this->final['date_query'][ $index ][ $key ] = $post['value'];
								}
							} elseif ( is_string( $tax_value['field'] ) ) {
								$this->final['date_query'][ $index ][ $key ] = $tax_value['field'];
							} elseif ( is_bool( $tax_value['field'] ) ) {
								$this->final['date_query'][ $index ][ $key ] = $tax_value['field'] ? true : false;
							}
						} elseif ( is_array( $tax_value ) && $tax_value && 'dynamic' === $tax_value['source'] ) {
							$this->final['date_query'][ $index ][ $key ] = $this->dynamic_maker( $tax_value, $post_id );
							$this->dynamic_url_checker( $tax_value, 'date_query|' . $index . '|' . $key . '' );
						} elseif ( is_string( $tax_value ) && 'multiple' != $key && 'relation' != $key ) {
							$this->final['date_query'][ $index ][ $key ] = $tax_value;
						} elseif ( is_bool( $tax_value ) && 'multiple' != $key ) {
							$this->final['date_query'][ $index ][ $key ] = $tax_value ? true : false;
						}
					}
				}
			}
		}

		$this->single_value_maker( $query_order_by, $post_id, 'orderby', $this->final );
		$this->single_value_maker( $query_meta_key, $post_id, 'meta_key', $this->final );
		$this->single_value_maker( $query_order, $post_id, 'order', $this->final );
		$this->single_value_maker( $query_offset, $post_id, 'offset', $this->final, true );
		$this->single_value_maker( $query_page, $post_id, 'paged', $this->final );
		$this->single_value_maker( $query_page, $post_id, 'page', $this->final );
		if ( $returnparams ) {
			return array(
				'args'   => $this->final,
				'params' => $this->urlparams,
			);
		} else {
			return $this->final;
		}
	}

	/**
	 * Get custom taxonomies terms
	 *
	 * @param int $post_id Post ID.
	 */
	public static function custom_taxonomies_terms( $post_id ) {
		// https://stackoverflow.com/questions/15502811/display-current-post-custom-taxonomy-in-wordpress .
		global $post;

		$query = null;
		if ( isset( $post->ID ) ) {
			$query = $post;
		} elseif ( $post_id ) {
			$query = $post_id;
		} else {
			$query = get_the_ID();
		}

		$final = array();
		if ( $query ) {
			$post_type = get_post_type( $query );
			// Get post type taxonomies.
			$taxonomies = get_object_taxonomies( $post_type );
			$final      = array();
			foreach ( $taxonomies as $taxonomy ) {
				// Get the terms related to post.
				$terms = get_the_terms( $post, $taxonomy );
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						$final[] = $term->term_id;
					}
				}
			}
		}
		return $final;
	}
}
