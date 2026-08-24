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
 * upload, and rewrites image markup on the frontend to serve them.
 */
class ImageManagement {

	/**
	 * Metadata key used to store generated modern-format variants, keyed
	 * per size and then per MIME type: array( 'file' => basename, 'filesize' => bytes ).
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
	 * Map of short format key => MIME type.
	 *
	 * @var array
	 */
	const FORMAT_MIME_TYPES = array(
		'avif' => 'image/avif',
		'webp' => 'image/webp',
	);

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
		add_filter( 'image_size_names_choose', array( $this, 'filter_image_size_names_choose' ) );
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'maybe_generate_modern_formats' ), 10, 2 );
		add_action( 'delete_attachment', array( $this, 'delete_variant_files' ) );

		if ( ! is_admin() ) {
			add_filter( 'wp_content_img_tag', array( $this, 'filter_content_img_tag' ), 10, 3 );
			add_filter( 'post_thumbnail_html', array( $this, 'filter_post_thumbnail_html' ), 10, 3 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_picture_styles' ) );
		}
	}

	/**
	 * Enqueue the small stylesheet used by the optional <picture> wrapper markup.
	 *
	 * @return void
	 */
	public function enqueue_picture_styles() {
		if ( ! $this->is_enabled() || ! $this->uses_picture_element() ) {
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
	 * Whether the frontend should wrap images in a <picture> element with
	 * one <source> per generated format, instead of rewriting the <img>
	 * src/srcset in place. Direct rewriting is the default: modern-format
	 * browser support is high enough that the extra markup usually isn't
	 * needed, and it keeps output closer to what themes already produce.
	 *
	 * @return bool
	 */
	private function uses_picture_element() {
		return (bool) ( $this->get_options()['image_format_use_picture'] ?? false );
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
	 * Add custom sizes flagged "show in picker" to the image-size dropdown
	 * shown when inserting/editing an image in the block and media editors.
	 * Sizes are otherwise invisible there unless a theme explicitly labels them.
	 *
	 * @param array $sizes Map of size name => label.
	 * @return array
	 */
	public function filter_image_size_names_choose( $sizes ) {
		if ( ! $this->is_enabled() ) {
			return $sizes;
		}

		$custom = (array) ( $this->get_options()['image_sizes_custom'] ?? array() );

		foreach ( $custom as $size ) {
			if ( empty( $size['name'] ) || empty( $size['show_in_picker'] ) ) {
				continue;
			}
			$sizes[ $size['name'] ] = ! empty( $size['label'] ) ? $size['label'] : $size['name'];
		}

		return $sizes;
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
	 * attachment's metadata (full size + intermediate sizes). The original
	 * file is never modified or deleted — generated variants are always
	 * additional files alongside it.
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

		$formats = 'both' === $target ? array( 'avif', 'webp' ) : array( $target );
		$dir     = trailingslashit( dirname( $file ) );

		// Remove previously generated variants first, so switching formats
		// (e.g. "both" -> "webp") or re-running after a size change doesn't
		// leave stale files behind.
		self::delete_variant_files_from_map( $dir, (array) ( $metadata[ self::VARIANTS_META_KEY ] ?? array() ) );

		$variants         = array();
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
	 * @return array Map of MIME type => array( 'file' => basename, 'filesize' => bytes ).
	 */
	private static function generate_variant_files( $source_path, $formats, $quality ) {
		$generated = array();

		foreach ( $formats as $format ) {
			$mime = self::FORMAT_MIME_TYPES[ $format ] ?? '';

			if ( '' === $mime || ! wp_image_editor_supports( array( 'mime_type' => $mime ) ) ) {
				continue;
			}

			$editor = wp_get_image_editor( $source_path );
			if ( is_wp_error( $editor ) ) {
				continue;
			}

			$editor->set_quality( $quality );

			$target_path = preg_replace( '/\.[^.]+$/', '.' . $format, $source_path );
			$saved       = $editor->save( $target_path, $mime );

			if ( is_wp_error( $saved ) || empty( $saved['path'] ) ) {
				continue;
			}

			$generated[ $mime ] = array(
				'file'     => basename( $saved['path'] ),
				'filesize' => file_exists( $saved['path'] ) ? filesize( $saved['path'] ) : 0, // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_filesize -- local media library file we just created, not a remote/user-controlled path.
			);
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
	 * Delete the on-disk files (and any modern-format variants) for sizes
	 * that are currently disabled, and remove them from the attachment's
	 * metadata. Disabling a size only stops *future* generation — without
	 * this, the disk-usage savings shown in the settings table are never
	 * actually reclaimed for existing media.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool True if any file was removed, false otherwise.
	 */
	public static function cleanup_disabled_size_files( $attachment_id ) {
		$options  = get_option( 'frontblocks_settings', array() );
		$disabled = (array) ( $options['image_sizes_disabled'] ?? array() );

		if ( empty( $disabled ) ) {
			return false;
		}

		$metadata = wp_get_attachment_metadata( $attachment_id );
		$file     = get_attached_file( $attachment_id );

		if ( ! $file || empty( $metadata['sizes'] ) || ! is_array( $metadata['sizes'] ) ) {
			return false;
		}

		$dir      = trailingslashit( dirname( $file ) );
		$variants = (array) ( $metadata[ self::VARIANTS_META_KEY ] ?? array() );
		$removed  = array();

		foreach ( $disabled as $size_name ) {
			if ( empty( $metadata['sizes'][ $size_name ]['file'] ) ) {
				continue;
			}

			$size_path = $dir . $metadata['sizes'][ $size_name ]['file'];
			if ( file_exists( $size_path ) ) {
				wp_delete_file( $size_path );
			}

			if ( ! empty( $variants[ $size_name ] ) ) {
				self::delete_variant_files_from_map( $dir, array( $variants[ $size_name ] ) );
			}

			unset( $metadata['sizes'][ $size_name ] );
			$removed[] = $size_name;
		}

		$changed = ! empty( $removed );

		if ( $changed ) {
			$metadata[ self::VARIANTS_META_KEY ] = array_diff_key( $variants, array_flip( $removed ) );
			wp_update_attachment_metadata( $attachment_id, $metadata );
		}

		return $changed;
	}

	/**
	 * Delete generated WebP/AVIF variant files when their attachment is
	 * deleted. WordPress core has no knowledge of these files (they aren't
	 * part of the standard intermediate-size metadata it manages), so
	 * without this they would be orphaned on disk forever.
	 *
	 * @param int $attachment_id Attachment ID being deleted.
	 * @return void
	 */
	public function delete_variant_files( $attachment_id ) {
		$metadata = wp_get_attachment_metadata( $attachment_id );
		$variants = (array) ( $metadata[ self::VARIANTS_META_KEY ] ?? array() );

		$file = get_attached_file( $attachment_id );
		if ( ! $file || empty( $variants ) ) {
			return;
		}

		self::delete_variant_files_from_map( trailingslashit( dirname( $file ) ), $variants );
	}

	/**
	 * Delete a set of previously generated variant files on disk.
	 *
	 * @param string $dir      Directory the files live in (trailing slash included).
	 * @param array  $variants Map of size name => MIME type => array( 'file' => basename, ... ),
	 *                         as stored in the VARIANTS_META_KEY metadata entry.
	 * @return void
	 */
	private static function delete_variant_files_from_map( $dir, $variants ) {
		foreach ( $variants as $size_variants ) {
			foreach ( (array) $size_variants as $variant ) {
				if ( empty( $variant['file'] ) ) {
					continue;
				}
				$path = $dir . $variant['file'];
				if ( file_exists( $path ) ) {
					wp_delete_file( $path );
				}
			}
		}
	}

	/**
	 * Same rewrite as filter_content_img_tag(), applied to featured images.
	 * the_post_thumbnail() markup never passes through wp_content_img_tag
	 * (that filter only covers the_content/the_excerpt), so without this,
	 * hero/archive-card featured images would never get modern-format delivery.
	 *
	 * @param string $html              Featured image <img> markup.
	 * @param int    $post_id           Post ID (unused).
	 * @param int    $post_thumbnail_id Attachment ID of the featured image.
	 * @return string
	 */
	public function filter_post_thumbnail_html( $html, $post_id, $post_thumbnail_id ) {
		return $this->filter_content_img_tag( $html, 'post_thumbnail', $post_thumbnail_id );
	}

	/**
	 * Serve generated modern-format variants for a content/featured image,
	 * either by rewriting its src/srcset in place (default — lighter markup,
	 * relies on the browser only requesting a format it supports via the
	 * srcset it's given) or, when the "picture" setting is enabled, by
	 * wrapping the original <img> in a <picture> element with one <source>
	 * per format, which additionally protects against a browser silently
	 * getting no image at all if a variant file goes missing.
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

		// AVIF first when both are available: it's the smaller format, and
		// this module only ever generates a format the server actually
		// supports producing.
		$mime = null;
		foreach ( array( 'image/avif', 'image/webp' ) as $candidate ) {
			if ( ! empty( $variants[ $size_key ][ $candidate ]['file'] ) ) {
				$mime = $candidate;
				break;
			}
		}
		if ( null === $mime ) {
			return $filtered_image;
		}

		$base_url = trailingslashit( dirname( $src_match[1] ) );

		return $this->uses_picture_element()
			? $this->wrap_in_picture( $filtered_image, $variants, $metadata, $base_url )
			: $this->rewrite_src_in_place( $filtered_image, $variants, $metadata, $base_url, $mime );
	}

	/**
	 * Rewrite the <img> tag's src and every URL in its srcset to point at
	 * the matching modern-format variant, falling back to leaving a
	 * candidate URL untouched if no variant was generated for its size
	 * (e.g. a size added after this attachment was last converted).
	 *
	 * @param string $image_tag Full <img ...> tag markup.
	 * @param array  $variants  Full variants map for the attachment (size => mime => file data).
	 * @param array  $metadata  Attachment metadata, used to resolve each srcset URL's size.
	 * @param string $base_url  Directory URL the variant files live in.
	 * @param string $mime      Target MIME type to switch to.
	 * @return string
	 */
	private function rewrite_src_in_place( $image_tag, $variants, $metadata, $base_url, $mime ) {
		$image_tag = preg_replace_callback(
			'/\bsrc=(["\'])([^"\']+)\1/',
			function ( $matches ) use ( $variants, $metadata, $base_url, $mime ) {
				return 'src=' . $matches[1] . esc_url( $this->variant_url_for( $matches[2], $variants, $metadata, $base_url, $mime ) ) . $matches[1];
			},
			$image_tag,
			1
		);

		return preg_replace_callback(
			'/\bsrcset=(["\'])([^"\']+)\1/',
			function ( $matches ) use ( $variants, $metadata, $base_url, $mime ) {
				return 'srcset=' . $matches[1] . esc_attr( $this->rewrite_srcset( $matches[2], $variants, $metadata, $base_url, $mime ) ) . $matches[1];
			},
			$image_tag,
			1
		);
	}

	/**
	 * Rewrite every "url widthDescriptor" candidate in a srcset attribute
	 * value, swapping each one for its matching modern-format variant.
	 *
	 * @param string $srcset   Original srcset attribute value.
	 * @param array  $variants Full variants map for the attachment.
	 * @param array  $metadata Attachment metadata.
	 * @param string $base_url Directory URL the variant files live in.
	 * @param string $mime     Target MIME type to switch to.
	 * @return string
	 */
	private function rewrite_srcset( $srcset, $variants, $metadata, $base_url, $mime ) {
		$candidates = array_map( 'trim', explode( ',', $srcset ) );

		foreach ( $candidates as &$candidate ) {
			if ( ! preg_match( '/^(\S+)(\s+.+)?$/', $candidate, $parts ) ) {
				continue;
			}
			$variant_url = $this->variant_url_for( $parts[1], $variants, $metadata, $base_url, $mime );
			$candidate   = $variant_url . ( $parts[2] ?? '' );
		}

		return implode( ', ', $candidates );
	}

	/**
	 * Resolve a single image URL to its matching modern-format variant URL,
	 * or return it unchanged if its size has no variant in the target format.
	 *
	 * @param string $url      Original image URL.
	 * @param array  $variants Full variants map for the attachment.
	 * @param array  $metadata Attachment metadata.
	 * @param string $base_url Directory URL the variant files live in.
	 * @param string $mime     Target MIME type to switch to.
	 * @return string
	 */
	private function variant_url_for( $url, $variants, $metadata, $base_url, $mime ) {
		$size_key = $this->match_variant_size( $url, $metadata );

		if ( null === $size_key || empty( $variants[ $size_key ][ $mime ]['file'] ) ) {
			return $url;
		}

		return $base_url . $variants[ $size_key ][ $mime ]['file'];
	}

	/**
	 * Wrap the original <img> tag in a <picture> element with one <source>
	 * per generated format, keeping the <img> itself as the last child so
	 * browsers without modern-format support fall back to it automatically.
	 *
	 * @param string $image_tag Full <img ...> tag markup.
	 * @param array  $variants  Full variants map for the attachment.
	 * @param array  $metadata  Attachment metadata, used to resolve the matched size.
	 * @param string $base_url  Directory URL the variant files live in.
	 * @return string
	 */
	private function wrap_in_picture( $image_tag, $variants, $metadata, $base_url ) {
		if ( ! preg_match( '/src=["\']([^"\']+)["\']/', $image_tag, $src_match ) ) {
			return $image_tag;
		}

		$size_key = $this->match_variant_size( $src_match[1], $metadata );
		if ( null === $size_key || empty( $variants[ $size_key ] ) ) {
			return $image_tag;
		}

		$sources = '';

		foreach ( array( 'image/avif', 'image/webp' ) as $mime ) {
			if ( empty( $variants[ $size_key ][ $mime ]['file'] ) ) {
				continue;
			}
			$sources .= sprintf(
				'<source type="%1$s" srcset="%2$s" />',
				esc_attr( $mime ),
				esc_url( $base_url . $variants[ $size_key ][ $mime ]['file'] )
			);
		}

		if ( '' === $sources ) {
			return $image_tag;
		}

		// The <img> tag itself is already WP-escaped markup; only the
		// <source> URLs added above are freshly built, and those go
		// through esc_url()/esc_attr() above.
		return '<picture class="frbl-picture">' . $sources . $image_tag . '</picture>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
