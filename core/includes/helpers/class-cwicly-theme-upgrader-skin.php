<?php
/**
 * Theme Skin.
 *
 * @package cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

/**
 * Theme Installer Skin
 */
class Cwicly_Theme_Upgrader_Skin extends Theme_Installer_Skin {

	/**
	 * Header
	 *
	 * @return void
	 */
	public function header() {
	}

	/**
	 * Footer
	 *
	 * @return void
	 */
	public function footer() {
	}

	/**
	 * Error
	 *
	 * @param array $errors Array of errors.
	 *
	 * @return void
	 */
	public function error( $errors ) {
	}

	/**
	 * Feedback
	 *
	 * @param string $string Feedback string.
	 * @param array  ...$args Array of args.
	 *
	 * @return void
	 */
	public function feedback( $string, ...$args ) {
	}
}
