<?php
/**
 * Cwicly Frontend Query API.
 *
 * Author: Cwicly
 * Author URI: https://cwicly.com
 * Date: 2023-09-18
 * Work based on the original file from Aucor.
 *
 * Plugin Name: WP_Query Route To REST API
 * Description: Adds new route /wp-json/wp_query/args/ to REST API
 * Author: Aucor
 * Author URI: https://www.aucor.fi/
 * Version: 1.3.2
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package cwicly
 **/

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\StoreApi\Utilities\QuantityLimits;

/**
 * Cwicly Frontend Query API.
 */
class Main_Query_API extends \WP_REST_Posts_Controller {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register REST route.
		$this->register_routes();
	}

	/**
	 * Register read-only /wp_query/args/ route.
	 */
	public function register_routes() {
		register_rest_route(
			'wp_query',
			'args',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			)
		);

		register_rest_route(
			'wp_query',
			'queries',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			)
		);

		register_rest_route(
			'wp_query',
			'cart_items',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cart_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			)
		);
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return apply_filters( 'cwicly/query/api/permissions', true, $request );
	}

	/**
	 * Sanitize Query Parameters.
	 *
	 * @param object $parameters Query parameters.
	 */
	public function sanitize_query_parameters( $parameters ) {
		$default_args = array(
			'post_status'  => 'publish',
			'has_password' => false,
		);
		$default_args = apply_filters( 'cwicly/query/api/default_args', $default_args );

		// Allow these args => what isn't explicitly allowed, is forbidden.
		$allowed_args = array(
			'category',
			'type',
			'p',
			'name',
			'title',
			'page_id',
			'pagename',
			'post_parent',
			'post_parent__in',
			'post_parent__not_in',
			'post__in',
			'post__not_in',
			'post_name__in',
			'post_type',
			'posts_per_page',
			'offset',
			'paged',
			'page',
			'ignore_sticky_posts',
			'order',
			'orderby',
			'year',
			'monthnum',
			'w',
			'day',
			'hour',
			'minute',
			'second',
			'm',
			'date_query',
			'inclusive',
			'compare',
			'column',
			'relation',
			'post_mime_type',
			'limit', // Woo.
			'featured', // Woo.
			'downloadable', // Woo.
			'stock_status', // Woo.
			'search',
			's',
			'lang', // Polylang.
			'number',
			'taxonomy',
			'slug',
			'include',
			'exclude',
			'exclude_tree',
			'child_of',
			'parent',
			'childless',
			'hide_empty',
			'name__like',
			'description__like',
			'maxitems',
			'term',
		);

		// Filter Authors.
		$allow_authors = apply_filters( 'cwicly/query/api/allow_authors', true );
		if ( $allow_authors ) {
			$allowed_args[] = 'author';
			$allowed_args[] = 'author_name';
			$allowed_args[] = 'author__in';
			$allowed_args[] = 'author__not_in';
		}

		// Filter Meta.
		$allow_meta = apply_filters( 'cwicly/query/api/allow_meta', true );
		if ( $allow_meta ) {
			$allowed_args[] = 'meta_key';
			$allowed_args[] = 'meta_value';
			$allowed_args[] = 'meta_value_num';
			$allowed_args[] = 'meta_compare';
			$allowed_args[] = 'meta_query';
		}

		// Filter Search.
		$allow_search = apply_filters( 'cwicly/query/api/allow_search', true );
		if ( $allow_search ) {
			$allowed_args[] = 's';
			$allowed_args[] = 'search';
		}

		// Filter Taxonomies.
		$allow_taxonomies = apply_filters( 'cwicly/query/api/allow_taxonomies', true );
		if ( $allow_taxonomies ) {
			$allowed_args[] = 'cat';
			$allowed_args[] = 'category_name';
			$allowed_args[] = 'category__and';
			$allowed_args[] = 'category__in';
			$allowed_args[] = 'category__not_in';
			$allowed_args[] = 'tag';
			$allowed_args[] = 'tag_id';
			$allowed_args[] = 'tag__and';
			$allowed_args[] = 'tag__in';
			$allowed_args[] = 'tag__not_in';
			$allowed_args[] = 'tag_slug__and';
			$allowed_args[] = 'tag_slug__in';
			$allowed_args[] = 'tax_query';
		}

		$allowed_args = apply_filters( 'cwicly/query/api/allowed_args', $allowed_args );

		// Args from url.
		$query_args = array();

		foreach ( $parameters as $key => $value ) {
			// Skip keys that are not explicitly allowed.
			if ( in_array( $key, $allowed_args ) ) {
				switch ( $key ) {
					// Posts type restrictions.
					case 'post_type':
						// Multiple values.
						if ( is_array( $value ) ) {
							foreach ( $value as $sub_key => $sub_value ) {
								// Bail if there's even one post type that's not allowed.
								if ( ! $this->check_is_post_type_allowed( $sub_value ) ) {
									$query_args[ $key ] = 'post';

									break;
								}
							}

							// Value "any".
						} elseif ( 'any' == $value ) {
							$query_args[ $key ] = $this->_get_allowed_post_types();

							break;

							// Single value.
						} elseif ( ! $this->check_is_post_type_allowed( $value ) ) {
							$query_args[ $key ] = 'post';

							break;
						}

						$query_args[ $key ] = $value;

						break;

						// Posts per page restrictions.
					case 'posts_per_page':
						$max_pages = apply_filters( 'cc_wp_query_max_posts_per_page', 50 );
						if ( $value <= 0 || $value > $max_pages ) {
							$query_args[ $key ] = $max_pages;

							break;
						}
						$query_args[ $key ] = $value;

						break;

						// Posts per page restrictions.
					case 'posts_status':
						// Multiple values.
						if ( is_array( $value ) ) {
							foreach ( $value as $sub_key => $sub_value ) {
								// Bail if there's even one post status that's not allowed.
								if ( ! $this->check_is_post_status_allowed( $sub_value ) ) {
									$query_args[ $key ] = 'publish';

									break;
								}
							}

							// Value "any".
						} elseif ( 'any' == $value ) {
							$query_args[ $key ] = $this->_get_allowed_post_status();

							break;

							// Single value.
						} elseif ( ! $this->check_is_post_status_allowed( $value ) ) {
							$query_args[ $key ] = 'publish';

							break;
						}

						$query_args[ $key ] = $value;

						break;

						// Set given value.
					default:
						$query_args[ $key ] = $value;

						break;
				}
			}
		}

		// Combine defaults and query_args.
		$args = wp_parse_args( $query_args, $default_args );

		// Make all the values filterable.
		foreach ( $args as $key => $value ) {
			$args[ $key ] = apply_filters( 'cwicly/query/api/arg_value', $value, $key, $args );
		}

		return $args;
	}

	/**
	 * Build the query from the given parameters.
	 *
	 * @param  array  $query_args Query arguments.
	 * @param  string $query_type Query type.
	 * @param  string $query_block_id Query block id.
	 */
	public function build_query( $query_args, $query_type, $query_block_id ) {
		if ( isset( $query_args['tax_query'] ) ) {
			foreach ( $query_args['tax_query'] as $index => $tax_query ) {
				if ( 'relation' !== $index ) {
					if ( is_string( $index ) ) {
						if ( isset( $tax_query->taxonomy ) && $tax_query->taxonomy && ( ! isset( $tax_query->terms ) || ( isset( $tax_query->terms ) && ! isset( $tax_query->terms ) ) ) ) {
							if ( 'relation' !== $index ) {
								$query_args['tax_query']->$index->operator = 'XXX';
							}
						}
					} elseif ( isset( $tax_query['taxonomy'] ) && $tax_query['taxonomy'] && ( ! isset( $tax_query['terms'] ) || ( isset( $tax_query['terms'] ) && ! $tax_query['terms'] ) ) ) {
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
						if ( ! isset( $meta_query->value ) || ! Helpers::check_if_exists( $meta_query->value ) ) {
							if ( 'relation' !== $index ) {
								$query_args['meta_query']->$index->value = array();
							}
						}
					} elseif ( ! isset( $meta_query['value'] ) || ! Helpers::check_if_exists( $meta_query['value'] ) ) {
						if ( 'relation' !== $index ) {
							$query_args['meta_query'][ $index ]['value'] = array();
						}
					}
				}
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
		} elseif ( ( 'terms' === $query_type || 'users' === $query_type ) && isset( $query_args['number'] ) && $query_args['number'] && isset( $query_args['offset'] ) && $query_args['offset'] ) {
			if ( isset( $query_args['paged'] ) && $query_args['paged'] ) {
				$query_args['offset'] = ( intval( $query_args['number'] ) * ( intval( $query_args['paged'] ) - 1 ) ) + ( intval( $query_args['offset'] ) );
			}
		} elseif ( ( 'terms' === $query_type || 'users' === $query_type ) && isset( $query_args['number'] ) && $query_args['number'] ) {
			if ( isset( $query_args['paged'] ) && $query_args['paged'] ) {
				$query_args['offset'] = ( intval( $query_args['number'] ) * ( intval( $query_args['paged'] ) - 1 ) );
			}
		}
		// FOR OFFSETING CORRECTLY.

		$query_args['exclude_content'] = true;
		add_filter( 'posts_results', array( '\Cwicly\Helpers', 'remove_content_field' ), 10, 2 );

		$query_args = apply_filters( 'cwicly/query/args', $query_args, array(), $query_block_id );

		// Relevanssi
		if ( function_exists( 'relevanssi_do_excerpt' ) && isset( $query_args['s'] ) && ! empty( $query_args['s'] ) ) {
			// Adding relevanssi to the query args so that the excerpt is generated correctly.
			$query_args['relevanssi'] = true;
		}

		if ( 'posts' === $query_type ) {
			$query = new \WP_Query( $query_args );
		} elseif ( 'terms' === $query_type ) {
			$query = new \WP_Term_Query( $query_args );
			$count = wp_count_terms( $query_args );

			return array(
				'terms' => $query,
				'total' => $count,
			);
		} elseif ( 'users' === $query_type ) {
			$query = new \WP_User_Query( $query_args );
		} elseif ( 'comments' === $query_type ) {
			$query = new \WP_Comment_Query( $query_args );
		} elseif ( CC_WOOCOMMERCE && 'products' === $query_type ) {
			$query_args['paginate'] = true;
			$query_prep             = wc_get_products( $query_args );

			$query = array();
			foreach ( $query_prep->products as $product ) {
				$query[] = $this->get_item_response( $product );
			}
			$price_range = \Cwicly\WooCommerce::get_filtered_price( $query_args );

			return array(
				'products'    => $query,
				'total'       => $query_prep->total,
				'price_range' => $price_range,
			);
		}

		remove_filter( 'posts_results', array( '\Cwicly\Helpers', 'remove_content_field' ), 10, 2 );

		if ( isset( $query ) ) {
			return $query;
		}
	}

	/**
	 * Build the query for the count.
	 *
	 * @param  array  $query_args Query arguments.
	 * @param  string $query_type Query type.
	 * @param  string $query_block_id Query block id.
	 * @return array              Query results.
	 */
	public function build_query_count( $query_args, $query_type, $query_block_id ) {

		$query_args['return']      = 'ids';
		$query_args['post_status'] = 'publish';

		if ( isset( $query_args['tax_query'] ) ) {
			foreach ( $query_args['tax_query'] as $index => $tax_query ) {
				if ( 'relation' !== $index ) {
					if ( is_string( $index ) ) {
						if ( ! isset( $tax_query->terms ) || ( isset( $tax_query->terms ) && ! $tax_query->terms ) ) {
							if ( 'relation' !== $index ) {
								$query_args['tax_query']->$index->operator = 'XXX';
							}
						}
					} elseif ( ! isset( $tax_query['terms'] ) || ( isset( $tax_query['terms'] ) && ! $tax_query['terms'] ) ) {
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
						if ( ! Helpers::check_if_exists( $meta_query->value ) ) {
							if ( 'relation' !== $index ) {
								$query_args['meta_query']->$index->value = array();
							}
						}
					} elseif ( ! Helpers::check_if_exists( $meta_query['value'] ) ) {
						if ( 'relation' !== $index ) {
							$query_args['meta_query'][ $index ]['value'] = array();
						}
					}
				}
			}
		}

		$query_args = apply_filters( 'cwicly/query/args', $query_args, array(), $query_block_id );

		if ( 'posts' === $query_type ) {
			$query_args['posts_per_page'] = -1;
			$query                        = new \WP_Query( $query_args );

			return $query->found_posts;
		} elseif ( 'terms' === $query_type ) {
			$query = new \WP_Term_Query( $query_args );
		} elseif ( 'users' === $query_type ) {
			$query = new \WP_User_Query( $query_args );
		} elseif ( 'comments' === $query_type ) {
			$query = new \WP_Comment_Query( $query_args );
		} elseif ( CC_WOOCOMMERCE && 'products' === $query_type ) {
			$query_args['paginate'] = true;
			$query_args['limit']    = -1;
			$query_prep             = wc_get_products( $query_args );

			return $query_prep->total;
		}

		return $query;
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 */
	public function get_items( $request ) {
		$sanitized  = $request->sanitize_params();
		$parameters = $request->get_query_params();

		$dynamic_args = '';
		if ( isset( $parameters['template_dynamics'] ) ) {
			$dynamic_args = $this->process_dynamics( json_decode( $parameters['template_dynamics'] ) );
		}

		$query_block_id = '';
		if ( isset( $parameters['query_block_id'] ) ) {
			$query_block_id = $parameters['query_block_id'];
		}

		$query_args = '';
		if ( isset( $parameters['query_args'] ) ) {
			// replace all "<" and ">" since they will otherwise be removed by sanitize_text_field.
			$pre_query_args = str_replace( array( '"<"', '">"' ), array( '"cc_less_than"', '"cc_greater_than"' ), $parameters['query_args'] );
			$pre_query_args = str_replace( array( '"<="', '">="' ), array( '"cc_less_than_equal"', '"cc_greater_than_equal"' ), $parameters['query_args'] );
			$query_args     = sanitize_text_field( $pre_query_args );
			$query_args     = str_replace( array( '"cc_less_than"', '"cc_greater_than"' ), array( '"<"', '">"' ), $query_args );
			$query_args     = str_replace( array( '"cc_less_than_equal"', '"cc_greater_than_equal"' ), array( '"<="', '">="' ), $query_args );
			$query_args     = json_decode( $query_args, true );
			$query_args     = wp_json_encode( $query_args );
			$query_args     = json_decode( $query_args, true );
			$query_args     = $this->sanitize_query_parameters( $query_args );
		}
		$query_type = '';
		if ( isset( $parameters['query_type'] ) ) {
			$query_type = sanitize_text_field( $parameters['query_type'] );
		}

		$body = $request->get_body();
		if ( $body ) {
			$body = json_decode( $body );
		}
		if ( isset( $body->quickCount ) && $body->quickCount ) {
			$quick_count_query = array();
			if ( isset( $body->queries ) && $body->queries ) {
				$all_queries = $body->queries;
				foreach ( $all_queries as $query_id => $filters ) {
					if ( isset( $filters->hash ) ) {
						$transient_id = 'cc-' . $filters->queryType . '-count-' . $query_id . '-' . $filters->hash . '';
						$not_cached   = true;
						if ( isset( $filters->hash ) && $filters->hash ) {
							$transient = get_transient( $transient_id );
							if ( false !== $transient ) {
								$not_cached                     = false;
								$quick_count_query[ $query_id ] = $transient;
							}
						}
						if ( $not_cached ) {
							$query_type                     = $filters->queryType;
							$quick_count_query[ $query_id ] = array();
							foreach ( $filters->queries as $index => $query ) {
								$spec_args                                      = json_decode( $query->args, true );
								$spec_args                                      = wp_json_encode( $spec_args );
								$spec_args                                      = json_decode( $spec_args, true );
								$spec_args                                      = $this->sanitize_query_parameters( $spec_args );
								$quick_count_query[ $query_id ][ $query->hash ] = $this->build_query_count( $spec_args, $query_type, $query_block_id );
							}
							set_transient( $transient_id, $quick_count_query[ $query_id ], 24 * HOUR_IN_SECONDS );
							$all_transients = get_option( 'cwicly_rest_transients' );
							if ( $all_transients ) {
								$all_transients[] = $transient_id;
								update_option( 'cwicly_rest_transients', $all_transients );
							} else {
								update_option( 'cwicly_rest_transients', array( $transient_id ) );
							}
						}
					}
				}
			}

			return $this->get_response( '', '', '', $quick_count_query );
		} else {
			if ( 'terms' === $query_type ) {
				$all_query   = $this->build_query( $query_args, $query_type, $query_block_id );
				$wp_query    = $all_query['terms'];
				$terms_total = $all_query['total'];
			} elseif ( 'products' != $query_type ) {
				$wp_query                 = $this->build_query( $query_args, $query_type, $query_block_id );
				$new_query_args           = $query_args;
				$new_query_args['fields'] = 'ids';
				$new_query_args['number'] = '';
				$new_query_args['count']  = true;
				$comments_total           = get_comments( $new_query_args );
			} elseif ( CC_WOOCOMMERCE ) {
				$all_query      = $this->build_query( $query_args, $query_type, $query_block_id );
				$wp_query       = $all_query['products'];
				$wc_query_total = $all_query['total'];
				$price_range    = $all_query['price_range'];
			}

			$data = array();
			$data = apply_filters( 'cwicly/query/api/default_data', $data );

			if ( 'posts' === $query_type ) {
				while ( $wp_query->have_posts() ) :
					$wp_query->the_post();

					// Extra safety check for unallowed posts.
					if ( $this->check_is_post_allowed( $wp_query->post, false ) ) {
						// After loop hook.
						$data = apply_filters( 'cwicly/query/api/after_loop', $data, $wp_query, $query_args );

						// Update properties post_type and meta to match current post_type.
						// This is kind of hacky, but the parent WP_REST_Posts_Controller.
						// does all kinds of assumptions from properties $post_type and.
						// $meta so we need to update it several times.
						// Allow filtering by meta: default yes.
						if ( apply_filters( 'cwicly/query/api/allow_meta_filter', true ) ) {
							$this->post_type = $wp_query->post->post_type;
							$this->meta      = new \WP_REST_Post_Meta_Fields( $wp_query->post->post_type );
						}

						// Use parent class functions to prepare the post.
						if ( apply_filters( 'cwicly/query/api/use_parent_class', true ) ) {
							$itemdata = parent::prepare_item_for_response( $wp_query->post, $request );
							$prep     = parent::prepare_response_for_collection( $itemdata );
							if ( isset( $prep['featured_media'] ) && $prep['featured_media'] ) {
								$prep['featured_image'] = $this->get_image( $prep['featured_media'] );
							}
							if ( isset( $dynamic_args['specific'] ) && $dynamic_args['specific'] ) {
								$prep['dynamic_specifics'] = $this->process_specific_dynamics( $dynamic_args['specific'], $wp_query->post->ID );
							}
							$prep['terms'] = $this->get_terms( $wp_query->post->ID );
							if ( isset( $prep['_links'] ) && $prep['_links'] ) {
								unset( $prep['_links'] );
							}
							$data[] = $prep;
						}
					}

				endwhile;
			} elseif ( 'terms' === $query_type ) {
				if ( ! empty( $wp_query->terms ) ) {
					foreach ( $wp_query->terms as $index => $term ) {
						$data = apply_filters( 'cwicly/query/api/after_loop', $data, $wp_query, $query_args );

						if ( apply_filters( 'cwicly/query/api/allow_meta_filter', true ) ) {
							$this->post_type = 'term';
							$this->meta      = new \WP_REST_Term_Meta_Fields( $term->slug );
						}

						ini_set( 'display_errors', 0 );

						$wp_rest_terms = new \WP_REST_Terms_Controller( $term->slug );
						// Use parent class functions to prepare the post.
						if ( apply_filters( 'cwicly/query/api/use_parent_class', true ) ) {
							$itemdata = $wp_rest_terms->prepare_item_for_response( $term, $request );
							$prep     = parent::prepare_response_for_collection( $itemdata );
							if ( isset( $prep['featured_media'] ) && $prep['featured_media'] ) {
								$prep['featured_image'] = $this->get_image( $prep['featured_media'] );
							}
							if ( isset( $dynamic_args['specific'] ) && $dynamic_args['specific'] ) {
								$prep['dynamic_specifics'] = $this->process_specific_dynamics( $dynamic_args['specific'], null, 'term', $term );
							}
							$data[] = $prep;
						}
					}
				}
			} elseif ( 'users' === $query_type ) {
				$result = $wp_query->get_results();
				if ( $result ) {
					if ( ! empty( $result ) ) {
						foreach ( $result as $index => $user ) {
							$data = apply_filters( 'cwicly/query/api/after_loop', $data, $wp_query, $query_args );

							if ( apply_filters( 'cwicly/query/api/allow_meta_filter', true ) ) {
								$this->post_type = 'term';
								$this->meta      = new \WP_REST_Post_Meta_Fields( 'term' );
							}

							$wp_rest_users = new \WP_REST_Users_Controller();
							// Use parent class functions to prepare the post.
							if ( apply_filters( 'cwicly/query/api/use_parent_class', true ) ) {
								$itemdata = $wp_rest_users->prepare_item_for_response( $user, $request );
								$prep     = parent::prepare_response_for_collection( $itemdata );
								if ( isset( $prep['featured_media'] ) && $prep['featured_media'] ) {
									$prep['featured_image'] = $this->get_image( $prep['featured_media'] );
								}
								if ( isset( $dynamic_args['specific'] ) && $dynamic_args['specific'] ) {
									$prep['dynamic_specifics'] = $this->process_specific_dynamics( $dynamic_args['specific'], null, 'user', $user );
								}
								$prep['display_name'] = $user->display_name;
								$data[]               = $prep;
							}
						}
					}
				}
			} elseif ( 'comments' === $query_type ) {
				$result = $wp_query->get_comments();
				if ( $result ) {
					if ( ! empty( $result ) ) {
						foreach ( $result as $index => $comment ) {
							$data = apply_filters( 'cwicly/query/api/after_loop', $data, $wp_query, $query_args );

							if ( apply_filters( 'cwicly/query/api/allow_meta_filter', true ) ) {
								$this->post_type = 'comment';
								$this->meta      = new \WP_REST_Post_Meta_Fields( 'comment' );
							}

							$wp_rest_comments = new \WP_REST_Comments_Controller();
							// Use parent class functions to prepare the post.
							if ( apply_filters( 'cwicly/query/api/use_parent_class', true ) ) {
								$itemdata = $wp_rest_comments->prepare_item_for_response( $comment, $request );
								$prep     = parent::prepare_response_for_collection( $itemdata );
								if ( isset( $prep['featured_media'] ) && $prep['featured_media'] ) {
									$prep['featured_image'] = $this->get_image( $prep['featured_media'] );
								}
								if ( isset( $dynamic_args['specific'] ) && $dynamic_args['specific'] ) {
									$prep['dynamic_specifics'] = $this->process_specific_dynamics( $dynamic_args['specific'], null, 'comment', $comment );
								}
								$data[] = $prep;
							}
						}
					}
				}
			} elseif ( isset( $wp_query ) ) {
				foreach ( $wp_query as $product ) {
					// Extra safety check for unallowed posts.
					if ( $this->check_is_post_allowed( $product, true ) ) {
						// After loop hook.
						$data = apply_filters( 'cwicly/query/api/after_loop', $data, $wp_query, $query_args );

						// Update properties post_type and meta to match current post_type.
						// This is kind of hacky, but the parent WP_REST_Posts_Controller.
						// does all kinds of assumptions from properties $post_type and.
						// $meta so we need to update it several times.
						// Allow filtering by meta: default yes.
						if ( apply_filters( 'cwicly/query/api/allow_meta_filter', true ) ) {
							$this->post_type = 'product';
							$this->meta      = new \WP_REST_Post_Meta_Fields( 'product' );
						}

						// Use parent class functions to prepare the post.
						if ( apply_filters( 'cwicly/query/api/use_parent_class', true ) ) {
							$itemdata = rest_ensure_response( $product );
							$prep     = parent::prepare_response_for_collection( $itemdata );
							if ( isset( $dynamic_args['specific'] ) && $dynamic_args['specific'] ) {
								$prep['dynamic_specifics'] = $this->process_specific_dynamics( $dynamic_args['specific'], $product['id'] );
							}
							$data[] = $prep;
						}
					}
				}
			}

			if ( isset( $parameters['current_id'] ) ) {
				global $post;
				$post = get_post( (int) $parameters['current_id'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}

			$final            = array();
			$final['success'] = true;
			$final['general'] = $this->general_prep();
			if ( isset( $parameters['query_dynamics'] ) ) {
				$final['dynamic_specifics'] = $this->process_specific_dynamics( json_decode( sanitize_text_field( $parameters['query_dynamics'] ) ), get_the_ID() );
			}
			$final['query'] = $data;
			if ( 'terms' === $query_type ) {
				$final['total'] = $terms_total;
			} elseif ( 'users' === $query_type ) {
				$final['total'] = $wp_query->get_total();
			} elseif ( 'comments' === $query_type ) {
				$final['total'] = $comments_total;
			} elseif ( 'products' != $query_type ) {
				$final['total'] = $wp_query->found_posts;
			} else {
				if ( isset( $wc_query_total ) ) {
					$final['total'] = $wc_query_total;
				}
				if ( isset( $price_range ) ) {
					$final['pricerange'] = $price_range;
				}
			}
			if ( isset( $dynamic_args['global'] ) && $dynamic_args['global'] ) {
				$final['dynamicData'] = $dynamic_args['global'];
			}

			$posts_per_page = get_option( 'posts_per_page' );
			if ( 'products' === $query_type && isset( $query_args['limit'] ) && $query_args['limit'] ) {
				$final['postsPerPage'] = $query_args['limit'];
			} elseif ( isset( $query_args['number'] ) && $query_args['number'] ) {
				$final['postsPerPage'] = $query_args['number'];
			} elseif ( isset( $query_args['posts_per_page'] ) && $query_args['posts_per_page'] ) {
				$final['postsPerPage'] = $query_args['posts_per_page'];
			} else {
				$final['postsPerPage'] = $posts_per_page;
			}

			if ( ! isset( $wp_query ) ) {
				$wp_query = array();
			}

			return $this->get_response( $request, $query_args, $wp_query, $final );
		}
	}

	/**
	 * Get terms.
	 *
	 * @param int $id Terms ID.
	 */
	protected function get_terms( $id ) {
		$taxonomies = get_taxonomies();
		$term_list  = array();
		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = get_the_terms( $id, $taxonomy );
			if ( $post_terms ) {
				foreach ( $post_terms as $term ) {
					$term_list[] = $term->term_id;
				}
			}
		}

		return $term_list;
	}

	/**
	 * Get response
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @param array           $args WP_Query args.
	 * @param WP_Query        $wp_query WP_Query object.
	 * @param array           $data response data.
	 *
	 * @return WP_REST_Response
	 */
	protected function get_response( $request, $args, $wp_query, $data ) {
		$response = new \WP_REST_Response( $data, 200 );

		return $response;
	}

	/**
	 * Get allowed post status
	 *
	 * @access protected
	 *
	 * @return array $post_status
	 */
	protected function _get_allowed_post_status() {
		$post_status = array( 'publish' );

		return apply_filters( 'cwicly/query/api/allowed_status', $post_status );
	}

	/**
	 * Check is post status allowed
	 *
	 * @access protected
	 * @param array $post_status The post status.
	 * @return abool
	 */
	protected function check_is_post_status_allowed( $post_status ) {
		return in_array( $post_status, $this->_get_allowed_post_status() );
	}

	/**
	 * Get allowed post types
	 *
	 * @access protected
	 *
	 * @return array $post_types
	 */
	protected function _get_allowed_post_types() {
		$post_types = get_post_types( array( 'show_in_rest' => true ) );

		return apply_filters( 'cwicly/query/api/allowed_post_types', $post_types );
	}

	/**
	 * Check is post type allowed
	 *
	 * @access protected
	 * @param array $post_type The post type.
	 * @return abool
	 */
	protected function check_is_post_type_allowed( $post_type ) {
		return in_array( $post_type, $this->_get_allowed_post_types() );
	}

	/**
	 * Post is allowed
	 *
	 * @access protected
	 * @param object $post The post object.
	 * @param bool   $is_product Is product.
	 * @return bool
	 */
	protected function check_is_post_allowed( $post, $is_product ) {
		if ( $is_product ) {
			return apply_filters( 'cwicly/query/api/post_allowed', true, $post );
		} else {
			// Is allowed post_status.
			if ( ! $this->check_is_post_status_allowed( $post->post_status ) ) {
				return false;
			}

			// Is allowed post_type.
			if ( ! $this->check_is_post_type_allowed( $post->post_type ) ) {
				return false;
			}
		}

		return apply_filters( 'cwicly/query/api/post_allowed', true, $post );
	}

	/**
	 * General Prep
	 *
	 * @access protected
	 *
	 * @return bool
	 */
	protected function general_prep() {
		$final           = array();
		$final['userID'] = get_current_user_id();

		return $final;
	}

	/**
	 * Process dynamics
	 *
	 * @access protected
	 *
	 * @param array $args The args.
	 *
	 * @return array $final
	 */
	protected function process_dynamics( $args ) {
		$current_user = wp_get_current_user();
		$final        = array(
			'global'   => array(),
			'specific' => array(),
		);
		foreach ( $args as $arg ) {
			if ( is_array( $arg ) && isset( $arg[0] ) ) {
				switch ( $arg[0] ) {
					case 'directlogout':
						if ( isset( $arg[1] ) && $arg[1] && 'specific' != $arg[1] ) {
							$url = '';
							if ( 'homeurl' === $arg[1] ) {
								$url = get_home_url();
							} elseif ( 'siteurl' === $arg[1] ) {
								$url = get_site_url();
							} elseif ( 'currentpage' === $arg[1] ) {
								$url = 'currentpage';
							}
							$final['global']['directlogout'] = wp_logout_url( $url );
						} elseif ( isset( $arg[1] ) && $arg[1] && 'specific' === $arg[1] && isset( $arg[2] ) && $arg[2] ) {
							$url                             = $arg[2];
							$final['global']['directlogout'] = wp_logout_url( $url );
						} else {
							$final['global']['directlogout'] = wp_logout_url();
						}

						break;

					case 'loginurl':
						if ( isset( $arg[1] ) && $arg[1] && 'specific' != $arg[1] ) {
							$url = '';
							if ( 'homeurl' === $arg[1] ) {
								$url = get_home_url();
							} elseif ( 'siteurl' === $arg[1] ) {
								$url = get_site_url();
							} elseif ( 'currentpage' === $arg[1] ) {
								$url = get_permalink();
							}
							$final['global']['loginurl'] = wp_login_url( $url );
						} elseif ( isset( $arg[1] ) && $arg[1] && 'specific' === $arg[1] && isset( $arg[2] ) && $arg[2] ) {
							$url                         = $arg[2];
							$final['global']['loginurl'] = wp_login_url( $url );
						} else {
							$final['global']['loginurl'] = wp_login_url();
						}

						break;

					case 'shortcode':
					case 'acffield':
					case 'acf_field':
						if ( isset( $arg[1] ) && $arg[1] ) {
							$final['specific'][] = array_push( $final['specific'], $arg );
						}

						break;

					case 'acf_group_field':
						if ( isset( $arg[1] ) && $arg[1] && isset( $arg[2] ) && $arg[2] ) {
							$final['specific'][] = array_push( $final['specific'], $arg );
						}

						break;

					case 'comment_date':
					case 'comment_time':
					case 'postexcerpt':
					case 'postcategory':
					case 'postdate':
					case 'date':
					case 'post_date':
					case 'time':
					case 'postcomments':
					case 'currentdate':
					case 'customcurrentdate':
					case 'authorinfo':
					case 'taxonomyterms':
						if ( ! in_array( $arg, $final['specific'] ) ) {
							$final['specific'][] = array_push( $final['specific'], $arg );
						}

						break;

					case 'userinfo':
						if ( isset( $arg[1] ) && $arg[1] ) {
							$current_user      = get_current_user_id();
							$current_user_meta = get_userdata( $current_user );
							$demand            = $arg[1];
							if ( 0 != $current_user ) {
								$final['global']['userinfo'] = nl2br( $current_user_meta->$demand );
							}
						}

						break;

					case 'siteoption':
						if ( isset( $arg[1] ) && $arg[1] ) {
							$final['global'][ 'siteoption_' . $arg[1] . '' ] = get_option( $arg[1] );
						}

						break;

					case 'current_date':
						$time_format = '';
						$date_format = '';
						if ( isset( $arg[1] ) && $arg[1] ) {
							if ( 'default' === $arg[1] ) {
								$time_format .= 'g:i a';
							}
							if ( '1' === $arg[1] ) {
								$time_format .= 'g:i a';
							}
							if ( '2' === $arg[1] ) {
								$time_format .= 'g:i A';
							}
							if ( '3' === $arg[1] ) {
								$time_format .= 'H:i';
							}
							if ( '4' === $arg[1] ) {
								$time_format .= '';
							}
						}
						if ( isset( $arg[2] ) && $arg[2] ) {
							if ( 'default' === $arg[2] ) {
								$date_format .= 'F j, Y';
							}
							if ( '1' === $arg[2] ) {
								$date_format .= 'F j, Y';
							}
							if ( '2' === $arg[2] ) {
								$date_format .= 'Y-m-d';
							}
							if ( '3' === $arg[2] ) {
								$date_format .= 'm/d/Y';
							}
							if ( '4' === $arg[2] ) {
								$date_format .= 'd/m/Y';
							}
							if ( '5' === $arg[2] ) {
								$date_format .= '';
							}
						}
						$final['global'][ wp_json_encode( $arg ) ] = esc_html( date_i18n( $date_format . ' ' . $time_format, current_time( 'timestamp', 0 ) ) );

						break;

					case 'custom_current_date':
						$custom_format = '';
						if ( isset( $arg[1] ) && $arg[1] ) {
							$custom_format = $arg[1];
						}
						$final['global'][ wp_json_encode( $arg ) ] = esc_html( date_i18n( $custom_format, current_time( 'timestamp', 0 ) ) );

						break;
				}
			} else {
				switch ( $arg ) {
					case 'pageurl':
					case 'archiveurl':
					case 'authorurl':
					case 'authorname':
					case 'author_name':
					case 'commentsurl':
					case 'postparentid':
					case 'postcomments':
					case 'ispostexcerpt':
					case 'ispostcontent':
					case 'posttype':
					case 'postcategory':
					case 'posttag':
					case 'authorpicture':
						if ( ! in_array( $arg, $final['specific'] ) ) {
							$final['specific'][] = array_push( $final['specific'], $arg );
						}

						break;

					case 'commentsregistration':
						if ( get_option( 'comment_registration' ) ) {
							$final['global']['commentsregistration'] = true;
						} else {
							$final['global']['commentsregistration'] = false;
						}

						break;

					case 'username':
						$final['global']['username'] = $current_user->user_login;

						break;

					case 'usercapabilities':
						$final['global']['usercapabilities'] = $current_user->allcaps;

						break;

					case 'userrole':
						$final['global']['userrole'] = $current_user->roles;

						break;

					case 'date':
						$final['global']['date'] = date_i18n( 'm/d/Y' );

						break;

					case 'dayweek':
						$final['global']['dayweek'] = date_i18n( 'l' );
						$final['global']['date']    = date_i18n( 'm/d/Y' );

						break;

					case 'daymonth':
						$final['global']['daymonth'] = date_i18n( 'j' );
						$final['global']['date']     = date_i18n( 'm/d/Y' );

						break;

					case 'time':
						$final['global']['time'] = date_i18n( 'H:i:s' );

						break;

					case 'homeurl':
						$final['global']['homeurl'] = get_home_url();

						break;

					case 'siteurl':
						$final['global']['siteurl'] = get_site_url();

						break;

					case 'directlogout':
						$final['global']['directlogout'] = wp_logout_url();

						break;

					case 'loginurl':
						$final['global']['loginurl'] = wp_login_url();

						break;

					case 'sitetagline':
					case 'site_tagline':
						$final['global']['sitetagline'] = get_bloginfo( 'description', 'display' );

						break;

					case 'sitetitle':
					case 'site_title':
						$final['global']['sitetitle'] = get_bloginfo( 'name', 'display' );

						break;

					case 'userpicture':
						if ( 0 != get_current_user_id() ) {
							$avatar = get_avatar_data( get_current_user_id() );
							if ( $avatar['found_avatar'] && $avatar['url'] ) {
								$final['global']['userpicture'] = $avatar['url'];
							}
						}

						break;
				}
			}
		}

		return $final;
	}

	/**
	 * Check if the ACF field can be used in the REST API.
	 *
	 * @param array $field The field.
	 *
	 * @return mixed
	 */
	protected function is_acf_safe( $field ) {
		if ( 'false' === get_option( 'cwicly_acf_rest_frontend' ) || ! $field ) {
			return true;
		}

		if ( isset( $field['show_in_rest'] ) && $field['show_in_rest'] ) {
			return true;
		}

		if ( isset( $field['parent'] ) && $field['parent'] ) {
			$field_group_id = $field['parent'];
			$field_group    = acf_get_field_group( $field_group_id );

			if ( ! $field_group ) {
				$field_group = acf_get_field( $field_group_id );
				if ( $field_group && $field_group['parent'] ) {
					$field_group = acf_get_field_group( $field_group['parent'] );
				}
			}
			if ( isset( $field_group['show_in_rest'] ) && $field_group['show_in_rest'] ) {
				return true;
			}
			if ( isset( $field_group['parent'] ) && $field_group['parent'] ) {
				while ( $field_group['parent'] ) {
					$field_group_id = $field_group['parent'];
					$field_group    = acf_get_field_group( $field_group_id );
				}
				if ( $field_group['show_in_rest'] ) {
					return true;
				} else {
					return false;
				}
			}
		}
	}

	/**
	 * Process the specific dynamics.
	 *
	 * @param array  $args        The arguments.
	 * @param int    $id          The ID.
	 * @param string $query_type  The query type.
	 * @param object $query_object The query object.
	 *
	 * @return array
	 */
	protected function process_specific_dynamics( $args, $id = null, $query_type = '', $query_object = '' ) {
		if ( $id ) {
			global $post;
			$post = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		$final = array();
		foreach ( $args as $arg ) {
			if ( is_array( $arg ) && isset( $arg[0] ) ) {
				switch ( $arg[0] ) {
					case 'acffield':
					case 'acf_field':
						$value      = '';
						$block_name = '';
						if ( isset( $arg[5] ) && $arg[5] ) {
							$block_name = $arg[5];
						}

						$options = array();
						if ( isset( $arg[6] ) && $arg[6] ) {
							$options = explode( '-', $arg[6] );
						}

						if ( isset( $arg[1] ) && $arg[1] ) {
							if ( isset( $arg[2] ) && 'false' != $arg[2] && isset( $arg[3] ) && 'false' != $arg[3] ) { // LOCATION + SPECIFIC OBJECT.
								if ( ! $this->is_acf_safe( get_field_object( $arg[1], $arg[2] ) ) ) {
									break;
								}
								$field = get_field( $arg[1], $arg[2] );
								if ( $field ) {
									if ( ! is_object( $field ) && ! is_array( $field ) ) {
										$value = \Cwicly\ACF::processor( $field, isset( $arg[4] ) ? $arg[4] : null, '', $block_name, true, $options );
									} elseif ( is_object( $field ) ) {
										$itis  = $field->{$arg[3]};
										$value = \Cwicly\ACF::processor( $itis, isset( $arg[4] ) ? $arg[4] : null, '', $block_name, true, $options );
									} elseif ( is_array( $field ) ) {
										$itis  = $field[ $arg[3] ];
										$value = \Cwicly\ACF::processor( $itis, isset( $arg[4] ) ? $arg[4] : null, '', $block_name, true, $options );
									}
								} elseif ( isset( $arg[4] ) && $arg[4] ) {
									if ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
										$value = $attributes['dynamicStaticFallbackURL'];
									}
								}
							} elseif ( isset( $arg[2] ) && 'false' != $arg[2] ) { // LOCATION ONLY.
								$field = '';
								if ( 'currentuser' === $arg[2] ) {
									$field = get_field_object( $arg[1], 'user_' . get_current_user_id() . '' );
								} elseif ( 'currentauthor' === $arg[2] ) {
									$author_id = get_post_field( 'post_author' );
									$field     = get_field_object( $arg[1], 'user_' . $author_id . '' );
								} elseif ( 'option' === $arg[2] ) {
									$field = get_field_object( $arg[1], 'option' );
								} elseif ( 'taxterm' === $arg[2] && isset( $block->context['taxterms'] ) ) {
									$field = get_field_object( $arg[1], $block->context['taxterms'] );
								} elseif ( 'termquery' === $arg[2] && 'term' === $query_type && $query_object ) {
									$field = get_field_object( $arg[1], $query_object );
								} elseif ( 'userquery' === $arg[2] && 'user' === $query_type && $query_object ) {
									$field = get_field_object( $arg[1], $query_object );
								} else {
									$field = get_field_object( $arg[1], $arg[2] );
								}
								if ( ! $this->is_acf_safe( $field ) ) {
									break;
								}
								if ( isset( $field['value'] ) ) {
									if ( isset( $field['type'] ) && 'repeater' === $field['type'] ) {
										$value = $field['value'];
									} else {
										$value = \Cwicly\ACF::processor( $field['value'], isset( $arg[4] ) ? $arg[4] : null, '', $block_name, true, $options, $field );
									}
								}
							} elseif ( isset( $arg[3] ) && 'false' != $arg[3] ) { // NO LOCATION + SPECIFIC OBJECT.
								if ( ! $this->is_acf_safe( get_field_object( $arg[1] ) ) ) {
									break;
								}
								$field = get_field( $arg[1] );
								if ( $field ) {
									if ( ! is_object( $field ) && ! is_array( $field ) ) {
										// $value = $field;
										$value = \Cwicly\ACF::processor( $field, isset( $arg[4] ) ? $arg[4] : null, '', $block_name, true, $options );
									} elseif ( is_object( $field ) ) {
										$itis  = $field->{$arg[3]};
										$value = \Cwicly\ACF::processor( $itis, isset( $arg[4] ) ? $arg[4] : null, '', $block_name, true, $options );
									} elseif ( is_array( $field ) ) {
										$itis  = $field[ $arg[3] ];
										$value = \Cwicly\ACF::processor( $itis, isset( $arg[4] ) ? $arg[4] : null, '', $block_name, true, $options );
									}
								} elseif ( isset( $arg[4] ) && $arg[4] ) {
									if ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
										$value = $attributes['dynamicStaticFallbackURL'];
									}
								}
							} else { // NO LOCATION + NO SPECIFIC OBJECT.
								$field = get_field_object( sanitize_text_field( $arg[1] ) );
								if ( ! $this->is_acf_safe( $field ) ) {
									break;
								}
								if ( isset( $field['value'] ) ) {
									if ( isset( $field['type'] ) && 'repeater' === $field['type'] ) {
										$value = $field['value'];
									} else {
										$value = \Cwicly\ACF::processor( $field['value'], isset( $arg[4] ) ? $arg[4] : null, '', $block_name, true, $options, $field );
									}
								}
							}
							if ( $value ) {
								$special = '';
								if ( 'cwicly/image' === $block_name ) {
									$special = '_image';
								}
								$extras = array();
								if ( isset( $arg[2] ) && 'false' != $arg[2] ) {
									$extras[] = $arg[2];
								}
								if ( isset( $arg[3] ) && 'false' != $arg[3] ) {
									$extras[] = $arg[3];
								}
								$extras = implode( '-', $extras );
								if ( $extras ) {
									$extras = '_' . $extras;
								}

								$final[ '' . $arg[1] . '_acffield' . $special . $extras . '' ] = $value;
							}
						}

						break;

					case 'acf_group_field':
						if ( isset( $arg[1] ) && $arg[1] && isset( $arg[2] ) && $arg[2] ) {
							$value      = '';
							$block_name = '';
							if ( isset( $arg[5] ) && $arg[5] ) {
								$block_name = $arg[5];
							}

							$options = array();
							if ( isset( $arg[6] ) && $arg[6] ) {
								$options = explode( '-', $arg[6] );
							}

							if ( isset( $arg[3] ) && 'false' != $arg[3] && isset( $arg[4] ) && 'false' != $arg[4] ) { // LOCATION + SPECIFIC OBJECT.
								if ( ! $this->is_acf_safe( get_field_object( $arg[1], $arg[3] ) ) ) {
									break;
								}
								$field = \Cwicly\Helpers::get_group_field( $arg[1], $arg[2], $arg[3] );

								$fallback = '';
								if ( isset( $arg[5] ) && $arg[5] ) {
									$fallback = $arg[5];
								} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
									$fallback = $attributes['dynamicStaticFallbackURL'];
								} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
									$fallback = $attributes['dynamicStaticFallback'];
								}

								if ( $field ) {
									if ( ! is_object( $field ) && ! is_array( $field ) ) {
										$value = \Cwicly\ACF::processor( $field, $fallback, '', $block_name );
									} elseif ( is_object( $field ) ) {
										$itis  = $field->{$arg[4]};
										$value = \Cwicly\ACF::processor( $itis, $fallback, '', $block_name );
									} elseif ( is_array( $field ) ) {
										$itis  = $field[ $arg[4] ];
										$value = \Cwicly\ACF::processor( $itis, $fallback, '', $block_name );
									}
								} elseif ( $fallback ) {
									$value = $fallback;
								}
							} elseif ( isset( $arg[3] ) && 'false' != $arg[3] ) { // LOCATION ONLY.
								$field = '';
								if ( 'currentuser' === $arg[3] ) {
									$field = \Cwicly\Helpers::get_group_field( $arg[1], $arg[2], 'user_' . get_current_user_id() . '' );
								} elseif ( 'currentauthor' === $arg[3] ) {
									$field = \Cwicly\Helpers::get_group_field( $arg[1], $arg[2], 'user_' . get_the_author_meta( 'ID' ) . '' );
								} elseif ( 'option' === $arg[3] ) {
									$field = \Cwicly\Helpers::get_group_field( $arg[1], $arg[2], 'option' );
								} elseif ( 'taxterm' === $arg[3] && isset( $block->context['taxterms'] ) ) {
									$field = \Cwicly\Helpers::get_group_field( $arg[1], $arg[2], $block->context['taxterms'] );
								} elseif ( 'termquery' === $arg[3] && isset( $block->context['termQuery'] ) ) {
									$field = \Cwicly\Helpers::get_group_field( $arg[1], $arg[2], $block->context['termQuery'] );
								} elseif ( 'userquery' === $arg[3] && isset( $block->context['userQuery'] ) ) {
									$field = \Cwicly\Helpers::get_group_field( $arg[1], $arg[2], $block->context['userQuery'] );
								} else {
									$field = \Cwicly\Helpers::get_group_field( $arg[1], $arg[2], $arg[3] );
								}
								if ( ! $this->is_acf_safe( get_field_object( $arg[1] ) ) ) {
									break;
								}
								$fallback = '';
								if ( isset( $arg[5] ) && $arg[5] ) {
									$fallback = $arg[5];
								} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
									$fallback = $attributes['dynamicStaticFallbackURL'];
								} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
									$fallback = $attributes['dynamicStaticFallback'];
								}
								$value = \Cwicly\ACF::processor( $field, $fallback, '', $block_name );
							} elseif ( isset( $arg[4] ) && 'false' != $arg[4] ) { // NO LOCATION + SPECIFIC OBJECT.
								if ( ! $this->is_acf_safe( get_field_object( $arg[1] ) ) ) {
									break;
								}
								$field = \Cwicly\Helpers::get_group_field( $arg[1], $arg[2] );

								$fallback = '';
								if ( isset( $arg[5] ) && $arg[5] ) {
									$fallback = $arg[5];
								} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
									$fallback = $attributes['dynamicStaticFallbackURL'];
								} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
									$fallback = $attributes['dynamicStaticFallback'];
								}

								if ( $field ) {
									if ( ! is_object( $field ) && ! is_array( $field ) ) {
										$value = \Cwicly\ACF::processor( $field, $fallback, '', $block_name );
									} elseif ( is_object( $field ) ) {
										$itis  = $field->{$arg[4]};
										$value = \Cwicly\ACF::processor( $itis, $fallback, '', $block_name );
									} elseif ( is_array( $field ) ) {
										$itis  = $field[ $arg[4] ];
										$value = \Cwicly\ACF::processor( $itis, $fallback, '', $block_name );
									}
								} elseif ( $fallback ) {
									$value = $fallback;
								}
							} else { // NO LOCATION + NO SPECIFIC OBJECT.
								$field = \Cwicly\Helpers::get_group_field( sanitize_text_field( $arg[1] ), sanitize_text_field( $arg[2] ) );
								if ( ! $this->is_acf_safe( get_field_object( $arg[1] ) ) ) {
									break;
								}

								$fallback = '';
								if ( isset( $arg[5] ) && $arg[5] ) {
									$fallback = $arg[5];
								} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
									$fallback = $attributes['dynamicStaticFallbackURL'];
								} elseif ( isset( $attributes['dynamicStaticFallback'] ) && $attributes['dynamicStaticFallback'] ) {
									$fallback = $attributes['dynamicStaticFallback'];
								}

								$value = \Cwicly\ACF::processor( $field, $fallback, '', $block_name, null, $options );
							}

							if ( $value ) {
								$special = '';
								if ( 'cwicly/image' === $block_name ) {
									$special = '_image';
								}
								$final[ '' . $arg[1] . '_' . $arg[2] . '_acfgroupfield' . $special . '' ] = $value;
							}
						}

						break;

					case 'shortcode':
						if ( isset( $arg[1] ) && $arg[1] ) {
							$allowed_shortcodes = apply_filters( 'cwicly/frontend/shortcodes_whitelist', array() );
							if ( ! in_array( $arg[1], $allowed_shortcodes ) ) {
								break;
							}
							$final[ '' . $arg[1] . '_shortcode' ] = do_shortcode( '[' . $arg[1] . ']' );
						}

						break;

					case 'postcategory':
						if ( isset( $arg[1] ) && $arg[1] ) {
							$categories = get_the_category();
							if ( $categories && isset( $categories[ intval( $arg[1] ) - 1 ] ) ) {
								$final['postcategory'][ wp_json_encode( $arg ) ] = $categories[ intval( $arg[1] ) - 1 ]->name;
							} elseif ( $categories && isset( $categories[0] ) ) {
								$final['postcategory'][ wp_json_encode( $arg ) ] = $categories[0]->name;
							}
						}
						break;

					case 'postexcerpt':
						if ( isset( $final['postexcerpt'] ) ) {
						} else {
							$final['postexcerpt'] = array();
						}
						add_filter( 'get_the_excerpt', array( '\Cwicly\Helpers', 'excerpt_gutenberg' ), 10, 2 );
						remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
						$character_limit = '';
						if ( isset( $arg[1] ) && $arg[1] ) {
							$character_limit = $arg[1];
						}
						if ( get_the_excerpt() ) {
							if ( $character_limit ) {
								$excerpt                                        = wp_strip_all_tags( get_the_excerpt() );
								$excerpt                                        = apply_filters( 'cwicly/excerpt', $excerpt );
								$excerpt                                        = substr( $excerpt, 0, $character_limit );
								$final['postexcerpt'][ wp_json_encode( $arg ) ] = substr( $excerpt, 0, strrpos( $excerpt, ' ' ) );
							} else {
								$final['postexcerpt'][ wp_json_encode( $arg ) ] = wp_strip_all_tags( get_the_excerpt() );
							}
						} elseif ( isset( $arg[2] ) && $arg[2] ) {
							$final['postexcerpt'][ wp_json_encode( $arg ) ] = $arg[2];
						}
						remove_filter( 'get_the_excerpt', array( '\Cwicly\Helpers', 'excerpt_gutenberg' ) );
						add_filter( 'get_the_excerpt', 'wp_trim_excerpt' );

						break;

					case 'postdate':
					case 'post_date':
						if ( isset( $arg[1] ) && 'published' === $arg[1] ) {
							if ( isset( $final['postdate'] ) ) {
							} else {
								$final['postdate'] = array();
							}
							if ( 'default' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_date( 'F j, Y' );
							}
							if ( '1' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_date( 'F j, Y' );
							}
							if ( '2' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_date( 'Y-m-d' );
							}
							if ( '3' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_date( 'm/d/Y' );
							}
							if ( '4' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_date( 'd/m/Y' );
							}
							if ( '5' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ) . ' ago';
							}
							if ( '6' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_date( 'd.m.y' );
							}
							if ( '7' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_date( 'd.m.Y' );
							}
							if ( 'custom' === $arg[2] && '' != $arg[3] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_date( $arg[3] );
							}
						}
						if ( isset( $arg[1] ) && 'modified' === $arg[1] ) {
							if ( 'default' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_modified_date( 'F j, Y' );
							}
							if ( '1' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_modified_date( 'F j, Y' );
							}
							if ( '2' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_modified_date( 'Y-m-d' );
							}
							if ( '3' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_modified_date( 'm/d/Y' );
							}
							if ( '4' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_modified_date( 'd/m/Y' );
							}
							if ( '5' === $arg[2] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = esc_html( human_time_diff( get_the_modified_date( 'U' ), current_time( 'timestamp' ) ) ) . ' ago';
							}
							if ( 'custom' === $arg[2] && '' != $arg[3] ) {
								$final['postdate'][ wp_json_encode( $arg ) ] = get_the_modified_date( $arg[3] );
							}
						}

						break;

					case 'comment_date':
						if ( isset( $arg[1] ) && 'published' === $arg[1] ) {
							if ( isset( $final['comment_date'] ) ) {
							} else {
								$final['comment_date'] = array();
							}
							if ( 'default' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'F j, Y', $query_object->comment_ID );
							}
							if ( '1' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'F j, Y', $query_object->comment_ID );
							}
							if ( '2' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'Y-m-d', $query_object->comment_ID );
							}
							if ( '3' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'm/d/Y', $query_object->comment_ID );
							}
							if ( '4' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'd/m/Y', $query_object->comment_ID );
							}
							if ( '5' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$date    = mysql2date( 'U', $comment->comment_date );
								$final['comment_date'][ wp_json_encode( $arg ) ] = esc_html( human_time_diff( $date, current_time( 'timestamp' ) ) ) . ' ago';
							}
							if ( '6' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'd.m.y', $query_object->comment_ID );
							}
							if ( '7' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'd.m.Y', $query_object->comment_ID );
							}
							if ( 'custom' === $arg[2] && '' != $arg[3] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( $arg[3], $query_object->comment_ID );
							}
						}
						if ( isset( $arg[1] ) && 'modified' === $arg[1] ) {
							if ( 'default' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'F j, Y', $query_object->comment_ID );
							}
							if ( '1' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'F j, Y', $query_object->comment_ID );
							}
							if ( '2' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'Y-m-d', $query_object->comment_ID );
							}
							if ( '3' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'm/d/Y', $query_object->comment_ID );
							}
							if ( '4' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'd/m/Y', $query_object->comment_ID );
							}
							if ( '5' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$date    = mysql2date( 'U', $comment->comment_date );
								$final['comment_date'][ wp_json_encode( $arg ) ] = esc_html( human_time_diff( $date, current_time( 'timestamp' ) ) ) . ' ago';
							}
							if ( '6' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'd.m.y', $query_object->comment_ID );
							}
							if ( '7' === $arg[2] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( 'd.m.Y', $query_object->comment_ID );
							}
							if ( 'custom' === $arg[2] && '' != $arg[3] ) {
								$final['comment_date'][ wp_json_encode( $arg ) ] = get_comment_date( $arg[3], $query_object->comment_ID );
							}
						}

						break;

					case 'comment_time':
						if ( isset( $arg[1] ) && 'published' === $arg[1] ) {
							if ( isset( $final['comment_time'] ) ) {
							} else {
								$final['comment_time'] = array();
							}
							if ( 'default' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( 'g:i a', $comment->comment_date );
							}
							if ( '1' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( 'g:i a', $comment->comment_date );
							}
							if ( '2' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( 'g:i A', $comment->comment_date );
							}
							if ( '3' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( 'H:i', $comment->comment_date );
							}
							if ( 'custom' === $arg[2] && '' != $arg[3] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( $arg[3], $comment->comment_date );
							}
						}
						if ( isset( $arg[1] ) && 'modified' === $arg[1] ) {
							if ( isset( $final['comment_time'] ) ) {
							} else {
								$final['comment_time'] = array();
							}
							if ( 'default' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( 'g:i a', $comment->comment_date );
							}
							if ( '1' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( 'g:i a', $comment->comment_date );
							}
							if ( '2' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( 'g:i A', $comment->comment_date );
							}
							if ( '3' === $arg[2] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( 'H:i', $comment->comment_date );
							}
							if ( 'custom' === $arg[2] && '' != $arg[3] ) {
								$comment = get_comment( $query_object->comment_ID );
								$final['comment_time'][ wp_json_encode( $arg ) ] = mysql2date( $arg[3], $comment->comment_date );
							}
						}
						break;

					case 'time':
						if ( isset( $final['time'] ) ) {
						} else {
							$final['time'] = array();
						}
						if ( isset( $arg[1] ) && 'published' === $arg[1] ) {
							if ( 'default' === $arg[2] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_time( 'g:i a' );
							}
							if ( '1' === $arg[2] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_time( 'g:i a' );
							}
							if ( '2' === $arg[2] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_time( 'g:i A' );
							}
							if ( '3' === $arg[2] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_time( 'H:i' );
							}
							if ( 'custom' === $arg[2] && '' != $arg[3] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_time( $arg[3] );
							}
						}
						if ( isset( $arg[1] ) && 'modified' === $arg[1] ) {
							if ( 'default' === $arg[2] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_modified_time( 'g:i a' );
							}
							if ( '1' === $arg[2] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_modified_time( 'g:i a' );
							}
							if ( '2' === $arg[2] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_modified_time( 'g:i A' );
							}
							if ( '3' === $arg[2] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_modified_time( 'H:i' );
							}
							if ( 'custom' === $arg[2] && '' != $arg[3] ) {
								$final['time'][ wp_json_encode( $arg ) ] = get_the_modified_time( $arg[3] );
							}
						}

						break;

					case 'currentdate':
						$time_format = '';
						$date_format = '';
						if ( isset( $arg[1] ) && $arg[1] ) {
							if ( 'default' === $arg[1] ) {
								$time_format .= 'g:i a';
							}
							if ( '1' === $arg[1] ) {
								$time_format .= 'g:i a';
							}
							if ( '2' === $arg[1] ) {
								$time_format .= 'g:i A';
							}
							if ( '3' === $arg[1] ) {
								$time_format .= 'H:i';
							}
							if ( '4' === $arg[1] ) {
								$time_format .= '';
							}
						}
						if ( isset( $arg[2] ) && $arg[2] ) {
							if ( 'default' === $arg[2] ) {
								$date_format .= 'F j, Y';
							}
							if ( '1' === $arg[2] ) {
								$date_format .= 'F j, Y';
							}
							if ( '2' === $arg[2] ) {
								$date_format .= 'Y-m-d';
							}
							if ( '3' === $arg[2] ) {
								$date_format .= 'm/d/Y';
							}
							if ( '4' === $arg[2] ) {
								$date_format .= 'd/m/Y';
							}
							if ( '5' === $arg[2] ) {
								$date_format .= '';
							}
							if ( '6' === $arg[2] ) {
								$date_format .= 'd.m.y';
							}
							if ( '7' === $arg[2] ) {
								$date_format .= 'd.m.Y';
							}
						}
						$final['currentdate'] = esc_html( date_i18n( $date_format . ' ' . $time_format, current_time( 'timestamp', 0 ) ) );

						break;

					case 'customcurrentdate':
						$custom_format = '';
						if ( isset( $arg[1] ) && $arg[1] ) {
							$custom_format = $arg[1];
						}
						$final['customcurrentdate'] = esc_html( date_i18n( $custom_format, current_time( 'timestamp', 0 ) ) );

						break;

					case 'postcomments':
						$comments = strval( get_comments_number() );
						if ( ! $comments ) {
							$comments = '0';
						}
						if ( isset( $arg[1] ) && $arg[1] && ( '0' === $comments || 0 === $comments ) ) {
							$final['postcomments'] = '' . $arg[1] . '';
						} elseif ( isset( $arg[2] ) && $arg[2] && ( '1' === $comments || 1 === $comments ) ) {
							$final['postcomments'] = '1 ' . $arg[2] . '';
						} elseif ( isset( $arg[3] ) && $arg[3] && intval( $comments ) > 1 ) {
							$final['postcomments'] = '' . $comments . ' ' . $arg[3] . '';
						}

						break;

					case 'customfield':
						if ( isset( $arg[1] ) && $arg[1] ) {
							$final['customfield'] = get_post_meta( get_the_ID(), $arg[1], true );
						}

						break;

					case 'authorcustomfield':
						if ( isset( $arg[1] ) && $arg[1] ) {
							$author_id = get_post_field( 'post_author' );
							$value     = get_user_meta( $author_id, $arg[1], true );
						}

						break;

					case 'usercustomfield':
						if ( isset( $arg[1] ) && $arg[1] ) {
							$value = get_user_meta( get_current_user_id(), $arg[1], true );
						}

						break;

					case 'authorinfo':
						if ( isset( $arg[1] ) && $arg[1] ) {
							$demand    = $arg[1];
							$author_id = get_post_field( 'post_author' );
							$value     = get_the_author_meta( $demand, $author_id );
							if ( $value ) {
								$final['authorinfo'] = nl2br( $value );
							}
						}

						break;

					case 'taxonomyterms':
						$taxes = array();
						if ( isset( $arg[1] ) && $arg[1] && isset( $arg[2] ) && $arg[2] ) {
							$real_arg = json_decode( wp_json_encode( $arg[1] ), true );
							if ( isset( $real_arg['taxtermsSource'] ) && $real_arg['taxtermsSource'] && 'current' === $real_arg['taxtermsSource'] ) {
								$excluded     = array();
								$tag_excluded = array();
								$included     = array();
								$tag_included = array();

								if ( isset( $real_arg['taxtermsInclude'] ) && $real_arg['taxtermsInclude'] ) {
									foreach ( $real_arg['taxtermsInclude'] as $includer ) {
										if ( isset( $includer['taxonomy'] ) && $includer['taxonomy'] ) {
											$tag_included[] = $includer['value'];
										} else {
											$included[] = $includer['value'];
										}
									}
								}

								if ( isset( $real_arg['taxtermsExclude'] ) && $real_arg['taxtermsExclude'] ) {
									foreach ( $real_arg['taxtermsExclude'] as $excluder ) {
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
									if ( get_the_ID() ) {
										$result = get_the_terms( get_the_ID(), $tax );
										if ( is_array( $result ) ) {
											if ( isset( $real_arg['taxtermsTopParents'] ) && $real_arg['taxtermsTopParents'] ) {
												foreach ( $result as $term_single ) {
															$arrays[] = \Cwicly\Helpers::get_term_top_level_parent( $term_single->term_id, $tax );
												}
											} else {
												$arrays = array_merge( $arrays, $result );
											}
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

								if ( isset( $real_arg['taxtermsNumber'] ) && $real_arg['taxtermsNumber'] ) {
									$limit = $real_arg['taxtermsNumber'];
								}

								foreach ( $arrays as $index => $tax ) {
									if ( isset( $tax ) && $tax ) {
										if ( ! in_array( $tax->term_id, $excluded ) ) {
											if ( ! $limit || $count <= intval( $limit ) ) {
												$count    = ++$count;
												$tax->url = get_term_link( $tax );
												$taxes[]  = $tax;
											}
										}
									}
								}
							} elseif ( isset( $real_arg['taxtermsSource'] ) && $real_arg['taxtermsSource'] && 'custom' === $real_arg['taxtermsSource'] && isset( $real_arg['taxtermsPostType'] ) && $real_arg['taxtermsPostType'] ) {
								$post_types      = array();
								$post_taxonomies = array();
								$excluded        = array();
								$included        = array();
								if ( isset( $real_arg['taxtermsExclude'] ) && $real_arg['taxtermsExclude'] ) {
									foreach ( $real_arg['taxtermsExclude'] as $excluder ) {
										$excluded[] = $excluder['value'];
									}
								}
								if ( isset( $real_arg['taxtermsInclude'] ) && $real_arg['taxtermsInclude'] ) {
									$included = array();
									foreach ( $real_arg['taxtermsInclude'] as $includer ) {
										$included[] = $includer['value'];
									}
								}

								foreach ( $real_arg['taxtermsPostType'] as $type ) {
									$post_types[]            = $type['value'];
									$current_post_taxonomies = get_object_taxonomies( $type['value'] );
									foreach ( $current_post_taxonomies as $each ) {
										if ( ! in_array( $each, $post_taxonomies ) ) {
											$post_taxonomies[] = $each;
										}
									}
								}

								$exclude_current = false;
								if ( isset( $real_arg['taxtermsExcludeCurrent'] ) && $real_arg['taxtermsExcludeCurrent'] ) {
									$exclude_current = true;
								}

								if ( isset( $real_arg['taxtermsTaxonomies'] ) && $real_arg['taxtermsTaxonomies'] ) {
									$selectde_tax = array();
									foreach ( $real_arg['taxtermsTaxonomies'] as $type ) {
										$selectde_tax[] = $type['value'];
									}

									$args = array(
										'taxonomy'   => $selectde_tax,
										'orderby'    => ( isset( $real_arg['taxtermsOrderBy'] ) && $real_arg['taxtermsOrderBy'] ) ? $real_arg['taxtermsOrderBy'] : 'name',
										'order'      => ( isset( $real_arg['taxtermsOrderDirection'] ) && $real_arg['taxtermsOrderDirection'] ) ? $real_arg['taxtermsOrderDirection'] : 'ASC',
										'hide_empty' => isset( $real_arg['taxtermsHideEmpty'] ) && $real_arg['taxtermsHideEmpty'] ? filter_var( $real_arg['taxtermsHideEmpty'], FILTER_VALIDATE_BOOLEAN ) : false,
										'exclude'    => $excluded,
										'include'    => $included,
									);

									if ( isset( $real_arg['taxtermsExcludeChildren'] ) && $real_arg['taxtermsExcludeChildren'] && filter_var( $real_arg['taxtermsExcludeChildren'], FILTER_VALIDATE_BOOLEAN ) ) {
										$args['parent'] = 0;
									}

									$terms = get_terms( $args );

									$count = 1;
									$limit = null;

									if ( isset( $real_arg['taxtermsNumber'] ) && $real_arg['taxtermsNumber'] ) {
										$limit = $real_arg['taxtermsNumber'];
									}

									foreach ( $terms as $index => $term ) {
										if ( ( ( $exclude_current && ! has_term( $term->term_id, $term->taxonomy ) ) || ( $exclude_current && is_archive() && get_queried_object()->term_id != $term->term_id ) || ! $exclude_current ) ) {
											if ( ! $limit || $count <= intval( $limit ) ) {
												$count     = ++$count;
												$term->url = get_term_link( $term );
												$taxes[]   = $term;
											}
										}
									}
								} else {
									$args = array(
										'taxonomy'   => array(),
										'orderby'    => ( isset( $real_arg['taxtermsOrderBy'] ) && $real_arg['taxtermsOrderBy'] ) ? $real_arg['taxtermsOrderBy'] : 'name',
										'order'      => ( isset( $real_arg['taxtermsOrderDirection'] ) && $real_arg['taxtermsOrderDirection'] ) ? $real_arg['taxtermsOrderDirection'] : 'ASC',
										'hide_empty' => isset( $real_arg['taxtermsHideEmpty'] ) && $real_arg['taxtermsHideEmpty'] ? filter_var( $real_arg['taxtermsHideEmpty'], FILTER_VALIDATE_BOOLEAN ) : false,
										'exclude'    => $excluded,
										'include'    => $included,
									);

									if ( isset( $real_arg['taxtermsExcludeChildren'] ) && $real_arg['taxtermsExcludeChildren'] && filter_var( $real_arg['taxtermsExcludeChildren'], FILTER_VALIDATE_BOOLEAN ) ) {
										$args['parent'] = 0;
									}

									$terms = get_terms( $args );

									$count = 1;
									$limit = null;

									if ( isset( $real_arg['taxtermsNumber'] ) && $real_arg['taxtermsNumber'] ) {
										$limit = $real_arg['taxtermsNumber'];
									}

									foreach ( $terms as $index => $term ) {
										if ( ( ( $exclude_current && ! has_term( $term->term_id, $term->taxonomy ) ) || ( $exclude_current && is_archive() && get_queried_object()->term_id != $term->term_id ) || ! $exclude_current ) ) {
											if ( in_array( $term->taxonomy, $post_taxonomies ) && ( ! $limit || $count <= intval( $limit ) ) ) {
												$count     = ++$count;
												$term->url = get_term_link( $term );
												$taxes[]   = $term;
											}
										}
									}
								}
							}
							if ( $taxes ) {
								$final[ $arg[2] ] = $taxes;
							}

							break;
						}
				}
			} else {
				switch ( $arg ) {
					case 'pageurl':
						$final['pageurl'] = get_permalink();

						break;

					case 'archiveurl':
						$post_type           = get_post_type();
						$final['archiveurl'] = get_post_type_archive_link( $post_type );

						break;

					case 'postarchiveurl':
						$post_type               = get_post_type();
						$final['postarchiveurl'] = get_post_type_archive_link( $post_type );

						break;

					case 'authorurl':
						$author_id          = get_post_field( 'post_author' );
						$final['authorurl'] = get_author_posts_url( $author_id );

						break;

					case 'authorname':
					case 'author_name':
						$author_id   = get_post_field( 'post_author' );
						$author_name = '';
						if ( $author_id ) {
							$author_name = get_the_author_meta( 'display_name', $author_id );
						}
						if ( $author_name ) {
							$final['authorname'] = $author_name;
						}

						break;

					case 'postparentid':
						$final['postparentid'] = wp_get_post_parent_id( get_the_ID() );

						break;

					case 'postcomments':
						$final['postcomments'] = get_comments_number();

						break;

					case 'ispostexcerpt':
						if ( has_excerpt() && get_the_excerpt() ) {
							$final['ispostexcerpt'] = true;
						} else {
							$final['ispostexcerpt'] = false;
						}

						break;

					case 'ispostcontent':
						if ( get_the_content() ) {
							$final['ispostcontent'] = true;
						} else {
							$final['ispostcontent'] = false;
						}

						break;

					case 'posttype':
						$final['posttype'] = get_post_type();

						break;

					case 'posttitle':
						$final['posttitle'] = get_the_title();

						break;

					case 'postcategory':
						$categories    = get_the_category();
						$category_list = array();
						if ( ! empty( $categories ) ) {
							foreach ( $categories as $category ) {
								$category_list[] = $category->name;
							}
						}
						$final['postcategory'] = $category_list;

						break;

					case 'posttag':
						$post_tags = wp_get_post_terms( get_the_ID() );
						$tag_list  = array();
						if ( $post_tags ) {
							foreach ( $post_tags as $tag ) {
								$tag_list[] = strtolower( $tag->name );
							}
						}
						$final['posttag'] = $tag_list;

						break;

					case 'commentsurl':
						$final['commentsurl'] = '' . get_permalink() . '#respond';

						break;

					case 'authorpicture':
						$author_id = get_post_field( 'post_author' );
						$avatar    = get_avatar_data( $author_id );
						if ( $avatar['found_avatar'] && $avatar['url'] ) {
							$final['authorpicture'] = $avatar['url'];
						} else {
							$final['authorpicture'] = CWICLY_URL . 'assets/images/placeholder.jpg';
						}

						break;
				}
			}
		}

		return $final;
	}

	/**
	 * Get image
	 *
	 * @param int $attachment_id Image attachment ID.
	 * @return array|null
	 */
	protected function get_image( $attachment_id ) {
		if ( ! $attachment_id ) {
			return null;
		}

		$allimagesizes = \Cwicly\Helpers::get_all_image_sizes();
		$src           = array();
		$src_sizes     = array();
		foreach ( $allimagesizes as $key => $value ) {
			$src[ $key ]       = wp_get_attachment_image_src( $attachment_id, $key );
			$src_sizes[ $key ] = wp_get_attachment_image_sizes( $attachment_id, $key );
		}

		$attachment = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( ! is_array( $attachment ) ) {
			return array();
		}

		$thumbnail = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );

		return array(
			'id'         => (int) $attachment_id,
			'src'        => current( $attachment ),
			'thumbnail'  => current( $thumbnail ),
			'srcset'     => (string) wp_get_attachment_image_srcset( $attachment_id, 'full' ),
			'sizes'      => array(
				'width'  => $attachment[1],
				'height' => $attachment[2],
			),
			'attr_sizes' => $src_sizes,
			'src_size'   => wp_get_attachment_image_sizes( $attachment_id ),
			'name'       => get_the_title( $attachment_id ),
			'alt'        => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			'all'        => $src,
		);
	}

	/**
	 * Convert a WooCommerce product into an object suitable for the response.
	 *
	 * @param \WC_Product $product Product instance.
	 * @return array
	 */
	public function get_item_response( $product ) {
		$children = array();
		if ( 'grouped' === $product->get_type() ) {
			$products = $product->get_children();
			foreach ( $products as $product_id ) {
				$specific   = wc_get_product( $product_id );
				$children[] = $this->get_item_response( $specific );
			}
		}

		return array(
			'id'                    => $product->get_id(),
			'name'                  => $product->get_title(),
			'parent'                => $product->get_parent_id(),
			'type'                  => $product->get_type(),
			'variation'             => $product->is_type( 'variable' ) ? $this->get_variation_main( $product ) : '',
			'permalink'             => $product->get_permalink(),
			'sku'                   => $product->get_sku(),
			'short_description'     => wc_format_content( wp_kses_post( $product->get_short_description() ) ),
			'description'           => wc_format_content( wp_kses_post( $product->get_description() ) ),
			'on_sale'               => $this->get_is_on_sale( $product ),
			'sale_percentage'       => WooCommerce::percentage_calculator( $product ),
			'prices'                => $this->all_prices( $product ),
			'price_html'            => $product->get_price_html(),
			'is_virtual'            => $product->is_virtual(),
			'is_downloadable'       => $product->is_downloadable(),
			'is_featured'           => $product->is_featured(),
			'average_rating'        => (string) $product->get_average_rating(),
			'review_count'          => $product->get_review_count(),
			'images'                => $this->get_images( $product ),
			'featured_image'        => $this->get_image( $product->get_image_id() ),
			'categories'            => $this->get_term_list( $product, 'product_cat' ),
			'tags'                  => $this->get_term_list( $product, 'product_tag' ),
			'attributes'            => $this->get_attributes( $product ),
			'grouped'               => $children,
			'variations'            => 'variable' === $product->get_type() ? $product->get_available_variations() : array(),
			'variation_defaults'    => 'variable' === $product->get_type() ? $product->get_default_attributes() : array(),
			'has_options'           => $product->has_options(),
			'is_purchasable'        => $product->is_purchasable(),
			'is_in_stock'           => $product->is_in_stock(),
			'is_on_backorder'       => 'onbackorder' === $product->get_stock_status(),
			'low_stock_remaining'   => $this->get_low_stock_remaining( $product ),
			'stock_quantity'        => $product->get_stock_quantity(),
			'stock_status'          => $product->get_stock_status(),
			'sale_from'             => $product->get_date_on_sale_from(),
			'sale_to'               => $product->get_date_on_sale_to(),
			'max_purchase_quantity' => $product->get_max_purchase_quantity(),
			'min_purchase_quantity' => $product->get_min_purchase_quantity(),
			'sold_individually'     => $product->is_sold_individually(),
			'reviews_allowed'       => $product->get_reviews_allowed(),
			'external_url'          => 'external' === $product->get_type() ? $product->get_product_url() : '',
			'add_to_cart'           => (object) array_merge(
				array(
					'text'        => $product->add_to_cart_text(),
					'description' => $product->add_to_cart_description(),
					'url'         => $product->add_to_cart_url(),
				),
				( new QuantityLimits() )->get_add_to_cart_limits( $product )
			),
		);
	}

	/**
	 * Check if product is on sale.
	 *
	 * @param \WC_Product $product Product instance.
	 */
	protected function get_is_on_sale( $product ) {
		if ( 'variable' === $product->get_type() ) {
			$variations = get_children(
				array(
					'post_parent' => $product->get_id(),
					'post_type'   => 'product_variation',
				)
			);

			$is_on_sale = false;

			foreach ( $variations as $variation_id ) {
				$variation = wc_get_product( $variation_id );

				if ( $variation->is_on_sale() ) {
					$is_on_sale = true;
					break;
				}
			}

			if ( $is_on_sale ) {
				return true;
			} else {
				return false;
			}
		} elseif ( $product->is_on_sale() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get main variation data.
	 *
	 * @param \WC_Product $product Product instance.
	 */
	protected function get_variation_main( $product ) {
		$variations                       = $product->get_variation_attributes();
		$variations_attributes_and_values = array();
		foreach ( $variations as $taxonomy => $terms_slug ) {
			$final = array();
			// To get the attribute label (in WooCommerce 3+).
			$taxonomy_label = wc_attribute_label( $taxonomy, $product );
			// Setting some data in an array.
			$final = array( 'label' => $taxonomy_label );
			if ( isset( wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) )->type ) ) {
				$final['type'] = wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) )->type;
			}
			$final['slug'] = $taxonomy;

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
						if ( $final['type'] && 'color' === $final['type'] ) {
							$term_type = get_term_meta( $term_id, '_cwicly_color', true );
						}
						if ( $final['type'] && 'image' === $final['type'] ) {
							$term_type = wp_get_attachment_url( get_term_meta( $term_id, '_cwicly_image_id', true ) );
						}

						// Setting the terms ID and values in the array.
						$final['terms'][] = array(
							'name' => $term_name,
							'slug' => $term_slug,
							'type' => $term_type,
						);
					}
				}
			} else {
				$final['terms'] = array();
				foreach ( $terms_slug as $term ) {
					$final['terms'][ $term ] = array(
						'name' => $term,
						'slug' => $term,
						'type' => null,
					);
				}
			}
			$variations_attributes_and_values[] = $final;
		}

		return $variations_attributes_and_values;
	}

	/**
	 * Get all variations with info
	 *
	 * @param \WC_Product $product Product instance.
	 * @return array
	 */
	protected function get_variations_data( $product ) {
		$variation_ids = $product->is_type( 'variable' ) ? $product->get_visible_children() : array();

		if ( ! count( $variation_ids ) ) {
			return array();
		}

		global $woocommerce; // Don't forget this!
		$final = array();
		foreach ( $variation_ids as $variation ) {
			$variation = wc_get_product( $variation );
			$final[]   = $this->get_item_response( $variation );
		}
		return $final;
	}

	/**
	 * Get all prices
	 *
	 * @param \WC_Product $product Product instance.
	 * @return array
	 */
	protected function all_prices( $product ) {
		$prices = array();
		// If we have a variable product, get the price from the variations (this will use the min value).
		if ( $product->is_type( 'variable' ) ) {
			$regular_price = $product->get_variation_regular_price();
			$sale_price    = $product->get_variation_sale_price();
		} else {
			$regular_price = $product->get_regular_price();
			$sale_price    = $product->get_sale_price();
		}

		if ( $product->is_type( 'variable' ) ) {
			$price     = $product->get_price();
			$prices    = $product->get_variation_prices();
			$min_price = current( $prices['price'] );
			$max_price = end( $prices['price'] );
			if ( $min_price !== $max_price ) {
				$price                                   = wc_price( $min_price ) . ' - ' . wc_price( $max_price );
				$price                                   = $product->get_price();
				$prices['price']                         = array();
				$prices['price']['blank']                = $min_price . ' - ' . $max_price;
				$prices['price']['formatted']            = WooCommerce::dynamic_price( $min_price, 'formatted', $product ) . ' - ' . WooCommerce::dynamic_price( $max_price, 'formatted', $product );
				$prices['price']['formattedcurrency']    = WooCommerce::dynamic_price(
					$min_price,
					'formattedcurrency',
					$product
				) . ' - ' . WooCommerce::dynamic_price(
					$max_price,
					'formattedcurrency',
					$product
				);
				$prices['price']['formattedtax']         = WooCommerce::dynamic_price(
					$min_price,
					'formattedtax',
					$product
				) . ' - ' . WooCommerce::dynamic_price(
					$max_price,
					'formattedtax',
					$product
				);
				$prices['price']['formattedtaxcurrency'] = WooCommerce::dynamic_price(
					$min_price,
					'formattedtaxcurrency',
					$product
				) . ' - ' . WooCommerce::dynamic_price(
					$max_price,
					'formattedtaxcurrency',
					$product
				);
			} else {
				$prices['price'] = $this->price_maker( $price, $product );
			}
		} else {
			$prices['price'] = $this->price_maker( $product->get_price(), $product );
		}
		$prices['regular_price'] = $this->price_maker( $regular_price, $product );
		$prices['sale_price']    = $this->price_maker( $sale_price, $product );
		$prices['price_range']   = $this->get_price_range( $product );

		return $prices;
	}

	/**
	 * Make price with formats
	 *
	 * @param int $price Price to format.
	 * @return array
	 */
	protected function price_maker( $price, $product ) {
		$final                         = array();
		$final['blank']                = $price;
		$final['formatted']            = WooCommerce::dynamic_price(
			$price,
			'formatted',
			$product
		);
		$final['formattedcurrency']    = WooCommerce::dynamic_price(
			$price,
			'formattedcurrency',
			$product
		);
		$final['formattedtax']         = WooCommerce::dynamic_price(
			$price,
			'formattedtax',
			$product
		);
		$final['formattedtaxcurrency'] = WooCommerce::dynamic_price(
			$price,
			'formattedtaxcurrency',
			$product
		);

		return $final;
	}

	/**
	 * Get list of product images.
	 *
	 * @param \WC_Product $product Product instance.
	 * @return array
	 */
	protected function get_images( \WC_Product $product ) {
		$attachment_ids = array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() );

		return array_filter( array_map( array( $this, 'get_image_stuff' ), $attachment_ids ) );
	}

	/**
	 * Convert a WooCommerce product into an object suitable for the response.
	 *
	 * @param int $attachment_id Image attachment ID.
	 * @return array|null
	 */
	protected function get_image_stuff( $attachment_id ) {
		if ( ! $attachment_id ) {
			return null;
		}

		$attachment = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( ! is_array( $attachment ) ) {
			return array();
		}

		$thumbnail = wp_get_attachment_image_src( $attachment_id, 'woocommerce_thumbnail' );

		return array(
			'id'        => (int) $attachment_id,
			'src'       => current( $attachment ),
			'thumbnail' => current( $thumbnail ),
			'srcset'    => (string) wp_get_attachment_image_srcset( $attachment_id, 'full' ),
			'sizes'     => array(
				'width'  => $attachment[1],
				'height' => $attachment[2],
			),
			'name'      => get_the_title( $attachment_id ),
			'alt'       => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
		);
	}

	/**
	 * Gets remaining stock amount for a product.
	 *
	 * @param \WC_Product $product Product instance.
	 * @return integer|null
	 */
	protected function get_remaining_stock( \WC_Product $product ) {
		if ( is_null( $product->get_stock_quantity() ) ) {
			return null;
		}

		return $product->get_stock_quantity();
	}

	/**
	 * If a product has low stock, return the remaining stock amount for display.
	 *
	 * @param \WC_Product $product Product instance.
	 * @return integer|null
	 */
	protected function get_low_stock_remaining( \WC_Product $product ) {
		$remaining_stock = $this->get_remaining_stock( $product );

		if ( ! is_null( $remaining_stock ) && $remaining_stock <= wc_get_low_stock_amount( $product ) ) {
			return max( $remaining_stock, 0 );
		}

		return null;
	}

	/**
	 * Returns true if the given attribute is valid.
	 *
	 * @param mixed $attribute Object or variable to check.
	 * @return boolean
	 */
	protected function filter_valid_attribute( $attribute ) {
		return is_a( $attribute, '\WC_Product_Attribute' );
	}

	/**
	 * Returns true if the given attribute is valid and used for variations.
	 *
	 * @param mixed $attribute Object or variable to check.
	 * @return boolean
	 */
	protected function filter_variation_attribute( $attribute ) {
		return $this->filter_valid_attribute( $attribute ) && $attribute->get_variation();
	}

	/**
	 * Get variation IDs and attributes from the DB.
	 *
	 * @param \WC_Product $product Product instance.
	 * @returns array
	 */
	protected function get_variations( \WC_Product $product ) {
		$variation_ids = $product->is_type( 'variable' ) ? $product->get_visible_children() : array();

		if ( ! count( $variation_ids ) ) {
			return array();
		}

		/**
		 * Gets default variation data which applies to all of this products variations.
		 */
		$attributes                  = array_filter( $product->get_attributes(), array( $this, 'filter_variation_attribute' ) );
		$default_variation_meta_data = array_reduce(
			$attributes,
			function ( $defaults, $attribute ) use ( $product ) {
				$meta_key              = wc_variation_attribute_name( $attribute->get_name() );
				$defaults[ $meta_key ] = array(
					'name'  => wc_attribute_label( $attribute->get_name(), $product ),
					'value' => null,
				);
				return $defaults;
			},
			array()
		);
		$not_attributes              = array(
			'_price',
			'_regular_price',
			'_sale_price',
			'_backorders',
			'_sold_individually',
			'_virtual',
			'_downloadable',
			'_stock',
			'_stock_status',
			'_variation_description',
			'_thumbnail_id',
		);

		$default_variation_meta_keys = array_keys( $default_variation_meta_data );

		$merge = array_merge( $default_variation_meta_keys, $not_attributes );

		/**
		 * Gets individual variation data from the database, using cache where possible.
		 */
		$cache_group   = 'product_variation_meta_data';
		$cache_value   = wp_cache_get( $product->get_id(), $cache_group );
		$last_modified = get_the_modified_date( 'U', $product->get_id() );

		if ( false === $cache_value || $last_modified !== $cache_value['last_modified'] ) {
			global $wpdb;
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$variation_meta_data = $wpdb->get_results(
				"
				SELECT post_id as variation_id, meta_key as attribute_key, meta_value as attribute_value
				FROM {$wpdb->postmeta}
				WHERE post_id IN (" . implode( ',', array_map( 'esc_sql', $variation_ids ) ) . ")
				AND meta_key IN ('" . implode( "','", array_map( 'esc_sql', $merge ) ) . "')
			"
			);
            // phpcs:enable

			wp_cache_set(
				$product->get_id(),
				array(
					'last_modified' => $last_modified,
					'data'          => $variation_meta_data,
				),
				$cache_group
			);
		} else {
			$variation_meta_data = $cache_value['data'];
		}

		/**
		 * Merges and formats default variation data with individual variation data.
		 */
		$attributes_by_variation = array_reduce(
			$variation_meta_data,
			function ( $values, $data ) use ( $merge ) {
				// The query above only includes the keys of $default_variation_meta_data so we know all of the attributes
				// being processed here apply to this product. However, we need an additional check here because the
				// cache may have been primed elsewhere and include keys from other products.
				// @see AbstractProductGrid::prime_product_variations.
				if ( in_array( $data->attribute_key, $merge, true ) ) {
					$values[ $data->variation_id ][ $data->attribute_key ] = $data->attribute_value;
				}

				return $values;
			},
			array_fill_keys( $variation_ids, array() )
		);

		$variations = array();

		foreach ( $variation_ids as $variation_id ) {
			$attribute_data = $default_variation_meta_data;
			$extras         = array();

			foreach ( $attributes_by_variation[ $variation_id ] as $meta_key => $meta_value ) {
				if ( '_price' === $meta_key ) {
					$extras['price'] = $this->price_maker( $meta_value, $product );
				} elseif ( '_regular_price' === $meta_key ) {
					$extras['regular_price'] = $this->price_maker( $meta_value, $product );
				} elseif ( '_sale_price' === $meta_key ) {
					$extras['sale_price'] = $this->price_maker( $meta_value, $product );
				} elseif ( '_sold_individually' === $meta_key && 'yes' === $meta_value ) {
					$extras['sold_individually'] = true;
				} elseif ( '_virtual' === $meta_key && 'yes' === $meta_value ) {
					$extras['virtual'] = true;
				} elseif ( '_downloadable' === $meta_key && 'yes' === $meta_value ) {
					$extras['downloadable'] = true;
				} elseif ( '_stock' === $meta_key ) {
					$extras['stock'] = $meta_value;
				} elseif ( '_stock_status' === $meta_key ) {
					$extras['stock_status'] = $meta_value;
				} elseif ( '_variation_description' === $meta_key ) {
					$extras['variation_description'] = esc_html( $meta_value );
				} elseif ( '_thumbnail_id' === $meta_key ) {
					$extras['thumbnail'] = $this->get_image( $meta_value );
				} elseif ( '' !== $meta_value ) {
					$attribute_data[ $meta_key ]['value'] = $meta_value;
				}
			}

			$variations[] = (object) array(
				'id'         => $variation_id,
				'attributes' => array_values( $attribute_data ),
				'extras'     => $extras,
			);
		}

		return $variations;
	}

	/**
	 * Get list of product attributes and attribute terms.
	 *
	 * @param \WC_Product $product Product instance.
	 * @return array
	 */
	protected function get_attributes( \WC_Product $product ) {
		$attributes         = array_filter( $product->get_attributes(), array( $this, 'filter_valid_attribute' ) );
		$default_attributes = $product->get_default_attributes();
		$return             = array();

		foreach ( $attributes as $attribute_slug => $attribute ) {
			// Only visible and variation attributes will be exposed by this API.
			if ( ! $attribute->get_visible() || ! $attribute->get_variation() ) {
				continue;
			}

			$terms = $attribute->is_taxonomy() ? array_map( array( $this, 'prepare_product_attribute_taxonomy_value' ), $attribute->get_terms() ) : array_map( array( $this, 'prepare_product_attribute_value' ), $attribute->get_options() );
			// Custom attribute names are sanitized to be the array keys.
			// So when we do the array_key_exists check below we also need to sanitize the attribute names.
			$sanitized_attribute_name = sanitize_key( $attribute->get_name() );

			if ( array_key_exists( $sanitized_attribute_name, $default_attributes ) ) {
				foreach ( $terms as $term ) {
					$term->default = $term->slug === $default_attributes[ $sanitized_attribute_name ];
				}
			}

			$return[] = (object) array(
				'id'             => $attribute->get_id(),
				'name'           => wc_attribute_label( $attribute->get_name(), $product ),
				'taxonomy'       => $attribute->is_taxonomy() ? $attribute->get_name() : null,
				'has_variations' => true === $attribute->get_variation(),
				'terms'          => $terms,
			);
		}

		return $return;
	}

	/**
	 * Prepare an attribute term for the response.
	 *
	 * @param \WP_Term $term Term object.
	 * @return object
	 */
	protected function prepare_product_attribute_taxonomy_value( \WP_Term $term ) {
		return $this->prepare_product_attribute_value( $term->name, $term->term_id, $term->slug );
	}

	/**
	 * Prepare an attribute term for the response.
	 *
	 * @param string $name Attribute term name.
	 * @param int    $id Attribute term ID.
	 * @param string $slug Attribute term slug.
	 * @return object
	 */
	protected function prepare_product_attribute_value( $name, $id = 0, $slug = '' ) {
		return (object) array(
			'id'   => (int) $id,
			'name' => $name,
			'slug' => $slug ? $slug : $name,
		);
	}

	/**
	 * WooCommerce can return prices including or excluding tax; choose the correct method based on tax display mode.
	 *
	 * @param string $tax_display_mode Provided tax display mode.
	 * @return string Valid tax display mode.
	 */
	protected function get_tax_display_mode( $tax_display_mode = '' ) {
		return in_array( $tax_display_mode, array( 'incl', 'excl' ), true ) ? $tax_display_mode : get_option( 'woocommerce_tax_display_shop' );
	}

	/**
	 * WooCommerce can return prices including or excluding tax; choose the correct method based on tax display mode.
	 *
	 * @param string $tax_display_mode If returned prices are incl or excl of tax.
	 * @return string Function name.
	 */
	protected function get_price_function_from_tax_display_mode( $tax_display_mode ) {
		return 'incl' === $tax_display_mode ? 'wc_get_price_including_tax' : 'wc_get_price_excluding_tax';
	}

	/**
	 * Get price range from certain product types.
	 *
	 * @param \WC_Product $product Product instance.
	 * @param string      $tax_display_mode If returned prices are incl or excl of tax.
	 * @return object|null
	 */
	protected function get_price_range( \WC_Product $product, $tax_display_mode = '' ) {
		$tax_display_mode = $this->get_tax_display_mode( $tax_display_mode );

		if ( $product->is_type( 'variable' ) ) {
			$prices = $product->get_variation_prices( true );

			if ( ! empty( $prices['price'] ) && ( min( $prices['price'] ) !== max( $prices['price'] ) ) ) {
				return (object) array(
					'min_amount' => $this->price_maker( min( $prices['price'] ), $product ),
					'max_amount' => $this->price_maker( max( $prices['price'] ), $product ),
				);
			}
		}

		if ( $product->is_type( 'grouped' ) ) {
			$children       = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
			$price_function = 'incl' === $tax_display_mode ? 'wc_get_price_including_tax' : 'wc_get_price_excluding_tax';

			foreach ( $children as $child ) {
				if ( '' !== $child->get_price() ) {
					$child_prices[] = $price_function( $child );
				}
			}

			if ( ! empty( $child_prices ) ) {
				return (object) array(
					'min_amount' => $this->price_maker( min( $child_prices ), $product ),
					'max_amount' => $this->price_maker( max( $child_prices ), $product ),
				);
			}
		}

		return null;
	}

	/**
	 * Returns a list of terms assigned to the product.
	 *
	 * @param \WC_Product $product Product object.
	 * @param string      $taxonomy Taxonomy name.
	 * @return array Array of terms (id, name, slug).
	 */
	protected function get_term_list( \WC_Product $product, $taxonomy = '' ) {
		if ( ! $taxonomy ) {
			return array();
		}

		$terms = get_the_terms( $product->get_id(), $taxonomy );

		if ( ! $terms || is_wp_error( $terms ) ) {
			return array();
		}

		$return           = array();
		$default_category = (int) get_option( 'default_product_cat', 0 );

		foreach ( $terms as $term ) {
			$link = get_term_link( $term, $taxonomy );

			if ( is_wp_error( $link ) ) {
				continue;
			}

			if ( $term->term_id === $default_category ) {
				continue;
			}

			$return[] = (object) array(
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
				'link' => $link,
			);
		}

		return $return;
	}

	/**
	 * Get a collection of cart items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 */
	public function get_cart_items( $request ) {
		$parameters = $request->get_query_params();
		$final      = array();
		$items      = array();

		if ( isset( $parameters['items'] ) ) {
			$items = $parameters['items'];
			if ( ! is_array( $items ) ) {
				$items = explode( ',', $items );
				$items = array_map( 'intval', $items );
			}
		}
		if ( $items ) {
			// Check if items are integers and foreach.
			foreach ( $items as $item_id ) {
				$product = wc_get_product( $item_id );
				$product = $this->get_item_response( $product );
				$final[] = $product;
			}
		}

		return $this->get_response( null, null, null, $final );
	}
}
