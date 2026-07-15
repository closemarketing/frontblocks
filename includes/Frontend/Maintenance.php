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
			<link rel="stylesheet" href="<?php echo esc_url( FRBL_PLUGIN_URL . 'assets/maintenance/frontblocks-maintenance.css' ); ?>">
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
}
