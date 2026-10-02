# Adopting the kit in an existing plugin

The Starter Kit is the right starting point for a brand new integration. It is
the wrong starting point for a plugin that already exists: you would be throwing
away a working repo to get a handful of files back.

This page lists the additive pieces an established plugin needs to become a
conformant VIP integration, and the kit files each one is copied or adapted
from. Nothing here asks you to restructure your plugin, adopt the kit's
directory layout, or delete anything you already have.

> **Rule of thumb:** if it is not in the checklist below, you do not need it.
> The admin pages, REST controller, settings screens, input factory, views,
> Codespaces config and Playwright page objects in this repo are demonstration
> material, not requirements.

## Working from this page

Whether you are a developer or a coding agent, the loop is the same:

1. Run the conformance checker in the plugin repo to get a baseline. It tells you
   which of the nine rules already pass, so you only do the work that is missing.
2. Work through the failing rules using the matching section below. Each one says
   what to add, which kit file to copy it from, and how the checker decides.
3. Re-run the checker after each change.

Three constraints hold throughout:

- **Additive only.** Do not restructure the plugin, move it to the kit's
  directory layout, rename its existing constants or namespace, or delete
  anything that works. Everything here is new files and small edits.
- **Do not copy the kit wholesale.** Take the named file, adapt its names, and
  leave the rest. "What you do not need" near the end of this page is explicit
  about what to ignore.
- **Green is not done.** Two items — the plugin ↔ platform config-schema match
  and the security review — are assessed by a human and cannot be satisfied by
  passing the checker.

## The gate you are aiming at

Conformance is an objective, automatable check. Run it from your plugin root at
any point:

```sh
npx @automattic/vip-integration validate
```

Nine rules are checked statically, and two items stay with a human reviewer
(plugin ↔ platform config-schema match, and security review). The checker exits
non-zero when any rule fails, so it can gate CI.

| Rule | What it checks                                              | Likely already passing? |
| ---- | ----------------------------------------------------------- | ----------------------- |
| 1    | Composer `wordpress-plugin` package with a root entry file  | Yes                     |
| 2    | `composer test` runs PHPUnit **and** an e2e runner          | Partly                  |
| 3    | `vip-manifest.yaml` present, schema-valid, placeholder-free | No — must be added      |
| 4    | Config constant referenced in code and documented           | No — must be added      |
| 5    | Missing or invalid config degrades without fataling         | No — must be added      |
| 6    | Docs carry a valid **and** an incomplete config example     | No — must be added      |
| 7    | CI matrix covers WP 6.9 + 7.0 and PHP 8.2–8.5 (exact list)  | Usually needs widening  |
| 8    | Build/install and test commands documented                  | Usually needs moving    |
| 9    | Telemetry is Tracks only, guarded, no secrets               | Optional                |

Rules 4, 5 and 6 report `n/a` when no config constant is found. That is not a
pass. An integration that takes runtime config and hides it behind another
mechanism has skipped the checks, not satisfied them.

## The essentials

### 1. The handoff manifest

**Add:** `vip-manifest.yaml` at your plugin root.
**Copy from:** [`vip-manifest.yaml`](../vip-manifest.yaml), documented field by
field in [manifest.md](manifest.md).

This is the single most important addition. VIP never sees your repository: it
registers, configures and loads your integration from this file alone. Without
it there is nothing to hand off.

Required top-level sections: `manifest_version`, `manifest_kind`, `integration`,
`documentation`, `runtime`, `runtime_config`, `release`. `telemetry` is
optional, but if present it must be complete.

```yaml
# yaml-language-server: $schema=./vip-manifest.schema.json

manifest_version: 1
manifest_kind: vip-integration-handoff

integration:
  slug: example-plugin # kebab-case, 3–63 chars, stable forever
  display_name: Example Plugin # ≤ 60 chars
  summary: One line describing what the integration does. # ≤ 200 chars
  partner:
    name: Example Vendor
    support_contact: support@example.com

documentation:
  public_url: https://example.com/docs/example-plugin
  support_url: https://example.com/docs/example-plugin/support

runtime:
  wordpress_plugin:
    folder: example-plugin
    entry_file: example-plugin.php
    php_namespace: 'ExampleVendor\ExamplePlugin'
    scope: site # or network

runtime_config:
  constant_name: VIP_EXAMPLE_PLUGIN_CONFIG # must match ^VIP_[A-Z0-9_]+_CONFIG$
  fields:
    - key: api_base_url
      label: API base URL
      type: url
      required: true
      help: Base URL for the vendor API.

release:
  plugin_version: 2.4.0 # must match the plugin header
  version_strategy: latest
  migration_required: false
  changelog: First release submitted to the VIP Integrations Center.
```

