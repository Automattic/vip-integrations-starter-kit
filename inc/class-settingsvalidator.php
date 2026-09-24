<?php

namespace ExampleVendor\ExampleIntegration;

/**
 * @phpstan-import-type SettingsArray from Settings
 */
abstract class SettingsValidator {
	/**
	 * @param mixed[] $settings
	 * @return SettingsArray
	 */
	public static function ensure_data_shape( array $settings ): array {
		$defaults = Settings::defaults();
		$result   = $settings + $defaults;
		foreach ( $result as $key => $_value ) {
			if ( ! isset( $defaults[ $key ] ) ) {
				unset( $result[ $key ] );
			}
		}

		/** @var mixed $value */
		foreach ( $result as $key => $value ) {
			$my_type    = gettype( $value );
			$their_type = gettype( $defaults[ $key ] );
			if ( $my_type !== $their_type ) {
				settype( $result[ $key ], $their_type );
			}
		}

		/** @var SettingsArray */
		return $result;
	}

	/**
	 * @param mixed $settings
	 * @return SettingsArray
	 */
	public static function sanitize( $settings ): array {
		if ( is_array( $settings ) ) {
			return self::ensure_data_shape( $settings );
		}

		return Settings::defaults();
	}
}
