<?php
/**
 * Tests for FrontBlocks\Admin\ImageManagement.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\ImageManagement;
use FrontBlocks\Frontend\ImageManagement as FrontendImageManagement;
use Yoast\WPTestUtils\WPIntegration\TestCase;

require_once ABSPATH . WPINC . '/class-wp-image-editor.php';

/**
 * A fully deterministic fake WP_Image_Editor implementation, registered via
 * the core `wp_image_editors` filter, so that
 * ImageManagement::downgrade_target_to_supported_formats() can be exercised
 * against every WebP/AVIF support combination regardless of what the host
 * actually running this test suite supports.
 *
 * For reference, this test environment's real support (probed once via
 * `var_export( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ), true )`
 * and the AVIF equivalent) was: WebP => true, AVIF => true. The dedicated
 * downgrade tests below do not rely on that, though — they fully control
 * support through this double.
 */
class FrblTestDoubleImageEditor extends WP_Image_Editor {

	/**
	 * MIME types this fake editor currently claims to support.
	 *
	 * @var string[]
	 */
	public static $supported_mime_types = array();

	public static function test( $args = array() ) {
		return true;
	}

	public static function supports_mime_type( $mime_type ) {
		return in_array( $mime_type, self::$supported_mime_types, true );
	}

	public function load() {
		return true;
	}

	public function save( $destfilename = null, $mime_type = null ) {
		return array();
	}

	public function resize( $max_w, $max_h, $crop = false ) {
		return true;
	}

	public function multi_resize( $sizes ) {
		return array();
	}

	public function crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false ) {
		return true;
	}

	public function rotate( $angle ) {
		return true;
	}

	public function flip( $horz, $vert ) {
		return true;
	}

	public function stream( $mime_type = null ) {
		return true;
	}
}

class AdminImageManagementTest extends TestCase {

	/**
	 * @var ImageManagement
	 */
	private $admin;

	/**
	 * Original values of the core-size options, so tests that write to
	 * them can be reverted precisely in tear_down().
	 *
	 * @var array
	 */
	private $original_core_size_options = array();

	/**
	 * Additional image sizes registered by a test, to remove in tear_down().
	 *
	 * @var string[]
	 */
	private $registered_extra_sizes = array();

	/**
	 * Attachment IDs created by a test, to delete (with files) in tear_down().
	 *
	 * @var int[]
	 */
	private $created_attachment_ids = array();

	public function set_up() {
		parent::set_up();

		$this->admin = new ImageManagement();

		// wp_die()/check_ajax_referer()/wp_send_json_*() route through the
		// 'wp_die_ajax_handler' filter (not 'wp_die_handler') whenever
		// wp_doing_ajax() is true — which the AJAX tests below force so
		// that a rejected/handled request throws WPDieException instead of
		// falling through to a raw die('-1'). The base TestCase only wires
		// up 'wp_die_handler', so this reuses its exception-throwing
		// handler for the ajax-specific filter too.
		add_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler' ) );

		foreach ( FrontendImageManagement::CORE_SIZES as $name ) {
			$this->original_core_size_options[ $name ] = array(
				'w'    => get_option( "{$name}_size_w" ),
				'h'    => get_option( "{$name}_size_h" ),
				'crop' => get_option( "{$name}_crop" ),
			);
		}
	}

	public function tear_down() {
		delete_option( 'frontblocks_settings' );

		foreach ( $this->original_core_size_options as $name => $values ) {
			update_option( "{$name}_size_w", $values['w'] );
			update_option( "{$name}_size_h", $values['h'] );
			update_option( "{$name}_crop", $values['crop'] );
		}

		global $_wp_additional_image_sizes;
		foreach ( $this->registered_extra_sizes as $name ) {
			remove_image_size( $name );
			unset( $_wp_additional_image_sizes[ $name ] );
		}
		$this->registered_extra_sizes = array();

		foreach ( $this->created_attachment_ids as $attachment_id ) {
			$file = get_attached_file( $attachment_id );
			wp_delete_attachment( $attachment_id, true );
			if ( $file && file_exists( $file ) ) {
				wp_delete_file( $file );
			}
		}
		$this->created_attachment_ids = array();

		global $wp_settings_sections, $wp_settings_fields;
		unset( $wp_settings_sections['frontblocks-settings']['frontblocks_section_image_management'] );
		unset( $wp_settings_fields['frontblocks-settings']['frontblocks_section_image_management'] );

		unset( $_POST['_wpnonce'], $_POST['frontblocks_settings'], $_POST['nonce'], $_POST['ids'] );
		unset( $_REQUEST['nonce'] );

		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', array( $this, 'get_wp_die_handler' ) );
		remove_all_filters( 'wp_image_editors' );
		FrblTestDoubleImageEditor::$supported_mime_types = array();
		wp_cache_delete( 'wp_image_editor_choose', 'image_editor' );

		parent::tear_down();
	}

