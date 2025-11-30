<?php
/**
 * Signature.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * Signature helpers.
 */
class Signature {

	/**
	 * Generate Cwicly salt.
	 *
	 * @return string
	 */
	public static function generate_salt() {
		$salt = wp_generate_password( 32, false );
		update_option( 'cwicly_salt', $salt );
		return $salt;
	}

	/**
	 * Get Cwicly salt.
	 *
	 * @return string
	 */
	public static function get_salt() {
		$salt = get_option( 'cwicly_salt' );
		if ( ! $salt ) {
			$salt = self::generate_salt();
		}
		return $salt;
	}

	/**
	 * Get Cwicly signature.
	 *
	 * @param string $name Name of the data.
	 * @param string $data Data to sign.
	 * @return string
	 */
	public static function get_signature( $name, $data ) {
		$salt = self::get_salt();
		$data = $name . wp_unslash( $data );
		return hash_hmac( 'sha256', $data, $salt );
	}

	/**
	 * Verify Cwicly signature.
	 *
	 * @param string $name Name of the data.
	 * @param string $data Data to sign.
	 * @param string $signature Signature to verify.
	 * @return bool
	 */
	public static function verify_signature( $name, $data, $signature ) {
		$salt = self::get_salt();
		$data = $name . wp_unslash( $data );
		$hash = hash_hmac( 'sha256', $data, $salt );
		return hash_equals( $signature, $hash );
	}
}
