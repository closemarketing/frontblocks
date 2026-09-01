<?php
/**
 * Tests for FrontBlocks\Frontend\Animations.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Animations;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class AnimationsTest extends TestCase {

	public function test_native_registration_adds_animation_attributes() {
		$animations = new Animations();
		$attributes = $animations->register_native_block_attributes(
			array(
				'attributes' => array(
					'customAttribute' => array(
						'type' => 'string',
					),
				),
			),
		'frontblocks/test-animation-attributes'
		);

		$this->assertArrayHasKey( 'customAttribute', $attributes['attributes'] );
		$this->assertSame( 'string', $attributes['attributes']['frblAnimation']['type'] );
		$this->assertSame( 0, $attributes['attributes']['frblAnimationDelay']['default'] );
		$this->assertFalse( $attributes['attributes']['frblAnimationRepeat']['default'] );
	}
}
