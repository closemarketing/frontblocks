<?php
/**
 * Cookie Notice module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * CookieNotice class.
 *
 * Displays a configurable cookie consent banner on the frontend, conditionally
 * loads Google Tag Manager / GA4 only after consent is granted, and keeps a
 * lightweight aggregate acceptance-rate counter.
 *
 * @since 1.0.0
 */
class CookieNotice {

	/**
	 * Name of the cookie storing the visitor's consent decision.
	 *
	 * @var string
	 */
	const COOKIE_NAME = 'frbl_cookie_consent';

	/**
	 * Option name storing the aggregate accepted/rejected counters.
	 *
	 * @var string
	 */
	const STATS_OPTION = 'frontblocks_cookie_notice_stats';

	/**
	 * Nonce action used to protect the consent-logging AJAX endpoint.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'frbl_cookie_notice_nonce';

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! is_admin() && $this->is_enabled() ) {
			add_action( 'wp_head', array( $this, 'maybe_output_head_scripts' ), 1 );
			add_action( 'wp_body_open', array( $this, 'maybe_output_body_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'wp_footer', array( $this, 'maybe_render_banner' ) );
		}

		// The logging endpoint must stay available for logged-out and logged-in visitors alike.
		add_action( 'wp_ajax_frbl_log_cookie_consent', array( $this, 'log_consent_callback' ) );
		add_action( 'wp_ajax_nopriv_frbl_log_cookie_consent', array( $this, 'log_consent_callback' ) );
	}

	/**
	 * Check if the Cookie Notice module is enabled.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		$options = get_option( 'frontblocks_settings', array() );
		return (bool) ( $options['enable_cookie_notice'] ?? false );
	}

	/**
	 * Get the visitor's current consent decision from the cookie.
	 *
	 * @return string 'accepted', 'rejected', or '' when the visitor has not decided yet.
	 */
	private function get_consent() {
		if ( ! isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return '';
		}

		$consent = sanitize_key( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );

		return in_array( $consent, array( 'accepted', 'rejected' ), true ) ? $consent : '';
	}

