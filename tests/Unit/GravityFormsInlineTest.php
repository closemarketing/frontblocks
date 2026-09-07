<?php
/**
 * Tests for FrontBlocks\Frontend\GravityFormsInline.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\GravityFormsInline;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class GravityFormsInlineTest extends TestCase {

	/**
	 * @var GravityFormsInline
	 */
	private $gf_inline;

	public function set_up() {
		parent::set_up();
		$this->gf_inline = new GravityFormsInline();
	}

	public function test_constructor_registers_the_expected_hooks() {
		$this->assertNotFalse( has_action( 'init', array( $this->gf_inline, 'register_scripts' ) ) );
		$this->assertNotFalse( has_action( 'enqueue_block_editor_assets', array( $this->gf_inline, 'enqueue_block_editor_assets' ) ) );
		$this->assertNotFalse( has_filter( 'render_block', array( $this->gf_inline, 'add_inline_attributes_to_gf_block' ) ) );
		$this->assertSame( 5, has_action( 'init', array( $this->gf_inline, 'register_custom_attributes' ) ) );
	}

	public function test_non_gravity_forms_blocks_are_left_untouched() {
		$content = '<p>Not a form</p>';
		$block   = array(
			'blockName' => 'core/paragraph',
			'attrs'     => array( 'frblGfInlineEnabled' => true ),
		);

		$this->assertSame( $content, $this->gf_inline->add_inline_attributes_to_gf_block( $content, $block ) );
	}

	public function test_gravity_forms_block_untouched_when_inline_disabled() {
		$content = '<div class="wp-block-gravityforms-form some-other-class"><form></form></div>';
		$block   = array(
			'blockName' => 'gravityforms/form',
			'attrs'     => array( 'frblGfInlineEnabled' => false ),
		);

		$this->assertSame( $content, $this->gf_inline->add_inline_attributes_to_gf_block( $content, $block ) );
	}

	public function test_enabled_block_gets_inline_class_and_data_attributes_with_default_gap() {
		$content = '<div class="wp-block-gravityforms-form some-other-class"><form></form></div>';
		$block   = array(
			'blockName' => 'gravityforms/form',
			'attrs'     => array( 'frblGfInlineEnabled' => true ),
		);

		$result = $this->gf_inline->add_inline_attributes_to_gf_block( $content, $block );

		$this->assertSame(
			'<div class="wp-block-gravityforms-form frontblocks-gf-inline some-other-class" data-gf-inline-enabled="true" data-gf-inline-gap="10"><form></form></div>',
			$result
		);
	}

	public function test_enabled_block_honors_custom_gap_value() {
		$content = '<div class="wp-block-gravityforms-form some-other-class"><form></form></div>';
		$block   = array(
			'blockName' => 'gravityforms/form',
			'attrs'     => array(
				'frblGfInlineEnabled' => true,
				'frblGfInlineGap'     => 25,
			),
		);

		$result = $this->gf_inline->add_inline_attributes_to_gf_block( $content, $block );

		$this->assertStringContainsString( 'data-gf-inline-gap="25"', $result );
	}

	public function test_enabled_block_without_matching_markup_is_wrapped_instead() {
		$content = '<p>Just a form placeholder</p>';
		$block   = array(
			'blockName' => 'gravityforms/form',
			'attrs'     => array( 'frblGfInlineEnabled' => true ),
		);

		$result = $this->gf_inline->add_inline_attributes_to_gf_block( $content, $block );

		$this->assertSame(
			'<div class="frontblocks-gf-inline-wrapper" data-gf-inline-gap="10"><p>Just a form placeholder</p></div>',
			$result
		);
	}

	public function test_enabled_block_with_empty_content_stays_empty() {
		$block = array(
			'blockName' => 'gravityforms/form',
			'attrs'     => array( 'frblGfInlineEnabled' => true ),
		);

		$this->assertSame( '', $this->gf_inline->add_inline_attributes_to_gf_block( '', $block ) );
	}
}
