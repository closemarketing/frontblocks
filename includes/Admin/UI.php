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
	 * Get the icon for a feature.
	 *
	 * @param string $feature_id The ID of the feature.
	 * @return string The icon HTML.
	 */
	public static function show_feature( $feature_id ) {
		?>
		<div class="frbl-feature-card ">
			<div class="frbl-feature-content">
				<div class="frbl-feature-icon">
					<?php self::get_feature_icon( $icon_slug ); ?>
				</div>
				<div class="frbl-feature-info">
					<h3 class="frbl-feature-title">
						Enable testimonials					</h3>
				</div>
				<div class="frbl-feature-toggle">
					<?php call_user_func( $field['callback'], $field['args'] ); ?>
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
		
	}
}
