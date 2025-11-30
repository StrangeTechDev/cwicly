<?php
/**
 * Theme Maker
 *
 * @since     1.0.8.3
 * @package Cwicly
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Maker
 *
 * @param array  $block_templates Block templates.
 * @param array  $query Query.
 * @param string $template_type Template type.
 */
function cc_themer_maker( $block_templates, $query, $template_type ) {
	if ( ! Cwicly\Helpers::is_rest() && ! is_admin() && 'wp_template' === $template_type ) {
		global $post;
		$conditions = get_option( 'cwicly_conditions' );
		if ( $conditions && is_string( $conditions ) ) {
			$conditions = json_decode( $conditions );
		}
		$name            = array();
		$slug            = array();
		$custom_template = array();

		$final_templates = array();
		$template_list   = array();

		$templater = array();

		$priorities = array();

		$status_codes = array();

		$override_page_template = array();

		$woocommerce_templates = array(
			'single-product',
			'product-search-results',
			'taxonomy-product_cat',
			'archive-product',
			'taxonomy-product_tag',
			'taxonomy-product_attribute',
			'page-cart',
			'cart',
			'checkout',
			'account',
			'order-confirmation',
		);

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- disable sniff for Snake Case since we are using JS variables.
		if ( isset( $conditions ) && isset( $conditions->include ) && $conditions->include ) {
			foreach ( $conditions->include as $template => $value ) {
				$send = array();
				if ( 'true' === $value->all ) {
					$send[] = 'true';
				}
				if ( count( $value->singular ) > 0 ) {
					foreach ( $value->singular as $condition ) {
						$unique_send = false;
						if ( isset( $condition->target ) && $condition->target ) {
							if ( 'all' === $condition->target ) {
								if ( is_404() ) {
									$unique_send = 'true';
								}
								if ( is_front_page() ) {
									$unique_send = 'true';
								}
								if ( is_singular() ) {
									$unique_send = 'true';
								}
							} elseif ( '404' === $condition->target ) {
								if ( is_404() ) {
									$unique_send = 'true';
								} else {
									$unique_send = 'false';
								}
							} elseif ( 'frontPage' === $condition->target ) {
								if ( is_front_page() ) {
									$unique_send = 'true';
								} else {
									$unique_send = 'false';
								}
							} elseif ( is_singular() ) {
								if ( $condition->target && is_singular( $condition->target ) ) {
									if ( ! isset( $condition->data ) || ( $condition->data && 'all' === $condition->data ) ) {
										$unique_send = 'true';
									} else {
										$unique_send = 'false';
									}
									if ( isset( $condition->data ) && $condition->data && 'all' !== $condition->data ) {
										if ( ! isset( $condition->data ) || ( $condition->data && 'directchildof' === $condition->data ) ) {
											$parent_id   = get_the_ID();
											$parent_page = wp_get_post_parent_id( $parent_id );
											if ( isset( $condition->extra ) && intval( $condition->extra ) === $parent_page ) {
												$unique_send = 'true';
											} else {
												$unique_send = 'false';
											}
										} elseif ( ! isset( $condition->extra ) || ( $condition->extra && 'all' === $condition->extra ) ) {
											if ( ! is_array( $condition->data ) && has_term( '', $condition->data ) ) {
												$unique_send = 'true';
											} elseif ( is_array( $condition->data ) ) {
												foreach ( $condition->data as $data ) {
													if ( $post->ID === $data ) {
														$unique_send = 'true';
													}
												}
											} else {
												$unique_send = 'false';
											}
										} elseif ( isset( $condition->extra ) && $condition->extra && 'all' !== $condition->extra ) {
											if ( has_term( $condition->extra, $condition->data ) ) {
												$unique_send = 'true';
											} else {
												$unique_send = 'false';
											}
											if ( isset( $condition->extraData ) && $condition->extraData && 'all' !== $condition->extraData ) {
												if ( get_the_ID() === $condition->extraData ) {
													$unique_send = 'true';
												} else {
													$unique_send = 'false';
												}
											}
										}
									}
								}
							}
						}
						$send[] = $unique_send;
					}
				}
				if ( count( $value->archive ) > 0 ) {
					foreach ( $value->archive as $condition ) {
						$unique_send = false;

						if ( isset( $condition->target ) && 'search' === $condition->target && is_search() ) {
							$unique_send = 'true';
						} elseif ( isset( $condition->target ) && 'author' === $condition->target && is_author() ) {
							if ( isset( $condition->data ) && $condition->data && 'all' !== $condition->data ) {
								if ( get_the_author_meta( 'ID' ) === $condition->data ) {
									$unique_send = 'true';
								} else {
									$unique_send = 'false';
								}
							} else {
								$unique_send = 'true';
							}
						} elseif ( is_archive() ) {
							if ( isset( $condition->target ) && $condition->target ) {
								$post_type = '';
								if ( isset( get_queried_object()->taxonomy ) ) {
									$post_type = get_taxonomy( get_queried_object()->taxonomy )->object_type[0];
								} elseif ( is_post_type_archive( $condition->target ) ) {
									$post_type = $condition->target;
								}
								if ( 'all' === $condition->target ) {
									$unique_send = 'true';
								} elseif ( ( ! isset( $condition->data ) || ( $condition->data && 'all' === $condition->data ) ) && $condition->target === $post_type ) {
									$unique_send = 'true';
								} elseif ( $post_type === $condition->target && isset( $condition->data ) && get_queried_object()->taxonomy === $condition->data && ( ! isset( $condition->extra ) || ( $condition->extra && 'all' === $condition->extra ) ) ) {
									$unique_send = 'true';
								} elseif ( $post_type === $condition->target && isset( $condition->data ) && get_queried_object()->taxonomy === $condition->data && isset( $condition->extra ) && get_queried_object()->term_id === $condition->extra ) {
									$unique_send = 'true';
								}
							}
						}
						$send[] = $unique_send;
					}
				}
				if ( count( $value->author ) > 0 ) {
					foreach ( $value->author as $condition ) {
						$unique_send = false;
						if ( is_author() ) {
							if ( true === $condition ) {
								$unique_send = 'true';
							} elseif ( isset( $condition->target ) && $condition->target && is_author( $condition->target ) ) {
								$unique_send = 'true';
							}
						}
						$send[] = $unique_send;
					}
				}
				if ( count( $value->custom ) > 0 ) {
					$current_user = wp_get_current_user();
					foreach ( $value->custom as $condition ) {
						$unique_send = false;
						$conditioner = '';
						if ( 'date' === $condition->target ) {
							$conditioner = gmdate( 'm/d/Y' );
						}
						if ( 'dayweek' === $condition->target ) {
							$conditioner = gmdate( 'l' );
						}
						if ( 'daymonth' === $condition->target ) {
							$conditioner = gmdate( 'd' );
						}
						if ( 'time' === $condition->target ) {
							$conditioner = gmdate( 'H:i:s' );
						}
						if ( 'username' === $condition->target ) {
							$conditioner = $current_user->user_login;
						}
						if ( 'userid' === $condition->target ) {
							$conditioner = strval( $current_user->ID );
						}
						if ( 'usercapabilities' === $condition->target ) {
							if ( current_user_can( $condition->extraData ) ) {
								$conditioner = 'true';
							}
						}
						if ( 'urlparameter' === $condition->target ) {
							if ( isset( $condition->key ) && $condition->key ) {
								if ( isset( $_GET[ $condition->key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
									$prep_key = sanitize_text_field( wp_unslash( $_GET[ $condition->key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
									if ( $prep_key ) {
										$key = htmlspecialchars( $prep_key, ENT_QUOTES, 'UTF-8' );
										if ( 'true' === $condition->extra || 'false' === $condition->extra ) {
											$conditioner = filter_var( $key, FILTER_VALIDATE_BOOLEAN );
										} else {
											$conditioner = $key;
										}
									}
								}
							}
						}
						switch ( $condition->extra ) {
							case '===':
								if ( 'cookie' === $condition->target ) {
									$loop = true;
									foreach ( $_COOKIE as $key => $val ) {
										if ( $key === $condition->extraData ) {
											$unique_send = 'true';
											$loop        = false;

											break;
										}
									}
									if ( $loop ) {
										$unique_send = 'false';
									}
								} elseif ( 'dayweek' === $condition->target ) {
									$date_now      = strtotime( 'today' );
									$date_compared = strtotime( '' . $condition->extraData . ' this week' );
									if ( $date_now === $date_compared ) {
										$unique_send = 'true';
									}
								} elseif ( 'userrole' === $condition->target ) {
									if ( in_array( $condition->extraData, $current_user->roles, true ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'usercapabilities' === $condition->target ) {
									if ( current_user_can( $condition->extraData ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( $field === $condition->extraData ) {
											$unique_send = 'true';
										}
									}
								} elseif ( $conditioner === $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '!=':
								if ( 'cookie' === $condition->target ) {
									$loop = true;
									foreach ( $_COOKIE as $key => $val ) {
										if ( $key === $condition->extraData ) {
											$unique_send = 'false';
											$loop        = false;

											break;
										}
									}
									if ( $loop ) {
										$unique_send = 'true';
									}
								} elseif ( 'dayweek' === $condition->target ) {
									$date_now      = strtotime( 'today' );
									$date_compared = strtotime( '' . $condition->extraData . ' this week' );
									if ( $date_now != $date_compared ) {
										$unique_send = 'true';
									}
								} elseif ( 'userrole' === $condition->target ) {
									if ( ! in_array( $condition->extraData, $current_user->roles, true ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'usercapabilities' === $condition->target ) {
									if ( ! current_user_can( $condition->extraData ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( $field != $condition->extraData ) {
											$unique_send = 'true';
										}
									}
								} elseif ( $conditioner != $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case 'contains':
								if ( 'cookie' === $condition->target ) {
									$loop = true;
									foreach ( $_COOKIE as $key => $val ) {
										if ( false !== strpos( $key, $condition->extraData ) ) {
											$unique_send = 'true';
											$loop        = false;

											break;
										}
									}
									if ( $loop ) {
										$unique_send = 'false';
									}
								} elseif ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( false !== strpos( $field, $condition->extraData ) ) {
											$unique_send = 'true';
										}
									}
								} elseif ( false !== strpos( $conditioner, $condition->extraData ) ) {
									$unique_send = 'true';
								}

								break;
							case 'notcontain':
								if ( 'cookie' === $condition->target ) {
									$loop = true;
									foreach ( $_COOKIE as $key => $val ) {
										if ( false === strpos( $key, $condition->extraData ) ) {
											$unique_send = 'true';
											$loop        = false;

											break;
										}
									}
									if ( $loop ) {
										$unique_send = 'false';
									}
								} elseif ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( false === strpos( $field, $condition->extraData ) ) {
											$unique_send = 'true';
										}
									}
								} elseif ( false === strpos( $conditioner, $condition->extraData ) ) {
									$unique_send = 'true';
								}

								break;
							case 'before':
								if ( 'dayweek' === $condition->target ) {
									$date_now      = strtotime( 'today' );
									$date_compared = strtotime( '' . $condition->extraData . ' this week' );
									if ( $date_now < $date_compared ) {
										$unique_send = 'true';
									}
								} elseif ( $conditioner < $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case 'after':
								if ( 'dayweek' === $condition->target ) {
									$date_now      = strtotime( 'today' );
									$date_compared = strtotime( '' . $condition->extraData . ' this week' );
									if ( $date_now > $date_compared ) {
										$unique_send = 'true';
									}
								} elseif ( $conditioner > $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '<':
								if ( $conditioner < $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '>':
								if ( $conditioner > $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '>=':
								if ( $conditioner >= $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '<=':
								if ( $conditioner <= $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case 'empty':
								if ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( empty( $field ) ) {
											$unique_send = 'true';
										}
									}
								}

								break;
							case 'notempty':
								if ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( ! empty( $field ) ) {
											$unique_send = 'true';
										}
									}
								}

								break;
							case 'true':
								if ( 'shortcode' === $condition->target ) {
									if ( 'true' === do_shortcode( '[' . $condition->extraData . ']' ) ) {
										$unique_send = 'true';
									}
								}
								if ( 'loggedin' === $condition->target ) {
									if ( is_user_logged_in() ) {
										$unique_send = 'true';
									}
								}
								if ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( true === filter_var( $field, FILTER_VALIDATE_BOOLEAN ) ) {
											$unique_send = 'true';
										}
									}
								}

								break;
							case 'false':
								if ( 'shortcode' === $condition->target ) {
									if ( 'false' === do_shortcode( '[' . $condition->extraData . ']' ) ) {
										$unique_send = 'true';
									}
								}
								if ( 'loggedin' === $condition->target ) {
									if ( ! is_user_logged_in() ) {
										$unique_send = 'true';
									}
								}
								if ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( false === filter_var( $field, FILTER_VALIDATE_BOOLEAN ) ) {
											$unique_send = 'true';
										}
									}
								}

								break;
						}
						$send[] = $unique_send;
					}
				}
				if ( isset( $value->priority ) && $value->priority ) {
					$priorities[ $template ] = $value->priority;
				} else {
					$priorities[ $template ] = 0;
				}
				if ( isset( $value->overridePageTemplate ) && $value->overridePageTemplate ) {
					$override_page_template[ $template ] = true;
				}
				if ( isset( $value->statusCode ) && $value->statusCode ) {
					$status_codes[ $template ] = $value->statusCode;
				}
				if ( $value->includeCondition && 'and' === $value->includeCondition && array_unique( $send ) == array( 'true' ) ) {
					$theme = get_stylesheet();
					if ( in_array( $template, $woocommerce_templates, true ) ) {
						$theme = 'woocommerce/woocommerce';
					}
					$custom_template[ $template ] = array( get_block_template( $theme . '//' . $template . '', 'wp_template' ) );
					$templater[]                  = $template;

					$args     = array(
						'name'        => $template,
						'post_type'   => 'wp_template',
						'post_status' => 'publish',
						'numberposts' => 1,
					);
					$my_posts = get_posts( $args );
					if ( $my_posts ) :
						$id                = $my_posts[0]->ID;
						$name[ $template ] = get_the_title( $id );
						$slug[ $template ] = $template;
					endif;
				} elseif ( $value->includeCondition && 'or' === $value->includeCondition && in_array( 'true', $send, true ) ) {
					$theme = get_stylesheet();
					if ( in_array( $template, $woocommerce_templates, true ) ) {
						$theme = 'woocommerce/woocommerce';
					}
					$custom_template[ $template ] = array( get_block_template( $theme . '//' . $template . '', 'wp_template' ) );
					$templater[]                  = $template;

					$args     = array(
						'name'        => $template,
						'post_type'   => 'wp_template',
						'post_status' => 'publish',
						'numberposts' => 1,
					);
					$my_posts = get_posts( $args );
					if ( $my_posts ) :
						$id                = $my_posts[0]->ID;
						$name[ $template ] = get_the_title( $id );
						$slug[ $template ] = $template;
					endif;
				}
				// }
			}
		}
		// phpcs:enable

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- disable sniff for Snake Case since we are using JS variables.
		foreach ( $templater as $templaterer ) {
			if ( $templaterer && $conditions->exclude->$templaterer ) {
				$exclude    = array();
				$no_exclude = array();
				$value      = $conditions->exclude->$templaterer;
				if ( 'true' === $value->all ) {
					$exclude[]    = 'true';
					$no_exclude[] = 'false';
				} else {
					$no_exclude[] = 'true';
				}
				if ( count( $value->singular ) > 0 ) {
					foreach ( $value->singular as $condition ) {
						$unique_send = 'false';
						if ( isset( $condition->target ) && $condition->target ) {
							if ( 'all' === $condition->target ) {
								if ( is_404() ) {
									$unique_send = 'true';
								}
								if ( is_front_page() ) {
									$unique_send = 'true';
								}
								if ( is_singular() ) {
									$unique_send = 'true';
								}
							} elseif ( '404' === $condition->target ) {
								if ( is_404() ) {
									$unique_send = 'true';
								} else {
									$unique_send = 'false';
								}
							} elseif ( 'frontPage' === $condition->target ) {
								if ( is_front_page() ) {
									$unique_send = 'true';
								} else {
									$unique_send = 'false';
								}
							} elseif ( is_singular() ) {
								if ( $condition->target && is_singular( $condition->target ) ) {
									if ( ! isset( $condition->data ) || ( $condition->data && 'all' === $condition->data ) ) {
										$unique_send = 'true';
									} else {
										$unique_send = 'false';
									}
									if ( isset( $condition->data ) && $condition->data && 'all' != $condition->data ) {
										if ( ! isset( $condition->data ) || ( $condition->data && 'directchildof' === $condition->data ) ) {
											$parent_id   = get_the_ID();
											$parent_page = wp_get_post_parent_id( $parent_id );
											if ( isset( $condition->extra ) && intval( $condition->extra ) === $parent_page ) {
												$unique_send = 'true';
											} else {
												$unique_send = 'false';
											}
										} elseif ( ! isset( $condition->extra ) || ( $condition->extra && 'all' === $condition->extra ) ) {
											if ( ! is_array( $condition->data ) && has_term( '', $condition->data ) ) {
												$unique_send = 'true';
											} elseif ( is_array( $condition->data ) ) {
												foreach ( $condition->data as $data ) {
													if ( $post->ID === $data ) {
														$unique_send = 'true';
													}
												}
											} else {
												$unique_send = 'false';
											}
										} elseif ( isset( $condition->extra ) && $condition->extra && 'all' != $condition->extra ) {
											if ( has_term( $condition->extra, $condition->data ) ) {
												$unique_send = 'true';
											} else {
												$unique_send = 'false';
											}
											if ( isset( $condition->extraData ) && $condition->extraData && 'all' != $condition->extraData ) {
												if ( get_the_ID() === $condition->extraData ) {
													$unique_send = 'true';
												} else {
													$unique_send = 'false';
												}
											}
										}
									}
								}
							}
						}
						$exclude[] = $unique_send;
					}
					$no_exclude[] = 'false';
				} else {
					$no_exclude[] = 'true';
				}
				if ( count( $value->archive ) > 0 ) {
					foreach ( $value->archive as $condition ) {
						$unique_send = 'false';

						if ( isset( $condition->target ) && 'search' === $condition->target && is_search() ) {
							$unique_send = 'true';
						} elseif ( isset( $condition->target ) && 'author' === $condition->target && is_author() ) {
							if ( isset( $condition->data ) && $condition->data && 'all' != $condition->data ) {
								if ( get_the_author_meta( 'ID' ) === $condition->data ) {
									$unique_send = 'true';
								} else {
									$unique_send = 'false';
								}
							} else {
								$unique_send = 'true';
							}
						} elseif ( is_archive() ) {
							if ( isset( $condition->target ) && $condition->target ) {
								$post_type = '';
								if ( isset( get_queried_object()->taxonomy ) ) {
									$post_type = get_taxonomy( get_queried_object()->taxonomy )->object_type[0];
								} elseif ( is_post_type_archive( $condition->target ) ) {
									$post_type = $condition->target;
								}
								if ( 'all' === $condition->target ) {
									$unique_send = 'true';
								} elseif ( ( ! isset( $condition->data ) || ( $condition->data && 'all' === $condition->data ) ) && $post_type === $condition->target ) {
									$unique_send = 'true';
								} elseif ( $post_type === $condition->target && isset( $condition->data ) && get_queried_object()->taxonomy === $condition->data && ( ! isset( $condition->extra ) || ( $condition->extra && 'all' === $condition->extra ) ) ) {
									$unique_send = 'true';
								} elseif ( $post_type === $condition->target && isset( $condition->data ) && get_queried_object()->taxonomy === $condition->data && isset( $condition->extra ) && get_queried_object()->term_id === $condition->extra ) {
									$unique_send = 'true';
								}
							}
						}
						$exclude[] = $unique_send;
					}
					$no_exclude[] = 'false';
				} else {
					$no_exclude[] = 'true';
				}
				if ( count( $value->author ) > 0 ) {
					foreach ( $value->author as $condition ) {
						$unique_send = 'false';
						if ( is_author() ) {
							if ( true === $condition ) {
								$unique_send = 'true';
							} elseif ( isset( $condition->target ) && $condition->target && is_author( $condition->target ) ) {
								$unique_send = 'true';
							}
						}
						$exclude[] = $unique_send;
					}
					$no_exclude[] = 'false';
				} else {
					$no_exclude[] = 'true';
				}
				if ( count( $value->custom ) > 0 ) {
					$current_user = wp_get_current_user();
					foreach ( $value->custom as $condition ) {
						$unique_send = 'false';
						$conditioner = '';
						if ( 'date' === $condition->target ) {
							$conditioner = gmdate( 'm/d/Y' );
						}
						if ( 'dayweek' === $condition->target ) {
							$conditioner = gmdate( 'l' );
						}
						if ( 'daymonth' === $condition->target ) {
							$conditioner = gmdate( 'd' );
						}
						if ( 'time' === $condition->target ) {
							$conditioner = gmdate( 'H:i:s' );
						}
						if ( 'username' === $condition->target ) {
							$conditioner = $current_user->user_login;
						}
						if ( 'userid' === $condition->target ) {
							$conditioner = strval( $current_user->ID );
						}
						if ( 'usercapabilities' === $condition->target ) {
							if ( current_user_can( $condition->extraData ) ) {
								$conditioner = 'true';
							}
						}
						if ( 'urlparameter' === $condition->target ) {
							if ( isset( $condition->key ) && $condition->key ) {
								if ( isset( $_GET[ $condition->key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
									$prep_key = sanitize_text_field( wp_unslash( $_GET[ $condition->key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.
									if ( $prep_key ) {
										$key = htmlspecialchars( $prep_key, ENT_QUOTES, 'UTF-8' );
										if ( 'true' === $condition->extra || 'false' === $condition->extra ) {
											$conditioner = filter_var( $key, FILTER_VALIDATE_BOOLEAN );
										} else {
											$conditioner = $key;
										}
									}
								}
							}
						}
						switch ( $condition->extra ) {
							case '===':
								if ( 'cookie' === $condition->target ) {
									$loop = true;
									foreach ( $_COOKIE as $key => $val ) {
										if ( $key === $condition->extraData ) {
											$unique_send = 'true';
											$loop        = false;

											break;
										}
									}
									if ( $loop ) {
										$unique_send = 'false';
									}
								} elseif ( 'dayweek' === $condition->target ) {
									$date_now      = strtotime( 'today' );
									$date_compared = strtotime( '' . $condition->extraData . ' this week' );
									if ( $date_now === $date_compared ) {
										$unique_send = 'true';
									}
								} elseif ( 'userrole' === $condition->target ) {
									if ( in_array( $condition->extraData, $current_user->roles, true ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'usercapabilities' === $condition->target ) {
									if ( current_user_can( $condition->extraData ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( $field === $condition->extraData ) {
											$unique_send = 'true';
										}
									}
								} elseif ( $conditioner === $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '!=':
								if ( 'cookie' === $condition->target ) {
									$loop = true;
									foreach ( $_COOKIE as $key => $val ) {
										if ( $key === $condition->extraData ) {
											$unique_send = 'false';
											$loop        = false;

											break;
										}
									}
									if ( $loop ) {
										$unique_send = 'true';
									}
								} elseif ( 'dayweek' === $condition->target ) {
									$date_now      = strtotime( 'today' );
									$date_compared = strtotime( '' . $condition->extraData . ' this week' );
									if ( $date_now != $date_compared ) {
										$unique_send = 'true';
									}
								} elseif ( 'userrole' === $condition->target ) {
									if ( ! in_array( $condition->extraData, $current_user->roles, true ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'usercapabilities' === $condition->target ) {
									if ( ! current_user_can( $condition->extraData ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( $field != $condition->extraData ) {
											$unique_send = 'true';
										}
									}
								} elseif ( $conditioner != $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case 'contains':
								if ( 'cookie' === $condition->target ) {
									$loop = true;
									foreach ( $_COOKIE as $key => $val ) {
										if ( false !== strpos( $key, $condition->extraData ) ) {
											$unique_send = 'true';
											$loop        = false;

											break;
										}
									}
									if ( $loop ) {
										$unique_send = 'false';
									}
								} elseif ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( false !== strpos( $field, $condition->extraData ) ) {
											$unique_send = 'true';
										}
									}
								} elseif ( false !== strpos( $conditioner, $condition->extraData ) ) {
									$unique_send = 'true';
								}

								break;
							case 'notcontain':
								if ( 'cookie' === $condition->target ) {
									$loop = true;
									foreach ( $_COOKIE as $key => $val ) {
										if ( false === strpos( $key, $condition->extraData ) ) {
											$unique_send = 'true';
											$loop        = false;

											break;
										}
									}
									if ( $loop ) {
										$unique_send = 'false';
									}
								} elseif ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( false === strpos( $field, $condition->extraData ) ) {
											$unique_send = 'true';
										}
									}
								} elseif ( false === strpos( $conditioner, $condition->extraData ) ) {
									$unique_send = 'true';
								}

								break;
							case 'before':
								if ( 'dayweek' === $condition->target ) {
									$date_now      = strtotime( 'today' );
									$date_compared = strtotime( '' . $condition->extraData . ' this week' );
									if ( $date_now < $date_compared ) {
										$unique_send = 'true';
									}
								} elseif ( $conditioner < $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case 'after':
								if ( 'dayweek' === $condition->target ) {
									$date_now      = strtotime( 'today' );
									$date_compared = strtotime( '' . $condition->extraData . ' this week' );
									if ( $date_now > $date_compared ) {
										$unique_send = 'true';
									}
								} elseif ( $conditioner > $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '<':
								if ( $conditioner < $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '>':
								if ( $conditioner > $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '>=':
								if ( $conditioner >= $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case '<=':
								if ( $conditioner <= $condition->extraData ) {
									$unique_send = 'true';
								}

								break;
							case 'empty':
								if ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( empty( $field ) ) {
											$unique_send = 'true';
										}
									}
								}

								break;
							case 'notempty':
								if ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}

										if ( ! empty( $field ) ) {
											$unique_send = 'true';
										}
									}
								}

								break;
							case 'true':
								if ( 'shortcode' === $condition->target ) {
									if ( true === boolval( do_shortcode( '[' . $condition->extraData . ']' ) ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'loggedin' === $condition->target ) {
									if ( is_user_logged_in() ) {
										$unique_send = 'true';
									}
								} elseif ( 'acf' === $condition->target ) {
									if ( isset( $condition->group ) && isset( $condition->field ) ) {
										$location = false;
										if ( isset( $condition->acfLocation ) ) {
											if ( 'currentpost' === $condition->acfLocation ) {
												$location = get_the_ID();
											} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											} elseif ( 'currentuser' === $condition->acfLocation ) {
												$location = 'user_' . get_current_user_id() . '';
											} elseif ( 'currentauthor' === $condition->acfLocation ) {
												$location = 'user_' . get_the_author_meta( 'ID' ) . '';
											} elseif ( 'option' === $condition->acfLocation ) {
												$location = 'option';
											} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
												$term     = get_term( $condition->acfLocationID->value );
												$location = $term->taxonomy . '_' . $term->term_id;
											} elseif ( isset( $condition->acfLocationID ) ) {
												$location = $condition->acfLocationID;
											}
										}

										$field = get_field( $condition->field, $location );
										if ( is_array( $field ) ) {
											$field = implode( ' ', $field );
										} else {
											$field = strval( $field );
										}
										if ( true === filter_var( $field, FILTER_VALIDATE_BOOLEAN ) ) {
											$unique_send = 'true';
										}
									}
								}

								break;
							case 'false':
								if ( 'shortcode' === $condition->target ) {
									if ( false === boolval( do_shortcode( '[' . $condition->extraData . ']' ) ) ) {
										$unique_send = 'true';
									}
								} elseif ( 'loggedin' === $condition->target ) {
									if ( ! is_user_logged_in() ) {
										$unique_send = 'true';
									}
								} elseif ( isset( $condition->group ) && isset( $condition->field ) ) {
									$location = false;
									if ( isset( $condition->acfLocation ) ) {
										if ( 'currentpost' === $condition->acfLocation ) {
											$location = get_the_ID();
										} elseif ( 'postid' === $condition->acfLocation && isset( $condition->acfLocationID ) ) {
											$location = $condition->acfLocationID;
										} elseif ( 'currentuser' === $condition->acfLocation ) {
											$location = 'user_' . get_current_user_id() . '';
										} elseif ( 'currentauthor' === $condition->acfLocation ) {
											$location = 'user_' . get_the_author_meta( 'ID' ) . '';
										} elseif ( 'option' === $condition->acfLocation ) {
											$location = 'option';
										} elseif ( 'taxonomyterm' === $condition->acfLocation && isset( $condition->acfLocationID ) && isset( $condition->acfLocationID->value ) ) {
											$term     = get_term( $condition->acfLocationID->value );
											$location = $term->taxonomy . '_' . $term->term_id;
										} elseif ( isset( $condition->acfLocationID ) ) {
											$location = $condition->acfLocationID;
										}
									}

									$field = get_field( $condition->field, $location );
									if ( is_array( $field ) ) {
										$field = implode( ' ', $field );
									} else {
										$field = strval( $field );
									}

									if ( false === filter_var( $field, FILTER_VALIDATE_BOOLEAN ) ) {
										$unique_send = 'true';
									}
								}

								break;
						}
						$exclude[] = $unique_send;
					}
					$no_exclude[] = 'false';
				} else {
					$no_exclude[] = 'true';
				}
				if ( $templaterer ) {
					if ( array_unique( $no_exclude ) === array( 'true' ) ) {
						$final_templates[ $templaterer ] = $custom_template[ $templaterer ];
						$template_list[]                 = $templaterer;
					} elseif ( $templaterer && $conditions->exclude->$templaterer->excludeCondition && 'and' === $conditions->exclude->$templaterer->excludeCondition && array_unique( $exclude ) !== array( 'true' ) ) {
						$final_templates[ $templaterer ] = $custom_template[ $templaterer ];
						$template_list[]                 = $templaterer;
					} elseif ( $templaterer && $conditions->exclude->$templaterer->excludeCondition && 'or' === $conditions->exclude->$templaterer->excludeCondition && ! in_array( 'true', $exclude, true ) ) {
						$final_templates[ $templaterer ] = $custom_template[ $templaterer ];
						$template_list[]                 = $templaterer;
					}
				}
			}
		}
		// phpcs:enable

		if ( isset( $query['slug__in'] ) ) {
			if ( Cwicly\Helpers::strposa( 'product', $query['slug__in'] ) || Cwicly\Helpers::strposa( 'cart', $query['slug__in'] ) || Cwicly\Helpers::strposa( 'checkout', $query['slug__in'] ) || Cwicly\Helpers::strposa( 'order-confirmation', $query['slug__in'] ) ) {
				if ( in_array( 'single-product', $query['slug__in'], true ) ) {
					if ( ! $final_templates ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', 'single-product', 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', 'single-product', 'tp' );
						return null;
					}
				} elseif ( in_array( 'product-search-results', $query['slug__in'], true ) ) {
					if ( ! $final_templates ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', 'product-search-results', 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', 'product-search-results', 'tp' );
						return null;
					}
				} elseif ( in_array( 'taxonomy-product_cat', $query['slug__in'], true ) ) {
					if ( ! $final_templates ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', 'taxonomy-product_cat', 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', 'taxonomy-product_cat', 'tp' );
						return null;
					}
				} elseif ( in_array( 'archive-product', $query['slug__in'], true ) ) {
					if ( ! $final_templates ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', 'archive-product', 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', 'archive-product', 'tp' );
						return null;
					}
				} elseif ( in_array( 'taxonomy-product_tag', $query['slug__in'], true ) ) {
					if ( ! $final_templates ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', 'taxonomy-product_tag', 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', 'taxonomy-product_tag', 'tp' );
						return null;
					}
				} elseif ( in_array( 'taxonomy-product_attribute', $query['slug__in'], true ) ) {
					if ( ! $final_templates ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', 'taxonomy-product_attribute', 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', 'taxonomy-product_attribute', 'tp' );
						return null;
					}
				} elseif ( in_array( 'cart', $query['slug__in'], true ) ) {
					if ( ! $final_templates ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', 'cart', 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', 'cart', 'tp' );
						return null;
					}
				} elseif ( in_array( 'checkout', $query['slug__in'], true ) ) {
					if ( ! $final_templates ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', 'checkout', 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', 'checkout', 'tp' );
						return null;
					}
				} elseif ( in_array( 'order-confirmation', $query['slug__in'], true ) ) {
					if ( ! $final_templates ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', 'order-confirmation', 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', 'order-confirmation', 'tp' );
						return null;
					}
				}
			}
		}

		foreach ( $priorities as $namer => $level ) {
			if ( ! in_array( $namer, $template_list, true ) ) {
				unset( $priorities[ $namer ] );
			}
		}

		if ( $priorities ) {
			$final = array_search( max( $priorities ), $priorities, true );
			if ( $final_templates[ $final ] && isset( $name[ $final ] ) && isset( $slug[ $final ] ) ) {
				if ( in_array( $final, $woocommerce_templates, true ) ) {
					Cwicly\Themer::namer( $name[ $final ], $slug[ $final ], $template_type, 'woocommerce/woocommerce' );
				} else {
					Cwicly\Themer::namer( $name[ $final ], $slug[ $final ], $template_type );
				}

				if ( isset( $final_templates[ $final ][0]->theme ) && $final_templates[ $final ][0]->theme && isset( $final_templates[ $final ][0]->slug ) && $final_templates[ $final ][0]->slug ) {
					if ( ! is_admin() ) {
						if ( isset( $status_codes[ $final_templates[ $final ][0]->slug ] ) ) {
							status_header( $status_codes[ $final_templates[ $final ][0]->slug ] );
						}
						Cwicly\Themer::add_template_styles( $final_templates[ $final ][0]->theme, $final_templates[ $final ][0]->slug, 'tp' );
					}
				}

				if ( ! is_page_template() || ( isset( $override_page_template[ $final_templates[ $final ][0]->slug ] ) && $override_page_template[ $final_templates[ $final ][0]->slug ] ) ) {
					if ( CC_WOOCOMMERCE ) {
						add_filter(
							'woocommerce_has_block_template',
							'cc_woo_customs'
						);
					}
					if ( in_array( $final, $woocommerce_templates, true ) ) {
						Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', $final, 'tp' );
						Cwicly\Themer::add_template_styles( 'cwicly', $final, 'tp' );
					}
					apply_filters( 'get_block_template', $final_templates[ $final ][0], '' . $final_templates[ $final ][0]->theme . '//' . $name[ $final ] . '', $template_type );
					return $final_templates[ $final ];
				}
			}
		} elseif ( $final_templates && array_values( $final_templates )[0] ) {
			if ( in_array( $name, $woocommerce_templates, true ) ) {
				Cwicly\Themer::namer( array_values( $name )[0], array_values( $slug )[0], $template_type, 'woocommerce/woocommerce' );
			} else {
				Cwicly\Themer::namer( array_values( $name )[0], array_values( $slug )[0], $template_type );
			}

			if ( isset( array_values( $final_templates )[0][0]->theme ) && array_values( $final_templates )[0][0]->theme && isset( array_values( $final_templates )[0][0]->slug ) && array_values( $final_templates )[0][0]->slug ) {
				if ( ! is_admin() ) {
					if ( isset( $status_codes[ array_values( $final_templates )[0][0]->slug ] ) ) {
						status_header( $status_codes[ array_values( $final_templates )[0][0]->slug ] );
					}
					Cwicly\Themer::add_template_styles( array_values( $final_templates )[0][0]->theme, array_values( $final_templates )[0][0]->slug, 'tp' );
				}
			}

			if ( ! is_page_template() || ( isset( $override_page_template[ array_values( $final_templates )[0][0]->slug ] ) && $override_page_template[ array_values( $final_templates )[0][0]->slug ] ) ) {
				if ( CC_WOOCOMMERCE ) {
					add_filter(
						'woocommerce_has_block_template',
						'cc_woo_customs'
					);
				}
				if ( in_array( $name, $woocommerce_templates, true ) ) {
					Cwicly\Themer::add_template_styles( 'woocommerce_woocommerce', $name, 'tp' );
					Cwicly\Themer::add_template_styles( 'cwicly', $name, 'tp' );
				}
				apply_filters( 'get_block_template', array_values( $final_templates )[0][0], '' . array_values( $final_templates )[0][0]->theme . '//' . array_values( $name )[0] . '', $template_type );
				return array_values( $final_templates )[0];}
		}
	}

	return null;
}

if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
	add_filter( 'pre_get_block_templates', 'cc_themer_maker', 10, 3 );
}

/**
 * Force Cwicly template in WooCommerce non-block types.
 */
function cc_woo_customs() {
	return true;
}
