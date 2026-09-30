<?php
/**
 * Tests for FrontBlocks\Admin\CookieNoticeDeprecationNotice, plus a couple of
 * regression checks confirming the legacy Cookie Notice runtime module (the
 * banner, its AJAX endpoints, and tracking integrations) was fully removed
 * without leaving any dangling dependency behind.
 *
 * This file used to be CookieNoticeTransitionTest.php, covering FrontBlocks'
 * own Cookie Notice module during its transition period alongside
 * CookieNoticeDeprecationNotice. That module has since been removed in a
 * hard cutover — cookie consent now lives entirely in the standalone
 * FrontConsent plugin — so only the tests exercising
 * CookieNoticeDeprecationNotice (which is kept) remain, plus new regression
 * coverage for the removal itself.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\CookieNoticeDeprecationNotice;
use FrontBlocks\Admin\Settings;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class CookieNoticeDeprecationNoticeTest extends TestCase {

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		parent::tear_down();
	}

	public function test_frontconsent_install_url_uses_the_published_wordpress_org_slug() {
		$url = CookieNoticeDeprecationNotice::get_frontconsent_action_url();

		$this->assertStringContainsString( 'plugin=frontconsent', html_entity_decode( $url, ENT_QUOTES, 'UTF-8' ) );
	}

	/**
	 * FrontBlocks' own Cookie Notice runtime was removed entirely rather than
	 * merely disabled — this confirms the class is really gone, not just
	 * inert, so nothing in FrontBlocks (or a third-party add-on) can go on
	 * instantiating or calling into it.
	 */
	public function test_the_legacy_cookie_notice_class_no_longer_exists() {
		$this->assertFalse( class_exists( 'FrontBlocks\Frontend\CookieNotice' ) );
	}

	/**
	 * The Settings "Cookie Notice" tab is now a pure FrontConsent promo panel
	 * (see Settings::render_cookie_notice_promo_tab()) that only depends on
	 * CookieNoticeDeprecationNotice, which is kept — rendering it must not
	 * fatal now that the legacy Frontend\CookieNotice class is gone.
	 */
	public function test_cookie_notice_promo_tab_renders_without_the_legacy_class() {
		$settings = new Settings();

		$method = new ReflectionMethod( Settings::class, 'render_cookie_notice_promo_tab' );
		$method->setAccessible( true );

		ob_start();
		$method->invoke( $settings );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'FrontConsent', $output );
	}
}
