<?php
/**
 * Cwicly Helpers
 *
 * Functions for creating and managing all Cwicly mains
 *
 * @package Cwicly
 * @version 1.1
 */

/**
 * Condition checker
 *
 * @param array $conditions Conditions.
 *
 * @return array
 */
function cc_condition_checker( $conditions ) {
	global $post;
	$templater  = '';
	$final      = array();
	$conditions = wp_json_encode( $conditions );
	$conditions = json_decode( $conditions );
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- disable sniff for Snake Case since we are using JS variables.
	if ( isset( $conditions ) && isset( $conditions->include ) && $conditions->include ) {
		foreach ( $conditions->include as $template => $value ) {
			$custom_template = array();
			$send            = array();
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
								if ( isset( $condition->data ) && $condition->data && 'all' != $condition->data ) {
									if ( ! isset( $condition->data ) || ( $condition->data && 'directchildof' === $condition->data ) ) {
										$parent_id   = get_the_ID();
										$parent_page = wp_get_post_parent_id( $parent_id );
										if ( isset( $condition->extra ) && $parent_page == $condition->extra ) {
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
					$send[] = $unique_send;
				}
			}
			if ( count( $value->archive ) > 0 ) {
				foreach ( $value->archive as $condition ) {
					$unique_send = false;

					if ( 'search' === $condition->target && is_search() ) {
						$unique_send = 'true';
					} elseif ( isset( $condition->target ) && 'author' === $condition->target && is_author() ) {
						if ( isset( $condition->data ) && $condition->data && 'all' != $condition->data ) {
							if ( get_the_author_meta( 'ID' ) === $condition->data ) {
								$unique_send = 'true';
							}
						} else {
							$unique_send = 'true';
						}
					} elseif ( is_archive() ) {
						if ( isset( $condition->target ) && $condition->target ) {
							$post_type = get_taxonomy( get_queried_object()->taxonomy )->object_type[0];
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
					$send[] = $unique_send;
				}
			}
			if ( count( $value->author ) > 0 ) {
				foreach ( $value->author as $condition ) {
					$unique_send = false;
					if ( true === $condition ) {
						$unique_send = 'true';
					} elseif ( isset( $condition->target ) && $condition->target && $post->post_author && intval( $post->post_author ) === $condition->target ) {
						$unique_send = 'true';
					}
					$send[] = $unique_send;
				}
			}
			if ( count( $value->custom ) > 0 ) {
				$current_user = wp_get_current_user();
				foreach ( $value->custom as $condition ) {
					$unique_send = false;
					$conditioner = '';
					if ( 'loggedin' === $condition->target ) {
						$conditioner = is_user_logged_in();
					}
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
							if ( 'acf' === $condition->target && isset( $condition->extraData ) ) {
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
								$shortcode = do_shortcode( '[' . $condition->extraData . ']' );
								if ( $shortcode ) {
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
										$field = $field[0];
									} else {
										$field = strval( $field );
									}
									if ( true === boolval( $field ) ) {
										$unique_send = 'true';
									} else {
										$unique_send = 'false';
									}
								}
							} elseif ( true === $conditioner ) {
								$unique_send = 'true';
							} else {
								$unique_send = 'false';
							}

							break;
						case 'false':
							if ( 'shortcode' === $condition->target ) {
								$shortcode = do_shortcode( '[' . $condition->extraData . ']' );
								if ( ! $shortcode ) {
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
										$field = $field[0];
									} else {
										$field = strval( $field );
									}
									if ( false === boolval( $field ) ) {
										$unique_send = 'true';
									} else {
										$unique_send = 'false';
									}
								}
							} elseif ( false === $conditioner ) {
								$unique_send = 'true';
							} else {
								$unique_send = 'false';
							}

							break;
					}
					$send[] = $unique_send;
				}
			}
			if ( isset( $value->includeCondition ) && $value->includeCondition && 'and' === $value->includeCondition && array_unique( $send ) == array( 'true' ) ) {
				$custom_template[] = get_block_template( get_stylesheet() . '//' . $template, 'wp_template_part' );
				$templater         = $template;
			} elseif ( isset( $value->includeCondition ) && $value->includeCondition && 'or' === $value->includeCondition && in_array( 'true', $send ) ) {
				$custom_template[] = get_block_template( get_stylesheet() . '//' . $template, 'wp_template_part' );
				$templater         = $template;
			}

			$exclude    = array();
			$no_exclude = array();
			if ( $templater && $conditions->exclude->$templater ) {
				$value = $conditions->exclude->$templater;
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
											if ( isset( $condition->extra ) && $parent_page == $condition->extra ) {
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

						if ( 'search' === $condition->target && is_search() ) {
							$unique_send = 'true';
						} elseif ( isset( $condition->target ) && 'author' === $condition->target && is_author() ) {
							if ( isset( $condition->data ) && $condition->data && 'all' != $condition->data ) {
								if ( get_the_author_meta( 'ID' ) === $condition->data ) {
									$unique_send = 'true';
								}
							} else {
								$unique_send = 'true';
							}
						} elseif ( is_archive() ) {
							if ( isset( $condition->target ) && $condition->target ) {
								$post_type = get_taxonomy( get_queried_object()->taxonomy )->object_type[0];
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
						if ( true === $condition ) {
							$unique_send = 'true';
						} elseif ( isset( $condition->target ) && $condition->target && $post->post_author && intval( $post->post_author ) === $condition->target ) {
							$unique_send = 'true';
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
										$field = get_field( $condition->field );
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
									$shortcode = do_shortcode( '[' . $condition->extraData . ']' );
									if ( $shortcode ) {
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
											$field = $field[0];
										} else {
											$field = strval( $field );
										}
										if ( true === boolval( $field ) ) {
											$unique_send = 'true';
										} else {
											$unique_send = 'false';
										}
									}
								}

								break;
							case 'false':
								if ( 'shortcode' === $condition->target ) {
									$shortcode = do_shortcode( '[' . $condition->extraData . ']' );
									if ( ! $shortcode ) {
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
											$field = $field[0];
										} else {
											$field = strval( $field );
										}
										if ( false === boolval( $field ) ) {
											$unique_send = 'true';
										} else {
											$unique_send = 'false';
										}
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
			}
			if ( $templater ) {
				if ( array_unique( $no_exclude ) == array( 'true' ) ) {
					$final[] = $custom_template;
				} elseif ( $templater && $conditions->exclude->$templater->excludeCondition && 'and' === $conditions->exclude->$templater->excludeCondition && array_unique( $exclude ) != array( 'true' ) ) {
					$final[] = $custom_template;
				} elseif ( $templater && $conditions->exclude->$templater->excludeCondition && 'or' === $conditions->exclude->$templater->excludeCondition && ! in_array( 'true', $exclude ) ) {
					$final[] = $custom_template;
				}
			}
		}
	}
	// phpcs:enable

	return $final;
}

/**
 * Check if a condition is true or false
 *
 * @param array  $attributes The attributes of the block.
 * @param string $property   The property to check.
 * @param string $type       The type of check to perform.
 *
 * @return bool
 */
function cc_attribute_checker( $attributes, $property, $type ) {
	if ( 'true' === $type ) {
		if ( isset( $attributes[ $property ] ) && $attributes[ $property ] ) {
			return true;
		} else {
			return false;
		}
	} elseif ( 'false' === $type ) {
		if ( ! isset( $attributes[ $property ] ) || ( isset( $attributes[ $property ] ) && ! $attributes[ $property ] ) ) {
			return true;
		} else {
			return false;
		}
	}
}
