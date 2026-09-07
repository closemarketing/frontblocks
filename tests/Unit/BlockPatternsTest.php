<?php
/**
 * Tests for FrontBlocks\Frontend\BlockPatterns.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\BlockPatterns;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class BlockPatternsTest extends TestCase {

	/**
	 * @var BlockPatterns
	 */
	private $block_patterns;

	public function set_up() {
		parent::set_up();
		$this->block_patterns = new BlockPatterns();
	}

	/**
	 * The dedicated "frontblocks" pattern category must be registered so the
	 * bundled patterns have somewhere to show up in the inserter.
	 */
	public function test_register_pattern_categories_registers_the_frontblocks_category() {
		$this->block_patterns->register_pattern_categories();

		$this->assertTrue(
			\WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( 'frontblocks' )
		);
	}

	/**
	 * register_patterns() must register the bundled hero carousel pattern
	 * under its expected slug and categories.
	 */
	public function test_register_patterns_registers_the_carousel_hero_pattern() {
		$this->block_patterns->register_patterns();

		$registry = \WP_Block_Patterns_Registry::get_instance();
		$this->assertTrue( $registry->is_registered( 'frontblocks/carousel-hero' ) );

		$pattern = $registry->get_registered( 'frontblocks/carousel-hero' );
		$this->assertSame( array( 'frontblocks', 'featured', 'header' ), $pattern['categories'] );
		$this->assertContains( 'carousel', $pattern['keywords'] );
		$this->assertSame( 1440, $pattern['viewportWidth'] );
	}

	/**
	 * The registered pattern content must actually contain the carousel grid
	 * option markup that makes it render as a carousel once inserted.
	 */
	public function test_carousel_hero_pattern_content_configures_the_carousel_grid_option() {
		$this->block_patterns->register_patterns();

		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'frontblocks/carousel-hero' );

		$this->assertStringContainsString( '"frblGridOption":"carousel"', $pattern['content'] );
		$this->assertStringContainsString( 'wp:cover', $pattern['content'] );
	}

	/**
	 * The private worker method can be invoked directly via reflection and
	 * must register the exact same pattern as the public entry point, with a
	 * human readable title and description for the inserter.
	 */
	public function test_register_carousel_hero_pattern_private_method_registers_pattern_with_title_and_description() {
		$method = new ReflectionMethod( BlockPatterns::class, 'register_carousel_hero_pattern' );
		$method->setAccessible( true );
		$method->invoke( $this->block_patterns );

		$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'frontblocks/carousel-hero' );

		$this->assertSame( 'Hero Carousel', $pattern['title'] );
		$this->assertStringContainsString( 'hero carousel', strtolower( $pattern['description'] ) );
	}

	/**
	 * Calling register_patterns() more than once (e.g. across multiple
	 * requests within the same process) must not throw and must leave the
	 * pattern registered with the same content, since register_block_pattern()
	 * simply overwrites the previous registration.
	 */
	public function test_register_patterns_is_safe_to_call_more_than_once() {
		$this->block_patterns->register_patterns();
		$pattern_content = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'frontblocks/carousel-hero' )['content'];

		$this->block_patterns->register_patterns();
		$pattern_after = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'frontblocks/carousel-hero' );

		$this->assertSame( $pattern_content, $pattern_after['content'] );
	}
}
