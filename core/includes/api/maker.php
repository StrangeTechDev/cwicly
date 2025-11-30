<?php
/**
 * API maker.
 *
 * @package cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once CWICLY_DIR_PATH . 'core/includes/api/class-heartbeat-api.php';
require_once CWICLY_DIR_PATH . 'core/includes/api/class-query-api.php';
require_once CWICLY_DIR_PATH . 'core/includes/api/class-entities-api.php';
require_once CWICLY_DIR_PATH . 'core/includes/api/class-backend-api.php';
require_once CWICLY_DIR_PATH . 'core/includes/api/class-themer-api.php';
require_once CWICLY_DIR_PATH . 'core/includes/api/class-admin-api.php';
require_once CWICLY_DIR_PATH . 'core/includes/api/class-editor-api.php';
require_once CWICLY_DIR_PATH . 'core/includes/api/class-main-query-api.php';
require_once CWICLY_DIR_PATH . 'core/includes/api/class-frontend-api.php';

if ( CC_WOOCOMMERCE ) {
	require_once CWICLY_DIR_PATH . 'core/includes/api/class-woocommerce-api.php';
}

/**
 * This allows access to the class instance from other places.
 */
function cc_api_starter() {
	static $backend;
	static $hearbeat;
	static $query;
	static $entities;
	static $themer;
	static $admin;
	static $editor;
	static $main_query;
	static $frontend;

	static $woocommerce;

	if ( ! $backend ) {
		$backend = new \Cwicly\Backend_API();
	}
	if ( ! $hearbeat ) {
		$hearbeat = new \Cwicly\Heartbeat_API();
	}

	if ( ! $query ) {
		$query = new \Cwicly\Query_API();
	}

	if ( ! $entities ) {
		$entities = new \Cwicly\Entities_API();
	}

	if ( ! $themer ) {
		$themer = new \Cwicly\Themer_API();
	}

	if ( ! $admin ) {
		$admin = new \Cwicly\Admin_API();
	}

	if ( ! $editor ) {
		$editor = new \Cwicly\Editor_API();
	}

	if ( ! $main_query ) {
		$main_query = new \Cwicly\Main_Query_API();
	}

	if ( ! $frontend ) {
		$frontend = new \Cwicly\Frontend_API();
	}

	if ( CC_WOOCOMMERCE ) {
		if ( ! $woocommerce ) {
			$woocommerce = new \Cwicly\WooCommerce_API();
		}
	}
}

/**
 * Init only when needed
 */
function cc_init_api_starter() {
	cc_api_starter();
}
add_action( 'rest_api_init', 'cc_init_api_starter' );
