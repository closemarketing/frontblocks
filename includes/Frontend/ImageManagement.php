<?php
/**
 * Image Management
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Registers/overrides image sizes, generates modern-format variants on
 * upload, and rewrites content images into <picture> markup on the
 * frontend when a modern-format variant is available.
 */
class ImageManagement {

	/**
	 * Metadata key used to store generated WebP/AVIF variant paths.
	 *
	 * @var string
	 */
	const VARIANTS_META_KEY = 'frbl_image_variants';

	/**
	 * Core image sizes whose dimensions live in wp_options rather than
	 * $_wp_additional_image_sizes.
	 *
	 * @var string[]
	 */
	const CORE_SIZES = array( 'thumbnail', 'medium', 'medium_large', 'large' );

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'after_setup_theme', array( $this, 'register_custom_and_override_sizes' ), 999 );
		add_filter( 'intermediate_image_sizes_advanced', array( $this, 'filter_intermediate_sizes_advanced' ) );
		add_filter( 'intermediate_image_sizes', array( $this, 'filter_intermediate_sizes' ) );
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'maybe_generate_modern_formats' ), 10, 2 );

		if ( ! is_admin() ) {
			add_filter( 'wp_content_img_tag', array( $this, 'filter_content_img_tag' ), 10, 3 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_picture_styles' ) );
		}
	}

	/**
	 * Enqueue the small stylesheet used by the <picture> wrapper markup.
	 *
	 * @return void
	 */
	public function enqueue_picture_styles() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		wp_enqueue_style(
			'frontblocks-image-picture',
			FRBL_PLUGIN_URL . 'assets/image-management/frontblocks-image-picture.css',
			array(),
			FRBL_VERSION
		);
	}

	/**
	 * Get the plugin's shared settings array.
	 *
	 * @return array
	 */
	private function get_options() {
		return get_option( 'frontblocks_settings', array() );
	}

	/**
	 * Whether the Image Management module is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$options = $this->get_options();
		return (bool) ( $options['enable_image_management'] ?? false );
	}

	/**
	 * Register custom image sizes and re-apply non-core size overrides.
	 * Core size overrides (thumbnail/medium/medium_large/large) are
	 * written to their wp_options directly when settings are saved, so
	 * they don't need to be re-applied on every request.
	 *
	 * @return void
	 */
	public function register_custom_and_override_sizes() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$options   = $this->get_options();
		$overrides = (array) ( $options['image_sizes_overrides'] ?? array() );
		$custom    = (array) ( $options['image_sizes_custom'] ?? array() );

		foreach ( $overrides as $name => $size ) {
			if ( in_array( $name, self::CORE_SIZES, true ) ) {
				continue;
			}
			add_image_size( $name, (int) ( $size['width'] ?? 0 ), (int) ( $size['height'] ?? 0 ), (bool) ( $size['crop'] ?? false ) );
		}

		foreach ( $custom as $size ) {
			if ( empty( $size['name'] ) ) {
				continue;
			}
			add_image_size( $size['name'], (int) ( $size['width'] ?? 0 ), (int) ( $size['height'] ?? 0 ), (bool) ( $size['crop'] ?? false ) );
		}
	}

	/**
	 * Remove disabled sizes from the "advanced" registered-sizes list
	 * (name => width/height/crop) consumed by wp_generate_attachment_metadata().
	 *
	 * @param array $sizes Registered sizes.
	 * @return array
	 */
	public function filter_intermediate_sizes_advanced( $sizes ) {
		if ( ! $this->is_enabled() ) {
			return $sizes;
		}

		$disabled = (array) ( $this->get_options()['image_sizes_disabled'] ?? array() );
		foreach ( $disabled as $name ) {
			unset( $sizes[ $name ] );
		}

		return $sizes;
	}

	/**
	 * Remove disabled sizes from the plain list of registered size names.
	 *
	 * @param string[] $sizes Registered size names.
	 * @return string[]
	 */
	public function filter_intermediate_sizes( $sizes ) {
		if ( ! $this->is_enabled() ) {
			return $sizes;
		}

		$disabled = (array) ( $this->get_options()['image_sizes_disabled'] ?? array() );

		return array_values( array_diff( $sizes, $disabled ) );
	}

	/**
	 * After core generates intermediate sizes, also generate modern-format
	 * (WebP/AVIF) variants for the full image and each generated size.
	 *
	 * @param array $metadata      Attachment metadata.
	 * @param int   $attachment_id Attachment ID.
	 * @return array
	 */
	public function maybe_generate_modern_formats( $metadata, $attachment_id ) {
		if ( ! $this->is_enabled() ) {
			return $metadata;
		}

		$options = $this->get_options();
		$target  = (string) ( $options['image_format_target'] ?? 'none' );

		if ( 'none' === $target || empty( $metadata ) ) {
			return $metadata;
		}

		return self::generate_variants_for_metadata( $attachment_id, $metadata, $target, (int) ( $options['image_format_quality'] ?? 82 ) );
	}

	/**
	 * Generate WebP/AVIF variants for every file referenced in an
	 * attachment's metadata (full size + intermediate sizes).
	 *
	 * Shared by the upload-time hook and the bulk-convert AJAX handler.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param array  $metadata      Attachment metadata.
	 * @param string $target        'webp', 'avif', or 'both'.
	 * @param int    $quality       Compression quality (1-100).
	 * @return array Updated metadata.
	 */
	public static function generate_variants_for_metadata( $attachment_id, $metadata, $target, $quality ) {
		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) ) {
			return $metadata;
		}

		$formats  = 'both' === $target ? array( 'webp', 'avif' ) : array( $target );
		$dir      = trailingslashit( dirname( $file ) );
		$variants = (array) ( $metadata[ self::VARIANTS_META_KEY ] ?? array() );

		$variants['full'] = self::generate_variant_files( $file, $formats, $quality );

		if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size_name => $size_data ) {
				if ( empty( $size_data['file'] ) ) {
					continue;
				}
				$variants[ $size_name ] = self::generate_variant_files( $dir . $size_data['file'], $formats, $quality );
			}
		}

		$metadata[ self::VARIANTS_META_KEY ] = array_filter( $variants );

		wp_update_attachment_metadata( $attachment_id, $metadata );

		return $metadata;
	}

	/**
	 * Generate one variant file per requested format for a single source image.
	 *
	 * @param string   $source_path Absolute path to the source image.
	 * @param string[] $formats     'webp' and/or 'avif'.
	 * @param int      $quality     Compression quality (1-100).
	 * @return array Map of format => generated file basename.
	 */
	private static function generate_variant_files( $source_path, $formats, $quality ) {
		$generated = array();

		foreach ( $formats as $format ) {
			$mime = 'webp' === $format ? 'image/webp' : 'image/avif';

			if ( ! wp_image_editor_supports( array( 'mime_type' => $mime ) ) ) {
				continue;
			}

			$editor = wp_get_image_editor( $source_path );
			if ( is_wp_error( $editor ) ) {
				continue;
			}

			$editor->set_quality( $quality );

			$target_path = preg_replace( '/\.[^.]+$/', '.' . $format, $source_path );
			$saved       = $editor->save( $target_path, $mime );

			if ( ! is_wp_error( $saved ) && ! empty( $saved['path'] ) ) {
				$generated[ $format ] = basename( $saved['path'] );
			}
		}

		return $generated;
	}

	/**
	 * Regenerate variants for an existing attachment (used by the bulk
	 * "convert existing images" admin action).
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool True on success, false if the attachment has no usable file.
	 */
	public static function convert_attachment( $attachment_id ) {
		$metadata = wp_get_attachment_metadata( $attachment_id );
		if ( empty( $metadata ) ) {
			return false;
		}

		$options = get_option( 'frontblocks_settings', array() );
		$target  = (string) ( $options['image_format_target'] ?? 'none' );

		if ( 'none' === $target ) {
			return false;
		}

		self::generate_variants_for_metadata( $attachment_id, $metadata, $target, (int) ( $options['image_format_quality'] ?? 82 ) );

		return true;
	}

	/**
	 * Rewrite a content <img> tag into a <picture> element with modern-format
	 * <source> tags when variants exist for the attachment, keeping the
	 * original <img> as the fallback.
	 *
	 * @param string $filtered_image Full <img ...> tag markup.
	 * @param string $context        Filter context (unused).
	 * @param int    $attachment_id  Attachment ID.
	 * @return string
	 */
	public function filter_content_img_tag( $filtered_image, $context, $attachment_id ) {
		if ( ! $this->is_enabled() || ! $attachment_id ) {
			return $filtered_image;
		}

		$metadata = wp_get_attachment_metadata( $attachment_id );
		$variants = (array) ( $metadata[ self::VARIANTS_META_KEY ] ?? array() );

		if ( empty( $variants ) ) {
			return $filtered_image;
		}

		if ( ! preg_match( '/src=["\']([^"\']+)["\']/', $filtered_image, $src_match ) ) {
			return $filtered_image;
		}

		$size_key = $this->match_variant_size( $src_match[1], $metadata );
		if ( null === $size_key || empty( $variants[ $size_key ] ) ) {
			return $filtered_image;
		}

		$base_url = trailingslashit( dirname( $src_match[1] ) );
		$sources  = '';

		foreach ( array( 'avif', 'webp' ) as $format ) {
			if ( empty( $variants[ $size_key ][ $format ] ) ) {
				continue;
			}
			$mime     = 'avif' === $format ? 'image/avif' : 'image/webp';
			$sources .= sprintf(
				'<source type="%1$s" srcset="%2$s" />',
				esc_attr( $mime ),
				esc_url( $base_url . $variants[ $size_key ][ $format ] )
			);
		}

		if ( '' === $sources ) {
			return $filtered_image;
		}

		// The <img> tag itself is already WP-escaped markup; only the
		// <source> URLs we add above are freshly built, and those go
		// through esc_url()/esc_attr() above.
		return '<picture class="frbl-picture">' . $sources . $filtered_image . '</picture>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Determine which registered size (or "full") a content image's src
	 * URL corresponds to, so the matching variant set can be looked up.
	 *
	 * @param string $src      The <img> tag's src URL.
	 * @param array  $metadata Attachment metadata.
	 * @return string|null
	 */
	private function match_variant_size( $src, $metadata ) {
		$basename = basename( wp_parse_url( $src, PHP_URL_PATH ) );

		if ( ! empty( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size_name => $size_data ) {
				if ( ! empty( $size_data['file'] ) && $size_data['file'] === $basename ) {
					return $size_name;
				}
			}
		}

		if ( ! empty( $metadata['file'] ) && basename( $metadata['file'] ) === $basename ) {
			return 'full';
		}

		return null;
	}

	/**
	 * Estimate disk usage per registered size, sampling the most recent
	 * attachments and extrapolating for large libraries.
	 *
	 * @param string[] $size_names Registered size names to estimate.
	 * @return array Map of size name => estimated bytes.
	 */
	public static function estimate_disk_usage_by_size( $size_names ) {
		$sample_limit = 200;

		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => $sample_limit,
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'fields'         => 'ids',
				'no_found_rows'  => false,
			)
		);

		$sample_count = count( $query->posts );
		$totals       = array_fill_keys( $size_names, 0 );

		if ( 0 === $sample_count ) {
			return $totals;
		}

		foreach ( $query->posts as $attachment_id ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			$file     = get_attached_file( $attachment_id );
			if ( ! $file || empty( $metadata['sizes'] ) ) {
				continue;
			}
			$dir = trailingslashit( dirname( $file ) );

			foreach ( $size_names as $size_name ) {
				if ( empty( $metadata['sizes'][ $size_name ]['file'] ) ) {
					continue;
				}
				$size_path = $dir . $metadata['sizes'][ $size_name ]['file'];
				if ( file_exists( $size_path ) ) {
					$totals[ $size_name ] += filesize( $size_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_filesize -- local media library file, not a remote/user-controlled path.
				}
			}
		}

		$scale = $query->found_posts > $sample_count && $sample_count > 0 ? $query->found_posts / $sample_count : 1;

		foreach ( $totals as $size_name => $bytes ) {
			$totals[ $size_name ] = (int) round( $bytes * $scale );
		}

		return $totals;
	}
}
