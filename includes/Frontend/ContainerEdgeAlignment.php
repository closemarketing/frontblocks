<?php
/**
 * Container Edge Alignment
 *
 * Adds custom controls to GenerateBlocks Container block to remove padding
 * from left or right side, creating an edge-to-edge effect on one side.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Container Edge Alignment class.
 *
 * @since 1.0.0
 */
class ContainerEdgeAlignment {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_frontend_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_filter( 'render_block', array( $this, 'add_edge_alignment_classes' ), 10, 2 );
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script(
			'frbl-edge-alignment-option',
			FRBL_PLUGIN_URL . 'assets/container-edge-alignment/frontblocks-edge-alignment.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-hooks', 'wp-compose', 'wp-block-editor' ),
			FRBL_VERSION,
			true
		);

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frbl-edge-alignment-option',
			'frontblocks'
		);

		wp_enqueue_style(
			'frbl-edge-alignment-editor',
			FRBL_PLUGIN_URL . 'assets/container-edge-alignment/frontblocks-edge-alignment.css',
			array(),
			FRBL_VERSION
		);
	}

	/**
	 * Register frontend assets for conditional enqueueing.
	 *
	 * @return void
	 */
	public function register_frontend_assets() {
		wp_register_style(
			'frbl-edge-alignment',
			FRBL_PLUGIN_URL . 'assets/container-edge-alignment/frontblocks-edge-alignment.css',
			array(),
			FRBL_VERSION
		);

		wp_register_script(
			'frbl-edge-alignment-js',
			FRBL_PLUGIN_URL . 'assets/container-edge-alignment/frontblocks-edge-alignment-frontend.js',
			array(),
			FRBL_VERSION,
			true
		);
	}

	/**
	 * Add edge alignment classes to blocks.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block data.
	 * @return string Modified block content.
	 */
	public function add_edge_alignment_classes( $block_content, $block ) {
		// Only process GenerateBlocks container blocks (support both old and new versions).
		if ( 'generateblocks/container' !== $block['blockName'] && 'generateblocks/element' !== $block['blockName'] ) {
			return $block_content;
		}

		// Check if edge alignment attribute exists.
		if ( empty( $block['attrs']['frblEdgeAlignment'] ) ) {
			return $block_content;
		}

		// Get the edge alignment value.
		$edge_alignment = $block['attrs']['frblEdgeAlignment'];

		// Determine which class to add.
		$class_string = '';

		if ( 'left' === $edge_alignment ) {
			$class_string = 'frbl-edge-left';
		} elseif ( 'right' === $edge_alignment ) {
			$class_string = 'frbl-edge-right';
		}

		// If no valid alignment, return.
		if ( empty( $class_string ) ) {
			return $block_content;
		}

		// Enqueue frontend assets only when an edge-aligned block is detected.
		if ( ! wp_style_is( 'frbl-edge-alignment', 'enqueued' ) ) {
			wp_enqueue_style( 'frbl-edge-alignment' );
		}
		if ( ! wp_script_is( 'frbl-edge-alignment-js', 'enqueued' ) ) {
			wp_enqueue_script( 'frbl-edge-alignment-js' );
		}

		// Add class to the block.
		// Find the first occurrence of class=" and add our class.
		if ( false !== strpos( $block_content, 'class="' ) ) {
			$block_content = preg_replace(
				'/class="/',
				'class="' . esc_attr( $class_string ) . ' ',
				$block_content,
				1
			);
		} else {
			// If no class attribute exists, add one after the first tag opening.
			$block_content = preg_replace(
				'/^<(\w+)/',
				'<$1 class="' . esc_attr( $class_string ) . '"',
				$block_content,
				1
			);
		}

		return $block_content;
	}
}
