<?php
/**
 * Class Animations
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'frbl_theme_scripts_animations', 99 );
/**
 * Loads Scripts
 *
 * @return void
 */
function frbl_theme_scripts_animations() {
	wp_enqueue_style(
		'frontblocks-animations',
		FRBL_PLUGIN_URL . 'includes/animations/frontblocks-animations.css',
		array(),
		FRBL_VERSION
	);

	wp_enqueue_script(
		'frontblocks-animations-custom',
		FRBL_PLUGIN_URL . 'includes/animations/frontblocks-animations.js',
		array( 'frontblocks-animations' ),
		FRBL_VERSION,
		true
	);
}
