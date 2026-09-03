<?php
/**
 * Google Sign-In module for FrontBlocks.
 *
 * Lets visitors sign in / register with their Google account on the
 * wp-admin login screen and on WooCommerce's My Account and Checkout
 * pages, using Google Identity Services and server-side ID token
 * verification.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\GoogleSignIn;

defined( 'ABSPATH' ) || exit;

/**
 * GoogleSignIn class.
 *
 * @since 1.0.0
 */
class GoogleSignIn {

	/**
	 * Nonce action used for the REST sign-in endpoint.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'frbl_google_signin_action';

	/**
	 * User meta key storing the linked Google account ID (sub claim).
	 *
	 * @var string
	 */
	const META_GOOGLE_ID = 'frbl_google_id';

	/**
	 * User meta key storing the Google profile picture URL.
	 *
	 * @var string
	 */
	const META_GOOGLE_AVATAR = 'frbl_google_avatar';

	/**
	 * Google Sign-In settings.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Whether the bridging script has already been localized on this request.
	 *
	 * @var bool
	 */
	private $localized = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			new Settings();
		}
		$this->options = Settings::get_options();

		// The shortcodes and block are always available; they render nothing
		// (or an admin-only notice) until Client ID and Secret are configured.
		add_shortcode( 'frontblocks_google_login', array( $this, 'shortcode_login' ) );
		add_shortcode( 'frontblocks_google_register', array( $this, 'shortcode_register' ) );
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		// Feature is fully optional/off by default until Client ID and Secret are configured.
		if ( ! Settings::is_configured() ) {
			return;
		}

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		if ( ! empty( $this->options['enable_wp_login'] ) ) {
			add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login_assets' ) );
			add_action( 'login_form', array( $this, 'render_wp_login_button' ) );
		}

		if ( class_exists( 'WooCommerce' ) ) {
			if ( ! empty( $this->options['enable_myaccount_login'] ) ) {
				add_action( 'woocommerce_login_form_start', array( $this, 'render_myaccount_login_button' ) );
			}

			if ( ! empty( $this->options['enable_myaccount_register'] ) ) {
				add_action( 'woocommerce_register_form_start', array( $this, 'render_myaccount_register_button' ) );
			}

			if ( ! empty( $this->options['enable_checkout'] ) ) {
				add_action( 'woocommerce_before_checkout_form', array( $this, 'render_checkout_button' ), 5 );
			}
		}
	}

	/**
	 * Register the REST route used to complete sign-in from the browser.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'frontblocks/v1',
			'/google-signin',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_signin' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'credential'  => array(
						'required' => true,
						'type'     => 'string',
					),
					'redirect_to' => array(
						'required' => false,
						'type'     => 'string',
					),
				),
			)
		);
	}

	/**
	 * Handle the REST request: verify the Google ID token, resolve a WP
	 * user (matching or creating one) and log them in.
	 *
	 * @param \WP_REST_Request $request Incoming request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle_signin( \WP_REST_Request $request ) {
		$redirect_to = $this->safe_redirect( (string) $request->get_param( 'redirect_to' ) );

		if ( is_user_logged_in() ) {
			return new \WP_REST_Response(
				array(
					'success'     => true,
					'redirect_to' => $redirect_to,
				)
			);
		}

		$credential = (string) $request->get_param( 'credential' );
		$payload    = TokenVerifier::verify( $credential, $this->options['client_id'] );

		if ( is_wp_error( $payload ) ) {
			return new \WP_Error( $payload->get_error_code(), $payload->get_error_message(), array( 'status' => 401 ) );
		}

		$user_id = $this->find_or_create_user( $payload );

		if ( is_wp_error( $user_id ) ) {
			return new \WP_Error( $user_id->get_error_code(), $user_id->get_error_message(), array( 'status' => 403 ) );
		}

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		$user = get_userdata( $user_id );
		if ( $user ) {
			do_action( 'wp_login', $user->user_login, $user );
		}

		return new \WP_REST_Response(
			array(
				'success'     => true,
				'redirect_to' => $redirect_to,
			)
		);
	}

	/**
	 * Resolve a WordPress user for a verified Google payload, matching an
	 * existing account (by linked Google ID, then by verified email) or
	 * creating a new one when registration is allowed.
	 *
	 * @param array $payload Verified Google ID token payload.
	 * @return int|\WP_Error User ID, or error.
	 */
	private function find_or_create_user( $payload ) {
		$email = sanitize_email( $payload['email'] );
		$sub   = sanitize_text_field( $payload['sub'] );

		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'frbl_gsi_invalid_email', __( 'Google did not return a valid email address.', 'frontblocks' ) );
		}

		// 1. Match by previously linked Google account ID.
		$linked = get_users(
			array(
				'meta_key'   => self::META_GOOGLE_ID, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $sub, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'number'     => 1,
				'fields'     => 'ID',
			)
		);

		if ( ! empty( $linked ) ) {
			return (int) $linked[0];
		}

		// 2. Match by verified email on an existing WP account.
		$user = get_user_by( 'email', $email );

		if ( $user ) {
			if ( ! get_user_meta( $user->ID, self::META_GOOGLE_ID, true ) ) {
				update_user_meta( $user->ID, self::META_GOOGLE_ID, $sub );
			}
			return $user->ID;
		}

		// 3. No matching account: create one, if registration is allowed.
		if ( ! $this->registration_allowed() ) {
			return new \WP_Error( 'frbl_gsi_registration_disabled', __( 'No account was found for this Google email, and new registrations are currently disabled on this site.', 'frontblocks' ) );
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => $this->generate_username( $email ),
				'user_email'   => $email,
				'user_pass'    => wp_generate_password( 24, true, true ),
				'first_name'   => sanitize_text_field( $payload['given_name'] ?? '' ),
				'last_name'    => sanitize_text_field( $payload['family_name'] ?? '' ),
				'display_name' => sanitize_text_field( $payload['name'] ?? $email ),
			)
		);

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		update_user_meta( $user_id, self::META_GOOGLE_ID, $sub );

		if ( ! empty( $payload['picture'] ) ) {
			update_user_meta( $user_id, self::META_GOOGLE_AVATAR, esc_url_raw( $payload['picture'] ) );
		}

		/**
		 * Fires after a new WordPress user has been created via Google Sign-In.
		 *
		 * @param int   $user_id New user ID.
		 * @param array $payload Verified Google ID token payload.
		 */
		do_action( 'frbl_google_signin_user_created', $user_id, $payload );

		return $user_id;
	}

	/**
	 * Whether creating a new WP account for an unmatched Google sign-in is allowed.
	 *
	 * @return bool
	 */
	private function registration_allowed() {
		$allowed = (bool) get_option( 'users_can_register' );

		if ( ! $allowed && class_exists( 'WooCommerce' ) ) {
			$allowed = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) || 'yes' === get_option( 'woocommerce_enable_signup_from_checkout' );
		}

		/**
		 * Filter whether Google Sign-In is allowed to create new WP users.
		 *
		 * @param bool $allowed Whether registration is currently allowed.
		 */
		return (bool) apply_filters( 'frbl_google_signin_allow_registration', $allowed );
	}

	/**
	 * Generate a unique, valid WP username from an email address.
	 *
	 * @param string $email Email address.
	 * @return string
	 */
	private function generate_username( $email ) {
		$base = sanitize_user( current( explode( '@', $email ) ), true );

		if ( '' === $base ) {
			$base = 'user';
		}

		$username = $base;
		$suffix   = 1;

		while ( username_exists( $username ) ) {
			$username = $base . $suffix;
			++$suffix;
		}

		return $username;
	}

	/**
	 * Validate a client-supplied redirect target, falling back to the site home.
	 *
	 * @param string $redirect_to Requested redirect target.
	 * @return string
	 */
	private function safe_redirect( $redirect_to ) {
		if ( '' === $redirect_to ) {
			return home_url( '/' );
		}

		return wp_validate_redirect( $redirect_to, home_url( '/' ) );
	}

	/**
	 * Enqueue Google Identity Services and our bridging script/style.
	 *
	 * @return void
	 */
	private function enqueue_assets() {
		wp_enqueue_style(
			'frontblocks-google-signin',
			FRBL_PLUGIN_URL . 'assets/google-signin/frontblocks-google-signin.css',
			array(),
			FRBL_VERSION
		);

		// Version intentionally omitted: this is Google's own, self-versioned CDN script.
		wp_enqueue_script(
			'frontblocks-google-identity-services',
			'https://accounts.google.com/gsi/client',
			array(),
			null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			true
		);

		wp_enqueue_script(
			'frontblocks-google-signin',
			FRBL_PLUGIN_URL . 'assets/google-signin/frontblocks-google-signin.js',
			array( 'frontblocks-google-identity-services' ),
			FRBL_VERSION,
			true
		);
	}

	/**
	 * Localize shared Google Sign-In data onto our bridging script.
	 *
	 * @param string $redirect_to Where to send the browser after a successful sign-in.
	 * @return void
	 */
	private function localize_script( $redirect_to ) {
		wp_localize_script(
			'frontblocks-google-signin',
			'frblGoogleSignIn',
			array(
				'clientId'   => $this->options['client_id'],
				'apiUrl'     => rest_url( 'frontblocks/v1/google-signin' ),
				'redirectTo' => $redirect_to,
				'i18n'       => array(
					'error' => __( 'Could not sign in with Google. Please try again or use the form below.', 'frontblocks' ),
				),
			)
		);
	}

	/**
	 * Enqueue assets and localize the bridging script at most once per request.
	 *
	 * @param string $redirect_to Where to send the browser after a successful sign-in.
	 * @return void
	 */
	private function ensure_frontend_assets( $redirect_to ) {
		$this->enqueue_assets();

		if ( ! $this->localized ) {
			$this->localize_script( $redirect_to );
			$this->localized = true;
		}
	}

	/**
	 * Current front-end URL, validated as a safe redirect target.
	 *
	 * @return string
	 */
	private function current_url() {
		return wp_validate_redirect( esc_url_raw( add_query_arg( array() ) ), home_url( '/' ) );
	}

	/**
	 * Enqueue assets on wp-login.php.
	 *
	 * @return void
	 */
	public function enqueue_login_assets() {
		$redirect_to = admin_url();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- read-only redirect target, validated via wp_validate_redirect() below.
		if ( isset( $_GET['redirect_to'] ) ) {
			$redirect_to = wp_validate_redirect( sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ), admin_url() ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$this->ensure_frontend_assets( $redirect_to );
	}

	/**
	 * Render the Google button on wp-login.php, alongside the standard form.
	 *
	 * @return void
	 */
	public function render_wp_login_button() {
		?>
		<div class="frbl-google-signin-divider"><span><?php esc_html_e( 'or', 'frontblocks' ); ?></span></div>
		<div class="frbl-google-signin-wrapper">
			<div class="frbl-google-signin-button" data-text="signin_with"></div>
		</div>
		<?php
	}

	/**
	 * Whether Google Sign-In assets should load on the current front-end request.
	 *
	 * @return bool
	 */
	private function should_enqueue_frontend() {
		if ( is_user_logged_in() ) {
			return false;
		}

		if ( function_exists( 'is_account_page' ) && is_account_page() && ( ! empty( $this->options['enable_myaccount_login'] ) || ! empty( $this->options['enable_myaccount_register'] ) ) ) {
			return true;
		}

		if ( function_exists( 'is_checkout' ) && is_checkout() && ! empty( $this->options['enable_checkout'] ) ) {
			return true;
		}

		global $post;
		if ( $post instanceof \WP_Post ) {
			return has_block( 'frontblocks/google-login', $post ) || has_shortcode( $post->post_content, 'frontblocks_google_login' ) || has_shortcode( $post->post_content, 'frontblocks_google_register' );
		}

		return false;
	}

	/**
	 * Enqueue assets on WooCommerce My Account / Checkout pages.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		if ( ! $this->should_enqueue_frontend() ) {
			return;
		}

		$this->ensure_frontend_assets( $this->current_url() );
	}

	/**
	 * Render the Google button above the WooCommerce My Account login form.
	 *
	 * @return void
	 */
	public function render_myaccount_login_button() {
		?>
		<div class="frbl-google-signin-wrapper">
			<div class="frbl-google-signin-button" data-text="signin_with"></div>
		</div>
		<div class="frbl-google-signin-divider"><span><?php esc_html_e( 'or', 'frontblocks' ); ?></span></div>
		<?php
	}

	/**
	 * Render the Google button above the WooCommerce My Account register form.
	 *
	 * @return void
	 */
	public function render_myaccount_register_button() {
		?>
		<div class="frbl-google-signin-wrapper">
			<div class="frbl-google-signin-button" data-text="signup_with"></div>
		</div>
		<div class="frbl-google-signin-divider"><span><?php esc_html_e( 'or', 'frontblocks' ); ?></span></div>
		<?php
	}

	/**
	 * Render the Google button on WooCommerce Checkout for guests.
	 *
	 * Does not disrupt guest checkout: it only adds a sign-in option
	 * before the existing checkout form.
	 *
	 * @return void
	 */
	public function render_checkout_button() {
		if ( is_user_logged_in() ) {
			return;
		}
		?>
		<div class="frbl-google-signin-wrapper frbl-google-signin-checkout">
			<div class="frbl-google-signin-button" data-text="signin_with" data-width="300"></div>
			<div class="frbl-google-signin-divider"><span><?php esc_html_e( 'or continue below', 'frontblocks' ); ?></span></div>
		</div>
		<?php
	}

	/**
	 * Render a standalone Google button, for use by the shortcodes and the block.
	 *
	 * @param string $mode     Either 'login' or 'register'.
	 * @param string $redirect Optional redirect target after a successful sign-in.
	 * @return string
	 */
	private function render_button_html( $mode, $redirect = '' ) {
		if ( ! Settings::is_configured() ) {
			return $this->not_configured_notice();
		}

		$redirect_to = '' !== $redirect ? $this->safe_redirect( $redirect ) : $this->current_url();
		$this->ensure_frontend_assets( $redirect_to );

		$text = ( 'register' === $mode ) ? 'signup_with' : 'signin_with';

		ob_start();
		?>
		<div class="frbl-google-signin-wrapper">
			<div
				class="frbl-google-signin-button"
				data-text="<?php echo esc_attr( $text ); ?>"
				<?php echo '' !== $redirect ? 'data-redirect="' . esc_attr( $redirect_to ) . '"' : ''; ?>
			></div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Notice shown to admins (only) when the shortcode/block is used before configuration.
	 *
	 * @return string
	 */
	private function not_configured_notice() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return '';
		}

		return '<p class="frbl-google-signin-notice">' . esc_html__( 'Google Sign-In is not configured yet. Add your Client ID and Client Secret under Appearance → FrontBlocks → Google Sign-In.', 'frontblocks' ) . '</p>';
	}

	/**
	 * [frontblocks_google_login] shortcode: "Sign in with Google" button.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function shortcode_login( $atts ) {
		return $this->render_shortcode( 'login', $atts );
	}

	/**
	 * [frontblocks_google_register] shortcode: "Sign up with Google" button.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function shortcode_register( $atts ) {
		return $this->render_shortcode( 'register', $atts );
	}

	/**
	 * Shared shortcode handler.
	 *
	 * @param string       $mode Either 'login' or 'register'.
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	private function render_shortcode( $mode, $atts ) {
		$atts = shortcode_atts( array( 'redirect' => '' ), (array) $atts, 'frontblocks_google_' . $mode );

		return $this->render_button_html( $mode, $atts['redirect'] );
	}

	/**
	 * Register the "Google Login" block.
	 *
	 * @return void
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'frontblocks-google-login-block-editor',
			FRBL_PLUGIN_URL . 'assets/google-signin/frontblocks-google-signin-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
			FRBL_VERSION,
			true
		);

		if ( version_compare( get_bloginfo( 'version' ), '5.5', '>=' ) ) {
			register_block_type( FRBL_PLUGIN_PATH . 'includes/GoogleSignIn/block', array( 'render_callback' => array( $this, 'render_block' ) ) );
			return;
		}

		register_block_type(
			'frontblocks/google-login',
			array(
				'editor_script'   => 'frontblocks-google-login-block-editor',
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => $this->get_block_attributes(),
			)
		);
	}

	/**
	 * Return Google Login block attributes for pre-metadata WordPress versions.
	 *
	 * @return array
	 */
	private function get_block_attributes() {
		return array(
			'mode'     => array(
				'type'    => 'string',
				'default' => 'login',
			),
			'redirect' => array(
				'type'    => 'string',
				'default' => '',
			),
		);
	}

	/**
	 * Render callback for the "Google Login" block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_block( $attributes ) {
		$mode     = ( isset( $attributes['mode'] ) && 'register' === $attributes['mode'] ) ? 'register' : 'login';
		$redirect = isset( $attributes['redirect'] ) ? (string) $attributes['redirect'] : '';

		$content = $this->render_button_html( $mode, $redirect );
		if ( function_exists( 'get_block_wrapper_attributes' ) ) {
			return sprintf( '<div %1$s>%2$s</div>', get_block_wrapper_attributes(), $content );
		}

		$align = isset( $attributes['align'] ) ? sanitize_key( $attributes['align'] ) : '';

		return sprintf( '<div class="%1$s">%2$s</div>', $align ? 'align' . esc_attr( $align ) : '', $content );
	}
}
