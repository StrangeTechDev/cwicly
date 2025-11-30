<?php
/**
 * Helpers class.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * Helpers class.
 *
 * @package cwicly
 */
class Helpers {

	/**
	 * Checks if the current request is a WP REST API request.
	 *
	 * Case #1: After WP_REST_Request initialisation
	 * Case #2: Support "plain" permalink settings and check if `rest_route` starts with `/`
	 * Case #3: It can happen that WP_Rewrite is not yet initialized,
	 *          so do this (wp-settings.php)
	 * Case #4: URL Path begins with wp-json/ (your REST prefix)
	 *          Also supports WP installations in subfolders
	 *
	 * @returns boolean
	 * @author matzeeable
	 */
	public static function is_rest() {
		if (
		defined( 'REST_REQUEST' ) && REST_REQUEST// (#1)
		|| isset( $_GET['rest_route'] ) // (#2)
		&& strpos( $_GET['rest_route'], '/', 0 ) === 0
		) {
			return true;
		}

		// (#3)
		global $wp_rewrite;
		if ( null === $wp_rewrite ) {
			$wp_rewrite = new \WP_Rewrite();
		}

		// (#4)
		$rest_url    = wp_parse_url( trailingslashit( rest_url() ) );
		$current_url = wp_parse_url( add_query_arg( array() ) );

		// Add check for 'path' key.
		if ( isset( $rest_url['path'] ) && isset( $current_url['path'] ) ) {
			return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
		}

		return false;
	}

	/**
	 * Add admin menu item to existing Cwicly menu
	 *
	 * @param array  $args   Array of arguments for the menu item.
	 * @param string $check The ID of the menu item to check for.
	 * @param int    $priority The priority of the action.
	 */
	public static function add_admin_menu_item( $args, $check = '', $priority = 99 ) {
		add_action(
			'admin_bar_menu',
			function ( $wp_admin_bar ) use ( $args, $check ) {
				$checker = false;
				if ( $check ) {
					$checker = $wp_admin_bar->get_node( $check );
				}
				if ( ! $checker ) {
					$wp_admin_bar->add_node( $args );
				}
			},
			$priority
		);
	}

	/**
	 * Get the current user role of the logged in user
	 */
	public static function get_current_user_roles() {
		if ( is_user_logged_in() ) {
			$user  = wp_get_current_user();
			$roles = (array) $user->roles;
			return $roles; // This returns an array.
		}
		return array();
	}

	/**
	 * Get correct server address
	 * https://stackoverflow.com/questions/5705082/is-serverserver-addr-safe-to-rely-on
	 *
	 * @return string
	 */
	public static function get_server_address() {
		if ( array_key_exists( 'SERVER_ADDR', $_SERVER ) ) {
			return $_SERVER['SERVER_ADDR'];
		} elseif ( array_key_exists( 'LOCAL_ADDR', $_SERVER ) ) {
			return $_SERVER['LOCAL_ADDR'];
		} elseif ( array_key_exists( 'SERVER_NAME', $_SERVER ) ) {
			return gethostbyname( $_SERVER['SERVER_NAME'] );
		}

		// Running CLI.
		if ( stristr( PHP_OS, 'WIN' ) ) {
			return gethostbyname( php_uname( 'n' ) );
		} else {
			$ifconfig = shell_exec( '/sbin/ifconfig eth0' );
			preg_match( '/addr:([\d\.]+)/', $ifconfig, $match );
			return $match[1];
		}
	}

	/**
	 * Allows us to write to log safely
	 *
	 * @param mixed $log Log to write.
	 */
	public static function write_log( $log ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}

	/**
	 * Function for checking if there is an array of needles inside string.
	 *
	 * @param string $str String to search in.
	 * @param array  $arr Array of needles.
	 */
	public static function strposa( $str, array $arr ) {
		if ( stripos( wp_json_encode( $arr ), $str ) !== false ) {
			return true;
		}
		return false;
	}

