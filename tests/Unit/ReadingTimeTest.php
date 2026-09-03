<?php
/**
 * Tests for FrontBlocks\Frontend\ReadingTime.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\ReadingTime;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ReadingTimeTest extends TestCase {

	/**
	 * @var ReadingTime
	 */
	private $reading_time;

	public function set_up() {
		parent::set_up();
		$this->reading_time = new ReadingTime();
	}

	public function tear_down() {
		wp_dequeue_style( 'frontblocks-reading-time-style' );
		parent::tear_down();
	}

	public function test_calculate_reading_time_returns_zero_when_no_post_id_is_available() {
		// Passing 0 explicitly skips the "use current post" branch and hits
		// the "no post id" guard directly, without depending on global state.
		$this->assertSame( 0, $this->reading_time->calculate_reading_time( 0 ) );
	}

	public function test_calculate_reading_time_rounds_up_to_the_next_minute() {
		// 450 words at 225 wpm is exactly 2 minutes.
		$content = implode( ' ', array_fill( 0, 450, 'word' ) );
		$post_id = self::factory()->post->create( array( 'post_content' => $content ) );

		$this->assertSame( 2, $this->reading_time->calculate_reading_time( $post_id ) );
	}

	public function test_calculate_reading_time_rounds_up_a_partial_minute() {
		// 226 words is just over 1 minute at 225 wpm, so it must round up to 2.
		$content = implode( ' ', array_fill( 0, 226, 'word' ) );
		$post_id = self::factory()->post->create( array( 'post_content' => $content ) );

		$this->assertSame( 2, $this->reading_time->calculate_reading_time( $post_id ) );
	}

	public function test_calculate_reading_time_has_a_minimum_of_one_minute() {
		$post_id = self::factory()->post->create( array( 'post_content' => '' ) );

		$this->assertSame( 1, $this->reading_time->calculate_reading_time( $post_id ) );
	}

	public function test_calculate_reading_time_prefers_stored_post_meta_over_recalculating() {
		$content = implode( ' ', array_fill( 0, 450, 'word' ) );
		$post_id = self::factory()->post->create( array( 'post_content' => $content ) );
		update_post_meta( $post_id, 'reading_time', 42 );

		$this->assertSame( 42, $this->reading_time->calculate_reading_time( $post_id ) );
	}

	public function test_render_reading_time_block_returns_empty_string_without_a_resolvable_post() {
		$this->assertSame( '', $this->reading_time->render_reading_time_block( array( 'postId' => 0 ) ) );
	}

	public function test_render_reading_time_block_outputs_the_icon_and_default_suffix() {
		$post_id = self::factory()->post->create( array( 'post_content' => str_repeat( 'word ', 225 ) ) );

		$output = $this->reading_time->render_reading_time_block( array( 'postId' => $post_id ) );

		$this->assertStringContainsString( 'frbl-reading-time-icon', $output );
		$this->assertStringContainsString( '<svg', $output );
		$this->assertMatchesRegularExpression( '/frbl-reading-time-text">\s*1\s*min/s', $output );
		$this->assertStringContainsString( 'align-left', $output );
	}

	public function test_render_reading_time_block_hides_the_icon_when_disabled() {
		$post_id = self::factory()->post->create( array( 'post_content' => str_repeat( 'word ', 225 ) ) );

		$output = $this->reading_time->render_reading_time_block(
			array(
				'postId'   => $post_id,
				'showIcon' => false,
			)
		);

		$this->assertStringNotContainsString( 'frbl-reading-time-icon', $output );
	}

	public function test_render_reading_time_block_applies_prefix_suffix_and_custom_styles() {
		$post_id = self::factory()->post->create( array( 'post_content' => str_repeat( 'word ', 225 ) ) );

		$output = $this->reading_time->render_reading_time_block(
			array(
				'postId'          => $post_id,
				'prefix'          => 'Takes',
				'suffix'          => 'minutes to read',
				'textColor'       => '#111111',
				'backgroundColor' => '#eeeeee',
				'fontSize'        => 20,
				'iconColor'       => '#ff0000',
				'alignment'       => 'center',
				'padding'         => 12,
				'borderRadius'    => 4,
				'className'       => 'my-custom-class',
			)
		);

		$this->assertStringContainsString( 'Takes', $output );
		$this->assertStringContainsString( 'minutes to read', $output );
		$this->assertStringContainsString( 'align-center', $output );
		$this->assertStringContainsString( 'my-custom-class', $output );
		$this->assertStringContainsString( '--frbl-text-color: #111111;', $output );
		$this->assertStringContainsString( '--frbl-bg-color: #eeeeee;', $output );
		$this->assertStringContainsString( '--frbl-font-size: 20px;', $output );
		$this->assertStringContainsString( '--frbl-icon-color: #ff0000;', $output );
		$this->assertStringContainsString( '--frbl-padding: 12px;', $output );
		$this->assertStringContainsString( '--frbl-border-radius: 4px;', $output );
	}

	public function test_shortcode_maps_show_icon_yes_no_to_a_boolean() {
		$post_id = self::factory()->post->create( array( 'post_content' => str_repeat( 'word ', 225 ) ) );

		$with_icon = $this->reading_time->reading_time_shortcode(
			array(
				'post_id'   => $post_id,
				'show_icon' => 'yes',
			)
		);
		$without_icon = $this->reading_time->reading_time_shortcode(
			array(
				'post_id'   => $post_id,
				'show_icon' => 'no',
			)
		);

		$this->assertStringContainsString( 'frbl-reading-time-icon', $with_icon );
		$this->assertStringNotContainsString( 'frbl-reading-time-icon', $without_icon );
	}

	public function test_shortcode_forwards_prefix_and_suffix_attributes() {
		$post_id = self::factory()->post->create( array( 'post_content' => str_repeat( 'word ', 225 ) ) );

		$output = $this->reading_time->reading_time_shortcode(
			array(
				'post_id' => $post_id,
				'prefix'  => 'This blog takes',
				'suffix'  => 'minutes to read',
			)
		);

		$this->assertStringContainsString( 'This blog takes', $output );
		$this->assertStringContainsString( 'minutes to read', $output );
	}

	public function test_register_reading_time_block_registers_the_expected_default_attributes() {
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'frontblocks/reading-time' );

		$this->assertNotNull( $block_type );
		$this->assertSame( 0, $block_type->attributes['postId']['default'] );
		$this->assertTrue( $block_type->attributes['showIcon']['default'] );
		$this->assertSame( 'min', $block_type->attributes['suffix']['default'] );
		$this->assertSame( 'left', $block_type->attributes['alignment']['default'] );
	}

	public function test_enqueue_style_only_loads_on_the_frontend_when_the_block_is_present() {
		$post_id = self::factory()->post->create(
			array( 'post_content' => '<!-- wp:frontblocks/reading-time /-->' )
		);
		go_to( get_permalink( $post_id ) );

		$this->reading_time->enqueue_style();

		$this->assertTrue( wp_style_is( 'frontblocks-reading-time-style', 'enqueued' ) );
	}

	public function test_enqueue_style_is_not_loaded_on_the_frontend_without_the_block() {
		$post_id = self::factory()->post->create( array( 'post_content' => 'No block here.' ) );
		go_to( get_permalink( $post_id ) );

		$this->reading_time->enqueue_style();

		$this->assertFalse( wp_style_is( 'frontblocks-reading-time-style', 'enqueued' ) );
	}
}
