<?php
/**
 * Animations module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Animations class.
 *
 * @since 1.0.0
 */
class Animations {

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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ), 5 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_animation_attributes' ), 15 );
		add_filter( 'render_block', array( $this, 'add_animation_classes_to_blocks' ), 10, 2 );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Enqueue custom animations CSS.
		wp_enqueue_style(
			'frontblocks-animations',
			FRBL_PLUGIN_URL . 'assets/animations/frontblocks-animations.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-animations-custom',
			FRBL_PLUGIN_URL . 'assets/animations/frontblocks-animations.js',
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
		// Enqueue custom animations CSS for editor.
		wp_enqueue_style(
			'frontblocks-animations-editor',
			FRBL_PLUGIN_URL . 'assets/animations/frontblocks-animations.css',
			array(),
			FRBL_VERSION
		);

		// Enqueue custom block editor script.
		wp_enqueue_script(
			'frontblocks-animation-editor',
			FRBL_PLUGIN_URL . 'assets/animations/frontblocks-animation-option.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
			FRBL_VERSION,
			true
		);

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-animation-editor',
			'frontblocks'
		);

		// Localize script with custom CSS URL.
		wp_localize_script(
			'frontblocks-animation-editor',
			'frontblocksAnimationData',
			array(
				'customCss' => FRBL_PLUGIN_URL . 'assets/animations/frontblocks-animations.css',
			)
		);
	}

	/**
	 * Register animation attributes for all blocks.
	 *
	 * @return void
	 */
	public function register_animation_attributes() {
		// This script runs on the client side to add animation attributes to all blocks.
		wp_add_inline_script(
			'wp-blocks',
			"
			wp.hooks.addFilter(
				'blocks.registerBlockType',
				'frontblocks/animation-attributes',
				function( settings, name ) {
					// Exclude Gravity Forms blocks from animation attributes.
					if ( name && name.indexOf('gravityforms/') === 0 ) {
						return settings;
					}
					
					// Defensive check for settings object.
					if ( ! settings || typeof settings !== 'object' ) {
						return settings;
					}
					
					// Ensure attributes property exists and is an object.
					if ( ! settings.attributes || typeof settings.attributes !== 'object' || Array.isArray( settings.attributes ) ) {
						settings.attributes = {};
					}
					
					// Use spread operator for safer attribute merging.
					try {
						settings.attributes = {
							...settings.attributes,
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
							},
							frblDisableAnimationMobile: {
								type: 'boolean',
								default: false
							},
							frblGlassEffect: {
								type: 'boolean',
								default: false
							},
							frblGlassBlur: {
								type: 'number',
								default: 10
							}
						};
					} catch( error ) {
						return settings;
					}

					return settings;
				}
			);
			"
		);
	}

	/**
	 * Add animation classes to blocks on frontend render.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block data.
	 * @return string Modified block content.
	 */
	public function add_animation_classes_to_blocks( $block_content, $block ) {
		if ( empty( $block['attrs'] ) ) {
			return $block_content;
		}

		$attrs = $block['attrs'];

		// Check if either animation or glass effect is set.
		$has_animation    = isset( $attrs['frblAnimation'] ) && ! empty( $attrs['frblAnimation'] );
		$has_glass_effect = isset( $attrs['frblGlassEffect'] ) && $attrs['frblGlassEffect'];

		if ( ! $has_animation && ! $has_glass_effect ) {
			return $block_content;
		}

		$properties = array();

		// Animation properties.
		if ( $has_animation ) {
			$animation                     = $attrs['frblAnimation'];
			$properties['animation']       = $animation;
			$delay                         = isset( $attrs['frblAnimationDelay'] ) ? $attrs['frblAnimationDelay'] : 0;
			$properties['delay']           = $delay;
			$duration                      = isset( $attrs['frblAnimationDuration'] ) ? $attrs['frblAnimationDuration'] : 1;
			$properties['duration']        = $duration;
			$repeat                        = isset( $attrs['frblAnimationRepeat'] ) ? $attrs['frblAnimationRepeat'] : false;
			$properties['repeat']          = $repeat;
			$infinite_repeat               = isset( $attrs['frblAnimationInfinite'] ) ? $attrs['frblAnimationInfinite'] : false;
			$properties['infinite_repeat'] = $infinite_repeat;
			$disable_mobile                = isset( $attrs['frblDisableAnimationMobile'] ) ? $attrs['frblDisableAnimationMobile'] : false;
			$properties['disable_mobile']  = $disable_mobile;
		}

		// Glass effect properties.
		if ( $has_glass_effect ) {
			$properties['glass_effect'] = true;
			$properties['glass_blur']   = isset( $attrs['frblGlassBlur'] ) ? $attrs['frblGlassBlur'] : 10;
		}

		// Build style attributes.
		$style_attr = '';

		// Animation styles.
		if ( $has_animation ) {
			if ( $properties['delay'] > 0 ) {
				$style_attr .= '--animate-delay:' . esc_attr( $properties['delay'] ) . 's;';
			}

			if ( 1 !== $properties['duration'] ) {
				$style_attr .= '--animate-duration:' . esc_attr( $properties['duration'] ) . 's;';
			}

			if ( $properties['infinite_repeat'] ) {
				$style_attr .= '--animate-repeat:infinite;';
			} elseif ( $properties['repeat'] ) {
				$style_attr .= '--animate-repeat:2;';
			}
		}

		// Glass effect styles.
		if ( $has_glass_effect ) {
			$blur_value  = $properties['glass_blur'];
			$style_attr .= 'backdrop-filter:blur(' . esc_attr( $blur_value ) . 'px);';
			$style_attr .= '-webkit-backdrop-filter:blur(' . esc_attr( $blur_value ) . 'px);';
		}

		// Add animation classes and styles to the first HTML tag.
		$block_content = preg_replace_callback(
			'/^<([a-z][a-z0-9]*)\s*((?:[^>]|\\n)*?)(?:style="([^"]*?)")?([^>]*?)>/i',
			function ( $matches ) use ( $properties, $style_attr, $has_animation, $has_glass_effect ) {
				$tag            = $matches[1] ?? 'div';
				$beginning      = $matches[2] ?? '';
				$existing_style = $matches[3] ?? '';
				$ending         = $matches[4] ?? '';

				$classes = '';

				// Add animation classes.
				if ( $has_animation ) {
					$classes = 'animate__animated animate__' . esc_attr( $properties['animation'] );

					if ( isset( $properties['disable_mobile'] ) && $properties['disable_mobile'] ) {
						$classes .= ' frbl-no-mobile-animation';
					}
				}

				// Add glass effect class.
				if ( $has_glass_effect ) {
					$classes .= ( ! empty( $classes ) ? ' ' : '' ) . 'frbl-glass-effect';
				}

				// Add classes to existing class attribute or create new one.
				if ( ! empty( $classes ) ) {
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
				}

				// Add animation data attributes.
				if ( $has_animation ) {
					$beginning .= ' data-frontblocks-animation="' . esc_attr( $properties['animation'] ) . '"';
					$beginning .= ' data-frontblocks-animation-delay="' . esc_attr( $properties['delay'] ) . '"';
					$beginning .= ' data-frontblocks-animation-duration="' . esc_attr( $properties['duration'] ) . '"';
					$beginning .= ' data-frontblocks-animation-repeat="' . esc_attr( $properties['repeat'] ) . '"';
					$beginning .= ' data-frontblocks-animation-infinite="' . esc_attr( $properties['infinite_repeat'] ) . '"';
				}

				// Add glass effect data attributes.
				if ( $has_glass_effect ) {
					$beginning .= ' data-frontblocks-glass-blur="' . esc_attr( $properties['glass_blur'] ) . '"';
				}

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
}
