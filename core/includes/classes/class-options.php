<?php
/**
 * Main options setup.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * This is the main options class. It is responsible for setting up the plugin options.
 */
class Options {

	/**
	 * The single instance of the class.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'options_setup' ) );
		add_action( 'admin_init', array( $this, 'register_options' ) );
		add_action( 'rest_api_init', array( $this, 'register_options' ) );
	}

	/**
	 * Sets up all the necessary so that we don't have errors when accessing the option for the first time.
	 */
	public function options_setup() {
		if ( is_admin() ) {
			$ssl_verify               = get_option( 'cwicly_ssl_verify' );
			$svg_cols                 = get_option( 'cwicly_svg_cols' );
			$custom_code              = get_option( 'cwicly_custom_code' );
			$section_defaults         = get_option( 'cwicly_section_defaults' );
			$role_editor              = get_option( 'cwicly_role_editor' );
			$optimise                 = get_option( 'cwicly_optimise' );
			$cwicly_acf_rest_frontend = get_option( 'cwicly_acf_rest_frontend' );
			$heartbeat                = get_option( 'cwicly_heartbeat' );
			$themer_heartbeat         = get_option( 'cwicly_themer_heartbeat' );
			$tailwind                 = get_option( 'cwicly_tailwind' );
			$breakpoints              = get_option( 'cwicly_breakpoints_list' );

			if ( get_option( 'cwicly_classes_add' ) ) {
				delete_option( 'cwicly_classes_add' );
			}
			if ( get_option( 'cwicly_css' ) ) {
				delete_option( 'cwicly_css' );
			}
			if ( get_option( 'cwicly_theme_license_key' ) ) {
				delete_option( 'cwicly_theme_license_key' );
			}
			if ( get_option( 'cwicly_plugin_license_key' ) ) {
				delete_option( 'cwicly_plugin_license_key' );
			}
			if ( get_option( 'cwicly_theme_license_key_status' ) ) {
				delete_option( 'cwicly_theme_license_key_status' );
			}

			$global_classes = get_option( 'cwicly_global_classes' );
			if ( $global_classes && is_array( $global_classes ) ) {
				update_option( 'cwicly_global_classes', wp_json_encode( $global_classes ) );
			} elseif ( false === $global_classes ) {
				update_option( 'cwicly_global_classes', '{}' );
			}
			$global_stylesheets = get_option( 'cwicly_global_stylesheets' );
			if ( $global_stylesheets && is_array( $global_stylesheets ) ) {
				update_option( 'cwicly_global_stylesheets', wp_json_encode( $global_stylesheets ) );
			} elseif ( false === $global_stylesheets ) {
				update_option( 'cwicly_global_stylesheets', '[]' );
			}
			$external_classes = get_option( 'cwicly_external_classes' );
			if ( $external_classes && is_array( $external_classes ) ) {
				update_option( 'cwicly_external_classes', wp_json_encode( $external_classes ) );
			} elseif ( false === $external_classes ) {
				update_option( 'cwicly_external_classes', '[]' );
			}
			$global_styles = get_option( 'cwicly_global_styles' );
			if ( $global_styles && is_array( $global_styles ) ) {
				update_option( 'cwicly_global_styles', wp_json_encode( $global_styles ) );
			} elseif ( false === $global_styles ) {
				update_option( 'cwicly_global_styles', '{}' );
			}
            // phpcs:disable
			// $cwiclyTailwindClasses = get_option("cwicly_tailwind_classes");
			// if ($cwiclyTailwindClasses == false) {
			// update_option('cwicly_tailwind_classes', '[]');
			// }
            // phpcs:enable

			$breakpoint_list = get_option( 'cwicly_breakpoints_list' );
			if ( empty( $breakpoint_list ) ) {
				$old_breakpoints = get_option( 'cwicly_breakpoints' );

				if ( $old_breakpoints ) {
					if ( ! isset( $old_breakpoints['md'] ) ) {
						$old_breakpoints['md'] = 992;
					}
					if ( ! isset( $old_breakpoints['sm'] ) ) {
						$old_breakpoints['sm'] = 576;
					}
					$breakpoint_list = '{"lg":{"name": "Desktop", "width":1366, "isMain": true, "icon": "Desktop"}, "md":{"name": "Tablet", "width":' . $old_breakpoints['md'] . ', "icon": "Tablet"},"sm":{"name": "Mobile", "width":' . $old_breakpoints['sm'] . ', "icon": "Mobile"}}';
				}

				if ( ! $old_breakpoints ) {
					$breakpoint_list = '{"lg":{"name": "Desktop", "width":1366, "isMain": true, "icon": "Desktop"}, "md":{"name": "Tablet", "width":992, "icon": "Tablet"},"sm":{"name": "Mobile", "width":576, "icon": "Mobile"}}';
				}
				update_option( 'cwicly_breakpoints_list', $breakpoint_list );
			}

			if ( empty( $svg_cols ) ) {

				$cols = array(
					'fontawesome'     => 'Font Awesome',
					'phosphorlight'   => 'Phosphor Light',
					'phosphorregular' => 'Phosphor Regular',
					'phosphorduo'     => 'Phosphor Duotone',
				);
				update_option( 'cwicly_svg_cols', wp_json_encode( $cols ) );
			}
			$font_cols = get_option( 'cwicly_font_cols' );
			if ( empty( $font_cols ) ) {
				$cols = '{}';
				update_option( 'cwicly_font_cols', $cols );
			}
			if ( empty( $ssl_verify ) ) {
				update_option( 'cwicly_ssl_verify', 'true' );
			}
			if ( empty( $custom_code ) ) {
				update_option( 'cwicly_custom_code', wp_json_encode( (object) array() ) );
			}
			if ( empty( $optimise ) ) {
				update_option( 'cwicly_optimise', array() );
			}
			if ( empty( $cwicly_acf_rest_frontend ) ) {
				update_option( 'cwicly_acf_rest_frontend', 'true' );
			}
			if ( empty( $section_defaults ) ) {
				update_option(
					'cwicly_section_defaults',
					array(
						'maxWidth'      => array( 'lg' => '1120px' ),
						'width'         => array( 'lg' => '90%' ),
						'paddingTop'    => array( 'lg' => '150px' ),
						'paddingBottom' => array( 'lg' => '150px' ),
						'paddingLeft'   => array( 'lg' => '' ),
						'paddingRight'  => array( 'lg' => '' ),
					)
				);
			}
			if ( empty( $heartbeat ) ) {
				update_option(
					'cwicly_heartbeat',
					array(
						'cwicly_local_active_fonts'     => time(),
						'cwicly_local_fonts'            => time(),
						'cwicly_section_defaults'       => time(),
						'cwicly_global_styles'          => time(),
						'cwicly_breakpoints'            => time(),
						'cwicly_global_classes'         => time(),
						'cwicly_global_classes_folders' => time(),
						'cwicly_global_stylesheets'     => time(),
						'cwicly_global_stylesheets_folders' => time(),
						'cwicly_components_folders'     => time(),
					)
				);
			}
			if ( empty( $themer_heartbeat ) ) {
				update_option(
					'cwicly_heartbeat',
					array(
						'cwicly_global_parts'   => time(),
						'cwicly_pre_conditions' => time(),
					)
				);
			}
			if ( empty( $role_editor ) ) {
				update_option(
					'cwicly_role_editor',
					array(
						'administrator' => array(
							'gutenbergEditor'      => array(
								'designLibrary'      => true,
								'headerToolbar'      => true,
								'hidePostTitle'      => true,
								'cwiclyNavigator'    => true,
								'quickInserter'      => true,
								'smartInserter'      => true,
								'globalStylesToggle' => true,
								'hideListView'       => false,
							),
							'globalBlockBehaviour' => array(
								'globalStylesPanel'  => true,
								'globalClassesPanel' => true,
								'collectionPanel'    => true,
								'conditions'         => true,
								'link'               => true,
								'interactions'       => true,
								'idClassManager'     => true,
								'designTab'          => true,
								'advancedTab'        => true,
								'tagControl'         => true,
								'hoverAnimation'     => true,
								'headingTag'         => true,
								'specificProperties' => true,
							),
							'blockToolbar'         => array(
								'controls'        => true,
								'dynamicValues'   => true,
								'richTextStyling' => true,
							),
							'miscellaneous'        => array(
								'selectPseudoClasses' => true,
								'addPseudoClasses'    => true,
							),
						),
					)
				);
			}
			if ( $role_editor && ! isset( $role_editor['administrator']['components'] ) ) {
				$role_editor['administrator']['components'] = array(
					'modify' => true,
				);
				update_option( 'cwicly_role_editor', $role_editor );
			}
			if ( $role_editor && ! isset( $role_editor['administrator']['gutenbergEditor']['componentLibrary'] ) ) {
				$role_editor['administrator']['gutenbergEditor']['componentLibrary'] = true;
				update_option( 'cwicly_role_editor', $role_editor );
			}
			if ( $role_editor && ! isset( $role_editor['administrator']['gutenbergEditor']['quickInserter'] ) ) {
				$role_editor['administrator']['gutenbergEditor']['quickInserter'] = true;
				update_option( 'cwicly_role_editor', $role_editor );
			}
			if ( $role_editor && ! isset( $role_editor['administrator']['tailwind'] ) ) {
				$role_editor['administrator']['tailwind'] = array(
					'configs'      => true,
					'shells'       => true,
					'convertShell' => true,
				);
				update_option( 'cwicly_role_editor', $role_editor );
			}
			if ( empty( $tailwind ) ) {
				update_option( 'cwicly_tailwind', true );
			}

			if ( $breakpoints ) {
				$breakpoints = json_decode( $breakpoints, true );
				if ( ! isset( $breakpoints['lg']['active'] ) ) {
					$breakpoints['lg']['active'] = true;
				}
				if ( ! isset( $breakpoints['md']['active'] ) ) {
					$breakpoints['md']['active'] = true;
				}
				update_option( 'cwicly_breakpoints_list', wp_json_encode( $breakpoints ) );
			}
		}
	}

