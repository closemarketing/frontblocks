<?php
/**
 * Tests for FrontBlocks\Frontend\SvgUpload.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\SvgUpload;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class SvgUploadTest extends TestCase {

	/**
	 * @var SvgUpload
	 */
	private $svg_upload;

	public function set_up() {
		parent::set_up();
		$this->svg_upload = new SvgUpload();
	}

	public function test_constructor_registers_upload_mime_filter() {
		$this->assertNotFalse( has_filter( 'upload_mimes', array( $this->svg_upload, 'add_svg_mime' ) ) );
	}

	public function test_constructor_registers_filetype_check_filter() {
		$this->assertNotFalse( has_filter( 'wp_check_filetype_and_ext', array( $this->svg_upload, 'fix_svg_mime_check' ) ) );
	}

	public function test_constructor_registers_upload_prefilter() {
		$this->assertNotFalse( has_filter( 'wp_handle_upload_prefilter', array( $this->svg_upload, 'sanitize_svg_upload' ) ) );
	}

	public function test_constructor_registers_media_library_fix_filter() {
		$this->assertNotFalse( has_filter( 'wp_prepare_attachment_for_js', array( $this->svg_upload, 'fix_svg_in_media_library' ) ) );
	}

	public function test_add_svg_mime_adds_svg_and_svgz() {
		$mimes = $this->svg_upload->add_svg_mime( array( 'jpg' => 'image/jpeg' ) );

		$this->assertSame( 'image/svg+xml', $mimes['svg'] );
		$this->assertSame( 'image/svg+xml', $mimes['svgz'] );
		$this->assertSame( 'image/jpeg', $mimes['jpg'] );
	}

	public function test_fix_svg_mime_check_leaves_already_detected_types_untouched() {
		$data = array(
			'ext'  => 'jpg',
			'type' => 'image/jpeg',
		);

		$result = $this->svg_upload->fix_svg_mime_check( $data, '/tmp/whatever.jpg', 'whatever.jpg' );

		$this->assertSame( $data, $result );
	}

	public function test_fix_svg_mime_check_detects_svg_extension() {
		$data = array(
			'ext'  => false,
			'type' => false,
		);

		$result = $this->svg_upload->fix_svg_mime_check( $data, '/tmp/icon.svg', 'icon.svg' );

		$this->assertSame( 'svg', $result['ext'] );
		$this->assertSame( 'image/svg+xml', $result['type'] );
	}

	public function test_fix_svg_mime_check_detects_svgz_extension_case_insensitively() {
		$data = array(
			'ext'  => '',
			'type' => '',
		);

		$result = $this->svg_upload->fix_svg_mime_check( $data, '/tmp/icon.SVGZ', 'icon.SVGZ' );

		$this->assertSame( 'svgz', $result['ext'] );
		$this->assertSame( 'image/svg+xml', $result['type'] );
	}

	public function test_fix_svg_mime_check_leaves_non_svg_unresolved_types_untouched() {
		$data = array(
			'ext'  => false,
			'type' => false,
		);

		$result = $this->svg_upload->fix_svg_mime_check( $data, '/tmp/document.pdf', 'document.pdf' );

		$this->assertFalse( $result['ext'] );
		$this->assertFalse( $result['type'] );
	}

	public function test_sanitize_svg_upload_ignores_non_svg_files() {
		$file = array(
			'type'     => 'image/jpeg',
			'tmp_name' => '/tmp/does-not-matter.jpg',
		);

		$result = $this->svg_upload->sanitize_svg_upload( $file );

		$this->assertSame( $file, $result );
	}

	public function test_sanitize_svg_upload_rejects_upload_for_non_admin_users() {
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );

		$file = array(
			'type'     => 'image/svg+xml',
			'tmp_name' => '/tmp/icon.svg',
		);

		$result = $this->svg_upload->sanitize_svg_upload( $file );

		$this->assertArrayHasKey( 'error', $result );
		$this->assertStringContainsString( 'Permission denied', $result['error'] );
	}

	public function test_sanitize_svg_upload_strips_script_tags_and_event_handlers_for_admins() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$tmp_file      = tempnam( sys_get_temp_dir(), 'frbl-svg-test' );
		$malicious_svg = '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)">'
			. '<script>alert(1)</script>'
			. '<circle cx="5" cy="5" r="4" onclick="alert(2)" /></svg>';
		file_put_contents( $tmp_file, $malicious_svg ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$file = array(
			'type'     => 'image/svg+xml',
			'tmp_name' => $tmp_file,
		);

		$result = $this->svg_upload->sanitize_svg_upload( $file );

		$this->assertArrayNotHasKey( 'error', $result );

		$sanitized_content = file_get_contents( $tmp_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$this->assertStringNotContainsString( '<script', $sanitized_content );
		$this->assertStringNotContainsString( 'onload=', $sanitized_content );
		$this->assertStringNotContainsString( 'onclick=', $sanitized_content );
		$this->assertStringContainsString( '<circle', $sanitized_content );

		wp_delete_file( $tmp_file );
	}

	public function test_sanitize_svg_upload_rejects_non_svg_content_for_admins() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$tmp_file = tempnam( sys_get_temp_dir(), 'frbl-svg-test' );
		file_put_contents( $tmp_file, '<html><body>not an svg</body></html>' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$file = array(
			'type'     => 'image/svg+xml',
			'tmp_name' => $tmp_file,
		);

		$result = $this->svg_upload->sanitize_svg_upload( $file );

		$this->assertArrayHasKey( 'error', $result );
		$this->assertStringContainsString( 'Invalid SVG file', $result['error'] );

		wp_delete_file( $tmp_file );
	}

	/**
	 * Exercises the private sanitize_svg() method directly, since it holds the
	 * actual sanitization logic exercised indirectly above via a temp file.
	 */
	public function test_sanitize_svg_removes_javascript_uri_from_href_attribute() {
		$method = new ReflectionMethod( SvgUpload::class, 'sanitize_svg' );
		$method->setAccessible( true );

		$svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'
			. '<a xlink:href="javascript:alert(1)"><rect width="1" height="1" /></a></svg>';

		$sanitized = $method->invoke( $this->svg_upload, $svg );

		$this->assertNotFalse( $sanitized );
		$this->assertStringNotContainsString( 'javascript:', $sanitized );
	}

	public function test_sanitize_svg_removes_dangerous_tags_such_as_iframe_and_foreignobject_form() {
		$method = new ReflectionMethod( SvgUpload::class, 'sanitize_svg' );
		$method->setAccessible( true );

		$svg = '<svg xmlns="http://www.w3.org/2000/svg"><iframe src="https://evil.example"></iframe>'
			. '<form><input type="text" /></form><rect width="1" height="1" /></svg>';

		$sanitized = $method->invoke( $this->svg_upload, $svg );

		$this->assertStringNotContainsString( '<iframe', $sanitized );
		$this->assertStringNotContainsString( '<form', $sanitized );
		$this->assertStringNotContainsString( '<input', $sanitized );
		$this->assertStringContainsString( '<rect', $sanitized );
	}

	public function test_sanitize_svg_returns_false_for_empty_content() {
		$method = new ReflectionMethod( SvgUpload::class, 'sanitize_svg' );
		$method->setAccessible( true );

		$this->assertFalse( $method->invoke( $this->svg_upload, '   ' ) );
	}

	public function test_sanitize_svg_returns_false_when_no_svg_element_present() {
		$method = new ReflectionMethod( SvgUpload::class, 'sanitize_svg' );
		$method->setAccessible( true );

		$this->assertFalse( $method->invoke( $this->svg_upload, '<div>not svg</div>' ) );
	}

	public function test_fix_svg_in_media_library_ignores_non_svg_attachments() {
		$response = array( 'mime' => 'image/jpeg' );

		$attachment_id = self::factory()->attachment->create();
		$attachment    = get_post( $attachment_id );

		$result = $this->svg_upload->fix_svg_in_media_library( $response, $attachment );

		$this->assertSame( $response, $result );
	}

	public function test_fix_svg_in_media_library_fills_fallback_width_height_and_sizes_for_svgs() {
		$attachment_id = self::factory()->attachment->create( array( 'post_mime_type' => 'image/svg+xml' ) );
		$attachment    = get_post( $attachment_id );

		$response = array(
			'mime'   => 'image/svg+xml',
			'width'  => 0,
			'height' => 0,
			'sizes'  => array(),
		);

		$result = $this->svg_upload->fix_svg_in_media_library( $response, $attachment );

		$this->assertSame( 100, $result['width'] );
		$this->assertSame( 100, $result['height'] );
		$this->assertArrayHasKey( 'full', $result['sizes'] );
		$this->assertSame( 100, $result['sizes']['full']['width'] );
		$this->assertSame( 'landscape', $result['sizes']['full']['orientation'] );
	}

	public function test_fix_svg_in_media_library_preserves_existing_width_height() {
		$attachment_id = self::factory()->attachment->create( array( 'post_mime_type' => 'image/svg+xml' ) );
		$attachment    = get_post( $attachment_id );

		$response = array(
			'mime'   => 'image/svg+xml',
			'width'  => 200,
			'height' => 300,
			'sizes'  => array( 'full' => array( 'url' => 'https://example.com/icon.svg' ) ),
		);

		$result = $this->svg_upload->fix_svg_in_media_library( $response, $attachment );

		$this->assertSame( 200, $result['width'] );
		$this->assertSame( 300, $result['height'] );
	}
}
