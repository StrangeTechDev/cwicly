<?php
/**
 * Polylang Compatibility
 *
 * @package Cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Polylang Compatibility Class
 */
class Polylang {

	/**
	 * Check if Polylang is active
	 */
	public static function is_polylang_active() {
		if ( function_exists( 'pll_current_language' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the original post ID for a translated post
	 */
	public static function get_translated_post_id() {
		if ( self::is_polylang_active() ) {
			$post_id      = get_the_ID();
			$current_lang = pll_current_language();

			if ( $current_lang == pll_default_language() ) {
				return $post_id;
			}

			$new_post_id = pll_get_post( $post_id, $current_lang );

			if ( $new_post_id ) {
				return $new_post_id;
			}

			return $post_id;
		}
		return false;
	}
}
