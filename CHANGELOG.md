# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### `Added` for new features

### `Changed` for changes in existing functionality

### `Deprecated` for soon-to-be removed features

### `Removed` for now removed features

### `Fixed` for any bugfixes

### `Security` in case of vulnerabilities

## [4.1.1] - 2025-09-23

chore: dead code from backyard 1 and backyard 2 removed

### Removed

- dead code from backyard 1 and backyard 2 removed

## [4.1.0] - 2025-09-20

added: PHP/8.4 support

### Changed

- all GitHub Actions combined into polish-the-code.yml
- all dev and management scripts combined into blast.sh
- `Test` folder standardized to `tests`

### Removed

- PHPUnit GitHub testing ignores tests in group: `http` (such as testGetDataContent()), because GitHub call ended with 403 HTTP Code response, instead of 200

### Fixed

- PHP 8.4 deprecated the “implicitly nullable” parameters

## [4.0.0] - 2024-11-25

PHP `>=7.4, <8.4`

## [3.4.3] - 2024-11-25

Prettier-fix, PHP `>=5.3, <7.4`

### Changed

- prettier-fix
- Note: to keep BackyardError::log() backward compatibility, let's limit "seablast/logger": "^1.0"
- PHPStan/2.x is showing different errors, so let's limit the support in 3.x only for PHP `>=5.3, <7.4`

## [3.4.2] - 2024-08-03

BackyardArray may use `\Psr\Log\NullLogger`

### Changed

- BackyardArray accepts null as logger => `\Psr\Log\NullLogger`
- BackyardHttp::movePage - extended array with additional HTTP status codes

## [3.4.1] - 2024-08-03

phpstan-baseline.neon removed

### Fixed

- BackyardBriefApiClient, BackyardGeo, BackyardJson typehinted and method parameters checked for validity
- BackyardMysqli::queryArray conditions changed to test for `instanceof \mysqli_result`

## [3.4.0] - 2024-07-29

BackyardError wraps \Seablast\Logger\Logger implementation

### Added

- "webmozart/assert": "^1.9.1" added to dev because of BackyardHttpTest
- `.github\linters\.htmlhintrc` added to set `"attr-value-double-quotes": false` to ignore how [example/test_coloursave.html](example/test_coloursave.html) is done.
- `.github\linters\.sqlfluff` added to specify a dialect.
- \Psr\Log\NullLogger() used if no proper logger used

### Changed

