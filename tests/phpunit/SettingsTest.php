<?php
declare(strict_types = 1);

namespace MyNamespace\TestDemo;

use LogicException;
use WP_UnitTestCase;

/**
 * @psalm-import-type SettingsArray from Settings
 * @covers \MyNamespace\TestDemo\Settings
 * @uses \MyNamespace\TestDemo\SettingsValidator::ensure_data_shape
 */
class SettingsTest extends WP_UnitTestCase {
	public function test_defaults(): void {
		$actual = Settings::defaults();

		static::assertIsArray( $actual );
		static::assertArrayHasKey( 'enabled', $actual );
		static::assertArrayHasKey( 'message', $actual );

		static::assertIsBool( $actual['enabled'] );
		static::assertIsString( $actual['message'] );
	}

	public function test_offset_get(): void {
		$settings = Settings::get_instance();
		$expected = [
			'enabled' => true,
			'message' => 'Hi',
		];

		update_option( Settings::OPTIONS_KEY, $expected );
		$settings->refresh();

		static::assertEquals( $expected['enabled'], $settings['enabled'] );
		static::assertEquals( $expected['message'], $settings['message'] );

		static::assertNull( $settings['this_key_does_not_exist'] );
	}

	public function test_offset_exists(): void {
		$settings = Settings::get_instance();

		static::assertTrue( isset( $settings['enabled'] ) );
		static::assertTrue( isset( $settings['message'] ) );

		static::assertFalse( isset( $settings['this_key_does_not_exist'] ) );
	}

	public function test_offset_set(): void {
		$this->expectException( LogicException::class );

		$settings           = Settings::get_instance();
		$settings['secret'] = '';
	}

	public function test_offset_unset(): void {
		$this->expectException( LogicException::class );

		$settings = Settings::get_instance();
		unset( $settings['secret'] );
	}
}
