<?php
/**
 * Tests for the Cookie Notice banner's policy link text.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\CookieNotice;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * CookieNoticeLinkTest class.
 */
class CookieNoticeLinkTest extends TestCase {

	/**
	 * Clean up options after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		parent::tear_down();
	}

	/**
	 * Render the private banner markup and capture its output.
	 *
	 * @param CookieNotice $notice Instance to render.
	 * @return string
	 */
	private function render_banner_markup( CookieNotice $notice ) {
		$method = new ReflectionMethod( CookieNotice::class, 'render_banner_markup' );
		$method->setAccessible( true );

		ob_start();
		$method->invoke( $notice );
		return ob_get_clean();
	}

	/**
	 * The policy link should use the configured page's own title, not a
	 * generic "Learn more" — so a screen reader user tabbing through links
	 * out of context knows where it leads.
	 *
	 * @return void
	 */
	public function test_policy_link_uses_the_policy_page_title_instead_of_generic_text() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Nuestra Política de Cookies',
				'post_status' => 'publish',
			)
		);

		update_option(
			'frontblocks_settings',
			array(
				'cookie_notice_message'        => 'We use cookies.',
				'cookie_notice_policy_page_id' => $page_id,
			)
		);

		$html = $this->render_banner_markup( new CookieNotice() );

		$this->assertStringContainsString( 'Nuestra Política de Cookies', $html );
		$this->assertStringNotContainsString( 'Learn more', $html );
	}

	/**
	 * A policy page with no title falls back to the generic label rather than
	 * rendering an empty link.
	 *
	 * @return void
	 */
	public function test_policy_link_falls_back_to_learn_more_when_the_page_has_no_title() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => '',
				'post_status' => 'publish',
			)
		);

		update_option(
			'frontblocks_settings',
			array(
				'cookie_notice_message'        => 'We use cookies.',
				'cookie_notice_policy_page_id' => $page_id,
			)
		);

		$html = $this->render_banner_markup( new CookieNotice() );

		$this->assertStringContainsString( 'Learn more', $html );
	}

	/**
	 * No policy page configured means no link is rendered at all.
	 *
	 * @return void
	 */
	public function test_no_link_is_rendered_when_no_policy_page_is_configured() {
		update_option(
			'frontblocks_settings',
			array(
				'cookie_notice_message' => 'We use cookies.',
			)
		);

		$html = $this->render_banner_markup( new CookieNotice() );

		$this->assertStringNotContainsString( 'frbl-cookie-notice__link', $html );
	}
}
