<?php

namespace MyNamespace\TestDemo;

use ArrayAccess;
use LogicException;

/**
 * @psalm-type SettingsArray = array{
 *  enabled: bool,
 *  message: string,
 * }
 *
 * @template-implements ArrayAccess<string, scalar>
 */
final class Settings implements ArrayAccess {
	/** @var string  */
	const OPTION_KEY = 'testdemo_settings';

	private static $instance;

	public static function get_instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @psalm-readonly
	 * @psalm-var SettingsArray
	 */
	private static $defaults = [
		'enabled' => false,
		'message' => 'Hello',
	];

	/**
	 * @var array
	 * @psalm-var SettingsArray
	 */
	private $options;

	private function __construct() {
		$this->refresh();
	}

	public function refresh(): void {
		/** @var mixed */
		$settings      = get_option( self::OPTION_KEY );
		$this->options = SettingsValidator::ensure_data_shape( is_array( $settings ) ? $settings : [] );
	}

	/**
	 * @psalm-return SettingsArray
	 */
	public static function defaults(): array {
		return self::$defaults;
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->options[ (string) $offset ] );
	}

	/**
	 * @param mixed $offset
	 * @return int|string|bool|null
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->options[ (string) $offset ] ?? null;
	}

	/**
	 * @param mixed $_offset
	 * @param mixed $_value
	 * @psalm-return never
	 * @throws LogicException
	 */
	public function offsetSet( $_offset, $_value ): void {
		throw new LogicException();
	}

	/**
	 * @param mixed $_offset
	 * @psalm-return never
	 * @throws LogicException
	 */
	public function offsetUnset( $_offset ): void {
		throw new LogicException();
	}

	/**
	 * @psalm-return SettingsArray
	 */
	public function as_array(): array {
		return $this->options;
	}
}
