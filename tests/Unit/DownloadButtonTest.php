<?php
/**
 * Tests for FrontBlocks\Frontend\DownloadButton.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Frontend\DownloadButton;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class DownloadButtonTest extends TestCase {

	/**
	 * @var DownloadButton
	 */
	private $download_button;

	public function set_up() {
		parent::set_up();
		$this->download_button = new DownloadButton();
	}

	public function test_constructor_registers_render_block_filter() {
		$this->assertNotFalse(
			has_filter( 'render_block_core/button', array( $this->download_button, 'apply_download_to_button' ) )
		);
	}

	public function test_constructor_registers_block_type_args_filter() {
		$this->assertNotFalse(
			has_filter( 'register_block_type_args', array( $this->download_button, 'register_native_block_attributes' ) )
		);
	}

	public function test_native_block_attributes_added_for_core_button() {
		$args = $this->download_button->register_native_block_attributes( array(), 'core/button' );

		$this->assertArrayHasKey( 'frblDownloadEnabled', $args['attributes'] );
		$this->assertFalse( $args['attributes']['frblDownloadEnabled']['default'] );
		$this->assertArrayHasKey( 'frblDownloadFileId', $args['attributes'] );
		$this->assertSame( 0, $args['attributes']['frblDownloadFileId']['default'] );
		$this->assertArrayHasKey( 'frblDownloadFileUrl', $args['attributes'] );
		$this->assertArrayHasKey( 'frblDownloadFileName', $args['attributes'] );
	}

	public function test_native_block_attributes_untouched_for_other_blocks() {
		$args = $this->download_button->register_native_block_attributes( array( 'foo' => 'bar' ), 'core/paragraph' );

		$this->assertSame( array( 'foo' => 'bar' ), $args );
	}

	public function test_button_unchanged_when_download_not_enabled() {
		$block   = array( 'attrs' => array( 'frblDownloadEnabled' => false ) );
		$content = '<div class="wp-block-button"><a href="https://example.com">Click</a></div>';

		$this->assertSame( $content, $this->download_button->apply_download_to_button( $content, $block ) );
	}

	public function test_button_unchanged_when_no_file_url_resolvable() {
		$block = array(
			'attrs' => array(
				'frblDownloadEnabled' => true,
			),
		);
		$content = '<div class="wp-block-button"><a href="https://example.com">Click</a></div>';

		$this->assertSame( $content, $this->download_button->apply_download_to_button( $content, $block ) );
	}

	public function test_button_href_and_download_attribute_set_from_file_url() {
		$block = array(
			'attrs' => array(
				'frblDownloadEnabled'  => true,
				'frblDownloadFileUrl'  => 'https://example.com/files/brochure.pdf',
				'frblDownloadFileName' => 'brochure.pdf',
			),
		);
		$content = '<div class="wp-block-button"><a href="https://example.com" target="_blank">Click</a></div>';

		$output = $this->download_button->apply_download_to_button( $content, $block );

		$this->assertStringContainsString( 'href="https://example.com/files/brochure.pdf"', $output );
		$this->assertStringContainsString( 'download="brochure.pdf"', $output );
		$this->assertStringNotContainsString( 'target="_blank"', $output );
	}

	public function test_button_download_attribute_has_no_value_when_file_name_absent() {
		$block = array(
			'attrs' => array(
				'frblDownloadEnabled' => true,
				'frblDownloadFileUrl' => 'https://example.com/files/brochure.pdf',
			),
		);
		$content = '<div class="wp-block-button"><a href="https://example.com">Click</a></div>';

		$output = $this->download_button->apply_download_to_button( $content, $block );

		$this->assertStringContainsString( 'href="https://example.com/files/brochure.pdf"', $output );
		$this->assertMatchesRegularExpression( '/\bdownload\b/', $output );
	}

	public function test_resolve_file_url_prefers_attachment_id_over_raw_url() {
		$file      = trailingslashit( get_temp_dir() ) . 'frbl-test-file.pdf';
		file_put_contents( $file, 'test' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		$attachment_id = self::factory()->attachment->create_upload_object( $file );

		$method = new ReflectionMethod( DownloadButton::class, 'resolve_file_url' );
		$method->setAccessible( true );

		$resolved = $method->invoke(
			$this->download_button,
			array(
				'frblDownloadFileId'  => $attachment_id,
				'frblDownloadFileUrl' => 'https://example.com/fallback.pdf',
			)
		);

		$this->assertSame( wp_get_attachment_url( $attachment_id ), $resolved );

		wp_delete_attachment( $attachment_id, true );
	}

	public function test_resolve_file_url_falls_back_to_raw_url_when_no_attachment() {
		$method = new ReflectionMethod( DownloadButton::class, 'resolve_file_url' );
		$method->setAccessible( true );

		$resolved = $method->invoke(
			$this->download_button,
			array(
				'frblDownloadFileId'  => 0,
				'frblDownloadFileUrl' => 'https://example.com/fallback.pdf',
			)
		);

		$this->assertSame( 'https://example.com/fallback.pdf', $resolved );
	}

	public function test_resolve_file_url_returns_empty_string_when_nothing_set() {
		$method = new ReflectionMethod( DownloadButton::class, 'resolve_file_url' );
		$method->setAccessible( true );

		$resolved = $method->invoke( $this->download_button, array() );

		$this->assertSame( '', $resolved );
	}
}
