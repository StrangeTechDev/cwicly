<?php
/**
 * License checker.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * License cheker.
 *
 * @package Cwicly
 */
class License {

	/**
	 * License constructor.
	 */
	public function __construct() {
		$this->init();
		// We don't need the license checker for now.
		// phpcs:disable
		// add_action( 'admin_init', array( $this, 'license_checker' ) );
		// phpcs:enable
	}

	/**
	 * Init
	 */
	public function init() {
		// Removed the notice for now, as it's not needed.
		// phpcs:disable
		// if ( ! defined( 'CC_LICENSE_KEY' ) || ! defined( 'CC_LICENSE_EMAIL' ) ) {
		// add_action( 'admin_notices', array( $this, 'no_license_key_found' ) );
		// }
		// phpcs:enable
	}

	/**
	 * Check license validity
	 */
	public static function license_checker() {
		if ( defined( 'CC_LICENSE_KEY' ) && defined( 'CC_LICENSE_EMAIL' ) ) {
			$option = get_option( 'cwicly_license_check' );

			$error                    = get_transient( 'cwicly_plugin_license_message' );
			$plugin_license_transient = get_transient( 'cwicly_license_constant' );
			$email_license_transient  = get_transient( 'cwicly_license_email_constant' );

			if ( ! $email_license_transient ) {
				set_transient( 'cwicly_license_email_constant', CC_LICENSE_EMAIL, 30 * DAY_IN_SECONDS );
			}
			if ( ! $plugin_license_transient ) {
				set_transient( 'cwicly_license_constant', CC_LICENSE_KEY, 30 * DAY_IN_SECONDS );
			}

			$plugin_transient = false;
			if ( ( $plugin_license_transient && CC_LICENSE_KEY !== $plugin_license_transient ) || ( ! $plugin_license_transient && CC_LICENSE_KEY ) ) {
				$plugin_transient = true;
				set_transient( 'cwicly_license_constant', CC_LICENSE_KEY, 30 * DAY_IN_SECONDS );
				$error = false;
			}
			$email_transient = false;
			if ( ( $email_license_transient && CC_LICENSE_EMAIL !== $email_license_transient ) || ( ! $email_license_transient && CC_LICENSE_EMAIL ) ) {
				$email_transient = true;
				set_transient( 'cwicly_license_email_constant', CC_LICENSE_EMAIL, 30 * DAY_IN_SECONDS );
				$error = false;
			}

			if ( ! $option && ! $error ) {
				delete_transient( 'cwicly_plugin_license_message' );
				self::the_lc_check();
			} elseif ( ! $error ) {
				$decode = json_decode( $option, true );
				if ( isset( $decode['date'] ) && $decode['date'] && isset( $decode['server'] ) && $decode['server'] ) {
					$some_date = new \DateTime( $decode['date'] );
					$now       = new \DateTime();

					if ( Helpers::get_server_address() !== $decode['server'] || $some_date->diff( $now )->days > 2 || $plugin_transient || $email_transient ) {
						delete_transient( 'cwicly_plugin_license_message' );
						self::the_lc_check();
					}
				} else {
					delete_transient( 'cwicly_plugin_license_message' );
					self::the_lc_check();
				}
			}
		}
	}

	/**
	 * No license key found notice
	 */
	public static function no_license_key_found() {
		?>
	<div class="error notice">
		<p>
		<?php
		esc_html_e( 'Cwicly license keys not found or incomplete - please contact ', 'cwicly' );
		?>
		<a href="mailto:support@cwicly.com">support@cwicly.com</a>
		</p>
		<p>
		<?php
		esc_html_e( 'Want to enter them manually? Please check the', 'cwicly' );
		?>
		<a href="https://docs.cwicly.com/settings/license">
		<?php
		esc_html_e( 'documentation', 'cwicly' );
		?>
		</a>.
		</p>
	</div>
		<?php
	}

