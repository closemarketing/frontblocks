<?php
/**
 * Google Sign-In settings section.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\GoogleSignIn;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and handles the "Google Sign-In" settings section on the
 * main FrontBlocks settings page.
 */
class Settings {

	/**
	 * Option name storing all Google Sign-In settings.
	 *
	 * @var string
	 */
	const OPTION = 'frbl_google_signin_settings';

	/**
	 * Nonce action for the settings form.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'frbl_google_signin_settings_save';

	/**
	 * FrontBlocks settings page slug (registered in Admin\Settings).
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'frontblocks-settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'frontblocks_register_settings', array( $this, 'register_section' ) );
		add_action( 'admin_init', array( $this, 'maybe_save' ) );
	}

	/**
	 * Get Google Sign-In settings, merged with defaults.
	 *
	 * @return array
	 */
	public static function get_options() {
		$defaults = array(
			'client_id'                 => '',
			'client_secret'             => '',
			'enable_wp_login'           => false,
			'enable_myaccount_login'    => false,
			'enable_myaccount_register' => false,
			'enable_checkout'           => false,
		);

		$saved = get_option( self::OPTION, array() );

		return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
	}

	/**
	 * Whether Client ID and Secret are both configured.
	 *
	 * @return bool
	 */
	public static function is_configured() {
		$options = self::get_options();

		return '' !== trim( (string) $options['client_id'] ) && '' !== trim( (string) $options['client_secret'] );
	}

	/**
	 * Register the settings section (rendered as a self-contained form).
	 *
	 * @return void
	 */
	public function register_section() {
		add_settings_section(
			'frontblocks_section_google_signin',
			__( 'Google Sign-In', 'frontblocks' ),
			array( $this, 'render_section' ),
			self::PAGE_SLUG
		);
	}

