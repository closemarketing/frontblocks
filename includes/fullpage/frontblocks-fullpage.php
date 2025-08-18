<?php
/**
 * Class FullPage
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'frbl_fullpage_scripts', 99 );
/**
 * Loads Scripts
 *
 * @return void
 */
function frbl_fullpage_scripts() {
	$dist_dir = WP_DEBUG ? 'fullpage/' : 'dist/';
	
	// Enqueue fullpage.js CSS
	wp_enqueue_style(
		'fullpage-css',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'fullpage.min.css',
		array(),
		'4.0.37'
	);
	
	// Enqueue custom fullpage styles
	wp_enqueue_style(
		'frontblocks-fullpage',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'frontblocks-fullpage.css',
		array( 'fullpage-css' ),
		FRBL_VERSION
	);

	// Enqueue fullpage.js extensions (includes scrolloverflow)
	wp_enqueue_script(
		'frontblocks-fullpage',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'fullpage.extensions.min.js',
		array(),
		'4.0.37',
		true
	);

	// Enqueue custom fullpage code
	wp_enqueue_script(
		'frontblocks-fullpage-custom',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . ( WP_DEBUG ? 'frontblocks-fullpage.js' : 'frontblocks-fullpage-min.js' ),
		array( 'frontblocks-fullpage' ),
		FRBL_VERSION,
		true
	);

	// Localize script with license key
	wp_localize_script(
		'frontblocks-fullpage-custom',
		'frblFullpageData',
		array(
			'licenseKey' => frbl_get_fullpage_license_key(),
		)
	);
}

add_action( 'enqueue_block_editor_assets', 'frbl_enqueue_fullpage_block_editor_assets' );
/**
 * Enqueue custom block editor script
 *
 * @return void
 */
function frbl_enqueue_fullpage_block_editor_assets() {
	wp_enqueue_script(
		'frontblocks-fullpage-option',
		FRBL_PLUGIN_URL . 'includes/dist/frontblocks-fullpage-option.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post' ),
		FRBL_VERSION,
		true
	);
}

add_filter( 'render_block_generateblocks/container', 'frbl_add_fullpage_attributes_to_container_block', 10, 2 );
/**
 * Hook to filter the block output on frontend.
 *
 * @param html  $block_content Block content.
 * @param array $block Block attributes.
 *
 * @return html
 */
function frbl_add_fullpage_attributes_to_container_block( $block_content, $block ) {
	$attrs = $block['attrs'] ?? array();
	$fullpage_enabled = isset( $attrs['frblFullpageEnabled'] ) ? (bool) $attrs['frblFullpageEnabled'] : false;
	$show_navigation = isset( $attrs['frblShowNavigation'] ) ? (bool) $attrs['frblShowNavigation'] : true;
	$show_scrollbar = isset( $attrs['frblShowScrollbar'] ) ? (bool) $attrs['frblShowScrollbar'] : true;
	$navigation_position = isset( $attrs['frblNavigationPosition'] ) ? sanitize_text_field( $attrs['frblNavigationPosition'] ) : 'right';
	$navigation_color = isset( $attrs['frblNavigationColor'] ) ? sanitize_text_field( $attrs['frblNavigationColor'] ) : '#000';
	$auto_scroll = isset( $attrs['frblAutoScroll'] ) ? (bool) $attrs['frblAutoScroll'] : false;
	$scroll_speed = isset( $attrs['frblScrollSpeed'] ) ? (int) $attrs['frblScrollSpeed'] : 700;
	$loop_bottom = isset( $attrs['frblLoopBottom'] ) ? (bool) $attrs['frblLoopBottom'] : false;
	$loop_top = isset( $attrs['frblLoopTop'] ) ? (bool) $attrs['frblLoopTop'] : false;
	$scrolloverflow = isset( $attrs['frblScrolloverflow'] ) ? (bool) $attrs['frblScrolloverflow'] : false;

	// Add fullpage attributes if enabled
	if ( $fullpage_enabled ) {
		$attributes = '';
		$attributes .= ' data-fullpage="true"';
		$attributes .= ' data-navigation="' . esc_attr( $show_navigation ? 'true' : 'false' ) . '"';
		$attributes .= ' data-scrollbar="' . esc_attr( $show_scrollbar ? 'true' : 'false' ) . '"';
		$attributes .= ' data-navigation-position="' . esc_attr( $navigation_position ) . '"';
		$attributes .= ' data-navigation-color="' . esc_attr( $navigation_color ) . '"';
		$attributes .= ' data-auto-scroll="' . esc_attr( $auto_scroll ? 'true' : 'false' ) . '"';
		$attributes .= ' data-scroll-speed="' . esc_attr( $scroll_speed ) . '"';
		$attributes .= ' data-loop-bottom="' . esc_attr( $loop_bottom ? 'true' : 'false' ) . '"';
		$attributes .= ' data-loop-top="' . esc_attr( $loop_top ? 'true' : 'false' ) . '"';
		$attributes .= ' data-scrolloverflow="' . esc_attr( $scrolloverflow ? 'true' : 'false' ) . '"';

		// Add data attributes and the 'frontblocks-fullpage' class to the first div in the block content
		$block_content = preg_replace(
			'/<div([^>]*)class="([^"]*gb-container[^"]*)"([^>]*)>/',
			'<div$1class="$2 frontblocks-fullpage"$3' . $attributes . '>',
			$block_content,
			1 // Only replace the first occurrence
		);
	}

	return $block_content;
}

