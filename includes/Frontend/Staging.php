<?php
/**
 * Staging Mode module for FrontBlocks.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2026 Closemarketing
 * @version    1.0
 */

namespace FrontBlocks\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Staging class.
 *
 * Applies a set of safety measures when the site is a staging/copy
 * environment, so it cannot be indexed, cannot send real emails, cannot
 * trigger real side effects and is clearly identified as staging.
 *
 * Everything is applied at runtime through filters. No stored option
 * (blog_public, gateway settings...) is ever modified, so the behaviour is
 * reversible and a database copy to production does not carry it over. Only
 * the manual toggle is stored; domain, environment and constant detection
 * are evaluated on every request.
 *
 * @since 1.6.0
 */
class Staging {

	/**
	 * Option holding every FrontBlocks setting.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'frontblocks_settings';

	/**
	 * Option holding the capped log of blocked emails.
	 *
	 * @var string
	 */
	const LOG_OPTION = 'frontblocks_staging_email_log';

	/**
	 * Maximum number of entries kept in the email log.
	 *
	 * @var int
	 */
	const LOG_LIMIT = 50;

	/**
	 * Replaces a removed tracking snippet, so the page shows it was stripped on purpose.
	 *
	 * @var string
	 */
	const ANALYTICS_REMOVED_MARKER = '<!-- FrontBlocks staging: tracking snippet removed -->';

	/**
	 * Reasons why staging mode can be active.
	 */
	const REASON_CONSTANT    = 'constant';
	const REASON_DOMAIN      = 'domain';
	const REASON_MANUAL      = 'manual';
	const REASON_ENVIRONMENT = 'environment';

	/**
	 * Detection result cached for the current request.
	 *
	 * @var array|null
	 */
	private static $state = null;

