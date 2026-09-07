<?php
/**
 * Tests for Cookie Notice cache and GTM4WP integrations.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\Settings;
use FrontBlocks\Frontend\CookieNotice;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Cookie Notice integrations test case.
 */
class CookieNoticeIntegrationsTest extends TestCase {

	/**
	 * Settings instance under test.
	 *
	 * @var CookieNotice
	 */
	private $settings;

	/**
	 * Set up the settings instance.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->settings = new CookieNotice();
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
		update_option( 'frontblocks_settings', $old_options );
		update_option( 'frontblocks_settings', $new_options );

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
		$settings = new Settings();

		$this->assertTrue( $method->invoke( $settings, 'GTM-ABC123' ) );
		$this->assertFalse( $method->invoke( $settings, 'GTM-OTHER' ) );
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
		$settings = new Settings();

		$this->assertFalse( $method->invoke( $settings, 'GTM-ABC123' ) );
	}

	/**
	 * Verify that legacy dedicated GTM/GA4 ids are moved into the shared
	 * tracking-integrations list, and the legacy keys are cleared.
	 *
	 * @return void
	 */
	public function test_migration_moves_legacy_gtm_and_ga4_ids_into_the_shared_list() {
		update_option(
			'frontblocks_settings',
			array(
				'cookie_notice_gtm_id' => 'GTM-LEGACY1',
				'cookie_notice_ga4_id' => 'G-LEGACY123',
			)
		);

		( new Settings() )->migrate_legacy_gtm_ga4_tracking_ids();

		$options = get_option( 'frontblocks_settings' );

		$this->assertArrayNotHasKey( 'cookie_notice_gtm_id', $options );
		$this->assertArrayNotHasKey( 'cookie_notice_ga4_id', $options );
		$this->assertSame(
			array(
				array( 'type' => 'gtm', 'id' => 'GTM-LEGACY1' ),
				array( 'type' => 'ga4', 'id' => 'G-LEGACY123' ),
			),
			$options['cookie_notice_tracking_integrations']
		);
	}

	/**
	 * A site with no legacy values must not gain an empty integrations key
	 * out of nowhere, since the migration is meant to be a no-op there.
	 *
	 * @return void
	 */
	public function test_migration_is_a_no_op_when_no_legacy_value_is_present() {
		update_option( 'frontblocks_settings', array( 'enable_cookie_notice' => true ) );

		( new Settings() )->migrate_legacy_gtm_ga4_tracking_ids();

		$options = get_option( 'frontblocks_settings' );

		$this->assertArrayNotHasKey( 'cookie_notice_tracking_integrations', $options );
	}

	/**
	 * If the admin already added an equivalent entry to the generic list
	 * before the migration runs, the legacy value must not override it.
	 *
	 * @return void
	 */
	public function test_migration_does_not_override_an_existing_gtm_entry() {
		update_option(
			'frontblocks_settings',
			array(
				'cookie_notice_gtm_id'                => 'GTM-LEGACY1',
				'cookie_notice_tracking_integrations' => array(
					array( 'type' => 'gtm', 'id' => 'GTM-CURRENT' ),
				),
			)
		);

		( new Settings() )->migrate_legacy_gtm_ga4_tracking_ids();

		$options = get_option( 'frontblocks_settings' );

		$this->assertArrayNotHasKey( 'cookie_notice_gtm_id', $options );
		$this->assertSame(
			array( array( 'type' => 'gtm', 'id' => 'GTM-CURRENT' ) ),
			$options['cookie_notice_tracking_integrations']
		);
	}

	/**
	 * Running the migration a second time (e.g. on a later admin_init) must
	 * not error or duplicate anything now that the legacy keys are gone.
	 *
	 * @return void
	 */
	public function test_migration_only_runs_once() {
		update_option( 'frontblocks_settings', array( 'cookie_notice_gtm_id' => 'GTM-LEGACY1' ) );

		$settings = new Settings();
		$settings->migrate_legacy_gtm_ga4_tracking_ids();
		$settings->migrate_legacy_gtm_ga4_tracking_ids();

		$options = get_option( 'frontblocks_settings' );

		$this->assertSame(
			array( array( 'type' => 'gtm', 'id' => 'GTM-LEGACY1' ) ),
			$options['cookie_notice_tracking_integrations']
		);
	}
}
