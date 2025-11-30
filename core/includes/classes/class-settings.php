<?php
/**
 * Main settings.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * Main settings.
 *
 * @package cwicly
 */
class Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
		add_action( 'admin_menu', array( $this, 'register_themer' ) );
		add_action( 'admin_menu', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_roleeditor' ) );
		add_action( 'admin_menu', array( $this, 'register_welcome' ) );
		remove_filter( 'admin_head', 'wp_check_widget_editor_deps' );
		add_action( 'init', array( $this, 'check_custom_code_transient' ) );
		add_action( 'wp_insert_post_data', array( $this, 'filter_saved_content' ), 10, 3 );
	}

	/**
	 * Register the main Cwicly menu page
	 */
	public function register_menu_page() {
		$cwicly_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTA2IiBoZWlnaHQ9IjQyNyIgdmlld0JveD0iMCAwIDUwNiA0MjciIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0yMDAuMDA4IDcwQzIxNy4wMTkgNzAgMjMwLjgwOSA4Ni4wNjExIDIzMC44MDkgMTA1Ljg3NUMyMzAuODA5IDEyNS42ODkgMjE3LjAxOSAxNDEuNzUgMjAwLjAwOCAxNDEuNzVIMTY5LjIwNkMxMzUuMTg0IDE0MS43NSAxMDcuNjAzIDE3My44NzQgMTA3LjYwMyAyMTMuNUMxMDcuNjAzIDI1My4xMjYgMTM1LjE4NCAyODUuMjUgMTY5LjIwNiAyODUuMjVIMjAwLjAwOEMyMTcuMDE5IDI4NS4yNSAyMzAuODA5IDMwMS4zMTEgMjMwLjgwOSAzMjEuMTI1QzIzMC44MDkgMzQwLjkzOSAyMTcuMDE5IDM1NyAyMDAuMDA4IDM1N0gxNjkuMjA2QzEwMS4xNjEgMzU3IDQ2IDI5Mi43NTMgNDYgMjEzLjVDNDYgMTM0LjI0NyAxMDEuMTYxIDcwIDE2OS4yMDYgNzBIMjAwLjAwOFoiIGZpbGw9IiNFOEU4RTgiLz4KPHBhdGggZD0iTTMwNS40NiA3MEMyODguNDQ5IDcwIDI3NC42NTkgODYuMDYxMSAyNzQuNjU5IDEwNS44NzVDMjc0LjY1OSAxMjUuNjg5IDI4OC40NDkgMTQxLjc1IDMwNS40NiAxNDEuNzVIMzM2LjI2MkMzNzAuMjg0IDE0MS43NSAzOTcuODY1IDE3My44NzQgMzk3Ljg2NSAyMTMuNUMzOTcuODY1IDI1My4xMjYgMzcwLjI4NCAyODUuMjUgMzM2LjI2MiAyODUuMjVIMzA1LjQ2QzI4OC40NDkgMjg1LjI1IDI3NC42NTkgMzAxLjMxMSAyNzQuNjU5IDMyMS4xMjVDMjc0LjY1OSAzNDAuOTM5IDI4OC40NDkgMzU3IDMwNS40NiAzNTdIMzM2LjI2MkM0MDQuMzA3IDM1NyA0NTkuNDY4IDI5Mi43NTMgNDU5LjQ2OCAyMTMuNUM0NTkuNDY4IDEzNC4yNDcgNDA0LjMwNyA3MCAzMzYuMjYyIDcwSDMwNS40NloiIGZpbGw9IiNFOEU4RTgiLz4KPC9zdmc+Cg==';
		add_menu_page(
			__( 'Cwicly', 'cwicly' ),
			__( 'Cwicly', 'cwicly' ),
			'manage_options',
			'cwicly',
			array( $this, 'themer_callback' ),
			$cwicly_icon,
		);
	}

	/**
	 * Register the themer submenu page and enqueue the scripts
	 */
	public function register_themer() {
		$page_hook_suffix = add_submenu_page( 'cwicly', 'Cwicly Themer', 'Themer', 'manage_options', 'cwicly' );
		add_action( "admin_print_scripts-{$page_hook_suffix}", array( $this, 'themer_enqueue' ) );
	}

	/**
	 * Register the settings submenu page and enqueue the scripts
	 */
	public function register_settings() {
		$settings_hook_suffix = add_submenu_page( 'cwicly', 'Cwicly Settings', 'Settings', 'manage_options', 'cwicly-settings', array( $this, 'settings_callback' ) );
		add_action( "admin_print_scripts-{$settings_hook_suffix}", array( $this, 'settings_enqueue' ) );
	}

	/**
	 * Register the role editor submenu page and enqueue the scripts
	 */
	public function register_roleeditor() {
		$roleeditor_hook_suffix = add_submenu_page( 'cwicly', 'Cwicly Role Editor', 'Role Editor', 'manage_options', 'cwicly-roleeditor', array( $this, 'roleeditor_callback' ) );
		add_action( "admin_print_scripts-{$roleeditor_hook_suffix}", array( $this, 'roleeditor_enqueue' ) );
	}

	/**
	 * Register the welcome submenu page and enqueue the scripts
	 */
	public function register_welcome() {
		$welcome_hook_suffix = add_submenu_page( 'cwicly', 'Cwicly Getting Started', 'Getting Started', 'manage_options', 'cwicly-welcome', array( $this, 'welcome_callback' ) );
		add_action( "admin_print_scripts-{$welcome_hook_suffix}", array( $this, 'welcome_enqueue' ) );
	}

	/**
	 * Enqueue files for the Cwicly settings menu. Also localises it.
	 */
	public function settings_enqueue() {
		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
		wp_enqueue_script( 'CodeMirrorCSS', CWICLY_DIR_URL . 'core/assets/js/css.js', array( 'wp-codemirror' ), CWICLY_VERSION, false );
		wp_enqueue_style( 'material', CWICLY_DIR_URL . 'core/assets/css/material.css', array(), CWICLY_VERSION );
		wp_enqueue_style( 'CCnorm', CWICLY_DIR_URL . 'assets/css/base.css', array(), CWICLY_VERSION );
		wp_enqueue_script( 'cc-settings-script', CWICLY_DIR_URL . 'core/includes/js/admin/build/index.js', array( 'react', 'wp-api', 'wp-i18n', 'wp-blocks', 'wp-editor', 'wp-block-editor', 'wp-element', 'wp-block-library', 'lodash', 'wp-components', 'wp-api-fetch', 'wp-core-data', 'wp-data', 'wp-polyfill', 'wp-notices' ), CWICLY_VERSION, true );
		wp_enqueue_style( 'cc-settings-style', CWICLY_DIR_URL . 'core/includes/js/admin/build/style-index.css', array( 'wp-components' ), CWICLY_VERSION );
		wp_enqueue_script( 'jquery' );

		wp_enqueue_editor();
		wp_enqueue_script( 'wp-format-library' );
		do_action( 'enqueue_block_editor_assets' );

		$wp_version = get_bloginfo( 'version' );

		$block_editor_context = new \WP_Block_Editor_Context( array( 'name' => 'core/edit-site' ) );
		$custom_settings      = array(
			'siteUrl'                  => site_url(),
			'postsPerPage'             => get_option( 'posts_per_page' ),
			'styles'                   => get_block_editor_theme_styles(),
			'defaultTemplatePartAreas' => get_allowed_block_template_part_areas(),
		'supportsLayout'            => version_compare( $wp_version, '6.2', '>=' ) ? wp_theme_has_theme_json() : \WP_Theme_JSON_Resolver::theme_has_support(), // phpcs:ignore 
		'supportsTemplatePartsMode'    => ! wp_is_block_theme() && current_theme_supports( 'block-template-parts' ),
		);

		/**
		 * Home template resolution is not needed when block template parts are supported.
		 * Set the value to `true` to satisfy the editor initialization guard clause.
		 */
		if ( $custom_settings['supportsTemplatePartsMode'] ) {
			$custom_settings['__unstableHomeTemplate'] = true;
		}

		$editor_settings = get_block_editor_settings( $custom_settings, $block_editor_context );

		$preload_paths = array();

		block_editor_rest_api_preload( $preload_paths, $block_editor_context );

		wp_add_inline_script(
			'wp-edit-site',
			sprintf(
				'wp.domReady( function() {
			wp.editSite.initializeEditor( "site-editor", %s );
		} );',
				wp_json_encode( $editor_settings )
			)
		);

		wp_add_inline_script(
			'wp-blocks',
			'wp.blocks.unstable__bootstrapServerSideBlockDefinitions(' . wp_json_encode( get_block_editor_server_block_settings() ) . ');'
		);

		wp_add_inline_script(
			'wp-blocks',
			sprintf( 'wp.blocks.setCategories( %s );', wp_json_encode( isset( $editor_settings['blockCategories'] ) ? $editor_settings['blockCategories'] : array() ) ),
			'after'
		);

		$cwicly_optimise = get_option( 'cwicly_optimise' );
		$cwicly_defaults = 'false';
		if ( isset( $cwicly_optimise['cwiclyDefaults'] ) && 'true' === $cwicly_optimise['cwiclyDefaults'] ) {
			$cwicly_defaults = 'true';
		}
		$remove_ids_classes = 'false';
		if ( isset( $cwicly_optimise['removeIDsClasses'] ) && 'true' === $cwicly_optimise['removeIDsClasses'] ) {
			$remove_ids_classes = 'true';
		}

		$blocks       = \WP_Block_Type_Registry::get_instance()->get_all_registered();
		$cwiclyblocks = array();
		if ( $blocks ) {
			foreach ( $blocks as $block ) {
				if ( isset( $block->name ) && strpos( $block->name, 'cwicly/' ) !== false ) {
					$cwiclyblocks[] = $block;
				}
			}
		}

		wp_localize_script(
			'cc-settings-script',
			'cwicly_info',
			array(
				'plugin'           => CWICLY_DIR_URL,
				'url'              => get_home_url(),
				'uploads'          => wp_upload_dir()['baseurl'],
				'admin'            => get_admin_url(),
				'version'          => CWICLY_VERSION,
				'wordpress'        => WORDPRESS_VERSION,
				'cwiclyDefaults'   => $cwicly_defaults,
				'cwiclyBlocks'     => $cwiclyblocks,
				'removeIDsClasses' => $remove_ids_classes,
				'mainBreakpoint'   => \Cwicly\Helpers::get_main_breakpoint(),
			)
		);
	}

	/**
	 * Enqueue files for the Cwicly themer menu. Also localises it.
	 */
	public function themer_enqueue() {
		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
		wp_enqueue_script( 'CodeMirrorCSS', CWICLY_DIR_URL . 'core/assets/js/css.js', null, CWICLY_VERSION, false );
		wp_enqueue_style( 'material', CWICLY_DIR_URL . 'core/assets/css/material.css', array(), CWICLY_VERSION );
		wp_enqueue_style( 'CCnorm', CWICLY_DIR_URL . 'assets/css/base.css', array(), CWICLY_VERSION );
		wp_enqueue_script( 'cc-themer-script', CWICLY_DIR_URL . 'core/includes/js/themer/build/index.js', array( 'wp-preferences', 'wp-api', 'wp-i18n', 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-api-fetch', 'wp-core-data', 'lodash' ), CWICLY_VERSION, true );
		wp_enqueue_style( 'cc-themer-style', CWICLY_DIR_URL . 'core/includes/js/themer/build/style-index.css', array( 'wp-components' ), CWICLY_VERSION );
		wp_enqueue_style( 'cc-themer-nstyle', CWICLY_DIR_URL . 'core/includes/js/themer/build/index.css', array( 'wp-components' ), CWICLY_VERSION );
		wp_enqueue_script( 'jquery' );

		if ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/cc-main.css' ) ) {
			wp_enqueue_style( 'cc', wp_upload_dir()['baseurl'] . '/cwicly/cc-main.css', array(), filemtime( wp_upload_dir()['basedir'] . '/cwicly/cc-main.css' ) );
		}
		wp_localize_script(
			'cc-themer-script',
			'cwicly_info',
			array(
				'plugin'    => CWICLY_DIR_URL,
				'theme'     => get_option( 'stylesheet' ),
				'url'       => get_home_url(),
				'uploads'   => wp_upload_dir()['baseurl'],
				'admin'     => get_admin_url(),
				'version'   => CWICLY_VERSION,
				'nonce'     => wp_create_nonce( 'cc-nonce' ),
				'wordpress' => WORDPRESS_VERSION,
			)
		);
	}

	/**
	 * Enqueue files for the Cwicly role editor menu. Also localises it.
	 */
	public function roleeditor_enqueue() {
		// LOAD CWICLY NORMALISER.
		wp_enqueue_style( 'CCnorm', CWICLY_DIR_URL . 'assets/css/base.css', array(), CWICLY_VERSION );
		// LOAD CWICLY NORMALISER.

		// LOAD CWICLY JS ROLE EDITOR.
		wp_enqueue_script( 'cc-roleeditor-script', CWICLY_DIR_URL . 'core/includes/js/role-editor/build/index.js', array( 'wp-api', 'wp-i18n', 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-api-fetch', 'wp-core-data', 'lodash' ), CWICLY_VERSION, true );
		// LOAD CWICLY JS ROLE EDITOR.

		// LOAD CWICLY CSS ROLE EDITOR.
		wp_enqueue_style( 'cc-roleeditor-style', CWICLY_DIR_URL . 'core/includes/js/role-editor/build/style-index.css', array( 'wp-components' ), CWICLY_VERSION );
		// LOAD CWICLY CSS ROLE EDITOR.

		wp_localize_script(
			'cc-roleeditor-script',
			'cwicly_info',
			array(
				'plugin'       => CWICLY_DIR_URL,
				'theme'        => get_option( 'stylesheet' ),
				'url'          => get_home_url(),
				'uploads'      => wp_upload_dir()['baseurl'],
				'admin'        => get_admin_url(),
				'version'      => CWICLY_VERSION,
				'nonce'        => wp_create_nonce( 'cc-nonce' ),
				'wordpress'    => WORDPRESS_VERSION,
				'current_user' => get_current_user_id(),
			)
		);
	}

	/**
	 * Enqueue files for the Cwicly role editor menu. Also localises it.
	 */
	public function welcome_enqueue() {
		// LOAD CWICLY NORMALISER.
		wp_enqueue_style( 'CCnorm', CWICLY_DIR_URL . 'assets/css/base.css', array(), CWICLY_VERSION );
		// LOAD CWICLY NORMALISER.

		// LOAD CWICLY JS ROLE EDITOR.
		wp_enqueue_script( 'cc-welcome-script', CWICLY_DIR_URL . 'core/includes/js/welcome/build/index.js', array( 'wp-preferences', 'wp-preferences-persistence', 'wp-api', 'wp-i18n', 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-api-fetch', 'wp-core-data', 'lodash' ), CWICLY_VERSION, true );
		// LOAD CWICLY JS ROLE EDITOR.

		// LOAD CWICLY CSS ROLE EDITOR.
		wp_enqueue_style( 'cc-welcome-style', CWICLY_DIR_URL . 'core/includes/js/welcome/build/style-index.css', array( 'wp-components' ), CWICLY_VERSION );
		// LOAD CWICLY CSS ROLE EDITOR.

		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_localize_script(
			'cc-welcome-script',
			'cwicly_info',
			array(
				'plugin'       => CWICLY_DIR_URL,
				'theme'        => get_option( 'stylesheet' ),
				'url'          => get_home_url(),
				'uploads'      => wp_upload_dir()['baseurl'],
				'admin'        => get_admin_url(),
				'version'      => CWICLY_VERSION,
				'nonce'        => wp_create_nonce( 'cc-nonce' ),
				'wordpress'    => WORDPRESS_VERSION,
				'current_user' => get_current_user_id(),
			)
		);
	}

	/**
	 * Cwicly settings callback. Div that allows us to render inside the div.
	 */
	public function settings_callback() {
		echo '<div id="cc-settings-page"></div>';
	}

	/**
	 * Cwicly role editor callback. Div that allows us to render inside the div.
	 */
	public function roleeditor_callback() {
		echo '<div id="cc-settings-page"></div>';
	}

	/**
	 * Cwicly themer callback. Div that allows us to render inside the div.
	 */
	public function themer_callback() {
		echo '<div id="cc-themer-page"></div>';
	}

	/**
	 * Cwicly themer callback. Div that allows us to render inside the div.
	 */
	public function welcome_callback() {
		echo '<div id="cc-welcome-page"></div>';
	}

	/**
	 * Checks if the code transient exists. If false, then creates it.
	 */
	public function check_custom_code_transient() {
		$transient = get_transient( 'cwicly_custom_code' );
		if ( ! $transient ) {
			$option = get_option( 'cwicly_custom_code' );
			if ( $option ) {
				self::custom_code_sanitize( $option );
			}
		}
	}

	/**
	 * Sanitise and prepare custom code before inputting to database.
	 *
	 * @param string $code The code to sanitise.
	 */
	public static function custom_code_sanitize( $code ) {
		if ( $code ) {
			$coded      = json_decode( $code );
			$head       = array();
			$body_start = array();
			$body_end   = array();

			foreach ( $coded as $k => $v ) {
				if ( 'head' === $v->position ) {
					$head[] = $v->code;
				}
				if ( 'bodyStart' === $v->position ) {
					$body_start[] = $v->code;
				}
				if ( 'bodyEnd' === $v->position ) {
					$body_end[] = $v->code;
				}
			}

			$head       = implode( ' ', $head );
			$body_start = implode( ' ', $body_start );
			$body_end   = implode( ' ', $body_end );

			$final = array( $head, $body_start, $body_end );
			delete_transient( 'cwicly_custom_code' );
			set_transient( 'cwicly_custom_code', $final, 30 * DAY_IN_SECONDS );
		}

		return $code;
	}

	/**
	 * Checks the saved content and filter if necessary.
	 *
	 * @param array $data The data to check.
	 * @param array $postarr The post array.
	 * @param array $unsanitized_postarr The unsanitized post array.
	 */
	public static function filter_saved_content( $data, $postarr, $unsanitized_postarr ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			$patterns             = array( '{return=', '{site_option', '{siteoption' );
			$data['post_content'] = str_replace( $patterns, '', $data['post_content'] );
		}

		$id = $postarr['ID'];

		if ( isset( $postarr['post_type'] ) && $postarr['post_type'] === 'revision' ) {
			$id = $postarr['post_parent'];
			return $data;
		}

		$current_blocks   = parse_blocks( stripslashes( $data['post_content'] ) );
		$previous_content = get_post_field( 'post_content', $id );

		if ( $previous_content ) {
			$previous_blocks = parse_blocks( $previous_content );

			$modified = false;
			self::check_replacement( $previous_blocks, $current_blocks, 'cwicly/svg', $modified );
			self::check_replacement( $previous_blocks, $current_blocks, 'cwicly/code', $modified );

			if ( $modified ) {
				$data['post_content'] = wp_slash( serialize_blocks( $current_blocks ) );
			}
		} else {
			$modified = false;
			self::check_replacement( array(), $current_blocks, 'cwicly/svg', $modified );
			self::check_replacement( array(), $current_blocks, 'cwicly/code', $modified );

			if ( $modified ) {
				$data['post_content'] = wp_slash( serialize_blocks( $current_blocks ) );
			}
		}

		return $data;
	}


	/**
	 * Check for necessary replacements.
	 *
	 * @param array  $previous_blocks The previous blocks.
	 * @param array  $current_blocks The current blocks.
	 * @param string $block_type The block type.
	 * @param bool   $modified Whether the blocks have been modified.
	 */
	public static function check_replacement( $previous_blocks, &$current_blocks, $block_type, &$modified ) {
		if ( ! is_array( $current_blocks ) ) {
			return;
		}
		foreach ( $current_blocks as $key => &$current_block ) {
			if ( ! isset( $current_block['attrs']['uniqueID'] ) ) {
				if ( isset( $current_block['innerBlocks'] ) ) {
					self::check_replacement(
						$previous_blocks,
						$current_block['innerBlocks'],
						$block_type,
						$modified
					);
				}
				continue;
			}

			if ( 'cwicly/svg' === $block_type && $block_type === $current_block['blockName'] ) {
				$unique = $current_block['attrs']['uniqueID'];
				$found  = self::find_block_with_unique( $previous_blocks, $unique );

				if ( $found ) {
					if ( ! Capabilities::permission( 'miscellaneous', 'inlineSvg', true ) ) {
						if ( isset( $found['attrs']['inlineSvg'] ) && $found['attrs']['inlineSvg'] ) {
							if ( ! isset( $current_block['attrs']['inlineSvg'] ) || ! $current_block['attrs']['inlineSvg'] || $found['attrs']['inlineSvg'] !== $current_block['attrs']['inlineSvg'] ) {
								$current_block['attrs']['inlineSvg'] = $found['attrs']['inlineSvg'];
								$modified                            = true;
							}
						} elseif ( isset( $current_block['attrs']['inlineSvg'] ) && $current_block['attrs']['inlineSvg'] ) {
							$current_block['attrs']['inlineSvg'] = '';
							$modified                            = true;
						}
					} elseif ( isset( $found['attrs']['inlineSvg'] ) && $found['attrs']['inlineSvg'] ) {
						$sanitized = Svg::sanitize_inline( $current_block['attrs']['inlineSvg'] );
						if ( $sanitized !== $current_block['attrs']['inlineSvg'] ) {
							$current_block['attrs']['inlineSvg'] = $sanitized;
							$modified                            = true;
						}
					}
				} elseif ( isset( $current_block['attrs']['inlineSvg'] ) && $current_block['attrs']['inlineSvg'] ) {
					if ( ! Capabilities::permission( 'miscellaneous', 'inlineSvg', true ) ) {
						$current_block['attrs']['inlineSvg'] = '';
						$modified                            = true;
					} elseif ( Capabilities::permission( 'miscellaneous', 'inlineSvg', true ) && isset( $current_block['attrs']['inlineSvg'] ) && $current_block['attrs']['inlineSvg'] ) {
						$sanitized = Svg::sanitize_inline( $current_block['attrs']['inlineSvg'] );
						if ( $sanitized !== $current_block['attrs']['inlineSvg'] ) {
							$current_block['attrs']['inlineSvg'] = $sanitized;
							$modified                            = true;
						}
					}
				}
			}
			if ( 'cwicly/code' === $block_type && $block_type === $current_block['blockName'] ) {
				$unique = $current_block['attrs']['uniqueID'];
				$found  = self::find_block_with_unique( $previous_blocks, $unique );

				if ( $found ) {
					if ( ! Capabilities::code_block_php() ) {
						if ( isset( $found['attrs']['code'] ) && $found['attrs']['code'] ) {
							if ( ! isset( $current_block['attrs']['code'] ) || ! $current_block['attrs']['code'] || $found['attrs']['code'] !== $current_block['attrs']['code'] ) {
								$current_block['attrs']['code']             = $found['attrs']['code'];
								$current_block['attrs']['codePHPSignature'] = \Cwicly\Signature::get_signature( 'codePHP', $found['attrs']['code'] );
								$modified                                   = true;
							}
						} elseif ( isset( $current_block['attrs']['code'] ) && $current_block['attrs']['code'] ) {
							$current_block['attrs']['code']             = '';
							$current_block['attrs']['codePHPSignature'] = '';
							$modified                                   = true;
						}
					} else {
						$signature_previous = isset( $found['attrs']['codePHPSignature'] ) ? $found['attrs']['codePHPSignature'] : '';
						$signature_current  = isset( $current_block['attrs']['code'] ) ? \Cwicly\Signature::get_signature( 'codePHP', $current_block['attrs']['code'] ) : '';
						$signature_saved    = isset( $current_block['attrs']['codePHPSignature'] ) ? $current_block['attrs']['codePHPSignature'] : '';
						if ( $signature_previous !== $signature_current || ! isset( $current_block['attrs']['codePHPSignature'] ) || $signature_saved !== $signature_current ) {
							$current_block['attrs']['codePHPSignature'] = $signature_current;
							$modified                                   = true;
						}
					}
					if ( ! Capabilities::code_block_js() ) {
						if ( isset( $found['attrs']['codeJS'] ) && $found['attrs']['codeJS'] ) {
							if ( ! isset( $current_block['attrs']['codeJS'] ) || ! $current_block['attrs']['codeJS'] || $found['attrs']['codeJS'] !== $current_block['attrs']['codeJS'] ) {
								$current_block['attrs']['codeJS']          = $found['attrs']['codeJS'];
								$current_block['attrs']['codeJSSignature'] = \Cwicly\Signature::get_signature( 'codeJS', $found['attrs']['codeJS'] );
								$modified                                  = true;
							}
						} elseif ( isset( $current_block['attrs']['codeJS'] ) && $current_block['attrs']['codeJS'] ) {
							$current_block['attrs']['codeJS']          = '';
							$current_block['attrs']['codeJSSignature'] = '';
							$modified                                  = true;
						}
					} else {
						$signature_previous = isset( $found['attrs']['codeJSSignature'] ) ? $found['attrs']['codeJSSignature'] : '';
						$signature_current  = isset( $current_block['attrs']['codeJS'] ) ? \Cwicly\Signature::get_signature( 'codeJS', $current_block['attrs']['codeJS'] ) : '';
						$signature_saved    = isset( $current_block['attrs']['codeJSSignature'] ) ? $current_block['attrs']['codeJSSignature'] : '';
						if ( $signature_previous !== $signature_current || ! isset( $current_block['attrs']['codeJSSignature'] ) || $signature_saved !== $signature_current ) {
							$current_block['attrs']['codeJSSignature'] = $signature_current;
							$modified                                  = true;
						}
					}
				} else {
					if ( isset( $current_block['attrs']['code'] ) && $current_block['attrs']['code'] ) {
						if ( ! Capabilities::code_block_php() ) {
							$current_block['attrs']['code'] = '';
							$modified                       = true;
						} else {
							$signature                                  = \Cwicly\Signature::get_signature( 'codePHP', $current_block['attrs']['code'] );
							$current_block['attrs']['codePHPSignature'] = $signature;
							$modified                                   = true;
						}
					}
					if ( isset( $current_block['attrs']['codeJS'] ) && $current_block['attrs']['codeJS'] ) {
						if ( ! Capabilities::code_block_js() ) {
							$current_block['attrs']['codeJS'] = '';
							$modified                         = true;
						} else {
							$signature                                 = \Cwicly\Signature::get_signature( 'codeJS', $current_block['attrs']['codeJS'] );
							$current_block['attrs']['codeJSSignature'] = $signature;
							$modified                                  = true;
						}
					}
				}
			}

			if ( isset( $current_block['innerBlocks'] ) ) {
				self::check_replacement(
					$previous_blocks,
					$current_block['innerBlocks'],
					$block_type,
					$modified
				);
			}
		}
		unset( $current_block );
	}

	/**
	 * Find a block with a unique ID.
	 *
	 * @param array  $blocks The blocks to search.
	 * @param string $unique The unique ID to search for.
	 */
	public static function find_block_with_unique( $blocks, $unique ) {
		$found = false;
		foreach ( $blocks as $key => $block ) {
			if ( isset( $block['blockName'] ) && strpos( $block['blockName'], 'cwicly/' ) !== false ) {
				if ( isset( $block['attrs']['uniqueID'] ) && $block['attrs']['uniqueID'] === $unique ) {
					$found = $block;
					break;
				}

				if ( isset( $block['innerBlocks'] ) ) {
					$found = self::find_block_with_unique( $block['innerBlocks'], $unique );

					if ( $found ) {
						break;
					}
				}
			} elseif ( isset( $block['innerBlocks'] ) ) {
					$found = self::find_block_with_unique( $block['innerBlocks'], $unique );

				if ( $found ) {
					break;
				}
			}
		}

		return $found;
	}
}
