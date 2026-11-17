<?php
/**
 * Helpers for the Settings page.
 *
 * @package    FrontBlocks
 * @author     Close <info@close.technology>
 * @copyright  2025 Close
 * @version    1.0.0
 */

namespace FrontBlocks\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * UI class for the Settings page.
 */
class UI {

	/**
	 * Show an info card for an always-active free feature (green, no toggle).
	 *
	 * @param string $icon_slug   Icon slug resolved via get_feature_icon().
	 * @param string $title       Feature title.
	 * @param string $description Feature description.
	 * @return void
	 */
	public static function show_info_card( $icon_slug, $title, $description ) {
		$icon = self::get_feature_icon( $icon_slug );
		?>
		<div class="frbl-feature-card frbl-feature-active">
			<div class="frbl-feature-content">
				<div class="frbl-feature-icon">
					<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="frbl-feature-info">
					<h3 class="frbl-feature-title">
						<?php echo esc_html( $title ); ?>
					</h3>
					<p class="frbl-feature-description">
						<?php echo esc_html( $description ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show a PRO info card (red, locked, no toggle).
	 * Icon is read from assets/admin/icons/<slug>.svg.
	 *
	 * @param string $icon_slug   SVG filename without extension.
	 * @param string $title       Feature title.
	 * @param string $description Feature description.
	 * @return void
	 */
	public static function show_pro_info_card( $icon_slug, $title, $description ) {
		$icon_path = FRBL_PLUGIN_PATH . 'assets/admin/icons/' . $icon_slug . '.svg';
		$icon_svg  = file_exists( $icon_path ) ? file_get_contents( $icon_path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$pro_url   = 'https://close.technology/wordpress-plugins/frontblocks-pro/?utm_source=frontblocks&utm_medium=plugin&utm_campaign=settings-pro-card';
		?>
		<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" rel="noopener noreferrer" style="text-decoration: none; color: inherit; display: block;">
			<div class="frbl-feature-card frbl-feature-pro frbl-feature-pro-info" style="height: 100%;">
				<div class="frbl-pro-badge">PRO</div>
				<div class="frbl-feature-content">
					<div class="frbl-feature-icon">
						<?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="frbl-feature-info">
						<h3 class="frbl-feature-title">
							<?php echo esc_html( $title ); ?>
						</h3>
						<p class="frbl-feature-description">
							<?php echo esc_html( $description ); ?>
						</p>
					</div>
				</div>
			</div>
		</a>
		<?php
	}

	/**
	 * Get inline SVG icon for a free feature.
	 *
	 * @param string $icon_slug Icon slug.
	 * @return string SVG HTML.
	 */
	public static function get_feature_icon( $icon_slug ) {
		$icons = array(
			'animations'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
			'carousel'            => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
			'gallery'             => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
			'sticky'              => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>',
			'insert_post'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
			'counter'             => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>',
			'reading_time'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
			'stacked_images'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="12" height="10" rx="1" transform="rotate(-5 9 12)"/><rect x="6" y="5" width="12" height="10" rx="1" transform="rotate(3 12 10)"/><rect x="9" y="3" width="12" height="10" rx="1"/></svg>',
			'product_categories'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>',
			'headline_marquee'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 12l4-4m-4 4l4 4m14-4l-4-4m4 4l-4 4"/></svg>',
			'svg_upload'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/><path d="M12 10v4m-2-2h4" stroke-linecap="round"/></svg>',
			'staging_noindex'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/></svg>',
			'staging_email'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zM22 6l-10 7L2 6"/></svg>',
			'staging_indicators'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/></svg>',
			'staging_top_bar'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3zM3 9h18"/></svg>',
			'staging_woocommerce' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/></svg>',
			'staging_pause'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a10 10 0 100-20 10 10 0 000 20zM10 15V9M14 15V9"/></svg>',
			'staging_hosts'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
			'staging_tracking'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
		);

		$default = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';

		return $icons[ $icon_slug ] ?? $default;
	}
}
