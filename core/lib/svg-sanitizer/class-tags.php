<?php
/**
 * Allowed tags for SVG sanitizer.
 *
 * @package cwicly
 */

namespace Cwicly\Svg;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Allowed tags for SVG sanitizer.
 */
class Tags extends \enshrined\svgSanitize\data\AllowedTags {
	/**
	 * Get allowed tags.
	 *
	 * @return array
	 */
	public static function getTags() {
		return apply_filters( 'cwicly/svg/allowed_tags', parent::getTags() );
	}
}
