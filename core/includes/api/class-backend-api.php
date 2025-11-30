<?php
/**
 * Cwicly Backend API.
 *
 * @package cwicly
 */

namespace Cwicly;

use enshrined\svgSanitize\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Backend API.
 */
class Backend_API extends \WP_REST_Controller {

	/**
	 * Accepted options access.
	 *
	 * @var array
	 */
	protected static $accepted_options = array(
		'cwicly_design_auth',
		'cwicly_gmap',
		'cwicly_local_fonts',
		'cwicly_local_active_fonts',
		'cwicly_css',
		'cwicly_global_fonts',
		'cwicly_global_css_fonts',
		'cwicly_breakpoints',
		'cwicly_breakpoints_list',
		'cwicly_section_defaults',
		'cwicly_global_css',
		'cwicly_pseudos',
		'cwicly_collection',
		'cwicly_regenerate_html',
		'cwicly_global_classes',
		'cwicly_shells',
		'cwicly_optimise',
		'cwicly_deprecated',
		'cwicly_global_styles',
		'cwicly_global_parts',
		'cwicly_conditions',
		'cwicly_pre_conditions',
		'cwicly_role_editor',
		'cwicly_font_cols',
		'cwicly_components_folders',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->register_routes();
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$namespace = 'cwicly/v' . CWICLY_API_VERSION;

		$base2 = 'woocommerce';
		register_rest_route(
			$namespace,
			'/' . $base2,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_woocommerce' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base3 = 'compiler';
		register_rest_route(
			$namespace,
			'/' . $base3,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'get_code' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base4 = 'refresh_license';
		register_rest_route(
			$namespace,
			'/' . $base4,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( 'Cwicly\License', 'the_lc_check' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base5 = 'get_google_fonts';
		register_rest_route(
			$namespace,
			'/' . $base5,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'get_google_fonts' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base6 = 'delete_local_font';
		register_rest_route(
			$namespace,
			'/' . $base6,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'delete_local_font' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base7 = 'upload_local_font';
		register_rest_route(
			$namespace,
			'/' . $base7,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'upload_local_font' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base8 = 'delete_local_custom_variant';
		register_rest_route(
			$namespace,
			'/' . $base8,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'delete_local_custom_variant' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
					'args'                => array(),
				),
			)
		);

		$base9 = 'duplicate_template';
		register_rest_route(
			$namespace,
			'/' . $base9,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'duplicate_template' ),
					'permission_callback' => array( $this, 'permissions_check_admin' ),
				),
			)
		);

		$base10 = 'dynamics';
		register_rest_route(
			$namespace,
			'/' . $base10,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'dynamics' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base11 = 'search_posts';
		register_rest_route(
			$namespace,
			'/' . $base11,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'search_posts' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		// $base11 = 'cwicly_license';
		// register_rest_route(
		// 	$namespace,
		// 	'/' . $base11,
		// 	array(
		// 		array(
		// 			'methods'             => \WP_REST_Server::READABLE,
		// 			'callback'            => array( $this, 'get_cwicly_license' ),
		// 			'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
		// 		),
		// 	)
		// );

		$base12 = 'cwicly_global_css';
		register_rest_route(
			$namespace,
			'/' . $base12,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_global_css' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base13 = 'cwicly_menus';
		register_rest_route(
			$namespace,
			'/' . $base13,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_menus' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base14 = 'cwicly_svgs';
		register_rest_route(
			$namespace,
			'/' . $base14,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_svgs' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base15 = 'options';
		register_rest_route(
			$namespace,
			'/' . $base15,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_options' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);
		register_rest_route(
			$namespace,
			'/' . $base15,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_options' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base16 = 'allpostsrenderids';
		register_rest_route(
			$namespace,
			'/' . $base16,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'all_posts_render_ids' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);

		$base17 = 'getposts';
		register_rest_route(
			$namespace,
			'/' . $base17,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_specific_posts' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);

		$base18 = 'post_html_render';
		register_rest_route(
			$namespace,
			'/' . $base18,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'parser_render' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);

		$base19 = 'post_isstyling_render';
		register_rest_route(
			$namespace,
			'/' . $base19,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'parser_render' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);

		$base19 = 'single_make_css';
		register_rest_route(
			$namespace,
			'/' . $base19,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'single_make_css' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base20 = 'backend_info';
		register_rest_route(
			$namespace,
			'/' . $base20,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'backend_info' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base21 = 'cwicly_dynamic_preview';
		register_rest_route(
			$namespace,
			'/' . $base21,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'dynamic_previewer' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base22 = 'code';
		register_rest_route(
			$namespace,
			'/' . $base22,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'code_maker' ),
					'permission_callback' => array( '\Cwicly\Capabilities', 'code_block_php' ),
				),
			)
		);

		$base23 = 'filter_query';
		register_rest_route(
			$namespace,
			'/' . $base23,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'filter_query' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base24 = 'cart_backend';
		register_rest_route(
			$namespace,
			'/' . $base24,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'cart_backend' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base25 = 'components/export';
		register_rest_route(
			$namespace,
			'/' . $base25,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'export_components' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);

		$base26 = 'template_post_content';
		register_rest_route(
			$namespace,
			'/' . $base26,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'template_post_content' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check' ),
				),
			)
		);

		$base27 = 'component_extras';
		register_rest_route(
			$namespace,
			'/' . $base27,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'component_extras' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);

		$base28 = 'tailwind-purge';
		register_rest_route(
			$namespace,
			'/' . $base28,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'tailwind_purge' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);

		$base29 = 'transfer_breakpoints';
		register_rest_route(
			$namespace,
			'/' . $base29,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'breakpoint_transfer' ),
					'permission_callback' => array( '\Cwicly\Helpers', 'permissions_check_admin' ),
				),
			)
		);
	}

	/**
	 * Dynamic Requests
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function dynamics( $request ) {
		$body = $request->get_body();
		$body = json_decode( $body, true );

		$dynamics = array();
		foreach ( $body as $main_key => $main_value ) {
			if ( 'backend_info' === $main_key ) {
				foreach ( $main_value as $array_value ) {
					foreach ( $array_value as $time => $key_value ) {
						foreach ( $key_value as $type => $value ) {
							$final = '';
							if ( 'featuredimage' === $type ) {
								if ( isset( $value['postid'] ) && has_post_thumbnail( $value['postid'] ) ) {
									$final = get_the_post_thumbnail_url( $value['postid'] );
								} else {
									$final = 'nofeaturedimage';
								}
							} elseif ( 'posttaxonomies' === $type ) {
								if ( isset( $value ) ) {
									$final = get_post_taxonomies( $value );
								}
							} elseif ( 'getterms' === $type && isset( $value['taxonomy'] ) && $value['taxonomy'] ) {
								$taxonomies = $value['taxonomy'];
								$terms      = array();

								$tax_includes = $value['taxIncludes'];

								foreach ( $taxonomies as $taxonomy ) {
									if ( in_array( $taxonomy, $tax_includes ) || ! $tax_includes ) {
										$term = get_the_terms( $value['id'], $taxonomy );
										if ( $term ) {
											if ( $value['topLevelParents'] ) {
												foreach ( $term as $term_single ) {
													$terms[] = \Cwicly\Helpers::get_term_top_level_parent( $term_single->term_id, $taxonomy );
												}
											} else {
												$terms = array_merge( $terms, $term );
											}
										}
									}
								}
								$final = $terms;
							} elseif ( 'acffield' === $type ) {
								if ( isset( $value['acffield'] ) && isset( $value['postid'] ) ) {
									$acfallfield = get_field_object( $value['acffield'], $value['postid'] );
									if ( isset( $value['svgcontent'] ) && isset( $acfallfield['type'] ) && 'image' === $acfallfield['type'] && isset( $acfallfield['value']['ID'] ) ) {
										$svg_content = '';
										$svg_path    = get_attached_file( $acfallfield['value']['ID'] );

										if ( file_exists( $svg_path ) ) {
											$svg_content = file_get_contents( $svg_path );
										}

										if ( ! $svg_content ) {
											return new \WP_Error( 'error', 'SVG file not found', array( 'status' => 400 ) );
										}

										$svg = \Cwicly\Helpers::get_svg_content( $svg_content );

										$acfallfield['svg'] = $svg;
									}
									$final = $acfallfield;
								}
							} elseif ( 'woocommerce' === $type && CC_WOOCOMMERCE ) {
								if ( isset( $value['all'] ) && $value['all'] && isset( $value['postid'] ) ) {
									$product     = wc_get_product( $value['postid'] );
									$groupfields = array();
									if ( $product ) {
										$price                                      = $product->get_price();
										$saleprice                                  = $product->get_sale_price();
										$regularprice                               = $product->get_regular_price();
										$groupfields['salepercentage']              = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::percentage_calculator( $product ) ) );
										$groupfields['price']                       = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $price, '' ) ) );
										$groupfields['price_formatted']             = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $price, 'formatted' ) ) );
										$groupfields['price_formattedcurrency']     = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $price, 'formattedcurrency' ) ) );
										$groupfields['price_formattedtax']          = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $price, 'formattedtax' ) ) );
										$groupfields['price_formattedtaxcurrency']  = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $price, 'formattedtaxcurrency' ) ) );
										$groupfields['saleprice']                   = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $saleprice, '' ) ) );
										$groupfields['saleprice_formatted']         = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $saleprice, 'formatted' ) ) );
										$groupfields['saleprice_formattedcurrency'] = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $saleprice, 'formattedcurrency' ) ) );
										$groupfields['saleprice_formattedtax']      = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $saleprice, 'formattedtax' ) ) );
										$groupfields['saleprice_formattedtaxcurrency']    = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $saleprice, 'formattedtaxcurrency' ) ) );
										$groupfields['regularprice']                      = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $regularprice, '' ) ) );
										$groupfields['regularprice_formatted']            = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $regularprice, 'formatted' ) ) );
										$groupfields['regularprice_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $regularprice, 'formattedcurrency' ) ) );
										$groupfields['regularprice_formattedtax']         = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $regularprice, 'formattedtax' ) ) );
										$groupfields['regularprice_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $regularprice, 'formattedtaxcurrency' ) ) );
									}
									$groupfields['currency']       = get_woocommerce_currency();
									$groupfields['currencysymbol'] = html_entity_decode( get_woocommerce_currency_symbol() );
									if ( $product && $product->get_type() === 'variable' ) {
										$variationminprice      = $product->get_variation_price();
										$variationmaxprice      = $product->get_variation_price( 'max' );
										$variationregnminprice  = $product->get_variation_regular_price();
										$variationregnmaxprice  = $product->get_variation_regular_price( 'max' );
										$variationsalenminprice = $product->get_variation_sale_price();
										$variationsalenmaxprice = $product->get_variation_sale_price( 'max' );

										$groupfields['variationmin']                      = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationminprice, '' ) ) );
										$groupfields['variationmin_formatted']            = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationminprice, 'formatted' ) ) );
										$groupfields['variationmin_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationminprice, 'formattedcurrency' ) ) );
										$groupfields['variationmin_formattedtax']         = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationminprice, 'formattedtax' ) ) );
										$groupfields['variationmin_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationminprice, 'formattedtaxcurrency' ) ) );
										$groupfields['variationmax']                      = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationmaxprice, 'formatted' ) ) );
										$groupfields['variationmax_formatted']            = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationmaxprice, 'formatted' ) ) );
										$groupfields['variationmax_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationmaxprice, 'formattedcurrency' ) ) );
										$groupfields['variationmax_formattedtax']         = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationmaxprice, 'formattedtax' ) ) );
										$groupfields['variationmax_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationmaxprice, 'formattedtaxcurrency' ) ) );

										$groupfields['variationregmin']                      = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnminprice, '' ) ) );
										$groupfields['variationregmin_formatted']            = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnminprice, 'formatted' ) ) );
										$groupfields['variationregmin_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnminprice, 'formattedcurrency' ) ) );
										$groupfields['variationregmin_formattedtax']         = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnminprice, 'formattedtax' ) ) );
										$groupfields['variationregmin_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnminprice, 'formattedtaxcurrency' ) ) );
										$groupfields['variationregmax']                      = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnmaxprice, 'formatted' ) ) );
										$groupfields['variationregmax_formatted']            = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnmaxprice, 'formatted' ) ) );
										$groupfields['variationregmax_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnmaxprice, 'formattedcurrency' ) ) );
										$groupfields['variationregmax_formattedtax']         = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnmaxprice, 'formattedtax' ) ) );
										$groupfields['variationregmax_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationregnmaxprice, 'formattedtaxcurrency' ) ) );

										$groupfields['variationsalemin']                      = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenminprice, '' ) ) );
										$groupfields['variationsalemin_formatted']            = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenminprice, 'formatted' ) ) );
										$groupfields['variationsalemin_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenminprice, 'formattedcurrency' ) ) );
										$groupfields['variationsalemin_formattedtax']         = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenminprice, 'formattedtax' ) ) );
										$groupfields['variationsalemin_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenminprice, 'formattedtaxcurrency' ) ) );
										$groupfields['variationsalemax']                      = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenmaxprice, 'formatted' ) ) );
										$groupfields['variationsalemax_formatted']            = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenmaxprice, 'formatted' ) ) );
										$groupfields['variationsalemax_formattedcurrency']    = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenmaxprice, 'formattedcurrency' ) ) );
										$groupfields['variationsalemax_formattedtax']         = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenmaxprice, 'formattedtax' ) ) );
										$groupfields['variationsalemax_formattedtaxcurrency'] = html_entity_decode( wp_strip_all_tags( \Cwicly\WooCommerce::dynamic_price( $variationsalenmaxprice, 'formattedtaxcurrency' ) ) );
									}
									$final = $groupfields;
								} elseif ( isset( $value['productattributes'] ) && isset( $value['postid'] ) ) {
									$product = wc_get_product( $value['postid'] );
									$final   = array();
									if ( $product && $product->get_type() === 'variable' ) {
										foreach ( $product->get_variation_attributes() as $taxonomy => $terms_slug ) {
											// To get the attribute label (in WooCommerce 3+).
											$taxonomy_label = wc_attribute_label( $taxonomy, $product );

											// Setting some data in an array.
											$variations_attributes_and_values[ $taxonomy ]         = array( 'label' => $taxonomy_label );
											$variations_attributes_and_values[ $taxonomy ]['type'] = wc_get_attribute( wc_attribute_taxonomy_id_by_name( $taxonomy ) )->type;
											$variations_attributes_and_values[ $taxonomy ]['slug'] = $taxonomy;

											foreach ( $terms_slug as $term ) {

												// Getting the term object from the slug.
												$term_obj = get_term_by( 'slug', $term, $taxonomy );

												$term_id   = $term_obj->term_id; // The ID.
												$term_name = $term_obj->name; // The Name.
												$term_slug = $term_obj->slug; // The Slug.
												$term_type = '';
												if ( $variations_attributes_and_values[ $taxonomy ]['type'] && 'color' === $variations_attributes_and_values[ $taxonomy ]['type'] ) {
													$term_type = get_term_meta( $term_id, '_cwicly_color', true );
												}
												if ( $variations_attributes_and_values[ $taxonomy ]['type'] && 'image' === $variations_attributes_and_values[ $taxonomy ]['type'] ) {
													$term_type = wp_get_attachment_url( get_term_meta( $term_id, '_cwicly_image_id', true ) );
												}

												// Setting the terms ID and values in the array.
												$variations_attributes_and_values[ $taxonomy ]['terms'][ $term_id ] = array(
													'name' => $term_name,
													'slug' => $term_slug,
													'type' => $term_type,
												);
											}
										}
										$groupfields = $variations_attributes_and_values;
										$final       = $groupfields;
									}
								} elseif ( isset( $value['woogroups'] ) && isset( $value['postid'] ) ) {
									$final   = array();
									$product = wc_get_product( $value['postid'] );
									if ( $product->get_type() === 'grouped' ) {
										$products    = $product->get_children();
										$groupfields = $products;
										$final       = $groupfields;
									}
								}
							} elseif ( isset( $value['featuredimage'] ) ) {
								if ( isset( $value['postid'] ) && has_post_thumbnail( $value['postid'] ) ) {
									$final = get_the_post_thumbnail_url( $value['postid'] );
								} else {
									$final = 'nofeaturedimage';
								}
								$final = 'nofeaturedimage';
							} elseif ( isset( $value['getattributeterms'] ) && isset( $value['type'] ) && isset( $value['term'] ) ) {
								$final      = array();
								$type       = $value['type'];
								$variations = get_terms( $value['term'] );
								foreach ( $variations as $index => $element ) {
									$final[ $index ]['label'] = $element->name;
									$final[ $index ]['value'] = $element->slug;
									$final[ $index ]['id']    = $element->term_id;
									if ( $type ) {
										if ( '_cwicly_image_id' === $type ) {
											$final[ $index ]['extra'] = wp_get_attachment_url( get_term_meta( $element->term_id, $type, true ) );
										} else {
											$final[ $index ]['extra'] = get_term_meta( $element->term_id, $type, true );
										}
									}
								}
							} elseif ( 'previouspost' === $type || 'nextpost' === $type ) {
								$final          = array();
								$taxonomy       = 'category';
								$in_same_term   = false;
								$excluded_terms = '';
								if ( isset( $value['taxonomy'] ) && '' !== $value['taxonomy'] ) {
									$taxonomy = $value['taxonomy'];
								}
								if ( isset( $value['in_same_term'] ) && 'true' === $value['in_same_term'] ) {
									$in_same_term = true;
								}
								if ( isset( $value['excluded_terms'] ) ) {
									$excluded_terms = $value['excluded_terms'];
								}
								if ( isset( $value['postid'] ) ) {
									global $post;
									$old_global = $post;
									$post       = get_post( $value['postid'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
									$poster     = '';
									if ( 'previouspost' === $type ) {
										$poster = get_previous_post( $in_same_term, $excluded_terms, $taxonomy );
									} elseif ( 'nextpost' === $type ) {
										$poster = get_next_post( $in_same_term, $excluded_terms, $taxonomy );
									}
									$post = $old_global; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

									if ( '' === $poster ) {
										return 0;
									}

									$final = array(
										'id'       => $poster->ID,
										'postType' => $poster->post_type,
									);
								}
							} elseif ( 'users' === $type ) {
								$final = array();
								if ( isset( $value['keyword'] ) ) {
									$search = '*' . esc_attr( $value['keyword'] ) . '*';
									$final  = get_users(
										array(
											'search' => $search,
											'search_columns' => array(
												'user_login',
												'user_nicename',
												'user_email',
												'user_url',
											),
										)
									);
								}
							} elseif ( 'imagedetails' === $type ) {
								$data                  = array();
								$post                  = get_post( $value['postid'] );
								$data['media_details'] = wp_get_attachment_metadata( $value['postid'] );

								// Ensure empty details is an empty object.
								if ( empty( $data['media_details'] ) ) {
									$data['media_details'] = new \stdClass();
								} elseif ( ! empty( $data['media_details']['sizes'] ) ) {

									foreach ( $data['media_details']['sizes'] as $size => &$size_data ) {

										if ( isset( $size_data['mime-type'] ) ) {
											$size_data['mime_type'] = $size_data['mime-type'];
											unset( $size_data['mime-type'] );
										}

										// Use the same method image_downsize() does.
										$image_src = wp_get_attachment_image_src( $value['postid'], $size );
										if ( ! $image_src ) {
											continue;
										}

										$size_data['source_url'] = $image_src[0];
									}

									$full_src = wp_get_attachment_image_src( $value['postid'], 'full' );

									if ( ! empty( $full_src ) ) {
										$data['media_details']['sizes']['full'] = array(
											'file'       => wp_basename( $full_src[0] ),
											'width'      => $full_src[1],
											'height'     => $full_src[2],
											'mime_type'  => $post->post_mime_type,
											'source_url' => $full_src[0],
										);
									}
								} else {
									$data['media_details']['sizes'] = new \stdClass();
								}

								$final = $data;
							} elseif ( 'svgcontent' === $type ) {
								$svg_content = '';
								$svg_path    = get_attached_file( $value['postid'] );

								if ( file_exists( $svg_path ) ) {
									$svg_content = file_get_contents( $svg_path );
								}

								if ( ! $svg_content ) {
									return new \WP_Error( 'error', 'SVG file not found', array( 'status' => 400 ) );
								}

								$svg = \Cwicly\Helpers::get_svg_content( $svg_content );

								$final = $svg;
							}

							if ( ! isset( $dynamics[ $type ] ) ) {
								$dynamics[ $type ]          = array();
								$dynamics[ $type ][ $time ] = $final;
							} else {
								$dynamics[ $type ][ $time ] = $final;
							}
						}
					}
				}
			}
		}

		return $dynamics;
	}

	/**
	 * Duplicate Template
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function duplicate_template( $request ) {
		$body = json_decode( $request->get_body(), true );

		$type      = $body['type'] ?? null;
		$id        = $body['id'] ?? null;
		$new_title = $body['title'] ?? null;
		$theme     = $body['theme'] ?? null;

		if ( 'woocommerce/woocommerce' === $theme ) {
			$theme = get_stylesheet();
		}

		$new_title      = wp_strip_all_tags( $new_title );
		$new_title_slug = sanitize_title( $new_title );

		if ( $type && $id && $new_title && $theme ) {
			$existing_template_id = $id; // Replace with the ID of the existing FSE template.
			$new_template_args    = array(
				'post_title'  => $new_title, // Replace with the title of the new template.
				'post_type'   => $type, // This should be 'wp_template' for FSE templates.
				'post_status' => 'publish', // Set the status of the new template post.
				'post_name'   => 'wp_template_part' === $type ? $new_title_slug : 'wp-custom-template-' . $new_title_slug, // Set the slug of the new template post.
				'tax_input'   => 'wp_template_part' === $type ?
				array(
					'wp_theme'              => array( $theme ),
					'wp_template_part_area' => array( 'uncategorized' ),
				)
				:
				array(
					'wp_theme' => array( $theme ),
				),
			);
			$new_template_id      = wp_insert_post( $new_template_args );
			if ( $new_template_id ) {
				$new_template_content = get_post_field( 'post_content', $existing_template_id, 'raw' );
				wp_update_post(
					array(
						'ID'           => $new_template_id,
						'post_content' => wp_slash( $new_template_content ),
					)
				);
			}

			return $new_template_id;
		} else {
			return new \WP_Error( 'error', 'Missing data', array( 'status' => 400 ) );
		}
	}

	/**
	 * Delete Local Custom Variant
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function delete_local_custom_variant( $request ) {
		$body = json_decode( $request->get_body(), true );

		$font_name = $body['font'] ?? null;
		$font_base = $body['base'] ?? null;

		if ( $font_name && $font_base ) {
			$uploads_dir = wp_upload_dir();
			$base_dir    = $uploads_dir['basedir'] . '/cwicly/local-fonts/';

			$font_dir = $base_dir . '/custom/' . $font_name . '/' . $font_base . '.woff2';

			if ( file_exists( $font_dir ) ) {
				// make it work from the frontend, as well.
				require_once ABSPATH . 'wp-admin/includes/file.php';
				// this variable will hold the selected filesystem class.
				global $wp_filesystem;
				// this function selects the appropriate filesystem class.
				WP_Filesystem();
				// finally, you can call the 'delete' function on the selected class,
				// which is now stored in the global '$wp_filesystem'.
				$wp_filesystem->delete( $font_dir, true );

				return true;
			}
		}

		return false;
	}

	/**
	 * Upload Local Custom Font
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public function upload_local_font( $request ) {
		$name = $request->get_param( 'name' );

		$files = $request->get_file_params();

		if ( empty( $files ) ) {
			return new \WP_Error( 'no_file', 'No file was uploaded', array( 'status' => 400 ) );
		}

		$countfiles = count( $files );

		$upload_dir  = wp_upload_dir();
		$base        = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/';
		$local_fonts = trailingslashit( $base ) . 'local-fonts/';
		$dir         = $local_fonts . 'custom/';

		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem( false, $upload_dir['basedir'], true );

		$paths = array();

		for ( $i = 0; $i < $countfiles; $i++ ) {
			// Make sure file is woff2, otherwise return error.
			$ext = pathinfo( $files[ 'file' . $i . '' ]['name'], PATHINFO_EXTENSION );
			if ( 'woff2' !== $ext ) {
				return new \WP_Error( 'wrong_file_type', 'Wrong file type', array( 'status' => 400 ) );
			}

			$filename = $files[ 'file' . $i . '' ]['name'];

			$target_file = $dir . '/' . $name . '/' . basename( $filename );

			if ( ! $wp_filesystem->is_dir( $dir . '/' . $name ) ) {
				$wp_filesystem->mkdir( $dir . '/' . $name );
			}

			// Upload file.
			move_uploaded_file( $files[ 'file' . $i . '' ]['tmp_name'], $target_file );

			// Dynamic URL so users don't have to worry about the path when migrating.
			$local_url = '{{CC_UPLOAD_URL}}/cwicly/local-fonts/custom/' . rawurlencode( $name ) . '/' . rawurlencode( $filename );
			// $local_url = CC_UPLOAD_URL . '/cwicly/local-fonts/custom/' . rawurlencode( $name ) . '/' . rawurlencode( $filename );

			$paths[] = $local_url;
		}

		return new \WP_REST_Response( $paths, 200 );
	}

	/**
	 * Delete local font
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_local_font( $request ) {
		$body = json_decode( $request->get_body(), true );

		$font_name = $body['font'] ?? null;
		$is_google = $body['isGoogle'] ?? null;

		if ( $font_name ) {
			$uploads_dir = wp_upload_dir();
			$base_dir    = $uploads_dir['basedir'] . '/cwicly/local-fonts/';

			if ( $is_google ) {
				$font_dir = $base_dir . '/google/' . $font_name . '/';
			} else {
				$font_dir = $base_dir . '/custom/' . $font_name . '/';
			}

			if ( file_exists( $font_dir ) ) {
				// make it work from the frontend, as well.
				require_once ABSPATH . 'wp-admin/includes/file.php';
				// this variable will hold the selected filesystem class.
				global $wp_filesystem;
				// this function selects the appropriate filesystem class.
				WP_Filesystem();
				// finally, you can call the 'delete' function on the selected class,
				// which is now stored in the global '$wp_filesystem'.
				$wp_filesystem->delete( $font_dir, true );

				return true;
			}
		}
		if ( ! $is_google ) {
			return true;
		}

		return false;
	}

	/**
	 * Get Google Fonts and save them to local
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_google_fonts( $request ) {
		$body = json_decode( $request->get_body(), true );

		$string = $body['string'] ?? null;

		if ( $string ) {
			// FIRST METHOD.

			preg_match_all( '#/\*.*?\*/\s*@font-face\s*\{.*?\}#s', $string, $matches );

			if ( empty( $matches[0] ) ) {
				return $string;
			}

			$uploads_dir = wp_upload_dir();
			$base_dir    = $uploads_dir['basedir'] . '/cwicly/local-fonts/';

			if ( ! file_exists( $base_dir ) ) {
				wp_mkdir_p( $base_dir );
			}

			foreach ( $matches[0] as $match ) {
				preg_match( "/url\((.*?)\) format\('woff2'\)/", $match, $woff2_url );
				$woff2_url = $woff2_url[1];
				preg_match( "/font-family: '(.*?)';/", $match, $font_family_matches );
				preg_match( '/font-weight: (.*?);/', $match, $font_weight_matches );
				preg_match( '/font-style: (.*?);/', $match, $font_style_matches );

				if ( ! empty( $font_family_matches[1] ) && ! empty( $font_weight_matches[1] ) && ! empty( $font_style_matches[1] ) ) {
					$font_family = $font_family_matches[1];
					$font_weight = $font_weight_matches[1];
					$font_style  = $font_style_matches[1];

					preg_match( '/\/\*(.*?)\*\//', $match, $unicode_range_matches );
					if ( ! empty( $unicode_range_matches[1] ) ) {
						$unicode_range = trim( $unicode_range_matches[1] );
						$font_dir      = $base_dir . '/google/' . $font_family . '/' . $unicode_range . '/';
					} else {
						$font_dir = $base_dir . '/google/' . $font_family . '/';
					}

					if ( ! file_exists( $font_dir ) ) {
						wp_mkdir_p( $font_dir );
					}

					$filename     = $font_family . '-' . $font_weight . '-' . $font_style . '.woff2';
					$filename_url = rawurlencode( $font_family ) . '-' . rawurlencode( $font_weight ) . '-' . rawurlencode( $font_style ) . '.woff2';

					// Dynamic URL so users don't have to worry about the path when migrating.
					$local_url = '{{CC_UPLOAD_URL}}/cwicly/local-fonts/google/' . rawurlencode( $font_family ) . '/' . $unicode_range . '/' . $filename_url;
					// $local_url    = CC_UPLOAD_URL . '/cwicly/local-fonts/google/' . rawurlencode( $font_family ) . '/' . $unicode_range . '/' . $filename_url;

					if ( ! file_exists( $font_dir . $filename ) ) {
						$response = wp_remote_get( $woff2_url );

						if ( is_array( $response ) && ! is_wp_error( $response ) ) {
							$body = wp_remote_retrieve_body( $response );

							if ( ! empty( $body ) ) {
								file_put_contents( $font_dir . $filename, $body );
							}
						} else {
							return 'Error: Failed to download the WOFF2 file ' . $woff2_url;
						}
					}

					$string = str_replace( $woff2_url, $local_url, $string );
				}
			}

			return $string;
		}
	}

	/**
	 * Compile and return Code
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_code( $request ) {
		if ( null !== ( $request->get_param( 'scss' ) ) ) {
			$code = $request->get_param( 'scss' );

			if ( ! class_exists( '\ScssPhp\ScssPhp\Compiler' ) ) {
				require_once CWICLY_DIR_PATH . 'core/lib/scssphp/scss.inc.php';
			}

			$compiler = new \ScssPhp\ScssPhp\Compiler();

			try {
				if ( method_exists( $compiler, 'compileString' ) ) {
					$code = $compiler->compileString( $code )->getCss();
				} else {
					$code = $compiler->compile( $code );
				}
			} catch ( \ScssPhp\ScssPhp\Exception\SassException $e ) {
				echo wp_json_encode(
					array(
						'error'   => true,
						'message' => $e->getMessage(),
					)
				);

				die;
			}

			return new \WP_REST_Response( $code, 200 );
		}
	}

	/**
	 * Catch all for WooCommerce API
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_woocommerce( $request ) {
		if ( null !== ( $request->get_param( 'upsellproducts' ) ) || null !== ( $request->get_param( 'relatedproducts' ) ) ) {
			$query_prep = new \WC_Product_Query( array( 'limit' => 4 ) );

			$products_query = $query_prep->get_products();
			$query          = array();
			foreach ( $products_query as $product ) {
				$producter                     = $product->get_data();
				$producter['cc_featuredimage'] = get_the_post_thumbnail_url( $producter['id'], 'full' );

				$original       = array();
				$main_image     = array();
				$main_image[]   = $product->get_image_id();
				$gallery_images = $product->get_gallery_image_ids();
				$attachment_ids = array_merge( $main_image, $gallery_images );
				foreach ( $attachment_ids as $images ) {
					$original[] = array(
						'src'     => wp_get_attachment_url( $images ),
						'name'    => get_the_title( $images ),
						'caption' => wp_get_attachment_caption( $images ),
					);
				}

				$producter['cc_images'] = $original;
				$query[]                = $producter;
			}
		}

		return new \WP_REST_Response( $query, 200 );
	}


	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function permissions_check( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check if a given request has access to get items as Admin
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function permissions_check_admin( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Search for posts
	 */
	public function search_posts() {
		try {
			$types_raw = get_post_types( array( 'public' => true ), 'objects' );
			$types     = array();

			foreach ( $types_raw as $type ) {

				$types[] = array(
					'label' => $type->label,
					'value' => ucfirst( ( $type->rest_base ) ? $type->rest_base : $type->name ),
				);
			}
			return array(
				'success' => true,
				'posts'   => $types,
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Retrieve the installed Cwicly license
	 */
	// public function get_cwicly_license() {
	// 	try {
	// 		{
	// 		if ( defined( 'CC_LICENSE_KEY' ) ) {
	// 			return CC_LICENSE_KEY;
	// 		} else {
	// 			return false;
	// 		}
	// 		}
	// 	} catch ( \Exception $e ) {
	// 		return array(
	// 			'success' => false,
	// 			'message' => $e->getMessage(),
	// 		);
	// 	}
	// }

	/**
	 * Update Global CSS
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return array
	 * @throws \Exception If settings parameter is missing.
	 */
	public function update_global_css( $request ) {
		try {
			$params = $request->get_params();
			if ( ! isset( $params['settings'] ) ) {
				throw new \Exception( 'Settings parameter is missing!' );
			}

			$settings = $params['settings'];

			if ( get_option( 'cwicly_global_css' ) == false ) {
				add_option( 'cwicly_global_css', $settings );
			} else {
				update_option( 'cwicly_global_css', $settings );
			}

			return array(
				'success' => true,
				'message' => 'Global CSS updated.',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Get WordPress Menus
	 *
	 * @param \WP_REST_Request $data Full data about the request.
	 */
	public function get_menus( $data ) {
		try {
			$settings = '';
			if ( $data->get_param( 'menus' ) ) {
				$settings = wp_get_nav_menus();
			}
			if ( $data->get_param( 'menu' ) ) {
				$menu_items = wp_get_nav_menu_items( $data->get_param( 'menu' ), array( 'update_post_term_cache' => false ) );
				if ( $menu_items ) {
					foreach ( $menu_items as &$item ) {
						$item->title      = wp_strip_all_tags( html_entity_decode( $item->title ) );
						$item->is_footer  = get_post_meta( $item->ID, '_is_footer', true );
						$item->hide_title = get_post_meta( $item->ID, '_hide_title', true );
					}
					$settings = $menu_items;
				}
			}

			return array(
				'success'  => true,
				'settings' => $settings,
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Get Icons
	 */
	public function get_svgs() {
		try {
			$settings = '';
			$svg_cols = get_option( 'cwicly_svg_cols', array() );
			if ( $svg_cols ) {
				$settings = $svg_cols;
			}

			return array(
				'success'  => true,
				'settings' => $settings,
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Get Options
	 *
	 * @param \WP_REST_Request $data Full data about the request.
	 * @return array
	 * @throws \Exception If option is not allowed.
	 */
	public function get_options( $data ) {
		try {
			if ( $data->get_param( 'getCapabilities' ) ) {
				$options = get_role( 'administrator' )->capabilities;
			}
			if ( $data->get_param( 'option' ) ) {
				// if ( $data->get_param( 'option' ) === 'theone' ) {
				// 	$options = get_option( 'cwicly_license_check' );
				// } else {

					if ( ! in_array( $data->get_param( 'option' ), self::$accepted_options, true ) ) {
						throw new \Exception( 'Option not allowed!' );
					}
					$options = get_option( $data->get_param( 'option' ) );
				// }
			}
			return array(
				'success'  => true,
				'settings' => $options,
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Update Options
	 *
	 * @param \WP_REST_Request $data Full data about the request.
	 * @return array
	 * @throws \Exception If option is not allowed.
	 */
	public function update_options( $data ) {
		try {
			$value = '';
			if ( $data->get_param( 'value' ) ) {
				$value = $data->get_param( 'value' );
			}
			if ( $data->get_param( 'option' ) ) {

				if ( ! in_array( $data->get_param( 'option' ), self::$accepted_options, true ) ) {
					throw new \Exception( 'Option not allowed!' );
				}
				update_option( $data->get_param( 'option' ), $value );
			}
			return array(
				'success' => true,
				'message' => 'Updated.',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Get All Posts that contain a cwicly block
	 */
	public function all_posts_render_ids() {
		try {
			$posts = array();
			if ( is_readable( get_stylesheet_directory() . '/block-templates/' ) ) {
				$scan = scandir( get_stylesheet_directory() . '/block-templates/' );
				if ( $scan ) {
					foreach ( $scan as $scanned ) {
						$path = get_stylesheet_directory() . '/block-templates/' . $scanned;
						if ( is_file( $path ) ) {
							$content = file_get_contents( get_stylesheet_directory() . '/block-templates/' . $scanned );
							if ( $content && strpos( $content, 'cwicly' ) !== false ) {
								$the_slug = basename( $scanned, '.html' );
								$args     = array(
									'name'        => $the_slug,
									'post_type'   => 'wp_template',
									'post_status' => 'publish',
									'numberposts' => 1,
								);
								$my_posts = get_posts( $args );
								if ( $my_posts && $my_posts[0]->ID ) {
									$posts[] = $my_posts[0]->ID;
								} else {
									$title = '';
									if ( isset( $default_template_types[ $the_slug ] ) ) {
										$title = $default_template_types[ $the_slug ]['title'];
									} else {
										$title = $the_slug;
									}

									// Gather post data.
									$my_post = array(
										'post_title'   => $title,
										'post_content' => $content,
										'post_status'  => 'publish',
										'post_author'  => 1,
										'post_type'    => 'wp_template',
										'post_name'    => $the_slug,
										'name'         => $the_slug,
									);

									// Insert the post into the database.
									$posts[] = wp_insert_post( $my_post );
								}
							}
						}
					}
				}
			} elseif ( is_readable( get_stylesheet_directory() . '/templates/' ) ) {
				$scan                   = scandir( get_stylesheet_directory() . '/templates/' );
				$default_template_types = get_default_block_template_types();
				if ( $scan ) {
					foreach ( $scan as $scanned ) {
						$path = get_stylesheet_directory() . '/templates/' . $scanned;
						if ( is_file( $path ) ) {
							$content = file_get_contents( get_stylesheet_directory() . '/templates/' . $scanned );
							if ( $content && strpos( $content, 'cwicly' ) !== false ) {
								$the_slug = basename( $scanned, '.html' );
								$args     = array(
									'name'        => $the_slug,
									'post_type'   => 'wp_template',
									'post_status' => 'publish',
									'numberposts' => 1,
								);
								$my_posts = get_posts( $args );
								if ( $my_posts && $my_posts[0]->ID ) {
									$posts[] = $my_posts[0]->ID;
								} else {
									$title       = '';
									$description = '';
									if ( isset( $default_template_types[ $the_slug ] ) ) {
										$title       = $default_template_types[ $the_slug ]['title'];
										$description = $default_template_types[ $the_slug ]['description'];
									} else {
										$title = $the_slug;
									}

									// Gather post data.
									$my_post = array(
										'post_title'   => $title,
										'post_excerpt' => $description,
										'meta_input'   => array(
											'origin' => 'theme',
										),
										'tax_input'    => array(
											'wp_theme' => wp_get_theme()->get_stylesheet(),
										),
										'post_content' => $content,
										'post_status'  => 'publish',
										'post_author'  => 1,
										'post_type'    => 'wp_template',
										'post_name'    => $the_slug,
										'name'         => $the_slug,
									);

									// Insert the post into the database.
									$posts[] = wp_insert_post( $my_post );
								}
							}
						}
					}
				}
			}
			$query = new \WP_Query(
				array(
					'orderby'                => 'date',
					'order'                  => 'ASC',
					'posts_per_page'         => -1,
					'post_type'              => 'cc_block',
					'post_status'            => 'any',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'fields'                 => 'ids',
				)
			);
			foreach ( $query->posts as $post ) {
				$posts[] = $post;
			}
			$query = new \WP_Query(
				array(
					'orderby'                => 'date',
					'order'                  => 'ASC',
					'posts_per_page'         => -1,
					'post_type'              => 'wp_block',
					'post_status'            => 'any',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'fields'                 => 'ids',
				)
			);
			foreach ( $query->posts as $post ) {
				$posts[] = $post;
			}
			$query = new \WP_Query(
				array(
					'orderby'                => 'date',
					'order'                  => 'ASC',
					'posts_per_page'         => -1,
					'post_type'              => 'wp_template',
					'post_status'            => 'any',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'fields'                 => 'ids',
				)
			);
			foreach ( $query->posts as $post ) {
				$posts[] = $post;
			}
			$query = new \WP_Query(
				array(
					'orderby'                => 'date',
					'order'                  => 'ASC',
					'posts_per_page'         => -1,
					'post_type'              => 'wp_template_part',
					'post_status'            => 'any',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'fields'                 => 'ids',
				)
			);
			foreach ( $query->posts as $post ) {
				$posts[] = $post;
			}
			$query = new \WP_Query(
				array(
					'orderby'                => 'date',
					'order'                  => 'ASC',
					'posts_per_page'         => -1,
					'post_type'              => 'any',
					'post_status'            => 'any',
					's'                      => 'cwicly/',
					'no_found_rows'          => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'fields'                 => 'ids',
				)
			);
			foreach ( $query->posts as $post ) {
				$posts[] = $post;
			}
			return $posts;
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Get posts.
	 *
	 * @param \WP_REST_Request $data Data.
	 *
	 * @return array
	 */
	public function get_specific_posts( $data ) {
		try {
			if ( $data->get_param( 'ids' ) ) {
				$ids   = $data->get_param( 'ids' );
				$ids   = explode( ',', $ids );
				$posts = array();
				foreach ( $ids as $id ) {
					$post    = get_post( $id );
					$posts[] = array(
						'content'    => $post->post_content,
						'id'         => $post->ID,
						'type'       => $post->post_type,
						'name'       => $post->post_name,
						'stylesheet' => get_stylesheet(),
						'reference'  => 'cc_block' === $post->post_type ? get_post_meta( $post->ID, 'reference', true ) : null,
						'variants'   => 'cc_block' === $post->post_type ? get_post_meta( $post->ID, 'variants', true ) : null,
						'properties' => 'cc_block' === $post->post_type ? get_post_meta( $post->ID, 'properties', true ) : null,
					);
				}
				return $posts;
			}
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Parse, Render and Update
	 *
	 * @param \WP_REST_Request $data Data.
	 *
	 * @return array
	 */
	public function parser_render( $data ) {
		try {
			if ( $data->get_param( 'renders' ) && is_array( $data->get_param( 'renders' ) ) ) {
				foreach ( $data->get_param( 'renders' ) as $id => $post ) {
					$content = get_post( $id );
					if ( $post && is_array( $post ) && ! empty( $post ) ) {
						foreach ( $post as $block_id => $html ) {

							$callback = function ( $block ) use ( $block_id, $html ) {
								if ( isset( $html['renderedHTML'] ) && isset( $block['attrs']['uniqueID'] ) && $block['attrs']['uniqueID'] === $block_id ) {
									$parsed = parse_blocks( $html['renderedHTML'] );
									if ( isset( $parsed[0] ) && isset( $parsed[0]['innerBlocks'] ) ) {
										$block['innerBlocks'] = $parsed[0]['innerBlocks'];
									}
									if ( isset( $parsed[0] ) && isset( $parsed[0]['innerContent'] ) ) {
										$block['innerContent'] = $parsed[0]['innerContent'];
									}
									if ( isset( $block['attrs']['htmlRender'] ) && $block['attrs']['htmlRender'] ) {
										unset( $block['attrs']['htmlRender'] );
									}
									if ( isset( $html['additionals'] ) ) {
										$block['attrs']['additionalClassesR'] = $html['additionals'];
									}
									if ( isset( $html['additionalsSection'] ) ) {
										$block['attrs']['additionalClassesWrapperR'] = $html['additionalsSection'];
									}
								}
								if ( isset( $block['blockName'] ) && 'cwicly/code' === $block['blockName'] ) {
									if ( ! isset( $block['attrs']['uniqueID'] ) || ! $block['attrs']['uniqueID'] ) {
										$block['attrs']['uniqueID'] = self::generateUUID();
									}
									if ( isset( $block['attrs']['code'] ) && $block['attrs']['code'] ) {
										$signature                          = \Cwicly\Signature::get_signature( 'codePHP', $block['attrs']['code'] );
										$block['attrs']['codePHPSignature'] = $signature;
									}
									if ( isset( $block['attrs']['codeJS'] ) && $block['attrs']['codeJS'] ) {
										$signature                         = \Cwicly\Signature::get_signature( 'codeJS', $block['attrs']['codeJS'] );
										$block['attrs']['codeJSSignature'] = $signature;
									}
								}

								return $block;
							};

							$content->post_content = \Cwicly\Cwicly_Parse_Blocks::get_new_content( $content, $callback );
						}
						wp_update_post(
							wp_slash(
								array(
									'ID'           => $id,
									'post_content' => $content->post_content,
								)
							),
							false,
							false
						);
					}
				}
			}
			return array(
				'success' => true,
				'message' => 'Updated.',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Transfer Breakpoint
	 *
	 * @param \WP_REST_Request $data Data.
	 *
	 * @return array
	 */
	public function breakpoint_transfer( $data ) {
		try {
			if ( $data->get_param( 'renders' ) && is_array( $data->get_param( 'renders' ) ) ) {
				foreach ( $data->get_param( 'renders' ) as $id => $post ) {
					$content = get_post( $id );
					if ( $post && is_array( $post ) && ! empty( $post ) ) {
						foreach ( $post as $block_id => $html ) {

							$callback = function ( $block ) use ( $block_id, $html ) {
								if ( isset( $html['values'] ) && $html['values'] && isset( $block['attrs']['uniqueID'] ) && $block['attrs']['uniqueID'] === $block_id ) {
									foreach ( $html['values'] as $key => $value ) {
										$block['attrs'][ $key ] = $value;
									}
								}

								return $block;
							};

							$content->post_content = \Cwicly\Cwicly_Parse_Blocks::get_new_content( $content, $callback );
						}
						wp_update_post(
							wp_slash(
								array(
									'ID'           => $id,
									'post_content' => $content->post_content,
								)
							),
							false,
							false
						);
					}
				}
			}
			return array(
				'success' => true,
				'message' => 'Updated.',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Generate UUID
	 *
	 * @return string
	 */
	public function generateUUID() {
		// Generate a random UUID.
		$uuid = sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			\mt_rand( 0, 0xffff ),
			\mt_rand( 0, 0xffff ),
			\mt_rand( 0, 0xffff ),
			\mt_rand( 0, 0x0fff ) | 0x4000,
			\mt_rand( 0, 0x3fff ) | 0x8000,
			\mt_rand( 0, 0xffff ),
			\mt_rand( 0, 0xffff ),
			\mt_rand( 0, 0xffff )
		);

		return $uuid;
	}

	/**
	 * Is Styling Render.
	 *
	 * @param \WP_REST_Request $data Data.
	 *
	 * @return array
	 */
	public function cc_isStyling_render( $data ) {
		try {
			if ( $data->get_param( 'renders' ) && is_array( $data->get_param( 'renders' ) ) ) {
				foreach ( $data->get_param( 'renders' ) as $id => $post ) {
					$content = get_post( $id );
					if ( $post && is_array( $post ) && ! empty( $post ) ) {
						foreach ( $post as $block_id => $html ) {

							$callback = function ( $block ) use ( $block_id, $html ) {
								if ( isset( $html['renderedHTML'] ) && isset( $block['attrs']['uniqueID'] ) && $block['attrs']['uniqueID'] === $block_id ) {
									$parsed = parse_blocks( $html['renderedHTML'] );
									if ( isset( $parsed[0] ) && isset( $parsed[0]['innerBlocks'] ) ) {
										$block['innerBlocks'] = $parsed[0]['innerBlocks'];
									}
									if ( isset( $parsed[0] ) && isset( $parsed[0]['innerContent'] ) ) {
										$block['innerContent'] = $parsed[0]['innerContent'];
									}
									if ( isset( $block['attrs']['htmlRender'] ) && $block['attrs']['htmlRender'] ) {
										unset( $block['attrs']['htmlRender'] );
									}
									if ( isset( $html['additionals'] ) ) {
										$block['attrs']['additionalClassesR'] = $html['additionals'];
									}
									if ( isset( $html['additionalsSection'] ) ) {
										$block['attrs']['additionalClassesWrapperR'] = $html['additionalsSection'];
									}
									if ( isset( $html['isStyling'] ) && $html['isStyling'] ) {
										$block['attrs']['isStyling'] = $html['isStyling'];
									}
								}

								return $block;
							};

							$content->post_content = \Cwicly\Cwicly_Parse_Blocks::get_new_content( $content, $callback );
						}
						wp_update_post(
							wp_slash(
								array(
									'ID'           => $id,
									'post_content' => $content->post_content,
								)
							),
							false,
							false
						);
					}
				}
			}
			return array(
				'success' => true,
				'message' => 'Updated.',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Make CSS for Single Entity.
	 *
	 * @param \WP_REST_Request $data Data.
	 *
	 * @return array
	 */
	public function single_make_css( $data ) {
		try {

			$option      = get_option( 'cwicly_breakpoints_list' );
			$breakpoints = json_decode( $option, true );

			$main_breakpoint = 'lg';

			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$allcss = '';
			if ( $data->get_param( 'css' ) ) {
				$allcss = $data->get_param( 'css' );
			}
			if ( $allcss ) {
				foreach ( $allcss as $main_key => $css ) {

					$global_fonts = array();
					$common       = array();
					$font         = array();
					$responsive   = array();

					foreach ( $breakpoints as $breakpoint => $value ) {
						$responsive[ $breakpoint ] = array();
						if ( isset( $value['isMain'] ) && $value['isMain'] ) {
							$main_breakpoint = $breakpoint;
						}
					}

					if ( $css ) {
						foreach ( $css['common'] as $value ) {
							array_push( $common, $value );
						}
						foreach ( $css['global'] as $value ) {
							array_push( $global_fonts, $value );
						}
						foreach ( $css['fontCSS'] as $value ) {
							array_push( $font, $value );
						}
						foreach ( $css as $key => $inner_css ) {
							if ( 'common' === $key || 'global' === $key || 'fontCSS' === $key ) {
								continue;
							}
							foreach ( $inner_css as $inner_value ) {
								if ( ! isset( $responsive[ $key ] ) ) {
									$responsive[ $key ] = array();
								}
								array_push( $responsive[ $key ], $inner_value );
							}
						}
					}
					$final_font        = implode( '', $font );
					$final_global_font = implode( '', $global_fonts );
					$final_common      = implode( '', $common );

					$final_responsive = array();
					foreach ( $responsive as $key => $css ) {
						$final_responsive[ $key ] = implode( '', $css );
					}

					$filename = '';
					if ( $main_key ) {
						$key_for_file = str_replace( '//', '_', $main_key );
						$key_for_file = str_replace( '/', '_', $key_for_file );
						$filename     = 'cc-' . $key_for_file . '.css';
					}

					$upload_dir = wp_upload_dir();
					$dir        = trailingslashit( $upload_dir['basedir'] ) . 'cwicly/css/';

					WP_Filesystem( false, $upload_dir['basedir'], true );

					if ( ! $wp_filesystem->is_dir( $dir ) ) {
						$wp_filesystem->mkdir( $dir );
					}

					$responsive_content = '';

					if ( isset( $final_responsive[ $main_breakpoint ] ) && $final_responsive[ $main_breakpoint ] ) {
						$responsive_content .= $final_responsive[ $main_breakpoint ];
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
							$min_widths[ $breakpoint['width'] ] = $final_responsive[ $key ];
						}
						if ( 'max' === $type ) {
							$max_widths[ $breakpoint['width'] ] = $final_responsive[ $key ];
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

					$content = $final_font . $final_global_font . $final_common . $responsive_content;

					if ( $content && $filename ) {
						file_put_contents( $dir . $filename, $content );
					} elseif ( file_exists( $dir . $filename ) ) {
						unlink( $dir . $filename );
					}
				}
			}
			return array(
				'success' => true,
				'message' => 'Updated.',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Get info for backend.
	 *
	 * @param \WP_REST_Request $data Data.
	 *
	 * @return array
	 */
	public function backend_info( $data ) {
		try {
			if ( $data->get_param( 'taxonomies' ) ) {
				global $wp_taxonomies;
				$taxonomies = array();
				$count      = 0;
				foreach ( $wp_taxonomies as $tax_name => $tax_obj ) {
					$final        = $tax_obj;
					$final->id    = $count;
					$count        = ++$count;
					$taxonomies[] = $final;
				}
				return rest_ensure_response( $taxonomies );
			} elseif ( $data->get_param( 'terms' ) ) {
				global $wp_taxonomies;
				$taxonomies = array();
				foreach ( $wp_taxonomies as $tax_name => $tax_obj ) {
					$taxonomies[ $tax_name ] = get_terms(
						array(
							'taxonomy'   => $tax_name,
							'hide_empty' => false,
							'number'     => 10,
							'search'     => $data->get_param( 'terms' ) ? $data->get_param( 'search' ) : '',
						)
					);
				}
				return rest_ensure_response( $taxonomies );
			} elseif ( $data->get_param( 'acfgroups' ) ) {
				$acfgroups = acf_get_field_groups();
				return rest_ensure_response( $acfgroups );
			} elseif ( $data->get_param( 'acffields' ) ) {
				$acfallfields = acf_get_fields( $data->get_param( 'acffields' ) );
				return rest_ensure_response( $acfallfields );
			} elseif ( $data->get_param( 'acffield' ) && $data->get_param( 'postid' ) ) {
				$acfallfield = get_field_object( $data->get_param( 'acffield' ), $data->get_param( 'postid' ) );
				if ( $data->get_param( 'svgcontent' ) && isset( $acfallfield['type'] ) && 'image' === $acfallfield['type'] && isset( $acfallfield['value']['ID'] ) ) {
					$svg_content = '';
					$svg_path    = get_attached_file( $acfallfield['value']['ID'] );

					if ( file_exists( $svg_path ) ) {
						$svg_content = file_get_contents( $svg_path );
					}

					if ( ! $svg_content ) {
						return new \WP_Error( 'error', 'SVG file not found', array( 'status' => 400 ) );
					}

					$svg = \Cwicly\Helpers::get_svg_content( $svg_content );

					$acfallfield['svg'] = $svg;
				}
				return rest_ensure_response( $acfallfield );
			} elseif ( $data->get_param( 'posttaxonomies' ) ) {
				$posttaxonomies = get_post_taxonomies( $data->get_param( 'posttaxonomies' ) );
				return rest_ensure_response( $posttaxonomies );
			} elseif ( $data->get_param( 'getterms' ) && $data->get_param( 'taxonomy' ) ) {
				$taxonomies = explode( ',', $data->get_param( 'taxonomy' ) );
				$terms      = array();

				$tax_includes = $data->get_param( 'taxIncludes' ) ? $data->get_param( 'taxIncludes' ) : array();

				if ( $tax_includes ) {
					$tax_includes = explode( ',', $tax_includes );
				}

				foreach ( $taxonomies as $taxonomy ) {
					if ( in_array( $taxonomy, $tax_includes ) || ! $tax_includes ) {
						$term = get_the_terms( $data->get_param( 'getterms' ), $taxonomy );
						if ( $term ) {
							if ( $data->get_param( 'topLevelParents' ) ) {
								foreach ( $term as $term_single ) {
									$terms[] = \Cwicly\Helpers::get_term_top_level_parent( $term_single->term_id, $taxonomy );
								}
							} else {
								$terms = array_merge( $terms, $term );
							}
						}
					}
				}
				return rest_ensure_response( $terms );
			} elseif ( $data->get_param( 'gettermscustom' ) ) {
				$args = array(
					'taxonomy'   => $data->get_param( 'taxonomy' ) ? explode( ',', $data->get_param( 'taxonomy' ) ) : array(),
					'orderby'    => $data->get_param( 'orderby' ) ? $data->get_param( 'orderby' ) : 'name',
					'order'      => $data->get_param( 'order' ) ? $data->get_param( 'order' ) : 'ASC',
					'hide_empty' => $data->get_param( 'hideempty' ) ? filter_var( $data->get_param( 'hideempty' ), FILTER_VALIDATE_BOOLEAN ) : false,
					'exclude'    => $data->get_param( 'exclude' ) ? $data->get_param( 'exclude' ) : '',
					'include'    => $data->get_param( 'include' ) ? $data->get_param( 'include' ) : '',
				);

				if ( $data->get_param( 'excludeChildren' ) && $data->get_param( 'excludeChildren' ) !== 'false' ) {
					$args['parent'] = 0;
				}

				$terms = get_terms( $args );
				return rest_ensure_response( $terms );
			} elseif ( $data->get_param( 'getwoocategorythumbnail' ) ) {
				$thumbnail_id = get_term_meta( $data->get_param( 'getwoocategorythumbnail' ), 'thumbnail_id', true );
				$image        = wp_get_attachment_url( $thumbnail_id );
				return rest_ensure_response( $image );
			} elseif ( $data->get_param( 'userroles' ) ) {
				global $wp_roles;

				$all_roles      = $wp_roles->roles;
				$editable_roles = apply_filters( 'editable_roles', $all_roles );
				return rest_ensure_response( $editable_roles );
			} elseif ( $data->get_param( 'users' ) ) {
				if ( $data->get_param( 'keyword' ) ) {
					$search = $data->get_param( 'keyword' );
					$users  = get_users( array( 'search' => $search ) );

					return rest_ensure_response( $users );
				} else {
					$final = array();

					$users = get_users( array( 'fields' => array( 'ID', 'user_login' ) ) );
					return rest_ensure_response( $users );
				}
			}
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Get post info for previewer.
	 *
	 * @param WP_REST_Request $data data.
	 *
	 * @return array
	 */
	public function dynamic_previewer( $data ) {
		try {
			$post_type = 'any';
			if ( $data->get_param( 'posttype' ) ) {
				$post_type = $data->get_param( 'posttype' );
			}
			if ( $data->get_param( 'taxonomies' ) ) {
				global $wp_taxonomies;
				$taxonomies = array();
				foreach ( $wp_taxonomies as $tax_name => $tax_obj ) {
					$taxonomies[ $tax_name ] = $tax_obj;
				}
				return array(
					'success'    => true,
					'taxonomies' => $taxonomies,
				);
			} elseif ( $data->get_param( 'terms' ) ) {
				global $wp_taxonomies;
				$taxonomies = array();
				foreach ( $wp_taxonomies as $tax_name => $tax_obj ) {
					$taxonomies[ $tax_name ] = get_terms(
						array(
							'taxonomy'   => $tax_name,
							'hide_empty' => false,
						)
					);
				}
				return array(
					'success' => true,
					'terms'   => $taxonomies,
				);
			} elseif ( $data->get_param( 'product' ) ) {
				// query for your post type.
				$post_type_query = new \WP_Query(
					array(
						'post_type'      => 'product',
						'posts_per_page' => -1,
						's'              => $data->get_param( 'keyword' ),
					)
				);
				// we need the array of posts.
				$posts_array = $post_type_query->posts;
				// create a list with needed information.
				// the key equals the ID, the value is the post_title.
				$post_title_array = wp_list_pluck( $posts_array, 'post_title', 'ID' );
				$post_type_array  = wp_list_pluck( $posts_array, 'post_type', 'ID' );
				return array(
					'success' => true,
					'title'   => $post_title_array,
					'type'    => $post_type_array,
				);
			} elseif ( $data->get_param( 'woocategories' ) ) {
				$category = get_categories(
					array(
						'taxonomy' => 'product_cat',
						'search'   => $data->get_param( 'keyword' ),
					)
				);
				return array(
					'success'    => true,
					'categories' => $category,
				);
			} elseif ( $data->get_param( 'wootags' ) ) {
				$tags = get_terms(
					'product_tag',
					array(
						'hide_empty' => false,
						'search'     => $data->get_param( 'keyword' ),
					)
				);
				return array(
					'success' => true,
					'tags'    => $tags,
				);
			} else {
				// query for your post type.
				$post_type_query = new \WP_Query(
					array(
						'post_type'      => $post_type,
						'posts_per_page' => -1,
						's'              => $data->get_param( 'keyword' ),
					)
				);
				// we need the array of posts.
				$posts_array = $post_type_query->posts;
				// create a list with needed information.
				// the key equals the ID, the value is the post_title.
				$post_title_array = wp_list_pluck( $posts_array, 'post_title', 'ID' );
				$post_type_array  = wp_list_pluck( $posts_array, 'post_type', 'ID' );
				return array(
					'success' => true,
					'title'   => $post_title_array,
					'type'    => $post_type_array,
				);
			}
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Code maker for backend.
	 *
	 * @param WP_REST_Request $data data.
	 *
	 * @return array
	 */
	public function code_maker( $data ) {
		try {
			$body = json_decode( $data->get_body(), true );

			$code      = $body['code'] ?? null;
			$post_id   = $body['postId'] ?? null;
			$post_type = $body['postType'] ?? null;

			if ( $code ) {

				ob_start();

				global $post;
				$post = get_post( $post_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );

				// ERROR.
				$error_reporting = error_reporting( E_ALL );
				$display_errors  = ini_get( 'display_errors' );
				ini_set( 'display_errors', 1 );

				try {
					if ( ! \Cwicly\Capabilities::execute_eval() ) {
						$final = '';
					} else {
						$eval   = eval( ' ?>' . $code . '<?php ' );
						$output = ob_get_clean();
					}
				} catch ( \Exception $e ) {
					wp_reset_postdata();
					ob_get_clean();
					return array(
						'success' => false,
						'message' => 'Exception: ' . $e->getMessage(),
					);
				} catch ( \ParseError $e ) {
					wp_reset_postdata();
					ob_get_clean();
					return array(
						'success' => false,
						'message' => 'ParseError: ' . $e->getMessage(),
					);
				} catch ( \Error $e ) {
					wp_reset_postdata();
					ob_get_clean();
					return array(
						'success' => false,
						'message' => 'Error: ' . $e->getMessage(),
					);
				}

				// RESET ERROR.
				ini_set( 'display_errors', $display_errors );
				error_reporting( $error_reporting );

				wp_reset_postdata();

				return array(
					'success' => true,
					'code'    => $output,
				);
			} else {
				return array(
					'success' => true,
					'code'    => '',
				);
			}
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Filter the Query.
	 *
	 * @param WP_REST_Request $data data.
	 *
	 * @return array
	 */
	public function filter_query( $data ) {
		try {

			$args = array();
			$args = \Cwicly\Helpers::filter_args_maker(
				$data->get_param( 'filterData' ),
				$data->get_param( 'filterInclude' ),
				$data->get_param( 'filterExclude' ),
				$data->get_param( 'filterParent' ),
				$data->get_param( 'filterOrderBy' ),
				$data->get_param( 'filterOrder' ),
				$data->get_param( 'filterChildless' ),
				$data->get_param( 'filterHideEmpty' )
			);

			$query = new \WP_Term_Query( $args );

			foreach ( $query->terms as $term ) {
				$term->name = wp_specialchars_decode( $term->name );
			}

			return array(
				'success' => true,
				'query'   => $query->terms,
				'args'    => $args,
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Backend Cart
	 *
	 * @param WP_REST_Request $data data.
	 *
	 * @return array
	 */
	public function cart_backend( $data ) {
		try {
			if ( $data->get_param( 'variations' ) ) {
				$final = array();
				foreach ( wc_get_attribute_taxonomies() as $values ) {
					$term_names = get_terms(
						array(
							'taxonomy' => 'pa_' . $values->attribute_name,
							'fields'   => 'names',
						)
					);
					$final[]    = array(
						'label' => $values->attribute_label,
						'value' => $term_names[0],
					);
				}
				return array(
					'success'    => true,
					'attributes' => $final,
				);
			} else {
				$query_prep = new \WC_Product_Query( array( 'limit' => 5 ) );

				$products_query = $query_prep->get_products();
				$query          = array();
				foreach ( $products_query as $product ) {
					$producter                     = $product->get_data();
					$producter['cc_featuredimage'] = get_the_post_thumbnail_url( $producter['id'], 'full' );

					$original       = array();
					$main_image     = array();
					$main_image[]   = $product->get_image_id();
					$gallery_images = $product->get_gallery_image_ids();
					$attachment_ids = array_merge( $main_image, $gallery_images );
					foreach ( $attachment_ids as $images ) {
						$original[] = array(
							'src'     => wp_get_attachment_url( $images ),
							'name'    => get_the_title( $images ),
							'caption' => wp_get_attachment_caption( $images ),
						);
					}

					$producter['cc_images'] = $original;
					$query[]                = $producter;
				}

				return array(
					'success' => true,
					'query'   => $query,
				);
			}
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Export all components in JSON format.
	 *
	 * @return array
	 */
	public function export_components() {
		$cc_blocks = get_posts(
			array(
				'post_type'      => 'cc_block',
				'posts_per_page' => -1,
			)
		);

		$collection = array();
		$folders    = get_option( 'cwicly_components_folders' );

		foreach ( $cc_blocks as $cc_block ) {
			$properties = get_post_meta( $cc_block->ID, 'properties', true );
			$variants   = get_post_meta( $cc_block->ID, 'variants', true );
			$reference  = get_post_meta( $cc_block->ID, 'reference', true );
			$preview    = get_post_meta( $cc_block->ID, 'preview', true );

			$property_groups  = get_post_meta( $cc_block->ID, 'propertyGroups', true );
			$variant_groups   = get_post_meta( $cc_block->ID, 'variantGroups', true );
			$style_variations = get_post_meta( $cc_block->ID, 'styleVariations', true );

			$collection[] = array(
				'title'     => $cc_block->post_title,
				'content'   => $cc_block->post_content,
				'meta'      => array(
					'properties'      => $properties,
					'variants'        => $variants,
					'reference'       => $reference,
					'preview'         => $preview,
					'propertyGroups'  => $property_groups,
					'variantGroups'   => $variant_groups,
					'styleVariations' => $style_variations,
				),
				'post_type' => $cc_block->post_type,
			);
		}

		$collection = array(
			'components' => $collection,
			'folders'    => $folders,
		);

		return array(
			'success' => true,
			'export'  => $collection,
		);
	}

	/**
	 * Get necessary attributes for Post Editor Template
	 *
	 * @param \WP_REST_Request $data Data.
	 * @return array
	 */
	public function template_post_content( $data ) {
		$template_slug = $data->get_param( 'template_slug' );
		$post_link     = $data->get_param( 'post_link' );
		$post_id       = $data->get_param( 'post_id' );

		$template = '';
		if ( $template_slug ) {
			$template = new \WP_Query(
				array(
					'post_type'      => 'wp_template',
					'posts_per_page' => 1,
					'name'           => $template_slug,
				)
			);

			if ( $template->posts && isset( $template->posts[0] ) ) {
				$template = $template->posts[0]->post_content;
				$template = parse_blocks( $template );
				$template = $this->find_block( $template, 'cwicly/content' );

				if ( $template ) {
					$template = array(
						array(
							'attributes' => $template['attrs'],
							'isCwicly'   => true,
							'name'       => 'cwicly/content',
						),
					);
				} else {
					$template = $this->find_block( $template, 'core/post-content' );

					if ( $template ) {
						$template = array(
							array(
								'attributes' => $template['attrs'],
								'isCwicly'   => false,
								'name'       => 'core/post-content',
							),
						);
					}
				}
			}
		}

		return $template;
	}

	/**
	 * Find Block
	 *
	 * @param array $template Template.
	 * @return array
	 */
	public function find_block( $template, $block_name ) {
		$result = array();

		foreach ( $template as $block ) {
			if ( $block_name === $block['blockName'] ) {
				$result = $block;
				break;
			} elseif ( isset( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as $inner_block ) {
					if ( $block_name === $inner_block['blockName'] ) {
						$result = $inner_block;
						break;
					} elseif ( isset( $inner_block['innerBlocks'] ) ) {
						$result = $this->find_block( $inner_block['innerBlocks'], $block_name );
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Get necessary attributes for Component
	 *
	 * @param \WP_REST_Request $data Data.
	 * @return array
	 */
	public function component_extras( $data ) {
		try {
			if ( $data->get_param( 'ref' ) ) {
				$ref = $data->get_param( 'ref' );

				// Get "variants" meta.
				$variants = get_post_meta( $ref, 'variants', true );

				// Get "properties" meta.
				$properties = get_post_meta( $ref, 'properties', true );

				return array(
					'success'    => true,
					'properties' => $properties,
					'variants'   => $variants,
				);
			}
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	public function tailwind_purge( $data ) {
		try {
			if ( $data->get_param( 'stylesheet' ) ) {
				cc_make_tailwind_stylesheet( $data->get_param( 'stylesheet' ) );
			}
			if ( $data->get_param( 'classes' ) ) {
				update_option( 'cwicly_tailwind_classes', $data->get_param( 'classes' ) );
			}
			return array(
				'success' => true,
				'message' => 'Updated.',
			);
		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}
}
