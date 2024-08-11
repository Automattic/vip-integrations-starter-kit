<?php
declare(strict_types = 1);

namespace MyNamespace\TestDemo;

use MyNamespace\TestDemo\Plugin;
use MyNamespace\TestDemo\REST_Controller;
use WP_UnitTestCase;

/**
 * @covers \MyNamespace\TestDemo\Plugin
*/
class PluginTest extends WP_UnitTestCase {
	public function test_construct(): void {
		$plugin = Plugin::get_instance();

		static::assertEquals( 10, has_action( 'init', [ $plugin, 'init' ] ) );
		static::assertEquals( 10, has_action( 'rest_api_init', [ REST_Controller::class, 'get_instance' ] ) );
		static::assertEquals( 10, has_action( 'wp_footer', [ $plugin, 'wp_footer' ] ) );
	}

	public function test_wp_footer(): void {
		$plugin = Plugin::get_instance();

		ob_start();
		$plugin->wp_footer();
		$actual = ob_get_clean();

		static::assertStringContainsString( '<p class="wp-test-demo-signature">Sample Plugin</p>', $actual );
	}
}