	/**
	 * Handle the settings form submission.
	 *
	 * @return void
	 */
	public function maybe_save() {
		if ( ! isset( $_POST['frbl_google_signin_nonce'] ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['frbl_google_signin_nonce'] ) ), self::NONCE_ACTION ) ) {
			wp_safe_redirect( add_query_arg( 'gsi-error', rawurlencode( __( 'Security check failed. Please try again.', 'frontblocks' ) ), $this->settings_url() ) );
			exit;
		}

		$existing = self::get_options();

		$submitted_secret = isset( $_POST['frbl_gsi_client_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['frbl_gsi_client_secret'] ) ) : '';

		$new = array(
			'client_id'                 => isset( $_POST['frbl_gsi_client_id'] ) ? sanitize_text_field( wp_unslash( $_POST['frbl_gsi_client_id'] ) ) : '',
			// Keep the stored secret if the field was left blank (so the admin doesn't have to re-paste it every time).
			'client_secret'             => '' !== $submitted_secret ? $submitted_secret : $existing['client_secret'],
			'enable_wp_login'           => isset( $_POST['frbl_gsi_enable_wp_login'] ),
			'enable_myaccount_login'    => isset( $_POST['frbl_gsi_enable_myaccount_login'] ),
			'enable_myaccount_register' => isset( $_POST['frbl_gsi_enable_myaccount_register'] ),
			'enable_checkout'           => isset( $_POST['frbl_gsi_enable_checkout'] ),
		);

		update_option( self::OPTION, $new );

		wp_safe_redirect( add_query_arg( 'gsi-updated', 'true', $this->settings_url() ) );
		exit;
	}

	/**
	 * URL of the FrontBlocks settings page.
	 *
	 * @return string
	 */
	private function settings_url() {
		return admin_url( 'themes.php?page=' . self::PAGE_SLUG );
	}

	/**
	 * Render the settings section.
	 *
	 * @return void
	 */
	public function render_section() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		$options = self::get_options();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only status flags, no data processed here.
		if ( isset( $_GET['gsi-updated'] ) && 'true' === $_GET['gsi-updated'] ) {
			echo '<div class="tw:mb-4 tw:p-3 tw:rounded-lg tw:bg-green-50 tw:border tw:border-green-200 tw:text-sm tw:text-green-700">' . esc_html__( 'Google Sign-In settings saved.', 'frontblocks' ) . '</div>';
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only status flag.
		if ( isset( $_GET['gsi-error'] ) ) {
			echo '<div class="tw:mb-4 tw:p-3 tw:rounded-lg tw:bg-red-50 tw:border tw:border-red-200 tw:text-sm tw:text-red-700">' . esc_html( sanitize_text_field( wp_unslash( $_GET['gsi-error'] ) ) ) . '</div>'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		?>
		<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
			<?php esc_html_e( 'Let visitors sign in or register with their Google account instead of, or alongside, a WordPress username and password. The button stays hidden everywhere until a Client ID and Client Secret are set below.', 'frontblocks' ); ?>
		</p>

		<form method="post" action="<?php echo esc_url( $this->settings_url() ); ?>" class="tw:space-y-6 tw:bg-white tw:border tw:border-gray-200 tw:rounded-lg tw:p-6">
			<?php wp_nonce_field( self::NONCE_ACTION, 'frbl_google_signin_nonce' ); ?>

			<div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-6">
				<div>
					<label for="frbl_gsi_client_id" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
						<?php esc_html_e( 'Google Client ID', 'frontblocks' ); ?>
					</label>
					<input
						type="text"
						id="frbl_gsi_client_id"
						name="frbl_gsi_client_id"
						value="<?php echo esc_attr( $options['client_id'] ); ?>"
						class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-sm"
						placeholder="1234567890-abcdefg.apps.googleusercontent.com"
						autocomplete="off"
					/>
				</div>
				<div>
					<label for="frbl_gsi_client_secret" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
						<?php esc_html_e( 'Google Client Secret', 'frontblocks' ); ?>
					</label>
					<input
						type="password"
						id="frbl_gsi_client_secret"
						name="frbl_gsi_client_secret"
						value=""
						class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-sm"
						placeholder="<?php echo esc_attr( $options['client_secret'] ? __( 'Leave blank to keep the current secret', 'frontblocks' ) : '' ); ?>"
						autocomplete="new-password"
					/>
				</div>
			</div>

			<div>
				<span class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-3">
					<?php esc_html_e( 'Show the "Sign in with Google" button on:', 'frontblocks' ); ?>
				</span>
				<div class="tw:grid tw:grid-cols-1 tw:sm:grid-cols-2 tw:gap-3">
					<?php
					$this->toggle_row( 'frbl_gsi_enable_wp_login', $options['enable_wp_login'], __( 'wp-admin login screen', 'frontblocks' ) );
					$this->toggle_row( 'frbl_gsi_enable_myaccount_login', $options['enable_myaccount_login'], __( 'WooCommerce My Account — Login', 'frontblocks' ) );
					$this->toggle_row( 'frbl_gsi_enable_myaccount_register', $options['enable_myaccount_register'], __( 'WooCommerce My Account — Register', 'frontblocks' ) );
					$this->toggle_row( 'frbl_gsi_enable_checkout', $options['enable_checkout'], __( 'WooCommerce Checkout (guests)', 'frontblocks' ) );
					?>
				</div>
				<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
					<p class="tw:text-xs tw:text-gray-500 tw:mt-2">
						<?php esc_html_e( 'WooCommerce is not active, so the My Account and Checkout options above will have no effect until it is installed.', 'frontblocks' ); ?>
					</p>
				<?php endif; ?>
			</div>

			<div class="tw:bg-gray-50 tw:border tw:border-gray-200 tw:rounded-lg tw:p-4">
				<p class="tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
					<?php esc_html_e( 'Google Cloud Console setup', 'frontblocks' ); ?>
				</p>
				<p class="tw:text-xs tw:text-gray-500 tw:mb-3">
					<?php esc_html_e( 'Create an OAuth 2.0 Client ID of type "Web application" and add these values, then paste the Client ID and Client Secret above.', 'frontblocks' ); ?>
				</p>
				<label class="tw:block tw:text-xs tw:font-medium tw:text-gray-500 tw:mb-1">
					<?php esc_html_e( 'Authorized JavaScript origin', 'frontblocks' ); ?>
				</label>
				<input type="text" readonly onclick="this.select();" value="<?php echo esc_attr( home_url( '/' ) ); ?>" class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:bg-white tw:text-sm tw:mb-3" />
				<label class="tw:block tw:text-xs tw:font-medium tw:text-gray-500 tw:mb-1">
					<?php esc_html_e( 'Authorized redirect URI', 'frontblocks' ); ?>
				</label>
				<input type="text" readonly onclick="this.select();" value="<?php echo esc_attr( home_url( '/wp-login.php' ) ); ?>" class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:bg-white tw:text-sm" />
			</div>

			<div class="tw:flex tw:justify-end">
				<button type="submit" class="tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:border tw:border-transparent tw:text-sm tw:font-medium tw:rounded-lg tw:text-white tw:bg-primary-500 tw:hover:bg-primary-600">
					<?php esc_html_e( 'Save Google Sign-In settings', 'frontblocks' ); ?>
				</button>
			</div>
		</form>
		<?php
	}

	/**
	 * Render a single toggle row.
	 *
	 * @param string $name    Checkbox field name.
	 * @param bool   $checked Whether the checkbox is checked.
	 * @param string $label   Row label.
	 * @return void
	 */
	private function toggle_row( $name, $checked, $label ) {
		?>
		<div class="tw:flex tw:items-center tw:justify-between tw:gap-3 tw:p-3 tw:border tw:border-gray-200 tw:rounded-lg">
			<span class="tw:text-sm tw:text-gray-700"><?php echo esc_html( $label ); ?></span>
			<label class="frbl-toggle">
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( true, (bool) $checked ); ?> />
				<span></span>
			</label>
		</div>
		<?php
	}
}
