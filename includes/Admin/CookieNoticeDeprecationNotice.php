<?php
/**
 * Cookie Notice Deprecation Notice
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Tells site owners still using FrontBlocks' bundled Cookie Notice module that
 * it moved to its own dedicated plugin, FrontConsent, and offers a one-click
 * install/activate link from wp-admin.
 *
 * FrontBlocks' own Cookie Notice module keeps working during this transition
 * period — this is a heads-up, not a functional change. Once FrontConsent is
 * installed and active, it migrates the site's settings/stats on its own and
 * disables this module automatically (see FrontConsent's own Migration class),
 * so no coordination happens from this side beyond the notice itself.
 *
 * @since 1.0.0
 */
class CookieNoticeDeprecationNotice {

	/**
	 * User meta key storing whether this notice was dismissed.
	 *
	 * @var string
	 */
	const DISMISSED_META_KEY = 'frbl_cookie_notice_deprecation_dismissed';

	/**
	 * Nonce action used to protect the dismissal AJAX endpoint.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'frbl_dismiss_cookie_notice_deprecation';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'render_notice' ) );
		add_action( 'wp_ajax_frbl_dismiss_cookie_notice_deprecation', array( $this, 'dismiss_notice_callback' ) );
	}

	/**
	 * Whether FrontConsent is already handling Cookie Notice on this site.
	 *
	 * @return bool
	 */
	private function is_frontconsent_active() {
		return function_exists( 'is_plugin_active' )
			? is_plugin_active( 'frontconsent/frontconsent.php' )
			: defined( 'FRCN_VERSION' );
	}

	/**
	 * Render the notice, only where relevant and not yet dismissed.
	 *
	 * @return void
	 */
	public function render_notice() {
		if ( ! current_user_can( 'install_plugins' ) || $this->is_frontconsent_active() ) {
			return;
		}

		$options = get_option( 'frontblocks_settings', array() );
		if ( empty( $options['enable_cookie_notice'] ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$allowed_screens = array( 'appearance_page_frontblocks-settings', 'dashboard', 'plugins' );
		if ( ! in_array( $screen->id, $allowed_screens, true ) ) {
			return;
		}

		if ( get_user_meta( get_current_user_id(), self::DISMISSED_META_KEY, true ) ) {
			return;
		}

		$install_url = wp_nonce_url(
			self_admin_url( 'update.php?action=install-plugin&plugin=front-consent' ),
			'install-plugin_front-consent'
		);
		?>
		<div id="frbl-cookie-notice-deprecation" class="notice notice-warning">
			<p><strong><?php echo esc_html__( 'FrontBlocks: Cookie Notice is moving to its own plugin', 'frontblocks' ); ?></strong></p>
			<p>
				<?php echo esc_html__( 'Cookie Notice is being extracted from FrontBlocks into a dedicated free plugin, FrontConsent, so it is easier to find and keeps improving on its own. Install FrontConsent and your existing settings and stats are migrated automatically, and this module turns itself off.', 'frontblocks' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary"><?php echo esc_html__( 'Install FrontConsent', 'frontblocks' ); ?></a>
				&nbsp;&nbsp;<a href="#" class="button-link frbl-dismiss-cookie-notice-deprecation"><?php echo esc_html__( 'Dismiss for now', 'frontblocks' ); ?></a>
			</p>
		</div>
		<script>
		document.addEventListener( 'DOMContentLoaded', function () {
			var dismissLink = document.querySelector( '.frbl-dismiss-cookie-notice-deprecation' );
			if ( ! dismissLink ) {
				return;
			}

			dismissLink.addEventListener( 'click', function ( event ) {
				event.preventDefault();

				var formData = new FormData();
				formData.append( 'action', 'frbl_dismiss_cookie_notice_deprecation' );
				formData.append( 'nonce', '<?php echo esc_js( wp_create_nonce( self::NONCE_ACTION ) ); ?>' );

				fetch( ajaxurl, { method: 'POST', credentials: 'same-origin', body: formData } )
					.then( function () {
						var notice = document.getElementById( 'frbl-cookie-notice-deprecation' );
						if ( notice ) {
							notice.remove();
						}
					} );
			} );
		} );
		</script>
		<?php
	}

	/**
	 * AJAX handler to persist the dismissal for the current user.
	 *
	 * @return void
	 */
	public function dismiss_notice_callback() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Security check failed.', 'frontblocks' ), '', array( 'response' => 403 ) );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'frontblocks' ), '', array( 'response' => 403 ) );
		}

		update_user_meta( get_current_user_id(), self::DISMISSED_META_KEY, true );
		wp_die();
	}
}