	/**
	 * Output the GTM script and GA4 base config on wp_head.
	 *
	 * Only runs for returning visitors who already accepted on a previous visit —
	 * first-time visitors get these scripts injected via JS after clicking Accept,
	 * never rendered in the initial page source.
	 *
	 * @return void
	 */
	public function maybe_output_head_scripts() {
		if ( 'accepted' !== $this->get_consent() ) {
			return;
		}

		$options = get_option( 'frontblocks_settings', array() );
		$gtm_id  = $this->sanitize_gtm_id( $options['cookie_notice_gtm_id'] ?? '' );
		$ga4_id  = $this->sanitize_ga4_id( $options['cookie_notice_ga4_id'] ?? '' );

		// Registered with a `false` src purely to hold the GTM bootstrap as an inline script.
		if ( $gtm_id ) {
			wp_register_script( 'frontblocks-cookie-notice-gtm', false, array(), FRBL_VERSION, false );
			wp_enqueue_script( 'frontblocks-cookie-notice-gtm' );
			wp_add_inline_script(
				'frontblocks-cookie-notice-gtm',
				"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . esc_js( $gtm_id ) . "');"
			);
		}

		if ( $ga4_id ) {
			wp_enqueue_script( 'frontblocks-cookie-notice-ga4', 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $ga4_id ), array(), FRBL_VERSION, false );
			wp_add_inline_script(
				'frontblocks-cookie-notice-ga4',
				"window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', '" . esc_js( $ga4_id ) . "');"
			);
		}
	}

	/**
	 * Output the GTM noscript iframe on wp_body_open.
	 *
	 * Same consent gating as maybe_output_head_scripts().
	 *
	 * @return void
	 */
	public function maybe_output_body_scripts() {
		if ( 'accepted' !== $this->get_consent() ) {
			return;
		}

		echo $this->get_body_scripts_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Build the GTM noscript iframe markup for an already-consenting visitor.
	 *
	 * @return string
	 */
	private function get_body_scripts_markup() {
		$options = get_option( 'frontblocks_settings', array() );
		$gtm_id  = $this->sanitize_gtm_id( $options['cookie_notice_gtm_id'] ?? '' );

		if ( ! $gtm_id ) {
			return '';
		}

		return '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( $gtm_id ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
	}

	/**
	 * Enqueue the frontend banner assets.
	 *
	 * Skipped entirely once the visitor already made a decision — no scripts,
	 * no styles, no markup for returning visitors.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( '' !== $this->get_consent() ) {
			return;
		}

		$options = get_option( 'frontblocks_settings', array() );
		$days    = (int) ( $options['cookie_notice_expiration_days'] ?? 365 );

		wp_enqueue_style(
			'frontblocks-cookie-notice',
			FRBL_PLUGIN_URL . 'assets/cookie-notice/frontblocks-cookie-notice.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_script(
			'frontblocks-cookie-notice',
			FRBL_PLUGIN_URL . 'assets/cookie-notice/frontblocks-cookie-notice.js',
			array(),
			FRBL_VERSION,
			true
		);

		wp_localize_script(
			'frontblocks-cookie-notice',
			'frblCookieNotice',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( self::NONCE_ACTION ),
				'cookieName'     => self::COOKIE_NAME,
				'expirationDays' => $days > 0 ? $days : 365,
			)
		);
	}

	/**
	 * Render the consent banner in the footer.
	 *
	 * Skipped once the visitor already made a decision.
	 *
	 * @return void
	 */
	public function maybe_render_banner() {
		if ( '' !== $this->get_consent() ) {
			return;
		}

		$this->render_banner();
	}

	/**
	 * Render the cookie consent banner markup.
	 *
	 * @return void
	 */
	private function render_banner() {
		$options = get_option( 'frontblocks_settings', array() );

		$message      = trim( (string) ( $options['cookie_notice_message'] ?? '' ) );
		$accept_label = trim( (string) ( $options['cookie_notice_accept_label'] ?? '' ) );
		$reject_label = trim( (string) ( $options['cookie_notice_reject_label'] ?? '' ) );
		$policy_url   = (string) ( $options['cookie_notice_policy_url'] ?? '' );
		$layout       = (string) ( $options['cookie_notice_layout'] ?? 'bar' );
		$position     = (string) ( $options['cookie_notice_position'] ?? 'bottom-right' );
		$color        = (string) ( $options['cookie_notice_color'] ?? '#687df9' );

		if ( '' === $message ) {
			$message = __( 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies.', 'frontblocks' );
		}

		if ( '' === $accept_label ) {
			$accept_label = __( 'Accept', 'frontblocks' );
		}

		if ( '' === $reject_label ) {
			$reject_label = __( 'Reject', 'frontblocks' );
		}

		if ( ! in_array( $layout, array( 'bar', 'box', 'popup' ), true ) ) {
			$layout = 'bar';
		}

		$classes = array( 'frbl-cookie-notice', 'frbl-cookie-notice--' . $layout );

		if ( 'box' === $layout ) {
			$classes[] = 'bottom-left' === $position ? 'frbl-cookie-notice--left' : 'frbl-cookie-notice--right';
		}

		$is_modal = 'popup' === $layout;
		?>
		<div
			id="frbl-cookie-notice"
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			style="--frbl-cookie-accent: <?php echo esc_attr( $color ); ?>;"
			role="<?php echo $is_modal ? 'dialog' : 'region'; ?>"
			<?php echo $is_modal ? 'aria-modal="true"' : ''; ?>
			aria-label="<?php echo esc_attr__( 'Cookie consent', 'frontblocks' ); ?>"
		>
			<div class="frbl-cookie-notice__panel">
				<p class="frbl-cookie-notice__message">
					<?php
					echo esc_html( $message );

					if ( $policy_url ) {
						echo ' <a href="' . esc_url( $policy_url ) . '" class="frbl-cookie-notice__link" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn more', 'frontblocks' ) . '</a>';
					}
					?>
				</p>
				<div class="frbl-cookie-notice__actions">
					<button
						type="button"
						class="frbl-cookie-notice__button frbl-cookie-notice__button--reject"
						data-frbl-cookie-action="reject"
					>
						<?php echo esc_html( $reject_label ); ?>
					</button>
					<button
						type="button"
						class="frbl-cookie-notice__button frbl-cookie-notice__button--accept"
						data-frbl-cookie-action="accept"
					>
						<?php echo esc_html( $accept_label ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX callback: logs the visitor's decision and, when accepted, returns the
	 * GTM/GA4 identifiers so the frontend script can inject them without a reload.
	 *
	 * The identifiers are only ever sent to the browser here, after Accept was
	 * clicked — they are never present in the page source beforehand.
	 *
	 * @return void
	 */
	public function log_consent_callback() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'frontblocks' ) ), 403 );
		}

		$decision = isset( $_POST['decision'] ) ? sanitize_key( wp_unslash( $_POST['decision'] ) ) : '';

		if ( ! in_array( $decision, array( 'accepted', 'rejected' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid decision.', 'frontblocks' ) ), 400 );
		}

		$this->maybe_increment_stat( $decision );

		$response = array();

		if ( 'accepted' === $decision ) {
			$options           = get_option( 'frontblocks_settings', array() );
			$response['gtmId'] = $this->sanitize_gtm_id( $options['cookie_notice_gtm_id'] ?? '' );
			$response['ga4Id'] = $this->sanitize_ga4_id( $options['cookie_notice_ga4_id'] ?? '' );
		}

		wp_send_json_success( $response );
	}

	/**
	 * Increment the aggregate accepted/rejected counter for a decision.
	 *
	 * Logged-in administrators are excluded so testing the banner doesn't skew stats.
	 *
	 * @param string $decision 'accepted' or 'rejected'.
	 * @return void
	 */
	private function maybe_increment_stat( $decision ) {
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		$stats = get_option( self::STATS_OPTION, array() );

		if ( ! is_array( $stats ) ) {
			$stats = array();
		}

		$stats['accepted'] = (int) ( $stats['accepted'] ?? 0 );
		$stats['rejected'] = (int) ( $stats['rejected'] ?? 0 );

		++$stats[ $decision ];

		update_option( self::STATS_OPTION, $stats, false );
	}

	/**
	 * Validate a Google Tag Manager container ID (e.g. GTM-XXXXXXX).
	 *
	 * @param string $value Raw value.
	 * @return string Sanitized ID, or an empty string when it doesn't match the expected format.
	 */
	private function sanitize_gtm_id( $value ) {
		$value = strtoupper( trim( (string) $value ) );

		return preg_match( '/^GTM-[A-Z0-9]+$/', $value ) ? $value : '';
	}

	/**
	 * Validate a GA4 Measurement ID (e.g. G-XXXXXXXXXX).
	 *
	 * @param string $value Raw value.
	 * @return string Sanitized ID, or an empty string when it doesn't match the expected format.
	 */
	private function sanitize_ga4_id( $value ) {
		$value = strtoupper( trim( (string) $value ) );

		return preg_match( '/^G-[A-Z0-9]+$/', $value ) ? $value : '';
	}
}
