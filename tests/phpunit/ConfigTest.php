<?php
declare(strict_types = 1);

namespace ExampleVendor\ExampleIntegration;

use WP_UnitTestCase;

/**
 * @covers \ExampleVendor\ExampleIntegration\Config
 */
class ConfigTest extends WP_UnitTestCase {
	private const FIXTURES_DIR = __DIR__ . '/../../fixtures';

	public function test_fully_configured_fixture(): void {
		$config = new Config( require self::FIXTURES_DIR . '/config-valid.php' );

		static::assertTrue( $config->is_available() );
		static::assertTrue( $config->is_ready() );
		static::assertSame( [], $config->missing_fields() );
		static::assertSame( 'https://api.vendor.example', $config->get( 'api_base_url' ) );
	}

	public function test_minimal_fixture_is_ready_and_optional_fields_fall_back(): void {
		$config = new Config( require self::FIXTURES_DIR . '/config-minimal.php' );

		static::assertTrue( $config->is_ready() );
		static::assertSame( 'fallback', $config->get( 'signature_label', 'fallback' ) );
	}

	public function test_incomplete_fixture_degrades_gracefully(): void {
		$config = new Config( require self::FIXTURES_DIR . '/config-incomplete.php' );

		static::assertTrue( $config->is_available() );
		static::assertFalse( $config->is_ready() );
		static::assertSame( [ 'api_token' ], $config->missing_fields() );
	}

	public function test_invalid_fixture_is_not_available(): void {
		$config = new Config( require self::FIXTURES_DIR . '/config-invalid.php' );

		static::assertFalse( $config->is_available() );
		static::assertFalse( $config->is_ready() );
		static::assertSame( 'default', $config->get( 'api_base_url', 'default' ) );
	}

	public function test_empty_required_field_counts_as_missing(): void {
		$config = new Config( [
			'api_base_url' => '',
			'api_token'    => 'mock-token',
		] );

		static::assertFalse( $config->is_ready() );
		static::assertSame( [ 'api_base_url' ], $config->missing_fields() );
	}

	/**
	 * A required field that is present but not a usable string must degrade,
	 * not enable the integration. Guards against "required means merely set".
	 *
	 * @dataProvider provide_unusable_values
	 * @param mixed $value
	 */
	public function test_unusable_required_field_counts_as_missing( $value ): void {
		$config = new Config( [
			'api_base_url' => 'https://api.vendor.example',
			'api_token'    => $value,
		] );

		static::assertFalse( $config->is_ready() );
		static::assertSame( [ 'api_token' ], $config->missing_fields() );
	}

	/**
	 * @return array<string, array{0: mixed}>
	 */
	public function provide_unusable_values(): array {
		return [
			'boolean false' => [ false ],
			'integer zero'  => [ 0 ],
			'whitespace'    => [ '   ' ],
			'empty array'   => [ [] ],
		];
	}

	public function test_undefined_constant_is_not_available(): void {
		$config = new Config( null );

		static::assertFalse( $config->is_available() );
		static::assertFalse( $config->is_ready() );
	}

	public function test_singleton_reads_the_constant(): void {
		// bootstrap.php defines the constant from the valid fixture.
		static::assertTrue( Config::get_instance()->is_ready() );
		static::assertSame( Config::get_instance(), Config::get_instance() );
	}

	public function test_for_display_masks_secrets_and_stringifies_values(): void {
		$config = new Config( [
			'api_base_url' => 'https://api.vendor.example',
			'api_token'    => 'super-secret-token',
			'retries'      => 3,
		] );

		$display = $config->for_display();

		static::assertSame( 'https://api.vendor.example', $display['api_base_url'] );
		static::assertSame( '3', $display['retries'] );
		static::assertNotSame( 'super-secret-token', $display['api_token'] );
		static::assertStringNotContainsString( 'super-secret-token', implode( '', $display ) );
	}
}
