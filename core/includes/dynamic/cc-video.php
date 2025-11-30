<?php
/**
 * Cwicly Video
 *
 * Functions for creating and managing Videos
 *
 * @package Cwicly\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Video from ACF
 *
 * @param array  $attributes The block attributes.
 * @param string $field The block object.
 * @return string
 */
function cc_video_url( $attributes, $field ) {
	$embed_url = '';
	if ( strpos( get_field( $field ), 'youtube' ) > 0 || strpos( get_field( $field ), 'youtu.be' ) > 0 ) {
		$branding = 0;
		if ( isset( $attributes['videoBranding'] ) && $attributes['videoBranding'] ) {
			$branding = 1;
		}
		$youtube_url = 'youtube';
		if ( isset( $attributes['videoPrivacy'] ) && $attributes['videoPrivacy'] ) {
			$youtube_url = 'youtube-nocookie';
		}
		preg_match( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", get_field( $attributes['videoDynamicAcfField'] ), $vidid );
		$embed_url = 'https://www.youtube.com/embed/' . $vidid[1] . '?modestbranding=' . $branding . '';
		if ( isset( $attributes['videoStart'] ) && $attributes['videoStart'] ) {
			$embed_url .= '&start=' . $attributes['videoStart'] . '';
		}
		if ( isset( $attributes['videoEnd'] ) && $attributes['videoEnd'] ) {
			$embed_url .= '&end=' . $attributes['videoEnd'] . '';
		}
		if ( isset( $attributes['videoAutoplay'] ) && $attributes['videoAutoplay'] ) {
			$embed_url .= '&autoplay=1';
		}
		if ( isset( $attributes['videoMute'] ) && $attributes['videoMute'] ) {
			$embed_url .= '&mute=1';
		}
		if ( isset( $attributes['videoLoop'] ) && $attributes['videoLoop'] ) {
			$embed_url .= '&loop=1';
		}
		if ( isset( $attributes['videoControls'] ) && ! $attributes['videoControls'] ) {
			$embed_url .= '&controls=0';
		}
		if ( isset( $attributes['videoRelated'] ) ) {
			if ( true === $attributes['videoRelated'] ) {
				$embed_url .= '&rel=1';
			} elseif ( false === $attributes['videoRelated'] ) {
				$embed_url .= '&rel=0';
			}
		}
	} elseif ( strpos( get_field( $field ), 'vimeo' ) > 0 ) {
		preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', get_field( $attributes['videoDynamicAcfField'] ), $vidid );
		$embed_url = 'https://player.vimeo.com/video/' . $vidid[3] . '?transparent=1';
		if ( isset( $attributes['videoStart'] ) && $attributes['videoStart'] ) {
			$embed_url .= '&#t=' . $attributes['videoStart'] . '';
		}
		if ( isset( $attributes['videoAutoplay'] ) && $attributes['videoAutoplay'] ) {
			$embed_url .= '&autoplay=true';
		}
		if ( isset( $attributes['videoMute'] ) && $attributes['videoMute'] ) {
			$embed_url .= '&muted=1';
		}
		if ( isset( $attributes['videoLoop'] ) && $attributes['videoLoop'] ) {
			$embed_url .= '&loop=1';
		}
		if ( isset( $attributes['videoPrivacy'] ) && $attributes['videoPrivacy'] ) {
			$embed_url .= '&dnt=1';
		}
	} elseif ( get_field( $field ) ) {
		$embed_url = get_field( $field );
	}

	return $embed_url;
}

/**
 * Cwicly Video
 *
 * @param array $attributes The block attributes.
 * @return string
 */
function cc_video_final_maker( $attributes ) {
	$embed_url = '';
	$final     = '';
	if ( isset( $attributes['videoType'] ) && 'dynamic' === $attributes['videoType'] ) {
		if ( isset( $attributes['videoDynamicType'] ) && 'acf' === $attributes['videoDynamicType'] ) {
			if ( isset( $attributes['videoDynamicAcfGroup'] ) && $attributes['videoDynamicAcfGroup'] ) {
				if ( isset( $attributes['videoDynamicAcfField'] ) && $attributes['videoDynamicAcfField'] ) {
					if ( strpos( get_field( $attributes['videoDynamicAcfField'] ), 'youtube' ) > 0 || strpos( get_field( $attributes['videoDynamicAcfField'] ), 'youtu.be' ) > 0 ) {
						$branding = 0;
						if ( isset( $attributes['videoBranding'] ) && $attributes['videoBranding'] ) {
							$branding = 1;
						}
						$youtube_url = 'youtube';
						if ( isset( $attributes['videoPrivacy'] ) && $attributes['videoPrivacy'] ) {
							$youtube_url = 'youtube-nocookie';
						}
						preg_match( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", get_field( $attributes['videoDynamicAcfField'] ), $vidid );
						$embed_url = 'https://www.youtube.com/embed/' . $vidid[1] . '?modestbranding=' . $branding . '';
						if ( isset( $attributes['videoStart'] ) && $attributes['videoStart'] ) {
							$embed_url .= '&start=' . $attributes['videoStart'] . '';
						}
						if ( isset( $attributes['videoEnd'] ) && $attributes['videoEnd'] ) {
							$embed_url .= '&end=' . $attributes['videoEnd'] . '';
						}
						if ( isset( $attributes['videoAutoplay'] ) && $attributes['videoAutoplay'] ) {
							$embed_url .= '&autoplay=1';
						}
						if ( isset( $attributes['videoMute'] ) && $attributes['videoMute'] ) {
							$embed_url .= '&mute=1';
						}
						if ( isset( $attributes['videoLoop'] ) && $attributes['videoLoop'] ) {
							$embed_url .= '&loop=1';
						}
						if ( isset( $attributes['videoControls'] ) && ! $attributes['videoControls'] ) {
							$embed_url .= '&controls=0';
						}
						if ( isset( $attributes['videoRelated'] ) ) {
							if ( true === $attributes['videoRelated'] ) {
								$embed_url .= '&rel=1';
							} elseif ( false === $attributes['videoRelated'] ) {
								$embed_url .= '&rel=0';
							}
						}
						$final .= '<div class="cc-iframe-container">';
						if ( isset( $attributes['videoImageOverlay'] ) && $attributes['videoImageOverlay'] ) {
							$final .= '<iframe title="Video" width="560" height="315" src="' . $embed_url . '" srcdoc="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
						} else {
							$final .= '<iframe title="Video" width="560" height="315" src="' . $embed_url . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
						}
						$final .= '</div>';
					} elseif ( strpos( get_field( $attributes['videoDynamicAcfField'] ), 'vimeo' ) > 0 ) {
						preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', get_field( $attributes['videoDynamicAcfField'] ), $vidid );
						$embed_url = 'https://player.vimeo.com/video/' . $vidid[3] . '?transparent=1';
						if ( isset( $attributes['videoStart'] ) && $attributes['videoStart'] ) {
							$embed_url .= '&#t=' . $attributes['videoStart'] . '';
						}
						if ( isset( $attributes['videoAutoplay'] ) && $attributes['videoAutoplay'] ) {
							$embed_url .= '&autoplay=true';
						}
						if ( isset( $attributes['videoMute'] ) && $attributes['videoMute'] ) {
							$embed_url .= '&muted=1';
						}
						if ( isset( $attributes['videoLoop'] ) && $attributes['videoLoop'] ) {
							$embed_url .= '&loop=1';
						}
						if ( isset( $attributes['videoPrivacy'] ) && $attributes['videoPrivacy'] ) {
							$embed_url .= '&dnt=1';
						}
						$final .= '<div class="cc-iframe-container">';
						if ( isset( $attributes['videoImageOverlay'] ) && $attributes['videoImageOverlay'] ) {
							$final .= '<iframe title="Video" width="560" height="315" src="' . $embed_url . '" srcdoc="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
						} else {
							$final .= '<iframe title="Video" width="560" height="315" src="' . $embed_url . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
						}
						$final .= '</div>';
					} elseif ( get_field( $attributes['videoDynamicAcfField'] ) ) {
						$embed_url     = get_field( $attributes['videoDynamicAcfField'] );
						$autoplay      = '';
						$mute          = '';
						$loop          = '';
						$controls      = '';
						$data_autoplay = '';
						if ( isset( $attributes['videoAutoplay'] ) && $attributes['videoAutoplay'] ) {
							$autoplay      = 'autoplay';
							$data_autoplay = 'data-autoplay';
						}
						if ( isset( $attributes['videoMute'] ) && $attributes['videoMute'] ) {
							$mute = 'muted="muted"';
						}
						if ( isset( $attributes['videoLoop'] ) && $attributes['videoLoop'] ) {
							$loop = 'loop';
						}
						if ( isset( $attributes['videoControls'] ) && $attributes['videoControls'] ) {
							$controls = 'controls';
						}
						if ( isset( $attributes['videoImageOverlay'] ) && $attributes['videoImageOverlay'] ) {
							$final .= '<video id="' . $attributes['id'] . '-videoe-local"></video>';
						} else {
							$final = '<video id="' . $attributes['id'] . '-videoe-local" src="' . $embed_url . '" ' . $autoplay . ' ' . $loop . ' ' . $mute . ' ' . $controls . ' ' . $data_autoplay . ' controlslist="nodownload" playsinline>Sorry, your browser doesn\'t support embedded videos.</video>';
						}
					}
				}
			}
		}
	}

	return $final;
}
