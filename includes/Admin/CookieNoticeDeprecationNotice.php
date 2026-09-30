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
 * Tells site owners who had FrontBlocks' Cookie Notice module enabled that
 * its runtime has been removed entirely (a hard cutover, not a transition)
 * and moved into its own dedicated plugin, FrontConsent, and offers a
 * one-click install/activate link from wp-admin.
 *
 * Unlike the original transition-period version of this notice, there is no
 * functional overlap left to manage: FrontBlocks no longer renders any
 * banner at all, so this is now an urgent "your site has no consent banner"
 * warning, not a heads-up. Once FrontConsent is installed and active, it
 * migrates the site's settings/stats on its own (see FrontConsent's own
 * Migration class), so no coordination happens from this side beyond the
 * notice itself.
 *
 * @since 1.0.0
 */
class CookieNoticeDeprecationNotice {

	/**
	 * User meta key storing whether this notice was dismissed.
	 *
	 * Versioned with a `_v2` suffix: the hard cutover that removed Cookie
	 * Notice's runtime module entirely (rather than just deprecating it)
	 * makes this notice urgent in a way the original "Dismiss for now" never
	 * anticipated — an admin who dismissed the old, lower-stakes transition
	 * notice must still see this one, since their site now has no consent
	 * banner at all until they install FrontConsent. Bumping the meta key
	 * makes every prior dismissal irrelevant without needing a migration.
	 *
	 * @var string
	 */
	const DISMISSED_META_KEY = 'frbl_cookie_notice_deprecation_dismissed_v2';

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
	public static function is_frontconsent_active() {
		if ( defined( 'FRCN_VERSION' ) ) {
			return true;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			return false;
		}

		foreach ( self::get_frontconsent_plugin_basenames() as $plugin_basename ) {
			if ( is_plugin_active( $plugin_basename ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return the possible plugin basenames used by FrontConsent distributions.
	 *
	 * WordPress.org installs FrontConsent in the frontconsent directory. Keep
	 * the former front-consent basename for manually-installed copies.
	 *
	 * @return string[]
	 */
	private static function get_frontconsent_plugin_basenames() {
		return array(
			'frontconsent/frontconsent.php',
			'front-consent/frontconsent.php',
		);
	}

	/**
	 * Return an install URL, or an activation URL when FrontConsent is present.
	 *
	 * @return string
	 */
	public static function get_frontconsent_action_url() {
		foreach ( self::get_frontconsent_plugin_basenames() as $plugin_basename ) {
			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_basename ) ) {
				return wp_nonce_url(
					self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin_basename ) ),
					'activate-plugin_' . $plugin_basename
				);
			}
		}

		return wp_nonce_url(
			self_admin_url( 'update.php?action=install-plugin&plugin=frontconsent' ),
			'install-plugin_frontconsent'
		);
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

		$action_url = self::get_frontconsent_action_url();
		?>
		<div id="frbl-cookie-notice-deprecation" class="notice notice-warning">
			<p><strong><?php echo esc_html__( 'FrontBlocks: your site no longer shows a cookie consent banner', 'frontblocks' ); ?></strong></p>
			<p>
				<?php echo esc_html__( 'Cookie Notice has been removed from FrontBlocks entirely and moved into its own dedicated free plugin, FrontConsent. Your site currently has no cookie consent banner at all until you install and activate it. Doing so automatically imports your existing settings and stats.', 'frontblocks' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $action_url ); ?>" class="button button-primary"><?php echo esc_html__( 'Install FrontConsent', 'frontblocks' ); ?></a>
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
