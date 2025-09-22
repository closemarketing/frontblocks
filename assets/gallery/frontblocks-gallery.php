<?php
/**
 * Class Gallery
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'frbl_gallery_scripts', 99 );
/**
 * Loads Scripts
 *
 * @return void
 */
function frbl_gallery_scripts() {
	$dist_dir = WP_DEBUG ? 'gallery/' : 'dist/';
	wp_enqueue_style(
		'frontblocks-gallery',
		FRBL_PLUGIN_URL . 'assets/' . $dist_dir . 'frontblocks-gallery.css',
		array(),
		FRBL_VERSION
	);

	// Enqueue Masonry library
    wp_enqueue_script(
		'frontblocks-masonry',
		FRBL_PLUGIN_URL . 'includes/dist/masonry.min.js',
		array(),
		'4.2.2',
		true
	);


	wp_enqueue_script(
		'frontblocks-gallery-custom',
		FRBL_PLUGIN_URL . 'assets/' . $dist_dir . ( WP_DEBUG ? 'frontblocks-gallery.js' : 'frontblocks-gallery-min.js' ),
		array( 'frontblocks-masonry' ),
		FRBL_VERSION,
		true
	);
}

add_action( 'enqueue_block_editor_assets', 'frbl_enqueue_block_editor_assets_gallery' );
/**
 * Enqueue custom block editor script
 *
 * @return void
 */
function frbl_enqueue_block_editor_assets_gallery() {
	wp_enqueue_script(
		'frontblocks-gallery-option',
		FRBL_PLUGIN_URL . 'includes/dist/frontblocks-gallery-option.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post' ),
		FRBL_VERSION,
		true
	);
}

add_filter( 'render_block_core/gallery', 'frbl_add_custom_attributes_to_gallery_block', 10, 2 );
/**
 * Hook to filter the gallery block output on frontend.
 *
 * @param html  $block_content Block content.
 * @param array $block Block attributes.
 *
 * @return html
 */
function frbl_add_custom_attributes_to_gallery_block( $block_content, $block ) {
	$attrs = $block['attrs'] ?? array();
	$gallery_layout = isset( $attrs['frblGalleryLayout'] ) ? sanitize_text_field( $attrs['frblGalleryLayout'] ) : 'grid';
	$gutter_size = isset( $attrs['frblGutterSize'] ) ? (int) $attrs['frblGutterSize'] : 20;
	$enable_lightbox = isset( $attrs['frblEnableLightbox'] ) ? (bool) $attrs['frblEnableLightbox'] : false;
	
	// Use WordPress built-in columns setting
	$columns = isset( $attrs['columns'] ) ? (int) $attrs['columns'] : 3;
	
	// Add data attributes to the figure element if masonry is enabled
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
			1 // Only replace the first occurrence
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
			1 // Only replace the first occurrence
		);
	}

	return $block_content;
}

// Register attributes before WordPress registers its blocks
add_action(
	'init',
	function () {
		// Use priority 9 (before the default 10)
		add_filter(
			'register_block_type_args',
			'frbl_register_custom_attributes_for_gallery_block',
			9,
			2
		);

		// Register attributes from the frontend side as well
		add_action(
			'enqueue_block_editor_assets',
			function () {
				wp_add_inline_script(
					'wp-blocks',
					"
					wp.hooks.addFilter(
						'blocks.registerBlockType',
						'frontblocks_gallery_attributes',
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
		);
	},
	5
);

/**
 * Register custom attributes for WordPress Gallery block.
 *
 * @param array  $args The block arguments.
 * @param string $name The name of the block.
 * @return array Modified block arguments.
 */
function frbl_register_custom_attributes_for_gallery_block( $args, $name ) {
	if ( 'core/gallery' !== $name ) {
		return $args;
	}

	$args['attributes']['frblGalleryLayout'] = array(
		'type'    => 'string',
		'default' => 'grid',
	);
	$args['attributes']['frblGutterSize'] = array(
		'type'    => 'number',
		'default' => 20,
	);
	$args['attributes']['frblEnableLightbox'] = array(
		'type'    => 'boolean',
		'default' => false,
	);
	
	return $args;
}
