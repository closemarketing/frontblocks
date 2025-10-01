<?php
/**
 * Plugin Main
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.3-beta.1
 */

namespace FrontBlocks;

defined( 'ABSPATH' ) || exit;

/**
 * Main FrontBlocks class.
 *
 * @since 1.0.0
 */
class Plugin_Main {
	/**
	 * Plugin instance.
	 *
	 * @var FrontBlocks
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return FrontBlocks
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	private function init() {
		// Load modules.
		$this->load_modules();

		// General enqueue scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
	}

	/**
	 * Load plugin modules.
	 *
	 * @return void
	 */
	private function load_modules() {
		// Admin settings page.
		if ( is_admin() ) {
			new Admin\Settings();
		}

		// Carousel module.
		new Frontend\Carousel();

		// Animations module.
		new Frontend\Animations();

		// Sticky Column module.
		new Frontend\StickyColumn();

		// Gallery module.
		new Frontend\Gallery();

		// Insert Post Block module.
		new Frontend\InsertPost();

		// Testimonials module (includes settings).
		new Frontend\Testimonials();

		// Headline module (GenerateBlocks Headline enhancements).
		new Frontend\Headline();

		// Product Categories module (WooCommerce).
		new Frontend\ProductCategories();
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$dist_dir = WP_DEBUG ? 'carousel/' : 'dist/';

		wp_enqueue_style(
			'frontblocks-carousel',
			FRBL_PLUGIN_URL . 'assets/' . $dist_dir . 'frontblocks-carousel.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-carousel-custom',
			FRBL_PLUGIN_URL . 'assets/' . $dist_dir . ( WP_DEBUG ? 'frontblocks-carousel.js' : 'frontblocks-carousel-min.js' ),
			array( 'frontblocks-carousel' ),
			FRBL_VERSION,
			true
		);

		wp_enqueue_script(
			'frontblocks-carousel',
			FRBL_PLUGIN_URL . 'assets/dist/glide.min.js',
			array(),
			FRBL_VERSION,
			true
		);
	}
}
