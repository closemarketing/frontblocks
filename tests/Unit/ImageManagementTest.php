<?php
/**
 * Tests for FrontBlocks\Frontend\ImageManagement.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\ImageManagement;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class ImageManagementTest extends TestCase {

	/**
	 * @var ImageManagement
	 */
	private $image_management;

	/**
	 * Attachment IDs created during a test, cleaned up in tear_down().
	 *
	 * @var int[]
	 */
	private $created_attachments = array();

	/**
	 * Extra on-disk files created during a test, cleaned up in tear_down().
	 *
	 * @var string[]
	 */
	private $created_files = array();

	public function set_up() {
		parent::set_up();
		$this->image_management = new ImageManagement();
	}

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		unset( $_SERVER['HTTP_ACCEPT'] );

		foreach ( $this->created_attachments as $attachment_id ) {
			wp_delete_attachment( $attachment_id, true );
		}
		$this->created_attachments = array();

		foreach ( $this->created_files as $file ) {
			if ( file_exists( $file ) ) {
				wp_delete_file( $file );
			}
		}
		$this->created_files = array();

		parent::tear_down();
	}

	/**
	 * Invoke a private/protected method via Reflection.
	 *
	 * @param string $method Method name.
	 * @param array  $args   Arguments.
	 * @return mixed
	 */
	private function call_private( $method, $args = array() ) {
		$reflection = new ReflectionMethod( ImageManagement::class, $method );
		$reflection->setAccessible( true );
		return $reflection->invokeArgs( $this->image_management, $args );
	}

	/**
	 * Create a minimal but real JPEG file on disk using GD.
	 *
	 * @param string $filename Basename to use, e.g. 'frbl-test.jpg'.
	 * @return string Absolute path to the created file.
	 */
	private function create_real_jpeg( $filename = 'frbl-test.jpg' ) {
		$path  = trailingslashit( get_temp_dir() ) . $filename;
		$image = imagecreatetruecolor( 300, 200 );
		imagefill( $image, 0, 0, imagecolorallocate( $image, 100, 150, 200 ) );
		imagejpeg( $image, $path );
		imagedestroy( $image );

		$this->created_files[] = $path;

		return $path;
	}

	/**
	 * Upload a real JPEG as an attachment, and generate real metadata for it
	 * (so it has actual intermediate size files on disk).
	 *
	 * @return array array( $attachment_id, $metadata ).
	 */
	private function create_uploaded_attachment_with_metadata() {
		$file          = $this->create_real_jpeg( 'frbl-upload-' . uniqid() . '.jpg' );
		$attachment_id = self::factory()->attachment->create_upload_object( $file );
		$this->created_attachments[] = $attachment_id;

		$metadata = wp_generate_attachment_metadata( $attachment_id, get_attached_file( $attachment_id ) );
		wp_update_attachment_metadata( $attachment_id, $metadata );

		return array( $attachment_id, $metadata );
	}

	// ------------------------------------------------------------------
	// is_enabled()
	// ------------------------------------------------------------------

	public function test_is_enabled_is_false_by_default() {
		$this->assertFalse( $this->image_management->is_enabled() );
	}

	public function test_is_enabled_reflects_the_option() {
		update_option( 'frontblocks_settings', array( 'enable_image_management' => true ) );
		$this->assertTrue( $this->image_management->is_enabled() );

		update_option( 'frontblocks_settings', array( 'enable_image_management' => false ) );
		$this->assertFalse( $this->image_management->is_enabled() );
	}

	// ------------------------------------------------------------------
	// register_custom_and_override_sizes()
	// ------------------------------------------------------------------

	public function test_register_custom_and_override_sizes_does_nothing_when_disabled() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => false,
				'image_sizes_custom'      => array(
					array(
						'name'   => 'frbl_disabled_custom',
						'width'  => 111,
						'height' => 111,
					),
				),
			)
		);

		$this->image_management->register_custom_and_override_sizes();

		$sizes = wp_get_additional_image_sizes();
		$this->assertArrayNotHasKey( 'frbl_disabled_custom', $sizes );
	}

	public function test_register_custom_and_override_sizes_applies_non_core_overrides() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_sizes_overrides'   => array(
					'frbl_override_target' => array(
						'width'  => 321,
						'height' => 654,
						'crop'   => true,
					),
				),
			)
		);

		$this->image_management->register_custom_and_override_sizes();

		$sizes = wp_get_additional_image_sizes();
		$this->assertArrayHasKey( 'frbl_override_target', $sizes );
		$this->assertSame( 321, $sizes['frbl_override_target']['width'] );
		$this->assertSame( 654, $sizes['frbl_override_target']['height'] );
		$this->assertTrue( $sizes['frbl_override_target']['crop'] );
	}

	public function test_register_custom_and_override_sizes_skips_core_size_overrides() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_sizes_overrides'   => array(
					'thumbnail' => array(
						'width'  => 999,
						'height' => 999,
					),
				),
			)
		);

		$this->image_management->register_custom_and_override_sizes();

		// Core sizes are never written into $_wp_additional_image_sizes.
		$sizes = wp_get_additional_image_sizes();
		$this->assertArrayNotHasKey( 'thumbnail', $sizes );
	}

	public function test_register_custom_and_override_sizes_registers_custom_sizes() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_sizes_custom'      => array(
					array(
						'name'   => 'frbl_custom_hero',
						'width'  => 1200,
						'height' => 500,
						'crop'   => true,
					),
				),
			)
		);

		$this->image_management->register_custom_and_override_sizes();

		$sizes = wp_get_additional_image_sizes();
		$this->assertArrayHasKey( 'frbl_custom_hero', $sizes );
		$this->assertSame( 1200, $sizes['frbl_custom_hero']['width'] );
		$this->assertSame( 500, $sizes['frbl_custom_hero']['height'] );
	}

	public function test_register_custom_and_override_sizes_ignores_custom_sizes_without_name() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_sizes_custom'      => array(
					array(
						'width'  => 400,
						'height' => 400,
					),
				),
			)
		);

		// Should not throw/warn, and should not register anything under an
		// empty-string key.
		$this->image_management->register_custom_and_override_sizes();

		$sizes = wp_get_additional_image_sizes();
		$this->assertArrayNotHasKey( '', $sizes );
	}

	// ------------------------------------------------------------------
	// filter_image_size_names_choose()
	// ------------------------------------------------------------------

	public function test_filter_image_size_names_choose_does_nothing_when_disabled() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => false,
				'image_sizes_custom'      => array(
					array(
						'name'           => 'frbl_picker_size',
						'show_in_picker' => true,
					),
				),
			)
		);

		$result = $this->image_management->filter_image_size_names_choose( array( 'thumbnail' => 'Thumbnail' ) );

		$this->assertArrayNotHasKey( 'frbl_picker_size', $result );
	}

	public function test_filter_image_size_names_choose_adds_only_picker_flagged_sizes() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_sizes_custom'      => array(
					array(
						'name'           => 'frbl_visible_size',
						'label'          => 'Visible Size',
						'show_in_picker' => true,
					),
					array(
						'name'           => 'frbl_hidden_size',
						'label'          => 'Hidden Size',
						'show_in_picker' => false,
					),
				),
			)
		);

		$result = $this->image_management->filter_image_size_names_choose( array( 'thumbnail' => 'Thumbnail' ) );

		$this->assertArrayHasKey( 'frbl_visible_size', $result );
		$this->assertSame( 'Visible Size', $result['frbl_visible_size'] );
		$this->assertArrayNotHasKey( 'frbl_hidden_size', $result );
		$this->assertArrayHasKey( 'thumbnail', $result );
	}

	public function test_filter_image_size_names_choose_uses_name_as_label_fallback() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_sizes_custom'      => array(
					array(
						'name'           => 'frbl_no_label_size',
						'show_in_picker' => true,
					),
				),
			)
		);

		$result = $this->image_management->filter_image_size_names_choose( array() );

		$this->assertSame( 'frbl_no_label_size', $result['frbl_no_label_size'] );
	}

	// ------------------------------------------------------------------
	// filter_intermediate_sizes_advanced() / filter_intermediate_sizes()
	// ------------------------------------------------------------------

	public function test_filter_intermediate_sizes_advanced_does_nothing_when_disabled() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => false,
				'image_sizes_disabled'    => array( 'medium' ),
			)
		);

		$sizes  = array(
			'thumbnail' => array( 'width' => 150 ),
			'medium'    => array( 'width' => 300 ),
		);
		$result = $this->image_management->filter_intermediate_sizes_advanced( $sizes );

		$this->assertSame( $sizes, $result );
	}

	public function test_filter_intermediate_sizes_advanced_removes_disabled_sizes() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_sizes_disabled'    => array( 'medium', 'large' ),
			)
		);

		$sizes = array(
			'thumbnail' => array( 'width' => 150 ),
			'medium'    => array( 'width' => 300 ),
			'large'     => array( 'width' => 1024 ),
		);

		$result = $this->image_management->filter_intermediate_sizes_advanced( $sizes );

		$this->assertArrayHasKey( 'thumbnail', $result );
		$this->assertArrayNotHasKey( 'medium', $result );
		$this->assertArrayNotHasKey( 'large', $result );
	}

	public function test_filter_intermediate_sizes_does_nothing_when_disabled() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => false,
				'image_sizes_disabled'    => array( 'medium' ),
			)
		);

		$sizes  = array( 'thumbnail', 'medium', 'large' );
		$result = $this->image_management->filter_intermediate_sizes( $sizes );

		$this->assertSame( $sizes, $result );
	}

	public function test_filter_intermediate_sizes_removes_disabled_sizes_from_plain_list() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_sizes_disabled'    => array( 'medium' ),
			)
		);

		$sizes  = array( 'thumbnail', 'medium', 'large' );
		$result = $this->image_management->filter_intermediate_sizes( $sizes );

		$this->assertSame( array( 'thumbnail', 'large' ), $result );
	}

	// ------------------------------------------------------------------
	// filter_big_image_size_threshold()
	// ------------------------------------------------------------------

	public function test_big_image_size_threshold_returns_core_default_when_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_image_management' => false ) );

		$result = $this->image_management->filter_big_image_size_threshold( 2560 );

		$this->assertSame( 2560, $result );
	}

	public function test_big_image_size_threshold_returns_false_when_explicitly_disabled() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management'             => true,
				'image_max_upload_dimension_enabled'  => false,
			)
		);

		$result = $this->image_management->filter_big_image_size_threshold( 2560 );

		$this->assertFalse( $result );
	}

	public function test_big_image_size_threshold_defaults_to_2048_when_never_saved() {
		// The option key was simply never set (as opposed to explicitly
		// false) -- must NOT be treated as disabled.
		update_option(
			'frontblocks_settings',
			array( 'enable_image_management' => true )
		);

		$result = $this->image_management->filter_big_image_size_threshold( 2560 );

		$this->assertSame( 2048, $result );
	}

	public function test_big_image_size_threshold_uses_configured_value() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management'            => true,
				'image_max_upload_dimension_enabled' => true,
				'image_max_upload_dimension'         => 3000,
			)
		);

		$result = $this->image_management->filter_big_image_size_threshold( 2560 );

		$this->assertSame( 3000, $result );
	}

	// ------------------------------------------------------------------
	// get_quality_settings()
	// ------------------------------------------------------------------

	public function test_get_quality_settings_defaults() {
		$result = ImageManagement::get_quality_settings( array() );

		$this->assertSame(
			array(
				'webp' => 82,
				'avif' => 60,
			),
			$result
		);
	}

	public function test_get_quality_settings_uses_saved_values() {
		$result = ImageManagement::get_quality_settings(
			array(
				'image_format_quality_webp' => 55,
				'image_format_quality_avif' => 40,
			)
		);

		$this->assertSame(
			array(
				'webp' => 55,
				'avif' => 40,
			),
			$result
		);
	}

	// ------------------------------------------------------------------
	// maybe_generate_modern_formats()
	// ------------------------------------------------------------------

	public function test_maybe_generate_modern_formats_does_nothing_when_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_image_management' => false ) );

		$metadata = array( 'file' => 'test.jpg' );
		$result   = $this->image_management->maybe_generate_modern_formats( $metadata, 123 );

		$this->assertSame( $metadata, $result );
	}

	public function test_maybe_generate_modern_formats_does_nothing_when_target_is_none() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_format_target'     => 'none',
			)
		);

		$metadata = array( 'file' => 'test.jpg' );
		$result   = $this->image_management->maybe_generate_modern_formats( $metadata, 123 );

		$this->assertSame( $metadata, $result );
	}

	public function test_maybe_generate_modern_formats_does_nothing_when_metadata_empty() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_format_target'     => 'webp',
			)
		);

		$result = $this->image_management->maybe_generate_modern_formats( array(), 123 );

		$this->assertSame( array(), $result );
	}

	public function test_maybe_generate_modern_formats_invokes_generation_for_a_real_attachment() {
		update_option(
			'frontblocks_settings',
			array(
				'enable_image_management' => true,
				'image_format_target'     => 'webp',
			)
		);

		list( $attachment_id, $metadata ) = $this->create_uploaded_attachment_with_metadata();

		$result = $this->image_management->maybe_generate_modern_formats( $metadata, $attachment_id );

		// Regardless of whether the test environment's image editor actually
		// supports WebP encoding, the metadata must at least carry the
		// variants key after going through the real generation path (it may
		// simply be empty when unsupported).
		$this->assertArrayHasKey( ImageManagement::VARIANTS_META_KEY, $result );

		if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			$this->assertNotEmpty( $result[ ImageManagement::VARIANTS_META_KEY ] );
			$this->assertArrayHasKey( 'full', $result[ ImageManagement::VARIANTS_META_KEY ] );
			$this->assertArrayHasKey( 'image/webp', $result[ ImageManagement::VARIANTS_META_KEY ]['full'] );

			$variant_file = dirname( get_attached_file( $attachment_id ) ) . '/' . $result[ ImageManagement::VARIANTS_META_KEY ]['full']['image/webp']['file'];
			$this->assertFileExists( $variant_file );
		} else {
			$this->assertSame( array(), $result[ ImageManagement::VARIANTS_META_KEY ] );
		}
	}

	// ------------------------------------------------------------------
	// best_mime_for_request() (private, via Reflection)
	// ------------------------------------------------------------------

	public function test_best_mime_for_request_returns_avif_when_accepted_and_available() {
		$_SERVER['HTTP_ACCEPT'] = 'text/html,image/avif,image/webp,*/*';

		$size_variants = array(
			'image/avif' => array( 'file' => 'test.avif' ),
			'image/webp' => array( 'file' => 'test.webp' ),
		);

		$result = $this->call_private( 'best_mime_for_request', array( $size_variants ) );

		$this->assertSame( 'image/avif', $result );
	}

	public function test_best_mime_for_request_falls_back_to_webp_without_avif_accept_header() {
		// Wildcard accept must NOT be treated as AVIF support -- this is the
		// strict-negotiation requirement.
		$_SERVER['HTTP_ACCEPT'] = 'image/webp,*/*';

		$size_variants = array(
			'image/avif' => array( 'file' => 'test.avif' ),
			'image/webp' => array( 'file' => 'test.webp' ),
		);

		$result = $this->call_private( 'best_mime_for_request', array( $size_variants ) );

		$this->assertSame( 'image/webp', $result );
	}

	public function test_best_mime_for_request_returns_webp_when_no_avif_variant_exists() {
		$_SERVER['HTTP_ACCEPT'] = 'image/avif,image/webp';

		$size_variants = array(
			'image/webp' => array( 'file' => 'test.webp' ),
		);

		$result = $this->call_private( 'best_mime_for_request', array( $size_variants ) );

		$this->assertSame( 'image/webp', $result );
	}

	public function test_best_mime_for_request_returns_null_when_no_variants_exist() {
		$_SERVER['HTTP_ACCEPT'] = 'image/avif,image/webp';

		$result = $this->call_private( 'best_mime_for_request', array( array() ) );

		$this->assertNull( $result );
	}

	// ------------------------------------------------------------------
	// accepts() (private, via Reflection)
	// ------------------------------------------------------------------

	public function test_accepts_matches_literal_mime_type() {
		$_SERVER['HTTP_ACCEPT'] = 'text/html,image/avif,*/*';

		$this->assertTrue( $this->call_private( 'accepts', array( 'image/avif' ) ) );
	}

	public function test_accepts_does_not_match_generic_image_wildcard() {
		$_SERVER['HTTP_ACCEPT'] = 'image/*,text/html';

		$this->assertFalse( $this->call_private( 'accepts', array( 'image/avif' ) ) );
	}

	public function test_accepts_does_not_match_full_wildcard() {
		$_SERVER['HTTP_ACCEPT'] = '*/*';

		$this->assertFalse( $this->call_private( 'accepts', array( 'image/avif' ) ) );
	}

	public function test_accepts_returns_false_when_no_accept_header_present() {
		unset( $_SERVER['HTTP_ACCEPT'] );

		$this->assertFalse( $this->call_private( 'accepts', array( 'image/avif' ) ) );
	}

	// ------------------------------------------------------------------
	// filter_content_img_tag()
	// ------------------------------------------------------------------

	public function test_filter_content_img_tag_returns_tag_unchanged_when_disabled() {
		update_option( 'frontblocks_settings', array( 'enable_image_management' => false ) );

		$tag = '<img src="https://example.com/wp-content/uploads/test.jpg" />';
		$this->assertSame( $tag, $this->image_management->filter_content_img_tag( $tag, 'the_content', 123 ) );
	}

	public function test_filter_content_img_tag_returns_tag_unchanged_without_variants() {
		update_option( 'frontblocks_settings', array( 'enable_image_management' => true ) );

		list( $attachment_id, $metadata ) = $this->create_uploaded_attachment_with_metadata();

		$src = wp_get_attachment_url( $attachment_id );
		$tag = '<img src="' . esc_url( $src ) . '" />';

		$result = $this->image_management->filter_content_img_tag( $tag, 'the_content', $attachment_id );

		$this->assertSame( $tag, $result );
	}

	public function test_filter_content_img_tag_rewrites_src_and_srcset_to_matching_mime() {
		update_option( 'frontblocks_settings', array( 'enable_image_management' => true ) );

		list( $attachment_id, $metadata ) = $this->create_uploaded_attachment_with_metadata();

		$file     = get_attached_file( $attachment_id );
		$dir      = trailingslashit( dirname( $file ) );
		$full_url = trailingslashit( dirname( wp_get_attachment_url( $attachment_id ) ) );

		// Fake variant files so this test doesn't depend on the test
		// environment actually supporting WebP/AVIF encoding.
		$webp_basename = preg_replace( '/\.[^.]+$/', '.webp', basename( $file ) );
		$webp_path     = $dir . $webp_basename;
		file_put_contents( $webp_path, 'fake-webp-bytes' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$this->created_files[] = $webp_path;

		$metadata[ ImageManagement::VARIANTS_META_KEY ] = array(
			'full' => array(
				'image/webp' => array(
					'file'     => $webp_basename,
					'filesize' => 123,
				),
			),
		);
		wp_update_attachment_metadata( $attachment_id, $metadata );

		$_SERVER['HTTP_ACCEPT'] = 'text/html,*/*'; // No AVIF support advertised -> expect WebP.

		$src = wp_get_attachment_url( $attachment_id );
		$tag = '<img src="' . esc_url( $src ) . '" srcset="' . esc_attr( $src . ' 300w' ) . '" />';

		$result = $this->image_management->filter_content_img_tag( $tag, 'the_content', $attachment_id );

		$this->assertStringContainsString( $webp_basename, $result );
		$this->assertStringNotContainsString( basename( $file ), $result );
		$this->assertStringNotContainsString( '<picture', $result );
	}

	// ------------------------------------------------------------------
	// match_variant_size() (private, via Reflection)
	// ------------------------------------------------------------------

	public function test_match_variant_size_returns_full_for_main_file() {
		$metadata = array(
			'file'  => '2026/09/main-image.jpg',
			'sizes' => array(
				'thumbnail' => array( 'file' => 'main-image-150x150.jpg' ),
			),
		);

		$result = $this->call_private(
			'match_variant_size',
			array( 'https://example.com/wp-content/uploads/2026/09/main-image.jpg', $metadata )
		);

		$this->assertSame( 'full', $result );
	}

	public function test_match_variant_size_returns_size_name_for_intermediate_size() {
		$metadata = array(
			'file'  => '2026/09/main-image.jpg',
			'sizes' => array(
				'thumbnail' => array( 'file' => 'main-image-150x150.jpg' ),
				'medium'    => array( 'file' => 'main-image-300x200.jpg' ),
			),
		);

		$result = $this->call_private(
			'match_variant_size',
			array( 'https://example.com/wp-content/uploads/2026/09/main-image-300x200.jpg', $metadata )
		);

		$this->assertSame( 'medium', $result );
	}

	public function test_match_variant_size_returns_null_when_no_match() {
		$metadata = array(
			'file'  => '2026/09/main-image.jpg',
			'sizes' => array(
				'thumbnail' => array( 'file' => 'main-image-150x150.jpg' ),
			),
		);

		$result = $this->call_private(
			'match_variant_size',
			array( 'https://example.com/wp-content/uploads/2026/09/unrelated-image.jpg', $metadata )
		);

		$this->assertNull( $result );
	}

	// ------------------------------------------------------------------
	// delete_variant_files() / cleanup_disabled_size_files()
	// ------------------------------------------------------------------

	public function test_delete_variant_files_removes_files_on_disk_when_attachment_is_deleted() {
		list( $attachment_id, $metadata ) = $this->create_uploaded_attachment_with_metadata();

		$file          = get_attached_file( $attachment_id );
		$dir           = trailingslashit( dirname( $file ) );
		$webp_basename = preg_replace( '/\.[^.]+$/', '.webp', basename( $file ) );
		$webp_path     = $dir . $webp_basename;
		file_put_contents( $webp_path, 'fake-webp-bytes' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		$metadata[ ImageManagement::VARIANTS_META_KEY ] = array(
			'full' => array(
				'image/webp' => array(
					'file'     => $webp_basename,
					'filesize' => 123,
				),
			),
		);
		wp_update_attachment_metadata( $attachment_id, $metadata );

		$this->assertFileExists( $webp_path );

		$this->image_management->delete_variant_files( $attachment_id );

		$this->assertFileDoesNotExist( $webp_path );

		// Prevent tear_down() from trying to delete a file that's already gone.
		$this->created_files = array_diff( $this->created_files, array( $webp_path ) );
	}

	public function test_delete_variant_files_does_nothing_without_variants() {
		list( $attachment_id, $metadata ) = $this->create_uploaded_attachment_with_metadata();

		// Should simply return without error/warning when there are no
		// variants recorded in the metadata.
		$this->image_management->delete_variant_files( $attachment_id );

		$this->assertTrue( true, 'delete_variant_files() completed without error for an attachment without variants.' );
	}

	public function test_cleanup_disabled_size_files_removes_disabled_size_files_and_updates_metadata() {
		list( $attachment_id, $metadata ) = $this->create_uploaded_attachment_with_metadata();

		$this->assertNotEmpty( $metadata['sizes'], 'Test image must generate at least one intermediate size to exercise cleanup.' );
		$size_names = array_keys( $metadata['sizes'] );
		$size_name  = $size_names[0];
		$size_file  = $metadata['sizes'][ $size_name ]['file'];

		$file     = get_attached_file( $attachment_id );
		$dir      = trailingslashit( dirname( $file ) );
		$size_path = $dir . $size_file;

		$this->assertFileExists( $size_path );

		update_option(
			'frontblocks_settings',
			array( 'image_sizes_disabled' => array( $size_name ) )
		);

		$result = ImageManagement::cleanup_disabled_size_files( $attachment_id );

		$this->assertTrue( $result );
		$this->assertFileDoesNotExist( $size_path );

		$updated_metadata = wp_get_attachment_metadata( $attachment_id );
		$this->assertArrayNotHasKey( $size_name, $updated_metadata['sizes'] );
	}

	public function test_cleanup_disabled_size_files_returns_false_when_no_sizes_disabled() {
		list( $attachment_id, $metadata ) = $this->create_uploaded_attachment_with_metadata();

		update_option( 'frontblocks_settings', array( 'image_sizes_disabled' => array() ) );

		$result = ImageManagement::cleanup_disabled_size_files( $attachment_id );

		$this->assertFalse( $result );
	}

	public function test_cleanup_disabled_size_files_returns_false_for_unknown_size_name() {
		list( $attachment_id, $metadata ) = $this->create_uploaded_attachment_with_metadata();

		update_option(
			'frontblocks_settings',
			array( 'image_sizes_disabled' => array( 'a_size_that_was_never_generated' ) )
		);

		$result = ImageManagement::cleanup_disabled_size_files( $attachment_id );

		$this->assertFalse( $result );
	}

	// ------------------------------------------------------------------
	// estimate_disk_usage_by_size()
	// ------------------------------------------------------------------

	public function test_estimate_disk_usage_by_size_totals_bytes_for_existing_files_and_zero_for_missing() {
		list( $attachment_id, $metadata ) = $this->create_uploaded_attachment_with_metadata();

		$this->assertNotEmpty( $metadata['sizes'], 'Test image must generate at least one intermediate size.' );
		$size_names = array_keys( $metadata['sizes'] );
		$known_size = $size_names[0];

		$totals = ImageManagement::estimate_disk_usage_by_size( array( $known_size, 'frbl_size_without_any_file' ) );

		$this->assertArrayHasKey( $known_size, $totals );
		$this->assertGreaterThan( 0, $totals[ $known_size ] );

		$this->assertArrayHasKey( 'frbl_size_without_any_file', $totals );
		$this->assertSame( 0, $totals['frbl_size_without_any_file'] );
	}

	public function test_estimate_disk_usage_by_size_returns_zeros_when_no_attachments_exist() {
		// No attachments have been created in this test, so the sample query
		// should find nothing and every requested size should total to zero.
		$totals = ImageManagement::estimate_disk_usage_by_size( array( 'thumbnail', 'medium' ) );

		$this->assertSame(
			array(
				'thumbnail' => 0,
				'medium'    => 0,
			),
			$totals
		);
	}
}
