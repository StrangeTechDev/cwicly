<?php
/**
 * Query templater.
 *
 * @package Cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Query templater class.
 */
class Block_Template {

	/**
	 * Is query.
	 *
	 * @var bool
	 */
	protected $is_query = false;

	/**
	 * Make single block.
	 *
	 * @param object $block The block object.
	 * @param bool   $is_query Is query.
	 */
	public function single_block( $block, $is_query ) {
		if ( $is_query ) {
			$this->is_query = true;
		}

		$inner_blocks   = array();
		$inner_blocks[] = self::maker_prep( $block->parsed_block );

		return $inner_blocks;
	}

	/**
	 * Make template.
	 *
	 * @param object $block The block object.
	 * @param bool   $is_query Is query.
	 */
	public function template( $block, $is_query ) {
		if ( $is_query ) {
			$this->is_query = true;
		}
		$inner_blocks = array();

		foreach ( $block->parsed_block['innerBlocks'] as $inner_block ) {
			$inner_blocks[] = self::maker_prep( $inner_block );
		}

		return $inner_blocks;
	}

	/**
	 * Template preparation.
	 *
	 * @param object $inner_block The inner block object.
	 */
	private function maker_prep( $inner_block ) {
		if ( str_contains( $inner_block['blockName'], 'cwicly' ) ) {
			$inner_inner_blocks = array();
			if ( isset( $inner_block['innerBlocks'] ) && $inner_block['innerBlocks'] ) {
				$inner_inner_blocks = $this->template_maker( $inner_block );
			}
			$attributers = ( new \WP_Block(
				$inner_block
			) )->attributes;

			$attrs = array();

			if ( 'cwicly/list' === $inner_block['blockName'] ) {
				if ( cc_attribute_checker( $attributers, 'listIconActive', 'true' ) && $attributers['listIconActive'] ) {
					$attrs['list']['icon'] = $attributers['listIconActive'];
				}
			}
			if ( 'cwicly/icon' === $inner_block['blockName'] ) {
				$attrs['icon'] = $this->icon( $attributers );
			}
			if ( 'cwicly/svg' === $inner_block['blockName'] ) {
				$attrs['svg'] = $this->svg( $attributers );
			}
			if ( 'cwicly/button' === $inner_block['blockName'] ) {
				$attrs['button'] = $this->button( $attributers );
			}
			if ( 'cwicly/image' === $inner_block['blockName'] ) {
				$attrs['image'] = $this->image( $attributers );
			}
			if ( 'cwicly/video' === $inner_block['blockName'] ) {
				$attrs['video'] = $this->video( $attributers );
			}
			if ( 'cwicly/filter' === $inner_block['blockName'] ) {
				$attrs['filter'] = $this->filter( $attributers );
			}
			if ( 'cwicly/rangeslider' === $inner_block['blockName'] ) {
				$attrs['rangeSlider'] = $this->rangeslider( $attributers );
			}
			if ( 'cwicly/input' === $inner_block['blockName'] ) {
				$attrs['input'] = $this->input( $attributers );
			}
			if ( 'cwicly/taxonomyterms' === $inner_block['blockName'] ) {
				$attrs['taxonomyterms'] = $this->taxonomy_terms( $attributers, $inner_block['blockName'] );
			}
			if ( 'cwicly/accordions' === $inner_block['blockName'] ) {
				$attrs['accordions'] = $this->accordions( $attributers );
			}
			if ( 'cwicly/accordion' === $inner_block['blockName'] ) {
				$attrs['accordion'] = $this->accordion( $attributers );
			}
			if ( 'cwicly/tab' === $inner_block['blockName'] ) {
				$attrs['tab'] = $this->tab( $attributers );
			}
			if ( 'cwicly/tablist' === $inner_block['blockName'] ) {
				$attrs['tabList'] = $this->tab_list( $attributers );
			}
			if ( 'cwicly/tabcontent' === $inner_block['blockName'] ) {
				$attrs['tabContent'] = $this->tab_content( $attributers );
			}
			if ( isset( $attributers['repeaterMasonry'] ) && $attributers['repeaterMasonry'] ) {
				$attrs['repeaterMasonry'] = true;
			}
			if ( 'cwicly/popover' === $inner_block['blockName'] ) {
				$attrs['popover'] = $this->popover( $attributers );
			}
			if ( 'cwicly/swatch' === $inner_block['blockName'] ) {
				$attrs['swatch'] = $this->swatch( $attributers );
			}
			if ( 'cwicly/query-pagination' === $inner_block['blockName'] ) {
				if ( isset( $attributers['queryPaginationSiV'] ) && $attributers['queryPaginationSiV'] ) {
					$attrs['siv'] = $attributers['queryPaginationSiV'];
				}
			}

			return array(
				'blockName'         => $inner_block['blockName'],
				'unique'            => substr( uniqid( '', true ), 0, 10 ),
				'id'                => $attributers['id'],
				'forceShowID'       => cc_attribute_checker( $attributers, 'forceShowID', 'true' ) ? true : false,
				'class'             => $attributers['classID'],
				'isStyling'         => cc_attribute_checker( $attributers, 'isStyling', 'true' ) ? true : false,
				'addclasses'        => isset( $attributers['additionalClassesR'] ) && $attributers['additionalClassesR'] ? self::replace_shells( $attributers['additionalClassesR'] ) : '',
				'addclasseswrapper' => isset( $attributers['additionalClassesWrapperR'] ) && $attributers['additionalClassesWrapperR'] ? $attributers['additionalClassesWrapperR'] : '',
				'globalclasses'     => \Cwicly\Helpers::global_classes( $attributers ),
				'tag'               => $this->tagger( $attributers, $inner_block['blockName'] ),
				'content'           => $this->content( $attributers ),
				'attrs'             => $attrs,
				'link'              => $this->link_wrapper( $attributers ),
				'isLinked'          => cc_attribute_checker( $attributers, 'linkWrapperActive', 'true' ) ? true : false,
				'conditions'        => $this->conditions( $attributers ),
				'innerBlocks'       => $inner_inner_blocks,
				'attributes'        => cc_attribute_checker( $attributers, 'htmlAttributes', 'true' ) ? $attributers['htmlAttributes'] : '',
				'styling'           => $this->styling( $attributers ),
				'repeater'          => $this->repeater( $attributers, $inner_block['blockName'] ),
				'tooltip'           => $this->tooltip( $attributers, $inner_block['blockName'] ),
				'skeleton'          => ( new \Cwicly_Skeleton() )->cc_skeleton_block( $inner_block, true, true ),
				'fontPreset'        => cc_attribute_checker( $attributers, 'fontGlobalStyleControl', 'true' ) && cc_attribute_checker( $attributers, 'fontGlobalStyle', 'true' ) ? $attributers['fontGlobalStyle'] : '',
				'presetAnim'        => cc_attribute_checker( $attributers, 'imageAnimation', 'true' ) ? $attributers['imageAnimation'] : '',
			);
		} else {
			return array(
				'blockName' => $inner_block['blockName'],
				'innerHTML' => $inner_block['innerHTML'],
			);
		}
	}

	/**
	 * Make the template for the block
	 *
	 * @param array $block The block to make the template for.
	 */
	private function template_maker( $block ) {
		$inner_blocks = array();
		foreach ( $block['innerBlocks'] as $inner_block ) {
			$inner_blocks[] = $this->maker_prep( $inner_block );
		}

		return $inner_blocks;
	}

	/**
	 * Make the tag for the block
	 *
	 * @param array  $attributes The attributes for the block.
	 * @param string $block_name The name of the block.
	 */
	private function tagger( $attributes, $block_name ) {
		$custom_tag = '';
		if ( 'cwicly/list' === $block_name ) {
			if ( cc_attribute_checker( $attributes, 'listTag', 'true' ) && $attributes['listTag'] ) {
				$custom_tag = $attributes['listTag'];
			} else {
				$custom_tag = 'ul';
			}
		} elseif ( isset( $attributes['linkWrapperActive'] ) && $attributes['linkWrapperActive'] ) {
			if ( 'cwicly/heading' === $block_name ) {
				$custom_tag = $attributes['headingTag'];
			} elseif ( isset( $attributes['containerLayoutTag'] ) && $attributes['containerLayoutTag'] && ( 'a' === $attributes['containerLayoutTag'] || 'button' === $attributes['containerLayoutTag'] ) ) {
				$custom_tag = $attributes['containerLayoutTag'];
			} else {
				$custom_tag = 'a';
			}
		} elseif ( isset( $attributes['containerLayoutTag'] ) && $attributes['containerLayoutTag'] ) {
			$custom_tag = $attributes['containerLayoutTag'];
		} elseif ( 'cwicly/paragraph' === $block_name ) {
			$custom_tag = 'p';
		} elseif ( 'cwicly/heading' === $block_name ) {
			$custom_tag = $attributes['headingTag'];
		} elseif ( 'cwicly/image' === $block_name ) {
			$custom_tag = 'img';
		} elseif ( 'cwicly/input' === $block_name ) {
			if ( cc_attribute_checker( $attributes, 'inputTemplate', 'true' ) && 'commenttextarea' === $attributes['inputTemplate'] ) {
				$custom_tag = 'textarea';
			} else {
				$custom_tag = 'input';
			}
		} else {
			$custom_tag = 'div';
		}

		return $custom_tag;
	}

