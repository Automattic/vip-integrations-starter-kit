<?php

use ExampleVendor\ExampleIntegration\Admin;
use ExampleVendor\ExampleIntegration\AdminSettings;
use ExampleVendor\ExampleIntegration\Config;

defined( 'ABSPATH' ) || die();

$config = Config::get_instance();
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<h2><?php esc_html_e( 'Runtime configuration', 'example-integration' ); ?></h2>
	<p>
		<?php
		printf(
			/* translators: %s: name of the runtime config constant */
			esc_html__( 'These values are injected by the VIP platform through the %s constant and are read-only here. The settings below are stored per-site — keep platform-managed values in the constant and only site-specific tweaks as options.', 'example-integration' ),
			'<code>' . esc_html( Config::CONSTANT_NAME ) . '</code>'
		);
		?>
	</p>
	<?php if ( $config->is_available() ) : ?>
	<table class="widefat striped" style="max-width: 40em;">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Key', 'example-integration' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Value', 'example-integration' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $config->for_display() as $key => $value ) : ?>
			<tr>
				<td><code><?php echo esc_html( $key ); ?></code></td>
				<td><?php echo esc_html( $value ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php else : ?>
	<p><em><?php esc_html_e( 'No runtime configuration is defined yet.', 'example-integration' ); ?></em></p>
	<?php endif; ?>

	<form action="options.php" method="post">
	<?php
		settings_fields( AdminSettings::OPTION_GROUP );
		do_settings_sections( Admin::OPTIONS_MENU_SLUG );
		submit_button( __( 'Save settings', 'example-integration' ) );
	?>
	</form>
</div>