- BackyardHttpTest use stricter handling of preg_replace, i.e. throws Exception on error
- BackyardError wraps \Seablast\Logger\Logger implementation.
- class BackyardTime extends \Seablast\Logger\LoggerTime
- bump .github/workflows to [WorkOfStan/seablast-actions](https://github.com/WorkOfStan/seablast-actions)
- src/js/all.js and src/js/coloursave.js tempSelector === 'owner_language' to tempSelector === 'owner-language'
- src/js/all.js #send_me_mm_button to #send-me-mm-button

### Removed

- `global $ERROR_HACK` is ignored by BackyardError. And so is `$_GET['ERROR_HACK']`.
- VERSION file. As only main branch is used.

### Fixed

- .htmlhintrc The ID and class attribute values must be in lowercase and split by a dash. (id-class-value)

## [3.3.2] - 2022-02-12

### Added

- PSR-4 compliant class HTMLPage instead of legacy LIB2 class_HTMLPage.php

## [3.3.1] - 2022-02-10

- PHPStan level=9

### Added

- dependabot.yml
- array<mixed> iterable type hint to accommodate PHPStan level=6
- phpstan.sh and phpstan-remove.sh for local testing
- phpstan-baseline.neon to hide type hint imperfections etc. in PHPStan level=9 (todo fix these later) to hold new code to a higher standard
- added cache for online PHPStan testing and using pre-built tool PHPStan

### Changed

- Limit the GitHub Action job running time
- Allow psr/log ^3.0 (relevant for PHP/8 version)
- Bump michaelw90/PHP-Lint to overtrue/phplint@4.1.0

### Fixed

- BackyardGeo::getClosestPOI Offset 'distance' does not exist if the object was too far => provide default long distance
- use super-linter main branch instead of master
- PHPCS newlines, textlint terminology
- spaces in /github/workspace/sql/poi.sql found by sqlfluff (todo sqlfluff Explore sql/poi.sql unparsable fail)

### Security

- CHANGELOG.md or .sh unreachable through web server

## [3.3.0] - 2021-03-09

Change of namespace + automatic testing of various PHP versions

- Change of namespace to the new GitHub account maintaining the repository
- GitHub online error-free static analysis workflows with composer dependencies, PHPUnit tests and PHPStan analysis run for various PHP versions ['5.3', '5.6', '7.3', '7.4']
- show E_NOTICE during PHPUnit tests
- production release branch renamed from `master` to `main`
- error-free PHPStan analysis till level 6

## [3.2.10] - 2020-10-31

PHPUnit on GitHub, BackyardHttp::getData throws Exception

1. Change

- BackyardHttp::getData @throws \Exception if $customHeaders are neither false nor string (instead of just log error)

2. Governance

- run PHPunit test on GitHub (it needs composer built environment)
- test also composer no-dev dependencies (on GitHub)
- BackyardHttp: more thorough treatment of error conditions
- Backyard/BackyardHttp.php: const logLevel to change potential debug level in one place
- PHP Composer + PHPUnit badge to README.md

3. Code style

- all properties in camelCase
- PHPCS: Ignore Warning 'Visibility must be declared on all constants if your project supports PHP 7.1 or later' in BackyardHttp.php as it is PHP/5 compatible

4. Various typehinting fixed

- typehint mixed replaced by more specific type combination, e.g. int|string or array|bool
- value type specified in iterable type array
- Method GodsDev\Backyard\BackyardJson::outputJSON() should return string but returns string|false.
- Method GodsDev\Backyard\BackyardTime::getPageTimestamp() should return float but returns float|null.
- Method GodsDev\Backyard\BackyardJson::minifyJSON() should return string but returns string|false.
- Parameter #1 $version1 of function version_compare expects string, string|false given.

## [3.2.9] - 2020-10-07

Lint all code

1. Lint all code

- PHP Lint and github/super-lint
- PHP CS fixer: white-spaces, empty lines and new lines, limit line length to 120 characters
  - BackyardError: phpcs:disable Generic.Files.LineLength
  - exclude old src/ code
- PHPSTAN
  - fixed PHPDoc and minor style fixes
  - exclude old src/ code
  - level max
- not using Ansible: getting rid of [WARN ] No Ansible base directory found at:[/github/workspace/ansible]
- Markdown lint - use ATX style
- VALIDATE_HTML: stop validating </example/test_coloursave.html>

- array type added in function parameters as it is working since PHP 5.1.0 <https://www.php.net/manual/en/functions.arguments.php>

- BackyardGeo::calculateDistanceFromLatLong - throw new \Exception('Unknown unit of measurement');
- BackyardMysqli::query doesn't @throws DBQueryException as it writes to log instead
- BackyardMysqli::query - return mixed \mysqli_result|false
- BackyardMysqli::nextIncrement - default arguments are of correct types
- BackyardHttp::getHTTPstatusCodeByUA - always returns int
- BackyardCrypt::randomId - fixed uniqid arguments
- BackyardTime::pageGeneratedIn - fixed Parameter #2 $replace of function str_replace expects array|string, float given.
- BackyardError::dieGraciously - fix default argument #3 value
- BackyardJson::$backyardError and BackyardGeo::$BackyardError renamed to $logger

2. php-composer-validate.yml - Automatically on GitHub check composer validate and composer update

3. remove X-Forwarded-For header from Test as it contains source IP and hence would be changing unnecessarily

## [3.2.8] - 2020-05-02

classes Test in separate path so that `/godsdev/mycms/classes/Test/` are not part of `autoload_static.php`

## [3.2.7] - 2020-04-20

- BackyardStringTest: fix compliance with PSR-4
- BackyardHttpTest: update of content of <http://dadastrip.cz/test/>

## [3.2.6] - 2019-12-24

### Fixed

- fix CommunicationId (logging ID) generated once both for request and response

### Added

- GodsDev\Backyard\BackyardString::stripDiacritics

## [3.2.5] - 2018-08-18

### Fixed

- $isHTTPS in BackyardHttp::getCurPageURL()

## [3.2.4] - 2018-08-18

### Security

- secured old /but potentially useful for refactoring older apps/ code

## [3.2.3] - 2018-05-25

- Coding standards
- `use Psr\Log\LoggerInterface;` instead of `use GodsDev\Backyard\BackyardError;` where possible
- warning error_log message for obsolete files (that may be still used somewhere)

## [3.2.2] - 2018-05-14

### Added

- `sendJsonLoad` added second OPTIONAL (default remains `POST`) parameter with HTTP verb `GET`,`PUT`,`DELETE`

## [3.2.1] - 2017-10-06

### Fixed

- BackyardBriefApiClient for $logger == null

## [3.2.0] - 2017-06-06

### Added

- Class Backyard/BackyardBriefApiClient.php added: very simple JSON RESTful API client that just sends JSON and returns what is to be returned with few optional decorators and error logging.

## [3.1.1] - 2017-05-04

### Fixed

- fix dieGraciously
- array type declaration in BackyardArray

## [3.1.0] - 2017-04-21

### Changed

- Backyard/BackyardError.php implements PSR-3
- License changed to MIT (from Apache)
- Usage badges to README.md

## [3.0.6] - 2017-03-15

### Added

- getData handles CURLOPT_CUSTOMREQUEST
- deployment described

## [3.0.5.1] - 2017-02-25

### Fixed

- no HTTTP response headers: $data['HEADER_FIELDS'] === array()

## [3.0.5] - 2017-02-25

### Added

- HTTP HEADER_FIELDS added as output of getData

## [3.0.4] - 2016-09-18

- IDE hint on Classes instantiated in variables within Class Backyard by @var PHPdoc (instead of @return for variable or getter /which would require using () in the access instance member chain/)

## [3.0.3] - 2016-09-15

getJsonAsArray extended, fix getOneColumnFromArray, PSR-3 logger methods

- getJsonAsArray uses getData and hence can use POST fields and custom HTTP headers (therefore BackyardHttp MUST be DI into BackyardJson)
- fix: getOneColumnFromArray handles one-dimensional values
- Eight PSR-3 logger methods added (see <http://www.php-fig.org/psr/psr-3/> )
- PHP constants null, true, false in lower case according to <http://www.php-fig.org/psr/psr-2/> chapter 2.5
- outputJSON returns HTTP 500 if failed
- $backyardConfConstruct not reassigned for second time

## [3.0.2] - 2016-08-16

- BackyardGeo better logging, BackyardCrypt salt introduced, BackyardHttpTest.php more robust

## [3.0.1] - 2016-07-06

Subclass lazy loading removed.

- So that Library in Backyard is compatible with PHP > 5.3.0 and object $Backyard->Http->method(); works everywhere.

## [3.0.0] - 2016-07-03

PSR-4 compliant Class

Backyard rewritten into PSR-4 compliant Class with PSR-3 compliant logger. Subclasses are lazy loaded. PHPunit testing is working for subclasses with no major external needs (BackyardArray, Crypt, Http, `Json`, BackardTime).
Invoke by:
use GodsDev\Backyard\Backyard;
$this->Backyard = new Backyard($backyardConf); //$backyardConf is array with delta to default values

## [2.0.2] - 2016-07-03

- Minor comments (before going to 3.0.0)

## [2.0.1] - 2016-02-29

- name changed to godsdev/backyard so that all GodsDev libraries are in the same vendor directory
- backyard_getData better no-response handling

## [2.0.0] - 2016-01-14

LIBrary in backyard 2.0.0

- Most backyard functions are named as backyard_camelCase
- Usage is require_once DIR . '/lib/backyard/src/backyard_system.php';
- All `src/backyard_*.php` files are almost PSR-1 compliant
- May be used in composer

## [1.0] - 2014-09-21

- fix for post functionality in backyard_getData

[Unreleased]: https://github.com/WorkOfStan/backyard/compare/v4.1.1...HEAD
[4.1.1]: https://github.com/WorkOfStan/backyard/compare/v4.1.0...v4.1.1
[4.1.0]: https://github.com/WorkOfStan/backyard/compare/v4.0.0...v4.1.0
[4.0.0]: https://github.com/WorkOfStan/backyard/compare/v3.4.3...v4.0.0
[3.4.3]: https://github.com/WorkOfStan/backyard/compare/v3.4.2...v3.4.3
[3.4.2]: https://github.com/WorkOfStan/backyard/compare/v3.4.1...v3.4.2
[3.4.1]: https://github.com/WorkOfStan/backyard/compare/v3.4.0...v3.4.1
[3.4.0]: https://github.com/WorkOfStan/backyard/compare/v3.3.2...v3.4.0
[3.3.2]: https://github.com/WorkOfStan/backyard/compare/v3.3.1...v3.3.2
[3.3.1]: https://github.com/WorkOfStan/backyard/compare/v3.3.0...v3.3.1
[3.3.0]: https://github.com/WorkOfStan/backyard/compare/v3.2.10...v3.3.0
[3.2.10]: https://github.com/WorkOfStan/backyard/compare/v3.2.9...v3.2.10
[3.2.9]: https://github.com/WorkOfStan/backyard/compare/v3.2.8...v3.2.9
[3.2.8]: https://github.com/WorkOfStan/backyard/compare/v3.2.7...v3.2.8
[3.2.7]: https://github.com/WorkOfStan/backyard/compare/v3.2.6...v3.2.7
[3.2.6]: https://github.com/WorkOfStan/backyard/compare/v3.2.5...v3.2.6
[3.2.5]: https://github.com/WorkOfStan/backyard/compare/v3.2.4...v3.2.5
[3.2.4]: https://github.com/WorkOfStan/backyard/compare/v3.2.3...v3.2.4
[3.2.3]: https://github.com/WorkOfStan/backyard/compare/v3.2.2...v3.2.3
[3.2.2]: https://github.com/WorkOfStan/backyard/compare/v3.2.1...v3.2.2
[3.2.1]: https://github.com/WorkOfStan/backyard/compare/v3.2.0...v3.2.1
[3.2.0]: https://github.com/WorkOfStan/backyard/compare/v3.1.1...v3.2.0
[3.1.1]: https://github.com/WorkOfStan/backyard/compare/v3.1.0...v3.1.1
[3.1.0]: https://github.com/WorkOfStan/backyard/compare/v3.0.6...v3.1.0
[3.0.6]: https://github.com/WorkOfStan/backyard/compare/v3.0.5.1...v3.0.6
[3.0.5.1]: https://github.com/WorkOfStan/backyard/compare/v3.0.5...v3.0.5.1
[3.0.5]: https://github.com/WorkOfStan/backyard/compare/v3.0.4...v3.0.5
[3.0.4]: https://github.com/WorkOfStan/backyard/compare/v3.0.3...v3.0.4
[3.0.3]: https://github.com/WorkOfStan/backyard/compare/v3.0.2...v3.0.3
[3.0.2]: https://github.com/WorkOfStan/backyard/compare/v3.0.1...v3.0.2
[3.0.1]: https://github.com/WorkOfStan/backyard/compare/v3.0.0...v3.0.1
[3.0.0]: https://github.com/WorkOfStan/backyard/compare/v2.0.2...v3.0.0
[2.0.2]: https://github.com/WorkOfStan/backyard/compare/v2.0.1...v2.0.2
[2.0.1]: https://github.com/WorkOfStan/backyard/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/WorkOfStan/backyard/compare/1.0...v2.0.0
[1.0]: https://github.com/WorkOfStan/backyard/releases/tag/1.0