Notes worth knowing before you write it:

- The schema sets `additionalProperties: false` at the top level, so a typo in a
  key is a hard failure rather than a silently ignored line.
- `runtime_config.fields` requires **at least one entry**. A plugin configured
  entirely through filters and constants still has to declare something. Pick
  the one setting a customer is most likely to want in the Dashboard, or raise
  it with your Technology Partner Manager rather than inventing a field the
  plugin ignores.
- Copying [`vip-manifest.schema.json`](../vip-manifest.schema.json) alongside it
  is optional. The checker carries its own copy; the local file only gives you
  editor autocompletion and inline validation via the `yaml-language-server`
  comment.
- The `init` scaffolder writes `REPLACE_ME` into fields it cannot derive. Any
  surviving `REPLACE_ME` fails rule 3.

### 2. The runtime config reader

**Add:** one `Config` class.
**Copy from:** [`inc/class-config.php`](../inc/class-config.php).

VIP defines a single PHP constant — a plain associative array — before your
plugin loads. Every read of it goes through one class. Do not read `$_ENV`,
do not add a second config source, and do not touch the constant from anywhere
but this class.

The minimum shape the checker and the platform expect:

```php
final class Config {
	public const CONSTANT_NAME = 'VIP_EXAMPLE_PLUGIN_CONFIG';

	/** Keys the plugin cannot work without. */
	public const REQUIRED_FIELDS = [];

	/** Keys whose values must never be rendered or logged. */
	public const SENSITIVE_FIELDS = [];

	private array $config = [];
	private bool $available = false;

	public function __construct( $raw ) {
		if ( is_array( $raw ) ) {
			$this->config    = $raw;
			$this->available = true;
		}
	}

	public function is_available(): bool { /* … */ }
	public function missing_fields(): array { /* … */ }
	public function is_ready(): bool { /* … */ }
	public function get( string $key, $default_value = null ) { /* … */ }
}
```

Two details matter beyond the general shape:

- `REQUIRED_FIELDS` and `SENSITIVE_FIELDS` are cross-checked against
  `runtime_config.fields` in the manifest. A required field missing from the
  manifest, or a sensitive field not declared `type: secret`, raises a warning
  you will be asked about in review.
- "Required" means present **and** usable. Treat a missing key, a non-string
  value, and a whitespace-only string identically, or you will enable a
  half-configured integration.

### 3. Graceful degradation

**Add:** an `is_ready()` check on every config-dependent feature, plus an admin
notice.
**Copy from:** [`inc/class-plugin.php`](../inc/class-plugin.php) (`init()` and
`render_config_notice()`).

Missing or invalid config is a normal state, not an error: an integration can be
enabled before the customer has finished filling in the Dashboard form. It must
never fatal.

```php
public function init(): void {
	if ( Config::get_instance()->is_ready() ) {
		// Register the config-dependent features.
	} else {
		add_action( 'admin_notices', [ $this, 'render_config_notice' ] );
	}

	// Everything that does not need config registers unconditionally.
}
```

The checker looks for `is_ready()`, `missing_fields()`, `is_available()`, a
`defined()` guard, or an `is_array()` guard **within 600 characters of the
config constant or `CONSTANT_NAME`**. A guard implemented far away from the
config access will not be detected. Back it with a behavioural test; the static
signal is only a proxy.

### 4. Config fixtures

**Add:** a `fixtures/` directory (or your existing test data location).
**Copy from:** [`fixtures/`](../fixtures/) — see
[`fixtures/README.md`](../fixtures/README.md).

Four small files returning arrays cover every state the plugin has to survive:
`config-valid.php`, `config-minimal.php`, `config-incomplete.php`,
`config-invalid.php`. They are what makes the degradation above testable rather
than asserted.

These are not checked by the conformance checker directly, but rule 5's
behavioural proof and rule 6's documented examples both come out of them, and
they cost almost nothing.

