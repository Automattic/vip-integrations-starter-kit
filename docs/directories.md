# Repository structure

| Path                       | Purpose                                                                                       |
| -------------------------- | --------------------------------------------------------------------------------------------- |
| `example-integration.php`  | Plugin entry file: header, guards, constants, autoloader, start. Kept intentionally small.    |
| `inc/`                     | The integration's WordPress runtime code (autoloaded via Composer classmap).                  |
| `views/`                   | Admin page templates.                                                                         |
| `fixtures/`                | Mock runtime configs for local development and tests (see `fixtures/README.md`).              |
| `tests/phpunit/`           | PHPUnit tests (run through `composer test:unit`).                                             |
| `tests/e2e/`               | Playwright end-to-end tests (run through `composer test:e2e`; needs a running `vip dev-env`). |
| `vip-manifest.yaml`        | The handoff manifest VIP registers and loads the integration from (see `docs/manifest.md`).   |
| `vip-manifest.schema.json` | JSON Schema the manifest is validated against.                                                |
| `bin/`                     | Repo tooling: the `setup.php` scaffold.                                                       |
| `docs/`                    | Operational docs, including the required `vip-integration.md` and `manifest.md`.              |
| `AGENTS.md`                | Orientation for AI coding agents working in this repo.                                        |
| `.wpvip/`                  | VIP local development environment config and plugin loader.                                   |
| `.devcontainer/`           | GitHub Codespaces configuration.                                                              |
| `.github/workflows/`       | CI: unit tests, e2e, linting, static analysis, CodeQL, dependency review.                     |