// Register attributes before GenerateBlocks registers its blocks
add_action(
	'init',
	function () {
		// Use priority 9 (before the default 10)
		add_filter(
			'generateblocks_blocks_registered_block',
			'frbl_register_fullpage_attributes_for_container_block',
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
						'frontblocks/container-fullpage-attributes',
						function( settings, name ) {
							if ( name !== 'generateblocks/container' ) {
								return settings;
							}

							settings.attributes = {
								...settings.attributes,
								frblFullpageEnabled: {
									type: 'boolean',
									default: false
								},
								frblShowNavigation: {
									type: 'boolean',
									default: true
								},
								frblShowArrows: {
									type: 'boolean',
									default: true
								},
								frblShowScrollbar: {
									type: 'boolean',
									default: true
								},
								frblNavigationPosition: {
									type: 'string',
									default: 'right'
								},
								frblNavigationColor: {
									type: 'string',
									default: '#000'
								},
								frblArrowColor: {
									type: 'string',
									default: '#000'
								},
								frblScrollbarColor: {
									type: 'string',
									default: '#000'
								},
								frblAutoScroll: {
									type: 'boolean',
									default: false
								},
								frblScrollSpeed: {
									type: 'number',
									default: 700
								},
								frblLoopBottom: {
									type: 'boolean',
									default: false
								},
								frblLoopTop: {
									type: 'boolean',
									default: false
								},
								frblScrolloverflow: {
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
 * Register custom attributes for GenerateBlocks Container block.
 *
 * @param array  $block_args The block arguments.
 * @param string $block_type The name of the block.
 * @return array Modified block arguments.
 * @author Closetechnology
 */
function frbl_register_fullpage_attributes_for_container_block( $block_args, $block_type ) {
	if ( 'generateblocks/container' !== $block_type ) {
		return $block_args;
	}

	$block_args['attributes']['frblFullpageEnabled'] = array(
		'type'    => 'boolean',
		'default' => false,
	);
	$block_args['attributes']['frblShowNavigation'] = array(
		'type'    => 'boolean',
		'default' => true,
	);

	$block_args['attributes']['frblShowScrollbar'] = array(
		'type'    => 'boolean',
		'default' => true,
	);
	$block_args['attributes']['frblNavigationPosition'] = array(
		'type'    => 'string',
		'default' => 'right',
	);
	$block_args['attributes']['frblNavigationColor'] = array(
		'type'    => 'string',
		'default' => '#000',
	);

	$block_args['attributes']['frblAutoScroll'] = array(
		'type'    => 'boolean',
		'default' => false,
	);
	$block_args['attributes']['frblScrollSpeed'] = array(
		'type'    => 'number',
		'default' => 700,
	);
	$block_args['attributes']['frblLoopBottom'] = array(
		'type'    => 'boolean',
		'default' => false,
	);
	$block_args['attributes']['frblLoopTop'] = array(
		'type'    => 'boolean',
		'default' => false,
	);
	$block_args['attributes']['frblScrolloverflow'] = array(
		'type'    => 'boolean',
		'default' => false,
	);

	return $block_args;
}