	/**
	 * Register all the options so that they can be accessible via the REST API.
	 */
	public function register_options() {
		// register_setting(
		// 	'cwicly',
		// 	'cwicly_plugin_license_key_status',
		// 	array( 'show_in_rest' => true )
		// );
		register_setting(
			'cwicly',
			'cwicly_font_cols',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_svg_cols',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_global_fonts',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_breakpoints',
			array(
				'show_in_rest' => true,
				'default'      => array(
					'md' => 992,
					'sm' => 576,
				),
			)
		);
		register_setting(
			'cwicly',
			'cwicly_ssl_verify',
			array(
				'show_in_rest' => true,
				'default'      => 'true',
			)
		);
		register_setting(
			'cwicly',
			'cwicly_close_importer',
			array(
				'show_in_rest' => true,
				'default'      => 'true',
			)
		);
		register_setting(
			'cwicly',
			'cwicly_custom_code',
			array(
				'show_in_rest'      => true,
				'sanitize_callback' => array( '\Cwicly\Settings', 'custom_code_sanitize' ),
			)
		);
		register_setting(
			'cwicly',
			'cwicly_conditions',
			array(
				'show_in_rest' => true,
				'default'      => array(
					'singular' => array(),
					'archive'  => array(),
					'all'      => array(),
				),
			)
		);
		register_setting(
			'cwicly',
			'cwicly_global_parts',
			array(
				'show_in_rest' => true,
				'default'      => array(
					'notices' => array(
						'error'   => array( 'template' => '' ),
						'notice'  => array( 'template' => '' ),
						'success' => array( 'template' => '' ),
					),
					'account' => array(),
				),
			),
		);
		register_setting(
			'cwicly',
			'cwicly_pre_conditions',
			array(
				'show_in_rest' => true,
				'default'      => '{}',
			)
		);
		register_setting(
			'cwicly',
			'cwicly_tailwind_classes',
			array( 'show_in_rest' => true )
		);
		// register_setting(
		// 	'cwicly',
		// 	'cwicly_license_check',
		// 	array( 'show_in_rest' => true )
		// );
		register_setting(
			'cwicly',
			'cwicly_global_classes_folders',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_global_stylesheets_folders',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_global_styles',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_global_classes_rendered',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_global_classes_save',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_pseudos',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_global_interactions',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_section_defaults',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_optimise',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_role_editor',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_scss_compiler',
			array(
				'show_in_rest'      => true,
				'sanitize_callback' => array( '\Cwicly\Options', 'scss_compiler_sanitize' ),
			)
		);
		register_setting(
			'cwicly',
			'cwicly_tailwind',
			array(
				'show_in_rest' => true,
			)
		);
		register_setting(
			'cwicly',
			'cwicly_acf_rest_frontend',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_hide_list_container',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_deactivate_heartbeat',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_design_auth',
			array(
				'show_in_rest' => true,
				'default'      => '[]',
			)
		);
		register_setting(
			'cwicly',
			'cwicly_deprecated',
			array( 'show_in_rest' => true )
		);
		register_setting(
			'cwicly',
			'cwicly_gmap',
			array( 'show_in_rest' => true )
		);
	}

	/**
	 * Gets/Removes the SCSS compiler assets
	 *
	 * @param string $option The option value.
	 */
	public static function scss_compiler_sanitize( $option ) {
		if ( 'true' === $option ) {
			return 'true';
		} else {
			$upload_dir = wp_upload_dir();
			$dir        = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/assets/sass.worker.min.js';
			wp_delete_file( $dir );
			return 'false';
		}
	}
}
