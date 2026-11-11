<?php
/**
 * Tests for FrontBlocks\Admin\SettingsTransfer — exporting the settings to a
 * JSON document and importing one on another site.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\ImageManagement;
use FrontBlocks\Admin\Settings;
use FrontBlocks\Admin\SettingsTransfer;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class SettingsTransferTest extends TestCase {

	public function set_up() {
		parent::set_up();
		delete_option( 'frontblocks_settings' );
	}

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		remove_all_filters( 'frontblocks_import_settings_schema' );
		remove_all_filters( 'frontblocks_export_settings' );
		remove_all_filters( 'wp_image_editors' );
		wp_cache_delete( 'wp_image_editor_choose', 'image_editor' );
		unset( $_POST['_wpnonce'], $_REQUEST['_wpnonce'], $_FILES['frbl_settings_file'] );
		parent::tear_down();
	}

	/**
	 * Build a valid export document around the given settings.
	 *
	 * @param array $settings Setting key => value.
	 * @param array $extra    Keys that replace those of the document.
	 * @return string
	 */
	private function document( array $settings, array $extra = array() ) {
		return wp_json_encode(
			array_merge(
				array(
					'format'         => 'frontblocks-settings',
					'format_version' => 1,
					'plugin_version' => '1.5.5',
					'source_site'    => 'client-a.example',
					'settings'       => $settings,
				),
				$extra
			)
		);
	}

	/**
	 * Route the nonce-bound sanitizers through update_option(), as on a real admin request.
	 *
	 * @return void
	 */
	private function register_settings_filters() {
		$settings = new Settings();
		register_setting(
			'frontblocks_settings',
			'frontblocks_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $settings, 'sanitize_settings' ),
				'default'           => array(),
			)
		);
		new ImageManagement();
	}

	/*
	 * ---------------------------------------------------------------
	 * Schema and value validation
	 * ---------------------------------------------------------------
	 */

	public function test_schema_covers_every_boolean_setting_of_the_settings_screen() {
		$schema = SettingsTransfer::get_schema();

		foreach ( Settings::get_boolean_option_keys() as $key ) {
			$this->assertArrayHasKey( $key, $schema, $key );
			$this->assertSame( 'bool', $schema[ $key ]['type'], $key );
		}
	}

	public function test_boolean_keys_are_unique() {
		$keys = Settings::get_boolean_option_keys();

		$this->assertSame( count( $keys ), count( array_unique( $keys ) ) );
	}

	public function test_schema_never_includes_credentials_or_site_specific_values() {
		$keys = array_keys( SettingsTransfer::get_schema() );

		foreach ( array( 'client_id', 'client_secret', 'maintenance_image', 'image_sizes_disabled', 'image_sizes_overrides', 'image_sizes_custom' ) as $forbidden ) {
			$this->assertNotContains( $forbidden, $keys, $forbidden );
		}
	}

	public function test_schema_can_be_extended_by_add_ons() {
		add_filter(
			'frontblocks_import_settings_schema',
			function ( $schema ) {
				$schema['pro_mode'] = array( 'type' => 'bool' );
				return $schema;
			}
		);

		$this->assertArrayHasKey( 'pro_mode', SettingsTransfer::get_schema() );
	}

	public function test_bool_values() {
		$rule = array( 'type' => 'bool' );

		$this->assertTrue( SettingsTransfer::sanitize_value( true, $rule )['value'] );
		$this->assertFalse( SettingsTransfer::sanitize_value( false, $rule )['value'] );
		$this->assertTrue( SettingsTransfer::sanitize_value( 1, $rule )['value'] );
		$this->assertFalse( SettingsTransfer::sanitize_value( '0', $rule )['value'] );

		foreach ( array( 'yes', 2, '', null, array( true ), 1.5 ) as $bad ) {
			$this->assertFalse( SettingsTransfer::sanitize_value( $bad, $rule )['ok'], wp_json_encode( $bad ) );
		}
	}

	public function test_enum_values() {
		$rule = array(
			'type'   => 'enum',
			'values' => array( 'cpt', 'posts' ),
		);

		$this->assertSame( 'posts', SettingsTransfer::sanitize_value( 'posts', $rule )['value'] );

		foreach ( array( 'page', '', 'CPT', 0, array( 'cpt' ) ) as $bad ) {
			$this->assertFalse( SettingsTransfer::sanitize_value( $bad, $rule )['ok'], wp_json_encode( $bad ) );
		}
	}

	public function test_url_values_accept_http_and_https_only() {
		$rule = array( 'type' => 'url' );

		$this->assertSame( 'https://example.com/icon.svg', SettingsTransfer::sanitize_value( 'https://example.com/icon.svg', $rule )['value'] );
		$this->assertSame( '', SettingsTransfer::sanitize_value( '', $rule )['value'], 'An empty URL clears the setting.' );

		foreach ( array( 'javascript:alert(1)', 'ftp://example.com/a.svg', 'data:text/html;base64,AAAA', 42, array( 'https://example.com' ) ) as $bad ) {
			$this->assertFalse( SettingsTransfer::sanitize_value( $bad, $rule )['ok'], wp_json_encode( $bad ) );
		}
	}

	public function test_text_values_are_sanitized_and_length_limited() {
		$rule = array(
			'type' => 'text',
			'max'  => 20,
		);

		$this->assertSame( 'Back soon', SettingsTransfer::sanitize_value( '<b>Back</b> soon', $rule )['value'] );
		$this->assertTrue( SettingsTransfer::sanitize_value( str_repeat( 'a', 20 ), $rule )['ok'] );
		$this->assertFalse( SettingsTransfer::sanitize_value( str_repeat( 'a', 21 ), $rule )['ok'] );
		$this->assertFalse( SettingsTransfer::sanitize_value( 5, $rule )['ok'] );
	}

	public function test_int_values_are_bounded() {
		$rule = array(
			'type' => 'int',
			'min'  => 300,
			'max'  => 10000,
		);

		$this->assertSame( 2048, SettingsTransfer::sanitize_value( 2048, $rule )['value'] );
		$this->assertSame( 2048, SettingsTransfer::sanitize_value( '2048', $rule )['value'] );

		foreach ( array( 299, 10001, 20.5, '20.5', 'abc', true, null, array( 2048 ) ) as $bad ) {
			$this->assertFalse( SettingsTransfer::sanitize_value( $bad, $rule )['ok'], wp_json_encode( $bad ) );
		}
	}

	public function test_unsupported_rule_type_is_rejected() {
		$this->assertFalse( SettingsTransfer::sanitize_value( 'x', array( 'type' => 'object' ) )['ok'] );
		$this->assertFalse( SettingsTransfer::sanitize_value( 'x', array() )['ok'] );
	}

	/*
	 * ---------------------------------------------------------------
	 * Export
	 * ---------------------------------------------------------------
	 */

	public function test_export_declares_its_format_and_origin() {
		$payload = SettingsTransfer::build_export_payload();

		$this->assertSame( 'frontblocks-settings', $payload['format'] );
		$this->assertSame( 1, $payload['format_version'] );
		$this->assertSame( FRBL_VERSION, $payload['plugin_version'] );
		$this->assertSame( wp_parse_url( home_url(), PHP_URL_HOST ), $payload['source_site'] );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $payload['exported_at'] );
	}

	public function test_export_lists_every_toggle_even_when_never_saved() {
		update_option( 'frontblocks_settings', array( 'enable_back_button' => true ) );

		$settings = SettingsTransfer::build_export_payload()['settings'];

		$this->assertTrue( $settings['enable_back_button'] );
		foreach ( Settings::get_boolean_option_keys() as $key ) {
			$this->assertArrayHasKey( $key, $settings, $key );
		}
		$this->assertFalse( $settings['enable_popups'], 'An unchecked toggle is exported as off.' );
	}

	public function test_export_includes_simple_options_that_are_set() {
		update_option(
			'frontblocks_settings',
			array(
				'scroll_top_position'        => 'bottom-left',
				'maintenance_title'          => 'Back soon',
				'image_format_target'        => 'webp',
				'image_format_quality_webp'  => 70,
				'image_max_upload_dimension' => 2560,
			)
		);

		$settings = SettingsTransfer::build_export_payload()['settings'];

		$this->assertSame( 'bottom-left', $settings['scroll_top_position'] );
		$this->assertSame( 'Back soon', $settings['maintenance_title'] );
		$this->assertSame( 'webp', $settings['image_format_target'] );
		$this->assertSame( 70, $settings['image_format_quality_webp'] );
		$this->assertSame( 2560, $settings['image_max_upload_dimension'] );
		$this->assertArrayNotHasKey( 'events_type', $settings, 'A setting that was never saved is left out.' );
	}

	public function test_export_never_contains_site_specific_or_unknown_values() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_back_button'    => true,
				'maintenance_image'     => 123,
				'image_sizes_disabled'  => array( 'large' ),
				'image_sizes_overrides' => array( 'large' => array( 'width' => 10 ) ),
				'image_sizes_custom'    => array( array( 'name' => 'x' ) ),
				'client_secret'         => 'top-secret',
				'something_else'        => 'value',
			)
		);

		$json = wp_json_encode( SettingsTransfer::build_export_payload() );

		foreach ( array( 'maintenance_image', 'image_sizes', 'client_secret', 'top-secret', 'something_else' ) as $leak ) {
			$this->assertStringNotContainsString( $leak, $json, $leak );
		}
	}

	public function test_export_drops_stored_values_that_no_longer_validate() {
		update_option( 'frontblocks_settings', array( 'scroll_top_position' => 'middle' ) );

		$this->assertArrayNotHasKey( 'scroll_top_position', SettingsTransfer::build_export_payload()['settings'] );
	}

	public function test_export_can_be_extended_by_add_ons() {
		update_option( 'frontblocks_settings', array( 'pro_mode' => true ) );
		add_filter(
			'frontblocks_export_settings',
			function ( $settings, $options ) {
				$settings['pro_mode'] = $options['pro_mode'];
				return $settings;
			},
			10,
			2
		);

		$this->assertTrue( SettingsTransfer::build_export_payload()['settings']['pro_mode'] );
	}

	public function test_export_filename_is_safe() {
		$name = SettingsTransfer::get_export_filename();

		$this->assertMatchesRegularExpression( '/^frontblocks-settings-[a-z0-9.\-]+-\d{4}-\d{2}-\d{2}\.json$/', $name );
	}

	/*
	 * ---------------------------------------------------------------
	 * File validation
	 * ---------------------------------------------------------------
	 */

	public function test_rejects_an_empty_file() {
		$this->assertWPError( SettingsTransfer::parse_file_contents( '' ) );
		$this->assertWPError( SettingsTransfer::parse_file_contents( "  \n " ) );
	}

	public function test_rejects_a_file_over_the_size_limit() {
		$error = SettingsTransfer::parse_file_contents( str_repeat( 'a', SettingsTransfer::MAX_FILE_SIZE + 1 ) );

		$this->assertWPError( $error );
		$this->assertSame( 'frbl_transfer_too_large', $error->get_error_code() );
	}

	public function test_rejects_invalid_json() {
		foreach ( array( '{not json', '[1,2', 'null', '"text"', '42' ) as $raw ) {
			$error = SettingsTransfer::parse_file_contents( $raw );
			$this->assertWPError( $error, $raw );
			$this->assertSame( 'frbl_transfer_invalid_json', $error->get_error_code(), $raw );
		}
	}

	public function test_rejects_a_document_that_is_not_a_frontblocks_export() {
		$error = SettingsTransfer::parse_file_contents( wp_json_encode( array( 'settings' => array( 'a' => 1 ) ) ) );
		$this->assertSame( 'frbl_transfer_invalid_format', $error->get_error_code() );

		$error = SettingsTransfer::parse_file_contents( $this->document( array( 'a' => 1 ), array( 'format' => 'another-plugin' ) ) );
		$this->assertSame( 'frbl_transfer_invalid_format', $error->get_error_code() );
	}

	public function test_rejects_a_missing_or_invalid_format_version() {
		foreach ( array( null, 0, -1, '1', 1.5, 'one' ) as $version ) {
			$error = SettingsTransfer::parse_file_contents( $this->document( array( 'a' => 1 ), array( 'format_version' => $version ) ) );
			$this->assertWPError( $error, wp_json_encode( $version ) );
			$this->assertSame( 'frbl_transfer_invalid_version', $error->get_error_code(), wp_json_encode( $version ) );
		}
	}

	public function test_rejects_a_file_from_a_newer_format() {
		$error = SettingsTransfer::parse_file_contents( $this->document( array( 'a' => 1 ), array( 'format_version' => 2 ) ) );

		$this->assertSame( 'frbl_transfer_newer_version', $error->get_error_code() );
	}

	public function test_rejects_a_file_without_settings() {
		foreach ( array( array(), 'text', null ) as $settings ) {
			$error = SettingsTransfer::parse_file_contents( $this->document( array(), array( 'settings' => $settings ) ) );
			$this->assertSame( 'frbl_transfer_no_settings', $error->get_error_code(), wp_json_encode( $settings ) );
		}
	}

	public function test_accepts_a_valid_file_and_a_byte_order_mark() {
		$raw = $this->document( array( 'enable_back_button' => true ) );

		$this->assertIsArray( SettingsTransfer::parse_file_contents( $raw ) );
		$this->assertIsArray( SettingsTransfer::parse_file_contents( "\xEF\xBB\xBF" . $raw ) );
	}

	public function test_rejects_deeply_nested_json() {
		$raw = '{"format":"frontblocks-settings","format_version":1,"settings":' . str_repeat( '[', 40 ) . str_repeat( ']', 40 ) . '}';

		$this->assertWPError( SettingsTransfer::parse_file_contents( $raw ) );
	}

	/*
	 * ---------------------------------------------------------------
	 * Validating the incoming settings
	 * ---------------------------------------------------------------
	 */

	public function test_validate_keeps_valid_values_and_reports_the_rest() {
		$result = SettingsTransfer::validate_settings(
			array(
				'enable_back_button'  => true,
				'scroll_top_position' => 'middle',
				'client_secret'       => 'x',
				'enable_popups'       => 'yes',
			)
		);

		$this->assertSame( array( 'enable_back_button' => true ), $result['valid'] );
		$this->assertSame( array( 'scroll_top_position', 'client_secret', 'enable_popups' ), array_keys( $result['skipped'] ) );
		$this->assertSame( 'unknown setting', $result['skipped']['client_secret'] );
	}

	public function test_validate_does_not_accept_numeric_keys_or_nested_values() {
		$result = SettingsTransfer::validate_settings(
			array(
				0                    => true,
				'enable_back_button' => array( 'nested' => true ),
			)
		);

		$this->assertSame( array(), $result['valid'] );
		$this->assertCount( 2, $result['skipped'] );
	}

	public function test_validate_downgrades_an_image_format_the_server_cannot_produce() {
		add_filter( 'wp_image_editors', '__return_empty_array' );
		wp_cache_delete( 'wp_image_editor_choose', 'image_editor' );

		$result = SettingsTransfer::validate_settings( array( 'image_format_target' => 'avif' ) );

		$this->assertSame( 'none', $result['valid']['image_format_target'] );
		$this->assertArrayHasKey( 'image_format_target', $result['adjusted'] );
	}

	public function test_validate_leaves_an_unneeded_image_format_alone() {
		add_filter( 'wp_image_editors', '__return_empty_array' );
		wp_cache_delete( 'wp_image_editor_choose', 'image_editor' );

		$result = SettingsTransfer::validate_settings( array( 'image_format_target' => 'none' ) );

		$this->assertSame( 'none', $result['valid']['image_format_target'] );
		$this->assertSame( array(), $result['adjusted'] );
	}

	/*
	 * ---------------------------------------------------------------
	 * Applying
	 * ---------------------------------------------------------------
	 */

	public function test_apply_merges_over_the_current_settings() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_back_button' => false,
				'maintenance_image'  => 123,
				'client_secret'      => 'keep-me',
			)
		);

		$result = SettingsTransfer::apply_settings( array( 'enable_back_button' => true ) );

		$stored = get_option( 'frontblocks_settings' );
		$this->assertTrue( $stored['enable_back_button'] );
		$this->assertSame( 123, $stored['maintenance_image'], 'Settings that are not in the file stay as they were.' );
		$this->assertSame( 'keep-me', $stored['client_secret'] );
		$this->assertSame( array( 'enable_back_button' ), $result['applied'] );
	}

	public function test_apply_reaches_the_option_through_the_form_sanitizers() {
		$this->register_settings_filters();

		SettingsTransfer::apply_settings(
			array(
				'enable_back_button'        => true,
				'image_format_quality_avif' => 55,
			)
		);

		$stored = get_option( 'frontblocks_settings' );
		$this->assertTrue( $stored['enable_back_button'] );
		$this->assertSame( 55, $stored['image_format_quality_avif'] );
	}

	public function test_form_sanitizers_still_ignore_saves_that_are_not_an_import() {
		update_option( 'frontblocks_settings', array( 'enable_back_button' => false ) );
		$this->register_settings_filters();

		// A save without the settings form nonce must still be refused.
		update_option( 'frontblocks_settings', array( 'enable_back_button' => true ) );

		$this->assertFalse( get_option( 'frontblocks_settings' )['enable_back_button'] );
		$this->assertFalse( SettingsTransfer::is_importing() );
	}

	public function test_import_flag_is_cleared_when_saving_fails() {
		add_filter(
			'sanitize_option_frontblocks_settings',
			function () {
				throw new RuntimeException( 'boom' );
			}
		);

		try {
			SettingsTransfer::apply_settings( array( 'enable_back_button' => true ) );
			$this->fail( 'The exception must reach the caller.' );
		} catch ( RuntimeException $e ) {
			$this->assertFalse( SettingsTransfer::is_importing() );
		}

		remove_all_filters( 'sanitize_option_frontblocks_settings' );
	}

	public function test_apply_keeps_the_two_short_description_options_exclusive() {
		update_option( 'frontblocks_settings', array( 'deactivate_short_description' => true ) );

		$result = SettingsTransfer::apply_settings( array( 'move_content_to_short_description' => true ) );

		$stored = get_option( 'frontblocks_settings' );
		$this->assertTrue( $stored['move_content_to_short_description'], 'The imported setting wins.' );
		$this->assertFalse( $stored['deactivate_short_description'] );
		$this->assertArrayHasKey( 'deactivate_short_description', $result['adjusted'] );
	}

	public function test_apply_turns_off_the_move_option_when_the_file_enables_both() {
		$result = SettingsTransfer::apply_settings(
			array(
				'deactivate_short_description'      => true,
				'move_content_to_short_description' => true,
			)
		);

		$stored = get_option( 'frontblocks_settings' );
		$this->assertTrue( $stored['deactivate_short_description'] );
		$this->assertFalse( $stored['move_content_to_short_description'] );
		$this->assertArrayHasKey( 'move_content_to_short_description', $result['adjusted'] );
	}

	/*
	 * ---------------------------------------------------------------
	 * Whole import
	 * ---------------------------------------------------------------
	 */

	public function test_import_applies_a_file_exported_from_another_site() {
		$this->register_settings_filters();

		$result = SettingsTransfer::import_from_string(
			$this->document(
				array(
					'enable_back_button'  => true,
					'enable_popups'       => true,
					'scroll_top_position' => 'bottom-left',
					'maintenance_title'   => 'Back soon',
					'bogus'               => 1,
				)
			)
		);

		$this->assertIsArray( $result );
		$this->assertSame( array( 'enable_back_button', 'enable_popups', 'scroll_top_position', 'maintenance_title' ), $result['applied'] );
		$this->assertSame( array( 'bogus' => 'unknown setting' ), $result['skipped'] );
		$this->assertSame( 'client-a.example', $result['source_site'] );
		$this->assertSame( '1.5.5', $result['source_version'] );

		$stored = get_option( 'frontblocks_settings' );
		$this->assertTrue( $stored['enable_back_button'] );
		$this->assertTrue( $stored['enable_popups'] );
		$this->assertSame( 'bottom-left', $stored['scroll_top_position'] );
		$this->assertSame( 'Back soon', $stored['maintenance_title'] );
		$this->assertArrayNotHasKey( 'bogus', $stored );
	}

	public function test_import_of_an_invalid_file_changes_nothing() {
		$before = array( 'enable_back_button' => true );
		update_option( 'frontblocks_settings', $before );
		$this->register_settings_filters();

		foreach ( array( '', '{bad', $this->document( array( 'a' => 1 ), array( 'format' => 'x' ) ), $this->document( array( 'a' => 1 ), array( 'format_version' => 9 ) ) ) as $raw ) {
			$this->assertWPError( SettingsTransfer::import_from_string( $raw ) );
			$this->assertSame( $before, get_option( 'frontblocks_settings' ) );
		}
	}

	public function test_a_file_made_of_unknown_settings_applies_nothing() {
		update_option( 'frontblocks_settings', array( 'enable_back_button' => true ) );

		$result = SettingsTransfer::import_from_string( $this->document( array( 'client_secret' => 'x' ) ) );

		$this->assertSame( array(), $result['applied'] );
		$this->assertSame( array( 'enable_back_button' => true ), get_option( 'frontblocks_settings' ) );
	}

	public function test_export_then_import_reproduces_the_configuration() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_back_button'        => true,
				'enable_fullpage_scroll'    => true,
				'scroll_top_position'       => 'bottom-left',
				'events_type'               => 'posts',
				'image_format_quality_webp' => 64,
			)
		);
		$exported = wp_json_encode( SettingsTransfer::build_export_payload() );

		update_option( 'frontblocks_settings', array( 'enable_back_button' => false, 'enable_popups' => true ) );
		$this->register_settings_filters();
		SettingsTransfer::import_from_string( $exported );

		$stored = get_option( 'frontblocks_settings' );
		$this->assertTrue( $stored['enable_back_button'] );
		$this->assertTrue( $stored['enable_fullpage_scroll'] );
		$this->assertFalse( $stored['enable_popups'], 'Every toggle in the file is applied, including the ones that are off.' );
		$this->assertSame( 'bottom-left', $stored['scroll_top_position'] );
		$this->assertSame( 'posts', $stored['events_type'] );
		$this->assertSame( 64, $stored['image_format_quality_webp'] );
	}

	/*
	 * ---------------------------------------------------------------
	 * Admin screen and requests
	 * ---------------------------------------------------------------
	 */

	public function test_adds_the_backup_tab_before_the_license_tab() {
		$transfer = new SettingsTransfer();
		$tabs     = $transfer->add_tab(
			array(
				array( 'id' => 'blocks' ),
				array( 'id' => 'license' ),
			)
		);

		$this->assertSame( array( 'blocks', 'backup', 'license' ), wp_list_pluck( $tabs, 'id' ) );
	}

	public function test_adds_the_backup_tab_at_the_end_when_there_is_no_license_tab() {
		$transfer = new SettingsTransfer();
		$tabs     = $transfer->add_tab( array( array( 'id' => 'blocks' ) ) );

		$this->assertSame( array( 'blocks', 'backup' ), wp_list_pluck( $tabs, 'id' ) );
	}

	public function test_registers_its_hooks() {
		$transfer = new SettingsTransfer();

		$this->assertNotFalse( has_filter( 'frontblocks_settings_tabs', array( $transfer, 'add_tab' ) ) );
		$this->assertNotFalse( has_action( 'frontblocks_settings_tab_panels', array( $transfer, 'render_panel' ) ) );
		$this->assertNotFalse( has_action( 'admin_post_frbl_export_settings', array( $transfer, 'handle_export' ) ) );
		$this->assertNotFalse( has_action( 'admin_post_frbl_import_settings', array( $transfer, 'handle_import' ) ) );
	}

	public function test_panel_renders_export_link_and_import_form_for_administrators() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		ob_start();
		( new SettingsTransfer() )->render_panel();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'data-tab-panel="backup"', $html );
		$this->assertStringContainsString( 'action=frbl_export_settings', $html );
		$this->assertStringContainsString( '_wpnonce=', $html );
		$this->assertStringContainsString( 'enctype="multipart/form-data"', $html );
		$this->assertStringContainsString( 'name="action" value="frbl_import_settings"', $html );
		$this->assertStringContainsString( 'name="frbl_settings_file"', $html );
		$this->assertStringContainsString( 'accept=".json,application/json"', $html );
	}

	public function test_panel_renders_nothing_without_the_capability() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		ob_start();
		( new SettingsTransfer() )->render_panel();

		$this->assertSame( '', ob_get_clean() );
	}

	public function test_panel_shows_the_import_summary_once() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_transient(
			'frbl_transfer_result_' . get_current_user_id(),
			array(
				'applied'     => array( 'enable_back_button', 'enable_popups' ),
				'skipped'     => array( 'bogus' => 'unknown setting' ),
				'adjusted'    => array(),
				'source_site' => 'client-a.example',
			),
			60
		);
		$_GET['frbl-transfer'] = '1';

		ob_start();
		( new SettingsTransfer() )->render_panel();
		$first = ob_get_clean();

		ob_start();
		( new SettingsTransfer() )->render_panel();
		$second = ob_get_clean();

		unset( $_GET['frbl-transfer'] );

		$this->assertStringContainsString( '2 settings imported.', $first );
		$this->assertStringContainsString( 'client-a.example', $first );
		$this->assertStringNotContainsString( 'Reload', $first );
		$this->assertStringContainsString( 'bogus', $first );
		$this->assertStringNotContainsString( 'settings imported', $second, 'The summary is consumed after being shown.' );
	}

	public function test_panel_shows_why_a_file_was_rejected() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_transient( 'frbl_transfer_result_' . get_current_user_id(), array( 'error' => 'The file is not valid JSON.' ), 60 );
		$_GET['frbl-transfer'] = '1';

		ob_start();
		( new SettingsTransfer() )->render_panel();
		$html = ob_get_clean();

		unset( $_GET['frbl-transfer'] );

		$this->assertStringContainsString( 'Nothing was imported. The file is not valid JSON.', $html );
	}

	public function test_summary_values_are_escaped() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_transient(
			'frbl_transfer_result_' . get_current_user_id(),
			array(
				'applied'     => array(),
				'skipped'     => array( '<script>alert(1)</script>' => '<img src=x onerror=alert(1)>' ),
				'adjusted'    => array(),
				'source_site' => '<b>evil</b>',
			),
			60
		);
		$_GET['frbl-transfer'] = '1';

		ob_start();
		( new SettingsTransfer() )->render_panel();
		$html = ob_get_clean();

		unset( $_GET['frbl-transfer'] );

		$this->assertStringNotContainsString( '<script>alert', $html );
		$this->assertStringNotContainsString( '<img src=x', $html );
		$this->assertStringNotContainsString( '<b>evil</b>', $html );
	}

	/**
	 * Make the next admin-post request carry a valid nonce for the action.
	 *
	 * @param string $action Nonce action.
	 * @return void
	 */
	private function with_nonce( $action ) {
		$_REQUEST['_wpnonce'] = wp_create_nonce( $action );
	}

	/**
	 * Run handle_import() and return the URL it redirects to.
	 *
	 * @return string
	 */
	private function run_import_request() {
		add_filter(
			'wp_redirect',
			function ( $location ) {
				throw new RuntimeException( $location );
			}
		);

		try {
			( new SettingsTransfer() )->handle_import();
		} catch ( RuntimeException $e ) {
			return $e->getMessage();
		} finally {
			remove_all_filters( 'wp_redirect' );
		}

		$this->fail( 'handle_import() must redirect.' );
	}

	public function test_export_request_is_refused_without_the_capability() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->with_nonce( 'frbl_export_settings' );
		add_filter( 'wp_die_handler', array( $this, 'get_wp_die_handler' ) );

		$this->expectException( 'WPDieException' );

		( new SettingsTransfer() )->handle_export();
	}

	public function test_import_request_is_refused_without_the_capability() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->with_nonce( 'frbl_import_settings' );
		add_filter( 'wp_die_handler', array( $this, 'get_wp_die_handler' ) );
		update_option( 'frontblocks_settings', array( 'enable_back_button' => false ) );

		try {
			( new SettingsTransfer() )->handle_import();
			$this->fail( 'The request must be refused.' );
		} catch ( WPDieException $e ) {
			$this->assertFalse( get_option( 'frontblocks_settings' )['enable_back_button'] );
			$this->assertFalse( get_transient( 'frbl_transfer_result_' . get_current_user_id() ), 'A refused request leaves no result behind.' );
		}

	}

	public function test_requests_are_refused_without_a_valid_nonce() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		add_filter( 'wp_die_handler', array( $this, 'get_wp_die_handler' ) );
		update_option( 'frontblocks_settings', array( 'enable_back_button' => false ) );

		foreach ( array( 'handle_export', 'handle_import' ) as $handler ) {
			// A nonce for the other action must not be accepted either.
			$_REQUEST['_wpnonce'] = wp_create_nonce( 'handle_export' === $handler ? 'frbl_import_settings' : 'frbl_export_settings' );

			try {
				( new SettingsTransfer() )->$handler();
				$this->fail( $handler . ' must check the nonce.' );
			} catch ( WPDieException $e ) {
				$this->assertFalse( get_option( 'frontblocks_settings' )['enable_back_button'] );
			}
		}

	}

	public function test_import_request_without_a_file_reports_it_and_returns_to_the_backup_tab() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->with_nonce( 'frbl_import_settings' );
		update_option( 'frontblocks_settings', array( 'enable_back_button' => true ) );

		$location = $this->run_import_request();

		$this->assertStringContainsString( 'page=frontblocks-settings', $location );
		$this->assertStringContainsString( 'frbl-transfer=1', $location );
		$this->assertStringEndsWith( '#backup', $location );

		$result = get_transient( 'frbl_transfer_result_' . get_current_user_id() );
		$this->assertSame( 'Choose a settings file to import.', $result['error'] );
		$this->assertSame( array( 'enable_back_button' => true ), get_option( 'frontblocks_settings' ) );

	}

	public function test_import_request_rejects_bad_uploads_without_touching_the_settings() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'frontblocks_settings', array( 'enable_back_button' => true ) );

		$tmp = wp_tempnam( 'frbl-settings.json' );
		file_put_contents( $tmp, $this->document( array( 'enable_back_button' => false ) ) );

		$cases = array(
			'upload error'        => array(
				array(
					'name'     => 'settings.json',
					'tmp_name' => $tmp,
					'size'     => 100,
					'error'    => UPLOAD_ERR_PARTIAL,
				),
				'The file could not be uploaded. Try again.',
			),
			'too large'           => array(
				array(
					'name'     => 'settings.json',
					'tmp_name' => $tmp,
					'size'     => SettingsTransfer::MAX_FILE_SIZE + 1,
					'error'    => UPLOAD_ERR_OK,
				),
				'The file is too large to be a FrontBlocks settings export.',
			),
			'wrong extension'     => array(
				array(
					'name'     => 'settings.php',
					'tmp_name' => $tmp,
					'size'     => 100,
					'error'    => UPLOAD_ERR_OK,
				),
				'Choose a .json file.',
			),
			'not an HTTP upload'  => array(
				array(
					'name'     => 'settings.json',
					'tmp_name' => $tmp,
					'size'     => 100,
					'error'    => UPLOAD_ERR_OK,
				),
				'The file could not be uploaded. Try again.',
			),
		);

		foreach ( $cases as $label => $case ) {
			$this->with_nonce( 'frbl_import_settings' );
			$_FILES['frbl_settings_file'] = $case[0];

			$this->run_import_request();

			$result = get_transient( 'frbl_transfer_result_' . get_current_user_id() );
			$this->assertSame( $case[1], $result['error'], $label );
			$this->assertSame( array( 'enable_back_button' => true ), get_option( 'frontblocks_settings' ), $label );
			delete_transient( 'frbl_transfer_result_' . get_current_user_id() );
		}

		unlink( $tmp );
	}
}
