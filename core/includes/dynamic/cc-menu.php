<?php
/**
 * Cwicly Menu
 *
 * Functions for creating and managing Menus
 *
 * @package Cwicly\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Menu Maker
 *
 * Create Menu for Menu block
 *
 * @package Cwicly\Functions
 * @version 1.1
 *
 * @param array $attributes Menu block attributes.
 * @param array $block Menu block.
 */
function cc_menu_maker( $attributes, $block ) {
	$flat_main_nav = '';
	$menu          = '';
	if ( isset( $attributes['menuSelected'] ) && $attributes['menuSelected'] ) {
		if ( strpos( $attributes['menuSelected'], '!ref=' ) !== false ) {
			preg_match( '/!ref=([\w-]+)!/', $attributes['menuSelected'], $ref );

			if ( isset( $ref[1] ) ) {
				$ref = $ref[1];
				$pre = \Cwicly\Helpers::get_component_value( $ref, $attributes, $block );
				if ( $pre ) {
					$flat_main_nav = wp_get_nav_menu_items( $pre );
				}
			}
		} else {
			$flat_main_nav = wp_get_nav_menu_items( $attributes['menuSelected'] );
		}
	}

	$menu_layout = '';
	$main_role   = '';

	$main_breakpoint = \Cwicly\Helpers::get_main_breakpoint();

	if ( isset( $attributes['menuLayout'][ $main_breakpoint ] ) && 'horizontal' === $attributes['menuLayout'][ $main_breakpoint ] ) {
		$menu_layout = 'hor';
	} else {
		$menu_layout = 'ver';
		$main_role   = ' role="tree"';
	}

	$menu_list = '<ul class="cc-menu ' . $menu_layout . '"' . $main_role . $menu . '>';

	if ( ! function_exists( 'cc_build_tree' ) ) {
		/**
		 * Build Tree
		 *
		 * @param array $elements Menu items.
		 * @param int   $parent_id Parent ID.
		 */
		function cc_build_tree( array &$elements, $parent_id = 0 ) {
			$branch = array();
			foreach ( $elements as &$element ) {
				if ( $element->menu_item_parent == $parent_id ) {
					$children = cc_build_tree( $elements, $element->ID );
					if ( $children ) {
						$element->child = $children;
					}

					$element->has_children = 1;

					$branch[ $element->ID ] = $element;
					unset( $element );
				}
			}
			return $branch;
		}
	}

	$menu_items = '';
	if ( $flat_main_nav ) {
		$menu_items = cc_build_tree( $flat_main_nav );
	}

	if ( ! function_exists( 'create_sub_menu' ) ) {
		/**
		 * Create Sub Menu
		 *
		 * @param object $item Menu item.
		 * @param array  $attributes Menu block attributes.
		 */
		function create_sub_menu( $item, $attributes ) {
			$link  = $item->url;
			$title = $item->title;
			$id    = $item->ID;

			$blank = '';
			if ( '_blank' === $item->target ) {
				$blank = ' target="_blank"';
			}

			$xfn = '';
			if ( $item->xfn ) {
				$xfn = ' rel="' . $item->xfn . '"';
			}

			$attribute = '';
			if ( $item->post_excerpt ) {
				$attribute = ' title="' . $item->post_excerpt . '"';
			}

			$current_item = '';
			$aria_current = '';
			$role_type    = '';
			$role_ul      = '';
			$role_li      = '';

			$real_id = '';
			$owns    = '';

			$main_breakpoint = \Cwicly\Helpers::get_main_breakpoint();

			if ( isset( $attributes['menuLayout'][ $main_breakpoint ] ) && 'horizontal' === $attributes['menuLayout'][ $main_breakpoint ] ) {
			} else {
				$role_type = 'role="treeitem"';
				$role_ul   = 'role="group"';
				$real_id   = ' id="cc-menu-' . $item->ID . '"';
				$owns      = ' aria-owns="cc-menu-' . $item->ID . '"';
				$role_li   = 'role="none"';
			}

			if ( in_array( 'current-menu-item', $item->classes, true ) ) {
				$current_item = ' current';
			}

			if ( ! $current_item ) {
				$is_current = \Cwicly\Helpers::is_current_url( $link );
				if ( $is_current ) {
					$current_item = ' current';
					$aria_current = ' aria-current="page"';
				}
			}

			if ( in_array( 'current-menu-item', $item->classes, true ) ) {
				$aria_current = 'aria-current="page"';
			}

			$final = '';

			$string_classes = $item->classes ? implode( ' ', $item->classes ) : '';

			$string_classes = \Cwicly\Helpers::add_space( $string_classes );

			if ( property_exists( $item, 'child' ) ) {
				$children    = $item->child;
				$icon_before = '';
				if ( $attributes['menuSubMenuIconActive'] && 'before' === $attributes['menuSubMenuIconPosition'] && isset( $attributes['menuSubMenuIconUnicode'] ) && $attributes['menuSubMenuIconUnicode'] ) {
					$icon_before = $attributes['menuSubMenuIconUnicode'];
				}
				$icon_after = '';
				if ( $attributes['menuSubMenuIconActive'] && 'after' === $attributes['menuSubMenuIconPosition'] && isset( $attributes['menuSubMenuIconUnicode'] ) && $attributes['menuSubMenuIconUnicode'] ) {
					$icon_after = $attributes['menuSubMenuIconUnicode'];
				}

				$final .= '<li ' . $role_li . '>';
				$final .= '<a' . $owns . ' href="' . $link . '" class="cc-menu-sub menu-id-' . $id . '' . $string_classes . '" aria-haspopup="true" ' . $role_type . ' aria-expanded="false" ' . $attribute . $blank . $xfn . '>' . $icon_before . '' . $title . '' . $icon_after . '</a>';
				$final .= '<ul' . $real_id . ' data-ccdropmenu class="cc-menu-dropdown" ' . $role_ul . ' aria-label="' . $title . '">';
				foreach ( $children as $child ) {
					$final .= create_sub_menu( $child, $attributes );
				}
				$final .= '</ul>';
				$final .= '</li>';
			} else {
				$final .= '<li ' . $role_li . '>';
				$final .= '<a href="' . $link . '" class="cc-menu-sub menu-id-' . $id . '' . $current_item . '' . $string_classes . '" ' . $role_type . '' . $aria_current . $attribute . $blank . $xfn . '>' . $title . '</a>';
				$final .= '</li>';
			}
			return $final;
		}
	}

	if ( ! function_exists( 'create_menu' ) ) {
		/**
		 * Create Menu
		 *
		 * @param object $item Menu item.
		 * @param array  $attributes Menu block attributes.
		 */
		function create_menu( $item, $attributes ) {
			$link  = $item->url;
			$title = $item->title;
			$id    = $item->ID;

			$xfn = '';
			if ( $item->xfn ) {
				$xfn = ' rel="' . $item->xfn . '"';
			}

			$blank = '';
			if ( '_blank' === $item->target ) {
				$blank = ' target="_blank"';
			}

			$attribute = '';
			if ( $item->post_excerpt ) {
				$attribute = ' title="' . $item->post_excerpt . '"';
			}

			$current_item = '';
			$aria_current = '';
			$role_type    = '';
			$role_ul      = '';
			$role_li      = '';

			$real_id = '';
			$owns    = '';

			$main_breakpoint = \Cwicly\Helpers::get_main_breakpoint();

			if ( isset( $attributes['menuLayout'] ) && isset( $attributes['menuLayout'][ $main_breakpoint ] ) && 'horizontal' === $attributes['menuLayout'][ $main_breakpoint ] ) {
			} else {
				$role_type = 'role="treeitem"';
				$role_ul   = 'role="group"';
				$real_id   = ' id="cc-menu-' . $item->ID . '"';
				$owns      = ' aria-owns="cc-menu-' . $item->ID . '"';
				$role_li   = 'role="none"';
			}

			if ( in_array( 'current-menu-item', $item->classes, true ) ) {
				$current_item = ' current';
			}

			if ( in_array( 'current-page-ancestor', $item->classes, true ) ) {
				$current_item = ' current';
			}

			if ( ! $current_item ) {
				$is_current = \Cwicly\Helpers::is_current_url( $link );
				if ( $is_current ) {
					$current_item = ' current';
					$aria_current = ' aria-current="page"';
				}
			}

			if ( in_array( 'current-menu-item', $item->classes, true ) ) {
				$aria_current = ' aria-current="page"';
			}
			if ( in_array( 'current-page-ancestor', $item->classes, true ) ) {
				$aria_current = ' aria-current="page"';
			}

			$final = '';

			$string_classes = $item->classes ? implode( ' ', $item->classes ) : '';

			$string_classes = \Cwicly\Helpers::add_space( $string_classes );

			if ( property_exists( $item, 'child' ) ) {
				$children    = $item->child;
				$icon_before = '';
				if ( isset( $attributes['menuMainMenuIconActive'] ) && $attributes['menuMainMenuIconActive'] && isset( $attributes['menuMainMenuIconUnicode'] ) && $attributes['menuMainMenuIconUnicode'] && 'before' === $attributes['menuMainMenuIconPosition'] ) {
					$icon_before = $attributes['menuMainMenuIconUnicode'];
				}
				$icon_after = '';
				if ( isset( $attributes['menuMainMenuIconActive'] ) && $attributes['menuMainMenuIconActive'] && 'after' === $attributes['menuMainMenuIconPosition'] && isset( $attributes['menuMainMenuIconUnicode'] ) && $attributes['menuMainMenuIconUnicode'] ) {
					$icon_after = $attributes['menuMainMenuIconUnicode'];
				}

				$final .= '<li ' . $role_li . '>';
				$final .= '<a' . $owns . ' href="' . $link . '" class="cc-menu-main menu-id-' . $id . '' . $current_item . '' . $string_classes . '" ' . $role_type . ' aria-haspopup="true" aria-expanded="false"' . $aria_current . $attribute . $blank . $xfn . '>' . $icon_before . '' . $title . '' . $icon_after . '</a>';
				$final .= '<ul ' . $real_id . ' class="cc-menu-dropdown" ' . $role_ul . ' aria-label="' . $title . '">';
				foreach ( $children as $child ) {
					$final .= create_sub_menu( $child, $attributes );
				}
				$final .= '</ul>';
				$final .= '</li>';
			} else {
				$final .= '<li ' . $role_li . '>';
				$final .= '<a href="' . $link . '" class="cc-menu-main menu-id-' . $id . '' . $current_item . '' . $string_classes . '" ' . $role_type . '' . $aria_current . $attribute . $blank . $xfn . '>' . $title . '</a>';
				$final .= '</li>';
			}
			return $final;
		}
	}

	if ( $menu_items ) {
		foreach ( $menu_items as $item ) {
			$menu_list .= create_menu( $item, $attributes );
		}
	}
	$menu_list .= '</ul>';

	return $menu_list;
}
