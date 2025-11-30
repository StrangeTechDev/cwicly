<?php
/**
 * Skeleton Maker
 *
 * @package cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Skeleton Generator for Frontend Rendering.
 */
class Cwicly_Skeleton {

	/**
	 * Skeleton Generator.
	 *
	 * @param object $block Block data.
	 * @param bool   $with_animation Skeleton with animation.
	 * @param bool   $is_block Skeleton is block.
	 * @param bool   $newq Skeleton is new query.
	 *
	 * @return string
	 */
	public function cc_skeleton_block( $block, $with_animation = false, $is_block = false, $newq = false ) {
		$final = '';
		if ( $newq ) {
			if ( isset( $block->parsed_block['innerBlocks'] ) ) {
				foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
					$final .= self::maker_prep_v2( $inner_block, $with_animation );
				}
			}
		} elseif ( $is_block ) {
			$final .= self::maker_prep( $block, $with_animation );
		} else {
			foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
				$final .= self::maker_prep( $inner_block, $with_animation );
			}
		}

		return $final;
	}

	/**
	 * Skeleton Preparation
	 *
	 * @param object $inner_block Block data.
	 * @param bool   $with_animation Skeleton with animation.
	 *
	 * @return string
	 */
	private function maker_prep( $inner_block, $with_animation ) {
		if ( isset( $inner_block['blockName'] ) && str_contains( $inner_block['blockName'], 'cwicly' ) ) {
			$inner_inner_blocks = '';
			if ( isset( $inner_block['innerBlocks'] ) && $inner_block['innerBlocks'] ) {
				$inner_inner_blocks .= $this->template_maker( $inner_block, $with_animation );
			}

			$attributers = ( new WP_Block(
				$inner_block
			) )->attributes;

			if ( ( isset( $attributers['skeletonActive'] ) && $attributers['skeletonActive'] ) || ( 'cwicly/query-template' === $inner_block['blockName'] ) ) {
				$open      = \Cwicly\Helpers::tag_maker( $attributers, '', true );
				$close_tag = \Cwicly\Helpers::tag_maker( $attributers, '' );

				if ( false === $with_animation ) {
					$extra_class = ' cc-no-anim';
				} else {
					$extra_class = '';
				}

				$count = 1;
				if ( 'cwicly/list' === $inner_block['blockName'] ) {
					if ( isset( $attributers['content'] ) && $attributers['content'] ) {
						$count = substr_count( $attributers['content'], '<li>' );
					}
				}

				if ( 'cwicly/heading' === $inner_block['blockName'] || 'cwicly/paragraph' === $inner_block['blockName'] || 'cwicly/button' === $inner_block['blockName'] || 'cwicly/input' === $inner_block['blockName'] ) {
					return '<' . $open . ' class="' . $attributers['classID'] . ' cc-loading-skeleton-top"><span aria-live="polite" aria-busy="true"><span class="cc-loading-skeleton' . $extra_class . '">‌</span></span>' . $close_tag . '';
				} elseif ( 'cwicly/image' === $inner_block['blockName'] ) {
					return '<' . $open . ' class="' . $attributers['classID'] . ' cc-loading-skeleton-top"><span aria-live="polite" aria-busy="true"><span class="cc-loading-skeleton' . $extra_class . '">‌</span></span>' . $close_tag . '';
				} elseif ( 'cwicly/icon' === $inner_block['blockName'] ) {
					return '<' . $open . ' class="' . $attributers['classID'] . ' cc-loading-skeleton-top"><span aria-live="polite" aria-busy="true"><svg class="cc-loading-skeleton' . $extra_class . '" style="border-radius: 50%;">‌</svg></span>' . $close_tag . '';
				} elseif ( 'cwicly/list' === $inner_block['blockName'] ) {
					$return = '';
					for ( $i = 0; $i < $count; $i++ ) {
						$return .= '<' . $open . ' class="' . $attributers['classID'] . '"><span aria-live="polite" aria-busy="true"><span class="cc-loading-skeleton' . $extra_class . '">‌</span></span>' . $close_tag . '';
					}

					return $return;
				} elseif ( 'cwicly/query-template' === $inner_block['blockName'] ) {
					$posts_per_page = get_option( 'posts_per_page' );
					$value          = '';
					if ( isset( $inner_block->context['queryPerPage'] ) && $inner_block->context['queryPerPage'] && isset( $inner_block->context['queryPerPage']['source'] ) && 'static' === $inner_block->context['queryPerPage']['source'] && isset( $inner_block->context['queryPerPage']['field'] ) && $inner_block->context['queryPerPage']['field'] ) {
						$posts_per_page = intval( $inner_block->context['queryPerPage']['field'] );
					}
					for ( $i = 0; $i < $posts_per_page; $i++ ) {
						$value .= '<div>' . $inner_inner_blocks . '</div>';
					}

					return '<' . $open . ' class="' . $attributers['classID'] . '">' . $value . $close_tag . '';
				} elseif ( ! $inner_inner_blocks ) {
					return '<' . $open . ' class="' . $attributers['classID'] . ' cc-loading-skeleton-top"><span aria-live="polite" aria-busy="true"><span class="cc-loading-skeleton' . $extra_class . '">‌</span></span>' . $close_tag . '';
				} else {
					return '<' . $open . ' class="' . $attributers['classID'] . '">' . $inner_inner_blocks . $close_tag . '';
				}
			}
		}
	}

	/**
	 * Skeleton Preparation v2
	 *
	 * @param object $inner_block Block data.
	 * @param bool   $with_animation Skeleton with animation.
	 *
	 * @return string
	 */
	private function maker_prep_v2( $inner_block, $with_animation ) {
		if ( isset( $inner_block['blockName'] ) && str_contains( $inner_block['blockName'], 'cwicly' ) ) {
			$inner_inner_blocks = '';
			if ( isset( $inner_block['innerBlocks'] ) && $inner_block['innerBlocks'] ) {
				$inner_inner_blocks .= $this->template_maker_v2( $inner_block, $with_animation );
			}

			$attributers = ( new WP_Block(
				$inner_block
			) )->attributes;

			$open      = \Cwicly\Helpers::tag_maker( $attributers, '', true );
			$close_tag = \Cwicly\Helpers::tag_maker( $attributers, '' );

			$count = 1;
			if ( 'cwicly/list' === $inner_block['blockName'] ) {
				if ( isset( $attributers['content'] ) && $attributers['content'] ) {
					$count = substr_count( $attributers['content'], '<li>' );
				}
			}

			if ( 'cwicly/heading' === $inner_block['blockName'] || 'cwicly/paragraph' === $inner_block['blockName'] || 'cwicly/button' === $inner_block['blockName'] || 'cwicly/input' === $inner_block['blockName'] ) {
				return '<' . $open . ' class="' . $attributers['classID'] . '">‌' . $close_tag . '';
			} elseif ( 'cwicly/image' === $inner_block['blockName'] ) {
				return '<' . $open . ' class="' . $attributers['classID'] . '">‌' . $close_tag . '';
			} elseif ( 'cwicly/icon' === $inner_block['blockName'] ) {
				return '<' . $open . ' class="' . $attributers['classID'] . '">‌' . $close_tag . '';
			} elseif ( 'cwicly/list' === $inner_block['blockName'] ) {
				$return = '';
				for ( $i = 0; $i < $count; $i++ ) {
					$return .= '<' . $open . ' class="' . $attributers['classID'] . '">‌' . $close_tag . '';
				}

				return $return;
			} else {
				return '<' . $open . ' class="' . $attributers['classID'] . '">' . $inner_inner_blocks . $close_tag . '';
			}
		}
	}

	/**
	 * Template Preparation
	 *
	 * @param object $block Block data.
	 * @param bool   $with_animation Skeleton with animation.
	 *
	 * @return string
	 */
	private function template_maker( $block, $with_animation ) {
		$inner_blocks = '';
		foreach ( $block['innerBlocks'] as $inner_block ) {
			$inner_blocks .= $this->maker_prep( $inner_block, $with_animation );
		}

		return $inner_blocks;
	}

	/**
	 * Skeleton Preparation v2
	 *
	 * @param object $block Block data.
	 * @param bool   $with_animation Skeleton with animation.
	 *
	 * @return string
	 */
	private function template_maker_v2( $block, $with_animation ) {
		$inner_blocks = '';
		foreach ( $block['innerBlocks'] as $inner_block ) {
			$inner_blocks .= $this->maker_prep_v2( $inner_block, $with_animation );
		}

		return $inner_blocks;
	}
}
