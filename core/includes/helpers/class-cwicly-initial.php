<?php
/**
 * Cwicly Initial Start.
 *
 * @package cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Cwicly_Initial' ) ) {
	/**
	 * Class Cwicly_Initial.
	 */
	class Cwicly_Initial {
		/**
		 * Instance.
		 *
		 * @var null
		 */
		protected static $instance = null;

		/**
		 * Cwicly_Initial constructor.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * PHP Version Check.
		 */
		public static function php_error_notice() {
			// translators: %s: PHP version.
			$message      = sprintf( esc_html__( 'Careful! Cwicly requires PHP version %s or higher. You will likely not be able to use the website builder on this install.', 'cwicly' ), '5.4' );
			$html_message = sprintf( '<div class="notice notice-error is-dismissible">%s</div>', wpautop( $message ) );
			echo wp_kses_post( $html_message );
		}

		/**
		 * WordPress Error Notice.
		 */
		public static function wordpress_error_notice() {
			// translators: %s: WordPress version.
			$message      = sprintf( esc_html__( 'Careful! Cwicly requires WordPress version %s or more. The blocks will inevitably lead to an error.', 'cwicly' ), '5.5' );
			$html_message = sprintf( '<div class="notice notice-error is-dismissible">%s</div>', wpautop( $message ) );
			echo wp_kses_post( $html_message );
		}

		/**
		 * Theme Error Notice.
		 */
		public static function theme_error_notice() {
			$message      = sprintf( esc_html__( 'Careful! You have activated the Cwicly plugin but not the Cwicly theme. We recommend installing and activating both the Cwicly theme and the Cwicly plugin.', 'cwicly' ) );
			$html_message = sprintf( '<div class="notice notice-error is-dismissible">%s</div>', wpautop( $message ) );
			echo wp_kses_post( $html_message );
		}
	}
}
