<?php
/**
 * NavMenu class file.
 *
 * @package Cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class NavMenu
 */
class NavMenu {

	/**
	 * NavMenu create tree.
	 *
	 * @param array  $dataset  The dataset.
	 * @param string $id  The id.
	 * @param string $relation  The relation.
	 */
	public static function create_data_tree( $dataset, $id = 'ID', $relation = 'menu_item_parent' ) {
		$hash_table = array();
		$data_tree  = array();

		foreach ( $dataset as $data ) {
			$hash_table[ $data->$id ]             = (array) $data;
			$hash_table[ $data->$id ]['children'] = array();
		}

		foreach ( $dataset as $data ) {
			if ( 0 != $data->$relation ) {
				$hash_table[ $data->$relation ]['children'][] = &$hash_table[ $data->$id ];
			} else {
				$data_tree[] = &$hash_table[ $data->$id ];
			}
		}

		return $data_tree;
	}

	/**
	 * NavMenu nav attrs.
	 *
	 * @param array $menu_item  The menu item.
	 */
	public static function nav_attrs( $menu_item ) {
		$atts           = array();
		$atts['title']  = ! empty( $menu_item['attr_title'] ) ? $menu_item['attr_title'] : '';
		$atts['target'] = ! empty( $menu_item['target'] ) ? $menu_item['target'] : '';
		if ( '_blank' === $menu_item['target'] && empty( $menu_item['xfn'] ) ) {
			$atts['rel'] = 'noopener';
		} else {
			$atts['rel'] = $menu_item['xfn'];
		}

		if ( ! empty( $menu_item['url'] ) ) {
			if ( get_privacy_policy_url() === $menu_item['url'] ) {
				$atts['rel'] = empty( $atts['rel'] ) ? 'privacy-policy' : $atts['rel'] . ' privacy-policy';
			}

			$atts['href'] = $menu_item['url'];
		} else {
			$atts['href'] = '';
		}

		$atts['aria-current'] = $menu_item['current'] ? 'page' : '';
		if ( ! $atts['aria-current'] && isset( $menu_item['url'] ) ) {
			$is_current = \Cwicly\Helpers::is_current_url( $menu_item['url'] );
			if ( $is_current ) {
				$atts['aria-current'] = 'page';
			}
		}

		/**
		 * Filters the arguments for a single nav menu item.
		 *
		 * @param stdClass $args      An object of wp_nav_menu() arguments.
		 * @param WP_Post  $menu_item Menu item data object.
		 * @param int      $depth     Depth of menu item. Used for padding.
		 */
		$args = apply_filters( 'nav_menu_item_args', null, $menu_item, 0 );

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, $args, 0 );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		return $attributes;
	}

	/**
	 * Build attributes string.
	 *
	 * @param array $attributes  The attributes.
	 */
	public static function build_attributes_string( $attributes ) {
		$result = '';

		foreach ( $attributes as $key => $value ) {
			$result .= $key . '="' . $value . '" ';
		}

		return $result;
	}

	/**
	 * NavMenu dropdown maker.
	 *
	 * @param array $element  The element.
	 * @param array $attributes  The attributes.
	 */
	public static function dropdown_maker( $element, $attributes, $link_classes ) {
		$width      = isset( $attributes['navMenuCaretSize'] )
		? floatval( $attributes['navMenuCaretSize'] ) / 2 + floatval( $attributes['navMenuCaretSize'] )
		: 16;
		$height     = isset( $attributes['navMenuCaretSize'] )
		? floatval( $attributes['navMenuCaretSize'] )
		: 12;
		$tip_radius = isset( $attributes['navMenuCaretRadius'] )
		? floatval( $attributes['navMenuCaretRadius'] )
		: 4;

		$svg_x = ( $width / 2 ) * ( $tip_radius / -8 + 1 );
		$svg_y = ( ( $height / 2 ) * $tip_radius ) / 4;

		$d_value =
			'M0,0' .
			' H' . $width .
			' L' . ( $width - $svg_x ) . ',' . ( $height - $svg_y ) .
			' Q' . ( $width / 2 ) . ',' . $height . ' ' . $svg_x . ',' . ( $height - $svg_y ) .
			' Z';

		$title_tag = isset( $element['url'] ) && $element['url']
		? ( isset( $attributes['containerLayoutTag'] ) && $attributes['containerLayoutTag'] &&
			( 'a' === $attributes['containerLayoutTag'] ||
				'button' === $attributes['containerLayoutTag'] )
			? $attributes['containerLayoutTag']
			: 'a' )
		: 'div';

		$main_button_tag =
		'a' === $title_tag || 'button' === $title_tag ? 'div' : 'button';

		$icon_tag = 'a' === $title_tag || 'button' === $title_tag ? 'button' : 'div';

		$main_button_state =
		'button' === $main_button_tag
		? array(
			'aria-expanded' => 'false',
			'aria-haspopup' => 'true',
			'aria-label' => 'Toggle Submenu',
			'is-trigger'    => 'true',
		)
		: array();

		$icon_state =
		'div' === $main_button_tag
		? array(
			'aria-expanded' => 'false',
			'aria-haspopup' => 'true',
			'aria-label' => 'Toggle Submenu',
			'is-trigger'    => 'true',
		)
		: array();

		$link = self::nav_attrs( $element );

		$output  = '<' . $main_button_tag . ' class="cc-nav-item cc-nav-dropdown__button" ' . self::build_attributes_string( $main_button_state ) . '>';
		$output .= '<' . $title_tag . ' class="cc-nav-dropdown__button--title' . $link_classes . '" ' . $link . '>';
		$output .= $element['title'];
		$output .= '</' . $title_tag . '>';

		if ( isset( $attributes['menuDropdownIconActive'] ) && $attributes['menuDropdownIconActive'] || isset( $attributes['menuModalDropdownIcon'] ) && $attributes['menuModalDropdownIcon'] ) {
			$extra = isset( $attributes['menuDropdownIconActive'] ) && $attributes['menuDropdownIconActive'] ? '' : ' cc-nav-dropdown__button--icon--modal';

			$output .= '<' . $icon_tag . ' class="cc-nav-dropdown__button--icon' . $extra . '" ' . self::build_attributes_string( $icon_state ) . '>';

			if ( isset( $attributes['menuDropdownIconActive'] ) && $attributes['menuDropdownIconActive'] && $attributes['menuDropdownIcon'] ) {
				$output .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . $attributes['menuDropdownIcon']['viewBox'] . '" class="';
				$output .= isset( $attributes['menuModalDropdownIcon'] ) && $attributes['menuModalDropdownIcon'] ? 'cc-nav-dropdown__button--icon--full' : '';
				$output .= '">';

				foreach ( $attributes['menuDropdownIcon']['paths'] as $e ) {
					if ( $e && $e['d'] ) {
						$fill    = isset( $e['fill'] ) ? $e['fill'] : '';
						$opacity = isset( $e['opacity'] ) ? $e['opacity'] : '';

						$output .= '<path d="' . $e['d'] . '" fill="' . $fill . '" opacity="' . $opacity . '"></path>';
					}
				}

				$output .= '</svg>';
			}

			if ( isset( $attributes['menuModalDropdownIcon'] ) && $attributes['menuModalDropdownIcon'] ) {
				$output .= '<div class="cc-nav-dropdown__button--title-wrapper">';
				$output .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . $attributes['menuModalDropdownIcon']['viewBox'] . '" class="cc-nav-dropdown__button--icon--modal">';

				foreach ( $attributes['menuModalDropdownIcon']['paths'] as $e ) {
					if ( $e && $e['d'] ) {
						$fill    = isset( $e['fill'] ) ? $e['fill'] : '';
						$opacity = isset( $e['opacity'] ) ? $e['opacity'] : '';

						$output .= '<path d="' . $e['d'] . '" fill="' . $fill . '" opacity="' . $opacity . '"></path>';
					}
				}

				$output .= '</svg>';
				$output .= '</div>';
			}

			$output .= '</' . $icon_tag . '>';
		}
		if ( isset( $element['url'] ) && $element['url'] && cc_attribute_checker( $attributes, 'menuDropdownIconActive', 'false' ) ) {
			$output .= '<button class="cc-nav-dropdown__button--icon-accessible" ' . self::build_attributes_string( $icon_state ) . '>';
			$output .= '<svg height="48" viewBox="0 0 9 48" width="9" xmlns="http://www.w3.org/2000/svg">
<path d="m7.4382 24.0621-6.5581-6.4238c-.2368-.2319-.2407-.6118-.0088-.8486.2324-.2373.6123-.2407.8486-.0088l7 6.8569c.1157.1138.1807.2695.1802.4316-.001.1621-.0674.3174-.1846.4297l-7 6.7242c-.1162.1118-.2661.1675-.4155.1675-.1577 0-.3149-.062-.4326-.1846-.2295-.2388-.2222-.6187.0171-.8481l6.5537-6.2959z"></path>
</svg>';
			$output .= '</button>';
		} else {
			$output .= '';
		}

		$output .= '</' . $main_button_tag . '>';

		$output .= self::dropdown_section_wrap( $element, $attributes, $width, $height, $d_value );

		return $output;
	}

	/**
	 * NavMenu dropdown section wrap.
	 *
	 * @param array  $element  The element.
	 * @param array  $attributes  The attributes.
	 * @param int    $width  The width.
	 * @param int    $height  The height.
	 * @param string $d_value  The d value.
	 */
	public static function dropdown_section_wrap( $element, $attributes, $width, $height, $d_value ) {
		$menu_title_no_tags = wp_strip_all_tags( ( $element['title'] ) );
		$content            = '';
		$content           .= "<div class='cc-nav-dropdown__content' style='position: absolute;' aria-hidden='true' menu-title='{$menu_title_no_tags}'>";

		if ( ! isset( $attributes['navMenuDropdownHideTitles'] ) || ! $attributes['navMenuDropdownHideTitles'] ) {
			$dropdown_title_tag = cc_attribute_checker( $attributes, 'navMenuDropdownTitleTag', 'true' ) ? $attributes['navMenuDropdownTitleTag'] : 'h2';
			$content           .= "<div class='cc-nav__section-header'>";
			if ( $element['url'] ) {
				$attrs    = self::nav_attrs( $element );
				$content .= "<$dropdown_title_tag class='cc-nav__section-title'><a {$attrs}>{$element['title']}</a></$dropdown_title_tag>";
			} else {
				$content .= "<$dropdown_title_tag class='cc-nav__section-title'>{$element['title']}</$dropdown_title_tag>";
			}
			$content .= '</div>';
		}
		$content .= self::dropdown_section( $element, $attributes, 'menuGroups', 'MenuGroups', 'Body' );
		$content .= self::dropdown_section( $element, $attributes, 'menuGroupsExtra', 'MenuGroupsExtra', 'Footer' );

		if ( ! isset( $attributes['menuHideCaret'] ) || ! $attributes['menuHideCaret'] ) {
			$content .= "<svg class='cc-nav-dropdown-caret' aria-hidden='true' width='{$width}' height='{$width}' viewBox='0 0 {$width} " . ( $height > $width ? $height : $width ) . "'>";
			$content .= "<path stroke='none' d='{$d_value}'></path>";
			$content .= '</svg>';
		}

		$content .= '</div>';

		return $content;
	}

	/**
	 * The main WordPress Nav maker.
	 *
	 * @param int   $id  The id.
	 * @param array $attributes  The attributes.
	 */
	public static function wp_nav_maker( $id, $attributes ) {
		$menu_items = wp_get_nav_menu_items( $id, array( 'update_post_term_cache' => false ) );
		foreach ( $menu_items as &$item ) {
			$item->is_footer  = get_post_meta( $item->ID, '_is_footer', true );
			$item->hide_title = get_post_meta( $item->ID, '_hide_title', true );
		}

		$menu_tree = self::create_data_tree( $menu_items );

		$output = '';

		if ( $menu_tree ) {
			foreach ( $menu_tree as $i => $element ) {
				$string_classes = \Cwicly\Helpers::nav_classes( $element );
				$list_classes   = $string_classes['list'];
				$link_classes   = $string_classes['link'];

				$current = $element['current'] ? ' current' : '';

				if ( $element && $element['children'] && count( $element['children'] ) > 0 ) {
					$output .= "<li class='cc-nav-dropdown$current$list_classes'>";
					$output .= self::dropdown_maker( $element, $attributes, $link_classes );
					$output .= '</li>';
				} else {
					$menu_attributes = self::nav_attrs( $element );

					if ( ! $current && isset( $element['url'] ) ) {
						$is_current = \Cwicly\Helpers::is_current_url( $element['url'] );
						if ( $is_current ) {
							$current = ' current';
						}
					}

					$output .= "<li class='cc-nav-link$current$list_classes'>";
					$output .= "<a $menu_attributes class='cc-nav-item$link_classes'>{$element['title']}</a>";
					$output .= '</li>';
				}
			}
		}

		return $output;
	}

	/**
	 * NavMenu dropdown section maker.
	 *
	 * @param array  $element  The element.
	 * @param array  $attributes  The attributes.
	 * @param string $type  The type.
	 * @param string $type_upper  The type upper.
	 * @param string $position  The position.
	 */
	public static function dropdown_section( $element, $attributes, $type, $type_upper, $position ) {
		$output = '';

		$has_footers  = false;
		$has_children = false;
		if ( $element && isset( $element['children'] ) ) {
			foreach ( $element['children'] as $child ) {
				if ( $child && $child['children'] ) {
					$has_children = true;
				}
				if ( $child && isset( $child['is_footer'] ) && 'on' === $child['is_footer'] ) {
					$has_footers = true;

					break;
				}
			}
		}

		if ( $has_children && $element['children'] && count( $element['children'] ) > 0 ) {
			$output .= '<div class="cc-nav__sublevels">';

			if ( cc_attribute_checker( $attributes, 'is' . $type_upper . 'Custom', 'false' ) && $element['children'] && count( $element['children'] ) > 0 ) {
				foreach ( $element['children'] as $index => $menu_group ) {
					if (
						( 'menuGroupsExtra' === $type && $menu_group['is_footer'] ) ||
						( 'menuGroupsExtra' !== $type && ! $menu_group['is_footer'] )
					) {
						if ( ( cc_attribute_checker( $attributes, "navMenuDropdown{$position}IsMultiLevel", 'true' ) && cc_attribute_checker( $attributes, "menuDropdown{$position}IsMultiLevel", 'false' ) ) ||
							( ( cc_attribute_checker( $attributes, "menuDropdown{$position}IsMultiLevel", 'false' ) ) && cc_attribute_checker( $attributes, "menuDropdown{$position}IsMultiLevel", 'true' ) ) ) {
							$output .= '<button class="cc-nav-item cc-nav-dropdown__button" aria-expanded="false" aria-haspopup="true" is-trigger="true" is-sublevel="true">';
							$output .= '<div class="cc-nav-dropdown__button--title">' . $menu_group['title'] . '</div>';

							if ( ( isset( $attributes['menuDropdownIconActive'] ) && $attributes['menuDropdownIconActive'] ) || ( isset( $attributes['menuModalDropdownIcon'] ) && $attributes['menuModalDropdownIcon'] ) ) {
								$output .= '<div class="cc-nav-dropdown__button--icon">';

								if ( $attributes['menuDropdownIcon'] && isset( $attributes['menuDropdownIconActive'] ) && $attributes['menuDropdownIconActive'] ) {
									$output .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . $attributes['menuDropdownIcon']['viewBox'] . '" class="cc-nav-dropdown__button--icon--full">';

									foreach ( $attributes['menuDropdownIcon']['paths'] as $entry ) {
										if ( $entry && $entry['d'] ) {
											$fill    = isset( $entry['fill'] ) ? $entry['fill'] : '';
											$opacity = isset( $entry['opacity'] ) ? $entry['opacity'] : '';

											$output .= '<path d="' . $entry['d'] . '" fill="' . $fill . '" opacity="' . $opacity . '"></path>';
										}
									}

									$output .= '</svg>';
								}

								if ( isset( $attributes['menuModalDropdownIcon'] ) && $attributes['menuModalDropdownIcon'] ) {
									$output .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . $attributes['menuModalDropdownIcon']['viewBox'] . '" class="cc-nav-dropdown__button--icon--modal">';

									foreach ( $attributes['menuModalDropdownIcon']['paths'] as $entry ) {
										if ( $entry && $entry['d'] ) {
											$fill    = isset( $entry['fill'] ) ? $entry['fill'] : '';
											$opacity = isset( $entry['opacity'] ) ? $entry['opacity'] : '';

											$output .= '<path d="' . $entry['d'] . '" fill="' . $fill . '" opacity="' . $opacity . '"></path>';
										}
									}

									$output .= '</svg>';
								}

								$output .= '</div>';
							}

							$output .= '</button>';
						}
					}
				}
			}

			$output .= '</div>';
		}

		if ( ! $has_children ) {
			if (
				count( $element['children'] ) > 0 &&
				(
					( 'menuGroupsExtra' === $type && $element['is_footer'] ) ||
					( 'menuGroupsExtra' !== $type && ! $element['is_footer'] )
				)
			) {
				$output            .= '<div class="cc-nav__section' . ( 'menuGroupsExtra' === $type ? ' cc-nav__section--footer' : '' ) . '">';
				$output            .= '<div class="cc-nav__submenu">';
				$output            .= '<ul class="cc-nav__submenu-list">';
				$menu_grouped       = array(
					'title' => $element['title'],
					'links' => array(),
				);
				$refined_menu_group = self::get_children( $element, $menu_grouped );

				foreach ( $refined_menu_group['links'] as $index => $link ) {
					$link_maker = self::nav_attrs( $link );

					$string_classes = \Cwicly\Helpers::nav_classes( $link );
					$list_classes   = $string_classes['list'];
					$link_classes   = $string_classes['link'];

					$output .= '<li class="cc-nav__submenu-item' . $list_classes . '">';
					$output .= '<a ' . $link_maker . ' class="cc-nav__submenu-item--link' . $link_classes . '">';
					$output .= '<span class="cc-nav__submenu-item--label-container">';
					if ( isset( $link['title'] ) && $link['title'] ) {
						$output .= '<span class="cc-nav__submenu-item--label">' . $link['title'] . '</span>';
					}
					if ( isset( $link['description'] ) && $link['description'] ) {
						$output .= '<p class="cc-nav__submenu-item--description">' . $link['description'] . '</p>';
					}
					$output .= '</span>';
					$output .= '</a>';
					$output .= '</li>';
				}
				$output .= '</ul>';
				$output .= '</div>';
				$output .= '</div>';
			}
		} elseif ( ( ( $element && 'menuGroupsExtra' !== $type ) || ( $has_footers && $element && 'menuGroupsExtra' === $type ) ) && $element['children'] && count( $element['children'] ) > 0 ) {
			$sublevel = '';
			if ( ( ( isset( $attributes[ 'navMenuDropdown' . $position . 'IsMultiLevel' ] ) && $attributes[ "navMenuDropdown{$position}IsMultiLevel" ] ) && ( ( isset( $attributes[ "menuDropdown{$position}IsMultiLevel" ] ) && false !== $attributes[ "menuDropdown{$position}IsMultiLevel" ] ) || ! isset( $attributes[ "menuDropdown{$position}IsMultiLevel" ] ) ) ) ||
				( ( ! isset( $attributes[ 'navMenuDropdown' . $position . 'IsMultiLevel' ] ) || ! $attributes[ 'navMenuDropdown' . $position . 'IsMultiLevel' ] ) && isset( $attributes[ "menuDropdown{$position}IsMultiLevel" ] ) && $attributes[ "menuDropdown{$position}IsMultiLevel" ] ) ) {
				$sublevel = ' sublevel="true"';
			}
			$output .= '<div class="cc-nav__section' . ( 'menuGroupsExtra' === $type ? ' cc-nav__section--footer' : '' ) . '"' . $sublevel . '>';

			$count = 0;
			if ( ( ! isset( $attributes[ "is{$type_upper}Custom" ] ) || ! $attributes[ "is{$type_upper}Custom" ] ) && $element['children'] && count( $element['children'] ) > 0 ) {
				foreach ( $element['children'] as $index => $menu_group ) {
					++$count;
					if (
						( 'menuGroupsExtra' === $type && $menu_group['is_footer'] ) ||
						( 'menuGroupsExtra' !== $type && ! $menu_group['is_footer'] )
					) {
						$link     = self::nav_attrs( $menu_group );
						$sublevel = '';
						if ( ( cc_attribute_checker( $attributes, "navMenuDropdown{$position}IsMultiLevel", 'true' ) && cc_attribute_checker( $attributes, "menuDropdown{$position}IsMultiLevel", 'false' ) ) ||
							( ( cc_attribute_checker( $attributes, "menuDropdown{$position}IsMultiLevel", 'false' ) ) && cc_attribute_checker( $attributes, "menuDropdown{$position}IsMultiLevel", 'true' ) ) ) {
							$sublevel = ' is-sublevel="true"';
						}

						$output .= '<div class="cc-nav__submenu"' . $sublevel . '>';

						if ( ! isset( $attributes['navMenuDropdownHideGroupTitles'] ) || ! $attributes['navMenuDropdownHideGroupTitles'] ) {
							if ( $link && ( ! isset( $menu_group['hide_title'] ) || ! $menu_group['hide_title'] ) ) {
								$string_classes = \Cwicly\Helpers::nav_classes( $menu_group );
								$list_classes   = $string_classes['list'];
								$link_classes   = $string_classes['link'];

								$output .= '<a ' . $link . ' class="cc-nav__submenu-header' . $list_classes . $link_classes . '">';
							} else {
								$output .= '<span class="cc-nav__submenu-header">';
							}
							if ( ! isset( $menu_group['hide_title'] ) || ! $menu_group['hide_title'] ) {
								$output .= '<span class="cc-nav__submenu-header--title">';
								$output .= $menu_group['title'] ? $menu_group['title'] : '​';
								$output .= '</span>';
							} else {
								$output .= '​';
							}
							if ( $link ) {
								$output .= '</a>';
							} else {
								$output .= '</span>';
							}
						}

						$output .= '<ul class="cc-nav__submenu-list">';
						if ( $menu_group['children'] && count( $menu_group['children'] ) > 0 ) {
							$menu_grouped       = array(
								'title' => $menu_group['title'],
								'links' => array(),
							);
							$refined_menu_group = self::get_children( $menu_group, $menu_grouped );

							foreach ( $refined_menu_group['links'] as $index => $link ) {
								$link_maker = self::nav_attrs( $link );

								$string_classes = \Cwicly\Helpers::nav_classes( $link );
								$list_classes   = $string_classes['list'];
								$link_classes   = $string_classes['link'];

								$output .= '<li class="cc-nav__submenu-item' . $list_classes . '">';
								$output .= '<a ' . $link_maker . ' class="cc-nav__submenu-item--link' . $link_classes . '">';
								$output .= '<span class="cc-nav__submenu-item--label-container">';
								if ( isset( $link['title'] ) && $link['title'] ) {
									$output .= '<span class="cc-nav__submenu-item--label">' . $link['title'] . '</span>';
								}
								if ( isset( $link['description'] ) && $link['description'] ) {
									$output .= '<p class="cc-nav__submenu-item--description">' . $link['description'] . '</p>';
								}
								$output .= '</span>';
								$output .= '</a>';
								$output .= '</li>';
							}
						}
						$output .= '</ul>';

						$output .= '</div>';

						if (
							( ( cc_attribute_checker( $attributes, 'menuGroupDividers', 'true' ) && 1 !== cc_attribute_checker( $attributes, "menuDropdown{$position}GroupDividers", 'false' ) ) || cc_attribute_checker( $attributes, "menuDropdown{$position}GroupDividers", 'true' ) )
							&& $count < count( $element['children'] ) && 1 !== count( $element['children'] ) ) {
							$output .= '<div class="cc-nav__submenu-divider"></div>';
						}
					}
				}
			}

			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * NavMenu get children.
	 *
	 * @param array $element  The element.
	 * @param array $menu_group  The menu group.
	 */
	public static function get_children( $element, $menu_group ) {
		if ( isset( $element['children'] ) && count( $element['children'] ) > 0 ) {
			foreach ( $element['children'] as $child ) {
				$menu_group['links'][] = $child;
				$menu_group            = self::get_children( $child, $menu_group );
			}
		}

		return $menu_group;
	}
}
