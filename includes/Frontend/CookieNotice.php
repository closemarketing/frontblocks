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
 * The banner markup and assets are always enqueued/rendered (never gated by
 * the visitor's own consent cookie) so that a full-page cache serves the exact
 * same HTML to every visitor of a given URL. All consent-specific behavior —
 * hiding the banner, and loading GTM/GA4 — happens client-side instead.
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
	 * Option name storing the aggregate accepted counter.
	 *
	 * @var string
	 */
	const STATS_OPTION_ACCEPTED = 'frontblocks_cookie_notice_accepted_count';

	/**
	 * Option name storing the aggregate rejected counter.
	 *
	 * @var string
	 */
	const STATS_OPTION_REJECTED = 'frontblocks_cookie_notice_rejected_count';

	/**
	 * Nonce action used to protect the consent-logging AJAX endpoint.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'frbl_cookie_notice_nonce';

	/**
	 * Nonce action used to protect the read-only tracking-config AJAX endpoint.
	 *
	 * @var string
	 */
	const CONFIG_NONCE_ACTION = 'frbl_cookie_notice_config_nonce';

	/**
	 * Transient key prefix for one-time decision tokens (anti-replay for the logging endpoint).
	 *
	 * @var string
	 */
	const TOKEN_TRANSIENT_PREFIX = 'frbl_cn_token_';

	/**
	 * Cached nonce for the config-fetch endpoint, shared between enqueue_assets() and render_banner().
	 *
	 * @var string|null
	 */
	private $config_nonce = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! is_admin() && $this->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'wp_footer', array( $this, 'render_banner' ) );
		}

		// The endpoints must stay available for logged-out and logged-in visitors alike.
		add_action( 'wp_ajax_frbl_log_cookie_consent', array( $this, 'log_consent_callback' ) );
		add_action( 'wp_ajax_nopriv_frbl_log_cookie_consent', array( $this, 'log_consent_callback' ) );
		add_action( 'wp_ajax_frbl_get_cookie_notice_config', array( $this, 'get_config_callback' ) );
		add_action( 'wp_ajax_nopriv_frbl_get_cookie_notice_config', array( $this, 'get_config_callback' ) );
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
	 * Check whether the current request is for the configured cookie policy page.
	 *
	 * Used to suppress the banner there so visitors can read the policy before
	 * deciding — otherwise, with the popup layout, the notice would immediately
	 * cover the policy content on that same page.
	 *
	 * @return bool
	 */
	private function is_policy_page() {
		$options    = get_option( 'frontblocks_settings', array() );
		$policy_url = (string) ( $options['cookie_notice_policy_url'] ?? '' );

		if ( '' === $policy_url ) {
			return false;
		}

		$policy_host = wp_parse_url( $policy_url, PHP_URL_HOST );
		$site_host   = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( ! $policy_host || $policy_host !== $site_host ) {
			return false;
		}

		$policy_path  = untrailingslashit( (string) wp_parse_url( $policy_url, PHP_URL_PATH ) );
		$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$current_path = untrailingslashit( (string) wp_parse_url( $request_uri, PHP_URL_PATH ) );

		return '' !== $policy_path && $policy_path === $current_path;
	}

	/**
	 * Get (and cache) the nonce protecting the config-fetch endpoint.
	 *
	 * @return string
	 */
	private function get_config_nonce() {
		if ( null === $this->config_nonce ) {
			$this->config_nonce = wp_create_nonce( self::CONFIG_NONCE_ACTION );
		}

		return $this->config_nonce;
	}

	/**
	 * Generate a one-time token for the next consent decision and store it as a transient.
	 *
	 * @return string
	 */
	private function generate_decision_token() {
		$token = wp_generate_password( 32, false, false );

		set_transient( self::TOKEN_TRANSIENT_PREFIX . $token, 1, HOUR_IN_SECONDS );

		return $token;
	}

	/**
	 * Verify and consume a one-time decision token, so the same token can never log a second decision.
	 *
	 * @param string $token Token submitted by the client.
	 * @return bool
	 */
	private function consume_decision_token( $token ) {
		$token = (string) $token;

		if ( '' === $token || ! get_transient( self::TOKEN_TRANSIENT_PREFIX . $token ) ) {
			return false;
		}

		delete_transient( self::TOKEN_TRANSIENT_PREFIX . $token );

		return true;
	}

	/**
	 * Enqueue the frontend banner assets.
	 *
	 * Always enqueued (never gated by the visitor's consent cookie) so a full-page
	 * cache can safely serve one cached HTML response to every visitor of a URL.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( $this->is_policy_page() ) {
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
				'logNonce'       => wp_create_nonce( self::NONCE_ACTION ),
				'configNonce'    => $this->get_config_nonce(),
				'decisionToken'  => $this->generate_decision_token(),
				'cookieName'     => self::COOKIE_NAME,
				'cookiePath'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
				'expirationDays' => $days > 0 ? $days : 365,
			)
		);
	}

	/**
	 * Render the consent banner (and its early consent-handling bootstrap script) in the footer.
	 *
	 * Always rendered (never gated by the visitor's consent cookie) for the same
	 * cache-safety reason as enqueue_assets(). The bootstrap script immediately
	 * hides the banner and requests tracking scripts client-side when a decision
	 * cookie already exists, so a returning visitor never sees a flash of the banner.
	 *
	 * @return void
	 */
	public function render_banner() {
		if ( $this->is_policy_page() ) {
			return;
		}

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

		$is_modal    = 'popup' === $layout;
		$accent_text = $this->get_readable_text_color( $color );
		$accent_link = $this->get_readable_on_white_color( $color );
		$style       = sprintf(
			'--frbl-cookie-accent: %1$s; --frbl-cookie-accent-contrast: %2$s; --frbl-cookie-accent-on-light: %3$s;',
			esc_attr( $color ),
			esc_attr( $accent_text ),
			esc_attr( $accent_link )
		);
		?>
		<div
			id="frbl-cookie-notice"
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			style="<?php echo esc_attr( $style ); ?>"
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
		<script>
		( function () {
			var cookieMatch = document.cookie.match( /(?:^|; )frbl_cookie_consent=([^;]*)/ );
			var consent     = cookieMatch ? decodeURIComponent( cookieMatch[ 1 ] ) : '';
			var banner      = document.getElementById( 'frbl-cookie-notice' );

			if ( banner && ( 'accepted' === consent || 'rejected' === consent ) ) {
				banner.style.display = 'none';
			}

			window.frblCookieNoticeInject = window.frblCookieNoticeInject || function ( gtmId, ga4Id ) {
				if ( gtmId ) {
					window.dataLayer = window.dataLayer || [];
					window.dataLayer.push( { 'gtm.start': new Date().getTime(), event: 'gtm.js' } );

					var gtmScript = document.createElement( 'script' );
					gtmScript.async = true;
					gtmScript.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent( gtmId );
					document.head.appendChild( gtmScript );
				}

				if ( ga4Id ) {
					var ga4Script = document.createElement( 'script' );
					ga4Script.async = true;
					ga4Script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent( ga4Id );
					document.head.appendChild( ga4Script );

					window.dataLayer = window.dataLayer || [];
					window.gtag = window.gtag || function () {
						window.dataLayer.push( arguments );
					};
					window.gtag( 'js', new Date() );
					window.gtag( 'config', ga4Id );
				}
			};

			if ( 'accepted' === consent ) {
				var formData = new FormData();
				formData.append( 'action', 'frbl_get_cookie_notice_config' );
				formData.append( 'nonce', '<?php echo esc_js( $this->get_config_nonce() ); ?>' );

				fetch( '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
					method: 'POST',
					credentials: 'same-origin',
					body: formData
				} )
					.then( function ( response ) { return response.json(); } )
					.then( function ( response ) {
						if ( response && response.success && response.data ) {
							window.frblCookieNoticeInject( response.data.gtmId, response.data.ga4Id );
						}
					} )
					.catch( function () {} );
			}
		} )();
		</script>
		<?php
	}

	/**
	 * Pick black or white text, whichever contrasts better against a background color.
	 *
	 * @param string $hex_color Background color, e.g. '#687df9'.
	 * @return string '#ffffff' or a near-black neutral.
	 */
	private function get_readable_text_color( $hex_color ) {
		$rgb       = $this->hex_to_rgb( $hex_color );
		$luminance = ( 0.299 * $rgb[0] + 0.587 * $rgb[1] + 0.114 * $rgb[2] ) / 255;

		return $luminance > 0.6 ? '#111827' : '#ffffff';
	}

	/**
	 * Ensure a color stays legible when used as text on the banner's white panel —
	 * accent colors light enough to disappear against white fall back to a dark neutral.
	 *
	 * @param string $hex_color Requested accent color, e.g. '#687df9'.
	 * @return string A color safe to use as text on a white background.
	 */
	private function get_readable_on_white_color( $hex_color ) {
		$rgb       = $this->hex_to_rgb( $hex_color );
		$luminance = ( 0.299 * $rgb[0] + 0.587 * $rgb[1] + 0.114 * $rgb[2] ) / 255;

		return $luminance > 0.55 ? '#111827' : $hex_color;
	}

	/**
	 * Convert a hex color (3 or 6 digits, with or without '#') to an [r, g, b] triple.
	 *
	 * @param string $hex_color Hex color string.
	 * @return int[] Three-item array of 0-255 RGB values; black if the input is invalid.
	 */
	private function hex_to_rgb( $hex_color ) {
		$hex = ltrim( (string) $hex_color, '#' );

		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
			return array( 0, 0, 0 );
		}

		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	/**
	 * AJAX callback: returns the GTM/GA4 identifiers, but only when the requesting
	 * browser's own consent cookie says 'accepted'.
	 *
	 * Read-only and safe to call on every page load for a returning visitor — unlike
	 * the logging endpoint, it never touches the aggregate counters.
	 *
	 * @return void
	 */
	public function get_config_callback() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::CONFIG_NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'frontblocks' ) ), 403 );
		}

		$response = array(
			'gtmId' => '',
			'ga4Id' => '',
		);

		if ( 'accepted' === $this->get_consent() ) {
			$options           = get_option( 'frontblocks_settings', array() );
			$response['gtmId'] = $this->sanitize_gtm_id( $options['cookie_notice_gtm_id'] ?? '' );
			$response['ga4Id'] = $this->sanitize_ga4_id( $options['cookie_notice_ga4_id'] ?? '' );
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX callback: logs the visitor's decision in the aggregate accepted/rejected counters.
	 *
	 * @return void
	 */
	public function log_consent_callback() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'frontblocks' ) ), 403 );
		}

		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

		if ( ! $this->consume_decision_token( $token ) ) {
			wp_send_json_error( array( 'message' => __( 'This decision was already recorded.', 'frontblocks' ) ), 409 );
		}

		$decision = isset( $_POST['decision'] ) ? sanitize_key( wp_unslash( $_POST['decision'] ) ) : '';

		if ( ! in_array( $decision, array( 'accepted', 'rejected' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid decision.', 'frontblocks' ) ), 400 );
		}

		$this->maybe_increment_stat( $decision );

		wp_send_json_success();
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

		$option_name = 'accepted' === $decision ? self::STATS_OPTION_ACCEPTED : self::STATS_OPTION_REJECTED;

		$this->increment_option_atomically( $option_name );
	}

	/**
	 * Increment an integer option by 1 directly in the database.
	 *
	 * A plain get_option()/update_option() round trip races under concurrent
	 * requests — two visitors deciding at the same moment can both read the same
	 * value and one increment gets overwritten. A single UPDATE ... SET value = value + 1
	 * lets the database serialize concurrent increments instead.
	 *
	 * @param string $option_name Option name storing a plain integer.
	 * @return void
	 */
	private function increment_option_atomically( $option_name ) {
		global $wpdb;

		$sql = $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s", $option_name );

		$updated = $wpdb->query( $sql );

		if ( ! $updated ) {
			// First time this counter is created. add_option() returns false if another
			// request created the row first — in that case fall back to the atomic UPDATE
			// so this increment isn't silently dropped.
			if ( ! add_option( $option_name, 1, '', 'no' ) ) {
				$wpdb->query( $sql );
			}
		}

		wp_cache_delete( $option_name, 'options' );
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
