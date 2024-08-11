<?php

namespace MyNamespace\TestDemo;

final class Plugin {
	private static $instance;

	// @codeCoverageIgnoreStart
	// This code is executed in bootstrap.php, before PHPUnit initializes test coverage
	public static function get_instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		if ( is_admin() ) {
			add_action( 'init', [ Admin::class, 'get_instance' ] );
		}
	}

	public function init(): void {
		add_action( 'rest_api_init', [ REST_Controller::class, 'get_instance' ] );
		add_action( 'wp_footer', [ $this, 'wp_footer' ] );
	}
	// @codeCoverageIgnoreEnd

	public function wp_footer(): void {
		echo '<p class="wp-test-demo-signature">Sample Plugin</p>';
	}
}
