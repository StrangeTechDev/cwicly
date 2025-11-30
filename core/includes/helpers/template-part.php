<?php
/**
 * Template part block rendering functions.
 *
 * @package Cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Modifies the callback for the core/template-part block.
 *
 * @param array  $args  Array of arguments.
 * @param string $name The block name.
 */
function cc_template_part_override( $args, $name ) {
	if ( 'core/template-part' === $name ) {
		$args['render_callback'] = 'cc_modify_template_part_render';
	}
	return $args;
}

/**
 * Modifies the template part block render callback to remove the template part wrapper
 *
 * @param array  $attributes The block attributes.
 * @param string $content    The block content.
 */
function cc_modify_template_part_render( $attributes, $content ) {
	static $seen_ids = array();

	$template_part_id = null;
	$content          = null;
	$area             = WP_TEMPLATE_PART_AREA_UNCATEGORIZED;
	$theme            = isset( $attributes['theme'] ) ? $attributes['theme'] : get_stylesheet();

	if ( isset( $attributes['slug'] ) && get_stylesheet() === $theme ) {
		$template_part_id    = $theme . '//' . $attributes['slug'];
		$template_part_query = new WP_Query(
			array(
				'post_type'           => 'wp_template_part',
				'post_status'         => 'publish',
				'post_name__in'       => array( $attributes['slug'] ),
				'tax_query'           => array(
					array(
						'taxonomy' => 'wp_theme',
						'field'    => 'name',
						'terms'    => $theme,
					),
				),
				'posts_per_page'      => 1,
				'no_found_rows'       => true,
				'lazy_load_term_meta' => false, // Do not lazy load term meta, as template parts only have one term.
			)
		);
		$template_part_post  = $template_part_query->have_posts() ? $template_part_query->next_post() : null;
		if ( $template_part_post ) {
			// A published post might already exist if this template part was customized elsewhere
			// or if it's part of a customized template.
			$content    = $template_part_post->post_content;
			$area_terms = get_the_terms( $template_part_post, 'wp_template_part_area' );
			if ( ! is_wp_error( $area_terms ) && false !== $area_terms ) {
				$area = $area_terms[0]->name;
			}
			/**
			 * Fires when a block template part is loaded from a template post stored in the database.
			 *
			 * @since 5.9.0
			 *
			 * @param string  $template_part_id   The requested template part namespaced to the theme.
			 * @param array   $attributes         The block attributes.
			 * @param WP_Post $template_part_post The template part post object.
			 * @param string  $content            The template part content.
			 */
			do_action( 'render_block_core_template_part_post', $template_part_id, $attributes, $template_part_post, $content );
		} else {
			$template_part_file_path = '';
			// Else, if the template part was provided by the active theme,
			// render the corresponding file content.
			if ( 0 === validate_file( $attributes['slug'] ) ) {
				$block_template = get_block_file_template( $template_part_id, 'wp_template_part' );

				$content = $block_template->content;
				if ( isset( $block_template->area ) ) {
					$area = $block_template->area;
				}
			}

			if ( '' !== $content && null !== $content ) {
				/**
				 * Fires when a block template part is loaded from a template part in the theme.
				 *
				 * @since 5.9.0
				 *
				 * @param string $template_part_id        The requested template part namespaced to the theme.
				 * @param array  $attributes              The block attributes.
				 * @param string $template_part_file_path Absolute path to the template path.
				 * @param string $content                 The template part content.
				 */
				do_action( 'render_block_core_template_part_file', $template_part_id, $attributes, $template_part_file_path, $content );
			} else {
				/**
				 * Fires when a requested block template part does not exist in the database nor in the theme.
				 *
				 * @since 5.9.0
				 *
				 * @param string $template_part_id        The requested template part namespaced to the theme.
				 * @param array  $attributes              The block attributes.
				 * @param string $template_part_file_path Absolute path to the not found template path.
				 */
				do_action( 'render_block_core_template_part_none', $template_part_id, $attributes, $template_part_file_path );
			}
		}
	}

	// WP_DEBUG_DISPLAY must only be honored when WP_DEBUG. This precedent
	// is set in `wp_debug_mode()`.
	$is_debug = WP_DEBUG && WP_DEBUG_DISPLAY;

	if ( is_null( $content ) && $is_debug ) {
		if ( ! isset( $attributes['slug'] ) ) {
			// If there is no slug this is a placeholder and we dont want to return any message.
			return;
		}
		return sprintf(
			/* translators: %s: Template part slug. */
			__( 'Template part has been deleted or is unavailable: %s' ),
			$attributes['slug']
		);
	}

	if ( isset( $seen_ids[ $template_part_id ] ) ) {
		return $is_debug ?
			// translators: Visible only in the front end, this warning takes the place of a faulty block.
			__( '[block rendering halted]' ) :
			'';
	}

	// Look up area definition.
	$area_definition = null;
	$defined_areas   = get_allowed_block_template_part_areas();
	foreach ( $defined_areas as $defined_area ) {
		if ( $defined_area['area'] === $area ) {
			$area_definition = $defined_area;
			break;
		}
	}

	// If $area is not allowed, set it back to the uncategorized default.
	if ( ! $area_definition ) {
		$area = WP_TEMPLATE_PART_AREA_UNCATEGORIZED;
	}

	// Run through the actions that are typically taken on the_content.
	$content                       = shortcode_unautop( $content );
	$content                       = do_shortcode( $content );
	$seen_ids[ $template_part_id ] = true;
	$content                       = do_blocks( $content );
	unset( $seen_ids[ $template_part_id ] );
	$content = wptexturize( $content );
	$content = convert_smilies( $content );
	$content = wp_filter_content_tags( $content, "template_part_{$area}" );

	// Handle embeds for block template parts.
	global $wp_embed;
	$content = $wp_embed->autoembed( $content );

	return str_replace( ']]>', ']]&gt;', $content );
}
