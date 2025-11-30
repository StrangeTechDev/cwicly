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
 * Cwicly Themer API.
 */
class Themer_API extends \WP_REST_Controller {
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

		$base = 'render';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'theme_render' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);
	}

	/**
	 * Prepare template query to override plugin filters.
	 *
	 * @param string $template_type Template type.
	 */
	public function cc_get_block_templates( $template_type = 'wp_template' ) {
		$wp_query_args = array(
			'post_status'         => array( 'auto-draft', 'draft', 'publish' ),
			'post_type'           => 'wp_template',
			'posts_per_page'      => -1,
			'no_found_rows'       => true,
			'lazy_load_term_meta' => false,
			'suppress_filters'    => true,
			'tax_query'           => array(
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => get_stylesheet(),
				),
			),
		);

		$template_query = new \WP_Query( $wp_query_args );
		$query_result   = array();
		foreach ( $template_query->posts as $post ) {
			$template = _build_block_template_result_from_post( $post );

			if ( is_wp_error( $template ) ) {
				continue;
			}

			$query_result[] = $template;
		}

		$block_templates = $query_result;

		return $block_templates;
	}

	/**
	 * Get all templates for rendering on the Themer page.
	 */
	public function theme_render() {
		try {

			$final     = new \stdClass();
			$all_posts = new \stdClass();
			$head      = new \stdClass();

			$block_templates = self::cc_get_block_templates();

			foreach ( $block_templates as $template ) {
				$template_html = '';
				$blocks        = parse_blocks( $template->content );
				if ( empty( $blocks ) ) {
					$final->{$template->slug}     = $template_html;
					$all_posts->{$template->slug} = $template;
				} else {
					foreach ( $blocks as $block ) {
						$render_block = true;
						if ( isset( $block['blockName'] ) && strpos( $block['blockName'], 'woocommerce/' ) !== false ) {
							$render_block = false;
						}
						if ( isset( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) ) {
							foreach ( $block['innerBlocks'] as $inner_block ) {
								if ( strpos( $inner_block['blockName'], 'woocommerce/' ) !== false ) {
									$render_block = false;
								}
							}
						}
						if ( $render_block ) {
							$template_html .= apply_filters( 'the_content', render_block( $block ) );
						}
						$final->{$template->slug}     = $template_html;
						$all_posts->{$template->slug} = $template;

						$head->{$template->slug} = self::style_head( $template->theme, $template->slug );
					}
				}
			}

			// get all template parts using wp_template_part post type.
			$template_parts = get_posts(
				array(
					'post_type'        => 'wp_template_part',
					'posts_per_page'   => -1,
					'suppress_filters' => true,
				)
			);

			$all_template_parts = new \stdClass();
			$parts_html         = new \stdClass();

			foreach ( $template_parts as $template_part ) {
				$template_html = '';
				$blocks        = parse_blocks( $template_part->post_content );
				if ( empty( $blocks ) ) {
					$parts_html->{$template_part->post_name}         = $template_html;
					$all_template_parts->{$template_part->post_name} = $template_part;
				} else {
					foreach ( $blocks as $block ) {
						$render_block = true;
						if ( isset( $block['blockName'] ) && $block['blockName'] && strpos( $block['blockName'], 'woocommerce/' ) !== false ) {
							$render_block = false;
						}
						if ( isset( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) ) {
							foreach ( $block['innerBlocks'] as $inner_block ) {
								if ( strpos( $inner_block['blockName'], 'woocommerce/' ) !== false ) {
									$render_block = false;
								}
							}
						}
						if ( $render_block ) {
							$template_html .= apply_filters( 'the_content', render_block( $block ) );
						}
						$parts_html->{$template_part->post_name}         = $template_html;
						$all_template_parts->{$template_part->post_name} = $template_part;

						$theme                             = get_option( 'stylesheet' );
						$head->{$template_part->post_name} = self::style_head( $theme, $template_part->post_name, 'tp' );
					}
				}
			}

			/**
			 * Use output buffering to convert a function that echoes
			 * to a return string instead
			 */
			function cc_get_head() {
				ob_start();
				wp_head();
				return ob_get_clean();
			}

			return array(
				'success'          => true,
				'message'          => $final,
				'allPosts'         => $all_posts,
				'allTemplateParts' => $all_template_parts,
				'parts'            => $parts_html,
				'head'             => cc_get_head(),
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Get all templates for rendering on the Themer page.
	 *
	 * @param string $theme Theme name.
	 * @param string $slug Template slug.
	 * @param string $type Template type.
	 */
	public static function style_head( $theme, $slug, $type = 'tp' ) {
		ob_start();
		Themer::add_template_styles( $theme, $slug, $type );
		return ob_get_clean();
	}
}
