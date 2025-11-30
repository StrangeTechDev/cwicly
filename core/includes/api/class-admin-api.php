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
class Admin_API extends \WP_REST_Controller {
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

		$base = 'upload_collection';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'upload_collection' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base2 = 'upload_icon';
		register_rest_route(
			$namespace,
			'/' . $base2,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'upload_icon' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
					'args'                => array(),
				),
			)
		);

		$base3 = 'upload_font';
		register_rest_route(
			$namespace,
			'/' . $base3,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'upload_font' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
					'args'                => array(),
				),
			)
		);

		$base4 = 'settings';
		register_rest_route(
			$namespace,
			'/' . $base4,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'settings' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
					'args'                => array(),
				),
			)
		);

		$base5 = 'themes';
		register_rest_route(
			$namespace,
			'/' . $base5,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'change_theme' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
					'args'                => array(),
				),
			)
		);

		$base6 = 'cwicly_global_classes_save';
		register_rest_route(
			$namespace,
			'/' . $base6,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_global_classes' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
					'args'                => array(),
				),
			)
		);
	}

	/**
	 * Process Collection
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function upload_collection( $request ) {
		try {
			$params = $request->get_params();
			if ( $params['imgBase64'] && $params['random'] ) {
				$img       = $params['imgBase64'];
				$img       = str_replace( 'data:image/png;base64,', '', $img );
				$img       = str_replace( ' ', '+', $img );
				$file_data = base64_decode( $img );

				$file_name = '' . $params['random'] . '.png';

				global $wp_filesystem;
				if ( ! $wp_filesystem ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}

				$upload_dir = wp_upload_dir();
				$dir        = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/my-collection/';

				WP_Filesystem( false, $upload_dir['basedir'], true );

				if ( ! $wp_filesystem->is_dir( $dir ) ) {
					$wp_filesystem->mkdir( $dir );
				}
				$target_file = $dir . basename( $file_name );

				file_put_contents( $target_file, $file_data );
			}
			if ( isset( $params['toDelete'] ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
				$wp_filesystem_direct = new \WP_Filesystem_Direct( '' );
				$file_name            = '' . $params['toDelete'] . '.png';
				$upload_dir           = wp_upload_dir();
				$dir                  = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/my-collection/' . $file_name . '';
				if ( file_exists( $dir ) ) {
					$wp_filesystem_direct->delete( $dir, true );
				}
			}
			return array(
				'success' => true,
				'message' => 'Successful upload',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Process Icon upload
	 *
	 *  @param \WP_REST_Request $request Full details about the request.
	 */
	public function upload_icon( $request ) {
		try {
			$files = $request->get_file_params();

			$file = $files['file'];

			// Check if file is svg, if not, return error.
			$ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
			if ( 'svg' !== $ext ) {
				return array(
					'success' => false,
					'message' => 'Only svg files are allowed!',
				);
			}

			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$upload_dir  = wp_upload_dir();
			$dir         = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/icons/';
			$target_file = $dir . basename( $file['name'] );

			WP_Filesystem( false, $upload_dir['basedir'], true );

			if ( ! $wp_filesystem->is_dir( $dir ) ) {
				$wp_filesystem->mkdir( $dir );
			}

			move_uploaded_file( $file['tmp_name'], $target_file );
			return array(
				'success' => true,
				'message' => 'Successful upload',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Process Font upload
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function upload_font( $request ) {
		try {
			$params = $request->get_params();

			if ( $request->get_file_params() ) {
				$files = $request->get_file_params();
			}

			if ( isset( $params['deleteFontVariation'] ) && $params['deleteFontVariation'] && isset( $params['fontName'] ) && $params['fontName'] ) {
				$font_name           = $params['fontName'];
				$font_name_variation = $params['deleteFontVariation'];
				$upload_dir          = wp_upload_dir();
				$dir                 = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/fonts/' . $font_name . '/' . $font_name_variation . '.woff2';
				wp_delete_file( $dir );
			}

			if ( isset( $params['deleteFont'] ) ) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
				$wp_filesystem_direct = new \WP_Filesystem_Direct( '' );
				$font_name            = $params['deleteFont'];
				$upload_dir           = wp_upload_dir();
				$dir                  = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/fonts/' . $font_name . '/';
				if ( file_exists( $dir ) ) {
					$wp_filesystem_direct->delete( $dir, true );
				}
			}

			if ( isset( $files['file'] ) && isset( $params['fontName'] ) ) {

				// Check if file is woff2, if not, return error.
				$ext = pathinfo( $files['file']['name'], PATHINFO_EXTENSION );
				if ( 'woff2' !== $ext ) {
					return array(
						'success' => false,
						'message' => 'Only woff2 files are allowed!',
					);
				}

				global $wp_filesystem;
				if ( ! $wp_filesystem ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				$font_name   = $params['fontName'];
				$file        = $files['file'];
				$upload_dir  = wp_upload_dir();
				$dir         = trailingslashit( $upload_dir['basedir'] ) . '/cwicly/fonts/' . $font_name . '/';
				$target_file = $dir . basename( $file['name'] );

				WP_Filesystem( false, $upload_dir['basedir'], true );

				if ( ! $wp_filesystem->is_dir( $dir ) ) {
					wp_mkdir_p( $dir );
				}

				move_uploaded_file( $file['tmp_name'], $target_file );
			}
			return array(
				'success' => true,
				'message' => 'Successful upload',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Remove Icon
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 */
	public function settings( $request ) {
		try {
			if ( $request->get_params() ) {
				$params = $request->get_params();
			}

			if ( isset( $params['deleteIcon'] ) ) {
				global $wp_filesystem;
				if ( ! $wp_filesystem ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				$upload_dir  = wp_upload_dir();
				$dir         = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/icons/';
				$target_file = $dir . $params['deleteIcon'] . '.svg';
				wp_delete_file( $target_file );
			}

			return array(
				'success' => true,
				'message' => 'Global CSS updated!',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Change Theme
	 *
	 * @param object $data Request data.
	 */
	public function change_theme( $data ) {
		try {

			if ( null !== $data->get_param( 'install' ) && $data->get_param( 'install' ) ) {
				$this->install_themer();
			}

			if ( null !== $data->get_param( 'settheme' ) && $data->get_param( 'settheme' ) ) {
				if ( $data->get_param( 'settheme' ) === 'default' ) {
					switch_theme( WP_DEFAULT_THEME );
				} else {
					switch_theme( $data->get_param( 'settheme' ) );
				}
			}

			$themes = wp_get_themes();
			foreach ( $themes as $theme ) {
				$theme->description = wp_strip_all_tags( $theme->description );
				$theme->name        = wp_strip_all_tags( $theme->name );
			}

			return array(
				'active'  => get_stylesheet(),
				'result'  => $themes,
				'default' => WP_DEFAULT_THEME,
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Install the cwicly theme.
	 *
	 * @return bool
	 */
	public function install_themer() {
		// includes necessary for Plugin_Upgrader and Plugin_Installer_Skin.
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/misc.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		wp_cache_flush();

		$theme = CWICLY_DIR_PATH . 'core/assets/theme/cwicly_theme_v1.0.3.zip';

		$upgrader  = new \Theme_Upgrader( new \Cwicly_Theme_Upgrader_Skin() );
		$installed = $upgrader->install( $theme );

		if ( ! is_wp_error( $installed ) && $installed && wp_get_theme( 'cwicly' )->exists() ) {
			switch_theme( 'cwicly' );
		}

		return $installed;
	}

	/**
	 * Update Global Classes.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return array
	 * @throws Exception If settings parameter is missing.
	 */
	public function update_global_classes( $request ) {
		try {
			$params = $request->get_params();
			if ( ! isset( $params['settings'] ) ) {
				throw new \Exception( 'Settings parameter is missing!' );
			}

			$settings = $params['settings'];

			cc_make_global_css( $settings );

			return array(
				'success' => true,
				'message' => 'Global Classes CSS updated!',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}
}
