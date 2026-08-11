<?php
declare(strict_types = 1);

namespace ExampleVendor\ExampleIntegration;

use WP_UnitTestCase;

/**
 * @covers \ExampleVendor\ExampleIntegration\AdminSettings
 * @uses \ExampleVendor\ExampleIntegration\InputFactory
 * @uses \ExampleVendor\ExampleIntegration\Settings
 * @uses \ExampleVendor\ExampleIntegration\SettingsValidator::ensure_data_shape
 */
class AdminSettingsTest extends WP_UnitTestCase {
	public static function setUpBeforeClass(): void {
		// `get_admin_page_title()` needs `menu` to be an array
		if ( ! isset( $GLOBALS['menu'] ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$GLOBALS['menu'] = [];
		}
	}

	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( 1 );
	}

	public function tearDown(): void {
		parent::tearDown();
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['plugin_page'] = null;
	}

	/**
	 * register_settings() registers the setting, its section, and its fields.
	 *
	 * @global mixed[] $wp_settings_sections
	 * @global mixed[] $wp_settings_fields
	 */
	public function test_register_settings(): void {
		global $wp_settings_sections;
		global $wp_settings_fields;

		/** @psalm-var array<string, array<string, mixed>> $wp_settings_sections */
		/** @psalm-var array<string, array<string, mixed>> $wp_settings_fields */

		$admin_settings = new AdminSettings( new InputFactory( Settings::OPTIONS_KEY, Settings::get_instance() ) );
		$admin_settings->register_settings();

		self::assertArrayHasKey( Admin::OPTIONS_MENU_SLUG, $wp_settings_sections );
		self::assertArrayHasKey( 'general-settings', $wp_settings_sections[ Admin::OPTIONS_MENU_SLUG ] );

		self::assertArrayHasKey( Admin::OPTIONS_MENU_SLUG, $wp_settings_fields );
		self::assertArrayHasKey( 'general-settings', $wp_settings_fields[ Admin::OPTIONS_MENU_SLUG ] );

		self::assertArrayHasKey( 'enabled', $wp_settings_fields[ Admin::OPTIONS_MENU_SLUG ]['general-settings'] );
		self::assertArrayHasKey( 'message', $wp_settings_fields[ Admin::OPTIONS_MENU_SLUG ]['general-settings'] );
	}

	/**
	 * register() registers the settings and reuses a single instance.
	 *
	 * @global mixed[] $wp_settings_fields
	 */
	public function test_register(): void {
		global $wp_settings_fields;

		/** @psalm-var array<string, array<string, mixed>> $wp_settings_fields */

		AdminSettings::register();

		self::assertArrayHasKey( Admin::OPTIONS_MENU_SLUG, $wp_settings_fields );
		self::assertSame( AdminSettings::get_instance(), AdminSettings::get_instance() );
	}

	public function testSettingsPage_guest(): void {
		global $plugin_page;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$plugin_page = Admin::OPTIONS_MENU_SLUG;
		wp_set_current_user( 0 );

		ob_start();
		AdminSettings::get_instance()->settings_page();
		$contents = ob_get_clean();

		self::assertEmpty( $contents );
	}

	public function testSettingsPage_admin(): void {
		global $plugin_page;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$plugin_page = Admin::OPTIONS_MENU_SLUG;

		ob_start();
		AdminSettings::get_instance()->settings_page();
		$contents = ob_get_clean();

		self::assertNotEmpty( $contents );
	}
}
