<?php
/**
 * Comments Main
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Comments Class.
 */
class Comments {

	/**
	 * Comments Tree.
	 *
	 * @param array $comments Comments.
	 */
	public static function query_comment_tree( $comments ) {
		$final = array();
		foreach ( $comments as $c ) {
			if ( ! $c->comment_parent ) {
				$final[ $c->comment_ID ]             = $c;
				$final[ $c->comment_ID ]->childrener = $c->get_children();
			}
		}
		foreach ( $comments as $c ) {
			if ( ! empty( $final[ $c->comment_ID ]->childrener ) ) {
				self::query_comment_tree_recur( $final[ $c->comment_ID ]->childrener, $final[ $c->comment_ID ]->childrener );
			}
		}

		return $final;
	}

	/**
	 * Comments Tree Recur.
	 *
	 * @param array $comments Comments.
	 * @param array $final Final.
	 */
	public static function query_comment_tree_recur( $comments, $final ) {
		foreach ( $comments as $c ) {
			$final[ $c->comment_ID ]->childrener = $c->get_children();
		}
		foreach ( $comments as $c ) {
			if ( ! empty( $final[ $c->comment_ID ]->childrener ) ) {
				self::query_comment_tree_recur( $final[ $c->comment_ID ]->childrener, $final[ $c->comment_ID ]->childrener );
			}
		}
	}

	/**
	 * Comments List.
	 *
	 * @param object $comment Comments.
	 * @param array  $args Args.
	 * @param int    $depth Depth.
	 */
	public static function comment_list( $comment, $args, $depth ) {
		if ( '1' === $comment->comment_approved || 'hold' === $args['status'] || 'all' === $args['status'] || ( '0' === $comment->comment_approved && isset( wp_get_current_commenter()['comment_author_email'] ) && wp_get_current_commenter()['comment_author_email'] === $comment->comment_author_email ) ) {
			$id            = 'comment-' . $comment->comment_ID . '';
			$div           = '';
			$close_div     = '';
			$comment_class = comment_class( array( "cc-query{$args['mason']}" ), $comment, '', false );
			$block_content = '';
			foreach ( $args['innerBlocks'] as $inner_block ) {
				$block_content .= ( new \WP_Block(
					$inner_block,
					array(
						'postType'         => get_post_type(),
						'postId'           => get_the_ID(),
						'queryTotal'       => $args['context']['queryTotal'],
						'queryCount'       => $args['context']['queryCount'],
						'queryPage'        => $args['context']['queryPage'],
						'queryCurrentPage' => $args['context']['queryCurrentPage'],
						'queryPostPerPage' => $args['context']['queryPostPerPage'],
						'queryId'          => $args['queryId'],
						'commentQuery'     => $comment,
						'rendered'         => true,
					)
				) )->render( array( 'dynamic' => true ) );
			}
			$last_query = '';
			echo "\n$div<div id=\"$id\" $comment_class$last_query>$block_content</div>$close_div\n";
		}
	}
}
