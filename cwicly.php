<?php
/**
 * Plugin Name:       Cwicly
 * Plugin URI:        https://cwicly.com/
 * Description:       Take Gutenberg by WordPress to the next level. Design & create professional responsive websites in minutes.
 * Version:           1.4.5
 * Author:            Cwicly
 * Author URI:        https://cwicly.com/
 * Text Domain:       cwicly
 * Requires at least: 6.1
 * Tested up to:      6.6.2
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package cwicly
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Version.
define( 'CWICLY_VERSION', '1.4.5' );

// Define WordPress.
define( 'WORDPRESS_VERSION', get_bloginfo( 'version' ) );

// Define Theme Version.
define( 'CWICLY_THEME_VERSION', '1.0.3' );

// Define Beta.
define( 'CWICLY_BETA', false );

// Define Directory URL.
define( 'CWICLY_DIR_URL', plugin_dir_url( __FILE__ ) );

// Define Physical Path.
define( 'CWICLY_DIR_PATH', plugin_dir_path( __FILE__ ) );

// Define Physical URL Path.
define( 'CWICLY_URL', plugin_dir_url( __FILE__ ) . '' );

define( 'CWICLY_FILE', __FILE__ );

//define( 'CWICLY_LICENSE_PAGE', 'cwicly' );

define( 'CWICLY_ITEM_NAME', 'Cwicly' );

define( 'CC_STORE_URL', 'https://cwicly.com' );

define( 'CC_PLUGIN_ID', 73 );

define( 'CC_THEME_ID', 71 );

define( 'CC_CLASSES', get_option( 'cwicly_classes_add' ) );

define( 'CC_UPLOAD_URL', cc_fix_ssl_upload_url() );

define( 'CWICLY_API_VERSION', '1' );


/**
 * Fix SSL Upload URL.
 *
 * @return string
 */
function cc_fix_ssl_upload_url() {
	$url = wp_upload_dir()['baseurl'];
	if ( is_ssl() ) {
		$url = str_replace( 'http://', 'https://', $url );
	}
	return $url;
}

// Test to see if WooCommerce is active (including network activated).
$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';

if (
	in_array( $plugin_path, wp_get_active_and_valid_plugins(), true )
) {
	define( 'CC_WOOCOMMERCE', true );
} else {
	define( 'CC_WOOCOMMERCE', false );
}

/**
 * Set Script Translations.
 */
function cwicly_set_script_translations() {
	load_plugin_textdomain( 'cwicly', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	wp_register_script( 'cwicly_editor_blocks', CWICLY_DIR_URL . 'build/index.js', array( 'lodash', 'wp-i18n', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-edit-post', 'wp-editor', 'wp-api', 'wp-data', 'wp-block-editor', 'wp-core-data' ), CWICLY_VERSION, true );
	wp_set_script_translations( 'cwicly_editor_blocks', 'cwicly', plugin_dir_path( __FILE__ ) . 'languages' );
}
add_action( 'init', 'cwicly_set_script_translations' );

// Define path and URL to the ACF plugin.
define( 'MY_ACF_PATH', plugin_dir_path( __FILE__ ) . 'core/includes/acf/' );
define( 'MY_ACF_URL', plugin_dir_url( __FILE__ ) . 'core/includes/acf/' );

// Include Notices File.
require_once CWICLY_DIR_PATH . 'core/includes/helpers/class-cwicly-initial.php'; // Initial Cwicly Data.

// Version Check & Include Core.
if ( ! version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	add_action( 'admin_notices', array( 'Cwicly_Initial', 'php_error_notice' ) ); // PHP Version Check.
} elseif ( ! version_compare( get_bloginfo( 'version' ), '5.6', '>=' ) ) {
	add_action( 'admin_notices', array( 'Cwicly_Initial', 'wordpress_error_notice' ) ); // WordPress Version Check.
} else {
	require_once CWICLY_DIR_PATH . 'core/includes/blocks/maker.php'; // Load Cwicly Blocks.
	// if ( file_exists( CWICLY_DIR_PATH . 'license-data.php' ) ) {
	// 	require_once CWICLY_DIR_PATH . 'license-data.php'; // Load Cwicly License DATA.
	// }
	require_once CWICLY_DIR_PATH . 'core/includes/helpers/theme-maker.php'; // Load Cwicly Themer.
	require_once CWICLY_DIR_PATH . 'core/includes/api/maker.php'; // Load Cwicly API.
	require_once CWICLY_DIR_PATH . 'core/includes/dynamic/maker.php'; // Load Cwicly Blocks.
	require_once CWICLY_DIR_PATH . 'core/includes/helpers/maker.php'; // Load Cwicly Helpers Maker.
	require_once CWICLY_DIR_PATH . 'core/includes/classes/maker.php'; // Load Cwicly Classes.
}

/**
 * Version check.
 */
function cc_update_db_check() {
	if ( is_admin() ) {
		if ( get_option( 'cwicly_db_version' ) ) {
			$deprecated = get_option( 'cwicly_deprecated' );
			if ( empty( $deprecated ) && ! is_array( $deprecated ) ) {
				$deprecated                     = array();
				$deprecated['oldSectionLayout'] = 'true';
				$deprecated['oldButton']        = 'true';
				update_option( 'cwicly_deprecated', $deprecated );
			}
		} else {
			$deprecated = get_option( 'cwicly_deprecated' );
			if ( empty( $deprecated ) && ! is_array( $deprecated ) ) {
				update_option( 'cwicly_deprecated', array() );
			}
		}
		if ( get_option( 'cwicly_db_version' ) !== CWICLY_VERSION ) {
			$regenerate_html = get_option( 'cwicly_regenerate_html' );

            // phpcs:disable
			// if ($regenerate_html !== 'true') {
			// update_option('cwicly_regenerate_html', 'true');
			// $regenerateHTML = 'true';
			// }
            // phpcs:enable

			if ( version_compare( get_option( 'cwicly_db_version' ), '1.4.1', '<' ) && 'true' !== $regenerate_html ) {
				update_option( 'cwicly_regenerate_html', 'true' );
			}

			update_option( 'cwicly_db_version', CWICLY_VERSION );
		}
	}
}
add_action( 'plugins_loaded', 'cc_update_db_check' );
