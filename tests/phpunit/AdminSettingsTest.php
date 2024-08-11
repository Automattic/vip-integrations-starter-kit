<?php
declare(strict_types = 1);

namespace MyNamespace\TestDemo;

use WP_UnitTestCase;

/**
 * @covers \MyNamespace\TestDemo\AdminSettings
 * @uses \MyNamespace\TestDemo\InputFactory
 * @uses \MyNamespace\TestDemo\Settings
 * @uses \MyNamespace\TestDemo\SettingsValidator::ensure_data_shape
 */
class AdminSettingsTest extends WP_UnitTestCase {
	public static function setUpBeforeClass(): void {
		// Fix for `get_admin_page_title()`
		if ( ! isset( $GLOBALS['menu'] ) ) {
			$GLOBALS['menu'] = [];
		}
	}

	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( 1 );
	}

	public function tearDown(): void {
		parent::tearDown();
		$GLOBALS['plugin_page'] = null;
	}

	/**
	 * @global mixed[] $wp_settings_sections
	 * @global mixed[] $wp_settings_fields
	 *
	 * @uses MyNamespace\TestDemo\SettingsValidator::ensure_data_shape
	 */
	public function test_construct(): void {
		global $wp_settings_sections;
		global $wp_settings_fields;

		/** @psalm-var array<string, array<string, mixed>> $wp_settings_sections */
		/** @psalm-var array<string, array<string, mixed>> $wp_settings_fields */

		AdminSettings::get_instance()->register_settings();

		self::assertArrayHasKey( Admin::OPTIONS_MENU_SLUG, $wp_settings_sections );
		self::assertArrayHasKey( 'general-settings', $wp_settings_sections[ Admin::OPTIONS_MENU_SLUG ] );

		self::assertArrayHasKey( Admin::OPTIONS_MENU_SLUG, $wp_settings_fields );
		self::assertArrayHasKey( 'general-settings', $wp_settings_fields[ Admin::OPTIONS_MENU_SLUG ] );

		self::assertArrayHasKey( 'enabled', $wp_settings_fields[ Admin::OPTIONS_MENU_SLUG ]['general-settings'] );
		self::assertArrayHasKey( 'message', $wp_settings_fields[ Admin::OPTIONS_MENU_SLUG ]['general-settings'] );
	}

	public function testSettingsPage_guest(): void {
		global $plugin_page;

		$plugin_page = Admin::OPTIONS_MENU_SLUG;
		wp_set_current_user( 0 );
		ob_start();
		AdminSettings::get_instance()->settings_page();
		$contents = ob_get_clean();

		self::assertEmpty( $contents );
	}

	public function testSettingsPage_admin(): void {
		global $plugin_page;

		$plugin_page = Admin::OPTIONS_MENU_SLUG;

		ob_start();
		AdminSettings::get_instance()->settings_page();
		$contents = ob_get_clean();

		self::assertNotEmpty( $contents );
	}
}
