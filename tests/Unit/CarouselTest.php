<?php
/**
 * Tests for FrontBlocks\Frontend\Carousel.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Carousel;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class CarouselTest extends TestCase {

	/**
	 * @var Carousel
	 */
	private $carousel;

	public function set_up() {
		parent::set_up();
		$this->carousel = new Carousel();
	}

	public function tear_down() {
		wp_dequeue_style( 'frontblocks-carousel' );
		wp_dequeue_script( 'frontblocks-carousel-custom' );
		parent::tear_down();
	}

	/**
	 * A GenerateBlocks grid without the carousel/slider option must be left
	 * completely untouched and must not enqueue the carousel assets.
	 */
	public function test_grid_block_with_none_option_is_untouched() {
		$content = '<div class="gb-grid-wrapper">content</div>';
		$block   = array( 'attrs' => array( 'frblGridOption' => 'none' ) );

		$result = $this->carousel->add_custom_attributes_to_grid_block( $content, $block );

		$this->assertSame( $content, $result );
		$this->assertFalse( wp_style_is( 'frontblocks-carousel', 'enqueued' ) );
	}

	/**
	 * A grid block set to "carousel" gets the frontblocks-carousel class and
	 * the full set of data-* attributes derived from its attrs, and the
	 * carousel assets get enqueued.
	 */
	public function test_grid_block_with_carousel_option_adds_class_and_data_attributes() {
		$content = '<div class="gb-grid-wrapper some-other-class">content</div>';
		$block   = array(
			'attrs' => array(
				'frblGridOption'   => 'carousel',
				'frblItemsToView'  => '3',
				'frblAutoplay'     => '5',
				'frblGap'          => '10',
				'frblButtons'      => 'dots',
				'frblButtonColor'  => '#fff',
			),
		);

		$result = $this->carousel->add_custom_attributes_to_grid_block( $content, $block );

		$this->assertStringContainsString( 'gb-grid-wrapper some-other-class frontblocks-carousel', $result );
		$this->assertStringContainsString( 'data-type="carousel"', $result );
		$this->assertStringContainsString( 'data-view="3"', $result );
		$this->assertStringContainsString( 'data-autoplay="5000"', $result );
		$this->assertStringContainsString( 'data-gap="10"', $result );
		$this->assertStringContainsString( 'data-buttons="dots"', $result );
		$this->assertStringContainsString( 'data-buttons-color="#fff"', $result );
		$this->assertStringNotContainsString( 'data-rewind=', $result, 'The rewind data attribute is only added for the slider option.' );
		$this->assertTrue( wp_style_is( 'frontblocks-carousel', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-carousel-custom', 'enqueued' ) );
	}

	/**
	 * The "slider" option is a variant of the carousel that additionally adds
	 * a data-rewind attribute reflecting the frblRewind flag.
	 */
	public function test_grid_block_with_slider_option_adds_rewind_data_attribute() {
		$content = '<div class="gb-grid-wrapper">content</div>';
		$block   = array(
			'attrs' => array(
				'frblGridOption' => 'slider',
				'frblRewind'     => false,
			),
		);

		$result = $this->carousel->add_custom_attributes_to_grid_block( $content, $block );

		$this->assertStringContainsString( 'data-type="slider"', $result );
		$this->assertStringContainsString( 'data-rewind=""', $result, 'esc_attr(false) renders as an empty string.' );
	}

	/**
	 * The GenerateBlocks Element block is only processed when its display
	 * style is "grid" — any other display value must pass through unchanged.
	 */
	public function test_element_block_without_grid_display_is_untouched() {
		$content = '<div class="gb-element-abc123">content</div>';
		$block   = array(
			'attrs' => array(
				'styles'         => array( 'display' => 'flex' ),
				'frblGridOption' => 'carousel',
			),
		);

		$result = $this->carousel->add_custom_attributes_to_element_block( $content, $block );

		$this->assertSame( $content, $result );
	}

	/**
	 * A grid-display Element block configured as a carousel gets the same
	 * data attribute treatment as the native Grid block.
	 */
	public function test_element_block_with_grid_display_and_carousel_option_is_modified() {
		$content = '<div class="gb-element-abc123">content</div>';
		$block   = array(
			'attrs' => array(
				'styles'         => array( 'display' => 'grid' ),
				'frblGridOption' => 'carousel',
			),
		);

		$result = $this->carousel->add_custom_attributes_to_element_block( $content, $block );

		$this->assertStringContainsString( 'frontblocks-carousel', $result );
		$this->assertStringContainsString( 'data-type="carousel"', $result );
	}

	/**
	 * core/group is only processed when its layout type is "grid".
	 */
	public function test_core_group_block_without_grid_layout_is_untouched() {
		$content = '<div class="wp-block-group">content</div>';
		$block   = array(
			'attrs' => array(
				'layout'         => array( 'type' => 'constrained' ),
				'frblGridOption' => 'carousel',
			),
		);

		$result = $this->carousel->add_custom_attributes_to_core_group_block( $content, $block );

		$this->assertSame( $content, $result );
	}

	public function test_core_group_block_with_grid_layout_and_carousel_option_is_modified() {
		$content = '<div class="wp-block-group">content</div>';
		$block   = array(
			'attrs' => array(
				'layout'         => array( 'type' => 'grid' ),
				'frblGridOption' => 'carousel',
			),
		);

		$result = $this->carousel->add_custom_attributes_to_core_group_block( $content, $block );

		$this->assertStringContainsString( 'wp-block-group frontblocks-carousel', $result );
		$this->assertStringContainsString( 'data-type="carousel"', $result );
	}

	/**
	 * core/query is only processed when frblGridOption is carousel or slider.
	 */
	public function test_query_block_with_none_option_is_untouched() {
		$content = '<ul class="wp-block-post-template">content</ul>';
		$block   = array( 'attrs' => array( 'frblGridOption' => 'none' ) );

		$result = $this->carousel->add_custom_attributes_to_query_block( $content, $block );

		$this->assertSame( $content, $result );
	}

	public function test_query_block_with_carousel_option_modifies_post_template_ul() {
		$content = '<ul class="wp-block-post-template">content</ul>';
		$block   = array(
			'attrs' => array(
				'frblGridOption'  => 'carousel',
				'frblItemsToView' => '2',
			),
		);

		$result = $this->carousel->add_custom_attributes_to_query_block( $content, $block );

		$this->assertStringContainsString( 'wp-block-post-template frontblocks-carousel', $result );
		$this->assertStringContainsString( 'data-type="carousel"', $result );
		$this->assertStringContainsString( 'data-view="2"', $result );
	}

	/**
	 * The GenerateBlocks Grid/Element attribute registration filter must only
	 * add the frbl* attributes to the two targeted block types, and must
	 * preserve any attributes already present.
	 */
	public function test_register_custom_attributes_for_grid_block_adds_attributes_for_targeted_blocks() {
		$block_args = array( 'attributes' => array( 'existingAttr' => array( 'type' => 'string' ) ) );

		$result = $this->carousel->register_custom_attributes_for_grid_block( $block_args, 'generateblocks/grid' );

		$this->assertArrayHasKey( 'existingAttr', $result['attributes'] );
		$this->assertArrayHasKey( 'frblGridOption', $result['attributes'] );
		$this->assertSame( 'none', $result['attributes']['frblGridOption']['default'] );
		$this->assertSame( 'arrows', $result['attributes']['frblButtons']['default'] );
		$this->assertTrue( $result['attributes']['frblRewind']['default'] );
	}

	public function test_register_custom_attributes_for_grid_block_ignores_unrelated_block_types() {
		$block_args = array( 'attributes' => array() );

		$result = $this->carousel->register_custom_attributes_for_grid_block( $block_args, 'core/paragraph' );

		$this->assertArrayNotHasKey( 'frblGridOption', $result['attributes'] );
	}

	/**
	 * The core/query attribute registration filter only applies to
	 * core/query and initializes the attributes array when it is missing.
	 */
	public function test_register_query_block_attributes_adds_attributes_for_core_query() {
		$args = array();

		$result = $this->carousel->register_query_block_attributes( $args, 'core/query' );

		$this->assertArrayHasKey( 'attributes', $result );
		$this->assertArrayHasKey( 'frblGridOption', $result['attributes'] );
		$this->assertSame( '4', $result['attributes']['frblItemsToView']['default'] );
	}

	public function test_register_query_block_attributes_ignores_unrelated_block_types() {
		$args = array( 'attributes' => array() );

		$result = $this->carousel->register_query_block_attributes( $args, 'core/post-template' );

		$this->assertArrayNotHasKey( 'frblGridOption', $result['attributes'] );
	}
}