	/**
	 * The license check
	 */
	public static function the_lc_check() {
		if ( defined( 'CC_LICENSE_KEY' ) && defined( 'CC_LICENSE_EMAIL' ) ) {

			$new_server = false;

			$protocols = array( 'http://', 'http://www.', 'www.', 'https://', 'https://www.' );
			$url       = str_replace( $protocols, '', home_url() );

			$verify_ssl_option = get_option( 'cwicly_ssl_verify' );
			if ( 'true' === $verify_ssl_option ) {
				$verify_ssl = true;
			} else {
				$verify_ssl = false;
			}

			$request = wp_remote_get(
				'https://cwicly.com/wp-json/cwicly/v1/cwicly_licenser?license=' . CC_LICENSE_KEY . '&url=' . $url . '&email=' . rawurlencode( CC_LICENSE_EMAIL ) . '',
				array(
					'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8) AppleWebKit/535.6.2 (KHTML, like Gecko) Version/5.2 Safari/535.6.2',
					'sslverify'  => $verify_ssl,
				)
			);

			if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) || ! json_decode( $request['body'] ) ) {
				Helpers::write_log( 'CWICLY LICENSING: Something went wrong in the Cwicly license check, trying on the new activation server.' );
				$request    = self::license_new_server();
				$new_server = true;
			}

			if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
				Helpers::write_log( 'CWICLY LICENSING: Something went wrong in the Cwicly license check, most likely a connection issue.' );
				update_option( 'cwicly_plugin_license_key_status', 'error' );
				set_transient( 'cwicly_plugin_license_message', 'error', 5 * MINUTE_IN_SECONDS );
				return array( 'success' => false );
			} else {
				if ( ! $new_server ) {
                    // phpcs:ignore -- this is a fill in for the old activation server
					// Helpers::write_log('CWICLY LICENSING: Old activation is working, no need to switch to the new one.'); // phpcs:ignore
					$final = json_decode( $request['body'] );
				} else {
					$final = json_decode( json_decode( $request['body'] ) );
				}
				$status = 'unknown';
				if ( isset( $final->state->success ) && $final->state->success ) {
					$status = 'valid';
				} elseif ( isset( $final->state->error ) && $final->state->error ) {
					$status = $final->state->error;
					set_transient( 'cwicly_plugin_license_message', 'error', 5 * MINUTE_IN_SECONDS );
				} else {
					set_transient( 'cwicly_plugin_license_message', 'unknown', 5 * MINUTE_IN_SECONDS );
				}
				update_option( 'cwicly_plugin_license_key_status', $status );
				if ( 'valid' === $status ) {
					$final->server = Helpers::get_server_address();
					$finaler       = wp_json_encode( $final );
					update_option( 'cwicly_license_check', $finaler );
				} elseif ( 'expired' === $status ) {
					$final->server = Helpers::get_server_address();
					$finaler       = wp_json_encode( $final );
					update_option( 'cwicly_license_check', $finaler );
				} else {
					delete_option( 'cwicly_license_check' );
				}
				return array( 'success' => true );
			}
		} else {
			return array( 'success' => false );
		}
	}

	/**
	 * Check the license through the new server
	 */
	public static function license_new_server() {
		$protocols = array( 'http://', 'http://www.', 'www.', 'https://', 'https://www.' );
		$url       = str_replace( $protocols, '', home_url() );

		$verify_ssl_option = get_option( 'cwicly_ssl_verify' );
		if ( 'true' === $verify_ssl_option ) {
			$verify_ssl = true;
		} else {
			$verify_ssl = false;
		}

		$final_url = '' . CC_LICENSE_KEY . '&url=' . $url . '&email=' . rawurlencode( CC_LICENSE_EMAIL ) . '';
		$request   = wp_remote_post(
			'https://license.cwicly.com/wp-json/cwicly/v1/cwicly_licenser',
			array(
				'timeout' => 15,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'url'       => $final_url,
						'sslverify' => $verify_ssl,
					)
				),
			)
		);

		return $request;
	}
}
