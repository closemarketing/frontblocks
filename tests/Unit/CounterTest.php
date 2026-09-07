<?php
/**
 * Tests for FrontBlocks\Frontend\Counter.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Counter;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class CounterTest extends TestCase {

	/**
	 * @var Counter
	 */
	private $counter;

	public function set_up() {
		parent::set_up();
		$this->counter = new Counter();
	}

	public function tear_down() {
		wp_dequeue_script( 'frontblocks-counter-runtime' );
		parent::tear_down();
	}

	public function test_constructor_registers_render_block_filter() {
		$this->assertNotFalse(
			has_filter( 'render_block', array( $this->counter, 'render_block_counter' ) )
		);
	}

	public function test_content_unchanged_for_unsupported_block() {
		$block   = array(
			'blockName' => 'core/image',
			'attrs'     => array( 'isCounterActive' => true ),
		);
		$content = '<img src="test.jpg" />';

		$this->assertSame( $content, $this->counter->render_block_counter( $content, $block ) );
	}

	public function test_content_unchanged_when_counter_not_active() {
		$block   = array(
			'blockName' => 'core/heading',
			'attrs'     => array( 'isCounterActive' => false ),
		);
		$content = '<h2 class="foo">100</h2>';

		$this->assertSame( $content, $this->counter->render_block_counter( $content, $block ) );
	}

	public function test_data_attributes_use_final_number_when_provided() {
		$block = array(
			'blockName' => 'core/heading',
			'attrs'     => array(
				'isCounterActive'   => true,
				'animationDuration' => 1500,
				'startNumber'       => '10',
				'finalNumber'       => '250',
				'numberPrefix'      => '$',
				'numberSuffix'      => '+',
			),
		);
		$content = '<h2 class="foo">100</h2>';

		$output = $this->counter->render_block_counter( $content, $block );

		$this->assertStringContainsString( 'data-counter-target="250"', $output );
		$this->assertStringContainsString( 'data-counter-start="10"', $output );
		$this->assertStringContainsString( 'data-counter-duration="1500"', $output );
		$this->assertStringContainsString( 'data-counter-prefix="$"', $output );
		$this->assertStringContainsString( 'data-counter-suffix="+"', $output );
		$this->assertStringContainsString( 'frontblocks-counter-active', $output );
	}

	public function test_target_extracted_from_block_content_when_final_number_absent() {
		$block = array(
			'blockName' => 'core/paragraph',
			'attrs'     => array( 'isCounterActive' => true ),
		);
		$content = '<p class="foo">1234</p>';

		$output = $this->counter->render_block_counter( $content, $block );

		$this->assertStringContainsString( 'data-counter-target="1234"', $output );
	}

	public function test_defaults_are_used_when_attributes_absent() {
		$block = array(
			'blockName' => 'generateblocks/text',
			'attrs'     => array( 'isCounterActive' => true ),
		);
		$content = '<div class="foo">42</div>';

		$output = $this->counter->render_block_counter( $content, $block );

		$this->assertStringContainsString( 'data-counter-start="0"', $output );
		$this->assertStringContainsString( 'data-counter-duration="2000"', $output );
		$this->assertStringContainsString( 'data-counter-prefix=""', $output );
		$this->assertStringContainsString( 'data-counter-suffix=""', $output );
	}

	public function test_content_unchanged_when_no_target_value_can_be_found() {
		$block = array(
			'blockName' => 'generateblocks/headline',
			'attrs'     => array( 'isCounterActive' => true ),
		);
		// No inner tag/text to extract a target number from.
		$content = 'plain text without tags';

		$this->assertSame( $content, $this->counter->render_block_counter( $content, $block ) );
	}

	public function test_counter_script_enqueued_when_active() {
		$block = array(
			'blockName' => 'core/heading',
			'attrs'     => array(
				'isCounterActive' => true,
				'finalNumber'     => '99',
			),
		);
		$content = '<h2 class="foo">99</h2>';

		$this->counter->render_block_counter( $content, $block );

		$this->assertTrue( wp_script_is( 'frontblocks-counter-runtime', 'enqueued' ) );
	}
}
