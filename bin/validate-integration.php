<?php
/**
 * Placeholder for the VIP-provided integration conformance validator.
 *
 * TODO: replace with the real validator once VIP publishes it. Until then
 * this placeholder exists so `composer run validate-integration` is present —
 * an integration requirement — and CI pipelines can already call it.
 */

fwrite( STDOUT, 'validate-integration: the VIP conformance validator is not yet available.' . PHP_EOL );
fwrite( STDOUT, 'This placeholder always succeeds and will be replaced by the VIP-provided checker.' . PHP_EOL );

exit( 0 );
