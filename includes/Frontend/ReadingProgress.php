<?php
/**
 * Reading Progress Bar module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * ReadingProgress class.
 *
 * @since 1.0.0
 */
class ReadingProgress {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_reading_progress_bar' ) );
	}

	/**
	 * Check if reading progress is enabled.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		$options = get_option( 'frontblocks_settings', array() );
		return (bool) ( $options['enable_reading_progress'] ?? false );
	}

	/**
	 * Enqueue frontend styles and scripts.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		// Only load on singular posts (blog entries).
		if ( ! is_singular( 'post' ) || ! $this->is_enabled() ) {
			return;
		}

		wp_enqueue_style(
			'frontblocks-reading-progress',
			FRBL_PLUGIN_URL . 'assets/reading-progress/frontblocks-reading-progress.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-reading-progress',
			FRBL_PLUGIN_URL . 'assets/reading-progress/frontblocks-reading-progress.js',
			array(),
			FRBL_VERSION,
			true
		);
	}

	/**
	 * Render the reading progress bar HTML.
	 *
	 * @return void
	 */
	public function render_reading_progress_bar() {
		// Only render on singular posts (blog entries).
		if ( ! is_singular( 'post' ) || ! $this->is_enabled() ) {
			return;
		}
		?>
		<div class="frbl-reading-progress-bar" role="progressbar" aria-label="<?php echo esc_attr__( 'Reading progress', 'frontblocks' ); ?>" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
			<div class="frbl-reading-progress-fill"></div>
		</div>
		<?php
	}
}

