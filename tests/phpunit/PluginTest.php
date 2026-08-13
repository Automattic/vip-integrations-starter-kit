<?php
declare(strict_types = 1);

namespace ExampleVendor\ExampleIntegration;

use ExampleVendor\ExampleIntegration\Config;
use ExampleVendor\ExampleIntegration\Plugin;
use ExampleVendor\ExampleIntegration\REST_Controller;
use ReflectionProperty;
use WP_UnitTestCase;

/**
 * @covers \ExampleVendor\ExampleIntegration\Plugin
*/
class PluginTest extends WP_UnitTestCase {
	public function tear_down(): void {
		// Drop any Config singleton swapped in for a test so the next test
		// re-reads the constant bootstrap.php defines.
		$this->set_config_singleton( null );
		parent::tear_down();
	}

	public function test_register_wires_hooks(): void {
		$plugin = Plugin::get_instance();

		static::assertEquals( 10, has_action( 'init', [ $plugin, 'init' ] ) );
		static::assertEquals( 10, has_action( 'rest_api_init', [ REST_Controller::class, 'register' ] ) );
		static::assertEquals( 10, has_action( 'wp_footer', [ $plugin, 'wp_footer' ] ) );
	}

	public function test_wp_footer(): void {
		$plugin = Plugin::get_instance();

		ob_start();
		$plugin->wp_footer();
		$actual = ob_get_clean();

		static::assertStringContainsString( '<p class="example-integration-signature">Example Integration (dev)</p>', $actual );
	}

	public function test_config_notice_lists_missing_fields(): void {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->set_config_singleton( new Config( [ 'api_base_url' => 'https://api.vendor.example' ] ) );

		$actual = $this->render_config_notice();

		static::assertStringContainsString( 'notice notice-warning', $actual );
		static::assertStringContainsString( 'missing required fields: api_token', $actual );
	}

	public function test_config_notice_reports_undefined_constant(): void {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->set_config_singleton( new Config( null ) );

		$actual = $this->render_config_notice();

		static::assertStringContainsString( 'the ' . Config::CONSTANT_NAME . ' constant is not defined', $actual );
	}

	public function test_config_notice_hidden_from_non_admins(): void {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$this->set_config_singleton( new Config( null ) );

		static::assertSame( '', $this->render_config_notice() );
	}

	private function render_config_notice(): string {
		ob_start();
		Plugin::get_instance()->render_config_notice();
		return (string) ob_get_clean();
	}

	/**
	 * @param Config|null $config
	 */
	private function set_config_singleton( ?Config $config ): void {
		$property = new ReflectionProperty( Config::class, 'instance' );
		$property->setAccessible( true );
		$property->setValue( null, $config );
	}
}
