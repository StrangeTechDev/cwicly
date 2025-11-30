<?php
/**
 * WPML Compatibility
 *
 * @package Cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WPML Compatibility Class
 */
class WPML {

	/**
	 * Check if WPML is active
	 */
	public static function is_wpml_active() {
		if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the original post ID for a translated post
	 */
	public static function get_original_post_id() {
		if ( self::is_wpml_active() ) {
			$post_id   = get_the_ID();
			$post_type = get_post_type( $post_id );

			$my_default_lang  = apply_filters( 'wpml_default_language', null );
			$original_post_id = apply_filters( 'wpml_object_id', $post_id, $post_type, true, $my_default_lang );

			return $original_post_id;
		}
		return false;
	}
}
