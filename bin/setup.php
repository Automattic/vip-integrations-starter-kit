<?php
/**
 * One-shot scaffold: rewrites the Starter Kit's example prefix set to your
 * integration's names. Plain string replacement, on purpose — see
 * docs/vip-integration.md for the token table this maintains.
 *
 * Usage:
 *   composer setup                               (interactive)
 *   composer setup -- --vendor="Acme" --name="Content Sync"
 */

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "setup must be run from the command line.\n" );
	exit( 1 );
}

$options = getopt( '', [ 'vendor:', 'name:' ] );

$vendor = isset( $options['vendor'] ) && is_string( $options['vendor'] ) ? $options['vendor'] : prompt( 'Vendor name (e.g. "Acme")' );
$name   = isset( $options['name'] ) && is_string( $options['name'] ) ? $options['name'] : prompt( 'Integration name (e.g. "Content Sync")' );

if ( '' === $vendor || '' === $name ) {
	fwrite( STDERR, "Vendor and integration name are both required.\n" );
	exit( 1 );
}

$vendor_pascal = pascal_case( $vendor );
$name_pascal   = pascal_case( $name );

// The pascal-case forms become PHP namespace parts; identifiers cannot start
// with a digit (e.g. "123-demo" would produce the invalid namespace 123Demo).
foreach ( [ 'Vendor' => $vendor_pascal, 'Integration' => $name_pascal ] as $label => $identifier ) {
	if ( ! preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier ) ) {
		fwrite( STDERR, "{$label} name must start with a letter to produce a valid PHP namespace (got \"{$identifier}\").\n" );
		exit( 1 );
	}
}
$vendor_kebab  = kebab_case( $vendor );
$name_kebab    = kebab_case( $name );
$name_snake    = str_replace( '-', '_', $name_kebab );
$name_upper    = strtoupper( $name_snake );
$name_words    = ucwords( str_replace( '-', ' ', $name_kebab ) );
$vendor_words  = ucwords( str_replace( '-', ' ', $vendor_kebab ) );

// Ordered: longer/more specific tokens first so substrings never mismatch.
$replacements = [
	'example-vendor/example-integration' => "{$vendor_kebab}/{$name_kebab}",
	'ExampleVendor'                      => $vendor_pascal,
	'ExampleIntegration'                 => $name_pascal,
	'VIP_EXAMPLE_INTEGRATION'            => "VIP_{$name_upper}",
	'example_integration'                => $name_snake,
	'example-integration'                => $name_kebab,
	'Example Integration'                => $name_words,
	'Example Vendor'                     => $vendor_words,
];

$skip_patterns = '#^(vendor/|node_modules/|bin/setup\.php|composer\.lock|package-lock\.json|\.playwright/)|\.(png|jpg|jpeg|gif|webp)$#';

exec( 'git ls-files', $files, $exit_code );
if ( 0 !== $exit_code ) {
	fwrite( STDERR, "Failed to list files via git.\n" );
	exit( 1 );
}

$changed = 0;
foreach ( $files as $file ) {
	if ( preg_match( $skip_patterns, $file ) || ! is_file( $file ) ) {
		continue;
	}

	$contents = (string) file_get_contents( $file );
	$updated  = strtr( $contents, $replacements );

	if ( $updated !== $contents ) {
		file_put_contents( $file, $updated );
		++$changed;
	}
}

if ( file_exists( 'example-integration.php' ) ) {
	rename( 'example-integration.php', "{$name_kebab}.php" );
	fwrite( STDOUT, "Renamed entry file to {$name_kebab}.php\n" );
}

fwrite( STDOUT, "Rewrote {$changed} file(s).\n" );
fwrite( STDOUT, "Prefix set: slug={$name_kebab}, namespace={$vendor_pascal}\\{$name_pascal}, constant=VIP_{$name_upper}_CONFIG\n" );
fwrite( STDOUT, "Next steps:\n" );
fwrite( STDOUT, "  1. Review the changes: git diff\n" );
fwrite( STDOUT, "  2. Refresh the Composer lock hash: composer update --lock\n" );
fwrite( STDOUT, "  3. Recreate your local environment if one exists: vip dev-env destroy && vip dev-env create\n" );

exit( 0 );

function prompt( string $label ): string {
	fwrite( STDOUT, "{$label}: " );
	$line = fgets( STDIN );

	return false === $line ? '' : trim( $line );
}

function pascal_case( string $value ): string {
	return str_replace( ' ', '', ucwords( strtolower( (string) preg_replace( '/[^a-zA-Z0-9]+/', ' ', $value ) ) ) );
}

function kebab_case( string $value ): string {
	return trim( strtolower( (string) preg_replace( '/[^a-zA-Z0-9]+/', '-', $value ) ), '-' );
}
