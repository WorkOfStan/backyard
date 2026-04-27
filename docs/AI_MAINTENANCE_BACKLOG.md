# AI Maintenance Backlog

## Critical

### cURL helpers disable SSL verification

- Affected files: `classes/BackyardHttp.php`, `classes/BackyardBriefApiClient.php`
- Why it matters: `CURLOPT_SSL_VERIFYPEER` is false and `CURLOPT_SSL_VERIFYHOST` is `0`, which accepts invalid certificates and can expose callers to man-in-the-middle attacks.
- Suggested safe approach: document the current behavior first, then add an opt-in config/parameter for strict verification with tests. Changing the default should be considered separately because it may break existing users with private certificates.
- Breaking or non-breaking: adding an opt-in strict mode is non-breaking; changing defaults may be breaking.
- Tests should be added: yes, at least unit tests around option selection; integration tests can use a controlled endpoint.

### SQL identifiers are interpolated from parameters/configuration

- Affected files: `classes/BackyardMysqli.php`, `classes/BackyardGeo.php`
- Why it matters: `nextIncrement()` interpolates table and column names, and `BackyardGeo` interpolates `geo_poi_list_table_name`. Values are partly cast/sanitized, but identifiers are not centrally validated.
- Suggested safe approach: add tests that lock current SQL generation, then introduce a small identifier validator for table/column names where safe. Treat any stricter behavior as compatibility-sensitive.
- Breaking or non-breaking: potentially breaking if existing callers pass unusual identifiers.
- Tests should be added: yes.

### Required PHP extensions are underdeclared

- Affected files: `composer.json`, `classes/BackyardMysqli.php`, `classes/BackyardHttp.php`
- Why it matters: Composer declares `ext-curl`, but optional public APIs also use `mysqli`, `sockets`, `mbstring`, and `iconv`. Consumers can install successfully and then hit fatal errors when using those classes or methods.
- Suggested safe approach: document class-specific extension requirements first. Consider `suggest` entries for optional helpers, or add hard `ext-*` requirements only in a major/compatibility-reviewed release.
- Breaking or non-breaking: documentation or `suggest` is non-breaking; hard requirements can be breaking for installs.
- Tests should be added: no direct tests required, but CI should cover extension availability if constraints change.

## High value

### Replace remote HTTP tests with deterministic tests

- Affected files: `tests/BackyardHttpTest.php`, `tests/BackyardJsonTest.php`
- Why it matters: tests call external URLs and are skipped on GitHub Actions. Results can change due to network, DNS, remote content, or HTTP headers.
- Suggested safe approach: add a local test server/fake transport or isolate response parsing into testable code. Keep remote tests as optional integration tests if still valuable.
- Breaking or non-breaking: non-breaking.
- Tests should be added: yes.

### Add missing tests for geo behavior

- Affected files: `classes/BackyardGeo.php`, new or existing tests under `tests/`
- Why it matters: distance calculations and POI result shapes are public behavior, but there are no tests.
- Suggested safe approach: start with pure tests for `getRoughDistance()` and `calculateDistanceFromLatLong()`. Then test `getClosestPOI()` with a safe fake or test double around database access.
- Breaking or non-breaking: non-breaking.
- Tests should be added: yes.

### Add missing tests for API client behavior

- Affected files: `classes/BackyardBriefApiClient.php`, new or existing tests under `tests/`
- Why it matters: this class performs network calls, logs files, decodes JSON, and throws/returns sentinels, but none of that is covered.
- Suggested safe approach: add tests with a local fake HTTP endpoint or refactor minimally to isolate the transport after behavior is pinned.
- Breaking or non-breaking: non-breaking if tests only document existing behavior.
- Tests should be added: yes.

### Add tests around global/header/termination behavior

- Affected files: `classes/BackyardHttp.php`, `classes/BackyardJson.php`, `classes/BackyardError.php`, `classes/HTMLPage.php`
- Why it matters: public methods call `header()`, echo output, flush buffers, `exit`, and `die`. These behaviors are fragile and easy for agents to break accidentally.
- Suggested safe approach: test non-terminating modes first, use output buffering, and isolate methods that can be called without `exit`/`die`.
- Breaking or non-breaking: non-breaking.
- Tests should be added: yes.

### Add local Composer script aliases

- Affected files: `composer.json`
- Why it matters: there is no `composer test` or `composer analyse`, so future agents must know tool paths and platform-specific script usage.
- Suggested safe approach: add only aliases for commands that already work after `composer install`, such as `test` for PHPUnit. Do not make PHPStan a script until its dependency strategy is decided.
- Breaking or non-breaking: non-breaking.
- Tests should be added: no, but run `composer validate` and the aliased command.

## Medium value

### Clarify README requirements for current major versions

- Affected files: `README.md`
- Why it matters: the README still opens with an old PHP 5.3 requirement before listing current 4.x constraints, which can confuse users and agents.
- Suggested safe approach: reword the requirements section to make current 4.x support (`>=7.4 <8.6`) primary and keep legacy 3.x notes separately.
- Breaking or non-breaking: non-breaking.
- Tests should be added: no.

### Improve error-handling documentation and consistency

- Affected files: `classes/*.php`, `README.md`, `docs/AI_MAINTENANCE_MAP.md`
- Why it matters: methods mix exceptions, `false`, `0`, logging, echo, `exit`, and `die`. This is maintainable only if documented and tested.
- Suggested safe approach: add docblocks/tests first. Change behavior only when explicitly requested.
- Breaking or non-breaking: docs/tests are non-breaking; behavior changes may be breaking.
- Tests should be added: yes for each clarified behavior.

### Reduce PHPStan ignores by adding precise types/docblocks

- Affected files: `phpstan.neon.dist`, `classes/*.php`
- Why it matters: PHPStan runs at max level with several ignores. Better local types can reduce future false positives and make agent changes safer.
- Suggested safe approach: handle one class at a time, prefer docblocks over native signatures where compatibility is uncertain, and run PHPStan after each slice.
- Breaking or non-breaking: usually non-breaking with docblocks; native signatures may be breaking.
- Tests should be added: only where behavior is touched.

### Translate or repair legacy comments carefully

- Affected files: `classes/*.php`, `README.md`, `CHANGELOG.md`
- Why it matters: several comments contain Czech text and mojibake-looking encoding, which makes maintenance harder.
- Suggested safe approach: translate comments to English only when meaning is clear. Never remove comments just for cleanup.
- Breaking or non-breaking: non-breaking.
- Tests should be added: no.

### Document `sql/poi.sql` expectations in user-facing docs

- Affected files: `sql/README.md`, `README.md`, `docs/AI_MAINTENANCE_MAP.md`
- Why it matters: `BackyardGeo` depends on specific POI table columns, but users need to discover that from SQL/source.
- Suggested safe approach: add a short schema note and link to `sql/poi.sql`.
- Breaking or non-breaking: non-breaking.
- Tests should be added: no.

## Low value

### Add `.editorconfig`

- Affected files: new `.editorconfig`
- Why it matters: helps editors preserve indentation and line endings consistently.
- Suggested safe approach: mirror the dominant PHP/Markdown style and avoid reformatting existing files in the same change.
- Breaking or non-breaking: non-breaking.
- Tests should be added: no.

### Add focused examples for common helper usage

- Affected files: `README.md`, possibly new `examples/`
- Why it matters: examples would reduce guesswork for facade setup, JSON fetches, MySQL connection, and geo lookup.
- Suggested safe approach: add small examples that reflect tested behavior and do not require real credentials.
- Breaking or non-breaking: non-breaking.
- Tests should be added: optional.
