<?php
/**
 * Settings page
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Settings class
 */
class Settings {

	/**
	 * Option key for testimonials feature.
	 *
	 * @var string
	 */
	private $option_enable_testimonials = 'enable_testimonials';

	/**
	 * Option key for Gutenberg in products (PRO).
	 *
	 * @var string
	 */
	private $option_enable_gutenberg = 'enable_gutenberg';

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	private $page_slug = 'frontblocks-settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
	}

	/**
	 * Enqueue admin styles for settings page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_styles( $hook ) {
		if ( 'appearance_page_' . $this->page_slug !== $hook ) {
			return;
		}

		// Enqueue custom settings page styles with Tailwind compiled.
		wp_enqueue_style(
			'frontblocks-admin-settings',
			FRBL_PLUGIN_URL . 'assets/admin/settings.css',
			array(),
			FRBL_VERSION
		);
	}

	/**
	 * Register options page under Appearance.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_theme_page(
			__( 'FrontBlocks Settings', 'frontblocks' ),
			__( 'FrontBlocks', 'frontblocks' ),
			'edit_theme_options',
			$this->page_slug,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register settings, sections and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'frontblocks_settings',
			'frontblocks_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'frontblocks_section_features',
			__( 'Features', 'frontblocks' ),
			function () {
				echo '<p>' . esc_html__( 'Visual enhancements for your website.', 'frontblocks' ) . '</p>';
			},
			$this->page_slug
		);

		add_settings_field(
			$this->option_enable_testimonials,
			__( 'Enable testimonials', 'frontblocks' ),
			array( $this, 'field_enable_testimonials' ),
			$this->page_slug,
			'frontblocks_section_features'
		);

		// PRO Features section.
		add_settings_section(
			'frontblocks_section_woocommerce_features',
			__( 'PRO Features', 'frontblocks' ),
			array( $this, 'section_pro_features_callback' ),
			$this->page_slug
		);

		add_settings_field(
			$this->option_enable_gutenberg,
			__( 'Enable Gutenberg in Products', 'frontblocks' ),
			array( $this, 'field_enable_gutenberg' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		do_action( 'frontblocks_register_settings' );
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		?>
		<div class="frbl-settings-wrapper tw-min-h-screen tw-bg-gray-50 tw-py-8">
			<div class="tw-max-w-5xl tw-mx-auto tw-px-4 sm:tw-px-6 lg:tw-px-8">
				<!-- Header Section -->
				<div class="tw-mb-8 frbl-animate-slide-in">
					<div class="tw-flex tw-items-center tw-justify-between">
						<div>
							<h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-mb-2">
								<?php echo esc_html__( 'FrontBlocks Settings', 'frontblocks' ); ?>
							</h1>
							<p class="tw-text-gray-600">
								<?php echo esc_html__( 'Add visual enhancements to your website with FrontBlocks.', 'frontblocks' ); ?>
							</p>
						</div>
						<div class="tw-flex tw-items-center tw-space-x-2">
							<span class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-bg-primary-100 tw-text-primary-700">
								<?php echo esc_html__( 'Version', 'frontblocks' ) . ' ' . esc_html( FRBL_VERSION ); ?>
							</span>
						</div>
					</div>
				</div>

				<!-- Settings Form -->
				<form method="post" action="options.php" class="tw-space-y-6">
					<?php settings_fields( 'frontblocks_settings' ); ?>

					<?php
					// Get all sections for this page.
					global $wp_settings_sections, $wp_settings_fields;

					if ( ! isset( $wp_settings_sections[ $this->page_slug ] ) ) {
						return;
					}

					foreach ( (array) $wp_settings_sections[ $this->page_slug ] as $section ) {
						$this->render_settings_section( $section );
					}
					?>

					<!-- Submit Button -->
					<div class="tw-flex tw-items-center tw-justify-between tw-pt-6 tw-border-t tw-border-gray-200">
						<div class="tw-text-sm tw-text-gray-500">
							<?php echo esc_html__( 'Changes will be applied immediately after saving.', 'frontblocks' ); ?>
						</div>
						<button type="submit" class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-lg tw-shadow-sm tw-text-white tw-bg-primary-500 hover:tw-bg-primary-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-primary-500 tw-transition-colors tw-duration-200">
							<svg class="tw-w-5 tw-h-5 tw-mr-2 tw--ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
							</svg>
							<?php echo esc_html__( 'Save Settings', 'frontblocks' ); ?>
						</button>
					</div>
				</form>

				<!-- Footer Info -->
				<div class="tw-mt-8 tw-text-center tw-text-sm tw-text-gray-500">
					<?php
					printf(
						/* translators: %s: Close·marketing link */
						esc_html__( 'Made with ❤️ by %s', 'frontblocks' ),
						'<a href="https://close.technology/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings" target="_blank" rel="noopener noreferrer" class="tw-text-primary-500 hover:tw-text-primary-600 tw-font-medium">Close·Technology</a>'
					);
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single settings section as a card.
	 *
	 * @param array $section Section data.
	 * @return void
	 */
	private function render_settings_section( $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[ $this->page_slug ][ $section['id'] ] ) ) {
			return;
		}
		?>
		<div class="frbl-card tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden frbl-animate-slide-in">
			<div class="tw-px-6 tw-py-5 tw-border-b tw-border-gray-200 tw-bg-gradient-to-r tw-from-gray-50 tw-to-white">
				<h2 class="tw-text-xl tw-font-semibold tw-text-gray-900">
					<?php echo esc_html( $section['title'] ); ?>
				</h2>
				<?php
				if ( $section['callback'] ) {
					echo '<div class="tw-mt-2 tw-text-sm tw-text-gray-600">';
					call_user_func( $section['callback'], $section );
					echo '</div>';
				}
				?>
			</div>
			<div class="tw-px-6 tw-py-5">
				<div class="tw-space-y-6">
					<?php
					foreach ( (array) $wp_settings_fields[ $this->page_slug ][ $section['id'] ] as $field ) {
						$this->render_settings_field( $field );
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single settings field.
	 *
	 * @param array $field Field data.
	 * @return void
	 */
	private function render_settings_field( $field ) {
		?>
		<div class="tw-flex tw-items-start">
			<div class="tw-flex-grow">
				<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">
					<?php echo esc_html( $field['title'] ); ?>
				</label>
				<div class="tw-mt-1">
					<?php call_user_func( $field['callback'], $field['args'] ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * PRO Features section description.
	 *
	 * @return void
	 */
	public function section_pro_features_callback() {
		if ( ! frbl_is_pro_active() ) {
			echo '<div class="tw-bg-blue-50 tw-border-l-4 tw-border-blue-400 tw-p-4 tw-mb-4">';
			echo '<div class="tw-flex">';
			echo '<div class="tw-flex-shrink-0">';
			echo '<svg class="tw-h-5 tw-w-5 tw-text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';
			echo '</div>';
			echo '<div class="tw-ml-3">';
			echo '<p class="tw-text-sm tw-text-blue-700">';
			printf(
				/* translators: %s: FrontBlocks PRO link */
				esc_html__( 'These features require %s. Upgrade to unlock advanced functionality.', 'frontblocks' ),
				'<a href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings" target="_blank" class="tw-font-medium tw-underline">FrontBlocks PRO</a>'
			);
			echo '</p>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		} else {
			echo '<p>' . esc_html__( 'Advanced features for WooCommerce and more.', 'frontblocks' ) . '</p>';
		}
	}

	/**
	 * Render toggle field for enable testimonials.
	 *
	 * @return void
	 */
	public function field_enable_testimonials() {
		$options = get_option( 'frontblocks_settings', array() );
		$enabled = (bool) ( $options[ $this->option_enable_testimonials ] ?? false );
		?>
		<div class="tw-flex tw-items-center tw-justify-between">
			<div class="tw-flex-grow">
				<p class="tw-mt-1 tw-text-sm tw-text-gray-500">
					<?php echo esc_html__( 'Create and manage testimonials in the WordPress admin.', 'frontblocks' ); ?>
				</p>
			</div>
			<label class="frbl-toggle">
				<input type="checkbox" 
					id="<?php echo esc_attr( $this->option_enable_testimonials ); ?>" 
					name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_testimonials ); ?>]" 
					value="1" 
					<?php checked( true, $enabled ); ?>
				/>
				<span></span>
			</label>
		</div>
		<?php
	}

	/**
	 * Render toggle field for enable Gutenberg in products (PRO).
	 *
	 * @return void
	 */
	public function field_enable_gutenberg() {
		$options     = get_option( 'frontblocks_settings', array() );
		$enabled     = (bool) ( $options[ $this->option_enable_gutenberg ] ?? false );
		$is_pro      = frbl_is_pro_active();
		$disabled    = ! $is_pro ? 'disabled' : '';
		$opacity_cls = ! $is_pro ? 'tw-opacity-50' : '';
		?>
		<div class="tw-flex tw-items-center tw-justify-between <?php echo esc_attr( $opacity_cls ); ?>">
			<div class="tw-flex-grow tw-pr-4">
				<div class="tw-flex tw-items-center tw-gap-2">
					<p class="tw-text-sm tw-text-gray-500">
						<?php echo esc_html__( 'Use the Gutenberg block editor for WooCommerce products.', 'frontblocks' ); ?>
					</p>
					<?php if ( ! $is_pro ) : ?>
						<a href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-badge" 
							target="_blank" 
							rel="noopener noreferrer"
							class="tw-inline-flex tw-items-center tw-px-3 tw-py-1 tw-ml-2 tw-rounded tw-text-xs tw-font-semibold tw-bg-primary-100 tw-text-primary-700 hover:tw-bg-primary-200 tw-transition-colors tw-no-underline"
							title="<?php echo esc_attr__( 'Upgrade to FrontBlocks PRO', 'frontblocks' ); ?>">
							PRO
						</a>
					<?php endif; ?>
				</div>
			</div>
			<label class="frbl-toggle">
				<input type="checkbox" 
					id="<?php echo esc_attr( $this->option_enable_gutenberg ); ?>" 
					name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_gutenberg ); ?>]" 
					value="1" 
					<?php checked( true, $enabled ); ?>
					<?php echo esc_attr( $disabled ); ?>
				/>
				<span></span>
			</label>
		</div>
		<?php
	}

	/**
	 * Sanitize settings array.
	 *
	 * @param array $value Raw value.
	 * @return array
	 */
	public function sanitize_settings( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $value as $key => $val ) {
			if ( $this->option_enable_testimonials === $key || $this->option_enable_gutenberg === $key ) {
				$sanitized[ $key ] = (bool) $val;
			} else {
				$sanitized[ $key ] = sanitize_text_field( $val );
			}
		}

		return $sanitized;
	}
}