### 5. Telemetry (optional, but expected)

**Add:** one `Telemetry` wrapper.
**Copy from:** [`inc/class-telemetry.php`](../inc/class-telemetry.php).

Rule 9 reports `n/a` if the plugin records nothing, so telemetry is not strictly
required. If you do record anything, it must be VIP Tracks only.

**Pick a single-word prefix.** Tracks event names are `<prefix><event>`, where
the prefix carries its own trailing underscore and identifies your product as a
single token — `livepreviews_`, not `live_previews_`; `myplugin_`, not
`my_plugin_`. Squash a multi-word name into one word rather than joining it with
underscores, otherwise the product name reads as part of the event name. This
applies to the Tracks prefix only: option keys, meta keys and other snake_case
identifiers keep their underscores.

```php
final class Telemetry {
	// Single word plus a trailing underscore — not `example_plugin_`.
	public const EVENT_PREFIX = 'exampleplugin_';

	private $client;

	private function __construct() {
		if ( class_exists( \Automattic\VIP\Telemetry\Telemetry::class ) ) {
			$this->client = new \Automattic\VIP\Telemetry\Telemetry(
				self::EVENT_PREFIX,
				[ 'plugin_version' => VIP_EXAMPLE_PLUGIN_VERSION ]
			);
		}
	}

	public function record_event( string $event_name, array $properties = [] ): void {
		if ( $this->client ) {
			$this->client->record_event( $event_name, $properties );
		}
	}
}
```

- The `class_exists` guard must sit within 600 characters of the telemetry call
  site or class, so keep the wrapper self-contained. Without the guard, non-VIP
  environments fatal.
- Stats and Pixel are a hard failure: any `Automattic\VIP\Stats`, `record_pixel`
  or `->pixel` usage fails rule 9 outright.
- Never put secrets, credentials, raw content, email addresses or customer data
  in event properties. A property named `token`, `password`, `secret`,
  `credential` or `email` near a `record_event()` call raises a warning.
- Declare every event by name in the manifest's `telemetry` section. The kit's
  schema enforces the single-word prefix as `^[a-z][a-z0-9]*_$`, so a
  multi-word prefix is flagged in your editor. Note that
  `vip-integration validate` bundles its own copy of the schema and, as of
  0.1.2, still accepts `^[a-z0-9_]+_$` — so the checker will pass a prefix the
  kit rejects. Follow the kit; the convention is the real constraint.
- Event and property names must match `^[a-z_][a-z0-9_]*$`. Anything else is
  rejected at record time and logged as `invalid_event_name`.

### 6. Entry file constants

**Add:** a loaded guard, a version constant, and a file constant.
**Copy from:** [`example-integration.php`](../example-integration.php).

```php
if ( defined( 'VIP_EXAMPLE_PLUGIN_LOADED' ) ) {
	return;
}

define( 'VIP_EXAMPLE_PLUGIN_LOADED', true );
define( 'VIP_EXAMPLE_PLUGIN_VERSION', '2.4.0' );
define( 'VIP_EXAMPLE_PLUGIN_FILE', __FILE__ );
```

VIP writes a loader class for your integration in its own mu-plugins, and that
class's `is_loaded()` check keys off a constant your entry file defines — that is
how a customer who also has your plugin in their own repository avoids loading it
twice. There is no manifest field for it, so say which constant you used when you
hand off. The version constant is what the telemetry wrapper reports.

**Your existing constant prefix stays.** Only the runtime config constant is
dictated: it must match `^VIP_[A-Z0-9_]+_CONFIG$`. An established plugin already
using, say, `MYPLUGIN_VERSION` and `MYPLUGIN_FILE` does not have to rename those.
Add `VIP_MYPLUGIN_CONFIG` for config and a loaded guard alongside them.

Your plugin header also needs `Version` matching `release.plugin_version` in the
manifest, plus `Requires at least` and `Requires PHP`.

**How rigid is the baseline?** The header values are not checked by anything —
set them to what your plugin genuinely supports. The rigid part is the CI matrix
in rule 7 below, which hard-codes its version list. If your plugin cannot support
the full matrix, that is a conversation and an exception flag, not a header edit.

### 7. `composer test` wiring

Rule 2 requires the `test` script to invoke PHPUnit **and** an e2e runner:
Playwright, Cypress, Codeception, Puppeteer or Behat.

