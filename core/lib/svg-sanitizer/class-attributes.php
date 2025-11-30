<?php
/**
 * Allowed attributes for SVG sanitizer.
 *
 * @package cwicly
 */

namespace Cwicly\Svg;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Allowed attributes for SVG sanitizer.
 */
class Attributes extends \enshrined\svgSanitize\data\AllowedAttributes {
	/**
	 * Get allowed attributes.
	 *
	 * @return array
	 */
	public static function getAttributes() {
		return apply_filters( 'cwicly/svg/allowed_attributes', parent::getAttributes() );
	}
}
