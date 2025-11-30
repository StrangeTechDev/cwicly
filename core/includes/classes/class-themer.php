<?php
/**
 * Main themer.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * Cwicly Theme Builder
 */
class Themer {

	/**
	 * List of filters
	 *
	 * @var array $filters List of filters
	 */
	private static $filters = array();

	/**
	 * Themer constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'add_global_fragments' ) );
		add_filter( 'get_block_templates', array( $this, 'defaults_admin_namer' ), 10, 3 );
		add_filter( 'render_block', array( $this, 'render_block' ), 10, 3 );
		remove_filter( 'wp_footer', 'the_block_template_skip_link' );
		add_action( 'wp_footer', array( $this, 'skip_link' ) );

		self::$filters = array();
	}

	/**
	 * Add Cwicly stylesheets to the head
	 *
	 * @param string $theme Theme name.
	 * @param string $slug Slug name.
	 * @param string $type Type name.
	 */
	public static function add_template_styles( $theme, $slug, $type ) {
		if ( ! $theme && ! $slug && ! $type ) {
			return;
		}
		$depencencies = array( 'CC', 'CCnorm', 'cc-global' );
		if ( ! is_admin() && file_exists( wp_upload_dir()['basedir'] . '/cwicly/cc-global-stylesheets.css' ) ) {
			$depencencies[] = 'cc-global-stylesheets';
		}
		if ( ! is_admin() && file_exists( wp_upload_dir()['basedir'] . '/cwicly/cc-global-classes.css' ) ) {
			$depencencies[] = 'cc-global-classes';
		}

		if ( 'tp' === $type ) {
			if ( isset( $theme ) && $theme && isset( $slug ) && $slug ) {
				if ( ! is_admin() && ! Helpers::is_rest() && ! in_array( $theme . '//' . $slug, self::$filters, true ) ) {
					self::$filters[] = $theme . '//' . $slug;
					do_action( 'cwicly/themer/templates', $theme, $slug );
				}

				if ( ! is_admin() && file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . $theme . '_' . $slug . '.css' ) ) {
					wp_enqueue_style( 'cc-' . $theme . '_' . $slug . '', CC_UPLOAD_URL . '/cwicly/css/cc-tp-' . $theme . '_' . $slug . '.css', $depencencies, filemtime( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . $theme . '_' . $slug . '.css' ) );
				}
			}
		} elseif ( 'rb' === $type && isset( $slug ) && $slug ) {
			if ( ! is_admin() && file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-rb-' . $slug . '.css' ) ) {
				wp_enqueue_style( 'cc-' . $slug . '', CC_UPLOAD_URL . '/cwicly/css/cc-rb-' . $slug . '.css', $depencencies, filemtime( wp_upload_dir()['basedir'] . '/cwicly/css/cc-rb-' . $slug . '.css' ) );
			}
		} elseif ( 'cm' === $type && isset( $slug ) && $slug ) {
			if ( ! is_admin() && file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-cm-' . $slug . '.css' ) ) {
				wp_enqueue_style( 'cc-' . $slug . '', CC_UPLOAD_URL . '/cwicly/css/cc-cm-' . $slug . '.css', $depencencies, filemtime( wp_upload_dir()['basedir'] . '/cwicly/css/cc-cm-' . $slug . '.css' ) );
			}
		} elseif ( 'post' === $type && isset( $slug ) && $slug ) {
			if ( ! is_admin() && file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $slug . '.css' ) ) {
				wp_enqueue_style( 'cc-' . $slug . '', CC_UPLOAD_URL . '/cwicly/css/cc-post-' . $slug . '.css', $depencencies, filemtime( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $slug . '.css' ) );
			}
		}
	}

	/**
	 * Retrieve all template parts for a given fragment
	 *
	 * @param string $fragment Fragment name.
	 * @return array
	 */
	public static function get_fragment( $fragment ) {
		if ( is_admin() ) {
			return;
		}
		$option = get_option( 'cwicly_global_parts' );
		$final  = '';
		if ( isset( $option['fragments'][ $fragment ]['conditions'] ) && $option['fragments'][ $fragment ]['conditions'] ) {
			$final = cc_condition_checker( $option['fragments'][ $fragment ]['conditions'] );
		}
		return $final;
	}

	/**
	 * Retrieve and add all global fragments
	 */
	public static function add_global_fragments() {
		if ( is_admin() ) {
			return;
		}
		$header = self::get_fragment( 'globalheader' );
		if ( isset( $header ) && is_array( $header ) ) {
			$header_content = '';
			foreach ( $header as $templates ) {
				foreach ( $templates as $template ) {
					$block = do_blocks( $template->content );

					if ( $template->theme && $template->slug ) {
						self::add_template_styles( $template->theme, $template->slug, 'tp' );
						self::namer( $template->title, $template->slug, 'wp_template_part' );
					}
					$header_content .= $block;
				}
			}
			add_action(
				'wp_body_open',
				function () use ( $header_content ) {
					echo $header_content; // phpcs:ignore WordPress.Security.EscapeOutput
				}
			);
		}

		$footer = self::get_fragment( 'globalfooter' );
		if ( isset( $footer ) && is_array( $footer ) ) {
			$footer_content = '';
			foreach ( $footer as $templates ) {
				foreach ( $templates as $template ) {
					$block = do_blocks( $template->content );

					if ( $template->theme && $template->slug ) {
						self::add_template_styles( $template->theme, $template->slug, 'tp' );
						self::namer( $template->theme, $template->slug, 'wp_template_part' );
					}
					$footer_content .= $block;
				}
			}

			add_action(
				'wp_footer',
				function () use ( $footer_content ) {
					echo $footer_content; // phpcs:ignore WordPress.Security.EscapeOutput
				}
			);
		}
	}

	/**
	 * Check template parts and add styles to head
	 *
	 * @param string   $block_content The block content about to be appended.
	 * @param array    $block         The full block, including name and attributes.
	 * @param WP_Block $instance      The block instance.
	 *
	 * @return string
	 */
	public function render_block( $block_content, $block, $instance ) {
		if ( is_admin() ) {
			return $block_content;
		}

		if ( isset( $block['blockName'] ) && str_contains( $block['blockName'], 'cwicly/' ) ) {
			if ( isset( $block['attrs']['fontLocation'] ) && 'custom' === $block['attrs']['fontLocation'] && isset( $block['attrs']['fontFamily'] ) && $block['attrs']['fontFamily'] ) {
				$this->local_font_maker( $block['attrs']['fontFamily'] );
			}
			if ( isset( $block['attrs']['fontFamilyExtras'] ) && isset( $block['attrs']['fontLocationExtras'] ) && is_array( $block['attrs']['fontLocationExtras'] ) ) {
				foreach ( $block['attrs']['fontLocationExtras'] as $key => $font_location_extra ) {
					if ( 'custom' === $font_location_extra && isset( $block['attrs']['fontFamilyExtras'][ $key ] ) ) {
						$this->local_font_maker( $block['attrs']['fontFamilyExtras'][ $key ] );
					}
				}
			}
		}
		if ( isset( $block['blockName'] ) && 'cwicly/gallery' === $block['blockName'] ) {
			$gallery_locations = array(
				'galleryFontTitleFamily',
				'galleryFontDescriptionFamily',
				'galleryFilterFontFamily',
				'galleryFilterActiveFontFamily',
			);
			foreach ( $gallery_locations as $location ) {
				if ( isset( $block['attrs'][ $location ] ) ) {
					$this->local_font_maker( $block['attrs'][ $location ] );
				}
			}
		} elseif ( isset( $block['blockName'] ) && 'cwicly/menu' === $block['blockName'] ) {
			$menu_locations = array(
				'menuMainMenuFontFamily',
				'menuSubMenuFontFamily',
			);
			foreach ( $menu_locations as $location ) {
				if ( isset( $block['attrs'][ $location ] ) ) {
					$this->local_font_maker( $block['attrs'][ $location ] );
				}
			}
		}

		if ( isset( $block['blockName'] ) && 'core/template-part' === $block['blockName'] && isset( $block['attrs']['slug'] ) && isset( $block['attrs']['theme'] ) ) {
			self::add_template_styles( $block['attrs']['theme'], $block['attrs']['slug'], 'tp' );
			$args     = array(
				'name'        => $block['attrs']['slug'],
				'post_type'   => 'wp_template_part',
				'post_status' => 'publish',
				'numberposts' => 1,
			);
			$my_posts = get_posts( $args );
			$name     = '';
			if ( $my_posts ) {
				$name = $my_posts[0]->post_title;
			}
			self::namer( $name, $block['attrs']['slug'], 'wp_template_part' );
		} elseif ( isset( $block['blockName'] ) && 'core/block' === $block['blockName'] && isset( $block['attrs']['ref'] ) ) {
			self::add_template_styles( '', $block['attrs']['ref'], 'rb' );
		} elseif ( isset( $block['blockName'] ) && 'cwicly/component' === $block['blockName'] && isset( $block['attrs']['ref'] ) ) {
			self::add_template_styles( '', $block['attrs']['ref'], 'cm' );
		}
		return $block_content;
	}

	/**
	 * Local font maker
	 *
	 * @param string $font_location The font location.
	 */
	public function local_font_maker( $font_location ) {
		$localfonts       = get_option( 'cwicly_local_fonts' );
		$localactivefonts = get_option( 'cwicly_local_active_fonts' );

		if ( isset( $localactivefonts ) && is_array( $localactivefonts ) && in_array( $font_location, $localactivefonts, true ) ) {
			if ( isset( $localfonts ) && is_array( $localfonts ) && isset( $localfonts[ $font_location ] ) && $localfonts[ $font_location ] ) {
				$css = '';
				if ( isset( $localfonts[ $font_location ]['css'] ) && $localfonts[ $font_location ]['css'] ) {
					$css = $localfonts[ $font_location ]['css'];
				} elseif ( isset( $localfonts[ $font_location ]['originalCSS'] ) && $localfonts[ $font_location ]['originalCSS'] ) {
					$css = $localfonts[ $font_location ]['originalCSS'];
				}

					// Replace {{CC_UPLOAD_URL}} with the actual upload URL.
					$css = str_replace( '{{CC_UPLOAD_URL}}', CC_UPLOAD_URL, $css );

				$font = str_replace( ' ', '-', $localfonts[ $font_location ]['family'] );
				if ( ! wp_style_is( 'cc-cf-' . $font, 'enqueued' ) ) {
					wp_register_style( 'cc-cf-' . $font, false, array(), CWICLY_VERSION );
					wp_enqueue_style( 'cc-cf-' . $font );

					wp_add_inline_style( 'cc-cf-' . $font, $css );
				}
			}
		}
	}

	/**
	 * If default template, add information to the admin bar
	 *
	 * @param WP_Block_Template[] $query_result  Array of found block templates.
	 * @param array               $query         Optional. Arguments to retrieve templates.
	 * @param string              $template_type wp_template or wp_template_part.
	 *
	 * @return WP_Block_Template[]
	 */
	public function defaults_admin_namer( $query_result, $query, $template_type ) {
		if ( is_admin() ) {
			return $query_result;
		}
		if ( 'wp_template' === $template_type ) {
			if ( isset( $query_result[0]->title ) && isset( $query_result[0]->slug ) ) {
				self::namer( $query_result[0]->title, $query_result[0]->slug, 'wp_template' );
				self::add_template_styles( get_stylesheet(), $query_result[0]->slug, 'tp' );
			}
		}
		// filter...
		return $query_result;
	}

	/**
	 * Name templates in admin bar
	 *
	 * @param string $templater template name.
	 * @param string $slug     template slug.
	 * @param string $type    template type.
	 */
	public static function namer( $templater, $slug, $type, $theme = '' ) {
		if ( is_admin() ) {
			return;
		}
		if ( is_user_logged_in() && ! Capabilities::permission( 'miscellaneous', 'hideAdminBarTemplateInfo' ) && ( current_user_can( 'edit_pages' ) ) ) {

			if ( ! $theme ) {
				$theme = get_stylesheet();
			}
			$linktoedit = '';

			if ( version_compare( WORDPRESS_VERSION, '6.1', '>=' ) ) {
				$linktoedit = '' . admin_url( 'site-editor.php?postType=' . $type . '&postId=' . $theme . '%2F%2F' . $slug . '&canvas=edit' ) . '';
			} elseif ( WORDPRESS_VERSION >= 5.9 ) {
				$linktoedit = '' . admin_url( 'site-editor.php?postType=' . $type . '&postId=' . $theme . '%2F%2F' . $slug . '' ) . '';
			} else {
				$linktoedit = '' . admin_url( 'themes.php?page=gutenberg-edit-site&postType=' . $type . '&postId=' . $theme . '%2F%2F' . $slug . '' ) . '';
			}

			if ( 'wp_template' === $type ) {
				$cwicly_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTA2IiBoZWlnaHQ9IjQyNyIgdmlld0JveD0iMCAwIDUwNiA0MjciIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0yMDAuMDA4IDcwQzIxNy4wMTkgNzAgMjMwLjgwOSA4Ni4wNjExIDIzMC44MDkgMTA1Ljg3NUMyMzAuODA5IDEyNS42ODkgMjE3LjAxOSAxNDEuNzUgMjAwLjAwOCAxNDEuNzVIMTY5LjIwNkMxMzUuMTg0IDE0MS43NSAxMDcuNjAzIDE3My44NzQgMTA3LjYwMyAyMTMuNUMxMDcuNjAzIDI1My4xMjYgMTM1LjE4NCAyODUuMjUgMTY5LjIwNiAyODUuMjVIMjAwLjAwOEMyMTcuMDE5IDI4NS4yNSAyMzAuODA5IDMwMS4zMTEgMjMwLjgwOSAzMjEuMTI1QzIzMC44MDkgMzQwLjkzOSAyMTcuMDE5IDM1NyAyMDAuMDA4IDM1N0gxNjkuMjA2QzEwMS4xNjEgMzU3IDQ2IDI5Mi43NTMgNDYgMjEzLjVDNDYgMTM0LjI0NyAxMDEuMTYxIDcwIDE2OS4yMDYgNzBIMjAwLjAwOFoiIGZpbGw9IiNFOEU4RTgiLz4KPHBhdGggZD0iTTMwNS40NiA3MEMyODguNDQ5IDcwIDI3NC42NTkgODYuMDYxMSAyNzQuNjU5IDEwNS44NzVDMjc0LjY1OSAxMjUuNjg5IDI4OC40NDkgMTQxLjc1IDMwNS40NiAxNDEuNzVIMzM2LjI2MkMzNzAuMjg0IDE0MS43NSAzOTcuODY1IDE3My44NzQgMzk3Ljg2NSAyMTMuNUMzOTcuODY1IDI1My4xMjYgMzcwLjI4NCAyODUuMjUgMzM2LjI2MiAyODUuMjVIMzA1LjQ2QzI4OC40NDkgMjg1LjI1IDI3NC42NTkgMzAxLjMxMSAyNzQuNjU5IDMyMS4xMjVDMjc0LjY1OSAzNDAuOTM5IDI4OC40NDkgMzU3IDMwNS40NiAzNTdIMzM2LjI2MkM0MDQuMzA3IDM1NyA0NTkuNDY4IDI5Mi43NTMgNDU5LjQ2OCAyMTMuNUM0NTkuNDY4IDEzNC4yNDcgNDA0LjMwNyA3MCAzMzYuMjYyIDcwSDMwNS40NloiIGZpbGw9IiNFOEU4RTgiLz4KPC9zdmc+Cg==';
				$args        = array(
					'id'    => 'cwiclythemer',
					'title' => '<style>#wpadminbar #wp-admin-bar-cwiclythemer>.ab-item:before {content: "";top: 3px;width: 20px;height: 26px;background-position: center;background-repeat: no-repeat;background-size: 20px; background-image: url(' . $cwicly_icon . ')!important;}</style><span style="--hello: url(' . $cwicly_icon . ');">Template: ' . $templater . '</span>',
					'href'  => $linktoedit,
				);
				Helpers::add_admin_menu_item( $args, '' );
			}

			if ( 'wp_template' === $type ) {
				$template = array(
					'id'     => 'fsetemplate',
					'title'  => 'Edit Template',
					'parent' => 'cwiclythemer',
					'href'   => $linktoedit,
					'meta'   => array(
						'title' => 'Edit ' . $templater . '',
					),
				);
				Helpers::add_admin_menu_item( $template, '', 20 );
			}

			if ( 'wp_template_part' === $type ) {
				$template_parts = array(
					'id'     => 'cctemplatepart-' . $slug . '',
					'title'  => $templater,
					'parent' => 'cctemplateparts',
					'href'   => $linktoedit,
					'meta'   => array(
						'title' => 'Go to Cwicly Template Parts',
					),
				);
				Helpers::add_admin_menu_item( $template_parts );
			}
		}
	}

	/**
	 * Prints the skip-link script & styles.
	 *
	 * @access private
	 * @since 5.8.0
	 *
	 * @global string $_wp_current_template_content
	 *
	 * @return void
	 */
	public static function skip_link() {
		global $_wp_current_template_content;

		// Early exit if not a block theme.
		if ( ! current_theme_supports( 'block-templates' ) ) {
			return;
		}

		// Early exit if not a block template.
		if ( ! $_wp_current_template_content ) {
			return;
		}

		$shortcut = apply_filters( 'cc_skip_link', null );
		if ( ! is_null( $shortcut ) ) {
			return $shortcut;
		}
		?>

		<?php
		/**
		 * Print the skip-link styles.
		 */
		?>
	<style id="skip-link-styles">.skip-link.screen-reader-text{border:0;clip:rect(1px,1px,1px,1px);clip-path:inset(50%);height:1px;margin:-1px;overflow:hidden;padding:0;position:absolute!important;width:1px;word-wrap:normal!important}.skip-link.screen-reader-text:focus{background-color:#eee;clip:auto!important;clip-path:none;color:#444;display:block;font-size:1em;height:auto;left:5px;line-height:normal;padding:15px 23px 14px;text-decoration:none;top:5px;width:auto;z-index:100000}</style>
		<?php
		/**
		 * Print the skip-link script.
		 */
		?>
	<script>
		!function(){var e,t,n,i=document.querySelector("main");i&&(e=document.body&&document.body.firstChild)&&((t=i.id)||(t="wp--skip-link--target",i.id=t),(n=document.createElement("a")).classList.add("skip-link","screen-reader-text"),n.href="#"+t,n.innerHTML='<?php esc_html_e( 'Skip to content' ); ?>',e.parentElement.insertBefore(n,e))}();
	</script>
		<?php
	}
}
