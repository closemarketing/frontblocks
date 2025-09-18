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
	}
}
