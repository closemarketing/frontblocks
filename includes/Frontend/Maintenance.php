<?php
/**
 * Maintenance Mode module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Maintenance class.
 *
 * @since 1.0.0
 */
class Maintenance {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Only intercept the frontend if maintenance mode is enabled.
		if ( ! is_admin() && $this->is_enabled() ) {
			add_action( 'template_redirect', array( $this, 'maybe_render_maintenance_page' ) );
		}

		// Flag maintenance mode in the admin bar (front and back office) so admins don't forget it's on.
		if ( $this->is_enabled() ) {
			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_node' ), 999 );
		}
	}

	/**
	 * Add a visible "Maintenance: ON" node to the admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_node( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'frbl-maintenance-status',
				'title' => '<span style="background:#d63638;color:#fff;padding:2px 8px;border-radius:3px;font-weight:600;">' . esc_html__( 'Maintenance: ON', 'frontblocks' ) . '</span>',
				'href'  => admin_url( 'themes.php?page=frontblocks-settings' ),
				'meta'  => array(
					'title' => esc_attr__( 'Maintenance mode is currently enabled. Click to manage it.', 'frontblocks' ),
				),
			)
		);
	}

	/**
	 * Check if maintenance mode is enabled.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		$options = get_option( 'frontblocks_settings', array() );
		return (bool) ( $options['enable_maintenance'] ?? false );
	}

	/**
	 * Render the maintenance curtain page and stop further execution,
	 * unless the current request should be allowed through.
	 *
	 * @return void
	 */
	public function maybe_render_maintenance_page() {
		if ( $this->should_bypass() ) {
			return;
		}

		$this->render_maintenance_page();
		exit;
	}

	/**
	 * Determine whether the current request should bypass the maintenance page.
	 *
	 * @return bool
	 */
	private function should_bypass() {
		// Never intercept AJAX, cron or REST requests.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return true;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		if ( is_customize_preview() ) {
			return true;
		}

		// Let logged-in administrators browse the real site to be able to turn the mode off.
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Output the maintenance curtain page.
	 *
	 * @return void
	 */
	private function render_maintenance_page() {
		$options   = get_option( 'frontblocks_settings', array() );
		$title     = trim( (string) ( $options['maintenance_title'] ?? '' ) );
		$image_id  = (int) ( $options['maintenance_image'] ?? 0 );
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

		if ( '' === $title ) {
			$title = __( 'We are currently performing maintenance', 'frontblocks' );
		}

		status_header( 503 );
		header( 'Retry-After: 3600' );
		nocache_headers();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="robots" content="noindex, nofollow">
			<title><?php echo esc_html( $title ); ?></title>
			<style><?php echo $this->get_maintenance_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
		</head>
		<body class="frbl-maintenance-body">
			<div class="frbl-maintenance-overlay" <?php echo $image_url ? 'style="background-image: url(\'' . esc_url( $image_url ) . '\');"' : ''; ?>>
				<div class="frbl-maintenance-content">
					<h1 class="frbl-maintenance-title"><?php echo esc_html( $title ); ?></h1>
				</div>
			</div>
		</body>
		</html>
		<?php
	}

	/**
	 * Read the maintenance page CSS to inline it.
	 *
	 * There is no theme/template context on this page (we bypass wp_head/wp_footer
	 * entirely), so the stylesheet is inlined instead of enqueued.
	 *
	 * @return string
	 */
	private function get_maintenance_css() {
		$css_path = FRBL_PLUGIN_PATH . 'assets/maintenance/frontblocks-maintenance.css';

		if ( ! file_exists( $css_path ) ) {
			return '';
		}

		return file_get_contents( $css_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
}
