<?php
/**
 * Tests for FrontBlocks\Admin\RedundantPlugins.
 *
 * @package FrontBlocks
 */

use FrontBlocks\Admin\RedundantPlugins;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class RedundantPluginsTest extends TestCase {

	public function tear_down() {
		delete_option( 'frontblocks_settings' );
		parent::tear_down();
	}

	public function test_default_entries_include_svg_upload_and_cookie_notice() {
		$entries = RedundantPlugins::get_entries();

		$this->assertArrayHasKey( 'svg-upload', $entries );
		$this->assertArrayHasKey( 'cookie-notice', $entries );
	}

	public function test_svg_upload_entry_is_always_enabled() {
		$entries = RedundantPlugins::get_entries();

		$this->assertTrue( $entries['svg-upload']['enabled'] );
		$this->assertArrayHasKey( 'safe-svg/safe-svg.php', $entries['svg-upload']['plugins'] );
		$this->assertArrayHasKey( 'svg-support/svg-support.php', $entries['svg-upload']['plugins'] );
	}

	public function test_cookie_notice_entry_enabled_follows_the_setting() {
		update_option( 'frontblocks_settings', array( 'enable_cookie_notice' => true ) );
		$entries = RedundantPlugins::get_entries();
		$this->assertTrue( $entries['cookie-notice']['enabled'] );

		update_option( 'frontblocks_settings', array( 'enable_cookie_notice' => false ) );
		$entries = RedundantPlugins::get_entries();
		$this->assertFalse( $entries['cookie-notice']['enabled'] );
	}

	public function test_cookie_notice_entry_lists_gdpr_cookie_compliance() {
		$entries = RedundantPlugins::get_entries();

		$this->assertArrayHasKey( 'gdpr-cookie-compliance/moove-gdpr.php', $entries['cookie-notice']['plugins'] );
	}

	/**
	 * New pairings (e.g. a future image compression feature) must be
	 * addable purely through the filter, without editing RedundantPlugins.
	 */
	public function test_new_entries_can_be_registered_through_the_filter() {
		$callback = function ( $entries ) {
			$entries['image-optimization'] = array(
				'feature' => 'Image Compression',
				'enabled' => true,
				'plugins' => array(
					'imagify/imagify.php' => 'Imagify',
				),
			);

			return $entries;
		};

		add_filter( 'frontblocks_redundant_plugins', $callback );
		$entries = RedundantPlugins::get_entries();
		remove_filter( 'frontblocks_redundant_plugins', $callback );

		$this->assertArrayHasKey( 'image-optimization', $entries );
		$this->assertSame( 'Imagify', $entries['image-optimization']['plugins']['imagify/imagify.php'] );
	}
}
