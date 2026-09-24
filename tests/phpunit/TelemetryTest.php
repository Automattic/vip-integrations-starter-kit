<?php
declare(strict_types = 1);

namespace ExampleVendor\ExampleIntegration;

use Automattic\VIP\Telemetry\Telemetry as VIP_Telemetry;
use WP_UnitTestCase;

/**
 * @covers \ExampleVendor\ExampleIntegration\Telemetry
 */
class TelemetryTest extends WP_UnitTestCase {
	public function test_record_event_forwards_to_the_vip_client(): void {
		VIP_Telemetry::$events = [];

		Telemetry::get_instance()->record_event( 'unit_test_event', [ 'foo' => 'bar' ] );

		static::assertSame(
			[
				[
					'prefix'     => Telemetry::EVENT_PREFIX,
					'event'      => 'unit_test_event',
					'properties' => [ 'foo' => 'bar' ],
				],
			],
			VIP_Telemetry::$events
		);
	}

	public function test_singleton(): void {
		static::assertSame( Telemetry::get_instance(), Telemetry::get_instance() );
	}
}
