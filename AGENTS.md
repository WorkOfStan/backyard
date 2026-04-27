# AGENTS.md

## Project overview

`workofstan/backyard` is a Composer PHP library of utility classes under `WorkOfStan\Backyard`: array helpers, JSON helpers, HTTP/cURL helpers, logging/error handling around `seablast/logger`, simple random IDs, string transliteration, time helpers, a `mysqli` wrapper, geo/POI lookup helpers, and legacy HTML/WML page output.

## Setup

Use a working Composer installation:

```sh
composer install
```

This repository has no required build step beyond installing Composer dependencies.

## Test commands

Run the PHPUnit suite:

```sh
vendor/bin/phpunit
```

On Windows PowerShell:

```powershell
.\vendor\bin\phpunit
```

Run the GitHub-style PHPUnit config that excludes `@group http` tests:

```sh
vendor/bin/phpunit -c conf/phpunit-github.xml
```

Run PHPStan through the project helper. On Windows, do not run `.sh` helper scripts directly from PowerShell; use Git Bash explicitly:

```powershell
& "C:\Program Files\Git\bin\bash.exe" -lc "./blast.sh phpstan"
```

Cleanup the temporary PHPStan packages:

```powershell
& "C:\Program Files\Git\bin\bash.exe" -lc "./blast.sh phpstan-remove"
```

Missing local aliases/tools:

- No `composer test` script is defined.
- PHPStan is not a committed dev dependency; `blast.sh phpstan` installs it temporarily.
- PHPCS config exists in `.github/linters/phpcs.xml`, but PHPCS is not a Composer dev dependency. CI runs PHPCS through `WorkOfStan/phpcs-fix`.

## Coding rules

- PHP compatibility is `>=7.4 <8.6`; do not silently raise the minimum PHP version.
- Namespace production classes as `WorkOfStan\Backyard` and place them in `classes/`.
- Namespace tests as `WorkOfStan\Backyard\Tests` and place them in `tests/`.
- Follow the existing PSR-12-ish formatting and the local preference for `array()` syntax.
- Many public methods are intentionally loosely typed for compatibility. Add native parameter or return types only when the compatibility impact is clear.
- Preserve existing comments. Remove a TODO only when you actually solve it; translating comments to English is acceptable when meaning is preserved.
- Error handling is mixed: some methods throw built-in exceptions, some return `false` or `0`, and some log, echo, send headers, `exit`, or `die`. Preserve current behavior unless the task explicitly asks to change it.
- Avoid new runtime dependencies unless clearly justified. Prefer standard PHP and existing Composer dependencies.

## Change rules for AI agents

- Prefer small, reviewable changes.
- Do not change public API unless the task explicitly asks for it.
- When fixing a bug, add or update a regression test.
- Preserve backward compatibility unless instructed otherwise.
- Do not silently raise the minimum PHP version.
- Do not replace existing architecture with a new architecture.
- Before editing, inspect nearby code and follow local conventions.
- After editing, run the most relevant available tests/checks.
- If tests cannot be run, explain exactly why.
- Avoid speculative cleanup unrelated to the requested task.

## Important files

- `composer.json`: package metadata, PHP constraint, dependencies, and PSR-4 autoloading.
- `composer.lock`: locked dependency versions. Keep it consistent with `composer.json`.
- `classes/`: public library classes.
- `tests/`: PHPUnit tests.
- `phpunit.xml`: default PHPUnit config.
- `conf/phpunit-github.xml`: PHPUnit config used to exclude HTTP-group tests in CI-like runs.
- `phpstan.neon.dist`: base PHPStan configuration.
- `conf/phpstan.webmozart-assert.neon`: PHPStan config used by `blast.sh phpstan`.
- `blast.sh`: development helper; use Git Bash explicitly from Windows PowerShell.
- `.github/workflows/polish-the-code.yml`: CI workflow for Composer/PHPUnit/PHPStan plus formatting/linting jobs.
- `.github/linters/phpcs.xml`: PSR-12 PHPCS config used by CI tooling.
- `sql/poi.sql`: database schema expected by geo/POI helpers.
- `docs/AI_MAINTENANCE_MAP.md`: architecture and public API map for future agents.
- `docs/AI_MAINTENANCE_BACKLOG.md`: prioritized maintenance backlog.
- `CHANGELOG.md`: release notes; update it for notable documentation, behavior, or tooling changes.

## Release / versioning notes

`CHANGELOG.md` says the project follows Keep a Changelog and Semantic Versioning. Releases appear to be GitHub/Packagist tags matching changelog versions. Public API compatibility matters for all classes in `classes/`, because they are directly autoloaded by Composer.
