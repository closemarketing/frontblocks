<?php
/**
 * Counter module for FrontBlocks (GenerateBlocks Counter enhancements).
 *
 * @package    FrontBlocks
 * @author     Alex castellón <castellon@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Counter class to handle counter block functionality.
 */
class Counter {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_filter( 'render_block', array( $this, 'render_block_counter' ), 10, 2 );
	}

	/**
	 * Register block assets for backend editor.
	 *
	 * @return void
	 */
	public function register_assets() {
		$dependencies = array(
			'wp-blocks',
			'wp-element',
			'wp-editor',
			'wp-components',
			'wp-compose',
			'wp-hooks',
			'wp-data',
		);

		wp_register_script(
			'frontblocks-counter-editor',
			FRBL_PLUGIN_URL . 'assets/counter/frontblocks-counter.js',
			$dependencies,
			FRBL_VERSION,
			true
		);

		wp_register_style(
			'frontblocks-counter-styles',
			FRBL_PLUGIN_URL . 'assets/counter/frontblocks-counter.css',
			array(),
			FRBL_VERSION
		);
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script( 'frontblocks-counter-editor' );

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-counter-editor',
			'frontblocks'
		);

		wp_enqueue_style( 'frontblocks-counter-styles' );
	}

	/**
	 * Render the counter block with necessary attributes and classes.
	 *
	 * @param string $block_content The original block content.
	 * @param array  $block         The block data.
	 * @return string Modified block content with counter functionality.
	 */
	public function render_block_counter( $block_content, $block ) {
		$supported = array( 'generateblocks/text', 'generateblocks/headline', 'core/heading', 'core/paragraph' );
		if ( ! in_array( $block['blockName'], $supported, true ) ) {
			return $block_content;
		}

		$attrs              = $block['attrs'];
		$is_counter_active  = isset( $attrs['isCounterActive'] ) && $attrs['isCounterActive'];
		$animation_duration = isset( $attrs['animationDuration'] ) ? (int) $attrs['animationDuration'] : 2000;
		$start_number       = isset( $attrs['startNumber'] ) ? $attrs['startNumber'] : '0';
		$final_number       = isset( $attrs['finalNumber'] ) ? $attrs['finalNumber'] : '';
		$number_prefix      = isset( $attrs['numberPrefix'] ) ? $attrs['numberPrefix'] : '';
		$number_suffix      = isset( $attrs['numberSuffix'] ) ? $attrs['numberSuffix'] : '';

		if ( $is_counter_active ) {
			$this->enqueue_counter_script();
			$class_to_add      = 'frontblocks-counter-active';
			$target_value_full = '';

			if ( ! empty( $final_number ) ) {
				$target_value_full = $final_number;
			} elseif ( preg_match( '/<[^>]+>([^<]+)<\/[^>]+>/', $block_content, $matches ) ) {
				$target_value_full = trim( $matches[1] );
			} else {
				return $block_content;
			}

			$data_attributes = ' data-counter-target="' . esc_attr( $target_value_full ) . '"' .
				' data-counter-start="' . esc_attr( $start_number ) . '"' .
				' data-counter-duration="' . $animation_duration . '"' .
				' data-counter-prefix="' . esc_attr( $number_prefix ) . '"' .
				' data-counter-suffix="' . esc_attr( $number_suffix ) . '"';

			$block_content = preg_replace(
				'/<([a-z0-9]+)(\s[^>]*?)?>/i',
				'<$1$2' . $data_attributes . '>',
				$block_content,
				1
			);

			$block_content = str_replace(
				'class="',
				'class="' . $class_to_add . ' ',
				$block_content
			);
		}

		return $block_content;
	}

	/**
	 * Enqueue the counter runtime script in the footer.
	 *
	 * @return void
	 */
	public function enqueue_counter_script() {
		$script_handle  = 'frontblocks-counter-runtime';
		$script_path    = FRBL_PLUGIN_URL . 'assets/counter/frontblocks-counter-runtime.js';
		$script_deps    = array();
		$script_version = FRBL_VERSION;

		if ( ! wp_script_is( $script_handle, 'enqueued' ) ) {
			wp_enqueue_script( $script_handle, $script_path, $script_deps, $script_version, true );
		}
	}
}
