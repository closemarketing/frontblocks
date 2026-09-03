<?php
/**
 * Tests for FrontBlocks\Frontend\BeforeAfter.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\BeforeAfter;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class BeforeAfterTest extends TestCase {

	/**
	 * @var BeforeAfter
	 */
	private $before_after;

	public function set_up() {
		parent::set_up();
		$this->before_after = new BeforeAfter();
	}

	public function tear_down() {
		wp_dequeue_style( 'frontblocks-before-after-style' );
		wp_dequeue_script( 'frontblocks-before-after-frontend' );
		parent::tear_down();
	}

	/**
	 * Without both a before and an after image URL the block cannot render
	 * anything meaningful, so it must output nothing rather than a broken
	 * comparison widget.
	 */
	public function test_render_returns_empty_string_when_before_url_is_missing() {
		$html = $this->before_after->render_before_after_block(
			array(
				'afterImageUrl' => 'https://example.com/after.jpg',
			)
		);

		$this->assertSame( '', $html );
	}

	public function test_render_returns_empty_string_when_after_url_is_missing() {
		$html = $this->before_after->render_before_after_block(
			array(
				'beforeImageUrl' => 'https://example.com/before.jpg',
			)
		);

		$this->assertSame( '', $html );
	}

	public function test_render_returns_empty_string_when_both_urls_are_missing() {
		$html = $this->before_after->render_before_after_block( array() );

		$this->assertSame( '', $html );
	}

	/**
	 * With both images set, default labels, position and non-fixed height
	 * must produce the expected markup shape.
	 */
	public function test_render_with_defaults_produces_expected_markup() {
		$html = $this->before_after->render_before_after_block(
			array(
				'beforeImageUrl' => 'https://example.com/before.jpg',
				'afterImageUrl'  => 'https://example.com/after.jpg',
			)
		);

		$this->assertStringContainsString( 'class="frbl-before-after"', $html );
		$this->assertStringNotContainsString( 'frbl-before-after--fixed-height', $html );
		$this->assertStringContainsString( 'data-initial-position="50"', $html );
		$this->assertStringContainsString( 'aria-valuenow="50"', $html );
		$this->assertStringContainsString( 'src="https://example.com/before.jpg"', $html );
		$this->assertStringContainsString( 'src="https://example.com/after.jpg"', $html );
		$this->assertStringContainsString( 'Before', $html );
		$this->assertStringContainsString( 'After', $html );
		$this->assertStringNotContainsString( 'style="height:', $html );
	}

	/**
	 * A custom initial position must be cast to an int and reflected both in
	 * the data attribute used by JS and the ARIA slider value.
	 */
	public function test_render_uses_custom_initial_position() {
		$html = $this->before_after->render_before_after_block(
			array(
				'beforeImageUrl'   => 'https://example.com/before.jpg',
				'afterImageUrl'    => 'https://example.com/after.jpg',
				'initialPosition'  => '30',
			)
		);

		$this->assertStringContainsString( 'data-initial-position="30"', $html );
		$this->assertStringContainsString( 'aria-valuenow="30"', $html );
	}

	/**
	 * A fixed height must add the modifier class and an inline height style
	 * using the configured pixel value.
	 */
	public function test_render_with_fixed_height_adds_modifier_class_and_inline_style() {
		$html = $this->before_after->render_before_after_block(
			array(
				'beforeImageUrl' => 'https://example.com/before.jpg',
				'afterImageUrl'  => 'https://example.com/after.jpg',
				'fixedHeight'    => true,
				'blockHeight'    => 550,
			)
		);

		$this->assertStringContainsString( 'frbl-before-after--fixed-height', $html );
		$this->assertStringContainsString( 'style="height:550px"', $html );
	}

	/**
	 * Fixed height with a zero block height must not emit an inline style at
	 * all (the code requires a truthy height).
	 */
	public function test_render_with_fixed_height_and_zero_block_height_has_no_inline_style() {
		$html = $this->before_after->render_before_after_block(
			array(
				'beforeImageUrl' => 'https://example.com/before.jpg',
				'afterImageUrl'  => 'https://example.com/after.jpg',
				'fixedHeight'    => true,
				'blockHeight'    => 0,
			)
		);

		$this->assertStringNotContainsString( 'style="height:', $html );
	}

	/**
	 * A block className supplied by the editor is appended to the wrapper.
	 */
	public function test_render_appends_custom_class_name() {
		$html = $this->before_after->render_before_after_block(
			array(
				'beforeImageUrl' => 'https://example.com/before.jpg',
				'afterImageUrl'  => 'https://example.com/after.jpg',
				'className'      => 'my-custom-class',
			)
		);

		$this->assertStringContainsString( 'my-custom-class', $html );
	}

	/**
	 * Custom labels are passed through wp_kses_post: safe HTML survives, but
	 * script tags are stripped.
	 */
	public function test_render_sanitizes_labels_through_wp_kses_post() {
		$html = $this->before_after->render_before_after_block(
			array(
				'beforeImageUrl' => 'https://example.com/before.jpg',
				'afterImageUrl'  => 'https://example.com/after.jpg',
				'beforeLabel'    => 'Old <script>alert(1)</script><strong>Look</strong>',
			)
		);

		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '<strong>Look</strong>', $html );
	}

	/**
	 * Empty labels must not render an empty label span at all.
	 */
	public function test_render_omits_label_span_when_label_is_empty() {
		$html = $this->before_after->render_before_after_block(
			array(
				'beforeImageUrl' => 'https://example.com/before.jpg',
				'afterImageUrl'  => 'https://example.com/after.jpg',
				'beforeLabel'    => '',
				'afterLabel'     => '',
			)
		);

		$this->assertStringNotContainsString( 'frbl-before-after__label--before', $html );
		$this->assertStringNotContainsString( 'frbl-before-after__label--after', $html );
	}

	/**
	 * The custom block gets registered under its expected name, guarded
	 * against duplicate registration when called more than once.
	 */
	public function test_register_before_after_block_registers_the_block_type_and_is_idempotent() {
		$this->before_after->register_before_after_block();
		$this->before_after->register_before_after_block();

		$this->assertTrue( \WP_Block_Type_Registry::get_instance()->is_registered( 'frontblocks/before-after' ) );
	}
}
