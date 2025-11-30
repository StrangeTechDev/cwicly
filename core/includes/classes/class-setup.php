<?php
/**
 * Main setup.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * This is the main setup class. It is responsible for setting up the plugin.
 */
class Setup {

	/**
	 * The single instance of the class.
	 */
	public function __construct() {
		//add_action( 'init', array( $this, 'updater' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
		add_filter( 'block_categories_all', array( $this, 'register_cwicly_category' ), 10, 2 );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 99 );
		add_action( 'after_setup_theme', array( $this, 'add_global_colors_to_iframe' ) );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'prefix_nav_menu_classes' ), 10, 3 );
		add_filter( 'plugin_action_links_' . plugin_basename( CWICLY_FILE ), array( $this, 'settings_links' ) );
		add_action( 'activated_plugin', array( $this, 'activation_redirect' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_jquery_admin' ) );
		add_action( 'wp_ajax_cc_dismissed_notice_handler', array( $this, 'ajax_notice_handler' ) );
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_custom_fields' ), 10, 2 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'menu_nav_update' ), 10, 2 );
		add_action( 'init', array( $this, 'create_initial_post_types' ) );
		add_action( 'init', array( $this, 'create_globals' ) );
		add_action( 'after_setup_theme', array( $this, 'optimiser' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice__regenerate' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );
		add_filter( 'pre_render_block', array( $this, 'pre_renderer' ), 10, 3 );
		add_filter( 'render_block_core/shortcode', array( $this, 'shortcode_renderer' ), 10, 3 );
		add_filter( 'content_save_pre', array( $this, 'replace_wpml_curved_strings' ) );
		add_filter( 'render_block_data', array( $this, 'render_block_data' ), 10, 3 );
		add_action( 'wp_default_styles', array( $this, 'remove_relative_position' ) );

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			remove_filter( 'term_description', 'wpautop' );
			remove_filter( 'the_content', 'wpautop' );
		}

		add_action( 'transition_post_status', array( $this, 'new_component' ), 10, 3 );
	}

	/**
	 * Enqueue Gutenberg block assets for backend
	 *
	 * @since 1.3.0.5
	 */
	public function enqueue_block_assets() {
		if ( is_admin() ) {
			wp_enqueue_style( 'CC-styles', CWICLY_DIR_URL . 'build/style-index.css', array( 'CCnorm' ), CWICLY_VERSION );
			wp_enqueue_style( 'CC-wrapper', CWICLY_DIR_URL . 'core/assets/css/editor-wrapper.css', array( 'CCnorm' ), CWICLY_VERSION );
			wp_enqueue_style( 'CC-hover-animation', CWICLY_DIR_URL . 'assets/css/hover-animation.css', array( 'CCnorm' ), CWICLY_VERSION );
			wp_enqueue_style( 'CC-gallery', CWICLY_DIR_URL . 'assets/css/gallery.css', array( 'CCnorm' ), CWICLY_VERSION );
			wp_enqueue_style( 'CC-splide', CWICLY_DIR_URL . 'assets/css/splide.css', array( 'CCnorm' ), CWICLY_VERSION );
			wp_enqueue_style( 'CC-swiper', CWICLY_DIR_URL . 'assets/css/swiper.css', array( 'CCnorm' ), CWICLY_VERSION );

			$hide_post_title_responsive = Capabilities::permission( 'gutenbergEditor', 'hidePostTitle', true );
			$header_toolbar             = Capabilities::permission( 'gutenbergEditor', 'headerToolbar', true );
			if ( $hide_post_title_responsive && $header_toolbar ) {
				wp_enqueue_style( 'CC-hide-post-title', CWICLY_DIR_URL . 'core/assets/css/hide-post-title.css', array( 'CCnorm' ), CWICLY_VERSION );
			} elseif ( $hide_post_title_responsive && ! $header_toolbar ) {
				wp_enqueue_style( 'CC-hide-post-title-responsive', CWICLY_DIR_URL . 'core/assets/css/hide-post-title-responsive.css', array( 'CCnorm' ), CWICLY_VERSION );
			} elseif ( $header_toolbar ) {
				wp_enqueue_style( 'CC-hide-post-title-toolbar', CWICLY_DIR_URL . 'core/assets/css/hide-post-title-toolbar.css', array( 'CCnorm' ), CWICLY_VERSION );
			}
		}
	}

	/**
	 * Register Cwicly blocks category.
	 *
	 * @param array   $block_categories Block categories.
	 * @param WP_Post $post Current post.
	 */
	public function register_cwicly_category( $block_categories, $post ) {
		array_unshift(
			$block_categories,
			array(
				'slug'  => 'cwicly',
				'title' => __( 'Cwicly Blocks', 'cwicly' ),
			)
		);
		return $block_categories;
	}

	/**
	 * Add Cwicly admin menu bar base
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The admin bar.
	 */
	public static function admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ) {
		if ( is_admin() ) {
			return;
		}
		$cwicly_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTA2IiBoZWlnaHQ9IjQyNyIgdmlld0JveD0iMCAwIDUwNiA0MjciIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0yMDAuMDA4IDcwQzIxNy4wMTkgNzAgMjMwLjgwOSA4Ni4wNjExIDIzMC44MDkgMTA1Ljg3NUMyMzAuODA5IDEyNS42ODkgMjE3LjAxOSAxNDEuNzUgMjAwLjAwOCAxNDEuNzVIMTY5LjIwNkMxMzUuMTg0IDE0MS43NSAxMDcuNjAzIDE3My44NzQgMTA3LjYwMyAyMTMuNUMxMDcuNjAzIDI1My4xMjYgMTM1LjE4NCAyODUuMjUgMTY5LjIwNiAyODUuMjVIMjAwLjAwOEMyMTcuMDE5IDI4NS4yNSAyMzAuODA5IDMwMS4zMTEgMjMwLjgwOSAzMjEuMTI1QzIzMC44MDkgMzQwLjkzOSAyMTcuMDE5IDM1NyAyMDAuMDA4IDM1N0gxNjkuMjA2QzEwMS4xNjEgMzU3IDQ2IDI5Mi43NTMgNDYgMjEzLjVDNDYgMTM0LjI0NyAxMDEuMTYxIDcwIDE2OS4yMDYgNzBIMjAwLjAwOFoiIGZpbGw9IiNFOEU4RTgiLz4KPHBhdGggZD0iTTMwNS40NiA3MEMyODguNDQ5IDcwIDI3NC42NTkgODYuMDYxMSAyNzQuNjU5IDEwNS44NzVDMjc0LjY1OSAxMjUuNjg5IDI4OC40NDkgMTQxLjc1IDMwNS40NiAxNDEuNzVIMzM2LjI2MkMzNzAuMjg0IDE0MS43NSAzOTcuODY1IDE3My44NzQgMzk3Ljg2NSAyMTMuNUMzOTcuODY1IDI1My4xMjYgMzcwLjI4NCAyODUuMjUgMzM2LjI2MiAyODUuMjVIMzA1LjQ2QzI4OC40NDkgMjg1LjI1IDI3NC42NTkgMzAxLjMxMSAyNzQuNjU5IDMyMS4xMjVDMjc0LjY1OSAzNDAuOTM5IDI4OC40NDkgMzU3IDMwNS40NiAzNTdIMzM2LjI2MkM0MDQuMzA3IDM1NyA0NTkuNDY4IDI5Mi43NTMgNDU5LjQ2OCAyMTMuNUM0NTkuNDY4IDEzNC4yNDcgNDA0LjMwNyA3MCAzMzYuMjYyIDcwSDMwNS40NloiIGZpbGw9IiNFOEU4RTgiLz4KPC9zdmc+Cg==';

		if ( ! Capabilities::permission( 'miscellaneous', 'hideAdminBarTemplateInfo' ) ) {

			// Load admin.min.css to add styles to the quick edit links.
			if ( is_admin_bar_showing() ) {
				wp_enqueue_style( 'cwicly-admin', CWICLY_DIR_URL . 'core/assets/css/admin.min.css', null, CWICLY_VERSION );
			}

			$args = array(
				'id'    => 'cwiclythemer',
				'title' => '<style>#wpadminbar #wp-admin-bar-cwiclythemer>.ab-item:before {content: "";top: 3px;width: 26px;height: 26px;background-position: center;background-repeat: no-repeat;background-size: 23px; background-image: url(' . $cwicly_icon . ')!important;}</style><span style="--hello: url(' . $cwicly_icon . ');">Edit Template</span>',
				'meta'  => array(
					'class' => 'ccthemer',
					'title' => 'Current Template in use',
				),
			);
			$wp_admin_bar->add_menu( $args );

			$template_parts = array(
				'id'     => 'cctemplateparts',
				'title'  => 'Template Parts',
				'parent' => 'cwiclythemer',
				'href'   => '' . admin_url( 'site-editor.php?postType=wp_template_part' ) . '',
				'meta'   => array(
					'title' => 'Go to Template Parts',
				),
			);
			$wp_admin_bar->add_node( $template_parts );

			$themer = array(
				'id'     => 'ccthemer',
				'title'  => 'Themer',
				'parent' => 'cwiclythemer',
				'href'   => '' . admin_url( 'admin.php?page=cwicly' ) . '',
				'meta'   => array(
					'title' => 'Go to Cwicly Themer',
				),
			);
			$wp_admin_bar->add_node( $themer );

			$settings = array(
				'id'     => 'ccsettings',
				'title'  => 'Settings',
				'parent' => 'cwiclythemer',
				'href'   => '' . admin_url( 'admin.php?page=cwicly-settings' ) . '',
				'meta'   => array(
					'title' => 'Go to Cwicly Settings',
				),
			);
			$wp_admin_bar->add_node( $settings );
		}
	}

	/**
	 * Make global colours for the Cwicly global style frame.
	 */
	public static function add_global_colors_to_iframe() {
		$globals = get_option( 'cwicly_global_styles' );
		$final   = array();
		if ( $globals && '{}' !== $globals ) {
			$globals = json_decode( $globals );
		if ( isset( $globals->styles->style1->colors ) ) { // phpcs:ignore
				$array = $globals->styles->style1->colors; // phpcs:ignore
				foreach ( $array as $key => $value ) {
					if ( isset( $value->variable ) && isset( $value->id ) ) {
						$final[] = array(
							'name'  => isset( $value->name ) ? $value->name : $value->id,
							'slug'  => 'cc-' . ( $value->id ) . '',
							'color' => 'var(--' . ( $value->variable ) . ')',
						);
					}
				}
			}
			add_theme_support( 'editor-color-palette', $final );
		}
	}

	/**
	 * Adds a class prefix to the main Cwicly settings so that we can style the icon.
	 *
	 * @param  array  $items The menu items, sorted by each menu item's menu order.
	 * @param  object $menu The menu object.
	 * @param  array  $args An array of arguments.
	 */
	public static function prefix_nav_menu_classes( $items, $menu, $args ) {
		_wp_menu_item_classes_by_context( $items );
		return $items;
	}

	/**
	 * Add necessary links to the plugin page for Cwicly.
	 *
	 * @param array $links The links to display on the plugin page.
	 */
	public static function settings_links( $links ) {
		$action_links = array(
			'settings'      => '<a href="' . admin_url( 'admin.php?page=cwicly-settings' ) . '" aria-label="' . esc_attr__( 'View Cwicly settings', 'cwicly' ) . '">' . esc_html__( 'Settings', 'cwicly' ) . '</a>',
			'documentation' => '<a href="https://docs.cwicly.com/" aria-label="' . esc_attr__( 'View Cwicly documentation', 'cwicly' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'cwicly' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Redirect to getting start or settings page after activation.
	 *
	 * @param string $plugin The plugin file.
	 */
	public static function activation_redirect( $plugin ) {
		if ( plugin_basename( CWICLY_FILE ) === $plugin ) {
			if ( ! get_option( 'cwicly_new_install' ) ) {
				update_option( 'cwicly_new_install', true );
				$deprecated = get_option( 'cwicly_deprecated' );
				if ( empty( $deprecated ) ) {
					$deprecated = array();
					update_option( 'cwicly_deprecated', $deprecated );
				}
				$optimise = get_option( 'cwicly_optimise' );
				if ( empty( $optimise ) ) {
					$optimise                          = array();
					$optimise['removeIDsClasses']      = 'true';
					$optimise['wordPressGlobalStyles'] = 'true';
					$optimise['svgFilter']             = 'true';
					$optimise['wordPressEmojis']       = 'true';
					$optimise['templatePartWrapper']   = 'true';
					update_option( 'cwicly_optimise', $optimise );
				}
				$welcome = admin_url( 'admin.php?page=cwicly-welcome' );
				exit( esc_html( wp_safe_redirect( $welcome ) ) );
			} else {
				$settings = admin_url( 'admin.php?page=cwicly-settings' );
				exit( esc_html( wp_safe_redirect( $settings ) ) );
			}
		}
	}

	/**
	 * Add a notice to the admin to let the user know that they need to regenerate the HTML.
	 */
	public static function enqueue_jquery_admin() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script(
			'cwicly-notify',
			CWICLY_DIR_URL . 'core/assets/js/dismisser.js',
			array( 'jquery' ),
			CWICLY_VERSION,
			false
		);
		wp_enqueue_script(
			'cwicly-editor-enhancements',
			CWICLY_DIR_URL . 'core/assets/js/editor-enhancements.js',
			array( 'jquery' ),
			CWICLY_VERSION,
			false
		);
	}

	/**
	 * AJAX handler to store the state of dismissible notices.
	 */
	public static function ajax_notice_handler() {
		// Store it in the options table.
		update_option( 'cwicly_regenerate_html', 'false' );
	}

	/**
	 * Initialize the updater. Hooked into `init` to work with the
	 * wp_version_check cron job, which allows auto-updates.
	 */
	/*public static function updater() {
		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		// We don't need to check license anymore.
		// phpcs:disable
		// if ( get_option( 'cwicly_plugin_license_key_status' ) === 'valid' ) {

		// if ( defined( 'CC_LICENSE_KEY' ) ) {
		// phpcs:enable 
			$cwicly_updater = new \Cwicly_Plugin_Updater(
				CC_STORE_URL,
				CWICLY_FILE,
				array(
					'version' => CWICLY_VERSION,
					'license' => defined( 'CC_LICENSE_KEY' ) ? CC_LICENSE_KEY : 'free',
					'item_id' => CC_PLUGIN_ID,
					'author'  => 'Cwicly',
					'beta'    => CWICLY_BETA,
				)
			);
			// phpcs:disable
			// }
			// phpcs:enable

		// phpcs:disable
		// }
		// phpcs:enable
	}*/

	/**
	 * Add custom fields to menu item
	 *
	 * This will allow us to play nicely with any other plugin that is adding the same hook
	 *
	 * @param  int    $item_id The menu item ID.
	 * @param  object $item The menu item object.
	 */
	public function menu_custom_fields( $item_id, $item ) {

		wp_nonce_field( 'cwicly_menu', '_cwicly_menu' );
		$is_footer = get_post_meta( $item_id, '_is_footer', true );
		if ( $is_footer ) {
			$is_footer = 'checked="checked"';
		}
		$hide_title = get_post_meta( $item_id, '_hide_title', true );
		if ( $hide_title ) {
			$hide_title = 'checked="checked"';
		}
		?>
		<div class="description-wide">
			<p class="field-cwicly_meta description" style="font-weight: 500;">
				<?php esc_html_e( 'Cwicly Nav', 'cwicly' ); ?>
			</p>

			<p class="field-cwicly_meta description">
				<input type="hidden" class="nav-menu-id" value="<?php echo esc_html( $item_id ); ?>" />

				<label for="hide_title-for-<?php echo esc_html( $item_id ); ?>">
					<input type="checkbox" name="hide_title[<?php echo esc_html( $item_id ); ?>]" id="hide_title-for-<?php echo esc_html( $item_id ); ?>"
						<?php echo esc_attr( $hide_title ); ?>/>

					<?php esc_html_e( 'Hide Title', 'cwicly' ); ?>
				</label>
			</p>
			<p class="field-cwicly_meta description">
				<input type="hidden" class="nav-menu-id" value="<?php echo esc_html( $item_id ); ?>" />

				<label for="is_footer-for-<?php echo esc_html( $item_id ); ?>">
					<input type="checkbox" name="is_footer[<?php echo esc_html( $item_id ); ?>]" id="is_footer-for-<?php echo esc_html( $item_id ); ?>"
						<?php echo esc_attr( $is_footer ); ?>/>

					<?php esc_html_e( 'Is footer', 'cwicly' ); ?>
				</label>
			</p>
		</div>
		<?php
	}

	/**
	 * Save the menu item meta
	 *
	 * @param int $menu_id The menu ID.
	 * @param int $menu_item_db_id The menu item ID.
	 */
	public function menu_nav_update( $menu_id, $menu_item_db_id ) {

		// Verify this came from our screen and with proper authorization.
		if ( ! isset( $_POST['_cwicly_menu'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_cwicly_menu'] ) ), 'cwicly_menu' ) ) {
			return $menu_id;
		}

		if ( isset( $_POST['is_footer'][ $menu_item_db_id ] ) ) {
			$sanitized_data = sanitize_text_field( wp_unslash( $_POST['is_footer'][ $menu_item_db_id ] ) );
			update_post_meta( $menu_item_db_id, '_is_footer', $sanitized_data );
		} else {
			delete_post_meta( $menu_item_db_id, '_is_footer' );
		}

		if ( isset( $_POST['hide_title'][ $menu_item_db_id ] ) ) {
			$sanitized_data = sanitize_text_field( wp_unslash( $_POST['hide_title'][ $menu_item_db_id ] ) );
			update_post_meta( $menu_item_db_id, '_hide_title', $sanitized_data );
		} else {
			delete_post_meta( $menu_item_db_id, '_hide_title' );
		}
	}

	/**
	 * Creates the necessary post types when 'init' action is fired.
	 */
	public function create_initial_post_types() {
		register_post_type(
			'cc_block',
			array(
				'labels'                => array(
					'name'                     => _x( 'Components', 'post type general name' ),
					'singular_name'            => _x( 'Component', 'post type singular name' ),
					'add_new'                  => _x( 'Add New', 'Component' ),
					'add_new_item'             => __( 'Add new Component' ),
					'new_item'                 => __( 'New Component' ),
					'edit_item'                => __( 'Edit Component' ),
					'view_item'                => __( 'View Component' ),
					'all_items'                => __( 'All Component' ),
					'search_items'             => __( 'Search components' ),
					'not_found'                => __( 'No components found.' ),
					'not_found_in_trash'       => __( 'No components found in Trash.' ),
					'filter_items_list'        => __( 'Filter components list' ),
					'items_list_navigation'    => __( 'Components list navigation' ),
					'items_list'               => __( 'Components list' ),
					'item_published'           => __( 'Components published.' ),
					'item_published_privately' => __( 'Components published privately.' ),
					'item_reverted_to_draft'   => __( 'Components reverted to draft.' ),
					'item_scheduled'           => __( 'Components scheduled.' ),
					'item_updated'             => __( 'Components updated.' ),
				),
				'public'                => false,
				'show_ui'               => false,
				'show_in_menu'          => false,
				'rewrite'               => false,
				'show_in_rest'          => true,
				'rest_base'             => 'components',
				'rest_controller_class' => 'WP_REST_Blocks_Controller',
				'capability_type'       => 'block',
				'capabilities'          => array(
					// You need to be able to edit posts, in order to read blocks in their raw form.
					'read'                   => 'edit_posts',
					// You need to be able to publish posts, in order to create blocks.
					'create_posts'           => 'publish_posts',
					'edit_posts'             => 'edit_posts',
					'edit_published_posts'   => 'edit_published_posts',
					'delete_published_posts' => 'delete_published_posts',
					'edit_others_posts'      => 'edit_others_posts',
					'delete_others_posts'    => 'delete_others_posts',
				),
				'map_meta_cap'          => true,
				'supports'              => array(
					'title',
					'editor',
					'revisions',
					'custom-fields',
				),
			)
		);

		register_post_meta(
			'cc_block',
			'properties',
			array(
				'single'       => true,
				'type'         => 'object',
				'show_in_rest' => array(
					'schema' => array(
						'type'              => 'object',
						'patternProperties' => array(
							'^.+$' => array(
								'type'       => 'object',
								'properties' => array(
									'name'              => array( 'type' => 'string' ),
									'type'              => array( 'type' => 'string' ),
									'min'               => array( 'type' => 'string' ),
									'max'               => array( 'type' => 'string' ),
									'step'              => array( 'type' => 'string' ),
									'default'           => array(
										'type' => array( 'string', 'object' ),
										'additionalProperties' => true,
									),
									'placeholder'       => array( 'type' => 'string' ),
									'inlinePlaceholder' => array( 'type' => 'string' ),
									'withUnit'          => array( 'type' => 'boolean' ),
									'responsive'        => array( 'type' => 'boolean' ),
									'settings'          => array(
										'type' => 'object',
										'additionalProperties' => true,
									),
									'options'           => array(
										'type' => 'object',
										'additionalProperties' => true,
									),
									'hideLabel'         => array( 'type' => 'boolean' ),
									'topLabel'          => array( 'type' => 'boolean' ),
									'isDynamic'         => array( 'type' => 'boolean' ),
									'dynamicSource'     => array( 'type' => 'string' ),
									'includeInCSS'      => array( 'type' => 'boolean' ),
									'autoGenerate'      => array( 'type' => 'boolean' ),
								),
							),
						),
					),
				),
			)
		);

		register_post_meta(
			'cc_block',
			'variants',
			array(
				'single'       => true,
				'type'         => 'array',
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'name'        => array( 'type' => 'string' ),
								'id'          => array( 'type' => 'string' ),
								'groupActive' => array( 'type' => 'boolean' ),
							),
						),
					),
				),
			)
		);

		register_post_meta(
			'cc_block',
			'reference',
			array(
				'single'       => true,
				'type'         => 'string',
				'show_in_rest' => array(
					'schema' => array(
						'type' => 'string',
					),
				),
			)
		);

		register_post_meta(
			'cc_block',
			'preview',
			array(
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => array(
					'schema' => array(
						'type' => 'string',
					),
				),
				'sanitize_callback' => array( '\Cwicly\Helpers', 'sanitize_base64_image' ),
			)
		);

		register_post_meta(
			'cc_block',
			'propertyGroups',
			array(
				'single'       => true,
				'type'         => 'object',
				'show_in_rest' => array(
					'schema' => array(
						'type'                 => 'object',
						'additionalProperties' => true,
					),
				),
			)
		);

		register_post_meta(
			'cc_block',
			'variantGroups',
			array(
				'single'       => true,
				'type'         => 'array',
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'name'   => array( 'type' => 'string' ),
								'styles' => array( 'type' => 'array' ),
								'id'     => array( 'type' => 'string' ),
							),
						),
					),
				),
			)
		);

		register_post_meta(
			'cc_block',
			'styleVariations',
			array(
				'single'       => true,
				'type'         => 'array',
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'name'   => array( 'type' => 'string' ),
								'styles' => array( 'type' => 'array' ),
								'id'     => array( 'type' => 'string' ),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Creates the necessary post types when 'init' action is fired.
	 *
	 * @param string $new_status The new status.
	 * @param string $old_status The old status.
	 * @param object $post The post object.
	 */
	public function new_component( $new_status, $old_status, $post ) {
		if ( 'publish' === $new_status && 'publish' !== $old_status && 'cc_block' === $post->post_type ) {

			if ( ! get_post_meta( $post->ID, 'reference', true ) ) {
				$random_string = substr( md5( wp_rand() ), 0, 10 );
				update_post_meta( $post->ID, 'reference', $random_string );
			}
		}
	}

	/**
	 * Creates the necessary globals when 'init' action is fired.
	 */
	public function create_globals() {
		global $active_components;
		$active_components = array();
	}

	/**
	 * Optimisations provided by Cwicly
	 */
	public function optimiser() {
		$option = get_option( 'cwicly_optimise' );
		if ( isset( $option['svgFilter'] ) && 'true' === $option['svgFilter'] ) {
			remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		}
		if ( isset( $option['wordPressGlobalStyles'] ) && 'true' === $option['wordPressGlobalStyles'] ) {
			remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
		}
		if ( isset( $option['wordPressEmojis'] ) && 'true' === $option['wordPressEmojis'] ) {
			self::disable_emojis();
		}
		if ( ! isset( $option['wooStylesheets'] ) || ( isset( $option['wooStylesheets'] ) && 'true' !== $option['wooStylesheets'] ) ) {
			add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
		}
		if ( ! isset( $option['wooScripts'] ) || ( isset( $option['wooScripts'] ) && 'true' !== $option['wooScripts'] ) ) {
			remove_theme_support( 'wc-product-gallery-zoom' );
			remove_theme_support( 'wc-product-gallery-lightbox' );
			remove_theme_support( 'wc-product-gallery-slider' );
		}
		if ( isset( $option['templatePartWrapper'] ) && 'true' === $option['templatePartWrapper'] ) {
			add_filter( 'register_block_type_args', 'cc_template_part_override', 999, 2 );
		}
	}

	/**
	 * Disable the emoji's
	 */
	public static function disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( '\Cwicly\Setup', 'disable_emojis_tinymce' ) );
		add_filter( 'wp_resource_hints', array( '\Cwicly\Setup', 'disable_emojis_remove_dns_prefetch' ), 10, 2 );
	}

	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 *
	 * @param array $plugins Array of TinyMCE plugins.
	 * @return array Difference betwen the two arrays
	 */
	public static function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	}

	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @param array  $urls URLs to print for resource hints.
	 * @param string $relation_type The relation type the URLs are printed for.
	 * @return array Difference betwen the two arrays.
	 */
	public static function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' == $relation_type ) {
			/** This filter is documented in wp-includes/formatting.php */
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

			$urls = array_diff( $urls, array( $emoji_svg_url ) );
		}

		return $urls;
	}

	/**
	 * Generate Notice if renegeration necessary
	 */
	public function admin_notice__regenerate() {
		$option = get_option( 'cwicly_regenerate_html' );

		if ( 'true' === $option ) {
			?>
			<div class="notice notice-warning is-dismissible notice-cwicly" style="display: flex; align-items: center;">
				<p><strong><?php esc_html_e( 'Cwicly HTML/CSS regeneration required.', 'cwicly' ); ?></strong></p>
				<a href="<?php echo esc_url( get_admin_url() ); ?>admin.php?page=cwicly-settings" style="margin-left: 8px;" class="button button-primary"><?php esc_html_e( 'Cwicly Settings', 'cwicly' ); ?></a>
				<a style="margin-left: 8px;" class="button"><?php esc_html_e( 'Dismiss.', 'cwicly' ); ?></a>
			</div>
			<?php
		} elseif ( ! $option ) {
			?>
			<div class="notice notice-warning is-dismissible notice-cwicly" style="display: flex; align-items: center;">
				<p><?php esc_html_e( 'If you are updating from a previous Cwicly installation, please regenerate your Cwicly HTML.', 'cwicly' ); ?></p>
				<a href="<?php echo esc_url( get_admin_url() ); ?>admin.php?page=cwicly-settings" style="margin-left: 8px;" class="button button-primary"><?php esc_html_e( 'Cwicly Settings', 'cwicly' ); ?></a>
				<a style="margin-left: 8px;" class="button"><?php esc_html_e( 'Dismiss.', 'cwicly' ); ?></a>
			</div>
			<?php
		}
	}

	/**
	 * Change script tag for specific files
	 *
	 * @param string $tag    The original script tag.
	 * @param string $handle The script's registered handle.
	 * @param string $src    The script's source URL.
	 */
	public function add_type_attribute( $tag, $handle, $src ) {
		// if not your script, do nothing and return original $tag.
		if ( ! str_contains( $handle, 'CCdyn' ) && ! str_contains( $handle, 'cc-m-' ) ) {
			return $tag;
		}
		// change the script tag by adding type="module" and return it.
		$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
		return $tag;
	}

	/**
	 * Function for `pre_render_block` filter-hook.
	 *
	 * @param string|null   $pre_render   The pre-rendered content.
	 * @param array         $parsed_block The block being rendered.
	 * @param WP_Block|null $parent_block If this is a nested block, a reference to the parent block.
	 *
	 * @return string|null
	 */
	public function pre_renderer( $pre_render, $parsed_block, $parent_block ) {
		if ( isset( $parsed_block['attrs']['dynamicContext'] ) && $parsed_block['attrs']['dynamicContext'] && ( 'previouspost' === $parsed_block['attrs']['dynamicContext'] || 'nextpost' === $parsed_block['attrs']['dynamicContext'] ) ) {
			$taxonomy       = 'category';
			$in_same_term   = false;
			$excluded_terms = '';
			if ( isset( $parsed_block['attrs']['dynamicAdjacentPost']['taxonomy'] ) && $parsed_block['attrs']['dynamicAdjacentPost']['taxonomy'] ) {
				$taxonomy = $parsed_block['attrs']['dynamicAdjacentPost']['taxonomy'];
			}
			if ( isset( $parsed_block['attrs']['dynamicAdjacentPost']['in_same_term'] ) && $parsed_block['attrs']['dynamicAdjacentPost']['in_same_term'] ) {
				$in_same_term = true;
			}
			if ( isset( $parsed_block['attrs']['dynamicAdjacentPost']['excluded_terms'] ) && $parsed_block['attrs']['dynamicAdjacentPost']['excluded_terms'] ) {
				$terms = array();
				foreach ( $parsed_block['attrs']['dynamicAdjacentPost']['excluded_terms'] as $term ) {
					if ( isset( $term['value'] ) ) {
						$terms[] = $term['value'];
					}
				}
				$excluded_terms = $terms;
			}

			global $post;
			if ( 'previouspost' === $parsed_block['attrs']['dynamicContext'] ) {
				$post = get_previous_post( $in_same_term, $excluded_terms, $taxonomy ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			} elseif ( 'nextpost' === $parsed_block['attrs']['dynamicContext'] ) {
				$post = get_next_post( $in_same_term, $excluded_terms, $taxonomy ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
			setup_postdata( $post );
			$final = ( new \WP_Block(
				$parsed_block,
				array(
					'postType' => get_post_type(),
					'postId'   => get_the_ID(),
				)
			) )->render( array( 'dynamic' => true ) );
			wp_reset_postdata();
			return $final;
		}
		return $pre_render;
	}

	/**
	 * Shortcode fixer
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The block.
	 * @param array  $instance      The instance.
	 */
	public function shortcode_renderer( $block_content, $block, $instance ) {
		$final = do_shortcode( wpautop( $block_content ) );
		return $final;
	}

	/**
	 * Replace strings encompassed by "<wpml_curved wpml_value='" and "'></wpml_curved>" with "{" and "}". This is a temporary fix for WPML compatibility, waiting for a fix from WPML.
	 *
	 * @param string $content The content.
	 */
	public function replace_wpml_curved_strings( $content ) {
		$filtered_content = $content;

		if ( \Cwicly\WPML::is_wpml_active() ) {
			// Define the regex pattern to match the strings.
			$pattern = '/&lt;wpml_curved wpml_value\=\\\\\'(.*?)\\\\\'&gt;&lt;\/wpml_curved&gt;/';

			// Use preg_replace_callback to perform the replacement.
			$filtered_content = preg_replace_callback(
				$pattern,
				function ( $matches ) {
					// Get the string between the tags.
					$replacement = isset( $matches[1] ) ? $matches[1] : '';

					// Replace the matched string with curly braces.
					return '{' . $replacement . '}';
				},
				$content
			);

			// Replace all instances of %7B and %7D with { and } respectively.
			$filtered_content = str_replace( '%7B', '{', $filtered_content );
			$filtered_content = str_replace( '%7D', '}', $filtered_content );
		}

		return $filtered_content;
	}

	/**
	 * Modify specific Tab content attribute
	 *
	 * @param array         $block         The block being rendered.
	 * @param WP_Block|null $source_block  The block being rendered.
	 * @param WP_Block|null $parent_block  If this is a nested block, a reference to the parent block.
	 */
	public function render_block_data( $block, $source_block, $parent_block ) {
		if ( 'cwicly/tabcontent' === $block['blockName'] && 'cwicly/tabcontents' === $parent_block->parsed_block['blockName'] ) {
			$inner_blocks = $parent_block->parsed_block['innerBlocks'];
			foreach ( $inner_blocks as $index => $inner_block ) {
				if ( 'cwicly/tabcontent' === $inner_block['blockName'] && $block['attrs']['uniqueID'] === $inner_block['attrs']['uniqueID'] && $index === 0 ) {
					$block['attrs']['tabContentActiveN'] = true;
					break;
				}
			}
		}
		if ( 'cwicly/tab' === $block['blockName'] && 'cwicly/tablist' === $parent_block->parsed_block['blockName'] ) {
			$inner_blocks = $parent_block->parsed_block['innerBlocks'];
			foreach ( $inner_blocks as $index => $inner_block ) {
				if ( 'cwicly/tab' === $inner_block['blockName'] && $block['attrs']['uniqueID'] === $inner_block['attrs']['uniqueID'] && $index === 0 ) {
					$block['attrs']['tabContentActiveN'] = true;
					break;
				}
			}
		}
		return $block;
	}

	/**
	 * Function for `wp_default_styles` action-hook.
	 *
	 * @param WP_Styles $wp_styles WP_Styles instance (passed by reference).
	 *
	 * @return void
	 */
	public function remove_relative_position( $wp_styles ) {
		$wp_styles->remove( array( 'wp-block-editor-content' ) );

		$wp_styles->add(
			'wp-block-editor-content',
			CWICLY_DIR_URL . 'core/assets/css/content.css',
			array( 'wp-components' )
		);
	}
}
