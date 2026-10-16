# The handoff manifest

`vip-manifest.yaml` is the one file WordPress VIP uses to register and load your
integration. VIP reads it, so everything VIP needs to wire up
the plugin loader, the config form, and secret storage has to be declared here,
correctly.

This kit ships a filled-in example ([`vip-manifest.yaml`](../vip-manifest.yaml))
and the schema it is validated against
([`vip-manifest.schema.json`](../vip-manifest.schema.json)).

```sh
npx @automattic/vip-integration validate
```

`composer setup` (or `vip-integration init`) rewrites the example names in the
manifest to yours — slug, folder, entry file, namespace, and config constant —
so most of the plumbing fields are filled in for you. You own the identity
fields and the config schema.

## Fields

### Top level

| Field              | Required | Notes                                |
| ------------------ | -------- | ------------------------------------ |
| `manifest_version` | yes      | Manifest format version. Always `1`. |
| `manifest_kind`    | yes      | Always `vip-integration-handoff`.    |

### `integration` — catalog identity

| Field                     | Required | Notes                                                                                                       |
| ------------------------- | -------- | ----------------------------------------------------------------------------------------------------------- |
| `slug`                    | yes      | Stable kebab-case id, unique in the Integration Center (3–30 chars). Changing it later re-lists the add-on. |
| `display_name`            | yes      | Name shown in the catalog (≤ 60 chars).                                                                     |
| `summary`                 | yes      | One-line description on the catalog card (≤ 200 chars).                                                     |
| `partner.name`            | yes      | Your vendor name.                                                                                           |
| `partner.support_contact` | yes      | An email address or support URL VIP can reach you at.                                                       |

### `documentation` — where the docs live

Links to documentation hosted on a website. Don't use a local Markdown path as the only reference.

| Field         | Required | Notes                                                 |
| ------------- | -------- | ----------------------------------------------------- |
| `public_url`  | yes      | Customer- or administrator-facing documentation URL.  |
| `support_url` | no       | Troubleshooting or partner-support documentation URL. |

### `runtime.wordpress_plugin` — how VIP loads the plugin

Set by `composer setup` — you rarely edit these by hand.

| Field           | Required | Notes                                                                       |
| --------------- | -------- | --------------------------------------------------------------------------- |
| `folder`        | yes      | Plugin folder name (kebab-case), matches your slug.                         |
| `entry_file`    | yes      | Root plugin file with the `Plugin Name:` header, e.g. `my-integration.php`. |
| `php_namespace` | yes      | Root PHP namespace, e.g. `MyVendor\MyIntegration`.                          |
| `scope`         | yes      | `site` (per-site) or `network` (network-wide).                              |

### `runtime_config` — the config the platform manages for you

