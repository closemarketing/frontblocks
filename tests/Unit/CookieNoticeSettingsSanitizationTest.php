<?php
/**
 * Tests for the Cookie Notice fields handled by Settings::sanitize_settings().
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\Settings;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class CookieNoticeSettingsSanitizationTest extends TestCase {

	/**
	 * @var Settings
	 */
	private $settings;

	public function set_up() {
		parent::set_up();
		$this->settings = new Settings();

		// sanitize_settings() rejects the call outright (returning the existing
		// option untouched) without a valid nonce — every test below needs one
		// present to actually exercise the sanitization logic under test.
		$_POST['_wpnonce'] = wp_create_nonce( 'frontblocks_settings-options' );
	}

	public function tear_down() {
		unset( $_POST['_wpnonce'] );
		parent::tear_down();
	}

	public function test_enable_cookie_notice_is_cast_to_bool() {
		$sanitized = $this->settings->sanitize_settings( array( 'enable_cookie_notice' => '1' ) );
		$this->assertTrue( $sanitized['enable_cookie_notice'] );
	}

	public function test_enable_cookie_notice_defaults_to_false_when_absent() {
		// Simulates an unchecked checkbox: the key is simply missing from $_POST.
		$sanitized = $this->settings->sanitize_settings( array() );
		$this->assertFalse( $sanitized['enable_cookie_notice'] );
	}

	public function test_message_is_sanitized_as_textarea_field() {
		$sanitized = $this->settings->sanitize_settings(
			array( 'cookie_notice_message' => "Line one\nLine two <script>alert(1)</script>" )
		);

		$this->assertStringNotContainsString( '<script>', $sanitized['cookie_notice_message'] );
		$this->assertStringContainsString( 'Line one', $sanitized['cookie_notice_message'] );
	}

	public function test_layout_only_accepts_known_values() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_layout' => 'popup' ) );
		$this->assertSame( 'popup', $sanitized['cookie_notice_layout'] );
	}

	public function test_layout_falls_back_to_bar_for_unknown_values() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_layout' => 'not-a-real-layout' ) );
		$this->assertSame( 'bar', $sanitized['cookie_notice_layout'] );
	}

	public function test_position_only_accepts_known_values() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_position' => 'bottom-left' ) );
		$this->assertSame( 'bottom-left', $sanitized['cookie_notice_position'] );
	}

	public function test_position_falls_back_to_bottom_right_for_unknown_values() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_position' => 'top-center' ) );
		$this->assertSame( 'bottom-right', $sanitized['cookie_notice_position'] );
	}

	public function test_policy_page_id_is_cast_to_int() {
		$page_id   = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$sanitized = $this->settings->sanitize_settings(
			array( 'cookie_notice_policy_page_id' => (string) $page_id )
		);

		$this->assertSame( $page_id, $sanitized['cookie_notice_policy_page_id'] );
		$this->assertIsInt( $sanitized['cookie_notice_policy_page_id'] );
	}

	public function test_policy_page_id_defaults_to_zero_for_empty_selection() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_policy_page_id' => '' ) );
		$this->assertSame( 0, $sanitized['cookie_notice_policy_page_id'] );
	}

	public function test_valid_hex_accent_color_is_preserved() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_color' => '#687df9' ) );
		$this->assertSame( '#687df9', $sanitized['cookie_notice_color'] );
	}

	public function test_invalid_hex_accent_color_falls_back_to_default() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_color' => 'not-a-hex-color' ) );
		$this->assertSame( '#687df9', $sanitized['cookie_notice_color'] );
	}

	public function test_valid_hex_background_color_is_preserved() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_bg_color' => '#111827' ) );
		$this->assertSame( '#111827', $sanitized['cookie_notice_bg_color'] );
	}

	public function test_invalid_hex_background_color_falls_back_to_default() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_bg_color' => 'not-a-hex-color' ) );
		$this->assertSame( '#ffffff', $sanitized['cookie_notice_bg_color'] );
	}

	public function test_radius_only_accepts_known_values() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_radius' => 'none' ) );
		$this->assertSame( 'none', $sanitized['cookie_notice_radius'] );

		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_radius' => 'large' ) );
		$this->assertSame( 'large', $sanitized['cookie_notice_radius'] );
	}

	public function test_radius_falls_back_to_small_for_unknown_values() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_radius' => 'extra-bevel' ) );
		$this->assertSame( 'small', $sanitized['cookie_notice_radius'] );
	}

	public function test_recognized_tracking_code_is_added_as_a_safe_integration_record() {
		$snippet   = '<script defer src="https://analyticsplusdev.clientify.net/analytics_plus/pixel/TestPixel1"></script>';
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_tracking_integration_code' => $snippet ) );

		$this->assertSame(
			array(
				array(
					'type' => 'clientify_analytics_plus',
					'id'   => 'TestPixel1',
				),
			),
			$sanitized['cookie_notice_tracking_integrations']
		);
		$this->assertArrayNotHasKey( 'cookie_notice_tracking_integration_code', $sanitized );
	}

	public function test_unrecognized_tracking_code_is_rejected_and_reports_an_error() {
		$before_count = $this->count_tracking_notice_errors();

		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_tracking_integration_code' => '<script src="https://example.com/unsupported.js"></script>' ) );

		$this->assertSame( array(), $sanitized['cookie_notice_tracking_integrations'] );
		$this->assertSame( $before_count + 1, $this->count_tracking_notice_errors(), 'Expected exactly one new admin notice for the unrecognized tracking snippet.' );
	}

	public function test_blank_tracking_code_leaves_the_integration_list_empty_without_an_error() {
		$before_count = $this->count_tracking_notice_errors();

		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_tracking_integration_code' => '' ) );

		$this->assertSame( array(), $sanitized['cookie_notice_tracking_integrations'] );
		$this->assertSame( $before_count, $this->count_tracking_notice_errors(), 'A blank snippet must not add an admin notice.' );
	}

	public function test_tracking_integration_can_be_removed() {
		update_option(
			'frontblocks_settings',
			array(
				'cookie_notice_tracking_integrations' => array(
					array( 'type' => 'brevo', 'id' => 'brevo-key' ),
				),
			)
		);

		$sanitized = $this->settings->sanitize_settings(
			array(
				'cookie_notice_tracking_integration_code' => '',
				'cookie_notice_tracking_remove'           => array( 'brevo' ),
			)
		);

		$this->assertSame( array(), $sanitized['cookie_notice_tracking_integrations'] );
	}

	/**
	 * Count how many "unrecognized tracking snippet" admin notices are
	 * currently queued, so tests can assert a delta instead of an absolute
	 * count — WordPress's settings-errors list is a process-wide global that
	 * earlier tests (in this file or elsewhere) may have already added to.
	 *
	 * @return int
	 */
	private function count_tracking_notice_errors() {
		$count = 0;

		foreach ( get_settings_errors( 'frontblocks_settings' ) as $error ) {
			if ( 'frbl_cookie_notice_tracking_unrecognized' === $error['code'] ) {
				++$count;
			}
		}

		return $count;
	}

	public function test_expiration_days_is_capped_at_730() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_expiration_days' => '99999' ) );
		$this->assertSame( 730, $sanitized['cookie_notice_expiration_days'] );
	}

	public function test_expiration_days_of_zero_falls_back_to_365() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_expiration_days' => '0' ) );
		$this->assertSame( 365, $sanitized['cookie_notice_expiration_days'] );
	}

	public function test_valid_gtm_id_pasted_through_the_generic_field_is_preserved_uppercased() {
		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_tracking_integration_code' => 'gtm-abc123' ) );

		$this->assertSame(
			array( array( 'type' => 'gtm', 'id' => 'GTM-ABC123' ) ),
			$sanitized['cookie_notice_tracking_integrations']
		);
	}

	public function test_malformed_gtm_id_is_rejected() {
		$before_count = $this->count_tracking_notice_errors();

		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_tracking_integration_code' => 'not-a-gtm-id' ) );

		$this->assertSame( array(), $sanitized['cookie_notice_tracking_integrations'] );
		$this->assertSame( $before_count + 1, $this->count_tracking_notice_errors() );
	}

	public function test_gtm_snippet_replaces_a_previously_saved_gtm_entry() {
		update_option(
			'frontblocks_settings',
			array(
				'cookie_notice_tracking_integrations' => array(
					array( 'type' => 'gtm', 'id' => 'GTM-OLD1234' ),
				),
			)
		);

		$sanitized = $this->settings->sanitize_settings( array( 'cookie_notice_tracking_integration_code' => 'GTM-NEW5678' ) );

		$this->assertSame(
			array( array( 'type' => 'gtm', 'id' => 'GTM-NEW5678' ) ),
			$sanitized['cookie_notice_tracking_integrations']
		);
	}

	public function test_gtm_entry_can_be_removed_through_the_generic_list() {
		update_option(
			'frontblocks_settings',
			array(
				'cookie_notice_tracking_integrations' => array(
					array( 'type' => 'gtm', 'id' => 'GTM-ABC123' ),
				),
			)
		);

		$sanitized = $this->settings->sanitize_settings(
			array(
				'cookie_notice_tracking_integration_code' => '',
				'cookie_notice_tracking_remove'           => array( 'gtm' ),
			)
		);

		$this->assertSame( array(), $sanitized['cookie_notice_tracking_integrations'] );
	}
}
