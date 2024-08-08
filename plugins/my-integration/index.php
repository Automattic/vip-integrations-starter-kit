<?php
/*
 * Plugin Name: Sample Plugin
 * Description: Plugin description goes here.
 * Version: 1.0.0
 * Author: Automattic
 * License: MIT
 * Text Domain: test-demo
 * Domain Path: /lang
 */

use MyNamespace\TestDemo\Plugin;

if ( defined( 'ABSPATH' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';

	Plugin::get_instance();
}
