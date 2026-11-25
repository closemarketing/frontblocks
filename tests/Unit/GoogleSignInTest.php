<?php
/**
 * Tests for the FrontBlocks\GoogleSignIn\GoogleSignIn block registration.
 *
 * @package FrontBlocks
 */

use Yoast\WPTestUtils\WPIntegration\TestCase;

class GoogleSignInTest extends TestCase {

	/**
	 * The block declared in block.json is registered with API version 3
	 * (iframed editor canvas) and the editor script that goes with it.
	 */
	public function test_google_login_block_is_registered_with_api_version_3() {
		$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( 'frontblocks/google-login' );

		$this->assertNotNull( $block_type );
		$this->assertSame( 3, $block_type->api_version );
		$this->assertContains( 'frontblocks-google-login-block-editor', $block_type->editor_script_handles );
	}

	/**
	 * The editor script is registered by register_block() and its edit
	 * function must use useBlockProps (required by API version 3), so the
	 * compiled file has to reference it and declare the version itself.
	 */
	public function test_editor_script_declares_api_version_3_and_uses_block_props() {
		$source = file_get_contents( FRBL_PLUGIN_PATH . 'assets/google-signin/frontblocks-google-signin-block.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		$this->assertMatchesRegularExpression( '/apiVersion:\s*3/', $source );
		$this->assertStringContainsString( 'useBlockProps', $source );
	}

	/**
	 * The pre-5.5 fallback registration (no block.json support) must declare
	 * the same API version as block.json.
	 */
	public function test_block_json_and_fallback_registration_agree_on_api_version() {
		$metadata = json_decode( file_get_contents( FRBL_PLUGIN_PATH . 'includes/GoogleSignIn/block/block.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertSame( 3, $metadata['apiVersion'] );

		$source = file_get_contents( FRBL_PLUGIN_PATH . 'includes/GoogleSignIn/GoogleSignIn.php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertMatchesRegularExpression( "/'api_version'\s*=>\s*3,/", $source );
	}
}