	/**
	 * Invoke a private/protected method via Reflection.
	 *
	 * @param object $object Object to invoke on.
	 * @param string $method Method name.
	 * @param array  $args   Positional arguments.
	 * @return mixed
	 */
	private function call_private( $object, $method, array $args = array() ) {
		$ref = new ReflectionMethod( $object, $method );
		$ref->setAccessible( true );
		return $ref->invokeArgs( $object, $args );
	}

	/**
	 * Register a fake image editor with the given set of supported MIME
	 * types, and clear the internal `wp_image_editor_choose` cache so the
	 * new support set takes effect immediately.
	 *
	 * @param string[] $supported_mime_types e.g. array( 'image/webp' ).
	 * @return void
	 */
	private function set_fake_editor_support( array $supported_mime_types ) {
		FrblTestDoubleImageEditor::$supported_mime_types = $supported_mime_types;
		add_filter(
			'wp_image_editors',
			function () {
				return array( 'FrblTestDoubleImageEditor' );
			}
		);
		wp_cache_delete( 'wp_image_editor_choose', 'image_editor' );
	}

	/**
	 * Populate $_POST with a valid nonce plus the given
	 * frontblocks_settings[...] fields, exactly as the settings form would.
	 *
	 * @param array $fields Fields for $_POST['frontblocks_settings'].
	 * @return void
	 */
	private function post_settings( array $fields ) {
		// image_format_target always has a value in the real form (it's a
		// <select>), so default it here unless a test overrides it — see
		// the note in this file's final report about sanitize_settings()
		// otherwise reading an undefined array key when the field is
		// missing entirely.
		$fields = array_merge( array( 'image_format_target' => 'none' ), $fields );

		$_POST['_wpnonce']             = wp_create_nonce( 'frontblocks_settings-options' );
		$_POST['frontblocks_settings'] = $fields;
	}

	/**
	 * Index a get_registered_sizes_info() result by its 'name' key.
	 *
	 * @param array $sizes Result of get_registered_sizes_info().
	 * @return array
	 */
	private function index_sizes_by_name( array $sizes ) {
		$by_name = array();
		foreach ( $sizes as $size ) {
			$by_name[ $size['name'] ] = $size;
		}
		return $by_name;
	}

	/**
	 * Create a real, on-disk JPEG attachment so file-dependent code
	 * (wp_generate_attachment_metadata(), variant generation, disabled-size
	 * cleanup) has an actual image to work with.
	 *
	 * @return int Attachment ID.
	 */
	private function create_real_image_attachment() {
		$upload   = wp_upload_dir();
		$filename = trailingslashit( $upload['path'] ) . 'frbl-test-' . wp_generate_password( 8, false ) . '.jpg';

		// Large enough that every default core size (including the
		// thumbnail crop) actually gets generated, rather than being
		// skipped for being smaller than the source.
		$image = imagecreatetruecolor( 800, 600 );
		imagejpeg( $image, $filename );
		imagedestroy( $image );

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => 'frbl-test-image',
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$filename
		);

		$this->created_attachment_ids[] = $attachment_id;

