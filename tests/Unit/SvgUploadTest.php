<?php
/**
 * Tests for FrontBlocks\Frontend\SvgUpload.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\SvgUpload;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class SvgUploadTest extends TestCase {

	public function test_svg_attachments_do_not_generate_a_srcset() {
		$attachment_id = self::factory()->attachment->create(
			array(
				'post_mime_type' => 'image/svg+xml',
			)
		);
		$svg_upload    = new SvgUpload();
		$sources       = array(
			'1024' => array(
				'url'        => 'https://example.org/wp-content/uploads/example.svg',
				'descriptor' => 'w',
				'value'      => 1024,
			),
		);

		$this->assertSame(
			array(),
			$svg_upload->disable_svg_srcset(
				$sources,
				array( 1024, 512 ),
				'https://example.org/wp-content/uploads/2026/09/example.svg',
				array(),
				$attachment_id
			)
		);
	}

	public function test_raster_attachments_keep_their_srcset() {
		$attachment_id = self::factory()->attachment->create(
			array(
				'post_mime_type' => 'image/jpeg',
			)
		);
		$svg_upload    = new SvgUpload();
		$sources       = array(
			'1024' => array(
				'url'        => 'https://example.org/wp-content/uploads/example.jpg',
				'descriptor' => 'w',
				'value'      => 1024,
			),
		);

		$this->assertSame(
			$sources,
			$svg_upload->disable_svg_srcset(
				$sources,
				array( 1024, 512 ),
				'https://example.org/wp-content/uploads/2026/09/example.jpg',
				array(),
				$attachment_id
			)
		);
	}
}
