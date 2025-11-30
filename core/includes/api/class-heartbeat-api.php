<?php
/**
 * Cwicly Heartbeat API.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Heartbeat API.
 */
class Heartbeat_API extends \WP_REST_Controller {
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

		$base = 'heartbeat';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'heartbeat' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base1 = 'themer-heartbeat';
		register_rest_route(
			$namespace,
			'/' . $base1,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'themer_heartbeat' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);
	}

	/**
	 * Hearbeat function
	 *
	 * @param  object $request Request object.
	 */
	public function heartbeat( $request ) {
		$heartbeat = get_option( 'cwicly_heartbeat' );

		$body = json_decode( $request->get_body(), true );

		if ( isset( $body['heartbeat'] ) && $body['heartbeat'] ) {
			$heartbeat_editor = $body['heartbeat'];

			$heartbeat_editor_new = array();
			foreach ( $heartbeat as $key => $value ) {
				if ( ! isset( $heartbeat_editor[ $key ] ) || $value > $heartbeat_editor[ $key ] ) {
					if ( 'cwicly_global_classes' === $key ) {
						$heartbeat_editor_new[] = array(
							'type'                  => $key,
							'time'                  => $heartbeat[ $key ],
							'globalClasses'         => get_option( 'cwicly_global_classes' ),
							'globalClassesRendered' => get_option( 'cwicly_global_classes_rendered' ),
						);
					} else {
						$heartbeat_editor_new[] = array(
							'type'  => $key,
							'time'  => $heartbeat[ $key ],
							'value' => get_option( $key ),
						);
					}
				}
			}

			return new \WP_REST_Response( wp_json_encode( $heartbeat_editor_new ), 200 );
		} else {
			return new \WP_Error( 'no_heartbeat', 'No heartbeat', array( 'status' => 404 ) );
		}
	}

	/**
	 * Themer Hearbeat function
	 *
	 * @param  object $request Request object.
	 */
	public function themer_heartbeat( $request ) {
		$heartbeat = get_option( 'cwicly_themer_heartbeat' );

		$body = json_decode( $request->get_body(), true );

		if ( isset( $body['heartbeat'] ) ) {
			$heartbeat_editor = $body['heartbeat'];

			$heartbeat_editor_new = array();
			foreach ( $heartbeat as $key => $value ) {
				if ( ! isset( $heartbeat_editor[ $key ] ) || $value > $heartbeat_editor[ $key ] ) {
					if ( 'cwicly_pre_conditions' === $key ) {
						$heartbeat_editor_new[] = array(
							'type'          => $key,
							'time'          => $heartbeat[ $key ],
							'preConditions' => get_option( 'cwicly_pre_conditions' ),
							'conditions'    => get_option( 'cwicly_conditions' ),
						);
					} else {
						$heartbeat_editor_new[] = array(
							'type'  => $key,
							'time'  => $heartbeat[ $key ],
							'value' => get_option( $key ),
						);
					}
				}
			}

			return new \WP_REST_Response( wp_json_encode( $heartbeat_editor_new ), 200 );
		} else {
			return new \WP_REST_Response( false, 200 );
		}
	}
}
