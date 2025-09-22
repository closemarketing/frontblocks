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
		$dist_dir = WP_DEBUG ? 'animations/' : 'dist/';

		// Enqueue custom animations CSS.
		wp_enqueue_style(
			'frontblocks-animations',
			FRBL_PLUGIN_URL . 'assets/' . $dist_dir . 'frontblocks-animations.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-animations-custom',
			FRBL_PLUGIN_URL . 'assets/' . $dist_dir . ( WP_DEBUG ? 'frontblocks-animations.js' : 'frontblocks-animations-min.js' ),
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
		$dist_dir = WP_DEBUG ? 'animations/' : 'dist/';

		// Enqueue custom animations CSS for editor.
		wp_enqueue_style(
			'frontblocks-animations-editor',
			FRBL_PLUGIN_URL . 'assets/' . $dist_dir . 'frontblocks-animations.css',
			array(),
			FRBL_VERSION
		);

		// Enqueue custom block editor script.
		wp_enqueue_script(
			'frontblocks-animation-editor',
			FRBL_PLUGIN_URL . 'assets/dist/frontblocks-animation-option.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
			FRBL_VERSION,
			true
		);

		// Localize script with custom CSS URL.
		wp_localize_script(
			'frontblocks-animation-editor',
			'frontblocksAnimationData',
			array(
				'customCss' => FRBL_PLUGIN_URL . 'assets/' . $dist_dir . 'frontblocks-animations.css',
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

		if ( ! isset( $attrs['frblAnimation'] ) || empty( $attrs['frblAnimation'] ) ) {
			return $block_content;
		}

		$properties                    = array();
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

		// Build style attributes.
		$style_attr = '';
		if ( $delay > 0 ) {
			$style_attr .= '--animate-delay:' . esc_attr( $delay ) . 's;';
		}

		if ( 1 !== $duration ) {
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
}
