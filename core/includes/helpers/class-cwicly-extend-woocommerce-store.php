<?php
/**
 * WooCommerce Subscriptions Extend Store API.
 *
 * A class to extend the store public API with subscription related data
 * for each subscription item
 *
 * @package WooCommerce Subscriptions
 */

use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\ProductSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;

add_action(
	'woocommerce_blocks_loaded',
	function () {
		$extend = StoreApi::container()->get( ExtendSchema::class );
		Cwicly_Extend_WooCommerce_Store::init( $extend );
	}
);

/**
 * Extending the WooCommerce Store Endpoint
 */
class Cwicly_Extend_WooCommerce_Store {

	/**
	 * Stores Rest Extending instance.
	 *
	 * @var ExtendSchema
	 */
	private static $extend;

	/**
	 * Plugin Identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'cwicly';

	/**
	 * Bootstraps the class and hooks required data.
	 *
	 * @param ExtendSchema $extend_rest_api An instance of the ExtendSchema class.
	 *
	 * @since 3.1.0
	 */
	public static function init( ExtendSchema $extend_rest_api ) {
		self::$extend = $extend_rest_api;
		self::extend_store();
	}

	/**
	 * Registers the actual data into each endpoint.
	 */
	public static function extend_store() {

		self::$extend->register_endpoint_data(
			array(
				'endpoint'      => CartItemSchema::IDENTIFIER,
				'namespace'     => self::IDENTIFIER,
				'data_callback' => array( 'Cwicly_Extend_WooCommerce_Store', 'extend_cart_item_data' ),
			)
		);

		self::$extend->register_endpoint_data(
			array(
				'endpoint'      => ProductSchema::IDENTIFIER,
				'namespace'     => self::IDENTIFIER,
				'data_callback' => array( 'Cwicly_Extend_WooCommerce_Store', 'extend_product_item_data' ),
			)
		);

		self::$extend->register_endpoint_data(
			array(
				'endpoint'      => CartSchema::IDENTIFIER,
				'namespace'     => self::IDENTIFIER,
				'data_callback' => array( 'Cwicly_Extend_WooCommerce_Store', 'extend_cart_data' ),
			)
		);
	}

