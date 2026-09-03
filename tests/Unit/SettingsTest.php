<?php
/**
 * Tests for FrontBlocks\Admin\Settings — covering sanitize_settings() logic
 * that is NOT already exercised by CookieNoticeSettingsSanitizationTest
 * (events type, scroll-to-top, maintenance mode, GA4 id, boolean options,
 * the short-description mutual exclusion rule) plus a few other public
 * helper methods.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\Settings;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class SettingsTest extends TestCase {

	/**
	 * @var Settings
	 */
	private $settings;

	public function set_up() {
		parent::set_up();
		$this->settings = new Settings();

		// sanitize_settings() bails out (returning the stored option untouched)
		// without a valid nonce, so every test needs one present.
		$_POST['_wpnonce'] = wp_create_nonce( 'frontblocks_settings-options' );
	}

	public function tear_down() {
		unset( $_POST['_wpnonce'] );
		delete_option( 'frontblocks_settings' );
		remove_all_filters( 'frontblocks_settings_tabs' );
		remove_all_actions( 'frontblocks_settings_tab_panels' );
		parent::tear_down();
	}

	public function test_sanitize_settings_returns_stored_option_unchanged_without_a_valid_nonce() {
		unset( $_POST['_wpnonce'] );
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => true ) );

		$sanitized = $this->settings->sanitize_settings( array( 'enable_testimonials' => false ) );

		$this->assertSame( array( 'enable_testimonials' => true ), $sanitized );
	}

	public function test_sanitize_settings_returns_empty_array_for_non_array_input() {
		$sanitized = $this->settings->sanitize_settings( 'not-an-array' );

		$this->assertSame( array(), $sanitized );
	}

	public function test_boolean_options_default_to_false_when_absent_from_submission() {
		update_option( 'frontblocks_settings', array( 'enable_back_button' => true ) );

		$sanitized = $this->settings->sanitize_settings( array() );

		$this->assertFalse( $sanitized['enable_back_button'], 'Unchecked checkboxes must be reset to false, not left at their previous value.' );
		$this->assertFalse( $sanitized['enable_reading_progress'] );
	}

	public function test_boolean_options_are_cast_to_bool_when_submitted() {
		$sanitized = $this->settings->sanitize_settings(
			array(
				'enable_back_button'      => '1',
				'enable_reading_progress' => 0,
			)
		);

		$this->assertTrue( $sanitized['enable_back_button'] );
		$this->assertFalse( $sanitized['enable_reading_progress'] );
	}

	public function test_events_type_only_accepts_cpt_or_posts() {
		$sanitized = $this->settings->sanitize_settings( array( 'events_type' => 'posts' ) );
		$this->assertSame( 'posts', $sanitized['events_type'] );

		$sanitized = $this->settings->sanitize_settings( array( 'events_type' => 'cpt' ) );
		$this->assertSame( 'cpt', $sanitized['events_type'] );
	}

	public function test_events_type_falls_back_to_cpt_for_unknown_values() {
		$sanitized = $this->settings->sanitize_settings( array( 'events_type' => 'something-else' ) );

		$this->assertSame( 'cpt', $sanitized['events_type'] );
	}

	public function test_scroll_top_position_only_accepts_known_values() {
		$sanitized = $this->settings->sanitize_settings( array( 'scroll_top_position' => 'bottom-left' ) );
		$this->assertSame( 'bottom-left', $sanitized['scroll_top_position'] );
	}

	public function test_scroll_top_position_falls_back_to_bottom_right_for_unknown_values() {
		$sanitized = $this->settings->sanitize_settings( array( 'scroll_top_position' => 'top-center' ) );

		$this->assertSame( 'bottom-right', $sanitized['scroll_top_position'] );
	}

	public function test_scroll_top_icon_url_is_sanitized_as_a_url() {
		$sanitized = $this->settings->sanitize_settings( array( 'scroll_top_icon_url' => 'javascript:alert(1)' ) );

		$this->assertSame( '', $sanitized['scroll_top_icon_url'] );

		$sanitized = $this->settings->sanitize_settings( array( 'scroll_top_icon_url' => 'https://example.com/icon.svg' ) );
		$this->assertSame( 'https://example.com/icon.svg', $sanitized['scroll_top_icon_url'] );
	}

	public function test_maintenance_title_is_sanitized_as_a_text_field() {
		$sanitized = $this->settings->sanitize_settings( array( 'maintenance_title' => "We'll be back <script>alert(1)</script>" ) );

		$this->assertStringNotContainsString( '<script>', $sanitized['maintenance_title'] );
		$this->assertStringContainsString( "We'll be back", $sanitized['maintenance_title'] );
	}

	public function test_maintenance_image_is_cast_to_a_non_negative_integer() {
		$sanitized = $this->settings->sanitize_settings( array( 'maintenance_image' => '42' ) );
		$this->assertSame( 42, $sanitized['maintenance_image'] );

		$sanitized = $this->settings->sanitize_settings( array( 'maintenance_image' => '-5' ) );
		$this->assertSame( 5, $sanitized['maintenance_image'] );
	}

	public function test_ga4_id_is_preserved_when_valid_and_uppercased() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_ga4_id' => 'g-abc123' ) );

		$this->assertSame( 'G-ABC123', $sanitized['cookie_notice_ga4_id'] );
	}

	public function test_ga4_id_is_rejected_when_malformed() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_ga4_id' => 'not-a-valid-id' ) );

		$this->assertSame( '', $sanitized['cookie_notice_ga4_id'] );
	}

	public function test_deactivate_short_description_and_move_content_are_mutually_exclusive() {
		// Both were already true in storage (should not normally happen, but
		// the sanitizer must still resolve to a single winner deterministically):
		// since deactivate was already on, it is the one turned off.
		update_option(
			'frontblocks_settings',
			array(
				'deactivate_short_description'       => true,
				'move_content_to_short_description'  => false,
			)
		);

		$sanitized = $this->settings->sanitize_settings(
			array(
				'deactivate_short_description'      => true,
				'move_content_to_short_description' => true,
			)
		);

		$this->assertFalse( $sanitized['deactivate_short_description'] );
		$this->assertTrue( $sanitized['move_content_to_short_description'] );
	}

	public function test_deactivate_short_description_wins_when_move_content_was_already_on() {
		update_option(
			'frontblocks_settings',
			array(
				'deactivate_short_description'      => false,
				'move_content_to_short_description' => true,
			)
		);

		$sanitized = $this->settings->sanitize_settings(
			array(
				'deactivate_short_description'      => true,
				'move_content_to_short_description' => true,
			)
		);

		$this->assertTrue( $sanitized['deactivate_short_description'] );
		$this->assertFalse( $sanitized['move_content_to_short_description'] );
	}

	public function test_deactivate_short_description_and_move_content_can_both_be_off() {
		$sanitized = $this->settings->sanitize_settings(
			array(
				'deactivate_short_description'      => false,
				'move_content_to_short_description' => false,
			)
		);

		$this->assertFalse( $sanitized['deactivate_short_description'] );
		$this->assertFalse( $sanitized['move_content_to_short_description'] );
	}

	public function test_register_menu_adds_the_settings_page_under_appearance() {
		global $submenu;

		set_current_screen( 'dashboard' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$this->settings->register_menu();

		$this->assertArrayHasKey( 'themes.php', $submenu );

		$found = false;
		foreach ( $submenu['themes.php'] as $item ) {
			if ( 'frontblocks-settings' === $item[2] ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, 'Expected a "frontblocks-settings" entry under the Appearance menu.' );
	}

	public function test_settings_tab_extensions_render_matching_tabs_and_panels() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		add_filter( 'frontblocks_settings_tabs', array( $this, 'add_test_settings_tab' ) );
		add_action( 'frontblocks_settings_tab_panels', array( $this, 'render_test_settings_tab_panel' ) );

		ob_start();
		$this->settings->render_page();
		$rendered = ob_get_clean();

		$this->assertStringContainsString( 'data-tab-target="test-extension"', $rendered );
		$this->assertStringContainsString( 'data-tab-panel="test-extension"', $rendered );
		$this->assertStringContainsString( 'Test extension panel', $rendered );
	}

	/**
	 * Add a tab used by test_settings_tab_extensions_render_matching_tabs_and_panels().
	 *
	 * @param array $tabs Registered settings tabs.
	 * @return array
	 */
	public function add_test_settings_tab( $tabs ) {
		$tabs[] = array(
			'id'    => 'test-extension',
			'label' => 'Test extension',
		);

		return $tabs;
	}

	/**
	 * Render the panel used by test_settings_tab_extensions_render_matching_tabs_and_panels().
	 *
	 * @return void
	 */
	public function render_test_settings_tab_panel() {
		echo '<div class="frbl-tab-panel" data-tab-panel="test-extension" hidden>Test extension panel</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function test_enqueue_admin_styles_does_nothing_outside_the_settings_page_hook() {
		$this->settings->enqueue_admin_styles( 'edit.php' );

		$this->assertFalse( wp_style_is( 'frontblocks-admin-settings', 'enqueued' ) );
	}

	public function test_enqueue_admin_styles_enqueues_assets_on_the_settings_page_hook() {
		$this->settings->enqueue_admin_styles( 'appearance_page_frontblocks-settings' );

		$this->assertTrue( wp_style_is( 'frontblocks-admin-settings', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'frontblocks-cookie-notice', 'enqueued' ) );

		wp_dequeue_style( 'frontblocks-admin-settings' );
		wp_dequeue_style( 'frontblocks-cookie-notice' );
	}
}
