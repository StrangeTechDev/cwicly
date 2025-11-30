<?php
/**
 * Cwicly Themer API.
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
class Editor_API extends \WP_REST_Controller {
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

		$base1 = 'editor_save';
		register_rest_route(
			$namespace,
			'/' . $base1,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'editor_save' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base2 = 'global_styles';
		register_rest_route(
			$namespace,
			'/' . $base2,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_global_styles' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);
	}

	/**
	 * Save the editor settings.
	 *
	 * @param \WP_REST_Request $request The request.
	 *
	 * @return array
	 */
	public function editor_save( $request ) {
		try {
			$params = $request->get_params();

			if ( isset( $params['globalClasses'] ) ) {
				update_option( 'cwicly_global_classes', $params['globalClasses'] );
			}
			if ( isset( $params['shells'] ) ) {
				update_option( 'cwicly_shells', $params['shells'] );
			}
			if ( isset( $params['globalInteractions'] ) ) {
				update_option( 'cwicly_global_interactions', $params['globalInteractions'] );
			}
			if ( isset( $params['globalClassesRendered'] ) ) {
				update_option( 'cwicly_global_classes_rendered', $params['globalClassesRendered'] );
			}
			if ( isset( $params['globalStylesheets'] ) ) {
				update_option( 'cwicly_global_stylesheets', $params['globalStylesheets'] );
			}
			if ( isset( $params['globalSettings'] ) ) {
				update_option( 'cwicly_global_styles', $params['globalSettings'] );
			}
			if ( isset( $params['globalCSS'] ) ) {
				update_option( 'cwicly_global_css', $params['globalCSS'] );
			}
			if ( isset( $params['globalPseudos'] ) ) {
				update_option( 'cwicly_pseudos', $params['globalPseudos'] );
			}
			if ( isset( $params['sectionDefaults'] ) ) {
				update_option( 'cwicly_section_defaults', $params['sectionDefaults'] );
			}
			if ( isset( $params['externalClasses'] ) ) {
				update_option( 'cwicly_external_classes', $params['externalClasses'] );
			}
			if ( isset( $params['componentsFolders'] ) ) {
				update_option( 'cwicly_components_folders', $params['componentsFolders'] );
			}
			if ( isset( $params['globalClassMaker'] ) ) {
				cc_make_global_css( $params['globalClassMaker'] );
			}
			if ( isset( $params['globalStylesheetSave'] ) ) {
				cc_make_global_stylesheets( $params['globalStylesheetSave'] );
			}
			if ( isset( $params['tailwindStylesheet'] ) ) {
				cc_make_tailwind_stylesheet( $params['tailwindStylesheet'] );
			}
			if ( isset( $params['tailwindClasses'] ) ) {
				update_option( 'cwicly_tailwind_classes', $params['tailwindClasses'] );
			}
			if ( isset( $params['tailwindFonts'] ) ) {
				update_option( 'cwicly_tailwind_fonts', $params['tailwindFonts'] );
			}
			return array(
				'success' => true,
				'message' => 'All saved! Thanks.',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Save the editor settings.
	 *
	 * @param \WP_REST_Request $request The request.
	 * @return array
	 * @throws \Exception When something goes wrong.
	 */
	public function update_global_styles( $request ) {
		try {
			$params = $request->get_params();
			if ( ! isset( $params['settings'] ) ) {
				throw new \Exception( 'Settings parameter is missing!' );
			}

			$settings = $params['settings'];

			if ( get_option( 'cwicly_global_styles' ) == false ) {
				add_option( 'cwicly_global_styles', $settings );
			} else {
				update_option( 'cwicly_global_styles', $settings );
			}

			return array(
				'success' => true,
				'message' => 'Global styles updated!',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}
}
