<?php
/**
 * Tests for FrontBlocks\Frontend\McpAbilities.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\Maintenance;
use FrontBlocks\Frontend\McpAbilities;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class McpAbilitiesTest extends TestCase {

	/**
	 * @var McpAbilities
	 */
	private $abilities;

	public function set_up() {
		parent::set_up();
		$this->abilities = new McpAbilities();
	}

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		delete_option( 'woocommerce_coming_soon' );
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	public function test_constructor_registers_the_expected_hooks() {
		$this->assertNotFalse( has_action( 'wp_abilities_api_categories_init', array( $this->abilities, 'register_category' ) ) );
		$this->assertNotFalse( has_action( 'wp_abilities_api_init', array( $this->abilities, 'register_abilities' ) ) );
	}

	public function test_check_permission_rejects_a_user_without_manage_options() {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		$this->assertFalse( $this->abilities->check_permission() );
	}

	public function test_check_permission_allows_a_user_with_manage_options() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$this->assertTrue( $this->abilities->check_permission() );
	}

	public function test_execute_maintenance_mode_reads_without_an_enabled_key() {
		Maintenance::set_enabled( true );

		$result = $this->abilities->execute_maintenance_mode( array() );

		$this->assertTrue( $result['enabled'] );
	}

	public function test_execute_maintenance_mode_turns_it_on_and_reports_the_new_state() {
		Maintenance::set_enabled( false );

		$result = $this->abilities->execute_maintenance_mode( array( 'enabled' => true ) );

		$this->assertTrue( $result['enabled'] );
		$this->assertTrue( Maintenance::is_enabled() );
	}

	public function test_execute_maintenance_mode_turns_it_off_and_reports_the_new_state() {
		Maintenance::set_enabled( true );

		$result = $this->abilities->execute_maintenance_mode( array( 'enabled' => false ) );

		$this->assertFalse( $result['enabled'] );
		$this->assertFalse( Maintenance::is_enabled() );
	}

	public function test_execute_maintenance_mode_is_idempotent_when_setting_the_current_state_again() {
		Maintenance::set_enabled( true );

		$first  = $this->abilities->execute_maintenance_mode( array( 'enabled' => true ) );
		$second = $this->abilities->execute_maintenance_mode( array( 'enabled' => true ) );

		$this->assertSame( $first, $second );
		$this->assertTrue( $second['enabled'] );
	}

	public function test_execute_maintenance_mode_reports_woocommerce_coming_soon_independently() {
		Maintenance::set_enabled( false );
		update_option( 'woocommerce_coming_soon', 'yes' );

		$result = $this->abilities->execute_maintenance_mode( array() );

		$this->assertFalse( $result['enabled'], 'FrontBlocks maintenance mode must stay reported as off.' );
		$this->assertTrue( $result['woocommerceComingSoon'], 'WooCommerce Coming soon must be reported independently.' );
	}

	public function test_execute_maintenance_mode_reports_woocommerce_coming_soon_as_false_by_default() {
		delete_option( 'woocommerce_coming_soon' );

		$result = $this->abilities->execute_maintenance_mode( array() );

		$this->assertFalse( $result['woocommerceComingSoon'] );
	}

	public function test_register_category_and_ability_integrate_with_the_abilities_api_when_available() {
		if ( ! function_exists( 'wp_has_ability_category' ) || ! function_exists( 'wp_has_ability' ) ) {
			$this->markTestSkipped( 'The Abilities API is not available in this WordPress version.' );
		}

		// This test's own set_up() just hooked a second McpAbilities instance on top
		// of the one Plugin_Main::load_modules() created at bootstrap. The Abilities
		// API's registries are a lazy, process-wide singleton: wp_has_ability_category()
		// below fires wp_abilities_api_categories_init/wp_abilities_api_init at most
		// once per process, the very first time anything calls into them. If that
		// first-ever firing happens during this test, both hooked instances would try
		// to register the same ability, and the registry's "already registered" guard
		// calls _doing_it_wrong(), which this suite's strict PHPUnit settings turn into
		// a thrown exception. Reducing to exactly one hooked instance first avoids
		// that; it's a harmless no-op if the singleton already fired earlier, since the
		// hooks below then simply never run again.
		remove_all_actions( 'wp_abilities_api_categories_init' );
		remove_all_actions( 'wp_abilities_api_init' );
		add_action( 'wp_abilities_api_categories_init', array( $this->abilities, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this->abilities, 'register_abilities' ) );

		$this->assertTrue( wp_has_ability_category( McpAbilities::CATEGORY ) );
		$this->assertTrue( wp_has_ability( 'frontblocks/maintenance-mode' ) );
	}
}
