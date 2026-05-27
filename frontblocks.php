<?php
/**
 * Plugin Name: FrontBlocks for Gutenberg/GeneratePress
 * Plugin URI:  https://wordpress.org/plugins/frontblocks/
 * Description: Blocks and helpers that extends Gutenberg and GeneratePress blocks.
 * Version:     1.3.5
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

define( 'FRBL_VERSION', '1.3.5' );
define( 'FRBL_PLUGIN', __FILE__ );
define( 'FRBL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FRBL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Load Composer autoloader.
if ( file_exists( FRBL_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
	require_once FRBL_PLUGIN_PATH . 'vendor/autoload.php';
}

require_once FRBL_PLUGIN_PATH . 'includes/Plugin_Main.php';

add_action(
	'plugins_loaded',
	function () {
		FrontBlocks\Plugin_Main::get_instance();
	}
);

/**
 * Redirect to settings page on plugin activation.
 */
function frbl_plugin_activation_redirect() {
	// Bail if activating from network or bulk activation.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking WordPress activation parameter, not processing form data.
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	// Redirect to settings page.
	if ( get_option( 'frbl_activation_redirect', false ) ) {
		delete_option( 'frbl_activation_redirect' );
		wp_safe_redirect( admin_url( 'themes.php?page=frontblocks-settings' ) );
		exit;
	}
}
add_action( 'admin_init', 'frbl_plugin_activation_redirect' );

/**
 * Set redirect flag on plugin activation.
 */
function frbl_set_activation_redirect() {
	add_option( 'frbl_activation_redirect', true );
}
register_activation_hook( __FILE__, 'frbl_set_activation_redirect' );

/**
 * Check if FrontBlocks PRO is active.
 *
 * @return bool
 */
function frbl_is_pro_active() {
	return defined( 'FRBLP_PRO_ACTIVE' ) && FRBLP_PRO_ACTIVE;
}

/**
 * Check if After Add to Cart Block is enabled.
 *
 * @return bool True if enabled.
 */
function frbl_is_after_add_to_cart_enabled() {
	if ( ! frbl_is_pro_active() ) {
		return false;
	}

	$options = get_option( 'frontblocks_settings', array() );
	return (bool) ( $options['enable_after_add_to_cart'] ?? false );
}

/**
 * Check if Horizontal Product Form is enabled.
 *
 * @return bool True if enabled.
 */
function frbl_is_horizontal_product_form_enabled() {
	if ( ! frbl_is_pro_active() ) {
		return false;
	}

	$options = get_option( 'frontblocks_settings', array() );
	return (bool) ( $options['horizontal_product_form'] ?? false );
}
