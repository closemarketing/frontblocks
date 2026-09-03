<?php
/**
 * Tests for FrontBlocks\Frontend\TextAnimation.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\TextAnimation;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class TextAnimationTest extends TestCase {

	/**
	 * @var TextAnimation
	 */
	private $text_animation;

	public function set_up() {
		parent::set_up();
		$this->text_animation = new TextAnimation();
	}

	public function tear_down() {
		foreach ( array( 'frontblocks-text-animation-frontend', 'frontblocks-animation-fade-in', 'frontblocks-animation-typewriter' ) as $script ) {
			wp_dequeue_script( $script );
		}
		wp_dequeue_style( 'frontblocks-text-animation' );
		parent::tear_down();
	}

	public function test_constructor_registers_expected_hooks() {
		$this->assertNotFalse( has_action( 'init', array( $this->text_animation, 'register_block' ) ) );
		$this->assertNotFalse( has_action( 'enqueue_block_editor_assets', array( $this->text_animation, 'register_text_animation_attributes' ) ) );
		$this->assertNotFalse( has_action( 'enqueue_block_editor_assets', array( $this->text_animation, 'enqueue_editor_assets' ) ) );
		$this->assertNotFalse( has_filter( 'render_block_frontblocks/text-animation', array( $this->text_animation, 'maybe_enqueue_frontend_assets' ) ) );
		$this->assertNotFalse( has_filter( 'register_block_type_args', array( $this->text_animation, 'register_native_block_attributes' ) ) );
		$this->assertNotFalse( has_filter( 'render_block_core/paragraph', array( $this->text_animation, 'apply_text_animation_to_native_block' ) ) );
		$this->assertNotFalse( has_filter( 'render_block_core/heading', array( $this->text_animation, 'apply_text_animation_to_native_block' ) ) );
	}

	public function test_register_native_block_attributes_adds_attributes_for_paragraph_and_heading() {
		$args = $this->text_animation->register_native_block_attributes( array(), 'core/paragraph' );

		$this->assertSame( 'string', $args['attributes']['frblTextAnimation']['type'] );
		$this->assertSame( 'none', $args['attributes']['frblTextAnimation']['default'] );
		$this->assertSame( 'boolean', $args['attributes']['frblTextAnimationLoop']['type'] );
		$this->assertFalse( $args['attributes']['frblTextAnimationLoop']['default'] );

		$args = $this->text_animation->register_native_block_attributes( array(), 'core/heading' );
		$this->assertArrayHasKey( 'frblTextAnimation', $args['attributes'] );
	}

	public function test_register_native_block_attributes_leaves_unrelated_blocks_untouched() {
		$args = array( 'foo' => 'bar' );

		$result = $this->text_animation->register_native_block_attributes( $args, 'core/image' );

		$this->assertSame( $args, $result );
	}

	public function test_register_native_block_attributes_preserves_existing_attributes() {
		$args = array(
			'attributes' => array(
				'content' => array( 'type' => 'string' ),
			),
		);

		$result = $this->text_animation->register_native_block_attributes( $args, 'core/paragraph' );

		$this->assertArrayHasKey( 'content', $result['attributes'] );
		$this->assertArrayHasKey( 'frblTextAnimation', $result['attributes'] );
	}

	public function test_apply_text_animation_to_native_block_returns_content_unchanged_when_animation_class_already_present() {
		$content = '<p class="frbl-text-animation" data-animation="fade-in">Hello</p>';

		$result = $this->text_animation->apply_text_animation_to_native_block( $content, array() );

		$this->assertSame( $content, $result );
		$this->assertTrue( wp_script_is( 'frontblocks-animation-fade-in', 'enqueued' ) );
	}

	public function test_apply_text_animation_to_native_block_ignores_none_animation() {
		$content = '<p>Hello</p>';
		$block   = array( 'attrs' => array( 'frblTextAnimation' => 'none' ) );

		$result = $this->text_animation->apply_text_animation_to_native_block( $content, $block );

		$this->assertSame( $content, $result );
	}

	public function test_apply_text_animation_to_native_block_ignores_missing_attribute() {
		$content = '<p>Hello</p>';
		$block   = array( 'attrs' => array() );

		$result = $this->text_animation->apply_text_animation_to_native_block( $content, $block );

		$this->assertSame( $content, $result );
	}

	public function test_apply_text_animation_to_native_block_injects_class_and_data_attribute_via_fallback() {
		$content = '<p class="has-text-align-center">Hello world</p>';
		$block   = array( 'attrs' => array( 'frblTextAnimation' => 'typewriter' ) );

		$result = $this->text_animation->apply_text_animation_to_native_block( $content, $block );

		$this->assertStringContainsString( 'frbl-text-animation', $result );
		$this->assertStringContainsString( 'data-animation="typewriter"', $result );
		$this->assertStringContainsString( 'has-text-align-center', $result );
		$this->assertTrue( wp_script_is( 'frontblocks-animation-typewriter', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'frontblocks-text-animation', 'enqueued' ) );
	}

	public function test_apply_text_animation_to_native_block_adds_class_attribute_when_none_present() {
		$content = '<h2>Title</h2>';
		$block   = array( 'attrs' => array( 'frblTextAnimation' => 'fade-in' ) );

		$result = $this->text_animation->apply_text_animation_to_native_block( $content, $block );

		$this->assertStringContainsString( 'class="frbl-text-animation"', $result );
		$this->assertStringContainsString( 'data-animation="fade-in"', $result );
	}

	public function test_maybe_enqueue_frontend_assets_ignores_none_animation() {
		$content = '<div>content</div>';
		$block   = array( 'attrs' => array( 'animationType' => 'none' ) );

		$result = $this->text_animation->maybe_enqueue_frontend_assets( $content, $block );

		$this->assertSame( $content, $result );
		$this->assertFalse( wp_script_is( 'frontblocks-text-animation-frontend', 'enqueued' ) );
	}

	public function test_maybe_enqueue_frontend_assets_enqueues_scripts_for_known_animation() {
		$content = '<div>content</div>';
		$block   = array( 'attrs' => array( 'animationType' => 'bounce-in' ) );

		$result = $this->text_animation->maybe_enqueue_frontend_assets( $content, $block );

		$this->assertSame( $content, $result );
		$this->assertTrue( wp_script_is( 'frontblocks-text-animation-frontend', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-animation-bounce-in', 'enqueued' ) );
	}

	public function test_maybe_enqueue_frontend_assets_defaults_missing_attribute_to_none() {
		$content = '<div>content</div>';
		$block   = array( 'attrs' => array() );

		$result = $this->text_animation->maybe_enqueue_frontend_assets( $content, $block );

		$this->assertSame( $content, $result );
	}

	/**
	 * enqueue_animation_scripts() is private but is where the animation-to-script
	 * mapping and the "don't double enqueue" logic actually live.
	 */
	public function test_enqueue_animation_scripts_does_nothing_extra_for_unknown_animation_slug() {
		$method = new ReflectionMethod( TextAnimation::class, 'enqueue_animation_scripts' );
		$method->setAccessible( true );

		$method->invoke( $this->text_animation, 'not-a-real-animation' );

		$this->assertTrue( wp_style_is( 'frontblocks-text-animation', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-text-animation-frontend', 'enqueued' ) );
	}
}
