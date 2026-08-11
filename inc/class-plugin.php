<?php

namespace ExampleVendor\ExampleIntegration;

final class Plugin {
	/** @var self|null */
	private static $instance;

	// @codeCoverageIgnoreStart
	// This code is executed in bootstrap.php, before PHPUnit initializes test coverage
	public static function get_instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the plugin's hooks with WordPress.
	 *
	 * The plugin entry file calls this during load.
	 */
	public function register(): void {
		add_action( 'init', [ $this, 'init' ] );
		if ( is_admin() ) {
			add_action( 'init', [ Admin::class, 'register' ] );
		}
	}

	public function init(): void {
		if ( Config::get_instance()->is_ready() ) {
			add_action( 'rest_api_init', [ REST_Controller::class, 'register' ] );
		} else {
			// Missing or incomplete runtime config must never fatal: disable the
			// config-dependent behavior and surface a diagnostic instead.
			add_action( 'admin_notices', [ $this, 'render_config_notice' ] );
		}

		add_action( 'wp_footer', [ $this, 'wp_footer' ] );
	}
	// @codeCoverageIgnoreEnd

	public function render_config_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$config  = Config::get_instance();
		$details = $config->is_available()
			? sprintf(
				/* translators: %s: comma-separated list of missing config fields */
				__( 'missing required fields: %s', 'example-integration' ),
				implode( ', ', $config->missing_fields() )
			)
			: sprintf(
				/* translators: %s: name of the runtime config constant */
				__( 'the %s constant is not defined', 'example-integration' ),
				Config::CONSTANT_NAME
			);

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s: reason the configuration is incomplete */
					__( 'Example Integration setup is incomplete (%s). Its REST API endpoints are disabled until the configuration is completed in the VIP Dashboard.', 'example-integration' ),
					$details
				)
			)
		);
	}

	public function wp_footer(): void {
		$label = (string) Config::get_instance()->get( 'signature_label', 'Example Integration' );
		printf( '<p class="example-integration-signature">%s</p>', esc_html( $label ) );
	}
}