	/**
	 * Prepare Content.
	 *
	 * @param array $attributes The attributes for the block.
	 */
	private function content( $attributes ) {
		$contenter = '';
		$extra     = '';
		$extra2    = '';
		$extra3    = '';
		$before    = '';
		$after     = '';
		$fallback  = '';

		if ( cc_attribute_checker( $attributes, 'dynamicStaticBefore', 'true' ) ) {
			$before = $attributes['dynamicStaticBefore'];
		}
		if ( cc_attribute_checker( $attributes, 'dynamicStaticAfter', 'true' ) ) {
			$after = $attributes['dynamicStaticAfter'];
		}
		if ( cc_attribute_checker( $attributes, 'dynamicStaticFallback', 'true' ) ) {
			$fallback = $attributes['dynamicStaticFallback'];
		}

		if ( isset( $attributes['dynamic'] ) && $attributes['dynamic'] ) {
			if ( 'wordpress' === $attributes['dynamic'] && isset( $attributes['dynamicWordPressType'] ) ) { // phpcs:ignore WordPress.WP.CapitalPDangit
				switch ( $attributes['dynamicWordPressType'] ) {
					case 'postcategory':
						if ( isset( $attributes['dynamicCategoryIndex'] ) && $attributes['dynamicCategoryIndex'] ) {
							$extra = $attributes['dynamicCategoryIndex'];
						}
						break;

					case 'postexcerpt':
						if ( isset( $attributes['dynamicWordPressExcerptLimit'] ) && $attributes['dynamicWordPressExcerptLimit'] ) {
							$extra = $attributes['dynamicWordPressExcerptLimit'];
						}

						break;
					case 'customcurrentdate':
						if ( isset( $attributes['dynamicWordPressCustomCurrentDate'] ) && $attributes['dynamicWordPressCustomCurrentDate'] ) {
							$extra = $attributes['dynamicWordPressCustomCurrentDate'];
						}

						break;
					case 'currentdate':
						if ( isset( $attributes['dynamicWordPressCurrentDateTime'] ) && $attributes['dynamicWordPressCurrentDateTime'] ) {
							$extra = $attributes['dynamicWordPressCurrentDateTime'];
						}
						if ( isset( $attributes['dynamicWordPressCurrentDateDate'] ) && $attributes['dynamicWordPressCurrentDateDate'] ) {
							$extra2 = $attributes['dynamicWordPressCurrentDateDate'];
						}

						break;
					case 'postdate':
						if ( isset( $attributes['dynamicWordPressDateType'] ) && $attributes['dynamicWordPressDateType'] ) {
							$extra = $attributes['dynamicWordPressDateType'];
						}
						if ( isset( $attributes['dynamicWordPressDateFormat'] ) && $attributes['dynamicWordPressDateFormat'] ) {
							$extra2 = $attributes['dynamicWordPressDateFormat'];
						}
						if ( isset( $attributes['dynamicWordPressDateCustom'] ) && $attributes['dynamicWordPressDateCustom'] ) {
							$extra3 = $attributes['dynamicWordPressDateCustom'];
						}

						break;
					case 'time':
						if ( isset( $attributes['dynamicWordPressTimeType'] ) && $attributes['dynamicWordPressTimeType'] ) {
							$extra = $attributes['dynamicWordPressTimeType'];
						}
						if ( isset( $attributes['dynamicWordPressTimeFormat'] ) && $attributes['dynamicWordPressTimeFormat'] ) {
							$extra2 = $attributes['dynamicWordPressTimeFormat'];
						}
						if ( isset( $attributes['dynamicWordPressTimeCustom'] ) && $attributes['dynamicWordPressTimeCustom'] ) {
							$extra3 = $attributes['dynamicWordPressTimeCustom'];
						}

						break;
					case 'postcomments':
						if ( isset( $attributes['dynamicWordPressCommentsNone'] ) && $attributes['dynamicWordPressCommentsNone'] ) {
							$extra = $attributes['dynamicWordPressCommentsNone'];
						}
						if ( isset( $attributes['dynamicWordPressCommentsOne'] ) && $attributes['dynamicWordPressCommentsOne'] ) {
							$extra2 = $attributes['dynamicWordPressCommentsOne'];
						}
						if ( isset( $attributes['dynamicWordPressCommentsMultiple'] ) && $attributes['dynamicWordPressCommentsMultiple'] ) {
							$extra3 = $attributes['dynamicWordPressCommentsMultiple'];
						}

						break;

					case 'authorinfo':
						if ( isset( $attributes['dynamicWordPressAuthorInfo'] ) && $attributes['dynamicWordPressAuthorInfo'] ) {
							$extra = $attributes['dynamicWordPressAuthorInfo'];
						}

						break;
					case 'userinfo':
						if ( isset( $attributes['dynamicWordPressUserInfo'] ) && $attributes['dynamicWordPressUserInfo'] ) {
							$extra = $attributes['dynamicWordPressUserInfo'];
						}

						break;
					case 'siteoption':
					case 'authorcustomfield':
					case 'usercustomfield':
					case 'customfield':
						if ( isset( $attributes['dynamicWordPressExtra'] ) && $attributes['dynamicWordPressExtra'] ) {
							$extra = $attributes['dynamicWordPressExtra'];
						}

						break;
				}
				$contenter = array(
					'source'   => 'wordpress',
					'type'     => $attributes['dynamicWordPressType'],
					'extra'    => $extra,
					'extra2'   => $extra2,
					'extra3'   => $extra3,
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'taxonomyquery' === $attributes['dynamic'] && isset( $attributes['dynamicWordPressType'] ) ) {
				$contenter = array(
					'source'   => 'taxonomyquery',
					'type'     => $attributes['dynamicWordPressType'],
					'extra'    => $extra,
					'extra2'   => $extra2,
					'extra3'   => $extra3,
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'userquery' === $attributes['dynamic'] && isset( $attributes['dynamicWordPressType'] ) ) {
				$contenter = array(
					'source'   => 'userquery',
					'type'     => $attributes['dynamicWordPressType'],
					'extra'    => $extra,
					'extra2'   => $extra2,
					'extra3'   => $extra3,
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'woocommerce' === $attributes['dynamic'] && isset( $attributes['dynamicWordPressType'] ) ) {
				switch ( $attributes['dynamicWordPressType'] ) {
					case 'price':
					case 'regularprice':
					case 'saleprice':
					case 'variationmax':
					case 'variationmin':
					case 'cartitemprice':
					case 'cartitemregularprice':
					case 'cartitemsaleprice':
					case 'cartitemdesc':
					case 'cartitemtotal':
					case 'carttotal':
					case 'cartsubtotal':
					case 'cartshippingtotal':
						if ( isset( $attributes['dynamicWooType'] ) && $attributes['dynamicWooType'] ) {
							$extra = $attributes['dynamicWooType'];
						}

						break;

					case 'salefrom':
					case 'saleto':
						if ( isset( $attributes['dynamicWordPressDateFormat'] ) && $attributes['dynamicWordPressDateFormat'] ) {
							$extra = $attributes['dynamicWordPressDateFormat'];
						}

						break;
				}
				$contenter = array(
					'source'   => 'woocommerce',
					'type'     => $attributes['dynamicWordPressType'],
					'extra'    => $extra,
					'extra2'   => $extra2,
					'extra3'   => $extra3,
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( ( 'filter' === $attributes['dynamic'] ) && isset( $attributes['dynamicWordPressType'] ) && $attributes['dynamicWordPressType'] ) {
				$contenter = array(
					'source'   => 'filter',
					'type'     => $attributes['dynamicWordPressType'],
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'filterselection' === $attributes['dynamic'] ) {
				$contenter = array(
					'source'   => 'filterselection',
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'acf' === $attributes['dynamic'] && cc_attribute_checker( $attributes, 'dynamicACFGroup', 'true' ) && cc_attribute_checker( $attributes, 'dynamicACFField', 'true' ) ) {
				$location = '';
				if ( cc_attribute_checker( $attributes, 'dynamicACFFieldLocation', 'true' ) ) {
					switch ( $attributes['dynamicACFFieldLocation'] ) {
						case 'postid':
							if ( cc_attribute_checker( $attributes, 'dynamicACFFieldLocationID', 'true' ) && $attributes['dynamicACFFieldLocationID'] ) {
								$location = $attributes['dynamicACFFieldLocationID'];
							}

							break;

						case 'userid':
							if ( cc_attribute_checker( $attributes, 'dynamicACFFieldLocationID', 'true' ) && $attributes['dynamicACFFieldLocationID'] ) {
								$location = 'user_' . $attributes['dynamicACFFieldLocationID'] . '';
							}

							break;

						case 'option':
						case 'currentuser':
						case 'termid':
						case 'termquery':
						case 'userquery':
							$location = $attributes['dynamicACFFieldLocation'];

							break;
					}
				}

				if ( isset( $attributes['dynamicACFFieldPlus'] ) && \Cwicly\Helpers::check_if_exists( $attributes['dynamicACFFieldPlus'] ) ) {
					$extra = $attributes['dynamicACFFieldPlus'];
				}

				$contenter = array(
					'source'   => 'acf',
					'group'    => $attributes['dynamicACFGroup'],
					'field'    => $attributes['dynamicACFField'],
					'location' => $location,
					'extra'    => $extra,
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'repeater' === $attributes['dynamic'] && cc_attribute_checker( $attributes, 'dynamicRepeaterField', 'true' ) && $attributes['dynamicRepeaterField'] ) {
				$contenter = array(
					'source'   => 'repeater',
					'field'    => $attributes['dynamicRepeaterField'],
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'postquery' === $attributes['dynamic'] && cc_attribute_checker( $attributes, 'dynamicWordPressType', 'true' ) && $attributes['dynamicWordPressType'] ) {
				$contenter = array(
					'source'   => 'postquery',
					'field'    => $attributes['dynamicWordPressType'],
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'taxonomyterms' === $attributes['dynamic'] && cc_attribute_checker( $attributes, 'dynamicTaxTermsType', 'true' ) && $attributes['dynamicTaxTermsType'] ) {
				$contenter = array(
					'source'   => 'taxonomyterms',
					'field'    => $attributes['dynamicTaxTermsType'],
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'commentquery' === $attributes['dynamic'] && cc_attribute_checker( $attributes, 'dynamicWordPressType', 'true' ) && $attributes['dynamicWordPressType'] ) {
				switch ( $attributes['dynamicWordPressType'] ) {
					case 'comment_date':
						if ( isset( $attributes['dynamicWordPressDateType'] ) && $attributes['dynamicWordPressDateType'] ) {
							$extra = $attributes['dynamicWordPressDateType'];
						}
						if ( isset( $attributes['dynamicWordPressDateFormat'] ) && $attributes['dynamicWordPressDateFormat'] ) {
							$extra2 = $attributes['dynamicWordPressDateFormat'];
						}
						if ( isset( $attributes['dynamicWordPressDateCustom'] ) && $attributes['dynamicWordPressDateCustom'] ) {
							$extra3 = $attributes['dynamicWordPressDateCustom'];
						}

						break;

					case 'comment_time':
						if ( isset( $attributes['dynamicWordPressDateType'] ) && $attributes['dynamicWordPressDateType'] ) {
							$extra = $attributes['dynamicWordPressDateType'];
						}
						if ( isset( $attributes['dynamicWordPressDateFormat'] ) && $attributes['dynamicWordPressDateFormat'] ) {
							$extra2 = $attributes['dynamicWordPressDateFormat'];
						}
						if ( isset( $attributes['dynamicWordPressDateCustom'] ) && $attributes['dynamicWordPressDateCustom'] ) {
							$extra3 = $attributes['dynamicWordPressDateCustom'];
						}

						break;
				}

				$contenter = array(
					'source'   => 'commentquery',
					'field'    => $attributes['dynamicWordPressType'],
					'extra'    => $extra,
					'extra2'   => $extra2,
					'extra3'   => $extra3,
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			} elseif ( 'cartvariation' === $attributes['dynamic'] && cc_attribute_checker( $attributes, 'dynamicWordPressType', 'true' ) && $attributes['dynamicWordPressType'] ) {
				$contenter = array(
					'source'   => 'cartvariation',
					'type'     => $attributes['dynamicWordPressType'],
					'before'   => $before,
					'after'    => $after,
					'fallback' => $fallback,
				);
			}
		} elseif ( isset( $attributes['content'] ) && $attributes['content'] ) {
			$contenter = $attributes['content'];
		}

		return $contenter;
	}

	/**
	 * Shell prep
	 *
	 * @param string $classes $attributes The attributes for the block.
	 * @return string
	 */
	private function replace_shells( $classes ) {
		// replace all elements in string that have {shell=x} and extract the x and replace it with the shell. multiple shells are possible, keep other parts of the string.
		$shell = array();
		preg_match_all( '/{shell=(.*?)}/', $classes, $shell );
		if ( isset( $shell[1] ) && $shell[1] ) {
			foreach ( $shell[1] as $shell_item ) {
				$shell_item = str_replace( ' ', '', $shell_item );
				$shell      = \Cwicly\Helpers::get_shell( $shell_item );
				$classes    = str_replace( '{shell=' . $shell_item . '}', $shell, $classes );
			}
		}

		return $classes;
	}

	/**
	 * Prepare Image.
	 *
	 * @param array $attributes The attributes.
	 * @param bool  $svg SVG.
	 *
	 * @return string
	 */
	private function image( $attributes, $svg = false ) {
		$fallback = '';
		$extra    = '';

		if ( isset( $attributes['dynamicStaticFallbackID'] ) && $attributes['dynamicStaticFallbackID'] ) {
			$thumbnail_size = 'full';
			if ( cc_attribute_checker( $attributes, 'imageThumbnailSize', 'true' ) && $attributes['imageThumbnailSize'] ) {
				$thumbnail_size = $attributes['imageThumbnailSize'];
			}
			$fallback = wp_get_attachment_image_src( $attributes['dynamicStaticFallbackID'], $thumbnail_size );
		} elseif ( isset( $attributes['dynamicStaticFallbackURL'] ) && $attributes['dynamicStaticFallbackURL'] ) {
			$fallback = $attributes['dynamicStaticFallbackURL'];
		}

		$size = '';
		if ( isset( $attributes['imageThumbnailSize'] ) && $attributes['imageThumbnailSize'] ) {
			$size = $attributes['imageThumbnailSize'];
		}

		$image = array();

		if ( cc_attribute_checker( $attributes, 'imageType', 'false' ) || ( cc_attribute_checker( $attributes, 'imageType', 'true' ) && 'static' === $attributes['imageType'] ) ) {
			if ( isset( $attributes['imageID'] ) && $attributes['imageID'] && isset( $attributes['imageURL'] ) && $attributes['imageURL'] ) {
				$image['source'] = 'static';
				$thumbnail_size  = 'full';
				if ( cc_attribute_checker( $attributes, 'imageThumbnailSize', 'true' ) && $attributes['imageThumbnailSize'] ) {
					$thumbnail_size = $attributes['imageThumbnailSize'];
				}
				$image_info = wp_get_attachment_image_src( $attributes['imageID'], $thumbnail_size );
				if ( $image_info ) {
					$image['src'] = $image_info[0];
				} else {
					$image['src'] = $attributes['imageURL'];
				}
				$image['id']     = $attributes['imageID'];
				$image['width']  = $image_info[1];
				$image['height'] = $image_info[2];
				if ( cc_attribute_checker( $attributes, 'imageDisableSrcSet', 'false' ) ) {
					$image['srcset'] = esc_attr( wp_get_attachment_image_srcset( $attributes['imageID'], 'medium' ) );
				}
				if ( $svg && $image['id'] ) {
					$svg_path = get_attached_file( $image['id'] );
					if ( file_exists( $svg_path ) ) {
						$svg_file = file_get_contents( $svg_path );
						if ( ! empty( $svg_file ) ) {
							$svg = \Cwicly\Helpers::get_svg_content( $svg_file );
							if ( isset( $svg['svg'] ) && $svg['svg'] ) {
								$image['svg'] = $svg['svg'];
							}
							$svg_attributes          = \Cwicly\Helpers::get_svg_attributes( $svg_file, true );
							$image['svg_attributes'] = $svg_attributes;
						}
					}
				}
			} elseif ( isset( $attributes['imageURL'] ) && $attributes['imageURL'] ) {
				$image['src'] = $attributes['imageURL'];
			}
		} elseif ( cc_attribute_checker( $attributes, 'imageType', 'true' ) && 'dynamic' === $attributes['imageType'] ) {
			if ( cc_attribute_checker( $attributes, 'dynamic', 'true' ) ) {
				if ( 'wordpress' === $attributes['dynamic'] || 'woocommerce' === $attributes['dynamic'] ) { // phpcs:ignore WordPress.WP.CapitalPDangit
					if ( cc_attribute_checker( $attributes, 'dynamicWordpressType', 'true' ) ) {
						$image = array(
							'source'    => $attributes['dynamic'],
							'type'      => $attributes['dynamicWordpressType'],
							'extra'     => $extra,
							'fallback'  => $fallback,
							'thumbnail' => $size,
						);
						if ( cc_attribute_checker( $attributes, 'imageDisableSrcSet', 'true' ) ) {
							$image['imageDisableSrcSet'] = true;
						}
					}
				} elseif ( 'acf' === $attributes['dynamic'] && cc_attribute_checker( $attributes, 'dynamicACFGroup', 'true' ) && cc_attribute_checker( $attributes, 'dynamicACFField', 'true' ) ) {
					$location = '';
					if ( cc_attribute_checker( $attributes, 'dynamicACFFieldLocation', 'true' ) ) {
						switch ( $attributes['dynamicACFFieldLocation'] ) {
							case 'postid':
								if ( cc_attribute_checker( $attributes, 'dynamicACFFieldLocationID', 'true' ) && $attributes['dynamicACFFieldLocationID'] ) {
									$location = $attributes['dynamicACFFieldLocationID'];
								}

								break;

							case 'userid':
								if ( cc_attribute_checker( $attributes, 'dynamicACFFieldLocationID', 'true' ) && $attributes['dynamicACFFieldLocationID'] ) {
									$location = 'user_' . $attributes['dynamicACFFieldLocationID'] . '';
								}

								break;

							case 'option':
							case 'currentuser':
							case 'termid':
							case 'termquery':
							case 'userquery':
								$location = $attributes['dynamicACFFieldLocation'];

								break;
						}
					}

					if ( cc_attribute_checker( $attributes, 'dynamicACFFieldPlus', 'true' ) && $attributes['dynamicACFFieldPlus'] ) {
						$extra = $attributes['dynamicACFFieldPlus'];
					}

					$image = array(
						'type'      => 'dynamic',
						'source'    => 'acf',
						'group'     => $attributes['dynamicACFGroup'],
						'field'     => $attributes['dynamicACFField'],
						'location'  => $location,
						'extra'     => $extra,
						'fallback'  => $fallback,
						'thumbnail' => $size,
					);
					if ( cc_attribute_checker( $attributes, 'imageDisableSrcSet', 'true' ) ) {
						$image['imageDisableSrcSet'] = true;
					}
				} elseif ( 'repeater' === $attributes['dynamic'] && cc_attribute_checker( $attributes, 'dynamicACFField', 'true' ) && $attributes['dynamicACFField'] ) {
					$image = array(
						'source'    => 'repeater',
						'field'     => $attributes['dynamicACFField'],
						'fallback'  => $fallback,
						'thumbnail' => $size,
					);
					if ( cc_attribute_checker( $attributes, 'imageDisableSrcSet', 'true' ) ) {
						$image['imageDisableSrcSet'] = true;
					}
				}
			}
		}

		if ( cc_attribute_checker( $attributes, 'imageAlt', 'true' ) && $attributes['imageAlt'] ) {
			$image['alt'] = $attributes['imageAlt'];
		} elseif ( isset( $attributes['imageID'] ) && $attributes['imageID'] ) {
			$image['alt'] = get_post_meta( $attributes['imageID'], '_wp_attachment_image_alt', true );
		}
		if ( isset( $attributes['lazyLoad'] ) ) {
			$image['lazyload'] = $attributes['lazyLoad'];
		}

		return $image;
	}

	/**
	 * Prepare Icon.
	 *
	 * @param array $attributes Attributes.
	 */
	private function icon( $attributes ) {
		$icon = array();

		if ( isset( $attributes['iconActive'] ) && $attributes['iconActive'] && isset( $attributes['iconIcon'] ) && $attributes['iconIcon'] && is_array( $attributes['iconIcon'] ) ) {
			$icon = $attributes['iconIcon'];
		}

		$main_breakpoint = \Cwicly\Helpers::get_main_breakpoint();

		if ( cc_attribute_checker( $attributes, 'iconSize', 'true' ) && isset( $attributes['iconSize'][ $main_breakpoint ] ) && $attributes['iconSize'][ $main_breakpoint ] ) {
			$icon['size'] = $attributes['iconSize'][ $main_breakpoint ];
		}

		return $icon;
	}

	/**
	 * Prepare SVG.
	 *
	 * @param array $attributes Attributes.
	 */
	private function svg( $attributes ) {
		$svg = array();

		if ( isset( $attributes['iconIcon'] ) && $attributes['iconIcon'] && is_array( $attributes['iconIcon'] ) ) {
			$svg['icon'] = $attributes['iconIcon'];
		}
		if ( isset( $attributes['svgType'] ) && $attributes['svgType'] ) {
			$svg['svgType'] = $attributes['svgType'];

			if ( 'image' === $attributes['svgType'] ) {
				$svg['image'] = self::image( $attributes, true );
			} elseif ( 'inline' === $attributes['svgType'] && isset( $attributes['inlineSvg'] ) && $attributes['inlineSvg'] ) {
				$svg['inline']           = \Cwicly\Helpers::get_svg_content( $attributes['inlineSvg'] );
				$svg['inlineAttributes'] = \Cwicly\Helpers::get_svg_attributes( $attributes['inlineSvg'], true );
			}
		}
		if ( isset( $attributes['imageType'] ) && $attributes['imageType'] ) {
			$svg['imageType'] = $attributes['imageType'];
		}

		return $svg;
	}

	/**
	 * Prepare Button.
	 *
	 * @param array $attributes Attributes.
	 */
	private function button( $attributes ) {
		$icon = array();

		if ( isset( $attributes['buttonIconActive'] ) && $attributes['buttonIconActive'] && isset( $attributes['buttonIcon'] ) && $attributes['buttonIcon'] && is_array( $attributes['buttonIcon'] ) ) {
			$icon['buttonIcon'] = $attributes['buttonIcon'];
		}
		if ( cc_attribute_checker( $attributes, 'buttonPosition', 'true' ) ) {
			$icon['iconPosition'] = $attributes['buttonPosition'];
		}
		return $icon;
	}

	/**
	 * Prepare Range Slider.
	 *
	 * @param array $attributes Attributes.
	 */
	private function rangeslider( $attributes ) {
		$final = array();
		if ( cc_attribute_checker( $attributes, 'rangeSliderVertical', 'true' ) ) {
			$final['rangeSliderVertical'] = $attributes['rangeSliderVertical'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderStep', 'true' ) ) {
			$final['rangeSliderStep'] = $attributes['rangeSliderStep'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderValues', 'true' ) ) {
			$final['rangeSliderValues'] = $attributes['rangeSliderValues'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderStep', 'true' ) ) {
			$final['rangeSliderStep'] = $attributes['rangeSliderStep'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderMin', 'true' ) ) {
			$final['rangeSliderMin'] = $attributes['rangeSliderMin'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderMax', 'true' ) ) {
			$final['rangeSliderMax'] = $attributes['rangeSliderMax'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderRange', 'true' ) ) {
			$final['rangeSliderRange'] = $attributes['rangeSliderRange'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderPush', 'true' ) ) {
			$final['rangeSliderPush'] = $attributes['rangeSliderPush'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderLabel', 'true' ) ) {
			$final['rangeSliderLabel'] = $attributes['rangeSliderLabel'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderHoverEffects', 'true' ) ) {
			$final['rangeSliderHoverEffects'] = $attributes['rangeSliderHoverEffects'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderPrefix', 'true' ) ) {
			$final['rangeSliderPrefix'] = $attributes['rangeSliderPrefix'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderSuffix', 'true' ) ) {
			$final['rangeSliderSuffix'] = $attributes['rangeSliderSuffix'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderPips', 'true' ) ) {
			$final['rangeSliderPips'] = $attributes['rangeSliderPips'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderPipStep', 'true' ) ) {
			$final['rangeSliderPipStep'] = $attributes['rangeSliderPipStep'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderPipsAll', 'true' ) ) {
			$final['rangeSliderPipsAll'] = $attributes['rangeSliderPipsAll'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderPipsFirst', 'true' ) ) {
			$final['rangeSliderPipsFirst'] = $attributes['rangeSliderPipsFirst'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderPipsLast', 'true' ) ) {
			$final['rangeSliderPipsLast'] = $attributes['rangeSliderPipsLast'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderPipsRest', 'true' ) ) {
			$final['rangeSliderPipsRest'] = $attributes['rangeSliderPipsRest'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderHandles', 'true' ) ) {
			$final['rangeSliderHandles'] = $attributes['rangeSliderHandles'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderFormatLabels', 'true' ) ) {
			$final['rangeSliderFormatLabels'] = $attributes['rangeSliderFormatLabels'];
		}
		if ( cc_attribute_checker( $attributes, 'rangeSliderFormatLocales', 'true' ) ) {
			$final['rangeSliderFormatLocales'] = $attributes['rangeSliderFormatLocales'];
		}

		return $final;
	}

	/**
	 * Prepare Filter.
	 *
	 * @param array $attributes Attributes.
	 */
	private function filter( $attributes ) {
		$final = array();
		if ( cc_attribute_checker( $attributes, 'filterQueryID', 'true' ) && isset( $attributes['filterQueryID']['field'] ) ) {
			if ( $attributes['filterQueryID']['field'] ) {
				$final['filterQueryID'] = $attributes['filterQueryID']['field'];
			}
		}
		if ( cc_attribute_checker( $attributes, 'filterTarget', 'true' ) ) {
			if ( isset( $attributes['filterTarget']['field'] ) ) {
				$final['filterTarget'] = $attributes['filterTarget']['field'];
			}
		}
		if ( cc_attribute_checker( $attributes, 'filterType', 'true' ) ) {
			$final['filterType'] = $attributes['filterType'];
		}
		if ( cc_attribute_checker( $attributes, 'filterSource', 'true' ) ) {
			$final['filterSource'] = $attributes['filterSource'];
		}
		if ( cc_attribute_checker( $attributes, 'filterDataType', 'true' ) ) {
			$final['filterDataType'] = $attributes['filterDataType'];
		}
		if ( cc_attribute_checker( $attributes, 'filterStaticData', 'true' ) ) {
			$final['filterStaticData'] = $attributes['filterStaticData'];
		}
		if ( cc_attribute_checker( $attributes, 'filterData', 'true' ) ) {
			$final['filterData'] = $attributes['filterData'];
		}
		if ( cc_attribute_checker( $attributes, 'filterParent', 'true' ) ) {
			$final['filterParent'] = $attributes['filterParent'];
		}
		if ( cc_attribute_checker( $attributes, 'filterInclude', 'true' ) ) {
			$final['filterInclude'] = $attributes['filterInclude'];
		}
		if ( cc_attribute_checker( $attributes, 'filterExclude', 'true' ) ) {
			$final['filterExclude'] = $attributes['filterExclude'];
		}
		if ( cc_attribute_checker( $attributes, 'filterOrderBy', 'true' ) ) {
			$final['filterOrderBy'] = $attributes['filterOrderBy'];
		}
		if ( cc_attribute_checker( $attributes, 'filterOrder', 'true' ) ) {
			$final['filterOrder'] = $attributes['filterOrder'];
		}
		if ( cc_attribute_checker( $attributes, 'filterChildless', 'true' ) ) {
			$final['filterChildless'] = $attributes['filterChildless'];
		}
		if ( cc_attribute_checker( $attributes, 'filterHideEmpty', 'true' ) ) {
			$final['filterHideEmpty'] = $attributes['filterHideEmpty'];
		}
		if ( cc_attribute_checker( $attributes, 'filterTaxField', 'true' ) ) {
			$final['filterTaxField'] = $attributes['filterTaxField'];
		}
		if ( cc_attribute_checker( $attributes, 'filterCountItems', 'true' ) ) {
			$final['filterCountItems'] = $attributes['filterCountItems'];
		}
		if ( cc_attribute_checker( $attributes, 'filterShowInSelection', 'true' ) ) {
			$final['filterShowInSelection'] = $attributes['filterShowInSelection'];
		}
		if ( cc_attribute_checker( $attributes, 'filterPlaceholder', 'true' ) ) {
			$final['filterPlaceholder'] = $attributes['filterPlaceholder'];
		}
		if ( cc_attribute_checker( $attributes, 'filterSelectionPrefix', 'true' ) ) {
			$final['filterSelectionPrefix'] = $attributes['filterSelectionPrefix'];
		}
		if ( cc_attribute_checker( $attributes, 'filterSelectionSuffix', 'true' ) ) {
			$final['filterSelectionSuffix'] = $attributes['filterSelectionSuffix'];
		}
		if ( cc_attribute_checker( $attributes, 'filterDynamicDefaults', 'true' ) ) {
			$final['filterDynamicDefaults'] = $attributes['filterDynamicDefaults'];
		}

		return $final;
	}

	/**
	 * Prepare Input.
	 *
	 * @param array $attributes Attributes.
	 */
	private function input( $attributes ) {
		$final = array();
		if ( cc_attribute_checker( $attributes, 'inputTemplate', 'true' ) ) {
			$final['inputTemplate'] = $attributes['inputTemplate'];
		}
		if ( cc_attribute_checker( $attributes, 'inputLabelDynamic', 'true' ) ) {
			$final['inputLabelDynamic'] = $attributes['inputLabelDynamic'];
		}
		if ( cc_attribute_checker( $attributes, 'inputPlaceholder', 'true' ) ) {
			$final['inputPlaceholder'] = $attributes['inputPlaceholder'];
		}

		return $final;
	}

	/**
	 * Prepare Button.
	 *
	 * @param array $attributes Attributes.
	 */
	private function video( $attributes ) {
		$final = array();
		if ( cc_attribute_checker( $attributes, 'videoType', 'true' ) ) {
			if ( cc_attribute_checker( $attributes, 'videoType', 'true' ) ) {
				$final['source'] = $attributes['videoType'];
			}
			if ( cc_attribute_checker( $attributes, 'videoStaticPlatform', 'true' ) ) {
				$final['videoStaticPlatform'] = $attributes['videoStaticPlatform'];
			}
			if ( cc_attribute_checker( $attributes, 'videoStaticURL', 'true' ) ) {
				$final['videoStaticURL'] = $attributes['videoStaticURL'];
			}
			if ( cc_attribute_checker( $attributes, 'videoPrivacy', 'true' ) ) {
				$final['videoPrivacy'] = $attributes['videoPrivacy'];
			}
			if ( isset( $attributes['videoRelated'] ) ) {
				$final['videoRelated'] = $attributes['videoRelated'];
			}
			if ( cc_attribute_checker( $attributes, 'videoBranding', 'true' ) ) {
				$final['videoBranding'] = $attributes['videoBranding'];
			}
			if ( cc_attribute_checker( $attributes, 'videoStart', 'true' ) ) {
				$final['videoStart'] = $attributes['videoStart'];
			}
			if ( cc_attribute_checker( $attributes, 'videoEnd', 'true' ) ) {
				$final['videoEnd'] = $attributes['videoEnd'];
			}
			if ( cc_attribute_checker( $attributes, 'videoAutoplay', 'true' ) ) {
				$final['videoAutoplay'] = $attributes['videoAutoplay'];
			}
			if ( cc_attribute_checker( $attributes, 'videoMute', 'true' ) ) {
				$final['videoMute'] = $attributes['videoMute'];
			}
			if ( cc_attribute_checker( $attributes, 'videoLoop', 'true' ) ) {
				$final['videoLoop'] = $attributes['videoLoop'];
			}
			if ( cc_attribute_checker( $attributes, 'videoControls', 'true' ) ) {
				$final['videoControls'] = $attributes['videoControls'];
			}
			if ( cc_attribute_checker( $attributes, 'videoStaticEmbedURL', 'true' ) ) {
				$final['videoStaticEmbedURL'] = $attributes['videoStaticEmbedURL'];
			}

			if ( isset( $attributes['videoDynamicType'] ) && 'acf' === $attributes['videoDynamicType'] && cc_attribute_checker( $attributes, 'videoDynamicAcfGroup', 'true' ) && cc_attribute_checker( $attributes, 'videoDynamicAcfField', 'true' ) ) {
				$final['dynamicType'] = $attributes['videoDynamicType'];
				$final['group']       = $attributes['videoDynamicAcfGroup'];
				$final['field']       = $attributes['videoDynamicAcfField'];
			}
		}

		if ( cc_attribute_checker( $attributes, 'videoImageOverlay', 'true' ) ) {
			$final['videoImageOverlay'] = true;
		}

		return $final;
	}

	/**
	 * Prepare Conditions.
	 *
	 * @param array $attributes Attributes.
	 */
	private function conditions( $attributes ) {
		$final = array();
		if ( isset( $attributes['hideConditionsToggle'] ) && $attributes['hideConditionsToggle'] ) {
			return $final;
		}
		if ( cc_attribute_checker( $attributes, 'hideLoggedIn', 'true' ) ) {
			$final['hideLoggedIn'] = true;
		}
		if ( cc_attribute_checker( $attributes, 'hideGuest', 'true' ) ) {
			$final['hideGuest'] = true;
		}
		if ( cc_attribute_checker( $attributes, 'hideConditions', 'true' ) ) {
			$final['hideConditions'] = $attributes['hideConditions'];
		}
		if ( cc_attribute_checker( $attributes, 'hideConditionsType', 'true' ) ) {
			$final['hideConditionsType'] = $attributes['hideConditionsType'];
		}

		return $final;
	}

	/**
	 * Prepare Styling.
	 *
	 * @param array $attributes Attributes.
	 */
	private function styling( $attributes ) {
		$extra    = '';
		$fallback = '';
		$final    = array();
		if ( cc_attribute_checker( $attributes, 'backgroundDynamicStaticFallbackID', 'true' ) && $attributes['backgroundDynamicStaticFallbackID'] ) {
			$fallback       = $attributes['backgroundDynamicStaticFallbackID'];
			$thumbnail_size = 'full';
			$fallback       = wp_get_attachment_image_src( $attributes['backgroundDynamicStaticFallbackID'], $thumbnail_size );
		} elseif ( cc_attribute_checker( $attributes, 'backgroundDynamicStaticFallbackURL', 'true' ) && $attributes['backgroundDynamicStaticFallbackURL'] ) {
			$fallback = $attributes['backgroundDynamicStaticFallbackURL'];
		}

		$main_breakpoint = \Cwicly\Helpers::get_main_breakpoint();
		if ( cc_attribute_checker( $attributes, 'backgroundType', 'true' ) && $attributes['backgroundType'] && isset( $attributes['backgroundType'][ $main_breakpoint ] ) && 'image' === $attributes['backgroundType'][ $main_breakpoint ] && cc_attribute_checker( $attributes, 'backgroundImageType', 'true' ) && 'dynamic' === $attributes['backgroundImageType'] && cc_attribute_checker( $attributes, 'backgroundDynamic', 'true' ) && $attributes['backgroundDynamic'] ) {
			if ( cc_attribute_checker( $attributes, 'backgroundDynamic', 'true' ) ) {
				if ( 'wordpress' === $attributes['backgroundDynamic'] ) { // phpcs:ignore WordPress.WP.CapitalPDangit
					if ( cc_attribute_checker( $attributes, 'backgroundDynamicWordpressType', 'true' ) ) {
						$final = array(
							'source'   => 'wordpress',
							'type'     => $attributes['backgroundDynamicWordpressType'],
							'fallback' => $fallback,
						);
					}
				} elseif ( 'acf' === $attributes['backgroundDynamic'] && cc_attribute_checker( $attributes, 'backgroundDynamicACFGroup', 'true' ) && cc_attribute_checker( $attributes, 'backgroundDynamicACFField', 'true' ) ) {
					$location = '';
					if ( cc_attribute_checker( $attributes, 'backgroundDynamicACFFieldLocation', 'true' ) ) {
						switch ( $attributes['backgroundDynamicACFFieldLocation'] ) {
							case 'postid':
								if ( cc_attribute_checker( $attributes, 'backgroundDynamicACFFieldLocationID', 'true' ) && $attributes['backgroundDynamicACFFieldLocationID'] ) {
									$location = $attributes['backgroundDynamicACFFieldLocationID'];
								}

								break;

							case 'userid':
								if ( cc_attribute_checker( $attributes, 'backgroundDynamicACFFieldLocationID', 'true' ) && $attributes['backgroundDynamicACFFieldLocationID'] ) {
									$location = 'user_' . $attributes['backgroundDynamicACFFieldLocationID'] . '';
								}

								break;

							case 'option':
							case 'currentuser':
							case 'termid':
							case 'termquery':
							case 'userquery':
								$location = $attributes['dynamicACFFieldLocation'];

								break;
						}
					}

					if ( cc_attribute_checker( $attributes, 'backgroundDynamicACFFieldPlus', 'true' ) && $attributes['backgroundDynamicACFFieldPlus'] ) {
						$extra = $attributes['backgroundDynamicACFFieldPlus'];
					}

					$final = array(
						'type'     => 'dynamic',
						'source'   => 'acf',
						'group'    => $attributes['backgroundDynamicACFGroup'],
						'field'    => $attributes['backgroundDynamicACFField'],
						'location' => $location,
						'extra'    => $extra,
						'fallback' => $fallback,
					);
				}
			}
		}

		return $final;
	}

	/**
	 * Prepare Link Wrapper.
	 *
	 * @param array $attributes Attributes.
	 */
	private function link_wrapper( $attributes ) {
		if ( cc_attribute_checker( $attributes, 'linkWrapperActive', 'true' ) ) {
			$description = '';
			if ( cc_attribute_checker( $attributes, 'linkWrapperShareDescription', 'true' ) ) {
				$description = $attributes['linkWrapperShareDescription'];
			}
			$title      = '';
			$aria_label = '';
			$rel        = '';
			$target     = '';
			$url        = array();
			if ( cc_attribute_checker( $attributes, 'linkWrapperRel', 'true' ) ) {
				$rel = $attributes['videoType'];
			}
			if ( cc_attribute_checker( $attributes, 'linkWrapperTitle', 'true' ) ) {
				$title = $attributes['linkWrapperTitle'];
			}
			if ( cc_attribute_checker( $attributes, 'linkWrapperAriaLabel', 'true' ) ) {
				$aria_label = $attributes['linkWrapperAriaLabel'];
			}
			if ( cc_attribute_checker( $attributes, 'linkWrapperNewTab', 'true' ) ) {
				$target = '_blank';
			} else {
				$target = '_self';
			}

			if ( cc_attribute_checker( $attributes, 'linkWrapperType', 'true' ) ) {
				switch ( $attributes['linkWrapperType'] ) {
					case 'action':
						if ( cc_attribute_checker( $attributes, 'linkWrapperAction', 'true' ) ) {
							switch ( $attributes['linkWrapperAction'] ) {
								case 'contact':
									$source   = '';
									$one_line = '';
									$skype    = '';
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionContactOneLine', 'true' ) ) {
										$one_line = $attributes['linkWrapperActionContactOneLine'];
									}
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionContactType', 'true' ) ) {
										$source = $attributes['linkWrapperActionContactType'];
									}
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionContactSkype', 'true' ) ) {
										$source = $attributes['linkWrapperActionContactSkype'];
									}
									$url['type']   = 'dynamic';
									$url['href']   = 'contact';
									$url['source'] = $source;
									if ( '' !== $one_line ) {
										$url['oneLine'] = $one_line;
									}
									if ( $skype ) {
										$url['skype'] = $skype;
									}

									break;
								case 'share':
									$source = '';
									if ( cc_attribute_checker( $attributes, 'linkWrapperShare', 'true' ) ) {
										$source = $attributes['linkWrapperShare'];
									}

									$url['type']        = 'dynamic';
									$url['href']        = 'share';
									$url['source']      = $source;
									$url['description'] = $description;

									break;

								case 'modal':
									$modal_id   = '';
									$modal_type = '';
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionModalBlockId', 'true' ) ) {
										$modal_id = $attributes['linkWrapperActionModalBlockId'];
									}
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionModalType', 'true' ) ) {
										$modal_type = $attributes['linkWrapperActionModalType'];
									}

									$url['type']   = 'dynamic';
									$url['href']   = 'modal';
									$url['source'] = $modal_type;
									if ( $modal_id ) {
										$url['modalID'] = $modal_id;
									}

									break;

								case 'prevQuery':
								case 'nextQuery':
								case 'infiniteButtonLoad':
									$url['type'] = 'dynamic';
									$url['href'] = $attributes['linkWrapperAction'];

									break;

								case 'scrolltotop':
									$offset     = '';
									$offset_out = '';
									$target_in  = '';
									$target_out = '';
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionExtra', 'true' ) && $attributes['linkWrapperActionExtra'] && $attributes['linkWrapperActionExtra']['offset'] ) {
										$offset = $attributes['linkWrapperActionExtra']['offset'];
									}
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionExtra', 'true' ) && $attributes['linkWrapperActionExtra'] && $attributes['linkWrapperActionExtra']['outoffset'] ) {
										$offset_out = $attributes['linkWrapperActionExtra']['outoffset'];
									}
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionExtra', 'true' ) && $attributes['linkWrapperActionExtra'] && $attributes['linkWrapperActionExtra']['intarget'] ) {
										$target_in = $attributes['linkWrapperActionExtra']['intarget'];
									}
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionExtra', 'true' ) && $attributes['linkWrapperActionExtra'] && $attributes['linkWrapperActionExtra']['outtarget'] ) {
										$target_out = $attributes['linkWrapperActionExtra']['outtarget'];
									}

									$url['type'] = 'dynamic';
									$url['href'] = 'scrolltotop';
									if ( $offset ) {
										$url['offset'] = $offset;
									}
									if ( $offset_out ) {
										$url['offsetOut'] = $offset_out;
									}
									if ( $target_in ) {
										$url['targetIn'] = $target_in;
									}
									if ( $target_out ) {
										$url['targetOut'] = $target_out;
									}

									break;
								case 'filter':
									$url['type'] = 'dynamic';
									$url['href'] = 'filter';

									break;

								case 'woostepup':
								case 'woostepdown':
								case 'woocartitemremove':
								case 'wooaddtocart':
									$url['type']  = 'dynamic';
									$url['href']  = $attributes['linkWrapperAction'];
									$url['extra'] = cc_attribute_checker( $attributes, 'linkWrapperActionExtra', 'true' ) && $attributes['linkWrapperActionExtra'] ? $attributes['linkWrapperActionExtra'] : '';

									break;

								case 'lightbox':
									$args = array(
										'source'        => cc_attribute_checker( $attributes, 'linkWrapperActionLighboxSourceType', 'true' ) && $attributes['linkWrapperActionLighboxSourceType'] ? $attributes['linkWrapperActionLighboxSourceType'] : 'static',
										'acf'           => cc_attribute_checker( $attributes, 'linkWrapperActionLighboxSourceDynamic', 'true' ) && 'acffield' === $attributes['linkWrapperActionLighboxSourceDynamic'] ? true : false,
										'featuredImage' => cc_attribute_checker( $attributes, 'linkWrapperActionLighboxSourceDynamic', 'true' ) && 'featuredimage' === $attributes['linkWrapperActionLighboxSourceDynamic'] ? true : false,
										'acfField'      => cc_attribute_checker( $attributes, 'linkWrapperActionLighboxAcfFields', 'true' ) && $attributes['linkWrapperActionLighboxAcfFields'] ? $attributes['linkWrapperActionLighboxAcfFields'] : '',
										'acfGroup'      => cc_attribute_checker( $attributes, 'linkWrapperActionLighboxAcfGroup', 'true' ) && $attributes['linkWrapperActionLighboxAcfGroup'] ? $attributes['linkWrapperActionLighboxAcfGroup'] : '',
										'type'          => cc_attribute_checker( $attributes, 'linkWrapperActionLighboxType', 'true' ) && $attributes['linkWrapperActionLighboxType'] ? $attributes['linkWrapperActionLighboxType'] : 'image',
										'videoURL'      => cc_attribute_checker( $attributes, 'linkWrapperActionLighboxVideoURL', 'true' ) && $attributes['linkWrapperActionLighboxVideoURL'] ? $attributes['linkWrapperActionLighboxVideoURL'] : '',
										'videoID'       => cc_attribute_checker( $attributes, 'linkWrapperActionLighboxVideoID', 'true' ) && $attributes['linkWrapperActionLighboxVideoID'] ? $attributes['linkWrapperActionLighboxVideoID'] : '',
										'imageURL'      => cc_attribute_checker( $attributes, 'linkWrapperActionLighboxURL', 'true' ) && $attributes['linkWrapperActionLighboxURL'] ? $attributes['linkWrapperActionLighboxURL'] : '',
									);
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionLighboxHeight', 'true' ) && $attributes['linkWrapperActionLighboxHeight'] ) {
										$args['height'] = $attributes['linkWrapperActionLighboxHeight'];
									}
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionLighboxWidth', 'true' ) && $attributes['linkWrapperActionLighboxWidth'] ) {
										$args['width'] = $attributes['linkWrapperActionLighboxWidth'];
									}
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionLighboxCaption', 'true' ) && $attributes['linkWrapperActionLighboxCaption'] ) {
										$args['caption'] = $attributes['linkWrapperActionLighboxCaption'];
									}
									if ( cc_attribute_checker( $attributes, 'linkWrapperActionLighboxOverlayGradientSelected', 'false' ) && cc_attribute_checker( $attributes, 'linkWrapperActionLighboxOverlay', 'true' ) && $attributes['linkWrapperActionLighboxOverlay'] ) {
										$args['overlay'] = $attributes['linkWrapperActionLighboxOverlay'];
									} elseif ( cc_attribute_checker( $attributes, 'linkWrapperActionLighboxOverlayGradient', 'true' ) && $attributes['linkWrapperActionLighboxOverlayGradient'] ) {
										$args['overlay'] = $attributes['linkWrapperActionLighboxOverlayGradient'];
									}
									$url['type']  = 'dynamic';
									$url['href']  = 'lightbox';
									$url['extra'] = $args;

									break;
							}
						}

						break;
					case 'url':
						if ( cc_attribute_checker( $attributes, 'linkWrapperSourceType', 'true' ) ) {
							if ( 'static' === $attributes['linkWrapperSourceType'] ) {
								if ( cc_attribute_checker( $attributes, 'linkWrapperUrl', 'true' ) ) {
									$url['type'] = 'static';

									$id   = '';
									$type = '';
									$kind = '';

									$link = '';

									if ( cc_attribute_checker( $attributes, 'linkWrapperStaticObject', 'true' ) ) {
										if ( isset( $attributes['linkWrapperStaticObject']['id'] ) && $attributes['linkWrapperStaticObject']['id'] ) {
											$id = intval( $attributes['linkWrapperStaticObject']['id'] );
										}
										if ( isset( $attributes['linkWrapperStaticObject']['type'] ) && $attributes['linkWrapperStaticObject']['type'] ) {
											$type = $attributes['linkWrapperStaticObject']['type'];
										}
										if ( isset( $attributes['linkWrapperStaticObject']['kind'] ) && $attributes['linkWrapperStaticObject']['kind'] ) {
											$kind = $attributes['linkWrapperStaticObject']['kind'];
										}

										switch ( $type ) {
											// get permalink.
											case 'post':
											case 'page':
												$link = get_permalink( $id );

												break;

											case 'category':
											case 'tag':
											case 'taxonomy':
												$link = get_term_link( $id );

												break;

											case 'post_format':
												$link = get_post_format_link( $id );

												break;
										}

										if ( ! $link ) {
											switch ( $kind ) {
												case 'taxonomy':
													$link = get_term_link( $id );

													break;

												case 'post-type':
													$link = get_permalink( $id );

													break;
											}
										}
									}

									$url['href'] = $link ? $link : $attributes['linkWrapperUrl'];
								}
							} elseif ( 'dynamic' === $attributes['linkWrapperSourceType'] ) {
								$url['type'] = 'dynamic';
								if ( cc_attribute_checker( $attributes, 'linkWrapperSourceDynamic', 'true' ) ) {
									switch ( $attributes['linkWrapperSourceDynamic'] ) {
										case 'acffield':
											if ( cc_attribute_checker( $attributes, 'linkWrapperAcfGroup', 'true' ) && cc_attribute_checker( $attributes, 'linkWrapperAcfFields', 'true' ) ) {
												$location = '';
												if ( cc_attribute_checker( $attributes, 'linkWrapperAcfLocation', 'true' ) ) {
													switch ( $attributes['linkWrapperAcfLocation'] ) {
														case 'postid':
															if ( cc_attribute_checker( $attributes, 'linkWrapperAcfLocationID', 'true' ) && $attributes['linkWrapperAcfLocationID'] ) {
																$location = $attributes['linkWrapperAcfLocationID'];
															}

															break;

														case 'userid':
															if ( cc_attribute_checker( $attributes, 'linkWrapperAcfLocationID', 'true' ) && $attributes['linkWrapperAcfLocationID'] ) {
																$location = 'user_' . $attributes['linkWrapperAcfLocationID'] . '';
															}

															break;

														case 'option':
														case 'currentuser':
														case 'termid':
														case 'termquery':
														case 'userquery':
															$location = $attributes['linkWrapperAcfLocation'];

															break;
													}
												}

												if ( cc_attribute_checker( $attributes, 'dynamicACFFieldPlus', 'true' ) && $attributes['dynamicACFFieldPlus'] ) {
													$extra = $attributes['dynamicACFFieldPlus'];
												}

												$url['href'] = array(
													'type' => 'dynamic',
													'source' => 'acffield',
													'group' => $attributes['linkWrapperAcfGroup'],
													'field' => $attributes['linkWrapperAcfFields'],
													'location' => $location,
													// 'extra' => $extra,
													// 'fallback' => $fallback,
												);
											}

											break;
										case 'acfrepeater':
											if ( cc_attribute_checker( $attributes, 'linkWrapperAcfRepeater', 'true' ) && cc_attribute_checker( $attributes, 'linkWrapperAcfRepeater', 'true' ) ) {
												$url['href'] = array(
													'type' => 'dynamic',
													'source' => 'acfrepeater',
													'field' => $attributes['linkWrapperAcfRepeater'],
													// 'extra' => $extra,
													// 'fallback' => $fallback,
												);
											}

											break;
										default:
											// } && $attributes['linkWrapperSourceDynamic'] != 'acffield' && $attributes['linkWrapperSourceDynamic'] != 'acfrepeater') {
											if ( 'taxonomytermsurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'taxonomytermsurl';
											} elseif ( 'taxonomyqueryurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'taxonomyqueryurl';
											} elseif ( 'userqueryurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'userqueryurl';
											} elseif ( 'commentqueryauthorarchive' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'commentqueryauthorarchive';
											} elseif ( 'commentqueryauthorinfourl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'commentquery=comment_author_url';
											} elseif ( 'commenturl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'commenturl';
											} elseif ( 'commentreplyurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'commentreplyurl';
											} elseif ( 'directlogout' === $attributes['linkWrapperSourceDynamic'] ) {
												if ( cc_attribute_checker( $attributes, 'linkWrapperSourceExtra', 'true' ) && 'specific' != $attributes['linkWrapperSourceExtra'] ) {
													$url['href'] = array(
														'source' => 'directlogout',
														'one' => $attributes['linkWrapperSourceExtra'],
													);
												} elseif ( cc_attribute_checker( $attributes, 'linkWrapperSourceExtra', 'true' ) && 'specific' === $attributes['linkWrapperSourceExtra'] && isset( $attributes['linkWrapperSourceExtra2'] ) && $attributes['linkWrapperSourceExtra2'] ) {
													$url['href'] = array(
														'source' => 'directlogout',
														'one' => 'specific',
														'two' => $attributes['linkWrapperSourceExtra2'],
													);
												} else {
													$url['href'] = 'directlogout';
												}
											} elseif ( 'loginurl' === $attributes['linkWrapperSourceDynamic'] ) {
												if ( $attributes['linkWrapperSourceExtra'] && 'specific' != $attributes['linkWrapperSourceExtra'] ) {
													$url['href'] = array(
														'source' => 'loginurl',
														'one' => $attributes['linkWrapperSourceExtra'],
													);
												} elseif ( cc_attribute_checker( $attributes, 'linkWrapperSourceExtra', 'true' ) && 'specific' === $attributes['linkWrapperSourceExtra'] && isset( $attributes['linkWrapperSourceExtra2'] ) && $attributes['linkWrapperSourceExtra2'] ) {
													$url['href'] = array(
														'source' => 'loginurl',
														'one' => 'specific',
														'two' => $attributes['linkWrapperSourceExtra2'],
													);
												} else {
													$url['href'] = 'loginurl';
												}
											} elseif ( 'editcommenturl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'editcommenturl';
											} elseif ( 'posturl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'pageurl';
											} elseif ( 'postarchiveurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'archiveurl';
											} elseif ( 'archiveurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'archiveurl';
											} elseif ( 'featuredimage' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'featuredimage';
											} elseif ( 'homeurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'homeurl';
											} elseif ( 'siteurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'siteurl';
											} elseif ( 'authorurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'authorurl';
											} elseif ( 'commentsurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'commentsurl';
											} elseif ( 'commentsurl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'internalurl';
											} elseif ( 'shortcode' === $attributes['linkWrapperSourceDynamic'] && cc_attribute_checker( $attributes, 'linkWrapperDynamicShortcode', 'true' ) ) {
												$url['href'] = array(
													'source' => 'shortcode',
													'one' => $attributes['linkWrapperDynamicShortcode'],
												);
											} elseif ( 'woocheckouturl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'woocheckouturl';
											} elseif ( 'woocarturl' === $attributes['linkWrapperSourceDynamic'] ) {
												$url['href'] = 'woocarturl';
											}

											break;
									}
								}
							}
						}

						break;
				}
			}

			return array(
				'rel'       => $rel,
				'title'     => $title,
				'ariaLabel' => $aria_label,
				'target'    => $target,
				'url'       => $url,
			);
		}

		return array();
	}

	/**
	 * Prepare Repeater.
	 *
	 * @param array  $attributes Attributes.
	 * @param string $block_name Block name.
	 *
	 * @return array
	 */
	private function repeater( $attributes, $block_name ) {
		if ( 'cwicly/repeater' === $block_name ) {
			$contenter = array();
			if ( cc_attribute_checker( $attributes, 'dynamic', 'true' ) && 'acf' === $attributes['dynamic'] && cc_attribute_checker( $attributes, 'dynamicACFGroup', 'true' ) && cc_attribute_checker( $attributes, 'dynamicACFField', 'true' ) ) {
				$location = '';
				if ( cc_attribute_checker( $attributes, 'dynamicACFFieldLocation', 'true' ) ) {
					switch ( $attributes['dynamicACFFieldLocation'] ) {
						case 'postid':
							if ( cc_attribute_checker( $attributes, 'dynamicACFFieldLocationID', 'true' ) && $attributes['dynamicACFFieldLocationID'] ) {
								$location = $attributes['dynamicACFFieldLocationID'];
							}

							break;

						case 'userid':
							if ( cc_attribute_checker( $attributes, 'dynamicACFFieldLocationID', 'true' ) && $attributes['dynamicACFFieldLocationID'] ) {
								$location = 'user_' . $attributes['dynamicACFFieldLocationID'] . '';
							}

							break;

						case 'option':
						case 'currentuser':
						case 'termid':
						case 'termquery':
						case 'userquery':
							$location = $attributes['dynamicACFFieldLocation'];

							break;
					}
				}

				$contenter = array(
					'source'   => 'acf',
					'group'    => $attributes['dynamicACFGroup'],
					'field'    => $attributes['dynamicACFField'],
					'location' => $location,
				);
			} elseif ( cc_attribute_checker( $attributes, 'dynamic', 'true' ) && 'woocartitems' === $attributes['dynamic'] ) {
				$contenter = array(
					'source' => 'woocartitems',
				);
			} elseif ( cc_attribute_checker( $attributes, 'dynamic', 'true' ) && 'woocartitemvariation' === $attributes['dynamic'] ) {
				$contenter = array(
					'source' => 'woocartitemvariation',
				);
			} elseif ( cc_attribute_checker( $attributes, 'dynamic', 'true' ) && 'woocrosssellproducts' === $attributes['dynamic'] ) {
				$contenter = array(
					'source' => 'woocrosssellproducts',
				);
			} elseif ( cc_attribute_checker( $attributes, 'dynamic', 'true' ) && 'woovariable' === $attributes['dynamic'] ) {
				$contenter = array(
					'source' => 'woovariable',
				);
			} elseif ( cc_attribute_checker( $attributes, 'dynamic', 'true' ) && 'woogrouped' === $attributes['dynamic'] ) {
				$contenter = array(
					'source' => 'woogrouped',
				);
			} elseif ( cc_attribute_checker( $attributes, 'dynamic', 'true' ) && 'woogallery' === $attributes['dynamic'] ) {
				$contenter = array(
					'source' => 'woogallery',
				);
			}

			if ( cc_attribute_checker( $attributes, 'repeaterSlider', 'true' ) ) {
				$contenter['repeaterSlider'] = true;
				if ( cc_attribute_checker( $attributes, 'repeaterSliderOptionsR', 'true' ) ) {
					$contenter['repeaterSliderOptionsR'] = $attributes['repeaterSliderOptionsR'];
				}
				if ( cc_attribute_checker( $attributes, 'repeaterSliderOptions', 'true' ) ) {
					$contenter['repeaterSliderOptions'] = $attributes['repeaterSliderOptions'];
				}
			}

			return $contenter;
		}
	}

	/**
	 * Prepare Taxonomy Terms.
	 *
	 * @param array  $attributes Attributes.
	 * @param string $block_name Block name.
	 *
	 * @return array
	 */
	private function taxonomy_terms( $attributes, $block_name ) {
		if ( 'cwicly/taxonomyterms' === $block_name ) {
			$contenter = array();
			if ( cc_attribute_checker( $attributes, 'taxtermsSource', 'true' ) ) {
				$contenter['taxtermsSource'] = $attributes['taxtermsSource'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsNumber', 'true' ) ) {
				$contenter['taxtermsNumber'] = $attributes['taxtermsNumber'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsPostType', 'true' ) ) {
				$contenter['taxtermsPostType'] = $attributes['taxtermsPostType'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsTaxonomies', 'true' ) ) {
				$contenter['taxtermsTaxonomies'] = $attributes['taxtermsTaxonomies'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsExclude', 'true' ) ) {
				$contenter['taxtermsExclude'] = $attributes['taxtermsExclude'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsInclude', 'true' ) ) {
				$contenter['taxtermsInclude'] = $attributes['taxtermsInclude'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsExcludeCurrent', 'true' ) ) {
				$contenter['taxtermsExcludeCurrent'] = $attributes['taxtermsExcludeCurrent'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsExcludeChildren', 'true' ) ) {
				$contenter['taxtermsExcludeChildren'] = $attributes['taxtermsExcludeChildren'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsOrderBy', 'true' ) ) {
				$contenter['taxtermsOrderBy'] = $attributes['taxtermsOrderBy'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsOrderDirection', 'true' ) ) {
				$contenter['taxtermsOrderDirection'] = $attributes['taxtermsOrderDirection'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsHideEmpty', 'true' ) ) {
				$contenter['taxtermsHideEmpty'] = $attributes['taxtermsHideEmpty'];
			}
			if ( cc_attribute_checker( $attributes, 'repeaterMasonry', 'true' ) ) {
				$contenter['repeaterMasonry'] = $attributes['repeaterMasonry'];
			}
			if ( cc_attribute_checker( $attributes, 'taxtermsTopParents', 'true' ) ) {
				$contenter['taxtermsTopParents'] = $attributes['taxtermsTopParents'];
			}

			return $contenter;
		}
	}

	/**
	 * Prepare Accordions
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	private function accordions( $attributes ) {
		$contenter = array();
		if ( cc_attribute_checker( $attributes, 'accordionLinked', 'true' ) && $attributes['accordionLinked'] && cc_attribute_checker( $attributes, 'accordionGroup', 'true' ) && $attributes['accordionGroup'] ) {
			$contenter['accordionGroup'] = $attributes['accordionGroup'];
		}

		return $contenter;
	}

	/**
	 * Prepare Accordion
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	private function accordion( $attributes ) {
		$contenter = array();
		if ( cc_attribute_checker( $attributes, 'accordionLinked', 'true' ) && $attributes['accordionLinked'] && cc_attribute_checker( $attributes, 'accordionGroup', 'true' ) && $attributes['accordionGroup'] ) {
			$contenter['accordionGroup'] = $attributes['accordionGroup'];
		}

		return $contenter;
	}

	/**
	 * Prepare Tab
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	private function tab( $attributes ) {
		$contenter = array();
		if ( cc_attribute_checker( $attributes, 'tabContentActive', 'true' ) && $attributes['tabContentActive'] ) {
			$contenter['tabContentActive'] = $attributes['tabContentActive'];
		}

		return $contenter;
	}

	/**
	 * Prepare Tab List
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	private function tab_list( $attributes ) {
		$contenter = array();
		if ( cc_attribute_checker( $attributes, 'tabContentsID', 'true' ) && $attributes['tabContentsID'] ) {
			$contenter['tabContentsID'] = $attributes['tabContentsID'];
		}

		return $contenter;
	}

	/**
	 * Prepare Tab Content
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	private function tab_content( $attributes ) {
		$contenter = array();
		if ( cc_attribute_checker( $attributes, 'tabContentActive', 'true' ) && $attributes['tabContentActive'] ) {
			$contenter['tabContentActive'] = $attributes['tabContentActive'];
		}

		return $contenter;
	}

	/**
	 * Prepare Popover
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	private function popover( $attributes ) {
		$contenter = array();
		if ( cc_attribute_checker( $attributes, 'popoverOptions', 'true' ) && $attributes['popoverOptions'] ) {
			$contenter['popoverOptions'] = $attributes['popoverOptions'];
		}

		return $contenter;
	}

	/**
	 * Prepare Swatch
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	private function swatch( $attributes ) {
		$contenter = array();
		if ( cc_attribute_checker( $attributes, 'swatchSlug', 'true' ) && $attributes['swatchSlug'] ) {
			$contenter['slug'] = $attributes['swatchSlug'];
		}
		if ( cc_attribute_checker( $attributes, 'swatchType', 'true' ) && $attributes['swatchType'] ) {
			$contenter['type'] = $attributes['swatchType'];
		}
		if ( cc_attribute_checker( $attributes, 'swatchText', 'true' ) && $attributes['swatchText'] ) {
			$contenter['text'] = $attributes['swatchText'];
		}

		return $contenter;
	}

	/**
	 * Prepare Tooltip
	 *
	 * @param array $attributes Attributes.
	 *
	 * @return array
	 */
	private function tooltip( $attributes ) {
		$contenter = array();
		if ( cc_attribute_checker( $attributes, 'tooltipActive', 'true' ) && $attributes['tooltipActive'] ) {
			$contenter['tooltipActive'] = $attributes['tooltipActive'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipSource', 'true' ) && $attributes['tooltipSource'] ) {
			$contenter['tooltipSource'] = $attributes['tooltipSource'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipACFGroup', 'true' ) && $attributes['tooltipACFGroup'] ) {
			$contenter['tooltipACFGroup'] = $attributes['tooltipACFGroup'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipACFField', 'true' ) && $attributes['tooltipACFField'] ) {
			$contenter['tooltipACFField'] = $attributes['tooltipACFField'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipCustom', 'true' ) && $attributes['tooltipCustom'] ) {
			$contenter['tooltipCustom'] = $attributes['tooltipCustom'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipExtra', 'true' ) && $attributes['tooltipExtra'] ) {
			$contenter['tooltipExtra'] = $attributes['tooltipExtra'];
		}
		if ( cc_attribute_checker( $attributes, 'dynamicWordPressExtra', 'true' ) && $attributes['dynamicWordPressExtra'] ) {
			$contenter['dynamicWordPressExtra'] = $attributes['dynamicWordPressExtra'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipContent', 'true' ) && $attributes['tooltipContent'] ) {
			$contenter['tooltipContent'] = $attributes['tooltipContent'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipArrow', 'true' ) && $attributes['tooltipArrow'] ) {
			$contenter['tooltipArrow'] = $attributes['tooltipArrow'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipAnimation', 'true' ) && $attributes['tooltipAnimation'] ) {
			$contenter['tooltipAnimation'] = $attributes['tooltipAnimation'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltiphideclick', 'true' ) && $attributes['tooltiphideclick'] ) {
			$contenter['tooltiphideclick'] = $attributes['tooltiphideclick'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipplace', 'true' ) && $attributes['tooltipplace'] ) {
			$contenter['tooltipplace'] = $attributes['tooltipplace'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipduration', 'true' ) && $attributes['tooltipduration'] ) {
			$contenter['tooltipduration'] = $attributes['tooltipduration'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipfollow', 'true' ) && $attributes['tooltipfollow'] ) {
			$contenter['tooltipfollow'] = $attributes['tooltipfollow'];
		}
		if ( cc_attribute_checker( $attributes, 'tooltipTheme', 'true' ) && $attributes['tooltipTheme'] ) {
			$contenter['tooltipTheme'] = $attributes['tooltipTheme'];
		}

		return $contenter;
	}
}
