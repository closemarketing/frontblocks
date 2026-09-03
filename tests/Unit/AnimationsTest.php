<?php
/**
 * Tests for FrontBlocks\Frontend\Animations.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Animations;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class AnimationsTest extends TestCase {

	/**
	 * @var Animations
	 */
	private $animations;

	public function set_up() {
		parent::set_up();
		$this->animations = new Animations();
	}

	public function tear_down() {
		wp_dequeue_style( 'frontblocks-animations' );
		wp_deregister_style( 'frontblocks-animations' );
		wp_dequeue_script( 'frontblocks-animations-custom' );
		wp_deregister_script( 'frontblocks-animations-custom' );
		parent::tear_down();
	}

	/**
	 * Blocks without any attributes at all must be left completely untouched.
	 */
	public function test_block_without_attrs_is_returned_unchanged() {
		$content = '<div class="wp-block-group">Hello</div>';
		$block   = array( 'attrs' => array() );

		$this->assertSame( $content, $this->animations->add_animation_classes_to_blocks( $content, $block ) );
	}

	/**
	 * Attributes present but none of the animation/glass/hover flags set must
	 * also leave the markup untouched (and must not enqueue any assets).
	 */
	public function test_block_with_unrelated_attrs_is_returned_unchanged_and_does_not_enqueue_assets() {
		$content = '<div class="wp-block-group">Hello</div>';
		$block   = array( 'attrs' => array( 'someOtherAttr' => 'value' ) );

		$result = $this->animations->add_animation_classes_to_blocks( $content, $block );

		$this->assertSame( $content, $result );
		$this->assertFalse( wp_style_is( 'frontblocks-animations', 'enqueued' ) );
	}

	/**
	 * A block with an animation attribute gets the animate.css classes and
	 * matching data-* attributes injected into its first tag, and the
	 * frontend assets get enqueued.
	 */
	public function test_animation_attribute_adds_classes_and_data_attributes() {
		$this->animations->register_scripts();

		$content = '<div class="wp-block-group">Hello</div>';
		$block   = array(
			'attrs' => array(
				'frblAnimation' => 'fadeIn',
			),
		);

		$result = $this->animations->add_animation_classes_to_blocks( $content, $block );

		$this->assertStringContainsString( 'animate__animated', $result );
		$this->assertStringContainsString( 'animate__fadeIn', $result );
		$this->assertStringContainsString( 'data-frontblocks-animation="fadeIn"', $result );
		$this->assertStringContainsString( 'data-frontblocks-animation-delay="0"', $result );
		$this->assertStringContainsString( 'data-frontblocks-animation-duration="1"', $result );
		$this->assertStringContainsString( 'wp-block-group', $result, 'The original class must be preserved, not replaced.' );
		$this->assertTrue( wp_style_is( 'frontblocks-animations', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-animations-custom', 'enqueued' ) );
	}

	/**
	 * A non-default delay/duration must be reflected in an inline style
	 * attribute using the animate.css custom properties.
	 */
	public function test_non_default_delay_and_duration_are_added_as_inline_style() {
		$this->animations->register_scripts();

		$content = '<div class="wp-block-group">Hello</div>';
		$block   = array(
			'attrs' => array(
				'frblAnimation'         => 'fadeIn',
				'frblAnimationDelay'    => 0.5,
				'frblAnimationDuration' => 2,
			),
		);

		$result = $this->animations->add_animation_classes_to_blocks( $content, $block );

		$this->assertStringContainsString( '--animate-delay:0.5s;', $result );
		$this->assertStringContainsString( '--animate-duration:2s;', $result );
	}

	/**
	 * Infinite repeat takes priority over the plain repeat flag.
	 */
	public function test_infinite_repeat_takes_priority_over_plain_repeat() {
		$this->animations->register_scripts();

		$content = '<div class="wp-block-group">Hello</div>';
		$block   = array(
			'attrs' => array(
				'frblAnimation'         => 'bounce',
				'frblAnimationRepeat'   => true,
				'frblAnimationInfinite' => true,
			),
		);

		$result = $this->animations->add_animation_classes_to_blocks( $content, $block );

		$this->assertStringContainsString( '--animate-repeat:infinite;', $result );
		$this->assertStringNotContainsString( '--animate-repeat:2;', $result );
	}

	/**
	 * Disabling the animation on mobile appends the dedicated helper class.
	 */
	public function test_disable_animation_on_mobile_adds_helper_class() {
		$this->animations->register_scripts();

		$content = '<div class="wp-block-group">Hello</div>';
		$block   = array(
			'attrs' => array(
				'frblAnimation'              => 'fadeIn',
				'frblDisableAnimationMobile' => true,
			),
		);

		$result = $this->animations->add_animation_classes_to_blocks( $content, $block );

		$this->assertStringContainsString( 'frbl-no-mobile-animation', $result );
	}

	/**
	 * The glass effect adds its own class, backdrop-filter style rules using
	 * the configured blur amount, and a data attribute for JS to read.
	 */
	public function test_glass_effect_adds_class_style_and_data_attribute() {
		$this->animations->register_scripts();

		$content = '<div class="wp-block-group">Hello</div>';
		$block   = array(
			'attrs' => array(
				'frblGlassEffect' => true,
				'frblGlassBlur'   => 15,
			),
		);

		$result = $this->animations->add_animation_classes_to_blocks( $content, $block );

		$this->assertStringContainsString( 'frbl-glass-effect', $result );
		$this->assertStringContainsString( 'backdrop-filter:blur(15px);', $result );
		$this->assertStringContainsString( 'data-frontblocks-glass-blur="15"', $result );
	}

	/**
	 * The hover background scale option adds its own class, a CSS custom
	 * property with the scale amount, and a matching data attribute.
	 */
	public function test_hover_bg_scale_adds_class_style_and_data_attribute() {
		$this->animations->register_scripts();

		$content = '<div class="wp-block-group">Hello</div>';
		$block   = array(
			'attrs' => array(
				'frblHoverBgScale'       => true,
				'frblHoverBgScaleAmount' => 1.25,
			),
		);

		$result = $this->animations->add_animation_classes_to_blocks( $content, $block );

		$this->assertStringContainsString( 'frbl-hover-bg-scale', $result );
		$this->assertStringContainsString( '--frbl-hover-scale:1.25;', $result );
		$this->assertStringContainsString( 'data-frontblocks-hover-scale="1.25"', $result );
	}

	/**
	 * All three feature groups (animation, glass, hover scale) can combine on
	 * a single block and their classes/styles are all appended together.
	 */
	public function test_all_effects_combine_on_the_same_block() {
		$this->animations->register_scripts();

		$content = '<div class="wp-block-group">Hello</div>';
		$block   = array(
			'attrs' => array(
				'frblAnimation'    => 'zoomIn',
				'frblGlassEffect'  => true,
				'frblHoverBgScale' => true,
			),
		);

		$result = $this->animations->add_animation_classes_to_blocks( $content, $block );

		$this->assertStringContainsString( 'animate__zoomIn', $result );
		$this->assertStringContainsString( 'frbl-glass-effect', $result );
		$this->assertStringContainsString( 'frbl-hover-bg-scale', $result );
	}
}
