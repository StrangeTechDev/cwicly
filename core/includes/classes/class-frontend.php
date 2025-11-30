<?php
/**
 * Main frontend.
 *
 * @package cwicly
 */

namespace Cwicly;

use CWICLY;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * All necessary frontend functions.
 */
class Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_head', array( $this, 'darkmode_script' ), 5 );
		add_action( 'wp_head', array( $this, 'global_fonts' ) );

		// Custom Code from Settings.
		$priority = 10;
		if ( defined( 'CC_CUSTOM_CODE_PRIORITY' ) ) {
			$priority = CC_CUSTOM_CODE_PRIORITY;
		}
		add_action(
			'wp_body_open',
			array( $this, 'before_body_tag_code' ),
			$priority
		);
		add_action(
			'wp_head',
			array( $this, 'head_tag_code' ),
			$priority
		);
		add_action(
			'wp_footer',
			array( $this, 'footer_tag_code' ),
			$priority
		);
	}

	/**
	 * Enqueue necessary block assets for frontend.
	 */
	public function enqueue_block_assets() {
		// LOAD CWICLY NORMALISER.
		$url = apply_filters( 'cc_normaliser_frontend', CWICLY_DIR_URL . 'assets/css/base.css' );
		wp_enqueue_style( 'CCnorm', $url, array(), CWICLY_VERSION );
		// LOAD CWICLY NORMALISER.

		wp_enqueue_style( 'CC', CWICLY_DIR_URL . 'build/style-index.css', array( 'CCnorm' ), CWICLY_VERSION );
	}

	/**
	 * Enqueue other necessary assets for frontend.
	 */
	public function enqueue() {
		if ( ! is_admin() ) {
			wp_enqueue_script( 'CCers', CWICLY_DIR_URL . 'assets/js/ccers.min.js', null, CWICLY_VERSION, true );

			if ( CC_WOOCOMMERCE && is_product() ) {
				wp_enqueue_script( 'CCWoo', CWICLY_DIR_URL . 'assets/js/cc-woocommerce.min.js', null, CWICLY_VERSION, true );
			}
			// LOAD GLOBAL STYLES.
			if ( ! is_admin() ) {
				$global_css = get_option( 'cwicly_global_css' );
				wp_register_style( 'cc-global', false, array(), CWICLY_VERSION );
				wp_enqueue_style( 'cc-global' );

				wp_add_inline_style( 'cc-global', $global_css );
			}
			// LOAD GLOBAL STYLES.

			// LOAD GLOBAL STYLESHEET.
			if ( ! is_admin() && file_exists( wp_upload_dir()['basedir'] . '/cwicly/cc-global-stylesheets.css' ) ) {
				wp_enqueue_style( 'cc-global-stylesheets', CC_UPLOAD_URL . '/cwicly/cc-global-stylesheets.css', array(), filemtime( wp_upload_dir()['basedir'] . '/cwicly/cc-global-stylesheets.css' ) );
			}

			// LOAD TAILWIND STYLESHEET.
			$tailwind = get_option( 'cwicly_tailwind' );
			if ( ! is_admin() && file_exists( wp_upload_dir()['basedir'] . '/cwicly/cc-tailwind.css' ) && $tailwind && 'true' === $tailwind ) {
				wp_enqueue_style( 'cc-tailwind', CC_UPLOAD_URL . '/cwicly/cc-tailwind.css', array(), filemtime( wp_upload_dir()['basedir'] . '/cwicly/cc-tailwind.css' ) );
			}

			// LOAD GLOBAL CLASSES.
			if ( ! is_admin() && file_exists( wp_upload_dir()['basedir'] . '/cwicly/cc-global-classes.css' ) ) {
				wp_enqueue_style( 'cc-global-classes', CC_UPLOAD_URL . '/cwicly/cc-global-classes.css', array(), filemtime( wp_upload_dir()['basedir'] . '/cwicly/cc-global-classes.css' ) );
			}

			// LOAD POST STYLESHEET.
			$post_id = get_the_ID();

			if ( \Cwicly\WPML::is_wpml_active() ) {
				if ( apply_filters( 'cwicly/frontend/wpml/original_post_stylesheet', true ) ) {
					$post_id = \Cwicly\WPML::get_original_post_id();
				}
			}
			if ( \Cwicly\Polylang::is_polylang_active() ) {
				$post_id = \Cwicly\Polylang::get_translated_post_id();
			}
			if ( ! is_admin() && $post_id && file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $post_id . '.css' ) ) {
				wp_enqueue_style( 'cc-post-' . $post_id . '', CC_UPLOAD_URL . '/cwicly/css/cc-post-' . $post_id . '.css', array(), filemtime( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $post_id . '.css' ) );
			}

			$cwicly_optimise = get_option( 'cwicly_optimise' );
			$cwicly_defaults = 'false';
			if ( isset( $cwicly_optimise['cwiclyDefaults'] ) && 'true' === $cwicly_optimise['cwiclyDefaults'] ) {
				$cwicly_defaults = 'true';
			}
			$remove_ids_classes = 'false';
			if ( isset( $cwicly_optimise['removeIDsClasses'] ) && 'true' === $cwicly_optimise['removeIDsClasses'] ) {
				$remove_ids_classes = 'true';
			}

			$breakpoints = get_option( 'cwicly_breakpoints_list' );

			$ccers = array(
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'restBase'         => untrailingslashit( rest_url() ),
				'nonce'            => wp_create_nonce( 'wp_rest' ),
				'logoutNonce'      => wp_create_nonce( 'log-out' ),
				'loggedIn'         => is_user_logged_in() ? true : false,
				'cwiclyDefaults'   => $cwicly_defaults,
				'postID'           => get_the_ID(),
				'removeIDsClasses' => $remove_ids_classes,
				'breakpoints'      => $breakpoints,
			);

			if ( CC_WOOCOMMERCE ) {
				$ccers['woo'] = array(
					'currency'         => html_entity_decode( get_woocommerce_currency_symbol() ),
					'currencyCode'     => get_woocommerce_currency(),
					'currencyPosition' => get_option( 'woocommerce_currency_pos' ),
					'nonce'            => wp_create_nonce( 'wc_store_api' ),
					'chooseOption'     => __( 'Choose an option', 'woocommerce' ),
					'mainVariations'   => $this->woo_variationer_local(),
					'checkoutURL'      => wc_get_checkout_url(),
					'cartURL'          => wc_get_cart_url(),
				);
			}

			wp_add_inline_script(
				'CCers',
				'window.CCers = ' . wp_json_encode(
					$ccers
				),
				'before'
			);
		}

		$localfonts       = get_option( 'cwicly_local_fonts' );
		$localactivefonts = get_option( 'cwicly_local_active_fonts' );

		$global_local_fonts = get_option( 'cwicly_global_css_fonts' );
		if ( $global_local_fonts ) {
			foreach ( $global_local_fonts as $global_font ) {
				if ( str_contains( $global_font, 'google-' ) || str_contains( $global_font, 'custom-' ) ) {
					if ( isset( $localactivefonts ) && is_array( $localactivefonts ) && in_array( $global_font, $localactivefonts, true ) ) {
						if ( isset( $localfonts ) && is_array( $localfonts ) && isset( $localfonts[ $global_font ] ) && $localfonts[ $global_font ] ) {
							$css = '';
							if ( isset( $localfonts[ $global_font ]['css'] ) && $localfonts[ $global_font ]['css'] ) {
								$css = $localfonts[ $global_font ]['css'];
							} elseif ( isset( $localfonts[ $global_font ]['originalCSS'] ) && $localfonts[ $global_font ]['originalCSS'] ) {
								$css = $localfonts[ $global_font ]['originalCSS'];
							}

							// Replace {{CC_UPLOAD_URL}} with the actual upload URL.
							$css = str_replace( '{{CC_UPLOAD_URL}}', CC_UPLOAD_URL, $css );

							$font = str_replace( ' ', '-', $localfonts[ $global_font ]['family'] );
							if ( ! wp_style_is( 'cc-cf-' . $font, 'enqueued' ) ) {
								wp_register_style( 'cc-cf-' . $font, false, array(), CWICLY_VERSION );
								wp_enqueue_style( 'cc-cf-' . $font );

								wp_add_inline_style( 'cc-cf-' . $font, $css );
							}
						}
					}
				}
			}
		}

		$cwicly_global_classes = get_option( 'cwicly_global_classes' );
		if ( $cwicly_global_classes && $localactivefonts && is_array( $localactivefonts ) && count( $localactivefonts ) > 0 ) {
			$global_classes = $this->get_custom_fonts_global_classes( $cwicly_global_classes );
			foreach ( $global_classes as $global_class ) {
				if ( str_contains( $global_class, 'google-' ) || str_contains( $global_class, 'custom-' ) ) {
					if ( isset( $localactivefonts ) && is_array( $localactivefonts ) && in_array( $global_class, $localactivefonts, true ) ) {
						if ( isset( $localfonts ) && is_array( $localfonts ) && isset( $localfonts[ $global_class ] ) && $localfonts[ $global_class ] ) {
							$css = '';
							if ( isset( $localfonts[ $global_class ]['css'] ) && $localfonts[ $global_class ]['css'] ) {
								$css = $localfonts[ $global_class ]['css'];
							} elseif ( isset( $localfonts[ $global_class ]['originalCSS'] ) && $localfonts[ $global_class ]['originalCSS'] ) {
								$css = $localfonts[ $global_class ]['originalCSS'];
							}

							// Replace {{CC_UPLOAD_URL}} with the actual upload URL.
							$css = str_replace( '{{CC_UPLOAD_URL}}', CC_UPLOAD_URL, $css );

							$font = str_replace( ' ', '-', $localfonts[ $global_class ]['family'] );
							if ( ! wp_style_is( 'cc-cf-' . $font, 'enqueued' ) ) {
								wp_register_style( 'cc-cf-' . $font, false, array(), CWICLY_VERSION );
								wp_enqueue_style( 'cc-cf-' . $font );

								wp_add_inline_style( 'cc-cf-' . $font, $css );
							}
						}
					}
				}
			}
		}

		$tailwind       = get_option( 'cwicly_tailwind' );
		$tailwind_fonts = get_option( 'cwicly_tailwind_fonts' );
		if ( $tailwind && $tailwind_fonts ) {
			$tailwind_fonts = json_decode( $tailwind_fonts, true );
			if ( $tailwind_fonts ) {
				foreach ( $tailwind_fonts as $key => $value ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $tailwind_font ) {
							$font = str_replace( ' ', '', $tailwind_font );
							if ( isset( $localactivefonts ) && is_array( $localactivefonts ) ) {
								foreach ( $localactivefonts as $localactivefont ) {
									$active_font = str_replace( 'google-', '', $localactivefont );
									$active_font = str_replace( 'custom-', '', $active_font );

									$active_font = strtolower( $active_font );
									$font        = strtolower( $font );

									if ( $active_font === $font ) {
										if ( isset( $localfonts ) && is_array( $localfonts ) && isset( $localfonts[ $localactivefont ] ) && $localfonts[ $localactivefont ] ) {
											$css = '';
											if ( isset( $localfonts[ $localactivefont ]['css'] ) && $localfonts[ $localactivefont ]['css'] ) {
												$css = $localfonts[ $localactivefont ]['css'];
											} elseif ( isset( $localfonts[ $localactivefont ]['originalCSS'] ) && $localfonts[ $localactivefont ]['originalCSS'] ) {
												$css = $localfonts[ $localactivefont ]['originalCSS'];
											}

											// Replace {{CC_UPLOAD_URL}} with the actual upload URL.
											$css = str_replace( '{{CC_UPLOAD_URL}}', CC_UPLOAD_URL, $css );

											if ( ! wp_style_is( 'cc-cf-' . $font, 'enqueued' ) ) {
												wp_register_style( 'cc-cf-' . $font, false, array(), CWICLY_VERSION );
												wp_enqueue_style( 'cc-cf-' . $font );

												wp_add_inline_style( 'cc-cf-' . $font, $css );
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Get custom fonts global classes
	 *
	 * @param string $input_string Input string.
	 *
	 * @return array
	 */
	public function get_custom_fonts_global_classes( $input_string ) {
		$parsed_input  = json_decode( $input_string, true );
		$font_families = array();

		foreach ( $parsed_input as $key => $value ) {
			$attributes = $value['attributes'];
			if ( isset( $attributes['fontLocation'] ) && 'custom' === $attributes['fontLocation'] && isset( $attributes['fontFamily'] ) ) {
				array_push( $font_families, $attributes['fontFamily'] );
			}
			if ( isset( $attributes['fontFamilyExtras'] ) && isset( $attributes['fontLocationExtras'] ) && is_array( $attributes['fontLocationExtras'] ) ) {
				foreach ( $attributes['fontLocationExtras'] as $key => $font_location_extra ) {
					if ( 'custom' === $font_location_extra && isset( $attributes['fontFamilyExtras'][ $key ] ) ) {
						array_push( $font_families, $attributes['fontFamilyExtras'][ $key ] );
					}
				}
			}
		}

		return $font_families;
	}

	/**
	 * Darkmode script
	 */
	public function darkmode_script() {
		$cwicly_darkmode_selectors = get_option( 'cwicly_darkmode_selectors' );
		if ( ! $cwicly_darkmode_selectors ) {
			$cwicly_darkmode_selectors = '.dark';
		}
		?>
		<script>var dmSelectors='<?php echo esc_html( $cwicly_darkmode_selectors ); ?>';</script>
		<script src="<?php echo esc_url( CWICLY_DIR_URL . 'assets/js/darkmode/dist/darkmode.min.js' ); ?>"></script>
		<?php
	}

	/**
	 * Get global fonts
	 */
	public function global_fonts() {
		$global_fonts = get_option( 'cwicly_global_fonts' );
		if ( $global_fonts ) {
			echo wp_kses(
				$global_fonts,
				array(
					'link'  => array(
						'href' => array(),
						'rel'  => array(),
					),
					'style' => array(
						'type' => array(),
					),
				)
			);
		}
	}

	/**
	 * Get local variations
	 */
	public function woo_variationer_local() {
		global $product;
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( get_the_ID() );
		}

		if ( $product && $product->get_type() === 'variable' && $product->get_attributes() ) {
			$attributes           = array_keys( $product->get_attributes() );
			$variation_attributes = $product->get_variation_attributes();
			$default_attributes   = $product->get_default_attributes();

			foreach ( $attributes as $attribute ) {
				if ( isset( $default_attributes[ $attribute ] ) ) {
					$default_variations[ $attribute ] = $default_attributes[ $attribute ];
				} elseif ( isset( $variation_attributes[ $attribute ] ) ) {
					$default_variations[ $attribute ] = '';
				}
			}
			return array(
				'variations'         => wp_json_encode( $product->get_available_variations() ),
				'default_variations' => wp_json_encode( $default_variations ),
			);
		}
	}

	/**
	 * Echoes all scripts created in Cwicly settings just after body open.
	 */
	public function before_body_tag_code() {
		$transient = get_transient( 'cwicly_custom_code' );
		if ( $transient && ! empty( $transient[1] ) ) {
			echo $transient[1];
		}
	}

	/**
	 * Echoes all scripts created in Cwicly settings for head.
	 */
	public function head_tag_code() {
		$transient = get_transient( 'cwicly_custom_code' );
		if ( $transient && ! empty( $transient[0] ) ) {
			echo $transient[0];
		}
	}

	/**
	 * Echoes all scripts created in Cwicly settings for footer.
	 */
	public function footer_tag_code() {
		$transient = get_transient( 'cwicly_custom_code' );
		if ( $transient && ! empty( $transient[2] ) ) {
			echo $transient[2];
		}
	}
}
