<?php

namespace ExampleVendor\ExampleIntegration;

use ArrayAccess;
use LogicException;

/**
 * @phpstan-type SettingsArray array{
 *  enabled: bool,
 *  message: string,
 * }
 *
 * @template-implements ArrayAccess<string, scalar>
 */
final class Settings implements ArrayAccess {
	/** @var string  */
	const OPTIONS_KEY = 'example_integration_settings';

	/** @var self|null */
	private static $instance;

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			// @codeCoverageIgnoreStart
			// Depending on the test order, the instance may have already been set
			self::$instance = new self();
			// @codeCoverageIgnoreEnd
		}

		return self::$instance;
	}

	/** @var SettingsArray */
	private const DEFAULTS = [
		'enabled' => false,
		'message' => 'Hello',
	];

	/** @var SettingsArray */
	private $options;

	/**
	 * @codeCoverageIgnore -- depending on the test order, this could be untestable because the class is a singleton
	 */
	private function __construct() {
		$this->refresh();
	}

	public function refresh(): void {
		/** @var mixed */
		$settings      = get_option( self::OPTIONS_KEY );
		$this->options = SettingsValidator::ensure_data_shape( is_array( $settings ) ? $settings : [] );
	}

	/**
	 * @return SettingsArray
	 */
	public static function defaults(): array {
		return self::DEFAULTS;
	}

	/**
	 * @param string $offset
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->options[ $offset ] );
	}

	/**
	 * @param string $offset
	 * @return string|bool|null
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->options[ $offset ] ?? null;
	}

	/**
	 * @param mixed $_offset
	 * @param mixed $_value
	 * @return never
	 * @throws LogicException
	 */
	public function offsetSet( $_offset, $_value ): void {
		throw new LogicException();
	}

	/**
	 * @param mixed $_offset
	 * @return never
	 * @throws LogicException
	 */
	public function offsetUnset( $_offset ): void {
		throw new LogicException();
	}
}