	/**
	 * Function for checking if a value exists even if equals 0.
	 *
	 * @param mixed $value Value to check.
	 */
	public static function check_if_exists( $value ) {
		if ( isset( $value ) && ( 0 === $value || '0' === $value || ! empty( $value ) ) && '' !== $value ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if the post is in one of the categories or any child category. // Thanks to https://wordpress.stackexchange.com/a/375247
	 *
	 * @param  int|string|array $category_ids (Single category id) or (comma separated string or array of category ids).
	 * @param  int              $post_id      Post ID to check. Default to `get_the_ID()`.
	 * @return bool true, iff post is in any category or child category.
	 */
	public static function cc_is_post_in_category( $category_ids, $post_id = null ) {
		$args = array(
			'include'  => $post_id ?? get_the_ID(),
			'category' => $category_ids,
			'fields'   => 'ids',
		);
		return 0 < count( get_posts( $args ) );
	}

	/**
	 * Function to create and sort Additional Classes
	 *
	 * @param array $attributes Attributes from block.
	 */
	public static function additional_classes( $attributes ) {
		$classes                = '';
		$additional_classes_ref = json_decode( CC_CLASSES );
		$additional_classes     = array();
		if ( isset( $attributes['additionalClass'] ) && $attributes['additionalClass'] ) {
			foreach ( $attributes['additionalClass'] as $value ) {
				if ( is_array( $value ) ) {
					if ( ( isset( $value['visibility'] ) && ! $value['visibility'] ) || ! isset( $value['visibility'] ) ) {
						if ( $value['isLinked'] ) {
							array_push( $additional_classes, $value['value'] );
						} elseif ( isset( $additional_classes_ref->{$value['value']} ) ) {
							array_push( $additional_classes, $additional_classes_ref->{$value['value']} );
						}
					}
				}
			}
		}
		$classes .= '' . implode( ' ', $additional_classes ) . '';
		return $classes;
	}

	/**
	 * Function to create and sort Global Classes from the Site Options
	 *
	 * @param array $attributes Attributes from block.
	 */
	public static function global_classes( $attributes ) {
		$classes = '';
		$globals = get_option( 'cwicly_global_classes' );
		if ( $globals ) {
			$globals = json_decode( $globals, true );
		} else {
			return;
		}
		$global_classes = array();
		if ( isset( $attributes['globalClass'] ) && $attributes['globalClass'] ) {
			foreach ( $attributes['globalClass'] as $value ) {
				if ( isset( $globals ) && $globals && isset( $globals[ $value ] ) && $globals[ $value ] && isset( $globals[ $value ]['attributes'] ) && $globals[ $value ]['attributes'] && isset( $globals[ $value ]['attributes']['classID'] ) && $globals[ $value ]['attributes']['classID'] ) {
					array_push( $global_classes, $globals[ $value ]['attributes']['classID'] );
				}
			}
		}
		$classes .= '' . implode( ' ', $global_classes ) . '';
		return $classes;
	}

	/**
	 * Function to insert in the body the Global Interactions scripts
	 */
	public static function add_global_interactions_inline_script() {
		echo '<script id="cc-global-interactions" type="application/json">' . PHP_EOL;

		echo wp_json_encode( get_option( 'cwicly_global_interactions' ), JSON_HEX_APOS );

		echo '</script>' . PHP_EOL;
	}

	/**
	 * Function to transform ACF Field into Video embed overlay
	 *
	 * @param array  $attributes Attributes from block.
	 * @param string $field ACF Field.
	 * @param object $block Block object.
	 */
	public static function get_dynamic_video_url( $attributes, $field, $block = null ) {

		$overlay         = self::get_parameter_component_value( $field, $attributes, $block, 'videoImageOverlay', 'videoImageOverlayComp', true );
		$autoplay        = self::get_parameter_component_value( $field, $attributes, $block, 'videoAutoplay', 'videoAutoplayComp', true );
		$mute            = self::get_parameter_component_value( $field, $attributes, $block, 'videoMute', 'videoMuteComp', true );
		$loop            = self::get_parameter_component_value( $field, $attributes, $block, 'videoLoop', 'videoLoopComp', true );
		$player_controls = self::get_parameter_component_value( $field, $attributes, $block, 'videoControls', 'videoControlsComp', true );
		$modest_branding = self::get_parameter_component_value( $field, $attributes, $block, 'videoBranding', 'videoBrandingComp', true );
		$privacy         = self::get_parameter_component_value( $field, $attributes, $block, 'videoPrivacy', 'videoPrivacyComp', true );
		$related         = self::get_parameter_component_value( $field, $attributes, $block, 'videoRelated', 'videoRelatedComp', false, true );
		$start           = self::get_parameter_component_value( $field, $attributes, $block, 'videoStart' );
		$end             = self::get_parameter_component_value( $field, $attributes, $block, 'videoEnd' );

		$final = '';
		if ( strpos( $field, 'youtube' ) > 0 || strpos( $field, 'youtu.be' ) > 0 ) {
			$branding = 0;
			if ( $modest_branding ) {
				$branding = 1;
			}
			$youtube_url = 'youtube';
			if ( $privacy ) {
				$youtube_url = 'youtube-nocookie';
			}
			preg_match( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $field, $vidid );
			$embed_url = 'https://www.' . $youtube_url . '.com/embed/' . $vidid[1] . '?modestbranding=' . $branding . '';
			if ( $start ) {
				$embed_url .= '&start=' . $start . '';
			}
			if ( $end ) {
				$embed_url .= '&end=' . $end . '';
			}
			if ( $autoplay ) {
				$embed_url .= '&autoplay=1';
			}
			if ( $mute ) {
				$embed_url .= '&mute=1';
			}
			if ( $loop ) {
				$embed_url .= '&loop=1';
			}
			if ( ! $player_controls ) {
				$embed_url .= '&controls=0';
			}
			if ( false === $related ) {
				$embed_url .= '&rel=0';
			} elseif ( true === $related ) {
				$embed_url .= '&rel=1';
			}
			$final .= '<div class="cc-iframe-container">';
			if ( $overlay ) {
				$final .= '<iframe title="Video" width="560" height="315" src="' . $embed_url . '" srcdoc="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
			} else {
				$final .= '<iframe title="Video" width="560" height="315" src="' . $embed_url . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
			}
			$final .= '</div>';
		} elseif ( strpos( $field, 'vimeo' ) > 0 ) {
			preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $field, $vidid );
			$embed_url = 'https://player.vimeo.com/video/' . $vidid[3] . '?transparent=1';
			if ( $start ) {
				$embed_url .= '&#t=' . $start . '';
			}
			if ( $autoplay ) {
				$embed_url .= '&autoplay=true';
			}
			if ( $mute ) {
				$embed_url .= '&muted=1';
			}
			if ( ! $player_controls ) {
				$embed_url .= '&controls=0';
			}
			if ( $loop ) {
				$embed_url .= '&loop=1';
			}
			if ( $privacy ) {
				$embed_url .= '&dnt=1';
			}
			$final .= '<div class="cc-iframe-container">';
			if ( $overlay ) {
				$final .= '<iframe  title="Video" width="560" height="315" src="' . $embed_url . '" srcdoc="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
			} else {
				$final .= '<iframe  title="Video" width="560" height="315" src="' . $embed_url . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
			}
			$final .= '</div>';
		} elseif ( $field ) {
			$args      = array();
			$embed_url = $field;

			if ( $start || $end ) {
				$time = '';
				if ( $start ) {
					$time .= $start;
				}
				if ( $end ) {
					$time .= ',' . $end;
				}
				if ( strpos( $embed_url, '?#t=' ) === false ) {
					$embed_url .= ( strpos( $embed_url, '?' ) === false ? '?' : '&' ) . '#t=' . $time;
				}
			}

			if ( $autoplay ) {
				$args['autoplay']      = true;
				$args['data-autoplay'] = true;
			}
			if ( $mute ) {
				$args['muted'] = true;
			}
			if ( $loop ) {
				$args['loop'] = true;
			}
			if ( $player_controls ) {
				$args['controls'] = true;
			}

			$args_string = '';
			foreach ( $args as $key => $value ) {
				$args_string .= $key . '="' . $value . '" ';
			}
			if ( $overlay ) {
				$final .= '<video id="' . $attributes['id'] . '-videoe-local"></video>';
			} else {
				$final = '<video id="' . $attributes['id'] . '-videoe-local" src="' . $embed_url . '" ' . $args_string . ' controlslist="nodownload" playsinline>Sorry, your browser doesn\'t support embedded videos.</video>';
			}
		}
		return $final;
	}

	/**
	 * Function to transform ACF Field into Video embed overlay
	 *
	 * @param array  $attributes Attributes from block.
	 * @param string $field ACF Field.
	 * @param object $block Block object.
	 */
	public static function get_dynamic_video_overlay_url( $attributes, $field, $block = null ) {
		$embed_url       = '';
		$autoplay        = self::get_parameter_component_value( $field, $attributes, $block, 'videoAutoplay', 'videoAutoplayComp', true );
		$mute            = self::get_parameter_component_value( $field, $attributes, $block, 'videoMute', 'videoMuteComp', true );
		$loop            = self::get_parameter_component_value( $field, $attributes, $block, 'videoLoop', 'videoLoopComp', true );
		$player_controls = self::get_parameter_component_value( $field, $attributes, $block, 'videoControls', 'videoControlsComp', true );
		$modest_branding = self::get_parameter_component_value( $field, $attributes, $block, 'videoBranding', 'videoBrandingComp', true );
		$privacy         = self::get_parameter_component_value( $field, $attributes, $block, 'videoPrivacy', 'videoPrivacyComp', true );
		$related         = self::get_parameter_component_value( $field, $attributes, $block, 'videoRelated', 'videoRelatedComp', false, true );
		$start           = self::get_parameter_component_value( $field, $attributes, $block, 'videoStart' );
		$end             = self::get_parameter_component_value( $field, $attributes, $block, 'videoEnd' );

		if ( strpos( $field, 'youtube' ) > 0 || strpos( $field, 'youtu.be' ) > 0 ) {
			$branding = 0;
			if ( $modest_branding ) {
				$branding = 1;
			}
			$youtube_url = 'youtube';
			if ( $privacy ) {
				$youtube_url = 'youtube-nocookie';
			}
			preg_match( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $field, $vidid );
			$embed_url = 'https://www.' . $youtube_url . '.com/embed/' . $vidid[1] . '?modestbranding=' . $branding . '';
			if ( $start ) {
				$embed_url .= '&start=' . $start . '';
			}
			if ( $end ) {
				$embed_url .= '&end=' . $end . '';
			}
			if ( $autoplay ) {
				$embed_url .= '&autoplay=1';
			}
			if ( $mute ) {
				$embed_url .= '&mute=1';
			}
			if ( $loop ) {
				$embed_url .= '&loop=1';
			}
			if ( ! $player_controls ) {
				$embed_url .= '&controls=0';
			}
			if ( false === $related ) {
				$embed_url .= '&rel=0';
			} elseif ( true === $related ) {
				$embed_url .= '&rel=1';
			}
		} elseif ( strpos( $field, 'vimeo' ) > 0 ) {
			preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $field, $vidid );
			$embed_url = 'https://player.vimeo.com/video/' . $vidid[3] . '?transparent=1';
			if ( $start ) {
				$embed_url .= '&#t=' . $start . '';
			}
			if ( $autoplay ) {
				$embed_url .= '&autoplay=true';
			}
			if ( $mute ) {
				$embed_url .= '&muted=1';
			}
			if ( ! $player_controls ) {
				$embed_url .= '&controls=0';
			}
			if ( $loop ) {
				$embed_url .= '&loop=1';
			}
			if ( $privacy ) {
				$embed_url .= '&dnt=1';
			}
		} elseif ( $field ) {
			$args      = array();
			$embed_url = $field;

			if ( $autoplay ) {
				$args['autoplay']      = true;
				$args['data-autoplay'] = true;
			}
			if ( $mute ) {
				$args['muted'] = true;
			}
			if ( $loop ) {
				$args['loop'] = true;
			}
			if ( isset( $attributes['videoControls'] ) && $attributes['videoControls'] ) {
				$args['controls'] = true;
			}

			$args_string = '';
			foreach ( $args as $key => $value ) {
				$args_string .= $key . '="' . $value . '" ';
			}
			if ( $args_string ) {
				$embed_url .= ( strpos( $embed_url, '?' ) === false ? '?' : '&' ) . $args_string;
			}
		}
		return $embed_url;
	}

	/**
	 * Check if URL corresponds to the currently requested URL
	 * https://developer.wordpress.org/reference/functions/_wp_menu_item_classes_by_context/
	 *
	 * @param string $url URL to check.
	 */
	public static function is_current_url( $url ) {
		if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			global $wp_rewrite;

			$front_page_url = home_url();

			$_root_relative_current = untrailingslashit( $_SERVER['REQUEST_URI'] );

			// If it's the customize page then it will strip the query var off the URL before entering the comparison block.
			if ( is_customize_preview() ) {
				$_root_relative_current = strtok( untrailingslashit( $_SERVER['REQUEST_URI'] ), '?' );
			}

			$current_url        = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_root_relative_current );
			$raw_item_url       = strpos( $url, '#' ) ? substr( $url, 0, strpos( $url, '#' ) ) : $url;
			$item_url           = set_url_scheme( untrailingslashit( $raw_item_url ) );
			$_indexless_current = untrailingslashit( preg_replace( '/' . preg_quote( $wp_rewrite->index, '/' ) . '$/', '', $current_url ) );

