<?php
/**
 * Plugin Name: FrontBlocks for GeneratePress
 * Plugin URI:  https://wordpress.org/plugins/frontblocks/
 * Description: Blocks and helpers that extends GeneratePress blocks.
 * Version:     1.0.3-beta.1
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

namespace FrontBlocks;

defined( 'ABSPATH' ) || exit;

/**
 * Main FrontBlocks class.
 *
 * @since 1.0.0
 */
class FrontBlocks {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.3-beta.1';

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
		// Carousel module.
		new Carousel\Carousel();

		// Animations module.
		new Animations\Animations();

		// Sticky Column module.
		new StickyColumn\StickyColumn();

		// Gallery module.
		new Gallery\Gallery();

		// Insert Post Block module.
		new Post\InsertPost();
	}
}
