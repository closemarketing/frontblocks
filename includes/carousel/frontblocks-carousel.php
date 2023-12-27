<?php
/**
 * Class Carousel
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2023 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'frbl_theme_scripts', 99 );
/**
 * Loads Scripts
 *
 * @return void
 */
function frbl_theme_scripts() {
	wp_enqueue_style(
		'frontblocks-carousel',
		FRBL_PLUGIN_URL . 'includes/carousel/frontblocks-carousel.css',
		array(),
		FRBL_VERSION
	);

	wp_enqueue_script(
		'frontblocks-carousel',
		FRBL_PLUGIN_URL . 'includes/carousel/glide.min.js',
		array(),
		FRBL_VERSION,
		true
	);

	wp_enqueue_script(
		'frontblocks-carousel-custom',
		FRBL_PLUGIN_URL . 'includes/carousel/frontblocks-carousel.js',
		array( 'frontblocks-carousel' ),
		FRBL_VERSION,
		true
	);
}
