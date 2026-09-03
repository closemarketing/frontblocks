<?php
/**
 * Tests for FrontBlocks\Frontend\ShapeAnimations.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\ShapeAnimations;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ShapeAnimationsTest extends TestCase {

	/**
	 * @var ShapeAnimations
	 */
	private $shape_animations;

	public function set_up() {
		parent::set_up();
		$this->shape_animations = new ShapeAnimations();
		$this->shape_animations->register_scripts();
	}

	public function tear_down() {
		// The queued CSS keyframes are kept on a private static property that
		// outlives individual instances; reset it so tests don't leak state.
		$property = new ReflectionProperty( ShapeAnimations::class, 'queued_css' );
		$property->setAccessible( true );
		$property->setValue( null, array() );

		wp_dequeue_style( 'frontblocks-shape-animations' );
		wp_dequeue_script( 'frontblocks-shape-animations' );

		parent::tear_down();
	}

	/**
	 * Invoke the private is_lottie_json() method.
	 *
	 * @param array $json_data Parsed JSON payload.
	 * @return bool
	 */
	private function is_lottie_json( $json_data ) {
		$method = new ReflectionMethod( ShapeAnimations::class, 'is_lottie_json' );
		$method->setAccessible( true );

		return $method->invoke( $this->shape_animations, $json_data );
	}

	public function test_is_lottie_json_detects_a_valid_lottie_payload() {
		$this->assertTrue(
			$this->is_lottie_json(
				array(
					'v'      => '5.5.2',
					'fr'     => 30,
					'layers' => array(),
				)
			)
		);
	}

	public function test_is_lottie_json_rejects_a_partial_payload() {
		$this->assertFalse( $this->is_lottie_json( array( 'v' => '5.5.2' ) ) );
		$this->assertFalse( $this->is_lottie_json( array() ) );
		$this->assertFalse( $this->is_lottie_json( array( 'svg' => '<svg></svg>' ) ) );
	}

	public function test_get_svg_allowed_tags_whitelists_the_expected_elements_and_attributes() {
		$method = new ReflectionMethod( ShapeAnimations::class, 'get_svg_allowed_tags' );
		$method->setAccessible( true );
		$allowed = $method->invoke( $this->shape_animations );

		$this->assertArrayHasKey( 'svg', $allowed );
		$this->assertArrayHasKey( 'path', $allowed );
		$this->assertArrayHasKey( 'viewbox', $allowed['svg'] );
		$this->assertArrayHasKey( 'd', $allowed['path'] );
		$this->assertArrayNotHasKey( 'script', $allowed );
	}

	public function test_add_animation_classes_ignores_unrelated_blocks() {
		$block_content = '<div class="wp-block-paragraph">Hello</div>';
		$block         = array(
			'blockName' => 'core/paragraph',
			'attrs'     => array( 'frblCustomSvgAnimationEnabled' => true ),
		);

		$this->assertSame(
			$block_content,
			$this->shape_animations->add_animation_classes_to_shape( $block_content, $block )
		);
	}

	public function test_add_animation_classes_ignores_shapes_without_animation_enabled() {
		$block_content = '<div class="wp-block-generateblocks-shape"><svg></svg></div>';
		$block         = array(
			'blockName' => 'generateblocks/shape',
			'attrs'     => array( 'frblCustomSvgAnimationEnabled' => false ),
		);

		$this->assertSame(
			$block_content,
			$this->shape_animations->add_animation_classes_to_shape( $block_content, $block )
		);
	}

	public function test_add_animation_classes_ignores_shapes_with_empty_animation_json() {
		$block_content = '<div class="wp-block-generateblocks-shape"><svg></svg></div>';
		$block         = array(
			'blockName' => 'generateblocks/shape',
			'attrs'     => array(
				'frblCustomSvgAnimationEnabled' => true,
				'frblCustomSvgAnimationJson'    => '',
			),
		);

		$this->assertSame(
			$block_content,
			$this->shape_animations->add_animation_classes_to_shape( $block_content, $block )
		);
	}

	public function test_add_animation_classes_ignores_invalid_json() {
		$block_content = '<div class="wp-block-generateblocks-shape"><svg></svg></div>';
		$block         = array(
			'blockName' => 'generateblocks/shape',
			'attrs'     => array(
				'frblCustomSvgAnimationEnabled' => true,
				'frblCustomSvgAnimationJson'    => 'not-json',
			),
		);

		$this->assertSame(
			$block_content,
			$this->shape_animations->add_animation_classes_to_shape( $block_content, $block )
		);
	}

	public function test_add_animation_classes_renders_a_lottie_container_and_enqueues_assets() {
		$block_content = '<div class="wp-block-generateblocks-shape"><svg viewBox="0 0 1 1"><rect/></svg></div>';
		$json          = array(
			'v'         => '5.5.2',
			'fr'        => 30,
			'layers'    => array(),
			'animation' => array(
				'loop'     => false,
				'autoplay' => false,
				'speed'    => 2,
			),
		);
		$block = array(
			'blockName' => 'generateblocks/shape',
			'attrs'     => array(
				'frblCustomSvgAnimationEnabled' => true,
				'frblCustomSvgAnimationJson'    => wp_json_encode( $json ),
			),
		);

		$output = $this->shape_animations->add_animation_classes_to_shape( $block_content, $block );

		$this->assertStringContainsString( 'frbl-lottie-animation', $output );
		$this->assertStringContainsString( 'frbl-has-lottie-animation', $output );
		$this->assertStringContainsString( 'data-loop="false"', $output );
		$this->assertStringContainsString( 'data-autoplay="false"', $output );
		$this->assertStringContainsString( 'data-speed="2"', $output );
		$this->assertStringNotContainsString( '<svg', $output );

		$this->assertTrue( wp_style_is( 'frontblocks-shape-animations', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-shape-animations', 'enqueued' ) );
	}

	public function test_add_animation_classes_renders_a_css_animation_and_queues_keyframes() {
		$block_content = '<div class="wp-block-generateblocks-shape"><svg><circle cx="1" cy="1" r="1"></circle></svg></div>';
		$json          = array(
			'svg'       => '<svg><circle cx="1" cy="1" r="1" fill="red"></circle></svg>',
			'animation' => array(
				'name'      => 'frblPulse',
				'trigger'   => 'hover',
				'keyframes' => '@keyframes frblPulse { from { opacity: 0; } to { opacity: 1; } }',
				'duration'  => '2s',
				'delay'     => '0.5s',
				'infinite'  => true,
			),
		);
		$block = array(
			'blockName' => 'generateblocks/shape',
			'attrs'     => array(
				'frblCustomSvgAnimationEnabled' => true,
				'frblCustomSvgAnimationJson'    => wp_json_encode( $json ),
			),
		);

		$output = $this->shape_animations->add_animation_classes_to_shape( $block_content, $block );

		$this->assertStringContainsString( 'frbl-custom-svg-animation', $output );
		$this->assertStringContainsString( 'frbl-shape-trigger-hover', $output );
		$this->assertStringContainsString( 'data-shape-animation="frblPulse"', $output );
		$this->assertStringContainsString( 'data-shape-trigger="hover"', $output );
		$this->assertStringContainsString( 'fill="red"', $output );

		ob_start();
		$this->shape_animations->output_queued_styles();
		$css = ob_get_clean();

		$this->assertStringContainsString( 'animation-name: frblPulse;', $css );
		$this->assertStringContainsString( 'animation-duration: 2s;', $css );
		$this->assertStringContainsString( 'animation-delay: 0.5s;', $css );
		$this->assertStringContainsString( 'animation-iteration-count: infinite;', $css );
		$this->assertStringContainsString( ':hover {', $css, 'A hover trigger must add a :hover rule that resumes the animation.' );
	}

	public function test_output_queued_styles_prints_nothing_when_the_queue_is_empty() {
		ob_start();
		$this->shape_animations->output_queued_styles();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}
}
