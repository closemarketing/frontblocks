<?php
/**
 * Tests for FrontBlocks\Admin\UI.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\UI;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class UITest extends TestCase {

	public function test_get_feature_icon_returns_known_icon_for_registered_slug() {
		$icon = UI::get_feature_icon( 'counter' );

		$this->assertStringContainsString( '<svg', $icon );
		$this->assertStringContainsString( 'M7 20l4-16', $icon );
	}

	public function test_get_feature_icon_returns_default_icon_for_unknown_slug() {
		$default_icon = UI::get_feature_icon( 'this-slug-does-not-exist' );

		$this->assertStringContainsString( '<svg', $default_icon );
		$this->assertSame( UI::get_feature_icon( 'another-unknown-slug' ), $default_icon );
	}

	public function test_get_feature_icon_returns_distinct_icons_for_different_known_slugs() {
		$this->assertNotSame( UI::get_feature_icon( 'counter' ), UI::get_feature_icon( 'gallery' ) );
	}

	public function test_show_info_card_outputs_title_description_and_icon() {
		ob_start();
		UI::show_info_card( 'counter', 'My Feature <script>', 'Some description & more' );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'frbl-feature-active', $output );
		$this->assertStringContainsString( 'My Feature &lt;script&gt;', $output );
		$this->assertStringContainsString( 'Some description &amp; more', $output );
		$this->assertStringContainsString( '<svg', $output );
		$this->assertStringNotContainsString( 'frbl-pro-badge', $output );
	}

	public function test_show_pro_info_card_outputs_pro_badge_and_link_to_pro_page() {
		ob_start();
		UI::show_pro_info_card( 'some-nonexistent-icon-slug', 'Pro Feature', 'Pro description' );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'frbl-feature-pro', $output );
		$this->assertStringContainsString( 'frbl-pro-badge', $output );
		$this->assertStringContainsString( 'PRO', $output );
		$this->assertStringContainsString( 'close.technology/wordpress-plugins/frontblocks-pro/', $output );
		$this->assertStringContainsString( 'Pro Feature', $output );
		$this->assertStringContainsString( 'Pro description', $output );
	}

	public function test_show_pro_info_card_escapes_title_and_description() {
		ob_start();
		UI::show_pro_info_card( 'missing-icon', '<b>Title</b>', '<i>Desc</i>' );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( '<b>Title</b>', $output );
		$this->assertStringContainsString( '&lt;b&gt;Title&lt;/b&gt;', $output );
	}
}
