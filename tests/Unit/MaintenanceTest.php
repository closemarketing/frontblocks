<?php
/**
 * Tests for FrontBlocks\Frontend\Maintenance.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Maintenance;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class MaintenanceTest extends TestCase {

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	/**
	 * Invoke a private/protected method on a Maintenance instance.
	 *
	 * @param Maintenance $instance Instance under test.
	 * @param string      $method   Method name.
	 * @param array       $args     Method arguments.
	 * @return mixed
	 */
	private function invoke_private( $instance, $method, array $args = array() ) {
		$reflection = new ReflectionMethod( Maintenance::class, $method );
		$reflection->setAccessible( true );

		return $reflection->invokeArgs( $instance, $args );
	}

	public function test_is_enabled_defaults_to_false_without_settings() {
		delete_option( 'frontblocks_settings' );
		$maintenance = new Maintenance();

		$this->assertFalse( $this->invoke_private( $maintenance, 'is_enabled' ) );
	}

	public function test_is_enabled_true_when_option_is_set() {
		update_option( 'frontblocks_settings', array( 'enable_maintenance' => true ) );
		$maintenance = new Maintenance();

		$this->assertTrue( $this->invoke_private( $maintenance, 'is_enabled' ) );
	}

	public function test_is_enabled_casts_truthy_string_to_bool() {
		update_option( 'frontblocks_settings', array( 'enable_maintenance' => '1' ) );
		$maintenance = new Maintenance();

		$this->assertTrue( $this->invoke_private( $maintenance, 'is_enabled' ) );
	}

	public function test_constructor_registers_template_redirect_when_enabled() {
		update_option( 'frontblocks_settings', array( 'enable_maintenance' => true ) );
		$maintenance = new Maintenance();

		$this->assertNotFalse( has_action( 'template_redirect', array( $maintenance, 'maybe_render_maintenance_page' ) ) );
		$this->assertSame( 999, has_action( 'admin_bar_menu', array( $maintenance, 'add_admin_bar_node' ) ) );
	}

	public function test_constructor_does_not_register_hooks_when_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_maintenance' => false ) );
		$maintenance = new Maintenance();

		$this->assertFalse( has_action( 'template_redirect', array( $maintenance, 'maybe_render_maintenance_page' ) ) );
		$this->assertFalse( has_action( 'admin_bar_menu', array( $maintenance, 'add_admin_bar_node' ) ) );
	}

	public function test_should_bypass_is_true_for_administrators() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$maintenance = new Maintenance();

		$this->assertTrue( $this->invoke_private( $maintenance, 'should_bypass' ) );
	}

	public function test_should_bypass_is_false_for_anonymous_visitors() {
		wp_set_current_user( 0 );

		$maintenance = new Maintenance();

		$this->assertFalse( $this->invoke_private( $maintenance, 'should_bypass' ) );
	}

	public function test_admin_bar_node_is_added_only_for_users_who_can_manage_options() {
		update_option( 'frontblocks_settings', array( 'enable_maintenance' => true ) );
		$maintenance = new Maintenance();

		$fake_admin_bar = new class() {
			public $called = false;
			public $args   = null;

			public function add_node( $args ) {
				$this->called = true;
				$this->args   = $args;
			}
		};

		// Anonymous visitor: no node should be added.
		wp_set_current_user( 0 );
		$maintenance->add_admin_bar_node( $fake_admin_bar );
		$this->assertFalse( $fake_admin_bar->called );

		// Administrator: node should be added with the expected id.
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$maintenance->add_admin_bar_node( $fake_admin_bar );

		$this->assertTrue( $fake_admin_bar->called );
		$this->assertSame( 'frbl-maintenance-status', $fake_admin_bar->args['id'] );
		$this->assertStringContainsString( 'frontblocks-settings', $fake_admin_bar->args['href'] );
	}

	public function test_maybe_render_returns_early_without_output_when_bypassed() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$maintenance = new Maintenance();

		ob_start();
		$maintenance->maybe_render_maintenance_page();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	public function test_get_maintenance_css_reads_the_real_stylesheet() {
		$maintenance = new Maintenance();
		$css_path    = FRBL_PLUGIN_PATH . 'assets/maintenance/frontblocks-maintenance.css';
		$expected    = file_exists( $css_path ) ? file_get_contents( $css_path ) : '';

		$this->assertSame( $expected, $this->invoke_private( $maintenance, 'get_maintenance_css' ) );
	}
}
