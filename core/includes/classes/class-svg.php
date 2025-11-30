<?php
/**
 * SVG processing.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * SVG enabling, sanitizing and permissions.
 */
class Svg {
	const SVG_MIME_TYPE = 'image/svg+xml';

	/**
	 * The single instance of the class.
	 */
	public function __construct() {
		add_action( 'upload_mimes', array( $this, 'enable' ) );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'pixel_fix' ), 10, 4 );
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'sanitize' ) );
	}

	/**
	 * Enable SVG upload.
	 *
	 * @param array $mimes Mime types.
	 * @return array
	 */
	public function enable( $mimes ) {
		if ( Capabilities::permission( 'miscellaneous', 'svgUploads', true ) ) {
			$mimes['svg']  = self::SVG_MIME_TYPE;
			$mimes['svgz'] = self::SVG_MIME_TYPE;
		}
		return $mimes;
	}

	/**
	 * Fix SVG pixel.
	 *
	 * @param array  $image Image.
	 * @param int    $attachment_id Attachment ID.
	 * @param string $size Size.
	 * @param bool   $icon Icon.
	 * @return array
	 */
	public function pixel_fix( $image, $attachment_id, $size, $icon ) {
		if ( self::SVG_MIME_TYPE === get_post_mime_type( $attachment_id ) ) {
			$image[1] = false;
			$image[2] = false;
		}
		return $image;
	}

	/**
	 * Sanitize SVG upload.
	 *
	 * @param array $file File.
	 * @return array
	 */
	public function sanitize( $file ) {

		if ( empty( $file['type'] ) || self::SVG_MIME_TYPE !== $file['type'] ) {
			return $file;
		}

		$disabled = apply_filters( 'cwicly/svg/sanitize', false );

		if ( $disabled ) {
			return $file;
		}

		if ( ! class_exists( '\enshrined\svgSanitize\Sanitizer' ) ) {
			require_once CWICLY_DIR_PATH . 'core/lib/svg-sanitizer/library/vendor/autoload.php';
		}
		if ( ! class_exists( '\Cwicly\Svg\Tags' ) ) {
			require_once CWICLY_DIR_PATH . 'core/lib/svg-sanitizer/class-tags.php';
		}
		if ( ! class_exists( '\Cwicly\Svg\Attributes' ) ) {
			require_once CWICLY_DIR_PATH . 'core/lib/svg-sanitizer/class-attributes.php';
		}

		$sanitizer = new \enshrined\svgSanitize\Sanitizer();
		$sanitizer->minify( true );

		$svg = file_get_contents( $file['tmp_name'] );

		// Allowed attributes and tags.
		$sanitizer->setAllowedTags( new Svg\Tags() );
		$sanitizer->setAllowedAttrs( new Svg\Attributes() );

		$svg = $sanitizer->sanitize( $svg );

		if ( ! $svg ) {
			$file['error'] = __( 'Unable to read SVG file.', 'cwicly' );
		}

		file_put_contents( $file['tmp_name'], $svg );

		return $file;
	}

	public static function sanitize_inline( $svg ) {
		if ( ! class_exists( '\enshrined\svgSanitize\Sanitizer' ) ) {
			require_once CWICLY_DIR_PATH . 'core/lib/svg-sanitizer/library/vendor/autoload.php';
		}
		if ( ! class_exists( '\Cwicly\Svg\Tags' ) ) {
			require_once CWICLY_DIR_PATH . 'core/lib/svg-sanitizer/class-tags.php';
		}
		if ( ! class_exists( '\Cwicly\Svg\Attributes' ) ) {
			require_once CWICLY_DIR_PATH . 'core/lib/svg-sanitizer/class-attributes.php';
		}

		$sanitizer = new \enshrined\svgSanitize\Sanitizer();
		$sanitizer->minify( true );
		$sanitizer->removeXMLTag( true );

		// Allowed attributes and tags.
		$sanitizer->setAllowedTags( new Svg\Tags() );
		$sanitizer->setAllowedAttrs( new Svg\Attributes() );

		$svg = $sanitizer->sanitize( $svg );

		if ( ! $svg ) {
			return '';
		}

		return $svg;
	}
}
