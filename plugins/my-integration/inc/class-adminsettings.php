<?php

namespace MyNamespace\TestDemo;

final class AdminSettings {
	const OPTION_GROUP = 'testdemo_settings';

	/** @var self|null */
	private static $instance;

	public static function get_instance(): self {
		if ( ! self::$instance ) {
			// @codeCoverageIgnoreStart
			// Depending on the test order, this may not be testable
			self::$instance = new self();
			// @codeCoverageIgnoreEnd
		}

		return self::$instance;
	}

	/** @var InputFactory */
	private $input_factory;

	/**
	 * @codeCoverageIgnore -- coverage depends on the test order
	 */
	private function __construct() {
		$this->register_settings();
	}

	public function register_settings(): void {
		$this->input_factory = new InputFactory( Settings::OPTIONS_KEY, Settings::get_instance() );
		register_setting(
			self::OPTION_GROUP,
			Settings::OPTIONS_KEY,
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
			'message',
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

	public static function settings_page(): void {
		if ( current_user_can( 'manage_options' ) ) {
			require __DIR__ . '/../views/options.php'; // NOSONAR
		}
	}
}
