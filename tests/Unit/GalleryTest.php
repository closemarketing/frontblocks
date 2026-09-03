<?php
/**
 * Tests for FrontBlocks\Frontend\Gallery.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Gallery;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class GalleryTest extends TestCase {

	/**
	 * @var Gallery
	 */
	private $gallery;

	public function set_up() {
		parent::set_up();
		$this->gallery = new Gallery();
	}

	public function test_constructor_registers_the_expected_hooks() {
		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', array( $this->gallery, 'enqueue_scripts' ) ) );
		$this->assertNotFalse( has_action( 'enqueue_block_editor_assets', array( $this->gallery, 'enqueue_editor_script' ) ) );
		$this->assertNotFalse( has_action( 'enqueue_block_assets', array( $this->gallery, 'enqueue_editor_style' ) ) );
		$this->assertNotFalse( has_filter( 'render_block_core/gallery', array( $this->gallery, 'add_custom_attributes_to_gallery_block' ) ) );
		$this->assertSame( 5, has_action( 'init', array( $this->gallery, 'register_custom_attributes' ) ) );
	}

	public function test_masonry_layout_adds_data_attributes_and_masonry_class() {
		$content = '<figure class="wp-block-gallery columns-3 is-cropped">inner</figure>';
		$block   = array(
			'attrs' => array(
				'frblGalleryLayout'  => 'masonry',
				'frblGutterSize'     => 15,
				'frblEnableLightbox' => true,
				'columns'            => 4,
			),
		);

		$result = $this->gallery->add_custom_attributes_to_gallery_block( $content, $block );

		$this->assertSame(
			'<figure class="wp-block-gallery columns-3 is-cropped frontblocks-gallery-masonry" data-layout="masonry" data-columns="4" data-gutter="15" data-lightbox="true">inner</figure>',
			$result
		);
	}

	public function test_grid_layout_uses_defaults_when_attributes_are_missing() {
		$content = '<figure class="wp-block-gallery columns-3 is-cropped">inner</figure>';
		$block   = array( 'attrs' => array() );

		$result = $this->gallery->add_custom_attributes_to_gallery_block( $content, $block );

		$this->assertSame(
			'<figure class="wp-block-gallery columns-3 is-cropped frontblocks-gallery-grid" data-layout="grid" data-columns="3" data-gutter="20" data-lightbox="false">inner</figure>',
			$result
		);
	}

	public function test_unsupported_layout_leaves_block_content_untouched() {
		$content = '<figure class="wp-block-gallery columns-3 is-cropped">inner</figure>';
		$block   = array( 'attrs' => array( 'frblGalleryLayout' => 'carousel' ) );

		$result = $this->gallery->add_custom_attributes_to_gallery_block( $content, $block );

		$this->assertSame( $content, $result );
	}

	public function test_register_custom_attributes_ignores_other_block_types() {
		$args   = array( 'foo' => 'bar' );
		$result = $this->gallery->register_custom_attributes_for_gallery_block( $args, 'core/paragraph' );

		$this->assertSame( $args, $result );
	}

	public function test_register_custom_attributes_adds_gallery_attributes_for_core_gallery() {
		$result = $this->gallery->register_custom_attributes_for_gallery_block( array(), 'core/gallery' );

		$this->assertSame(
			array(
				'type'    => 'string',
				'default' => 'grid',
			),
			$result['attributes']['frblGalleryLayout']
		);
		$this->assertSame(
			array(
				'type'    => 'number',
				'default' => 20,
			),
			$result['attributes']['frblGutterSize']
		);
		$this->assertSame(
			array(
				'type'    => 'boolean',
				'default' => false,
			),
			$result['attributes']['frblEnableLightbox']
		);
	}
}
