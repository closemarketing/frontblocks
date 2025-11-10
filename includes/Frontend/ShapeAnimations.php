<?php
/**
 * Shape Animations module for FrontBlocks.
 *
 * Adds animation controls to GenerateBlocks Shape block.
 *
 * @package    FrontBlocks
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * ShapeAnimations class.
 *
 * @since 1.0.0
 */
class ShapeAnimations {

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
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_shape_animation_attributes' ), 15 );
		add_filter( 'render_block', array( $this, 'add_animation_classes_to_shape' ), 10, 2 );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'frontblocks-shape-animations',
			FRBL_PLUGIN_URL . 'assets/shape-animations/frontblocks-shape-animations.css',
			array(),
			FRBL_VERSION
		);

		// Enqueue Lottie library from CDN.
		wp_enqueue_script(
			'lottie-player',
			'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js',
			array(),
			'5.12.2',
			true
		);

		wp_enqueue_script(
			'frontblocks-shape-animations',
			FRBL_PLUGIN_URL . 'assets/shape-animations/frontblocks-shape-animations.js',
			array( 'lottie-player' ),
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
			'frontblocks-shape-animations-editor',
			FRBL_PLUGIN_URL . 'assets/shape-animations/frontblocks-shape-animations.css',
			array(),
			FRBL_VERSION
		);

		// Enqueue Lottie library for editor preview.
		wp_enqueue_script(
			'lottie-player-editor',
			'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js',
			array(),
			'5.12.2',
			true
		);

		// Enqueue editor controls script.
		wp_enqueue_script(
			'frontblocks-shape-animation-editor',
			FRBL_PLUGIN_URL . 'assets/shape-animations/frontblocks-shape-animation-option.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-hooks', 'wp-block-editor', 'lottie-player-editor' ),
			FRBL_VERSION,
			true
		);

		wp_set_script_translations(
			'frontblocks-shape-animation-editor',
			'frontblocks'
		);
	}

	/**
	 * Register animation attributes for Shape block.
	 *
	 * @return void
	 */
	public function register_shape_animation_attributes() {
		wp_add_inline_script(
			'wp-blocks',
			"
			wp.hooks.addFilter(
				'blocks.registerBlockType',
				'frontblocks/shape-animation-attributes',
				function( settings, name ) {
					if ( 'generateblocks/shape' !== name ) {
						return settings;
					}
					
					if ( ! settings || typeof settings !== 'object' ) {
						return settings;
					}
					
					if ( ! settings.attributes || typeof settings.attributes !== 'object' ) {
						settings.attributes = {};
					}
					
					try {
						settings.attributes = {
							...settings.attributes,
							frblCustomSvgAnimationEnabled: {
								type: 'boolean',
								default: false
							},
							frblCustomSvgAnimationJson: {
								type: 'string',
								default: ''
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
	 * Add animation classes to shape blocks on frontend render.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block data.
	 * @return string Modified block content.
	 */
	public function add_animation_classes_to_shape( $block_content, $block ) {
		if ( ! isset( $block['blockName'] ) || 'generateblocks/shape' !== $block['blockName'] ) {
			return $block_content;
		}

		if ( empty( $block['attrs'] ) ) {
			return $block_content;
		}

		$attrs = $block['attrs'];

		if ( ! isset( $attrs['frblCustomSvgAnimationEnabled'] ) || ! $attrs['frblCustomSvgAnimationEnabled'] ) {
			return $block_content;
		}

		if ( empty( $attrs['frblCustomSvgAnimationJson'] ) ) {
			return $block_content;
		}

		// Parse JSON data.
		$json_data = json_decode( $attrs['frblCustomSvgAnimationJson'], true );

		if ( ! $json_data || ! is_array( $json_data ) ) {
			return $block_content;
		}

		// Detect animation type: Lottie or CSS.
		$is_lottie = $this->is_lottie_json( $json_data );

		if ( $is_lottie ) {
			return $this->render_lottie_animation( $block_content, $json_data, $attrs );
		} else {
			return $this->render_css_animation( $block_content, $json_data, $attrs );
		}
	}

	/**
	 * Detect if JSON is a Lottie animation.
	 *
	 * @param array $json_data Parsed JSON data.
	 * @return bool True if Lottie format detected.
	 */
	private function is_lottie_json( $json_data ) {
		// Lottie JSON typically has these properties.
		return isset( $json_data['v'] ) && isset( $json_data['fr'] ) && isset( $json_data['layers'] );
	}

	/**
	 * Render Lottie animation.
	 *
	 * @param string $block_content Original block content.
	 * @param array  $json_data Lottie JSON data.
	 * @param array  $attrs Block attributes.
	 * @return string Modified block content.
	 */
	private function render_lottie_animation( $block_content, $json_data, $attrs ) {
		// Generate unique ID for this Lottie instance.
		$unique_id = 'frbl-lottie-' . wp_generate_password( 8, false );

		// Get optional settings.
		$loop     = true; // Default to loop for Lottie.
		$autoplay = true; // Default to autoplay.
		$speed    = 1; // Default speed.

		// Override with animation settings if provided.
		if ( isset( $json_data['animation'] ) ) {
			$loop     = isset( $json_data['animation']['loop'] ) ? (bool) $json_data['animation']['loop'] : true;
			$autoplay = isset( $json_data['animation']['autoplay'] ) ? (bool) $json_data['animation']['autoplay'] : true;
			$speed    = isset( $json_data['animation']['speed'] ) ? (float) $json_data['animation']['speed'] : 1;
		}

		// Get Shape block styles (width, height, colors from GenerateBlocks).
		$styles       = isset( $attrs['styles'] ) ? $attrs['styles'] : array();
		$svg_styles   = isset( $styles['svg'] ) ? $styles['svg'] : array();
		$width        = isset( $svg_styles['width'] ) ? $svg_styles['width'] : '';
		$height       = isset( $svg_styles['height'] ) ? $svg_styles['height'] : '';
		$fill_color   = isset( $svg_styles['fill'] ) ? $svg_styles['fill'] : '';
		$stroke_color = isset( $svg_styles['color'] ) ? $svg_styles['color'] : '';

		// Build inline styles.
		$inline_styles  = 'width: ' . ( ! empty( $width ) ? esc_attr( $width ) : '100%' ) . ';';
		$inline_styles .= 'height: ' . ( ! empty( $height ) ? esc_attr( $height ) : '100%' ) . ';';
		if ( ! empty( $fill_color ) ) {
			$inline_styles .= '--lottie-color: ' . esc_attr( $fill_color ) . ';';
		}

		// Encode Lottie JSON for data attribute.
		$lottie_json_encoded = htmlspecialchars( wp_json_encode( $json_data ), ENT_QUOTES, 'UTF-8' );

		// Create Lottie container.
		$lottie_container  = '<div ';
		$lottie_container .= 'id="' . esc_attr( $unique_id ) . '" ';
		$lottie_container .= 'class="frbl-lottie-animation" ';
		$lottie_container .= 'data-lottie-json="' . $lottie_json_encoded . '" ';
		$lottie_container .= 'data-loop="' . esc_attr( $loop ? 'true' : 'false' ) . '" ';
		$lottie_container .= 'data-autoplay="' . esc_attr( $autoplay ? 'true' : 'false' ) . '" ';
		$lottie_container .= 'data-speed="' . esc_attr( $speed ) . '" ';
		$lottie_container .= 'style="' . $inline_styles . '"';
		$lottie_container .= '></div>';

		// Replace SVG with Lottie container.
		$block_content = preg_replace(
			'/<svg[^>]*>.*?<\/svg>/is',
			$lottie_container,
			$block_content
		);

		// Add Lottie class to wrapper.
		$block_content = preg_replace_callback(
			'/^<([a-z][a-z0-9]*)\s*((?:[^>]|\\n)*?)(?:class="([^"]*?)")?([^>]*?)>/i',
			function ( $matches ) {
				$tag            = $matches[1] ?? 'div';
				$beginning      = $matches[2] ?? '';
				$existing_class = $matches[3] ?? '';
				$ending         = $matches[4] ?? '';

				// Add Lottie wrapper class.
				if ( ! empty( $existing_class ) ) {
					$new_class = $existing_class . ' frbl-has-lottie-animation';
				} else {
					$new_class = 'frbl-has-lottie-animation';
				}

				$result  = '<' . $tag . ' ' . $beginning;
				$result .= ' class="' . $new_class . '"';
				$result .= $ending . '>';

				return $result;
			},
			$block_content,
			1
		);

		return $block_content;
	}

	/**
	 * Render CSS animation (original functionality).
	 *
	 * @param string $block_content Original block content.
	 * @param array  $json_data JSON data with SVG and animation.
	 * @param array  $attrs Block attributes (unused, kept for consistent signature).
	 * @return string Modified block content.
	 */
	private function render_css_animation( $block_content, $json_data, $attrs ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Extract SVG and animation data.
		$custom_svg          = isset( $json_data['svg'] ) ? $json_data['svg'] : '';
		$animation_data      = isset( $json_data['animation'] ) ? $json_data['animation'] : array();
		$animation_name      = isset( $animation_data['name'] ) ? sanitize_key( $animation_data['name'] ) : 'customAnimation';
		$animation_trigger   = isset( $animation_data['trigger'] ) ? sanitize_text_field( $animation_data['trigger'] ) : 'load';
		$animation_keyframes = isset( $animation_data['keyframes'] ) ? $animation_data['keyframes'] : '';
		$animation_duration  = isset( $animation_data['duration'] ) ? sanitize_text_field( $animation_data['duration'] ) : '1s';
		$animation_delay     = isset( $animation_data['delay'] ) ? sanitize_text_field( $animation_data['delay'] ) : '0s';
		$animation_infinite  = isset( $animation_data['infinite'] ) ? (bool) $animation_data['infinite'] : false;

		// Generate unique ID for this block's animation.
		$unique_id = 'frbl-anim-' . md5( $animation_keyframes );

		// Inject custom keyframes and SVG.
		if ( ! empty( $animation_keyframes ) ) {
			// Add inline style with keyframes.
			$inline_style  = '<style>';
			$inline_style .= esc_html( $animation_keyframes );
			$inline_style .= ' .frbl-custom-svg-animation.' . esc_attr( $unique_id ) . ' { ';
			$inline_style .= 'animation-name: ' . esc_attr( $animation_name ) . '; ';
			$inline_style .= 'animation-duration: ' . esc_attr( $animation_duration ) . '; ';
			$inline_style .= 'animation-delay: ' . esc_attr( $animation_delay ) . '; ';
			$inline_style .= 'animation-fill-mode: both; ';
			$inline_style .= 'animation-timing-function: ease-in-out; ';
			if ( $animation_infinite ) {
				$inline_style .= 'animation-iteration-count: infinite; ';
			}
			$inline_style .= '} ';
			if ( 'hover' === $animation_trigger ) {
				$inline_style .= ' .frbl-custom-svg-animation.' . esc_attr( $unique_id ) . ':hover { ';
				$inline_style .= 'animation-play-state: running; ';
				$inline_style .= '} ';
			}
			$inline_style .= '</style>';

			$block_content = $inline_style . $block_content;
		}

		// Replace SVG content if provided.
		if ( ! empty( $custom_svg ) ) {
			// Sanitize SVG.
			$custom_svg = wp_kses( $custom_svg, $this->get_svg_allowed_tags() );

			// Replace the SVG content in the block.
			$block_content = preg_replace(
				'/<svg[^>]*>.*?<\/svg>/is',
				$custom_svg,
				$block_content
			);
		}

		// Add custom animation class.
		$animation_class  = 'frbl-custom-svg-animation ' . $unique_id;
		$animation_class .= ' frbl-shape-trigger-' . esc_attr( $animation_trigger );

		// Add class to the wrapper.
		$block_content = preg_replace_callback(
			'/^<([a-z][a-z0-9]*)\s*((?:[^>]|\\n)*?)(?:class="([^"]*?)")?([^>]*?)>/i',
			function ( $matches ) use ( $animation_class, $animation_trigger, $animation_name ) {
				$tag            = $matches[1] ?? 'div';
				$beginning      = $matches[2] ?? '';
				$existing_class = $matches[3] ?? '';
				$ending         = $matches[4] ?? '';

				// Add classes.
				if ( ! empty( $existing_class ) ) {
					$new_class = $existing_class . ' ' . $animation_class;
				} else {
					$new_class = $animation_class;
				}

				// Add data attributes.
				$data_attrs  = ' data-shape-animation="' . esc_attr( $animation_name ) . '"';
				$data_attrs .= ' data-shape-trigger="' . esc_attr( $animation_trigger ) . '"';

				// Build the opening tag.
				$result  = '<' . $tag . ' ' . $beginning;
				$result .= ' class="' . $new_class . '"';
				$result .= $data_attrs;
				$result .= $ending . '>';

				return $result;
			},
			$block_content,
			1
		);

		return $block_content;
	}

	/**
	 * Get allowed SVG tags for wp_kses.
	 *
	 * @return array Allowed tags and attributes.
	 */
	private function get_svg_allowed_tags() {
		return array(
			'svg'      => array(
				'xmlns'       => array(),
				'viewbox'     => array(),
				'width'       => array(),
				'height'      => array(),
				'fill'        => array(),
				'class'       => array(),
				'aria-hidden' => array(),
				'role'        => array(),
			),
			'path'     => array(
				'd'            => array(),
				'fill'         => array(),
				'stroke'       => array(),
				'stroke-width' => array(),
				'class'        => array(),
			),
			'circle'   => array(
				'cx'           => array(),
				'cy'           => array(),
				'r'            => array(),
				'fill'         => array(),
				'stroke'       => array(),
				'stroke-width' => array(),
				'class'        => array(),
			),
			'rect'     => array(
				'x'            => array(),
				'y'            => array(),
				'width'        => array(),
				'height'       => array(),
				'fill'         => array(),
				'stroke'       => array(),
				'stroke-width' => array(),
				'rx'           => array(),
				'ry'           => array(),
				'class'        => array(),
			),
			'line'     => array(
				'x1'           => array(),
				'y1'           => array(),
				'x2'           => array(),
				'y2'           => array(),
				'stroke'       => array(),
				'stroke-width' => array(),
				'class'        => array(),
			),
			'polygon'  => array(
				'points'       => array(),
				'fill'         => array(),
				'stroke'       => array(),
				'stroke-width' => array(),
				'class'        => array(),
			),
			'polyline' => array(
				'points'       => array(),
				'fill'         => array(),
				'stroke'       => array(),
				'stroke-width' => array(),
				'class'        => array(),
			),
			'ellipse'  => array(
				'cx'           => array(),
				'cy'           => array(),
				'rx'           => array(),
				'ry'           => array(),
				'fill'         => array(),
				'stroke'       => array(),
				'stroke-width' => array(),
				'class'        => array(),
			),
			'g'        => array(
				'fill'      => array(),
				'class'     => array(),
				'transform' => array(),
			),
			'defs'     => array(),
			'clippath' => array(
				'id' => array(),
			),
			'use'      => array(
				'xlink:href' => array(),
				'href'       => array(),
			),
		);
	}
}
