# Linting and coding standards

Linting is powered by [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer)
with the [WordPress VIP](https://github.com/Automattic/VIP-Coding-Standards) and
[WordPress](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards)
rulesets, plus [PHPCompatibilityWP](https://github.com/PHPCompatibility/PHPCompatibilityWP)
pinned to the VIP platform PHP baseline. The ruleset lives in
[`phpcs.xml.dist`](../phpcs.xml.dist).

| Purpose | Command |
| --- | --- |
| Check | `composer phpcs` |
| Auto-fix what can be fixed | `composer phpcbf` |
| Static analysis (Psalm) | `composer psalm` |

CI runs both on every push and pull request (`lint.yml`,
`static-code-analysis.yml`).
