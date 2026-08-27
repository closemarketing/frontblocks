<?php
/**
 * Tests for Cookie Notice cache and GTM4WP integrations.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\Settings;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Cookie Notice integrations test case.
 */
class CookieNoticeIntegrationsTest extends TestCase {

	/**
	 * Settings instance under test.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Set up the settings instance.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->settings = new Settings();
	}

	/**
	 * Verify that cache integrations are notified after a Cookie Notice change.
	 *
	 * @return void
	 */
	public function test_cookie_notice_update_fires_extension_action() {
		$received = array();
		$callback = static function ( $old_options, $new_options ) use ( &$received ) {
			$received = array( $old_options, $new_options );
		};

		add_action( 'frbl_cookie_notice_settings_updated', $callback, 10, 2 );

		$old_options = array( 'enable_cookie_notice' => false );
		$new_options = array( 'enable_cookie_notice' => true );
		$this->settings->handle_frontblocks_settings_updated( $old_options, $new_options, 'frontblocks_settings' );

		remove_action( 'frbl_cookie_notice_settings_updated', $callback, 10 );

		$this->assertSame( $old_options, $received[0] );
		$this->assertSame( $new_options, $received[1] );
	}

	/**
	 * Verify that unrelated settings do not trigger cache invalidation hooks.
	 *
	 * @return void
	 */
	public function test_unrelated_settings_update_does_not_fire_cookie_notice_action() {
		$was_called = false;
		$callback   = static function () use ( &$was_called ) {
			$was_called = true;
		};

		add_action( 'frbl_cookie_notice_settings_updated', $callback );

		$this->settings->handle_frontblocks_settings_updated(
			array( 'enable_events' => false ),
			array( 'enable_events' => true ),
			'frontblocks_settings'
		);

		remove_action( 'frbl_cookie_notice_settings_updated', $callback );

		$this->assertFalse( $was_called );
	}

	/**
	 * Verify that a matching active GTM4WP container is detected.
	 *
	 * @return void
	 */
	public function test_gtm4wp_conflict_is_detected_when_matching_container_is_enabled() {
		if ( ! defined( 'GTM4WP_OPTIONS' ) ) {
			define( 'GTM4WP_OPTIONS', 'gtm4wp-options' );
		}

		update_option(
			'gtm4wp-options',
			array(
				'gtm-code'           => 'GTM-ABC123',
				'gtm-code-placement' => 0,
			)
		);

		$method = new ReflectionMethod( Settings::class, 'is_gtm4wp_container_loading' );
		$method->setAccessible( true );

		$this->assertTrue( $method->invoke( $this->settings, 'GTM-ABC123' ) );
		$this->assertFalse( $method->invoke( $this->settings, 'GTM-OTHER' ) );
	}

	/**
	 * Verify that GTM4WP placement off does not produce a conflict.
	 *
	 * @return void
	 */
	public function test_gtm4wp_conflict_is_not_reported_when_container_code_is_off() {
		if ( ! defined( 'GTM4WP_OPTIONS' ) ) {
			define( 'GTM4WP_OPTIONS', 'gtm4wp-options' );
		}

		update_option(
			'gtm4wp-options',
			array(
				'gtm-code'           => 'GTM-ABC123',
				'gtm-code-placement' => 3,
			)
		);

		$method = new ReflectionMethod( Settings::class, 'is_gtm4wp_container_loading' );
		$method->setAccessible( true );

		$this->assertFalse( $method->invoke( $this->settings, 'GTM-ABC123' ) );
	}
}
