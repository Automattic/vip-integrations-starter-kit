# Config fixtures

On the VIP platform, runtime configuration is injected as a single PHP
constant (`VIP_EXAMPLE_INTEGRATION_CONFIG`) holding a plain associative array,
defined **before** the plugin is loaded. These fixtures mock that constant for
local development and automated testing.

| Fixture | State it simulates |
| --- | --- |
| `config-valid.php` | Fully configured: all required and optional fields set. |
| `config-minimal.php` | Required fields only; optional fields fall back to defaults. |
| `config-incomplete.php` | Setup in progress: a required field is missing. The plugin must degrade gracefully, never fatal. |
| `config-invalid.php` | Constant holds a non-array value. Exercises the `is_array()` guard. |
| `config-local.php` | Optional, **git-ignored** local override — see below. |

## Local overrides (`config-local.php`)

To test with values you don't want committed (real-ish tokens, a staging API
URL, experiments), copy any fixture to `fixtures/config-local.php` and edit it:

```sh
cp fixtures/config-valid.php fixtures/config-local.php
```

When it exists, the dev-env plugin loader uses it instead of
`config-valid.php`. It is listed in `.gitignore`, so it never ends up in a
commit.

## Where they are used

- **Local development:** [`.wpvip/plugin-loader.php`](../.wpvip/plugin-loader.php)
  defines the constant from `config-local.php` when present, otherwise
  `config-valid.php`, before loading the plugin — mirroring how VIP injects
  config in production. Swap the fixture there to observe other states
  (e.g. the incomplete-setup admin notice).
- **Unit tests:** [`tests/phpunit/bootstrap.php`](../tests/phpunit/bootstrap.php)
  defines the constant from `config-valid.php`; `ConfigTest` constructs
  `Config` directly from each fixture to cover every state.

Never put real production secrets in fixtures — including `config-local.php`;
git-ignored is not encrypted.
