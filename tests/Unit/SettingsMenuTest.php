<?php
/**
 * Tests for the FrontBlocks menu: a menu of its own with a submenu for each
 * settings tab, the URL helpers and the redirect of the old Appearance URL.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\Settings;
use FrontBlocks\Frontend\Maintenance;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class SettingsMenuTest extends TestCase {

	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var array Menu globals saved in set_up() and restored in tear_down().
	 */
	private $saved = array();

	public function set_up() {
		parent::set_up();

		global $menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages, $pagenow;
		$this->saved = array( $menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages, $pagenow );
		$menu        = array();
		$submenu     = array();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->settings = new Settings();
	}

	public function tear_down() {
		global $menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages, $pagenow;
		list( $menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages, $pagenow ) = $this->saved;

		unset( $_GET['page'] );
		$_GET = array();
		remove_all_filters( 'frontblocks_settings_tabs' );
		remove_all_filters( 'wp_redirect' );
		remove_all_actions( 'toplevel_page_frontblocks-settings' );
		delete_option( 'frbl_activation_redirect' );
		delete_option( 'frontblocks_settings' );
		wp_dequeue_style( 'frontblocks-admin-settings' );

		parent::tear_down();
	}

	/**
	 * Slugs of the submenu entries of the FrontBlocks menu, in order.
	 *
	 * @return string[]
	 */
	private function submenu_slugs() {
		global $submenu;

		return isset( $submenu['frontblocks-settings'] ) ? wp_list_pluck( $submenu['frontblocks-settings'], 2 ) : array();
	}

	/**
	 * Run the legacy redirect and return the URL it sends the visitor to.
	 *
	 * @return string|null The location, or null when nothing was redirected.
	 */
	private function legacy_redirect_location() {
		add_filter(
			'wp_redirect',
			function ( $location ) {
				throw new RuntimeException( $location );
			}
		);

		try {
			$this->settings->redirect_legacy_url();
		} catch ( RuntimeException $e ) {
			return $e->getMessage();
		}

		return null;
	}

	/*
	 * ---------------------------------------------------------------
	 * The menu
	 * ---------------------------------------------------------------
	 */

	public function test_the_settings_are_a_menu_of_their_own_and_no_longer_under_appearance() {
		global $menu, $submenu;

		$this->settings->register_menu();

		$entry = null;
		foreach ( $menu as $position => $item ) {
			if ( 'frontblocks-settings' === $item[2] ) {
				$entry = array( $position, $item );
			}
		}

		$this->assertNotNull( $entry, 'The FrontBlocks menu must exist.' );
		$this->assertSame( 'edit_theme_options', $entry[1][1] );
		$this->assertSame( 'FrontBlocks', $entry[1][0] );
		$this->assertStringEndsWith( 'assets/admin/icons/icon-menu.svg', $entry[1][6] );
		$this->assertEquals( 60.5, (float) $entry[0], 'It goes right below Appearance.' );

		$under_appearance = isset( $submenu['themes.php'] ) ? wp_list_pluck( $submenu['themes.php'], 2 ) : array();
		$this->assertNotContains( 'frontblocks-settings', $under_appearance );
	}

	public function test_every_tab_has_a_submenu_entry_in_the_order_of_the_tabs() {
		global $submenu;

		$this->settings->register_menu();

		$this->assertSame(
			array(
				'frontblocks-settings',
				'frontblocks-settings-pro',
				'frontblocks-settings-optional',
				'frontblocks-settings-image-management',
				'frontblocks-settings-maintenance',
				'frontblocks-settings-woocommerce',
				'frontblocks-settings-cookies',
				'frontblocks-settings-google-signin',
				'frontblocks-settings-cpt',
				'frontblocks-settings-license',
			),
			$this->submenu_slugs()
		);

		$labels = wp_list_pluck( $submenu['frontblocks-settings'], 0 );
		$this->assertSame( 'Blocks', $labels[0], 'The first entry replaces the menu title as the way to the first tab.' );
		$this->assertContains( 'Image Management', $labels );
		$this->assertContains( 'Social Login', $labels );
		foreach ( $submenu['frontblocks-settings'] as $item ) {
			$this->assertSame( 'edit_theme_options', $item[1] );
		}
	}

	public function test_each_entry_has_its_own_page_title() {
		global $submenu;

		$this->settings->register_menu();

		$titles = wp_list_pluck( $submenu['frontblocks-settings'], 3 );
		$this->assertContains( 'FrontBlocks: Maintenance', $titles );
		$this->assertCount( count( $titles ), array_unique( $titles ) );
	}

	public function test_a_tab_added_by_a_companion_plugin_gets_a_submenu_entry() {
		add_filter(
			'frontblocks_settings_tabs',
			function ( $tabs ) {
				$tabs[] = array(
					'id'    => 'leads',
					'label' => 'Leads',
				);
				return $tabs;
			}
		);

		$this->settings->register_menu();

		$this->assertContains( 'frontblocks-settings-leads', $this->submenu_slugs() );
	}

	public function test_a_tab_can_stay_out_of_the_menu() {
		add_filter(
			'frontblocks_settings_tabs',
			function ( $tabs ) {
				$tabs[] = array(
					'id'    => 'hidden-one',
					'label' => 'Hidden one',
					'menu'  => false,
				);
				return $tabs;
			}
		);

		$this->settings->register_menu();

		$this->assertNotContains( 'frontblocks-settings-hidden-one', $this->submenu_slugs() );
	}

	public function test_tabs_that_are_not_valid_are_left_out_of_the_menu() {
		add_filter(
			'frontblocks_settings_tabs',
			function ( $tabs ) {
				$tabs[] = array( 'label' => 'No id' );
				$tabs[] = array( 'id' => 'no-label' );
				$tabs[] = array(
					'id'    => 5,
					'label' => 'Number',
				);
				$tabs[] = array(
					'id'    => '',
					'label' => 'Empty id',
				);
				return $tabs;
			}
		);

		$this->settings->register_menu();

		$this->assertCount( 10, $this->submenu_slugs() );
	}

	public function test_a_filter_that_returns_something_else_than_tabs_leaves_the_menu_without_entries() {
		add_filter( 'frontblocks_settings_tabs', '__return_false' );

		$this->settings->register_menu();

		$this->assertSame( array(), $this->submenu_slugs() );
	}

	public function test_the_page_is_rendered_once_per_entry_and_not_twice_for_the_first() {
		global $wp_filter;

		$this->settings->register_menu();

		$this->assertCount( 1, $wp_filter['toplevel_page_frontblocks-settings']->callbacks[10] );
		$this->assertNotFalse( has_action( get_plugin_page_hookname( 'frontblocks-settings-maintenance', 'frontblocks-settings' ), array( $this->settings, 'render_page' ) ) );
	}

	/*
	 * ---------------------------------------------------------------
	 * URLs and screens
	 * ---------------------------------------------------------------
	 */

	public function test_page_slug_of_a_tab() {
		$this->assertSame( 'frontblocks-settings', Settings::get_page_slug() );
		$this->assertSame( 'frontblocks-settings', Settings::get_page_slug( 'blocks' ) );
		$this->assertSame( 'frontblocks-settings-backup', Settings::get_page_slug( 'backup' ) );
		$this->assertSame( 'frontblocks-settings-image-management', Settings::get_page_slug( 'image-management' ) );
		$this->assertSame( 'frontblocks-settings-evilscript', Settings::get_page_slug( 'EVIL"<script>' ), 'The tab id is sanitized.' );
	}

	public function test_tab_of_a_page_slug() {
		$this->assertSame( 'blocks', Settings::get_tab_from_page( 'frontblocks-settings' ) );
		$this->assertSame( 'backup', Settings::get_tab_from_page( 'frontblocks-settings-backup' ) );
		$this->assertSame( 'image-management', Settings::get_tab_from_page( 'frontblocks-settings-image-management' ) );
		$this->assertSame( '', Settings::get_tab_from_page( 'woocommerce' ) );
		$this->assertSame( '', Settings::get_tab_from_page( 'frontblocks-settingsx' ) );
		$this->assertSame( '', Settings::get_tab_from_page( array( 'frontblocks-settings' ) ) );
	}

	public function test_slug_and_tab_are_inverse_of_each_other() {
		foreach ( array( 'blocks', 'pro', 'optional', 'image-management', 'google-signin', 'license', 'leads' ) as $tab ) {
			$this->assertSame( $tab, Settings::get_tab_from_page( Settings::get_page_slug( $tab ) ), $tab );
		}
	}

	public function test_settings_url_points_at_the_menu_page() {
		$this->assertSame( admin_url( 'admin.php?page=frontblocks-settings' ), Settings::get_settings_url() );
		$this->assertSame( admin_url( 'admin.php?page=frontblocks-settings-backup' ), Settings::get_settings_url( 'backup' ) );
		$this->assertStringNotContainsString( 'themes.php', Settings::get_settings_url() );
	}

	public function test_which_screens_are_settings_screens() {
		$this->assertTrue( Settings::is_settings_screen( 'toplevel_page_frontblocks-settings' ) );
		$this->assertTrue( Settings::is_settings_screen( 'frontblocks_page_frontblocks-settings-backup' ) );
		$this->assertTrue( Settings::is_settings_screen( 'frontblocks_page_frontblocks-settings-image-management' ) );
		$this->assertTrue( Settings::is_settings_screen( 'appearance_page_frontblocks-settings' ), 'The screen the page had under Appearance.' );

		foreach ( array( 'dashboard', 'plugins', 'edit-post', 'toplevel_page_woocommerce', '', null, array(), 5 ) as $other ) {
			$this->assertFalse( Settings::is_settings_screen( $other ), wp_json_encode( $other ) );
		}
	}

	public function test_styles_load_on_every_settings_screen_and_nowhere_else() {
		foreach ( array( 'toplevel_page_frontblocks-settings', 'frontblocks_page_frontblocks-settings-backup', 'appearance_page_frontblocks-settings' ) as $hook ) {
			wp_dequeue_style( 'frontblocks-admin-settings' );
			$this->settings->enqueue_admin_styles( $hook );
			$this->assertTrue( wp_style_is( 'frontblocks-admin-settings', 'enqueued' ), $hook );
		}

		wp_dequeue_style( 'frontblocks-admin-settings' );
		foreach ( array( 'edit.php', 'toplevel_page_woocommerce', 'themes.php' ) as $hook ) {
			$this->settings->enqueue_admin_styles( $hook );
			$this->assertFalse( wp_style_is( 'frontblocks-admin-settings', 'enqueued' ), $hook );
		}
	}

	/*
	 * ---------------------------------------------------------------
	 * The tab that opens
	 * ---------------------------------------------------------------
	 */

	private function render_page_for( $page ) {
		if ( null === $page ) {
			unset( $_GET['page'] );
		} else {
			$_GET['page'] = $page;
		}
		$this->settings->register_settings();

		ob_start();
		$this->settings->render_page();

		return ob_get_clean();
	}

	public function test_the_tab_of_the_page_that_was_opened_is_the_default_one() {
		$this->assertStringContainsString( 'data-default-tab="image-management"', $this->render_page_for( 'frontblocks-settings-image-management' ) );
		$this->assertStringContainsString( 'data-default-tab="blocks"', $this->render_page_for( 'frontblocks-settings' ) );
		$this->assertStringContainsString( 'data-default-tab="blocks"', $this->render_page_for( null ) );
		$this->assertStringContainsString( 'data-default-tab=""', $this->render_page_for( 'something-else' ) );
	}

	public function test_the_page_hands_the_script_the_page_of_each_tab() {
		$html = $this->render_page_for( 'frontblocks-settings' );

		$this->assertSame( 1, preg_match( '/data-tab-pages="([^"]*)"/', $html, $found ) );
		$pages = json_decode( html_entity_decode( $found[1], ENT_QUOTES ), true );

		$this->assertSame( 'frontblocks-settings', $pages['blocks'] );
		$this->assertSame( 'frontblocks-settings-maintenance', $pages['maintenance'] );
		$this->assertSame( 'frontblocks-settings-google-signin', $pages['google-signin'] );
	}

	public function test_the_page_still_shows_the_toggle_counts_of_the_tabs() {
		$html = $this->render_page_for( 'frontblocks-settings' );

		$this->assertMatchesRegularExpression( '/data-tab-target="optional"[^>]*>.*?frbl-tab-count">\d+\/\d+</s', $html );
	}

	public function test_the_tab_script_follows_the_menu() {
		$html = $this->render_page_for( 'frontblocks-settings' );
		$this->settings->enqueue_admin_styles( 'toplevel_page_frontblocks-settings' );
		$script = implode( "\n", (array) wp_scripts()->get_data( 'jquery', 'after' ) );

		$this->assertStringContainsString( 'function syncMenu', $script );
		$this->assertStringContainsString( 'function updateUrl', $script );
		$this->assertStringContainsString( "addEventListener('hashchange'", $script );
		$this->assertStringContainsString( 'toplevel_page_frontblocks-settings', $script );
		$this->assertNotSame( '', $html );
	}

	/*
	 * ---------------------------------------------------------------
	 * The old URL
	 * ---------------------------------------------------------------
	 */

	public function test_the_old_url_goes_to_the_new_menu_and_keeps_its_arguments() {
		global $pagenow;
		$pagenow = 'themes.php';
		$_GET    = array(
			'page'          => 'frontblocks-settings',
			'gsi-updated'   => 'true',
			'frbl-transfer' => '1',
		);

		$this->assertSame( admin_url( 'admin.php?page=frontblocks-settings&gsi-updated=true&frbl-transfer=1' ), $this->legacy_redirect_location() );
	}

	public function test_the_old_url_sanitizes_what_it_forwards() {
		global $pagenow;
		$pagenow = 'themes.php';
		$_GET    = array(
			'page'         => 'frontblocks-settings',
			'gsi-error'    => 'Bad <script>alert(1)</script> thing',
			'list'         => array( 'nested' ),
			'Weird Key!'   => 'x',
		);

		$location = $this->legacy_redirect_location();

		$this->assertStringNotContainsString( '<script', $location );
		$this->assertStringNotContainsString( 'list', $location );
		$this->assertStringContainsString( 'gsi-error=Bad%20thing', $location );
		$this->assertStringContainsString( 'weirdkey=x', $location );
	}

	public function test_the_old_url_never_leaves_the_site() {
		global $pagenow;
		$pagenow = 'themes.php';
		$_GET    = array(
			'page'     => 'frontblocks-settings',
			'redirect' => 'https://evil.example/',
		);

		$location = $this->legacy_redirect_location();

		$this->assertStringStartsWith( admin_url( 'admin.php' ), $location );
	}

	public function test_other_pages_are_not_redirected() {
		global $pagenow;

		$pagenow = 'themes.php';
		$_GET    = array( 'page' => 'custom-background' );
		$this->assertNull( $this->legacy_redirect_location() );

		$pagenow = 'themes.php';
		$_GET    = array();
		$this->assertNull( $this->legacy_redirect_location() );

		// The same page slug on another admin file is not the old settings URL.
		$pagenow = 'admin.php';
		$_GET    = array( 'page' => 'frontblocks-settings' );
		$this->assertNull( $this->legacy_redirect_location() );

		$pagenow = 'themes.php';
		$_GET    = array( 'page' => array( 'frontblocks-settings' ) );
		$this->assertNull( $this->legacy_redirect_location() );
	}

	public function test_the_old_url_is_left_to_wordpress_for_users_who_cannot_open_the_settings() {
		global $pagenow;
		$pagenow = 'themes.php';
		$_GET    = array( 'page' => 'frontblocks-settings' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$this->assertNull( $this->legacy_redirect_location() );
	}

	public function test_the_redirect_runs_while_the_menu_is_built() {
		$this->assertSame( 99, has_action( 'admin_menu', array( $this->settings, 'redirect_legacy_url' ) ) );
		$this->assertSame( 10, has_action( 'admin_menu', array( $this->settings, 'register_menu' ) ) );
	}

	/*
	 * ---------------------------------------------------------------
	 * Links in the rest of the plugin
	 * ---------------------------------------------------------------
	 */

	public function test_google_sign_in_returns_to_its_tab() {
		$settings = new \FrontBlocks\GoogleSignIn\Settings();
		$method   = new ReflectionMethod( $settings, 'settings_url' );
		$method->setAccessible( true );

		$this->assertSame( admin_url( 'admin.php?page=frontblocks-settings-google-signin' ), $method->invoke( $settings ) );
	}

	public function test_the_admin_bar_maintenance_link_opens_the_maintenance_tab() {
		$nodes = array();
		$bar   = new class( $nodes ) {
			public $nodes = array();
			public function __construct( &$nodes ) {
				$this->nodes = &$nodes;
			}
			public function add_node( $args ) {
				$this->nodes[] = $args;
			}
		};

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		( new Maintenance() )->add_admin_bar_node( $bar );

		$this->assertSame( admin_url( 'admin.php?page=frontblocks-settings-maintenance' ), $nodes[0]['href'] );
	}

	public function test_the_activation_redirect_opens_the_new_menu() {
		update_option( 'frbl_activation_redirect', true );
		$_GET = array();

		add_filter(
			'wp_redirect',
			function ( $location ) {
				throw new RuntimeException( $location );
			}
		);

		try {
			frbl_plugin_activation_redirect();
			$this->fail( 'The activation redirect must run.' );
		} catch ( RuntimeException $e ) {
			$this->assertSame( admin_url( 'admin.php?page=frontblocks-settings' ), $e->getMessage() );
		}
	}

	public function test_redundant_plugin_notices_link_to_the_new_menu() {
		$method = new ReflectionMethod( \FrontBlocks\Admin\RedundantPlugins::class, 'get_default_entries' );
		$method->setAccessible( true );
		$entries = $method->invoke( null );

		$this->assertSame( admin_url( 'admin.php?page=frontblocks-settings' ), $entries['svg-upload']['doc_url'] );
		$this->assertSame( admin_url( 'admin.php?page=frontblocks-settings-google-signin' ), $entries['login-google']['doc_url'] );
	}

	public function test_the_cookie_notice_migration_notice_shows_on_every_settings_screen() {
		update_option( 'frontblocks_settings', array( 'enable_cookie_notice' => true ) );
		$notice = new \FrontBlocks\Admin\CookieNoticeDeprecationNotice();

		foreach ( array( 'toplevel_page_frontblocks-settings', 'frontblocks_page_frontblocks-settings-cookies' ) as $screen ) {
			set_current_screen( $screen );
			ob_start();
			$notice->render_notice();
			$this->assertNotSame( '', ob_get_clean(), $screen );
		}

		set_current_screen( 'edit-post' );
		ob_start();
		$notice->render_notice();
		$this->assertSame( '', ob_get_clean(), 'It stays out of unrelated screens.' );
	}
}
