<?php

namespace ExampleVendor\ExampleIntegration;

final class AdminSettings {
	const OPTION_GROUP = 'example_integration_settings';

	/** @var self|null */
	private static $instance;

	/** @var InputFactory */
	private $input_factory;

	public static function get_instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self( new InputFactory( Settings::OPTIONS_KEY, Settings::get_instance() ) );
		}

		return self::$instance;
	}

	/**
	 * Register the plugin's setting, section, and fields.
	 */
	public static function register(): void {
		self::get_instance()->register_settings();
	}

	/**
	 * @param InputFactory $input_factory Renders the settings fields.
	 */
	public function __construct( InputFactory $input_factory ) {
		$this->input_factory = $input_factory;
	}

	public function register_settings(): void {
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
			__( 'General Settings', 'example-integration' ),
			'__return_empty_string', // NOSONAR
			Admin::OPTIONS_MENU_SLUG
		);

		add_settings_field(
			'enabled',
			__( 'Enable plugin', 'example-integration' ),
			[ $this->input_factory, 'checkbox' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'enabled',
			]
		);

		add_settings_field(
			'message',
			__( 'Message', 'example-integration' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'message',
				'required'  => true,
				'help'      => __(
					'Help text goes here.',
					'example-integration'
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
