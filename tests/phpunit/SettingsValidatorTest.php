<?php
declare(strict_types = 1);

namespace MyNamespace\TestDemo;

use WP_UnitTestCase;

/**
 * @covers MyNamespace\TestDemo\SettingsValidator
 * @uses \MyNamespace\TestDemo\Settings
 * @uses \MyNamespace\TestDemo\InputFactory::__construct
 * @psalm-import-type SettingsArray from Settings
 */
class SettingsValidatorTest extends WP_UnitTestCase {
	/**
	 * @dataProvider data_sanitize
	 * @uses \MyNamespace\TestDemo\AdminSettings
	 * @param mixed $value
	 * @psalm-param SettingsArray $expected
	 */
	public function test_sanitize( $value, array $expected ): void {
		AdminSettings::get_instance()->register_settings();
		update_option( Settings::OPTIONS_KEY, $value );

		/** @var mixed */
		$actual = get_option( Settings::OPTIONS_KEY );
		static::assertEquals( $expected, $actual );
	}

	/**
	 * @psalm-return iterable<array-key, array{mixed, SettingsArray}>
	 */
	public function data_sanitize(): iterable {
		return [
			[
				'',
				[
					'enabled' => false,
					'message' => 'Hello',
				],
			],
			[
				[ 'enabled' => '1' ],
				[
					'enabled' => true,
					'message' => 'Hello',
				],
			],
			[
				[ 'extra' => 'abcdef' ],
				[
					'enabled' => false,
					'message' => 'Hello',
				],
			],
		];
	}

	/**
	 * @dataProvider data_ensure_data_shape
	 * @param mixed[] $value
	 * @psalm-param SettingsArray $expected
	 */
	public function test_ensure_data_shape( array $value, array $expected ): void {
		$actual = SettingsValidator::ensure_data_shape( $value );
		static::assertEquals( $expected, $actual );
	}

	/**
	 * @psalm-return iterable<array-key, array{mixed[], SettingsArray}>
	 */
	public function data_ensure_data_shape(): iterable {
		return [
			[
				[],
				Settings::defaults(),
			],
			[
				[
					'message' => 20,
					'extra'   => true,
				],
				[
					'message' => '20',
				] + Settings::defaults(),
			],
		];
	}
}