			$matches = array(
				$current_url,
				urldecode( $current_url ),
				$_indexless_current,
				urldecode( $_indexless_current ),
				$_root_relative_current,
				urldecode( $_root_relative_current ),
			);

			if ( $raw_item_url && in_array( $item_url, $matches, true ) ) {
				return true;
			} elseif ( $item_url == $front_page_url && is_front_page() ) {
				return true;
			}
		}
	}

	/**
	 * Get Parent Component Property
	 *
	 * @param string $ref Reference to the property.
	 * @param object $block Block object.
	 */
	public static function get_parent_property( $ref, $block ) {
		$value = '';
		if ( isset( $block->context['componentParentProperties'] ) && is_array( $block->context['componentParentProperties'] ) ) {
			foreach ( $block->context['componentParentProperties'] as $key => $parent_property ) {
				if ( $key === $ref && ( isset( $parent_property['globalClass'] ) && $parent_property['globalClass'] ) || ( isset( $parent_property['additionalClass'] ) && $parent_property['additionalClass'] ) ) {
					$value = $parent_property;
				} elseif ( $key === $ref && isset( $parent_property['value'] ) && $parent_property['value'] ) {
					if ( isset( $parent_property['parent'] ) && $parent_property['parent'] ) {
						$value = self::get_parent_property( $parent_property['value'], $block );
						break;
					} else {
						$value = $parent_property['value'];
						break;
					}
				} elseif ( $key === $ref && isset( $parent_property['default'] ) && $parent_property['default'] ) {
					$value = $parent_property['default'];
					break;
				}
			}
		}
		return $value;
	}

	/**
	 * Get Class Defaults
	 *
	 * @param string $ref Reference to the property.
	 * @param object $block Block object.
	 */
	public static function get_component_class_defaults( $ref, $block ) {
		$value = '';
		if ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {

			$additionals = array();
			$classes     = $block->context['componentMetaProperties'][ $ref ]['default'];
			if ( isset( $classes['additionalClass'] ) && $classes['additionalClass'] ) {
				foreach ( $classes['additionalClass'] as $class ) {
					if ( isset( $class['value'] ) && $class['value'] ) {
						$additionals[] = $class['value'];
					}
				}
			}
			$value   = implode( ' ', $additionals );
			$globals = '';
			if ( isset( $classes['globalClass'] ) && $classes['globalClass'] ) {
				$globals = self::global_classes( $classes );
				if ( $value ) {
					$value .= ' ';
				}
				$value .= $globals;
			}
		}
		return $value;
	}

	/**
	 * Get Component Value
	 *
	 * @param string $ref Reference to the property.
	 * @param array  $attributes Block attributes.
	 * @param object $block Block object.
	 */
	public static function get_component_value( $ref, $attributes, $block ) {
		$value = '';
		if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
			$parameter = $block->context['componentProperties'][ $ref ];
			if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) && isset( $block->context['componentProperties'][ $ref ]['parent'] ) ) {
				$parameter_parent   = self::get_parent_property( $ref, $block );
				$parameter          = array();
				$parameter['value'] = $parameter_parent;
			}
			if ( isset( $parameter['value'] ) && $parameter['value'] && isset( $parameter['value']['maker'] ) && $parameter['value']['maker'] ) {
				if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] && ( ( ! isset( $parameter['value']['dynamic'] ) || ! $parameter['value']['dynamic'] ) ) ) {
					$value = self::get_component_option_value_from_id( $parameter['value']['maker'], $ref, $block );
				} else {
					$value = cc_parser( $parameter['value']['maker'], $attributes, $block );
				}
			} elseif ( isset( $parameter['value'] ) && $parameter['value'] && ! isset( $parameter['value']['maker'] ) ) {
				if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] ) {
					$value = self::get_component_option_value_from_id( $parameter['value'], $ref, $block );
				} else {
					$value = $parameter['value'];
				}
			} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
				if ( isset( $block->context['componentMetaProperties'][ $ref ]['type'] ) && 'options' === $block->context['componentMetaProperties'][ $ref ]['type'] && ( ! isset( $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) || ! $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) ) {
					$value = self::get_component_option_value_from_id( $block->context['componentMetaProperties'][ $ref ]['default'], $ref, $block );
				} else {
					$value = $block->context['componentMetaProperties'][ $ref ]['default'];
				}
			}
		} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
			if ( isset( $block->context['componentMetaProperties'][ $ref ]['type'] ) && 'options' === $block->context['componentMetaProperties'][ $ref ]['type'] && ( ! isset( $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) || ! $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) ) {
				$value = self::get_component_option_value_from_id( $block->context['componentMetaProperties'][ $ref ]['default'], $ref, $block );
			} else {
				$value = $block->context['componentMetaProperties'][ $ref ]['default'];
			}
		}
		return $value;
	}

	/**
	 * Get Component Value with Options properly formatted
	 *
	 * @param string $ref Reference to the property.
	 * @param array  $attributes Block attributes.
	 * @param object $block Block object.
	 */
	public static function get_component_value_with_options( $ref, $attributes, $block ) {
		$value = '';
		if ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
			if ( isset( $block->context['componentProperties'] ) && isset( $block->context['componentProperties'][ $ref ] ) ) {
				$parameter = $block->context['componentProperties'][ $ref ];
				if ( isset( $parameter['parent'] ) && isset( $parameter['value'] ) ) {
					$parameter_parent   = self::get_parent_property( $ref, $block );
					$parameter          = array();
					$parameter['value'] = $parameter_parent;
				}
				if ( isset( $parameter['value'] ) && $parameter['value'] && isset( $parameter['value']['maker'] ) && $parameter['value']['maker'] ) {
					if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] && ( ( ! isset( $parameter['value']['dynamic'] ) || ! $parameter['value']['dynamic'] ) ) ) {
						$value = self::get_component_option_value_from_id( $parameter['value']['maker'], $ref, $block );
					} else {
						$value = cc_parser( $parameter['value']['maker'], $attributes, $block );
					}
				} elseif ( isset( $parameter['value'] ) && $parameter['value'] && ! isset( $parameter['value']['maker'] ) ) {
					if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] ) {
						$value = self::get_component_option_value_from_id( $parameter['value'], $ref, $block );
					} else {
						$value = $parameter['value'];
					}
				} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
					$value = $block->context['componentMetaProperties'][ $ref ]['default'];
				}
			}
		}
		return $value;
	}

	public static function get_parameter_component_value( $ref, $attributes, $block, $param, $param_comp = null, $bool = false, $bool_value = false ) {

		$parameter = $param_comp ? $param_comp : $param;

		if ( isset( $attributes[ $parameter ] ) && $attributes[ $parameter ] ) {
			if ( strpos( $attributes[ $parameter ], '!ref=' ) !== false ) {
				preg_match( '/!ref=([\w-]+)!/', $attributes[ $parameter ], $ref );
				if ( isset( $ref[1] ) ) {
					$ref   = $ref[1];
					$value = self::get_component_value( $ref, $attributes, $block );
					if ( $bool_value ) {
						if ( 'true' === $value ) {
							return true;
						} elseif ( 'false' === $value ) {
							return false;
						} else {
							return '';
						}
					} elseif ( $bool && 'true' === $value ) {
						return true;
					} elseif ( ! $bool ) {
						return $value;
					}
				}
			} elseif ( isset( $attributes[ $parameter ] ) ) {
				if ( $bool_value ) {
					if ( true === $attributes[ $parameter ] ) {
						return true;
					} elseif ( false === $attributes[ $parameter ] ) {
						return false;
					} else {
						return '';
					}
				} elseif ( $attributes[ $parameter ] ) {
					if ( $bool ) {
						return true;
					} else {
						return $attributes[ $parameter ];
					}
				}
			}
		} elseif ( $param_comp && $param && isset( $attributes[ $param ] ) ) {
			if ( $bool_value ) {
				if ( true === $attributes[ $param ] ) {
					return true;
				} elseif ( false === $attributes[ $param ] ) {
					return false;
				} else {
					return '';
				}
			} elseif ( $attributes[ $param ] ) {
				if ( $bool ) {
					return true;
				} else {
					return $attributes[ $param ];
				}
			}
		} elseif ( $bool_value ) {
			return '';
		}

		return false;
	}

	/**
	 * Get Component Visibility
	 *
	 * @param array  $attributes Block attributes.
	 * @param object $block Block object.
	 */
	public static function get_component_visibility( $attributes, $block ) {
		$value = true;
		if ( isset( $attributes['componentConnectors'] ) && isset( $attributes['componentConnectors']['visibility'] ) && isset( $attributes['componentConnectors']['visibility']['ref'] ) ) {
			$ref   = $attributes['componentConnectors']['visibility']['ref'];
			$value = self::get_component_value( $ref, $attributes, $block );
		}
		if ( 'hidden' === $value ) {
			$value = false;
		} else {
			$value = true;
		}
		return $value;
	}

	/**
	 * Get Component Conditions
	 *
	 * @param array  $attributes Block attributes.
	 * @param object $block Block object.
	 */
	public static function get_component_conditions( $attributes, $block ) {
		$value = true;

		if ( isset( $attributes['componentConnectors'] ) && isset( $attributes['componentConnectors']['conditions'] ) && isset( $attributes['componentConnectors']['conditions']['ref'] ) ) {
			$ref             = $attributes['componentConnectors']['conditions']['ref'];
			$component_value = self::get_component_value( $ref, $attributes, $block );
			if ( isset( $component_value['conditions'] ) && $component_value['conditions'] ) {
				$hide_logged_in = cc_hide_logged_in( $component_value['conditions'] );
				$hide_guest     = cc_hide_guest( $component_value['conditions'] );

				if ( ( isset( $component_value['conditions']['hideConditionsToggle'] ) && $component_value['conditions']['hideConditionsToggle'] ) || ( $hide_guest && $hide_logged_in && cc_conditions_maker( $component_value['conditions'], $block ) ) ) {
					$value = true;
				} else {
					$value = false;
				}
			}
		}
		return $value;
	}

	/**
	 * Get Component Image Default
	 *
	 * @param object $block Block object.
	 * @param string $ref Reference to the property.
	 * @param array  $attributes Block attributes.
	 */
	public static function get_component_image_default( $block, $ref, $attributes ) {
		$value = '';
		if ( isset( $block->context['componentMetaProperties'][ $ref ]['default']['maker'] ) && $block->context['componentMetaProperties'][ $ref ]['default']['maker'] ) {
			$image = $block->context['componentMetaProperties'][ $ref ]['default']['maker'];
			if ( isset( $image['src'] ) ) {
				$value .= $image['src'];
			}

			if ( isset( $block->name ) && $block->name && isset( $attributes['imageAlt'] ) && $attributes['imageAlt'] ) {
				unset( $image['alt'] );
			}

			$count = 0;
			foreach ( $image as $key => $image_value ) {
				if ( 'src' !== $key && $image_value ) {
					++$count;
					$value .= '" ' . $key . '="' . $image_value . '';
				}
			}
		}
		return $value;
	}

	/**
	 * Get Component Option Value from ID.
	 *
	 * @param string $provided_value Value of the option.
	 * @param string $ref Reference to the property.
	 * @param object $block Block object.
	 */
	public static function get_component_option_value_from_id( $provided_value, $ref, $block ) {
		$value = '';
		if ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['options'] ) ) {
			$options = $block->context['componentMetaProperties'][ $ref ]['options'];
			foreach ( $options as $option ) {
				if ( $option['id'] === $provided_value ) {
					$value = $option['value'];
				}
			}
		}
		return $value;
	}

	/**
	 * Check if provided component variant is a group or simple.
	 *
	 * @param string $cs Component variant ID.
	 */
	public static function is_component_variant_group( $cs ) {
		if ( $cs && is_string( $cs ) ) {
			if ( strpos( $cs, 'group-' ) !== false ) {
				$cs = str_replace( 'group-', '', $cs );
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Make Component Gallery Name.
	 *
	 * @param int   $index Index of the gallery.
	 * @param array $attributes Block attributes.
	 */
	public static function component_gallery_name_maker( $index, $attributes ) {
		if ( isset( $attributes['galleries'][ $index ]['name'] ) && $attributes['galleries'][ $index ]['name'] ) {
			return preg_replace( '/\W+/', '-', strtolower( $attributes['galleries'][ $index ]['name'] ) );
		} else {
			return 'gallery-' . ( $index + 1 );
		}
	}

	/**
	 * Make Component Gallery URL.
	 *
	 *  @param string $itemer URL of the image.
	 * @param int    $index Index of the gallery.
	 * @param int    $indexer Index of the image.
	 * @param array  $attributes Block attributes.
	 */
	public static function component_gallery_url_maker( $itemer, $index, $indexer, $attributes ) {
		if ( isset( $attributes['linkWrapperActive'] ) && $attributes['linkWrapperActive'] ) {
			if ( $attributes['linkWrapperType'] ) {
				if ( 'lightbox' === $attributes['linkWrapperType'] ) {
					return 'href="{image=' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '}" data-gallery="' . $attributes['id'] . '"';
				} elseif ( 'url' === $attributes['linkWrapperType'] ) {
					if (
						$attributes['galleries'] &&
						$attributes['galleries'][ $index ] &&
						$attributes['galleries'][ $index ]['customLinks'] &&
						$attributes['galleries'][ $index ]['customLinks'][ $indexer ]
					) {
						return 'href="' . $attributes['galleries'][ $index ]['customLinks'][ $indexer ] . '"';
					}
				}
			}
		}
		return '';
	}

	/**
	 * Create Gallery Component
	 *
	 * @param array  $attributes Attributes from block.
	 * @param object $block Block object.
	 */
	public static function create_gallery_component( $attributes, $block ) {
		$output = '';

		$gallery_tag = 'div';
		if ( isset( $attributes['linkWrapperActive'] ) && $attributes['linkWrapperActive'] ) {
			$gallery_tag = 'a';
		}

		if ( 'static' === $attributes['galleryDynamic'] && isset( $attributes['galleries'] ) ) {
			$image_sizes = '';
			if ( isset( $attributes['galleryThumbnailSize'] ) && $attributes['galleryThumbnailSize'] ) {
				if ( strpos( $attributes['galleryThumbnailSize'], '!ref=' ) !== false ) {
					preg_match( '/!ref=([\w-]+)!/', $attributes['galleryThumbnailSize'], $ref );
					if ( isset( $ref[1] ) ) {
						$value     = null;
						$ref       = $ref[1];
						$parameter = isset( $block->context['componentProperties'][ $ref ] ) ? $block->context['componentProperties'][ $ref ] : '';
						if ( isset( $parameter['parent'] ) && isset( $parameter['value'] ) ) {
							$parameter_parent   = self::get_parent_property( $parameter['value'], $block );
							$parameter          = array();
							$parameter['value'] = $parameter_parent;
						}
						if ( isset( $parameter['value'] ) && $parameter['value'] && isset( $parameter['value']['maker'] ) && $parameter['value']['maker'] ) {
							if ( isset( $parameter['type'] ) && 'options' === $parameter['type'] && ( ( ! isset( $parameter['value']['dynamic'] ) || ! $parameter['value']['dynamic'] ) ) ) {
								$value = self::get_component_option_value_from_id( $parameter['value']['maker'], $ref, $block );
							} else {
								$value = cc_parser( $parameter['value']['maker'], $attributes, $block );
							}
						} elseif ( isset( $block->context['componentMetaProperties'] ) && isset( $block->context['componentMetaProperties'][ $ref ] ) && isset( $block->context['componentMetaProperties'][ $ref ]['default'] ) ) {
							if ( isset( $block->context['componentMetaProperties'][ $ref ]['type'] ) && 'options' === $block->context['componentMetaProperties'][ $ref ]['type'] && ( ! isset( $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) || ! $block->context['componentMetaProperties'][ $ref ]['isDynamic'] ) ) {
								$value = self::get_component_option_value_from_id( $block->context['componentMetaProperties'][ $ref ]['default'], $ref, $block );
							} else {
								$value = $block->context['componentMetaProperties'][ $ref ]['default'];
							}
						}

						if ( $value ) {
							$image_sizes = $value;
						}
					}
				} else {
					$image_sizes = $attributes['galleryThumbnailSize'];
				}
			}

					$lazy_load = '';
			if ( isset( $attributes['galleryLazyLoadComp'] ) && $attributes['galleryLazyLoadComp'] ) {
				if ( strpos( $attributes['galleryLazyLoadComp'], '!ref=' ) !== false ) {
					preg_match( '/!ref=([\w-]+)!/', $attributes['galleryLazyLoadComp'], $ref );
					if ( isset( $ref[1] ) ) {
						$ref   = $ref[1];
						$value = self::get_component_value( $ref, $attributes, $block );
						if ( $value && 'true' === $value ) {
							$lazy_load = ' loading="lazy"';
						}
					}
				}
			} elseif ( isset( $attributes['galleryLazyLoad'] ) && $attributes['galleryLazyLoad'] ) {
				$lazy_load = ' loading="lazy"';
			}

					$gallery_type = '';
			if ( isset( $attributes['galleryType'] ) && $attributes['galleryType'] ) {
				if ( strpos( $attributes['galleryType'], '!ref=' ) !== false ) {
					preg_match( '/!ref=([\w-]+)!/', $attributes['galleryType'], $ref );
					if ( isset( $ref[1] ) ) {
						$ref          = $ref[1];
						$value        = self::get_component_value_with_options( $ref, $attributes, $block );
						$gallery_type = $value;
					}
				} else {
					$gallery_type = $attributes['galleryType'];
				}
			}

					$title = false;
			if ( isset( $attributes['galleryTitleControlComp'] ) && $attributes['galleryTitleControlComp'] ) {
				if ( strpos( $attributes['galleryTitleControlComp'], '!ref=' ) !== false ) {
					preg_match( '/!ref=([\w-]+)!/', $attributes['galleryTitleControlComp'], $ref );
					if ( isset( $ref[1] ) ) {
						$ref   = $ref[1];
						$value = self::get_component_value( $ref, $attributes, $block );
						if ( $value && 'true' === $value ) {
							$title = true;
						}
					}
				}
			} elseif ( isset( $attributes['galleryTitleControl'] ) && $attributes['galleryTitleControl'] ) {
				$title = true;
			}

					$description = false;
			if ( isset( $attributes['galleryDescriptionControlComp'] ) && $attributes['galleryDescriptionControlComp'] ) {
				if ( strpos( $attributes['galleryDescriptionControlComp'], '!ref=' ) !== false ) {
					preg_match( '/!ref=([\w-]+)!/', $attributes['galleryDescriptionControlComp'], $ref );
					if ( isset( $ref[1] ) ) {
						$ref   = $ref[1];
						$value = self::get_component_value( $ref, $attributes, $block );
						if ( $value && 'true' === $value ) {
							$description = true;
						}
					}
				}
			} elseif ( isset( $attributes['galleryDescriptionControl'] ) && $attributes['galleryDescriptionControl'] ) {
				$description = true;
			}

					$filter = false;
			if ( isset( $attributes['galleryFilterComp'] ) && $attributes['galleryFilterComp'] ) {
				if ( strpos( $attributes['galleryFilterComp'], '!ref=' ) !== false ) {
					preg_match( '/!ref=([\w-]+)!/', $attributes['galleryFilterComp'], $ref );
					if ( isset( $ref[1] ) ) {
						$ref   = $ref[1];
						$value = self::get_component_value( $ref, $attributes, $block );
						if ( $value && 'true' === $value ) {
							$filter = true;
						}
					}
				}
			} elseif ( isset( $attributes['galleryFilter'] ) && $attributes['galleryFilter'] ) {
				$filter = true;
			}

			if ( $filter ) {
				$output .= self::create_gallery_filter_component( $attributes, $block );
			}

			$output .= '<div class="cc-gallery">';
			foreach ( $attributes['galleries'] as $index => $item ) {
				if ( isset( $item['urls'] ) ) {

					if ( is_array( $item['urls'] ) ) {
						foreach ( $item['urls'] as $indexer => $itemer ) {
							$figure_class = 'cc-gallery-card';
							if ( isset( $attributes['galleryOverlay'] ) && $attributes['galleryOverlay'] ) {
								$figure_class .= ' ' . $attributes['galleryOverlay'];
							}
							$figure_class .= ' ' . self::component_gallery_name_maker( $index, $attributes );
							if ( ! $attributes['galleryFilterAll'] && $index > 0 ) {
								$figure_class .= ' hide';
							}

							$output .= '<figure style="position: relative" class="' . $figure_class . '" data-ccgalleryname="' . self::component_gallery_name_maker( $index, $attributes ) . '">';
							$output .= '<div style="overflow: hidden; height: 100%; width: 100%; position: relative">';

							$gallery_tag_class = 'cc-gallery-lightbox';
							if ( isset( $attributes['linkWrapperActive'] ) && $attributes['linkWrapperActive'] && 'lightbox' === $attributes['linkWrapperType'] ) {
								$gallery_tag_class = 'cc-lightbox cc-gallery-lightbox';
							}

							$output .= '<' . $gallery_tag . ' class="' . $gallery_tag_class . '" ' . self::component_gallery_url_maker( $itemer, $index, $indexer, $attributes ) . '>';

							if ( 'grid' === $gallery_type ) {
								$output .= '<img style="width: ' . ( isset( $attributes['galleryOverlay'] ) && 'gallery-sunrise' === $attributes['galleryOverlay'] ? 'null' : '100%' ) . '; height: ' . ( isset( $attributes['galleryOverlay'] ) && 'gallery-sunrise' === $attributes['galleryOverlay'] ? '100%' : '100%' ) . '; object-fit: cover" src="';

								if ( $image_sizes ) {
									$output .= '{imagesrc';
								} else {
									$output .= '{image';
								}

								$output .= '=' . $attributes['galleries'][ $index ]['images'][ $indexer ];

								if ( $image_sizes ) {
									$output .= '=' . $image_sizes . '}"';
								}

								if ( ! $image_sizes ) {
									$output .= ' srcset="{imageset=' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '}"';
								}

								if ( $image_sizes ) {
									$output .= ' sizes="{imagesizes=' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '=' . $image_sizes . '}"';
								}

								$output .= '' . $lazy_load . ' alt="' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '" width="' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '" height="' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '"></img>';
							}

							if ( 'masonry' === $gallery_type ) {
								$output .= '<img style="width: 100%; height: 100%" src="';

								if ( $image_sizes ) {
									$output .= '{imagesrc';
								} else {
									$output .= '{image';
								}

								$output .= '=' . $attributes['galleries'][ $index ]['images'][ $indexer ];

								if ( $image_sizes ) {
									$output .= '=' . $image_sizes . '}"';
								}

								$output .= '" srcset="{imageset=' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '}"';

								if ( $image_sizes ) {
									$output .= ' sizes="{imagesizes=' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '=' . $image_sizes . '}"';
								}

								$output .= '' . $lazy_load . ' alt="' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '" width="' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '" height="' . $attributes['galleries'][ $index ]['images'][ $indexer ] . '"></img>';
							}

							$output .= '<figcaption>';
							if ( $title ) {
								$output .= '<p class="cc-gallery-title">';
								if ( isset( $attributes['galleries'][ $index ]['titles'][ $indexer ] ) ) {
									$output .= $attributes['galleries'][ $index ]['titles'][ $indexer ];
								}
								$output .= '</p>';
							}
							if ( $description ) {
								$output .= '<p class="cc-gallery-description">';
								if ( isset( $attributes['galleries'][ $index ]['descriptions'][ $indexer ] ) ) {
									$output .= $attributes['galleries'][ $index ]['descriptions'][ $indexer ];
								}
								$output .= '</p>';
							}
							$output .= '</figcaption>';
							$output .= '</' . $gallery_tag . '>';
							$output .= '</div>';
							$output .= '</figure>';
						}
					}
				}
			}
					$output .= '</div>';
		} elseif ( 'dynamic' === $attributes['galleryDynamic'] && 'acf' === $attributes['galleryDynamicType'] && isset( $attributes['galleryDynamicACFGroup'] ) && $attributes['galleryDynamicACFGroup'] && isset( $attributes['galleryDynamicACFField'] ) && $attributes['galleryDynamicACFField'] ) {

			$gallery_dynamic = array();

			if (
				'dynamic' === $attributes['galleryDynamic'] &&
				'acf' === $attributes['galleryDynamicType'] &&
				isset( $attributes['galleryDynamicACFGroup'] ) &&
				isset( $attributes['galleryDynamicACFField'] )
			) {
				array_push( $gallery_dynamic, 'acfgallery' );
				array_push( $gallery_dynamic, $attributes['galleryDynamicACFField'] );
				array_push( $gallery_dynamic, 'true' );

				if ( isset( $attributes['galleryDynamicACFFieldLocation'] ) ) {
					array_push( $gallery_dynamic, $attributes['galleryDynamicACFFieldLocation'] );
				}

				if (
					isset( $attributes['galleryDynamicACFFieldLocation'] ) &&
					isset( $attributes['dynamicACFFieldLocationID'] )
				) {
					array_push( $gallery_dynamic, $attributes['dynamicACFFieldLocationID'] );
				}
			}
			$gallery_dynamic_string = implode( '=', $gallery_dynamic );

			$pre    = '{' . $gallery_dynamic_string . '}';
			$output = cc_parser( $pre, $attributes, $block );
		}

		return $output;
	}

	/**
	 * Create Gallery Filter Name Component
	 *
	 * @param int   $index Index of the gallery.
	 * @param array $attributes Block attributes.
	 */
	public static function gallery_filter_name_maker( $index, $attributes ) {
		if ( isset( $attributes['galleries'][ $index ]['name'] ) && ! empty( $attributes['galleries'][ $index ]['name'] ) ) {
			return preg_replace( '/\W+/', '-', strtolower( $attributes['galleries'][ $index ]['name'] ) );
		} else {
			return 'gallery-' . ( $index + 1 );
		}
	}

	/**
	 * Create Gallery Filter Button Name Component
	 *
	 * @param int   $index Index of the gallery.
	 * @param array $attributes Block attributes.
	 */
	public static function gallery_filter_button_name_maker( $index, $attributes ) {
		if ( isset( $attributes['galleries'][ $index ]['name'] ) && ! empty( $attributes['galleries'][ $index ]['name'] ) ) {
			return $attributes['galleries'][ $index ]['name'];
		} else {
			return 'Gallery n°' . ( $index + 1 );
		}
	}

	/**
	 * Create Gallery Filter Component
	 *
	 * @param array  $attributes Attributes from block.
	 * @param object $block Block object.
	 */
	public static function create_gallery_filter_component( $attributes, $block ) {
		$output = '<div class="cc-gallery-buttons-container">';
		if ( $attributes['galleryFilterAll'] ) {
			$output .= '<button id="all-{class}{idadd}" class="cc-gallery-button-filter active" data-ccgalleryid="' . $attributes['id'] . '" data-ccfilter="all">';
			$output .= isset( $attributes['galleryFilterAllLabel'] ) && $attributes['galleryFilterAllLabel'] ? $attributes['galleryFilterAllLabel'] : __( 'All', 'cwicly' );
			$output .= '</button>';
		}

		if ( $attributes['galleries'] ) {
			foreach ( $attributes['galleries'] as $index => $item ) {
				$output .= '<button class="cc-gallery-button-filter' . ( ! $attributes['galleryFilterAll'] && 0 === $index ? ' active' : '' ) . '" data-ccgalleryid="' . $attributes['id'] . '" data-ccfilter="' . self::gallery_filter_name_maker( $index, $attributes ) . '">';
				$output .= self::gallery_filter_button_name_maker( $index, $attributes );
				$output .= '</button>';
			}
		}
		$output .= '</div>';
		return $output;
	}

	/**
	 * Create Video Component
	 *
	 * @param array  $property Property of the component.
	 * @param array  $attributes Attributes from block.
	 * @param object $block Block object.
	 * @param bool   $embed Embed or not.
	 */
	public static function create_video_component( $property, $attributes, $block, $embed = false ) {
		if ( isset( $property['parse'] ) && $property['parse'] ) {
			if ( isset( $property['video']['videoType'] ) && 'static' === $property['video']['videoType'] ) {
				$parsed = cc_parser( $property['parse'], $attributes, $block );
				if ( $embed ) {
					return self::get_dynamic_video_overlay_url( $attributes, $parsed, $block );
				} else {
					return self::get_dynamic_video_url( $attributes, $parsed, $block );
				}
			} else {
				$new    = str_replace( '=isVideo', '', $property['parse'] );
				$parsed = cc_parser( $new, $attributes, $block );

				if ( $embed ) {
					return self::get_dynamic_video_overlay_url( $attributes, $parsed, $block );
				} else {
					return self::get_dynamic_video_url( $attributes, $parsed, $block );
				}
			}
		}
	}

	/**
	 * Extract necessary context from block content
	 *
	 * @param array $content Block context.
	 */
	public static function extract_necessary_context( $content ) {
		$extractable = array(
			'isComponent',
			'componentProperties',
			'componentParentProperties',
			'componentParentClasses',
			'componentVariant',
			'componentVariants',
			'componentVariantGroups',
			'componentMetaProperties',
			'componentParentMetaProperties',
			'rendered',
			'componentIndex',
			'componentVariantClasses',
			'componentVariantStyles',
			'componentInnerBlocks',
			'termQuery',
		);

		$context = array();

		foreach ( $extractable as $key ) {
			if ( isset( $content[ $key ] ) ) {
				$context[ $key ] = $content[ $key ];
			}
		}

		return $context;
	}

	/**
	 * Return block conditions check
	 *
	 * @param array  $attributes Block attributes.
	 * @param object $block Block object.
	 */
	public static function block_conditions_check( $attributes, $block ) {
		$hide_logged_in       = cc_hide_logged_in( $attributes );
		$hide_guest           = cc_hide_guest( $attributes );
		$component_visibility = self::get_component_visibility( $attributes, $block );
		$component_conditions = self::get_component_conditions( $attributes, $block );

		if ( $component_visibility && $component_conditions && ( ( isset( $attributes['hideConditionsToggle'] ) && $attributes['hideConditionsToggle'] ) || ( $hide_guest && $hide_logged_in && cc_conditions_maker( $attributes, $block ) ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Make tag out of block attributes
	 *
	 * @param array  $attributes Block attributes.
	 * @param object $block Block object.
	 * @param bool   $open Open or close tag.
	 */
	public static function tag_maker( $attributes, $block, $open = false ) {
		$custom_tag = '';
		if ( isset( $attributes['linkWrapperActive'] ) && $attributes['linkWrapperActive'] ) {
			if ( isset( $attributes['containerLayoutTag'] ) && $attributes['containerLayoutTag'] && ( 'a' === $attributes['containerLayoutTag'] || 'button' === $attributes['containerLayoutTag'] ) ) {
				if ( $open ) {
					$custom_tag = $attributes['containerLayoutTag'];
				} else {
					$custom_tag = '</' . $attributes['containerLayoutTag'] . '>';
				}
			} elseif ( $open ) {
				$custom_tag = 'a';
			} else {
				$custom_tag = '</a>';
			}
		} elseif ( isset( $attributes['headingTag'] ) && $attributes['headingTag'] ) {
			if ( $open ) {
				$custom_tag = $attributes['headingTag'];
			} else {
				$custom_tag = '</' . $attributes['headingTag'] . '>';
			}
		} elseif ( isset( $attributes['containerLayoutTag'] ) && $attributes['containerLayoutTag'] ) {
			if ( $open ) {
				$custom_tag = $attributes['containerLayoutTag'];
			} else {
				$custom_tag = '</' . $attributes['containerLayoutTag'] . '>';
			}
		} elseif ( isset( $block->name ) && $block->name && 'cwicly/section' === $block->name ) {
			if ( $open ) {
				$custom_tag = 'section';
			} else {
				$custom_tag = '</section>';
			}
		} elseif ( $open ) {
			$custom_tag = 'div';
		} else {
			$custom_tag = '</div>';
		}

		return $custom_tag;
	}

	/**
	 * Query Prev-Next linker
	 *
	 * @param object $block The block object.
	 * @param string $type The type of link to create.
	 */
	public static function block_query_prev_next( $block, $type ) {
		if ( ! is_admin() ) {
			$content = '';

			$page = 0;
			if ( isset( $block->context['queryPageKey'] ) && $block->context['queryPageKey'] ) {
				$page = empty( $_GET[ $block->context['queryPageKey'] ] ) ? 1 : (int) $_GET[ $block->context['queryPageKey'] ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
			$total = 0;
			if ( isset( $block->context['queryTotal'] ) ) {
				$total = $block->context['queryTotal'];
			}

			if ( 'prev' === $type ) {
				if ( isset( $block->context['queryInherit'] ) && $block->context['queryInherit'] ) {
					global $paged;
					if ( 0 === $paged ) {
					} else {
						$content = previous_posts( false );
					}
				} elseif ( $page - 1 > 0 ) {
					$content = esc_url( add_query_arg( $block->context['queryPageKey'], $page - 1 ) );
				}
			} elseif ( 'next' === $type ) {
				if ( isset( $block->context['queryInherit'] ) && $block->context['queryInherit'] ) {
					if ( $total ) {
						$content = next_posts( $total, false );
					}
				} else {
					$nextpage = (int) $page + 1;
					if ( $nextpage <= $total ) {
						$content = esc_url( add_query_arg( $block->context['queryPageKey'], $page + 1 ) );
					}
				}
			}

			return $content;
		}
	}

	/**
	 * Echo function
	 *
	 * @param string $input Input.
	 * @return string
	 */
	public static function echo( $input ) {
		$callback = $input;
		$args     = array();

		if ( strpos( $callback, '(' ) !== false ) {
			$openstrpos  = strpos( $callback, '(' );
			$closestrpos = strrpos( $callback, ')' );
			$match       = substr( $callback, $openstrpos + 1, $closestrpos - $openstrpos - 1 );

			$args = explode( ',', $match );

			if ( count( $args ) > 0 ) {
				foreach ( $args as $key => $arg ) {
					$str = trim( $arg );
					if ( ! empty( $str ) ) {
						if ( $str[0] == '\'' && $str[ strlen( $str ) - 1 ] == '\'' ) {
							$str          = substr( $str, 1, -1 );
							$args[ $key ] = $str;
						} elseif ( is_int( $str ) ) {
							$args[ $key ] = (int) $str;
						} elseif ( is_float( $str ) ) {
							$args[ $key ] = (float) $str;
						} elseif ( is_bool( $str ) ) {
							$args[ $key ] = (bool) $str;
						} elseif ( strpos( $str, '(' ) !== false ) {
							$args[ $key ] = self::echo( $str );
						}
					}
				}
			}

			$callback = strtok( $callback, '(' );
			$callback = trim( $callback );

		} else {
			$callback = trim( $callback );
		}

		try {
			if ( 'echo' === $callback ) {
				return $args[0];
			} else {
				return function_exists( $callback ) ? call_user_func_array( $callback, $args ) : '';
			}
		} catch ( \Exception $error ) {
			error_log( 'Exception: ' . print_r( $error->getMessage(), true ) );
		} catch ( \ParseError $error ) {
			error_log( 'ParseError: ' . print_r( $error->getMessage(), true ) );
		} catch ( \Error $error ) {
			error_log( 'Error: ' . print_r( $error->getMessage(), true ) );
		}

		return '';
	}

	/**
	 * Filter Args
	 *
	 * @param array $taxonomies Taxonomies.
	 * @param array $includes Includes.
	 * @param array $excludes Excludes.
	 * @param array $parents Parents.
	 * @param array $orderbys Orderbys.
	 * @param array $orders Orders.
	 * @param bool  $childlesss Childless.
	 * @param bool  $hide_emptys Hide emptys.
	 */
	public static function filter_args_maker(
	$taxonomies,
	$includes,
	$excludes,
	$parents,
	$orderbys,
	$orders,
	$childlesss,
	$hide_emptys
	) {
		$args = array();

		$taxonomy   = self::filter_array( $taxonomies );
		$include    = self::filter_array( $includes );
		$exclude    = self::filter_array( $excludes );
		$parent     = self::filter_single( $parents );
		$orderby    = self::filter_single( $orderbys );
		$order      = self::filter_single( $orders );
		$childless  = $childlesss;
		$hide_empty = $hide_emptys;

		if ( $taxonomy ) {
			$args['taxonomy'] = $taxonomy;
		} else {
			$args['taxonomy'] = '';
		}
		if ( $include ) {
			$args['include'] = $include;
		}
		if ( $exclude ) {
			$args['exclude'] = $exclude;
		}
		if ( $parent ) {
			$args['parent'] = $parent;
		}
		if ( $orderby ) {
			$args['orderby'] = $orderby;
		}
		if ( $order ) {
			$args['order'] = $order;
		}
		if ( true === $childless ) {
			$args['childless'] = true;
		} else {
			$args['childless'] = false;
		}
		if ( true === $hide_empty ) {
			$args['hide_empty'] = true;
		} else {
			$args['hide_empty'] = false;
		}

		return $args;
	}

	/**
	 * Filter Array
	 *
	 * @param array $value Array.
	 */
	public static function filter_array( $value ) {
		$final = array();
		if ( $value ) {
			foreach ( $value as $select ) {
				$final[] = $select['value'];
			}
		}
		return $final;
	}

	/**
	 * Filter Single
	 *
	 * @param string $select Select.
	 */
	public static function filter_single( $select ) {
		$final = '';
		if ( $select ) {
			$final = $select;
		}
		return $final;
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @return WP_Error|bool
	 */
	public static function permissions_check() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check if a given request has access to get items as Admin
	 *
	 * @return WP_Error|bool
	 */
	public static function permissions_check_admin() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Remove content field from posts
	 *
	 * @param array  $posts Array of posts.
	 * @param object $query WP_Query object.
	 *
	 * @return array
	 */
	public static function remove_content_field( $posts, $query ) {
		if ( $query->get( 'exclude_content' ) ) {
			foreach ( $posts as &$post ) {
				unset( $post->post_content );
			}
		}

		return $posts;
	}

	/**
	 * Process excerpt for Cwicly blocks
	 *
	 * @param string   $post_excerpt Post excerpt.
	 * @param \WP_Post $post Post object.
	 */
	public static function excerpt_gutenberg( string $post_excerpt, \WP_Post $post ) {
		$cc_allowed_wrapper_blocks = array(
			'cwicly/div',
			'cwicly/section',
			'cwicly/column',
			'cwicly/columns',
		);

		$cc_allowed_inner_blocks = array(
			'cwicly/heading',
			'cwicly/paragraph',
			'cwicly/list',
			'core/paragraph',
			'core/heading',
		);

		$blocks = parse_blocks( $post->post_content );
		$output = '';
		foreach ( $blocks as $block ) {
			if ( ! empty( $block['innerBlocks'] ) ) {
				if ( in_array( $block['blockName'], $cc_allowed_wrapper_blocks, true ) ) {
					$output .= self::excerpt_render_inner_blocks( $block, $cc_allowed_inner_blocks );
					continue;
				}

				// Skip the block if it has disallowed or nested inner blocks.
				foreach ( $block['innerBlocks'] as $inner_block ) {
					if (
					! in_array( $inner_block['blockName'], $cc_allowed_inner_blocks, true ) ||
					! empty( $inner_block['innerBlocks'] )
					) {
						continue 2;
					}
				}
			}
			if ( isset( $block['attrs']['dynamic'] ) && $block['attrs']['dynamic'] && 'wordpress' === $block['attrs']['dynamic'] && isset( $block['attrs']['dynamicWordPressType'] ) && $block['attrs']['dynamicWordPressType'] && 'postexcerpt' === $block['attrs']['dynamicWordPressType'] ) { // phpcs:ignore WordPress.WP.CapitalPDangit
			} elseif ( isset( $block['attrs']['content'] ) && $block['attrs']['content'] &&
			strpos( $block['attrs']['content'], 'post_excerpt' ) !== false ) {
			} else {
				$output .= render_block( $block );
			}
		}
		if ( $post_excerpt ) {
			return $post_excerpt;
		} elseif ( $output ) {
			return $output;
		} else {
			return $post_excerpt;
		}
	}

	/**
	 * Process excerpt for Cwicly inner blocks
	 *
	 * @param array $parsed_block Parsed block.
	 * @param array $allowed_blocks Allowed blocks.
	 */
	public static function excerpt_render_inner_blocks( $parsed_block, $allowed_blocks ) {
		$output = '';

		foreach ( $parsed_block['innerBlocks'] as $inner_block ) {
			if ( ! in_array( $inner_block['blockName'], $allowed_blocks, true ) ) {
				continue;
			}

			$cc_allowed_wrapper_blocks = array(
				'cwicly/div',
				'cwicly/section',
				'cwicly/columns',
				'cwicly/column',
			);

			if ( empty( $inner_block['innerBlocks'] ) ) {
				if ( isset( $inner_block['attrs']['dynamic'] ) && $inner_block['attrs']['dynamic'] && 'wordpress' === $inner_block['attrs']['dynamic'] && isset( $inner_block['attrs']['dynamicWordPressType'] ) && $inner_block['attrs']['dynamicWordPressType'] && $inner_block['attrs']['dynamicWordPressType'] == 'postexcerpt' ) { // phpcs:ignore WordPress.WP.CapitalPDangit
				} elseif ( isset( $inner_block['attrs']['content'] ) && $inner_block['attrs']['content'] &&
				strpos( $inner_block['attrs']['content'], 'post_excerpt' ) !== false ) {
				} else {
					$output .= render_block( $inner_block );
					$output .= ' ';
				}
			} else {
				$output .= self::excerpt_render_inner_blocks( $inner_block, $cc_allowed_wrapper_blocks );
			}
		}

		return $output;
	}

	/**
	 * Get all the registered image sizes along with their dimensions
	 *
	 * @global array $_wp_additional_image_sizes
	 *
	 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
	 *
	 * @return array $image_sizes The image sizes
	 */
	public static function get_all_image_sizes() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = get_intermediate_image_sizes();

		foreach ( $default_image_sizes as $size ) {
			$image_sizes[ $size ]['width']  = intval( get_option( "{$size}_size_w" ) );
			$image_sizes[ $size ]['height'] = intval( get_option( "{$size}_size_h" ) );
			$image_sizes[ $size ]['crop']   = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
		}

		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
		}

		return $image_sizes;
	}

	/**
	 * Get ACF Group Field
	 * https://support.advancedcustomfields.com/forums/topic/how-to-get-field-data-from-a-group-inside-a-group/
	 *
	 * @param string $group Group name.
	 * @param string $field Field name.
	 * @param int    $location Location ID.
	 */
	public static function get_group_field( string $group, string $field, $location = 0 ) {
		$group_data = get_field( $group, $location );
		if ( is_array( $group_data ) && array_key_exists( $field, $group_data ) ) {
			return $group_data[ $field ];
		}
		return null;
	}

	/**
	 * Sanitise a Base64-encoded image
	 *
	 * @param string $meta_value The image path to sanitize.
	 * @param string $meta_key The meta key.
	 * @param string $object_type The object type.
	 * @param string $object_subtype The object subtype.
	 *
	 * @return string The sanitized image path on success, an error message otherwise.
	 */
	public static function sanitize_base64_image( $meta_value, $meta_key, $object_type, $object_subtype ) {
		return esc_url( $meta_value, array( 'data' ) );
	}

	/**
	 *
	 * Extract classes from a string
	 *
	 * @param string $input The string to extract classes from.
	 */
	public static function extract_classes_from_string( $input ) {
		// Define the regular expression pattern to match class names.
		$pattern = '/\.[\w-]+/';

		// Use preg_match_all to find all matches in the input string.
		preg_match_all( $pattern, $input, $matches );

		// $matches[0] contains an array of matched class names, so let's extract them.
		$classes = $matches[0];

		// Remove the leading dot (.) from each class name.
		$classes = array_map(
			function( $class ) {
				return ltrim( $class, '.' );
			},
			$classes
		);

		// Join the class names into a single string with spaces.
		$result = implode( ' ', $classes );

		return $result;
	}

	/**
	 * Add space to string if not present
	 *
	 * @param string $value The string to add space to.
	 */
	public static function add_space( $value ) {
		if ( is_string( $value ) && $value && '' !== $value && ' ' !== substr( $value, 0, 1 ) ) {
			return ' ' . $value;
		}
		return $value;
	}

	/**
	 * Construct the WordPress nav classes for a given element
	 *
	 * @param array $element The element to construct the classes for.
	 * @return array The classes for the element.
	 */
	public static function nav_classes( $element ) {
		$position = apply_filters( 'cwicly/nav/wordpress/classes_position', 'list' );

		$string_classes = isset( $element['classes'] ) && $element['classes'] ? implode( ' ', $element['classes'] ) : '';
		$string_classes = self::add_space( $string_classes );

		$list_classes = 'link' !== $position ? $string_classes : '';
		$link_classes = 'link' === $position ? $string_classes : '';

		return array(
			'list' => $list_classes,
			'link' => $link_classes,
		);
	}

	/**
	 * Get the main Cwicly breakpoint
	 *
	 * @return string The main breakpoint
	 */
	public static function get_main_breakpoint() {
		$breakpoints = get_option( 'cwicly_breakpoints_list' );
		$breakpoints = json_decode( $breakpoints, true );

		$main_breakpoint = '';

		foreach ( $breakpoints as $key => $value ) {
			if ( isset( $value['isMain'] ) && $value['isMain'] ) {
				$main_breakpoint = $key;
			}
		}

		return $main_breakpoint;
	}

	/**
	 * Get top-level parent term
	 *
	 * @param int    $term The term to get the top-level parent from.
	 * @param string $taxonomy The taxonomy of the term.
	 */
	public static function get_term_top_level_parent( $term, $taxonomy ) {

		$parent = get_term( $term, $taxonomy );

		while ( $parent && ! is_wp_error( $parent ) && '0' !== $parent->parent && 0 !== $parent->parent ) {
			$term_id = $parent->parent;
			$parent  = get_term( $term_id, $taxonomy );
		}
		return $parent;
	}

	/**
	 * Get the corresponding Tailwind classes from the Shell ID
	 *
	 * @param string $shell The shell ID.
	 */
	public static function get_shell( $shell ) {
		$shells = get_option( 'cwicly_shells' );
		$shells = json_decode( $shells, true );

		$classes = array();
		if ( isset( $shells[ $shell ] ) && isset( $shells[ $shell ]['baseClasses'] ) ) {
			foreach ( $shells[ $shell ]['baseClasses'] as $key => $value ) {
				if ( isset( $value['isShell'] ) && $value['isShell'] ) {
					$shell_classes = self::get_shell( $value['value'] );
					if ( $shell_classes ) {
						$classes[] = $shell_classes;
					}
				} elseif ( isset( $value['value'] ) && $value['value'] ) {
					$classes[] = $value['value'];
				}
			}
		}

		$result = implode( ' ', $classes );

		return $result;
	}

	/**
	 * Get the SVG content from an SVG
	 *
	 * @param string $svg The SVG to get the content from.
	 */
	public static function get_svg_content( $svg ) {
		preg_match( '/<svg(.*?)>(.*?)<\/svg>/is', $svg, $matches );

		if ( ! isset( $matches[2] ) || ! $matches[2] ) {
			return '';
		}
		$svg     = $matches[2];
		$viewbox = '';
		if ( isset( $matches[1] ) && $matches[1] ) {
			preg_match( '/viewBox="(.*?)"/is', $matches[1], $matches_viewbox );
			if ( isset( $matches_viewbox[1] ) && $matches_viewbox[1] ) {
				$viewbox = $matches_viewbox[1];
			}
		}

		$svg = trim( $svg );

		return array(
			'svg'        => $svg,
			'viewBox'    => $viewbox,
			'attributes' => self::get_svg_attributes( $matches[0], true ),
		);
	}

	/**
	 * Get the SVG Attributes
	 *
	 * @param string $svg_string The SVG to get the attributes from.
	 * @param bool   $array Whether to return an array or a string.
	 */
	public static function get_svg_attributes( $svg_string, $array = false ) {
		if ( ! $svg_string ) {
			return array();
		}

		$svg = new \DOMDocument();
		@$svg->loadHTML( $svg_string );
		$svg_element = $svg->getElementsByTagName( 'svg' )->item( 0 );

		$attributes = array();
		if ( $svg_element->hasAttributes() ) {
			foreach ( $svg_element->attributes as $attribute ) {
				$attributes[ $attribute->name ] = $attribute->value;
			}
		}
		$svg_element = $attributes;

		if ( ! $svg_element ) {
			return array();
		}

		$attributes_string = '';
		$attributes        = array();

		if ( isset( $svg_element['viewbox'] ) && $svg_element['viewbox'] ) {
			$attributes_string .= $svg_element['viewbox'] . '"';
		} elseif ( isset( $svg_element['viewBox'] ) && $svg_element['viewBox'] ) {
			$attributes_string .= $svg_element['viewBox'] . '"';
		}

		foreach ( $svg_element as $key => $attribute ) {
			if ( $array && ( 'viewbox' === $key || 'viewBox' === $key ) ) {
				$attributes['viewBox'] = $attribute;
			}

			if ( 'id' === $key || 'class' === $key || 'viewbox' === $key || 'viewBox' === $key ) {
				continue;
			}
			if ( $array ) {
				$attributes[ $key ] = $attribute;
			} else {
				if ( $attributes_string ) {
					$attributes_string .= ' ';
				}
				$attributes_string .= $key . '="' . htmlspecialchars( (string) $attribute ) . '" ';
			}
		}

		if ( $array ) {
			return $attributes;
		} else {
			$attributes_string = rtrim( $attributes_string, ' "' );

			return rtrim( $attributes_string );
		}
	}
}
