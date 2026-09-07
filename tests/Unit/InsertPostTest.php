<?php
/**
 * Tests for FrontBlocks\Frontend\InsertPost.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\InsertPost;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class InsertPostTest extends TestCase {

	/**
	 * @var InsertPost
	 */
	private $insert_post;

	public function set_up() {
		parent::set_up();
		$this->insert_post = new InsertPost();
	}

	public function test_constructor_registers_the_expected_hooks() {
		$this->assertNotFalse( has_action( 'init', array( $this->insert_post, 'register_scripts' ) ) );
		$this->assertNotFalse( has_action( 'enqueue_block_editor_assets', array( $this->insert_post, 'enqueue_block_editor_assets' ) ) );
		$this->assertNotFalse( has_action( 'wp_ajax_frbl_search_posts', array( $this->insert_post, 'search_posts_callback' ) ) );
		$this->assertNotFalse( has_action( 'wp_ajax_nopriv_frbl_search_posts', array( $this->insert_post, 'search_posts_callback' ) ) );
		$this->assertNotFalse( has_filter( 'render_block_generateblocks/grid', array( $this->insert_post, 'add_insert_post_attributes_to_grid_block' ) ) );
		$this->assertSame( 5, has_action( 'init', array( $this->insert_post, 'register_custom_attributes' ) ) );
	}

	public function test_render_returns_empty_placeholder_when_no_post_selected() {
		$result = $this->insert_post->render_insert_post_block( array( 'selectedPostId' => 0 ) );

		$this->assertSame( '<div class="frbl-insert-post-empty">No post selected</div>', $result );
	}

	public function test_render_returns_error_when_post_is_not_published() {
		$post_id = self::factory()->post->create(
			array(
				'post_status'  => 'draft',
				'post_title'   => 'Draft Post',
				'post_content' => 'Some content',
			)
		);

		$result = $this->insert_post->render_insert_post_block( array( 'selectedPostId' => $post_id ) );

		$this->assertSame( '<div class="frbl-insert-post-error">Selected post not found or not published</div>', $result );
	}

	public function test_render_returns_empty_string_when_published_post_has_no_content() {
		$post_id = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_title'   => 'Empty Post',
				'post_content' => '',
			)
		);

		$result = $this->insert_post->render_insert_post_block( array( 'selectedPostId' => $post_id ) );

		$this->assertSame( '', $result );
	}

	public function test_render_outputs_title_content_and_custom_class_for_published_post() {
		$post_id = self::factory()->post->create(
			array(
				'post_status'  => 'publish',
				'post_title'   => 'My Inserted Post',
				'post_content' => 'Hello World',
			)
		);

		$result = $this->insert_post->render_insert_post_block(
			array(
				'selectedPostId' => $post_id,
				'className'      => 'extra-class',
			)
		);

		$this->assertStringContainsString( 'class="frbl-insert-post extra-class"', $result );
		$this->assertStringContainsString( '<h2 class="frbl-insert-post-title">My Inserted Post</h2>', $result );
		$this->assertStringContainsString( 'Hello World', $result );
	}

	public function test_grid_block_gets_insert_post_class_and_data_attribute_when_enabled() {
		$content = '<div class="gb-grid-wrapper some-class">grid content</div>';
		$block   = array( 'attrs' => array( 'frblInsertPostEnabled' => true ) );

		$result = $this->insert_post->add_insert_post_attributes_to_grid_block( $content, $block );

		$this->assertSame(
			'<div class="gb-grid-wrapper some-class frbl-insert-post-grid" data-insert-post="true">grid content</div>',
			$result
		);
	}

	public function test_grid_block_untouched_when_insert_post_disabled() {
		$content = '<div class="gb-grid-wrapper some-class">grid content</div>';
		$block   = array( 'attrs' => array( 'frblInsertPostEnabled' => false ) );

		$this->assertSame( $content, $this->insert_post->add_insert_post_attributes_to_grid_block( $content, $block ) );
	}

	public function test_grid_block_untouched_when_attribute_missing() {
		$content = '<div class="gb-grid-wrapper some-class">grid content</div>';

		$this->assertSame( $content, $this->insert_post->add_insert_post_attributes_to_grid_block( $content, array() ) );
	}

	public function test_register_insert_post_attributes_ignores_other_block_types() {
		$args = array( 'foo' => 'bar' );

		$this->assertSame( $args, $this->insert_post->register_insert_post_attributes_for_grid_block( $args, 'core/paragraph' ) );
	}

	public function test_register_insert_post_attributes_adds_boolean_attribute_for_grid_block() {
		$result = $this->insert_post->register_insert_post_attributes_for_grid_block( array(), 'generateblocks/grid' );

		$this->assertSame(
			array(
				'type'    => 'boolean',
				'default' => false,
			),
			$result['attributes']['frblInsertPostEnabled']
		);
	}
}
