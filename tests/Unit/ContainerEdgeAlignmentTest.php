<?php
/**
 * Tests for FrontBlocks\Frontend\ContainerEdgeAlignment.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\ContainerEdgeAlignment;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ContainerEdgeAlignmentTest extends TestCase {

	/**
	 * @var ContainerEdgeAlignment
	 */
	private $edge_alignment;

	public function set_up() {
		parent::set_up();
		$this->edge_alignment = new ContainerEdgeAlignment();
	}

	public function tear_down() {
		wp_dequeue_style( 'frbl-edge-alignment' );
		wp_dequeue_script( 'frbl-edge-alignment-js' );
		parent::tear_down();
	}

	public function test_constructor_registers_render_block_filter() {
		$this->assertNotFalse(
			has_filter( 'render_block', array( $this->edge_alignment, 'add_edge_alignment_classes' ) )
		);
	}

	public function test_constructor_registers_block_type_args_filter() {
		$this->assertNotFalse(
			has_filter( 'register_block_type_args', array( $this->edge_alignment, 'register_native_block_attributes' ) )
		);
	}

	public function test_native_block_attributes_added_for_core_group() {
		$args = $this->edge_alignment->register_native_block_attributes( array(), 'core/group' );

		$this->assertArrayHasKey( 'frblEdgeAlignment', $args['attributes'] );
		$this->assertSame( 'string', $args['attributes']['frblEdgeAlignment']['type'] );
		$this->assertSame( '', $args['attributes']['frblEdgeAlignment']['default'] );
	}

	public function test_native_block_attributes_added_for_core_columns() {
		$args = $this->edge_alignment->register_native_block_attributes( array(), 'core/columns' );

		$this->assertArrayHasKey( 'frblEdgeAlignment', $args['attributes'] );
	}

	public function test_native_block_attributes_preserves_existing_attributes() {
		$args = $this->edge_alignment->register_native_block_attributes(
			array( 'attributes' => array( 'existing' => array( 'type' => 'string' ) ) ),
			'core/group'
		);

		$this->assertArrayHasKey( 'existing', $args['attributes'] );
		$this->assertArrayHasKey( 'frblEdgeAlignment', $args['attributes'] );
	}

	public function test_native_block_attributes_untouched_for_unsupported_block() {
		$args = $this->edge_alignment->register_native_block_attributes( array( 'foo' => 'bar' ), 'core/paragraph' );

		$this->assertSame( array( 'foo' => 'bar' ), $args );
	}

	public function test_edge_alignment_class_added_for_left_alignment() {
		$block = array(
			'blockName' => 'core/group',
			'attrs'     => array( 'frblEdgeAlignment' => 'left' ),
		);

		$output = $this->edge_alignment->add_edge_alignment_classes( '<div class="wp-block-group">content</div>', $block );

		$this->assertStringContainsString( 'frbl-edge-left', $output );
		$this->assertStringContainsString( 'wp-block-group', $output );
	}

	public function test_edge_alignment_class_added_for_right_alignment() {
		$block = array(
			'blockName' => 'generateblocks/container',
			'attrs'     => array( 'frblEdgeAlignment' => 'right' ),
		);

		$output = $this->edge_alignment->add_edge_alignment_classes( '<div class="gb-container">content</div>', $block );

		$this->assertStringContainsString( 'frbl-edge-right', $output );
	}

	public function test_edge_alignment_adds_class_attribute_when_missing() {
		$block = array(
			'blockName' => 'core/group',
			'attrs'     => array( 'frblEdgeAlignment' => 'left' ),
		);

		$output = $this->edge_alignment->add_edge_alignment_classes( '<div>content</div>', $block );

		$this->assertStringContainsString( '<div class="frbl-edge-left">', $output );
	}

	public function test_edge_alignment_enqueues_frontend_assets() {
		$block = array(
			'blockName' => 'core/group',
			'attrs'     => array( 'frblEdgeAlignment' => 'left' ),
		);

		$this->edge_alignment->register_frontend_assets();
		$this->edge_alignment->add_edge_alignment_classes( '<div class="wp-block-group">content</div>', $block );

		$this->assertTrue( wp_style_is( 'frbl-edge-alignment', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frbl-edge-alignment-js', 'enqueued' ) );
	}

	public function test_block_content_unchanged_for_unsupported_block_name() {
		$block = array(
			'blockName' => 'core/paragraph',
			'attrs'     => array( 'frblEdgeAlignment' => 'left' ),
		);

		$content = '<p class="foo">content</p>';
		$output  = $this->edge_alignment->add_edge_alignment_classes( $content, $block );

		$this->assertSame( $content, $output );
	}

	public function test_block_content_unchanged_when_attribute_empty() {
		$block = array(
			'blockName' => 'core/group',
			'attrs'     => array(),
		);

		$content = '<div class="wp-block-group">content</div>';
		$output  = $this->edge_alignment->add_edge_alignment_classes( $content, $block );

		$this->assertSame( $content, $output );
	}

	public function test_block_content_unchanged_for_invalid_alignment_value() {
		$block = array(
			'blockName' => 'core/group',
			'attrs'     => array( 'frblEdgeAlignment' => 'center' ),
		);

		$content = '<div class="wp-block-group">content</div>';
		$output  = $this->edge_alignment->add_edge_alignment_classes( $content, $block );

		$this->assertSame( $content, $output );
	}
}
