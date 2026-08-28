<?php
/**
 * Redundant Plugins Notice
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Detects third-party plugins that duplicate a FrontBlocks feature and shows
 * a persistent admin notice recommending the site owner deactivate them.
 *
 * This class is intentionally generic: it does not hardcode any plugin
 * knowledge itself. Every "FrontBlocks feature X makes plugin Y redundant"
 * pairing lives in an entry returned by the `frontblocks_redundant_plugins`
 * filter, so new pairings (e.g. a future image compression feature flagging
 * Imagify/Smush/ShortPixel) can be added without touching this file.
 *
 * See docs/REDUNDANT-PLUGINS.md for the entry format and examples.
 *
 * @since 1.0.0
 */
class RedundantPlugins {

	/**
	 * User meta key storing, per entry id, a hash of the redundant-plugin
	 * state (which plugins + versions) that was last dismissed.
	 *
	 * @var string
	 */
	const DISMISSED_META_KEY = 'frbl_redundant_plugins_dismissed';

	/**
	 * Nonce action used to protect the dismissal AJAX endpoint.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'frbl_dismiss_redundant_plugin';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'render_notices' ) );
		add_action( 'wp_ajax_frbl_dismiss_redundant_plugin_notice', array( $this, 'dismiss_notice_callback' ) );
	}

	/**
	 * Get the registered redundant-plugin entries.
	 *
	 * Any FrontBlocks feature (or third-party code) can add its own entry by
	 * hooking the `frontblocks_redundant_plugins` filter. See
	 * docs/REDUNDANT-PLUGINS.md for the entry format.
	 *
	 * @return array<string, array{feature: string, enabled: bool, plugins: array<string, string>, doc_url?: string}>
	 */
	public static function get_entries() {
		/**
		 * Filters the list of "FrontBlocks feature -> redundant plugin(s)" entries.
		 *
		 * Each entry is keyed by a unique, stable id and must provide:
		 * - feature (string)              Human-readable name of the FrontBlocks feature.
		 * - enabled (bool)                Whether that feature is currently active on this site.
		 * - plugins (array<string,string>) Map of plugin basename (as used by is_plugin_active())
		 *                                  to a human-readable plugin name.
		 * - doc_url (string, optional)    Link with more information about the FrontBlocks feature.
		 *
		 * @since 1.0.0
		 *
		 * @param array $entries Redundant-plugin entries, keyed by id.
		 */
		return (array) apply_filters( 'frontblocks_redundant_plugins', self::get_default_entries() );
	}

	/**
	 * Default entries shipped with FrontBlocks core.
	 *
	 * These double as a reference implementation for anyone adding new
	 * entries via the `frontblocks_redundant_plugins` filter.
	 *
	 * @return array
	 */
	private static function get_default_entries() {
		$settings = get_option( 'frontblocks_settings', array() );

		return array(
			'svg-upload'    => array(
				'feature' => __( 'SVG Upload', 'frontblocks' ),
				// Always active: FrontBlocks enables sanitized SVG uploads in the media library out of the box.
				'enabled' => true,
				'plugins' => array(
					'safe-svg/safe-svg.php'       => 'Safe SVG',
					'svg-support/svg-support.php' => 'SVG Support',
				),
				'doc_url' => admin_url( 'themes.php?page=frontblocks-settings' ),
			),
			'cookie-notice' => array(
				'feature' => __( 'Cookie Notice', 'frontblocks' ),
				'enabled' => (bool) ( $settings['enable_cookie_notice'] ?? false ),
				'plugins' => array(
					'gdpr-cookie-compliance/moove-gdpr.php' => 'GDPR Cookie Compliance',
				),
				'doc_url' => admin_url( 'themes.php?page=frontblocks-settings' ),
			),
		);
	}

	/**
	 * Render one persistent admin notice per redundant plugin that is
	 * currently detected and not already dismissed for its current state.
	 *
	 * @return void
	 */
	public function render_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$allowed_screens = array( 'appearance_page_frontblocks-settings', 'dashboard', 'plugins' );
		if ( ! in_array( $screen->id, $allowed_screens, true ) && false === strpos( $screen->id, 'frontblocks' ) ) {
			return;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$dismissed    = get_user_meta( get_current_user_id(), self::DISMISSED_META_KEY, true );
		$dismissed    = is_array( $dismissed ) ? $dismissed : array();
		$rendered_any = false;

		foreach ( self::get_entries() as $entry_id => $entry ) {
			if ( ! $this->is_valid_entry( $entry ) || ! $entry['enabled'] ) {
				continue;
			}

			$matched = $this->get_matched_plugins( $entry['plugins'] );
			if ( empty( $matched ) ) {
				continue;
			}

			$state_hash = $this->get_state_hash( $matched );
			if ( isset( $dismissed[ $entry_id ] ) && $dismissed[ $entry_id ] === $state_hash ) {
				continue;
			}

			$this->render_notice( $entry_id, $entry, $matched, $state_hash );
			$rendered_any = true;
		}

		if ( $rendered_any ) {
			$this->enqueue_script();
		}
	}

