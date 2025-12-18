<?php
/**
 * Headline module for FrontBlocks (GenerateBlocks Headline enhancements).
 *
 * @package    FrontBlocks
 * @author     Alex castellón <castellon@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Headline class.
 *
 * @since 1.1.0
 */
class Headline {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ), 100 );
		add_filter( 'generateblocks_attr_headline', array( $this, 'add_line_class_attribute' ), 10 );
		add_filter( 'generateblocks_attr_text', array( $this, 'add_marquee_speed_attribute' ), 10, 2 );
	}

	/**
	 * Register block assets for backend editor.
	 *
	 * @return void
	 */
	public function register_assets() {
		wp_register_script(
			'frontblocks-headline-editor',
			FRBL_PLUGIN_URL . 'assets/headline/frontblocks-headline.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-editor',
				'wp-components',
				'wp-compose',
				'wp-hooks',
				'wp-data',
			),
			FRBL_VERSION,
			true
		);

		wp_register_style(
			'frontblocks-headline-styles',
			FRBL_PLUGIN_URL . 'assets/headline/frontblocks-headline.css',
			array(),
			FRBL_VERSION
		);

		wp_register_script(
			'frontblocks-headline-marquee',
			FRBL_PLUGIN_URL . 'assets/headline/frontblocks-headline-marquee.js',
			array(),
			FRBL_VERSION,
			true
		);
	}
	/**
	 * Editor assets
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script( 'frontblocks-headline-editor' );

		// Set script translations for JavaScript.
		wp_set_script_translations(
			'frontblocks-headline-editor',
			'frontblocks'
		);

		wp_enqueue_style( 'frontblocks-headline-styles' );
	}

	/**
	 * Enqueue frontend styles and scripts.
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles() {
		wp_enqueue_style( 'frontblocks-headline-styles' );
		wp_enqueue_script( 'frontblocks-headline-marquee' );
	}

	/**
	 * Add line class attribute.
	 *
	 * @param array $attributes Attributes values for the block.
	 * Add line class attribute to headline block.
	 *
	 * @return array
	 */
	public function add_line_class_attribute( $attributes ) {
		return $attributes;
	}

	/**
	 * Add marquee speed data attribute to text block.
	 *
	 * @param array $attributes HTML attributes.
	 * @param array $block_attributes Block attributes.
	 * @return array
	 */
	public function add_marquee_speed_attribute( $attributes, $block_attributes ) {
		// Speed presets mapping.
		$speed_presets = array(
			'fast'   => 10,
			'medium' => 20,
			'slow'   => 40,
		);

		$speed_value = 20; // Default.

		// Check if speed is set in block attributes (can be preset string or number).
		if ( isset( $block_attributes['frblMarqueeSpeed'] ) && ! empty( $block_attributes['frblMarqueeSpeed'] ) ) {
			$speed_preset = $block_attributes['frblMarqueeSpeed'];
			// Check if it's a preset string.
			if ( isset( $speed_presets[ $speed_preset ] ) ) {
				$speed_value = $speed_presets[ $speed_preset ];
			} else {
				// Try to parse as number.
				$speed_value = absint( $speed_preset );
				if ( $speed_value <= 0 ) {
					$speed_value = 20;
				}
			}
		}

		// Also check if it's already in htmlAttributes (from editor) - this takes precedence.
		if ( isset( $block_attributes['htmlAttributes'] ) && is_array( $block_attributes['htmlAttributes'] ) ) {
			if ( isset( $block_attributes['htmlAttributes']['data-marquee-speed'] ) && ! empty( $block_attributes['htmlAttributes']['data-marquee-speed'] ) ) {
				$html_speed = $block_attributes['htmlAttributes']['data-marquee-speed'];
				// If it's a number, use it directly.
				if ( is_numeric( $html_speed ) ) {
					$speed_value = absint( $html_speed );
				} elseif ( isset( $speed_presets[ $html_speed ] ) ) {
					$speed_value = $speed_presets[ $html_speed ];
				}
			}
		}

		$attributes['data-marquee-speed'] = $speed_value;

		return $attributes;
	}
}
