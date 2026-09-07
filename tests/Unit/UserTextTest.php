<?php
/**
 * Tests for FrontBlocks\Frontend\UserText.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\UserText;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class UserTextTest extends TestCase {

	/**
	 * @var UserText
	 */
	private $user_text;

	public function set_up() {
		parent::set_up();
		$this->user_text = new UserText();
	}

	public function tear_down() {
		remove_all_filters( 'frbl_user_text_render' );
		unregister_block_type( 'frontblocks/user-text' );
		parent::tear_down();
	}

	public function test_constructor_registers_expected_hooks() {
		$this->assertNotFalse( has_action( 'init', array( $this->user_text, 'register_block' ) ) );
		$this->assertNotFalse( has_action( 'enqueue_block_editor_assets', array( $this->user_text, 'enqueue_editor_assets' ) ) );
	}

	public function test_register_block_registers_block_type_with_expected_default_attributes() {
		$this->user_text->register_block();

		$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( 'frontblocks/user-text' );

		$this->assertNotNull( $block_type );
		$this->assertSame( 'p', $block_type->attributes['htmlTag']['default'] );
		$this->assertSame( '', $block_type->attributes['textPattern']['default'] );
		$this->assertSame( 'string', $block_type->attributes['textPattern']['type'] );
		$this->assertSame( array( $this->user_text, 'render_block' ), $block_type->render_callback );
	}

	public function test_render_block_returns_empty_string_when_no_filter_is_hooked() {
		$output = $this->user_text->render_block( array( 'textPattern' => 'Hello {first_name}' ) );

		$this->assertSame( '', $output );
	}

	public function test_render_block_delegates_rendering_to_the_frbl_user_text_render_filter() {
		add_filter(
			'frbl_user_text_render',
			static function ( $output, $attrs ) {
				return 'Hi ' . $attrs['textPattern'];
			},
			10,
			2
		);

		$output = $this->user_text->render_block( array( 'textPattern' => 'John' ) );

		$this->assertSame( 'Hi John', $output );
	}

	public function test_render_block_passes_full_attributes_array_to_the_filter() {
		$received_attrs = null;

		add_filter(
			'frbl_user_text_render',
			static function ( $output, $attrs ) use ( &$received_attrs ) {
				$received_attrs = $attrs;
				return $output;
			},
			10,
			2
		);

		$attrs = array(
			'textPattern'   => 'Welcome {first_name}',
			'htmlTag'       => 'h2',
			'loggedOutText' => 'Please log in',
		);

		$this->user_text->render_block( $attrs );

		$this->assertSame( $attrs, $received_attrs );
	}
}
