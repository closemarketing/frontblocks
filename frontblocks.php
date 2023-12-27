<?php
/**
 * Plugin Name: FrontBlocks
 * Plugin URI:  https://github.com/closemarketing/frontblocks
 * Description: Blocks and helpers to facilitate GeneratePress frontend developing.
 * Version:     0.1.0-beta.2
 * Author:      Closemarketing
 * Author URI:  https://close.technology
 * Text Domain: frontblocks
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Requires at least: 5.4
 * Requires PHP: 7.0
 * Requires Plugins: GeneratePress, GenerateBlocks
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

define( 'FRBL_VERSION', '0.1.0-beta.2' );
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
