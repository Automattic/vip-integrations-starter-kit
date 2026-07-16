<?php

namespace ExampleVendor\ExampleIntegration;

/**
 * Tracks-only telemetry helper — the pattern VIP integrations should reuse.
 *
 * Wraps the VIP Telemetry API shipped by the platform's MU plugins. That API
 * is present under `vip dev-env` and in production but absent in bare PHPUnit
 * runs, hence the class_exists guard: without it, recording events is a no-op.
 *
 * Never put secrets, raw content, email addresses, or other customer data in
 * event properties.
 */
final class Telemetry {
	public const EVENT_PREFIX = 'example_integration_';

	/** @var self|null */
	private static $instance;

	/** @var \Automattic\VIP\Telemetry\Telemetry|null */
	private $client;

	public static function get_instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		if ( class_exists( \Automattic\VIP\Telemetry\Telemetry::class ) ) {
			$this->client = new \Automattic\VIP\Telemetry\Telemetry(
				self::EVENT_PREFIX,
				[ 'plugin_version' => VIP_EXAMPLE_INTEGRATION_VERSION ]
			);
		}
	}

	/**
	 * Record a Tracks event. The event name is automatically prefixed with
	 * EVENT_PREFIX by the VIP Telemetry client.
	 *
	 * @param array<string, mixed> $properties
	 */
	public function record_event( string $event_name, array $properties = [] ): void {
		if ( $this->client ) {
			$this->client->record_event( $event_name, $properties );
		}
	}
}