	/**
	 * Constructor.
	 *
	 * Hooks are registered on `init` (not here) so filters added by other
	 * plugins during `plugins_loaded`, such as `frontblocks_staging_host_prefixes`,
	 * are already in place when the state is resolved and cached.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'boot' ), 1 );
	}

	/**
	 * Register the hooks of every enabled measure, when staging mode is active.
	 *
	 * @return void
	 */
	public function boot() {
		if ( ! self::is_active() ) {
			return;
		}

		// Visual indicators.
		if ( self::is_measure_enabled( 'staging_indicators' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_node' ), 999 );
			add_filter( 'document_title_parts', array( $this, 'prefix_title_parts' ), 999 );
			add_filter( 'pre_get_document_title', array( $this, 'prefix_title' ), 999 );
			add_filter( 'admin_title', array( $this, 'prefix_admin_title' ), 999 );
			add_action( 'admin_notices', array( $this, 'render_admin_notice' ) );
		}

		if ( self::is_measure_enabled( 'staging_top_bar' ) ) {
			add_action( 'wp_body_open', array( $this, 'render_top_bar' ) );
		}

		if ( self::is_measure_enabled( 'staging_noindex' ) ) {
			$this->register_noindex();
		}

		if ( self::is_measure_enabled( 'staging_block_email' ) ) {
			$this->register_email_blocking();
		}

		if ( self::is_measure_enabled( 'staging_restrict_access' ) && ! is_admin() ) {
			add_action( 'template_redirect', array( $this, 'maybe_restrict_access' ), 0 );
		}

		if ( self::is_measure_enabled( 'staging_woocommerce' ) && class_exists( 'WooCommerce' ) ) {
			$this->register_woocommerce_safety();
		}

		if ( self::is_measure_enabled( 'staging_pause_cron' ) ) {
			// An array (even empty) short-circuits the list of events due to run.
			add_filter( 'pre_get_ready_cron_jobs', '__return_empty_array' );
			// Action Scheduler does not claim batches when no concurrent batch is allowed.
			add_filter( 'action_scheduler_queue_runner_concurrent_batches', '__return_zero', 999 );
		}

		if ( self::is_measure_enabled( 'staging_block_analytics' ) ) {
			$this->register_analytics_removal();
		}

		// A single HTTP guard covers the email APIs and the configured hosts.
		add_filter( 'pre_http_request', array( $this, 'filter_http_request' ), 1, 3 );
	}

	/*
	 * ------------------------------------------------------------------
	 * Detection.
	 * ------------------------------------------------------------------
	 */

	/**
	 * Whether staging mode is active for the current request.
	 *
	 * @return bool
	 */
	public static function is_active() {
		$state = self::get_state();

		return $state['active'];
	}

	/**
	 * Detection result for the current request, cached per request.
	 *
	 * @return array{active: bool, reasons: string[], forced_off: bool, details: array}
	 */
	public static function get_state() {
		if ( null === self::$state ) {
			self::$state = self::resolve();
		}

		return self::$state;
	}

	/**
	 * Drop the cached detection result (used when the inputs change mid-request, e.g. in tests).
	 *
	 * @return void
	 */
	public static function flush_cache() {
		self::$state = null;
	}

	/**
	 * Build the detection context from the real sources and resolve the state.
	 *
	 * @return array
	 */
	private static function resolve() {
		$options = get_option( self::OPTION_NAME, array() );
		$options = is_array( $options ) ? $options : array();

		/**
		 * Filters the host prefixes that mark a site as staging.
		 *
		 * @param string[] $prefixes Host prefixes, e.g. `beta.`. Defaults to `beta.`.
		 */
		$prefixes = apply_filters( 'frontblocks_staging_host_prefixes', array( 'beta.' ) );

		$context = array(
			'constant'    => self::get_constant_override(),
			'host'        => self::get_site_host(),
			'prefixes'    => is_array( $prefixes ) ? $prefixes : array(),
			'manual'      => ! empty( $options['enable_staging'] ),
			'environment' => wp_get_environment_type(),
		);

		return self::finalize_state( self::detect( $context ), $context['constant'] );
	}

	/**
	 * Apply the `frontblocks_is_staging` filter and the constant override to a detected state.
	 *
	 * The `FRBL_STAGING` constant is applied after the filter, so it always
	 * has the last word: `false` is the emergency switch that keeps a cloned
	 * option or an add-on from ever turning staging on in production.
	 *
	 * @param array     $state    Detection result.
	 * @param bool|null $constant Interpreted FRBL_STAGING value, or null when not set.
	 * @return array
	 */
	public static function finalize_state( array $state, $constant ) {
		/**
		 * Filters whether staging mode is active.
		 *
		 * Lets add-ons and FrontBlocks PRO force the state. The `FRBL_STAGING`
		 * constant is applied after this filter, so it always has the last word.
		 *
		 * @param bool  $active Whether staging mode is active.
		 * @param array $state  Detection result (`active`, `reasons`, `forced_off`, `details`).
		 */
		$active = (bool) apply_filters( 'frontblocks_is_staging', $state['active'], $state );

		if ( null !== $constant ) {
			$active = $constant;
		}

		$state['active'] = $active;

		return $state;
	}

	/**
	 * Pure detection logic: decide the state from an explicit context.
	 *
	 * Precedence: `FRBL_STAGING = false` always wins (emergency switch, so a
	 * production site can never be locked into staging by a cloned option).
	 * Otherwise staging is active when the constant is true, the host starts
	 * with a staging prefix, the manual toggle is on, or the environment type
	 * is `staging` or `development`.
	 *
	 * @param array $context {
	 *     Detection inputs.
	 *
	 *     @type bool|null $constant    Value of FRBL_STAGING, or null when not set.
	 *     @type string    $host        Site host.
	 *     @type string[]  $prefixes    Host prefixes that mark staging.
	 *     @type bool      $manual      Stored manual toggle.
	 *     @type string    $environment Result of wp_get_environment_type().
	 * }
	 * @return array{active: bool, reasons: string[], forced_off: bool, details: array}
	 */
	public static function detect( array $context ) {
		$constant = array_key_exists( 'constant', $context ) ? $context['constant'] : null;

		$state = array(
			'active'     => false,
			'reasons'    => array(),
			'forced_off' => false,
			'details'    => array(),
		);

		if ( false === $constant ) {
			$state['forced_off'] = true;

			return $state;
		}

		if ( true === $constant ) {
			$state['reasons'][] = self::REASON_CONSTANT;
		}

		$host = strtolower( (string) ( $context['host'] ?? '' ) );
		if ( '' !== $host ) {
			foreach ( (array) ( $context['prefixes'] ?? array() ) as $prefix ) {
				$prefix = strtolower( trim( (string) $prefix ) );
				if ( '' !== $prefix && 0 === strpos( $host, $prefix ) ) {
					$state['reasons'][]         = self::REASON_DOMAIN;
					$state['details']['host']   = $host;
					$state['details']['prefix'] = $prefix;
					break;
				}
			}
		}

		if ( ! empty( $context['manual'] ) ) {
			$state['reasons'][] = self::REASON_MANUAL;
		}

		$environment = (string) ( $context['environment'] ?? '' );
		if ( in_array( $environment, array( 'staging', 'development' ), true ) ) {
			$state['reasons'][]              = self::REASON_ENVIRONMENT;
			$state['details']['environment'] = $environment;
		}

		$state['active'] = array() !== $state['reasons'];

		return $state;
	}

	/**
	 * Value of the FRBL_STAGING constant as a strict boolean, or null when unset or unrecognised.
	 *
	 * @return bool|null
	 */
	private static function get_constant_override() {
		if ( ! defined( 'FRBL_STAGING' ) ) {
			return null;
		}

		return self::parse_constant_value( FRBL_STAGING );
	}

	/**
	 * Interpret the value of the FRBL_STAGING constant.
	 *
	 * Accepts booleans and the usual boolean-like strings (`'true'`, `'0'`...).
	 * Anything else is ignored, so a typo can never force staging on or off.
	 *
	 * @param mixed $value Constant value.
	 * @return bool|null True, false, or null when the value is not recognised.
	 */
	public static function parse_constant_value( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( ! is_scalar( $value ) ) {
			return null;
		}

		return filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	}

	/**
	 * Host of the site URL.
	 *
	 * The site URL (not the request host) is used because it is available
	 * in cron and CLI requests too.
	 *
	 * @return string
	 */
	private static function get_site_host() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );

