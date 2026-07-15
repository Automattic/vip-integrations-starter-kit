# Repository structure

| Path | Purpose |
| --- | --- |
| `example-integration.php` | Plugin entry file: header, guards, constants, autoloader, start. Kept intentionally small. |
| `inc/` | The integration's WordPress runtime code (autoloaded via Composer classmap). |
| `views/` | Admin page templates. |
| `fixtures/` | Mock runtime configs for local development and tests (see `fixtures/README.md`). |
| `tests/phpunit/` | PHPUnit tests (run through `composer test:unit`). |
| `tests/e2e/` | Playwright end-to-end tests (run through `composer test:e2e`; needs a running `vip dev-env`). |
| `bin/` | Repo tooling: `setup.php` scaffold and the `validate-integration.php` conformance-checker wrapper. |
| `docs/` | Operational docs, including the required `vip-integration.md`. |
| `.wpvip/` | VIP local development environment config and plugin loader. |
| `.devcontainer/` | GitHub Codespaces configuration. |
| `.github/workflows/` | CI: unit tests, e2e, linting, static analysis, CodeQL, dependency review. |

These folders constitute a complete WordPress VIP application — do not remove
them. For how VIP codebases are structured, see
<https://docs.wpvip.com/technical-references/vip-codebase/>.
