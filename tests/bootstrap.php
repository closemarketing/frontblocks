<?php
/**
 * PHPUnit bootstrap for FrontBlocks.
 *
 * @package FrontBlocks
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Load the plugin under test.
 */
function _frbl_manually_load_plugin() {
	require dirname( __DIR__ ) . '/frontblocks.php';
}
tests_add_filter( 'muplugins_loaded', '_frbl_manually_load_plugin' );

require "{$_tests_dir}/includes/bootstrap.php";