		return is_string( $host ) ? strtolower( $host ) : '';
	}

	/**
	 * Hosts that belong to this site (home and site URL).
	 *
	 * @return string[]
	 */
	private static function get_own_hosts() {
		$hosts = array();

		foreach ( array( home_url(), site_url() ) as $url ) {
			$host = wp_parse_url( $url, PHP_URL_HOST );
			if ( is_string( $host ) && '' !== $host ) {
				$hosts[] = strtolower( $host );
			}
		}

		return array_values( array_unique( $hosts ) );
	}

	/**
	 * Human-readable explanations of why staging mode is (or is not) active.
	 *
	 * @param array|null $state Detection result. Defaults to the current one.
	 * @return string[]
	 */
	public static function describe_reasons( $state = null ) {
		$state = is_array( $state ) ? $state : self::get_state();
		$lines = array();

		if ( ! empty( $state['forced_off'] ) ) {
			$lines[] = __( 'The FRBL_STAGING constant is set to false in wp-config.php, so staging mode is forced off.', 'frontblocks' );

			return $lines;
		}

		foreach ( $state['reasons'] as $reason ) {
			switch ( $reason ) {
				case self::REASON_CONSTANT:
					$lines[] = __( 'The FRBL_STAGING constant is set to true in wp-config.php.', 'frontblocks' );
					break;
				case self::REASON_DOMAIN:
					$lines[] = sprintf(
						/* translators: 1: site host, 2: staging host prefix. */
						__( 'The domain %1$s starts with "%2$s".', 'frontblocks' ),
						(string) ( $state['details']['host'] ?? '' ),
						(string) ( $state['details']['prefix'] ?? '' )
					);
					break;
				case self::REASON_MANUAL:
					$lines[] = __( 'Staging mode was enabled manually in the settings.', 'frontblocks' );
					break;
				case self::REASON_ENVIRONMENT:
					$lines[] = sprintf(
						/* translators: %s: WordPress environment type. */
						__( 'The WordPress environment type is "%s".', 'frontblocks' ),
						(string) ( $state['details']['environment'] ?? '' )
					);
					break;
			}
		}

		return $lines;
	}

	/**
	 * Whether a measure is enabled.
	 *
	 * Measures are enabled by default (except restricting access), so a
	 * staging site is protected without any configuration. A saved value wins.
	 *
	 * @param string $key Measure option key.
	 * @return bool
	 */
	public static function is_measure_enabled( $key ) {
		$options = get_option( self::OPTION_NAME, array() );

		if ( is_array( $options ) && array_key_exists( $key, $options ) ) {
			return (bool) $options[ $key ];
		}

		$defaults = self::get_measure_defaults();

		return (bool) ( $defaults[ $key ] ?? false );
	}

	/**
	 * Default value of every measure checkbox.
	 *
	 * Restricting access is the only measure off by default: a client
	 * reviewing a `beta.` site would otherwise land on a login wall.
	 *
	 * @return bool[]
	 */
	public static function get_measure_defaults() {
		return array(
			'staging_noindex'         => true,
			'staging_block_email'     => true,
			'staging_restrict_access' => false,
			'staging_indicators'      => true,
			'staging_top_bar'         => true,
			'staging_woocommerce'     => true,
			'staging_pause_cron'      => true,
			'staging_block_http'      => true,
			'staging_block_analytics' => true,
		);
	}

	/*
	 * ------------------------------------------------------------------
	 * 1. Prevent indexing.
	 * ------------------------------------------------------------------
	 */

	/**
	 * Register the measures that keep search engines away.
	 *
	 * @return void
	 */
	private function register_noindex() {
		add_filter( 'wp_robots', array( $this, 'filter_robots' ), 999 );
		// SEO plugins print their own robots meta and ignore `wp_robots`.
		add_filter( 'rank_math/frontend/robots', array( $this, 'filter_seo_robots' ), 999 );
		add_filter( 'wpseo_robots', array( $this, 'filter_seo_robots' ), 999 );
		add_filter( 'wp_headers', array( $this, 'filter_headers' ) );
		add_filter( 'robots_txt', array( $this, 'filter_robots_txt' ), 999 );
		add_filter( 'wp_sitemaps_enabled', '__return_false' );
		add_action( 'parse_query', array( $this, 'block_sitemap_requests' ), 0 );
	}

	/**
	 * Force `noindex, nofollow` in the robots meta tag, without changing blog_public.
	 *
	 * @param array $robots Robots directives.
	 * @return array
	 */
	public function filter_robots( $robots ) {
		$robots = is_array( $robots ) ? $robots : array();

		unset( $robots['index'], $robots['follow'], $robots['max-image-preview'] );
		$robots['noindex']  = true;
		$robots['nofollow'] = true;

		return $robots;
	}

	/**
	 * Force `noindex, nofollow` in the robots meta printed by an SEO plugin.
	 *
	 * Rank Math builds an array of directives (and removes every `wp_robots`
	 * filter, including ours), while Yoast SEO filters a string.
	 *
	 * @param array|string $robots Robots directives.
	 * @return array|string
	 */
	public function filter_seo_robots( $robots ) {
		if ( is_array( $robots ) ) {
			return array(
				'index'  => 'noindex',
				'follow' => 'nofollow',
			);
		}

		return 'noindex, nofollow';
	}

	/**
	 * Send the X-Robots-Tag header.
	 *
	 * @param array $headers Response headers.
	 * @return array
	 */
	public function filter_headers( $headers ) {
		$headers                 = is_array( $headers ) ? $headers : array();
		$headers['X-Robots-Tag'] = 'noindex, nofollow';

		return $headers;
	}

	/**
	 * Serve a closed robots.txt.
	 *
	 * Only applies when WordPress serves robots.txt: a physical file in the
	 * web root is served by the web server before WordPress runs.
	 *
	 * @return string
	 */
	public function filter_robots_txt() {
		return "User-agent: *\nDisallow: /\n";
	}

	/**
	 * Answer sitemap requests with a 404, whichever plugin serves them.
	 *
	 * WordPress core and Rank Math read the `sitemap` query variable (and the
	 * stylesheet variants) to serve their XML, and Yoast SEO uses the same
	 * variable. Rank Math exposes no filter to switch its sitemap off, so the
	 * variables are cleared before they can read them, and the request becomes
	 * a 404.
	 *
	 * @param \WP_Query $query Query being parsed.
	 * @return void
	 */
	public function block_sitemap_requests( $query ) {
		if ( ! $query instanceof \WP_Query || ! $query->is_main_query() ) {
			return;
		}

		$variables = array( 'sitemap', 'sitemap_n', 'sitemap-stylesheet', 'xsl' );
		$requested = false;

		foreach ( $variables as $variable ) {
			if ( '' !== (string) $query->get( $variable ) ) {
				$requested = true;
				$query->set( $variable, '' );
			}
		}

		if ( $requested ) {
			$query->set_404();

			// WordPress only sends the 404 status itself when the query is not
			// flagged as a 404 yet, so it has to be sent here.
			status_header( 404 );
			nocache_headers();
		}
	}

	/*
	 * ------------------------------------------------------------------
	 * 2. Block outgoing email.
	 * ------------------------------------------------------------------
	 */

	/**
	 * Register the email measures: block wp_mail(), or redirect it to one address.
	 *
	 * Both filters are always registered and each one decides when an email is
	 * sent, so changing the redirect address never needs a new request to apply.
	 *
	 * @return void
	 */
	private function register_email_blocking() {
		add_filter( 'pre_wp_mail', array( $this, 'block_mail' ), 999, 2 );
		add_filter( 'wp_mail', array( $this, 'redirect_mail' ), 999 );
	}

	/**
	 * Stored address that receives every email instead of its real recipients.
	 *
	 * @return string Valid email address, or an empty string.
	 */
	public static function get_redirect_email() {
		$options = get_option( self::OPTION_NAME, array() );
		$email   = is_array( $options ) ? sanitize_email( (string) ( $options['staging_redirect_email'] ?? '' ) ) : '';

		return is_email( $email ) ? $email : '';
	}

	/**
	 * Short-circuit wp_mail() so nothing is ever sent.
	 *
	 * Returns true so flows that check the result (password reset, order
	 * emails) carry on as if the email had been sent.
	 *
	 * @param null|bool $pre  Value returned by an earlier short-circuit.
	 * @param array     $atts Email attributes (`to`, `subject`, ...).
	 * @return null|bool
	 */
	public function block_mail( $pre, $atts ) {
		// Another short-circuit already decided, or the emails are redirected instead.
		if ( null !== $pre || '' !== self::get_redirect_email() ) {
			return $pre;
		}

		$atts = is_array( $atts ) ? $atts : array();

		$this->log_email( 'blocked', $atts['to'] ?? '', $atts['subject'] ?? '' );

		return true;
	}

	/**
	 * Send every email to the configured test address instead of its recipients.
	 *
	 * @param array $atts Email attributes.
	 * @return array
	 */
	public function redirect_mail( $atts ) {
		$redirect = self::get_redirect_email();
		if ( ! is_array( $atts ) || '' === $redirect ) {
			return $atts;
		}

		$original = self::recipients_to_string( $atts['to'] ?? '' );

		$this->log_email( 'redirected', $original, $atts['subject'] ?? '' );

		$atts['to']      = $redirect;
		$atts['subject'] = '[Staging] ' . (string) ( $atts['subject'] ?? '' );
		$atts['message'] = sprintf(
			/* translators: %s: original recipients. */
			__( 'Staging site: this email was originally addressed to %s.', 'frontblocks' ),
			$original
		) . "\n\n" . (string) ( $atts['message'] ?? '' );

		// Copies would still reach the real recipients.
		$headers = $atts['headers'] ?? array();
		$headers = is_array( $headers ) ? $headers : explode( "\n", str_replace( "\r\n", "\n", (string) $headers ) );

		$atts['headers'] = array_values(
			array_filter(
				$headers,
				static function ( $header ) {
					return 1 !== preg_match( '/^\s*b?cc\s*:/i', (string) $header );
				}
			)
		);

		return $atts;
	}

	/**
	 * Hosts of transactional email APIs that are always blocked.
	 *
	 * @return string[] Host names, plus regular expressions starting with `#`.
	 */
	public static function get_mail_api_hosts() {
		$hosts = array(
			'api.sendgrid.com',
			'api.mailgun.net',
			'api.eu.mailgun.net',
			'api.brevo.com',
			'api.sendinblue.com',
			'api.postmarkapp.com',
			'api.sparkpost.com',
			'api.eu.sparkpost.com',
			'api.mailjet.com',
			// Amazon SES, per region.
			'#^email(-fips)?\.[a-z0-9-]+\.amazonaws\.com$#',
		);

		/**
		 * Filters the transactional email API hosts blocked on staging.
		 *
		 * @param string[] $hosts Host names, or regular expressions starting with `#`.
		 */
		$filtered = apply_filters( 'frontblocks_staging_mail_api_hosts', $hosts );

		return is_array( $filtered ) ? $filtered : $hosts;
	}

	/**
	 * Cap and store a blocked email in the log.
	 *
	 * @param string       $type    `blocked`, `redirected` or `api`.
	 * @param string|array $to      Recipients, or the API host for `api`.
	 * @param string       $subject Subject, empty for `api`.
	 * @return void
	 */
	private function log_email( $type, $to, $subject ) {
		$log = get_option( self::LOG_OPTION, array() );
		$log = is_array( $log ) ? $log : array();

		array_unshift(
			$log,
			array(
				'time'    => time(),
				'type'    => $type,
				'to'      => sanitize_text_field( self::recipients_to_string( $to ) ),
				'subject' => sanitize_text_field( (string) $subject ),
				'caller'  => $this->get_caller(),
			)
		);

		// Not autoloaded: it is only read on the settings screen.
		update_option( self::LOG_OPTION, array_slice( $log, 0, self::LOG_LIMIT ), false );
	}

	/**
	 * Blocked and redirected emails, newest first.
	 *
	 * @return array[]
	 */
	public static function get_email_log() {
		$log = get_option( self::LOG_OPTION, array() );

		return is_array( $log ) ? $log : array();
	}

	/**
	 * Flatten a `to` value (string or list) to a readable string.
	 *
	 * @param string|array $to Recipients.
	 * @return string
	 */
	private static function recipients_to_string( $to ) {
		if ( is_array( $to ) ) {
			$to = implode( ', ', array_map( 'strval', $to ) );
		}

		return (string) $to;
	}

	/**
	 * Short description of the code that triggered the email.
	 *
	 * @return string
	 */
	private function get_caller() {
		$frames = wp_debug_backtrace_summary( __CLASS__, 0, false ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_wp_debug_backtrace_summary -- Records which code triggered a blocked email.
		$frames = is_array( $frames ) ? $frames : array();

		// Drop the hook plumbing and the sending functions themselves.
		$frames = array_filter(
			$frames,
			static function ( $frame ) {
				return 1 !== preg_match( '/^(WP_Hook|apply_filters|wp_mail$|WP_Http|wp_remote_|_wp_http)/', (string) $frame );
			}
		);

		return substr( implode( ' > ', array_slice( array_values( $frames ), 0, 3 ) ), 0, 255 );
	}

	/*
	 * ------------------------------------------------------------------
	 * 3. Restrict access.
	 * ------------------------------------------------------------------
	 */

	/**
	 * Whether the current visitor must be stopped by the private staging page.
	 *
	 * Reuses the Maintenance rules for system requests (AJAX, cron, REST,
	 * Customizer), lets robots.txt through and lets any logged-in user through.
	 *
	 * @return bool
	 */
	public function should_restrict_request() {
		if ( Maintenance::is_system_request() ) {
			return false;
		}

		// Crawlers must read the closed robots.txt: an error there means "no restrictions".
		if ( is_robots() ) {
			return false;
		}

		return ! is_user_logged_in();
	}

	/**
	 * Show the "Private staging site" page to visitors who are not logged in.
	 *
	 * @return void
	 */
	public function maybe_restrict_access() {
		if ( ! $this->should_restrict_request() ) {
			return;
		}

		status_header( 403 );
		nocache_headers();

		Maintenance::output_curtain(
			__( 'Private staging site', 'frontblocks' ),
			'',
			wp_login_url(),
			__( 'Log in', 'frontblocks' )
		);
		exit;
	}

	/*
	 * ------------------------------------------------------------------
	 * 4. Visual indicators.
	 * ------------------------------------------------------------------
	 */

	/**
	 * Add a visible STAGING node to the admin bar (front and back office).
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_node( $wp_admin_bar ) {
		$node = array(
			'id'    => 'frbl-staging-status',
			'title' => '<span style="background:#dba617;color:#1d2327;padding:2px 8px;border-radius:3px;font-weight:600;">' . esc_html__( 'STAGING', 'frontblocks' ) . '</span>',
			'meta'  => array(
				'title' => esc_attr( implode( ' ', self::describe_reasons() ) ),
			),
		);

		if ( current_user_can( 'manage_options' ) ) {
			$node['href'] = admin_url( 'themes.php?page=frontblocks-settings' );
		}

		$wp_admin_bar->add_node( $node );
	}

	/**
	 * Prefix the document title (default title parts).
	 *
	 * @param array $parts Title parts.
	 * @return array
	 */
	public function prefix_title_parts( $parts ) {
		if ( is_array( $parts ) && isset( $parts['title'] ) ) {
			$parts['title'] = self::prefix_text( (string) $parts['title'] );
		}

		return $parts;
	}

	/**
	 * Prefix the document title when an SEO plugin builds the whole title itself.
	 *
	 * @param string $title Title, empty when core builds it from its parts.
	 * @return string
	 */
	public function prefix_title( $title ) {
		return '' === (string) $title ? $title : self::prefix_text( (string) $title );
	}

	/**
	 * Prefix the admin screen title.
	 *
	 * @param string $admin_title Admin title.
	 * @return string
	 */
	public function prefix_admin_title( $admin_title ) {
		return self::prefix_text( (string) $admin_title );
	}

	/**
	 * Prefix a text with [STAGING], once.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	private static function prefix_text( $text ) {
		$prefix = '[STAGING]';

		return 0 === strpos( $text, $prefix ) ? $text : $prefix . ' ' . $text;
	}

	/**
	 * Print the top bar shown to logged-in users on the frontend.
	 *
	 * @return void
	 */
	public function render_top_bar() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		echo '<div class="frbl-staging-bar" style="background:#dba617;color:#1d2327;text-align:center;padding:6px 12px;font:600 13px/1.4 -apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">';
		echo esc_html__( 'Staging site — changes here are not live', 'frontblocks' );
		echo '</div>';
	}

	/**
	 * Admin notice on the dashboard and the FrontBlocks settings screen.
	 *
	 * @return void
	 */
	public function render_admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'appearance_page_frontblocks-settings' ), true ) ) {
			return;
		}

		?>
		<div class="notice notice-warning">
			<p><strong><?php echo esc_html__( 'FrontBlocks staging mode is active.', 'frontblocks' ); ?></strong>
				<?php echo esc_html( implode( ' ', self::describe_reasons() ) ); ?>
				<?php echo esc_html__( 'The safety measures chosen in the Maintenance tab of the FrontBlocks settings are applied.', 'frontblocks' ); ?></p>
		</div>
		<?php
	}

	/*
	 * ------------------------------------------------------------------
	 * 5. WooCommerce safety.
	 * ------------------------------------------------------------------
	 */

	/**
	 * Register the WooCommerce measures.
	 *
	 * @return void
	 */
	private function register_woocommerce_safety() {
		add_action( 'woocommerce_before_cart', array( $this, 'print_woocommerce_notice' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'print_woocommerce_notice' ) );
		add_filter( 'render_block', array( $this, 'prepend_notice_to_blocks' ), 10, 2 );

		// Customers must not be charged twice: renewals become manual on a copy.
		add_filter( 'woocommerce_subscriptions_is_duplicate_site', '__return_true', 999 );

		// Webhooks would write to the live systems that listen to them.
		add_filter( 'woocommerce_webhook_should_deliver', '__return_false', 999 );
	}

	/**
	 * Print the "test site" notice (classic cart and checkout).
	 *
	 * @return void
	 */
	public function print_woocommerce_notice() {
		echo $this->get_woocommerce_notice_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in get_woocommerce_notice_html().
	}

	/**
	 * Add the notice before the Cart and Checkout blocks.
	 *
	 * @param string $content Rendered block.
	 * @param array  $block   Parsed block.
	 * @return string
	 */
	public function prepend_notice_to_blocks( $content, $block ) {
		$name = is_array( $block ) ? (string) ( $block['blockName'] ?? '' ) : '';

		if ( 'woocommerce/cart' !== $name && 'woocommerce/checkout' !== $name ) {
			return $content;
		}

		return $this->get_woocommerce_notice_html() . $content;
	}

	/**
	 * Markup of the "test site" notice.
	 *
	 * @return string
	 */
	private function get_woocommerce_notice_html() {
		return '<div class="woocommerce-info frbl-staging-notice" role="status">' . esc_html__( 'This is a test site. Orders placed here are not real and no payment should be made.', 'frontblocks' ) . '</div>';
	}

	/*
	 * ------------------------------------------------------------------
	 * 6. Background tasks and external connections.
	 * ------------------------------------------------------------------
	 */

	/**
	 * Hosts whose outgoing requests are blocked, from the settings and the filter.
	 *
	 * @return string[]
	 */
	public static function get_blocked_hosts() {
		$options = get_option( self::OPTION_NAME, array() );
		$hosts   = is_array( $options ) ? self::parse_hosts( (string) ( $options['staging_blocked_hosts'] ?? '' ) ) : array();

		/**
		 * Filters the hosts whose outgoing HTTP requests are blocked on staging.
		 *
		 * Add-ons (ERP, CRM or automation connectors) use it so staging never
		 * writes to a live system. A host also blocks its subdomains.
		 *
		 * @param string[] $hosts Host names.
		 */
		$filtered = apply_filters( 'frontblocks_staging_blocked_hosts', $hosts );

		return is_array( $filtered ) ? array_values( array_filter( array_map( 'strval', $filtered ) ) ) : $hosts;
	}

	/**
	 * Turn a textarea value (one host or URL per line) into a clean list of host names.
	 *
	 * @param string $text Raw text.
	 * @return string[]
	 */
	public static function parse_hosts( $text ) {
		$hosts = array();

		foreach ( preg_split( '/[\r\n,]+/', (string) $text ) as $line ) {
			$line = strtolower( trim( $line ) );
			if ( '' === $line ) {
				continue;
			}

			// Accept full URLs and `*.example.com` wildcards.
			if ( false !== strpos( $line, '://' ) ) {
				$parsed = wp_parse_url( $line, PHP_URL_HOST );
				$line   = is_string( $parsed ) ? $parsed : '';
			}
			$line = preg_replace( '#^\*\.#', '', $line );
			$line = trim( (string) $line, '. /' );

			if ( 1 === preg_match( '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/', $line ) ) {
				$hosts[] = $line;
			}
		}

		return array_values( array_unique( $hosts ) );
	}

	/**
	 * Whether a host matches a list of host names or `#` regular expressions.
	 *
	 * A host name also matches its subdomains.
	 *
	 * @param string   $host     Host to test.
	 * @param string[] $patterns Host names, or regular expressions starting with `#`.
	 * @return bool
	 */
	public static function host_matches( $host, array $patterns ) {
		$host = strtolower( (string) $host );
		if ( '' === $host ) {
			return false;
		}

		foreach ( $patterns as $pattern ) {
			$pattern = strtolower( (string) $pattern );
			if ( '' === $pattern ) {
				continue;
			}

			if ( '#' === $pattern[0] ) {
				if ( 1 === preg_match( $pattern, $host ) ) {
					return true;
				}
				continue;
			}

			if ( $host === $pattern || substr( $host, -( strlen( $pattern ) + 1 ) ) === '.' . $pattern ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Block outgoing HTTP requests to email APIs and to the configured hosts.
	 *
	 * @param false|array|\WP_Error $preempt Response to short-circuit with, or false.
	 * @param array                 $args    Request arguments.
	 * @param string                $url     Request URL.
	 * @return false|array|\WP_Error
	 */
	public function filter_http_request( $preempt, $args, $url ) {
		if ( false !== $preempt ) {
			return $preempt;
		}

		$host = wp_parse_url( (string) $url, PHP_URL_HOST );
		if ( ! is_string( $host ) || '' === $host ) {
			return $preempt;
		}

		// Never cut the site off from itself (loopback requests, REST, cron).
		if ( self::host_matches( $host, self::get_own_hosts() ) ) {
			return $preempt;
		}

		if ( self::is_measure_enabled( 'staging_block_email' ) && self::host_matches( $host, self::get_mail_api_hosts() ) ) {
			$this->log_email( 'api', strtolower( $host ), '' );

			return new \WP_Error( 'frontblocks_staging_blocked', __( 'Blocked by FrontBlocks staging mode: transactional email APIs are disabled on staging sites.', 'frontblocks' ) );
		}

		if ( self::is_measure_enabled( 'staging_block_http' ) && self::host_matches( $host, self::get_blocked_hosts() ) ) {
			return new \WP_Error( 'frontblocks_staging_blocked', __( 'Blocked by FrontBlocks staging mode: outgoing requests to this host are disabled on staging sites.', 'frontblocks' ) );
		}

		return $preempt;
	}

	/*
	 * ------------------------------------------------------------------
	 * 7. Analytics.
	 * ------------------------------------------------------------------
	 */

	/**
	 * Register the measures that keep test traffic out of the analytics data.
	 *
	 * @return void
	 */
	private function register_analytics_removal() {
		// FrontConsent only loads GTM/GA4 once this says consent was given.
		add_filter( 'frcn_cookie_notice_has_tracking_consent', '__return_false', 999 );

		// Scripts registered through the WordPress script API.
		add_filter( 'script_loader_tag', array( $this, 'filter_script_tag' ), 999, 3 );

		// Snippets printed by plugins or the theme.
		add_action( 'template_redirect', array( $this, 'start_analytics_buffer' ), 0 );
	}

	/**
	 * Hosts of the tracking scripts removed on staging.
	 *
	 * @return string[]
	 */
	public static function get_analytics_hosts() {
		$hosts = array(
			'googletagmanager.com',
			'google-analytics.com',
		);

		/**
		 * Filters the tracking hosts whose scripts are removed on staging.
		 *
		 * @param string[] $hosts Host names. A host also matches its subdomains.
		 */
		$filtered = apply_filters( 'frontblocks_staging_analytics_hosts', $hosts );

		return is_array( $filtered ) ? $filtered : $hosts;
	}

	/**
	 * Remove enqueued tracking scripts.
	 *
	 * @param string $tag    Script tag.
	 * @param string $handle Script handle.
	 * @param string $src    Script URL.
	 * @return string
	 */
	public function filter_script_tag( $tag, $handle, $src ) {
		unset( $handle );

		$host = wp_parse_url( (string) $src, PHP_URL_HOST );

		if ( is_string( $host ) && self::host_matches( $host, self::get_analytics_hosts() ) ) {
			return '';
		}

		return $tag;
	}

	/**
	 * Start buffering the page to strip tracking snippets from it.
	 *
	 * @return void
	 */
	public function start_analytics_buffer() {
		if ( is_feed() || is_robots() || is_embed() ) {
			return;
		}

		ob_start( array( __CLASS__, 'strip_analytics' ) );
	}

	/**
	 * Script ID prefixes that are never removed, even if they mention a tracking host.
	 *
	 * FrontConsent prints inline scripts (consent bootstrap, banner logic) that
	 * contain the GTM/GA4 URLs as templates. They only load tracking after
	 * consent, which this module already switches off, so they must stay.
	 *
	 * @return string[]
	 */
	public static function get_protected_script_prefixes() {
		$prefixes = array( 'frontconsent-' );

		/**
		 * Filters the script ID prefixes the tracking removal never touches.
		 *
		 * @param string[] $prefixes Script ID prefixes.
		 */
		$filtered = apply_filters( 'frontblocks_staging_analytics_protected_script_ids', $prefixes );

		return is_array( $filtered ) ? $filtered : $prefixes;
	}

	/**
	 * Remove Google Analytics / Tag Manager snippets from an HTML page.
	 *
	 * Removes `<script>` elements that load or call GA/GTM, and the GTM
	 * `<noscript>` iframe. Anything that is not an HTML document is returned
	 * unchanged, and so are the scripts of the protected IDs.
	 *
	 * @param string $html Page markup.
	 * @return string
	 */
	public static function strip_analytics( $html ) {
		$html = (string) $html;

		if ( false === stripos( $html, '<html' ) ) {
			return $html;
		}

		$pattern   = '#googletagmanager\.com|google-analytics\.com|\bgtag\s*\(|GoogleAnalyticsObject|\bga\s*\(\s*[\'"]create[\'"]#i';
		$protected = array();
		foreach ( self::get_protected_script_prefixes() as $prefix ) {
			$protected[] = preg_quote( (string) $prefix, '#' );
		}
		$keep = array() === $protected ? '' : '#^<script\b[^>]*\bid=["\'](?:' . implode( '|', $protected ) . ')#i';

		$stripped = preg_replace_callback(
			'#<script\b[^>]*>.*?</script\s*>#is',
			static function ( $matches ) use ( $pattern, $keep ) {
				if ( '' !== $keep && 1 === preg_match( $keep, $matches[0] ) ) {
					return $matches[0];
				}

				return 1 === preg_match( $pattern, $matches[0] ) ? Staging::ANALYTICS_REMOVED_MARKER : $matches[0];
			},
			$html
		);

		// A failed regex (backtrack limit) must never blank the page.
		if ( null === $stripped ) {
			return $html;
		}

		$without_iframe = preg_replace( '#<noscript>\s*<iframe[^>]+googletagmanager\.com[^>]*>\s*</iframe>\s*</noscript>#is', self::ANALYTICS_REMOVED_MARKER, $stripped );

		return null === $without_iframe ? $stripped : $without_iframe;
	}
}
