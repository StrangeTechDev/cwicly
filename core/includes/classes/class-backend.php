<?php
/**
 * Backend Class.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * Backend Class.
 */
class Backend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_filter( 'block_editor_rest_api_preload_paths', array( $this, 'preload_apis' ), 10, 2 );
	}

	/**
	 * Preload API's.
	 */
	public static function enqueue_block_editor_assets() {
		// LOAD CWICLY JS BLOCKS.
		wp_enqueue_script( 'cwicly_editor_blocks', CWICLY_DIR_URL . 'build/index.js', array( 'lodash', 'wp-i18n', 'wp-blocks', 'wp-core-data', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-api' ), CWICLY_VERSION );
		// LOAD CWICLY JS BLOCKS.

		// LOAD CWICLY CSS BLOCKS.
		wp_enqueue_style( 'cwicly_blocks_editor', CWICLY_DIR_URL . 'build/index.css', array(), filemtime( CWICLY_DIR_PATH . 'build/index.css' ) );
		// LOAD CWICLY CSS BLOCKS.

		// LOAD CWICLY NORMALISER BLOCKS.
		if ( WORDPRESS_VERSION < 6.3 ) {
			$url = apply_filters( 'cc_normaliser_frontend', CWICLY_DIR_URL . 'assets/css/base.css' );
			wp_enqueue_style( 'CCnorm', $url, array(), CWICLY_VERSION );
		}
		// LOAD CWICLY NORMALISER BLOCKS.

		if ( WORDPRESS_VERSION >= 6.3 ) {
			wp_enqueue_style( 'cwicly_new_editor', CWICLY_DIR_URL . 'core/assets/css/new-editor.min.css', array(), CWICLY_VERSION );
		}

		wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

		// LOAD CSS FOR CODE EDITOR.
		wp_enqueue_style( 'material', CWICLY_DIR_URL . 'core/assets/css/material.css', array(), CWICLY_VERSION );
		// LOAD CSS FOR CODE EDITOR.

		$cwicly_optimise = get_option( 'cwicly_optimise' );
		$cwicly_defaults = 'false';
		if ( isset( $cwicly_optimise['cwiclyDefaults'] ) && 'true' === $cwicly_optimise['cwiclyDefaults'] ) {
			$cwicly_defaults = 'true';
		}
		$remove_ids_classes = 'false';
		if ( isset( $cwicly_optimise['removeIDsClasses'] ) && 'true' === $cwicly_optimise['removeIDsClasses'] ) {
			$remove_ids_classes = 'true';
		}
		$no_container_display = false;
		if ( isset( $cwicly_optimise['removeContainerDisplay'] ) && 'true' === $cwicly_optimise['removeContainerDisplay'] ) {
			$no_container_display = true;
		}

		$cwicly_deprecated = get_option( 'cwicly_deprecated' );
		$old_section       = false;
		if ( isset( $cwicly_deprecated['oldSectionLayout'] ) && 'true' === $cwicly_deprecated['oldSectionLayout'] ) {
			$old_section = true;
		}
		$old_button = false;
		if ( isset( $cwicly_deprecated['oldButton'] ) && 'true' === $cwicly_deprecated['oldButton'] ) {
			$old_button = true;
		}

		$site_editor = false;
		if ( isset( $GLOBALS['pagenow'] ) && 'site-editor.php' === $GLOBALS['pagenow'] ) {
			$site_editor = true;
		}

		$api_key = get_option( 'cwicly_gmap' );

		$components_references = array();

		$posts = get_posts(
			array(
				'post_type'   => 'cc_block',
				'numberposts' => -1,
			)
		);

		foreach ( $posts as $post ) {
			$components_references[ $post->ID ] = get_post_meta( $post->ID, 'reference', true );
		}

		$post_type = get_post_type();

		$breakpoints = get_option( 'cwicly_breakpoints_list' );

		$tailwind = get_option( 'cwicly_tailwind' );

		$acf = false;
		if ( class_exists( 'ACF' ) ) {
			$acf = true;
		}

		wp_add_inline_script(
			'cwicly_editor_blocks',
			'window.cwicly_info = ' . wp_json_encode(
				array(
					'plugin'             => CWICLY_DIR_URL,
					'url'                => get_home_url(),
					'uploads'            => CC_UPLOAD_URL,
					'admin'              => get_admin_url(),
					'version'            => CWICLY_VERSION,
					'wordpress'          => WORDPRESS_VERSION,
					'woocommerce'        => CC_WOOCOMMERCE,
					'scss'               => get_option( 'cwicly_scss_compiler' ),
					'pluginClasses'      => apply_filters( 'cwicly_plugin_classes', array() ),
					'pluginGlobalColors' => apply_filters( 'cwicly_global_colors', array() ),
					'currentuser'        => get_current_user_id(),
					'userrole'           => Helpers::get_current_user_roles(),
					'usereditor'         => get_option( 'cwicly_role_editor' ),
					'cwiclyDefaults'     => $cwicly_defaults,
					'restBase'           => untrailingslashit( rest_url() ),
					'nonce'              => wp_create_nonce( 'wp_rest' ),
					'removeIDsClasses'   => $remove_ids_classes,
					'theme'              => get_stylesheet(),
					'oldSection'         => $old_section,
					'oldButton'          => $old_button,
					'siteEditor'         => $site_editor,
					'noContainerDisplay' => $no_container_display,
					//'clc'                => get_option( 'cwicly_license_check' ),
					'showPostTitle'      => Capabilities::permission( 'gutenbergEditor', 'hidePostTitle', true ),
					'gmap'               => $api_key,
					'components'         => $components_references,
					'navClassType'       => apply_filters( 'cwicly/nav/wordpress/classes_position', 'list' ),
					'mainBreakpoint'     => \Cwicly\Helpers::get_main_breakpoint(),
					'postType'           => $post_type,
					'clientView'         => $post_type ? Capabilities::permission( 'clientViews', $post_type, true ) : false,
					'breakpoints'        => $breakpoints,
					'starters'           => self::editor_starters(),
					'tailwind'           => 'true' === $tailwind ? true : false,
					'acf'                => $acf,
				)
			),
			'before'
		);

		// LOAD GOOGLE MAPS WHEN API KEY.
		if ( $api_key ) {
			wp_enqueue_script( 'cc-gmap-places', 'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&callback=Function.prototype', null, CWICLY_VERSION, false );
		}
		// LOAD GOOGLE MAPS WHEN API KEY.
	}

	/**
	 * Function for `block_editor_rest_api_preload_paths` filter-hook.
	 *
	 * @param array $preload_paths Array of paths to preload.
	 * @param array $block_editor_context The block editor context.
	 */
	public function preload_apis( $preload_paths, $block_editor_context ) {

		$preload_paths[] = '/cwicly/v1/global-styles';
		$preload_paths[] = '/cwicly/v1/global-classes';
		$preload_paths[] = '/cwicly/v1/tailwind-classes';
		$preload_paths[] = '/cwicly/v1/tailwind-configurations';
		$preload_paths[] = '/cwicly/v1/global-classes-folders';
		$preload_paths[] = '/cwicly/v1/global-stylesheets-folders';
		$preload_paths[] = '/cwicly/v1/global-stylesheets';
		$preload_paths[] = '/cwicly/v1/external-classes';
		$preload_paths[] = '/cwicly/v1/breakpoints-list';
		$preload_paths[] = '/cwicly/v1/lightmode-selectors';
		$preload_paths[] = '/cwicly/v1/darkmode-selectors';
		$preload_paths[] = '/cwicly/v1/fonts-col';
		$preload_paths[] = '/cwicly/v1/shells';
		return $preload_paths;
	}

	/**
	 * Preload all necessary Editor information directly instantly of calling the API.
	 */
	public static function editor_starters() {
		// ROLES.
		global $wp_roles;
		$all_roles      = $wp_roles->roles;
		$editable_roles = apply_filters( 'editable_roles', $all_roles );
		$roles          = $editable_roles;
		// ROLES.

		// CAPABILITIES.
		$capabilities = get_role( 'administrator' )->capabilities;
		// CAPABILITIES.

		// GLOBAL INTERACTIONS.
		$globalinteractions = get_option( 'cwicly_global_interactions' );
		// GLOBAL INTERACTIONS.

		// GLOBAL CLASSES RENDERED.
		$globalclassesrendered = get_option( 'cwicly_global_classes_rendered' );
		// GLOBAL CLASSES RENDERED.

		// LICENSE CHECK.
		//$licensecheck = get_option( 'cwicly_license_check' );
		// LICENSE CHECK.

		// BREAKPOINTS.
		$breakpoints = get_option( 'cwicly_breakpoints' );
		// BREAKPOINTS.

		// GLOBAL FONTS.
		$globalfonts = get_option( 'cwicly_global_fonts' );
		// GLOBAL FONTS.

		// LOCAL FONTS.
		$localfonts = get_option( 'cwicly_local_fonts' );
		// LOCAL FONTS.

		// LOCAL ACTIVE FONTS.
		$localactivefonts = get_option( 'cwicly_local_active_fonts' );
		// LOCAL ACTIVE FONTS.

		// HEARTBEAT.
		$heartbeat = get_option( 'cwicly_heartbeat' );
		// HEARTBEAT.

		// SECTION DEFAULTS.
		$sectiondefaults = get_option( 'cwicly_section_defaults' );
		// SECTION DEFAULTS.

		// POSTS PER PAGE DEFAULTS.
		$postsperpage = get_option( 'posts_per_page' );
		// POSTS PER PAGE DEFAULTS.

		// GLOBAL PARTS.
		$globalparts = get_option( 'cwicly_global_parts' );
		// GLOBAL PARTS.

		// CWICLY PSEUDOS.
		$cwiclypseudos = get_option( 'cwicly_pseudos' );
		// CWICLY PSEUDOS.

		// CWICLY COLLECTION.
		$cwiclycollection = get_option( 'cwicly_collection' );
		// CWICLY COLLECTION.

		// WOO TAX CLASSES SLUG.
		$wootaxclasses = '';
		if ( CC_WOOCOMMERCE ) {
			$wootaxclasses = \WC_Tax::get_tax_class_slugs();
		}
		// WOO TAX CLASSES SLUG.

		// WOO PRODUCT TYPES.
		$wooproducttypes = '';
		if ( CC_WOOCOMMERCE ) {
			$wooproducttypes = wc_get_product_types();
		}
		// WOO PRODUCT TYPES.

		// WOO SHIPPING CLASSES.
		$wooshippingclasses = '';
		if ( CC_WOOCOMMERCE ) {
			$wc_shipping        = new \WC_Shipping();
			$wooshippingclasses = $wc_shipping->get_shipping_classes();
		}
		// WOO SHIPPING CLASSES.

		// ALL IMAGE SIZES.
		$allimagesizes = \Cwicly\Helpers::get_all_image_sizes();
		// ALL IMAGE SIZES.

		// ROLE EDITOR.
		$roleeditor = get_option( 'cwicly_role_editor' );
		// ROLE EDITOR.

		$globalstyles = get_option( 'cwicly_global_styles' );

		$globalstyles = false === $globalstyles ? '{}' : $globalstyles;

		// CWICLY COLLECTION.
		$cwiclycomponentsfolders = get_option( 'cwicly_components_folders' );
		// CWICLY COLLECTION.

		// CWICLY TAILWIND CLASSES.
		$cwiclytailwindclasses = get_option( 'cwicly_tailwind_classes' );
		// CWICLY TAILWIND CLASSES.

		return array(
			'success'                 => true,
			'roles'                   => $roles,
			'capabilities'            => $capabilities,
			'globalinteractions'      => $globalinteractions,
			'globalclassesrendered'   => $globalclassesrendered,
			// 'licensecheck'            => $licensecheck,
			'breakpoints'             => $breakpoints,
			'globalfonts'             => $globalfonts,
			'localfonts'              => $localfonts,
			'localactivefonts'        => $localactivefonts,
			'heartbeat'               => $heartbeat,
			'sectiondefaults'         => $sectiondefaults,
			'wootaxclasses'           => $wootaxclasses,
			'wooshippingclasses'      => $wooshippingclasses,
			'wooproducttypes'         => $wooproducttypes,
			'cwiclypseudos'           => $cwiclypseudos,
			'cwiclycollection'        => $cwiclycollection,
			'postsperpage'            => $postsperpage,
			'globalparts'             => $globalparts,
			'allimagesizes'           => $allimagesizes,
			'roleeditor'              => $roleeditor,
			'cwiclycomponentsfolders' => $cwiclycomponentsfolders,
			'tailwindClasses'         => $cwiclytailwindclasses,

			'globalstyles'            => $globalstyles,
		);
	}
}
