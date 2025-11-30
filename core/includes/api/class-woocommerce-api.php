<?php
/**
 * Cwicly WooCommerce API.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly WooCommerce API.
 */
class WooCommerce_API extends \WP_REST_Controller {
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

		$base = 'woo_variation';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'woo_variation' ),
					'permission_callback' => '__return_true',
					'args'                => array(),
				),
			)
		);
	}

	/**
	 * Find matching product variation
	 *
	 * @param object $product_id The product ID.
	 * @param array  $attributes The attributes.
	 * @return int
	 */
	public function find_matching_product_variation_id( $product_id, $attributes ) {
		return ( new \WC_Product_Data_Store_CPT() )->find_matching_product_variation(
			new \WC_Product( $product_id ),
			$attributes
		);
	}

	/**
	 * Get product variation
	 *
	 * @param object $data The data.
	 * @return int
	 */
	public function woo_variation( $data ) {
		try {
			$id = '';
			if ( null !== $data->get_param( 'id' ) ) {
				$id = $data->get_param( 'id' );
			}
			$args = array();
			if ( null !== $data->get_param( 'attributes' ) ) {
				$args = $data->get_param( 'attributes' );
				$args = json_decode( $args, true );
			}

			if ( $id && $args ) {
				$variations = $this->find_matching_product_variation_id( $id, $args );
				return $variations;
			}
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}
}
