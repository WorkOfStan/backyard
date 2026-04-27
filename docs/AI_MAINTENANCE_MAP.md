# AI Maintenance Map

## 1. Library purpose

`workofstan/backyard` is a PHP utility library. In practical terms, it provides a facade (`Backyard`) that wires together helper classes for arrays, JSON, HTTP/cURL, logging/error handling, simple random IDs, string transliteration, time measurement, MySQL access, geo/POI distance lookup, and legacy HTML/WML page output.

This is a library, not a standalone application. There is no `src/` directory; Composer autoloads `classes/` as `WorkOfStan\Backyard\`.

## 2. Public API

Conservative rule for future agents: every class in `classes/` is Composer-autoloaded and should be treated as public API unless the project later marks it `@internal`. Changing class names, namespaces, constructor signatures, method names, public properties, return shapes, exception behavior, or side effects can break downstream users.

### `WorkOfStan\Backyard\Backyard`

- File: `classes/Backyard.php`
- Responsibility: facade/container that creates the core helper objects.
- Main API:
  - `__construct(array $backyardConfConstruct = array())`
  - `newMysqli($host_port, $user, $pass, $db)`
  - Public properties: `BackyardArray`, `BackyardError`, `BackyardTime`, `Crypt`, `Geo`, `Http`, `Json`, `PageTimestamp`
- Input/output expectations: accepts a configuration array; constructs helper instances sharing `BackyardError`; `newMysqli()` returns `BackyardMysqli`.
- Breaking-change risk: high. Public property names and object wiring are likely used directly by consumers.

### `WorkOfStan\Backyard\BackyardArray`

- File: `classes/BackyardArray.php`
- Responsibility: array utility methods with optional PSR-3 logging.
- Main methods:
  - `inArrayWildcards($needle, array $haystack): bool`
  - `getOneColumnFromArray(array $myArray, $columnName, $columnAlwaysExpected = false): array`
  - `removeOneColumnFromArray(array $myArray, $columnName): array`
  - `dumpArrayAsOneLine(array $myArray): string`
  - `arrayVlookup($searchedValue, array $searchedArray, $columnName, $allExactMatches = false, $columnAlwaysExpected = true): array|false`
  - `arrayDiffAssocRecursive(array $array1, array $array2): array|int`
- Input/output expectations: accepts PHP arrays, mostly loose scalar column names and values; logs missing columns when requested; returns arrays, `false`, or `0` depending on method.
- Breaking-change risk: medium to high. Return sentinels (`false`, `0`) are part of current behavior.

### `WorkOfStan\Backyard\BackyardBriefApiClient`

- File: `classes/BackyardBriefApiClient.php`
- Responsibility: very small JSON REST client around cURL.
- Main methods:
  - `__construct($apiUrl, $appLogFolder = null, ?LoggerInterface $logger = null)`
  - `sendJsonLoad($json, $httpVerb = 'POST')`
  - `getJsonArray($json): array`
  - `getArrayArray(array $arr): array`
- Input/output expectations: sends JSON to a configured URL; may write request/response JSON files under `$appLogFolder`; returns raw cURL result, `false`, arrays, or throws.
- Breaking-change risk: high for HTTP behavior, supported verbs, logging filenames, and empty-array-on-decode-failure behavior.

### `WorkOfStan\Backyard\BackyardCrypt`

- File: `classes/BackyardCrypt.php`
- Responsibility: generates custom-length random-looking IDs.
- Main method: `randomId($randomIdLength = 10): string`
- Input/output expectations: accepts desired length; recursively extends generated material if needed; logs the generated ID at debug level.
- Breaking-change risk: medium. Output length is tested; exact character distribution is not.

### `WorkOfStan\Backyard\BackyardError`

- File: `classes/BackyardError.php`
- Responsibility: wraps `Seablast\Logger\Logger` and adds `dieGraciously()`.
- Main methods:
  - `__construct(array $backyardConfConstruct = array(), ?LoggerTime $backyardTime = null)`
  - `log($level, $message, array $context = array()): void`
  - `dieGraciously($errorNumber, $errorString, $feedbackButtonMarkup = ''): void`
- Input/output expectations: merges config defaults, delegates logging to the parent logger, updates global `$RUNNING_TIME`, and `dieGraciously()` writes output then terminates execution.
- Breaking-change risk: high. Logging configuration keys, global side effects, and termination behavior may be relied on.

### `WorkOfStan\Backyard\BackyardGeo`

- File: `classes/BackyardGeo.php`
- Responsibility: distance calculations and closest point-of-interest lookup using a `BackyardMysqli` connection.
- Main methods:
  - `getRoughDistance($clientLng, $clientLat, $poiLng, $poiLat): float`
  - `getClosestPOI($lat, $long, $poiCategory, BackyardMysqli $poiConnection): array|false`
  - `getListOfPOI($poiCategory, BackyardMysqli $poiConnection): array|false`
  - `calculateDistanceFromLatLong($point1, $point2, $uom = 'km'): float`
- Input/output expectations: config keys include `geo_rough_distance_limit`, `geo_maximum_meters_from_poi`, and `geo_poi_list_table_name`; POI rows are expected to include `poi_id`, `category`, `mesto`, `PSC`, `adresa`, `long`, and `lat`.
- Breaking-change risk: high. Return array keys and SQL expectations are observable API.

### `WorkOfStan\Backyard\BackyardHttp`

- File: `classes/BackyardHttp.php`
- Responsibility: HTTP helper methods for redirects, request parameter lookup, current URL construction, cURL fetches, and socket-based HTTP status checks.
- Main API:
  - Constant: `LOG_LEVEL`
  - `movePage($num, $url, $stopCodeExecution = true): void`
  - `retrieveFromPostThenGet($nameOfTheParameter): string|false`
  - `getCurPageURL($includeTheQueryPart = true): string`
  - `getData($url, $useragent = 'PHP/cURL', $timeout = 5, $customHeaders = false, array $postArray = array(), $customRequest = null): array`
  - `getHTTPstatusCode($URL_STRING): int|string`
  - `getHTTPstatusCodeByUA($URL_STRING, $userAgent = 'GetStatusCode/1.1'): int`
  - Namespaced fallback function: `WorkOfStan\Backyard\apache_request_headers()`
- Input/output expectations: uses `$_GET`, `$_POST`, `$_SERVER`, `header()`, `exit`, cURL, sockets, and DNS. `getData()` returns an associative array with keys such as `HTTP_CODE`, `message_body`, `HEADER_FIELDS`, `CONTENT_TYPE`, and optionally `REDIRECT_URL`.
- Breaking-change risk: high. Header output, process termination, returned array shape, and network behavior are externally visible.

### `WorkOfStan\Backyard\BackyardJson`

- File: `classes/BackyardJson.php`
- Responsibility: JSON minification, JSON output, comment-stripping decode, and JSON-over-HTTP retrieval.
- Main methods:
  - `minifyJSON(string $jsonInput, int $logLevel = 5): string`
  - `outputJSON($jsonString, $exitAfterOutput = false, $logLevel = 5): string`
  - `jsonCleanDecode($json2decode, $assoc = false, $depth = 512, $options = 0): array|false`
  - `getJsonAsArray($url, $useragent = 'PHP/cURL', $timeout = 5, $customHeaders = false, array $postArray = array()): array|false`
- Input/output expectations: invalid JSON is logged and may return the literal error JSON string; `outputJSON()` sends headers, echoes content, and may exit.
- Breaking-change risk: high for return sentinels, headers, and error JSON format.

### `WorkOfStan\Backyard\BackyardMysqli`

- File: `classes/BackyardMysqli.php`
- Responsibility: `mysqli` subclass with logging, UTF-8 charset setup, array conversion, and a helper for next numeric IDs.
- Main methods:
  - `__construct($host_port, $user, $pass, $db, ?BackyardError $logger = null)`
  - `query($sql, $errorLogOutput = 1)`
  - `queryArray($sql, $justOneRow = false): array|false`
  - `nextIncrement($table, $metricDimension, $primaryDimension = '', $primaryDimensionValue = 0): int`
- Input/output expectations: `$host_port` may contain `host:port` or `p:host:port`; connection charset is set to `utf8`; query failures are logged; connection failures may call `dieGraciously()` if the logger is `BackyardError`.
- Breaking-change risk: very high. It extends a PHP internal class and intentionally avoids stricter signatures for compatibility.

### `WorkOfStan\Backyard\BackyardString`

- File: `classes/BackyardString.php`
- Responsibility: string transliteration helper.
- Main method: `stripDiacritics($string): string`
- Input/output expectations: maps a fixed Czech/Slovak-ish character table to ASCII equivalents.
- Breaking-change risk: medium. Encoding behavior appears legacy and is tested.

### `WorkOfStan\Backyard\BackyardTime`

- File: `classes/BackyardTime.php`
- Responsibility: project alias/subclass of `Seablast\Logger\LoggerTime`.
- Main methods: none declared locally; tests call inherited `getRunningTime()` and `pageGeneratedIn()`, and `Backyard` calls inherited `getPageTimestamp()`.
- Input/output expectations: inherited from `seablast/logger`.
- Breaking-change risk: medium to high. Changing the parent class or constructor behavior can break facade initialization and time tests.

### `WorkOfStan\Backyard\HTMLPage`

- File: `classes/HTMLPage.php`
- Responsibility: legacy HTML/WML page generation.
- Main API:
  - Public properties: `contentType`, `header`, `footer`, `body`
  - `__construct($TITLE = 'Backyard rocks', $CONTENT_TYPE = 'text/html', $LOAD_JQ = 1, $LOAD_STYLE = 1, $LOAD_JQUERYMOBILE = 0, $beforeViewport = '', $manifestCache = '')`
  - `startPage(): void`
  - `outputCurrentBody(): void`
  - `endPage(): void`
  - `fixXml($text): string`
  - `addToBody($add): void`
  - `addToHeader($add): void`
- Input/output expectations: builds HTML/WML strings, sends `Content-Type` headers, echoes body/footer, flushes buffers, and may load old jQuery/jQuery Mobile URLs.
- Breaking-change risk: high for generated markup and public properties, even though comments label it legacy.

### Data/schema files

- `sql/poi.sql` and `sql/README.md` document the POI table structure expected by `BackyardGeo`.
- `.htaccess` is deployment hardening for hiding repository, Composer, SQL, Markdown, Neon, shell, and YAML files when the package is exposed under Apache.

## 3. Internal components

There are no formal `@internal` classes. Internal-by-convention pieces include:

- `BackyardBriefApiClient::getCommunicationId()` and `logCommunication()`.
- `HTMLPage::treatForWml()`.
- Shared logger fields inside helper classes.
- The exact array shapes returned by `BackyardHttp::getData()` and `BackyardGeo::getClosestPOI()`, although these are effectively public because callers receive them.
- SQL query strings inside `BackyardGeo` and `BackyardMysqli`.

Future agents should avoid treating private methods as extension points, but should preserve their behavior when changing public methods.

## 4. Data flow / control flow

Typical facade flow:

1. User code includes Composer autoload and creates `new WorkOfStan\Backyard\Backyard($config)`.
2. `Backyard` creates `BackyardTime`, records `PageTimestamp`, creates `BackyardError`, then creates array, crypt, geo, HTTP, and JSON helpers sharing that logger.
3. User code calls helpers through public properties, for example `$backyard->Json->minifyJSON(...)`.
4. Helper methods return values directly, log through PSR-3, or perform side effects such as headers, echo, `exit`, `die`, filesystem logging, cURL calls, socket calls, or database queries.

Direct-use flow:

1. Users may instantiate any helper class directly through PSR-4 autoloading.
2. Most helpers accept an optional PSR-3 logger; missing loggers usually become `Psr\Log\NullLogger`.
3. `BackyardJson` needs both a logger and a `BackyardHttp`.
4. `BackyardGeo` needs a logger, config array, and `BackyardMysqli` connection for POI lookup.

HTTP/JSON flow:

1. `BackyardJson::getJsonAsArray()` calls `BackyardHttp::getData()`.
2. `getData()` uses cURL and returns a response array.
3. `BackyardJson` reads `message_body`, strips JSON comments if requested, decodes, logs errors, and returns an array or `false`.

Geo/database flow:

1. User creates `BackyardMysqli` directly or via `Backyard::newMysqli()`.
2. `BackyardGeo::getClosestPOI()` validates category input, calls `getListOfPOI()`, and receives rows from `BackyardMysqli::queryArray()`.
3. It computes rough distances, filters by config, computes exact distances with the Haversine formula, sorts, and returns the closest POI array or `false`.

## 5. Extension points

- Config array passed to `Backyard`, `BackyardError`, and `BackyardGeo`.
- PSR-3 logger injection into helper constructors.
- `BackyardJson` accepts a `BackyardHttp` instance, allowing replacement with a compatible subclass/object only if it matches the type.
- `BackyardMysqli` can be created with a custom `host:port` or persistent `p:host:port` string.
- `BackyardGeo` can use a different POI table through `geo_poi_list_table_name`.
- `BackyardBriefApiClient` can log request/response bodies to a configured folder.
- `HTMLPage` constructor flags configure content type, jQuery/jQuery Mobile loading, CSS loading, viewport preamble, and manifest.

There are no interfaces, events, dependency-injection container bindings, service providers, or plugin hooks in this repository.

## 6. Error handling

There are no custom exception classes in this repository.

Built-in exceptions thrown include:

- `InvalidArgumentException` in `BackyardHttp::movePage()` and `BackyardGeo::getClosestPOI()`.
- `RuntimeException` in `BackyardHttp::retrieveFromPostThenGet()`, `BackyardHttp::getCurPageURL()`, `BackyardHttp::getData()`, and `BackyardBriefApiClient::getArrayArray()`.
- `UnexpectedValueException` in `BackyardHttp::getHTTPstatusCode()`, `BackyardGeo`, and `BackyardBriefApiClient::getJsonArray()`.
- Generic `Exception` is declared in some docblocks but not consistently thrown.

Error return values and side effects:

- Many methods return `false` on failure (`queryArray()`, `getJsonAsArray()`, `getClosestPOI()`, `getListOfPOI()`, `retrieveFromPostThenGet()`, `sendJsonLoad()`).
- `BackyardArray::arrayDiffAssocRecursive()` returns integer `0` when arrays are equal.
- `BackyardError::dieGraciously()` logs, echoes optional markup, and terminates execution.
- `BackyardHttp::movePage()` sends headers and exits by default.
- `BackyardJson::outputJSON()` sends headers, echoes JSON, and may exit.
- cURL and socket failures are usually logged and converted to return values rather than exceptions.

Underdocumented areas:

- Exact logger configuration inherited from `seablast/logger`.
- Whether callers should expect exceptions or false values for each failure path.
- Security implications of disabled cURL SSL verification.
- Required PHP extensions beyond `ext-curl`.

## 7. Compatibility constraints

- Composer PHP constraint: `>=7.4 <8.6`.
- CI matrix in `.github/workflows/polish-the-code.yml`: PHP 7.4 through 8.5.
- Runtime dependencies: `ext-curl`, `seablast/logger`.
- Lockfile runtime packages observed: `psr/log`, `seablast/logger`, `tracy/tracy`.
- Dev dependencies: PHPUnit and Webmozart Assert.
- Additional extensions used by optional classes/methods but not declared in `composer.json`: `mysqli`, `sockets`, `mbstring`, and likely `iconv`.
- No `declare(strict_types=1)` is used.
- The code intentionally keeps several signatures loose for PHP and `mysqli` compatibility.
- Changelog states the project follows Semantic Versioning.
- Current evidence of compatibility risk: `composer.json` requires `seablast/logger` `^2.0.5`, but `composer.lock` currently records `v2.0.4`.

## 8. Tests

How to run tests after installing dependencies:

```sh
vendor/bin/phpunit
```

On Windows PowerShell:

```powershell
.\vendor\bin\phpunit
```

To run the GitHub-style PHPUnit config that excludes HTTP-group tests:

```sh
vendor/bin/phpunit -c conf/phpunit-github.xml
```

Current coverage strengths:

- Array utilities are covered reasonably well.
- `BackyardCrypt::randomId()` length behavior is covered.
- `BackyardString::stripDiacritics()` is covered for known strings.
- Basic `BackyardTime` inherited timing methods are covered.
- JSON minification and comment-cleaning decode are covered.
- `Backyard` facade wiring is lightly covered through `$backyard->Json`.

Weak or missing coverage:

- No tests for `BackyardMysqli`.
- No tests for `BackyardGeo`.
- No tests for `BackyardError::log()` or `dieGraciously()`.
- No tests for `BackyardBriefApiClient`.
- No tests for `HTMLPage`.
- Header/exit/die behavior is mostly skipped or untested.
- HTTP tests depend on remote URLs and are skipped in GitHub Actions.
- Socket-based HTTP status methods are untested.

Recommended tests to add first:

- Pure unit tests for `BackyardGeo::calculateDistanceFromLatLong()` and `getRoughDistance()`.
- Unit tests for `BackyardJson::getJsonAsArray()` using a fake `BackyardHttp`.
- Tests for `BackyardHttp::retrieveFromPostThenGet()` and `getCurPageURL()` with isolated superglobal setup/teardown.
- Tests for `BackyardBriefApiClient` against a local fake HTTP endpoint or an injectable transport.
- Tests around `BackyardMysqli` SQL construction should use a safe test database or a refactor that isolates query building first.

## 9. Tooling

Available or documented commands:

```sh
composer install
vendor/bin/phpunit
vendor/bin/phpunit -c conf/phpunit-github.xml
```

On Windows PowerShell, do not run `blast.sh` directly. Use Git Bash explicitly:

```powershell
& "C:\Program Files\Git\bin\bash.exe" -lc "./blast.sh phpstan"
& "C:\Program Files\Git\bin\bash.exe" -lc "./blast.sh phpstan-remove"
```

Important tooling notes:

- There are no Composer script aliases such as `composer test`.
- `phpstan.neon.dist` exists, but PHPStan is not a committed/dev dependency in `composer.json`; `blast.sh phpstan` installs `phpstan/phpstan-webmozart-assert` and `phpstan/phpstan-phpunit` temporarily before running analysis.
- PHPCS config exists at `.github/linters/phpcs.xml`, but PHPCS is not a local Composer dev dependency. CI uses `WorkOfStan/phpcs-fix@v1`.
- Super-Linter and Prettier are run in GitHub Actions, not through local Composer scripts.

## 10. Risk areas for AI agents

- Public API: all classes in `classes/`, public properties on `Backyard` and `HTMLPage`, and return array shapes.
- Backward compatibility: loose method signatures and `#[\ReturnTypeWillChange]` in `BackyardMysqli::query()` are intentional compatibility work.
- Global state: `$_GET`, `$_POST`, `$_SERVER`, global `$RUNNING_TIME`, `header()`, `exit`, `die`, output buffers.
- Network access: cURL and sockets are used directly; tests depend on remote services.
- Security-sensitive behavior: disabled SSL verification in cURL helpers; SQL identifier interpolation in `BackyardMysqli::nextIncrement()` and configurable table names in `BackyardGeo`.
- Filesystem access: API communication logs can write JSON files to `$appLogFolder`.
- Database behavior: `BackyardMysqli` extends `mysqli`, connects in the constructor, changes charset to `utf8`, and may terminate on connect error.
- Encoding: comments and string transliteration tables contain legacy/mojibake-looking Czech text; preserve or deliberately translate comments rather than deleting them.
- Date/time/randomness: `BackyardTime` behavior is inherited; `BackyardCrypt::randomId()` is not cryptographically documented despite its name.
- Weak tests: geo, MySQL, brief API client, error handling, HTML output, headers, and socket status checks lack coverage.
- Tooling drift: `composer.json` and `composer.lock` disagree on `seablast/logger`.

## 11. Safe change policy

Safe changes:

- Documentation improvements.
- Adding focused tests for existing behavior.
- Local refactors with no public API or behavior changes.
- Bugfixes with regression tests.
- Translating comments to English when meaning is preserved.
- Adding Composer scripts only when they invoke already-existing tools and do not change dependencies or runtime behavior.

Changes requiring extra caution:

- Changing method signatures, parameter defaults, return types, public property names, class names, namespaces, or constants.
- Changing exceptions, `false`/`0` sentinels, echoed output, headers, `exit`, or `die` behavior.
- Changing Composer PHP/dependency constraints.
- Changing default logger, config, cURL, SSL, database charset, SQL, or redirect behavior.
- Replacing helper classes with a new architecture.
- Removing comments, especially historical compatibility notes, unless a `TODO` was actually solved or the comment is translated to English.
