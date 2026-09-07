<?php
/**
 * Tests for FrontBlocks\Frontend\FluidTypography.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\FluidTypography;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class FluidTypographyTest extends TestCase {

	/**
	 * @var FluidTypography
	 */
	private $fluid_typography;

	public function set_up() {
		parent::set_up();
		$this->fluid_typography = new FluidTypography();
	}

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		wp_dequeue_style( 'generate-style' );
		wp_deregister_style( 'generate-style' );
		wp_dequeue_style( 'child-style' );
		wp_deregister_style( 'child-style' );
		parent::tear_down();
	}

	/**
	 * Invoke a private/protected method on the instance under test.
	 *
	 * @param string $method Method name.
	 * @param array  $args   Arguments.
	 * @return mixed
	 */
	private function call( $method, array $args = array() ) {
		$reflected = new ReflectionMethod( FluidTypography::class, $method );
		$reflected->setAccessible( true );

		return $reflected->invokeArgs( $this->fluid_typography, $args );
	}

	public function test_is_enabled_defaults_to_true_when_option_absent() {
		delete_option( 'frontblocks_settings' );

		$this->assertTrue( $this->call( 'is_enabled' ) );
	}

	public function test_is_enabled_can_be_disabled_via_settings() {
		update_option( 'frontblocks_settings', array( 'enable_fluid_typography' => false ) );

		$this->assertFalse( $this->call( 'is_enabled' ) );
	}

	public function test_is_enabled_reflects_true_value_explicitly() {
		update_option( 'frontblocks_settings', array( 'enable_fluid_typography' => true ) );

		$this->assertTrue( $this->call( 'is_enabled' ) );
	}

	public function test_extract_and_convert_selector_builds_clamp_rule_for_heading() {
		$css = 'h1 { font-size: 40px; }'
			. '@media (max-width: 1024px) { h1 { font-size: 32px; } }'
			. '@media (max-width: 768px) { h1 { font-size: 24px; } }';

		$rule = $this->call( 'extract_and_convert_selector', array( $css, 'h1', false ) );

		$this->assertStringContainsString( 'body h1 {', $rule );
		$this->assertStringContainsString( 'clamp(24px,', $rule );
		$this->assertStringContainsString( ', 40px)', $rule );
		$this->assertStringContainsString( '!important', $rule );
	}

	public function test_extract_and_convert_selector_adds_generateblocks_selector_when_active() {
		$css = 'h2 { font-size: 30px; }'
			. '@media (max-width: 768px) { h2 { font-size: 20px; } }';

		$rule = $this->call( 'extract_and_convert_selector', array( $css, 'h2', true ) );

		$this->assertStringContainsString( 'body h2.gb-headline {', $rule );
	}

	public function test_extract_and_convert_selector_omits_generateblocks_selector_when_inactive() {
		$css = 'h2 { font-size: 30px; }'
			. '@media (max-width: 768px) { h2 { font-size: 20px; } }';

		$rule = $this->call( 'extract_and_convert_selector', array( $css, 'h2', false ) );

		$this->assertStringNotContainsString( 'gb-headline', $rule );
	}

	public function test_extract_and_convert_selector_handles_paragraph_selector_without_body_prefix() {
		$css = 'p { font-size: 18px; }'
			. '@media (max-width: 768px) { p { font-size: 14px; } }';

		$rule = $this->call( 'extract_and_convert_selector', array( $css, 'p', false ) );

		$this->assertStringContainsString( 'p {', $rule );
		$this->assertStringNotContainsString( 'body p {', $rule );
	}

	public function test_extract_and_convert_selector_returns_empty_when_mobile_size_missing() {
		$css = 'h3 { font-size: 28px; }';

		$rule = $this->call( 'extract_and_convert_selector', array( $css, 'h3', false ) );

		$this->assertSame( '', $rule );
	}

	public function test_extract_and_convert_selector_returns_empty_when_units_mismatch() {
		$css = 'h4 { font-size: 2rem; }'
			. '@media (max-width: 768px) { h4 { font-size: 20px; } }';

		$rule = $this->call( 'extract_and_convert_selector', array( $css, 'h4', false ) );

		$this->assertSame( '', $rule );
	}

	public function test_extract_and_convert_selector_returns_empty_when_sizes_are_equal() {
		$css = 'h5 { font-size: 20px; }'
			. '@media (max-width: 768px) { h5 { font-size: 20px; } }';

		$rule = $this->call( 'extract_and_convert_selector', array( $css, 'h5', false ) );

		$this->assertSame( '', $rule );
	}

	public function test_extract_and_convert_selector_supports_body_multiple_selector_pattern() {
		$css = 'body { font-size: 18px; }'
			. '@media (max-width: 768px) { body { font-size: 14px; } }';

		$rule = $this->call( 'extract_and_convert_selector', array( $css, 'body', false ) );

		$this->assertStringContainsString( 'body {', $rule );
		$this->assertStringContainsString( 'clamp(14px,', $rule );
	}

	public function test_extract_and_convert_selector_adds_gb_headline_text_for_body_when_generateblocks_active() {
		$css = 'body { font-size: 18px; }'
			. '@media (max-width: 768px) { body { font-size: 14px; } }';

		$rule = $this->call( 'extract_and_convert_selector', array( $css, 'body', true ) );

		$this->assertStringContainsString( 'p.gb-headline-text {', $rule );
	}

	public function test_convert_to_fluid_typography_combines_rules_for_multiple_selectors() {
		$css = 'h1 { font-size: 40px; }'
			. '@media (max-width: 768px) { h1 { font-size: 24px; } }'
			. 'p { font-size: 18px; }'
			. '@media (max-width: 768px) { p { font-size: 14px; } }';

		$fluid_css = $this->call( 'convert_to_fluid_typography', array( $css ) );

		$this->assertStringContainsString( 'body h1 {', $fluid_css );
		$this->assertStringContainsString( 'p {', $fluid_css );
	}

	public function test_convert_to_fluid_typography_returns_empty_string_for_css_without_font_sizes() {
		$css = '.some-class { color: red; }';

		$fluid_css = $this->call( 'convert_to_fluid_typography', array( $css ) );

		$this->assertSame( '', $fluid_css );
	}

	public function test_get_style_handle_prefers_generate_style_when_enqueued() {
		wp_register_style( 'generate-style', false, array(), '1.0' );
		wp_enqueue_style( 'generate-style' );

		$this->assertSame( 'generate-style', $this->call( 'get_style_handle' ) );
	}

	public function test_get_style_handle_falls_back_to_known_theme_candidate() {
		wp_register_style( 'child-style', false, array(), '1.0' );
		wp_enqueue_style( 'child-style' );

		$this->assertSame( 'child-style', $this->call( 'get_style_handle' ) );
	}

	public function test_get_style_handle_falls_back_to_wp_block_library_when_nothing_matches() {
		$this->assertSame( 'wp-block-library', $this->call( 'get_style_handle' ) );
	}
}
