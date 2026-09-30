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

	/**
	 * A user who cannot install plugins (e.g. a multisite subsite admin with
	 * `edit_theme_options` but not `install_plugins`) must never see a CTA
	 * whose nonce-bearing install URL will fail authorization for them —
	 * they get a message pointing at a site/network administrator instead.
	 */
	public function test_promo_tab_hides_the_install_cta_without_install_plugins_capability() {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );

		$this->assertFalse( current_user_can( 'install_plugins' ) );

		$settings = new Settings();
		$method   = new ReflectionMethod( Settings::class, 'render_cookie_notice_promo_tab' );
		$method->setAccessible( true );

		ob_start();
		$method->invoke( $settings );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( 'action=install-plugin', $output );
		$this->assertStringContainsString( 'administrator', $output );
	}

	/**
	 * A user who does have the capability still sees the real install link.
	 */
	public function test_promo_tab_shows_the_install_cta_with_install_plugins_capability() {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$this->assertTrue( current_user_can( 'install_plugins' ) );

		$settings = new Settings();
		$method   = new ReflectionMethod( Settings::class, 'render_cookie_notice_promo_tab' );
		$method->setAccessible( true );

		ob_start();
		$method->invoke( $settings );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'action=install-plugin', html_entity_decode( $output, ENT_QUOTES, 'UTF-8' ) );
	}

	/**
	 * The hard cutover makes this notice urgent in a way the original,
	 * lower-stakes transition notice never was: an admin who dismissed that
	 * old notice must still see this one, since their site now has no
	 * consent banner at all until FrontConsent is installed. The dismissal
	 * meta key was bumped to a new name specifically so every prior
	 * dismissal (stored under the old key) becomes irrelevant.
	 */
	public function test_dismissal_meta_key_was_bumped_for_the_hard_cutover() {
		$this->assertSame(
			'frbl_cookie_notice_deprecation_dismissed_v2',
			CookieNoticeDeprecationNotice::DISMISSED_META_KEY
		);
	}
}
