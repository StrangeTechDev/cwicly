<?php
/**
 * Cwicly Slider
 *
 * Functions for creating and managing the sliders.
 *
 * @package Cwicly
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Slider
 *
 * @param array  $attributes The block attributes.
 * @param object $block The block object.
 * @return string
 */
function cc_slider_maker( $attributes, $block ) {
	global $product;
	if ( CC_WOOCOMMERCE && isset( $product ) && $product ) {
		$content        = '';
		$original       = array();
		$full_url       = array();
		$medium_url     = array();
		$thumbnail_url  = array();
		$main_image     = array();
		$main_image[]   = $product->get_image_id();
		$gallery_images = $product->get_gallery_image_ids();
		$attachment_ids = array_merge( $main_image, $gallery_images );
		foreach ( $attachment_ids as $images ) {
			$original[]       = wp_get_attachment_url( $images );
			$originalsrcset[] = wp_get_attachment_image_srcset( $images );
			$full_url[]       = wp_get_attachment_image_src( $images, 'full' )[0];
			$medium_url[]     = wp_get_attachment_image_src( $images, 'medium' )[0];
			$thumbnail_url[]  = wp_get_attachment_image_src( $images, 'thumbnail' )[0];
		}

		foreach ( $original as $key => $value ) {
			if ( $block->parsed_block['innerBlocks'][0] ) {
				$block_content = ( new WP_Block(
					$block->parsed_block['innerBlocks'][0],
					array(
						'postType'        => get_post_type(),
						'postId'          => get_the_ID(),
						'woocommerce'     => array( $value, $originalsrcset[ $key ] ),
						'woocommerce_row' => $key + 1,
					)
				) )->render( array( 'dynamic' => true ) );
				$content      .= "<div class='swiper-slide cc-slider'>$block_content</div>";
			}
		}
		return $content;
	}
}
