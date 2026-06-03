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
	 * Show an info card for a FrontBlocks Pro feature.
	 *
	 * Shows green (active) when Pro license is valid, red (locked) otherwise.
	 *
	 * @param string $feature_id  The ID of the feature.
	 * @param string $title       The title of the feature.
	 * @param string $description The description of the feature.
	 * @param bool   $pro_active  Whether the Pro license is active.
	 * @return void
	 */
	public static function show_pro_info_card( $feature_id, $title, $description, $pro_active = false ) {
		$icon       = self::get_feature_icon( $feature_id );
		$card_class = $pro_active ? 'frbl-feature-active' : 'frbl-feature-pro frbl-feature-locked';
		?>
		<div class="frbl-feature-card <?php echo esc_attr( $card_class ); ?>">
			<span class="frbl-pro-badge"><?php echo esc_html__( 'PRO', 'frontblocks' ); ?></span>
			<div class="frbl-feature-content">
				<div class="frbl-feature-icon">
					<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="frbl-feature-info">
					<h3 class="frbl-feature-title">
						<?php echo esc_html( $title ); ?>
					</h3>
					<p class="frbl-feature-description">
						<?php
						if ( $pro_active ) {
							echo esc_html( $description );
						} else {
							echo esc_html( $description ) . ' <a href="' . esc_url( admin_url( 'themes.php?page=frontblocks-settings&tab=license' ) ) . '">' . esc_html__( 'Activate FrontBlocks Pro', 'frontblocks' ) . '</a>';
						}
						?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show an info card for an always-active feature (no toggle).
	 *
	 * @param string $feature_id  The ID of the feature.
	 * @param string $title       The title of the feature.
	 * @param string $description The description of the feature.
	 * @return void
	 */
	public static function show_info_card( $feature_id, $title, $description ) {
		$icon = self::get_feature_icon( $feature_id );
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
	 * Get the icon for a feature.
	 *
	 * @param string $icon_slug The slug of the icon.
	 * @return string The icon HTML.
	 */
	public static function get_feature_icon( $icon_slug ) {
		// Animations icon.
		$animations_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

		// Carousel icon.
		$carousel_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';

		// Gallery icon.
		$gallery_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';

		// Sticky icon.
		$sticky_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>';

		// Insert post icon.
		$insert_post_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';

		// Counter icon.
		$counter_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>';

		// Reading time icon.
		$reading_time_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

		// Stacked images icon.
		$stacked_images_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="12" height="10" rx="1" transform="rotate(-5 9 12)"/><rect x="6" y="5" width="12" height="10" rx="1" transform="rotate(3 12 10)"/><rect x="9" y="3" width="12" height="10" rx="1"/></svg>';

		// Product categories icon.
		$product_categories_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>';

		// Headline marquee icon.
		$headline_marquee_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 12l4-4m-4 4l4 4m14-4l-4-4m4 4l-4 4"/></svg>';

		// User text icon.
		$user_text_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';

		// SVG upload icon.
		$svg_upload_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/><path d="M12 10v4m-2-2h4" stroke-linecap="round"/></svg>';

		// Default icon.
		$default_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';

		$icons = array(
			'animations'         => $animations_icon,
			'carousel'           => $carousel_icon,
			'gallery'            => $gallery_icon,
			'sticky'             => $sticky_icon,
			'insert_post'        => $insert_post_icon,
			'counter'            => $counter_icon,
			'reading_time'       => $reading_time_icon,
			'stacked_images'     => $stacked_images_icon,
			'product_categories' => $product_categories_icon,
			'headline_marquee'   => $headline_marquee_icon,
			'user_text'          => $user_text_icon,
			'svg_upload'         => $svg_upload_icon,
		);

		return $icons[ $icon_slug ] ?? $default_icon;
	}
}
