<?php
/**
 * Tests for FrontBlocks\Frontend\ColumnsSameHeight.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\ColumnsSameHeight;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ColumnsSameHeightTest extends TestCase {

	/**
	 * @var ColumnsSameHeight
	 */
	private $columns_same_height;

	public function set_up() {
		parent::set_up();
		$this->columns_same_height = new ColumnsSameHeight();
	}

	public function tear_down() {
		wp_dequeue_style( 'frontblocks-columns-same-height' );
		parent::tear_down();
	}

	/**
	 * The frblSameHeight attribute must be registered server-side for
	 * core/columns only, defaulting to false, and existing attributes must
	 * be preserved.
	 */
	public function test_register_native_block_attributes_adds_attribute_for_core_columns() {
		$args = array( 'attributes' => array( 'existingAttr' => array( 'type' => 'string' ) ) );

		$result = $this->columns_same_height->register_native_block_attributes( $args, 'core/columns' );

		$this->assertArrayHasKey( 'existingAttr', $result['attributes'] );
		$this->assertArrayHasKey( 'frblSameHeight', $result['attributes'] );
		$this->assertSame( 'boolean', $result['attributes']['frblSameHeight']['type'] );
		$this->assertFalse( $result['attributes']['frblSameHeight']['default'] );
	}

	/**
	 * When core/columns has no attributes array at all yet, the method must
	 * initialize one instead of erroring out.
	 */
	public function test_register_native_block_attributes_initializes_missing_attributes_array() {
		$args = array();

		$result = $this->columns_same_height->register_native_block_attributes( $args, 'core/columns' );

		$this->assertArrayHasKey( 'attributes', $result );
		$this->assertArrayHasKey( 'frblSameHeight', $result['attributes'] );
	}

	public function test_register_native_block_attributes_ignores_unrelated_block_types() {
		$args = array( 'attributes' => array() );

		$result = $this->columns_same_height->register_native_block_attributes( $args, 'core/column' );

		$this->assertArrayNotHasKey( 'frblSameHeight', $result['attributes'] );
	}

	/**
	 * Blocks without the frblSameHeight attribute set (or set to a falsy
	 * value) must be left completely untouched and must not enqueue the
	 * dedicated stylesheet.
	 */
	public function test_apply_same_height_class_leaves_content_untouched_when_attribute_is_absent() {
		$content = '<div class="wp-block-columns"><div class="wp-block-column">A</div></div>';
		$block   = array( 'attrs' => array() );

		$result = $this->columns_same_height->apply_same_height_class( $content, $block );

		$this->assertSame( $content, $result );
		$this->assertFalse( wp_style_is( 'frontblocks-columns-same-height', 'enqueued' ) );
	}

	public function test_apply_same_height_class_leaves_content_untouched_when_attribute_is_false() {
		$content = '<div class="wp-block-columns"></div>';
		$block   = array( 'attrs' => array( 'frblSameHeight' => false ) );

		$result = $this->columns_same_height->apply_same_height_class( $content, $block );

		$this->assertSame( $content, $result );
	}

	/**
	 * When enabled, the wrapper's class attribute must gain the
	 * frbl-columns-same-height class while preserving any existing classes,
	 * and the dedicated stylesheet must be enqueued.
	 */
	public function test_apply_same_height_class_adds_class_and_enqueues_style_when_enabled() {
		$content = '<div class="wp-block-columns are-vertically-aligned-center"><div class="wp-block-column">A</div></div>';
		$block   = array( 'attrs' => array( 'frblSameHeight' => true ) );

		$result = $this->columns_same_height->apply_same_height_class( $content, $block );

		$this->assertStringContainsString( 'wp-block-columns', $result );
		$this->assertStringContainsString( 'are-vertically-aligned-center', $result );
		$this->assertStringContainsString( 'frbl-columns-same-height', $result );
		$this->assertTrue( wp_style_is( 'frontblocks-columns-same-height', 'enqueued' ) );
	}

	/**
	 * If the wp-block-columns wrapper cannot be found in the markup (e.g. an
	 * unexpected shape), the content must be returned unmodified rather than
	 * corrupted.
	 */
	public function test_apply_same_height_class_returns_content_unmodified_when_wrapper_not_found() {
		if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$this->markTestSkipped( 'WP_HTML_Tag_Processor is not available on this WP version.' );
		}

		$content = '<div class="some-other-wrapper">no columns here</div>';
		$block   = array( 'attrs' => array( 'frblSameHeight' => true ) );

		$result = $this->columns_same_height->apply_same_height_class( $content, $block );

		$this->assertSame( $content, $result );
	}

	/**
	 * The register_assets() hook target must register both the editor
	 * script and the frontend stylesheet handles (without enqueueing them).
	 */
	public function test_register_assets_registers_script_and_style_handles() {
		$this->columns_same_height->register_assets();

		global $wp_scripts, $wp_styles;

		$this->assertArrayHasKey( 'frontblocks-columns-same-height-editor', $wp_scripts->registered );
		$this->assertArrayHasKey( 'frontblocks-columns-same-height', $wp_styles->registered );
		$this->assertFalse( wp_style_is( 'frontblocks-columns-same-height', 'enqueued' ) );
	}

	/**
	 * enqueue_editor_styles() only injects inline CSS into wp-block-library
	 * when running inside wp-admin (the block editor); on the frontend it
	 * must be a no-op.
	 */
	public function test_enqueue_editor_styles_is_a_no_op_outside_admin() {
		$this->assertFalse( is_admin() );

		if ( ! wp_style_is( 'wp-block-library', 'registered' ) ) {
			wp_register_style( 'wp-block-library', false );
		}
		$before = wp_styles()->get_data( 'wp-block-library', 'after' );

		$this->columns_same_height->enqueue_editor_styles();

		$after = wp_styles()->get_data( 'wp-block-library', 'after' );

		$this->assertSame( $before, $after );
	}
}
