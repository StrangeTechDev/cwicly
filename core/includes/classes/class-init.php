<?php
/**
 * Main init.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * Main init.
 *
 * @package cwicly
 */
class Init {

	/**
	 * Init constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * The machine instance
	 *
	 * @var null
	 */
	public static $instance = null;

	/**
	 * Init machine :)
	 */
	public function init() {
		new Options();
		new Setup();
		new License();
		new Settings();
		if ( CC_WOOCOMMERCE ) {
			new WooCommerce();
		}
		new Svg();
		new Actions();
		if ( is_admin() ) {
			new Backend();
		}
		new Frontend();
		new Themer();
	}

	/**
	 * Machine turn on. Only one instance of the class is allowed.
	 */
	public static function machine() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Init ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Init::machine();
