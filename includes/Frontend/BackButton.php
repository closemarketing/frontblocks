<?php
/**
 * Back Button module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * BackButton class.
 *
 * @since 1.0.0
 */
class BackButton {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Only load on frontend if enabled.
		if ( ! is_admin() && $this->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'wp_footer', array( $this, 'render_button' ) );
		}
	}

	/**
	 * Check if back button is enabled.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		$options = get_option( 'frontblocks_settings', array() );
		return (bool) ( $options['enable_back_button'] ?? false );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'frontblocks-back-button',
			FRBL_PLUGIN_URL . 'assets/back-button/frontblocks-back-button.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-back-button',
			FRBL_PLUGIN_URL . 'assets/back-button/frontblocks-back-button.js',
			array(),
			FRBL_VERSION,
			true
		);
	}

	/**
	 * Render the back button HTML.
	 *
	 * @return void
	 */
	public function render_button() {
		?>
		<button 
			id="frbl-back-button" 
			class="frbl-back-button" 
			aria-label="<?php echo esc_attr__( 'Go back to previous page', 'frontblocks' ); ?>"
			title="<?php echo esc_attr__( 'Go back', 'frontblocks' ); ?>"
		>
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<line x1="19" y1="12" x2="5" y2="12"></line>
				<polyline points="12 19 5 12 12 5"></polyline>
			</svg>
		</button>
		<?php
	}
}

new BackButton();

