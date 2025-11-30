<?php
/**
 * Cwicly Content
 *
 * Functions for creating and managing the Content
 *
 * @package Cwicly\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Content Maker
 *
 * Create correct Content so that it processes like normal post_content
 *
 * @package Cwicly\Functions
 * @version 1.1
 *
 * @param object $block Block object.
 */
function cc_content_maker( $block ) {
	if ( ! is_admin() && ! \Cwicly\Helpers::is_rest() ) {
		static $seen_ids = array();
		$page_id         = get_queried_object_id();

		if ( ! isset( $block->context['postId'] ) ) {
			return '';
		}

		if ( isset( $block->context['query_index'] ) && $block->context['postId'] === $page_id ) {
			return '';
		}

		$id = get_the_ID();

		add_action(
			'wp_enqueue_scripts',
			function () use ( $block, $page_id, $id ) {
				if ( isset( $block->context['query_index'] ) && $block->context['postId'] !== $page_id && ! is_admin() && $id && file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $id . '.css' ) ) {
					wp_enqueue_style( 'cc-post-' . $id . '', CC_UPLOAD_URL . '/cwicly/css/cc-post-' . $id . '.css', array(), filemtime( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $id . '.css' ) );
				}
			}
		);

		$post_id = $block->context['postId'];

		if ( isset( $seen_ids[ $post_id ] ) ) {
			$is_debug = defined( 'WP_DEBUG' ) && WP_DEBUG &&
			defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
			return $is_debug ?
			// translators: Visible only in the front end, this warning takes the place of a faulty block.
			__( '[block rendering halted]' ) :
			'';
		}

		$seen_ids[ $post_id ] = true;

		if ( ! in_the_loop() && have_posts() ) {
			the_post();
		}

		// When inside the main loop, we want to use queried object
		// so that `the_preview` for the current post can apply.
		// We force this behavior by omitting the third argument (post ID) from the `get_the_content`.
		$content = get_the_content();

		/** This filter is documented in wp-includes/post-template.php */
		$content = apply_filters( 'the_content', str_replace( ']]>', ']]&gt;', $content ) );
		unset( $seen_ids[ $post_id ] );

		if ( empty( $content ) ) {
			return '';
		} else {
			return $content;
		}
	}
}
