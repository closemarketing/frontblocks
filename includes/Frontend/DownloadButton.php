<?php
/**
 * Download Button module for FrontBlocks (native button block download option).
 *
 * @package    FrontBlocks
 * @author     Alex castellón <castellon@close.technology>
 * @copyright  2026 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * DownloadButton class.
 *
 * Adds a download toggle to the native core/button block. When enabled,
 * the button links to an uploaded file and forces the browser to download
 * it instead of navigating to a URL.
 *
 * @since 1.3.7
 */
class DownloadButton {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_filter( 'register_block_type_args', array( $this, 'register_native_block_attributes' ), 10, 2 );
		add_filter( 'render_block_core/button', array( $this, 'apply_download_to_button' ), 10, 2 );
	}

	/**
	 * Register block assets for backend editor.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_script(
			'frontblocks-download-button-editor',
			FRBL_PLUGIN_URL . 'assets/download-button/frontblocks-download-button-option.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-block-editor',
				'wp-components',
				'wp-compose',
				'wp-hooks',
				'wp-i18n',
			),
			FRBL_VERSION,
			true
		);
	}

	/**
	 * Editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script( 'frontblocks-download-button-editor' );

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-download-button-editor',
			'frontblocks'
		);
	}

	/**
	 * Register download attributes server-side for the native button block.
	 *
	 * @param array  $args       Block type args.
	 * @param string $block_type Block type name.
	 * @return array
	 */
	public function register_native_block_attributes( $args, $block_type ) {
		if ( 'core/button' !== $block_type ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		$args['attributes']['frblDownloadEnabled'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		$args['attributes']['frblDownloadFileId'] = array(
			'type'    => 'number',
			'default' => 0,
		);

		$args['attributes']['frblDownloadFileUrl'] = array(
			'type'    => 'string',
			'default' => '',
		);

		$args['attributes']['frblDownloadFileName'] = array(
			'type'    => 'string',
			'default' => '',
		);

		return $args;
	}

	/**
	 * Force the button anchor to download the selected file.
	 *
	 * @param string $block_content Block HTML.
	 * @param array  $block         Block data.
	 * @return string
	 */
	public function apply_download_to_button( $block_content, $block ) {
		$attrs = $block['attrs'] ?? array();

		if ( empty( $attrs['frblDownloadEnabled'] ) ) {
			return $block_content;
		}

		$file_url = $this->resolve_file_url( $attrs );

		if ( empty( $file_url ) ) {
			return $block_content;
		}

		$file_name = ! empty( $attrs['frblDownloadFileName'] ) ? sanitize_file_name( $attrs['frblDownloadFileName'] ) : '';

		// WP 6.2+: rewrite the anchor with the HTML API.
		if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$processor = new \WP_HTML_Tag_Processor( $block_content );

			if ( $processor->next_tag( 'a' ) ) {
				$processor->set_attribute( 'href', esc_url( $file_url ) );
				$processor->set_attribute( 'download', $file_name ? $file_name : true );
				$processor->remove_attribute( 'target' );

				return $processor->get_updated_html();
			}

			return $block_content;
		}

		// Fallback for older WordPress: inject attributes into the first anchor.
		$download_attr = $file_name ? ' download="' . esc_attr( $file_name ) . '"' : ' download';

		return preg_replace(
			'/<a\s([^>]*?)href="[^"]*"([^>]*)>/i',
			'<a $1href="' . esc_url( $file_url ) . '"' . $download_attr . '$2>',
			$block_content,
			1
		);
	}

	/**
	 * Resolve the download file URL, preferring a fresh attachment URL.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	private function resolve_file_url( $attrs ) {
		// Prefer the attachment ID so the URL survives file moves or renames.
		if ( ! empty( $attrs['frblDownloadFileId'] ) ) {
			$attachment_url = wp_get_attachment_url( absint( $attrs['frblDownloadFileId'] ) );

			if ( $attachment_url ) {
				return $attachment_url;
			}
		}

		return ! empty( $attrs['frblDownloadFileUrl'] ) ? esc_url_raw( $attrs['frblDownloadFileUrl'] ) : '';
	}
}
