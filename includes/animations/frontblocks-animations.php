<?php
/**
 * Class Animations
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'frbl_theme_scripts_animations', 100 );
/**
 * Loads Scripts
 *
 * @return void
 */
function frbl_theme_scripts_animations() {
	$dist_dir = WP_DEBUG ? 'animations/' : 'dist/';

	// Then enqueue our custom animations CSS
	wp_enqueue_style(
		'frontblocks-animations',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'frontblocks-animations.css',
		array(),
		FRBL_VERSION
	);

	wp_enqueue_script(
		'frontblocks-animations-custom',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'frontblocks-animations.js',
		array(),
		FRBL_VERSION,
		true
	);
}

add_action( 'enqueue_block_editor_assets', 'frbl_editor_scripts_animations', 5 );
/**
 * Enqueue Animation Scripts and Styles for Editor
 */
function frbl_editor_scripts_animations() {
	$dist_dir = WP_DEBUG ? 'animations/' : 'dist/';

	// Then enqueue our custom animations CSS
	wp_enqueue_style(
		'frontblocks-animations-editor',
		FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'frontblocks-animations.css',
		array(),
		FRBL_VERSION
	);

	// Enqueue our custom block editor script.
	wp_enqueue_script(
		'frontblocks-animation-editor',
		FRBL_PLUGIN_URL . 'includes/dist/frontblocks-animation-option.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
		FRBL_VERSION,
		true
	);
    // Localize script with custom CSS URL
    wp_localize_script(
        'frontblocks-animation-editor',
        'frontblocksAnimationData',
        array(
            'customCss' => FRBL_PLUGIN_URL . 'includes/' . $dist_dir . 'frontblocks-animations.css',
        )
    );
}

add_action( 'enqueue_block_editor_assets', 'frbl_register_animation_attributes', 9 );
/**
 * Register animation attributes for all blocks
 */
function frbl_register_animation_attributes() {
	// This script runs on the client side to add animation attributes to all blocks.
	wp_add_inline_script(
		'wp-blocks',
		"
		wp.hooks.addFilter(
			'blocks.registerBlockType',
			'frontblocks/animation-attributes',
			function( settings, name ) {
				// Add animation attributes to all blocks
				settings.attributes = Object.assign( settings.attributes || {}, {
					frblAnimation: {
						type: 'string',
						default: ''
					},
					frblAnimationDelay: {
						type: 'number',
						default: 0
					},
					frblAnimationDuration: {
						type: 'number',
						default: 1
					},
					frblAnimationRepeat: {
						type: 'boolean',
						default: false
					},
					frblAnimationInfinite: {
						type: 'boolean',
						default: false
					}
				});

				return settings;
			}
		);
		"
	);
}


add_filter( 'render_block', 'frbl_add_animation_classes_to_blocks', 10, 2 );
/**
 * Add animation classes to blocks on frontend render
 *
 * @param string $block_content Block content.
 * @param array  $block Block data.
 * @return string Modified block content.
 */
function frbl_add_animation_classes_to_blocks( $block_content, $block ) {
	if ( empty( $block['attrs'] ) ) {
		return $block_content;
	}

	$attrs = $block['attrs'];

	if ( ! isset( $attrs['frblAnimation'] ) || empty( $attrs['frblAnimation'] ) ) {
		return $block_content;
	}

    $properties = array();
    $animation       = $properties['animation'] = $attrs['frblAnimation'];
	$delay           = $properties['delay'] = isset( $attrs['frblAnimationDelay'] ) ? $attrs['frblAnimationDelay'] : 0;
	$duration        = $properties['duration'] = isset( $attrs['frblAnimationDuration'] ) ? $attrs['frblAnimationDuration'] : 1;
	$repeat          = $properties['repeat'] = isset( $attrs['frblAnimationRepeat'] ) ? $attrs['frblAnimationRepeat'] : false;
	$infinite_repeat = $properties['infinite_repeat'] = isset( $attrs['frblAnimationInfinite'] ) ? $attrs['frblAnimationInfinite'] : false;

	// Build style attributes.
	$style_attr = '';
	if ( $delay > 0 ) {
		$style_attr .= '--animate-delay:' . esc_attr( $delay ) . 's;';
	}

	if ( $duration !== 1 ) {
		$style_attr .= '--animate-duration:' . esc_attr( $duration ) . 's;';
	}

	if ( $infinite_repeat ) {
		$style_attr .= '--animate-repeat:infinite;';
	} elseif ( $repeat ) {
		$style_attr .= '--animate-repeat:2;';
	}

	// Add animation classes and styles to the first HTML tag.
	$block_content = preg_replace_callback(
		'/^<([a-z][a-z0-9]*)\s*((?:[^>]|\\n)*?)(?:style="([^"]*?)")?([^>]*?)>/i',
		function ( $matches ) use ( $properties, $style_attr ) {
			$tag            = $matches[1] ?? 'div';
			$beginning      = $matches[2] ?? '';
			$existing_style = $matches[3] ?? '';
			$ending         = $matches[4] ?? '';
            
			$classes = 'animate__animated animate__' . esc_attr( $properties['animation'] );

			// Add classes to existing class attribute or create new one.
			if ( strpos( $beginning . $ending, 'class="' ) !== false ) {
				$beginning = preg_replace(
					'/class="([^"]*)"/',
					'class="$1 ' . $classes . '"',
					$beginning . $ending,
					1
				);
			} else {
				$beginning .= ' class="' . $classes . '"';
			}
            $beginning .= ' data-frontblocks-animation="' . esc_attr( $properties['animation'] ) . '"';
            $beginning .= ' data-frontblocks-animation-delay="' . esc_attr( $properties['delay'] ) . '"';
            $beginning .= ' data-frontblocks-animation-duration="' . esc_attr( $properties['duration'] ) . '"';
            $beginning .= ' data-frontblocks-animation-repeat="' . esc_attr( $properties['repeat'] ) . '"';
            $beginning .= ' data-frontblocks-animation-infinite="' . esc_attr( $properties['infinite_repeat'] ) . '"';
			// Add styles if needed.
			if ( ! empty( $style_attr ) ) {
				$combined_style = $existing_style . ( ! empty( $existing_style ) ? ';' : '' ) . $style_attr;
				return '<' . $tag . ' ' . $beginning . ' style="' . $combined_style . '">';
			}

			return '<' . $tag . ' ' . $beginning . '>';
		},
		$block_content,
		1
	);

	return $block_content;
}