Behat counts because, run through WP-CLI against a real WordPress install, it
exercises the whole stack — plugin loaded, commands registered, database
touched, output asserted. For an integration whose surfaces are WP-CLI commands
or request handling rather than admin screens, that *is* the end-to-end surface,
and a browser driver would verify nothing the Behat suite does not already
cover. An integration that does ship admin screens should still expect a
reviewer to ask why they are untested; the rule checks that an e2e suite is
wired up, not that it covers the right surfaces.

So if your plugin already has a Behat suite, this rule costs you a line in
`composer.json` rather than a new test suite. If it has neither Behat nor
browser tests, this is the one rule that asks for genuinely new test work.

> **Needs a checker newer than 0.1.2.** Behat was added after that release. Until
> a version carrying it is published, `npx @automattic/vip-integration validate`
> will still report `no playwright/cypress/codeception/puppeteer invocation` for
> a Behat-only suite.

```json
"scripts": {
  "test": [ "@test:unit", "@test:e2e" ],
  "test:unit": "phpunit",
  "test:e2e": "npm test"
}
```

A plugin with an existing Behat suite usually only has to add it to `test`:

```json
"scripts": {
  "test": [ "@test:unit", "@test:integration", "@test:behat" ],
  "test:behat": "behat --colors"
}
```

**Only the name `test` is fixed.** The checker looks up
`composer.json` → `scripts.test` and nothing else. How you get there is yours:
`test:unit` / `test:e2e` above are the kit's convention, not a requirement, and a
single-line `"test": "phpunit && npx playwright test"` passes identically. Keep
your existing script names if you have them.

What it does with that script:

- resolves `@script` references recursively, so delegation is fine
- follows `npm test` / `npm run <script>` into `package.json` and judges the
  script body, not the word "test"
- strips `echo`, `:`, `true` and comment segments before matching, so a banner
  line cannot smuggle a fake pass through
- requires the runner to be the command actually invoked — optionally via
  `npx`/`pnpm`/`yarn`/`@php` or a `vendor/bin/` path — so a stray
  `rm -rf cypress-artifacts` does not count as running Cypress

It verifies the commands are wired, not that they pass.

### 8. CI compatibility matrix

Rule 7 wants evidence, in `.github/workflows/`, that the plugin is tested on
PHP 8.2, 8.3, 8.4 and 8.5, against WordPress 6.9 as the floor and 7.0 or newer
at the top. `latest` satisfies the upper bound, so a matrix pinned to `latest`
keeps passing as WordPress releases move on — you do not need to pin 7.0.

```yaml
strategy:
  matrix:
    php: ['8.2', '8.3', '8.4', '8.5']
    wordpress: ['6.9', 'latest']
```

**This list is hard-coded, and every entry is mandatory.** The checker asserts
WordPress `6.9`, then `7.0` **or** `latest`, then each of PHP `8.2`, `8.3`,
`8.4` and `8.5` in turn. There is no "two versions either side" logic and no
tolerance for a near miss, so:

| Your matrix                      | Result                                         |
| -------------------------------- | ---------------------------------------------- |
| WP `6.8` + `latest`              | **Fail** — `6.9` is required by name           |
| WP `6.9` + `latest`              | Pass                                           |
| WP `6.9` + `7.0`                 | Pass                                           |
| PHP `8.3`, `8.5`                 | **Fail** — missing `8.2` and `8.4`             |
| PHP `8.2`, `8.3`, `8.4`, `8.5`   | Pass                                           |

Testing *more* than the list is fine; the check is a subset test, so extra
versions are ignored rather than penalised. Testing *fewer* fails, even when the
versions you skipped are older than the ones you cover.

Reading the tokens:

- They must sit against a recognised key: `php`, `php-version`, `php-versions`,
  `wp`, `wordpress`, `wp-version(s)`, `wordpress-version(s)`. Versions elsewhere
  in the workflow are ignored, deliberately — a `node` matrix or an action tag
  is not compatibility evidence.
- Same-line scalars, flow arrays and block sequences are all understood, and a
  patch-level or wildcard token matches on its `x.y` part, so `6.9.x` satisfies
  `6.9`.
