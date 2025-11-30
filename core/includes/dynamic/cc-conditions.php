<?php
/**
 * Cwicly Conditions
 *
 * Functions for creating and managing the repeaters
 *
 * @package Cwicly\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cwicly Hide for Logged In
 *
 * Function to check if User is logged in and Block is hidden if true
 *
 * @package Cwicly\Functions
 * @version 1.1
 *
 * @param array $attributes Block attributes.
 */
function cc_hide_logged_in( $attributes ) {
	$hide_logged_in = false;
	if ( ( isset( $attributes['hideLoggedIn'] ) && $attributes['hideLoggedIn'] && ! is_user_logged_in() ) || ( isset( $attributes['hideLoggedIn'] ) && ! $attributes['hideLoggedIn'] ) || ! isset( $attributes['hideLoggedIn'] ) ) {
		$hide_logged_in = true;
	}

	return $hide_logged_in;
}

/**
 * Cwicly Hide for Guest
 *
 * Function to check if User is a guest in and Block is hidden if true
 *
 * @package Cwicly\Functions
 * @version 1.1
 *
 * @param array $attributes Block attributes.
 */
function cc_hide_guest( $attributes ) {
	$hide_guest = false;
	if ( ( isset( $attributes['hideGuest'] ) && $attributes['hideGuest'] && is_user_logged_in() ) || ( isset( $attributes['hideGuest'] ) && ! $attributes['hideGuest'] ) || ! isset( $attributes['hideGuest'] ) ) {
		$hide_guest = true;
	}

	return $hide_guest;
}

/**
 * Cwicly Conditions Maker
 *
 * Functions for creating static and dynamic conditions
 *
 * @package Cwicly\Functions
 * @version 1.1
 *
 * @param array  $attributes Block attributes.
 * @param object $block Block data.
 */
