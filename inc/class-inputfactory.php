<?php

namespace ExampleVendor\ExampleIntegration;

use ArrayAccess;

/**
 * @phpstan-type InputArgs array{label_for: string, type?: string, help?: string, ...<string, mixed>}
 * @phpstan-type CheckBoxArgs array{label_for: string, help?: string, ...<string, mixed>}
 */
final class InputFactory {
	/** @var string */
	private $option_name;
	/** @var ArrayAccess<string,scalar>|array<string,scalar> */
	private $settings;

	/**
	 * @param ArrayAccess<string,scalar>|array<string,scalar> $settings
	 */
	public function __construct( string $option_name, $settings ) {
		$this->option_name = $option_name;
		$this->settings    = $settings;
	}

	/**
	 * @param InputArgs $args
	 */
	public function input( array $args ): void {
		$name  = $this->option_name;
		$id    = $args['label_for'];
		$type  = $args['type'] ?? 'text';
		$value = $this->settings[ $id ];

		printf(
			'<input type="%s" name="%s[%s]" id="%s" value="%s" %s/>',
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( $id ),
			esc_attr( (string) $value ),
			self::get_attributes( $args ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

		self::render_help( $args );
	}

	/**
	 * @param CheckBoxArgs $args
	 */
	public function checkbox( array $args ): void {
		$name  = $this->option_name;
		$id    = $args['label_for'];
		$value = $this->settings[ $id ];

		printf(
			'<input type="hidden" name="%s[%s]" value="0"/>',
			esc_attr( $name ),
			esc_attr( $id )
		);

		printf(
			'<input type="checkbox" name="%s[%s]" id="%s" value="1"%s%s/>',
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( $id ),
			checked( $value, true, false ),
			self::get_attributes( $args ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);

		self::render_help( $args );
	}

	/**
	 * @param array<string,mixed> $params
	 */
	private static function get_attributes( array $params ): string {
		unset( $params['label_for'], $params['type'], $params['help'], $params['options'], $params['name'], $params['id'], $params['value'], $params['checked'] );
		$attrs = [];
		/** @var mixed $value */
		foreach ( $params as $name => $value ) {
			if ( is_scalar( $value ) ) {
				if ( gettype( $value ) === 'boolean' ) {
					if ( ! $value ) {
						continue;
					}

					$value = $name;
				}

				$attrs[] = sprintf( '%s="%s"', esc_attr( $name ), esc_attr( (string) $value ) );
			}
		}

		return [] !== $attrs ? ' ' . join( ' ', $attrs ) : '';
	}

	/**
	 * @param array{help?: string, ...<string, mixed>} $args
	 */
	private static function render_help( array $args ): void {
		if ( isset( $args['help'] ) && '' !== $args['help'] ) {
			printf(
				'<p class="help">%s</p>',
				wp_kses(
					$args['help'],
					[
						'br'     => [],
						'code'   => [],
						'em'     => [],
						'strong' => [],
					]
				)
			);
		}
	}
}
