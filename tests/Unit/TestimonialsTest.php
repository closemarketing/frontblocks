<?php
/**
 * Tests for FrontBlocks\Frontend\Testimonials.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Testimonials;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestimonialsTest extends TestCase {

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		unregister_post_type( 'fbrl_testimonial' );
		parent::tear_down();
	}

	public function test_constructor_registers_no_hooks_when_feature_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => false ) );

		$testimonials = new Testimonials();

		$this->assertFalse( has_action( 'init', array( $testimonials, 'register_cpt_testimonials' ) ) );
		$this->assertFalse( shortcode_exists( 'frontblocks_testimonials_carousel' ) );
	}

	public function test_constructor_registers_hooks_when_feature_enabled() {
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => true ) );

		$testimonials = new Testimonials();

		$this->assertNotFalse( has_action( 'init', array( $testimonials, 'register_cpt_testimonials' ) ) );
		$this->assertNotFalse( has_action( 'add_meta_boxes', array( $testimonials, 'add_metabox_stars' ) ) );
		$this->assertNotFalse( has_action( 'save_post', array( $testimonials, 'save_metabox_stars' ) ) );
		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', array( $testimonials, 'enqueue_scripts' ) ) );
		$this->assertTrue( shortcode_exists( 'frontblocks_testimonials_carousel' ) );

		remove_shortcode( 'frontblocks_testimonials_carousel' );
	}

	public function test_is_testimonials_enabled_reflects_the_stored_option() {
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => true ) );
		$enabled_instance = new Testimonials();

		$method = new ReflectionMethod( Testimonials::class, 'is_testimonials_enabled' );
		$method->setAccessible( true );

		$this->assertTrue( $method->invoke( $enabled_instance ) );

		update_option( 'frontblocks_settings', array( 'enable_testimonials' => false ) );
		$this->assertFalse( $method->invoke( $enabled_instance ) );

		delete_option( 'frontblocks_settings' );
		$this->assertFalse( $method->invoke( $enabled_instance ), 'Absent option must default to disabled.' );
	}

	public function test_register_cpt_testimonials_registers_the_expected_post_type() {
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => true ) );
		$testimonials = new Testimonials();

		$testimonials->register_cpt_testimonials();

		$this->assertTrue( post_type_exists( 'fbrl_testimonial' ) );

		$post_type_object = get_post_type_object( 'fbrl_testimonial' );
		$this->assertTrue( $post_type_object->public );
		$this->assertTrue( post_type_supports( 'fbrl_testimonial', 'title' ) );
		$this->assertTrue( post_type_supports( 'fbrl_testimonial', 'editor' ) );
		$this->assertTrue( post_type_supports( 'fbrl_testimonial', 'thumbnail' ) );
	}

	public function test_save_metabox_stars_ignores_request_without_valid_nonce() {
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => true ) );
		$testimonials = new Testimonials();

		$post_id = self::factory()->post->create();

		unset( $_POST['frontblocks_testimonials_nonce'] );
		$_POST['frontblocks_stars'] = 4;

		$testimonials->save_metabox_stars( $post_id );

		$this->assertSame( '', get_post_meta( $post_id, 'frontblocks_stars', true ) );

		unset( $_POST['frontblocks_stars'] );
	}

	public function test_save_metabox_stars_persists_rating_with_valid_nonce_and_permission() {
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => true ) );
		$testimonials = new Testimonials();

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$post_id = self::factory()->post->create( array( 'post_author' => $admin_id ) );

		$_POST['frontblocks_testimonials_nonce'] = wp_create_nonce( basename( FRBL_PLUGIN_PATH . 'includes/Frontend/Testimonials.php' ) );
		$_POST['frontblocks_stars']               = '5';

		$testimonials->save_metabox_stars( $post_id );

		$this->assertSame( 5, (int) get_post_meta( $post_id, 'frontblocks_stars', true ) );

		unset( $_POST['frontblocks_testimonials_nonce'], $_POST['frontblocks_stars'] );
	}

	public function test_save_metabox_stars_ignores_request_without_edit_permission() {
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => true ) );
		$testimonials = new Testimonials();

		$author_id     = self::factory()->user->create( array( 'role' => 'author' ) );
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		$post_id = self::factory()->post->create( array( 'post_author' => $author_id ) );

		$_POST['frontblocks_testimonials_nonce'] = wp_create_nonce( basename( FRBL_PLUGIN_PATH . 'includes/Frontend/Testimonials.php' ) );
		$_POST['frontblocks_stars']               = '3';

		$testimonials->save_metabox_stars( $post_id );

		$this->assertSame( '', get_post_meta( $post_id, 'frontblocks_stars', true ) );

		unset( $_POST['frontblocks_testimonials_nonce'], $_POST['frontblocks_stars'] );
	}

	public function test_testimonials_shortcode_returns_empty_string_when_no_testimonials_exist() {
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => true ) );
		$testimonials = new Testimonials();
		$testimonials->register_cpt_testimonials();

		$output = $testimonials->testimonials_shortcode();

		$this->assertSame( '', $output );
	}

	public function test_testimonials_shortcode_renders_stars_and_content_for_existing_testimonials() {
		update_option( 'frontblocks_settings', array( 'enable_testimonials' => true ) );
		$testimonials = new Testimonials();
		$testimonials->register_cpt_testimonials();

		$post_id = self::factory()->post->create(
			array(
				'post_type'    => 'fbrl_testimonial',
				'post_title'   => 'Jane Doe',
				'post_content' => 'Great plugin!',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $post_id, 'frontblocks_stars', 3 );

		$output = $testimonials->testimonials_shortcode();

		$this->assertStringContainsString( 'Jane Doe', $output );
		$this->assertStringContainsString( 'Great plugin!', $output );
		$this->assertStringContainsString( 'frontblocks-testimonials-carousel', $output );
		$this->assertSame( 3, substr_count( $output, 'star filled' ) );
		$this->assertSame( 2, substr_count( $output, '<span class="star">' ) );
	}
}
