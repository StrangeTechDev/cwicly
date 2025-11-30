<?php
/**
 * ACF class file.
 *
 * @package Cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

// Include the ACF plugin if it's not already included.
if ( file_exists( CWICLY_DIR_PATH . 'core/includes/acf' ) && ! class_exists( 'ACF' ) && ( ! defined( 'CWICLY_ACF' ) || CWICLY_ACF ) ) {
	require_once MY_ACF_PATH . 'acf.php';
}

/**
 * All necessary actions for ACF.
 *
 * @package Cwicly
 */
class ACF {

	/**
	 * ACF constructor.
	 */
	public function __construct() {
		add_filter( 'acf/settings/url', array( $this, 'acf_url' ) );
	}

	/**
	 * Set the path to the ACF plugin.
	 *
	 * @return string
	 */
	public function acf_url() {
		return MY_ACF_URL;
	}

	/**
	 * Process ACF fields the Cwicly way.
	 *
	 * @param array  $field The field.
	 * @param string $fallback The fallback.
	 * @param array  $attributes The attributes.
	 * @param string $block_name The block name.
	 * @param bool   $frontent_rendering Whether or not we are rendering on the frontend.
	 * @param array  $options The options.
	 * @param object $field_object The field object.
	 */
	public static function processor( $field, $fallback, $attributes, $block_name, $frontent_rendering = false, $options = array(), $field_object = null ) {
		if ( $field ) {
			if ( is_array( $field ) ) {
				if ( isset( $field['url'] ) && 'cwicly/svg' !== $block_name ) {
					if ( 'cwicly/image' === $block_name && isset( $field['width'] ) && isset( $field['height'] ) ) {
						$width     = $field['width'];
						$height    = $field['height'];
						$url       = $field['url'];
						$alt       = $field['alt'];
						$final_alt = '';
						if ( isset( $options[1] ) && $alt && '1' === $options[1] ) {
							$final_alt = '" alt="' . $alt . '';
						}
						$srcset        = '';
						$final_src_set = '';
						$size          = '';
						if ( isset( $options[2] ) && '1' === $options[2] && $field['id'] ) {
							$srcset = wp_get_attachment_image_srcset( $field['id'] );
							if ( $srcset ) {
								$final_src_set = '" srcset="' . $srcset . '';
							}
						}
						if ( isset( $options[0] ) && $options[0] && '0' != $options[0] && $field['sizes'] && isset( $field['sizes'][ $options[0] ] ) ) {
							if ( $field['sizes'][ $options[0] ] ) {
								$url    = $field['sizes'][ $options[0] ];
								$height = $field['sizes'][ $options[0] . '-height' ];
								$width  = $field['sizes'][ $options[0] . '-width' ];
								$size   = '" sizes="' . wp_get_attachment_image_sizes( $field['id'], $options[0] ) . '';
							}
						}
						if ( ! $frontent_rendering ) {
							return '' . $url . '" height="' . $height . '" width="' . $width . $final_alt . $final_src_set . $size . '';
						} else {
							$size = '';
							if ( isset( $options[0] ) && $options[0] ) {
								$size   = wp_get_attachment_image_sizes( $field['id'], $options[0] );
								$height = $field['sizes'][ $options[0] . '-height' ];
								$width  = $field['sizes'][ $options[0] . '-width' ];
							} else {
								$size = wp_get_attachment_image_sizes( $field['id'] );
							}

							return array(
								'url'    => $url,
								'width'  => $width,
								'height' => $height,
								'alt'    => $alt,
								'srcset' => $srcset,
								'size'   => $size,
							);
						}
					} else {
						if ( isset( $options[0] ) ) {
							if ( 'isVideo' === $options[0] ) {
								return \Cwicly\Helpers::get_dynamic_video_url( $attributes, $field );
							} elseif ( 'isVideoOverlay' === $options[0] ) {
								return \Cwicly\Helpers::get_dynamic_video_overlay_url( $attributes, $field );
							}
						}

						return $field['url'];
					}
				} elseif ( $field_object && isset( $options[0] ) && 'svg' === $options[0] && 'image' === $field_object['type'] ) {
					$svg_content = '';
					$svg_path    = get_attached_file( $field_object['value']['ID'] );

					if ( file_exists( $svg_path ) ) {
						$svg_content = file_get_contents( $svg_path );
					}

					if ( ! $svg_content ) {
						return new \WP_Error( 'error', 'SVG file not found', array( 'status' => 400 ) );
					}

					if ( isset( $options[1] ) && 'all' === $options[1] ) {
						$svg            = \Cwicly\Helpers::get_svg_content( $svg_content )['svg'];
						$svg_attributes = \Cwicly\Helpers::get_svg_attributes( $svg_content, true );
						return array(
							'content'    => $svg,
							'attributes' => $svg_attributes,
						);
					}

					if ( isset( $options[1] ) && 'viewBox' === $options[1] ) {
						$svg_attributes = \Cwicly\Helpers::get_svg_attributes( $svg_content );
						if ( $svg_attributes ) {
							return $svg_attributes;
						}
					}
					return \Cwicly\Helpers::get_svg_content( $svg_content )[ $options[1] ];
				} elseif ( $field_object && 'checkbox' === $field_object['type'] ) {
					if ( 'value' === $field_object['return_format'] || 'label' === $field_object['return_format'] ) {
						return implode( ',', $field );
					} elseif ( 'array' === $field_object['return_format'] ) {
						$final = array();
						foreach ( $field as $f ) {
							if ( isset( $f['value'] ) ) {
								$final[] = $f['value'];
							} elseif ( isset( $f['label'] ) ) {
								$final[] = $f['label'];
							}
						}

						return implode( ',', $final );
					}
				} elseif ( $field_object && 'radio' === $field_object['type'] ) {
					if ( 'value' === $field_object['return_format'] || 'label' === $field_object['return_format'] ) {
						return $field;
					} elseif ( 'array' === $field_object['return_format'] ) {
						return $field['value'];
					}
				} elseif ( $field_object && 'relationship' === $field_object['type'] ) {
					if ( 'object' === $field_object['return_format'] ) {
						$final = array();
						foreach ( $field as $f ) {
							if ( isset( $f->ID ) ) {
								$final[] = $f->ID;
							}
						}

						return implode( ',', $final );
					} elseif ( 'id' === $field_object['return_format'] ) {
						return implode( ',', $field );
					}
				} elseif ( $field_object && 'button_group' === $field_object['type'] ) {
					if ( 'value' === $field_object['return_format'] || 'label' === $field_object['return_format'] ) {
						return $field;
					} elseif ( 'array' === $field_object['return_format'] ) {
						return $field['value'];
					}
				} elseif ( $field_object && 'select' === $field_object['type'] ) {
					if ( 'value' === $field_object['return_format'] || 'label' === $field_object['return_format'] ) {
						return $field;
					} elseif ( 'array' === $field_object['return_format'] ) {
						return $field['value'];
					}
				} elseif ( $field_object && 'true_false' === $field_object['type'] ) {
					if ( 'value' === $field_object['return_format'] || 'label' === $field_object['return_format'] ) {
						return $field;
					} elseif ( 'array' === $field_object['return_format'] ) {
						return $field['value'];
					}
				} elseif ( $field_object && 'taxonomy' === $field_object['type'] ) {
					if ( 'object' === $field_object['return_format'] ) {
						$final = array();
						foreach ( $field as $f ) {
							if ( isset( $f->term_id ) ) {
								$final[] = $f->term_id;
							}
						}

						return implode( ',', $final );
					} elseif ( 'id' === $field_object['return_format'] ) {
						return implode( ',', $field );
					}
				} elseif ( $field_object && 'user' === $field_object['type'] ) {
					if ( 'object' === $field_object['return_format'] ) {
						$final = array();
						foreach ( $field as $f ) {
							if ( isset( $f->ID ) ) {
								$final[] = $f->ID;
							}
						}

						return implode( ',', $final );
					} elseif ( 'id' === $field_object['return_format'] ) {
						return implode( ',', $field );
					}
				} elseif ( $field_object && 'group' === $field_object['type'] ) {
					$final = array();
					foreach ( $field as $f ) {
						$final[] = self::processor( $f, $fallback, $attributes, $block_name, $frontent_rendering, $options, $field_object );
					}

					return implode( ',', $final );
				} elseif ( $field_object && 'post_object' === $field_object['type'] ) {
					if ( 'object' === $field_object['return_format'] ) {
						$final = array();
						foreach ( $field as $f ) {
							if ( isset( $f->ID ) ) {
								$final[] = $f->ID;
							}
						}

						return implode( ',', $final );
					} elseif ( 'id' === $field_object['return_format'] ) {
						return implode( ',', $field );
					}
				} elseif ( $field_object && 'flexible_content' === $field_object['type'] ) {
					$final = array();
					foreach ( $field as $f ) {
						$final[] = self::processor( $f, $fallback, $attributes, $block_name, $frontent_rendering, $options, $field_object );
					}

					return implode( ',', $final );
				} elseif ( $field_object && 'clone' === $field_object['type'] ) {
					$final = array();
					foreach ( $field as $f ) {
						$final[] = self::processor( $f, $fallback, $attributes, $block_name, $frontent_rendering, $options, $field_object );
					}

					return implode( ',', $final );
				} elseif ( $field_object && 'link' === $field_object['type'] ) {
					if ( 'array' === $field_object['return_format'] && isset( $field['url'] ) ) {
						return $field['url'];
					} elseif ( 'url' === $field_object['return_format'] ) {
						return $field;
					}
				} elseif ( $field_object && 'gallery' === $field_object['type'] ) {
					if ( 'array' === $field_object['return_format'] ) {
						$final = array();
						foreach ( $field as $f ) {
							if ( isset( $f['url'] ) ) {
								$final[] = $f['url'];
							}
						}

						return implode( ',', $final );
					} elseif ( 'url' === $field_object['return_format'] ) {
						$final = array();
						foreach ( $field as $f ) {
							$final[] = $f;
						}

						return implode( ',', $final );
					}
				} elseif ( $field_object && 'repeater' === $field_object['type'] ) {
					$final = array();
					foreach ( $field as $f ) {
						$final[] = self::processor( $f, $fallback, $attributes, $block_name, $frontent_rendering, $options, $field_object );
					}

					return implode( ',', $final );
				}
			} elseif ( isset( $field ) ) {
				if ( is_object( $field ) ) {
					if ( isset( $field->ID ) ) {
						return get_permalink( $field->ID );
					}
				} else {
					if ( isset( $options[0] ) ) {
						if ( 'isVideo' === $options[0] ) {
							return \Cwicly\Helpers::get_dynamic_video_url( $attributes, $field );
						} elseif ( 'isVideoOverlay' === $options[0] ) {
							return \Cwicly\Helpers::get_dynamic_video_overlay_url( $attributes, $field );
						}
					}

					return $field;
				}
			} elseif ( $fallback && 'false' !== $fallback ) {
				if ( is_numeric( $fallback ) && 'cwicly/image' === $block_name ) {
					return wp_get_attachment_url( $fallback );
				} else {
					return $fallback;
				}
			}
		} elseif ( $fallback && 'false' !== $fallback ) {
			if ( is_numeric( $fallback ) && 'cwicly/svg' === $block_name ) {
				$svg_content = '';
				$svg_path    = get_attached_file( $fallback );

				if ( file_exists( $svg_path ) ) {
					$svg_content = file_get_contents( $svg_path );
				}

				if ( ! $svg_content ) {
					return new \WP_Error( 'error', 'SVG file not found', array( 'status' => 400 ) );
				}

				if ( isset( $options[1] ) && 'viewBox' === $options[1] ) {
					$svg_attributes = \Cwicly\Helpers::get_svg_attributes( $svg_content );
					if ( $svg_attributes ) {
						return $svg_attributes;
					}
				}
				return \Cwicly\Helpers::get_svg_content( $svg_content )[ $options[1] ];
			} elseif ( is_numeric( $fallback ) && 'cwicly/image' === $block_name ) {
				return wp_get_attachment_url( $fallback );
			} else {
				return $fallback;
			}
		}
	}
}