function cc_conditions_maker( $attributes, $block ) {
	$final_conditions = array();
	$hide_conditions  = array();
	$condition_type   = '&&';
	if ( isset( $attributes['hideConditionsType'] ) && $attributes['hideConditionsType'] ) {
		$condition_type = $attributes['hideConditionsType'];
	}

	global $post;
	$oldpost = $post;
	if ( isset( $block->context['postId'] ) && $block->context['postId'] ) {
		$post = get_post( $block->context['postId'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	if ( CC_WOOCOMMERCE ) {
		global $product;
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( get_the_ID() );
		}

		$old_product = $product;
		if ( isset( $block->context['product'] ) && $block->context['product'] ) {
			$product = $block->context['product'];
		}
	}

	if ( isset( $attributes['hideConditions'] ) && $attributes['hideConditions'] ) {
		$current_user    = wp_get_current_user();
		$hide_conditions = $attributes['hideConditions'];
		foreach ( $hide_conditions as $value ) {
			if ( $value['condition'] && $value['operator'] ) {
				$condition = '';

				// WOOCOMMERCE.
				if ( CC_WOOCOMMERCE ) {
					if ( $product ) {
						if ( 'wooshippingtaxable' === $value['condition'] ) {
							$condition = $product->is_shipping_taxable();
						}
						if ( 'wooshippingclass' === $value['condition'] ) {
							$condition = $product->get_shipping_class();
						}
						if ( 'wooshippingclassid' === $value['condition'] ) {
							$condition = $product->get_shipping_class_id();
						}
						if ( 'wootaxclass' === $value['condition'] ) {
							$condition = $product->get_tax_class();
						}
						if ( 'wootaxstatus' === $value['condition'] ) {
							$condition = $product->get_tax_status();
						}
						if ( 'woowidth' === $value['condition'] ) {
							$condition = $product->get_width();
						}
						if ( 'wooheigth' === $value['condition'] ) {
							$condition = $product->get_height();
						}
						if ( 'woolength' === $value['condition'] ) {
							$condition = $product->get_length();
						}
						if ( 'wooweight' === $value['condition'] ) {
							$condition = $product->get_weight();
						}
						if ( 'woopurchasable' === $value['condition'] ) {
							$condition = $product->is_purchasable();
						}
						if ( 'woostockquantity' === $value['condition'] ) {
							$condition = $product->get_stock_quantity();
						}
						if ( 'woosoldindividually' === $value['condition'] ) {
							$condition = $product->is_sold_individually();
						}
						if ( 'woobackordersallowed' === $value['condition'] ) {
							$condition = $product->backorders_allowed();
						}
						if ( 'woodownloadable' === $value['condition'] ) {
							$condition = $product->is_downloadable();
						}
						if ( 'woovirtual' === $value['condition'] ) {
							$condition = $product->is_virtual();
						}
						if ( 'woofeatured' === $value['condition'] ) {
							$condition = $product->is_featured();
						}
						if ( 'woomanagestock' === $value['condition'] ) {
							$condition = $product->managing_stock();
						}
						if ( 'wooonsale' === $value['condition'] ) {
							if ( 'variable' === $product->get_type() ) {
								$variations = get_children(
									array(
										'post_parent' => $product->get_id(),
										'post_type'   => 'product_variation',
									)
								);

								$is_on_sale = false;

								foreach ( $variations as $variation_id ) {
									$variation = wc_get_product( $variation_id );

									if ( $variation->is_on_sale() ) {
										$is_on_sale = true;
										break;
									}
								}

								if ( $is_on_sale ) {
									$condition = true;
								} else {
									$condition = false;
								}
							} elseif ( $product->is_on_sale() ) {
								$condition = true;
							} else {
								$condition = false;
							}
						}
						if ( 'woohasgalleryitems' === $value['condition'] ) {
							$condition = $product->get_gallery_image_ids();
							if ( is_array( $condition ) && count( $condition ) > 0 ) {
								$condition = true;
							} else {
								$condition = false;
							}
						}
						if ( 'wooreviewsallowed' === $value['condition'] ) {
							$condition = $product->get_reviews_allowed();
						}
						if ( 'wooshippingrequired' === $value['condition'] ) {
							$condition = $product->needs_shipping();
						}
						if ( 'woocatalogvisibility' === $value['condition'] ) {
							$condition = $product->get_catalog_visibility();
						}
						if ( 'woostockstatus' === $value['condition'] ) {
							$condition = $product->get_stock_status();
						}
						if ( 'woototalsales' === $value['condition'] ) {
							$condition = $product->get_total_sales();
						}
						if ( 'woolowstockamount' === $value['condition'] ) {
							$condition = $product->get_low_stock_amount();
						}
						if ( 'wootype' === $value['condition'] ) {
							$condition = $product->get_type();
						}
						if ( 'woosaleprice' === $value['condition'] ) {
							$condition = $product->get_sale_price();
						}
						if ( 'woodescription' === $value['condition'] ) {
							$condition = $product->get_description();
						}
						if ( 'wooshortdescription' === $value['condition'] ) {
							$condition = $product->get_short_description();
						}
						if ( 'wooquantityinstock' === $value['condition'] ) {
							$condition = $product->get_stock_quantity();
						}
						if ( 'woomaxpurchasequantity' === $value['condition'] ) {
							$condition = $product->get_max_purchase_quantity();
						}
						if ( 'woominpurchasequantity' === $value['condition'] ) {
							$condition = $product->get_min_purchase_quantity();
						}
						if ( 'woosalefrom' === $value['condition'] ) {
							$condition = $product->get_date_on_sale_from();
						}
						if ( 'woosaletill' === $value['condition'] ) {
							$condition = $product->get_date_on_sale_to();
						}
						if ( 'woometa' === $value['condition'] ) {
							$condition = $product->get_sale_price();
						}
						if ( 'woosku' === $value['condition'] ) {
							$condition = $product->get_sku();
						}
						if ( 'wooreviews' === $value['condition'] ) {
							$condition = $product->get_sale_price();
						}
						if ( 'wooratingcount' === $value['condition'] ) {
							$condition = $product->get_rating_count();
						}
						if ( 'wooreviewcount' === $value['condition'] ) {
							$condition = $product->get_review_count();
						}
						if ( 'wooaveragerating' === $value['condition'] ) {
							$condition = $product->get_average_rating();
						}
						if ( 'woototalsold' === $value['condition'] ) {
							$condition = get_post_meta( $product->id, 'total_sales', true );
						}
						if ( 'woohasrelatedproducts' === $value['condition'] ) {
							$related_products = wc_get_related_products( $product->get_id() );
							if ( ! empty( $related_products ) ) {
								$condition = true;
							}
						}
					}
				}
				// WOOCOMMERCE.

				if ( 'authorname' === $value['condition'] ) {
					$condition = get_the_author();
				}
				if ( 'date' === $value['condition'] ) {
					$condition = date_i18n( 'm/d/Y' );
				}
				if ( 'dayweek' === $value['condition'] ) {
					$condition = date_i18n( 'l' );
				}
				if ( 'daymonth' === $value['condition'] ) {
					$condition = date_i18n( 'd' );
				}
				if ( 'time' === $value['condition'] ) {
					$condition = date_i18n( 'H:i:s' );
				}
				if ( 'postid' === $value['condition'] ) {
					$condition = strval( get_the_ID() );
				}
				if ( 'postparentid' === $value['condition'] ) {
					$condition = wp_get_post_parent_id( get_the_ID() );
					$condition = strval( $condition );
				}
				if ( 'posttitle' === $value['condition'] ) {
					$condition = get_the_title();
				}
				if ( 'postfeaturedimage' === $value['condition'] ) {
					if ( has_post_thumbnail() ) {
						$condition = 'true';
					} else {
						$condition = 'false';
					}
				}
				if ( 'postcomments' === $value['condition'] ) {
					$condition = strval( get_comments_number() );
				}
				if ( 'postexcerpt' === $value['condition'] ) {
					if ( has_excerpt() && get_the_excerpt() ) {
						$condition = 'true';
					} else {
						$condition = 'false';
					}
				}
				if ( 'postcontent' === $value['condition'] ) {
					if ( get_the_content() ) {
						$condition = 'true';
					} else {
						$condition = 'false';
					}
				}
				if ( 'posttype' === $value['condition'] ) {
					$condition = get_post_type();
				}
				if ( 'postcategory' === $value['condition'] ) {
					$categories    = get_the_category();
					$category_list = array();
					if ( ! empty( $categories ) ) {
						foreach ( $categories as $category ) {
							$category_list[] = $category->name;
						}
					}
				}
				if ( 'postterm' === $value['condition'] ) {
					$taxonomies = get_taxonomies();
					$term_list  = array();
					foreach ( $taxonomies as $taxonomy ) {
						$post_terms = get_the_terms( get_the_ID(), $taxonomy );
						if ( $post_terms ) {
							foreach ( $post_terms as $term ) {
								$term_list[] = $term->term_id;
							}
						}
					}
				}
				if ( 'posttag' === $value['condition'] ) {
					$post_tags = wp_get_post_terms( get_the_ID() );
					$tag_list  = array();
					if ( $post_tags ) {
						foreach ( $post_tags as $tag ) {
							$tag_list[] = strtolower( $tag->name );
						}
					}
				}
				if ( 'username' === $value['condition'] ) {
					$condition = $current_user->user_login;
				}
				if ( 'userid' === $value['condition'] ) {
					$condition = strval( $current_user->ID );
				}
				if ( 'usercapabilities' === $value['condition'] ) {
					if ( current_user_can( $value['data'] ) ) {
						$condition = true;
					} else {
						$condition = false;
					}
				}
				if ( 'commentsopen' === $value['condition'] ) {
					if ( comments_open() ) {
						$condition = 'true';
					} else {
						$condition = 'false';
					}
				}
				if ( 'commentapproved' === $value['condition'] && isset( $block->context['commentQuery'] ) ) {
					if ( '1' === $block->context['commentQuery']->comment_approved ) {
						$condition = true;
					} else {
						$condition = false;
					}
				}
				if ( 'commentsregistration' === $value['condition'] ) {
					if ( get_option( 'comment_registration' ) ) {
						$condition = true;
					} else {
						$condition = false;
					}
				}
				if ( 'commentisauthor' === $value['condition'] ) {
					$commenter = wp_get_current_commenter();
					if ( isset( $commenter['comment_author_email'] ) && ! $commenter['comment_author_email'] ) {
						$commenter['comment_author_email'] =
							isset( $current_user->user_email ) ? $current_user->user_email : '';
					}
					if ( isset( $block->context['commentQuery']->comment_author_email ) && ! empty( $commenter ) ) {
						if ( $block->context['commentQuery']->comment_author_email === $commenter['comment_author_email'] ) {
							$condition = true;
						} else {
							$condition = false;
						}
					} else {
						$condition = false;
					}
				}
				if ( 'queryissinglepage' === $value['condition'] ) {
					if ( isset( $block->context['queryTotal'] ) ) {
						if ( 1 === intval( $block->context['queryTotal'] ) ) {
							$condition = true;
						} else {
							$condition = false;
						}
					}
				}
				if ( 'queryhasitems' === $value['condition'] ) {
					if ( 'cwicly/query' === $block->parsed_block['blockName'] ) {
						$condition = 'cc_pass';
					} elseif ( isset( $block->context['hasPosts'] ) && $block->context['hasPosts'] ) {
						$condition = true;
					} else {
						$condition = false;
					}
				}
				if ( 'queryhasprevpage' === $value['condition'] ) {
					if ( isset( $block->context['queryId'] ) && $block->context['queryId'] ) {
						if ( ! \Cwicly\Helpers::block_query_prev_next( $block, 'prev' ) ) {
							$condition = false;
						} else {
							$condition = true;
						}
					} elseif ( ! \Cwicly\Helpers::block_query_prev_next( $block, 'prev' ) ) {
						$condition = false;
					} else {
						$condition = true;
					}
				}
				if ( 'queryhasnextpage' === $value['condition'] ) {
					if ( isset( $block->context['queryId'] ) && $block->context['queryId'] ) {
						if ( ! \Cwicly\Helpers::block_query_prev_next( $block, 'next' ) ) {
							$condition = false;
						} else {
							$condition = true;
						}
					} elseif ( ! \Cwicly\Helpers::block_query_prev_next( $block, 'next' ) ) {
						$condition = false;
					} else {
						$condition = true;
					}
				}
				if ( 'querycount' === $value['condition'] ) {
					if ( isset( $block->context['queryCount'] ) ) {
						$condition = strval( $block->context['queryCount'] );
					}
				}
				if ( 'functionreturn' === $value['condition'] ) {
					if ( isset( $value['function'] ) && $value['function'] ) {
						$condition = \Cwicly\Helpers::echo( $value['function'] );
					}
				}
				if ( 'urlparameter' === $value['condition'] ) {
					if ( isset( $value['key'] ) && $value['key'] ) {
						if ( isset( $_GET[ $value['key'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
							$prep_key = sanitize_text_field( wp_unslash( $_GET[ $value['key'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
							if ( $prep_key ) {
								$key = htmlspecialchars( $prep_key, ENT_QUOTES, 'UTF-8' );
								if ( 'true' === $value['operator'] || 'false' === $value['operator'] ) {
									$condition = filter_var( $key, FILTER_VALIDATE_BOOLEAN );
								} else {
									$condition = $key;
								}
							}
						}
					}
				}
				if ( isset( $value['data'] ) && is_string( $value['data'] ) && $value['data'] && '{' == $value['data'][0] && '}' == $value['data'][ strlen( $value['data'] ) - 1 ] ) {
					$value['data'] = cc_parser( '' . $value['data'] . '', $attributes, $block );
				}
				switch ( $value['operator'] ) {
					case '===':
						if ( 'cookie' === $value['condition'] ) {
							$loop = true;
							foreach ( $_COOKIE as $key => $val ) {
								if ( $key === $value['data'] ) {
									$final_conditions[] = 'true';
									$loop               = false;

									break;
								}
							}
							if ( $loop ) {
								$final_conditions[] = 'false';
							}
						} elseif ( 'usercapabilities' === $value['condition'] ) {
							if ( $condition ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'dayweek' === $value['condition'] ) {
							$date_now      = strtotime( 'today' );
							$date_compared = strtotime( '' . $value['data'] . ' this week' );
							if ( $date_now === $date_compared ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'postcategory' === $value['condition'] ) {
							if ( in_array( get_cat_name( $value['data'] ), $category_list ) ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'posttag' === $value['condition'] ) {
							if ( in_array( $value['data'], $tag_list ) ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'postterm' === $value['condition'] ) {
							if ( isset( $value['data']['value'] ) ) {
								if ( in_array( $value['data']['value'], $term_list ) ) {
									$final_conditions[] = 'true';
								} else {
									$final_conditions[] = 'false';
								}
							}
						} elseif ( 'userrole' === $value['condition'] ) {
							if ( in_array( $value['data'], $current_user->roles, true ) ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'woosalefrom' === $value['condition'] || 'woosaletill' === $value['condition'] ) {
							$date_now      = strtotime( $condition );
							$date_compared = strtotime( $value['data'] );
							if ( $date_now === $date_compared ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'acf' === $value['condition'] ) {
							if ( isset( $value['acfGroup'] ) && isset( $value['acfField'] ) ) {
								$location = false;
								if ( isset( $value['acfLocation'] ) ) {
									if ( 'currentpost' === $value['acfLocation'] ) {
										$location = get_the_ID();
									} elseif ( 'postid' === $value['acfLocation'] && isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									} elseif ( 'currentuser' === $value['acfLocation'] ) {
										$location = 'user_' . get_current_user_id() . '';
									} elseif ( 'currentauthor' === $value['acfLocation'] ) {
										$location = 'user_' . get_the_author_meta( 'ID' ) . '';
									} elseif ( 'option' === $value['acfLocation'] ) {
										$location = 'option';
									} elseif ( ( 'termid' === $value['acfLocation'] || 'taxterm' === $value['acfLocation'] ) && isset( $block->context['taxterms'] ) ) {
										$location = $block->context['taxterms'];
									} elseif ( 'termquery' === $value['acfLocation'] && isset( $block->context['termQuery'] ) ) {
										$location = $block->context['termQuery'];
									} elseif ( 'userquery' === $value['acfLocation'] && isset( $block->context['userQuery'] ) ) {
										$location = $block->context['userQuery'];
									} elseif ( 'taxonomyterm' === $value['acfLocation'] && isset( $value['acfLocationID'] ) && isset( $value['acfLocationID']['value'] ) ) {
										$term     = get_term( $value['acfLocationID']['value'] );
										$location = $term->taxonomy . '_' . $term->term_id;
									} elseif ( isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									}
								}

								if ( isset( $value['acfRepeaterField'] ) && 'cc_overall' !== $value['acfRepeaterField'] ) {
									$field          = '';
									$repeater_array = array();
									if ( $block->context && isset( $block->context['repeaters'] ) && $block->context['repeaters'] ) {
										$repeater_array = $block->context['repeaters'];
									}
									$row = 0;
									if ( $block->context && isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
										$row = $block->context['repeater_row'];
									}
									if ( $repeater_array ) {
										if ( $repeater_array[ $row ][ $value['acfRepeaterField'] ] ) {
											$field = \Cwicly\ACF::processor( $repeater_array[ $row ][ $value['acfRepeaterField'] ], null, $attributes, $block->parsed_block['blockName'] );
										}
									}
									if ( $field === $value['data'] ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								} else {
									$field_object = get_field_object( sanitize_text_field( $value['acfField'] ), $location );
									if ( isset( $field_object['value'] ) ) {
										$field = $field_object['value'];
									} else {
										$field = $field_object;
									}
									$field = \Cwicly\ACF::processor( $field, null, $attributes, $block->parsed_block['blockName'], false, array(), $field_object );

									if ( is_array( $field ) ) {
										$field = implode( ' ', $field );
									} else {
										$field = strval( $field );
									}
									if ( $field === $value['data'] ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								}
							}
						} elseif ( 'device' === $value['condition'] ) {
							$detect = new Mobile_Detect();
							if ( 'mobile' === $value['data'] ) {
								if ( $detect->isMobile() ) {
									$final_conditions[] = 'true';
								} else {
									$final_conditions[] = 'false';
								}
							}
							if ( 'tablet' === $value['data'] ) {
								if ( $detect->isTablet() ) {
									$final_conditions[] = 'true';
								} else {
									$final_conditions[] = 'false';
								}
							}
							if ( 'desktop' === $value['data'] ) {
								if ( ! $detect->isMobile() && ! $detect->isTablet() ) {
									$final_conditions[] = 'true';
								} else {
									$final_conditions[] = 'false';
								}
							}
						} elseif ( $condition === $value['data'] ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case '!=':
						if ( 'cookie' === $value['condition'] ) {
							$loop = true;
							foreach ( $_COOKIE as $key => $val ) {
								if ( $key === $value['data'] ) {
									$final_conditions[] = 'false';
									$loop               = false;

									break;
								}
							}
							if ( $loop ) {
								$final_conditions[] = 'true';
							}
						} elseif ( 'woosalefrom' === $value['condition'] || 'woosaletill' === $value['condition'] ) {
							$date_now      = strtotime( $condition );
							$date_compared = strtotime( $value['data'] );
							if ( $date_now != $date_compared ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'dayweek' === $value['condition'] ) {
							$date_now      = strtotime( 'today' );
							$date_compared = strtotime( '' . $value['data'] . ' this week' );
							if ( $date_now != $date_compared ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'postcategory' === $value['condition'] ) {
							if ( ! in_array( $value['data'], $category_list ) ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'posttag' === $value['condition'] ) {
							if ( ! in_array( $value['data'], $tag_list ) ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'postterm' === $value['condition'] ) {
							if ( isset( $value['data']['value'] ) ) {
								if ( ! in_array( $value['data']['value'], $term_list ) ) {
									$final_conditions[] = 'true';
								} else {
									$final_conditions[] = 'false';
								}
							}
						} elseif ( 'userrole' === $value['condition'] ) {
							if ( ! in_array( $value['data'], $current_user->roles, true ) ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'acf' === $value['condition'] ) {
							if ( isset( $value['acfGroup'] ) && isset( $value['acfField'] ) ) {
								$location = '';
								if ( isset( $value['acfLocation'] ) ) {
									if ( 'currentpost' === $value['acfLocation'] ) {
										$location = get_the_ID();
									} elseif ( 'postid' === $value['acfLocation'] && isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									} elseif ( 'currentuser' === $value['acfLocation'] ) {
										$location = 'user_' . get_current_user_id() . '';
									} elseif ( 'currentauthor' === $value['acfLocation'] ) {
										$location = 'user_' . get_the_author_meta( 'ID' ) . '';
									} elseif ( 'option' === $value['acfLocation'] ) {
										$location = 'option';
									} elseif ( ( 'termid' === $value['acfLocation'] || 'taxterm' === $value['acfLocation'] ) && isset( $block->context['taxterms'] ) ) {
										$location = $block->context['taxterms'];
									} elseif ( 'termquery' === $value['acfLocation'] && isset( $block->context['termQuery'] ) ) {
										$location = $block->context['termQuery'];
									} elseif ( 'userquery' === $value['acfLocation'] && isset( $block->context['userQuery'] ) ) {
										$location = $block->context['userQuery'];
									} elseif ( 'taxonomyterm' === $value['acfLocation'] && isset( $value['acfLocationID'] ) && isset( $value['acfLocationID']['value'] ) ) {
										$term     = get_term( $value['acfLocationID']['value'] );
										$location = $term->taxonomy . '_' . $term->term_id;
									} elseif ( isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									}
								}

								if ( isset( $value['acfRepeaterField'] ) && 'cc_overall' !== $value['acfRepeaterField'] ) {
									$field          = '';
									$repeater_array = array();
									if ( $block->context && isset( $block->context['repeaters'] ) && $block->context['repeaters'] ) {
										$repeater_array = $block->context['repeaters'];
									}
									$row = 0;
									if ( $block->context && isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
										$row = $block->context['repeater_row'];
									}
									if ( $repeater_array ) {
										if ( $repeater_array[ $row ][ $value['acfRepeaterField'] ] ) {
											$field = \Cwicly\ACF::processor( $repeater_array[ $row ][ $value['acfRepeaterField'] ], null, $attributes, $block->parsed_block['blockName'] );
										}
									}
									if ( $field != $value['data'] ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								} else {
									$field_object = get_field_object( sanitize_text_field( $value['acfField'] ), $location );
									if ( isset( $field_object['value'] ) ) {
										$field = $field_object['value'];
									} else {
										$field = $field_object;
									}
									$field = \Cwicly\ACF::processor( $field, null, $attributes, $block->parsed_block['blockName'], false, array(), $field_object );

									if ( is_array( $field ) ) {
										$field = implode( ' ', $field );
									} else {
										$field = strval( $field );
									}

									if ( $field != $value['data'] ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								}
							}
						} elseif ( 'device' === $value['condition'] ) {
							$detect = new Mobile_Detect();
							if ( 'mobile' === $value['data'] ) {
								if ( $detect->isMobile() ) {
									$final_conditions[] = 'false';
								} else {
									$final_conditions[] = 'true';
								}
							}
							if ( 'tablet' === $value['data'] ) {
								if ( $detect->isTablet() ) {
									$final_conditions[] = 'false';
								} else {
									$final_conditions[] = 'true';
								}
							}
							if ( 'desktop' === $value['data'] ) {
								if ( ! $detect->isMobile() && ! $detect->isTablet() ) {
									$final_conditions[] = 'false';
								} else {
									$final_conditions[] = 'true';
								}
							}
						} elseif ( $condition != $value['data'] ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case 'contains':
						if ( 'cookie' === $value['condition'] ) {
							$loop = true;
							foreach ( $_COOKIE as $key => $val ) {
								if ( false !== strpos( $key, $value['data'] ) ) {
									$final_conditions[] = 'true';
									$loop               = false;

									break;
								}
							}
							if ( $loop ) {
								$final_conditions[] = 'false';
							}
						} elseif ( 'acf' === $value['condition'] ) {
							if ( isset( $value['acfGroup'] ) && isset( $value['acfField'] ) ) {
								$location = false;
								if ( isset( $value['acfLocation'] ) ) {
									if ( 'currentpost' === $value['acfLocation'] ) {
										$location = get_the_ID();
									} elseif ( 'postid' === $value['acfLocation'] && isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									} elseif ( 'currentuser' === $value['acfLocation'] ) {
										$location = 'user_' . get_current_user_id() . '';
									} elseif ( 'currentauthor' === $value['acfLocation'] ) {
										$location = 'user_' . get_the_author_meta( 'ID' ) . '';
									} elseif ( 'option' === $value['acfLocation'] ) {
										$location = 'option';
									} elseif ( ( 'termid' === $value['acfLocation'] || 'taxterm' === $value['acfLocation'] ) && isset( $block->context['taxterms'] ) ) {
										$location = $block->context['taxterms'];
									} elseif ( 'termquery' === $value['acfLocation'] && isset( $block->context['termQuery'] ) ) {
										$location = $block->context['termQuery'];
									} elseif ( 'userquery' === $value['acfLocation'] && isset( $block->context['userQuery'] ) ) {
										$location = $block->context['userQuery'];
									} elseif ( 'taxonomyterm' === $value['acfLocation'] && isset( $value['acfLocationID'] ) && isset( $value['acfLocationID']['value'] ) ) {
										$term     = get_term( $value['acfLocationID']['value'] );
										$location = $term->taxonomy . '_' . $term->term_id;
									} elseif ( isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									}
								}

								if ( isset( $value['acfRepeaterField'] ) && 'cc_overall' !== $value['acfRepeaterField'] ) {
									$field          = '';
									$repeater_array = array();
									if ( $block->context && isset( $block->context['repeaters'] ) && $block->context['repeaters'] ) {
										$repeater_array = $block->context['repeaters'];
									}
									$row = 0;
									if ( $block->context && isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
										$row = $block->context['repeater_row'];
									}
									if ( $repeater_array ) {
										if ( $repeater_array[ $row ][ $value['acfRepeaterField'] ] ) {
											$field = \Cwicly\ACF::processor( $repeater_array[ $row ][ $value['acfRepeaterField'] ], null, $attributes, $block->parsed_block['blockName'] );
										}
									}
									if ( false !== strpos( $field, $value['data'] ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								} else {
									$field_object = get_field_object( sanitize_text_field( $value['acfField'] ), $location );
									if ( isset( $field_object['value'] ) ) {
										$field = $field_object['value'];
									} else {
										$field = $field_object;
									}
									$field = \Cwicly\ACF::processor( $field, null, $attributes, $block->parsed_block['blockName'], false, array(), $field_object );

									if ( is_array( $field ) ) {
										$field = implode( ' ', $field );
									} else {
										$field = strval( $field );
									}

									if ( false !== strpos( $field, $value['data'] ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								}
							}
						} elseif ( false !== strpos( $condition, $value['data'] ) ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case 'notcontain':
						if ( 'cookie' === $value['condition'] ) {
							$loop = true;
							foreach ( $_COOKIE as $key => $val ) {
								if ( false === strpos( $key, $value['data'] ) ) {
									$final_conditions[] = 'true';
									$loop               = false;

									break;
								}
							}
							if ( $loop ) {
								$final_conditions[] = 'false';
							}
						} elseif ( 'acf' === $value['condition'] ) {
							if ( isset( $value['acfGroup'] ) && isset( $value['acfField'] ) ) {
								$location = false;
								if ( isset( $value['acfLocation'] ) ) {
									if ( 'currentpost' === $value['acfLocation'] ) {
										$location = get_the_ID();
									} elseif ( 'postid' === $value['acfLocation'] && isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									} elseif ( 'currentuser' === $value['acfLocation'] ) {
										$location = 'user_' . get_current_user_id() . '';
									} elseif ( 'currentauthor' === $value['acfLocation'] ) {
										$location = 'user_' . get_the_author_meta( 'ID' ) . '';
									} elseif ( 'option' === $value['acfLocation'] ) {
										$location = 'option';
									} elseif ( ( 'termid' === $value['acfLocation'] || 'taxterm' === $value['acfLocation'] ) && isset( $block->context['taxterms'] ) ) {
										$location = $block->context['taxterms'];
									} elseif ( 'termquery' === $value['acfLocation'] && isset( $block->context['termQuery'] ) ) {
										$location = $block->context['termQuery'];
									} elseif ( 'userquery' === $value['acfLocation'] && isset( $block->context['userQuery'] ) ) {
										$location = $block->context['userQuery'];
									} elseif ( 'taxonomyterm' === $value['acfLocation'] && isset( $value['acfLocationID'] ) && isset( $value['acfLocationID']['value'] ) ) {
										$term     = get_term( $value['acfLocationID']['value'] );
										$location = $term->taxonomy . '_' . $term->term_id;
									} elseif ( isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									}
								}

								if ( isset( $value['acfRepeaterField'] ) && 'cc_overall' !== $value['acfRepeaterField'] ) {
									$field          = '';
									$repeater_array = array();
									if ( $block->context && isset( $block->context['repeaters'] ) && $block->context['repeaters'] ) {
										$repeater_array = $block->context['repeaters'];
									}
									$row = 0;
									if ( $block->context && isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
										$row = $block->context['repeater_row'];
									}
									if ( $repeater_array ) {
										if ( $repeater_array[ $row ][ $value['acfRepeaterField'] ] ) {
											$field = \Cwicly\ACF::processor( $repeater_array[ $row ][ $value['acfRepeaterField'] ], null, $attributes, $block->parsed_block['blockName'] );
										}
									}
									if ( false === strpos( $field, $value['data'] ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								} else {
									$field_object = get_field_object( sanitize_text_field( $value['acfField'] ), $location );
									if ( isset( $field_object['value'] ) ) {
										$field = $field_object['value'];
									} else {
										$field = $field_object;
									}
									$field = \Cwicly\ACF::processor( $field, null, $attributes, $block->parsed_block['blockName'], false, array(), $field_object );

									if ( is_array( $field ) ) {
										$field = implode( ' ', $field );
									} else {
										$field = strval( $field );
									}

									if ( false === strpos( $field, $value['data'] ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								}
							}
						} elseif ( false === strpos( $condition, $value['data'] ) ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case 'before':
						if ( 'dayweek' === $value['condition'] ) {
							$date_now      = strtotime( 'today' );
							$date_compared = strtotime( '' . $value['data'] . ' this week' );
							if ( $date_now < $date_compared ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'woosalefrom' === $value['condition'] || 'woosaletill' === $value['condition'] ) {
							$date_now      = strtotime( $value['condition'] );
							$date_compared = strtotime( $value['data'] );
							if ( $date_now < $date_compared ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( $condition < $value['data'] ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case 'after':
						if ( 'dayweek' === $value['condition'] ) {
							$date_now      = strtotime( 'today' );
							$date_compared = strtotime( '' . $value['data'] . ' this week' );
							if ( $date_now > $date_compared ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'woosalefrom' === $value['condition'] || 'woosaletill' === $value['condition'] ) {
							$date_now      = strtotime( $value['condition'] );
							$date_compared = strtotime( $value['data'] );
							if ( $date_now > $date_compared ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( $condition > $value['data'] ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case '<':
						if ( $condition < $value['data'] ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case '>':
						if ( $condition > $value['data'] ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case '>=':
						if ( $condition >= $value['data'] ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case '<=':
						if ( $condition <= $value['data'] ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case 'empty':
						if ( 'acf' === $value['condition'] ) {
							if ( isset( $value['acfGroup'] ) && isset( $value['acfField'] ) ) {
								$location = false;
								if ( isset( $value['acfLocation'] ) ) {
									if ( 'currentpost' === $value['acfLocation'] ) {
										$location = get_the_ID();
									} elseif ( 'postid' === $value['acfLocation'] && isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									} elseif ( 'currentuser' === $value['acfLocation'] ) {
										$location = 'user_' . get_current_user_id() . '';
									} elseif ( 'currentauthor' === $value['acfLocation'] ) {
										$location = 'user_' . get_the_author_meta( 'ID' ) . '';
									} elseif ( 'option' === $value['acfLocation'] ) {
										$location = 'option';
									} elseif ( ( 'termid' === $value['acfLocation'] || 'taxterm' === $value['acfLocation'] ) && isset( $block->context['taxterms'] ) ) {
										$location = $block->context['taxterms'];
									} elseif ( 'termquery' === $value['acfLocation'] && isset( $block->context['termQuery'] ) ) {
										$location = $block->context['termQuery'];
									} elseif ( 'userquery' === $value['acfLocation'] && isset( $block->context['userQuery'] ) ) {
										$location = $block->context['userQuery'];
									} elseif ( 'taxonomyterm' === $value['acfLocation'] && isset( $value['acfLocationID'] ) && isset( $value['acfLocationID']['value'] ) ) {
										$term     = get_term( $value['acfLocationID']['value'] );
										$location = $term->taxonomy . '_' . $term->term_id;
									} elseif ( isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									}
								}

								if ( isset( $value['acfRepeaterField'] ) && 'cc_overall' !== $value['acfRepeaterField'] ) {
									$field          = '';
									$repeater_array = array();
									if ( $block->context && isset( $block->context['repeaters'] ) && $block->context['repeaters'] ) {
										$repeater_array = $block->context['repeaters'];
									}
									$row = 0;
									if ( $block->context && isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
										$row = $block->context['repeater_row'];
									}
									if ( $repeater_array ) {
										if ( $repeater_array[ $row ][ $value['acfRepeaterField'] ] ) {
											$field = \Cwicly\ACF::processor( $repeater_array[ $row ][ $value['acfRepeaterField'] ], null, $attributes, $block->parsed_block['blockName'] );
										}
									}

									if ( empty( $field ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								} else {
									$field_object = get_field_object( sanitize_text_field( $value['acfField'] ), $location );
									if ( isset( $field_object['value'] ) ) {
										$field = $field_object['value'];
									} else {
										$field = $field_object;
									}
									$field = \Cwicly\ACF::processor( $field, null, $attributes, $block->parsed_block['blockName'], false, array(), $field_object );

									if ( empty( $field ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								}
							}
						}

						break;
					case 'notempty':
						if ( 'acf' === $value['condition'] ) {
							if ( isset( $value['acfGroup'] ) && isset( $value['acfField'] ) ) {
								$location = false;
								if ( isset( $value['acfLocation'] ) ) {
									if ( 'currentpost' === $value['acfLocation'] ) {
										$location = get_the_ID();
									} elseif ( 'postid' === $value['acfLocation'] && isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									} elseif ( 'currentuser' === $value['acfLocation'] ) {
										$location = 'user_' . get_current_user_id() . '';
									} elseif ( 'currentauthor' === $value['acfLocation'] ) {
										$location = 'user_' . get_the_author_meta( 'ID' ) . '';
									} elseif ( 'option' === $value['acfLocation'] ) {
										$location = 'option';
									} elseif ( ( 'termid' === $value['acfLocation'] || 'taxterm' === $value['acfLocation'] ) && isset( $block->context['taxterms'] ) ) {
										$location = $block->context['taxterms'];
									} elseif ( 'termquery' === $value['acfLocation'] && isset( $block->context['termQuery'] ) ) {
										$location = $block->context['termQuery'];
									} elseif ( 'userquery' === $value['acfLocation'] && isset( $block->context['userQuery'] ) ) {
										$location = $block->context['userQuery'];
									} elseif ( 'taxonomyterm' === $value['acfLocation'] && isset( $value['acfLocationID'] ) && isset( $value['acfLocationID']['value'] ) ) {
										$term     = get_term( $value['acfLocationID']['value'] );
										$location = $term->taxonomy . '_' . $term->term_id;
									} elseif ( isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									}
								}

								if ( isset( $value['acfRepeaterField'] ) && 'cc_overall' !== $value['acfRepeaterField'] ) {
									$field          = '';
									$repeater_array = array();
									if ( $block->context && isset( $block->context['repeaters'] ) && $block->context['repeaters'] ) {
										$repeater_array = $block->context['repeaters'];
									}
									$row = 0;
									if ( $block->context && isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
										$row = $block->context['repeater_row'];
									}
									if ( $repeater_array ) {
										if ( $repeater_array[ $row ][ $value['acfRepeaterField'] ] ) {
											$field = \Cwicly\ACF::processor( $repeater_array[ $row ][ $value['acfRepeaterField'] ], null, $attributes, $block->parsed_block['blockName'] );
										}
									}
									if ( ! empty( $field ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								} else {
									$field_object = get_field_object( sanitize_text_field( $value['acfField'] ), $location );
									if ( isset( $field_object['value'] ) ) {
										$field = $field_object['value'];
									} else {
										$field = $field_object;
									}
									$field = \Cwicly\ACF::processor( $field, null, $attributes, $block->parsed_block['blockName'], false, array(), $field_object );

									if ( ! empty( $field ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								}
							}
						}

						break;
					case 'true':
						if ( 'cc_pass' === $condition ) {
							$final_conditions[] = 'true';
						} elseif ( 'shortcode' === $value['condition'] ) {
							$shortcode = do_shortcode( '[' . $value['data'] . ']' );
							if ( $shortcode ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'acf' === $value['condition'] ) {
							if ( isset( $value['acfGroup'] ) && isset( $value['acfField'] ) ) {
								$location = false;
								if ( isset( $value['acfLocation'] ) ) {
									if ( 'currentpost' === $value['acfLocation'] ) {
										$location = get_the_ID();
									} elseif ( 'postid' === $value['acfLocation'] && isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									} elseif ( 'currentuser' === $value['acfLocation'] ) {
										$location = 'user_' . get_current_user_id() . '';
									} elseif ( 'currentauthor' === $value['acfLocation'] ) {
										$location = 'user_' . get_the_author_meta( 'ID' ) . '';
									} elseif ( 'option' === $value['acfLocation'] ) {
										$location = 'option';
									} elseif ( ( 'termid' === $value['acfLocation'] || 'taxterm' === $value['acfLocation'] ) && isset( $block->context['taxterms'] ) ) {
										$location = $block->context['taxterms'];
									} elseif ( 'termquery' === $value['acfLocation'] && isset( $block->context['termQuery'] ) ) {
										$location = $block->context['termQuery'];
									} elseif ( 'userquery' === $value['acfLocation'] && isset( $block->context['userQuery'] ) ) {
										$location = $block->context['userQuery'];
									} elseif ( 'taxonomyterm' === $value['acfLocation'] && isset( $value['acfLocationID'] ) && isset( $value['acfLocationID']['value'] ) ) {
										$term     = get_term( $value['acfLocationID']['value'] );
										$location = $term->taxonomy . '_' . $term->term_id;
									} elseif ( isset( $value['acfLocationID'] ) ) {
										$location = $value['acfLocationID'];
									}
								}

								if ( isset( $value['acfRepeaterField'] ) && 'cc_overall' !== $value['acfRepeaterField'] ) {
									$field          = '';
									$repeater_array = array();
									if ( $block->context && isset( $block->context['repeaters'] ) && $block->context['repeaters'] ) {
										$repeater_array = $block->context['repeaters'];
									}
									$row = 0;
									if ( $block->context && isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
										$row = $block->context['repeater_row'];
									}
									if ( $repeater_array ) {
										if ( $repeater_array[ $row ][ $value['acfRepeaterField'] ] ) {
											$field = \Cwicly\ACF::processor( $repeater_array[ $row ][ $value['acfRepeaterField'] ], null, $attributes, $block->parsed_block['blockName'] );
										}
									}
									if ( true === boolval( $field ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								} else {
									$field_object = get_field_object( sanitize_text_field( $value['acfField'] ), $location );
									if ( isset( $field_object['value'] ) ) {
										$field = $field_object['value'];
									} else {
										$field = $field_object;
									}
									$field = \Cwicly\ACF::processor( $field, null, $attributes, $block->parsed_block['blockName'], false, array(), $field_object );

									if ( is_array( $field ) ) {
										if ( isset( $field[0] ) ) {
											$field = $field[0];
										}
									} else {
										$field = strval( $field );
									}
									if ( true === boolval( $field ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								}
							}
						} elseif ( true === $condition ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
					case 'false':
						if ( 'cc_pass' === $condition ) {
							$final_conditions[] = 'true';
						} elseif ( 'shortcode' === $value['condition'] ) {
							$shortcode = do_shortcode( '[' . $value['data'] . ']' );
							if ( ! $shortcode ) {
								$final_conditions[] = 'true';
							} else {
								$final_conditions[] = 'false';
							}
						} elseif ( 'acf' === $value['condition'] ) {
							$location = false;
							if ( isset( $value['acfLocation'] ) ) {
								if ( 'currentpost' === $value['acfLocation'] ) {
									$location = get_the_ID();
								} elseif ( 'postid' === $value['acfLocation'] && isset( $value['acfLocationID'] ) ) {
									$location = $value['acfLocationID'];
								} elseif ( 'currentuser' === $value['acfLocation'] ) {
									$location = 'user_' . get_current_user_id() . '';
								} elseif ( 'currentauthor' === $value['acfLocation'] ) {
									$location = 'user_' . get_the_author_meta( 'ID' ) . '';
								} elseif ( 'option' === $value['acfLocation'] ) {
									$location = 'option';
								} elseif ( ( 'termid' === $value['acfLocation'] || 'taxterm' === $value['acfLocation'] ) && isset( $block->context['taxterms'] ) ) {
									$location = $block->context['taxterms'];
								} elseif ( 'termquery' === $value['acfLocation'] && isset( $block->context['termQuery'] ) ) {
									$location = $block->context['termQuery'];
								} elseif ( 'userquery' === $value['acfLocation'] && isset( $block->context['userQuery'] ) ) {
									$location = $block->context['userQuery'];
								} elseif ( 'taxonomyterm' === $value['acfLocation'] && isset( $value['acfLocationID'] ) && isset( $value['acfLocationID']['value'] ) ) {
									$term     = get_term( $value['acfLocationID']['value'] );
									$location = $term->taxonomy . '_' . $term->term_id;
								} elseif ( isset( $value['acfLocationID'] ) ) {
									$location = $value['acfLocationID'];
								}
							}

							if ( isset( $value['acfGroup'] ) && isset( $value['acfField'] ) ) {
								if ( isset( $value['acfRepeaterField'] ) && 'cc_overall' !== $value['acfRepeaterField'] ) {
									$field          = '';
									$repeater_array = array();
									if ( $block->context && isset( $block->context['repeaters'] ) && $block->context['repeaters'] ) {
										$repeater_array = $block->context['repeaters'];
									}
									$row = 0;
									if ( $block->context && isset( $block->context['repeater_row'] ) && $block->context['repeater_row'] ) {
										$row = $block->context['repeater_row'];
									}
									if ( $repeater_array ) {
										if ( $repeater_array[ $row ][ $value['acfRepeaterField'] ] ) {
											$field = \Cwicly\ACF::processor( $repeater_array[ $row ][ $value['acfRepeaterField'] ], null, $attributes, $block->parsed_block['blockName'] );
										}
									}
									if ( false === boolval( $field ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								} else {
									$field_object = get_field_object( sanitize_text_field( $value['acfField'] ), $location );
									if ( isset( $field_object['value'] ) ) {
										$field = $field_object['value'];
									} else {
										$field = $field_object;
									}
									$field = \Cwicly\ACF::processor( $field, null, $attributes, $block->parsed_block['blockName'], false, array(), $field_object );

									if ( is_array( $field ) ) {
										if ( isset( $field[0] ) ) {
											$field = $field[0];
										}
									} else {
										$field = strval( $field );
									}
									if ( false === boolval( $field ) ) {
										$final_conditions[] = 'true';
									} else {
										$final_conditions[] = 'false';
									}
								}
							}
						} elseif ( false === $condition ) {
							$final_conditions[] = 'true';
						} else {
							$final_conditions[] = 'false';
						}

						break;
				}
			}
		}
	}

	if ( CC_WOOCOMMERCE ) {
		$product = $old_product;
	}

	$post = $oldpost; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	switch ( $condition_type ) {
		case '&&':
			if ( $final_conditions && in_array( 'false', $final_conditions, true ) ) {
				return false;
			} else {
				return true;
			}

			break;
		case '||':
			if ( $final_conditions && in_array( 'true', $final_conditions, true ) ) {
				return true;
			} else {
				return false;
			}

			break;
	}
}
