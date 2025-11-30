<?php
/**
 * Cwicly Query API.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Query API.
 */
class Query_API extends \WP_REST_Controller {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->register_routes();
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$namespace = 'cwicly/v' . CWICLY_API_VERSION;

		$base = 'dynamic_query';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'get_dynamic_query' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);
	}

		/**
		 * Get a collection of items for Query block
		 *
		 * @param WP_REST_Request $request Full data about the request.
		 * @return WP_Error|WP_REST_Response
		 */
	public function get_dynamic_query( $request ) {
		$body = json_decode( $request->get_body(), true );

		$query_type = $body['queryType'] ?? null;
		$post_id    = $body['postId'] ?? null;

		$query_max_items = $body['queryMaxItems'] ?? null;

		$query_sticky            = $body['querySticky'] ?? null;
		$query_post_type         = $body['queryPostType'] ?? null;
		$query_per_page          = $body['queryPerPage'] ?? null;
		$query_offset            = $body['queryOffset'] ?? null;
		$query_exclude_current   = $body['queryExcludeCurrent'] ?? null;
		$query_meta_key          = $body['queryMetaKey'] ?? null;
		$query_order_by          = $body['queryOrderBy'] ?? null;
		$query_order             = $body['queryOrder'] ?? null;
		$query_include           = $body['queryInclude'] ?? null;
		$query_exclude           = $body['queryExclude'] ?? null;
		$query_post_parent       = $body['queryPostParent'] ?? null;
		$query_in_parent         = $body['queryInParent'] ?? null;
		$query_not_in_parent     = $body['queryNotInParent'] ?? null;
		$query_taxonomy          = $body['queryTaxonomy'] ?? null;
		$query_meta              = $body['queryMeta'] ?? null;
		$query_taxonomy_relation = $body['queryTaxonomyRelation'] ?? null;
		$query_meta_relation     = $body['queryMetaRelation'] ?? null;
		$query_password          = $body['queryPassword'] ?? null;
		$query_post_password     = $body['queryPostPassword'] ?? null;
		$query_author            = $body['queryAuthor'] ?? null;
		$query_author_name       = $body['queryAuthorName'] ?? null;
		$query_author_in         = $body['queryAuthorIn'] ?? null;
		$query_author_not_in     = $body['queryAuthorNotIn'] ?? null;
		$query_search            = $body['querySearch'] ?? null;
		$query_post_status       = $body['queryPostStatus'] ?? null;
		$query_comment_count     = $body['queryCommentCount'] ?? null;
		$query_comment_compare   = $body['queryCommentCompare'] ?? null;
		$query_perm              = $body['queryPerm'] ?? null;
		$query_mime_type         = $body['queryMimeType'] ?? null;
		$query_date              = $body['queryDate'] ?? null;
		$query_date_relation     = $body['queryDateRelation'] ?? null;

		$query_taxonomies       = $body['queryTaxonomies'] ?? null;
		$query_object_i_ds      = $body['queryObjectIDs'] ?? null;
		$query_hide_empty       = $body['queryHideEmpty'] ?? null;
		$query_count            = $body['queryCount'] ?? null;
		$query_pad_count        = $body['queryPadCount'] ?? null;
		$query_exclude_tree     = $body['queryExcludeTree'] ?? null;
		$query_number           = $body['queryNumber'] ?? null;
		$query_fields           = $body['queryFields'] ?? null;
		$query_name             = $body['queryName'] ?? null;
		$query_slug             = $body['querySlug'] ?? null;
		$query_hierarchical     = $body['queryHierarchical'] ?? null;
		$query_name_like        = $body['queryNameLike'] ?? null;
		$query_description_like = $body['queryDescriptionLike'] ?? null;
		$query_get              = $body['queryGet'] ?? null;
		$query_child_of         = $body['queryChildOf'] ?? null;
		$query_parent           = $body['queryParent'] ?? null;
		$query_childless        = $body['queryChildless'] ?? null;

		$query_role          = $body['queryRole'] ?? null;
		$query_role_in       = $body['queryRoleIn'] ?? null;
		$query_role_not_in   = $body['queryRoleNotIn'] ?? null;
		$query_blog_id       = $body['queryBlogID'] ?? null;
		$query_search_column = $body['querySearchColumn'] ?? null;
		$query_who           = $body['queryWho'] ?? null;
		$query_total_count   = $body['queryTotalCount'] ?? null;
		$query_has_published = $body['queryHasPublished'] ?? null;

		$query_comment_parent             = $body['queryCommentParent'] ?? null;
		$query_comment_in_parent          = $body['queryCommentInParent'] ?? null;
		$query_comment_not_parent         = $body['queryCommentNotParent'] ?? null;
		$query_comment_post_id            = $body['queryCommentPostID'] ?? null;
		$query_comment_id                 = $body['queryCommentID'] ?? null;
		$query_comment_not_id             = $body['queryCommentNotID'] ?? null;
		$query_comment_include_unapproved = $body['queryCommentIncludeUnapproved'] ?? null;
		$query_comment_karma              = $body['queryCommentKarma'] ?? null;
		$query_author_email               = $body['queryAuthorEmail'] ?? null;
		$query_author_url                 = $body['queryAuthorURL'] ?? null;
		$query_comment_author_in          = $body['queryCommentAuthorIn'] ?? null;
		$query_comment_author_not_in      = $body['queryCommentAuthorNotIn'] ?? null;

		$query_woo_type              = $body['queryWooType'] ?? null;
		$query_woo_parent_exclude    = $body['queryWooParentExclude'] ?? null;
		$query_woo_sku               = $body['queryWooSKU'] ?? null;
		$query_woo_tag               = $body['queryWooTag'] ?? null;
		$query_woo_category          = $body['queryWooCategory'] ?? null;
		$query_woo_width             = $body['queryWooWidth'] ?? null;
		$query_woo_height            = $body['queryWooHeight'] ?? null;
		$query_woo_weight            = $body['queryWooWeight'] ?? null;
		$query_woo_length            = $body['queryWooLength'] ?? null;
		$query_woo_price             = $body['queryWooPrice'] ?? null;
		$query_woo_regular_price     = $body['queryWooRegularPrice'] ?? null;
		$query_woo_sale_price        = $body['queryWooSalePrice'] ?? null;
		$query_woo_total_sales       = $body['queryWooTotalSales'] ?? null;
		$query_woo_virtual           = $body['queryWooVirtual'] ?? null;
		$query_woo_downloadable      = $body['queryWooDownloadable'] ?? null;
		$query_woo_featured          = $body['queryWooFeatured'] ?? null;
		$query_woo_sold_individually = $body['queryWooSoldIndividually'] ?? null;
		$query_woo_manage_stock      = $body['queryWooManageStock'] ?? null;
		$query_woo_reviews_allowed   = $body['queryWooReviewsAllowed'] ?? null;
		$query_woo_backorders        = $body['queryWooBackorders'] ?? null;
		$query_woo_visibility        = $body['queryWooVisibility'] ?? null;
		$query_woo_stock_quantity    = $body['queryWooStockQuantity'] ?? null;
		$query_woo_stock_status      = $body['queryWooStockStatus'] ?? null;
		$query_woo_tax_status        = $body['queryWooTaxStatus'] ?? null;
		$query_woo_tax_class         = $body['queryWooTaxClass'] ?? null;
		$query_woo_shipping_class    = $body['queryWooShippingClass'] ?? null;
		$query_woo_download_limit    = $body['queryWooDownloadLimit'] ?? null;
		$query_woo_download_expiry   = $body['queryWooDownloadExpiry'] ?? null;
		$query_woo_average_rating    = $body['queryWooAverageRating'] ?? null;
		$query_woo_review_count      = $body['queryWooReviewCount'] ?? null;
		$query_woo_date_created      = $body['queryWooDateCreated'] ?? null;
		$query_woo_date_modified     = $body['queryWooDateModified'] ?? null;
		$query_woo_date_on_sale_from = $body['queryWooDateOnSaleFrom'] ?? null;
		$query_woo_date_on_sale_to   = $body['queryWooDateOnSaleTo'] ?? null;

		$block_context = array();

		if ( isset( $body['taxterms'] ) && $body['taxterms'] ) {
			$block_context['taxterms'] = $body['taxterms'];
		}

		$args_class = new \Cwicly_Query_Args( $block_context );
		$args       = $args_class->query_preparation(
			$query_type,
			$post_id,
			$query_max_items,
			$query_sticky,
			'',
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
			false
		);

		$query = '';

		if ( isset( $args['tax_query'] ) ) {
			foreach ( $args['tax_query'] as $index => $tax_query ) {
				if ( 'relation' !== $index ) {
					if ( is_string( $index ) && isset( $tax_query->taxonomy ) ) {
						if ( ! isset( $tax_query->terms ) || ( isset( $tax_query->terms ) && ! $tax_query->terms ) ) {
							if ( 'relation' !== $index ) {
								$args['tax_query']->$index->operator = 'XXX';
							}
						}
					} elseif ( isset( $tax_query['taxonomy'] ) && ( ! isset( $tax_query['terms'] ) || ( isset( $tax_query['terms'] ) && ! $tax_query['terms'] ) ) ) {
						if ( 'relation' !== $index ) {
							$args['tax_query'][ $index ]['operator'] = 'XXX';
						}
					}
				}
			}
		}
		if ( isset( $args['meta_query'] ) ) {
			foreach ( $args['meta_query'] as $index => $meta_query ) {
				if ( 'relation' !== $index ) {
					if ( is_string( $index ) ) {
						if ( ! isset( $meta_query->value ) || ! Helpers::check_if_exists( $meta_query->value ) ) {
							if ( 'relation' !== $index ) {
								$args['meta_query']->$index->value = array();
							}
						}
					} elseif ( ! isset( $meta_query['value'] ) || ! Helpers::check_if_exists( $meta_query['value'] ) ) {
						if ( 'relation' !== $index ) {
							$args['meta_query'][ $index ]['value'] = array();
						}
					}
				}
			}
		}

		add_filter( 'posts_results', array( '\Cwicly\Helpers', 'remove_content_field' ), 10, 2 );

		if ( 'posts' === $query_type ) {
			if ( $body['queryMake'] ) {
				$query_prep  = new \WP_Query( $args );
				$posts_query = $query_prep->posts;
				$query       = array();
				foreach ( $posts_query as $post ) {
					$poster                     = (array) $post;
					$poster['cc_featuredimage'] = get_the_post_thumbnail_url( $poster['ID'], 'full' );
					$query[]                    = (object) $poster;
				}
			}
		} elseif ( 'terms' === $query_type ) {
			if ( $body['queryMake'] ) {
				$query = new \WP_Term_Query( $args );
			}
		} elseif ( 'users' === $query_type ) {
			if ( $body['queryMake'] ) {
				$query_prep = new \WP_User_Query( $args );
				$query      = $query_prep->get_results();
			}
		} elseif ( 'comments' === $query_type ) {
			if ( $body['queryMake'] ) {
				$query = new \WP_Comment_Query( $args );
				if ( isset( $args['hierarchical'] ) && 'flat' === $args['hierarchical'] ) {
					$query->comments = \Cwicly\Comments::query_comment_tree( $query->comments );
				}
			}
		} elseif ( 'products' === $query_type ) {
			if ( $body['queryMake'] ) {
				$query_prep     = new \WC_Product_Query( $args );
				$products_query = $query_prep->get_products();
				$query          = array();

				$ids = array();
				foreach ( $products_query as $product ) {
					$ids[]     = $product->get_id();
					$producter = $this->product_preparation( $product, $request );

					if ( 'grouped' === $product->get_type() ) {
						$children = $product->get_children();
						foreach ( $children as $child ) {
							if ( $ids && ! in_array( $child, $ids ) ) {
								$producter['woogroups_info'][] = $this->product_preparation( wc_get_product( $child ), $request );
							}
						}
					}

					$query[] = $producter;
				}
			}
		}

		remove_filter( 'posts_results', array( '\Cwicly\Helpers', 'remove_content_field' ), 10, 2 );
		$final = array(
			'query'  => $query,
			'result' => var_export( $args, true ),
		);

		return new \WP_REST_Response( $final, 200 );
	}

	/**
	 * Get all WooCommerce product info necessary for backend
	 *
	 * @param object          $product WC_Product The product object.
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function product_preparation( $product, $request ) {
		$producter                     = $product->get_data();
		$producter['cc_featuredimage'] = get_the_post_thumbnail_url( $producter['id'], 'full' );

		$original       = array();
		$main_image     = array();
		$main_image[]   = $product->get_image_id();
		$gallery_images = $product->get_gallery_image_ids();
		$attachment_ids = array_merge( $main_image, $gallery_images );
		foreach ( $attachment_ids as $images ) {
			$original[] = array(
				'src'     => wp_get_attachment_url( $images ),
				'name'    => get_the_title( $images ),
				'caption' => wp_get_attachment_caption( $images ),
			);
		}

		$producter['cc_images'] = $original;

		$producter['wooFields'] = array();

		if ( $product ) {
			$price                                     = $product->get_price();
			$saleprice                                 = $product->get_sale_price();
			$regularprice                              = $product->get_regular_price();
			$producter['wooFields']['salepercentage']  = html_entity_decode( wp_strip_all_tags( WooCommerce::percentage_calculator( $product ) ) );
			$producter['wooFields']['price']           = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $price, '', $product ) ) );
			$producter['wooFields']['price_formatted'] = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $price, 'formatted', $product ) ) );
			$producter['wooFields']['price_formattedcurrency']           = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $price, 'formattedcurrency', $product ) ) );
			$producter['wooFields']['price_formattedtax']                = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $price, 'formattedtax', $product ) ) );
			$producter['wooFields']['price_formattedtaxcurrency']        = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $price, 'formattedtaxcurrency', $product ) ) );
			$producter['wooFields']['saleprice']                         = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $saleprice, '', $product ) ) );
			$producter['wooFields']['saleprice_formatted']               = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $saleprice, 'formatted', $product ) ) );
			$producter['wooFields']['saleprice_formattedcurrency']       = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $saleprice, 'formattedcurrency', $product ) ) );
			$producter['wooFields']['saleprice_formattedtax']            = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $saleprice, 'formattedtax', $product ) ) );
			$producter['wooFields']['saleprice_formattedtaxcurrency']    = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $saleprice, 'formattedtaxcurrency', $product ) ) );
			$producter['wooFields']['regularprice']                      = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $regularprice, '', $product ) ) );
			$producter['wooFields']['regularprice_formatted']            = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $regularprice, 'formatted', $product ) ) );
			$producter['wooFields']['regularprice_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $regularprice, 'formattedcurrency', $product ) ) );
			$producter['wooFields']['regularprice_formattedtax']         = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $regularprice, 'formattedtax', $product ) ) );
			$producter['wooFields']['regularprice_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $regularprice, 'formattedtaxcurrency', $product ) ) );
		}
		$producter['wooFields']['currency']       = get_woocommerce_currency();
		$producter['wooFields']['currencysymbol'] = html_entity_decode( get_woocommerce_currency_symbol() );
		if ( $product && 'variable' === $product->get_type() ) {
			$variationminprice      = $product->get_variation_price();
			$variationmaxprice      = $product->get_variation_price( 'max' );
			$variationregnminprice  = $product->get_variation_regular_price();
			$variationregnmaxprice  = $product->get_variation_regular_price( 'max' );
			$variationsalenminprice = $product->get_variation_sale_price();
			$variationsalenmaxprice = $product->get_variation_sale_price( 'max' );

			$producter['wooFields']['variationmin']                      = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationminprice, '', $product ) ) );
			$producter['wooFields']['variationmin_formatted']            = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationminprice, 'formatted', $product ) ) );
			$producter['wooFields']['variationmin_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationminprice, 'formattedcurrency', $product ) ) );
			$producter['wooFields']['variationmin_formattedtax']         = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationminprice, 'formattedtax', $product ) ) );
			$producter['wooFields']['variationmin_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationminprice, 'formattedtaxcurrency', $product ) ) );
			$producter['wooFields']['variationmax']                      = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationmaxprice, 'formatted', $product ) ) );
			$producter['wooFields']['variationmax_formatted']            = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationmaxprice, 'formatted', $product ) ) );
			$producter['wooFields']['variationmax_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationmaxprice, 'formattedcurrency', $product ) ) );
			$producter['wooFields']['variationmax_formattedtax']         = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationmaxprice, 'formattedtax', $product ) ) );
			$producter['wooFields']['variationmax_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationmaxprice, 'formattedtaxcurrency', $product ) ) );

			$producter['wooFields']['variationregmin']                      = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnminprice, '', $product ) ) );
			$producter['wooFields']['variationregmin_formatted']            = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnminprice, 'formatted', $product ) ) );
			$producter['wooFields']['variationregmin_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnminprice, 'formattedcurrency', $product ) ) );
			$producter['wooFields']['variationregmin_formattedtax']         = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnminprice, 'formattedtax', $product ) ) );
			$producter['wooFields']['variationregmin_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnminprice, 'formattedtaxcurrency', $product ) ) );
			$producter['wooFields']['variationregmax']                      = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnmaxprice, 'formatted', $product ) ) );
			$producter['wooFields']['variationregmax_formatted']            = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnmaxprice, 'formatted', $product ) ) );
			$producter['wooFields']['variationregmax_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnmaxprice, 'formattedcurrency', $product ) ) );
			$producter['wooFields']['variationregmax_formattedtax']         = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnmaxprice, 'formattedtax', $product ) ) );
			$producter['wooFields']['variationregmax_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationregnmaxprice, 'formattedtaxcurrency', $product ) ) );

			$producter['wooFields']['variationsalemin']                      = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenminprice, '', $product ) ) );
			$producter['wooFields']['variationsalemin_formatted']            = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenminprice, 'formatted', $product ) ) );
			$producter['wooFields']['variationsalemin_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenminprice, 'formattedcurrency', $product ) ) );
			$producter['wooFields']['variationsalemin_formattedtax']         = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenminprice, 'formattedtax', $product ) ) );
			$producter['wooFields']['variationsalemin_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenminprice, 'formattedtaxcurrency', $product ) ) );
			$producter['wooFields']['variationsalemax']                      = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenmaxprice, 'formatted', $product ) ) );
			$producter['wooFields']['variationsalemax_formatted']            = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenmaxprice, 'formatted', $product ) ) );
			$producter['wooFields']['variationsalemax_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenmaxprice, 'formattedcurrency', $product ) ) );
			$producter['wooFields']['variationsalemax_formattedtax']         = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenmaxprice, 'formattedtax', $product ) ) );
			$producter['wooFields']['variationsalemax_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( WooCommerce::dynamic_price( $variationsalenmaxprice, 'formattedtaxcurrency', $product ) ) );
		}

		if ( $product && 'variable' === $product->get_type() ) {
			$variations_attributes_and_values = array();
			foreach ( $product->get_variation_attributes() as $taxonomy => $terms_slug ) {
				// To get the attribute label (in WooCommerce 3+).
				$taxonomy_label = wc_attribute_label( $taxonomy, $product );

				// Setting some data in an array.
				$variations_attributes_and_values[ $taxonomy ] = array( 'label' => $taxonomy_label );
				if ( null !== wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) ) ) {
					$variations_attributes_and_values[ $taxonomy ]['type'] = wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) )->type;
				} else {
					$variations_attributes_and_values[ $taxonomy ]['type'] = null;
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
						if ( taxonomy_exists( $taxonomy ) ) {
							// Getting the term object from the slug.

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
							$variations_attributes_and_values[ $taxonomy ]['terms'][] = array(
								'name' => $term_name,
								'slug' => $term_slug,
								'type' => $term_type,
							);
						}
					}
				} else {
					foreach ( $terms_slug as $term ) {
						$variations_attributes_and_values[ $taxonomy ]['terms'][ $term ] = array(
							'name' => $term,
							'slug' => $term,
							'type' => null,
						);
					}
				}
			}
			$producter['woovariables'] = $variations_attributes_and_values;
		} else {
			$producter['woovariables'] = array();
		}

		if ( 'grouped' === $product->get_type() ) {
			$products                    = $product->get_children();
			$producter['woogroups']      = $products;
			$producter['woogroups_info'] = array();
		} else {
			$producter['woogroups'] = array();
		}

		if ( has_post_thumbnail( $producter['id'] ) ) {
			$producter['cc_featuredimage'] = get_the_post_thumbnail_url( $producter['id'] );
		} else {
			$producter['cc_featuredimage'] = 'nofeaturedimage';
		}

		return $producter;
	}
}