		return $attachment_id;
	}

	/*
	 * ---------------------------------------------------------------
	 * register_settings()
	 * ---------------------------------------------------------------
	 */

	public function test_register_settings_adds_section_and_field() {
		global $wp_settings_sections, $wp_settings_fields;

		$this->admin->register_settings();

		$this->assertArrayHasKey(
			'frontblocks_section_image_management',
			$wp_settings_sections['frontblocks-settings'],
			'The Image Management settings section must be registered on the frontblocks-settings page.'
		);
		$this->assertArrayHasKey(
			'enable_image_management',
			$wp_settings_fields['frontblocks-settings']['frontblocks_section_image_management'],
			'The enable_image_management field must be registered under the Image Management section.'
		);
	}

	/*
	 * ---------------------------------------------------------------
	 * get_registered_size_names() (private)
	 * ---------------------------------------------------------------
	 */

	public function test_get_registered_size_names_defaults_to_core_sizes_only() {
		$names = $this->call_private( $this->admin, 'get_registered_size_names' );

		// WordPress core itself registers a couple of additional sizes by
		// default (e.g. the big-image scaling thresholds), so this can be a
		// superset of CORE_SIZES — but it must contain every core size
		// exactly once, and no duplicates at all.
		foreach ( FrontendImageManagement::CORE_SIZES as $core_name ) {
			$this->assertContains( $core_name, $names );
		}
		$this->assertSame( array_values( array_unique( $names ) ), $names, 'Result must not contain duplicate names.' );
	}

	public function test_get_registered_size_names_merges_additional_sizes_without_duplicates() {
		add_image_size( 'frbl_extra_size', 640, 480, false );
		$this->registered_extra_sizes[] = 'frbl_extra_size';

		$names = $this->call_private( $this->admin, 'get_registered_size_names' );

		$this->assertContains( 'frbl_extra_size', $names );
		$this->assertSame( array_values( array_unique( $names ) ), $names, 'Result must not contain duplicate names.' );
		foreach ( FrontendImageManagement::CORE_SIZES as $core_name ) {
			$this->assertContains( $core_name, $names );
		}
	}

	/*
	 * ---------------------------------------------------------------
	 * get_registered_sizes_info() (private)
	 * ---------------------------------------------------------------
	 */

	public function test_get_registered_sizes_info_reflects_core_size_options() {
		update_option( 'thumbnail_size_w', 111 );
		update_option( 'thumbnail_size_h', 222 );
		update_option( 'thumbnail_crop', true );

		$sizes = $this->call_private( $this->admin, 'get_registered_sizes_info' );
		$by_name = $this->index_sizes_by_name( $sizes );

		$this->assertArrayHasKey( 'thumbnail', $by_name );
		$this->assertSame( 'core', $by_name['thumbnail']['source'] );
		$this->assertSame( 111, $by_name['thumbnail']['width'] );
		$this->assertSame( 222, $by_name['thumbnail']['height'] );
		$this->assertTrue( $by_name['thumbnail']['crop'] );
	}

	public function test_get_registered_sizes_info_lists_additional_sizes_as_theme_or_plugin_source() {
		add_image_size( 'frbl_extra_size', 640, 480, true );
		$this->registered_extra_sizes[] = 'frbl_extra_size';

		$sizes   = $this->call_private( $this->admin, 'get_registered_sizes_info' );
		$by_name = $this->index_sizes_by_name( $sizes );

		$this->assertArrayHasKey( 'frbl_extra_size', $by_name );
		$this->assertSame( 'theme/plugin', $by_name['frbl_extra_size']['source'] );
		$this->assertSame( 640, $by_name['frbl_extra_size']['width'] );
		$this->assertSame( 480, $by_name['frbl_extra_size']['height'] );
		$this->assertTrue( $by_name['frbl_extra_size']['crop'] );
	}

	public function test_get_registered_sizes_info_excludes_sizes_already_listed_as_custom() {
		add_image_size( 'frbl_promo_banner', 1200, 400, false );
		$this->registered_extra_sizes[] = 'frbl_promo_banner';

		update_option(
			'frontblocks_settings',
			array(
				'image_sizes_custom' => array(
					array(
						'name'   => 'frbl_promo_banner',
						'width'  => 1200,
						'height' => 400,
					),
				),
			)
		);

		$sizes = $this->call_private( $this->admin, 'get_registered_sizes_info' );
		$names = wp_list_pluck( $sizes, 'name' );

		$this->assertNotContains(
			'frbl_promo_banner',
			$names,
			'A size already declared under image_sizes_custom must not also appear in the generic theme/plugin list.'
		);
		$this->assertContains( 'thumbnail', $names, 'Core sizes must still be listed.' );
	}

	/*
	 * ---------------------------------------------------------------
	 * downgrade_target_to_supported_formats() (private)
	 * ---------------------------------------------------------------
	 */

	public function test_downgrade_keeps_both_when_webp_and_avif_are_supported() {
		$this->set_fake_editor_support( array( 'image/webp', 'image/avif' ) );

		$this->assertSame( 'both', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'both' ) ) );
		$this->assertSame( 'webp', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'webp' ) ) );
		$this->assertSame( 'avif', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'avif' ) ) );
		$this->assertSame( 'none', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'none' ) ) );
	}

	public function test_downgrade_falls_back_to_webp_only_when_avif_unsupported() {
		$this->set_fake_editor_support( array( 'image/webp' ) );

		$this->assertSame( 'webp', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'both' ) ), 'both must downgrade to webp when only webp is supported.' );
		$this->assertSame( 'webp', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'webp' ) ) );
		$this->assertSame( 'none', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'avif' ) ), 'avif must downgrade to none when avif is unsupported.' );
	}

	public function test_downgrade_falls_back_to_avif_only_when_webp_unsupported() {
		$this->set_fake_editor_support( array( 'image/avif' ) );

		$this->assertSame( 'avif', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'both' ) ), 'both must downgrade to avif when only avif is supported.' );
		$this->assertSame( 'avif', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'avif' ) ) );
		$this->assertSame( 'none', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'webp' ) ), 'webp must downgrade to none when webp is unsupported.' );
	}

	public function test_downgrade_falls_back_to_none_when_neither_format_is_supported() {
		$this->set_fake_editor_support( array() );

		$this->assertSame( 'none', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'both' ) ) );
		$this->assertSame( 'none', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'webp' ) ) );
		$this->assertSame( 'none', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'avif' ) ) );
		$this->assertSame( 'none', $this->call_private( $this->admin, 'downgrade_target_to_supported_formats', array( 'none' ) ), '"none" must always pass through unchanged, regardless of server support.' );
	}

	/*
	 * ---------------------------------------------------------------
	 * sanitize_settings()
	 * ---------------------------------------------------------------
	 */

	public function test_sanitize_settings_ignores_non_array_values() {
		$result = $this->admin->sanitize_settings( 'not-an-array', 'frontblocks_settings' );
		$this->assertSame( 'not-an-array', $result );
	}

	public function test_sanitize_settings_ignores_other_options() {
		$result = $this->admin->sanitize_settings( array( 'untouched' => true ), 'some_other_option' );
		$this->assertSame( array( 'untouched' => true ), $result );
	}

	public function test_sanitize_settings_returns_value_unchanged_without_a_nonce() {
		unset( $_POST['_wpnonce'] );
		$_POST['frontblocks_settings'] = array( 'enable_image_management' => '1' );

		$result = $this->admin->sanitize_settings( array( 'untouched' => true ), 'frontblocks_settings' );

		$this->assertSame( array( 'untouched' => true ), $result );
	}

	public function test_sanitize_settings_returns_value_unchanged_with_an_invalid_nonce() {
		$_POST['_wpnonce']             = 'not-a-valid-nonce';
		$_POST['frontblocks_settings'] = array( 'enable_image_management' => '1' );

		$result = $this->admin->sanitize_settings( array( 'untouched' => true ), 'frontblocks_settings' );

		$this->assertSame( array( 'untouched' => true ), $result );
	}

	public function test_sanitize_settings_returns_value_unchanged_without_posted_settings_array() {
		$_POST['_wpnonce'] = wp_create_nonce( 'frontblocks_settings-options' );
		unset( $_POST['frontblocks_settings'] );

		$result = $this->admin->sanitize_settings( array( 'untouched' => true ), 'frontblocks_settings' );

		$this->assertSame( array( 'untouched' => true ), $result );
	}

	public function test_sanitize_settings_casts_enable_flag_to_boolean() {
		$this->post_settings( array( 'enable_image_management' => '1' ) );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertTrue( $sanitized['enable_image_management'] );

		$this->post_settings( array() );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertFalse( $sanitized['enable_image_management'], 'Absent means an unchecked checkbox.' );
	}

	public function test_sanitize_settings_image_format_target_only_accepts_whitelisted_values() {
		$this->post_settings( array( 'image_format_target' => 'not-a-real-target' ) );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertSame( 'none', $sanitized['image_format_target'] );
	}

	public function test_sanitize_settings_image_format_target_is_downgraded_to_actual_server_support() {
		$webp_supported = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
		$avif_supported = wp_image_editor_supports( array( 'mime_type' => 'image/avif' ) );

		$this->post_settings( array( 'image_format_target' => 'webp' ) );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertSame( $webp_supported ? 'webp' : 'none', $sanitized['image_format_target'] );

		$this->post_settings( array( 'image_format_target' => 'avif' ) );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertSame( $avif_supported ? 'avif' : 'none', $sanitized['image_format_target'] );
	}

	public function test_sanitize_settings_quality_fields_default_when_absent() {
		$this->post_settings( array() );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );

		$this->assertSame( 82, $sanitized['image_format_quality_webp'] );
		$this->assertSame( 60, $sanitized['image_format_quality_avif'] );
	}

	public function test_sanitize_settings_quality_fields_are_clamped_to_1_100() {
		$this->post_settings(
			array(
				'image_format_quality_webp' => '500',
				'image_format_quality_avif' => '0',
			)
		);
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );

		$this->assertSame( 100, $sanitized['image_format_quality_webp'], 'Values above 100 must be clamped to 100.' );
		$this->assertSame( 60, $sanitized['image_format_quality_avif'], 'A zero/invalid value must fall back to the AVIF default of 60.' );
	}

	public function test_sanitize_settings_quality_fields_keep_valid_values() {
		$this->post_settings(
			array(
				'image_format_quality_webp' => '95',
				'image_format_quality_avif' => '40',
			)
		);
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );

		$this->assertSame( 95, $sanitized['image_format_quality_webp'] );
		$this->assertSame( 40, $sanitized['image_format_quality_avif'] );
	}

	public function test_sanitize_settings_max_upload_dimension_enabled_is_boolean() {
		$this->post_settings( array( 'image_max_upload_dimension_enabled' => '1' ) );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertTrue( $sanitized['image_max_upload_dimension_enabled'] );

		$this->post_settings( array() );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertFalse( $sanitized['image_max_upload_dimension_enabled'] );
	}

	public function test_sanitize_settings_max_upload_dimension_is_clamped_between_300_and_10000() {
		$this->post_settings( array( 'image_max_upload_dimension' => '300' ) );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertSame( 300, $sanitized['image_max_upload_dimension'] );

		$this->post_settings( array( 'image_max_upload_dimension' => '99999' ) );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertSame( 10000, $sanitized['image_max_upload_dimension'] );
	}

	public function test_sanitize_settings_max_upload_dimension_falls_back_to_2048_when_too_small() {
		$this->post_settings( array( 'image_max_upload_dimension' => '299' ) );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertSame( 2048, $sanitized['image_max_upload_dimension'] );

		$this->post_settings( array( 'image_max_upload_dimension' => '' ) );
		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );
		$this->assertSame( 2048, $sanitized['image_max_upload_dimension'] );
	}

	public function test_sanitize_settings_disabled_sizes_are_filtered_to_registered_names_only() {
		$this->post_settings(
			array(
				'image_sizes_config' => wp_json_encode(
					array(
						'disabled' => array( 'thumbnail', 'totally-unregistered-size' ),
					)
				),
			)
		);

		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );

		$this->assertSame( array( 'thumbnail' ), $sanitized['image_sizes_disabled'] );
	}

	public function test_sanitize_settings_overrides_are_sanitized_and_unregistered_sizes_discarded() {
		add_image_size( 'frbl_extra_size', 640, 480, false );
		$this->registered_extra_sizes[] = 'frbl_extra_size';

		$this->post_settings(
			array(
				'image_sizes_config' => wp_json_encode(
					array(
						'overrides' => array(
							'frbl_extra_size'            => array(
								'width'  => '800',
								'height' => '600',
								'crop'   => '1',
							),
							'totally-unregistered-size' => array(
								'width'  => 10,
								'height' => 10,
							),
						),
					)
				),
			)
		);

		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );

		$this->assertArrayHasKey( 'frbl_extra_size', $sanitized['image_sizes_overrides'] );
		$this->assertSame(
			array(
				'width'  => 800,
				'height' => 600,
				'crop'   => true,
			),
			$sanitized['image_sizes_overrides']['frbl_extra_size']
		);
		$this->assertArrayNotHasKey( 'totally-unregistered-size', $sanitized['image_sizes_overrides'] );
	}

	public function test_sanitize_settings_custom_sizes_are_sanitized_and_entries_without_a_name_are_discarded() {
		$this->post_settings(
			array(
				'image_sizes_config' => wp_json_encode(
					array(
						'custom' => array(
							array(
								'name'           => 'frbl_custom_size',
								'width'          => '300',
								'height'         => '200',
								'crop'           => '1',
								'label'          => 'My <b>Custom</b> Size',
								'show_in_picker' => '1',
							),
							array(
								'width'  => 50,
								'height' => 50,
							),
						),
					)
				),
			)
		);

		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );

		$this->assertCount( 1, $sanitized['image_sizes_custom'], 'An entry with no name must be discarded.' );
		$this->assertSame(
			array(
				'name'           => 'frbl_custom_size',
				'width'          => 300,
				'height'         => 200,
				'crop'           => true,
				'label'          => 'My Custom Size',
				'show_in_picker' => true,
			),
			$sanitized['image_sizes_custom'][0]
		);
	}

	public function test_sanitize_settings_core_size_overrides_update_the_core_size_options_directly() {
		$this->post_settings(
			array(
				'image_sizes_config' => wp_json_encode(
					array(
						'overrides' => array(
							'thumbnail' => array(
								'width'  => 130,
								'height' => 150,
								'crop'   => true,
							),
						),
					)
				),
			)
		);

		$this->admin->sanitize_settings( array(), 'frontblocks_settings' );

		$this->assertSame( 130, (int) get_option( 'thumbnail_size_w' ) );
		$this->assertSame( 150, (int) get_option( 'thumbnail_size_h' ) );
		$this->assertTrue( (bool) get_option( 'thumbnail_crop' ) );
	}

	public function test_sanitize_settings_no_longer_persists_the_removed_picture_element_option() {
		$this->post_settings(
			array(
				'enable_image_management'  => '1',
				'image_format_use_picture' => '1',
			)
		);

		$sanitized = $this->admin->sanitize_settings( array(), 'frontblocks_settings' );

		$this->assertArrayNotHasKey(
			'image_format_use_picture',
			$sanitized,
			'The <picture> element support was removed; this legacy option must never be persisted again.'
		);
	}

	/*
	 * ---------------------------------------------------------------
	 * enqueue_assets()
	 * ---------------------------------------------------------------
	 */

	public function test_enqueue_assets_only_enqueues_on_its_own_settings_page() {
		$this->admin->enqueue_assets( 'some_other_admin_page' );
		$this->assertFalse( wp_script_is( 'frontblocks-image-management', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'frontblocks-image-management', 'enqueued' ) );

		$this->admin->enqueue_assets( 'appearance_page_frontblocks-settings' );
		$this->assertTrue( wp_script_is( 'frontblocks-image-management', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'frontblocks-image-management', 'enqueued' ) );

		wp_dequeue_script( 'frontblocks-image-management' );
		wp_deregister_script( 'frontblocks-image-management' );
		wp_dequeue_style( 'frontblocks-image-management' );
		wp_deregister_style( 'frontblocks-image-management' );
	}

	/*
	 * ---------------------------------------------------------------
	 * AJAX handlers
	 * ---------------------------------------------------------------
	 */

	/**
	 * Set up a valid AJAX context: `wp_doing_ajax()` forced true (so
	 * wp_die()/wp_send_json_*() go through the test suite's WPDieException
	 * handler instead of a raw `die()` that would kill the test process),
	 * plus a nonce for the given user.
	 *
	 * @param int $user_id User the nonce (and the request) should be attributed to.
	 * @return void
	 */
	private function set_up_valid_ajax_context( $user_id ) {
		wp_set_current_user( $user_id );
		add_filter( 'wp_doing_ajax', '__return_true' );
		$nonce             = wp_create_nonce( 'frbl_image_management' );
		$_POST['nonce']    = $nonce;
		$_REQUEST['nonce'] = $nonce;
	}

	/**
	 * Run an AJAX callback, expecting it to end in wp_die() (as every
	 * checked/successful path in this class does via check_ajax_referer()
	 * or wp_send_json_*()), and return its decoded JSON body.
	 *
	 * @param callable $callback Zero-arg callback invoking the AJAX method.
	 * @return array{body: mixed, exception: WPDieException}
	 */
	private function run_ajax_expecting_die( $callback ) {
		ob_start();
		try {
			$callback();
			$this->fail( 'Expected the AJAX callback to call wp_die().' );
		} catch ( WPDieException $exception ) {
			$output = ob_get_clean();
			return array(
				'body'      => json_decode( $output, true ),
				'exception' => $exception,
			);
		}
	}

	public function test_ajax_handlers_reject_requests_without_a_valid_nonce() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		add_filter( 'wp_doing_ajax', '__return_true' );
		$_POST['nonce']    = 'not-a-valid-nonce';
		$_REQUEST['nonce'] = 'not-a-valid-nonce';

		foreach ( array( 'ajax_list_attachment_ids', 'ajax_start_bulk_job', 'ajax_bulk_job_status' ) as $method ) {
			try {
				$this->admin->{$method}();
				$this->fail( "Expected {$method}() to call wp_die() when the nonce is invalid." );
			} catch ( WPDieException $exception ) {
				$this->assertSame( 403, $exception->getCode() );
			}
		}
	}

	public function test_ajax_handlers_reject_users_without_edit_theme_options_capability() {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->set_up_valid_ajax_context( $subscriber_id );

		foreach ( array( 'ajax_list_attachment_ids', 'ajax_start_bulk_job', 'ajax_bulk_job_status' ) as $method ) {
			$result = $this->run_ajax_expecting_die( array( $this->admin, $method ) );
			$this->assertFalse( $result['body']['success'], "{$method}() must report failure for a user without edit_theme_options." );
		}
	}

	public function test_ajax_list_attachment_ids_returns_only_image_attachments() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->set_up_valid_ajax_context( $admin_id );

		$image_id_1 = self::factory()->attachment->create_object(
			array(
				'file'           => 'frbl-list-test-1.jpg',
				'post_mime_type' => 'image/jpeg',
				'post_type'      => 'attachment',
			)
		);
		$image_id_2 = self::factory()->attachment->create_object(
			array(
				'file'           => 'frbl-list-test-2.png',
				'post_mime_type' => 'image/png',
				'post_type'      => 'attachment',
			)
		);
		$non_image_id = self::factory()->attachment->create_object(
			array(
				'file'           => 'frbl-list-test.pdf',
				'post_mime_type' => 'application/pdf',
				'post_type'      => 'attachment',
			)
		);
		$this->created_attachment_ids = array_merge( $this->created_attachment_ids, array( $image_id_1, $image_id_2, $non_image_id ) );

		$result = $this->run_ajax_expecting_die( array( $this->admin, 'ajax_list_attachment_ids' ) );

		$this->assertTrue( $result['body']['success'] );
		$ids = $result['body']['data']['ids'];
		$this->assertContains( $image_id_1, $ids );
		$this->assertContains( $image_id_2, $ids );
		$this->assertNotContains( $non_image_id, $ids );
	}

	public function test_ajax_start_bulk_job_rejects_an_invalid_job_type() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->set_up_valid_ajax_context( $admin_id );

		$_POST['job_type'] = 'not-a-real-job-type';

		$result = $this->run_ajax_expecting_die( array( $this->admin, 'ajax_start_bulk_job' ) );

		$this->assertFalse( $result['body']['success'] );
	}

	public function test_ajax_start_bulk_job_schedules_one_action_per_image_attachment() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->set_up_valid_ajax_context( $admin_id );

		$image_id_1   = $this->create_real_image_attachment();
		$image_id_2   = $this->create_real_image_attachment();
		$non_image_id = self::factory()->attachment->create_object(
			array(
				'file'           => 'frbl-job-test.pdf',
				'post_mime_type' => 'application/pdf',
				'post_type'      => 'attachment',
			)
		);
		$this->created_attachment_ids[] = $non_image_id;

		$_POST['job_type'] = 'regenerate';

		$result = $this->run_ajax_expecting_die( array( $this->admin, 'ajax_start_bulk_job' ) );

		$this->assertTrue( $result['body']['success'] );
		$job_id = $result['body']['data']['job_id'];
		$this->assertNotEmpty( $job_id );
		$this->assertGreaterThanOrEqual( 2, $result['body']['data']['total'], 'Total must count at least the two image attachments created for this test.' );

		$scheduled = as_get_scheduled_actions(
			array(
				'group'  => ImageManagement::JOB_GROUP,
				'status' => \ActionScheduler_Store::STATUS_PENDING,
			)
		);
		$scheduled_attachment_ids = array_map(
			function ( $action ) {
				$args = $action->get_args();
				return $args[1];
			},
			$scheduled
		);
		$this->assertContains( $image_id_1, $scheduled_attachment_ids );
		$this->assertContains( $image_id_2, $scheduled_attachment_ids );
		$this->assertNotContains( $non_image_id, $scheduled_attachment_ids, 'A non-image attachment must never be scheduled.' );

		$progress = get_option( 'frbl_image_job_' . $job_id );
		$this->assertSame( $result['body']['data']['total'], $progress['total'] );
		$this->assertSame( 0, $progress['done'] );
	}

	public function test_ajax_bulk_job_status_rejects_an_unknown_job_id() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->set_up_valid_ajax_context( $admin_id );

		$_POST['job_id'] = 'does-not-exist';

		$result = $this->run_ajax_expecting_die( array( $this->admin, 'ajax_bulk_job_status' ) );

		$this->assertFalse( $result['body']['success'] );
	}

	public function test_ajax_bulk_job_status_reports_progress() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->set_up_valid_ajax_context( $admin_id );

		update_option( 'frbl_image_job_test-job', array( 'total' => 5, 'done' => 2 ) );
		$_POST['job_id'] = 'test-job';

		$result = $this->run_ajax_expecting_die( array( $this->admin, 'ajax_bulk_job_status' ) );

		$this->assertTrue( $result['body']['success'] );
		$this->assertSame( 5, $result['body']['data']['total'] );
		$this->assertSame( 2, $result['body']['data']['done'] );
		$this->assertNotFalse( get_option( 'frbl_image_job_test-job' ), 'An in-progress job must not be deleted yet.' );
	}

	public function test_ajax_bulk_job_status_deletes_the_progress_option_once_the_job_is_complete() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->set_up_valid_ajax_context( $admin_id );

		update_option( 'frbl_image_job_test-job', array( 'total' => 3, 'done' => 3 ) );
		$_POST['job_id'] = 'test-job';

		$result = $this->run_ajax_expecting_die( array( $this->admin, 'ajax_bulk_job_status' ) );

		$this->assertTrue( $result['body']['success'] );
		$this->assertFalse( get_option( 'frbl_image_job_test-job' ), 'A completed job must have its progress option cleaned up.' );
	}

	public function test_process_bulk_item_regenerate_generates_metadata_for_a_real_image() {
		$attachment_id = $this->create_real_image_attachment();

		$this->admin->process_bulk_item( 'regenerate', $attachment_id, 'irrelevant-job-id' );

		$this->assertNotEmpty( wp_get_attachment_metadata( $attachment_id ) );
	}

	public function test_process_bulk_item_regenerate_does_nothing_for_a_missing_file() {
		$attachment_id = self::factory()->attachment->create_object(
			array(
				'file'           => 'frbl-does-not-exist-on-disk.jpg',
				'post_mime_type' => 'image/jpeg',
				'post_type'      => 'attachment',
			)
		);
		$this->created_attachment_ids[] = $attachment_id;

		// Must not throw/warn even though the underlying file is missing.
		$this->admin->process_bulk_item( 'regenerate', $attachment_id, 'irrelevant-job-id' );

		$this->assertEmpty( wp_get_attachment_metadata( $attachment_id ) );
	}

	public function test_process_bulk_item_convert_generates_variants_when_a_supported_format_target_is_configured() {
		$webp_supported = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
		if ( ! $webp_supported ) {
			$this->markTestSkipped( 'This environment does not support generating WebP images.' );
		}

		update_option( 'frontblocks_settings', array( 'image_format_target' => 'webp' ) );

		$attachment_id = $this->create_real_image_attachment();
		wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) );

		$this->admin->process_bulk_item( 'convert', $attachment_id, 'irrelevant-job-id' );

		$metadata = wp_get_attachment_metadata( $attachment_id );
		$this->assertNotEmpty( $metadata[ FrontendImageManagement::VARIANTS_META_KEY ]['full'] ?? null );
	}

	public function test_process_bulk_item_cleanup_removes_files_for_a_disabled_size() {
		$attachment_id = $this->create_real_image_attachment();
		$metadata       = wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		if ( empty( $metadata['sizes']['thumbnail']['file'] ) ) {
			$this->markTestSkipped( 'No thumbnail size was generated for the test image on this environment.' );
		}

		update_option( 'frontblocks_settings', array( 'image_sizes_disabled' => array( 'thumbnail' ) ) );

		$dir            = trailingslashit( dirname( get_attached_file( $attachment_id ) ) );
		$thumbnail_path = $dir . $metadata['sizes']['thumbnail']['file'];
		$this->assertFileExists( $thumbnail_path, 'Sanity check: the thumbnail file must exist before cleanup.' );

		$this->admin->process_bulk_item( 'cleanup', $attachment_id, 'irrelevant-job-id' );

		$this->assertFileDoesNotExist( $thumbnail_path, 'The disabled size file must have been deleted.' );
	}

	public function test_process_bulk_item_increments_the_jobs_progress_option() {
		$attachment_id = $this->create_real_image_attachment();
		update_option( 'frbl_image_job_test-job', array( 'total' => 2, 'done' => 0 ) );

		$this->admin->process_bulk_item( 'regenerate', $attachment_id, 'test-job' );
		$progress = get_option( 'frbl_image_job_test-job' );
		$this->assertSame( 1, $progress['done'] );

		$this->admin->process_bulk_item( 'regenerate', $attachment_id, 'test-job' );
		$progress = get_option( 'frbl_image_job_test-job' );
		$this->assertSame( 2, $progress['done'] );
	}

	public function test_process_bulk_item_does_not_increment_progress_past_the_total() {
		$attachment_id = $this->create_real_image_attachment();
		update_option( 'frbl_image_job_test-job', array( 'total' => 1, 'done' => 1 ) );

		$this->admin->process_bulk_item( 'regenerate', $attachment_id, 'test-job' );

		$progress = get_option( 'frbl_image_job_test-job' );
		$this->assertSame( 1, $progress['done'] );
	}

	public function test_process_bulk_item_silently_ignores_an_unknown_job_id() {
		$attachment_id = $this->create_real_image_attachment();

		// Must not throw/warn even though no progress option exists for this job id.
		$this->admin->process_bulk_item( 'regenerate', $attachment_id, 'a-job-id-with-no-progress-option' );

		$this->assertNotEmpty( wp_get_attachment_metadata( $attachment_id ) );
	}
}
