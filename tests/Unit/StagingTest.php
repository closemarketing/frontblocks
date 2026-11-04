<?php
/**
 * Tests for FrontBlocks\Frontend\Staging.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\Settings;
use FrontBlocks\Frontend\Staging;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class StagingTest extends TestCase {

	public function set_up() {
		parent::set_up();
		Staging::flush_cache();
		delete_option( 'frontblocks_settings' );
		delete_option( Staging::LOG_OPTION );
	}

	public function tear_down() {
		Staging::flush_cache();
		delete_option( 'frontblocks_settings' );
		delete_option( Staging::LOG_OPTION );
		wp_set_current_user( 0 );
		unset( $_POST['_wpnonce'] );
		parent::tear_down();
	}

	/**
	 * Switch staging mode on through the stored manual toggle and register its hooks.
	 *
	 * @param array $settings Extra frontblocks_settings values.
	 * @return Staging
	 */
	private function boot_staging( array $settings = array() ) {
		update_option( 'frontblocks_settings', array_merge( array( 'enable_staging' => true ), $settings ) );
		Staging::flush_cache();

		$staging = new Staging();
		$staging->boot();

		return $staging;
	}

	/**
	 * Pretend the site lives on a given host.
	 *
	 * @param string $host Host name.
	 * @return void
	 */
	private function set_site_host( $host ) {
		$url = 'https://' . $host;
		add_filter(
			'pre_option_home',
			static function () use ( $url ) {
				return $url;
			}
		);
		Staging::flush_cache();
	}

	/**
	 * Run a callback and return what it printed.
	 *
	 * @param callable $callback Callback.
	 * @return string
	 */
	private function capture( $callback ) {
		ob_start();
		call_user_func( $callback );

		return (string) ob_get_clean();
	}

	/*
	 * ------------------------------------------------------------------
	 * Detection: pure logic.
	 * ------------------------------------------------------------------
	 */

	private function context( array $overrides = array() ) {
		return array_merge(
			array(
				'constant'    => null,
				'host'        => 'example.com',
				'prefixes'    => array( 'beta.' ),
				'manual'      => false,
				'environment' => 'production',
			),
			$overrides
		);
	}

	public function test_nothing_active_on_a_plain_production_site() {
		$state = Staging::detect( $this->context() );

		$this->assertFalse( $state['active'] );
		$this->assertSame( array(), $state['reasons'] );
		$this->assertFalse( $state['forced_off'] );
	}

	public function test_beta_domain_is_detected_without_any_configuration() {
		$state = Staging::detect( $this->context( array( 'host' => 'beta.example.com' ) ) );

		$this->assertTrue( $state['active'] );
		$this->assertSame( array( Staging::REASON_DOMAIN ), $state['reasons'] );
		$this->assertSame( 'beta.', $state['details']['prefix'] );
	}

	public function test_domain_detection_is_case_insensitive_and_only_matches_the_start() {
		$this->assertTrue( Staging::detect( $this->context( array( 'host' => 'BETA.Example.com' ) ) )['active'] );
		$this->assertFalse( Staging::detect( $this->context( array( 'host' => 'www.beta.example.com' ) ) )['active'] );
		$this->assertFalse( Staging::detect( $this->context( array( 'host' => 'betamax.example.com' ) ) )['active'] );
	}

	public function test_extra_prefixes_are_honoured_and_empty_ones_are_ignored() {
		$context = $this->context(
			array(
				'host'     => 'staging.example.com',
				'prefixes' => array( 'beta.', '', 'staging.', 'dev.' ),
			)
		);
		$this->assertTrue( Staging::detect( $context )['active'] );

		$context['host'] = 'example.com';
		$this->assertFalse( Staging::detect( $context )['active'], 'An empty prefix must not match every host.' );
	}

	public function test_manual_toggle_activates_staging_on_any_domain() {
		$state = Staging::detect( $this->context( array( 'manual' => true ) ) );

		$this->assertTrue( $state['active'] );
		$this->assertSame( array( Staging::REASON_MANUAL ), $state['reasons'] );
	}

	public function test_staging_and_development_environments_activate_staging() {
		foreach ( array( 'staging', 'development' ) as $environment ) {
			$state = Staging::detect( $this->context( array( 'environment' => $environment ) ) );

			$this->assertTrue( $state['active'], $environment );
			$this->assertSame( array( Staging::REASON_ENVIRONMENT ), $state['reasons'], $environment );
		}

		foreach ( array( 'production', 'local' ) as $environment ) {
			$this->assertFalse( Staging::detect( $this->context( array( 'environment' => $environment ) ) )['active'], $environment );
		}
	}

	public function test_constant_true_forces_staging_on() {
		$state = Staging::detect( $this->context( array( 'constant' => true ) ) );

		$this->assertTrue( $state['active'] );
		$this->assertSame( array( Staging::REASON_CONSTANT ), $state['reasons'] );
	}

	public function test_constant_false_overrides_every_other_signal() {
		$state = Staging::detect(
			$this->context(
				array(
					'constant'    => false,
					'host'        => 'beta.example.com',
					'manual'      => true,
					'environment' => 'staging',
				)
			)
		);

		$this->assertFalse( $state['active'] );
		$this->assertTrue( $state['forced_off'] );
		$this->assertSame( array(), $state['reasons'] );
	}

	public function test_every_matching_reason_is_reported() {
		$state = Staging::detect(
			$this->context(
				array(
					'constant'    => true,
					'host'        => 'beta.example.com',
					'manual'      => true,
					'environment' => 'development',
				)
			)
		);

		$this->assertSame(
			array( Staging::REASON_CONSTANT, Staging::REASON_DOMAIN, Staging::REASON_MANUAL, Staging::REASON_ENVIRONMENT ),
			$state['reasons']
		);
	}

	public function test_describe_reasons_explains_why_staging_is_active() {
		$state = Staging::detect( $this->context( array( 'host' => 'beta.example.com', 'manual' => true ) ) );
		$lines = Staging::describe_reasons( $state );

		$this->assertCount( 2, $lines );
		$this->assertStringContainsString( 'beta.example.com', $lines[0] );
		$this->assertStringContainsString( 'manually', $lines[1] );

		$forced = Staging::describe_reasons( Staging::detect( $this->context( array( 'constant' => false ) ) ) );
		$this->assertStringContainsString( 'FRBL_STAGING', $forced[0] );
	}

	/*
	 * ------------------------------------------------------------------
	 * Detection: real sources.
	 * ------------------------------------------------------------------
	 */

	public function test_state_is_inactive_by_default() {
		if ( in_array( wp_get_environment_type(), array( 'staging', 'development' ), true ) ) {
			$this->markTestSkipped( 'The test environment itself is a staging/development environment.' );
		}

		$this->assertFalse( Staging::is_active() );
		$this->assertFalse( frontblocks_is_staging() );
	}

	public function test_beta_site_url_activates_staging_through_the_real_sources() {
		$this->set_site_host( 'beta.example.com' );

		$this->assertTrue( Staging::is_active() );
		$this->assertTrue( frontblocks_is_staging() );
		$this->assertSame( array( Staging::REASON_DOMAIN ), Staging::get_state()['reasons'] );
	}

	public function test_host_prefixes_are_filterable() {
		$this->set_site_host( 'dev.example.com' );
		$this->assertFalse( Staging::is_active() );

		add_filter(
			'frontblocks_staging_host_prefixes',
			static function ( $prefixes ) {
				$prefixes[] = 'dev.';

				return $prefixes;
			}
		);
		Staging::flush_cache();

		$this->assertTrue( Staging::is_active() );
	}

	public function test_manual_toggle_is_read_from_the_settings() {
		update_option( 'frontblocks_settings', array( 'enable_staging' => true ) );
		Staging::flush_cache();

		$this->assertTrue( Staging::is_active() );
	}

	public function test_is_staging_filter_can_force_the_state() {
		add_filter( 'frontblocks_is_staging', '__return_true' );

		$this->assertTrue( Staging::is_active() );
	}

	public function test_state_is_cached_for_the_request() {
		$this->assertFalse( Staging::is_active() );

		update_option( 'frontblocks_settings', array( 'enable_staging' => true ) );
		$this->assertFalse( Staging::is_active(), 'The result must be cached until flushed.' );

		Staging::flush_cache();
		$this->assertTrue( Staging::is_active() );
	}

	public function test_auto_detected_state_is_never_written_to_the_database() {
		$this->set_site_host( 'beta.example.com' );
		$staging = new Staging();
		$staging->boot();

		$this->assertTrue( Staging::is_active() );
		$options = get_option( 'frontblocks_settings', array() );
		$this->assertSame( array(), $options, 'Nothing is stored by detection, only the manual toggle is.' );
	}

	public function test_constant_has_the_last_word_over_the_filter() {
		$state = Staging::detect( $this->context( array( 'host' => 'beta.example.com' ) ) );
		add_filter( 'frontblocks_is_staging', '__return_true' );

		$off = Staging::finalize_state( Staging::detect( $this->context( array( 'constant' => false ) ) ), false );
		$this->assertFalse( $off['active'], 'FRBL_STAGING = false beats a filter that says true.' );

		remove_filter( 'frontblocks_is_staging', '__return_true' );
		add_filter( 'frontblocks_is_staging', '__return_false' );

		$on = Staging::finalize_state( $state, true );
		$this->assertTrue( $on['active'], 'FRBL_STAGING = true beats a filter that says false.' );

		$unset = Staging::finalize_state( $state, null );
		$this->assertFalse( $unset['active'], 'Without the constant the filter decides.' );
	}

	public function test_constant_values_are_interpreted_strictly() {
		$this->assertTrue( Staging::parse_constant_value( true ) );
		$this->assertTrue( Staging::parse_constant_value( 'true' ) );
		$this->assertTrue( Staging::parse_constant_value( 1 ) );
		$this->assertFalse( Staging::parse_constant_value( false ) );
		$this->assertFalse( Staging::parse_constant_value( 'false' ) );
		$this->assertFalse( Staging::parse_constant_value( '0' ) );

		// A typo must never force staging on or off.
		$this->assertNull( Staging::parse_constant_value( 'maybe' ) );
		$this->assertNull( Staging::parse_constant_value( null ) );
		$this->assertNull( Staging::parse_constant_value( array( true ) ) );
	}

	/*
	 * ------------------------------------------------------------------
	 * Nothing changes when staging is off.
	 * ------------------------------------------------------------------
	 */

	public function test_no_hook_is_registered_when_staging_is_off() {
		$staging = new Staging();
		$staging->boot();

		$this->assertFalse( has_filter( 'wp_robots', array( $staging, 'filter_robots' ) ) );
		$this->assertFalse( has_filter( 'pre_wp_mail', array( $staging, 'block_mail' ) ) );
		$this->assertFalse( has_filter( 'pre_http_request', array( $staging, 'filter_http_request' ) ) );
		$this->assertFalse( has_filter( 'robots_txt', array( $staging, 'filter_robots_txt' ) ) );
		$this->assertFalse( has_action( 'admin_bar_menu', array( $staging, 'add_admin_bar_node' ) ) );
		$this->assertNotFalse( has_action( 'init', array( $staging, 'boot' ) ) );
	}

	public function test_measures_cannot_be_turned_off() {
		// Values left in the database by older versions must not switch a measure off.
		$staging = $this->boot_staging( array( 'staging_noindex' => false, 'staging_pause_cron' => false ) );

		$this->assertNotFalse( has_filter( 'wp_robots', array( $staging, 'filter_robots' ) ) );
		$this->assertNotFalse( has_filter( 'pre_wp_mail', array( $staging, 'block_mail' ) ) );
		$this->assertNotFalse( has_filter( 'pre_get_ready_cron_jobs', '__return_empty_array' ) );
	}

	/*
	 * ------------------------------------------------------------------
	 * 1. Prevent indexing.
	 * ------------------------------------------------------------------
	 */

	public function test_robots_meta_is_forced_to_noindex_nofollow() {
		$this->boot_staging();

		$robots = apply_filters( 'wp_robots', array( 'max-image-preview' => 'large', 'follow' => true ) );

		$this->assertTrue( $robots['noindex'] );
		$this->assertTrue( $robots['nofollow'] );
		$this->assertArrayNotHasKey( 'max-image-preview', $robots );
		$this->assertArrayNotHasKey( 'follow', $robots );
	}

	public function test_seo_plugins_robots_meta_is_forced_to_noindex_nofollow() {
		$staging = $this->boot_staging();

		// Rank Math builds an array and removes the core wp_robots filters.
		$this->assertSame(
			array(
				'index'  => 'noindex',
				'follow' => 'nofollow',
			),
			apply_filters( 'rank_math/frontend/robots', array( 'index' => 'index', 'follow' => 'follow', 'max-snippet' => 'max-snippet:-1' ) )
		);
		// Yoast SEO filters a string.
		$this->assertSame( 'noindex, nofollow', apply_filters( 'wpseo_robots', 'index, follow' ) );
		$this->assertNotFalse( has_filter( 'rank_math/frontend/robots', array( $staging, 'filter_seo_robots' ) ) );
	}

	public function test_x_robots_tag_header_is_sent() {
		$this->boot_staging();

		$headers = apply_filters( 'wp_headers', array() );

		$this->assertSame( 'noindex, nofollow', $headers['X-Robots-Tag'] );
	}

	public function test_robots_txt_disallows_everything() {
		$this->boot_staging();

		$robots = apply_filters( 'robots_txt', "User-agent: *\nAllow: /\n", true );

		$this->assertSame( "User-agent: *\nDisallow: /\n", $robots );
	}

	public function test_blog_public_option_is_not_modified() {
		update_option( 'blog_public', 1 );
		$this->boot_staging();

		apply_filters( 'wp_robots', array() );

		$this->assertSame( '1', (string) get_option( 'blog_public' ) );
	}

	public function test_core_sitemaps_are_disabled() {
		$this->boot_staging();

		$this->assertFalse( apply_filters( 'wp_sitemaps_enabled', true ) );
	}

	public function test_sitemap_requests_become_a_404_for_any_plugin() {
		$staging = $this->boot_staging();
		$query   = $GLOBALS['wp_the_query'];
		$query->set( 'sitemap', 'index' );
		$query->set( 'xsl', 'main' );
		$status = null;
		add_filter(
			'status_header',
			static function ( $header, $code ) use ( &$status ) {
				$status = $code;

				return $header;
			},
			10,
			2
		);

		$staging->block_sitemap_requests( $query );

		// WordPress does not send the 404 status when the query is already flagged as one.
		$this->assertSame( 404, $status );

		// Rank Math and Yoast read these variables to decide to serve a sitemap.
		$this->assertSame( '', $query->get( 'sitemap' ) );
		$this->assertSame( '', $query->get( 'xsl' ) );
		$this->assertTrue( $query->is_404() );
		$this->assertNotFalse( has_action( 'parse_query', array( $staging, 'block_sitemap_requests' ) ) );
		$this->assertLessThan( 1, has_action( 'parse_query', array( $staging, 'block_sitemap_requests' ) ), 'It must run before the SEO plugins (priority 1).' );
	}

	public function test_normal_requests_are_not_turned_into_a_404() {
		$staging = $this->boot_staging();
		$query   = $GLOBALS['wp_the_query'];

		$staging->block_sitemap_requests( $query );

		$this->assertFalse( $query->is_404() );
	}

	/*
	 * ------------------------------------------------------------------
	 * 2. Block outgoing email.
	 * ------------------------------------------------------------------
	 */

	public function test_wp_mail_never_sends() {
		$this->boot_staging();
		reset_phpmailer_instance();

		$sent = wp_mail( 'customer@example.org', 'Order received', 'Thanks' );

		$this->assertTrue( $sent );
		$this->assertFalse( tests_retrieve_phpmailer_instance()->get_sent() );
	}

	public function test_blocked_email_is_logged_with_recipient_subject_time_and_caller() {
		$this->boot_staging();
		$before = time();

		wp_mail( array( 'a@example.org', 'b@example.org' ), 'Password reset', 'Body' );

		$log = Staging::get_email_log();
		$this->assertCount( 1, $log );
		$this->assertSame( 'blocked', $log[0]['type'] );
		$this->assertSame( 'a@example.org, b@example.org', $log[0]['to'] );
		$this->assertSame( 'Password reset', $log[0]['subject'] );
		$this->assertGreaterThanOrEqual( $before, $log[0]['time'] );
		$this->assertArrayHasKey( 'caller', $log[0] );
	}

	public function test_email_log_is_capped_and_keeps_the_newest_first() {
		$this->boot_staging();

		for ( $i = 1; $i <= Staging::LOG_LIMIT + 10; $i++ ) {
			wp_mail( 'x@example.org', 'Mail ' . $i, 'Body' );
		}

		$log = Staging::get_email_log();
		$this->assertCount( Staging::LOG_LIMIT, $log );
		$this->assertSame( 'Mail ' . ( Staging::LOG_LIMIT + 10 ), $log[0]['subject'] );
	}

	public function test_email_log_is_not_autoloaded() {
		$this->boot_staging();
		wp_mail( 'x@example.org', 'Hello', 'Body' );

		$autoload = $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( "SELECT autoload FROM {$GLOBALS['wpdb']->options} WHERE option_name = %s", Staging::LOG_OPTION ) );
		$this->assertNotContains( $autoload, array( 'yes', 'on', 'auto-on' ) );
	}

	public function test_an_earlier_short_circuit_is_respected() {
		$staging = $this->boot_staging();

		$this->assertFalse( $staging->block_mail( false, array( 'to' => 'x@example.org' ) ) );
		$this->assertSame( array(), Staging::get_email_log() );
	}

	public function test_emails_can_be_redirected_instead_of_blocked() {
		$this->boot_staging( array( 'staging_redirect_email' => 'qa@example.org' ) );
		reset_phpmailer_instance();

		wp_mail( 'customer@example.org', 'Order received', 'Thanks', array( 'Cc: boss@example.org', 'Bcc: spy@example.org', 'Reply-To: shop@example.org' ) );

		$mailer = tests_retrieve_phpmailer_instance();
		$sent   = $mailer->get_sent();
		$this->assertNotFalse( $sent );
		$this->assertSame( 'qa@example.org', $mailer->get_recipient( 'to' )->address );
		$this->assertFalse( $mailer->get_recipient( 'cc' ) );
		$this->assertFalse( $mailer->get_recipient( 'bcc' ) );
		$this->assertSame( '[Staging] Order received', $sent->subject );
		$this->assertStringContainsString( 'customer@example.org', $sent->body );
		$this->assertSame( 'redirected', Staging::get_email_log()[0]['type'] );
	}

	public function test_redirect_ignores_an_invalid_address_and_blocks_instead() {
		$this->boot_staging( array( 'staging_redirect_email' => 'not-an-email' ) );
		reset_phpmailer_instance();

		$this->assertSame( '', Staging::get_redirect_email() );
		$this->assertTrue( wp_mail( 'customer@example.org', 'Order', 'Body' ) );
		$this->assertFalse( tests_retrieve_phpmailer_instance()->get_sent() );
		$this->assertSame( 'blocked', Staging::get_email_log()[0]['type'] );
	}

	public function test_the_redirect_address_applies_even_if_it_is_saved_after_boot() {
		$this->boot_staging();
		update_option( 'frontblocks_settings', array( 'enable_staging' => true, 'staging_redirect_email' => 'qa@example.org' ) );
		reset_phpmailer_instance();

		wp_mail( 'customer@example.org', 'Order', 'Body' );

		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertNotFalse( $mailer->get_sent() );
		$this->assertSame( 'qa@example.org', $mailer->get_recipient( 'to' )->address );
	}

	public function test_transactional_email_apis_are_blocked() {
		$this->boot_staging();

		$urls = array(
			'https://api.sendgrid.com/v3/mail/send',
			'https://api.mailgun.net/v3/example.org/messages',
			'https://api.eu.mailgun.net/v3/example.org/messages',
			'https://api.brevo.com/v3/smtp/email',
			'https://api.sendinblue.com/v3/smtp/email',
			'https://api.postmarkapp.com/email',
			'https://email.eu-west-1.amazonaws.com/v2/email/outbound-emails',
			'https://api.sparkpost.com/api/v1/transmissions',
			'https://api.mailjet.com/v3.1/send',
		);

		foreach ( $urls as $url ) {
			$response = apply_filters( 'pre_http_request', false, array(), $url );

			$this->assertWPError( $response, $url );
			$this->assertSame( 'frontblocks_staging_blocked', $response->get_error_code(), $url );
		}

		$this->assertCount( count( $urls ), Staging::get_email_log() );
		$this->assertSame( 'api', Staging::get_email_log()[0]['type'] );
	}

	public function test_other_requests_are_left_alone() {
		$this->boot_staging();

		$this->assertFalse( apply_filters( 'pre_http_request', false, array(), 'https://api.wordpress.org/core/version-check/1.7/' ) );
		$this->assertFalse( apply_filters( 'pre_http_request', false, array(), 'https://notsendgrid.com/' ) );
		$this->assertFalse( apply_filters( 'pre_http_request', false, array(), 'https://example.org/api.sendgrid.com' ) );
	}

	public function test_a_response_from_an_earlier_filter_is_not_replaced() {
		$this->boot_staging();
		$cached = array( 'response' => array( 'code' => 200 ) );

		$staging = new Staging();

		$this->assertSame( $cached, $staging->filter_http_request( $cached, array(), 'https://api.sendgrid.com/v3/mail/send' ) );
	}

	public function test_the_curtain_page_can_be_rendered_with_a_login_link() {
		$html = $this->capture(
			static function () {
				\FrontBlocks\Frontend\Maintenance::output_curtain( 'Private staging site', '', 'https://example.org/wp-login.php', 'Log in' );
			}
		);

		$this->assertStringContainsString( 'Private staging site', $html );
		$this->assertStringContainsString( 'frbl-maintenance-link', $html );
		$this->assertStringContainsString( 'https://example.org/wp-login.php', $html );
		$this->assertStringContainsString( 'noindex, nofollow', $html );
	}

	/*
	 * ------------------------------------------------------------------
	 * 4. Visual indicators.
	 * ------------------------------------------------------------------
	 */

	public function test_admin_bar_shows_a_staging_node() {
		$staging = $this->boot_staging();
		require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
		$bar = new WP_Admin_Bar();

		$staging->add_admin_bar_node( $bar );

		$node = $bar->get_node( 'frbl-staging-status' );
		$this->assertNotNull( $node );
		$this->assertStringContainsString( 'STAGING', $node->title );
		$this->assertStringContainsString( 'manually', $node->meta['title'] );
		$this->assertNotFalse( has_action( 'admin_bar_menu', array( $staging, 'add_admin_bar_node' ) ) );
	}

	public function test_document_title_is_prefixed_once() {
		$this->boot_staging();

		$parts = apply_filters( 'document_title_parts', array( 'title' => 'About us', 'site' => 'Shop' ) );
		$this->assertSame( '[STAGING] About us', $parts['title'] );

		$again = apply_filters( 'document_title_parts', $parts );
		$this->assertSame( '[STAGING] About us', $again['title'] );
	}

	public function test_title_built_by_an_seo_plugin_is_prefixed_and_core_titles_are_left_to_core() {
		$this->boot_staging();

		$this->assertSame( '[STAGING] Custom SEO title', apply_filters( 'pre_get_document_title', 'Custom SEO title' ) );
		$this->assertSame( '', apply_filters( 'pre_get_document_title', '' ) );
	}

	public function test_admin_title_is_prefixed() {
		$this->boot_staging();

		$this->assertSame( '[STAGING] Dashboard', apply_filters( 'admin_title', 'Dashboard', 'Dashboard' ) );
	}

	public function test_top_bar_is_only_shown_to_logged_in_users() {
		$staging = $this->boot_staging();

		$this->assertSame( '', $this->capture( array( $staging, 'render_top_bar' ) ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$html = $this->capture( array( $staging, 'render_top_bar' ) );
		$this->assertStringContainsString( 'frbl-staging-bar', $html );
		$this->assertStringContainsString( 'changes here are not live', $html );
	}

	public function test_admin_notice_shows_on_the_dashboard_and_the_settings_screen_for_administrators() {
		$staging = $this->boot_staging();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		foreach ( array( 'dashboard', 'appearance_page_frontblocks-settings' ) as $screen ) {
			set_current_screen( $screen );
			$html = $this->capture( array( $staging, 'render_admin_notice' ) );

			$this->assertStringContainsString( 'staging mode is active', $html, $screen );
		}

		set_current_screen( 'edit-post' );
		$this->assertSame( '', $this->capture( array( $staging, 'render_admin_notice' ) ) );
	}

	public function test_admin_notice_is_hidden_from_users_who_cannot_manage_options() {
		$staging = $this->boot_staging();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		set_current_screen( 'dashboard' );

		$this->assertSame( '', $this->capture( array( $staging, 'render_admin_notice' ) ) );
	}

	/*
	 * ------------------------------------------------------------------
	 * 5. WooCommerce safety.
	 * ------------------------------------------------------------------
	 */

	public function test_woocommerce_measures_are_only_registered_when_woocommerce_is_active() {
		if ( class_exists( 'WooCommerce' ) ) {
			$this->markTestSkipped( 'WooCommerce is active in this environment.' );
		}

		$staging = $this->boot_staging();

		$this->assertFalse( has_filter( 'woocommerce_webhook_should_deliver', '__return_false' ) );
		$this->assertFalse( has_filter( 'woocommerce_subscriptions_is_duplicate_site', '__return_true' ) );
		$this->assertFalse( has_action( 'woocommerce_before_cart', array( $staging, 'print_woocommerce_notice' ) ) );
	}

	public function test_woocommerce_measures_register_the_notice_webhook_and_subscription_filters() {
		$staging = new Staging();
		$method  = new ReflectionMethod( Staging::class, 'register_woocommerce_safety' );
		$method->setAccessible( true );
		$method->invoke( $staging );

		$this->assertNotFalse( has_action( 'woocommerce_before_cart', array( $staging, 'print_woocommerce_notice' ) ) );
		$this->assertNotFalse( has_action( 'woocommerce_before_checkout_form', array( $staging, 'print_woocommerce_notice' ) ) );
		$this->assertFalse( apply_filters( 'woocommerce_webhook_should_deliver', true, null, array() ) );
		$this->assertTrue( apply_filters( 'woocommerce_subscriptions_is_duplicate_site', false ) );
	}

	public function test_the_test_site_notice_is_added_to_the_cart_and_checkout_blocks_only() {
		$staging = new Staging();

		$cart     = $staging->prepend_notice_to_blocks( '<div>cart</div>', array( 'blockName' => 'woocommerce/cart' ) );
		$checkout = $staging->prepend_notice_to_blocks( '<div>checkout</div>', array( 'blockName' => 'woocommerce/checkout' ) );
		$other    = $staging->prepend_notice_to_blocks( '<p>text</p>', array( 'blockName' => 'core/paragraph' ) );

		$this->assertStringContainsString( 'frbl-staging-notice', $cart );
		$this->assertStringEndsWith( '<div>cart</div>', $cart );
		$this->assertStringContainsString( 'frbl-staging-notice', $checkout );
		$this->assertSame( '<p>text</p>', $other );
		$this->assertStringContainsString( 'test site', $this->capture( array( $staging, 'print_woocommerce_notice' ) ) );
	}

	/*
	 * ------------------------------------------------------------------
	 * 6. Background tasks and external connections.
	 * ------------------------------------------------------------------
	 */

	public function test_wp_cron_events_do_not_run() {
		wp_schedule_single_event( time() - 60, 'frbl_staging_test_event' );
		$this->assertNotEmpty( wp_get_ready_cron_jobs(), 'The event must be due before staging is switched on.' );

		$this->boot_staging();

		$this->assertSame( array(), wp_get_ready_cron_jobs() );
		wp_clear_scheduled_hook( 'frbl_staging_test_event' );
	}

	public function test_action_scheduler_is_paused() {
		$this->boot_staging();

		$this->assertSame( 0, apply_filters( 'action_scheduler_queue_runner_concurrent_batches', 1 ) );
	}

	public function test_requests_to_configured_hosts_and_their_subdomains_are_blocked() {
		$this->boot_staging( array( 'staging_blocked_hosts' => "hooks.zapier.com\nerp.client.es" ) );

		$this->assertWPError( apply_filters( 'pre_http_request', false, array(), 'https://hooks.zapier.com/hooks/catch/123/abc' ) );
		$this->assertWPError( apply_filters( 'pre_http_request', false, array(), 'https://api.erp.client.es/v1/orders' ) );
		$this->assertFalse( apply_filters( 'pre_http_request', false, array(), 'https://example.org/' ) );
		$this->assertFalse( apply_filters( 'pre_http_request', false, array(), 'https://notzapier.com/' ) );
	}

	public function test_add_ons_can_add_blocked_hosts_through_a_filter() {
		add_filter(
			'frontblocks_staging_blocked_hosts',
			static function ( $hosts ) {
				$hosts[] = 'crm.example-connector.com';

				return $hosts;
			}
		);
		$this->boot_staging();

		$this->assertWPError( apply_filters( 'pre_http_request', false, array(), 'https://crm.example-connector.com/api/contacts' ) );
	}

	public function test_the_site_is_never_blocked_from_itself() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		$this->boot_staging( array( 'staging_blocked_hosts' => $host ) );

		$this->assertFalse( apply_filters( 'pre_http_request', false, array(), home_url( '/wp-cron.php' ) ) );
		$this->assertFalse( apply_filters( 'pre_http_request', false, array(), rest_url() ) );
	}

	public function test_blocked_hosts_text_is_parsed_defensively() {
		$hosts = Staging::parse_hosts( "  Hooks.Zapier.com \nhttps://hook.eu1.make.com/abc?x=1\n*.crm.example.org\n\nnot a host\n<script>\n127.0.0.1\nhooks.zapier.com,erp.client.es" );

		$this->assertSame( array( 'hooks.zapier.com', 'hook.eu1.make.com', 'crm.example.org', '127.0.0.1', 'erp.client.es' ), $hosts );
	}

	public function test_host_matching_covers_subdomains_and_regular_expressions() {
		$this->assertTrue( Staging::host_matches( 'api.example.org', array( 'example.org' ) ) );
		$this->assertTrue( Staging::host_matches( 'example.org', array( 'example.org' ) ) );
		$this->assertFalse( Staging::host_matches( 'badexample.org', array( 'example.org' ) ) );
		$this->assertTrue( Staging::host_matches( 'email.us-east-1.amazonaws.com', array( '#^email\.[a-z0-9-]+\.amazonaws\.com$#' ) ) );
		$this->assertFalse( Staging::host_matches( 's3.us-east-1.amazonaws.com', array( '#^email\.[a-z0-9-]+\.amazonaws\.com$#' ) ) );
		$this->assertFalse( Staging::host_matches( '', array( 'example.org' ) ) );
	}

	/*
	 * ------------------------------------------------------------------
	 * 7. Analytics.
	 * ------------------------------------------------------------------
	 */

	public function test_tracking_scripts_are_removed_from_the_page() {
		$html = '<!DOCTYPE html><html><head>'
			. '<script async src="https://www.googletagmanager.com/gtag/js?id=G-ABC123"></script>'
			. "<script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date());</script>"
			. "<script>(function(w,d,s,l,i){w[l]=w[l]||[];var j=d.createElement(s);j.src='https://www.googletagmanager.com/gtm.js?id='+i;})(window,document,'script','dataLayer','GTM-ABCD12');</script>"
			. "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;})(window,document,'script','//www.google-analytics.com/analytics.js','ga');</script>"
			. '<script src="/wp-content/themes/theme/app.js"></script>'
			. '<script type="application/ld+json">{"@type":"Organization"}</script>'
			. '</head><body>'
			. '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-ABCD12" height="0" width="0"></iframe></noscript>'
			. '<p>Hello</p></body></html>';

		$clean = Staging::strip_analytics( $html );

		$this->assertStringNotContainsString( 'googletagmanager.com', $clean );
		$this->assertStringNotContainsString( 'google-analytics.com', $clean );
		$this->assertStringNotContainsString( 'gtag(', $clean );
		$this->assertStringContainsString( '/wp-content/themes/theme/app.js', $clean );
		$this->assertStringContainsString( '{"@type":"Organization"}', $clean );
		$this->assertStringContainsString( '<p>Hello</p>', $clean );
		$this->assertStringContainsString( Staging::ANALYTICS_REMOVED_MARKER, $clean );
	}

	public function test_frontconsent_scripts_are_kept_even_though_they_mention_tracking_urls() {
		$consent = "<script id=\"frontconsent-consent-bootstrap-js-after\">var u='https://www.googletagmanager.com/gtm.js?id='; function gtag(){}</script>";
		$html    = '<html><head>' . $consent . '<script id="other-js-after">gtag(\'js\', new Date());</script></head><body></body></html>';

		$clean = Staging::strip_analytics( $html );

		$this->assertStringContainsString( $consent, $clean );
		$this->assertStringNotContainsString( 'other-js-after', $clean );
	}

	public function test_protected_script_ids_are_filterable() {
		add_filter(
			'frontblocks_staging_analytics_protected_script_ids',
			static function ( $prefixes ) {
				$prefixes[] = 'my-consent-';

				return $prefixes;
			}
		);
		$kept = '<script id="my-consent-js">gtag("consent","default",{});</script>';

		$this->assertStringContainsString( $kept, Staging::strip_analytics( '<html><body>' . $kept . '</body></html>' ) );
	}

	public function test_pages_without_tracking_and_non_html_responses_are_untouched() {
		$page = '<html><head><script src="/app.js"></script></head><body>Hi</body></html>';
		$json = '{"url":"https://www.googletagmanager.com/gtm.js"}';

		$this->assertSame( $page, Staging::strip_analytics( $page ) );
		$this->assertSame( $json, Staging::strip_analytics( $json ) );
		$this->assertSame( '', Staging::strip_analytics( '' ) );
	}

	public function test_enqueued_tracking_scripts_are_dropped() {
		$staging = $this->boot_staging();
		$tag     = '<script src="https://www.googletagmanager.com/gtag/js?id=G-ABC123"></script>';

		$this->assertSame( '', apply_filters( 'script_loader_tag', $tag, 'gtag', 'https://www.googletagmanager.com/gtag/js?id=G-ABC123' ) );
		$other = '<script src="https://example.org/app.js"></script>';
		$this->assertSame( $other, apply_filters( 'script_loader_tag', $other, 'app', 'https://example.org/app.js' ) );
		$this->assertNotFalse( has_action( 'template_redirect', array( $staging, 'start_analytics_buffer' ) ) );
	}

	public function test_frontconsent_does_not_load_tracking_on_staging() {
		$this->boot_staging();

		$this->assertFalse( apply_filters( 'frcn_cookie_notice_has_tracking_consent', true ) );
	}

	/*
	 * ------------------------------------------------------------------
	 * Settings.
	 * ------------------------------------------------------------------
	 */

	public function test_settings_only_save_the_manual_toggle_for_staging() {
		$_POST['_wpnonce'] = wp_create_nonce( 'frontblocks_settings-options' );
		$settings          = new Settings();

		$saved = $settings->sanitize_settings( array( 'enable_staging' => '1' ) );

		$this->assertTrue( $saved['enable_staging'] );
		// Measures are always on: they are not settings anymore.
		$this->assertArrayNotHasKey( 'staging_noindex', $saved );
		$this->assertArrayNotHasKey( 'staging_restrict_access', $saved );
	}

	public function test_unchecking_the_manual_toggle_turns_it_off() {
		$_POST['_wpnonce'] = wp_create_nonce( 'frontblocks_settings-options' );
		update_option( 'frontblocks_settings', array( 'enable_staging' => true ) );
		$settings = new Settings();

		$saved = $settings->sanitize_settings( array( 'staging_noindex' => '1' ) );

		$this->assertFalse( $saved['enable_staging'] );
	}

	public function test_settings_sanitize_the_redirect_address_and_the_blocked_hosts() {
		$_POST['_wpnonce'] = wp_create_nonce( 'frontblocks_settings-options' );
		$settings          = new Settings();

		$saved = $settings->sanitize_settings(
			array(
				'staging_redirect_email' => ' QA@Example.org ',
				'staging_blocked_hosts'  => "hooks.zapier.com\n<script>alert(1)</script>\nhttps://erp.client.es/path",
			)
		);

		$this->assertSame( 'QA@Example.org', $saved['staging_redirect_email'] );
		$this->assertSame( "hooks.zapier.com\nerp.client.es", $saved['staging_blocked_hosts'] );

		$invalid = $settings->sanitize_settings( array( 'staging_redirect_email' => 'nope' ) );
		$this->assertSame( '', $invalid['staging_redirect_email'] );
	}

	public function test_settings_screen_explains_why_staging_is_active_and_lists_the_log() {
		$this->set_site_host( 'beta.example.com' );
		$this->boot_staging();
		wp_mail( 'x@example.org', 'Welcome <b>aboard</b>', 'Body' );
		$settings = new Settings();

		$html = $this->capture( array( $settings, 'field_enable_staging' ) );

		$this->assertStringContainsString( 'Staging mode is active on this site.', $html );
		$this->assertStringContainsString( 'starts with', $html );
		$this->assertStringContainsString( 'x@example.org', $html );
		$this->assertStringNotContainsString( '<b>', $html, 'Logged values must not carry markup.' );
		// The manual checkbox reflects only the stored value, never auto-detection.
		$this->assertMatchesRegularExpression( '/id="enable_staging"[^>]*\\/>/', $html );
	}

	public function test_settings_screen_manual_checkbox_ignores_auto_detection() {
		$this->set_site_host( 'beta.example.com' );
		$settings = new Settings();

		$html = $this->capture( array( $settings, 'field_enable_staging' ) );

		$this->assertDoesNotMatchRegularExpression( '/id="enable_staging"[^>]*checked/s', $html );
	}
}
