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
		<h1><?php esc_html_e( 'FrontBlocks Settings', 'frontblocks' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'frbl_settings' );
			do_settings_sections( 'frontblocks-settings' );
			submit_button();
			?>
		</form>
		
		<div class="card" style="max-width: 600px; margin-top: 20px;">
			<h2><?php esc_html_e( 'About FrontBlocks', 'frontblocks' ); ?></h2>
			<p>
				<?php esc_html_e( 'FrontBlocks extends GenerateBlocks with additional functionality including FullPage.js integration for creating vertical scrolling sections.', 'frontblocks' ); ?>
			</p>
			
			<h3><?php esc_html_e( 'FullPage.js License', 'frontblocks' ); ?></h3>
			<p>
				<?php
				echo sprintf(
					/* translators: %s: FullPage.js pricing URL */
					__( 'FullPage.js is free for personal and non-profit projects. For commercial use, you need a license. %s', 'frontblocks' ),
					'<a href="https://close.technology/likes/fullpage/" target="_blank">' . esc_html__( 'Get your license here', 'frontblocks' ) . '</a>'
				);
				?>
			</p>
			
			<h3><?php esc_html_e( 'Features', 'frontblocks' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Vertical scrolling sections', 'frontblocks' ); ?></li>
				<li><?php esc_html_e( 'Customizable navigation dots', 'frontblocks' ); ?></li>
				<li><?php esc_html_e( 'Scroll overflow support', 'frontblocks' ); ?></li>
				<li><?php esc_html_e( 'Auto scroll functionality', 'frontblocks' ); ?></li>
				<li><?php esc_html_e( 'Responsive design', 'frontblocks' ); ?></li>
			</ul>
		</div>
	</div>
	<?php
}

// Function to get license key for use in JavaScript
function frbl_get_fullpage_license_key() {
	return get_option( 'frbl_fullpage_license_key', '' );
}
