<?php
/**
 * Main maker.
 *
 * @package cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once CWICLY_DIR_PATH . 'core/includes/classes/class-options.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-setup.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-settings.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-signature.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-woocommerce.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-backend.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-themer.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-frontend.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-helpers.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-capabilities.php';
//require_once CWICLY_DIR_PATH . 'core/includes/classes/class-license.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-actions.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-acf.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-query.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-comments.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-wpml.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-polylang.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-svg.php';
require_once CWICLY_DIR_PATH . 'core/includes/classes/class-init.php';
