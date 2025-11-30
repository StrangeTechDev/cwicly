<?php
/**
 * Ground helpers.
 *
 * @package cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.


/**
 * Make Global Stylesheets.
 *
 * @param string $data Data.
 */
function cc_make_global_stylesheets( $data ) {
	try {
		// CSS.
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$css = '';
		if ( $data ) {
			$css = $data;
			if ( 'empty' === $css ) {
				$css = '';
			}
			update_option( 'cwicly_global_stylesheets_rendered', $css );

			$filename = 'cc-global-stylesheets.css';

			$upload_dir = wp_upload_dir();
			$dir        = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/';

			WP_Filesystem( false, $upload_dir['basedir'], true );

			if ( ! $wp_filesystem->is_dir( $dir ) ) {
				$wp_filesystem->mkdir( $dir );
			}

			file_put_contents( $dir . $filename, $css );
		}
		return array(
			'success' => true,
			'message' => 'Updated.',
		);
	} catch ( Exception $e ) {
		return array(
			'success' => false,
			'message' => $e->getMessage(),
		);
	}
}

/**
 * Make Global CSS.
 *
 * @param string $data Data.
 */
function cc_make_global_css( $data ) {
	try {

		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$option      = get_option( 'cwicly_breakpoints_list' );
		$breakpoints = json_decode( $option, true );

		$main_breakpoint = 'lg';

		$css        = array();
		$common     = '';
		$font       = '';
		$responsive = array();

		if ( $data ) {
			$css = $data;
		}
		if ( $css ) {
			if ( $css['common'] ) {
				$common = $css['common'];
			}
			if ( $css['font'] ) {
				$font = $css['font'];
			}

			foreach ( $breakpoints as $breakpoint => $value ) {
				$responsive[ $breakpoint ] = array();
				if ( isset( $value['isMain'] ) && $value['isMain'] ) {
					$main_breakpoint = $breakpoint;
				}
			}

			foreach ( $css as $key => $inner_css ) {
				if ( 'common' === $key || 'font' === $key ) {
					continue;
				}
				$responsive[ $key ] = $inner_css;
			}
		}

		$filename = 'cc-global-classes.css';

		$upload_dir = wp_upload_dir();
		$dir        = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/';

		WP_Filesystem( false, $upload_dir['basedir'], true );

		if ( ! $wp_filesystem->is_dir( $dir ) ) {
			$wp_filesystem->mkdir( $dir );
		}

		$responsive_content = '';

		if ( isset( $responsive[ $main_breakpoint ] ) && $responsive[ $main_breakpoint ] ) {
			$responsive_content .= $responsive[ $main_breakpoint ];
		}

		$is_main_index = array_search( $main_breakpoint, array_keys( $breakpoints ), true );

		$min_widths = array();
		$max_widths = array();

		foreach ( $breakpoints as $key => $breakpoint ) {

			if ( isset( $breakpoint['isMain'] ) && $breakpoint['isMain'] ) {
				continue;
			}

			$type = 'max';

			if ( array_search( $key, array_keys( $breakpoints ), true ) < $is_main_index ) {
				$type = 'min';
			}

			if ( 'min' === $type ) {
				$min_widths[ $breakpoint['width'] ] = $responsive[ $key ];
			}
			if ( 'max' === $type ) {
				$max_widths[ $breakpoint['width'] ] = $responsive[ $key ];
			}
		}

		ksort( $min_widths );

		foreach ( $min_widths as $width => $content ) {
			if ( $content ) {
				$responsive_content .= '@media screen and (min-width: ' . $width . 'px){' . $content . '}';
			}
		}

		krsort( $max_widths );

		foreach ( $max_widths as $width => $content ) {
			if ( $content ) {
				$responsive_content .= '@media screen and (max-width: ' . $width . 'px){' . $content . '}';
			}
		}

		file_put_contents( $dir . $filename, $font . $common . $responsive_content );

		return array(
			'success' => true,
			'message' => 'Updated.',
		);
	} catch ( Exception $e ) {
		return array(
			'success' => false,
			'message' => $e->getMessage(),
		);
	}
}

/**
 * Make Global Stylesheets.
 *
 * @param string $data Data.
 */
function cc_make_tailwind_stylesheet( $css ) {
	try {
		// CSS.
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

			$filename = 'cc-tailwind.css';

			$upload_dir = wp_upload_dir();
			$dir        = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/';

			WP_Filesystem( false, $upload_dir['basedir'], true );

		if ( ! $wp_filesystem->is_dir( $dir ) ) {
			$wp_filesystem->mkdir( $dir );
		}

		if ( $css ) {
			file_put_contents( $dir . $filename, $css );
		} elseif ( file_exists( $dir . $filename ) ) {
				unlink( $dir . $filename );
		}

		return array(
			'success' => true,
			'message' => 'Updated.',
		);
	} catch ( Exception $e ) {
		return array(
			'success' => false,
			'message' => $e->getMessage(),
		);
	}
}
