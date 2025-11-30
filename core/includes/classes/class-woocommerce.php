<?php
/**
 * WooCommerce Cwicly.
 *
 * @package cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * Main WooCommerce helper.
 *
 * @package cwicly
 */
class WooCommerce {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_variation_title_include_attributes', '__return_true' );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_color_picker' ) );
		add_action( 'edit_term', array( $this, 'cwicly_save_term_fields' ), 10, 2 );
		add_action( 'create_term', array( $this, 'cwicly_save_term_fields' ), 10, 2 );
		$this->add_form_fields();
		add_action( 'current_screen', array( $this, 'woo_current_screen' ) );
		add_filter( 'woocommerce_variation_is_active', array( $this, 'grey_out_variations_out_of_stock' ), 10, 2 );
		add_filter( 'woocommerce_available_variation', array( $this, 'woocommerce_available_variation_filter' ), 10, 3 );
		add_filter( 'body_class', array( $this, 'woo_name' ) );
		add_filter(
			'woocommerce_product_data_store_cpt_get_products_query',
			function ( $wp_query_args, $query_vars, $data_store_cpt ) {
				if ( ! empty( $query_vars['meta_query'] ) ) {
					$wp_query_args['meta_query'][] = $query_vars['meta_query'];
				}
				return $wp_query_args;
			},
			10,
			3
		);
	}

	/**
	 * Get price based on settings.
	 *
	 * @param string $price        Price.
	 * @param string $price_format Price format.
	 */
	public static function dynamic_price( $price, $price_format, $product = false ) {
		$contenter = $price;
		if ( isset( $price_format ) ) {
			if ( 'formatted' === $price_format ) {
				if ( $product ) {
					$contenter = self::format_price(
						wc_get_price_excluding_tax(
							$product,
							array(
								'price'         => $price,
								'show_currency' => false,
							)
						)
					);
				} else {
					$contenter = self::format_price(
						$price,
						array(
							'ex_tax_label'  => false,
							'show_currency' => false,
						)
					);
				}
			} elseif ( 'formattedcurrency' === $price_format ) {
				$contenter = self::format_price(
					$price,
					array(
						'show_currency' => true,
						'ex_tax_label'  => false,
					),
					array(
						'show_currency' => true,
					)
				);
			} elseif ( 'formattedtax' === $price_format ) {
				if ( $product ) {
					$contenter = self::format_price(
						wc_get_price_including_tax(
							$product,
							array(
								'price'         => $price,
								'show_currency' => false,
							)
						)
					);
				} else {
					$contenter = self::format_price(
						$price,
						array(
							'ex_tax_label'  => false,
							'show_currency' => false,
						)
					);
				}
			} elseif ( 'formattedtaxcurrency' === $price_format ) {
				if ( $product ) {
					$contenter = self::format_price(
						wc_get_price_including_tax(
							$product,
							array( 'price' => $price )
						),
						array(
							'show_currency' => true,
						)
					);
				} else {
					$contenter = self::format_price(
						$price,
						array(
							'show_currency' => true,
						)
					);
				}
			}
		} else {
			$contenter = $price;
		}
		return $contenter;
	}

	/**
	 * Format price based on settings.
	 *
	 * @param string $price Price.
	 * @param array  $args  Arguments.
	 */
	public static function format_price( $price, $args = array() ) {
		$args = apply_filters(
			'wc_price_args',
			wp_parse_args(
				$args,
				array(
					'ex_tax_label'       => false,
					'currency'           => '',
					'show_currency'      => false,
					'decimal_separator'  => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimals'           => wc_get_price_decimals(),
					'price_format'       => get_woocommerce_price_format(),
				)
			)
		);

		$original_price = $price;

		// Convert to float to avoid issues on PHP 8.
		$price = (float) $price;

		$unformatted_price = $price;
		$negative          = $price < 0;

		/**
		 * Filter raw price.
		 *
		 * @param float        $raw_price      Raw price.
		 * @param float|string $original_price Original price as float, or empty string. Since 5.0.0.
		 */
		$price = apply_filters( 'raw_woocommerce_price', $negative ? $price * -1 : $price, $original_price );

		/**
		 * Filter formatted price.
		 *
		 * @param float        $formatted_price    Formatted price.
		 * @param float        $price              Unformatted price.
		 * @param int          $decimals           Number of decimals.
		 * @param string       $decimal_separator  Decimal separator.
		 * @param string       $thousand_separator Thousand separator.
		 * @param float|string $original_price     Original price as float, or empty string. Since 5.0.0.
		 */
		$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'], $original_price );

		if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
			$price = wc_trim_zeros( $price );
		}

		if ( $args['show_currency'] ) {
			$currency = '<span class="currency">' . get_woocommerce_currency_symbol( $args['currency'] ) . '</span>';
		} else {
			$currency = '';
		}

		$return = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], $currency, $price );

		if ( $args['ex_tax_label'] && wc_tax_enabled() ) {
			$return .= ' <small class="tax">' . WC()->countries->ex_tax_or_vat() . '</small>';
		}

		/**
		 * Filters the string of price markup.
		 *
		 * @param string       $return            Price HTML markup.
		 * @param string       $price             Formatted price.
		 * @param array        $args              Pass on the args.
		 * @param float        $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
		 * @param float|string $original_price    Original price as float, or empty string. Since 5.0.0.
		 */
		return apply_filters( 'wc_price', $return, $price, $args, $unformatted_price, $original_price );
	}

	/**
	 * Get price based on settings.
	 *
	 * @param object $product Product.
	 * @param array  $args    Arguments.
	 * @param string $type    Type.
	 */
	public static function price_maker( $product, $args = array(), $type = '' ) {
		if ( isset( $product ) && $product && is_object( $product ) ) {
			$price = $product->get_price();
			if ( 'regular' === $type ) {
				$price = $product->get_regular_price();
			}
			$value = '';

			if ( 'variable' === $product->get_type() ) {
				// get min and max price of variable product.
				$prices    = $product->get_variation_prices();
				$min_price = current( $prices['price'] );
				$max_price = end( $prices['price'] );
				if ( $min_price !== $max_price ) {
					$price = wc_price( $min_price ) . ' - ' . wc_price( $max_price );
					if ( isset( $args[0] ) && $args[0] ) {
						$price = $product->get_price();
						$value = self::dynamic_price( $min_price, $args[0], $product ) . ' - ' . self::dynamic_price( $max_price, $args[0], $product );
					} else {
						$value = self::dynamic_price( $min_price, false, $product );
					}
				} elseif ( isset( $args[0] ) && $args[0] ) {
					$value = self::dynamic_price( $price, $args[0], $product );
				} else {
					$value = $product->get_price();
				}
			} elseif ( $product->get_type() === 'grouped' ) {
				// Check if the product is a grouped product.
				if ( $product->is_type( 'grouped' ) ) {
					// Get the child products of the grouped product.
					$child_products = $product->get_children();

					// Initialize variables for minimum and maximum price.
					$min_price = PHP_INT_MAX;
					$max_price = 0;

					// Loop through the child products.
					foreach ( $child_products as $child_product_id ) {
						// Get the child product object.
						$child_product = wc_get_product( $child_product_id );

						// Get the price of the child product.
						$price = $child_product->get_price();

						// Update the minimum and maximum price variables.
						$min_price = min( $min_price, $price );
						$max_price = max( $max_price, $price );
					}
					if ( $min_price !== $max_price ) {
						$price = wc_price( $min_price ) . ' - ' . wc_price( $max_price );
						if ( isset( $args[0] ) && $args[0] ) {
							$price = $product->get_price();
							$value = self::dynamic_price( $min_price, $args[0], $product ) . ' - ' . self::dynamic_price( $max_price, $args[0], $product );
						} else {
							$value = self::dynamic_price( $min_price, false, $product );
						}
					} elseif ( isset( $args[0] ) && $args[0] ) {
						$value = self::dynamic_price( $price, $args[0], $product );
					} else {
						$value = $product->get_price();
					}
				}
			} elseif ( isset( $args[0] ) && $args[0] ) {
				$value = self::dynamic_price( $price, $args[0], $product );
			} else {
				$value = $price;
			}
			return $value;
		}
	}

	/**
	 * Calculate percentage on sale.
	 *
	 * @param object $product Product.
	 */
	public static function percentage_calculator( $product ) {
		$percentage = '';
		if ( $product->is_type( 'variable' ) ) {
			$percentages = array();

			// Get all variation prices.
			$prices = $product->get_variation_prices();

			// Loop through variation prices.
			foreach ( $prices['price'] as $key => $price ) {
				// Only on sale variations.
				if ( $prices['regular_price'][ $key ] !== $price ) {
					// Calculate and set in the array the percentage for each variation on sale.
					$percentages[] = round( 100 - ( floatval( $prices['sale_price'][ $key ] ) / floatval( $prices['regular_price'][ $key ] ) * 100 ) );
				}
			}
			// We keep the highest value.
			if ( $percentage ) {
				$percentage = max( $percentages ) . '%';
			}
		} elseif ( $product->is_type( 'grouped' ) ) {
			$percentages = array();

			// Get all variation prices.
			$children_ids = $product->get_children();

			// Loop through variation prices.
			foreach ( $children_ids as $child_id ) {
				$child_product = wc_get_product( $child_id );

				$regular_price = (float) $child_product->get_regular_price();
				$sale_price    = (float) $child_product->get_sale_price();

				if ( 0 !== $sale_price || ! empty( $sale_price ) ) {
					// Calculate and set in the array the percentage for each child on sale.
					$percentages[] = round( 100 - ( $sale_price / $regular_price * 100 ) );
				}
			}
			// We keep the highest value.
			if ( $percentage ) {
				$percentage = max( $percentages ) . '%';
			}
		} else {
			$regular_price = (float) $product->get_regular_price();
			$sale_price    = (float) $product->get_sale_price();

			if ( ( 0 !== $sale_price || ! empty( $sale_price ) ) && 0 !== $regular_price && ! empty( $regular_price ) ) {
				$percentage = round( 100 - ( $sale_price / $regular_price * 100 ) ) . '%';
			}
		}
		return $percentage;
	}

	/**
	 * Enqueue color picker
	 */
	public function enqueue_color_picker() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'cwicly-backend', CWICLY_DIR_URL . 'core/assets/js/backend.js', array( 'wp-color-picker' ), CWICLY_VERSION, true );
		wp_enqueue_media();
	}

	/**
	 * Save custom fields to product attributes
	 *
	 * @param int    $term_id The term ID.
	 * @param string $taxonomy The taxonomy slug.
	 */
	public function cwicly_save_term_fields( $term_id, $taxonomy ) {
		if ( isset( $_POST['_cwicly_an'] ) && isset( $_POST['_cwicly_color'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_cwicly_an'] ) ), 'cwicly_an' ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST['_cwicly_color'] ) );
			update_term_meta( $term_id, '_cwicly_color', sanitize_hex_color( $value ) );
		}

		if ( isset( $_POST['_cwicly_an'] ) && isset( $_POST['_cwicly_image_id'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_cwicly_an'] ) ), 'cwicly_an' ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST['_cwicly_image_id'] ) );
			update_term_meta( $term_id, '_cwicly_image_id', absint( $value ) );
		}
	}

	/**
	 * Add custom fields to product attributes
	 */
	public function woo_current_screen() {
		if ( function_exists( 'get_current_screen' ) ) {

			$pt = get_current_screen()->base;
			if ( 'product_page_product_attributes' === $pt ) {
				add_filter(
					'product_attributes_type_selector',
					function ( $output ) {
						$output['button'] = __( 'Button', 'woocommerce' );
						$output['color']  = __( 'Color', 'woocommerce' );
						$output['image']  = __( 'Image', 'woocommerce' );
						return $output;
					}
				);
			}
		}
	}

	/**
	 * Add custom fields to product attributes
	 */
	public function add_form_fields() {
		if ( isset( $_GET['taxonomy'] ) && ! empty( $_GET['taxonomy'] ) ) {
			$taxonomy_name = sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) );

			if ( strpos( $taxonomy_name, 'pa_' ) !== false ) {
				add_action( $taxonomy_name . '_add_form_fields', array( $this, 'add_woo_pa_field' ), 10, 2 );
				add_action( $taxonomy_name . '_edit_form_fields', array( $this, 'add_woo_pa_field_edit' ), 10, 2 );
			}
		}
	}

	/**
	 * Add custom fields to product attributes
	 *
	 * @param object $term The term object.
	 */
	public function add_woo_pa_field( $term ) {
		wp_nonce_field( 'cwicly_an', '_cwicly_an' );
		$get_terms_id = wc_attribute_taxonomy_id_by_name( $term );
		$get_terms    = wc_get_attribute( $get_terms_id );
		if ( 'color' === $get_terms->type ) {
			?>
		<div class="form-field">
			<label for="term-colorpicker"><?php esc_html_e( 'Color', 'woocommerce' ); ?></label>
			<input type="text" name="_cwicly_color" class="colorpicker" id="term-colorpicker" />
			<!-- <p><?php esc_html_e( 'Color HEX goes here.', 'woocommerce' ); ?></p> -->
		</div>
			<?php
		}
		if ( 'image' === $get_terms->type ) {
			?>
		<div class="form-field term-thumbnail-wrap">
			<label><?php esc_html_e( 'Image', 'woocommerce' ); ?></label>
			<div id="product_cat_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width="60px" height="60px" /></div>
			<div style="line-height: 60px;">
				<input type="hidden" id="_cwicly_image_id" name="_cwicly_image_id" />
				<button type="button" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'woocommerce' ); ?></button>
				<button type="button" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'woocommerce' ); ?></button>
			</div>
			<script type="text/javascript">
				// Only show the "remove image" button when needed
				if (!jQuery('#_cwicly_image_id').val()) {
					jQuery('.remove_image_button').hide();
				}

				// Uploading files
				var file_frame;

				jQuery(document).on('click', '.upload_image_button', function(event) {

					event.preventDefault();

					// If the media frame already exists, reopen it.
					if (file_frame) {
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.downloadable_file = wp.media({
						title: '<?php esc_html_e( 'Choose an image', 'woocommerce' ); ?>',
						button: {
							text: '<?php esc_html_e( 'Use image', 'woocommerce' ); ?>'
						},
						multiple: false
					});

					// When an image is selected, run a callback.
					file_frame.on('select', function() {
						var attachment = file_frame.state().get('selection').first().toJSON();
						var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

						jQuery('#_cwicly_image_id').val(attachment.id);
						jQuery('#product_cat_thumbnail').find('img').attr('src', attachment_thumbnail.url);
						jQuery('.remove_image_button').show();
					});

					// Finally, open the modal.
					file_frame.open();
				});

				jQuery(document).on('click', '.remove_image_button', function() {
					jQuery('#product_cat_thumbnail').find('img').attr('src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>');
					jQuery('#_cwicly_image_id').val('');
					jQuery('.remove_image_button').hide();
					return false;
				});

				jQuery(document).ajaxComplete(function(event, request, options) {
					if (request && 4 === request.readyState && 200 === request.status &&
						options.data && 0 <= options.data.indexOf('action=add-tag')) {

						var res = wpAjax.parseAjaxResponse(request.responseXML, 'ajax-response');
						if (!res || res.errors) {
							return;
						}
						// Clear Thumbnail fields on submit
						jQuery('#product_cat_thumbnail').find('img').attr('src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>');
						jQuery('#_cwicly_image_id').val('');
						jQuery('.remove_image_button').hide();
						// Clear Display type field on submit
						jQuery('#display_type').val('');
						return;
					}
				});
			</script>
			<div class="clear"></div>
		</div>
			<?php
		}
	}

	/**
	 * Save custom fields to product attributes
	 *
	 * @param object $term The term.
	 * @param int    $taxonomy The term taxonomy.
	 */
	public function add_woo_pa_field_edit( $term, $taxonomy ) {

		wp_nonce_field( 'cwicly_an', '_cwicly_an' );
		$get_terms_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
		$get_terms    = wc_get_attribute( $get_terms_id );
		if ( 'color' === $get_terms->type ) {
			$value = get_term_meta( $term->term_id, '_cwicly_color', true );
			?>
		<tr class="form-field">
			<th>
				<label for="term-colorpicker"><?php esc_html_e( 'Color', 'woocommerce' ); ?></label>
			</th>
			<td>
				<input type="text" name="_cwicly_color" id="term-colorpicker" class="colorpicker" value="<?php echo esc_attr( $value ); ?>" />
				<!-- <p><?php esc_html_e( 'Color HEX goes here.', 'woocommerce' ); ?></p> -->
			</td>
		</tr>
			<?php
		}

		if ( 'image' === $get_terms->type ) {

			$thumbnail_id = absint( get_term_meta( $term->term_id, '_cwicly_image_id', true ) );

			if ( $thumbnail_id ) {
				$image = wp_get_attachment_thumb_url( $thumbnail_id );
			} else {
				$image = wc_placeholder_img_src();
			}
			?>
		<tr class="form-field term-thumbnail-wrap">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Image', 'woocommerce' ); ?></label></th>
			<td>
				<div id="product_cat_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( $image ); ?>" width="60px" height="60px" /></div>
				<div style="line-height: 60px;">
					<input type="hidden" id="_cwicly_image_id" name="_cwicly_image_id" value="<?php echo esc_attr( $thumbnail_id ); ?>" />
					<button type="button" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'woocommerce' ); ?></button>
					<button type="button" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'woocommerce' ); ?></button>
				</div>
				<script type="text/javascript">
					// Only show the "remove image" button when needed
					if ('0' === jQuery('#_cwicly_image_id').val()) {
						jQuery('.remove_image_button').hide();
					}

					// Uploading files
					var file_frame;

					jQuery(document).on('click', '.upload_image_button', function(event) {

						event.preventDefault();

						// If the media frame already exists, reopen it.
						if (file_frame) {
							file_frame.open();
							return;
						}

						// Create the media frame.
						file_frame = wp.media.frames.downloadable_file = wp.media({
							title: '<?php esc_html_e( 'Choose an image', 'woocommerce' ); ?>',
							button: {
								text: '<?php esc_html_e( 'Use image', 'woocommerce' ); ?>'
							},
							multiple: false
						});

						// When an image is selected, run a callback.
						file_frame.on('select', function() {
							var attachment = file_frame.state().get('selection').first().toJSON();
							var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

							jQuery('#_cwicly_image_id').val(attachment.id);
							jQuery('#product_cat_thumbnail').find('img').attr('src', attachment_thumbnail.url);
							jQuery('.remove_image_button').show();
						});

						// Finally, open the modal.
						file_frame.open();
					});

					jQuery(document).on('click', '.remove_image_button', function() {
						jQuery('#product_cat_thumbnail').find('img').attr('src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>');
						jQuery('#_cwicly_image_id').val('');
						jQuery('.remove_image_button').hide();
						return false;
					});
				</script>
				<div class="clear"></div>
			</td>
		</tr>
			<?php
		}
	}

	/**
	 * Gray out variations that are out of stock
	 *
	 * @param bool   $is_active Whether the variation is active.
	 * @param object $variation The variation.
	 * @return bool
	 */
	public function grey_out_variations_out_of_stock( $is_active, $variation ) {
		if ( ! $variation->is_in_stock() ) {
			return false;
		}

		return $is_active;
	}

	/**
	 * Function for `woocommerce_available_variation` filter-hook.
	 *
	 * @param array  $array Array of variation data.
	 * @param object $that WC_Product_Variation.
	 * @param object $variation WC_Product_Variation.
	 */
	public function woocommerce_available_variation_filter( $array, $that, $variation ) {
		$array['on_sale'] = $variation->is_on_sale();

		$product = wc_get_product( $variation->get_parent_id() );
		$price   = $variation->get_price();

		$price_array                         = array();
		$price_array['blank']                = $price;
		$price_array['formatted']            = self::dynamic_price( $price, 'formatted', $product );
		$price_array['formattedcurrency']    = self::dynamic_price(
			$price,
			'formattedcurrency',
			$product
		);
		$price_array['formattedtax']         = self::dynamic_price( $price, 'formattedtax', $product );
		$price_array['formattedtaxcurrency'] = self::dynamic_price(
			$price,
			'formattedtaxcurrency',
			$product
		);

		$array['price'] = $price_array;

		$sale_price                               = $variation->get_sale_price();
		$sale_price_array                         = array();
		$sale_price_array['blank']                = $sale_price;
		$sale_price_array['formatted']            = self::dynamic_price( $sale_price, 'formatted', $product );
		$sale_price_array['formattedcurrency']    = self::dynamic_price(
			$sale_price,
			'formattedcurrency',
			$product
		);
		$sale_price_array['formattedtax']         = self::dynamic_price( $sale_price, 'formattedtax', $product );
		$sale_price_array['formattedtaxcurrency'] = self::dynamic_price(
			$sale_price,
			'formattedtaxcurrency',
			$product
		);

		$array['sale_price'] = $sale_price_array;

		$regular_price                               = $variation->get_regular_price();
		$regular_price_array                         = array();
		$regular_price_array['blank']                = $regular_price;
		$regular_price_array['formatted']            = self::dynamic_price( $regular_price, 'formatted', $product );
		$regular_price_array['formattedcurrency']    = self::dynamic_price(
			$regular_price,
			'formattedcurrency',
			$product
		);
		$regular_price_array['formattedtax']         = self::dynamic_price( $regular_price, 'formattedtax', $product );
		$regular_price_array['formattedtaxcurrency'] = self::dynamic_price(
			$regular_price,
			'formattedtaxcurrency',
			$product
		);

		$array['regular_price'] = $regular_price_array;

		$array['description']       = $variation->get_description();
		$array['short_description'] = $variation->get_short_description();

		// if the variation is on sale, calculate the percentage.

		if ( $variation->is_on_sale() ) {
			$array['sale_percentage'] = self::percentage_calculator( $variation );
		} else {
			$array['sale_percentage'] = '';
		}

		$attributes        = array();
		$attributes_object = wc_get_product( $variation->get_parent_id() )->get_variation_attributes();
		foreach ( $variation->get_attributes() as $key => $value ) {
			foreach ( $attributes_object as $attribute_key => $attribute_value ) {
				if ( $key == strtolower( $attribute_key ) ) {
					$attributes[ $key ] = $attribute_key;
				}
			}
		}

		$array['attributes_full'] = $attributes;
		return $array;
	}

	/**
	 * Add class to body if product is variable
	 *
	 * @param array $classes Array of classes.
	 */
	public function woo_name( $classes ) {
		if ( CC_WOOCOMMERCE && is_product() ) {
			global $post;
			$product = wc_get_product( $post->ID );
			if ( 'variable' === $product->get_type() ) {
				$classes[] = 'variable-product';
			}
		}
		return $classes;
	}

	/**
	 * Get filtered price
	 * https://gist.github.com/Daniel217D/11c0ac0c2a70676448ff8007cb7cdce9
	 *
	 * @param object $args WP_Query args.
	 */
	public static function get_filtered_price( $args ) {
		global $wpdb;

		$tax_query  = isset( $args->tax_query->queries ) ? $args->tax_query->queries : array();
		$meta_query = isset( $args->query_vars['meta_query'] ) ? $args->query_vars['meta_query'] : array();

		foreach ( $meta_query + $tax_query as $key => $query ) {
			if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		$meta_query = new \WP_Meta_Query( $meta_query );
		$tax_query  = new \WP_Tax_Query( $tax_query );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		$sql  = "SELECT min( FLOOR( price_meta.meta_value ) ) as min_price, max( CEILING( price_meta.meta_value ) ) as max_price FROM {$wpdb->posts} ";
		$sql .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " 	WHERE {$wpdb->posts}.post_type IN ('product')
			AND {$wpdb->posts}.post_status = 'publish'
			AND price_meta.meta_key IN ('_price')
			AND price_meta.meta_value > '' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		$prices = $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.

		return array(
			'min' => floor( $prices->min_price ),
			'max' => ceil( $prices->max_price ),
		);
	}
}
