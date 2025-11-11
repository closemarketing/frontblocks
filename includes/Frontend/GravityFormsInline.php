<?php
/**
 * Gravity Forms Inline Layout module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * GravityFormsInline class.
 *
 * Adds inline layout option for Gravity Forms blocks.
 *
 * @since 1.2.2
 */
class GravityFormsInline {

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
		add_filter( 'render_block', array( $this, 'add_inline_attributes_to_gf_block' ), 10, 2 );
		add_action( 'init', array( $this, 'register_custom_attributes' ), 5 );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'frontblocks-gf-inline',
			FRBL_PLUGIN_URL . 'assets/gravityforms-inline/frontblocks-gf-inline.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-gf-inline-runtime',
			FRBL_PLUGIN_URL . 'assets/gravityforms-inline/frontblocks-gf-inline.js',
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
		// Enqueue CSS for editor preview.
		wp_enqueue_style(
			'frontblocks-gf-inline-editor',
			FRBL_PLUGIN_URL . 'assets/gravityforms-inline/frontblocks-gf-inline.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-gf-inline-editor-js',
			FRBL_PLUGIN_URL . 'assets/gravityforms-inline/frontblocks-gf-inline-option.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-i18n', 'wp-hooks', 'wp-block-editor', 'wp-compose', 'wp-api-fetch' ),
			FRBL_VERSION,
			false
		);

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-gf-inline-editor-js',
			'frontblocks'
		);
	}

	/**
	 * Add inline layout attributes to Gravity Forms block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function add_inline_attributes_to_gf_block( $block_content, $block ) {
		// Only process Gravity Forms blocks.
		if ( ! isset( $block['blockName'] ) || 'gravityforms/form' !== $block['blockName'] ) {
			return $block_content;
		}

		$attrs          = $block['attrs'] ?? array();
		$inline_enabled = isset( $attrs['frblGfInlineEnabled'] ) ? (bool) $attrs['frblGfInlineEnabled'] : false;
		$inline_gap     = isset( $attrs['frblGfInlineGap'] ) ? (int) $attrs['frblGfInlineGap'] : 10;

		// Add inline class and attributes if enabled.
		if ( $inline_enabled ) {
			// Try multiple approaches to add the class.
			// Method 1: Add to wp-block-gravityforms-form wrapper.
			if ( strpos( $block_content, 'wp-block-gravityforms-form' ) !== false ) {
				$block_content = str_replace(
					'wp-block-gravityforms-form',
					'wp-block-gravityforms-form frontblocks-gf-inline',
					$block_content
				);
			}

			// Method 2: Add data attributes to any div with wp-block-gravityforms-form.
			$block_content = preg_replace(
				'/(<div[^>]*class="[^"]*wp-block-gravityforms-form[^"]*")/i',
				'$1 data-gf-inline-enabled="true" data-gf-inline-gap="' . esc_attr( $inline_gap ) . '"',
				$block_content,
				1
			);

			// Method 3: Add wrapper with inline class if not already wrapped.
			if ( strpos( $block_content, 'frontblocks-gf-inline' ) === false && ! empty( $block_content ) ) {
				$block_content = '<div class="frontblocks-gf-inline-wrapper" data-gf-inline-gap="' . esc_attr( $inline_gap ) . '">' . $block_content . '</div>';
			}
		}

		return $block_content;
	}

	/**
	 * Register custom attributes for blocks.
	 *
	 * @return void
	 */
	public function register_custom_attributes() {
		// Register attributes from frontend side.
		add_action(
			'enqueue_block_editor_assets',
			array( $this, 'add_inline_script_for_attributes' )
		);
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
				'frontblocks/gf-inline-attributes',
				function( settings, name ) {
					if ( name !== 'gravityforms/form' ) {
						return settings;
					}

					// Add our custom attributes.
					settings.attributes = {
						...settings.attributes,
						frblGfInlineEnabled: {
							type: 'boolean',
							default: false
						},
						frblGfInlineGap: {
							type: 'number',
							default: 10
						}
					};

					return settings;
				}
			);
			"
		);
	}
}
