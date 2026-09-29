<?php
/**
 * Tests for FrontBlocks\Frontend\CookieNotice transition behavior.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\CookieNotice;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class CookieNoticeTransitionTest extends TestCase {

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		parent::tear_down();
	}

	public function test_legacy_gtm_and_ga4_ids_are_available_without_a_migration_save() {
		$integrations = CookieNotice::get_tracking_integrations(
			array(
				'cookie_notice_gtm_id' => 'GTM-LEGACY1',
				'cookie_notice_ga4_id' => 'G-LEGACY123',
			)
		);

		$this->assertSame(
			array(
				array( 'type' => 'gtm', 'id' => 'GTM-LEGACY1' ),
				array( 'type' => 'ga4', 'id' => 'G-LEGACY123' ),
			),
			$integrations
		);
	}

	public function test_existing_shared_integration_wins_over_the_retired_dedicated_id() {
		$integrations = CookieNotice::get_tracking_integrations(
			array(
				'cookie_notice_tracking_integrations' => array(
					array( 'type' => 'gtm', 'id' => 'GTM-CURRENT' ),
				),
				'cookie_notice_gtm_id'                => 'GTM-LEGACY1',
			)
		);

		$this->assertSame( array( array( 'type' => 'gtm', 'id' => 'GTM-CURRENT' ) ), $integrations );
	}

	public function test_frontconsent_install_url_uses_the_published_wordpress_org_slug() {
		$url = \FrontBlocks\Admin\CookieNoticeDeprecationNotice::get_frontconsent_action_url();

		$this->assertStringContainsString( 'plugin=frontconsent', html_entity_decode( $url, ENT_QUOTES, 'UTF-8' ) );
	}

	public function test_frontconsent_disables_an_enabled_legacy_module() {
		define( 'FRCN_VERSION', '1.0.1' );
		update_option( 'frontblocks_settings', array( 'enable_cookie_notice' => true ) );

		$method = new ReflectionMethod( CookieNotice::class, 'is_enabled' );
		$method->setAccessible( true );

		$this->assertFalse( $method->invoke( new CookieNotice() ) );
	}
}
