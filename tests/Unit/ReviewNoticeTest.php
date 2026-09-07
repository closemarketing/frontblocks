<?php
/**
 * Tests for FrontBlocks\Admin\ReviewNotice.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\ReviewNotice;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ReviewNoticeTest extends TestCase {

	/**
	 * @var ReviewNotice
	 */
	private $review_notice;

	/**
	 * @var int
	 */
	private $admin_id;

	public function set_up() {
		parent::set_up();
		$this->review_notice = new ReviewNotice();
		$this->admin_id      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->admin_id );
	}

	public function tear_down() {
		delete_option( 'frbl_activation_date' );
		delete_user_meta( $this->admin_id, 'frbl_review_notice_dismissed' );
		unset( $_POST['nonce'] );
		parent::tear_down();
	}

	public function test_constructor_registers_admin_notices_action() {
		$this->assertNotFalse( has_action( 'admin_notices', array( $this->review_notice, 'review_notice' ) ) );
	}

	public function test_constructor_registers_ajax_dismiss_handler() {
		$this->assertNotFalse( has_action( 'wp_ajax_frbl_dismiss_review_notice', array( $this->review_notice, 'dismiss_review_notice' ) ) );
	}

	public function test_review_notice_does_nothing_for_users_without_manage_options() {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		set_current_screen( 'dashboard' );

		ob_start();
		$this->review_notice->review_notice();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_review_notice_sets_activation_date_and_does_not_render_on_first_run() {
		set_current_screen( 'dashboard' );

		$this->assertFalse( get_option( 'frbl_activation_date' ) );

		ob_start();
		$this->review_notice->review_notice();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
		$this->assertNotFalse( get_option( 'frbl_activation_date' ), 'The activation date must be recorded the first time the notice logic runs.' );
	}

	public function test_review_notice_stays_hidden_before_the_configured_number_of_days_has_elapsed() {
		set_current_screen( 'dashboard' );
		update_option( 'frbl_activation_date', time() - ( 2 * DAY_IN_SECONDS ), false );

		ob_start();
		$this->review_notice->review_notice();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_review_notice_renders_after_the_configured_number_of_days_has_elapsed() {
		set_current_screen( 'dashboard' );
		update_option( 'frbl_activation_date', time() - ( ReviewNotice::DAYS_UNTIL_NOTICE + 1 ) * DAY_IN_SECONDS, false );

		ob_start();
		$this->review_notice->review_notice();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'frbl-review-notice', $output );
		$this->assertStringContainsString( 'wordpress.org/support/plugin/frontblocks/reviews', $output );
	}

	public function test_review_notice_stays_hidden_when_already_dismissed_by_the_user() {
		set_current_screen( 'dashboard' );
		update_option( 'frbl_activation_date', time() - ( ReviewNotice::DAYS_UNTIL_NOTICE + 1 ) * DAY_IN_SECONDS, false );
		update_user_meta( $this->admin_id, 'frbl_review_notice_dismissed', true );

		ob_start();
		$this->review_notice->review_notice();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_review_notice_stays_hidden_on_screens_unrelated_to_the_plugin() {
		set_current_screen( 'edit-post' );
		update_option( 'frbl_activation_date', time() - ( ReviewNotice::DAYS_UNTIL_NOTICE + 1 ) * DAY_IN_SECONDS, false );

		ob_start();
		$this->review_notice->review_notice();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_dismiss_review_notice_dies_with_403_when_nonce_is_missing() {
		unset( $_POST['nonce'] );

		try {
			$this->review_notice->dismiss_review_notice();
			$this->fail( 'Expected dismiss_review_notice() to call wp_die() on a missing nonce.' );
		} catch ( \WPDieException $e ) {
			$this->assertStringContainsString( 'Security check failed', $e->getMessage() );
		}

		$this->assertSame( '', get_user_meta( $this->admin_id, 'frbl_review_notice_dismissed', true ) );
	}

	public function test_dismiss_review_notice_dies_with_403_for_users_without_manage_options() {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );
		$_POST['nonce'] = wp_create_nonce( 'frbl_dismiss_review' );

		try {
			$this->review_notice->dismiss_review_notice();
			$this->fail( 'Expected dismiss_review_notice() to call wp_die() for a user without permission.' );
		} catch ( \WPDieException $e ) {
			$this->assertStringContainsString( 'do not have permission', $e->getMessage() );
		}
	}

	public function test_dismiss_review_notice_persists_dismissal_for_authorized_requests() {
		$_POST['nonce'] = wp_create_nonce( 'frbl_dismiss_review' );

		try {
			$this->review_notice->dismiss_review_notice();
		} catch ( \WPDieException $e ) {
			// wp_die() with no message/args still throws in the test environment; that's expected.
		}

		$this->assertTrue( (bool) get_user_meta( $this->admin_id, 'frbl_review_notice_dismissed', true ) );
	}
}
