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
		add_action( 'wp_ajax_frbl_get_cookie_notice_log_nonce', array( $this, 'get_log_nonce_callback' ) );
		add_action( 'wp_ajax_nopriv_frbl_get_cookie_notice_log_nonce', array( $this, 'get_log_nonce_callback' ) );
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
	 * Name of the cookie storing the visitor's consent decision.
	 *
	 * On multisite, COOKIEPATH alone can't isolate the root site from its
	 * subsites (the root site's path is '/', which every subsite path sits
	 * under), so the blog ID is folded into the cookie name itself instead.
	 *
	 * @return string
	 */
	private function get_cookie_name() {
		if ( is_multisite() ) {
			return 'frbl_cookie_consent_' . get_current_blog_id();
		}

		return 'frbl_cookie_consent';
	}

	/**
	 * Get the admin-ajax.php URL, forced to the current request's own scheme.
	 *
	 * The admin_url() function can return an https URL on an http frontend when
	 * the site forces SSL only for wp-admin (e.g. FORCE_SSL_ADMIN). That makes
	 * the consent AJAX request cross-origin: 'credentials: same-origin' on the
	 * frontend then omits the consent cookie, and the browser's CORS check
	 * would block the response regardless. Matching the current request's
	 * scheme keeps the AJAX call same-origin.
	 *
	 * @return string
	 */
	private function get_ajax_url() {
		return admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' );
	}

	/**
	 * Get the visitor's current consent decision from the cookie.
	 *
	 * @return string 'accepted', 'rejected', or '' when the visitor has not decided yet.
	 */
	private function get_consent() {
		$cookie_name = $this->get_cookie_name();

		if ( ! isset( $_COOKIE[ $cookie_name ] ) ) {
			return '';
		}

		$consent = sanitize_key( wp_unslash( $_COOKIE[ $cookie_name ] ) );

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

		$policy_path  = $this->normalize_url_path( (string) wp_parse_url( $policy_url, PHP_URL_PATH ) );
		$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$current_path = $this->normalize_url_path( (string) wp_parse_url( $request_uri, PHP_URL_PATH ) );

		if ( $policy_path !== $current_path ) {
			return false;
		}

		// On sites using plain permalinks, the path alone (e.g. '/' for every
		// '/?page_id=N' request) doesn't identify the page — the query string
		// does, so it has to match too.
		$policy_query  = $this->parse_query_pairs( (string) wp_parse_url( $policy_url, PHP_URL_QUERY ) );
		$current_query = $this->parse_query_pairs( (string) wp_parse_url( $request_uri, PHP_URL_QUERY ) );

		return $policy_query === $current_query;
	}

	/**
	 * Normalize a URL path for comparison: strip a trailing slash, but keep the
	 * site root as '/' rather than letting it collapse to an empty string (which
	 * would otherwise never match anything and wrongly rule out the root as a
	 * valid policy page).
	 *
	 * @param string $path Raw URL path.
	 * @return string Normalized path, always at least '/'.
	 */
	private function normalize_url_path( $path ) {
		$path = untrailingslashit( $path );

		return '' === $path ? '/' : $path;
	}

	/**
	 * Parse a URL query string into a sorted key/value array, so two query
	 * strings that carry the same parameters in a different order still compare
	 * as equal.
	 *
	 * @param string $query Raw query string, without the leading '?'.
	 * @return array<string, mixed>
	 */
	private function parse_query_pairs( $query ) {
		$pairs = array();

		wp_parse_str( $query, $pairs );
		ksort( $pairs );

		return $pairs;
	}

	/**
	 * Enqueue the frontend banner assets.
	 *
	 * Always enqueued, on every page including the configured policy page —
	 * never gated by the visitor's consent cookie, so a full-page cache can
	 * safely serve one cached HTML response to every visitor of a URL. The
	 * policy page only suppresses the visible banner markup (see
	 * render_banner()); it still needs these assets so an accepted visitor
	 * keeps getting tracking scripts there too.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
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
				'ajaxUrl'        => $this->get_ajax_url(),
				'cookieName'     => $this->get_cookie_name(),
				'cookiePath'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
				'expirationDays' => $days > 0 ? $days : 365,
			)
		);
	}

	/**
	 * Render the consent banner (and its early consent-handling bootstrap script) in the footer.
	 *
	 * The banner markup is always rendered the same way for every visitor of a
	 * given URL — never gated by the visitor's own consent cookie — so a full-page
	 * cache stays safe. The bootstrap script printed alongside it immediately
	 * hides the banner and requests tracking scripts client-side when a decision
	 * cookie already exists, so a returning visitor never sees a flash of the banner.
	 *
	 * The visible banner UI is suppressed on the configured cookie policy page (so
	 * a popup layout can't block that page's own content), but the bootstrap
	 * script still runs there — an already-accepted visitor must keep getting
	 * tracking scripts on every page, including the policy page.
	 *
	 * @return void
	 */
	public function render_banner() {
		$show_banner = ! $this->is_policy_page();

		if ( $show_banner ) {
			$this->render_banner_markup();
		}

		$this->render_consent_bootstrap_script();
	}

	/**
	 * Render the visible banner markup.
	 *
	 * @return void
	 */
	private function render_banner_markup() {
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
		<?php
		if ( $is_modal ) {
			?>
			<noscript>
				<style>
					.frbl-cookie-notice--popup {
						position: static;
						display: block;
						overflow: visible;
						background-color: transparent;
						padding: 0;
					}
					.frbl-cookie-notice--popup .frbl-cookie-notice__panel {
						max-width: none;
						box-shadow: none;
					}
				</style>
			</noscript>
			<?php
		}
	}

	/**
	 * Print the inline bootstrap script: hides the banner immediately when a
	 * decision cookie already exists, and — for an accepted visitor — fetches
	 * and injects the tracking scripts. Runs on every page (including the
	 * policy page, where render_banner_markup() is skipped).
	 *
	 * This is an optimization, not the only implementation: it sets
	 * window.frblCookieNoticeBootstrapped so the registered
	 * frontblocks-cookie-notice.js file (enqueued in enqueue_assets()) knows
	 * this already ran and skips redoing it. On a site whose Content Security
	 * Policy blocks unnonced inline scripts, this one is simply never executed
	 * by the browser, and that registered script performs the same bootstrap
	 * itself instead — banner hiding and tracking still work there, just
	 * without the no-flash guarantee this inline copy provides.
	 *
	 * @return void
	 */
	private function render_consent_bootstrap_script() {
		$cookie_name = $this->get_cookie_name();
		?>
		<script>
		( function () {
			var cookieMatch = document.cookie.match( new RegExp( '(?:^|; )<?php echo esc_js( $cookie_name ); ?>=([^;]*)' ) );
			var consent     = '';
			var banner      = document.getElementById( 'frbl-cookie-notice' );

			if ( cookieMatch ) {
				try {
					consent = decodeURIComponent( cookieMatch[ 1 ] );
				} catch ( e ) {
					// Malformed percent-encoding: treat it the same as no cookie at all.
					consent = '';
				}
			}

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

				fetch( '<?php echo esc_url( $this->get_ajax_url() ); ?>', {
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

			window.frblCookieNoticeBootstrapped = true;
		} )();
		</script>
		<?php
	}

	/**
	 * Pick black or white text, whichever has the higher actual WCAG contrast
	 * ratio against a background color (not just whichever "looks" darker/lighter).
	 *
	 * @param string $hex_color Background color, e.g. '#687df9'.
	 * @return string '#ffffff' or a near-black neutral.
	 */
	private function get_readable_text_color( $hex_color ) {
		$bg_luminance   = $this->get_relative_luminance( $this->hex_to_rgb( $hex_color ) );
		$dark_luminance = $this->get_relative_luminance( $this->hex_to_rgb( '#111827' ) );

		$white_contrast = $this->get_contrast_ratio( $bg_luminance, 1 );
		$dark_contrast  = $this->get_contrast_ratio( $bg_luminance, $dark_luminance );

		return $white_contrast >= $dark_contrast ? '#ffffff' : '#111827';
	}

	/**
	 * Ensure a color stays legible when used as text on the banner's white panel —
	 * accent colors that don't reach a 4.5:1 contrast ratio against white fall
	 * back to a dark neutral instead.
	 *
	 * @param string $hex_color Requested accent color, e.g. '#687df9'.
	 * @return string A color safe to use as text on a white background.
	 */
	private function get_readable_on_white_color( $hex_color ) {
		$luminance = $this->get_relative_luminance( $this->hex_to_rgb( $hex_color ) );
		$contrast  = $this->get_contrast_ratio( $luminance, 1 );

		return $contrast >= 4.5 ? $hex_color : '#111827';
	}

	/**
	 * WCAG relative luminance of an sRGB color.
	 *
	 * @param int[] $rgb Three-item [r, g, b] array, each 0-255.
	 * @return float Relative luminance between 0 (black) and 1 (white).
	 */
	private function get_relative_luminance( $rgb ) {
		$channels = array();

		foreach ( $rgb as $channel ) {
			$channel    = $channel / 255;
			$channels[] = $channel <= 0.03928 ? $channel / 12.92 : ( ( $channel + 0.055 ) / 1.055 ) ** 2.4;
		}

		return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
	}

	/**
	 * WCAG contrast ratio between two relative luminances.
	 *
	 * @param float $luminance_a First relative luminance (0-1).
	 * @param float $luminance_b Second relative luminance (0-1).
	 * @return float Contrast ratio, from 1 (no contrast) to 21 (black on white).
	 */
	private function get_contrast_ratio( $luminance_a, $luminance_b ) {
		$lighter = max( $luminance_a, $luminance_b );
		$darker  = min( $luminance_a, $luminance_b );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
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
	 * Deliberately unauthenticated: it's read-only, never touches the aggregate
	 * counters, and only ever echoes back non-secret IDs that are already public
	 * once GTM/GA4 loads. A nonce would have to be embedded in the cache-neutral
	 * HTML this module renders, and would go stale on any page a full-page cache
	 * keeps for longer than a WordPress nonce's lifetime — breaking tracking for
	 * every visitor of that cached page until it expires from the cache.
	 *
	 * @return void
	 */
	public function get_config_callback() {
		$response = array(
			'gtmId' => '',
			'ga4Id' => '',
		);

		if ( $this->is_enabled() && 'accepted' === $this->get_consent() ) {
			$options           = get_option( 'frontblocks_settings', array() );
			$response['gtmId'] = $this->sanitize_gtm_id( $options['cookie_notice_gtm_id'] ?? '' );
			$response['ga4Id'] = $this->sanitize_ga4_id( $options['cookie_notice_ga4_id'] ?? '' );
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX callback: returns a fresh nonce for the logging endpoint.
	 *
	 * Fetched live at the moment a visitor actually decides, instead of being
	 * embedded in the cache-neutral HTML this module renders — a nonce baked
	 * into that HTML would go stale on any page a full-page cache keeps around
	 * longer than a WordPress nonce's lifetime, silently dropping every decision
	 * logged from that cached response. Generating a nonce isn't a sensitive
	 * action in itself (the same thing any login form does for a logged-out
	 * visitor), so this endpoint needs no authentication of its own.
	 *
	 * @return void
	 */
	public function get_log_nonce_callback() {
		wp_send_json_success( array( 'nonce' => wp_create_nonce( self::NONCE_ACTION ) ) );
	}

	/**
	 * AJAX callback: logs the visitor's decision in the aggregate accepted/rejected counters.
	 *
	 * This is a best-effort, lightweight aggregate stat, not a precise per-visitor
	 * metering system — the module explicitly renders cache-neutral HTML (see
	 * render_banner()), so there is no page-embedded value this endpoint could use
	 * to deduplicate a replayed request without also breaking under a full-page
	 * cache, the same way a one-time token would. The nonce itself is fetched
	 * fresh via get_log_nonce_callback() right before this call, so it stays
	 * valid regardless of how long a cache keeps the page that triggered it.
	 *
	 * @return void
	 */
	public function log_consent_callback() {
		if ( ! $this->is_enabled() ) {
			wp_send_json_error( array( 'message' => __( 'Cookie Notice is disabled.', 'frontblocks' ) ), 403 );
		}

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'frontblocks' ) ), 403 );
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
