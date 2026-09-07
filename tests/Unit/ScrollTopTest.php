<?php
/**
 * Tests for FrontBlocks\Frontend\ScrollTop.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\ScrollTop;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ScrollTopTest extends TestCase {

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		wp_dequeue_style( 'frontblocks-scroll-top' );
		wp_dequeue_script( 'frontblocks-scroll-top' );
		parent::tear_down();
	}

	public function test_constructor_registers_frontend_hooks_when_enabled() {
		update_option( 'frontblocks_settings', array( 'enable_scroll_top' => true ) );
		$scroll_top = new ScrollTop();

		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', array( $scroll_top, 'enqueue_assets' ) ) );
		$this->assertNotFalse( has_action( 'wp_footer', array( $scroll_top, 'render_button' ) ) );
	}

	public function test_constructor_registers_nothing_when_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_scroll_top' => false ) );
		$scroll_top = new ScrollTop();

		$this->assertFalse( has_action( 'wp_enqueue_scripts', array( $scroll_top, 'enqueue_assets' ) ) );
		$this->assertFalse( has_action( 'wp_footer', array( $scroll_top, 'render_button' ) ) );
	}

	public function test_render_button_uses_the_right_position_class_by_default() {
		update_option( 'frontblocks_settings', array( 'enable_scroll_top' => true ) );
		$scroll_top = new ScrollTop();

		ob_start();
		$scroll_top->render_button();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'frbl-scroll-top--right', $output );
		$this->assertStringNotContainsString( 'frbl-scroll-top--left', $output );
	}

	public function test_render_button_uses_left_position_class_when_configured() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_scroll_top'    => true,
				'scroll_top_position'  => 'bottom-left',
			)
		);
		$scroll_top = new ScrollTop();

		ob_start();
		$scroll_top->render_button();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'frbl-scroll-top--left', $output );
	}

	public function test_render_button_falls_back_to_the_default_svg_icon_when_no_url_is_set() {
		update_option( 'frontblocks_settings', array( 'enable_scroll_top' => true ) );
		$scroll_top = new ScrollTop();

		ob_start();
		$scroll_top->render_button();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<svg', $output );
		$this->assertStringNotContainsString( '<img', $output );
	}

	public function test_render_button_uses_the_custom_icon_url_when_provided() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_scroll_top'    => true,
				'scroll_top_icon_url'  => 'https://example.com/arrow.svg',
			)
		);
		$scroll_top = new ScrollTop();

		ob_start();
		$scroll_top->render_button();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<img src="https://example.com/arrow.svg"', $output );
		$this->assertStringNotContainsString( '<svg', $output );
	}

	public function test_enqueue_assets_localizes_the_configured_position() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_scroll_top'   => true,
				'scroll_top_position' => 'bottom-left',
			)
		);
		$scroll_top = new ScrollTop();
		$scroll_top->enqueue_assets();

		$this->assertTrue( wp_style_is( 'frontblocks-scroll-top', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-scroll-top', 'enqueued' ) );

		$localized_data = wp_scripts()->get_data( 'frontblocks-scroll-top', 'data' );
		$this->assertStringContainsString( 'bottom-left', $localized_data );
	}
}
