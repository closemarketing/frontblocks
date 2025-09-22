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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_filter( 'render_block_generateblocks/grid', array( $this, 'add_sticky_attributes_to_grid_block' ), 10, 2 );
		add_action( 'init', array( $this, 'register_custom_attributes' ), 5 );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$dist_dir = WP_DEBUG ? 'sticky-column/' : 'dist/';
		
		wp_enqueue_style(
			'frontblocks-sticky-column',
			FRBL_PLUGIN_URL . 'assets/' . $dist_dir . 'frontblocks-sticky-column.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-sticky-column-custom',
			FRBL_PLUGIN_URL . 'assets/' . $dist_dir . ( WP_DEBUG ? 'frontblocks-sticky-column.js' : 'frontblocks-sticky-column-min.js' ),
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
			FRBL_PLUGIN_URL . 'assets/sticky-column/frontblocks-sticky-column-option.jsx',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post' ),
			FRBL_VERSION,
			true
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
		$attrs = $block['attrs'] ?? array();
		$sticky_enabled = isset( $attrs['frblStickyEnabled'] ) ? (bool) $attrs['frblStickyEnabled'] : false;
		$sticky_offset = isset( $attrs['frblStickyOffset'] ) ? (int) $attrs['frblStickyOffset'] : 0;
		$sticky_column_index = isset( $attrs['frblStickyColumnIndex'] ) ? (int) $attrs['frblStickyColumnIndex'] : 0;

		// Add sticky attributes to the wrapper div if sticky is enabled.
		if ( $sticky_enabled ) {
			$block_content = preg_replace(
				'/<div([^>]*)class="([^"]*gb-grid-wrapper[^"]*)"([^>]*)>/',
				'<div$1class="$2 frontblocks-sticky-wrapper"$3' .
					' data-sticky-enabled="' . esc_attr( $sticky_enabled ? 'true' : 'false' ) . '"' .
					' data-sticky-offset="' . esc_attr( $sticky_offset ) . '"' .
					' data-sticky-column-index="' . esc_attr( $sticky_column_index ) . '"' .
					'>',
				$block_content,
				1 // Only replace the first occurrence.
			);
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

		$block_args['attributes']['frblStickyEnabled'] = array(
			'type'    => 'boolean',
			'default' => false,
		);
		$block_args['attributes']['frblStickyOffset'] = array(
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
					if ( name !== 'generateblocks/grid' ) {
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