	/**
	 * Check that an entry has the minimum required shape.
	 *
	 * @param mixed $entry Entry to validate.
	 * @return bool
	 */
	private function is_valid_entry( $entry ) {
		return is_array( $entry )
			&& ! empty( $entry['feature'] )
			&& array_key_exists( 'enabled', $entry )
			&& ! empty( $entry['plugins'] )
			&& is_array( $entry['plugins'] );
	}

	/**
	 * Find which of an entry's candidate plugins are currently active.
	 *
	 * @param array $plugins Map of plugin basename to human-readable name.
	 * @return array<string, array{name: string, version: string}> Matched plugins, keyed by basename.
	 */
	private function get_matched_plugins( $plugins ) {
		$installed = get_plugins();
		$matched   = array();

		foreach ( $plugins as $basename => $name ) {
			if ( ! is_plugin_active( $basename ) ) {
				continue;
			}

			$matched[ $basename ] = array(
				'name'    => $name,
				'version' => isset( $installed[ $basename ]['Version'] ) ? $installed[ $basename ]['Version'] : '',
			);
		}

		return $matched;
	}

	/**
	 * Build a hash representing the current "which redundant plugins, at
	 * which versions, are active" state for an entry.
	 *
	 * Storing this (instead of a plain dismissed flag) is what makes the
	 * notice persistent: if the plugin is deactivated and later reactivated,
	 * or updated, the hash changes and the notice comes back.
	 *
	 * @param array $matched Matched plugins as returned by get_matched_plugins().
	 * @return string
	 */
	private function get_state_hash( $matched ) {
		$parts = array();
		foreach ( $matched as $basename => $data ) {
			$parts[] = $basename . '@' . $data['version'];
		}
		sort( $parts );

		return md5( implode( '|', $parts ) );
	}

	/**
	 * Output a single notice for one entry.
	 *
	 * @param string $entry_id   Entry id.
	 * @param array  $entry      Entry data.
	 * @param array  $matched    Matched plugins, as returned by get_matched_plugins().
	 * @param string $state_hash Current state hash for this entry.
	 * @return void
	 */
	private function render_notice( $entry_id, $entry, $matched, $state_hash ) {
		$plugin_names = wp_list_pluck( $matched, 'name' );

		$notice_message = sprintf(
			/* translators: 1: FrontBlocks feature name, 2: comma-separated list of redundant plugin names. */
			__( 'FrontBlocks already includes %1$s. The following active plugin(s) may no longer be necessary: %2$s.', 'frontblocks' ),
			'<strong>' . esc_html( $entry['feature'] ) . '</strong>',
			esc_html( implode( ', ', $plugin_names ) )
		);

		echo '<div id="frbl-redundant-plugin-notice-' . esc_attr( $entry_id ) . '" class="notice notice-warning frbl-redundant-plugin-notice" data-entry-id="' . esc_attr( $entry_id ) . '" data-state-hash="' . esc_attr( $state_hash ) . '">';
		echo '<p><strong>' . esc_html__( 'FrontBlocks: possibly unnecessary plugin', 'frontblocks' ) . '</strong></p>';
		echo '<p>' . wp_kses_post( $notice_message ) . '</p>';
		echo '<p>';
		echo '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '" class="button button-secondary">' . esc_html__( 'Review Plugins', 'frontblocks' ) . '</a>';
		if ( ! empty( $entry['doc_url'] ) ) {
			echo '&nbsp;&nbsp;<a href="' . esc_url( $entry['doc_url'] ) . '" class="button button-secondary">' . esc_html__( 'Learn More', 'frontblocks' ) . '</a>';
		}
		echo '&nbsp;&nbsp;<a href="#" class="button-link frbl-dismiss-redundant-plugin">' . esc_html__( 'Dismiss for now', 'frontblocks' ) . '</a>';
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Enqueue the JS needed to handle dismissal.
	 *
	 * @return void
	 */
	private function enqueue_script() {
		wp_enqueue_script(
			'frbl-redundant-plugins-notice',
			FRBL_PLUGIN_URL . 'assets/admin/redundant-plugins-notice.js',
			array(),
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frbl-redundant-plugins-notice',
			'frblRedundantPluginsNotice',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
			)
		);
	}

	/**
	 * AJAX handler to persist a notice dismissal for the current user.
	 *
	 * @return void
	 */
	public function dismiss_notice_callback() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Security check failed.', 'frontblocks' ), '', array( 'response' => 403 ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'frontblocks' ), '', array( 'response' => 403 ) );
		}

		$entry_id   = isset( $_POST['entry_id'] ) ? sanitize_key( wp_unslash( $_POST['entry_id'] ) ) : '';
		$state_hash = isset( $_POST['state_hash'] ) ? sanitize_text_field( wp_unslash( $_POST['state_hash'] ) ) : '';

		if ( '' === $entry_id || '' === $state_hash ) {
			wp_die( esc_html__( 'Invalid request.', 'frontblocks' ), '', array( 'response' => 400 ) );
		}

		$dismissed              = get_user_meta( get_current_user_id(), self::DISMISSED_META_KEY, true );
		$dismissed              = is_array( $dismissed ) ? $dismissed : array();
		$dismissed[ $entry_id ] = $state_hash;

		update_user_meta( get_current_user_id(), self::DISMISSED_META_KEY, $dismissed );
		wp_die();
	}
}
