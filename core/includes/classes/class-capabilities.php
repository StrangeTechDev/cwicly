<?php
/**
 * Capabilities.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.
/**
 * Capabilities helpers.
 */
class Capabilities {

	/**
	 * Get the capabilities of the user.
	 *
	 * @param string $type      Type of capability.
	 * @param string $condition Condition of capability.
	 * @param bool   $force     Force the capability.
	 *
	 * @return bool
	 */
	public static function permission( $type, $condition, $force = false ) {
		if ( is_admin() && ! $force ) {
			return;
		}
		$user_roles   = Helpers::get_current_user_roles();
		$role_editor  = get_option( 'cwicly_role_editor' );
		$current_user = get_current_user_id();

		$final = false;

		if ( $user_roles && is_array( $user_roles ) && count( $user_roles ) > 0 && $role_editor && is_array( $role_editor ) && count( $role_editor ) > 0 ) {
			if ( isset( $current_user ) && isset( $role_editor[ 'user_' . $current_user ] ) ) {
				if ( get_post_type() && isset( $role_editor[ 'user_' . $current_user ]['postTypes']['hideList'] ) && in_array( get_post_type(), $role_editor[ 'user_' . $current_user ]['postTypes']['hideList'], true ) ) {
					return false;
				} else {
					if ( isset( $role_editor[ 'user_' . $current_user ][ $type ][ $condition ] ) ) {
						$final = $role_editor[ 'user_' . $current_user ][ $type ][ $condition ];
					}
					return $final;
				}
			} elseif ( isset( $role_editor ) ) {
				if ( get_post_type() && isset( $user_roles[0] ) && isset( $role_editor[ $user_roles[0] ]['postTypes']['hideList'] ) && in_array( get_post_type(), $role_editor[ $user_roles[0] ]['postTypes']['hideList'], true ) ) {
					return false;
				} else {
					$user_roles = wp_get_current_user()->roles;
					foreach ( $role_editor as $role => $value ) {
						if ( array_intersect( $user_roles, array( $role ) ) ) {
							if ( isset( $value[ $type ][ $condition ] ) ) {
								$final = $value[ $type ][ $condition ];
							}
						}
					}
					return $final;
				}
			}
		}
	}

	/**
	 * Check to see if the user can save PHP to the database.
	 */
	public static function code_block_php() {
		if ( ! self::execute_eval() ) {
			return false;
		}

		$capability = self::permission( 'codeBlock', 'php', true );

		return apply_filters( 'cwicly/code/php', $capability );
	}


	/**
	 * Check to see if the user can save JS to the database.
	 */
	public static function code_block_js() {
		$capability = self::permission( 'codeBlock', 'js', true );

		return apply_filters( 'cwicly/code/js', $capability );
	}

	/**
	 * Check to see if eval is allowed.
	 */
	public static function execute_eval() {
		return apply_filters( 'cwicly/code/execute_eval', true );
	}
}
