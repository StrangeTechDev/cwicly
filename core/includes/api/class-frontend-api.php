<?php
/**
 * REST API
 *
 * @package Cwicly
 * @subpackage REST_API
 * @since 1.0.0
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * REST API
 */
class Frontend_API extends \WP_REST_Posts_Controller {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->register_routes();
	}

	/**
	 * Register read-only /wp_query/args/ route.
	 */
	public function register_routes() {
		register_rest_route(
			'cwicly/v1',
			'terms',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_terms' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			)
		);

		register_rest_route(
			'cwicly/v1',
			'terms_col',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_terms_col' ),
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
		return apply_filters( 'wp_query_route_to_rest_api_permissions_check', true, $request );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 */
	public function get_terms( $request ) {
		$parameters = $request->get_query_params();

		$not_cached = true;

		$terms = '';
		if ( isset( $parameters['taxonomy'] ) && $parameters['taxonomy'] ) {
			if ( isset( $parameters['hash'] ) && $parameters['hash'] ) {
				$transient_id = 'cc-terms_' . implode( '-', json_decode( $parameters['taxonomy'] ) ) . '_' . $parameters['hash'] . '';
				$transient    = get_transient( $transient_id );
				if ( false !== $transient ) {
					$not_cached = false;
					$terms      = $transient;
				}
			}
			if ( $not_cached ) {
				$args = array();
				if ( isset( $parameters['parent'] ) && $parameters['parent'] ) {
					$args['parent'] = $parameters['parent'];
				}
				if ( isset( $parameters['include'] ) && $parameters['include'] ) {
					$args['include'] = $parameters['include'];
				}
				if ( isset( $parameters['exclude'] ) && $parameters['exclude'] ) {
					$args['exclude'] = $parameters['exclude'];
				}
				if ( isset( $parameters['orderby'] ) && $parameters['orderby'] ) {
					$args['orderby'] = $parameters['orderby'];
				}
				if ( isset( $parameters['order'] ) && $parameters['order'] ) {
					$args['order'] = $parameters['order'];
				}
				if ( isset( $parameters['childless'] ) && $parameters['childless'] ) {
					$args['childless'] = $parameters['childless'];
				} else {
					$args['childless'] = false;
				}
				if ( isset( $parameters['hide_empty'] ) && $parameters['hide_empty'] ) {
					$args['hide_empty'] = $parameters['hide_empty'];
				} else {
					$args['hide_empty'] = false;
				}

				$args['taxonomy'] = json_decode( $parameters['taxonomy'] );

				if ( $args['taxonomy'] ) {
					$terms = get_terms( $args );

					if ( CC_WOOCOMMERCE ) {
						// Get an array of product attribute taxonomies slugs.
						$attributes_tax_slugs = array_keys( wc_get_attribute_taxonomy_labels() );

						// Get an array of product attribute taxonomies names (starting with "pa_").
						$attributes_tax_names = array_filter( array_map( 'wc_attribute_taxonomy_name', $attributes_tax_slugs ) );

						foreach ( $terms as $term ) {
							if ( in_array( $term->taxonomy, $attributes_tax_names ) ) {
								$term->wooColor = get_term_meta( $term->term_id, '_cwicly_color', true );
								$term->wooImage = wp_get_attachment_url( get_term_meta( $term->term_id, '_cwicly_image_id', true ) );
							}
						}
					}
					foreach ( $terms as $term ) {
						if ( isset( $term->name ) && $term->name ) {
							$term->name = wp_specialchars_decode( $term->name );
						}
					}
					if ( isset( $parameters['hash'] ) && $parameters['hash'] ) {
						set_transient( $transient_id, $terms, 24 * HOUR_IN_SECONDS );
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
			return $this->get_response( $terms, $not_cached );
		}
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 */
	public function get_terms_col( $request ) {
		$terms_col  = null;
		$parameters = $request->get_query_params();
		if ( isset( $parameters['termsCol'] ) && $parameters['termsCol'] ) {
			$terms_col = json_decode( $parameters['termsCol'], true );
			$terms_col = wp_json_encode( $terms_col );
			$terms_col = json_decode( $terms_col, true );
		}

		$final = array();
		if ( $terms_col ) {
			foreach ( $terms_col as $hash => $query ) {
				$not_cached = true;
				$terms      = '';
				if ( isset( $query['taxonomy'] ) && $query['taxonomy'] ) {
					if ( $hash ) {
						$transient_id = 'cc-terms_' . implode( '-', json_decode( $query['taxonomy'] ) ) . '_' . $hash . '';
						$transient    = get_transient( $transient_id );
						if ( false !== $transient ) {
							$not_cached = false;
							$terms      = $transient;
						}
					}
					if ( $not_cached ) {
						$args = array();
						if ( isset( $query['parent'] ) && $query['parent'] ) {
							$args['parent'] = $query['parent'];
						}
						if ( isset( $query['include'] ) && $query['include'] ) {
							$args['include'] = $query['include'];
						}
						if ( isset( $query['exclude'] ) && $query['exclude'] ) {
							$args['exclude'] = $query['exclude'];
						}
						if ( isset( $query['orderby'] ) && $query['orderby'] ) {
							$args['orderby'] = $query['orderby'];
						}
						if ( isset( $query['order'] ) && $query['order'] ) {
							$args['order'] = $query['order'];
						}
						if ( isset( $query['childless'] ) && $query['childless'] ) {
							$args['childless'] = $query['childless'];
						} else {
							$args['childless'] = false;
						}
						if ( isset( $query['hide_empty'] ) && $query['hide_empty'] ) {
							$args['hide_empty'] = $query['hide_empty'];
						} else {
							$args['hide_empty'] = false;
						}

						$args['taxonomy'] = json_decode( $query['taxonomy'] );

						if ( $args['taxonomy'] ) {
							$terms = get_terms( $args );

							if ( CC_WOOCOMMERCE ) {
								// Get an array of product attribute taxonomies slugs.
								$attributes_tax_slugs = array_keys( wc_get_attribute_taxonomy_labels() );

								// Get an array of product attribute taxonomies names (starting with "pa_").
								$attributes_tax_names = array_filter( array_map( 'wc_attribute_taxonomy_name', $attributes_tax_slugs ) );

								foreach ( $terms as $term ) {
									if ( in_array( $term->taxonomy, $attributes_tax_names ) ) {
										$term->wooColor = get_term_meta( $term->term_id, '_cwicly_color', true );
										$term->wooImage = wp_get_attachment_url( get_term_meta( $term->term_id, '_cwicly_image_id', true ) );
									}
								}
							}
							foreach ( $terms as $term ) {
								if ( isset( $term->name ) && $term->name ) {
									$term->name = wp_specialchars_decode( $term->name );
								}
							}
							if ( $hash ) {
								set_transient( $transient_id, $terms, 24 * HOUR_IN_SECONDS );
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
					$final[ $hash ] = $terms;
				}
			}
			return $this->get_response( $final );
		}
	}

	/**
	 * Get response
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $data Full details about the request.
	 * @param bool            $not_cached Not cached.
	 *
	 * @return WP_REST_Response
	 */
	protected function get_response( $data, $not_cached = false ) {

		// Prepare data.
		$response = new \WP_REST_Response( $data, 200 );

		// Total amount of posts.
		$response->header( 'X-CC-Uncached', $not_cached ? 'true' : 'false' );

		return $response;
	}
}
