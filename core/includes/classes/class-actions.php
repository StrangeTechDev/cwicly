<?php
/**
 * Actions class file.
 *
 * @package Cwicly
 */

namespace Cwicly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.

/**
 * All necessary actions for Cwicly.
 *
 * @package Cwicly
 */
class Actions {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'save_post', array( $this, 'save_post' ), 999, 3 );
		add_action( 'delete_post', array( $this, 'delete_post' ), 999, 3 );
		add_action( 'deleted_transient', array( $this, 'delete_transient' ), 999, 3 );
		add_action( 'created_term', array( $this, 'delete_terms' ), 999, 3 );
		add_action( 'edited_term', array( $this, 'delete_terms' ), 999, 3 );
		add_action( 'delete_term', array( $this, 'delete_terms' ), 999, 3 );
		add_filter( 'block_type_metadata', array( $this, 'filter_metadata_registration' ) );
		add_action( 'wp_trash_post', array( $this, 'delete_trash_post_action' ) );
		add_action( 'delete_post', array( $this, 'delete_post_action' ), 10, 2 );
		add_action( 'updated_option', array( $this, 'updated_option_action' ), 10, 3 );
	}

	/**
	 * Fired upon WordPress 'save_post' hook. On post update delete all related caches, on post creation delete all
	 * non-single endpoint caches for this post type.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @param bool     $update Whether this is an existing post being updated or not.
	 */
	public static function save_post( $post_id, $post, $update ) {
		if ( 'auto-draft' === $post->post_status ) {
			return;
		}
		if ( $update ) {
			$change         = false;
			$all_transients = get_option( 'cwicly_rest_transients' );
			if ( $all_transients ) {
				if ( 'product' === $post->post_type ) {
					$type = 'products';
				} else {
					$type = 'posts';
				}
				foreach ( $all_transients as $index => $transient_name ) {
					if ( str_contains( $transient_name, 'cc-' . $type . '-count' ) ) {
						delete_transient( $transient_name );
						$all_transients = array_filter(
							$all_transients,
							static function ( $element ) use ( $transient_name ) {
								return $element !== $transient_name;
							}
						);
						$change         = true;
					}
				}
				if ( $change ) {
					update_option( 'cwicly_rest_transients', $all_transients );
				}
			}
		}
	}

	/**
	 * Fired upon WordPress 'delete_post' hook. Delete all related caches, including all single cache statistics.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function delete_post( $post_id ) {
		$post = get_post( $post_id );
		if ( wp_is_post_revision( $post ) ) {
			return;
		}
		$change         = false;
		$all_transients = get_option( 'cwicly_rest_transients' );
		if ( $all_transients ) {
			if ( 'product' === $post->post_type ) {
				$type = 'products';
			} else {
				$type = 'posts';
			}
			foreach ( $all_transients as $index => $transient_name ) {
				if ( str_contains( $transient_name, 'cc-' . $type . '-count' ) ) {
					delete_transient( $transient_name );
					$all_transients = array_filter(
						$all_transients,
						static function ( $element ) use ( $transient_name ) {
							return $element !== $transient_name;
						}
					);
					$change         = true;
				}
			}
			if ( $change ) {
				update_option( 'cwicly_rest_transients', $all_transients );
			}
		}
	}

	/**
	 * Fired upon WordPress 'deleted_transient' hook. Delete all related caches, including all single cache statistics.
	 *
	 * @param string $transient Transient name.
	 */
	public static function delete_transient( $transient ) {
		$all_transients = get_option( 'cwicly_rest_transients' );
		if ( $all_transients && in_array( $transient, $all_transients, true ) ) {
			$all_transients = array_filter(
				$all_transients,
				static function ( $element ) use ( $transient ) {
					return $element !== $transient;
				}
			);
			update_option( 'cwicly_rest_transients', $all_transients );
		}
	}

	/**
	 * Fired upon WordPress 'delete_post' hook. Delete all related caches, including all single cache statistics.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $tt_id   Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public static function delete_terms( int $term_id, int $tt_id, string $taxonomy ) {
		$change         = false;
		$all_transients = get_option( 'cwicly_rest_transients' );
		if ( $all_transients ) {
			foreach ( $all_transients as $index => $transient_name ) {
				if ( str_contains( $transient_name, 'cc-terms' ) && str_contains( $transient_name, $taxonomy ) ) {
					delete_transient( $transient_name );

					$all_transients = array_filter(
						$all_transients,
						static function ( $element ) use ( $transient_name ) {
							return $element !== $transient_name;
						}
					);
					$change         = true;
				}
			}
			if ( $change ) {
				update_option( 'cwicly_rest_transients', $all_transients );
			}
		}
	}

	/**
	 * Function for `wp_trash_post` action-hook.
	 *
	 * @param int $postid Post ID.
	 *
	 * @return void
	 */
	public function delete_trash_post_action( $postid ) {
		$post = get_post( $postid );

		if ( 'wp_template_part' === $post->post_type ) {
			if ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . get_stylesheet() . '_' . $post->post_name . '.css' ) ) {
				wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . get_stylesheet() . '_' . $post->post_name . '.css' );
			}
		} elseif ( 'wp_template' === $post->post_type ) {
			if ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . get_stylesheet() . '_' . $post->post_name . '.css' ) ) {
				wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . get_stylesheet() . '_' . $post->post_name . '.css' );
			}
		} elseif ( 'wp_block' === $post->post_type ) {
			if ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-rb-' . $postid . '.css' ) ) {
				wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-rb-' . $postid . '.css' );
			}
		} elseif ( 'cc_block' === $post->post_type ) {
			if ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-cm-' . $postid . '.css' ) ) {
				wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-cm-' . $postid . '.css' );
			}
		} elseif ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $postid . '.css' ) ) {
			wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $postid . '.css' );
		}
	}

	/**
	 * Function for `delete_post` action-hook.
	 *
	 * @param int     $postid Post ID.
	 * @param WP_Post $post   Post object.
	 *
	 * @return void
	 */
	public static function delete_post_action( $postid, $post ) {
		if ( 'wp_template_part' === $post->post_type ) {
			if ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . get_stylesheet() . '_' . $post->post_name . '.css' ) ) {
				wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . get_stylesheet() . '_' . $post->post_name . '.css' );
			}
		} elseif ( 'wp_template' === $post->post_type ) {
			if ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . get_stylesheet() . '_' . $post->post_name . '.css' ) ) {
				wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-tp-' . get_stylesheet() . '_' . $post->post_name . '.css' );
			}
		} elseif ( 'wp_block' === $post->post_type ) {
			if ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-rb-' . $postid . '.css' ) ) {
				wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-rb-' . $postid . '.css' );
			}
		} elseif ( 'cc_block' === $post->post_type ) {
			if ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-cm-' . $postid . '.css' ) ) {
				wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-cm-' . $postid . '.css' );
			}
		} elseif ( file_exists( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $postid . '.css' ) ) {
			wp_delete_file( wp_upload_dir()['basedir'] . '/cwicly/css/cc-post-' . $postid . '.css' );
		}
	}

	/**
	 * Fired upon WordPress metadata call for blocks. Allows us to filter the defaults.
	 *
	 * @param array $metadata The metadata for a specific block.
	 */
	public function filter_metadata_registration( $metadata ) {
		if ( isset( $metadata['name'] ) && $metadata['name'] && strpos( $metadata['name'], 'cwicly/' ) !== false ) {
			$optimise = get_option( 'cwicly_optimise' );
			if ( $optimise && isset( $optimise['cwiclyDefaults'] ) && 'true' === $optimise['cwiclyDefaults'] ) {
				return $metadata;
			}

			if ( strpos( $metadata['name'], 'cwicly/query-template' ) === false ) {
				$metadata['attributes']['containerLayoutDisplay'] = array( 'type' => 'object' );
			}

			$metadata['attributes']['containerLayoutPosition']   = array( 'type' => 'object' );
			$metadata['attributes']['backgroundImageTypePseudo'] = array( 'type' => 'object' );
			if ( strpos( $metadata['name'], 'cwicly/styler' ) === false ) {
				$metadata['attributes']['backgroundImageType'] = array( 'type' => 'string' );
			}
			$metadata['attributes']['containerLayoutTag'] = array( 'type' => 'string' );

			// BUTTON.
			$metadata['attributes']['containerLayoutAlignItems']    = array( 'type' => 'object' );
			$metadata['attributes']['containerLayoutFlexDirection'] = array( 'type' => 'object' );
			// BUTTON.

			// COLUMNS & MAPS.
			if ( strpos( $metadata['name'], 'cwicly/columns' ) !== false ||
				strpos( $metadata['name'], 'cwicly/image' ) !== false ||
				strpos( $metadata['name'], 'cwicly/modal' ) !== false ||
				strpos( $metadata['name'], 'cwicly/icon' ) !== false ||
				strpos( $metadata['name'], 'cwicly/section' ) !== false ||
				strpos( $metadata['name'], 'cwicly/maps' ) !== false ) {
				$metadata['attributes']['containerSizeWidth'] = array( 'type' => 'object' );
			}
			// COLUMNS & MAPS.

			// COLUMNS.
			if ( strpos( $metadata['name'], 'cwicly/columns' ) !== false ) {
				$metadata['attributes']['columnsControl']             = array( 'type' => 'boolean' );
				$metadata['attributes']['columnsTemplateColumns']     = array( 'type' => 'object' );
				$metadata['attributes']['columnsMinimumColumnsWidth'] = array( 'type' => 'object' );
				$metadata['attributes']['columnsAutoFitControl']      = array( 'type' => 'boolean' );
				$metadata['attributes']['columnsColumnGap']           = array( 'type' => 'object' );
				$metadata['attributes']['columnsRowGap']              = array( 'type' => 'object' );
				$metadata['attributes']['columnsRowHeight']           = array( 'type' => 'object' );
				$metadata['attributes']['columnsItems']               = array( 'type' => 'object' );
				$metadata['attributes']['columnsAutoItems']           = array( 'type' => 'object' );
				// COLUMNS.
			}

			if ( strpos( $metadata['name'], 'cwicly/repeater' ) !== false ||
				strpos( $metadata['name'], 'cwicly/query-template' ) !== false ||
				strpos( $metadata['name'], 'cwicly/taxonomyterms' ) !== false ) {
				$metadata['attributes']['columnsColumnGap'] = array( 'type' => 'object' );
				$metadata['attributes']['columnsRowGap']    = array( 'type' => 'object' );
			}

			// IMAGES.
			if ( strpos( $metadata['name'], 'cwicly/image' ) !== false ||
				strpos( $metadata['name'], 'cwicly/slide' ) !== false ) {
				$metadata['attributes']['containerSizeWidth']  = array( 'type' => 'object' );
				$metadata['attributes']['containerSizeHeight'] = array( 'type' => 'object' );
			}
			// IMAGES.

			// LIST.
			if ( strpos( $metadata['name'], 'cwicly/list' ) !== false ) {
				$metadata['attributes']['listStyleUl'] = array( 'type' => 'string' );
				$metadata['attributes']['listStyleOl'] = array( 'type' => 'string' );
			}
			// LIST.

			// SECTION.
			if ( strpos( $metadata['name'], 'cwicly/section' ) !== false ) {
				$metadata['attributes']['containerSizeMaxWidth'] = array( 'type' => 'object' );
			}
			// SECTION.
			return $metadata;
		}
		return $metadata;
	}

	/**
	 * Function for `updated_option` action-hook.
	 *
	 * @param string $option    Name of the updated option.
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 *
	 * @return void
	 */
	public function updated_option_action( $option, $old_value, $value ) {
		if (
			'cwicly_local_active_fonts' === $option ||
			'cwicly_local_fonts' === $option ||
			'cwicly_section_defaults' === $option ||
			'cwicly_global_styles' === $option ||
			'cwicly_breakpoints' === $option ||
			'cwicly_global_classes' === $option ||
			'cwicly_global_classes_folders' === $option ||
			'cwicly_global_stylesheets' === $option ||
			'cwicly_global_stylesheets_folders' === $option ||
			'cwicly_components_folders' === $option
		) {
			$hearbeat = get_option( 'cwicly_heartbeat' );

			if ( ! $hearbeat ) {
				$hearbeat = array();
			}
			$hearbeat[ $option ] = time();

			update_option( 'cwicly_heartbeat', $hearbeat );
		}

		if (
			'cwicly_global_parts' === $option ||
			'cwicly_pre_conditions' === $option
		) {
			$hearbeat = get_option( 'cwicly_themer_heartbeat' );

			if ( ! $hearbeat ) {
				$hearbeat = array();
			}
			$hearbeat[ $option ] = time();

			update_option( 'cwicly_themer_heartbeat', $hearbeat );
		}
	}
}
