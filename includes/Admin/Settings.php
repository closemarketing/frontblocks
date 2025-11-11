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

use FrontBlocks\Admin\UI;

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
	 * Option key for reading progress bar feature.
	 *
	 * @var string
	 */
	private $option_enable_reading_progress = 'enable_reading_progress';

	/**
	 * Option key for back button feature.
	 *
	 * @var string
	 */
	private $option_enable_back_button = 'enable_back_button';

	/**
	 * Option key for Gutenberg in products (PRO).
	 *
	 * @var string
	 */
	private $option_enable_gutenberg = 'enable_gutenberg';

	/**
	 * Option key for Simple Prices Variable Products (PRO).
	 *
	 * @var string
	 */
	private $option_enable_simple_prices_variable_products = 'enable_simple_prices_variable_products';

	/**
	 * Option key for After Add to Cart Block (PRO).
	 *
	 * @var string
	 */
	private $option_enable_after_add_to_cart = 'enable_after_add_to_cart';

	/**
	 * Option key for deactivate short description (PRO).
	 *
	 * @var string
	 */
	private $option_deactivate_short_description = 'deactivate_short_description';

	/**
	 * Option key for move content to short description (PRO).
	 *
	 * @var string
	 */
	private $option_move_content_to_short_description = 'move_content_to_short_description';

	/**
	 * Option key for disable zoom in WooCommerce images (PRO).
	 *
	 * @var string
	 */
	private $option_disable_zoom_images = 'disable_zoom_images';

	/**
	 * Option key for add share buttons in product page (PRO).
	 *
	 * @var string
	 */
	private $option_add_share_buttons = 'add_share_buttons';

	/**
	 * Option key for deactivate product tabs (PRO).
	 *
	 * @var string
	 */
	private $option_deactivate_product_tabs = 'deactivate_product_tabs';

	/**
	 * Option key for horizontal product form layout (PRO).
	 *
	 * @var string
	 */
	private $option_horizontal_product_form = 'horizontal_product_form';

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	private $page_slug = 'frontblocks-settings';

	/**
	 * Is license valid.
	 *
	 * @var bool
	 */
	private $is_license_valid = false;

	/**
	 * Option key for license key.
	 *
	 * @var string
	 */
	private $option_license_key;

	/**
	 * Option key for product ID.
	 *
	 * @var string
	 */
	private $option_product_id;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $frontblocks_pro_license;
		$this->is_license_valid = ! empty( $frontblocks_pro_license ) && $frontblocks_pro_license->get_api_key_status( true );

		$this->option_license_key = ! empty( $frontblocks_pro_license ) ? $frontblocks_pro_license->get_option_key( 'apikey' ) : '';
		$this->option_product_id  = ! empty( $frontblocks_pro_license ) ? $frontblocks_pro_license->get_option_key( 'product_id' ) : '';

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

		wp_enqueue_style(
			'frontblocks-admin-settings',
			FRBL_PLUGIN_URL . 'assets/admin/settings.css',
			array(),
			FRBL_VERSION
		);

		wp_add_inline_script(
			'jquery',
			"
			document.addEventListener('DOMContentLoaded', function() {
				const deactivateCheckbox = document.getElementById('deactivate_short_description');
				const moveContentCheckbox = document.getElementById('move_content_to_short_description');
				
				if (!deactivateCheckbox || !moveContentCheckbox) return;
				
				function updateMutualExclusion() {
					const deactivateWrapper = deactivateCheckbox.closest('.tw-flex');
					const moveContentWrapper = moveContentCheckbox.closest('.tw-flex');
					
					// Check if license is valid (not just PRO active).
					const isLicenseValid = " . ( $this->is_license_valid ? 'true' : 'false' ) . ";
					
					if (deactivateCheckbox.checked) {
						moveContentCheckbox.disabled = true;
						if (moveContentWrapper) {
							moveContentWrapper.style.opacity = '0.5';
							moveContentWrapper.style.filter = 'grayscale(100%)';
							const toggle = moveContentWrapper.querySelector('.frbl-toggle');
							if (toggle) {
								toggle.style.borderColor = '#ef4444';
								toggle.style.opacity = '0.7';
							}
						}
					} else {
						moveContentCheckbox.disabled = !isLicenseValid;
						if (moveContentWrapper) {
							moveContentWrapper.style.opacity = isLicenseValid ? '1' : '0.5';
							moveContentWrapper.style.filter = '';
							const toggle = moveContentWrapper.querySelector('.frbl-toggle');
							if (toggle) {
								toggle.style.borderColor = '';
								toggle.style.opacity = '';
							}
						}
					}
					
					if (moveContentCheckbox.checked) {
						deactivateCheckbox.disabled = true;
						if (deactivateWrapper) {
							deactivateWrapper.style.opacity = '0.5';
							deactivateWrapper.style.filter = 'grayscale(100%)';
							const toggle = deactivateWrapper.querySelector('.frbl-toggle');
							if (toggle) {
								toggle.style.borderColor = '#ef4444';
								toggle.style.opacity = '0.7';
							}
						}
					} else {
						deactivateCheckbox.disabled = !isLicenseValid;
						if (deactivateWrapper) {
							deactivateWrapper.style.opacity = isLicenseValid ? '1' : '0.5';
							deactivateWrapper.style.filter = '';
							const toggle = deactivateWrapper.querySelector('.frbl-toggle');
							if (toggle) {
								toggle.style.borderColor = '';
								toggle.style.opacity = '';
							}
						}
					}
				}
				
				deactivateCheckbox.addEventListener('change', updateMutualExclusion);
				moveContentCheckbox.addEventListener('change', updateMutualExclusion);
				
				updateMutualExclusion();
			});
			"
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
		global $frontblocks_pro_license;
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
			array( $this, 'section_features_callback' ),
			$this->page_slug
		);

		add_settings_field(
			$this->option_enable_testimonials,
			__( 'Enable testimonials', 'frontblocks' ),
			array( $this, 'field_enable_testimonials' ),
			$this->page_slug,
			'frontblocks_section_features'
		);

		add_settings_field(
			$this->option_enable_reading_progress,
			__( 'Enable reading progress bar', 'frontblocks' ),
			array( $this, 'field_enable_reading_progress' ),
			$this->page_slug,
			'frontblocks_section_features'
		);

		add_settings_field(
			$this->option_enable_back_button,
			__( 'Enable Back Button', 'frontblocks' ),
			array( $this, 'field_enable_back_button' ),
			$this->page_slug,
			'frontblocks_section_features'
		);

		// PRO Features section.
		add_settings_section(
			'frontblocks_section_woocommerce_features',
			__( 'WooCommerce Features', 'frontblocks' ),
			array( $this, 'section_woo_features_callback' ),
			$this->page_slug
		);

		add_settings_field(
			$this->option_enable_gutenberg,
			__( 'Enable Gutenberg in Products', 'frontblocks' ),
			array( $this, 'field_enable_gutenberg' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_enable_simple_prices_variable_products,
			__( 'Enable Simple Prices Variable Products', 'frontblocks' ),
			array( $this, 'field_enable_simple_prices_variable_products' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_enable_after_add_to_cart,
			__( 'Enable After Add to Cart Block', 'frontblocks' ),
			array( $this, 'field_enable_after_add_to_cart' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_deactivate_short_description,
			__( 'Deactivate Short Description', 'frontblocks' ),
			array( $this, 'field_deactivate_short_description' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_move_content_to_short_description,
			__( 'Move Content to Short Description', 'frontblocks' ),
			array( $this, 'field_move_content_to_short_description' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_disable_zoom_images,
			__( 'Disable Zoom in Product Images', 'frontblocks' ),
			array( $this, 'field_disable_zoom_images' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_add_share_buttons,
			__( 'Add Share Buttons in Product Page', 'frontblocks' ),
			array( $this, 'field_add_share_buttons' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_deactivate_product_tabs,
			__( 'Deactivate Product Tabs', 'frontblocks' ),
			array( $this, 'field_deactivate_product_tabs' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_horizontal_product_form,
			__( 'Horizontal Product Form Layout', 'frontblocks' ),
			array( $this, 'field_horizontal_product_form' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		// License section (only if PRO is active).
		if ( frbl_is_pro_active() && ! empty( $frontblocks_pro_license ) ) {
			add_settings_section(
				'frontblocks_section_license',
				__( 'License', 'frontblocks' ),
				array( $this, 'section_license_callback' ),
				$this->page_slug
			);

			add_settings_field(
				$frontblocks_pro_license->get_option_key( 'apikey' ),
				__( 'License Information', 'frontblocks' ),
				array( $this, 'field_license_key' ),
				$this->page_slug,
				'frontblocks_section_license'
			);
		}

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

				<?php
				// Show success message after settings are saved.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['settings-updated'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) ) :
					?>
					<div style="background-color: #f0fdf4; border-left: 4px solid #4ade80; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
						<div class="tw-flex">
							<div class="tw-flex-shrink-0">
								<svg class="tw-h-5 tw-w-5" style="color: #4ade80;" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
								</svg>
							</div>
							<div class="tw-ml-3">
								<p class="tw-text-sm tw-font-medium" style="color: #15803d; margin: 0;">
									<?php esc_html_e( 'Changes saved successfully', 'frontblocks' ); ?>
								</p>
							</div>
						</div>
					</div>
					<?php
				endif;

				// Show error message if saving failed.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['settings-error'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['settings-error'] ) ) ) :
					?>
					<div style="background-color: #fef2f2; border-left: 4px solid #f87171; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
						<div class="tw-flex">
							<div class="tw-flex-shrink-0">
								<svg class="tw-h-5 tw-w-5" style="color: #f87171;" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
								</svg>
							</div>
							<div class="tw-ml-3">
								<p class="tw-text-sm tw-font-medium" style="color: #991b1b; margin: 0;">
									<?php esc_html_e( 'Failed to save changes. Please try again.', 'frontblocks' ); ?>
								</p>
							</div>
						</div>
					</div>
					<?php
				endif;
				?>

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
						<button type="submit" class="tw-inline-flex tw-items-center tw-px-4 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-lg tw-shadow-sm tw-text-white tw-bg-primary-500 hover:tw-bg-primary-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-primary-500 tw-transition-colors tw-duration-200">
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
	 * Section features callback.
	 *
	 * @return void
	 */
	private function section_features_callback() {
		echo '<p>' . esc_html__( 'Visual enhancements for your website.', 'frontblocks' ) . '</p>';
		?>
		<div class="frbl-features-grid">
			<?php
			UI::show_feature( 'animations' );
			UI::show_feature( 'animations' );
			UI::show_feature( 'animations' );
			?>
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

		// Check if this is the license section - render it full width.
		$is_license_section = 'frontblocks_section_license' === $section['id'];

		if ( $is_license_section ) {
			// Render license section as a full-width card.
			?>
			<div class="frbl-card tw-bg-white tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden frbl-animate-slide-in tw-mb-8">
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
					<?php
					foreach ( (array) $wp_settings_fields[ $this->page_slug ][ $section['id'] ] as $field ) {
						call_user_func( $field['callback'], $field['args'] );
					}
					?>
				</div>
			</div>
			<?php
		} else {
			// Render regular sections with feature grid.
			?>
			<div class="frbl-section-wrapper">
				<!-- Section Header -->
				<div class="frbl-section-header">
					<h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-2">
						<?php echo esc_html( $section['title'] ); ?>
					</h2>
					<?php
					if ( $section['callback'] ) {
						echo '<div class="tw-text-sm tw-text-gray-600">';
						call_user_func( $section['callback'], $section );
						echo '</div>';
					}
					?>
				</div>
				
				<!-- Features Grid -->
				<div class="frbl-features-grid">
					<?php
					foreach ( (array) $wp_settings_fields[ $this->page_slug ][ $section['id'] ] as $field ) {
						$this->render_settings_field( $field );
					}
					?>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Render a single settings field as a card.
	 *
	 * @param array $field Field data.
	 * @return void
	 */
	private function render_settings_field( $field ) {
		// Determine if this is a PRO feature (always, regardless of license status).
		$is_pro_feature = in_array(
			$field['id'],
			array(
				$this->option_enable_gutenberg,
				$this->option_enable_simple_prices_variable_products,
				$this->option_enable_after_add_to_cart,
				$this->option_deactivate_short_description,
				$this->option_move_content_to_short_description,
				$this->option_disable_zoom_images,
				$this->option_add_share_buttons,
				$this->option_deactivate_product_tabs,
				$this->option_horizontal_product_form,
			),
			true
		);

		// Apply PRO styling only if license is not valid.
		$needs_license = $is_pro_feature && ! $this->is_license_valid;

		// Get icon for this feature.
		$icon = $this->get_feature_icon( $field['id'] );

		?>
		<div class="frbl-feature-card <?php echo $needs_license ? 'frbl-feature-pro' : ''; ?>">
			<?php if ( $is_pro_feature ) : ?>
				<div class="frbl-pro-badge">PRO</div>
			<?php endif; ?>
			
			<div class="frbl-feature-content">
				<div class="frbl-feature-icon">
					<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="frbl-feature-info">
					<h3 class="frbl-feature-title">
						<?php echo esc_html( $field['title'] ); ?>
					</h3>
				</div>
				<div class="frbl-feature-toggle">
					<?php call_user_func( $field['callback'], $field['args'] ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get icon SVG for a feature.
	 *
	 * @param string $field_id Field ID.
	 * @return string SVG icon markup.
	 */
	private function get_feature_icon( $field_id ) {
		// Testimonials icon.
		$testimonials_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>';

		// Reading progress icon.
		$reading_progress_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';

		// Back button icon.
		$back_button_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>';

		// Gutenberg icon.
		$gutenberg_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';

		// Simple prices icon.
		$simple_prices_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

		// After add to cart icon.
		$after_cart_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>';

		// Deactivate description icon.
		$deactivate_desc_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';

		// Move content icon.
		$move_content_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7h12M8 12h12m-12 5h12m-15-9v.01M5 12v.01M5 19v.01"/></svg>';

		// Disable zoom icon.
		$disable_zoom_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';

		// Share buttons icon.
		$share_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>';

		// Deactivate tabs icon.
		$deactivate_tabs_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>';

		// Horizontal form icon.
		$horizontal_form_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>';

		// Default icon.
		$default_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';

		$icons = array(
			$this->option_enable_testimonials          => $testimonials_icon,
			$this->option_enable_reading_progress      => $reading_progress_icon,
			$this->option_enable_back_button           => $back_button_icon,
			$this->option_enable_gutenberg             => $gutenberg_icon,
			$this->option_enable_simple_prices_variable_products => $simple_prices_icon,
			$this->option_enable_after_add_to_cart     => $after_cart_icon,
			$this->option_deactivate_short_description => $deactivate_desc_icon,
			$this->option_move_content_to_short_description => $move_content_icon,
			$this->option_disable_zoom_images          => $disable_zoom_icon,
			$this->option_add_share_buttons            => $share_icon,
			$this->option_deactivate_product_tabs      => $deactivate_tabs_icon,
			$this->option_horizontal_product_form      => $horizontal_form_icon,
		);

		return $icons[ $field_id ] ?? $default_icon;
	}

	/**
	 * PRO Features section description.
	 *
	 * @return void
	 */
	public function section_woo_features_callback() {
		global $frontblocks_pro_license;
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
		} elseif ( ! $this->is_license_valid ) {
			echo '<div class="tw-bg-yellow-50 tw-border-l-4 tw-border-yellow-400 tw-p-4 tw-mb-4">';
			echo '<div class="tw-flex">';
			echo '<div class="tw-flex-shrink-0">';
			echo '<svg class="tw-h-5 tw-w-5 tw-text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';
			echo '</div>';
			echo '<div class="tw-ml-3">';
			echo '<p class="tw-text-sm tw-text-yellow-700">';
			printf(
				/* translators: %s: License section link */
				esc_html__( 'License is not activated. Please activate your license in the %s section below to enable these features.', 'frontblocks' ),
				'<a href="#frontblocks_section_license" class="tw-font-medium tw-underline">' . esc_html__( 'License', 'frontblocks' ) . '</a>'
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
		<label class="frbl-toggle">
			<input type="checkbox" 
				id="<?php echo esc_attr( $this->option_enable_testimonials ); ?>" 
				name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_testimonials ); ?>]" 
				value="1" 
				<?php checked( true, $enabled ); ?>
			/>
			<span></span>
		</label>
		<?php
	}

	/**
	 * Render toggle field for enable reading progress bar.
	 *
	 * @return void
	 */
	public function field_enable_reading_progress() {
		$options = get_option( 'frontblocks_settings', array() );
		$enabled = (bool) ( $options[ $this->option_enable_reading_progress ] ?? false );
		?>
		<label class="frbl-toggle">
			<input type="checkbox"
				id="<?php echo esc_attr( $this->option_enable_reading_progress ); ?>"
				name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_reading_progress ); ?>]"
				value="1"
				<?php checked( true, $enabled ); ?>
			/>
			<span></span>
		</label>
		<?php
	}

	/**
	 * Render toggle field for enable back button.
	 *
	 * @return void
	 */
	public function field_enable_back_button() {
		$options = get_option( 'frontblocks_settings', array() );
		$enabled = (bool) ( $options[ $this->option_enable_back_button ] ?? false );
		?>
		<label class="frbl-toggle">
			<input type="checkbox" 
				id="<?php echo esc_attr( $this->option_enable_back_button ); ?>" 
				name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_back_button ); ?>]" 
				value="1" 
				<?php checked( true, $enabled ); ?>
			/>
			<span></span>
		</label>
		<?php
	}

	/**
	 * Render toggle field for enable Gutenberg in products (PRO).
	 *
	 * @return void
	 */
	public function field_enable_gutenberg() {
		$this->render_pro_toggle( $this->option_enable_gutenberg );
	}

	/**
	 * Render toggle field for enable Simple Prices Variable Products (PRO).
	 *
	 * @return void
	 */
	public function field_enable_simple_prices_variable_products() {
		$this->render_pro_toggle( $this->option_enable_simple_prices_variable_products );
	}

	/**
	 * Render After Add to Cart Block field.
	 *
	 * @return void
	 */
	public function field_enable_after_add_to_cart() {
		$this->render_pro_toggle( $this->option_enable_after_add_to_cart );
	}

	/**
	 * Render Deactivate Short Description field.
	 *
	 * @return void
	 */
	public function field_deactivate_short_description() {
		$this->render_pro_toggle( $this->option_deactivate_short_description );
	}

	/**
	 * Render Move Content to Short Description field.
	 *
	 * @return void
	 */
	public function field_move_content_to_short_description() {
		$this->render_pro_toggle( $this->option_move_content_to_short_description );
	}

	/**
	 * Render Disable Zoom in Product Images field.
	 *
	 * @return void
	 */
	public function field_disable_zoom_images() {
		$this->render_pro_toggle( $this->option_disable_zoom_images );
	}

	/**
	 * Render Add Share Buttons in Product Page field.
	 *
	 * @return void
	 */
	public function field_add_share_buttons() {
		$this->render_pro_toggle( $this->option_add_share_buttons );
	}

	/**
	 * Render Deactivate Product Tabs field.
	 *
	 * @return void
	 */
	public function field_deactivate_product_tabs() {
		$this->render_pro_toggle( $this->option_deactivate_product_tabs );
	}

	/**
	 * Render Horizontal Product Form Layout field.
	 *
	 * @return void
	 */
	public function field_horizontal_product_form() {
		$this->render_pro_toggle( $this->option_horizontal_product_form );
	}

	/**
	 * License section description.
	 *
	 * @return void
	 */
	public function section_license_callback() {
		echo '<p>' . esc_html__( 'Manage your FrontBlocks PRO license.', 'frontblocks' ) . '</p>';
	}

	/**
	 * Render license key field.
	 *
	 * @return void
	 */
	public function field_license_key() {
		global $frontblocks_pro_license;
		$license_key = get_option( $this->option_license_key );
		$product_id  = get_option( $this->option_product_id );
		?>
		<div class="tw-space-y-4">
			<!-- License Key and Product ID Fields in a row -->
			<div class="tw-flex tw-w-full">
				<!-- License Key Field - 66.6% (2/3) -->
				<div style="flex: 4 1 0%;">
					<input type="text" 
						id="<?php echo esc_attr( $this->option_license_key ); ?>" 
						name="<?php echo esc_attr( $this->option_license_key ); ?>" 
						value="<?php echo esc_attr( $license_key ); ?>"
						placeholder="<?php echo esc_attr__( 'Enter your license key', 'frontblocks' ); ?>"
						class="tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-base focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent"
					/>
				</div>

				<!-- Product ID Field - 33.3% (1/3) -->
				<div style="flex: 1 1 0%;">
					<input type="text" 
						id="<?php echo esc_attr( $this->option_product_id ); ?>" 
						name="<?php echo esc_attr( $this->option_product_id ); ?>" 
						value="<?php echo esc_attr( $product_id ); ?>"
						placeholder="<?php echo esc_attr__( 'Product ID', 'frontblocks' ); ?>"
						title="<?php echo esc_attr__( 'Product ID - You can find this in your purchase confirmation email.', 'frontblocks' ); ?>"
						class="tw-block tw-w-full tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg tw-text-base focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent"
					/>
				</div>
			</div>

			<!-- Help Text for Product ID -->
			<p class="tw-text-xs tw-text-gray-500 tw-mt-1">
				<?php echo esc_html__( 'Enter your license key and product ID. You can find both in your purchase confirmation email.', 'frontblocks' ); ?>
			</p>

			<!-- License Status Field (Read-only) -->
			<div>
				<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-mb-2">
					<?php echo esc_html__( 'License Status', 'frontblocks' ); ?>
				</label>
				<?php
				$status_text  = '';
				$status_class = '';
				$status_icon  = '';
				global $frontblocks_pro_license;
				$license_data   = $frontblocks_pro_license->license_key_status( true );
				$license_status = empty( $license_data ) ? 'not_activated' : $license_data['status_check'];

				switch ( $license_status ) {
					case 'active':
						$status_text  = __( 'Active', 'frontblocks' );
						$status_class = 'tw-bg-green-100 tw-text-green-800 tw-border-green-300';
						$status_icon  = '<svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
						break;
					case 'expired':
						$status_text  = __( 'Expired', 'frontblocks' );
						$status_class = 'tw-bg-red-100 tw-text-red-800 tw-border-red-300';
						$status_icon  = '<svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
						break;
					default:
						$status_text  = __( 'Not Activated', 'frontblocks' );
						$status_class = 'tw-bg-yellow-100 tw-text-yellow-800 tw-border-yellow-300';
						$status_icon  = '<svg class="tw-w-5 tw-h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';
						break;
				}
				?>
				<div class="tw-flex tw-items-center tw-gap-3 tw-px-4 tw-py-3 tw-border tw-rounded-lg <?php echo esc_attr( $status_class ); ?>">
					<span class="tw-flex-shrink-0">
						<?php echo $status_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="tw-font-semibold tw-text-base">
						<?php echo esc_html( $status_text ); ?>
					</span>
					<?php if ( ! empty( $license_data['expires'] ) && 'valid' === $license_data['status'] ) : ?>
						<span class="tw-ml-auto tw-text-sm">
							<?php
							printf(
								/* translators: %s: expiration date */
								esc_html__( 'Expires: %s', 'frontblocks' ),
								esc_html( $license_data['expires'] )
							);
							?>
						</span>
					<?php endif; ?>
				</div>
			</div>

			<!-- Help Text -->
			<?php if ( empty( $license_key ) && empty( $product_id ) ) : ?>
				<div class="tw-p-4 tw-rounded-lg tw-bg-gray-50 tw-border tw-border-gray-200">
					<p class="tw-text-sm tw-text-gray-600">
						<?php
						printf(
							/* translators: %s: purchase link */
							esc_html__( 'Don\'t have a license? %s to get started.', 'frontblocks' ),
							'<a href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-license" target="_blank" rel="noopener noreferrer" class="tw-text-primary-500 hover:tw-text-primary-600 tw-font-medium">' . esc_html__( 'Purchase FrontBlocks PRO', 'frontblocks' ) . '</a>'
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( 'expired' === $license_status ) : ?>
				<div class="tw-p-3 tw-rounded-lg tw-bg-red-50 tw-border tw-border-red-200">
					<p class="tw-text-sm tw-text-red-700">
						<?php
						printf(
							/* translators: %s: renewal link */
							esc_html__( 'Your license has expired. %s to continue receiving updates and support.', 'frontblocks' ),
							'<a href="https://close.technology/my-account/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=renew-license" target="_blank" rel="noopener noreferrer" class="tw-font-medium tw-underline hover:tw-no-underline">' . esc_html__( 'Renew your license', 'frontblocks' ) . '</a>'
						);
						?>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Helper method to render PRO toggle fields.
	 *
	 * @param string $option_key Option key.
	 * @return void
	 */
	private function render_pro_toggle( $option_key ) {
		$options    = get_option( 'frontblocks_settings', array() );
		$enabled    = (bool) ( $options[ $option_key ] ?? false );
		$is_enabled = $this->is_license_valid;
		$disabled   = ! $is_enabled ? 'disabled' : '';
		?>
		<label class="frbl-toggle">
			<input type="checkbox" 
				id="<?php echo esc_attr( $option_key ); ?>" 
				name="frontblocks_settings[<?php echo esc_attr( $option_key ); ?>]" 
				value="1" 
				<?php checked( true, $enabled ); ?>
				<?php echo esc_attr( $disabled ); ?>
			/>
			<span></span>
		</label>
		<?php
	}

	/**
	 * Sanitize settings array.
	 *
	 * @param array $value Raw value.
	 * @return array
	 */
	public function sanitize_settings( $value ) {
		// Nonce verification.
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'frontblocks_settings-options' ) ) {
			add_settings_error( 'frontblocks_settings', 'frontblocks_settings_nonce', esc_html__( 'Security check failed. Please try again.', 'frontblocks' ), 'error' );

			return get_option( 'frontblocks_settings', array() );
		}

		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $value as $key => $val ) {

			if ( $this->option_enable_testimonials === $key || $this->option_enable_reading_progress === $key || $this->option_enable_back_button === $key || $this->option_enable_gutenberg === $key || $this->option_enable_simple_prices_variable_products === $key || $this->option_enable_after_add_to_cart === $key || $this->option_deactivate_short_description === $key || $this->option_move_content_to_short_description === $key || $this->option_disable_zoom_images === $key || $this->option_add_share_buttons === $key || $this->option_deactivate_product_tabs === $key || $this->option_horizontal_product_form === $key ) {
				$sanitized[ $key ] = (bool) $val;
			}
		}

		// Ensure mutual exclusion: if both description options are enabled, keep only the last one changed.
		if ( ! empty( $sanitized[ $this->option_deactivate_short_description ] ) && ! empty( $sanitized[ $this->option_move_content_to_short_description ] ) ) {
			// Get current saved values to determine which one was just changed.
			$current_options    = get_option( 'frontblocks_settings', array() );
			$current_deactivate = ! empty( $current_options[ $this->option_deactivate_short_description ] );
			$current_move       = ! empty( $current_options[ $this->option_move_content_to_short_description ] );

			// If deactivate was already on, turn it off (move is the new one).
			if ( $current_deactivate ) {
				$sanitized[ $this->option_deactivate_short_description ] = false;
			} else {
				// Otherwise turn off move (deactivate is the new one).
				$sanitized[ $this->option_move_content_to_short_description ] = false;
			}
		}

		// Save license key and product id.
		global $frontblocks_pro_license;
		if ( ! empty( $frontblocks_pro_license ) ) {
			$result = $frontblocks_pro_license->sanitize_fields_license( $_POST );

			if ( 'ok' === $result['status'] ) {
				add_settings_error( 'frontblocks_settings', 'frontblocks_settings_license', $result['message'], 'updated' );
			} else {
				add_settings_error( 'frontblocks_settings', 'frontblocks_settings_license', $result['message'], 'error' );
			}
		}

		return $sanitized;
	}
}
