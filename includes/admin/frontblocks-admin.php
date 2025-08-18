<?php
/**
 * FrontBlocks Admin Settings
 *
 * @package    WordPress
 * @author     David Perez <david@close.technology>
 * @copyright  2025 Closemarketing
 * @version    1.0
 */

defined( 'ABSPATH' ) || exit;

// Add admin menu
add_action( 'admin_menu', 'frbl_add_admin_menu' );
function frbl_add_admin_menu() {
	add_submenu_page(
		'generateblocks', // Parent slug (GenerateBlocks menu)
		'FrontBlocks Settings', // Page title
		'FrontBlocks', // Menu title
		'manage_options', // Capability
		'frontblocks-settings', // Menu slug
		'frbl_admin_page' // Callback function
	);
}

// Register settings
add_action( 'admin_init', 'frbl_register_settings' );
function frbl_register_settings() {
	register_setting( 'frbl_settings', 'frbl_fullpage_license_key' );
	
	add_settings_section(
		'frbl_fullpage_section',
		'FullPage.js Settings',
		'frbl_fullpage_section_callback',
		'frontblocks-settings'
	);
	
	add_settings_field(
		'frbl_fullpage_license_key',
		'FullPage.js License Key',
		'frbl_license_key_callback',
		'frontblocks-settings',
		'frbl_fullpage_section'
	);
}

// Section callback
function frbl_fullpage_section_callback() {
	echo '<p>Configure FullPage.js settings for FrontBlocks.</p>';
}

// License key field callback
function frbl_license_key_callback() {
	$license_key = get_option( 'frbl_fullpage_license_key', '' );
	echo '<input type="text" id="frbl_fullpage_license_key" name="frbl_fullpage_license_key" value="' . esc_attr( $license_key ) . '" class="regular-text" />';
	echo '<p class="description">Enter your FullPage.js license key. Leave empty if you don\'t have a license (will show watermark).</p>';
}

// Admin page callback
function frbl_admin_page() {
	?>
	<div class="wrap">
		<h1>FrontBlocks Settings</h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'frbl_settings' );
			do_settings_sections( 'frontblocks-settings' );
			submit_button();
			?>
		</form>
		
		<div class="card" style="max-width: 600px; margin-top: 20px;">
			<h2>About FrontBlocks</h2>
			<p>FrontBlocks extends GenerateBlocks with additional functionality including FullPage.js integration for creating vertical scrolling sections.</p>
			
			<h3>FullPage.js License</h3>
			<p>FullPage.js is free for personal and non-profit projects. For commercial use, you need a license. <a href="https://alvarotrigo.com/fullPage/pricing/" target="_blank">Get your license here</a>.</p>
			
			<h3>Features</h3>
			<ul>
				<li>Vertical scrolling sections</li>
				<li>Customizable navigation dots</li>
				<li>Scroll overflow support</li>
				<li>Auto scroll functionality</li>
				<li>Responsive design</li>
			</ul>
		</div>
	</div>
	<?php
}

// Function to get license key for use in JavaScript
function frbl_get_fullpage_license_key() {
	return get_option( 'frbl_fullpage_license_key', '' );
}
