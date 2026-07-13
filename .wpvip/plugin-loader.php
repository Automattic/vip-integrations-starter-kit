<?php

defined( 'ABSPATH' ) || die();

if ( ! defined( 'WP_TESTS_DOMAIN' ) && function_exists( 'wpcom_vip_load_plugin' ) ) {
	if ( ! defined( 'VIP_EXAMPLE_INTEGRATION_CONFIG' ) ) {
		// Mirror the VIP platform: runtime config is defined before the plugin loads.
		// A git-ignored fixtures/config-local.php overrides the committed fixture —
		// handy for local secrets and experiments (see fixtures/README.md).
		$vip_example_integration_fixtures = WP_CONTENT_DIR . '/plugins/example-integration/fixtures';
		define(
			'VIP_EXAMPLE_INTEGRATION_CONFIG',
			file_exists( $vip_example_integration_fixtures . '/config-local.php' )
				? require $vip_example_integration_fixtures . '/config-local.php'
				: require $vip_example_integration_fixtures . '/config-valid.php'
		);
		unset( $vip_example_integration_fixtures );
	}

	wpcom_vip_load_plugin( 'example-integration/example-integration.php' );
}
