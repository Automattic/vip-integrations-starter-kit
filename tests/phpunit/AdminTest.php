<?php
declare(strict_types = 1);

namespace MyNamespace\TestDemo;

use WP_UnitTest_Factory;
use WP_UnitTestCase;

/**
 * @covers \MyNamespace\TestDemo\Admin
 */
class AdminTest extends WP_UnitTestCase {
	protected static int $admin_id = 0;

	const PLUGIN_FILE = 'my-integration/index.php';

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ): void {
		static::$admin_id = $factory->user->create( [ 'role' => 'administrator' ] );
		grant_super_admin( static::$admin_id );
	}

	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( static::$admin_id );
	}

	public function test_construct(): void {
		$admin = Admin::get_instance();
		$admin->init();

		static::assertEquals( 10, has_action( 'admin_init', [ $admin, 'admin_init' ] ) );
		static::assertEquals( 10, has_action( 'admin_menu', [ $admin, 'admin_menu' ] ) );
	}

	public function test_admin_init(): void {
		$admin = Admin::get_instance();
		$admin->admin_init();

		$filter = 'plugin_action_links_' . static::PLUGIN_FILE;
		static::assertEquals( 10, has_filter( $filter, [ $admin, 'plugin_action_links' ] ) );
	}

	/**
	 * @global bool[] $_registered_pages
	 */
	public function test_admin_menu(): void {
		/** @psalm-var array<string, bool> $_registered_pages */
		global $_registered_pages;

		Admin::get_instance()->init();
		do_action( 'admin_menu' );

		$key = 'admin_page_' . Admin::OPTIONS_MENU_SLUG;

		static::assertArrayHasKey( $key, $_registered_pages );
		static::assertTrue( $_registered_pages[ $key ] );
	}

	public function test_plugin_action_links(): void {
		$filter = 'plugin_action_links_' . static::PLUGIN_FILE;

		$plugin = Admin::get_instance();
		$plugin->admin_init();

		/** @var mixed */
		$links = apply_filters( $filter, [] );

		static::assertIsArray( $links );
		static::assertArrayHasKey( 'settings', $links );
		static::assertStringContainsString( 'options-general.php?page=' . Admin::OPTIONS_MENU_SLUG, $links['settings'] );
	}
}
