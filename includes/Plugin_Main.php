<?php
/**
 * Plugin Main
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.3
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
			// Load Admin classes if autoloader is not available.
			if ( ! class_exists( 'FrontBlocks\Admin\UI' ) ) {
				require_once FRBL_PLUGIN_PATH . 'includes/Admin/UI.php';
			}
			if ( ! class_exists( 'FrontBlocks\Admin\Settings' ) ) {
				require_once FRBL_PLUGIN_PATH . 'includes/Admin/Settings.php';
			}
			new Admin\Settings();
		}

		// Container Edge Alignment for GenerateBlocks.
		new Frontend\ContainerEdgeAlignment();

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

		// Testimonials Block (Gutenberg block).
		if ( ! class_exists( 'FrontBlocks\Blocks\Testimonials_Block' ) ) {
			require_once FRBL_PLUGIN_PATH . 'includes/Blocks/Testimonials_Block.php';
		}
		new Blocks\Testimonials_Block();

		// Headline module (GenerateBlocks Headline enhancements).
		new Frontend\Headline();

		// Product Categories module (WooCommerce).
		new Frontend\ProductCategories();

		// Counter module (GenerateBlocks Headline counter effect).
		new Frontend\Counter();

		// Reading Time module.
		new Frontend\ReadingTime();

		// Reading Progress Bar module.
		new Frontend\ReadingProgress();

		// Back Button module.
		new Frontend\BackButton();

		// Shape Animations module (for GenerateBlocks Shape block).
		new Frontend\ShapeAnimations();

		// Gravity Forms Inline Layout module.
		new Frontend\GravityFormsInline();
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'frontblocks-carousel',
			FRBL_PLUGIN_URL . 'assets/carousel/frontblocks-carousel.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-carousel-custom',
			FRBL_PLUGIN_URL . 'assets/carousel/frontblocks-carousel.js',
			array( 'frontblocks-carousel' ),
			FRBL_VERSION,
			true
		);

		wp_enqueue_script(
			'frontblocks-carousel',
			FRBL_PLUGIN_URL . 'assets/carousel/glide.min.js',
			array(),
			FRBL_VERSION,
			true
		);
	}
}
