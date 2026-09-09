<?php
/**
 * Column Link module for FrontBlocks (native column block enhancement).
 *
 * @package    FrontBlocks
 * @author     Alex castellón <castellon@close.technology>
 * @copyright  2026 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * ColumnLink class.
 *
 * Adds a "Column Link" option to the native core/column block. When a URL
 * is set, the whole column becomes clickable and navigates to that URL —
 * except for clicks that land on a real link, button, or other interactive
 * element already inside the column, which keep working normally.
 *
 * @since 1.6.0
 */
class ColumnLink {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_editor_styles' ) );
		add_filter( 'register_block_type_args', array( $this, 'register_native_block_attributes' ), 10, 2 );
		add_filter( 'render_block_core/column', array( $this, 'apply_column_link' ), 10, 2 );
	}

	/**
	 * Register block assets for the editor and the frontend.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_script(
			'frontblocks-column-link-editor',
			FRBL_PLUGIN_URL . 'assets/column-link/frontblocks-column-link-option.js',
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

		wp_register_style(
			'frontblocks-column-link',
			FRBL_PLUGIN_URL . 'assets/column-link/frontblocks-column-link.css',
			array(),
			FRBL_VERSION
		);

		wp_register_script(
			'frontblocks-column-link-custom',
			FRBL_PLUGIN_URL . 'assets/column-link/frontblocks-column-link.js',
			array(),
			FRBL_VERSION,
			true
		);
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script( 'frontblocks-column-link-editor' );

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-column-link-editor',
			'frontblocks'
		);
	}

	/**
	 * Inject editor styles inside the iframe via wp-block-library inline
	 * styles, so a linked column gets a visual hint in the editor without
	 * making the canvas itself navigate on click.
	 *
	 * `wp-block-library` is guaranteed to load inside the WP 6.3+ editor
	 * iframe, so attaching inline CSS to it ensures the styles reach block
	 * content — same approach as ColumnsSameHeight::enqueue_editor_styles().
	 *
	 * @return void
	 */
	public function enqueue_editor_styles() {
		if ( ! is_admin() ) {
			return;
		}

		$css = '
			[data-frbl-column-link-active] {
				outline: 1px dashed currentColor;
				outline-offset: -1px;
			}
		';

		wp_add_inline_style( 'wp-block-library', $css );
	}

	/**
	 * Register the link attributes server-side for the native column block.
	 *
	 * @param array  $args       Block type args.
	 * @param string $block_type Block type name.
	 * @return array
	 */
	public function register_native_block_attributes( $args, $block_type ) {
		if ( 'core/column' !== $block_type ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		$args['attributes']['frblColumnLinkUrl'] = array(
			'type'    => 'string',
			'default' => '',
		);

		$args['attributes']['frblColumnLinkNewTab'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		return $args;
	}

	/**
	 * Make the whole column clickable when a link URL is configured.
	 *
	 * @param string $block_content Block HTML.
	 * @param array  $block         Block data.
	 * @return string
	 */
	public function apply_column_link( $block_content, $block ) {
		$attrs = $block['attrs'] ?? array();
		$url   = trim( (string) ( $attrs['frblColumnLinkUrl'] ?? '' ) );

		if ( '' === $url ) {
			return $block_content;
		}

		$escaped_url = esc_url( $url );

		// esc_url() strips disallowed protocols (e.g. javascript:) and
		// returns an empty string for anything it can't make safe — bail
		// rather than render an interactive column with nowhere to go.
		if ( '' === $escaped_url ) {
			return $block_content;
		}

		$new_tab = ! empty( $attrs['frblColumnLinkNewTab'] );

		if ( ! wp_style_is( 'frontblocks-column-link', 'enqueued' ) ) {
			wp_enqueue_style( 'frontblocks-column-link' );
		}
		if ( ! wp_script_is( 'frontblocks-column-link-custom', 'enqueued' ) ) {
			wp_enqueue_script( 'frontblocks-column-link-custom' );
		}

		if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$processor = new \WP_HTML_Tag_Processor( $block_content );

			if ( $processor->next_tag( array( 'class_name' => 'wp-block-column' ) ) ) {
				$existing = $processor->get_attribute( 'class' ) ?? '';
				$processor->set_attribute( 'class', trim( $existing . ' frbl-column-link' ) );
				$processor->set_attribute( 'data-frbl-column-link-url', $escaped_url );
				$processor->set_attribute( 'role', 'link' );
				$processor->set_attribute( 'tabindex', '0' );

				if ( $new_tab ) {
					$processor->set_attribute( 'data-frbl-column-link-target', '_blank' );
				}

				return $processor->get_updated_html();
			}

			return $block_content;
		}

		// Fallback for WP < 6.2.
		$extra_attrs  = ' data-frbl-column-link-url="' . esc_attr( $escaped_url ) . '"';
		$extra_attrs .= $new_tab ? ' data-frbl-column-link-target="_blank"' : '';
		$extra_attrs .= ' role="link" tabindex="0"';

		return preg_replace(
			'/<div([^>]*)class="([^"]*wp-block-column[^"]*)"([^>]*)>/',
			'<div$1class="$2 frbl-column-link"$3' . $extra_attrs . '>',
			$block_content,
			1
		);
	}
}
