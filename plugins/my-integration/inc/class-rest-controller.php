<?php

namespace MyNamespace\TestDemo;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class REST_COntroller {
	public const NAMESPACE = 'wp-test-demo/v1';

	private static $instance;

	public static function get_instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->register_routes();
	}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/sum/(?P<a>\\d+)/(?P<b>\\d+)',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'sum' ],
				'permission_callback' => fn() => current_user_can( 'read' ),
				'args'                => [
					'a' => [
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'intval',
					],
					'b' => [
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'intval',
					],
				],
			]
		);
	}

	public function sum( WP_REST_Request $request ): WP_REST_Response {
		$a = $request->get_param( 'a' );
		$b = $request->get_param( 'b' );

		return rest_ensure_response( $a + $b );
	}
}
