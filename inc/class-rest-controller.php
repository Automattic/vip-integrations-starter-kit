<?php

namespace ExampleVendor\ExampleIntegration;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class REST_Controller {
	public const NAMESPACE = 'example-integration/v1';

	/** @var self|null */
	private static $instance;

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the plugin's REST API routes.
	 */
	public static function register(): void {
		self::get_instance()->register_routes();
	}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/sum/(?P<a>\\d+)/(?P<b>\\d+)',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'sum' ],
				'permission_callback' => fn(): bool => current_user_can( 'read' ),
				// Core sanitizes each argument to its schema type.
				'args'                => [
					'a' => [
						'required' => true,
						'type'     => 'integer',
					],
					'b' => [
						'required' => true,
						'type'     => 'integer',
					],
				],
			]
		);
	}

	/**
	 * @param WP_REST_Request<array{a: int, b: int}> $request
	 */
	public function sum( WP_REST_Request $request ): WP_REST_Response {
		$a = (int) $request->get_param( 'a' );
		$b = (int) $request->get_param( 'b' );

		// Usage metadata only — never secrets, content, or PII.
		Telemetry::get_instance()->record_event( 'sum_requested', [ 'route' => 'sum' ] );

		return rest_ensure_response( $a + $b );
	}
}
