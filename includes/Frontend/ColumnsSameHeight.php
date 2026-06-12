<?php
/**
 * Columns Same Height module for FrontBlocks (native columns block enhancement).
 *
 * @package    FrontBlocks
 * @author     Alex castellón <castellon@close.technology>
 * @copyright  2026 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * ColumnsSameHeight class.
 *
 * Adds a "Same height columns" toggle to the native core/columns block.
 * When enabled, all sibling columns stretch to the height of the tallest one.
 *
 * @since 1.3.7
 */
class ColumnsSameHeight {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_filter( 'register_block_type_args', array( $this, 'register_native_block_attributes' ), 10, 2 );
		add_filter( 'render_block_core/columns', array( $this, 'apply_same_height_class' ), 10, 2 );
	}

	/**
	 * Register block assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_script(
			'frontblocks-columns-same-height-editor',
			FRBL_PLUGIN_URL . 'assets/columns-same-height/frontblocks-columns-same-height-option.js',
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
			'frontblocks-columns-same-height',
			FRBL_PLUGIN_URL . 'assets/columns-same-height/frontblocks-columns-same-height.css',
			array(),
			FRBL_VERSION
		);
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script( 'frontblocks-columns-same-height-editor' );

		wp_set_script_translations(
			'frontblocks-columns-same-height-editor',
			'frontblocks'
		);
	}

	/**
	 * Register frblSameHeight attribute server-side for core/columns.
	 *
	 * @param array  $args       Block type args.
	 * @param string $block_type Block type name.
	 * @return array
	 */
	public function register_native_block_attributes( $args, $block_type ) {
		if ( 'core/columns' !== $block_type ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		$args['attributes']['frblSameHeight'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		return $args;
	}

	/**
	 * Add the same-height CSS class to the columns wrapper and enqueue styles.
	 *
	 * @param string $block_content Block HTML.
	 * @param array  $block         Block data.
	 * @return string
	 */
	public function apply_same_height_class( $block_content, $block ) {
		$attrs = $block['attrs'] ?? array();

		if ( empty( $attrs['frblSameHeight'] ) ) {
			return $block_content;
		}

		if ( ! wp_style_is( 'frontblocks-columns-same-height', 'enqueued' ) ) {
			wp_enqueue_style( 'frontblocks-columns-same-height' );
		}

		if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$processor = new \WP_HTML_Tag_Processor( $block_content );

			if ( $processor->next_tag( array( 'class_name' => 'wp-block-columns' ) ) ) {
				$existing = $processor->get_attribute( 'class' ) ?? '';
				$processor->set_attribute( 'class', trim( $existing . ' frbl-columns-same-height' ) );
				return $processor->get_updated_html();
			}

			return $block_content;
		}

		// Fallback for WP < 6.2.
		return preg_replace(
			'/(<div[^>]+class="[^"]*wp-block-columns[^"]*")/',
			'$1 frbl-columns-same-height',
			$block_content,
			1
		);
	}
}
