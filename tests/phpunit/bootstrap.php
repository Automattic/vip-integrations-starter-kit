<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$_tests_dir = (string) getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- CLI
	throw new Exception( "Could not find {$_tests_dir}/includes/functions.php" ); // NOSONAR
}

if ( ! defined( 'WPMU_PLUGIN_DIR' ) ) {
	define( 'WPMU_PLUGIN_DIR', 'empty' );
}

// Give access to tests_add_filter() function.
/** @psalm-suppress UnresolvableInclude */
require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin(): void {
	require_once __DIR__ . '/../../index.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
/** @psalm-suppress UnresolvableInclude */
require_once $_tests_dir . '/includes/bootstrap.php';

/**
 * @psalm-suppress InvalidGlobal
 * @var string
 */
global $wp_version;
echo 'WP Version: ', esc_html( $wp_version ), PHP_EOL;
