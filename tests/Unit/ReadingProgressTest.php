<?php
/**
 * Tests for FrontBlocks\Frontend\ReadingProgress.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\ReadingProgress;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ReadingProgressTest extends TestCase {

	/**
	 * @var ReadingProgress
	 */
	private $reading_progress;

	public function set_up() {
		parent::set_up();
		$this->reading_progress = new ReadingProgress();
	}

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		wp_dequeue_style( 'frontblocks-reading-progress' );
		wp_dequeue_script( 'frontblocks-reading-progress' );
		parent::tear_down();
	}

	/**
	 * Invoke the private is_enabled() method.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		$method = new ReflectionMethod( ReadingProgress::class, 'is_enabled' );
		$method->setAccessible( true );

		return $method->invoke( $this->reading_progress );
	}

	public function test_is_enabled_defaults_to_false_when_option_is_absent() {
		delete_option( 'frontblocks_settings' );
		$this->assertFalse( $this->is_enabled() );
	}

	public function test_is_enabled_follows_the_setting() {
		update_option( 'frontblocks_settings', array( 'enable_reading_progress' => true ) );
		$this->assertTrue( $this->is_enabled() );

		update_option( 'frontblocks_settings', array( 'enable_reading_progress' => false ) );
		$this->assertFalse( $this->is_enabled() );
	}

	public function test_render_bar_outputs_progressbar_markup_when_enabled_on_a_singular_post() {
		update_option( 'frontblocks_settings', array( 'enable_reading_progress' => true ) );
		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$this->assertTrue( is_singular( 'post' ), 'Precondition: the test must be viewing a singular post.' );

		ob_start();
		$this->reading_progress->render_reading_progress_bar();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'frbl-reading-progress-bar', $output );
		$this->assertStringContainsString( 'role="progressbar"', $output );
		$this->assertStringContainsString( 'frbl-reading-progress-fill', $output );
	}

	public function test_render_bar_outputs_nothing_when_the_feature_is_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_reading_progress' => false ) );
		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		ob_start();
		$this->reading_progress->render_reading_progress_bar();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_render_bar_outputs_nothing_on_a_non_post_singular_view() {
		update_option( 'frontblocks_settings', array( 'enable_reading_progress' => true ) );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$this->go_to( get_permalink( $page_id ) );

		ob_start();
		$this->reading_progress->render_reading_progress_bar();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_enqueue_frontend_assets_registers_assets_when_enabled_on_a_post() {
		update_option( 'frontblocks_settings', array( 'enable_reading_progress' => true ) );
		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$this->reading_progress->enqueue_frontend_assets();

		$this->assertTrue( wp_style_is( 'frontblocks-reading-progress', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-reading-progress', 'enqueued' ) );
	}

	public function test_enqueue_frontend_assets_does_nothing_when_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_reading_progress' => false ) );
		$post_id = self::factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );

		$this->reading_progress->enqueue_frontend_assets();

		$this->assertFalse( wp_style_is( 'frontblocks-reading-progress', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'frontblocks-reading-progress', 'enqueued' ) );
	}

	public function test_enqueue_frontend_assets_does_nothing_on_a_non_post_view() {
		update_option( 'frontblocks_settings', array( 'enable_reading_progress' => true ) );
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$this->go_to( get_permalink( $page_id ) );

		$this->reading_progress->enqueue_frontend_assets();

		$this->assertFalse( wp_style_is( 'frontblocks-reading-progress', 'enqueued' ) );
	}
}
