<?php
declare(strict_types = 1);

namespace MyNamespace\TestDemo;

use Spy_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Test_REST_TestCase;

/**
 * @covers \MyNamespace\TestDemo\REST_Controller
 */
class RESTControllerTest extends WP_Test_REST_TestCase {
	/**
	 * @global WP_REST_Server|null $wp_rest_server
	 */
	public function setUp(): void {
		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;

		parent::setUp();

		$wp_rest_server = new Spy_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );
		REST_Controller::get_instance()->register_routes();

		wp_set_current_user( 1 );
	}

	/**
	 * @global WP_REST_Server|null $wp_rest_server
	 */
	public function tearDown(): void {
		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tearDown();
	}

	protected function dispatch_request( string $method, string $route, ?array $post = null ): WP_REST_Response {
		$route = '/' . ltrim( $route, '/' );

		$request = new WP_REST_Request( $method, $route );
		if ( $post ) {
			$request->set_body_params( $post );
		}

		return rest_do_request( $request );
	}

	public function test_register_routes(): void {
		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;

		$routes = $wp_rest_server->get_routes();
		static::assertArrayHasKey( '/' . REST_Controller::NAMESPACE . '/sum/(?P<a>\\d+)/(?P<b>\\d+)', $routes );
	}

	public function test_sum(): void {
		$response = $this->dispatch_request( 'GET', REST_Controller::NAMESPACE . '/sum/2/3' );
		static::assertEquals( 200, $response->get_status() );
		static::assertEquals( 5, $response->get_data() );
	}

	public function test_not_found(): void {
		$response = $this->dispatch_request( 'GET', REST_Controller::NAMESPACE . '/sum/a/b' );
		static::assertEquals( 404, $response->get_status() );
	}
}
