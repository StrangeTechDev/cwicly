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
 * Cwicly Entities API.
 */
class Entities_API extends \WP_REST_Controller {
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

		$base = 'entities';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'entities' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-styles',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'global_styles' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-styles',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-stylesheets',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'global_stylesheets' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-stylesheets',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/lightmode-selectors',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'lightmode_selectors' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/darkmode-selectors',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'darkmode_selectors' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/lightmode-selectors',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/darkmode-selectors',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/fonts-col',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'fonts_col' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/fonts_col',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-stylesheets-folders',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'global_stylesheets_folders' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-stylesheets-folders',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-classes',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'global_classes' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-classes',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-classes-folders',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'global_classes_folders' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/global-classes-folders',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/external-classes',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'external_classes' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/external-classes',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/tailwind-classes',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'tailwind_classes' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/tailwind-classes',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/tailwind-configurations',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'tailwind_configurations' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/tailwind-configurations',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/components-folders',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'components_folders' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/components-folders',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/breakpoints-list',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'breakpoints_list' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/breakpoints-list',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/shells',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'shells' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$namespace,
			'/shells',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'entities_post' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Entities
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function entities( $request ) {
		$params = $request->get_params();
		$type   = $params['type'] ?? null;

		$list = array(
			'fonts-col'                  => 'cwicly_font_cols',
			'global-styles'              => 'cwicly_global_styles',
			'global-stylesheets'         => 'cwicly_global_stylesheets',
			'global-stylesheets-folders' => 'cwicly_global_stylesheets_folders',
			'global-classes'             => 'cwicly_global_classes',
			'global-classes-folders'     => 'cwicly_global_classes_folders',
			'external-classes'           => 'cwicly_external_classes',
			'tailwind-classes'           => 'cwicly_tailwind_classes',
			'components-folders'         => 'cwicly_components_folders',
			'breakpoints-list'           => 'cwicly_breakpoints_list',
			'shells'                     => 'cwicly_shells',

		);

		foreach ( $list as $key => $value ) {
			if ( $type === $key ) {
				$option = get_option( $value );

				return array(
					$value => $option,
				);
			}
		}
	}

	/**
	 * Entities Post
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function entities_post( $request ) {
		$body = $request->get_body();
		$body = json_decode( $body, true );
		foreach ( $body as $key => $value ) {
			if ( 'cwicly_tailwind_configurations' === $key ) {
				$permissions = Capabilities::permission( 'tailwind', 'configs', true );
				if ( ! $permissions ) {
					continue;
				}
			} elseif ( 'cwicly_shells' === $key ) {
				$permissions = Capabilities::permission( 'tailwind', 'shells', true );
				if ( ! $permissions ) {
					continue;
				}
			}
			update_option( $key, $value );
		}

		return $body;
	}

	/**
	 * Global Styles
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function global_styles( $request ) {
		$option = get_option( 'cwicly_global_styles' );

		return array(
			'cwicly_global_styles' => $option,
		);
	}

	/**
	 * Global Stylesheets
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function global_stylesheets( $request ) {
		$option = get_option( 'cwicly_global_stylesheets' );

		return array(
			'cwicly_global_stylesheets' => $option,
		);
	}

	/**
	 * Fonts Collection // Deprecated
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function fonts_col( $request ) {
		$option = get_option( 'cwicly_font_cols' );

		return array(
			'cwicly_font_cols' => $option,
		);
	}

	/**
	 * Lightmode Selector
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function lightmode_selectors( $request ) {
		$option = get_option( 'cwicly_lightmode_selectors' );

		return array(
			'cwicly_lightmode_selectors' => $option,
		);
	}

	/**
	 * Darkmode Selector
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function darkmode_selectors( $request ) {
		$option = get_option( 'cwicly_darkmode_selectors' );

		return array(
			'cwicly_darkmode_selectors' => $option,
		);
	}

	/**
	 * Global Stylesheets Folders
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function global_stylesheets_folders( $request ) {
		$option = get_option( 'cwicly_global_stylesheets_folders' );

		return array(
			'cwicly_global_stylesheets_folders' => $option,
		);
	}

	/**
	 * Global Classes
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function global_classes( $request ) {
		$option = get_option( 'cwicly_global_classes' );

		return array(
			'cwicly_global_classes' => $option,
		);
	}

	/**
	 * Global Classes Folders
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function global_classes_folders( $request ) {
		$option = get_option( 'cwicly_global_classes_folders' );

		return array(
			'cwicly_global_classes_folders' => $option,
		);
	}

	/**
	 * External Classes
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function external_classes( $request ) {
		$option = get_option( 'cwicly_external_classes' );

		return array(
			'cwicly_external_classes' => $option,
		);
	}

	/**
	 * Tailwind Classes
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function tailwind_classes( $request ) {
		$option = get_option( 'cwicly_tailwind_classes' );

		return array(
			'cwicly_tailwind_classes' => $option,
		);
	}

	/**
	 * Tailwind Configurations
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function tailwind_configurations( $request ) {
		$option = get_option( 'cwicly_tailwind_configurations' );

		return array(
			'cwicly_tailwind_configurations' => $option,
		);
	}

	/**
	 * Components Folders
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function components_folders( $request ) {
		$option = get_option( 'cwicly_components_folders' );

		return array(
			'cwicly_components_folders' => $option,
		);
	}

	/**
	 * Breakpoints List
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function breakpoints_list( $request ) {
		$option = get_option( 'cwicly_breakpoints_list' );

		return array(
			'cwicly_breakpoints_list' => $option,
		);
	}

	/**
	 * Shells
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function shells( $request ) {
		$option = get_option( 'cwicly_shells' );

		return array(
			'cwicly_shells' => $option,
		);
	}
}
