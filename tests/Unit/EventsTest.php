<?php
/**
 * Tests for FrontBlocks\Frontend\Events.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Events;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class EventsTest extends TestCase {

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		unset( $_POST['frbl_event_meta_nonce'], $_POST['all_day'], $_POST['start_date'], $_POST['start_time'], $_POST['end_date'], $_POST['end_time'], $_POST['cost'], $_POST['web'], $_POST['poster_evento'], $_POST['direccion_evento'] );
		parent::tear_down();
	}

	public function test_is_events_enabled_returns_false_by_default() {
		delete_option( 'frontblocks_settings' );

		$method = new ReflectionMethod( Events::class, 'is_events_enabled' );
		$method->setAccessible( true );
		$events = new Events();

		$this->assertFalse( $method->invoke( $events ) );
	}

	public function test_is_events_enabled_reflects_the_option() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );

		$method = new ReflectionMethod( Events::class, 'is_events_enabled' );
		$method->setAccessible( true );
		$events = new Events();

		$this->assertTrue( $method->invoke( $events ) );
	}

	public function test_get_events_type_defaults_to_cpt() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );

		$method = new ReflectionMethod( Events::class, 'get_events_type' );
		$method->setAccessible( true );
		$events = new Events();

		$this->assertSame( 'cpt', $method->invoke( $events ) );
	}

	public function test_get_events_type_reads_the_option_value() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_events' => true,
				'events_type'   => 'posts',
			)
		);

		$method = new ReflectionMethod( Events::class, 'get_events_type' );
		$method->setAccessible( true );
		$events = new Events();

		$this->assertSame( 'posts', $method->invoke( $events ) );
	}

	public function test_no_hooks_registered_when_events_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_events' => false ) );

		$events = new Events();

		$this->assertFalse( has_action( 'init', array( $events, 'register_cpt_event' ) ) );
		$this->assertFalse( has_action( 'add_meta_boxes', array( $events, 'add_metaboxes' ) ) );
		$this->assertFalse( has_action( 'add_meta_boxes', array( $events, 'add_metaboxes_posts' ) ) );
	}

	public function test_cpt_hooks_registered_when_type_is_cpt() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_events' => true,
				'events_type'   => 'cpt',
			)
		);

		$events = new Events();

		$this->assertNotFalse( has_action( 'init', array( $events, 'register_cpt_event' ) ) );
		$this->assertNotFalse( has_action( 'init', array( $events, 'register_taxonomy_event_category' ) ) );
		$this->assertNotFalse( has_action( 'add_meta_boxes', array( $events, 'add_metaboxes' ) ) );
		$this->assertNotFalse( has_action( 'save_post_event', array( $events, 'save_meta' ) ) );
		$this->assertFalse( has_action( 'add_meta_boxes', array( $events, 'add_metaboxes_posts' ) ) );
	}

	public function test_posts_hooks_registered_when_type_is_posts() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_events' => true,
				'events_type'   => 'posts',
			)
		);

		$events = new Events();

		$this->assertNotFalse( has_action( 'add_meta_boxes', array( $events, 'add_metaboxes_posts' ) ) );
		$this->assertNotFalse( has_action( 'save_post', array( $events, 'save_meta_posts' ) ) );
		$this->assertFalse( has_action( 'add_meta_boxes', array( $events, 'add_metaboxes' ) ) );
	}

	public function test_register_cpt_event_registers_the_event_post_type() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );
		$events = new Events();
		$events->register_cpt_event();

		$this->assertTrue( post_type_exists( 'event' ) );

		unregister_post_type( 'event' );
	}

	public function test_register_taxonomy_registers_event_category() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );
		$events = new Events();
		$events->register_cpt_event();
		$events->register_taxonomy_event_category();

		$this->assertTrue( taxonomy_exists( 'event_category' ) );

		unregister_taxonomy( 'event_category' );
		unregister_post_type( 'event' );
	}

	public function test_save_meta_updates_post_meta_with_valid_nonce() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );
		$events  = new Events();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$_POST['frbl_event_meta_nonce'] = wp_create_nonce( 'frbl_event_save_meta' );
		$_POST['all_day']               = '1';
		$_POST['start_date']            = '2026-01-01';
		$_POST['start_time']            = '10:00';
		$_POST['end_date']              = '2026-01-02';
		$_POST['end_time']              = '18:00';
		$_POST['cost']                  = '19.99';
		$_POST['web']                   = 'https://example.com/event';
		$_POST['poster_evento']         = 'https://example.com/poster.jpg';
		$_POST['direccion_evento']      = 'Main Street 1';

		$events->save_meta( $post_id );

		$this->assertSame( '1', get_post_meta( $post_id, 'all_day', true ) );
		$this->assertSame( '2026-01-01', get_post_meta( $post_id, 'start_date', true ) );
		$this->assertSame( '19.99', get_post_meta( $post_id, 'cost', true ) );
		$this->assertSame( 'https://example.com/event', get_post_meta( $post_id, 'web', true ) );
		$this->assertSame( 'Main Street 1', get_post_meta( $post_id, 'direccion_evento', true ) );
	}

	public function test_save_meta_does_nothing_without_nonce() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );
		$events  = new Events();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$_POST['cost'] = '19.99';

		$events->save_meta( $post_id );

		$this->assertSame( '', get_post_meta( $post_id, 'cost', true ) );
	}

	public function test_save_meta_does_nothing_with_invalid_nonce() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );
		$events  = new Events();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$_POST['frbl_event_meta_nonce'] = 'invalid-nonce';
		$_POST['cost']                  = '19.99';

		$events->save_meta( $post_id );

		$this->assertSame( '', get_post_meta( $post_id, 'cost', true ) );
	}

	public function test_save_meta_posts_only_saves_for_post_post_type() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );
		$events  = new Events();
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );

		$_POST['frbl_event_meta_nonce'] = wp_create_nonce( 'frbl_event_save_meta' );
		$_POST['cost']                  = '5.00';

		$events->save_meta_posts( $page_id );

		$this->assertSame( '', get_post_meta( $page_id, 'cost', true ) );
	}

	public function test_save_meta_posts_saves_for_post_type_post() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );
		$events  = new Events();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$_POST['frbl_event_meta_nonce'] = wp_create_nonce( 'frbl_event_save_meta' );
		$_POST['cost']                  = '5.00';

		$events->save_meta_posts( $post_id );

		$this->assertSame( '5.00', get_post_meta( $post_id, 'cost', true ) );
	}

	public function test_save_meta_all_day_defaults_to_zero_when_unchecked() {
		update_option( 'frontblocks_settings', array( 'enable_events' => true ) );
		$events  = new Events();
		$post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		$_POST['frbl_event_meta_nonce'] = wp_create_nonce( 'frbl_event_save_meta' );
		// all_day intentionally not set to simulate an unchecked checkbox.

		$events->save_meta( $post_id );

		$this->assertSame( '0', get_post_meta( $post_id, 'all_day', true ) );
	}
}
