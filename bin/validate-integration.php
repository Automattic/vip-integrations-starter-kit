<?php
/**
 * Runs the VIP integration conformance checker against this plugin.
 *
 * Thin wrapper around `vip validate integration` (shipped in the VIP-CLI): it
 * points the checker at the plugin root so `composer run validate-integration`
 * works from anywhere and exits non-zero when the integration is not
 * conformant, so it can gate CI.
 *
 * Requires the VIP-CLI: https://docs.wpvip.com/vip-cli/ (`npm install -g @automattic/vip`).
 */

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "validate-integration must be run from the command line.\n" );
	exit( 1 );
}

$plugin_root = dirname( __DIR__ );

$vip_binary = trim( (string) shell_exec( 'command -v vip 2>/dev/null' ) );
if ( '' === $vip_binary ) {
	fwrite( STDERR, "VIP-CLI not found. Install it to validate the integration:\n" );
	fwrite( STDERR, "  npm install -g @automattic/vip\n" );
	fwrite( STDERR, "Then re-run: composer run validate-integration\n" );
	exit( 1 );
}

$command = sprintf(
	'%s validate integration %s',
	escapeshellarg( $vip_binary ),
	escapeshellarg( $plugin_root )
);

passthru( $command, $exit_code );

exit( $exit_code );
