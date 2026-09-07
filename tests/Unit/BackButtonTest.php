<?php
/**
 * Tests for FrontBlocks\Frontend\BackButton.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\BackButton;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class BackButtonTest extends TestCase {

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		wp_dequeue_style( 'frontblocks-back-button' );
		wp_deregister_style( 'frontblocks-back-button' );
		wp_dequeue_script( 'frontblocks-back-button' );
		wp_deregister_script( 'frontblocks-back-button' );
		parent::tear_down();
	}

	private function is_enabled( BackButton $back_button ) {
		$method = new ReflectionMethod( BackButton::class, 'is_enabled' );
		$method->setAccessible( true );

		return $method->invoke( $back_button );
	}

	public function test_is_enabled_is_false_when_the_setting_is_absent() {
		delete_option( 'frontblocks_settings' );
		$back_button = new BackButton();

		$this->assertFalse( $this->is_enabled( $back_button ) );
	}

	public function test_is_enabled_is_false_when_the_setting_is_explicitly_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_back_button' => false ) );
		$back_button = new BackButton();

		$this->assertFalse( $this->is_enabled( $back_button ) );
	}

	public function test_is_enabled_casts_truthy_setting_to_bool() {
		update_option( 'frontblocks_settings', array( 'enable_back_button' => '1' ) );
		$back_button = new BackButton();

		$this->assertTrue( $this->is_enabled( $back_button ) );
	}

	/**
	 * When enabled and on the frontend, the constructor must wire up both the
	 * asset enqueue and the footer render hooks for this specific instance.
	 */
	public function test_constructor_registers_hooks_when_enabled_and_not_in_admin() {
		update_option( 'frontblocks_settings', array( 'enable_back_button' => true ) );
		$this->assertFalse( is_admin(), 'This test assumes a frontend context (is_admin() === false).' );

		$back_button = new BackButton();

		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', array( $back_button, 'enqueue_assets' ) ) );
		$this->assertNotFalse( has_action( 'wp_footer', array( $back_button, 'render_button' ) ) );

		remove_action( 'wp_enqueue_scripts', array( $back_button, 'enqueue_assets' ) );
		remove_action( 'wp_footer', array( $back_button, 'render_button' ) );
	}

	/**
	 * When disabled, the constructor must not wire up any hooks for the new
	 * instance at all.
	 */
	public function test_constructor_does_not_register_hooks_when_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_back_button' => false ) );

		$back_button = new BackButton();

		$this->assertFalse( has_action( 'wp_enqueue_scripts', array( $back_button, 'enqueue_assets' ) ) );
		$this->assertFalse( has_action( 'wp_footer', array( $back_button, 'render_button' ) ) );
	}

	public function test_enqueue_assets_registers_style_and_script() {
		$back_button = new BackButton();
		$back_button->enqueue_assets();

		$this->assertTrue( wp_style_is( 'frontblocks-back-button', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-back-button', 'enqueued' ) );
	}

	/**
	 * The rendered markup must contain the expected button id, accessible
	 * label, and inline SVG icon — this is the actual visible output of the
	 * feature.
	 */
	public function test_render_button_outputs_expected_markup() {
		$back_button = new BackButton();

		ob_start();
		$back_button->render_button();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'id="frbl-back-button"', $html );
		$this->assertStringContainsString( 'class="frbl-back-button"', $html );
		$this->assertStringContainsString( 'aria-label="Go back to previous page"', $html );
		$this->assertStringContainsString( 'title="Go back"', $html );
		$this->assertStringContainsString( '<svg', $html );
	}
}
