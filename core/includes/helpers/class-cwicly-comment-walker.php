<?php
/**
 * Comments Walker
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Comments Walker.
 */
class Cwicly_Comment_Walker extends \Walker_Comment {

	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param string $comment Comment.
	 * @param int    $depth  Depth of comment.
	 * @param array  $args   An array of arguments.
	 * @param int    $id     Comment ID.
	 */
	public function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) {
		++$depth;
		$GLOBALS['comment_depth'] = $depth; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		if ( ! empty( $args['callback'] ) ) {
			if ( 1 === $depth ) {
				switch ( $args['style'] ) {
					case 'div':
						$output .= '<div class="cc-query-item">' . "\n";

						break;
				}
			}
			ob_start();
			call_user_func( $args['callback'], $comment, $args, $depth );
			$output .= ob_get_clean();

			return;
		}
	}

	/**
	 * Starts the level
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of comment.
	 * @param array  $args   An array of arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		switch ( $args['style'] ) {
			case 'div':
				$output .= '<div class="cc-query-child">' . "\n";

				break;
		}
	}

	/**
	 * Ends the level
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of comment.
	 * @param array  $args   An array of arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		switch ( $args['style'] ) {
			case 'div':
				$output .= "</div>\n";

				break;
		}
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param object $data_object  Comment data object.
	 * @param int    $depth  Depth of comment.
	 * @param array  $args   An array of arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = array() ) {
		++$depth;
		if ( 1 === $depth ) {
			if ( $output && 'div' === $args['style'] ) {
				$output .= "</div>\n";
			}
		}
	}

	/**
	 * Outputs a pingback comment.
	 *
	 * @param string $comment Comment.
	 * @param int    $depth  Depth of comment.
	 * @param array  $args   An array of arguments.
	 */
	protected function comment( $comment, $depth, $args ) {
	}
}
