<?php
/**
 * Tests for FrontBlocks\Frontend\Headline.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Headline;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class HeadlineTest extends TestCase {

	/**
	 * @var Headline
	 */
	private $headline;

	public function set_up() {
		parent::set_up();
		$this->headline = new Headline();
	}

	public function test_constructor_registers_the_expected_hooks() {
		$this->assertNotFalse( has_filter( 'generateblocks_attr_headline', array( $this->headline, 'add_line_class_attribute' ) ) );
		$this->assertNotFalse( has_filter( 'generateblocks_attr_text', array( $this->headline, 'add_marquee_speed_attribute' ) ) );
		$this->assertNotFalse( has_filter( 'render_block_generateblocks/text', array( $this->headline, 'maybe_enqueue_frontend_assets' ) ) );
		$this->assertNotFalse( has_filter( 'render_block_generateblocks/headline', array( $this->headline, 'maybe_enqueue_frontend_assets' ) ) );
		$this->assertNotFalse( has_filter( 'register_block_type_args', array( $this->headline, 'register_native_block_attributes' ) ) );
		$this->assertNotFalse( has_filter( 'render_block_core/paragraph', array( $this->headline, 'apply_marquee_to_native_block' ) ) );
		$this->assertNotFalse( has_filter( 'render_block_core/heading', array( $this->headline, 'apply_marquee_to_native_block' ) ) );
	}

	public function test_register_native_block_attributes_ignores_unrelated_block_types() {
		$args = array( 'foo' => 'bar' );

		$this->assertSame( $args, $this->headline->register_native_block_attributes( $args, 'core/group' ) );
	}

	public function test_register_native_block_attributes_adds_marquee_speed_for_paragraph_and_heading() {
		$paragraph_result = $this->headline->register_native_block_attributes( array(), 'core/paragraph' );
		$heading_result   = $this->headline->register_native_block_attributes( array(), 'core/heading' );

		$expected = array(
			'type'    => 'string',
			'default' => '',
		);

		$this->assertSame( $expected, $paragraph_result['attributes']['frblMarqueeSpeed'] );
		$this->assertSame( $expected, $heading_result['attributes']['frblMarqueeSpeed'] );
	}

	public function test_apply_marquee_ignores_blocks_without_the_marquee_class() {
		$content = '<p class="some-other-class">Text</p>';
		$block   = array( 'attrs' => array( 'className' => 'some-other-class' ) );

		$this->assertSame( $content, $this->headline->apply_marquee_to_native_block( $content, $block ) );
	}

	public function test_apply_marquee_uses_preset_speed_and_injects_data_attribute() {
		$content = '<p class="gb-marquee-infinite-scroll foo">Text</p>';
		$block   = array(
			'attrs' => array(
				'className'        => 'gb-marquee-infinite-scroll foo',
				'frblMarqueeSpeed' => 'fast',
			),
		);

		$result = $this->headline->apply_marquee_to_native_block( $content, $block );

		$this->assertStringContainsString( 'data-marquee-speed="10"', $result );
		$this->assertStringContainsString( '>Text</p>', $result );
	}

	public function test_apply_marquee_defaults_to_medium_speed_when_not_specified() {
		$content = '<p class="gb-marquee-infinite-scroll foo">Text</p>';
		$block   = array( 'attrs' => array( 'className' => 'gb-marquee-infinite-scroll foo' ) );

		$result = $this->headline->apply_marquee_to_native_block( $content, $block );

		$this->assertStringContainsString( 'data-marquee-speed="20"', $result );
	}

	public function test_apply_marquee_falls_back_to_20_for_unknown_preset() {
		$content = '<p class="gb-marquee-infinite-scroll foo">Text</p>';
		$block   = array(
			'attrs' => array(
				'className'        => 'gb-marquee-infinite-scroll foo',
				'frblMarqueeSpeed' => 'ultra',
			),
		);

		$result = $this->headline->apply_marquee_to_native_block( $content, $block );

		$this->assertStringContainsString( 'data-marquee-speed="20"', $result );
	}

	public function test_marquee_speed_attribute_defaults_to_20_without_block_attributes() {
		$attributes = $this->headline->add_marquee_speed_attribute( array(), array() );

		$this->assertSame( 20, $attributes['data-marquee-speed'] );
	}

	public function test_marquee_speed_attribute_resolves_named_preset() {
		$attributes = $this->headline->add_marquee_speed_attribute( array(), array( 'frblMarqueeSpeed' => 'slow' ) );

		$this->assertSame( 40, $attributes['data-marquee-speed'] );
	}

	public function test_marquee_speed_attribute_parses_numeric_string() {
		$attributes = $this->headline->add_marquee_speed_attribute( array(), array( 'frblMarqueeSpeed' => '15' ) );

		$this->assertSame( 15, $attributes['data-marquee-speed'] );
	}

	public function test_marquee_speed_attribute_falls_back_to_20_for_zero() {
		$attributes = $this->headline->add_marquee_speed_attribute( array(), array( 'frblMarqueeSpeed' => '0' ) );

		$this->assertSame( 20, $attributes['data-marquee-speed'] );
	}

	public function test_html_attributes_numeric_override_takes_precedence() {
		$attributes = $this->headline->add_marquee_speed_attribute(
			array(),
			array(
				'frblMarqueeSpeed' => 'fast',
				'htmlAttributes'   => array( 'data-marquee-speed' => '30' ),
			)
		);

		$this->assertSame( 30, $attributes['data-marquee-speed'] );
	}

	public function test_html_attributes_preset_override_takes_precedence() {
		$attributes = $this->headline->add_marquee_speed_attribute(
			array(),
			array( 'htmlAttributes' => array( 'data-marquee-speed' => 'slow' ) )
		);

		$this->assertSame( 40, $attributes['data-marquee-speed'] );
	}
}