- The scan reads `.yml` and `.yaml` files under `.github/workflows/`, up to two
  directories deep. A matrix defined in a reusable workflow in another
  repository is invisible to it.

If you genuinely cannot support a version, set
`extra.vip.compatibility-exception: approved` in `composer.json`. That downgrades
the failure to a warning a reviewer must sign off; it is not a free pass, and it
is the only supported escape hatch.

> **The version list ages.** `@automattic/vip-integration` pins these numbers in
> its own source, so the floor only moves when the CLI is released again. As WordPress
> moves on, `latest` keeps satisfying the upper bound automatically, but the `6.9`
> floor stays required until a new CLI version drops it — which means you may be
> asked to keep a CI leg on a WordPress version you would otherwise have retired.
> If the pinned floor has fallen behind the versions VIP actually supports, that
> is a bug in the checker; report it rather than contorting your matrix around it.

### 9. Documentation

Two rules depend on what is in your docs, and both read **only** `README.md` at
the root and every `.md` file under `docs/`. Content in `CONTRIBUTING.md` does
not count — this is the most common surprise for an established repo that keeps
its developer instructions there.

**Rule 8** needs both:

- a build or install command — `composer install`, `npm ci`, `npm install` or
  `npm run build`
- a test command — `composer test`, `phpunit`, or a named e2e runner (the same
  list as rule 2, so documenting `behat` counts)

**Rule 6** needs two fenced code blocks that mention the config constant by
name, one of which is explicitly labelled as the incomplete case. The labelling
matters: the checker cannot tell a shorter valid example from a missing-required
one by counting keys, so it looks for the words "incomplete", "missing
required", or "setup in progress" in your docs.

````markdown
Example valid config:

```php
define( 'VIP_EXAMPLE_PLUGIN_CONFIG', [
	'api_base_url' => 'https://api.vendor.example',
] );
```

Example incomplete config (setup in progress — a required value is missing):

```php
define( 'VIP_EXAMPLE_PLUGIN_CONFIG', [] );
```
````

**Rule 4** additionally needs the constant name to appear somewhere in those
docs, which the examples above satisfy.

The kit's [vip-integration.md](vip-integration.md) is a ready-made template for
all of this; copying it into your `docs/` and editing the names is the quickest
route.

## What you do not need

Everything below is demonstration or convenience. Leaving it out costs you
nothing at review:

| Kit file or directory                           | Why you can skip it                                             |
| ----------------------------------------------- | --------------------------------------------------------------- |
| `inc/class-admin.php`, `class-adminsettings.php` | Example admin screen.                                           |
| `inc/class-settings.php`, `class-settingsvalidator.php`, `class-inputfactory.php` | Example settings plumbing.     |
| `inc/class-rest-controller.php`, `views/`       | Example REST endpoint and templates.                            |
| `bin/setup.php`, `composer setup`               | Renames the kit's example tokens. You have no example tokens.    |
| `.devcontainer/`                                | Codespaces convenience.                                         |
| `.wpvip/plugin-loader.php`                      | Useful only if you develop against `vip dev-env`.               |
| `phpstan.neon.dist`, `phpcs.xml.dist`           | Keep your existing static analysis and coding standards config. |
| `tests/e2e/lib/*.pom.ts`                        | Page objects for the kit's example screens.                     |
| `docs/directories.md`                           | Describes the kit's layout, not yours.                          |

The "do not delete the app folders" warning in the README applies to a clone of
this repo. It does not apply to your plugin.

## Order of work

0. Run `npx @automattic/vip-integration validate` for a baseline before changing
   anything, and note which rules already pass.
1. Write `vip-manifest.yaml` and get rule 3 green. It forces every other
   decision — slug, namespace, constant name, config fields.
2. Add the `Config` class, the constant guard and the admin notice (rules 4, 5).
3. Add the fixtures and a test proving the degradation behaviour.
4. Move or duplicate your build and test commands into `README.md` or `docs/`,
   with the two config examples (rules 6, 8).
5. Widen the CI matrix (rule 7).
6. Add the entry file constants and, if you record anything, the telemetry
   wrapper (rule 9).
7. Wire `composer test` to an e2e suite (rule 2). If you already have Behat,
   that is a one-line change and can move up the list; if you have no e2e suite
   at all, it is the largest piece of new work, hence last.

Re-run `npx @automattic/vip-integration validate` after each step.
