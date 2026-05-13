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
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_filter( 'render_block_frontblocks/text-animation', array( $this, 'maybe_enqueue_frontend_assets' ), 10, 2 );
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
			'frontblocks-animation-slide-up',
			FRBL_PLUGIN_URL . 'assets/text-animation/animations/slide-up.js',
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

		$script_map = array(
			'fade-in'      => 'frontblocks-animation-fade-in',
			'typewriter'   => 'frontblocks-animation-typewriter',
			'shuffle-text' => 'frontblocks-animation-shuffle-text',
			'slide-up'     => 'frontblocks-animation-slide-up',
		);

		if ( ! wp_script_is( 'frontblocks-text-animation-frontend', 'enqueued' ) ) {
			wp_enqueue_script( 'frontblocks-text-animation-frontend' );
		}

		if ( isset( $script_map[ $animation ] ) && ! wp_script_is( $script_map[ $animation ], 'enqueued' ) ) {
			wp_enqueue_script( $script_map[ $animation ] );
		}

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
