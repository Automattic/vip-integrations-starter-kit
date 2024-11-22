<?php

namespace MyNamespace\TestDemo;

class Integration extends \Automattic\VIP\Integrations\Integration {
	/** The version of the plugin to load */
	protected string $version = '1.0';

	/**
	 * Returns `true` if the plugin is already available e.g. via customer code. We will use
	 * this function to prevent activating of integration from platform side.
	 */
	public function is_loaded(): bool {
		return class_exists( Plugin::class, false );
	}

	/**
	 * Applies hooks to load the plugin.
	 */
	public function load(): void {
		// Wait until plugins_loaded to give precedence to the plugin in the customer repo.
		add_action( 'plugins_loaded', function () {
			// Return if the integration is already loaded.
			//
			// In activate() method we do make sure to not activate the integration if its already loaded
			// but still adding it here as a safety measure i.e. if load() is called directly.
			if ( $this->is_loaded() ) {
				return;
			}

			// Load the version of the plugin that should be set to the latest version, otherwise if it's not found deactivate the integration.
			// FIXME -- the path has to be updated
			$load_path = __DIR__ . '/index.php';
			if ( file_exists( $load_path ) ) {
				require_once $load_path;
			} else {
				$this->is_active = false;
			}
		} );
	}
}