	/**
	 * Register Cwicly product data into cart/items endpoint.
	 *
	 * @param array $cart_item Current cart item data.
	 *
	 * @return array $item_data Registered data or empty array if condition is not satisfied.
	 */
	public static function extend_cart_item_data( $cart_item ) {
		$product = $cart_item['data'];

		$price        = $product->get_price();
		$saleprice    = $product->get_sale_price();
		$regularprice = $product->get_regular_price();
		$line_total   = $cart_item['line_total'];
		$item_data    = array(
			'price'             => array(
				'percentage'                        => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::percentage_calculator( $product ) ) ),
				'salepercentage'                    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::percentage_calculator( $product ) ) ),
				'price'                             => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, '', $product ) ) ),
				'price_formatted'                   => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, 'formatted', $product ) ) ),
				'price_formattedcurrency'           => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, 'formattedcurrency', $product ) ) ),
				'price_formattedtax'                => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, 'formattedtax', $product ) ) ),
				'price_formattedtaxcurrency'        => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, 'formattedtaxcurrency', $product ) ) ),
				'saleprice'                         => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, '', $product ) ) ),
				'saleprice_formatted'               => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, 'formatted', $product ) ) ),
				'saleprice_formattedcurrency'       => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, 'formattedcurrency', $product ) ) ),
				'saleprice_formattedtax'            => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, 'formattedtax', $product ) ) ),
				'saleprice_formattedtaxcurrency'    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, 'formattedtaxcurrency', $product ) ) ),
				'regularprice'                      => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, '', $product ) ) ),
				'regularprice_formatted'            => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, 'formatted', $product ) ) ),
				'regularprice_formattedcurrency'    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, 'formattedcurrency', $product ) ) ),
				'regularprice_formattedtax'         => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, 'formattedtax', $product ) ) ),
				'regularprice_formattedtaxcurrency' => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, 'formattedtaxcurrency', $product ) ) ),
			),
			'image'             => self::get_image( $product ),
			'line_total'        => array(
				'price'                      => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $line_total, '', $product ) ) ),
				'price_formatted'            => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $line_total, 'formatted', $product ) ) ),
				'price_formattedcurrency'    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $line_total, 'formattedcurrency', $product ) ) ),
				'price_formattedtax'         => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $line_total, 'formattedtax', $product ) ) ),
				'price_formattedtaxcurrency' => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $line_total, 'formattedtaxcurrency', $product ) ) ),
			),
			'on_sale'           => $product->is_on_sale(),
			'type'              => $product->get_type(),
			'is_downloadable'   => $product->is_downloadable(),
			'is_purchasable'    => $product->is_purchasable(),
			'sold_individually' => $product->is_sold_individually(),
			'is_virtual'        => $product->is_virtual(),
			'is_featured'       => $product->is_featured(),
		);

		return $item_data;
	}

	/**
	 * Register Cwicly product data into cart/items endpoint.
	 *
	 * @param object $cart_item Current cart item data.
	 *
	 * @return array $item_data Registered data or empty array if condition is not satisfied.
	 */
	public static function extend_product_item_data( $cart_item ) {
		// get woocommerce product.
		$product_id = $cart_item->get_id();
		$product    = wc_get_product( $product_id );

		$price        = $product->get_price();
		$saleprice    = $product->get_sale_price();
		$regularprice = $product->get_regular_price();
		$item_data    = array(
			'price'             => array(
				'percentage'                        => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::percentage_calculator( $product ) ) ),
				'salepercentage'                    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::percentage_calculator( $product ) ) ),
				'price'                             => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, '', $product ) ) ),
				'price_formatted'                   => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, 'formatted', $product ) ) ),
				'price_formattedcurrency'           => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, 'formattedcurrency', $product ) ) ),
				'price_formattedtax'                => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, 'formattedtax', $product ) ) ),
				'price_formattedtaxcurrency'        => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $price, 'formattedtaxcurrency', $product ) ) ),
				'saleprice'                         => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, '', $product ) ) ),
				'saleprice_formatted'               => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, 'formatted', $product ) ) ),
				'saleprice_formattedcurrency'       => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, 'formattedcurrency', $product ) ) ),
				'saleprice_formattedtax'            => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, 'formattedtax', $product ) ) ),
				'saleprice_formattedtaxcurrency'    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $saleprice, 'formattedtaxcurrency', $product ) ) ),
				'regularprice'                      => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, '', $product ) ) ),
				'regularprice_formatted'            => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, 'formatted', $product ) ) ),
				'regularprice_formattedcurrency'    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, 'formattedcurrency', $product ) ) ),
				'regularprice_formattedtax'         => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, 'formattedtax', $product ) ) ),
				'regularprice_formattedtaxcurrency' => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $regularprice, 'formattedtaxcurrency', $product ) ) ),
			),
			'image'             => self::get_image( $product ),
			'on_sale'           => $product->is_on_sale(),
			'type'              => $product->get_type(),
			'is_downloadable'   => $product->is_downloadable(),
			'is_purchasable'    => $product->is_purchasable(),
			'sold_individually' => $product->is_sold_individually(),
			'is_virtual'        => $product->is_virtual(),
			'is_featured'       => $product->is_featured(),
		);

		return $item_data;
	}

	/**
	 * Register Cwicly main cart data into cart/items endpoint.
	 */
	public static function extend_cart_data() {
		$shipping_total             = WC()->cart->get_shipping_total() + WC()->cart->get_shipping_tax();
		$shipping_total_without_tax = WC()->cart->get_shipping_total();

		$shipping_total = array(
			'price'                      => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $shipping_total_without_tax, '' ) ) ),
			'price_formatted'            => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $shipping_total_without_tax, 'formatted' ) ) ),
			'price_formattedcurrency'    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $shipping_total_without_tax, 'formattedcurrency' ) ) ),
			'price_formattedtax'         => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $shipping_total, 'formattedtax' ) ) ),
			'price_formattedtaxcurrency' => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $shipping_total, 'formattedtaxcurrency' ) ) ),
		);

		$sub_total          = WC()->cart->get_subtotal();
		$sub_total_with_tax = WC()->cart->get_subtotal() + WC()->cart->get_subtotal_tax();
		$sub_total          = array(
			'price'                      => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $sub_total, '' ) ) ),
			'price_formatted'            => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $sub_total, 'formatted' ) ) ),
			'price_formattedcurrency'    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $sub_total, 'formattedcurrency' ) ) ),
			'price_formattedtax'         => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $sub_total_with_tax, 'formattedtax' ) ) ),
			'price_formattedtaxcurrency' => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $sub_total_with_tax, 'formattedtaxcurrency' ) ) ),
		);

		$total             = WC()->cart->get_total( 'raw' );
		$total_without_tax = WC()->cart->get_total( 'raw' ) - WC()->cart->get_total_tax( 'raw' ) - WC()->cart->get_shipping_tax( 'raw' );
		$total             = array(
			'price'                      => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $total_without_tax, '' ) ) ),
			'price_formatted'            => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $total_without_tax, 'formatted' ) ) ),
			'price_formattedcurrency'    => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $total_without_tax, 'formattedcurrency' ) ) ),
			'price_formattedtax'         => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $total, 'formattedtax' ) ) ),
			'price_formattedtaxcurrency' => html_entity_decode( wp_strip_all_tags( Cwicly\WooCommerce::dynamic_price( $total, 'formattedtaxcurrency' ) ) ),
		);

		return array(
			'shipping_total' => $shipping_total,
			'sub_total'      => $sub_total,
			'total'          => $total,
		);
	}

	/**
	 * Get image
	 *
	 * @param WC_Product $product Product object.
	 * @return array|null
	 */
	private static function get_image( $product ) {
		$attachment_id = $product->get_image_id();

		if ( ! $attachment_id ) {
			return null;
		}

		$allimagesizes = \Cwicly\Helpers::get_all_image_sizes();
		$src           = array();
		foreach ( $allimagesizes as $key => $value ) {
			$src[ $key ] = wp_get_attachment_image_src( $attachment_id, $key );
		}

		$attachment = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( ! is_array( $attachment ) ) {
			return array();
		}

		$thumbnail = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );

		return array(
			'id'        => (int) $attachment_id,
			'src'       => current( $attachment ),
			'thumbnail' => current( $thumbnail ),
			'srcset'    => (string) wp_get_attachment_image_srcset( $attachment_id, 'full' ),
			'sizes'     => array(
				'width'  => $attachment[1],
				'height' => $attachment[2],
			),
			'name'      => get_the_title( $attachment_id ),
			'alt'       => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			'all'       => $src,
		);
	}
}
