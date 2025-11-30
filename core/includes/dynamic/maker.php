<?php
/**
 * Core Maker
 *
 * @package  Cwicly/Blocks
 * @category Core
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once CWICLY_DIR_PATH . 'core/includes/dynamic/cc-conditions.php';
require_once CWICLY_DIR_PATH . 'core/includes/dynamic/cc-content.php';
require_once CWICLY_DIR_PATH . 'core/includes/dynamic/cc-menu.php';
require_once CWICLY_DIR_PATH . 'core/includes/dynamic/cc-repeater.php';
require_once CWICLY_DIR_PATH . 'core/includes/dynamic/cc-swatch.php';
require_once CWICLY_DIR_PATH . 'core/includes/dynamic/cc-taxonomy.php';
require_once CWICLY_DIR_PATH . 'core/includes/dynamic/cc-video.php';
require_once CWICLY_DIR_PATH . 'core/includes/dynamic/cc-slider.php';

// HELPERS.
require_once CWICLY_DIR_PATH . 'core/includes/dynamic/cc-helpers.php';
// HELPERS.

// RENDER.
require_once CWICLY_DIR_PATH . 'core/includes/dynamic/render.php';
// RENDER.
