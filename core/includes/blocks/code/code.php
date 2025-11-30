<?php
/**
 * Register Cwicly block.
 *
 * @package cwicly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register block render callback.
 */
register_block_type(
	__DIR__,
	array(
		'render_callback' => 'cc_code_render_callback',
	)
);

/**
 * Render callback.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 * @param object $block Block data.
 *
 * @return string
 */
function cc_code_render_callback( $attributes, $content, $block ) {
	$hide_loggedin = cc_hide_logged_in( $attributes );
	$hide_guest    = cc_hide_guest( $attributes );

	if ( ! is_admin() && $hide_guest && $hide_loggedin && cc_conditions_maker( $attributes, $block ) && isset( $attributes['uniqueID'] ) && $attributes['uniqueID'] ) {

		$repeated = false;
		if ( isset( $block->context['product_index'] ) ) {
			$repeated = true;
		} elseif ( isset( $block->context['taxterms_index'] ) ) {
			$repeated = true;
		} elseif ( isset( $block->context['repeater_row'] ) ) {
			$repeated = true;
		} elseif ( isset( $block->context['queryId'] ) && ! isset( $block->context['query_index'] ) ) {
			$repeated = true;
		} elseif ( isset( $block->context['queryId'] ) && isset( $block->context['query_index'] ) && 1 !== $block->context['query_index'] ) {
			$repeated = true;
		}

		if ( isset( $attributes['codeCSS'] ) && $attributes['codeCSS'] && ! $repeated ) {
			$customcss = str_replace( array( "\r", "\n" ), '', $attributes['codeCSS'] );
			$customcss = preg_replace( '!\s+!', ' ', $customcss );
			$customcss = cc_render( $customcss, $attributes, $block );

			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				$action = 'wp_head';
			} else {
				$action = 'wp_footer';
			}
			add_action(
				$action,
				function () use ( $customcss, $attributes ) {
					$id = isset( $attributes['id'] ) && $attributes['id'] ? 'css-' . $attributes['id'] . '' : 'css-code-' . wp_rand( 1000, 9999 );
					echo '<style id="' . $id . '">' . $customcss . '</style>' . PHP_EOL;
				}
			);
		}
		if ( isset( $attributes['codeJS'] ) && $attributes['codeJS'] && ! $repeated && isset( $attributes['codeJSSignature'] ) && \Cwicly\Signature::verify_signature( 'codeJS', $attributes['codeJS'], $attributes['codeJSSignature'] ) ) {
			$customjs = $attributes['codeJS'];
			$customjs = cc_render( $customjs, $attributes, $block );
			add_action(
				'wp_footer',
				function () use ( $customjs, $attributes ) {
					$id = isset( $attributes['id'] ) && $attributes['id'] ? 'script-' . $attributes['id'] . '' : 'script-code-' . wp_rand( 1000, 9999 );
					echo '<script id="' . $id . '" type="text/javascript">' . $customjs . '</script>' . PHP_EOL;
				}
			);
		}

		$final = '';
		if ( isset( $attributes['code'] ) && $attributes['code'] && isset( $attributes['codePHPSignature'] ) && \Cwicly\Signature::verify_signature( 'codePHP', $attributes['code'], $attributes['codePHPSignature'] ) ) {

				ob_start();

				// ERROR.
				$error_reporting = error_reporting( E_ALL );
				$display_errors  = ini_get( 'display_errors' );
				ini_set( 'display_errors', 1 );

			try {
				if ( ! \Cwicly\Capabilities::execute_eval() ) {
					$final = '';
				} else {
					$eval  = eval( ' ?>' . $attributes['code'] . '<?php ' );
					$final = ob_get_clean();
				}
			} catch ( Exception $e ) {
				wp_reset_postdata();
				ob_get_clean();
				echo 'Exception: ' . $e->getMessage();
			} catch ( ParseError $e ) {
				wp_reset_postdata();
				ob_get_clean();
				echo 'ParseError: ' . $e->getMessage();
			} catch ( Error $e ) {
				wp_reset_postdata();
				ob_get_clean();
				echo 'Error: ' . $e->getMessage();
			}

				// RESET ERROR.
				ini_set( 'display_errors', $display_errors );
				error_reporting( $error_reporting );

		}
		return cc_render( $final, $attributes, $block );
	}
}
