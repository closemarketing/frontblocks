<?php
/**
 * Sticky Column module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * StickyColumn class.
 *
 * @since 1.0.0
 */
class StickyColumn {

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
		add_action( 'init', array( $this, 'register_scripts' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_filter( 'render_block_generateblocks/grid', array( $this, 'add_sticky_attributes_to_grid_block' ), 10, 2 );
		add_filter( 'render_block_core/columns', array( $this, 'add_sticky_attributes_to_columns_block' ), 10, 2 );
		add_action( 'init', array( $this, 'register_custom_attributes' ), 5 );
	}

	/**
	 * Register frontend scripts and styles for conditional enqueueing.
	 *
	 * @return void
	 */
	public function register_scripts() {
		wp_register_style(
			'frontblocks-sticky-column',
			FRBL_PLUGIN_URL . 'assets/sticky-column/frontblocks-sticky-column.css',
			array(),
			FRBL_VERSION
		);

		wp_register_script(
			'frontblocks-sticky-column-custom',
			FRBL_PLUGIN_URL . 'assets/sticky-column/frontblocks-sticky-column.js',
			array(),
			FRBL_VERSION,
			true
		);
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'frontblocks-sticky-column-editor',
			FRBL_PLUGIN_URL . 'assets/sticky-column/frontblocks-sticky-column-option.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-i18n', 'wp-hooks', 'wp-block-editor' ),
			FRBL_VERSION,
			false
		);

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-sticky-column-editor',
			'frontblocks'
		);
	}

	/**
	 * Add sticky attributes to grid block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function add_sticky_attributes_to_grid_block( $block_content, $block ) {
		return $this->add_sticky_attributes( $block_content, $block, 'gb-grid-wrapper' );
	}

	/**
	 * Add sticky attributes to native core/columns block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function add_sticky_attributes_to_columns_block( $block_content, $block ) {
		return $this->add_sticky_attributes( $block_content, $block, 'wp-block-columns' );
	}

	/**
	 * Generic method to inject sticky wrapper class and data attributes.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block data.
	 * @param string $wrapper_class CSS class that identifies the wrapper div.
	 * @return string
	 */
	private function add_sticky_attributes( $block_content, $block, $wrapper_class ) {
		$attrs               = $block['attrs'] ?? array();
		$sticky_enabled      = isset( $attrs['frblStickyEnabled'] ) ? (bool) $attrs['frblStickyEnabled'] : false;
		$sticky_offset       = isset( $attrs['frblStickyOffset'] ) ? (int) $attrs['frblStickyOffset'] : 0;
		$sticky_column_index = isset( $attrs['frblStickyColumnIndex'] ) ? (int) $attrs['frblStickyColumnIndex'] : 0;

		if ( ! $sticky_enabled ) {
			return $block_content;
		}

		$block_content = preg_replace(
			'/<div([^>]*)class="([^"]*' . preg_quote( $wrapper_class, '/' ) . '[^"]*)"([^>]*)>/',
			'<div$1class="$2 frontblocks-sticky-wrapper"$3' .
				' data-sticky-enabled="true"' .
				' data-sticky-offset="' . esc_attr( $sticky_offset ) . '"' .
				' data-sticky-column-index="' . esc_attr( $sticky_column_index ) . '"' .
				'>',
			$block_content,
			1
		);

		if ( ! wp_style_is( 'frontblocks-sticky-column', 'enqueued' ) ) {
			wp_enqueue_style( 'frontblocks-sticky-column' );
		}
		if ( ! wp_script_is( 'frontblocks-sticky-column-custom', 'enqueued' ) ) {
			wp_enqueue_script( 'frontblocks-sticky-column-custom' );
		}

		return $block_content;
	}

	/**
	 * Register custom attributes for blocks.
	 *
	 * @return void
	 */
	public function register_custom_attributes() {
		// Register attributes before GenerateBlocks registers its blocks.
		add_filter(
			'generateblocks_blocks_registered_block',
			array( $this, 'register_sticky_attributes_for_grid_block' ),
			9,
			2
		);

		// Register attributes for native core/columns block.
		add_filter( 'register_block_type_args', array( $this, 'register_sticky_attributes_for_columns_block' ), 10, 2 );

		// Register attributes from frontend side as well.
		add_action(
			'enqueue_block_editor_assets',
			array( $this, 'add_inline_script_for_attributes' )
		);
	}

	/**
	 * Register sticky attributes for GenerateBlocks Grid block.
	 *
	 * @param array  $block_args The block arguments.
	 * @param string $block_type The name of the block.
	 * @return array Modified block arguments.
	 */
	public function register_sticky_attributes_for_grid_block( $block_args, $block_type ) {
		if ( 'generateblocks/grid' !== $block_type ) {
			return $block_args;
		}

		$block_args['attributes']['frblStickyEnabled']     = array(
			'type'    => 'boolean',
			'default' => false,
		);
		$block_args['attributes']['frblStickyOffset']      = array(
			'type'    => 'number',
			'default' => 0,
		);
		$block_args['attributes']['frblStickyColumnIndex'] = array(
			'type'    => 'number',
			'default' => 0,
		);

		return $block_args;
	}

	/**
	 * Register sticky attributes for native core/columns block via register_block_type_args.
	 *
	 * @param array  $args       Block type arguments.
	 * @param string $block_type Block type name.
	 * @return array
	 */
	public function register_sticky_attributes_for_columns_block( $args, $block_type ) {
		if ( 'core/columns' !== $block_type ) {
			return $args;
		}

		$args['attributes']['frblStickyEnabled']     = array(
			'type'    => 'boolean',
			'default' => false,
		);
		$args['attributes']['frblStickyOffset']      = array(
			'type'    => 'number',
			'default' => 0,
		);
		$args['attributes']['frblStickyColumnIndex'] = array(
			'type'    => 'number',
			'default' => 0,
		);

		return $args;
	}

	/**
	 * Add inline script for block attributes.
	 *
	 * @return void
	 */
	public function add_inline_script_for_attributes() {
		wp_add_inline_script(
			'wp-blocks',
			"
			wp.hooks.addFilter(
				'blocks.registerBlockType',
				'frontblocks/sticky-attributes',
				function( settings, name ) {
					if ( name !== 'generateblocks/grid' && name !== 'core/columns' ) {
						return settings;
					}

					settings.attributes = {
						...settings.attributes,
						frblStickyEnabled: {
							type: 'boolean',
							default: false
						},
						frblStickyOffset: {
							type: 'number',
							default: 0
						},
						frblStickyColumnIndex: {
							type: 'number',
							default: 0
						}
					};

					return settings;
				}
			);
			"
		);
	}
}
