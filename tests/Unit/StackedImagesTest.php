<?php
/**
 * Tests for FrontBlocks\Frontend\StackedImages.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\StackedImages;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class StackedImagesTest extends TestCase {

	/**
	 * @var StackedImages
	 */
	private $stacked_images;

	public function set_up() {
		parent::set_up();
		$this->stacked_images = new StackedImages();
	}

	public function test_register_stacked_images_block_registers_expected_default_attributes() {
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'frontblocks/stacked-images' );

		$this->assertNotNull( $block_type );
		$this->assertSame( array(), $block_type->attributes['images']['default'] );
		$this->assertSame( 'bottom', $block_type->attributes['direction']['default'] );
		$this->assertSame( 1000, $block_type->attributes['animationDuration']['default'] );
		$this->assertSame( 500, $block_type->attributes['animationDelay']['default'] );
		$this->assertSame( 500, $block_type->attributes['containerHeight']['default'] );
	}

	public function test_render_returns_a_placeholder_when_no_images_are_set() {
		$output = $this->stacked_images->render_stacked_images_block( array( 'images' => array() ) );

		$this->assertStringContainsString( 'frbl-stacked-images-placeholder', $output );
		$this->assertStringNotContainsString( 'frbl-stacked-images-container', $output );
	}

	public function test_render_returns_a_placeholder_when_the_images_attribute_is_missing() {
		$output = $this->stacked_images->render_stacked_images_block( array() );

		$this->assertStringContainsString( 'frbl-stacked-images-placeholder', $output );
	}

	public function test_render_outputs_wrapper_data_attributes_from_block_attributes() {
		$attributes = array(
			'images'            => array(
				array(
					'url' => 'https://example.com/one.jpg',
					'alt' => 'First image',
					'id'  => 10,
				),
				array(
					'url' => 'https://example.com/two.jpg',
					'alt' => 'Second image',
					'id'  => 11,
				),
			),
			'direction'         => 'left',
			'animationDuration' => 750,
			'animationDelay'    => 250,
			'containerHeight'   => 300,
			'className'         => 'my-extra-class',
		);

		$output = $this->stacked_images->render_stacked_images_block( $attributes );

		$this->assertStringContainsString( 'data-direction="left"', $output );
		$this->assertStringContainsString( 'data-duration="750"', $output );
		$this->assertStringContainsString( 'data-delay="250"', $output );
		$this->assertStringContainsString( 'height: 300px;', $output );
		$this->assertStringContainsString( 'my-extra-class', $output );
		$this->assertStringContainsString( 'frbl-stacked-images-wrapper', $output );
	}

	public function test_render_outputs_one_image_element_per_entry_with_incrementing_z_index() {
		$attributes = array(
			'images' => array(
				array(
					'url' => 'https://example.com/one.jpg',
					'alt' => 'First image',
					'id'  => 10,
				),
				array(
					'url' => 'https://example.com/two.jpg',
					'alt' => 'Second image',
					'id'  => 11,
				),
			),
		);

		$output = $this->stacked_images->render_stacked_images_block( $attributes );

		$this->assertStringContainsString( 'src="https://example.com/one.jpg"', $output );
		$this->assertStringContainsString( 'alt="First image"', $output );
		$this->assertStringContainsString( 'src="https://example.com/two.jpg"', $output );
		$this->assertStringContainsString( 'alt="Second image"', $output );
		$this->assertStringContainsString( 'data-index="0"', $output );
		$this->assertStringContainsString( 'data-index="1"', $output );
		$this->assertStringContainsString( 'z-index: 1;', $output );
		$this->assertStringContainsString( 'z-index: 2;', $output );
		$this->assertSame( 2, substr_count( $output, 'data-index="' ), 'Sanity check: exactly two image wrappers should be rendered.' );
	}

	public function test_render_falls_back_to_default_direction_and_dimensions_when_omitted() {
		$attributes = array(
			'images' => array(
				array(
					'url' => 'https://example.com/one.jpg',
					'alt' => '',
					'id'  => 0,
				),
			),
		);

		$output = $this->stacked_images->render_stacked_images_block( $attributes );

		$this->assertStringContainsString( 'data-direction="bottom"', $output );
		$this->assertStringContainsString( 'data-duration="1000"', $output );
		$this->assertStringContainsString( 'data-delay="500"', $output );
		$this->assertStringContainsString( 'height: 500px;', $output );
	}

	public function test_enqueue_frontend_scripts_only_loads_when_the_block_is_present() {
		$post_id = self::factory()->post->create(
			array( 'post_content' => '<!-- wp:frontblocks/stacked-images /-->' )
		);
		go_to( get_permalink( $post_id ) );

		$this->stacked_images->enqueue_frontend_scripts();

		$this->assertTrue( wp_style_is( 'frontblocks-stacked-images-style', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-stacked-images-frontend', 'enqueued' ) );
	}

	public function test_enqueue_frontend_scripts_are_not_loaded_without_the_block() {
		$post_id = self::factory()->post->create( array( 'post_content' => 'No block here.' ) );
		go_to( get_permalink( $post_id ) );

		$this->stacked_images->enqueue_frontend_scripts();

		$this->assertFalse( wp_style_is( 'frontblocks-stacked-images-style', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'frontblocks-stacked-images-frontend', 'enqueued' ) );
	}

	public function tear_down() {
		wp_dequeue_style( 'frontblocks-stacked-images-style' );
		wp_dequeue_script( 'frontblocks-stacked-images-frontend' );
		parent::tear_down();
	}
}
