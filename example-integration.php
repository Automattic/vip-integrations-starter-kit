<?php
/**
 * Plugin Name: Example Integration
 * Description: Reference implementation of a WordPress VIP partner integration, built from the VIP Integrations Starter Kit.
 * Version: 1.0.0
 * Requires at least: 6.9
 * Requires PHP: 8.2
 * Author: Example Vendor
 * License: MIT
 * Text Domain: example-integration
 */

use ExampleVendor\ExampleIntegration\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'VIP_EXAMPLE_INTEGRATION_LOADED' ) ) {
	return;
}

define( 'VIP_EXAMPLE_INTEGRATION_LOADED', true );
define( 'VIP_EXAMPLE_INTEGRATION_VERSION', '1.0.0' );
define( 'VIP_EXAMPLE_INTEGRATION_FILE', __FILE__ );

require_once __DIR__ . '/vendor/autoload.php';

Plugin::get_instance()->register();
