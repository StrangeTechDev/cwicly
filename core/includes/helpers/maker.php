<?php
/**
 * Main maker.
 *
 * @package cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once CWICLY_DIR_PATH . 'core/includes/helpers/template-part.php';
require_once CWICLY_DIR_PATH . 'core/includes/helpers/ground.php';
require_once CWICLY_DIR_PATH . 'core/includes/helpers/class-cwicly-theme-upgrader-skin.php';
require_once CWICLY_DIR_PATH . 'core/includes/helpers/class-cwicly-parse-blocks.php';
require_once CWICLY_DIR_PATH . 'core/includes/helpers/class-block-template.php';
require_once CWICLY_DIR_PATH . 'core/includes/helpers/class-cwicly-query-args.php';
require_once CWICLY_DIR_PATH . 'core/includes/helpers/class-cwicly-skeleton.php';
require_once CWICLY_DIR_PATH . 'core/includes/helpers/class-cwicly-comment-walker.php';

if ( CC_WOOCOMMERCE ) {
	require_once CWICLY_DIR_PATH . 'core/includes/helpers/class-cwicly-extend-woocommerce-store.php';
}

// if ( ! class_exists( 'Cwicly_Plugin_Updater' ) ) {
// 	require_once CWICLY_DIR_PATH . 'core/includes/helpers/class-cwicly-plugin-updater.php';
// }
