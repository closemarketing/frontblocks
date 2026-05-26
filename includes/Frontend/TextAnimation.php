<?php
/**
 * Text Animation block for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Alex castellón <castellon@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * TextAnimation class.
 *
 * @since 1.3.0
 */
class TextAnimation {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_text_animation_attributes' ), 5 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_filter( 'render_block_frontblocks/text-animation', array( $this, 'maybe_enqueue_frontend_assets' ), 10, 2 );
		add_filter( 'register_block_type_args', array( $this, 'register_native_block_attributes' ), 10, 2 );
		add_filter( 'render_block_core/paragraph', array( $this, 'apply_text_animation_to_native_block' ), 10, 2 );
		add_filter( 'render_block_core/heading', array( $this, 'apply_text_animation_to_native_block' ), 10, 2 );
	}

	/**
	 * Register the block type and its frontend style.
	 *
	 * @return void
	 */
	public function register_block() {
		wp_register_script(
			'frontblocks-text-animation-editor',
			FRBL_PLUGIN_URL . 'assets/text-animation/frontblocks-text-animation.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-editor',
				'wp-block-editor',
				'wp-components',
				'wp-i18n',
				'wp-data',
				'wp-dom-ready',
			),
			FRBL_VERSION,
			true
		);

		wp_register_style(
			'frontblocks-text-animation',
			FRBL_PLUGIN_URL . 'assets/text-animation/frontblocks-text-animation.css',
			array(),
			FRBL_VERSION
		);

		wp_register_script(
			'frontblocks-text-animation-frontend',
			FRBL_PLUGIN_URL . 'assets/text-animation/frontblocks-text-animation-frontend.js',
			array(),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-fade-in',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/fade-in.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-typewriter',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/typewriter.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-rotate-in',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/rotate-in.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-flip-in',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/flip-in.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-bounce-in',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/bounce-in.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-glow-in',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/glow-in.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-blur-in',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/blur-in.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-scale-in',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/scale-in.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-slide-up',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/slide-up.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-slide-down',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/slide-down.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-slide-left',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/slide-left.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-slide-right',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/slide-right.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-drop-in',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/drop-in.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-swing',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/swing.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-pulse',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/pulse.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-flash',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/flash.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-rubber-band',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/rubber-band.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-wave',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/wave.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-stretch',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/stretch.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-squeeze',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/squeeze.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-roll-in',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/roll-in.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-glitch',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/glitch.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-random-reveal',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/random-reveal.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-flicker',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/flicker.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-block-reveal',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/block-reveal.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-tracking-expand',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/tracking-expand.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-terminal-type',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/terminal-type.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-solid-outline',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/solid-outline.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-glitch-rgb',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/glitch-rgb.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-water-drop',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/water-drop.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-pulse-neon',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/pulse-neon.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-shadow-pop',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/shadow-pop.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		wp_register_script(
			'frontblocks-animation-shuffle-text',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/shuffle-text.js',
			array( 'frontblocks-text-animation-frontend' ),
			FRBL_VERSION,
			true
		);

		register_block_type(
			'frontblocks/text-animation',
			array(
				'editor_script' => 'frontblocks-text-animation-editor',
				'editor_style'  => 'frontblocks-text-animation',
				'style'         => 'frontblocks-text-animation',
			)
		);
	}

	/**
	 * Inject frblTextAnimation attribute into core blocks via inline script on wp-blocks.
	 *
	 * Runs at priority 5 (before wp-block-library registers core blocks) so the
	 * blocks.registerBlockType filter is in place when core/paragraph and core/heading
	 * are registered. This ensures the serializer includes the attribute in the block comment.
	 *
	 * @return void
	 */
	public function register_text_animation_attributes() {
		wp_add_inline_script(
			'wp-blocks',
			"
			wp.hooks.addFilter(
				'blocks.registerBlockType',
				'frontblocks/text-animation-native-attrs',
				function( settings, name ) {
					if ( 'core/paragraph' !== name && 'core/heading' !== name ) {
						return settings;
					}
					if ( ! settings || typeof settings !== 'object' ) {
						return settings;
					}
					if ( ! settings.attributes || typeof settings.attributes !== 'object' ) {
						settings.attributes = {};
					}

					settings.attributes = Object.assign( {}, settings.attributes, {
						frblTextAnimation:     { type: 'string',  default: 'none'  },
						frblTextAnimationLoop: { type: 'boolean', default: false   }
					} );

					var originalSave = settings.save;
					settings.save = function( props ) {
						var animation = props.attributes.frblTextAnimation;
						if ( ! animation || 'none' === animation ) {
							return originalSave( props );
						}
						var element = originalSave( props );
						if ( ! element ) return element;
						try {
							var existingClass = ( element.props && element.props.className ) || '';
							var extraProps = {
								className:        existingClass ? existingClass + ' frbl-text-animation' : 'frbl-text-animation',
								'data-animation': animation
							};
							if ( props.attributes.frblTextAnimationLoop ) {
								extraProps['data-animation-loop'] = '1';
							}
							return wp.element.cloneElement( element, extraProps );
						} catch ( e ) {
							return element;
						}
					};

					return settings;
				}
			);
			"
		);
	}

	/**
	 * Register frblTextAnimation attribute server-side for paragraph and heading.
	 *
	 * @param array  $args       Block type args.
	 * @param string $block_type Block type name.
	 * @return array
	 */
	public function register_native_block_attributes( $args, $block_type ) {
		if ( 'core/paragraph' !== $block_type && 'core/heading' !== $block_type ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		$args['attributes']['frblTextAnimation'] = array(
			'type'    => 'string',
			'default' => 'none',
		);

		$args['attributes']['frblTextAnimationLoop'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		return $args;
	}

	/**
	 * Add frbl-text-animation class and data-animation attr to paragraph/heading.
	 *
	 * @param string $block_content Block HTML.
	 * @param array  $block         Block data.
	 * @return string
	 */
	public function apply_text_animation_to_native_block( $block_content, $block ) {
		// Primary path: save() already wrote the class+data-animation into the HTML.
		if ( false !== strpos( $block_content, 'frbl-text-animation' ) ) {
			if ( preg_match( '/data-animation="([^"]+)"/', $block_content, $m ) ) {
				$this->enqueue_animation_scripts( sanitize_text_field( $m[1] ) );
			}
			return $block_content;
		}

		// Fallback: attribute stored in block comment (older saves or edge cases).
		$animation = isset( $block['attrs']['frblTextAnimation'] ) ? sanitize_text_field( $block['attrs']['frblTextAnimation'] ) : 'none';
		if ( 'none' === $animation || '' === $animation ) {
			return $block_content;
		}

		$block_content = preg_replace_callback(
			'/^<([a-z][a-z0-9]*)\s*((?:[^>]|\n)*?)(?:style="([^"]*?)")?([^>]*?)>/i',
			function ( $matches ) use ( $animation ) {
				$tag    = $matches[1] ?? 'div';
				$before = $matches[2] ?? '';
				$style  = $matches[3] ?? '';
				$after  = $matches[4] ?? '';

				if ( strpos( $before . $after, 'class="' ) !== false ) {
					$before = preg_replace(
						'/class="([^"]*)"/',
						'class="$1 frbl-text-animation"',
						$before . $after,
						1
					);
					$after  = '';
				} else {
					$before .= ' class="frbl-text-animation"';
				}

				$before .= ' data-animation="' . esc_attr( $animation ) . '"';

				if ( ! empty( $style ) ) {
					return '<' . $tag . ' ' . $before . ' style="' . $style . '">';
				}

				return '<' . $tag . ' ' . $before . '>';
			},
			$block_content,
			1
		);

		$this->enqueue_animation_scripts( $animation );
		return $block_content;
	}

	/**
	 * Enqueue the frontend scripts for a given animation type.
	 *
	 * @param string $animation Animation type slug.
	 * @return void
	 */
	private function enqueue_animation_scripts( $animation ) {
		$script_map = array(
			'fade-in'         => 'frontblocks-animation-fade-in',
			'blur-in'         => 'frontblocks-animation-blur-in',
			'glow-in'         => 'frontblocks-animation-glow-in',
			'bounce-in'       => 'frontblocks-animation-bounce-in',
			'flip-in'         => 'frontblocks-animation-flip-in',
			'rotate-in'       => 'frontblocks-animation-rotate-in',
			'typewriter'      => 'frontblocks-animation-typewriter',
			'shuffle-text'    => 'frontblocks-animation-shuffle-text',
			'slide-up'        => 'frontblocks-animation-slide-up',
			'slide-down'      => 'frontblocks-animation-slide-down',
			'slide-left'      => 'frontblocks-animation-slide-left',
			'slide-right'     => 'frontblocks-animation-slide-right',
			'drop-in'         => 'frontblocks-animation-drop-in',
			'swing'           => 'frontblocks-animation-swing',
			'pulse'           => 'frontblocks-animation-pulse',
			'flash'           => 'frontblocks-animation-flash',
			'rubber-band'     => 'frontblocks-animation-rubber-band',
			'wave'            => 'frontblocks-animation-wave',
			'stretch'         => 'frontblocks-animation-stretch',
			'squeeze'         => 'frontblocks-animation-squeeze',
			'roll-in'         => 'frontblocks-animation-roll-in',
			'glitch'          => 'frontblocks-animation-glitch',
			'random-reveal'   => 'frontblocks-animation-random-reveal',
			'flicker'         => 'frontblocks-animation-flicker',
			'block-reveal'    => 'frontblocks-animation-block-reveal',
			'tracking-expand' => 'frontblocks-animation-tracking-expand',
			'terminal-type'   => 'frontblocks-animation-terminal-type',
			'solid-outline'   => 'frontblocks-animation-solid-outline',
			'glitch-rgb'      => 'frontblocks-animation-glitch-rgb',
			'water-drop'      => 'frontblocks-animation-water-drop',
			'pulse-neon'      => 'frontblocks-animation-pulse-neon',
			'shadow-pop'      => 'frontblocks-animation-shadow-pop',
			'scale-in'        => 'frontblocks-animation-scale-in',
		);

		if ( ! wp_style_is( 'frontblocks-text-animation', 'enqueued' ) ) {
			wp_enqueue_style( 'frontblocks-text-animation' );
		}

		if ( ! wp_script_is( 'frontblocks-text-animation-frontend', 'enqueued' ) ) {
			wp_enqueue_script( 'frontblocks-text-animation-frontend' );
		}

		if ( isset( $script_map[ $animation ] ) && ! wp_script_is( $script_map[ $animation ], 'enqueued' ) ) {
			wp_enqueue_script( $script_map[ $animation ] );
		}
	}

	/**
	 * Enqueue frontend JS only when block with animation is on the page.
	 *
	 * @param string $block_content Block HTML.
	 * @param array  $block         Block data.
	 * @return string
	 */
	public function maybe_enqueue_frontend_assets( $block_content, $block ) {
		$animation = $block['attrs']['animationType'] ?? 'none';

		if ( 'none' === $animation ) {
			return $block_content;
		}

		$this->enqueue_animation_scripts( $animation );

		return $block_content;
	}

	/**
	 * Enqueue editor-only assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script( 'frontblocks-text-animation-editor' );

		wp_set_script_translations(
			'frontblocks-text-animation-editor',
			'frontblocks'
		);
	}
}
