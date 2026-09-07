<?php
/**
 * Tests for FrontBlocks\Frontend\StickyColumn.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\StickyColumn;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class StickyColumnTest extends TestCase {

	/**
	 * @var StickyColumn
	 */
	private $sticky_column;

	public function set_up() {
		parent::set_up();
		$this->sticky_column = new StickyColumn();
		$this->sticky_column->register_scripts();
		$this->sticky_column->register_custom_attributes();
	}

	public function tear_down() {
		wp_dequeue_style( 'frontblocks-sticky-column' );
		wp_dequeue_script( 'frontblocks-sticky-column-custom' );
		remove_filter(
			'generateblocks_blocks_registered_block',
			array( $this->sticky_column, 'register_sticky_attributes_for_grid_block' ),
			9
		);
		remove_filter(
			'register_block_type_args',
			array( $this->sticky_column, 'register_sticky_attributes_for_columns_block' )
		);
		remove_action(
			'enqueue_block_editor_assets',
			array( $this->sticky_column, 'add_inline_script_for_attributes' )
		);
		parent::tear_down();
	}

	public function test_grid_block_content_is_left_untouched_when_sticky_is_not_enabled() {
		$block_content = '<div class="gb-grid-wrapper some-other-class">content</div>';
		$block         = array( 'attrs' => array( 'frblStickyEnabled' => false ) );

		$output = $this->sticky_column->add_sticky_attributes_to_grid_block( $block_content, $block );

		$this->assertSame( $block_content, $output );
		$this->assertFalse( wp_style_is( 'frontblocks-sticky-column', 'enqueued' ) );
	}

	public function test_grid_block_content_is_left_untouched_when_attrs_are_missing() {
		$block_content = '<div class="gb-grid-wrapper">content</div>';
		$block         = array();

		$this->assertSame(
			$block_content,
			$this->sticky_column->add_sticky_attributes_to_grid_block( $block_content, $block )
		);
	}

	public function test_grid_block_gets_sticky_wrapper_class_and_data_attributes_when_enabled() {
		$block_content = '<div class="gb-grid-wrapper some-other-class">content</div>';
		$block         = array(
			'attrs' => array(
				'frblStickyEnabled'      => true,
				'frblStickyOffset'       => 80,
				'frblStickyColumnIndex'  => 1,
			),
		);

		$output = $this->sticky_column->add_sticky_attributes_to_grid_block( $block_content, $block );

		$this->assertStringContainsString( 'frontblocks-sticky-wrapper', $output );
		$this->assertStringContainsString( 'data-sticky-enabled="true"', $output );
		$this->assertStringContainsString( 'data-sticky-offset="80"', $output );
		$this->assertStringContainsString( 'data-sticky-column-index="1"', $output );
		$this->assertStringContainsString( 'gb-grid-wrapper', $output );

		$this->assertTrue( wp_style_is( 'frontblocks-sticky-column', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-sticky-column-custom', 'enqueued' ) );
	}

	public function test_columns_block_gets_sticky_attributes_using_its_own_wrapper_class() {
		$block_content = '<div class="wp-block-columns">content</div>';
		$block         = array(
			'attrs' => array(
				'frblStickyEnabled'     => true,
				'frblStickyOffset'      => 0,
				'frblStickyColumnIndex' => 0,
			),
		);

		$output = $this->sticky_column->add_sticky_attributes_to_columns_block( $block_content, $block );

		$this->assertStringContainsString( 'frontblocks-sticky-wrapper', $output );
		$this->assertStringContainsString( 'data-sticky-enabled="true"', $output );
	}

	public function test_columns_block_content_is_left_untouched_when_wrapper_class_is_not_found() {
		// The regex only matches when the wrapper class is present in the markup.
		$block_content = '<div class="unrelated-wrapper">content</div>';
		$block         = array( 'attrs' => array( 'frblStickyEnabled' => true ) );

		$this->assertSame(
			$block_content,
			$this->sticky_column->add_sticky_attributes_to_columns_block( $block_content, $block )
		);
	}

	public function test_register_sticky_attributes_for_grid_block_ignores_other_block_types() {
		$args = array( 'attributes' => array() );

		$this->assertSame(
			$args,
			$this->sticky_column->register_sticky_attributes_for_grid_block( $args, 'core/paragraph' )
		);
	}

	public function test_register_sticky_attributes_for_grid_block_adds_the_expected_attributes() {
		$args = $this->sticky_column->register_sticky_attributes_for_grid_block(
			array( 'attributes' => array() ),
			'generateblocks/grid'
		);

		$this->assertSame(
			array(
				'type'    => 'boolean',
				'default' => false,
			),
			$args['attributes']['frblStickyEnabled']
		);
		$this->assertSame(
			array(
				'type'    => 'number',
				'default' => 0,
			),
			$args['attributes']['frblStickyOffset']
		);
		$this->assertSame(
			array(
				'type'    => 'number',
				'default' => 0,
			),
			$args['attributes']['frblStickyColumnIndex']
		);
	}

	public function test_register_sticky_attributes_for_columns_block_ignores_other_block_types() {
		$args = array( 'attributes' => array() );

		$this->assertSame(
			$args,
			$this->sticky_column->register_sticky_attributes_for_columns_block( $args, 'core/paragraph' )
		);
	}

	public function test_register_sticky_attributes_for_columns_block_adds_the_expected_attributes() {
		$args = $this->sticky_column->register_sticky_attributes_for_columns_block(
			array( 'attributes' => array() ),
			'core/columns'
		);

		$this->assertArrayHasKey( 'frblStickyEnabled', $args['attributes'] );
		$this->assertArrayHasKey( 'frblStickyOffset', $args['attributes'] );
		$this->assertArrayHasKey( 'frblStickyColumnIndex', $args['attributes'] );
		$this->assertFalse( $args['attributes']['frblStickyEnabled']['default'] );
	}

	public function test_the_columns_block_attributes_are_actually_registered_via_register_block_type_args_filter() {
		$this->assertNotFalse(
			has_filter(
				'register_block_type_args',
				array( $this->sticky_column, 'register_sticky_attributes_for_columns_block' )
			)
		);
		$this->assertNotFalse(
			has_filter(
				'generateblocks_blocks_registered_block',
				array( $this->sticky_column, 'register_sticky_attributes_for_grid_block' )
			)
		);
	}
}
