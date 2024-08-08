<?php

namespace MyNamespace\TestDemo;

final class AdminSettings {
	const OPTION_GROUP = 'testdemo_settings';

	private static $instance;

	public static function get_instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/** @var InputFactory */
	private $input_factory;

	/**
	 * Constructed during `admin_init`
	 *
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		$this->register_settings();
	}

	public function register_settings(): void {
		$this->input_factory = new InputFactory( Settings::OPTION_KEY, Settings::get_instance() );
		register_setting(
			self::OPTION_GROUP,
			Settings::OPTION_KEY,
			[
				'default'           => [],
				'sanitize_callback' => [ SettingsValidator::class, 'sanitize' ],
			]
		);

		$settings_section = 'general-settings';
		add_settings_section(
			$settings_section,
			__( 'General Settings', 'test-demo' ),
			'__return_empty_string', // NOSONAR
			Admin::OPTIONS_MENU_SLUG
		);

		add_settings_field(
			'enabled',
			__( 'Enable plugin', 'test-demo' ),
			[ $this->input_factory, 'checkbox' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'enabled',
			]
		);

		add_settings_field(
			'host',
			__( 'Message', 'test-demo' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'message',
				'required'  => true,
				'help'      => __(
					'Help text goes here.',
					'test-demo'
				),
			]
		);
	}
}
