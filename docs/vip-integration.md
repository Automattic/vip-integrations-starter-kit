# VIP Integration

Example Integration is the reference implementation of a WordPress VIP partner
integration, built from the [VIP Integrations Starter Kit](https://github.com/Automattic/vip-integrations-starter-kit).
It demonstrates the patterns WordPress VIP requires of partner integrations: a single runtime config constant read through a central `Config`
class, graceful degradation when required config is missing, and Tracks-only
telemetry through the VIP Telemetry API.

## Running and testing locally

1. `composer install && npm ci`
2. `vip dev-env create && vip dev-env start`
3. `composer test`

> **Prerequisite:** `composer test` runs PHPUnit **and** the Playwright e2e
> suite. The e2e half needs the `vip dev-env` from step 2 up and reachable —
> without it, `composer test` fails on the e2e stage. That is an environment
> gap, not a broken kit; run `composer test:unit` for PHPUnit alone.

PHPUnit also needs the WordPress test library (`WP_TESTS_DIR`); inside the
dev-env container (`vip dev-env shell`) it is preconfigured.

## Build, Test, And Validate Commands

| Purpose                   | Command                                                                                                        |
| ------------------------- | -------------------------------------------------------------------------------------------------------------- |
| Install PHP dependencies  | `composer install`                                                                                             |
| Install Node dependencies | `npm ci`                                                                                                       |
| Build release assets      | not applicable — this integration ships no compiled JS/CSS assets (`npm run build` documents this)             |
| Tests                     | `composer test`                                                                                                |
| Integration validation    | `npx @automattic/vip-integration validate` (the external conformance checker — see [manifest.md](manifest.md)) |

## Runtime Config

Config constant: `VIP_EXAMPLE_INTEGRATION_CONFIG`

The VIP platform defines the constant (a plain PHP associative array) before
the plugin loads. All reads go through `ExampleVendor\ExampleIntegration\Config`.

Required values:

- `api_base_url`: Base URL for the vendor API.
- `api_token`: Token used to authenticate vendor API requests.

Optional values:

- `signature_label`: Text rendered in the site footer signature.

Example valid local mock config:

```php
define( 'VIP_EXAMPLE_INTEGRATION_CONFIG', [
	'api_base_url'    => 'https://api.vendor.example',
	'api_token'       => 'mock-token',
	'signature_label' => 'Example Integration (dev)',
] );
```

Example incomplete config (setup in progress — a required value is missing):

```php
define( 'VIP_EXAMPLE_INTEGRATION_CONFIG', [
	'api_base_url' => 'https://api.vendor.example',
] );
```

With incomplete config the plugin **must not fatal**: it disables its REST API
endpoints and shows an admin notice naming the missing fields. See
[`fixtures/`](../fixtures/README.md) for all mocked states and where they are
wired in.

## Telemetry

Telemetry uses the helper in `inc/class-telemetry.php`, which wraps the VIP
Telemetry API (Tracks events only, no Stats) behind a `class_exists` guard so
environments without VIP MU plugins no-op. Event names are prefixed with
`example_integration_`. Never include secrets, raw content, email addresses,
or customer credentials in event properties.

| Name                                | Type   | Trigger                                    | Properties                         | Notes                                    |
| ----------------------------------- | ------ | ------------------------------------------ | ---------------------------------- | ---------------------------------------- |
| `example_integration_sum_requested` | Tracks | The REST `/sum` endpoint serves a request. | `route`, `plugin_version` (global) | Usage metadata only; no request payload. |

## Making it your own

`composer setup` rewrites the example prefix set to your integration's names
via plain string replacement:

| Token                                                                         | Becomes                             |
| ----------------------------------------------------------------------------- | ----------------------------------- |
| `example-integration` (slug, folder, text domain, entry file, REST namespace) | your integration slug               |
| `ExampleVendor\ExampleIntegration` (PHP namespace)                            | your vendor + integration namespace |
| `VIP_EXAMPLE_INTEGRATION_*` (constants)                                       | `VIP_<YOUR_NAME>_*`                 |
| `example_integration_` (telemetry prefix, option keys)                        | `<your_name>_`                      |
| `example-vendor/example-integration` (Composer name)                          | `<your-vendor>/<your-slug>`         |

Run it interactively (`composer setup`) or non-interactively
(`composer setup -- --vendor="Acme" --name="Content Sync"`).
