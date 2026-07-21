<?php
/**
 * Scroll to Top module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * ScrollTop class.
 *
 * @since 1.0.0
 */
class ScrollTop {

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! is_admin() && $this->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'wp_footer', array( $this, 'render_button' ) );
		}
	}

	/**
	 * Check if scroll to top is enabled.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		$options = get_option( 'frontblocks_settings', array() );
		return (bool) ( $options['enable_scroll_top'] ?? false );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$options  = get_option( 'frontblocks_settings', array() );
		$position = sanitize_text_field( $options['scroll_top_position'] ?? 'bottom-right' );

		wp_enqueue_style(
			'frontblocks-scroll-top',
			FRBL_PLUGIN_URL . 'assets/scroll-top/frontblocks-scroll-top.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-scroll-top',
			FRBL_PLUGIN_URL . 'assets/scroll-top/frontblocks-scroll-top.js',
			array(),
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frontblocks-scroll-top',
			'frblScrollTop',
			array(
				'position' => $position,
			)
		);
	}

	/**
	 * Render the scroll to top button HTML.
	 *
	 * @return void
	 */
	public function render_button() {
		$options        = get_option( 'frontblocks_settings', array() );
		$icon_url       = esc_url( $options['scroll_top_icon_url'] ?? '' );
		$position       = sanitize_text_field( $options['scroll_top_position'] ?? 'bottom-right' );
		$position_class = 'bottom-left' === $position ? 'frbl-scroll-top--left' : 'frbl-scroll-top--right';
		?>
		<button
			id="frbl-scroll-top"
			class="frbl-scroll-top <?php echo esc_attr( $position_class ); ?>"
			aria-label="<?php echo esc_attr__( 'Scroll to top', 'frontblocks' ); ?>"
			title="<?php echo esc_attr__( 'Scroll to top', 'frontblocks' ); ?>"
		>
			<?php if ( $icon_url ) : ?>
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="" aria-hidden="true" />
			<?php else : ?>
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="12" y1="19" x2="12" y2="5"></line>
					<polyline points="5 12 12 5 19 12"></polyline>
				</svg>
			<?php endif; ?>
		</button>
		<?php
	}
}
