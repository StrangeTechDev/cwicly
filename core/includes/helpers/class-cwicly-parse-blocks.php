<?php
/**
 * Render and parse blocks
 *
 * @package Cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class CCBlockRenderParse
 */
class Cwicly_Parse_Blocks {

	/**
	 *  Get the content of a post and parse it's blocks
	 *
	 * @param \WP_Post|int $post    Post object or ID.
	 * @param callable     $callback Callback function to run on each block.
	 *
	 * @return string
	 */
	public static function get_new_content( $post, $callback ) {
		$content = $post->post_content;

		if ( has_blocks( $post->post_content ) ) {
			$blocks        = parse_blocks( $post->post_content );
			$parsed_blocks = self::parse_blocks( $blocks, $callback );

			$content = serialize_blocks( $parsed_blocks );
		}

		return $content;
	}

	/**
	 * Get the content of a post and parse it's blocks
	 *
	 * @param \WP_Block_Parser_Block[]|array[] $blocks  Array of block objects or arrays.
	 * @param callable                         $callback Callback function to run on each block.
	 *
	 * @return \WP_Block_Parser_Block[]
	 */
	protected static function parse_blocks( $blocks, $callback ): array {
		$all_blocks = array();

		foreach ( $blocks as $block ) {
			// Go into inner blocks and run this method recursively.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = self::parse_blocks( $block['innerBlocks'], $callback );
			}

			// Make sure that is a valid block (some block names may be NULL).
			if ( ! empty( $block['blockName'] ) ) {
				$all_blocks[] = $callback( $block ); // the magic is here...
				continue;
			}

			// Continuously create back the blocks array.
			$all_blocks[] = $block;
		}

		return $all_blocks;
	}
}
