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
	 * Option key for scroll to top feature.
	 *
	 * @var string
	 */
	private $option_enable_scroll_top = 'enable_scroll_top';

	/**
	 * Option key for scroll to top button position.
	 *
	 * @var string
	 */
	private $option_scroll_top_position = 'scroll_top_position';

	/**
	 * Option key for scroll to top custom icon URL.
	 *
	 * @var string
	 */
	private $option_scroll_top_icon_url = 'scroll_top_icon_url';

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
	 * Option key for maintenance mode feature.
	 *
	 * @var string
	 */
	private $option_enable_maintenance = 'enable_maintenance';

	/**
	 * Option key for maintenance page title.
	 *
	 * @var string
	 */
	private $option_maintenance_title = 'maintenance_title';

	/**
	 * Option key for maintenance page background image (attachment ID).
	 *
	 * @var string
	 */
	private $option_maintenance_image = 'maintenance_image';

	/**
	 * Option key for the cookie notice feature.
	 *
	 * @var string
	 */
	private $option_enable_cookie_notice = 'enable_cookie_notice';

	/**
	 * Option key for the cookie notice message.
	 *
	 * @var string
	 */
	private $option_cookie_notice_message = 'cookie_notice_message';

	/**
	 * Option key for the cookie notice accept button label.
	 *
	 * @var string
	 */
	private $option_cookie_notice_accept_label = 'cookie_notice_accept_label';

	/**
	 * Option key for the cookie notice reject button label.
	 *
	 * @var string
	 */
	private $option_cookie_notice_reject_label = 'cookie_notice_reject_label';

	/**
	 * Option key for the cookie policy page ID.
	 *
	 * @var string
	 */
	private $option_cookie_notice_policy_page_id = 'cookie_notice_policy_page_id';

	/**
	 * Option key for the cookie notice layout variant.
	 *
	 * @var string
	 */
	private $option_cookie_notice_layout = 'cookie_notice_layout';

	/**
	 * Option key for the cookie notice boxed panel position.
	 *
	 * @var string
	 */
	private $option_cookie_notice_position = 'cookie_notice_position';

	/**
	 * Option key for the cookie notice accent color.
	 *
	 * @var string
	 */
	private $option_cookie_notice_color = 'cookie_notice_color';

	/**
	 * Option key for the cookie notice panel background color.
	 *
	 * @var string
	 */
	private $option_cookie_notice_bg_color = 'cookie_notice_bg_color';

	/**
	 * Option key for the cookie notice panel corner rounding.
	 *
	 * @var string
	 */
	private $option_cookie_notice_radius = 'cookie_notice_radius';

	/**
	 * Option key for the cookie notice expiration (in days).
	 *
	 * @var string
	 */
	private $option_cookie_notice_expiration_days = 'cookie_notice_expiration_days';

	/**
	 * Option key for the Google Tag Manager container ID.
	 *
	 * @var string
	 */
	private $option_cookie_notice_gtm_id = 'cookie_notice_gtm_id';

	/**
	 * Option key for the GA4 Measurement ID.
	 *
	 * @var string
	 */
	private $option_cookie_notice_ga4_id = 'cookie_notice_ga4_id';

	/**
	 * Option key for the additional tracking integrations.
	 *
	 * @var string
	 */
	private $option_cookie_notice_tracking_integrations = 'cookie_notice_tracking_integrations';

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
	 * Option key for enabling variant display mode per-attribute selector (PRO).
	 *
	 * @var string
	 */
	private $option_enable_variant_display_mode = 'enable_variant_display_mode';

	/**
	 * Option key for disabling coupon field in cart (PRO).
	 *
	 * @var string
	 */
	private $option_disable_cart_coupon = 'disable_cart_coupon';

	/**
	 * Option key for disabling cross-sells in cart (PRO).
	 *
	 * @var string
	 */
	private $option_disable_cart_cross_sells = 'disable_cart_cross_sells';

	/**
	 * Option key for disabling coupon form in checkout (PRO).
	 *
	 * @var string
	 */
	private $option_disable_checkout_coupon = 'disable_checkout_coupon';

	/**
	 * Option key for disabling order notes in checkout (PRO).
	 *
	 * @var string
	 */
	private $option_disable_checkout_order_notes = 'disable_checkout_order_notes';

	/**
	 * Option key for disabling login prompt in checkout (PRO).
	 *
	 * @var string
	 */
	private $option_disable_checkout_login_prompt = 'disable_checkout_login_prompt';

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

		// Reuse the real frontend banner styles so the settings-page preview
		// renders pixel-identical to what visitors will actually see.
		wp_enqueue_style(
			'frontblocks-cookie-notice',
			FRBL_PLUGIN_URL . 'assets/cookie-notice/frontblocks-cookie-notice.css',
			array(),
			FRBL_VERSION
		);

		wp_enqueue_media();

		wp_add_inline_script(
			'jquery',
			"
			document.addEventListener('DOMContentLoaded', function() {
				const deactivateCheckbox = document.getElementById('deactivate_short_description');
				const moveContentCheckbox = document.getElementById('move_content_to_short_description');
				
				if (deactivateCheckbox && moveContentCheckbox) {
					function updateMutualExclusion() {
						const deactivateWrapper = deactivateCheckbox.closest('.tw:flex');
						const moveContentWrapper = moveContentCheckbox.closest('.tw:flex');
						
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

				// Show/hide scroll-top sub-settings based on toggle state.
				const scrollTopCheckbox = document.getElementById('enable_scroll_top');
				const scrollTopWrapper = document.getElementById('scroll-top-settings-wrapper');

				if (scrollTopCheckbox && scrollTopWrapper) {
					const scrollTopCard = scrollTopCheckbox.closest('.frbl-feature-card');
					const scrollTopContent = scrollTopCard ? scrollTopCard.querySelector('.frbl-feature-content') : null;

					if (scrollTopCard && scrollTopContent && scrollTopContent.contains(scrollTopWrapper)) {
						scrollTopCard.appendChild(scrollTopWrapper);
					}

					function updateScrollTopVisibility() {
						if (scrollTopCheckbox.checked) {
							scrollTopWrapper.style.display = 'block';
							scrollTopWrapper.style.width = '100%';
							scrollTopWrapper.style.minWidth = '100%';
							scrollTopWrapper.style.marginTop = '1rem';
							scrollTopWrapper.style.paddingTop = '1rem';
							scrollTopWrapper.style.paddingLeft = '1rem';
							scrollTopWrapper.style.paddingRight = '1rem';
							scrollTopWrapper.style.paddingBottom = '1rem';
							scrollTopWrapper.style.borderTop = '1px solid #e5e7eb';
							scrollTopWrapper.style.backgroundColor = '#f9fafb';
							if (scrollTopCard) {
								scrollTopCard.style.display = 'flex';
								scrollTopCard.style.flexDirection = 'column';
							}
							if (scrollTopContent) {
								scrollTopContent.style.flexDirection = 'row';
								scrollTopContent.style.alignItems = 'center';
								scrollTopContent.style.justifyContent = 'space-between';
							}
						} else {
							scrollTopWrapper.style.display = 'none';
							if (scrollTopCard) {
								scrollTopCard.style.display = '';
								scrollTopCard.style.flexDirection = '';
							}
							if (scrollTopContent) {
								scrollTopContent.style.flexDirection = 'row';
								scrollTopContent.style.alignItems = 'center';
								scrollTopContent.style.justifyContent = 'space-between';
							}
						}
					}

					scrollTopCheckbox.addEventListener('change', updateScrollTopVisibility);
					updateScrollTopVisibility();

					// Media uploader for custom icon.
					const uploadBtn = document.getElementById('scroll-top-icon-upload');
					const removeBtn = document.getElementById('scroll-top-icon-remove');
					const iconInput = document.getElementById('scroll_top_icon_url');
					const iconPreview = document.getElementById('scroll-top-icon-preview');
					const iconImg = document.getElementById('scroll-top-icon-img');

					if (uploadBtn && iconInput && typeof wp !== 'undefined' && wp.media) {
						var mediaFrame;

						uploadBtn.addEventListener('click', function(e) {
							e.preventDefault();

							if (mediaFrame) {
								mediaFrame.open();
								return;
							}

							mediaFrame = wp.media({
								title: '" . esc_js( __( 'Select Icon', 'frontblocks' ) ) . "',
								button: { text: '" . esc_js( __( 'Use this image', 'frontblocks' ) ) . "' },
								multiple: false
							});

							mediaFrame.on('select', function() {
								const attachment = mediaFrame.state().get('selection').first().toJSON();
								iconInput.value = attachment.url;
								if (iconImg) { iconImg.src = attachment.url; }
								if (iconPreview) { iconPreview.style.display = ''; }
								if (removeBtn) { removeBtn.style.display = ''; }
							});

							mediaFrame.open();
						});
					}

					if (removeBtn && iconInput) {
						removeBtn.addEventListener('click', function(e) {
							e.preventDefault();
							iconInput.value = '';
							if (iconImg) { iconImg.src = ''; }
							if (iconPreview) { iconPreview.style.display = 'none'; }
							removeBtn.style.display = 'none';
						});
					}
				}

			});
			"
		);

		// Separate, isolated inline script for the maintenance mode fields: kept apart from the
		// script above so that an error there can never prevent this one from running.
		wp_add_inline_script(
			'jquery',
			"
			document.addEventListener('DOMContentLoaded', function() {
				const maintenanceCheckbox = document.getElementById('enable_maintenance');
				const maintenanceWrapper = document.getElementById('maintenance-fields-wrapper');

				if (maintenanceCheckbox && maintenanceWrapper) {
					maintenanceCheckbox.addEventListener('change', function () {
						maintenanceWrapper.style.display = maintenanceCheckbox.checked ? 'block' : 'none';
					});
				}

				// Event delegation: works even if the button is added/replaced later, and does not
				// depend on any other inline script having run successfully first.
				let mediaFrame;

				document.addEventListener('click', function (event) {
					const selectButton = event.target.closest('.frbl-maintenance-select-image');
					const removeButton = event.target.closest('.frbl-maintenance-remove-image');

					if (! selectButton && ! removeButton) {
						return;
					}

					event.preventDefault();

					const imageInput = document.getElementById('maintenance_image');
					const previewWrapper = document.querySelector('.frbl-maintenance-image-preview');
					const previewImg = previewWrapper ? previewWrapper.querySelector('img') : null;

					if (removeButton) {
						if (imageInput) {
							imageInput.value = '';
						}
						if (previewWrapper) {
							previewWrapper.style.display = 'none';
						}
						removeButton.style.display = 'none';
						return;
					}

					if (! window.wp || ! window.wp.media) {
						window.alert('" . esc_js( __( 'The media library failed to load. Please reload the page and try again.', 'frontblocks' ) ) . "');
						return;
					}

					if (mediaFrame) {
						mediaFrame.open();
						return;
					}

					mediaFrame = window.wp.media({
						title: selectButton.textContent,
						button: { text: selectButton.textContent },
						multiple: false,
						library: { type: 'image' },
					});

					mediaFrame.on('select', function () {
						const attachment = mediaFrame.state().get('selection').first().toJSON();

						if (imageInput) {
							imageInput.value = attachment.id;
						}
						if (previewImg) {
							previewImg.src = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
						}
						if (previewWrapper) {
							previewWrapper.style.display = 'block';
						}

						const currentRemoveButton = document.querySelector('.frbl-maintenance-remove-image');
						if (currentRemoveButton) {
							currentRemoveButton.style.display = '';
						}
					});

					mediaFrame.open();
				});
			});
			"
		);

		// Enqueue the WordPress media uploader for the maintenance background image field.
		wp_enqueue_media();

		// Separate, isolated inline script for the cookie notice fields, kept apart so a
		// failure in another script can never prevent this one from running.
		wp_add_inline_script(
			'jquery',
			"
			document.addEventListener('DOMContentLoaded', function() {
				const cookieCheckbox = document.getElementById('enable_cookie_notice');
				const cookieWrapper = document.getElementById('cookie-notice-fields-wrapper');

				if (cookieCheckbox && cookieWrapper) {
					cookieCheckbox.addEventListener('change', function () {
						cookieWrapper.style.display = cookieCheckbox.checked ? 'block' : 'none';
					});
				}

				const layoutSelect = document.getElementById('cookie_notice_layout');
				const positionSelect = document.getElementById('cookie_notice_position');
				const radiusSelect = document.getElementById('cookie_notice_radius');
				const positionWrapper = document.getElementById('cookie-notice-position-wrapper');
				const preview = document.getElementById('frbl-cookie-notice-preview');

				if (layoutSelect && positionWrapper) {
					layoutSelect.addEventListener('change', function () {
						positionWrapper.style.display = layoutSelect.value === 'box' ? 'block' : 'none';
					});
				}

				function updatePreviewLayout() {
					if (!preview) {
						return;
					}

					var layout = layoutSelect ? layoutSelect.value : 'bar';
					var position = positionSelect ? positionSelect.value : 'bottom-right';

					preview.className = 'frbl-cookie-notice frbl-cookie-notice-preview frbl-cookie-notice--' + layout;

					if (layout === 'box') {
						preview.className += ' frbl-cookie-notice--' + (position === 'bottom-left' ? 'left' : 'right');
					}

					if (radiusSelect) {
						var radii = { none: '0', small: '12px', large: '24px' };
						preview.style.setProperty('--frbl-cookie-radius', radii[radiusSelect.value] || radii.small);
					}
				}

				if (layoutSelect) {
					layoutSelect.addEventListener('change', updatePreviewLayout);
				}

				if (positionSelect) {
					positionSelect.addEventListener('change', updatePreviewLayout);
				}

				if (radiusSelect) {
					radiusSelect.addEventListener('change', updatePreviewLayout);
				}
			});
			"
		);

		// Isolated inline script for the tabbed admin shell: tab switching, bulk
		// enable/disable, discard, and "unsaved changes" tracking on the save bar.
		wp_add_inline_script(
			'jquery',
			"
				document.addEventListener('DOMContentLoaded', function() {
					var tabButtons = document.querySelectorAll('[data-tab-target]');
					var tabPanels  = document.querySelectorAll('[data-tab-panel]');
					var saveBar    = document.querySelector('.frbl-settings-save-bar');

				function activateTab(tabId) {
					var found = false;

					tabPanels.forEach(function (panel) {
						var match = panel.getAttribute('data-tab-panel') === tabId;
						panel.hidden = ! match;
						found = found || match;
					});

					if (saveBar) {
						saveBar.hidden = found && 'google-signin' === tabId;
					}

					if (! found) {
						return;
					}

					tabButtons.forEach(function (btn) {
						if (! btn.classList.contains('frbl-tab-btn')) {
							return;
						}
						var active = btn.getAttribute('data-tab-target') === tabId;
						btn.classList.toggle('is-active', active);
						btn.setAttribute('aria-selected', active ? 'true' : 'false');
					});
				}

				tabButtons.forEach(function (btn) {
					btn.addEventListener('click', function () {
						activateTab(btn.getAttribute('data-tab-target'));
					});
				});

				// Legacy inline links pointing at '#frontblocks_section_license'
				// (upsell notices) should open the License tab instead of jumping
				// to a hidden anchor.
				document.addEventListener('click', function (event) {
					var link = event.target.closest('a[href=\"#frontblocks_section_license\"]');
					if (link) {
						event.preventDefault();
						activateTab('license');
					}
				});

				var initialTab = 'blocks';
				var hash = window.location.hash.replace('#', '');
				if (hash === 'frontblocks_section_license') {
					hash = 'license';
				}
				if (hash && document.querySelector('[data-tab-panel=\"' + hash + '\"]')) {
					initialTab = hash;
				}
				activateTab(initialTab);

				// Keep the active tab across a save (WordPress redirects back to
				// the referring URL after options.php processes the form).
				var form = document.getElementById('frbl-settings-form');
				var referer = form ? form.querySelector('input[name=\"_wp_http_referer\"]') : null;

				if (form && referer) {
					form.addEventListener('submit', function () {
						var active = document.querySelector('.frbl-tab-btn.is-active');
						var tabId  = active ? active.getAttribute('data-tab-target') : initialTab;
						var url    = referer.value.split('#')[0];
						referer.value = url + '#' + tabId;
					});
				}

				// Bulk enable/disable within the active tab.
				document.querySelectorAll('[data-bulk-action]').forEach(function (btn) {
					btn.addEventListener('click', function () {
						var panel = btn.closest('[data-tab-panel]');
						if (! panel) {
							return;
						}
						var enable = btn.getAttribute('data-bulk-action') === 'enable';
						panel.querySelectorAll('input[type=\"checkbox\"]:not(:disabled)').forEach(function (checkbox) {
							if (checkbox.checked !== enable) {
								checkbox.checked = enable;
								checkbox.dispatchEvent(new Event('change', { bubbles: true }));
							}
						});
					});
				});

				// 'Unsaved changes' indicator on the sticky save bar.
				var saveStatus = document.querySelector('[data-save-status]');
				var savedText    = " . wp_json_encode( __( 'All changes saved.', 'frontblocks' ) ) . ';
				var unsavedText  = ' . wp_json_encode( __( 'Unsaved changes — applied on the front end immediately after saving.', 'frontblocks' ) ) . ";

				function markDirty() {
					if (saveStatus) {
						saveStatus.textContent = unsavedText;
					}
				}

				if (form && saveStatus) {
					form.addEventListener('change', markDirty);
					form.addEventListener('input', markDirty);

					form.addEventListener('reset', function () {
						setTimeout(function () {
							form.querySelectorAll('input[type=\"checkbox\"]').forEach(function (checkbox) {
								checkbox.dispatchEvent(new Event('change', { bubbles: true }));
							});
							saveStatus.textContent = savedText;
						}, 0);
					});
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
			$this->option_enable_scroll_top,
			__( 'Enable Scroll to Top', 'frontblocks' ),
			array( $this, 'field_enable_scroll_top' ),
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

		// Maintenance Mode section (own full-width section, needs room for title + image fields).
		add_settings_section(
			'frontblocks_section_maintenance',
			__( 'Maintenance Mode', 'frontblocks' ),
			array( $this, 'section_maintenance_callback' ),
			$this->page_slug
		);

		add_settings_field(
			$this->option_enable_maintenance,
			__( 'Enable Maintenance Mode', 'frontblocks' ),
			array( $this, 'field_enable_maintenance' ),
			$this->page_slug,
			'frontblocks_section_maintenance'
		);

		// Cookie Notice section (own full-width section: banner copy, layout, colors, GTM/GA4 and stats).
		add_settings_section(
			'frontblocks_section_cookie_notice',
			__( 'Cookie Notice', 'frontblocks' ),
			array( $this, 'section_cookie_notice_callback' ),
			$this->page_slug
		);

		add_settings_field(
			$this->option_enable_cookie_notice,
			__( 'Enable Cookie Notice', 'frontblocks' ),
			array( $this, 'field_enable_cookie_notice' ),
			$this->page_slug,
			'frontblocks_section_cookie_notice'
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
			$this->option_enable_variant_display_mode,
			__( 'Variant Display Mode', 'frontblocks' ),
			array( $this, 'field_enable_variant_display_mode' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_disable_cart_coupon,
			__( 'Disable Coupon Field in Cart', 'frontblocks' ),
			array( $this, 'field_disable_cart_coupon' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_disable_cart_cross_sells,
			__( 'Disable Cross-Sells in Cart', 'frontblocks' ),
			array( $this, 'field_disable_cart_cross_sells' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_disable_checkout_coupon,
			__( 'Disable Coupon Field in Checkout', 'frontblocks' ),
			array( $this, 'field_disable_checkout_coupon' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_disable_checkout_order_notes,
			__( 'Disable Order Notes in Checkout', 'frontblocks' ),
			array( $this, 'field_disable_checkout_order_notes' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_disable_checkout_login_prompt,
			__( 'Disable Login Prompt in Checkout', 'frontblocks' ),
			array( $this, 'field_disable_checkout_login_prompt' ),
			$this->page_slug,
			'frontblocks_section_woocommerce_features'
		);

		add_settings_field(
			$this->option_enable_fullpage_scroll,
			__( 'Enable Full Page Scroll', 'frontblocks' ),
			array( $this, 'field_enable_fullpage_scroll' ),
			$this->page_slug,
			'frontblocks_section_features'
		);

		add_settings_field(
			$this->option_enable_language_banner,
			__( 'Enable Language Banner', 'frontblocks' ),
			array( $this, 'field_enable_language_banner' ),
			$this->page_slug,
			'frontblocks_section_features'
		);

		// Popups section.
		add_settings_section(
			'frontblocks_section_popups',
			__( 'Popups', 'frontblocks' ),
			array( $this, 'section_popups_callback' ),
			$this->page_slug
		);

		add_settings_field(
			$this->option_enable_popups,
			__( 'Enable Popups', 'frontblocks' ),
			array( $this, 'field_enable_popups' ),
			$this->page_slug,
			'frontblocks_section_popups'
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

		global $wp_settings_sections;

		$sections    = isset( $wp_settings_sections[ $this->page_slug ] ) ? (array) $wp_settings_sections[ $this->page_slug ] : array();
		$has_cpt_tab = false;

		foreach ( $sections as $section ) {
			if ( 'frontblocks_section_custom_post_types' === $section['id'] ) {
				$has_cpt_tab = true;
				break;
			}
		}

		list( $features_on, $features_total ) = $this->count_section_toggles( 'frontblocks_section_features' );
		list( $woo_on, $woo_total )           = $this->count_section_toggles( 'frontblocks_section_woocommerce_features' );
		list( $popups_on, $popups_total )     = $this->count_section_toggles( 'frontblocks_section_popups' );

		$tabs = array(
			array(
				'id'    => 'blocks',
				'label' => __( 'Blocks', 'frontblocks' ),
			),
			array(
				'id'    => 'pro',
				'label' => __( 'PRO blocks', 'frontblocks' ),
			),
			array(
				'id'    => 'optional',
				'label' => __( 'Optional features', 'frontblocks' ),
				'on'    => $features_on,
				'total' => $features_total,
			),
			array(
				'id'    => 'maintenance',
				'label' => __( 'Maintenance', 'frontblocks' ),
			),
			array(
				'id'    => 'image-management',
				'label' => __( 'Image Management', 'frontblocks' ),
			),
			array(
				'id'    => 'woocommerce',
				'label' => __( 'WooCommerce', 'frontblocks' ),
				'on'    => $woo_on,
				'total' => $woo_total,
			),
			array(
				'id'    => 'popups',
				'label' => __( 'Popups', 'frontblocks' ),
				'on'    => $popups_on,
				'total' => $popups_total,
			),
			array(
				'id'    => 'cookies',
				'label' => __( 'Cookies', 'frontblocks' ),
			),
			array(
				'id'    => 'google-signin',
				'label' => __( 'Google Sign-In', 'frontblocks' ),
			),
			array(
				'id'    => 'cpt',
				'label' => __( 'Post types', 'frontblocks' ),
			),
			array(
				'id'    => 'license',
				'label' => __( 'License', 'frontblocks' ),
			),
		);

		/**
		 * Filters the settings tabs displayed in the FrontBlocks admin screen.
		 *
		 * Companion plugins can add a tab by appending an array with an `id` and
		 * `label` key, then render its matching panel on
		 * `frontblocks_settings_tab_panels`.
		 *
		 * @since 1.5.3
		 * @param array $tabs Settings tabs.
		 */
		$tabs = apply_filters( 'frontblocks_settings_tabs', $tabs );
		?>
		<div class="frbl-settings-wrapper">
			<div class="frbl-admin-header">
				<div class="frbl-admin-header-top">
					<div class="frbl-admin-title-group">
						<h1><?php esc_html_e( 'FrontBlocks', 'frontblocks' ); ?></h1>
						<span class="frbl-admin-subtitle"><?php esc_html_e( 'Settings', 'frontblocks' ); ?></span>
					</div>
					<span class="frbl-version-chip">v<?php echo esc_html( FRBL_VERSION ); ?></span>
					<div class="frbl-header-spacer"></div>
					<?php $this->render_license_badge(); ?>
				</div>
				<div class="frbl-tabs" role="tablist">
					<?php foreach ( $tabs as $tab ) : ?>
						<button type="button" class="frbl-tab-btn" data-tab-target="<?php echo esc_attr( $tab['id'] ); ?>" role="tab" aria-selected="false">
							<span><?php echo esc_html( $tab['label'] ); ?></span>
							<?php if ( isset( $tab['total'] ) ) : ?>
								<span class="frbl-tab-count"><?php echo esc_html( $tab['on'] . '/' . $tab['total'] ); ?></span>
							<?php endif; ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="frbl-admin-body">
				<?php
				// Show success message after settings are saved.
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['settings-updated'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) ) :
					?>
					<div style="background-color: #f0fdf4; border-left: 4px solid #4ade80; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
						<div class="tw:flex">
							<div class="tw:flex-shrink-0">
								<svg class="tw:h-5 tw:w-5" style="color: #4ade80;" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
								</svg>
							</div>
							<div class="tw:ml-3">
								<p class="tw:text-sm tw:font-medium" style="color: #15803d; margin: 0;">
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
						<div class="tw:flex">
							<div class="tw:flex-shrink-0">
								<svg class="tw:h-5 tw:w-5" style="color: #f87171;" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
								</svg>
							</div>
							<div class="tw:ml-3">
								<p class="tw:text-sm tw:font-medium" style="color: #991b1b; margin: 0;">
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
						<div class="tw:flex">
							<div class="tw:flex-shrink-0">
								<svg class="tw:h-5 tw:w-5" style="color: #4ade80;" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
								</svg>
							</div>
							<div class="tw:ml-3">
								<p class="tw:text-sm tw:font-medium" style="color: #15803d; margin: 0;">
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
						<div class="tw:flex">
							<div class="tw:flex-shrink-0">
								<svg class="tw:h-5 tw:w-5" style="color: #fbbf24;" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
								</svg>
							</div>
							<div class="tw:ml-3">
								<p class="tw:text-sm tw:font-medium" style="color: #92400e; margin: 0;">
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
						<div class="tw:flex">
							<div class="tw:flex-shrink-0">
								<svg class="tw:h-5 tw:w-5" style="color: #f87171;" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
								</svg>
							</div>
							<div class="tw:ml-3">
								<p class="tw:text-sm tw:font-medium" style="color: #991b1b; margin: 0;">
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

				$this->render_cookie_notice_cache_notice();
				?>

				<!-- Settings Form -->
				<form method="post" action="options.php" id="frbl-settings-form">
					<?php settings_fields( 'frontblocks_settings' ); ?>

					<div class="frbl-tab-panel" data-tab-panel="blocks">
						<?php $this->render_blocks_tab(); ?>
					</div>

					<div class="frbl-tab-panel" data-tab-panel="pro" hidden>
						<?php $this->render_pro_blocks_tab(); ?>
					</div>

					<div class="frbl-tab-panel" data-tab-panel="optional" hidden>
						<div class="frbl-tab-panel-head">
							<div>
								<h2><?php esc_html_e( 'Optional features', 'frontblocks' ); ?></h2>
								<p><?php esc_html_e( 'Site-wide behaviours that are not blocks — progress bars, buttons and typography.', 'frontblocks' ); ?></p>
							</div>
							<?php if ( $features_total > 0 ) : ?>
								<div class="frbl-bulk-actions">
									<button type="button" class="frbl-btn-outline" data-bulk-action="enable"><?php esc_html_e( 'Enable all', 'frontblocks' ); ?></button>
									<button type="button" class="frbl-btn-ghost" data-bulk-action="disable"><?php esc_html_e( 'Disable all', 'frontblocks' ); ?></button>
								</div>
							<?php endif; ?>
						</div>
						<?php $this->render_section_if_exists( $sections, 'frontblocks_section_features' ); ?>
					</div>

					<div class="frbl-tab-panel" data-tab-panel="maintenance" hidden>
						<?php $this->render_section_if_exists( $sections, 'frontblocks_section_maintenance' ); ?>
					</div>

					<div class="frbl-tab-panel" data-tab-panel="image-management" hidden>
						<?php $this->render_section_if_exists( $sections, 'frontblocks_section_image_management' ); ?>
					</div>

					<div class="frbl-tab-panel" data-tab-panel="woocommerce" hidden>
						<div class="frbl-tab-panel-head">
							<div>
								<h2><?php esc_html_e( 'WooCommerce', 'frontblocks' ); ?></h2>
								<p><?php esc_html_e( 'Additions and removals applied to product, cart and checkout pages.', 'frontblocks' ); ?></p>
							</div>
							<?php if ( $woo_total > 0 ) : ?>
								<div class="frbl-bulk-actions">
									<button type="button" class="frbl-btn-outline" data-bulk-action="enable"><?php esc_html_e( 'Enable all', 'frontblocks' ); ?></button>
									<button type="button" class="frbl-btn-ghost" data-bulk-action="disable"><?php esc_html_e( 'Disable all', 'frontblocks' ); ?></button>
								</div>
							<?php endif; ?>
						</div>
						<?php $this->render_section_if_exists( $sections, 'frontblocks_section_woocommerce_features' ); ?>
					</div>

					<div class="frbl-tab-panel" data-tab-panel="popups" hidden>
						<div class="frbl-tab-panel-head">
							<div>
								<h2><?php esc_html_e( 'Popups', 'frontblocks' ); ?></h2>
								<p><?php esc_html_e( 'Create popup content with the block editor and choose when and where it appears.', 'frontblocks' ); ?></p>
							</div>
							<?php if ( $popups_total > 0 ) : ?>
								<div class="frbl-bulk-actions">
									<button type="button" class="frbl-btn-outline" data-bulk-action="enable"><?php esc_html_e( 'Enable all', 'frontblocks' ); ?></button>
									<button type="button" class="frbl-btn-ghost" data-bulk-action="disable"><?php esc_html_e( 'Disable all', 'frontblocks' ); ?></button>
								</div>
							<?php endif; ?>
						</div>
						<?php $this->render_section_if_exists( $sections, 'frontblocks_section_popups' ); ?>
					</div>

					<div class="frbl-tab-panel" data-tab-panel="cookies" hidden>
						<?php $this->render_section_if_exists( $sections, 'frontblocks_section_cookie_notice' ); ?>
					</div>

					<?php if ( $has_cpt_tab ) : ?>
						<div class="frbl-tab-panel" data-tab-panel="cpt" hidden>
							<?php $this->render_section_if_exists( $sections, 'frontblocks_section_custom_post_types' ); ?>
						</div>
					<?php endif; ?>

					<!-- Submit Button -->
					<div class="frbl-settings-save-bar">
						<span class="frbl-save-status" data-save-status><?php esc_html_e( 'All changes saved.', 'frontblocks' ); ?></span>
						<div class="frbl-save-bar-spacer"></div>
						<button type="reset" class="frbl-btn-ghost" data-discard-btn><?php esc_html_e( 'Discard', 'frontblocks' ); ?></button>
						<button type="submit" class="frbl-btn-primary">
							<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
							</svg>
							<?php echo esc_html__( 'Save settings', 'frontblocks' ); ?>
						</button>
					</div>
				</form>

				<div class="frbl-tab-panel" data-tab-panel="google-signin" hidden>
					<?php
					// Rendered outside the main settings form: Google Sign-In manages
					// its own form, nonce, and save handling (see GoogleSignIn\Settings).
					$this->render_section_if_exists( $sections, 'frontblocks_section_google_signin' );
					?>
				</div>
				<?php do_action( 'frontblocks_settings_tab_panels' ); ?>

				<?php if ( ! $has_cpt_tab ) : ?>
					<div class="frbl-tab-panel" data-tab-panel="cpt" hidden>
						<?php
						$this->render_pro_upsell_card(
							__( 'Custom Post Types builder', 'frontblocks' ),
							__( 'Create and manage post types with advanced configuration options, directly from the admin panel.', 'frontblocks' ),
							'settings-cpt-tab'
						);
						?>
					</div>
				<?php endif; ?>

				<div class="frbl-tab-panel" data-tab-panel="license" id="frontblocks_section_license" hidden>
					<?php
					// Render license section (outside the settings form: it posts to its own option group).
					if ( frbl_is_pro_active() ) {
						$this->render_license_section();
					} else {
						$this->render_pro_upsell_card(
							__( 'PRO license', 'frontblocks' ),
							__( 'Install FrontBlocks PRO to manage your license key, automatic updates and priority support from here.', 'frontblocks' ),
							'settings-license-tab'
						);
					}
					?>
				</div>

				<?php $this->render_debug_section(); ?>

				<!-- Footer Info -->
				<div class="frbl-admin-footer">
					<?php
					printf(
						/* translators: %s: Close·marketing link */
						esc_html__( 'Made with ❤️ by %s', 'frontblocks' ),
						'<a href="https://close.technology/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings" target="_blank" rel="noopener noreferrer">Close·Technology</a>'
					);
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Find a registered settings section by id and render it, if present.
	 *
	 * @param array  $sections   All registered sections for this page (from $wp_settings_sections).
	 * @param string $section_id Section id to look for.
	 * @return void
	 */
	private function render_section_if_exists( array $sections, $section_id ) {
		foreach ( $sections as $section ) {
			if ( $section['id'] === $section_id ) {
				$this->render_settings_section( $section );
				return;
			}
		}
	}

	/**
	 * Count how many toggle fields of a section are currently enabled.
	 *
	 * @param string $section_id Section id.
	 * @return array{0:int,1:int} [enabled count, total count].
	 */
	private function count_section_toggles( $section_id ) {
		global $wp_settings_fields;

		if ( empty( $wp_settings_fields[ $this->page_slug ][ $section_id ] ) ) {
			return array( 0, 0 );
		}

		$options = get_option( 'frontblocks_settings', array() );
		$total   = 0;
		$on      = 0;

		foreach ( $wp_settings_fields[ $this->page_slug ][ $section_id ] as $field ) {
			++$total;
			if ( ! empty( $options[ $field['id'] ] ) ) {
				++$on;
			}
		}

		return array( $on, $total );
	}

	/**
	 * Render the license status badge shown in the sticky header.
	 *
	 * @return void
	 */
	private function render_license_badge() {
		if ( ! frbl_is_pro_active() ) {
			?>
			<a
				href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-header-cta"
				target="_blank"
				rel="noopener noreferrer"
				class="frbl-license-badge frbl-license-badge--none"
			>
				<?php esc_html_e( 'Get FrontBlocks PRO', 'frontblocks' ); ?>
			</a>
			<?php
			return;
		}

		if ( $this->is_license_valid ) {
			?>
			<span class="frbl-license-badge frbl-license-badge--active">
				<span class="frbl-license-dot"></span>
				<?php esc_html_e( 'PRO license active', 'frontblocks' ); ?>
			</span>
			<?php
			return;
		}
		?>
		<span class="frbl-license-badge frbl-license-badge--inactive">
			<?php esc_html_e( 'License not active', 'frontblocks' ); ?>
		</span>
		<button type="button" class="frbl-license-manage" data-tab-target="license"><?php esc_html_e( 'Activate', 'frontblocks' ); ?></button>
		<?php
	}

	/**
	 * Render the "Blocks" tab: core blocks always available in the block editor.
	 *
	 * @return void
	 */
	private function render_blocks_tab() {
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
		?>
		<div class="frbl-tab-panel-head">
			<div>
				<h2><?php esc_html_e( 'Blocks & features', 'frontblocks' ); ?></h2>
				<p><?php esc_html_e( 'Core blocks available in the block editor. Always active.', 'frontblocks' ); ?></p>
			</div>
		</div>
		<div class="frbl-features-grid">
			<?php foreach ( $active_blocks as $block ) : ?>
				<?php UI::show_info_card( $block['icon'], $block['title'], $block['desc'] ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render the "PRO blocks" tab.
	 *
	 * @return void
	 */
	private function render_pro_blocks_tab() {
		$pro_blocks = apply_filters( 'frbl_pro_blocks', $this->get_default_pro_blocks() );
		?>
		<div class="frbl-tab-panel-head">
			<div>
				<h2><?php esc_html_e( 'PRO blocks', 'frontblocks' ); ?></h2>
				<p><?php esc_html_e( 'Included with your active license. Each one adds a block or an editor capability.', 'frontblocks' ); ?></p>
			</div>
		</div>
		<div class="frbl-features-grid">
			<?php foreach ( $pro_blocks as $block ) : ?>
				<?php UI::show_pro_info_card( $block['icon'], $block['title'], $block['desc'] ); ?>
			<?php endforeach; ?>
		</div>
		<?php if ( ! frbl_is_pro_active() ) : ?>
			<div class="frbl-tab-cta">
				<a
					href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-pro-tab"
					target="_blank"
					rel="noopener noreferrer"
					class="frbl-btn-primary"
				>
					<?php esc_html_e( 'Get FrontBlocks PRO', 'frontblocks' ); ?> →
				</a>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render a generic PRO upsell card, used by tabs whose feature is entirely
	 * absent (not just unlicensed) when FrontBlocks PRO isn't installed.
	 *
	 * @param string $title        Card title.
	 * @param string $desc         Card description.
	 * @param string $utm_campaign utm_campaign value for the outbound link.
	 * @return void
	 */
	private function render_pro_upsell_card( $title, $desc, $utm_campaign ) {
		$pro_url = 'https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=' . rawurlencode( $utm_campaign );
		?>
		<div class="frbl-upsell-card">
			<span class="frbl-pro-chip">PRO</span>
			<h2><?php echo esc_html( $title ); ?></h2>
			<p><?php echo esc_html( $desc ); ?></p>
			<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" rel="noopener noreferrer" class="frbl-btn-primary">
				<?php esc_html_e( 'Get FrontBlocks PRO', 'frontblocks' ); ?> →
			</a>
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
		<div class="tw:mt-8 tw:p-6 tw:bg-yellow-50 tw:border tw:border-yellow-200 tw:rounded-lg">
			<h3 class="tw:text-lg tw:font-semibold tw:text-gray-900 tw:mb-4">
				🐛 Debug: Fluid Typography Settings
			</h3>
			<p class="tw:text-sm tw:text-gray-600 tw:mb-4">
				<?php echo esc_html__( 'This shows the GeneratePress font settings being used by the Fluid Typography module.', 'frontblocks' ); ?>
			</p>
			<div class="tw:bg-white tw:p-4 tw:rounded tw:border tw:border-gray-300 tw:overflow-auto" style="max-height: 400px;">
				<pre style="margin: 0; font-size: 12px;"><?php print_r( $font_settings ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r ?></pre>
			</div>
			<p class="tw:text-xs tw:text-gray-500 tw:mt-4">
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

		$pro_blocks = apply_filters( 'frbl_pro_blocks', $this->get_default_pro_blocks() );

		?>
		<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
			<?php echo esc_html__( 'These blocks and features are always active and available in the block editor.', 'frontblocks' ); ?>
		</p>
		<div class="frbl-features-grid">
			<?php
			foreach ( $active_blocks as $block ) {
				UI::show_info_card( $block['icon'], $block['title'], $block['desc'] );
			}

			foreach ( $pro_blocks as $block ) {
				UI::show_pro_info_card( $block['icon'], $block['title'], $block['desc'] );
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
		<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
			<?php echo esc_html__( 'Enable or disable these optional features as needed.', 'frontblocks' ); ?>
		</p>
		<?php
	}

	/**
	 * Popups section callback.
	 *
	 * @return void
	 */
	private function section_popups_callback() {
		?>
		<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
			<?php echo esc_html__( 'Create popup content with the block editor and configure when and where it appears.', 'frontblocks' ); ?>
		</p>
		<?php
	}

	/**
	 * Maintenance mode section callback.
	 *
	 * @return void
	 */
	private function section_maintenance_callback() {
		?>
		<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
			<?php echo esc_html__( 'Show a full-screen curtain page on every URL of the site while you work on it.', 'frontblocks' ); ?>
		</p>
		<?php
	}

	/**
	 * Cookie Notice section callback.
	 *
	 * @return void
	 */
	private function section_cookie_notice_callback() {
		?>
		<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
			<?php echo esc_html__( 'Show a cookie consent banner and only load Google Tag Manager / GA4 after a visitor accepts.', 'frontblocks' ); ?>
		</p>
		<?php
		$this->render_advanced_cookies_upsell();
	}

	/**
	 * Show what FrontBlocks PRO's Advanced Cookie Management adds on top of
	 * this banner. The feature's own settings (and the fields to configure
	 * it) live entirely in the PRO plugin — this is only a plain message,
	 * shown when PRO isn't active/licensed yet.
	 *
	 * @return void
	 */
	private function render_advanced_cookies_upsell() {
		if ( frbl_is_pro_active() && $this->is_license_valid ) {
			// Already unlocked: the PRO plugin renders its own settings section
			// right below this one.
			return;
		}

		if ( ! frbl_is_pro_active() ) {
			echo '<div class="tw:bg-blue-50 tw:border-l-4 tw:border-blue-400 tw:p-4 tw:mb-4">';
			echo '<p class="tw:text-sm tw:text-blue-700 tw:font-medium tw:mb-1">' . esc_html__( 'Want per-category consent?', 'frontblocks' ) . '</p>';
			echo '<p class="tw:text-sm tw:text-blue-700">';
			printf(
				/* translators: %s: FrontBlocks PRO link */
				esc_html__( '%s adds a "Customize" option so visitors can accept Analytics and Marketing cookies separately, with native Google Ads, Meta Pixel and Microsoft Clarity integrations that only load once accepted.', 'frontblocks' ),
				'<a href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-cookie-notice" target="_blank" rel="noopener noreferrer" class="tw:font-medium tw:underline">FrontBlocks PRO</a>'
			);
			echo '</p>';
			echo '</div>';
		} else {
			echo '<div class="tw:bg-yellow-50 tw:border-l-4 tw:border-yellow-400 tw:p-4 tw:mb-4">';
			echo '<p class="tw:text-sm tw:text-yellow-700">';
			printf(
				/* translators: %s: License section link */
				esc_html__( 'Advanced Cookie Management (per-category consent, Google Ads / Meta Pixel / Microsoft Clarity) is included with FrontBlocks PRO. Activate your license in the %s section below to unlock it.', 'frontblocks' ),
				'<a href="#frontblocks_section_license" class="tw:font-medium tw:underline">' . esc_html__( 'License', 'frontblocks' ) . '</a>'
			);
			echo '</p>';
			echo '</div>';
		}
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

		// Check if this is a section that needs full width (rich fields, not a simple toggle grid).
		$is_cpt_section = in_array( $section['id'], array( 'frontblocks_section_custom_post_types', 'frontblocks_section_maintenance', 'frontblocks_section_cookie_notice', 'frontblocks_section_image_management' ), true );

		// Show PRO CTA button before the Optional Features section.
		if ( 'frontblocks_section_features' === $section['id'] && ! $this->is_license_valid ) {
			?>
			<div class="tw:mb-4">
				<a
					href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-optional-cta"
					target="_blank"
					rel="noopener noreferrer"
					class="tw:inline-flex tw:items-center tw:border tw:border-transparent tw:text-sm tw:font-medium tw:rounded-lg tw:shadow-sm tw:transition-colors tw:duration-200"
					style="background-color: #ef4444; color: #fff; padding: 10px 20px;"
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
					<h2 class="tw:text-2xl tw:font-bold tw:text-gray-900 tw:mb-0">
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
			<div class="frbl-card tw:bg-white tw:rounded-lg tw:shadow-sm tw:border tw:border-gray-200 tw:overflow-hidden frbl-animate-slide-in tw:mb-8">
				<div class="tw:px-6 tw:py-5 tw:border-b tw:border-gray-200 tw:bg-gradient-to-r tw:from-gray-50 tw:to-white">
					<h2 class="tw:text-xl tw:font-semibold tw:text-gray-900">
						<?php echo esc_html( $section['title'] ); ?>
					</h2>
					<?php
					if ( $section['callback'] ) {
						echo '<div class="tw:mt-2 tw:text-sm tw:text-gray-600">';
						call_user_func( $section['callback'], $section );
						echo '</div>';
					}
					?>
				</div>
				<div class="tw:px-6 tw:py-5">
					<?php
					foreach ( (array) $wp_settings_fields[ $this->page_slug ][ $section['id'] ] as $field ) {
						call_user_func( $field['callback'], $field['args'] );
					}
					?>
				</div>
			</div>
			<?php
		} else {
			// Render regular sections with feature grid. The "Optional features" and
			// "WooCommerce" and "Popups" tabs already print their own <h2>/description
			// in the tab head, so skip the duplicate title for those sections — the callback (which
			// may render a PRO upsell notice) still runs.
			$suppress_title = in_array( $section['id'], array( 'frontblocks_section_features', 'frontblocks_section_woocommerce_features', 'frontblocks_section_popups' ), true );
			?>
			<div class="frbl-section-wrapper">
				<?php if ( ! $suppress_title ) : ?>
					<!-- Section Header -->
					<div class="frbl-section-header">
						<h2 class="tw:text-2xl tw:font-bold tw:text-gray-900 tw:mb-0">
							<?php echo esc_html( $section['title'] ); ?>
						</h2>
						<?php
						if ( $section['callback'] ) {
							echo '<div class="tw:text-sm tw:text-gray-600">';
							call_user_func( $section['callback'], $section );
							echo '</div>';
						}
						?>
					</div>
				<?php elseif ( $section['callback'] ) : ?>
					<div class="tw:text-sm tw:text-gray-600 tw:mb-4">
						<?php call_user_func( $section['callback'], $section ); ?>
					</div>
				<?php endif; ?>

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
				$this->option_enable_variant_display_mode,
				$this->option_enable_fullpage_scroll,
				$this->option_enable_language_banner,
				$this->option_enable_popups,
				$this->option_checkout_inline,
				$this->option_disable_cart_coupon,
				$this->option_disable_cart_cross_sells,
				$this->option_disable_checkout_coupon,
				$this->option_disable_checkout_order_notes,
				$this->option_disable_checkout_login_prompt,
			),
			true
		);

		// Apply PRO styling only if license is not valid.
		$needs_license = $is_pro_feature && ! $this->is_license_valid;

		// Get icon for this feature.
		$icon = $this->get_feature_icon( $field['id'] );

		// Short descriptions for each field.
		$descriptions = array(
			$this->option_enable_testimonials           => __( 'Add a testimonials block to display customer reviews.', 'frontblocks' ),
			$this->option_enable_reading_progress       => __( 'Show a progress bar at the top of the page while reading posts.', 'frontblocks' ),
			$this->option_enable_back_button            => __( 'Add a floating back button for easy navigation.', 'frontblocks' ),
			$this->option_enable_scroll_top             => __( 'Add a floating button to scroll back to the top of the page.', 'frontblocks' ),
			$this->option_enable_events                 => __( 'Register and display events using a CPT or blog posts.', 'frontblocks' ),
			$this->option_enable_fluid_typography       => __( 'Font sizes scale smoothly between mobile and desktop using CSS clamp().', 'frontblocks' ),
			$this->option_enable_popups                 => __( 'Create popups with the block editor and configure when and where they appear.', 'frontblocks' ),
			$this->option_enable_gutenberg              => __( 'Use the block editor to write WooCommerce product descriptions.', 'frontblocks' ),
			$this->option_enable_simple_prices_variable_products => __( 'Show a simplified price range for variable products.', 'frontblocks' ),
			$this->option_enable_after_add_to_cart      => __( 'Insert custom block content right after the Add to Cart button.', 'frontblocks' ),
			$this->option_deactivate_short_description  => __( 'Remove the short description field from product pages.', 'frontblocks' ),
			$this->option_move_content_to_short_description => __( 'Move the main product content into the short description area.', 'frontblocks' ),
			$this->option_disable_zoom_images           => __( 'Remove the zoom effect on WooCommerce product images.', 'frontblocks' ),
			$this->option_add_share_buttons             => __( 'Add social share buttons to WooCommerce product pages.', 'frontblocks' ),
			$this->option_deactivate_product_tabs       => __( 'Remove the default description, reviews and attributes tabs.', 'frontblocks' ),
			$this->option_horizontal_product_form       => __( 'Display quantity and Add to Cart button side by side.', 'frontblocks' ),
			$this->option_enable_variant_display_mode   => __( 'Choose per attribute whether to display product variations as a dropdown or radio buttons.', 'frontblocks' ),
			$this->option_enable_fullpage_scroll        => __( 'Enable full-page scroll navigation between sections.', 'frontblocks' ),
			$this->option_enable_language_banner        => __( 'Show a banner when the visitor language differs from the site language.', 'frontblocks' ),
			$this->option_checkout_inline               => __( 'Display address, email and phone fields side by side in the WooCommerce checkout.', 'frontblocks' ),
			$this->option_disable_zoom_images           => __( 'Remove the zoom effect on WooCommerce product images.', 'frontblocks' ),
			$this->option_add_share_buttons             => __( 'Add social share buttons to WooCommerce product pages.', 'frontblocks' ),
			$this->option_deactivate_product_tabs       => __( 'Remove the default description, reviews and attributes tabs.', 'frontblocks' ),
			$this->option_horizontal_product_form       => __( 'Display quantity and Add to Cart button side by side.', 'frontblocks' ),
			$this->option_enable_fullpage_scroll        => __( 'Enable full-page scroll navigation between sections.', 'frontblocks' ),
			$this->option_enable_language_banner        => __( 'Show a banner when the visitor language differs from the site language.', 'frontblocks' ),
			$this->option_disable_cart_coupon           => __( 'Hide the coupon code input field from the Cart page.', 'frontblocks' ),
			$this->option_disable_cart_cross_sells      => __( 'Remove the cross-sell product suggestions from the Cart page.', 'frontblocks' ),
			$this->option_disable_checkout_coupon       => __( 'Hide the coupon code form shown above the Checkout form.', 'frontblocks' ),
			$this->option_disable_checkout_order_notes  => __( 'Remove the order notes / additional information field from Checkout.', 'frontblocks' ),
			$this->option_disable_checkout_login_prompt => __( 'Hide the login and registration prompt shown before the Checkout form.', 'frontblocks' ),
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
			$this->option_enable_testimonials           => 'testimonials',
			$this->option_enable_reading_progress       => 'reading-progress',
			$this->option_enable_back_button            => 'back-button',
			$this->option_enable_scroll_top             => 'scroll-top',
			$this->option_enable_events                 => 'events',
			$this->option_enable_fluid_typography       => 'fluid-typography',
			$this->option_enable_popups                 => 'popups',
			$this->option_enable_gutenberg              => 'gutenberg',
			$this->option_enable_simple_prices_variable_products => 'simple-prices',
			$this->option_enable_after_add_to_cart      => 'after-add-to-cart',
			$this->option_deactivate_short_description  => 'deactivate-description',
			$this->option_move_content_to_short_description => 'move-content',
			$this->option_disable_zoom_images           => 'disable-zoom',
			$this->option_add_share_buttons             => 'share-buttons',
			$this->option_deactivate_product_tabs       => 'deactivate-tabs',
			$this->option_horizontal_product_form       => 'horizontal-form',
			$this->option_enable_variant_display_mode   => 'default',
			$this->option_enable_fullpage_scroll        => 'fullpage-scroll',
			$this->option_enable_language_banner        => 'language-banner',
			$this->option_checkout_inline               => 'horizontal-form',
			$this->option_disable_zoom_images           => 'disable-zoom',
			$this->option_add_share_buttons             => 'share-buttons',
			$this->option_deactivate_product_tabs       => 'deactivate-tabs',
			$this->option_horizontal_product_form       => 'horizontal-form',
			$this->option_enable_fullpage_scroll        => 'fullpage-scroll',
			$this->option_enable_language_banner        => 'language-banner',
			$this->option_disable_cart_coupon           => 'default',
			$this->option_disable_cart_cross_sells      => 'default',
			$this->option_disable_checkout_coupon       => 'default',
			$this->option_disable_checkout_order_notes  => 'default',
			$this->option_disable_checkout_login_prompt => 'default',
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
			echo '<div class="tw:bg-blue-50 tw:border-l-4 tw:border-blue-400 tw:p-4 tw:mb-4">';
			echo '<div class="tw:flex">';
			echo '<div class="tw:flex-shrink-0">';
			echo '<svg class="tw:h-5 tw:w-5 tw:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';
			echo '</div>';
			echo '<div class="tw:ml-3">';
			echo '<p class="tw:text-sm tw:text-blue-700">';
			printf(
				/* translators: %s: FrontBlocks PRO link */
				esc_html__( 'These features require %s. Upgrade to unlock advanced functionality.', 'frontblocks' ),
				'<a href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings" target="_blank" class="tw:font-medium tw:underline">FrontBlocks PRO</a>'
			);
			echo '</p>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		} elseif ( ! $this->is_license_valid ) {
			echo '<div class="tw:bg-yellow-50 tw:border-l-4 tw:border-yellow-400 tw:p-4 tw:mb-4">';
			echo '<div class="tw:flex">';
			echo '<div class="tw:flex-shrink-0">';
			echo '<svg class="tw:h-5 tw:w-5 tw:text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';
			echo '</div>';
			echo '<div class="tw:ml-3">';
			echo '<p class="tw:text-sm tw:text-yellow-700">';
			printf(
				/* translators: %s: License section link */
				esc_html__( 'License is not activated. Please activate your license in the %s section below to enable these features.', 'frontblocks' ),
				'<a href="#frontblocks_section_license" class="tw:font-medium tw:underline">' . esc_html__( 'License', 'frontblocks' ) . '</a>'
			);
			echo '</p>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		} else {
			?>
			<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
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
	 * Render toggle field and sub-settings for scroll to top.
	 *
	 * @return void
	 */
	public function field_enable_scroll_top() {
		$options  = get_option( 'frontblocks_settings', array() );
		$enabled  = (bool) ( $options[ $this->option_enable_scroll_top ] ?? false );
		$position = sanitize_text_field( $options[ $this->option_scroll_top_position ] ?? 'bottom-right' );
		$icon_url = esc_url( $options[ $this->option_scroll_top_icon_url ] ?? '' );
		?>
		<label class="frbl-toggle">
			<input type="checkbox"
				id="<?php echo esc_attr( $this->option_enable_scroll_top ); ?>"
				name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_scroll_top ); ?>]"
				value="1"
				<?php checked( true, $enabled ); ?>
			/>
			<span></span>
		</label>

		<!-- Sub-settings - moved below the card by JavaScript -->
		<div id="scroll-top-settings-wrapper" style="<?php echo $enabled ? '' : 'display: none;'; ?>">
			<div class="tw-mb-4">
				<label for="<?php echo esc_attr( $this->option_scroll_top_position ); ?>" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
					<?php echo esc_html__( 'Position', 'frontblocks' ); ?>
				</label>
				<select
					id="<?php echo esc_attr( $this->option_scroll_top_position ); ?>"
					name="frontblocks_settings[<?php echo esc_attr( $this->option_scroll_top_position ); ?>]"
					class="tw-block tw-pl-3 tw-pr-8 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-border-transparent"
				>
					<option value="bottom-right" <?php selected( $position, 'bottom-right' ); ?>>
						<?php echo esc_html__( 'Bottom right', 'frontblocks' ); ?>
					</option>
					<option value="bottom-left" <?php selected( $position, 'bottom-left' ); ?>>
						<?php echo esc_html__( 'Bottom left', 'frontblocks' ); ?>
					</option>
				</select>
			</div>

			<div>
				<label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">
					<?php echo esc_html__( 'Custom icon (optional)', 'frontblocks' ); ?>
				</label>
				<div id="scroll-top-icon-preview" class="tw-mb-2" style="<?php echo $icon_url ? '' : 'display: none;'; ?>">
					<img id="scroll-top-icon-img" src="<?php echo esc_url( $icon_url ); ?>" alt="" style="width: 48px; height: 48px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 0.375rem; padding: 4px;" />
				</div>
				<input type="hidden" id="scroll_top_icon_url" name="frontblocks_settings[<?php echo esc_attr( $this->option_scroll_top_icon_url ); ?>]" value="<?php echo esc_attr( $icon_url ); ?>" />
				<div class="tw-flex tw-gap-2 tw-items-center">
					<button type="button" id="scroll-top-icon-upload" class="tw-px-3 tw-py-1.5 tw-text-sm tw-border tw-border-gray-300 tw-rounded-lg tw-bg-white hover:tw-bg-gray-50 tw-text-gray-700 tw-transition-colors">
						<?php echo esc_html__( 'Select image', 'frontblocks' ); ?>
					</button>
					<button type="button" id="scroll-top-icon-remove" class="tw-px-3 tw-py-1.5 tw-text-sm tw-border tw-border-red-200 tw-rounded-lg tw-bg-white hover:tw-bg-red-50 tw-text-red-600 tw-transition-colors" style="<?php echo $icon_url ? '' : 'display: none;'; ?>">
						<?php echo esc_html__( 'Remove', 'frontblocks' ); ?>
					</button>
				</div>
				<p class="tw-text-xs tw-text-gray-500 tw-mt-2">
					<?php echo esc_html__( 'Upload an SVG, PNG or any image. Leave empty to use the default arrow icon.', 'frontblocks' ); ?>
				</p>
			</div>
		</div>
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
		<div id="events-type-wrapper" class="tw:mt-4" style="<?php echo $enabled ? 'width: 100%; min-width: 100%; display: block;' : 'display: none;'; ?>">
			<label for="<?php echo esc_attr( $this->option_events_type ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
				<?php echo esc_html__( 'Event type', 'frontblocks' ); ?>
			</label>
			<select 
				id="<?php echo esc_attr( $this->option_events_type ); ?>" 
				name="frontblocks_settings[<?php echo esc_attr( $this->option_events_type ); ?>]"
				class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
				style="width: 100%; min-width: 100%; max-width: 100%; box-sizing: border-box;"
			>
				<option value="cpt" <?php selected( $events_type, 'cpt' ); ?>>
					<?php echo esc_html__( 'Custom Post Type (CPT)', 'frontblocks' ); ?>
				</option>
				<option value="posts" <?php selected( $events_type, 'posts' ); ?>>
					<?php echo esc_html__( 'Blog posts', 'frontblocks' ); ?>
				</option>
			</select>
			<p class="tw:text-xs tw:text-gray-500 tw:mt-2">
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
	 * Render toggle field for enable maintenance mode.
	 *
	 * @return void
	 */
	public function field_enable_maintenance() {
		$options   = get_option( 'frontblocks_settings', array() );
		$enabled   = (bool) ( $options[ $this->option_enable_maintenance ] ?? false );
		$title     = (string) ( $options[ $this->option_maintenance_title ] ?? '' );
		$image_id  = (int) ( $options[ $this->option_maintenance_image ] ?? 0 );
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
		?>
		<div class="frbl-maintenance-wrapper">
			<div class="tw:flex tw:items-center tw:justify-between tw:mb-4">
				<label for="<?php echo esc_attr( $this->option_enable_maintenance ); ?>" class="tw:text-base tw:font-medium tw:text-gray-900">
					<?php echo esc_html__( 'Enable Maintenance Mode', 'frontblocks' ); ?>
				</label>
				<label class="frbl-toggle">
					<input type="checkbox"
						id="<?php echo esc_attr( $this->option_enable_maintenance ); ?>"
						name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_maintenance ); ?>]"
						value="1"
						<?php checked( true, $enabled ); ?>
					/>
					<span></span>
				</label>
			</div>

			<div id="maintenance-fields-wrapper" style="<?php echo $enabled ? '' : 'display: none;'; ?>">
				<div class="tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200">
					<label for="<?php echo esc_attr( $this->option_maintenance_title ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
						<?php echo esc_html__( 'Maintenance page title', 'frontblocks' ); ?>
					</label>
					<input
						type="text"
						id="<?php echo esc_attr( $this->option_maintenance_title ); ?>"
						name="frontblocks_settings[<?php echo esc_attr( $this->option_maintenance_title ); ?>]"
						value="<?php echo esc_attr( $title ); ?>"
						placeholder="<?php echo esc_attr__( 'We are currently performing maintenance', 'frontblocks' ); ?>"
						class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
					/>

					<label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mt-4 tw:mb-2">
						<?php echo esc_html__( 'Background image', 'frontblocks' ); ?>
					</label>
					<input type="hidden" id="<?php echo esc_attr( $this->option_maintenance_image ); ?>" name="frontblocks_settings[<?php echo esc_attr( $this->option_maintenance_image ); ?>]" value="<?php echo esc_attr( $image_id ); ?>" />
					<div class="frbl-maintenance-image-preview tw:mb-2" style="<?php echo $image_url ? '' : 'display:none;'; ?>">
						<img src="<?php echo esc_url( $image_url ? $image_url : '' ); ?>" alt="" style="max-width: 200px; height: auto; border-radius: 8px; display: block;" />
					</div>
					<div class="tw:flex tw:gap-2">
						<button type="button" class="button frbl-maintenance-select-image">
							<?php echo esc_html__( 'Select image', 'frontblocks' ); ?>
						</button>
						<button type="button" class="button frbl-maintenance-remove-image" style="<?php echo $image_url ? '' : 'display:none;'; ?>">
							<?php echo esc_html__( 'Remove image', 'frontblocks' ); ?>
						</button>
					</div>
					<p class="tw:text-xs tw:text-gray-500 tw:mt-2 tw:mb-0">
						<?php echo esc_html__( 'Shown as the full-screen background while maintenance mode is active. Recommended size: 1920×1080px.', 'frontblocks' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render toggle field and sub-settings for the cookie notice banner.
	 *
	 * @return void
	 */
	public function field_enable_cookie_notice() {
		$options               = get_option( 'frontblocks_settings', array() );
		$enabled               = (bool) ( $options[ $this->option_enable_cookie_notice ] ?? false );
		$message               = (string) ( $options[ $this->option_cookie_notice_message ] ?? '' );
		$accept_label          = (string) ( $options[ $this->option_cookie_notice_accept_label ] ?? '' );
		$reject_label          = (string) ( $options[ $this->option_cookie_notice_reject_label ] ?? '' );
		$policy_page_id        = (int) ( $options[ $this->option_cookie_notice_policy_page_id ] ?? 0 );
		$layout                = (string) ( $options[ $this->option_cookie_notice_layout ] ?? 'bar' );
		$position              = (string) ( $options[ $this->option_cookie_notice_position ] ?? 'bottom-right' );
		$color                 = (string) ( $options[ $this->option_cookie_notice_color ] ?? '#687df9' );
		$bg_color              = (string) ( $options[ $this->option_cookie_notice_bg_color ] ?? '#ffffff' );
		$radius                = (string) ( $options[ $this->option_cookie_notice_radius ] ?? 'small' );
		$expiration            = (int) ( $options[ $this->option_cookie_notice_expiration_days ] ?? 365 );
		$gtm_id                = (string) ( $options[ $this->option_cookie_notice_gtm_id ] ?? '' );
		$ga4_id                = (string) ( $options[ $this->option_cookie_notice_ga4_id ] ?? '' );
		$tracking_integrations = \FrontBlocks\Frontend\CookieNotice::get_tracking_integrations( $options );
		$site_kit_tags         = $this->get_google_site_kit_managed_tags();
		$accepted_count        = (int) get_option( \FrontBlocks\Frontend\CookieNotice::STATS_OPTION_ACCEPTED, 0 );
		$rejected_count        = (int) get_option( \FrontBlocks\Frontend\CookieNotice::STATS_OPTION_REJECTED, 0 );
		$total_count           = $accepted_count + $rejected_count;
		$acceptance_pct        = $total_count > 0 ? round( ( $accepted_count / $total_count ) * 100, 1 ) : 0;
		?>
		<div class="frbl-cookie-notice-wrapper">
			<div class="tw:flex tw:items-center tw:justify-between tw:mb-4">
				<label for="<?php echo esc_attr( $this->option_enable_cookie_notice ); ?>" class="tw:text-base tw:font-medium tw:text-gray-900">
					<?php echo esc_html__( 'Enable Cookie Notice', 'frontblocks' ); ?>
				</label>
				<label class="frbl-toggle">
					<input type="checkbox"
						id="<?php echo esc_attr( $this->option_enable_cookie_notice ); ?>"
						name="frontblocks_settings[<?php echo esc_attr( $this->option_enable_cookie_notice ); ?>]"
						value="1"
						<?php checked( true, $enabled ); ?>
					/>
					<span></span>
				</label>
			</div>

			<div id="cookie-notice-fields-wrapper" style="<?php echo $enabled ? '' : 'display: none;'; ?>">

				<?php if ( $total_count > 0 ) : ?>
					<div class="tw:flex tw:gap-4 tw:mb-4 tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200">
						<div class="tw:flex-1">
							<p class="tw:text-xs tw:text-gray-500 tw:mb-1"><?php echo esc_html__( 'Acceptance rate', 'frontblocks' ); ?></p>
							<p class="tw:text-2xl tw:font-bold tw:text-gray-900 tw:m-0"><?php echo esc_html( $acceptance_pct ); ?>%</p>
						</div>
						<div class="tw:flex-1">
							<p class="tw:text-xs tw:text-gray-500 tw:mb-1"><?php echo esc_html__( 'Accepted', 'frontblocks' ); ?></p>
							<p class="tw:text-2xl tw:font-bold tw:text-gray-900 tw:m-0"><?php echo esc_html( $accepted_count ); ?></p>
						</div>
						<div class="tw:flex-1">
							<p class="tw:text-xs tw:text-gray-500 tw:mb-1"><?php echo esc_html__( 'Rejected', 'frontblocks' ); ?></p>
							<p class="tw:text-2xl tw:font-bold tw:text-gray-900 tw:m-0"><?php echo esc_html( $rejected_count ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<div class="tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200 tw:mb-4">
					<label for="<?php echo esc_attr( $this->option_cookie_notice_message ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
						<?php echo esc_html__( 'Message', 'frontblocks' ); ?>
					</label>
					<textarea
						id="<?php echo esc_attr( $this->option_cookie_notice_message ); ?>"
						name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_message ); ?>]"
						rows="3"
						placeholder="<?php echo esc_attr__( 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies.', 'frontblocks' ); ?>"
						class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
					><?php echo esc_textarea( $message ); ?></textarea>

					<div class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4">
						<div>
							<label for="<?php echo esc_attr( $this->option_cookie_notice_accept_label ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
								<?php echo esc_html__( 'Accept button label', 'frontblocks' ); ?>
							</label>
							<input
								type="text"
								id="<?php echo esc_attr( $this->option_cookie_notice_accept_label ); ?>"
								name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_accept_label ); ?>]"
								value="<?php echo esc_attr( $accept_label ); ?>"
								placeholder="<?php echo esc_attr__( 'Accept', 'frontblocks' ); ?>"
								class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
							/>
						</div>
						<div>
							<label for="<?php echo esc_attr( $this->option_cookie_notice_reject_label ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
								<?php echo esc_html__( 'Reject button label', 'frontblocks' ); ?>
							</label>
							<input
								type="text"
								id="<?php echo esc_attr( $this->option_cookie_notice_reject_label ); ?>"
								name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_reject_label ); ?>]"
								value="<?php echo esc_attr( $reject_label ); ?>"
								placeholder="<?php echo esc_attr__( 'Reject', 'frontblocks' ); ?>"
								class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
							/>
						</div>
					</div>

					<label for="<?php echo esc_attr( $this->option_cookie_notice_policy_page_id ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mt-4 tw:mb-2">
						<?php echo esc_html__( 'Cookie policy page (optional)', 'frontblocks' ); ?>
					</label>
					<select
						id="<?php echo esc_attr( $this->option_cookie_notice_policy_page_id ); ?>"
						name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_policy_page_id ); ?>]"
						class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
					>
						<option value=""><?php echo esc_html__( '— None —', 'frontblocks' ); ?></option>
						<?php
						$pages_limit = 300;
						$pages       = get_pages(
							array(
								'sort_column' => 'post_title',
								'number'      => $pages_limit,
							)
						);

						// The saved page may fall outside the limited list above (e.g. sorted
						// past it alphabetically on a large site) — make sure it still shows up
						// and stays selected instead of silently disappearing from the dropdown.
						$selected_page_listed = 0 === $policy_page_id;
						foreach ( $pages as $page ) {
							if ( $policy_page_id === $page->ID ) {
								$selected_page_listed = true;
								break;
							}
						}

						if ( ! $selected_page_listed ) {
							$selected_page = get_post( $policy_page_id );
							if ( $selected_page instanceof \WP_Post ) {
								array_unshift( $pages, $selected_page );
							}
						}

						foreach ( $pages as $page ) {
							printf(
								'<option value="%1$d" %2$s>%3$s</option>',
								(int) $page->ID,
								selected( $policy_page_id, $page->ID, false ),
								esc_html( $page->post_title )
							);
						}
						?>
					</select>
					<?php if ( count( $pages ) >= $pages_limit ) : ?>
						<p class="tw:text-xs tw:text-amber-600 tw:mt-2">
							<?php
							printf(
								/* translators: %d: number of pages shown in the dropdown. */
								esc_html__( 'Showing the first %d pages. If the page you need is missing, search for it in the Pages list to find its ID and set it via the frontblocks_settings option.', 'frontblocks' ),
								(int) $pages_limit
							);
							?>
						</p>
					<?php endif; ?>
					<p class="tw:text-xs tw:text-gray-500 tw:mt-2">
						<?php echo esc_html__( 'The banner is hidden on this page so visitors can read it before deciding. A "Learn more" link to it is added to the message.', 'frontblocks' ); ?>
					</p>
				</div>

				<div class="tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200 tw:mb-4">
					<div class="tw:grid tw:grid-cols-3 tw:gap-4">
						<div>
							<label for="<?php echo esc_attr( $this->option_cookie_notice_layout ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
								<?php echo esc_html__( 'Layout', 'frontblocks' ); ?>
							</label>
							<select
								id="<?php echo esc_attr( $this->option_cookie_notice_layout ); ?>"
								name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_layout ); ?>]"
								class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
							>
								<option value="bar" <?php selected( $layout, 'bar' ); ?>><?php echo esc_html__( 'Full-width bar', 'frontblocks' ); ?></option>
								<option value="box" <?php selected( $layout, 'box' ); ?>><?php echo esc_html__( 'Boxed panel', 'frontblocks' ); ?></option>
								<option value="popup" <?php selected( $layout, 'popup' ); ?>><?php echo esc_html__( 'Centered popup', 'frontblocks' ); ?></option>
							</select>
						</div>
						<div id="cookie-notice-position-wrapper" style="<?php echo 'box' === $layout ? '' : 'display: none;'; ?>">
							<label for="<?php echo esc_attr( $this->option_cookie_notice_position ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
								<?php echo esc_html__( 'Position', 'frontblocks' ); ?>
							</label>
							<select
								id="<?php echo esc_attr( $this->option_cookie_notice_position ); ?>"
								name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_position ); ?>]"
								class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
							>
								<option value="bottom-right" <?php selected( $position, 'bottom-right' ); ?>><?php echo esc_html__( 'Bottom right', 'frontblocks' ); ?></option>
								<option value="bottom-left" <?php selected( $position, 'bottom-left' ); ?>><?php echo esc_html__( 'Bottom left', 'frontblocks' ); ?></option>
							</select>
						</div>
						<div>
							<label for="<?php echo esc_attr( $this->option_cookie_notice_color ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
								<?php echo esc_html__( 'Accent color', 'frontblocks' ); ?>
							</label>
							<input
								type="color"
								id="<?php echo esc_attr( $this->option_cookie_notice_color ); ?>"
								name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_color ); ?>]"
								value="<?php echo esc_attr( $color ); ?>"
								class="tw:h-10 tw:w-full tw:border tw:border-gray-300 tw:rounded-lg"
							/>
						</div>
					</div>

					<div class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4">
						<div>
							<label for="<?php echo esc_attr( $this->option_cookie_notice_bg_color ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
								<?php echo esc_html__( 'Background color', 'frontblocks' ); ?>
							</label>
							<input
								type="color"
								id="<?php echo esc_attr( $this->option_cookie_notice_bg_color ); ?>"
								name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_bg_color ); ?>]"
								value="<?php echo esc_attr( $bg_color ); ?>"
								class="tw:h-10 tw:w-full tw:border tw:border-gray-300 tw:rounded-lg"
							/>
						</div>
						<div>
							<label for="<?php echo esc_attr( $this->option_cookie_notice_radius ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
								<?php echo esc_html__( 'Corner rounding', 'frontblocks' ); ?>
							</label>
							<select
								id="<?php echo esc_attr( $this->option_cookie_notice_radius ); ?>"
								name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_radius ); ?>]"
								class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
							>
								<option value="none" <?php selected( $radius, 'none' ); ?>><?php echo esc_html__( 'None', 'frontblocks' ); ?></option>
								<option value="small" <?php selected( $radius, 'small' ); ?>><?php echo esc_html__( 'Slightly rounded', 'frontblocks' ); ?></option>
								<option value="large" <?php selected( $radius, 'large' ); ?>><?php echo esc_html__( 'Very rounded', 'frontblocks' ); ?></option>
							</select>
						</div>
					</div>

					<div class="tw:mt-4">
						<p class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
							<?php echo esc_html__( 'Preview', 'frontblocks' ); ?>
						</p>
						<?php
						$preview_accent_text = \FrontBlocks\Frontend\CookieNotice::get_readable_text_color( $color );
						$preview_accent_link = \FrontBlocks\Frontend\CookieNotice::get_readable_on_white_color( $color );
						$preview_panel_text  = \FrontBlocks\Frontend\CookieNotice::get_readable_text_color( $bg_color );
						$preview_radius      = \FrontBlocks\Frontend\CookieNotice::get_radius_value( $radius );
						?>
						<div id="frbl-cookie-notice-preview-stage" class="frbl-cookie-notice-preview-stage">
							<div
								id="frbl-cookie-notice-preview"
								class="frbl-cookie-notice frbl-cookie-notice-preview frbl-cookie-notice--<?php echo esc_attr( $layout ); ?><?php echo 'box' === $layout ? ' frbl-cookie-notice--' . ( 'bottom-left' === $position ? 'left' : 'right' ) : ''; ?>"
								style="--frbl-cookie-accent: <?php echo esc_attr( $color ); ?>; --frbl-cookie-accent-contrast: <?php echo esc_attr( $preview_accent_text ); ?>; --frbl-cookie-accent-on-light: <?php echo esc_attr( $preview_accent_link ); ?>; --frbl-cookie-bg: <?php echo esc_attr( $bg_color ); ?>; --frbl-cookie-text: <?php echo esc_attr( $preview_panel_text ); ?>; --frbl-cookie-radius: <?php echo esc_attr( $preview_radius ); ?>;"
							>
								<div class="frbl-cookie-notice__panel">
									<span id="frbl-cookie-notice-preview-icon" class="frbl-cookie-notice__icon">
										<?php echo \FrontBlocks\Frontend\CookieNotice::get_cookie_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG, no dynamic data. ?>
									</span>
									<p class="frbl-cookie-notice__message">
										<?php echo esc_html( '' !== $message ? $message : __( 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies.', 'frontblocks' ) ); ?>
									</p>
									<div class="frbl-cookie-notice__actions">
										<button type="button" class="frbl-cookie-notice__button frbl-cookie-notice__button--reject" disabled>
											<?php echo esc_html( '' !== $reject_label ? $reject_label : __( 'Reject', 'frontblocks' ) ); ?>
										</button>
										<button type="button" class="frbl-cookie-notice__button frbl-cookie-notice__button--accept" disabled>
											<?php echo esc_html( '' !== $accept_label ? $accept_label : __( 'Accept', 'frontblocks' ) ); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>

					<label for="<?php echo esc_attr( $this->option_cookie_notice_expiration_days ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mt-4 tw:mb-2">
						<?php echo esc_html__( 'Cookie expiration (days)', 'frontblocks' ); ?>
					</label>
					<input
						type="number"
						min="1"
						max="730"
						id="<?php echo esc_attr( $this->option_cookie_notice_expiration_days ); ?>"
						name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_expiration_days ); ?>]"
						value="<?php echo esc_attr( $expiration ); ?>"
						class="tw:block tw:w-32 tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
					/>
				</div>

				<div class="tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200">
					<?php if ( $site_kit_tags['gtm'] || $site_kit_tags['ga4'] ) : ?>
						<p class="tw:text-sm tw:text-gray-600 tw:m-0">
							<?php echo esc_html__( 'Google Site Kit manages the configured Google tag. FrontBlocks applies Consent Mode to it, so no duplicate ID is needed here.', 'frontblocks' ); ?>
						</p>
					<?php endif; ?>

					<?php if ( ! $site_kit_tags['gtm'] || ! $site_kit_tags['ga4'] ) : ?>
						<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
							<?php echo esc_html__( 'Scripts are only requested after a visitor accepts — never before.', 'frontblocks' ); ?>
						</p>
						<div class="tw:grid tw:grid-cols-1 tw:gap-4<?php echo ! $site_kit_tags['gtm'] && ! $site_kit_tags['ga4'] ? ' tw:grid-cols-2' : ''; ?>">
							<?php if ( ! $site_kit_tags['gtm'] ) : ?>
							<div>
								<label for="<?php echo esc_attr( $this->option_cookie_notice_gtm_id ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
									<?php echo esc_html__( 'Google Tag Manager ID', 'frontblocks' ); ?>
								</label>
								<input
									type="text"
									id="<?php echo esc_attr( $this->option_cookie_notice_gtm_id ); ?>"
									name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_gtm_id ); ?>]"
									value="<?php echo esc_attr( $gtm_id ); ?>"
									placeholder="GTM-XXXXXXX"
									class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
								/>
							</div>
							<?php endif; ?>
							<?php if ( ! $site_kit_tags['ga4'] ) : ?>
							<div>
								<label for="<?php echo esc_attr( $this->option_cookie_notice_ga4_id ); ?>" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
									<?php echo esc_html__( 'GA4 Measurement ID', 'frontblocks' ); ?>
								</label>
								<input
									type="text"
									id="<?php echo esc_attr( $this->option_cookie_notice_ga4_id ); ?>"
									name="frontblocks_settings[<?php echo esc_attr( $this->option_cookie_notice_ga4_id ); ?>]"
									value="<?php echo esc_attr( $ga4_id ); ?>"
									placeholder="G-XXXXXXXXXX"
									class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:text-base tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
								/>
							</div>
							<?php endif; ?>
						</div>

						<?php if ( $enabled && $this->is_gtm4wp_container_loading( $gtm_id ) ) : ?>
						<div class="tw:mt-4 tw:p-4 tw:bg-amber-50 tw:border tw:border-amber-200 tw:rounded-lg" role="alert">
							<p class="tw:text-sm tw:font-medium tw:text-amber-900 tw:mt-0 tw:mb-2">
								<?php echo esc_html__( 'Google Tag Manager may load twice.', 'frontblocks' ); ?>
							</p>
							<p class="tw:text-sm tw:text-amber-800 tw:m-0">
								<?php
								printf(
									wp_kses(
										/* translators: %s: Google Tag Manager for WordPress settings page URL. */
										__( 'The same container is enabled in Google Tag Manager for WordPress. Disable its container-code injection in <a href="%s">its settings</a> so FrontBlocks can load it only after consent. Its data layer can remain enabled.', 'frontblocks' ),
										array( 'a' => array( 'href' => array() ) )
									),
									esc_url( admin_url( 'options-general.php?page=gtm4wp-settings' ) )
								);
								?>
							</p>
						</div>
						<?php endif; ?>
					<?php endif; ?>

					<div class="tw:mt-4">
						<?php
						$tracking_labels = array(
							'clientify_analytics_plus'    => __( 'Clientify Analytics Plus', 'frontblocks' ),
							'clientify_analytics_classic' => __( 'Clientify Analytics (classic)', 'frontblocks' ),
							'brevo'                       => __( 'Brevo', 'frontblocks' ),
							'openai_chatgpt_ads'          => __( 'ChatGPT Ads', 'frontblocks' ),
						);
						?>
						<p class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
							<?php echo esc_html__( 'Added tracking integrations', 'frontblocks' ); ?>
						</p>
						<?php if ( $tracking_integrations ) : ?>
							<ul class="tw:space-y-2 tw:mb-4">
								<?php foreach ( $tracking_integrations as $integration ) : ?>
									<li class="tw:flex tw:items-center tw:justify-between tw:gap-4 tw:p-3 tw:bg-gray-50 tw:border tw:border-gray-200 tw:rounded-lg">
										<span class="tw:text-sm tw:text-gray-700">
											<strong><?php echo esc_html( apply_filters( 'frbl_cookie_notice_tracking_type_label', $tracking_labels[ $integration['type'] ] ?? $integration['type'], $integration['type'] ) ); ?></strong>
											<span class="tw:font-mono tw:text-xs">(<?php echo esc_html( $integration['id'] ); ?>)</span>
										</span>
										<label class="tw:text-sm tw:text-red-700 tw:whitespace-nowrap">
											<input type="checkbox" name="frontblocks_settings[cookie_notice_tracking_remove][]" value="<?php echo esc_attr( $integration['type'] ); ?>" />
											<?php echo esc_html__( 'Remove', 'frontblocks' ); ?>
										</label>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p class="tw:text-sm tw:text-gray-500 tw:mb-4"><?php echo esc_html__( 'No additional tracking integrations have been added.', 'frontblocks' ); ?></p>
						<?php endif; ?>
						<label for="cookie_notice_tracking_integration_code" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
							<?php echo esc_html__( 'Add a tracking integration', 'frontblocks' ); ?>
						</label>
						<input
							type="text"
							id="cookie_notice_tracking_integration_code"
							name="frontblocks_settings[cookie_notice_tracking_integration_code]"
							value=""
							placeholder="<?php echo esc_attr__( 'Paste a supported tracking code or ID…', 'frontblocks' ); ?>"
							class="tw:block tw:w-full tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:font-mono tw:text-xs tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent"
						/>
						<p class="tw:text-xs tw:text-gray-500 tw:mt-2 tw:mb-0">
							<?php
							printf(
								wp_kses(
									/* translators: %s: contact page URL. */
									__( 'For security reasons, only a supported integration ID is extracted and saved; the pasted code is discarded. Need another tool supported? <a href="%s" target="_blank" rel="noopener noreferrer">Contact us</a>.', 'frontblocks' ),
									array(
										'a' => array(
											'href'   => array(),
											'target' => array(),
											'rel'    => array(),
										),
									)
								),
								esc_url( 'https://close.technology/contacto' )
							);
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check whether GTM4WP is set to load the same container as Cookie Notice.
	 *
	 * @param string $frontblocks_gtm_id FrontBlocks GTM container ID.
	 * @return bool
	 */
	private function is_gtm4wp_container_loading( $frontblocks_gtm_id ) {
		if ( '' === $frontblocks_gtm_id || ( ! defined( 'GTM4WP_OPTIONS' ) && ! function_exists( 'gtm4wp_the_gtm_tag' ) ) ) {
			return false;
		}

		$gtm4wp_options = get_option( 'gtm4wp-options', array() );
		if ( ! is_array( $gtm4wp_options ) ) {
			return false;
		}

		$placement_off = defined( 'GTM4WP_PLACEMENT_OFF' ) ? (int) GTM4WP_PLACEMENT_OFF : 3;
		$placement     = isset( $gtm4wp_options['gtm-code-placement'] ) ? (int) $gtm4wp_options['gtm-code-placement'] : 0;

		if ( $placement_off === $placement ) {
			return false;
		}

		$container_ids = array_filter(
			array_map(
				'trim',
				explode( ',', (string) ( $gtm4wp_options['gtm-code'] ?? '' ) )
			)
		);

		if ( isset( $gtm4wp_options['gtm-containers'] ) && is_array( $gtm4wp_options['gtm-containers'] ) ) {
			foreach ( $gtm4wp_options['gtm-containers'] as $container ) {
				if ( is_array( $container ) && ! empty( $container['id'] ) ) {
					$container_ids[] = (string) $container['id'];
				}
			}
		}

		$container_ids = array_map( 'strtoupper', $container_ids );

		return in_array( strtoupper( $frontblocks_gtm_id ), $container_ids, true );
	}

	/**
	 * Get the Google tags that Site Kit is configured to place.
	 *
	 * @return array{gtm: bool, ga4: bool}
	 */
	private function get_google_site_kit_managed_tags() {
		$tags = array(
			'gtm' => false,
			'ga4' => false,
		);

		if ( ! defined( 'GOOGLESITEKIT_VERSION' ) && ! class_exists( '\\Google\\Site_Kit\\Plugin' ) ) {
			return $tags;
		}

		$tag_manager_settings = get_option( 'googlesitekit_tagmanager_settings', array() );
		if ( is_array( $tag_manager_settings ) && ! empty( $tag_manager_settings['containerID'] ) && ( ! isset( $tag_manager_settings['useSnippet'] ) || $tag_manager_settings['useSnippet'] ) ) {
			$tags['gtm'] = true;
		}

		$analytics_settings = get_option( 'googlesitekit_analytics-4_settings', array() );
		if ( is_array( $analytics_settings ) && ! empty( $analytics_settings['measurementID'] ) && ( ! isset( $analytics_settings['useSnippet'] ) || $analytics_settings['useSnippet'] ) ) {
			$tags['ga4'] = true;
		}

		return $tags;
	}

	/**
	 * Render a one-time cache notice after Cookie Notice settings are saved.
	 *
	 * @return void
	 */
	private function render_cookie_notice_cache_notice() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$notice = get_transient( 'frbl_cookie_notice_cache_notice_' . $user_id );
		if ( ! $notice ) {
			return;
		}

		delete_transient( 'frbl_cookie_notice_cache_notice_' . $user_id );
		?>
		<div style="background-color: #eff6ff; border-left: 4px solid #60a5fa; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);">
			<p class="tw:text-sm tw:font-medium" style="color: #1d4ed8; margin: 0;">
				<?php
				if ( 'wp-rocket' === $notice ) {
					esc_html_e( 'Cookie Notice settings were updated and the WP Rocket cache was cleared.', 'frontblocks' );
				} else {
					esc_html_e( 'Cookie Notice settings were updated. If you use a full-page cache, purge it now so visitors receive the new configuration.', 'frontblocks' );
				}
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render toggle field for enable popups (PRO feature).
	 *
	 * @return void
	 */
	public function field_enable_popups() {
		$this->render_pro_toggle( $this->option_enable_popups );
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
	 * Render Variant Display Mode toggle field.
	 *
	 * @return void
	 */
	public function field_enable_variant_display_mode() {
		$this->render_pro_toggle( $this->option_enable_variant_display_mode );
	}

	/**
	 * Render Disable Coupon Field in Cart field.
	 *
	 * @return void
	 */
	public function field_disable_cart_coupon() {
		$this->render_pro_toggle( $this->option_disable_cart_coupon );
	}

	/**
	 * Render Disable Cross-Sells in Cart field.
	 *
	 * @return void
	 */
	public function field_disable_cart_cross_sells() {
		$this->render_pro_toggle( $this->option_disable_cart_cross_sells );
	}

	/**
	 * Render Disable Coupon Field in Checkout field.
	 *
	 * @return void
	 */
	public function field_disable_checkout_coupon() {
		$this->render_pro_toggle( $this->option_disable_checkout_coupon );
	}

	/**
	 * Render Disable Order Notes in Checkout field.
	 *
	 * @return void
	 */
	public function field_disable_checkout_order_notes() {
		$this->render_pro_toggle( $this->option_disable_checkout_order_notes );
	}

	/**
	 * Render Disable Login Prompt in Checkout field.
	 *
	 * @return void
	 */
	public function field_disable_checkout_login_prompt() {
		$this->render_pro_toggle( $this->option_disable_checkout_login_prompt );
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
			echo '<div class="tw:bg-blue-50 tw:border-l-4 tw:border-blue-400 tw:p-4 tw:mb-4">';
			echo '<div class="tw:flex">';
			echo '<div class="tw:flex-shrink-0">';
			echo '<svg class="tw:h-5 tw:w-5 tw:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';
			echo '</div>';
			echo '<div class="tw:ml-3">';
			echo '<p class="tw:text-sm tw:text-blue-700">';
			printf(
				/* translators: %s: FrontBlocks PRO link */
				esc_html__( 'This feature requires %s. Upgrade to unlock advanced functionality.', 'frontblocks' ),
				'<a href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings" target="_blank" class="tw:font-medium tw:underline">FrontBlocks PRO</a>'
			);
			echo '</p>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		} elseif ( ! $this->is_license_valid ) {
			echo '<div class="tw:bg-yellow-50 tw:border-l-4 tw:border-yellow-400 tw:p-4 tw:mb-4">';
			echo '<div class="tw:flex">';
			echo '<div class="tw:flex-shrink-0">';
			echo '<svg class="tw:h-5 tw:w-5 tw:text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';
			echo '</div>';
			echo '<div class="tw:ml-3">';
			echo '<p class="tw:text-sm tw:text-yellow-700">';
			printf(
				/* translators: %s: License section link */
				esc_html__( 'License is not activated. Please activate your license in the %s section below to enable these features.', 'frontblocks' ),
				'<a href="#frontblocks_section_license" class="tw:font-medium tw:underline">' . esc_html__( 'License', 'frontblocks' ) . '</a>'
			);
			echo '</p>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		} else {
			?>
			<p class="tw:text-sm tw:text-gray-600 tw:mt-0 tw:mb-4">
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
			<div class="tw:flex tw:items-center tw:justify-between tw:mb-4">
				<label for="<?php echo esc_attr( $this->option_enable_custom_post_types ); ?>" class="tw:text-base tw:font-medium tw:text-gray-900">
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
					<div class="tw:mt-4 tw:p-4 tw:bg-gray-50 tw:rounded-lg tw:border tw:border-gray-200">
						<label for="frbl-cpt-name" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
							<?php echo esc_html__( 'Post Type Name', 'frontblocks' ); ?>
						</label>
						<div class="tw:flex tw:gap-2">
							<input 
								type="text" 
								id="frbl-cpt-name" 
								class="tw:flex-1 tw:px-3 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:focus:border-transparent" 
								placeholder="<?php echo esc_attr__( 'e.g., Portfolio, Team, Services', 'frontblocks' ); ?>"
							/>
							<button 
								type="button" 
								id="frbl-create-cpt-btn" 
								class="tw:px-4 tw:py-2 tw:bg-primary-500 tw:text-white tw:rounded-lg tw:hover:bg-primary-600 tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-primary-500 tw:transition-colors"
							>
								<?php echo esc_html__( 'Create', 'frontblocks' ); ?>
							</button>
						</div>
						<p class="tw:text-xs tw:text-gray-500 tw:mt-2">
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
			array(
				'icon'  => 'popups',
				'title' => __( 'Popups', 'frontblocks' ),
				'desc'  => __( 'Create popups with the block editor and configure when and where they appear.', 'frontblocks' ),
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
		<div class="frbl-section-wrapper tw:mt-6">
			<div class="frbl-section-header">
				<h2 class="tw:text-2xl tw:font-bold tw:text-gray-900 tw:mb-0">
					<?php echo esc_html__( 'FrontBlocks PRO Features', 'frontblocks' ); ?>
				</h2>
				<div class="tw:text-sm tw:text-gray-600">
					<p class="tw:mt-0 tw:mb-4">
						<?php
						$anchor = frbl_is_pro_active()
							? '<a href="#frontblocks_section_license" class="tw:font-medium tw:text-red-600 tw:hover:text-red-700 tw:underline">' . esc_html__( 'License', 'frontblocks' ) . '</a>'
							: '<a href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-pro-showcase" target="_blank" rel="noopener noreferrer" class="tw:font-medium tw:text-red-600 tw:hover:text-red-700 tw:underline">FrontBlocks PRO</a>';
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
			<div class="tw:mt-6 tw:text-center">
				<a
					href="https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-pro-cta"
					target="_blank"
					rel="noopener noreferrer"
					class="tw:inline-flex tw:items-center tw:px-6 tw:py-3 tw:border tw:border-transparent tw:text-base tw:font-medium tw:rounded-lg tw:shadow-sm tw:transition-colors tw:duration-200"
					style="background-color: #ef4444; color: #fff;"
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
		<div class="tw:mt-6">
			<?php
			// Check if license instance exists.
			if ( ! $frblp_license ) {
				?>
				<div class="tw:p-4 tw:rounded-lg tw:bg-red-50 tw:border tw:border-red-200">
					<p class="tw:text-sm tw:text-red-700">
						<?php echo esc_html__( 'License manager not initialized.', 'frontblocks' ); ?>
					</p>
				</div>
				<?php
				return;
			}

			// Check if License class exists (requires FrontBlocks PRO).
			if ( ! class_exists( '\Closemarketing\WPLicenseManager\License' ) ) {
				?>
				<div class="tw:p-4 tw:rounded-lg tw:bg-yellow-50 tw:border tw:border-yellow-200">
					<p class="tw:text-sm tw:text-yellow-700">
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
			$this->option_enable_scroll_top,
			$this->option_enable_events,
			$this->option_enable_fluid_typography,
			$this->option_enable_maintenance,
			$this->option_enable_cookie_notice,
			$this->option_enable_gutenberg,
			$this->option_enable_simple_prices_variable_products,
			$this->option_enable_after_add_to_cart,
			$this->option_deactivate_short_description,
			$this->option_move_content_to_short_description,
			$this->option_disable_zoom_images,
			$this->option_add_share_buttons,
			$this->option_deactivate_product_tabs,
			$this->option_horizontal_product_form,
			$this->option_enable_variant_display_mode,
			$this->option_enable_custom_post_types,
			$this->option_enable_fullpage_scroll,
			$this->option_enable_language_banner,
			$this->option_enable_popups,
			$this->option_checkout_inline,
			$this->option_disable_cart_coupon,
			$this->option_disable_cart_cross_sells,
			$this->option_disable_checkout_coupon,
			$this->option_disable_checkout_order_notes,
			$this->option_disable_checkout_login_prompt,
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
			} elseif ( $this->option_scroll_top_position === $key ) {
				$sanitized[ $key ] = in_array( $val, array( 'bottom-right', 'bottom-left' ), true ) ? $val : 'bottom-right';
			} elseif ( $this->option_scroll_top_icon_url === $key ) {
				$sanitized[ $key ] = esc_url_raw( $val );
			} elseif ( $this->option_maintenance_title === $key ) {
				$sanitized[ $key ] = sanitize_text_field( $val );
			} elseif ( $this->option_maintenance_image === $key ) {
				$sanitized[ $key ] = absint( $val );
			} elseif ( $this->option_cookie_notice_message === $key ) {
				$sanitized[ $key ] = sanitize_textarea_field( $val );
			} elseif ( in_array( $key, array( $this->option_cookie_notice_accept_label, $this->option_cookie_notice_reject_label ), true ) ) {
				$sanitized[ $key ] = sanitize_text_field( $val );
			} elseif ( $this->option_cookie_notice_policy_page_id === $key ) {
				$sanitized[ $key ] = absint( $val );
			} elseif ( $this->option_cookie_notice_layout === $key ) {
				$sanitized[ $key ] = in_array( $val, array( 'bar', 'box', 'popup' ), true ) ? $val : 'bar';
			} elseif ( $this->option_cookie_notice_position === $key ) {
				$sanitized[ $key ] = in_array( $val, array( 'bottom-right', 'bottom-left' ), true ) ? $val : 'bottom-right';
			} elseif ( $this->option_cookie_notice_color === $key ) {
				$hex_color         = sanitize_hex_color( $val );
				$sanitized[ $key ] = $hex_color ? $hex_color : '#687df9';
			} elseif ( $this->option_cookie_notice_bg_color === $key ) {
				$hex_color         = sanitize_hex_color( $val );
				$sanitized[ $key ] = $hex_color ? $hex_color : '#ffffff';
			} elseif ( $this->option_cookie_notice_radius === $key ) {
				$sanitized[ $key ] = in_array( $val, array( 'none', 'small', 'large' ), true ) ? $val : 'small';
			} elseif ( $this->option_cookie_notice_expiration_days === $key ) {
				$days              = absint( $val );
				$sanitized[ $key ] = $days > 0 ? min( $days, 730 ) : 365;
			} elseif ( $this->option_cookie_notice_gtm_id === $key ) {
				$gtm_id            = strtoupper( sanitize_text_field( $val ) );
				$sanitized[ $key ] = preg_match( '/^GTM-[A-Z0-9]+$/', $gtm_id ) ? $gtm_id : '';
			} elseif ( $this->option_cookie_notice_ga4_id === $key ) {
				$ga4_id            = strtoupper( sanitize_text_field( $val ) );
				$sanitized[ $key ] = preg_match( '/^G-[A-Z0-9]+$/', $ga4_id ) ? $ga4_id : '';
			}
		}

		if ( array_key_exists( 'cookie_notice_tracking_integration_code', $value ) || array_key_exists( 'cookie_notice_tracking_remove', $value ) ) {
			$tracking_integrations = \FrontBlocks\Frontend\CookieNotice::get_tracking_integrations( $current_options, true );
			$remove_types          = isset( $value['cookie_notice_tracking_remove'] ) && is_array( $value['cookie_notice_tracking_remove'] ) ? array_map( 'sanitize_key', $value['cookie_notice_tracking_remove'] ) : array();
			$tracking_integrations = array_values(
				array_filter(
					$tracking_integrations,
					static function ( $integration ) use ( $remove_types ) {
						return ! in_array( $integration['type'], $remove_types, true );
					}
				)
			);

			$raw_code = (string) ( $value['cookie_notice_tracking_integration_code'] ?? '' );
			$detected = \FrontBlocks\Frontend\CookieNotice::detect_tracking_snippet( $raw_code );

			if ( null === $detected && '' !== trim( $raw_code ) ) {
				add_settings_error(
					'frontblocks_settings',
					'frbl_cookie_notice_tracking_unrecognized',
					sprintf(
						/* translators: %s: contact page URL. */
						esc_html__( 'The tracking code was not recognized and was not saved. Need support for this tool? Contact us at %s.', 'frontblocks' ),
						'close.technology/contacto'
					),
					'error'
				);
			}

			if ( $detected ) {
				$tracking_integrations   = array_values(
					array_filter(
						$tracking_integrations,
						static function ( $integration ) use ( $detected ) {
							return $integration['type'] !== $detected['type'];
						}
					)
				);
				$tracking_integrations[] = array(
					'type' => $detected['type'],
					'id'   => sanitize_text_field( $detected['id'] ),
				);
			}

			$sanitized[ $this->option_cookie_notice_tracking_integrations ] = $tracking_integrations;
			unset( $sanitized['cookie_notice_tracking_type'], $sanitized['cookie_notice_tracking_id'] );
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
