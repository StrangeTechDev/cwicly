<?php
/**
 * Query Main
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Query Class.
 */
class Query {
	/**
	 * Cwicly Helpers
	 *
	 * Query Preparation for Arguments on Frontend
	 *
	 * @param array  $attributes The attributes.
	 * @param array  $returnparams The return params.
	 * @param object $block The block object.
	 */
	public static function front_args( $attributes, $returnparams, $block ) {
		if ( ! is_admin() ) { // Needed otherwise error on the backend.
			$query_type = $attributes['queryType'];
			$post_id    = get_the_ID();

			$query_max_items = isset( $attributes['queryMaxItems'] ) ? $attributes['queryMaxItems'] : '';

			$query_sticky            = isset( $attributes['querySticky'] ) ? $attributes['querySticky'] : '';
			$query_page              = isset( $attributes['queryPage'] ) ? $attributes['queryPage'] : '';
			$query_post_type         = isset( $attributes['queryPostType'] ) ? $attributes['queryPostType'] : '';
			$query_per_page          = isset( $attributes['queryPerPage'] ) ? $attributes['queryPerPage'] : '';
			$query_offset            = isset( $attributes['queryOffset'] ) ? $attributes['queryOffset'] : '';
			$query_exclude_current   = isset( $attributes['queryExcludeCurrent'] ) ? $attributes['queryExcludeCurrent'] : '';
			$query_order_by          = isset( $attributes['queryOrderBy'] ) ? $attributes['queryOrderBy'] : '';
			$query_meta_key          = isset( $attributes['queryMetaKey'] ) ? $attributes['queryMetaKey'] : '';
			$query_order             = isset( $attributes['queryOrder'] ) ? $attributes['queryOrder'] : '';
			$query_include           = isset( $attributes['queryInclude'] ) ? $attributes['queryInclude'] : '';
			$query_exclude           = isset( $attributes['queryExclude'] ) ? $attributes['queryExclude'] : '';
			$query_post_parent       = isset( $attributes['queryPostParent'] ) ? $attributes['queryPostParent'] : '';
			$query_in_parent         = isset( $attributes['queryInParent'] ) ? $attributes['queryInParent'] : '';
			$query_not_in_parent     = isset( $attributes['queryNotInParent'] ) ? $attributes['queryNotInParent'] : '';
			$query_taxonomy          = isset( $attributes['queryTaxonomy'] ) ? $attributes['queryTaxonomy'] : '';
			$query_meta              = isset( $attributes['queryMeta'] ) ? $attributes['queryMeta'] : '';
			$query_taxonomy_relation = isset( $attributes['queryTaxonomyRelation'] ) ? $attributes['queryTaxonomyRelation'] : '';
			$query_meta_relation     = isset( $attributes['queryMetaRelation'] ) ? $attributes['queryMetaRelation'] : '';
			$query_password          = isset( $attributes['queryPassword'] ) ? $attributes['queryPassword'] : '';
			$query_post_password     = isset( $attributes['queryPostPassword'] ) ? $attributes['queryPostPassword'] : '';
			$query_author            = isset( $attributes['queryAuthor'] ) ? $attributes['queryAuthor'] : '';
			$query_author_name       = isset( $attributes['queryAuthorName'] ) ? $attributes['queryAuthorName'] : '';
			$query_author_in         = isset( $attributes['queryAuthorIn'] ) ? $attributes['queryAuthorIn'] : '';
			$query_author_not_in     = isset( $attributes['queryAuthorNotIn'] ) ? $attributes['queryAuthorNotIn'] : '';
			$query_search            = isset( $attributes['querySearch'] ) ? $attributes['querySearch'] : '';
			$query_post_status       = isset( $attributes['queryPostStatus'] ) ? $attributes['queryPostStatus'] : '';
			$query_comment_count     = isset( $attributes['queryCommentCount'] ) ? $attributes['queryCommentCount'] : '';
			$query_comment_compare   = isset( $attributes['queryCommentCompare'] ) ? $attributes['queryCommentCompare'] : '';
			$query_perm              = isset( $attributes['queryPerm'] ) ? $attributes['queryPerm'] : '';
			$query_mime_type         = isset( $attributes['queryMimeType'] ) ? $attributes['queryMimeType'] : '';
			$query_date              = isset( $attributes['queryDate'] ) ? $attributes['queryDate'] : '';
			$query_date_relation     = isset( $attributes['queryDateRelation'] ) ? $attributes['queryDateRelation'] : '';

			$query_taxonomies       = isset( $attributes['queryTaxonomies'] ) ? $attributes['queryTaxonomies'] : '';
			$query_object_i_ds      = isset( $attributes['queryObjectIDs'] ) ? $attributes['queryObjectIDs'] : '';
			$query_hide_empty       = isset( $attributes['queryHideEmpty'] ) ? $attributes['queryHideEmpty'] : '';
			$query_count            = isset( $attributes['queryCount'] ) ? $attributes['queryCount'] : '';
			$query_pad_count        = isset( $attributes['queryPadCount'] ) ? $attributes['queryPadCount'] : '';
			$query_exclude_tree     = isset( $attributes['queryExcludeTree'] ) ? $attributes['queryExcludeTree'] : '';
			$query_number           = isset( $attributes['queryNumber'] ) ? $attributes['queryNumber'] : '';
			$query_fields           = isset( $attributes['queryFields'] ) ? $attributes['queryFields'] : '';
			$query_name             = isset( $attributes['queryName'] ) ? $attributes['queryName'] : '';
			$query_slug             = isset( $attributes['querySlug'] ) ? $attributes['querySlug'] : '';
			$query_hierarchical     = isset( $attributes['queryHierarchical'] ) ? $attributes['queryHierarchical'] : '';
			$query_name_like        = isset( $attributes['queryNameLike'] ) ? $attributes['queryNameLike'] : '';
			$query_description_like = isset( $attributes['queryDescriptionLike'] ) ? $attributes['queryDescriptionLike'] : '';
			$query_get              = isset( $attributes['queryGet'] ) ? $attributes['queryGet'] : '';
			$query_child_of         = isset( $attributes['queryChildOf'] ) ? $attributes['queryChildOf'] : '';
			$query_parent           = isset( $attributes['queryParent'] ) ? $attributes['queryParent'] : '';
			$query_childless        = isset( $attributes['queryChildless'] ) ? $attributes['queryChildless'] : '';

			$query_role          = isset( $attributes['queryRole'] ) ? $attributes['queryRole'] : '';
			$query_role_in       = isset( $attributes['queryRoleIn'] ) ? $attributes['queryRoleIn'] : '';
			$query_role_not_in   = isset( $attributes['queryRoleNotIn'] ) ? $attributes['queryRoleNotIn'] : '';
			$query_blog_id       = isset( $attributes['queryBlogID'] ) ? $attributes['queryBlogID'] : '';
			$query_search_column = isset( $attributes['querySearchColumn'] ) ? $attributes['querySearchColumn'] : '';
			$query_who           = isset( $attributes['queryWho'] ) ? $attributes['queryWho'] : '';
			$query_total_count   = isset( $attributes['queryTotalCount'] ) ? $attributes['queryTotalCount'] : '';
			$query_has_published = isset( $attributes['queryHasPublished'] ) ? $attributes['queryHasPublished'] : '';

			$query_comment_parent             = isset( $attributes['queryCommentParent'] ) ? $attributes['queryCommentParent'] : '';
			$query_comment_in_parent          = isset( $attributes['queryCommentInParent'] ) ? $attributes['queryCommentInParent'] : '';
			$query_comment_not_parent         = isset( $attributes['queryCommentNotParent'] ) ? $attributes['queryCommentNotParent'] : '';
			$query_comment_post_id            = isset( $attributes['queryCommentPostID'] ) ? $attributes['queryCommentPostID'] : '';
			$query_comment_id                 = isset( $attributes['queryCommentID'] ) ? $attributes['queryCommentID'] : '';
			$query_comment_not_id             = isset( $attributes['queryCommentNotID'] ) ? $attributes['queryCommentNotID'] : '';
			$query_comment_include_unapproved = isset( $attributes['queryCommentIncludeUnapproved'] ) ? $attributes['queryCommentIncludeUnapproved'] : '';
			$query_comment_karma              = isset( $attributes['queryCommentKarma'] ) ? $attributes['queryCommentKarma'] : '';
			$query_author_email               = isset( $attributes['queryAuthorEmail'] ) ? $attributes['queryAuthorEmail'] : '';
			$query_author_url                 = isset( $attributes['queryAuthorURL'] ) ? $attributes['queryAuthorURL'] : '';
			$query_comment_author_in          = isset( $attributes['queryCommentAuthorIn'] ) ? $attributes['queryCommentAuthorIn'] : '';
			$query_comment_author_not_in      = isset( $attributes['queryCommentAuthorNotIn'] ) ? $attributes['queryCommentAuthorNotIn'] : '';

			$query_woo_type              = isset( $attributes['queryWooType'] ) ? $attributes['queryWooType'] : '';
			$query_woo_parent_exclude    = isset( $attributes['queryWooParentExclude'] ) ? $attributes['queryWooParentExclude'] : '';
			$query_woo_sku               = isset( $attributes['queryWooSKU'] ) ? $attributes['queryWooSKU'] : '';
			$query_woo_tag               = isset( $attributes['queryWooTag'] ) ? $attributes['queryWooTag'] : '';
			$query_woo_category          = isset( $attributes['queryWooCategory'] ) ? $attributes['queryWooCategory'] : '';
			$query_woo_width             = isset( $attributes['queryWooWidth'] ) ? $attributes['queryWooWidth'] : '';
			$query_woo_height            = isset( $attributes['queryWooHeight'] ) ? $attributes['queryWooHeight'] : '';
			$query_woo_weight            = isset( $attributes['queryWooWeight'] ) ? $attributes['queryWooWeight'] : '';
			$query_woo_length            = isset( $attributes['queryWooLength'] ) ? $attributes['queryWooLength'] : '';
			$query_woo_price             = isset( $attributes['queryWooPrice'] ) ? $attributes['queryWooPrice'] : '';
			$query_woo_regular_price     = isset( $attributes['queryWooRegularPrice'] ) ? $attributes['queryWooRegularPrice'] : '';
			$query_woo_sale_price        = isset( $attributes['queryWooSalePrice'] ) ? $attributes['queryWooSalePrice'] : '';
			$query_woo_total_sales       = isset( $attributes['queryWooTotalSales'] ) ? $attributes['queryWooTotalSales'] : '';
			$query_woo_virtual           = isset( $attributes['queryWooVirtual'] ) ? $attributes['queryWooVirtual'] : '';
			$query_woo_downloadable      = isset( $attributes['queryWooDownloadable'] ) ? $attributes['queryWooDownloadable'] : '';
			$query_woo_featured          = isset( $attributes['queryWooFeatured'] ) ? $attributes['queryWooFeatured'] : '';
			$query_woo_sold_individually = isset( $attributes['queryWooSoldIndividually'] ) ? $attributes['queryWooSoldIndividually'] : '';
			$query_woo_manage_stock      = isset( $attributes['queryWooManageStock'] ) ? $attributes['queryWooManageStock'] : '';
			$query_woo_reviews_allowed   = isset( $attributes['queryWooReviewsAllowed'] ) ? $attributes['queryWooReviewsAllowed'] : '';
			$query_woo_backorders        = isset( $attributes['queryWooBackorders'] ) ? $attributes['queryWooBackorders'] : '';
			$query_woo_visibility        = isset( $attributes['queryWooVisibility'] ) ? $attributes['queryWooVisibility'] : '';
			$query_woo_stock_quantity    = isset( $attributes['queryWooStockQuantity'] ) ? $attributes['queryWooStockQuantity'] : '';
			$query_woo_stock_status      = isset( $attributes['queryWooStockStatus'] ) ? $attributes['queryWooStockStatus'] : '';
			$query_woo_tax_status        = isset( $attributes['queryWooTaxStatus'] ) ? $attributes['queryWooTaxStatus'] : '';
			$query_woo_tax_class         = isset( $attributes['queryWooTaxClass'] ) ? $attributes['queryWooTaxClass'] : '';
			$query_woo_shipping_class    = isset( $attributes['queryWooShippingClass'] ) ? $attributes['queryWooShippingClass'] : '';
			$query_woo_download_limit    = isset( $attributes['queryWooDownloadLimit'] ) ? $attributes['queryWooDownloadLimit'] : '';
			$query_woo_download_expiry   = isset( $attributes['queryWooDownloadExpiry'] ) ? $attributes['queryWooDownloadExpiry'] : '';
			$query_woo_average_rating    = isset( $attributes['queryWooAverageRating'] ) ? $attributes['queryWooAverageRating'] : '';
			$query_woo_review_count      = isset( $attributes['queryWooReviewCount'] ) ? $attributes['queryWooReviewCount'] : '';
			$query_woo_date_created      = isset( $attributes['queryWooDateCreated'] ) ? $attributes['queryWooDateCreated'] : '';
			$query_woo_date_modified     = isset( $attributes['queryWooDateModified'] ) ? $attributes['queryWooDateModified'] : '';
			$query_woo_date_on_sale_from = isset( $attributes['queryWooDateOnSaleFrom'] ) ? $attributes['queryWooDateOnSaleFrom'] : '';
			$query_woo_date_on_sale_to   = isset( $attributes['queryWooDateOnSaleTo'] ) ? $attributes['queryWooDateOnSaleTo'] : '';

			$block_context = array();

			if ( isset( $block->context ) ) {
				$block_context = $block->context;
			}

			$args_class = new \Cwicly_Query_Args( $block_context, $block );
			$args       = $args_class->query_preparation(
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
			);

			return $args;
		}
	}

