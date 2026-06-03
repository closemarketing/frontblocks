<?php
/**
 * SVG Upload
 *
 * Allows SVG files to be uploaded to the WordPress media library.
 * Sanitizes SVG content on upload to prevent XSS attacks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * SVG Upload class.
 *
 * Enables SVG uploads in the media library with server-side sanitization.
 */
class SvgUpload {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_filter( 'upload_mimes', array( $this, 'add_svg_mime' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'fix_svg_mime_check' ), 10, 5 );
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'sanitize_svg_upload' ) );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'fix_svg_in_media_library' ), 10, 3 );
	}

	/**
	 * Add SVG to allowed upload MIME types.
	 *
	 * @param array $mimes Allowed MIME types.
	 * @return array
	 */
	public function add_svg_mime( $mimes ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
		return $mimes;
	}

	/**
	 * Fix MIME type detection for SVG files.
	 *
	 * PHP's finfo often misidentifies SVGs; this corrects the check.
	 *
	 * @param array       $data      File data.
	 * @param string      $file      Full path to the file.
	 * @param string      $filename  Original filename.
	 * @param array       $mimes     Allowed MIME types.
	 * @param string|bool $real_mime Detected MIME type from finfo.
	 * @return array
	 */
	public function fix_svg_mime_check( $data, $file, $filename, $mimes, $real_mime ) {
		if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
			return $data;
		}

		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		if ( 'svg' === $ext || 'svgz' === $ext ) {
			$data['ext']  = $ext;
			$data['type'] = 'image/svg+xml';
		}

		return $data;
	}

	/**
	 * Sanitize SVG file on upload.
	 *
	 * Strips dangerous elements and event-handler attributes before saving.
	 *
	 * @param array $file Upload data.
	 * @return array
	 */
	public function sanitize_svg_upload( $file ) {
		if ( 'image/svg+xml' !== $file['type'] ) {
			return $file;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$file['error'] = __( 'Permission denied: only administrators can upload SVG files.', 'frontblocks' );
			return $file;
		}

		$svg_content = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $svg_content ) {
			$file['error'] = __( 'Could not read the SVG file.', 'frontblocks' );
			return $file;
		}

		$sanitized = $this->sanitize_svg( $svg_content );

		if ( false === $sanitized ) {
			$file['error'] = __( 'Invalid SVG file: the file does not appear to be a valid SVG.', 'frontblocks' );
			return $file;
		}

		file_put_contents( $file['tmp_name'], $sanitized ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents

		return $file;
	}

	/**
	 * Sanitize SVG content by removing dangerous elements and attributes.
	 *
	 * @param string $content Raw SVG content.
	 * @return string|false Sanitized SVG, or false if invalid.
	 */
	private function sanitize_svg( $content ) {
		if ( empty( trim( $content ) ) ) {
			return false;
		}

		// Remove PHP processing instructions (not XML declarations).
		$content = preg_replace( '/<\?(?!xml\s).*?\?>/si', '', $content );

		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadXML( $content, LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( false );

		// Verify it's actually an SVG.
		$svg_elements = $dom->getElementsByTagName( 'svg' );
		if ( 0 === $svg_elements->length ) {
			return false;
		}

		// Tags that could execute code or load external content.
		$dangerous_tags = array(
			'script',
			'iframe',
			'object',
			'embed',
			'base',
			'form',
			'input',
			'button',
			'textarea',
			'select',
			'option',
			'link',
			'meta',
		);

		foreach ( $dangerous_tags as $tag ) {
			$elements  = $dom->getElementsByTagName( $tag );
			$to_remove = array();
			foreach ( $elements as $element ) {
				$to_remove[] = $element;
			}
			foreach ( $to_remove as $element ) {
				if ( $element->parentNode ) {
					$element->parentNode->removeChild( $element );
				}
			}
		}

		// Collect all elements before iterating (live NodeList can skip nodes).
		$all_elements = $dom->getElementsByTagName( '*' );
		$to_process   = array();
		foreach ( $all_elements as $element ) {
			$to_process[] = $element;
		}

		foreach ( $to_process as $element ) {
			$attrs_to_remove = array();

			foreach ( $element->attributes as $attr ) {
				$attr_lower = strtolower( $attr->nodeName );

				// Remove all on* event handlers.
				if ( 0 === strpos( $attr_lower, 'on' ) ) {
					$attrs_to_remove[] = $attr->nodeName;
					continue;
				}

				// Remove javascript: and data: URIs from URL attributes.
				if ( in_array( $attr_lower, array( 'href', 'xlink:href', 'src', 'action', 'formaction', 'data' ), true ) ) {
					$value = ltrim( preg_replace( '/\s+/', '', $attr->value ) );
					if ( 0 === stripos( $value, 'javascript:' ) || 0 === stripos( $value, 'data:text' ) ) {
						$attrs_to_remove[] = $attr->nodeName;
					}
				}
			}

			foreach ( $attrs_to_remove as $attr_name ) {
				$element->removeAttribute( $attr_name );
			}
		}

		return $dom->saveXML();
	}

	/**
	 * Fix SVG display in the media library JS modal.
	 *
	 * SVGs have no raster dimensions; provide fallback values so the
	 * media library can render a thumbnail without throwing JS errors.
	 *
	 * @param array      $response   Attachment data for JS.
	 * @param \WP_Post   $attachment Attachment post object.
	 * @param array|bool $meta       Attachment meta.
	 * @return array
	 */
	public function fix_svg_in_media_library( $response, $attachment, $meta ) {
		if ( 'image/svg+xml' !== $response['mime'] ) {
			return $response;
		}

		if ( empty( $response['width'] ) ) {
			$response['width'] = 100;
		}

		if ( empty( $response['height'] ) ) {
			$response['height'] = 100;
		}

		if ( empty( $response['sizes'] ) ) {
			$svg_url           = wp_get_attachment_url( $attachment->ID );
			$response['sizes'] = array(
				'full' => array(
					'url'         => $svg_url,
					'width'       => $response['width'],
					'height'      => $response['height'],
					'orientation' => 'landscape',
				),
			);
		}

		return $response;
	}
}
