<?php
/**
 * Plugin Name: FrontBlocks
 * Plugin URI:  https://github.com/closemarketing/frontblocks
 * Description: Blocks and helpers to facilitate GeneratePress frontend developing.
 * Version:     0.2.5
 * Author:      Closemarketing
 * Author URI:  https://close.marketing
 * Text Domain: frontblocks
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Requires at least: 6.7
 * Requires PHP: 7.0
 *
 * @package     FrontBlocks
 * @author      Closemarketing
 * @copyright   2023 Closemarketing
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 *
 * Prefix:      frbl_
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'FRBL_VERSION', '0.2.5' );
define( 'FRBL_PLUGIN', __FILE__ );
define( 'FRBL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FRBL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'plugins_loaded', 'frbl__plugin_init' );
/**
 * Load localization files
 *
 * @return void
 */
function frbl__plugin_init() {
	load_plugin_textdomain( 'frontblocks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Carousel.
require_once FRBL_PLUGIN_PATH . 'includes/carousel/frontblocks-carousel.php';

// Animations.
require_once FRBL_PLUGIN_PATH . 'includes/animations/frontblocks-animations.php';


// Enqueue custom block editor script
add_action( 'enqueue_block_editor_assets', 'enqueue_block_editor_assets' );

function enqueue_block_editor_assets() {
	wp_enqueue_script(
		'frontblocks-advanced-option',
		FRBL_PLUGIN_URL . 'includes/dist/frontblocks-advanced-option.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-edit-post' ),
		FRBL_VERSION,
		true
	);
}

// Register the custom attribute for the block.
add_filter(
	'generateblocks_blocks_registered_block',
	function ( $block_args, $block_type ) {
		if ( $block_type !== 'generateblocks/grid' ) {
			return $block_args;
		}

		$block_args['attributes']['cmCustomGridOption'] = array(
			'type'    => 'string',
			'default' => '',
		);

		return $block_args;
	},
	10,
	2
);