	/**
	 * Prepares the query arguments for the front end.
	 *
	 * @param array  $attributes The block attributes.
	 * @param object $block      The block.
	 *
	 * @return array
	 */
	public static function front_prep( $attributes, $block ) {
		$max_page = '';

		$query_args = self::front_args( $attributes, false, $block );

		// MAKE TAX AND META IF EMPTY.
		if ( isset( $query_args['tax_query'] ) ) {
			foreach ( $query_args['tax_query'] as $index => $tax_query ) {
				if ( 'relation' !== $index ) {
					if ( is_string( $index ) && isset( $tax_query->taxonomy ) ) {
						if ( ! isset( $tax_query->terms ) || ( isset( $tax_query->terms ) && ! $tax_query->terms ) ) {
							if ( 'relation' !== $index ) {
								$query_args['tax_query']->$index->operator = 'XXX';
							}
						}
					} elseif ( isset( $tax_query['taxonomy'] ) && ( ! isset( $tax_query['terms'] ) || ( isset( $tax_query['terms'] ) && ! $tax_query['terms'] ) ) ) {
						if ( 'relation' !== $index ) {
							$query_args['tax_query'][ $index ]['operator'] = 'XXX';
						}
					}
				}
			}
		}
		if ( isset( $query_args['meta_query'] ) ) {
			foreach ( $query_args['meta_query'] as $index => $meta_query ) {
				if ( 'relation' !== $index ) {
					if ( is_string( $index ) ) {
						if ( ! isset( $meta_query->value ) || ! \Cwicly\Helpers::check_if_exists( $meta_query->value ) ) {
							if ( 'relation' !== $index ) {
								$query_args['meta_query']->$index->value = array();
							}
						}
					} elseif ( ! isset( $meta_query['value'] ) || ! \Cwicly\Helpers::check_if_exists( $meta_query['value'] ) ) {
						if ( 'relation' !== $index ) {
							$query_args['meta_query'][ $index ]['value'] = array();
						}
					}
				}
			}
		}
		// MAKE TAX AND META IF EMPTY.

		$starting_offset = '';
		if ( isset( $query_args['offset'] ) && $query_args['offset'] ) {
			$starting_offset = $query_args['offset'];
		}

		if ( isset( $attributes['queryPage']['field'] ) && $attributes['queryPage']['field'] ) {
			$page_key = $attributes['queryPage']['field'];
		} else {
			$page_key = isset( $attributes['queryId'] ) ? 'query-' . $attributes['queryId'] . '-page' : 'query-page';
			if ( empty( $_GET[ $page_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$query_args['paged'] = 1;
				$query_args['page']  = 1;
			} else {
				$query_args['paged'] = (int) $_GET[ $page_key ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$query_args['page']  = (int) $_GET[ $page_key ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
		}

		// FOR OFFSETING CORRECTLY.
		if ( isset( $query_args['offset'] ) && $query_args['offset'] ) {
			if ( isset( $query_args['paged'] ) && $query_args['paged'] ) {
				if ( isset( $query_args['posts_per_page'] ) && $query_args['posts_per_page'] ) {
					$query_args['offset'] = ( intval( $query_args['posts_per_page'] ) * ( intval( $query_args['paged'] ) - 1 ) ) + ( intval( $query_args['offset'] ) );
				} else {
					$default_posts_per_page = get_option( 'posts_per_page' );
					$query_args['offset']   = ( intval( $default_posts_per_page ) * ( intval( $query_args['paged'] ) - 1 ) ) + ( intval( $query_args['offset'] ) );
				}
			}
		} elseif ( ( 'terms' === $attributes['queryType'] || 'users' === $attributes['queryType'] ) && isset( $query_args['number'] ) && $query_args['number'] && isset( $query_args['offset'] ) && $query_args['offset'] ) {
			if ( isset( $query_args['paged'] ) && $query_args['paged'] ) {
				$query_args['offset'] = ( intval( $query_args['number'] ) * ( intval( $query_args['paged'] ) - 1 ) ) + ( intval( $query_args['offset'] ) );
			}
		} elseif ( ( 'terms' === $attributes['queryType'] || 'users' === $attributes['queryType'] ) && isset( $query_args['number'] ) && $query_args['number'] ) {
			if ( isset( $query_args['paged'] ) && $query_args['paged'] ) {
				$query_args['offset'] = ( intval( $query_args['number'] ) * ( intval( $query_args['paged'] ) - 1 ) );
			}
		}
		// FOR OFFSETING CORRECTLY.

		// Override the custom query with the global query if needed.
		$use_global_query = ( isset( $attributes['queryInherit'] ) && $attributes['queryInherit'] );
		if ( $use_global_query ) {
			global $wp_query;
			if ( $wp_query && isset( $wp_query->query_vars ) && is_array( $wp_query->query_vars ) ) {
				unset( $query_args['offset'] );
				$query_args = wp_parse_args( $wp_query->query_vars, $query_args );

				if ( empty( $query_args['post_type'] ) && is_singular() ) {
					$query_args['post_type'] = get_post_type( get_the_ID() );
				}
			}
		}

		$id         = isset( $attributes['id'] ) && $attributes['id'] ? $attributes['id'] : '';
		$query_args = apply_filters( 'cwicly/query/args', $query_args, $attributes, $id );

		// Relevanssi
		if ( function_exists( 'relevanssi_do_excerpt' ) && isset( $query_args['s'] ) && ! empty( $query_args['s'] ) ) {
			// Adding relevanssi to the query args so that the excerpt is generated correctly.
			$query_args['relevanssi'] = true;
		}

		$query = '';
		if ( 'posts' === $attributes['queryType'] ) {
			$query_args = apply_filters( 'cwicly/query/posts/args', $query_args, $attributes, $id );
			$query      = new \WP_Query( $query_args );
		} elseif ( 'terms' === $attributes['queryType'] ) {
			$query_args = apply_filters( 'cwicly/query/terms/args', $query_args, $attributes, $id );
			$query      = new \WP_Term_Query( $query_args );
		} elseif ( 'users' === $attributes['queryType'] ) {
			$query_args                = apply_filters( 'cwicly/query/users/args', $query_args, $attributes, $id );
			$query_args['count_total'] = true;
			$query                     = new \WP_User_Query( $query_args );
		} elseif ( 'comments' === $attributes['queryType'] ) {
			$query_args = apply_filters( 'cwicly/query/comments/args', $query_args, $attributes, $id );
			$query      = new \WP_Comment_Query( $query_args );
		} elseif ( CC_WOOCOMMERCE && 'products' === $attributes['queryType'] ) {
			$query_args = apply_filters( 'cwicly/query/products/args', $query_args, $attributes, $id );
			$query      = new \WC_Product_Query( $query_args );

			$query_args['paginate'] = true;
			$wcquery                = wc_get_products( $query_args );
		}

		$has_posts = false;
		if ( ! \Cwicly\Helpers::is_rest() ) {
			if ( CC_WOOCOMMERCE && 'products' === $attributes['queryType'] && $wcquery && isset( $wcquery->products ) && count( $wcquery->products ) > 0 ) {
				$has_posts = true;
			} elseif ( 'terms' === $attributes['queryType'] && $query && isset( $query->terms ) && count( $query->terms ) > 0 ) {
				$has_posts = true;
			} elseif ( 'users' === $attributes['queryType'] && $query && isset( $query->results ) && count( $query->results ) > 0 ) {
				$has_posts = true;
			} elseif ( 'comments' === $attributes['queryType'] && $query && isset( $query->comments ) && count( $query->comments ) > 0 ) {
				$has_posts = true;
			} elseif ( 'posts' === $attributes['queryType'] && $query->have_posts() ) {
				$has_posts = true;
			}
		}

		$count = 0;
		if ( isset( $query->found_posts ) ) {
			$count = $query->found_posts;
		} elseif ( 'products' === $attributes['queryType'] && isset( $wcquery ) ) {
			$count = $wcquery->total;
		}

		$total = '';
		if ( 'products' === $attributes['queryType'] && isset( $query_args['limit'] ) && $query_args['limit'] && isset( $query_args['offset'] ) && $query_args['offset'] ) {
			$total_rows = max( 0, $query->found_posts - $starting_offset );
			$total      = ceil( $total_rows / $query_args['limit'] );
			$count      = $query->found_posts;
		} elseif ( isset( $query_args['posts_per_page'] ) && $query_args['posts_per_page'] && isset( $query_args['offset'] ) && $query_args['offset'] ) {
			$total_rows = max( 0, $query->found_posts - $starting_offset );
			$total      = ceil( $total_rows / $query_args['posts_per_page'] );
			$count      = $query->found_posts;
		} elseif ( 'terms' === $attributes['queryType'] && isset( $query_args['number'] ) && $query_args['number'] ) {
			$new_query_args           = $query_args;
			$new_query_args['fields'] = 'ids';
			$new_query_args['number'] = '';
			$query_for_count          = new \WP_Term_Query( $new_query_args );
			$count                    = count( $query_for_count->terms );
			$total                    = ceil( $count / $query_args['number'] );
		} elseif ( 'users' === $attributes['queryType'] ) {
			if ( isset( $query_args['number'] ) && $query_args['number'] ) {
				$count = $query->get_total();
				$total = ceil( $count / $query_args['number'] );
			} else {
				$default_posts_per_page = get_option( 'posts_per_page' );
				$count                  = $query->get_total();
				$total                  = ceil( $count / $default_posts_per_page );
			}
		} elseif ( 'comments' === $attributes['queryType'] ) {
			if ( isset( $query_args['number'] ) && $query_args['number'] ) {
				$new_query_args           = $query_args;
				$new_query_args['fields'] = 'ids';
				$new_query_args['number'] = '';
				$new_query_args['count']  = true;
				$count                    = get_comments( $new_query_args );
				$total                    = ceil( $count / $query_args['number'] );
			} else {
				$default_posts_per_page   = get_option( 'posts_per_page' );
				$new_query_args           = $query_args;
				$new_query_args['fields'] = 'ids';
				$new_query_args['number'] = '';
				$new_query_args['count']  = true;
				$count                    = get_comments( $new_query_args );
				$total                    = ceil( $count / $default_posts_per_page );
			}
		} elseif ( CC_WOOCOMMERCE && 'products' === $attributes['queryType'] ) {
			$total = ! $max_page || $max_page > $wcquery->max_num_pages ? $wcquery->max_num_pages : $max_page;
		} elseif ( 'terms' != $attributes['queryType'] ) {
			$total = isset( $query ) && $query && ( ! $max_page || $max_page > $query->max_num_pages ) ? $query->max_num_pages : $max_page;
		} elseif ( 'terms' === $attributes['queryType'] ) {
			$default_posts_per_page   = get_option( 'posts_per_page' );
			$new_query_args           = $query_args;
			$new_query_args['fields'] = 'ids';
			$query_for_count          = new \WP_Term_Query( $new_query_args );
			$count                    = count( $query_for_count->terms );
			$total                    = ceil( $count / $default_posts_per_page );
		} else {
			$total = ! $max_page || $max_page > $query->max_num_pages ? $query->max_num_pages : $max_page;
		}

		$content = '';

		$page          = empty( $_GET[ $page_key ] ) ? 1 : (int) $_GET[ $page_key ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$paginate_args = array(
			'base'      => str_replace( '%_%', 1 == $page ? '' : "?$page_key=%#%", "?$page_key=%#%" ),
			'format'    => "?$page_key=%#%",
			'current'   => max( 1, $page ),
			'total'     => $total,
			'prev_next' => false,
		);

		$block_context = $block->context;

		$new_context = array(
			'queryId'          => isset( $attributes['queryId'] ) && $attributes['queryId'] ? $attributes['queryId'] : 0,
			'queryRendered'    => $query,
			'queryTotal'       => $total,
			'queryCount'       => $count,
			'queryPageKey'     => $page_key,
			'paginateArgs'     => $paginate_args,
			'queryType'        => $attributes['queryType'],
			'queryInherit'     => $attributes['queryInherit'],
			'queryPage'        => $page,
			'queryCurrentPage' => $page,
			'queryPostPerPage' => ( isset( $query_args['posts_per_page'] ) && $query_args['posts_per_page'] ) ? $query_args['posts_per_page'] : get_option( 'posts_per_page' ),
			'rendered'         => true,
			'hasPosts'         => $has_posts,
		);

		$merged_context = array_merge( $block_context, $new_context );

		if ( isset( $block->parsed_block['innerBlocks'] ) && $block->parsed_block['innerBlocks'] ) {
			foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
				$content .= ( new \WP_Block(
					$inner_block,
					$merged_context
				) )->render( array( 'dynamic' => true ) );
			}
		}

		return array(
			'content'  => $content,
			'hasPosts' => $has_posts,
		);
	}

	/**
	 * Render the blocks from the query.
	 *
	 * @param array  $attributes The block attributes.
	 * @param object $block The block object.
	 *
	 * @return string
	 */
	public static function front_maker( $attributes, $block ) {
		$mason = '';
		if ( isset( $attributes['repeaterMasonry'] ) && $attributes['repeaterMasonry'] ) {
			$mason = ' cc-masonry-item';
		}

		$content = '';
		$query   = '';

		if ( isset( $block->context['queryRendered'] ) && $block->context['queryRendered'] ) {
			$query = $block->context['queryRendered'];
		}

		if ( ! is_admin() && $query ) {
			if ( 'posts' === $block->context['queryType'] ) {
				if ( $query && $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						$block_content = '';
						foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {

							$block_context = \Cwicly\Helpers::extract_necessary_context( $block->context );

							$new_context = array(
								'postType'         => get_post_type(),
								'postId'           => get_the_ID(),
								'query_index'      => $query->current_post + 1,
								'queryId'          => $block->context['queryId'],
								'queryTotal'       => $block->context['queryTotal'],
								'queryCount'       => $block->context['queryCount'],
								'queryPage'        => $block->context['queryPage'],
								'queryCurrentPage' => $block->context['queryCurrentPage'],
								'queryPostPerPage' => $block->context['queryPostPerPage'],
								'rendered'         => true,
							);

							$merged_context = array_merge( $block_context, $new_context );

							$block_content .= ( new \WP_Block(
								$inner_block,
								$merged_context
							) )->render( array( 'dynamic' => true ) );
						}
						$last_query = '';
						if ( $query->current_post + 1 == $query->post_count ) {
							if ( isset( $block->context['queryCurrentPage'] ) && $block->context['queryCurrentPage'] && is_integer( $block->context['queryCurrentPage'] ) ) {
								$last_query = ' data-lastpost="' . $block->context['queryCurrentPage'] . '"';
							}
						}
						if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
							$content .= '<li class="splide__slide">' . $block_content . '</li>';
						} else {
							$content .= '<div class="cc-query-item' . $mason . '"' . $last_query . '>' . $block_content . '</div>';
						}
					}
					wp_reset_postdata();
				}
			} elseif ( 'terms' === $block->context['queryType'] ) {
				if ( $query->terms && ! empty( $query->terms ) ) {
					foreach ( $query->terms as $index => $term ) {
						$block_content = '';
						foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {

							$block_context = \Cwicly\Helpers::extract_necessary_context( $block->context );

							$new_context = array(
								'postType'         => get_post_type(),
								'postId'           => get_the_ID(),
								'query_index'      => $index + 1,
								'queryId'          => $block->context['queryId'],
								'queryTotal'       => $block->context['queryTotal'],
								'queryCount'       => $block->context['queryCount'],
								'queryPage'        => $block->context['queryPage'],
								'queryCurrentPage' => $block->context['queryCurrentPage'],
								'queryPostPerPage' => $block->context['queryPostPerPage'],
								'termQuery'        => $term,
								'rendered'         => true,
							);

							$merged_context = array_merge( $block_context, $new_context );

							$block_content .= ( new \WP_Block(
								$inner_block,
								$new_context
							) )->render( array( 'dynamic' => true ) );
						}
						$last_query = '';
						if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
							$content .= '<li class="splide__slide">' . $block_content . '</li>';
						} else {
							$content .= '<div class="cc-query-item' . $mason . '"' . $last_query . '>' . $block_content . '</div>';
						}
					}
				}
			} elseif ( 'comments' === $block->context['queryType'] ) {
				if ( $query->comments && ! empty( $query->comments ) ) {
					$content .= wp_list_comments(
						array(
							'walker'      => new \Cwicly\Cwicly_Comment_Walker(),
							'callback'    => array( '\Cwicly\Comments', 'comment_list' ),
							'echo'        => false,
							'style'       => 'div',
							'status'      => $query->query_vars['status'],
							'mason'       => $mason,
							'queryId'     => $block->context['queryId'],
							'innerBlocks' => $block->parsed_block['innerBlocks'],
							'context'     => $block->context,
						),
						$query->comments
					);
				}
			} elseif ( 'users' === $block->context['queryType'] ) {
				$result = $query->get_results();
				if ( $result ) {
					if ( ! empty( $result ) ) {
						foreach ( $result as $index => $user ) {
							$block_content = '';
							foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {

								$block_context = \Cwicly\Helpers::extract_necessary_context( $block->context );

								$new_context = array(
									'postType'         => get_post_type(),
									'postId'           => get_the_ID(),
									'query_index'      => $index + 1,
									'queryId'          => $block->context['queryId'],
									'queryTotal'       => $block->context['queryTotal'],
									'queryCount'       => $block->context['queryCount'],
									'queryPage'        => $block->context['queryPage'],
									'queryCurrentPage' => $block->context['queryCurrentPage'],
									'queryPostPerPage' => $block->context['queryPostPerPage'],
									'userQuery'        => $user,
									'rendered'         => true,
								);

								$merged_context = array_merge( $block_context, $new_context );

								$block_content .= ( new \WP_Block(
									$inner_block,
									$merged_context
								) )->render( array( 'dynamic' => true ) );
							}
							$last_query = '';
							if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
								$content .= '<li class="splide__slide">' . $block_content . '</li>';
							} else {
								$content .= '<div class="cc-query-item' . $mason . '"' . $last_query . '>' . $block_content . '</div>';
							}
						}
					}
				}
			} elseif ( 'products' === $block->context['queryType'] ) {
				if ( $query ) {
					$products = $query->get_products();

					foreach ( $products as $index => $product ) {
						global $post;
						$post = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

						$type               = ' data-cc-woo-type="' . $product->get_type() . '"';
						$id                 = ' data-cc-id="' . $product->get_id() . '"';
						$variations         = array();
						$default_variations = array();
						if ( 'variable' === $product->get_type() && $product->get_attributes() ) {
							$attributers        = array_keys( $product->get_attributes() );
							$default_attributes = $product->get_default_attributes();
							foreach ( $attributers as $attribute ) {
								if ( isset( $default_attributes[ $attribute ] ) ) {
									$default_variations[ $attribute ] = $default_attributes[ $attribute ];
								} else {
									$default_variations[ $attribute ] = '';
								}
							}
							$variations         = ' data-cc-woo-variations="' . htmlspecialchars( wp_json_encode( $product->get_available_variations() ) ) . '"';
							$default_variations = ' data-cc-woo-default-variations="' . htmlspecialchars( wp_json_encode( $default_variations ) ) . '"';
						} else {
							$variations         = '';
							$default_variations = '';
						}

						$block_content = '';
						if ( isset( $block->parsed_block['innerBlocks'] ) && $block->parsed_block['innerBlocks'] ) {
							foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {

								$block_context = \Cwicly\Helpers::extract_necessary_context( $block->context );

								$new_context = array(
									'postType'         => get_post_type(),
									'postId'           => $product->get_id(),
									'product'          => $product,
									'query_index'      => $index + 1,
									'queryId'          => $block->context['queryId'],
									'queryTotal'       => $block->context['queryTotal'],
									'queryCount'       => $block->context['queryCount'],
									'queryPage'        => $block->context['queryPage'],
									'queryCurrentPage' => $block->context['queryCurrentPage'],
									'queryPostPerPage' => $block->context['queryPostPerPage'],
									'return'           => 'ids',
									'rendered'         => true,
								);

								$merged_context = array_merge( $block_context, $new_context );

								$block_content .= ( new \WP_Block(
									$inner_block,
									$merged_context
								) )->render( array( 'dynamic' => true ) );
							}
						}
						$last_query = '';
						if ( isset( $attributes['repeaterSlider'] ) && $attributes['repeaterSlider'] ) {
							$content .= '<li class="splide__slide"' . $type . $id . $variations . $default_variations . $last_query . '>' . $block_content . '</li>';
						} else {
							$content .= '<div class="cc-query-item' . $mason . '"' . $type . $id . $variations . $default_variations . $last_query . '>' . $block_content . '</div>';
						}
					}
					wp_reset_postdata();
				}
			}
		}

		return $content;
	}

	/**
	 * Get the query args for a query block.
	 *
	 * @param array  $attributes The block attributes.
	 * @param object $block The block object.
	 */
	public static function fr_args( $attributes, $block ) {
		$all_query_args = self::front_args( $attributes, true, $block );

		$old_get               = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$_GET                  = null;
		$all_query_args_no_get = self::front_args( $attributes, false, $block );
		$_GET                  = $old_get;

		$query_args = $all_query_args['args'];

		$params = $all_query_args['params'];

		if ( isset( $attributes['queryPage']['field'] ) && $attributes['queryPage']['field'] ) {
			$page_key = $attributes['queryPage']['field'];
		} else {
			$page_key = isset( $attributes['queryId'] ) ? 'query-' . $attributes['queryId'] . '-page' : 'query-page';
			if ( empty( $_GET[ $page_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$query_args['paged'] = 1;
				$query_args['page']  = 1;
			} else {
				$query_args['paged'] = (int) $_GET[ $page_key ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$query_args['page']  = (int) $_GET[ $page_key ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
		}

		// FOR OFFSETING CORRECTLY.
		if ( isset( $query_args['offset'] ) && $query_args['offset'] ) {
			if ( isset( $query_args['paged'] ) && $query_args['paged'] ) {
				if ( isset( $query_args['posts_per_page'] ) && $query_args['posts_per_page'] ) {
					$query_args['offset'] = ( intval( $query_args['posts_per_page'] ) * ( intval( $query_args['paged'] ) - 1 ) ) + ( intval( $query_args['offset'] ) );
				} else {
					$default_posts_per_page = get_option( 'posts_per_page' );
					$query_args['offset']   = ( intval( $default_posts_per_page ) * ( intval( $query_args['paged'] ) - 1 ) ) + ( intval( $query_args['offset'] ) );
				}
			}
		} elseif ( 'terms' === $attributes['queryType'] && isset( $query_args['number'] ) && $query_args['number'] && isset( $query_args['offset'] ) && $query_args['offset'] ) {
			if ( isset( $query_args['paged'] ) && $query_args['paged'] ) {
				$query_args['offset'] = ( intval( $query_args['number'] ) * ( intval( $query_args['paged'] ) - 1 ) ) + ( intval( $query_args['offset'] ) );
			}
		} elseif ( 'terms' === $attributes['queryType'] && isset( $query_args['number'] ) && $query_args['number'] ) {
			if ( isset( $query_args['paged'] ) && $query_args['paged'] ) {
				$query_args['offset'] = ( intval( $query_args['number'] ) * ( intval( $query_args['paged'] ) - 1 ) );
			}
		}
		// FOR OFFSETING CORRECTLY.

		// Override the custom query with the global query if needed.
		$use_global_query = ( isset( $attributes['queryInherit'] ) && $attributes['queryInherit'] );
		if ( $use_global_query ) {
			global $wp_query;
			if ( $wp_query && isset( $wp_query->query_vars ) && is_array( $wp_query->query_vars ) ) {
				unset( $query_args['offset'] );
				$query_args = wp_parse_args( $wp_query->query_vars, $query_args );

				if ( empty( $query_args['post_type'] ) && is_singular() ) {
					$query_args['post_type'] = get_post_type( get_the_ID() );
				}
			}
		}

		$posts_per_page = get_option( 'posts_per_page' );
		if ( 'products' === $attributes['queryType'] && isset( $query_args['limit'] ) && $query_args['limit'] ) {
			$posts_per_page = $query_args['limit'];
		} elseif ( isset( $query_args['posts_per_page'] ) && $query_args['posts_per_page'] ) {
			$posts_per_page = $query_args['posts_per_page'];
		}

		return array(
			'query_args'            => $query_args,
			'all_query_args_no_get' => $all_query_args_no_get,
			'params'                => $params,
			'postsPerPage'          => $posts_per_page,
		);
	}
}