VIP defines a single PHP constant (a config array) before your plugin loads, and
renders a form in the Dashboard from the fields you declare here. See
[vip-integration.md](vip-integration.md#runtime-config) for how the plugin reads
it.

| Field           | Required | Notes                                                                                        |
| --------------- | -------- | -------------------------------------------------------------------------------------------- |
| `constant_name` | yes      | The constant your plugin reads. Must match `VIP_*_CONFIG`, e.g. `VIP_MY_INTEGRATION_CONFIG`. |
| `fields`        | yes      | At least one config field (below).                                                           |

Each entry in `fields`:

| Field                      | Required              | Notes                                                                                                       |
| -------------------------- | --------------------- | ----------------------------------------------------------------------------------------------------------- |
| `key`                      | yes                   | snake_case key this value is stored under in the config array.                                              |
| `label`                    | yes                   | Field label shown in the form.                                                                              |
| `type`                     | yes                   | One of `string`, `text`, `url`, `email`, `number`, `boolean`, `secret`, `enum`, `list`.                     |
| `required`                 | no                    | `true` if the field must be filled before the integration can activate.                                     |
| `help`                     | no                    | Helper text shown under the field.                                                                          |
| `default`                  | no                    | Default value pre-filled in the form. Must pass the field's own rules; a list for `type: list`.             |
| `example`                  | when `pattern` is set | Example value shown as the input placeholder. Must pass the field's own rules.                              |
| `nullable`                 | no                    | `true` if saving `null` clears the value, so the plugin falls back to its own default.                      |
| `values`                   | when `type: enum`     | The allowed values for an enum field.                                                                       |
| `integer`                  | no                    | `number` only. `true` if the value must be a whole number.                                                  |
| `min`, `max`               | no                    | `number` only. The smallest and largest allowed values, inclusive.                                          |
| `unit`                     | no                    | `number` only. One of `seconds`, `minutes`, `hours`, `days`.                                                |
| `min_length`, `max_length` | no                    | `string`, `text`, `url`, `email`, `secret` only. The fewest and most characters allowed.                    |
| `pattern`                  | no                    | Same types as `min_length`. A regular expression (JavaScript syntax) the whole value must match.            |
| `format`                   | no                    | Same types as `min_length`. One of `ip_or_cidr`, `url`, `email`.                                            |
| `items`                    | when `type: list`     | What each entry must be: `type: string`, plus optional `min_length`, `max_length`, `pattern`, and `format`. |
| `max_items`                | no                    | `list` only. The most entries the list may have.                                                            |
| `autogen`                  | no                    | `true` if the field should be autogenerated and not editable by the customer.                               |
| `note`                     | no                    | A note explaining the field's purpose or usage. For internal use.                                           |

A rule on a type it doesn't apply to fails validation (`min` on a `string`
field), so it is never silently ignored. For example, a number of seconds and a
list of IP ranges:

```yaml
- key: grace_period
  label: Grace period
  type: number
  integer: true
  min: 1
  unit: seconds
  nullable: true
- key: ip_allowlist
  label: IP allowlist
  type: list
  items:
    type: string
    format: ip_or_cidr
  max_items: 50
  example: [192.0.2.0/24, '2001:db8::/32']
```

> **Secrets:** use `type: secret` for tokens, keys, and passwords. VIP stores
> those encrypted and never exposes them back through the API. Do not put secret
> values in this file — you only declare the field here; the customer enters the
> value in the Dashboard.

### `telemetry` — the Tracks events you record

Declares your VIP Tracks events by name so VIP knows what usage the integration
reports. **Names only — never values.** Never declare secrets, credentials, raw
content, email addresses, or customer data as properties. Omit this whole
section if the integration records no telemetry.

| Field                | Required | Notes                                                           |
| -------------------- | -------- | --------------------------------------------------------------- |
| `prefix`             | yes      | Event name prefix: a single word plus a trailing underscore, e.g. `acmewidget_`. Squash a multi-word name — `acme_widget_` is rejected. |
| `default_properties` | yes      | Property names added to every event (may be an empty list).     |
| `events`             | yes      | At least one event (below).                                     |

Each entry in `events`:

| Field        | Required | Notes                                                |
| ------------ | -------- | ---------------------------------------------------- |
| `name`       | yes      | Full event name, including the prefix.               |
| `type`       | yes      | Always `tracks`.                                     |
| `trigger`    | yes      | What causes the event to fire.                       |
| `properties` | yes      | Property names recorded with the event (names only). |
| `note`       | no       | Why the event exists.                                |

### `release` — the submitted version

| Field                | Required | Notes                                                      |
| -------------------- | -------- | ---------------------------------------------------------- |
| `plugin_version`     | yes      | Semantic version matching the plugin header, e.g. `1.2.0`. |
| `version_strategy`   | yes      | Release strategy identifier, e.g. `latest`.                |
| `migration_required` | yes      | `true` if this release needs migration work.               |
| `changelog`          | yes      | Short description of what this release contains.           |

## Keeping it honest

The manifest is a contract. `vip-integration validate` checks that it is present,
parses as YAML, and matches the schema exactly — including rejecting unknown or
misspelled keys, since VIP loads your integration from these field names alone.
It confirms the shape, not the values: that a URL resolves or a token is real is
verified in human review.
