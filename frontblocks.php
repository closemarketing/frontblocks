<?php
/**
 * Plugin Name: FrontBlocks for GeneratePress
 * Plugin URI:  https://wordpress.org/plugins/frontblocks/
 * Description: Blocks and helpers that extends GeneratePress blocks.
 * Version:     1.1.0
 * Author:      Closemarketing
 * Author URI:  https://close.marketing
 * Text Domain: frontblocks
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Requires at least: 5.0
 * Requires PHP: 7.0
 *
 * @package     FrontBlocks
 * @author      Closemarketing
 * @copyright   2025 Closemarketing
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 *
 * Prefix:      frbl_
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'FRBL_VERSION', '1.1.0' );
define( 'FRBL_PLUGIN', __FILE__ );
define( 'FRBL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FRBL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Load Composer autoloader.
if ( file_exists( FRBL_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
	require_once FRBL_PLUGIN_PATH . 'vendor/autoload.php';
}

require_once FRBL_PLUGIN_PATH . 'includes/Plugin_Main.php';
// Initialize the plugin.
FrontBlocks\Plugin_Main::get_instance();

/**
 * Check if FrontBlocks PRO is active.
 *
 * @return bool
 */
function frbl_is_pro_active() {
	return defined( 'FRBLP_PRO_ACTIVE' ) && FRBLP_PRO_ACTIVE;
}
