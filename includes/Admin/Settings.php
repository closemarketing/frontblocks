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
	 * Option key for events CPT feature.
	 *
	 * @var string
	 */
	private $option_enable_events = 'enable_events';

	/**
	 * Option key for events type (cpt or posts).
	 *
	 * @var string
	 */
	private $option_events_type = 'events_type';

	/**
	 * Option key for popups feature.
	 *
	 * @var string
	 */
	private $option_enable_popups = 'enable_popups';

	/**
	 * Option key for fluid typography feature.
	 *
	 * @var string
	 */
	private $option_enable_fluid_typography = 'enable_fluid_typography';

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
	 * Option key for custom post types builder (PRO).
	 *
	 * @var string
	 */
	private $option_enable_custom_post_types = 'enable_custom_post_types';

	/**
	 * Option key for full page scroll feature (PRO).
	 *
	 * @var string
	 */
	private $option_enable_fullpage_scroll = 'enable_fullpage_scroll';

	/**
	 * Option key for language banner feature (PRO).
	 *
	 * @var string
	 */
	private $option_enable_language_banner = 'enable_language_banner';

	/**
	 * Option key for checkout inline fields (PRO).
	 *
	 * @var string
	 */
	private $option_checkout_inline = 'checkout_inline';

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
	 * Constructor.
	 */
	public function __construct() {
		// Check license via FrontBlocks PRO helper function.
		$this->is_license_valid = function_exists( 'frblp_is_license_valid' ) && frblp_is_license_valid();

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_head', array( $this, 'add_menu_icon_styles' ) );
	}

	/**
	 * Add menu icon styles.
	 *
	 * @return void
	 */
	public function add_menu_icon_styles() {
		?>
		<style>
		#toplevel_page_frontblocks-settings .wp-menu-image img,
		#toplevel_page_frontblocks-settings .wp-menu-image svg {
			width: 20px !important;
			height: 20px !important;
			max-width: 20px !important;
			max-height: 20px !important;
		}
		</style>
		<?php
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
				
				if (deactivateCheckbox && moveContentCheckbox) {
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
				}

				// Show/hide events type select based on toggle state.
				const eventsCheckbox = document.getElementById('enable_events');
				const eventsTypeWrapper = document.getElementById('events-type-wrapper');
				
				if (eventsCheckbox && eventsTypeWrapper) {
					// Find the parent feature card and feature content.
					const featureCard = eventsCheckbox.closest('.frbl-feature-card');
					const featureContent = featureCard ? featureCard.querySelector('.frbl-feature-content') : null;
					
					// Move the wrapper outside of frbl-feature-content but inside frbl-feature-card.
					// This ensures it appears below the entire horizontal row (icon, text, toggle).
					if (featureCard && featureContent) {
						// Check if wrapper is still inside feature-content and move it.
						if (featureContent.contains(eventsTypeWrapper)) {
							// Move it to be a direct child of feature-card, after feature-content.
							featureCard.appendChild(eventsTypeWrapper);
						}
					}
					
					function updateEventsTypeVisibility() {
						if (eventsCheckbox.checked) {
							eventsTypeWrapper.style.display = 'block';
							eventsTypeWrapper.style.width = '100%';
							eventsTypeWrapper.style.minWidth = '100%';
							eventsTypeWrapper.style.marginTop = '1rem';
							eventsTypeWrapper.style.paddingTop = '1rem';
							eventsTypeWrapper.style.paddingLeft = '1rem';
							eventsTypeWrapper.style.paddingRight = '1rem';
							eventsTypeWrapper.style.paddingBottom = '1rem';
							eventsTypeWrapper.style.borderTop = '1px solid #e5e7eb';
							eventsTypeWrapper.style.backgroundColor = '#f9fafb';
							// Set feature card to column layout.
							if (featureCard) {
								featureCard.style.display = 'flex';
								featureCard.style.flexDirection = 'column';
							}
							// Keep the feature content horizontal - never change it.
							if (featureContent) {
								featureContent.style.flexDirection = 'row';
								featureContent.style.alignItems = 'center';
								featureContent.style.justifyContent = 'space-between';
							}
						} else {
							eventsTypeWrapper.style.display = 'none';
							// Reset feature card layout.
							if (featureCard) {
								featureCard.style.display = '';
								featureCard.style.flexDirection = '';
							}
							// Keep the feature content horizontal.
							if (featureContent) {
								featureContent.style.flexDirection = 'row';
								featureContent.style.alignItems = 'center';
								featureContent.style.justifyContent = 'space-between';
							}
						}
					}
					
					// Run immediately to move the element on page load.
					if (featureCard && featureContent && featureContent.contains(eventsTypeWrapper)) {
						featureCard.appendChild(eventsTypeWrapper);
					}
					
					eventsCheckbox.addEventListener('change', updateEventsTypeVisibility);
					updateEventsTypeVisibility();
				}

			});
			"
		);

		// Enqueue script for custom post types if PRO is active and license is valid.
		if ( frbl_is_pro_active() && $this->is_license_valid ) {
			wp_enqueue_script(
				'frontblocks-cpt-admin',
				FRBL_PLUGIN_URL . 'assets/admin/custom-post-types.js',
				array( 'jquery' ),
				FRBL_VERSION,
				true
			);

			wp_localize_script(
				'frontblocks-cpt-admin',
				'frontblocksCpt',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'frontblocks_create_cpt' ),
					'i18n'    => array(
						'creating' => __( 'Creating...', 'frontblocks' ),
						'error'    => __( 'Error creating post type. Please try again.', 'frontblocks' ),
						'success'  => __( 'Post type created successfully!', 'frontblocks' ),
					),
				)
			);
		}
	}

	/**
	 * Register options page as dedicated menu.
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

		// Register license setting group for FrontBlocks PRO.
		global $frblp_license;
		if ( $frblp_license && class_exists( '\Closemarketing\WPLicenseManager\License' ) ) {
			// Register each individual license field.
			register_setting(
				'frontblocks-pro_license',
				'frontblocks-pro_license_apikey',
				array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			register_setting(
				'frontblocks-pro_license',
				'frontblocks-pro_license_deactivate_checkbox',
				array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// Hook into admin_init to process license activation/deactivation.
			add_action(
				'admin_init',
				function () use ( $frblp_license ) {
					// Check if license form was submitted and verify nonce.
					if ( isset( $_POST['option_page'], $_POST['_wpnonce'] ) && 'frontblocks-pro_license' === $_POST['option_page'] && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'frontblocks-pro_license-options' ) ) {
						if ( isset( $_POST['submit_license'] ) ) {
							// Build input array for validate_license.
							$input = array(
								'frontblocks-pro_license_apikey'              => isset( $_POST['frontblocks-pro_license_apikey'] ) ? sanitize_text_field( wp_unslash( $_POST['frontblocks-pro_license_apikey'] ) ) : '',
								'frontblocks-pro_license_deactivate_checkbox' => isset( $_POST['frontblocks-pro_license_deactivate_checkbox'] ) ? sanitize_text_field( wp_unslash( $_POST['frontblocks-pro_license_deactivate_checkbox'] ) ) : '',
							);

							// Call the license validation.
							$frblp_license->validate_license( $input );
						}
					}
				},
				15
			);
		}

		// Always Active Blocks section.
		add_settings_section(
			'frontblocks_section_active_blocks',
			__( 'Active Blocks & Features', 'frontblocks' ),
			array( $this, 'section_active_blocks_callback' ),
			$this->page_slug
		);

		add_settings_section(
			'frontblocks_section_features',
			__( 'Optional Features', 'frontblocks' ),
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

		add_settings_field(
			$this->option_enable_events,
			__( 'Enable Events', 'frontblocks' ),
			array( $this, 'field_enable_events' ),
			$this->page_slug,
			'frontblocks_section_features'
		);

		add_settings_field(
			$this->option_enable_fluid_typography,
			__( 'Enable Fluid Typography', 'frontblocks' ),
			array( $this, 'field_enable_fluid_typography' ),
			$this->page_slug,
			'frontblocks_section_features'
		);

		add_settings_field(
			$this->option_enable_popups,
			__( 'Enable Popups', 'frontblocks' ),
			array( $this, 'field_enable_popups' ),
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

		add_settings_field(
			$this->option_enable_fullpage_scroll,
			__( 'Enable Full Page Scroll', 'frontblocks' ),
			array( $this, 'field_enable_fullpage_scroll' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_enable_language_banner,
			__( 'Enable Language Banner', 'frontblocks' ),
			array( $this, 'field_enable_language_banner' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_checkout_inline,
			__( 'Checkout Inline Fields', 'frontblocks' ),
			array( $this, 'field_checkout_inline' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		// Custom Post Types section (PRO).
		if ( frbl_is_pro_active() ) {
			add_settings_section(
				'frontblocks_section_custom_post_types',
				__( 'Custom Post Types', 'frontblocks' ),
				array( $this, 'section_custom_post_types_callback' ),
				$this->page_slug
			);

			add_settings_field(
				$this->option_enable_custom_post_types,
				__( 'Enable Custom Post Types Builder', 'frontblocks' ),
				array( $this, 'field_enable_custom_post_types' ),
				$this->page_slug,
				'frontblocks_section_custom_post_types'
			);
		}

		// Note: License section is rendered separately outside the main form.
		// See render_license_section() method called from render_page().

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

				// Show license activated message.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['license_activated'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['license_activated'] ) ) ) :
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
									<?php esc_html_e( 'License activated successfully!', 'frontblocks' ); ?>
								</p>
							</div>
						</div>
					</div>
					<?php
				endif;

				// Show license deactivated message.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['license_deactivated'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['license_deactivated'] ) ) ) :
					?>
					<div style="background-color: #fffbeb; border-left: 4px solid #fbbf24; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
						<div class="tw-flex">
							<div class="tw-flex-shrink-0">
								<svg class="tw-h-5 tw-w-5" style="color: #fbbf24;" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
								</svg>
							</div>
							<div class="tw-ml-3">
								<p class="tw-text-sm tw-font-medium" style="color: #92400e; margin: 0;">
									<?php esc_html_e( 'License deactivated successfully.', 'frontblocks' ); ?>
								</p>
							</div>
						</div>
					</div>
					<?php
				endif;

				// Show license error message.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['license_error'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['license_error'] ) ) ) :
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$error_msg = isset( $_GET['error_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['error_msg'] ) ) : '';
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
									<?php
									if ( ! empty( $error_msg ) ) {
										echo esc_html__( 'Failed to activate license: ', 'frontblocks' ) . '<br><strong>' . esc_html( $error_msg ) . '</strong>';
									} else {
										esc_html_e( 'Failed to activate license. Please check your license key and try again.', 'frontblocks' );
									}
									?>
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

				<?php
				// Render license section separately (outside main form) if PRO is active.
				if ( frbl_is_pro_active() ) {
					$this->render_license_section();
				}
				?>

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

				<?php $this->render_debug_section(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render debug section for Fluid Typography.
	 *
	 * @return void
	 */
	private function render_debug_section() {
		// Only show if Fluid Typography is enabled and user requested debug.
		$options = get_option( 'frontblocks_settings', array() );
		$enabled = ! empty( $options['enable_fluid_typography'] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $enabled || ! isset( $_GET['frbl_debug_typography'] ) ) {
			return;
		}

		// Get GeneratePress settings.
		$gp_settings = get_option( 'generate_settings', array() );

		// Filter only font-related settings.
		$font_settings = array();
		foreach ( $gp_settings as $key => $value ) {
			if ( strpos( $key, 'font' ) !== false || strpos( $key, 'heading' ) !== false ) {
				$font_settings[ $key ] = $value;
			}
		}

		?>
		<div class="tw-mt-8 tw-p-6 tw-bg-yellow-50 tw-border tw-border-yellow-200 tw-rounded-lg">
			<h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">
				🐛 Debug: Fluid Typography Settings
			</h3>
			<p class="tw-text-sm tw-text-gray-600 tw-mb-4">
				<?php echo esc_html__( 'This shows the GeneratePress font settings being used by the Fluid Typography module.', 'frontblocks' ); ?>
			</p>
			<div class="tw-bg-white tw-p-4 tw-rounded tw-border tw-border-gray-300 tw-overflow-auto" style="max-height: 400px;">
				<pre style="margin: 0; font-size: 12px;"><?php print_r( $font_settings ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r ?></pre>
			</div>
			<p class="tw-text-xs tw-text-gray-500 tw-mt-4">
				<?php
				printf(
					/* translators: %s: URL parameter */
					esc_html__( 'To hide this debug info, remove %s from the URL.', 'frontblocks' ),
					'<code>?frbl_debug_typography=1</code>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Active Blocks section callback.
	 *
	 * @return void
	 */
	private function section_active_blocks_callback() {
		$active_blocks = apply_filters(
			'frbl_active_blocks',
			array(
				array(
					'icon'  => 'animations',
					'title' => __( 'Animations', 'frontblocks' ),
					'desc'  => __( 'Add animations to any block using Animate.css', 'frontblocks' ),
				),
				array(
					'icon'  => 'carousel',
					'title' => __( 'Carousel/Slider', 'frontblocks' ),
					'desc'  => __( 'Transform any Grid block into a carousel or slider', 'frontblocks' ),
				),
				array(
					'icon'  => 'gallery',
					'title' => __( 'Native Gallery', 'frontblocks' ),
					'desc'  => __( 'Enhanced gallery block with carousel and masonry options', 'frontblocks' ),
				),
				array(
					'icon'  => 'sticky',
					'title' => __( 'Sticky Columns', 'frontblocks' ),
					'desc'  => __( 'Make Grid blocks sticky when scrolling', 'frontblocks' ),
				),
				array(
					'icon'  => 'insert_post',
					'title' => __( 'Insert Post Block', 'frontblocks' ),
					'desc'  => __( 'Display content from other posts, pages or custom post types', 'frontblocks' ),
				),
				array(
					'icon'  => 'counter',
					'title' => __( 'Counter Block', 'frontblocks' ),
					'desc'  => __( 'Display animated counters with start and end values', 'frontblocks' ),
				),
				array(
					'icon'  => 'reading_time',
					'title' => __( 'Reading Time Block', 'frontblocks' ),
					'desc'  => __( 'Show estimated reading time for posts', 'frontblocks' ),
				),
				array(
					'icon'  => 'stacked_images',
					'title' => __( 'Stacked Images Block', 'frontblocks' ),
					'desc'  => __( 'Display images with animated stacking effect from different directions', 'frontblocks' ),
				),
				array(
					'icon'  => 'product_categories',
					'title' => __( 'Product Categories Block', 'frontblocks' ),
					'desc'  => __( 'Display WooCommerce product categories', 'frontblocks' ),
				),
				array(
					'icon'  => 'headline_marquee',
					'title' => __( 'Headline Marquee', 'frontblocks' ),
					'desc'  => __( 'Infinite scrolling marquee effect for headline/text blocks with customizable speed', 'frontblocks' ),
				),
				array(
					'icon'  => 'svg_upload',
					'title' => __( 'SVG Uploads', 'frontblocks' ),
					'desc'  => __( 'Upload SVG files to the media library. Files are automatically sanitized to prevent security risks.', 'frontblocks' ),
				),
			)
		);

		$pro_blocks    = apply_filters( 'frbl_pro_blocks', $this->get_default_pro_blocks() );
		$license_valid = function_exists( 'frblp_is_license_valid' ) && frblp_is_license_valid();

		?>
		<p class="tw-text-sm tw-text-gray-600 tw-mt-0 tw-mb-4">
			<?php echo esc_html__( 'These blocks and features are always active and available in the block editor.', 'frontblocks' ); ?>
		</p>
		<div class="frbl-features-grid">
			<?php
			foreach ( $active_blocks as $block ) {
				UI::show_info_card( $block['icon'], $block['title'], $block['desc'] );
			}

			if ( ! $license_valid ) {
				foreach ( $pro_blocks as $block ) {
					UI::show_pro_info_card( $block['icon'], $block['title'], $block['desc'] );
				}
			}
			?>
		</div>
		<?php
	}

	/**
	 * Features section callback.
	 *
	 * @return void
	 */
	private function section_features_callback() {
		$license_valid = function_exists( 'frblp_is_license_valid' ) && frblp_is_license_valid();
		?>
		<p class="tw-text-sm tw-text-gray-600 tw-mt-0 tw-mb-4">
			<?php echo esc_html__( 'Enable or disable these optional features as needed.', 'frontblocks' ); ?>
		</p>
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

		$has_fields = isset( $wp_settings_fields[ $this->page_slug ][ $section['id'] ] );

		// Si no hay campos Y no hay callback, no renderizar nada.
		if ( ! $has_fields && ! $section['callback'] ) {
			return;
		}

		// Check if this is a section with callback only (like active_blocks).
		$is_callback_only = ! $has_fields && $section['callback'];

		// Check if this is the custom post types section - render it full width.
		$is_cpt_section = 'frontblocks_section_custom_post_types' === $section['id'];

		// Show PRO CTA button before the Optional Features section.
		if ( 'frontblocks_section_features' === $section['id'] && ! $this->is_license_valid ) {
			?>
			<div class="tw-mb-4">
				<a
					href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-optional-cta"
					target="_blank"
					rel="noopener noreferrer"
					class="tw-inline-flex tw-items-center tw-border tw-border-transparent tw-text-sm tw-font-medium tw-rounded-lg tw-shadow-sm tw-text-white tw-transition-colors tw-duration-200"
					style="background-color: #ef4444; padding: 10px 20px;"
					onmouseover="this.style.backgroundColor='#dc2626'"
					onmouseout="this.style.backgroundColor='#ef4444'"
				>
					<?php echo esc_html__( 'Get FrontBlocks PRO', 'frontblocks' ); ?> →
				</a>
			</div>
			<?php
		}

		if ( $is_callback_only ) {
			// Render section with only callback (no fields).
			?>
			<div class="frbl-section-wrapper">
				<div class="frbl-section-header">
					<h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-0">
						<?php echo esc_html( $section['title'] ); ?>
					</h2>
				</div>
				<?php call_user_func( $section['callback'], $section ); ?>
			</div>
			<?php
			return;
		}

		if ( $is_cpt_section ) {
			// Render CPT section as a full-width card.
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
					<h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-0">
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
				$this->option_enable_fullpage_scroll,
				$this->option_enable_language_banner,
				$this->option_checkout_inline,
			),
			true
		);

		// Apply PRO styling only if license is not valid.
		$needs_license = $is_pro_feature && ! $this->is_license_valid;

		// Get icon for this feature.
		$icon = $this->get_feature_icon( $field['id'] );

		// Short descriptions for each field.
		$descriptions = array(
			$this->option_enable_testimonials          => __( 'Add a testimonials block to display customer reviews.', 'frontblocks' ),
			$this->option_enable_reading_progress      => __( 'Show a progress bar at the top of the page while reading posts.', 'frontblocks' ),
			$this->option_enable_back_button           => __( 'Add a floating back button for easy navigation.', 'frontblocks' ),
			$this->option_enable_events                => __( 'Register and display events using a CPT or blog posts.', 'frontblocks' ),
			$this->option_enable_fluid_typography      => __( 'Font sizes scale smoothly between mobile and desktop using CSS clamp().', 'frontblocks' ),
			$this->option_enable_popups                => __( 'Create popups with the block editor and configure when and where they appear.', 'frontblocks' ),
			$this->option_enable_gutenberg             => __( 'Use the block editor to write WooCommerce product descriptions.', 'frontblocks' ),
			$this->option_enable_simple_prices_variable_products => __( 'Show a simplified price range for variable products.', 'frontblocks' ),
			$this->option_enable_after_add_to_cart     => __( 'Insert custom block content right after the Add to Cart button.', 'frontblocks' ),
			$this->option_deactivate_short_description => __( 'Remove the short description field from product pages.', 'frontblocks' ),
			$this->option_move_content_to_short_description => __( 'Move the main product content into the short description area.', 'frontblocks' ),
			$this->option_disable_zoom_images          => __( 'Remove the zoom effect on WooCommerce product images.', 'frontblocks' ),
			$this->option_add_share_buttons            => __( 'Add social share buttons to WooCommerce product pages.', 'frontblocks' ),
			$this->option_deactivate_product_tabs      => __( 'Remove the default description, reviews and attributes tabs.', 'frontblocks' ),
			$this->option_horizontal_product_form      => __( 'Display quantity and Add to Cart button side by side.', 'frontblocks' ),
			$this->option_enable_fullpage_scroll       => __( 'Enable full-page scroll navigation between sections.', 'frontblocks' ),
			$this->option_enable_language_banner       => __( 'Show a banner when the visitor language differs from the site language.', 'frontblocks' ),
			$this->option_checkout_inline              => __( 'Display address, email and phone fields side by side in the WooCommerce checkout.', 'frontblocks' ),
		);

		$desc = $descriptions[ $field['id'] ] ?? '';

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
					<?php if ( $desc ) : ?>
						<p class="frbl-feature-description">
							<?php echo esc_html( $desc ); ?>
						</p>
					<?php endif; ?>
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
		// Map field IDs to icon file names.
		$icon_map = array(
			$this->option_enable_testimonials          => 'testimonials',
			$this->option_enable_reading_progress      => 'reading-progress',
			$this->option_enable_back_button           => 'back-button',
			$this->option_enable_events                => 'events',
			$this->option_enable_fluid_typography      => 'fluid-typography',
			$this->option_enable_popups                => 'popups',
			$this->option_enable_gutenberg             => 'gutenberg',
			$this->option_enable_simple_prices_variable_products => 'simple-prices',
			$this->option_enable_after_add_to_cart     => 'after-add-to-cart',
			$this->option_deactivate_short_description => 'deactivate-description',
			$this->option_move_content_to_short_description => 'move-content',
			$this->option_disable_zoom_images          => 'disable-zoom',
			$this->option_add_share_buttons            => 'share-buttons',
			$this->option_deactivate_product_tabs      => 'deactivate-tabs',
			$this->option_horizontal_product_form      => 'horizontal-form',
			$this->option_enable_fullpage_scroll       => 'fullpage-scroll',
			$this->option_enable_language_banner       => 'language-banner',
			$this->option_checkout_inline              => 'horizontal-form',
		);

		$icon_name = $icon_map[ $field_id ] ?? 'default';
		$icon_path = FRBL_PLUGIN_PATH . 'assets/admin/icons/' . $icon_name . '.svg';

		if ( file_exists( $icon_path ) ) {
			$svg_content = file_get_contents( $icon_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			return $svg_content ? $svg_content : '';
		}

		return '';
	}

	/**
	 * PRO Features section description.
	 *
	 * @return void
	 */
	public function section_woo_features_callback() {
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
			?>
			<p class="tw-text-sm tw-text-gray-600 tw-mt-0 tw-mb-4">
				<?php echo esc_html__( 'Advanced features for WooCommerce and more.', 'frontblocks' ); ?>
			</p>
			<?php
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
	 * Render toggle field for enable events.
	 *
	 * @return void
	 */
	public function field_enable_events() {
		$options     = get_option( 'frontblocks_settings', array() );
		$enabled     = (bool) ( $options[ $this->option_enable_events ] ?? false );
		$events_type = sanitize_text_field( $options[ $this->option_events_type ] ?? 'cpt' );
		?>
		<!-- Toggle - stays in horizontal layout with icon and text -->
		<label class="frbl-toggle">
			<input type="checkbox" 
				id="<?php echo esc_attr( $this->option_enable_events ); ?>" 
				name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_events ); ?>]" 
				value="1" 
				<?php checked( true, $enabled ); ?>
			/>
			<span></span>
		</label>
		
		<!-- Select and description - will be moved below the card by JavaScript -->
		<div id="events-type-wrapper" class="tw-mt-4" style="<?php echo $enabled ? 'width: 100%; min-width: 100%; display: block;' : 'display: none;'; ?>">
			<label for="<?php echo esc_attr( $this->option_events_type ); ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
				<?php echo esc_html__( 'Event type', 'frontblocks' ); ?>
			</label>
			<select 
				id="<?php echo esc_attr( $this->option_events_type ); ?>" 
				name="frontblocks_settings[<?php echo esc_attr( $this->option_events_type ); ?>]"
				class="tw-block tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-base focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent"
				style="width: 100%; min-width: 100%; max-width: 100%; box-sizing: border-box;"
			>
				<option value="cpt" <?php selected( $events_type, 'cpt' ); ?>>
					<?php echo esc_html__( 'Custom Post Type (CPT)', 'frontblocks' ); ?>
				</option>
				<option value="posts" <?php selected( $events_type, 'posts' ); ?>>
					<?php echo esc_html__( 'Blog posts', 'frontblocks' ); ?>
				</option>
			</select>
			<p class="tw-text-xs tw-text-gray-500 tw-mt-2">
				<?php echo esc_html__( 'Choose whether events will be created in a dedicated CPT or in regular blog posts.', 'frontblocks' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render toggle field for enable fluid typography.
	 *
	 * @return void
	 */
	public function field_enable_fluid_typography() {
		$options = get_option( 'frontblocks_settings', array() );
		$enabled = (bool) ( $options[ $this->option_enable_fluid_typography ] ?? true );
		?>
		<label class="frbl-toggle">
			<input type="checkbox" 
				id="<?php echo esc_attr( $this->option_enable_fluid_typography ); ?>" 
				name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_fluid_typography ); ?>]" 
				value="1" 
				<?php checked( true, $enabled ); ?>
			/>
			<span></span>
		</label>
		<?php
	}

	/**
	 * Render toggle field for enable popups.
	 *
	 * @return void
	 */
	public function field_enable_popups() {
		$options = get_option( 'frontblocks_settings', array() );
		$enabled = (bool) ( $options[ $this->option_enable_popups ] ?? false );
		?>
		<label class="frbl-toggle">
			<input type="checkbox"
				id="<?php echo esc_attr( $this->option_enable_popups ); ?>"
				name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_popups ); ?>]"
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
	 * Render Enable Full Page Scroll field.
	 *
	 * @return void
	 */
	public function field_enable_fullpage_scroll() {
		$this->render_pro_toggle( $this->option_enable_fullpage_scroll );
	}

	/**
	 * Render Enable Language Banner field.
	 *
	 * @return void
	 */
	public function field_enable_language_banner() {
		$this->render_pro_toggle( $this->option_enable_language_banner );
	}

	/**
	 * Render toggle field for checkout inline fields (PRO).
	 *
	 * @return void
	 */
	public function field_checkout_inline() {
		$this->render_pro_toggle( $this->option_checkout_inline );
	}

	/**
	 * Custom Post Types section callback.
	 *
	 * @return void
	 */
	public function section_custom_post_types_callback() {
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
				esc_html__( 'This feature requires %s. Upgrade to unlock advanced functionality.', 'frontblocks' ),
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
			?>
			<p class="tw-text-sm tw-text-gray-600 tw-mt-0 tw-mb-4">
				<?php echo esc_html__( 'Create and manage custom post types with advanced configuration options.', 'frontblocks' ); ?>
			</p>
			<?php
		}
	}

	/**
	 * Render toggle field for enable custom post types.
	 *
	 * @return void
	 */
	public function field_enable_custom_post_types() {
		$options    = get_option( 'frontblocks_settings', array() );
		$enabled    = (bool) ( $options[ $this->option_enable_custom_post_types ] ?? false );
		$is_enabled = $this->is_license_valid;
		$disabled   = ! $is_enabled ? 'disabled' : '';
		?>
		<div class="frbl-custom-post-types-wrapper">
			<div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
				<label for="<?php echo esc_attr( $this->option_enable_custom_post_types ); ?>" class="tw-text-base tw-font-medium tw-text-gray-900">
					<?php echo esc_html__( 'Enable Custom Post Types Builder', 'frontblocks' ); ?>
				</label>
				<label class="frbl-toggle">
					<input type="checkbox" 
						id="<?php echo esc_attr( $this->option_enable_custom_post_types ); ?>" 
						name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_custom_post_types ); ?>]" 
						value="1" 
						<?php checked( true, $enabled ); ?>
						<?php echo esc_attr( $disabled ); ?>
					/>
					<span></span>
				</label>
			</div>
			
			<?php if ( $is_enabled ) : ?>
				<div id="frbl-cpt-builder" class="frbl-cpt-builder" style="<?php echo $enabled ? '' : 'display: none;'; ?>">
					<div class="tw-mt-4 tw-p-4 tw-bg-gray-50 tw-rounded-lg tw-border tw-border-gray-200">
						<label for="frbl-cpt-name" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
							<?php echo esc_html__( 'Post Type Name', 'frontblocks' ); ?>
						</label>
						<div class="tw-flex tw-gap-2">
							<input 
								type="text" 
								id="frbl-cpt-name" 
								class="tw-flex-1 tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent" 
								placeholder="<?php echo esc_attr__( 'e.g., Portfolio, Team, Services', 'frontblocks' ); ?>"
							/>
							<button 
								type="button" 
								id="frbl-create-cpt-btn" 
								class="tw-px-4 tw-py-2 tw-bg-primary-500 tw-text-white tw-rounded-lg hover:tw-bg-primary-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 tw-transition-colors"
							>
								<?php echo esc_html__( 'Create', 'frontblocks' ); ?>
							</button>
						</div>
						<p class="tw-text-xs tw-text-gray-500 tw-mt-2">
							<?php echo esc_html__( 'Enter a singular name for your custom post type (e.g., "Portfolio" will create "portfolio" post type).', 'frontblocks' ); ?>
						</p>
					</div>

					<?php do_action( 'frontblocks_render_existing_cpts' ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render PRO features section after the main form.
	 * Shows when frbl_pro_blocks filter returns blocks and license is not active.
	 *
	 * @return void
	 */
	/**
	 * Default PRO blocks list — shown as promotional cards when PRO is not installed.
	 * The frbl_pro_blocks filter lets the PRO plugin override this list.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function get_default_pro_blocks(): array {
		return array(
			array(
				'icon'  => 'meta-fields',
				'title' => __( 'Dynamic Meta Fields', 'frontblocks' ),
				'desc'  => __( 'Bind any paragraph or heading to a custom post meta field, editable from the block editor.', 'frontblocks' ),
			),
			array(
				'icon'  => 'user-text',
				'title' => __( 'User Data Block', 'frontblocks' ),
				'desc'  => __( 'Display logged-in user data with placeholders like {nombre}, {email}, {username}.', 'frontblocks' ),
			),
			array(
				'icon'  => 'fullpage-scroll',
				'title' => __( 'Full Page Scroll', 'frontblocks' ),
				'desc'  => __( 'Full-page scroll navigation between sections with smooth transitions.', 'frontblocks' ),
			),
			array(
				'icon'  => 'language-banner',
				'title' => __( 'Language Banner', 'frontblocks' ),
				'desc'  => __( 'Detect visitor language and show a recommendation banner (WPML/Polylang).', 'frontblocks' ),
			),
			array(
				'icon'  => 'gutenberg',
				'title' => __( 'Gutenberg in Products', 'frontblocks' ),
				'desc'  => __( 'Use the full block editor to build WooCommerce product descriptions.', 'frontblocks' ),
			),
			array(
				'icon'  => 'after-add-to-cart',
				'title' => __( 'After Add to Cart Block', 'frontblocks' ),
				'desc'  => __( 'Insert custom block content right after the Add to Cart button.', 'frontblocks' ),
			),
			array(
				'icon'  => 'simple-prices',
				'title' => __( 'Simple Prices Variable Products', 'frontblocks' ),
				'desc'  => __( 'Simplified price display for variable WooCommerce products.', 'frontblocks' ),
			),
			array(
				'icon'  => 'horizontal-form',
				'title' => __( 'Horizontal Product Form', 'frontblocks' ),
				'desc'  => __( 'Switch the WooCommerce product form to a horizontal layout.', 'frontblocks' ),
			),
			array(
				'icon'  => 'deactivate-tabs',
				'title' => __( 'Deactivate Product Tabs', 'frontblocks' ),
				'desc'  => __( 'Remove the default tabs from WooCommerce product pages.', 'frontblocks' ),
			),
			array(
				'icon'  => 'disable-zoom',
				'title' => __( 'Disable Product Image Zoom', 'frontblocks' ),
				'desc'  => __( 'Remove zoom effect on WooCommerce product images.', 'frontblocks' ),
			),
			array(
				'icon'  => 'share-buttons',
				'title' => __( 'Share Buttons', 'frontblocks' ),
				'desc'  => __( 'Add social share buttons to WooCommerce product pages.', 'frontblocks' ),
			),
			array(
				'icon'  => 'deactivate-description',
				'title' => __( 'Manage Short Description', 'frontblocks' ),
				'desc'  => __( 'Deactivate or move the WooCommerce product short description.', 'frontblocks' ),
			),
			array(
				'icon'  => 'default',
				'title' => __( 'Custom Post Types Builder', 'frontblocks' ),
				'desc'  => __( 'Create and manage custom post types directly from the admin panel.', 'frontblocks' ),
			),
			array(
				'icon'  => 'horizontal-form',
				'title' => __( 'Checkout Inline Fields', 'frontblocks' ),
				'desc'  => __( 'Display address, email and phone fields side by side in the WooCommerce checkout.', 'frontblocks' ),
			),
		);
	}

	/**
	 * Render PRO features section after the main form.
	 *
	 * @return void
	 */
	private function render_pro_section(): void {
		$pro_blocks    = apply_filters( 'frbl_pro_blocks', $this->get_default_pro_blocks() );
		$license_valid = function_exists( 'frblp_is_license_valid' ) && frblp_is_license_valid();

		if ( empty( $pro_blocks ) || $license_valid ) {
			return;
		}

		?>
		<div class="frbl-section-wrapper tw-mt-6">
			<div class="frbl-section-header">
				<h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-0">
					<?php echo esc_html__( 'FrontBlocks PRO Features', 'frontblocks' ); ?>
				</h2>
				<div class="tw-text-sm tw-text-gray-600">
					<p class="tw-mt-0 tw-mb-4">
						<?php
						$anchor = frbl_is_pro_active()
							? '<a href="#frontblocks_section_license" class="tw-font-medium tw-text-red-600 hover:tw-text-red-700 tw-underline">' . esc_html__( 'License', 'frontblocks' ) . '</a>'
							: '<a href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-pro-showcase" target="_blank" rel="noopener noreferrer" class="tw-font-medium tw-text-red-600 hover:tw-text-red-700 tw-underline">FrontBlocks PRO</a>';
						printf(
							frbl_is_pro_active()
								/* translators: %s: license section link */
								? esc_html__( 'Activate your license in the %s section below to unlock all features.', 'frontblocks' )
								/* translators: %s: FrontBlocks PRO link */
								: esc_html__( 'Unlock these features with %s.', 'frontblocks' ),
							$anchor // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						);
						?>
					</p>
				</div>
			</div>

			<div class="frbl-features-grid">
				<?php foreach ( $pro_blocks as $block ) : ?>
					<?php UI::show_pro_info_card( $block['icon'], $block['title'], $block['desc'] ); ?>
				<?php endforeach; ?>
			</div>

			<?php if ( ! frbl_is_pro_active() ) : ?>
			<div class="tw-mt-6 tw-text-center">
				<a
					href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-pro-cta"
					target="_blank"
					rel="noopener noreferrer"
					class="tw-inline-flex tw-items-center tw-px-6 tw-py-3 tw-border tw-border-transparent tw-text-base tw-font-medium tw-rounded-lg tw-shadow-sm tw-text-white tw-transition-colors tw-duration-200"
					style="background-color: #ef4444;"
					onmouseover="this.style.backgroundColor='#dc2626'"
					onmouseout="this.style.backgroundColor='#ef4444'"
				>
					<?php echo esc_html__( 'Get FrontBlocks PRO', 'frontblocks' ); ?> →
				</a>
			</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render license section (separate from main form).
	 *
	 * @return void
	 */
	private function render_license_section() {
		global $frblp_license;

		?>
		<div class="tw-mt-6" id="frontblocks_section_license">
			<?php
			// Check if license instance exists.
			if ( ! $frblp_license ) {
				?>
				<div class="tw-p-4 tw-rounded-lg tw-bg-red-50 tw-border tw-border-red-200">
					<p class="tw-text-sm tw-text-red-700">
						<?php echo esc_html__( 'License manager not initialized.', 'frontblocks' ); ?>
					</p>
				</div>
				<?php
				return;
			}

			// Check if License class exists (requires FrontBlocks PRO).
			if ( ! class_exists( '\Closemarketing\WPLicenseManager\License' ) ) {
				?>
				<div class="tw-p-4 tw-rounded-lg tw-bg-yellow-50 tw-border tw-border-yellow-200">
					<p class="tw-text-sm tw-text-yellow-700">
						<?php echo esc_html__( 'License management requires FrontBlocks PRO to be installed and active.', 'frontblocks' ); ?>
					</p>
				</div>
				<?php
				return;
			}

			// Render license settings inline.
			$this->render_inline_license_settings( $frblp_license );
			?>
		</div>
		<?php
	}

	/**
	 * Render inline license settings.
	 *
	 * @param \Closemarketing\WPLicenseManager\License $license License instance.
	 * @return void
	 */
	private function render_inline_license_settings( $license ) {
		// Get license data.
		$license_key    = $license->get_option_value( 'apikey' );
		$is_active      = $license->is_license_active();
		$license_status = get_option( 'frontblocks-pro_license_activated', 'Deactivated' );

		?>
		<div class="formscrm-license-wrapper">
			<!-- Main Card -->
			<div class="formscrm-card">
				<!-- Header -->
				<div class="formscrm-card-header">
					<h2><?php echo esc_html__( 'FrontBlocks PRO License', 'frontblocks' ); ?></h2>
					<p><?php echo esc_html__( 'Manage your license to receive automatic updates and support.', 'frontblocks' ); ?></p>
				</div>

				<!-- License Status -->
				<div class="formscrm-form-group">
					<?php if ( $is_active ) : ?>
						<div class="formscrm-status-box formscrm-status-active">
							<span class="formscrm-status-icon">
								<svg class="formscrm-icon" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
								</svg>
							</span>
							<span class="formscrm-status-text"><?php echo esc_html__( 'License Active', 'frontblocks' ); ?></span>
						</div>
					<?php else : ?>
						<div class="formscrm-status-box formscrm-status-inactive">
							<span class="formscrm-status-icon">
								<svg class="formscrm-icon" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
								</svg>
							</span>
							<span class="formscrm-status-text"><?php echo esc_html__( 'License Inactive', 'frontblocks' ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<!-- License Form -->
				<form method="post" action="options.php" class="formscrm-license-form">
					<?php settings_fields( 'frontblocks-pro_license' ); ?>
					<?php wp_nonce_field( 'Update_License_Options', 'license_nonce' ); ?>

					<!-- License Key Field -->
					<div class="formscrm-form-group">
						<label class="formscrm-label" for="frontblocks-pro_license_apikey">
							<?php echo esc_html__( 'License Key', 'frontblocks' ); ?>
						</label>
						<div class="formscrm-input-group">
							<input 
								type="text" 
								id="frontblocks-pro_license_apikey" 
								name="frontblocks-pro_license_apikey" 
								value="<?php echo esc_attr( $license_key ); ?>" 
								class="formscrm-input"
								placeholder="<?php echo esc_attr__( 'CTECH-XXXXX-XXXXX-XXXXX-XXXXX', 'frontblocks' ); ?>"
								<?php echo $is_active ? 'readonly' : ''; ?>
							/>
							<?php if ( $is_active ) : ?>
								<label class="formscrm-deactivate-label">
									<input type="checkbox" name="frontblocks-pro_license_deactivate_checkbox" value="on" />
									<span><?php echo esc_html__( 'Deactivate', 'frontblocks' ); ?></span>
								</label>
							<?php endif; ?>
						</div>
						<p class="formscrm-help-text">
							<?php
							printf(
								/* translators: %s: Purchase URL */
								esc_html__( 'Enter your license key. You can find it in %s.', 'frontblocks' ),
								'<a href="https://close.technology/my-account/" target="_blank">' . esc_html__( 'your account', 'frontblocks' ) . '</a>'
							);
							?>
						</p>
					</div>

					<!-- License Status -->
					<div class="formscrm-form-group">
						<label class="formscrm-label"><?php echo esc_html__( 'License Status', 'frontblocks' ); ?></label>
						<div class="formscrm-status-box <?php echo $is_active ? 'formscrm-status-active' : 'formscrm-status-inactive'; ?>">
							<span class="formscrm-status-icon">
								<?php if ( $is_active ) : ?>
									<svg class="formscrm-icon" fill="currentColor" viewBox="0 0 20 20">
										<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
									</svg>
								<?php else : ?>
									<svg class="formscrm-icon" fill="currentColor" viewBox="0 0 20 20">
										<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
									</svg>
								<?php endif; ?>
							</span>
							<span class="formscrm-status-text">
								<?php echo $is_active ? esc_html__( 'Active', 'frontblocks' ) : esc_html__( 'Not Activated', 'frontblocks' ); ?>
							</span>
						</div>
					</div>

					<!-- Submit Button -->
					<div class="formscrm-form-actions">
						<button type="submit" name="submit_license" class="formscrm-button formscrm-button-primary">
							<?php echo $is_active ? esc_html__( 'Update License', 'frontblocks' ) : esc_html__( 'Activate License', 'frontblocks' ); ?>
						</button>
					</div>
				</form>
			</div>

			<!-- Sidebar Info -->
			<div class="formscrm-info-card">
				<h3><?php echo esc_html__( 'License Benefits', 'frontblocks' ); ?></h3>
				<p><?php echo esc_html__( 'An active license provides the following benefits:', 'frontblocks' ); ?></p>
				
				<ul class="formscrm-benefits-list">
					<li><?php echo esc_html__( 'Automatic plugin updates', 'frontblocks' ); ?></li>
					<li><?php echo esc_html__( 'Access to new features', 'frontblocks' ); ?></li>
					<li><?php echo esc_html__( 'Priority support', 'frontblocks' ); ?></li>
					<li><?php echo esc_html__( 'Security patches', 'frontblocks' ); ?></li>
				</ul>

				<hr style="margin: 20px 0; border: none; border-top: 1px solid #e2e8f0;">

				<div style="font-size: 0.875rem; color: #64748b;">
					<p style="margin-bottom: 8px;">
						<strong><?php echo esc_html__( 'Need Help?', 'frontblocks' ); ?></strong>
					</p>
					<p style="margin-bottom: 8px;">
						<a href="https://close.technology/wordpress-plugins/frontblocks-pro/" target="_blank" style="color: #8b5cf6; text-decoration: none;">
							<?php echo esc_html__( 'Purchase License', 'frontblocks' ); ?> →
						</a>
					</p>
					<p style="margin-bottom: 8px;">
						<a href="https://close.technology/my-account/" target="_blank" style="color: #8b5cf6; text-decoration: none;">
							<?php echo esc_html__( 'My Account', 'frontblocks' ); ?> →
						</a>
					</p>
					<p>
						<a href="https://close.technology/support/" target="_blank" style="color: #8b5cf6; text-decoration: none;">
							<?php echo esc_html__( 'Support', 'frontblocks' ); ?> →
						</a>
					</p>
				</div>
			</div>
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

		// Get current options to preserve unchecked checkboxes.
		$current_options = get_option( 'frontblocks_settings', array() );

		// Initialize sanitized array with current values.
		$sanitized = $current_options;

		// List of all boolean options (checkboxes).
		$boolean_options = array(
			$this->option_enable_testimonials,
			$this->option_enable_reading_progress,
			$this->option_enable_back_button,
			$this->option_enable_events,
			$this->option_enable_fluid_typography,
			$this->option_enable_gutenberg,
			$this->option_enable_simple_prices_variable_products,
			$this->option_enable_after_add_to_cart,
			$this->option_deactivate_short_description,
			$this->option_move_content_to_short_description,
			$this->option_disable_zoom_images,
			$this->option_add_share_buttons,
			$this->option_deactivate_product_tabs,
			$this->option_horizontal_product_form,
			$this->option_enable_custom_post_types,
			$this->option_enable_fullpage_scroll,
			$this->option_enable_language_banner,
			$this->option_enable_popups,
			$this->option_checkout_inline,
		);

		// Initialize all boolean options to false (unchecked checkboxes are not submitted).
		foreach ( $boolean_options as $option ) {
			$sanitized[ $option ] = false;
		}

		// Process submitted values.
		foreach ( $value as $key => $val ) {
			if ( in_array( $key, $boolean_options, true ) ) {
				$sanitized[ $key ] = (bool) $val;
			} elseif ( $this->option_events_type === $key ) {
				// Sanitize events type: only allow 'cpt' or 'posts'.
				$sanitized[ $key ] = in_array( $val, array( 'cpt', 'posts' ), true ) ? $val : 'cpt';
			}
		}

		// Ensure mutual exclusion: if both description options are enabled, keep only the last one changed.
		if ( ! empty( $sanitized[ $this->option_deactivate_short_description ] ) && ! empty( $sanitized[ $this->option_move_content_to_short_description ] ) ) {
			// Get current saved values to determine which one was just changed.
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

		do_action( 'frontblocks_sanitize_settings', $sanitized );

		return $sanitized;
	}
}
