<?php
/**
 * Tests for FrontBlocks\Frontend\ColumnLink.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\ColumnLink;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ColumnLinkTest extends TestCase {

	/**
	 * @var ColumnLink
	 */
	private $column_link;

	public function set_up() {
		parent::set_up();
		$this->column_link = new ColumnLink();
	}

	public function tear_down() {
		wp_dequeue_style( 'frontblocks-column-link' );
		wp_dequeue_script( 'frontblocks-column-link-custom' );
		parent::tear_down();
	}

	public function test_constructor_registers_render_block_filter() {
		$this->assertNotFalse(
			has_filter( 'render_block_core/column', array( $this->column_link, 'apply_column_link' ) )
		);
	}

	public function test_constructor_registers_block_type_args_filter() {
		$this->assertNotFalse(
			has_filter( 'register_block_type_args', array( $this->column_link, 'register_native_block_attributes' ) )
		);
	}

	/**
	 * The link attributes must be registered server-side for core/column
	 * only, defaulting to an empty URL and no new-tab flag, and existing
	 * attributes must be preserved.
	 */
	public function test_register_native_block_attributes_adds_attributes_for_core_column() {
		$args = array( 'attributes' => array( 'existingAttr' => array( 'type' => 'string' ) ) );

		$result = $this->column_link->register_native_block_attributes( $args, 'core/column' );

		$this->assertArrayHasKey( 'existingAttr', $result['attributes'] );
		$this->assertArrayHasKey( 'frblColumnLinkUrl', $result['attributes'] );
		$this->assertSame( 'string', $result['attributes']['frblColumnLinkUrl']['type'] );
		$this->assertSame( '', $result['attributes']['frblColumnLinkUrl']['default'] );
		$this->assertArrayHasKey( 'frblColumnLinkNewTab', $result['attributes'] );
		$this->assertSame( 'boolean', $result['attributes']['frblColumnLinkNewTab']['type'] );
		$this->assertFalse( $result['attributes']['frblColumnLinkNewTab']['default'] );
	}

	/**
	 * When core/column has no attributes array at all yet, the method must
	 * initialize one instead of erroring out.
	 */
	public function test_register_native_block_attributes_initializes_missing_attributes_array() {
		$args = array();

		$result = $this->column_link->register_native_block_attributes( $args, 'core/column' );

		$this->assertArrayHasKey( 'attributes', $result );
		$this->assertArrayHasKey( 'frblColumnLinkUrl', $result['attributes'] );
	}

	public function test_register_native_block_attributes_ignores_unrelated_block_types() {
		$args = array( 'attributes' => array() );

		$result = $this->column_link->register_native_block_attributes( $args, 'core/columns' );

		$this->assertArrayNotHasKey( 'frblColumnLinkUrl', $result['attributes'] );
	}

	/**
	 * A column with no configured URL must be left completely untouched and
	 * must not enqueue the frontend assets.
	 */
	public function test_apply_column_link_leaves_content_untouched_when_url_is_absent() {
		$content = '<div class="wp-block-column">A</div>';
		$block   = array( 'attrs' => array() );

		$result = $this->column_link->apply_column_link( $content, $block );

		$this->assertSame( $content, $result );
		$this->assertFalse( wp_style_is( 'frontblocks-column-link', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'frontblocks-column-link-custom', 'enqueued' ) );
	}

	public function test_apply_column_link_leaves_content_untouched_when_url_is_blank() {
		$content = '<div class="wp-block-column">A</div>';
		$block   = array( 'attrs' => array( 'frblColumnLinkUrl' => '   ' ) );

		$result = $this->column_link->apply_column_link( $content, $block );

		$this->assertSame( $content, $result );
	}

	/**
	 * A disallowed URL scheme (e.g. javascript:) must be rejected entirely,
	 * rather than rendering an interactive column with an unsafe target.
	 */
	public function test_apply_column_link_leaves_content_untouched_for_unsafe_url() {
		$content = '<div class="wp-block-column">A</div>';
		$block   = array( 'attrs' => array( 'frblColumnLinkUrl' => 'javascript:alert(1)' ) );

		$result = $this->column_link->apply_column_link( $content, $block );

		$this->assertSame( $content, $result );
		$this->assertFalse( wp_style_is( 'frontblocks-column-link', 'enqueued' ) );
	}

	/**
	 * When a valid URL is set, the wrapper must gain the frbl-column-link
	 * class, the data attribute carrying the escaped URL, and accessibility
	 * attributes, while existing classes and content are preserved and the
	 * frontend assets get enqueued.
	 */
	public function test_apply_column_link_adds_attributes_and_enqueues_assets() {
		$content = '<div class="wp-block-column is-style-default"><p>Hello</p></div>';
		$block   = array( 'attrs' => array( 'frblColumnLinkUrl' => 'https://example.com/landing' ) );

		$result = $this->column_link->apply_column_link( $content, $block );

		$this->assertStringContainsString( 'wp-block-column', $result );
		$this->assertStringContainsString( 'is-style-default', $result );
		$this->assertStringContainsString( 'frbl-column-link', $result );
		$this->assertStringContainsString( 'data-frbl-column-link-url="https://example.com/landing"', $result );
		$this->assertStringContainsString( 'role="link"', $result );
		$this->assertStringContainsString( 'tabindex="0"', $result );
		$this->assertStringContainsString( '<p>Hello</p>', $result );
		$this->assertStringNotContainsString( 'data-frbl-column-link-target', $result );
		$this->assertTrue( wp_style_is( 'frontblocks-column-link', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'frontblocks-column-link-custom', 'enqueued' ) );
	}

	public function test_apply_column_link_sets_new_tab_target_when_enabled() {
		$content = '<div class="wp-block-column">A</div>';
		$block   = array(
			'attrs' => array(
				'frblColumnLinkUrl'    => 'https://example.com/landing',
				'frblColumnLinkNewTab' => true,
			),
		);

		$result = $this->column_link->apply_column_link( $content, $block );

		$this->assertStringContainsString( 'data-frbl-column-link-target="_blank"', $result );
	}

	/**
	 * If the wp-block-column wrapper cannot be found in the markup (e.g. an
	 * unexpected shape), the content must be returned unmodified rather than
	 * corrupted.
	 */
	public function test_apply_column_link_returns_content_unmodified_when_wrapper_not_found() {
		if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$this->markTestSkipped( 'WP_HTML_Tag_Processor is not available on this WP version.' );
		}

		$content = '<div class="some-other-wrapper">no column here</div>';
		$block   = array( 'attrs' => array( 'frblColumnLinkUrl' => 'https://example.com' ) );

		$result = $this->column_link->apply_column_link( $content, $block );

		$this->assertSame( $content, $result );
	}

	/**
	 * The register_assets() hook target must register the editor script and
	 * both frontend handles (without enqueueing them).
	 */
	public function test_register_assets_registers_script_and_style_handles() {
		$this->column_link->register_assets();

		global $wp_scripts, $wp_styles;

		$this->assertArrayHasKey( 'frontblocks-column-link-editor', $wp_scripts->registered );
		$this->assertArrayHasKey( 'frontblocks-column-link-custom', $wp_scripts->registered );
		$this->assertArrayHasKey( 'frontblocks-column-link', $wp_styles->registered );
		$this->assertFalse( wp_style_is( 'frontblocks-column-link', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'frontblocks-column-link-custom', 'enqueued' ) );
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

		$this->column_link->enqueue_editor_styles();

		$after = wp_styles()->get_data( 'wp-block-library', 'after' );

		$this->assertSame( $before, $after );
	}
}
