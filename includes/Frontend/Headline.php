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
	}

	/**
	 * Register assets.
	 *
	 * @return void
	 */
	public function register_assets() {
		$asset_file = array(
			'dependencies' => array(
				'wp-blocks',
				'wp-element',
				'wp-editor',
				'wp-components',
				'wp-compose',
				'wp-hooks',
				'wp-data',
			),
			'version'      => FRBL_VERSION,
		);

		wp_register_script(
			'frontblocks-headline-editor',
			FRBL_PLUGIN_URL . 'assets/headline/frontblocks-headline.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_register_style(
			'frontblocks-headline-styles',
			FRBL_PLUGIN_URL . 'assets/headline/frontblocks-headline.css',
			array(),
			FRBL_VERSION
		);
	}

	/**
	 * Editor assets
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_script( 'frontblocks-headline-editor' );
		wp_enqueue_style( 'frontblocks-headline-styles' );
	}

	/**
	 * Enqueue Frontend styles.
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles() {
		wp_enqueue_style( 'frontblocks-headline-styles' );
	}

	/**
	 * Add line class attribute.
	 *
	 * @param array $attributes Attributes values for the block.
	 *
	 * @return array
	 */
	public function add_line_class_attribute( $attributes ) {
		return $attributes;
	}
}
