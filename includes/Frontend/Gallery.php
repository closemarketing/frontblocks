<?php
/**
 * Gallery module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Gallery class.
 *
 * @since 1.0.0
 */
class Gallery {

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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_filter( 'render_block_core/gallery', array( $this, 'add_custom_attributes_to_gallery_block' ), 10, 2 );
		add_action( 'init', array( $this, 'register_custom_attributes' ), 5 );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Register scripts and styles.
		wp_register_style(
			'frontblocks-gallery',
			FRBL_PLUGIN_URL . 'assets/gallery/frontblocks-gallery.css',
			array(),
			FRBL_VERSION
		);

		// Register Masonry library.
		wp_register_script(
			'frontblocks-masonry',
			FRBL_PLUGIN_URL . 'assets/gallery/masonry.min.js',
			array(),
			'4.2.2',
			true
		);

		wp_register_script(
			'frontblocks-gallery-custom',
			FRBL_PLUGIN_URL . 'assets/gallery/frontblocks-gallery.js',
			array( 'frontblocks-masonry' ),
			FRBL_VERSION,
			true
		);

		// Check if we have gallery blocks in the content.
		if ( is_admin() || has_block( 'core/gallery' ) ) {
			wp_enqueue_style( 'frontblocks-gallery' );
			wp_enqueue_script( 'frontblocks-masonry' );
			wp_enqueue_script( 'frontblocks-gallery-custom' );
		}
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'frontblocks-gallery-option',
			FRBL_PLUGIN_URL . 'assets/gallery/frontblocks-gallery-option.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post', 'wp-i18n', 'wp-block-editor', 'wp-compose' ),
			FRBL_VERSION,
			true
		);

		// Enqueue gallery styles in editor for live preview.
		wp_enqueue_style(
			'frontblocks-gallery',
			FRBL_PLUGIN_URL . 'assets/gallery/frontblocks-gallery.css',
			array(),
			FRBL_VERSION
		);
	}

	/**
	 * Add custom attributes to gallery block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function add_custom_attributes_to_gallery_block( $block_content, $block ) {
		$attrs           = $block['attrs'] ?? array();
		$gallery_layout  = isset( $attrs['frblGalleryLayout'] ) ? sanitize_text_field( $attrs['frblGalleryLayout'] ) : 'grid';
		$gutter_size     = isset( $attrs['frblGutterSize'] ) ? (int) $attrs['frblGutterSize'] : 20;
		$enable_lightbox = isset( $attrs['frblEnableLightbox'] ) ? (bool) $attrs['frblEnableLightbox'] : false;

		// Use WordPress built-in columns setting.
		$columns = isset( $attrs['columns'] ) ? (int) $attrs['columns'] : 3;

		// Enqueue scripts when we detect a gallery with custom layout.
		if ( 'masonry' === $gallery_layout || 'grid' === $gallery_layout ) {
			wp_enqueue_style( 'frontblocks-gallery' );
			wp_enqueue_script( 'frontblocks-masonry' );
			wp_enqueue_script( 'frontblocks-gallery-custom' );
		}

		// Add data attributes to the figure element if masonry is enabled.
		if ( 'masonry' === $gallery_layout ) {
			$block_content = preg_replace(
				'/<figure([^>]*)class="([^"]*wp-block-gallery[^"]*)"([^>]*)>/',
				'<figure$1class="$2 frontblocks-gallery-masonry"$3' .
					' data-layout="' . esc_attr( $gallery_layout ) . '"' .
					' data-columns="' . esc_attr( $columns ) . '"' .
					' data-gutter="' . esc_attr( $gutter_size ) . '"' .
					' data-lightbox="' . esc_attr( $enable_lightbox ? 'true' : 'false' ) . '"' .
					'>',
				$block_content,
				1 // Only replace the first occurrence.
			);
		} elseif ( 'grid' === $gallery_layout ) {
			$block_content = preg_replace(
				'/<figure([^>]*)class="([^"]*wp-block-gallery[^"]*)"([^>]*)>/',
				'<figure$1class="$2 frontblocks-gallery-grid"$3' .
					' data-layout="' . esc_attr( $gallery_layout ) . '"' .
					' data-columns="' . esc_attr( $columns ) . '"' .
					' data-gutter="' . esc_attr( $gutter_size ) . '"' .
					' data-lightbox="' . esc_attr( $enable_lightbox ? 'true' : 'false' ) . '"' .
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
		// Register attributes before WordPress registers its blocks.
		add_filter(
			'register_block_type_args',
			array( $this, 'register_custom_attributes_for_gallery_block' ),
			9,
			2
		);

		// Register attributes from the frontend side as well.
		add_action(
			'enqueue_block_editor_assets',
			array( $this, 'add_inline_script_for_attributes' )
		);
	}

	/**
	 * Register custom attributes for WordPress Gallery block.
	 *
	 * @param array  $args The block arguments.
	 * @param string $name The name of the block.
	 * @return array Modified block arguments.
	 */
	public function register_custom_attributes_for_gallery_block( $args, $name ) {
		if ( 'core/gallery' !== $name ) {
			return $args;
		}

		$args['attributes']['frblGalleryLayout']  = array(
			'type'    => 'string',
			'default' => 'grid',
		);
		$args['attributes']['frblGutterSize']     = array(
			'type'    => 'number',
			'default' => 20,
		);
		$args['attributes']['frblEnableLightbox'] = array(
			'type'    => 'boolean',
			'default' => false,
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
				'frontblocks/gallery-attributes',
				function( settings, name ) {
					if ( name !== 'core/gallery' ) {
						return settings;
					}

					settings.attributes = {
						...settings.attributes,
						frblGalleryLayout: {
							type: 'string',
							default: 'grid'
						},
						frblGutterSize: {
							type: 'number',
							default: 20
						},
						frblEnableLightbox: {
							type: 'boolean',
							default: false
						}
					};

					return settings;
				}
			);
			"
		);
	}
}
