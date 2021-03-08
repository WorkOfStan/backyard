# Library In Backyard

**Collection of useful functions**

[![Total Downloads](https://img.shields.io/packagist/dt/workofstan/backyard.svg)](https://packagist.org/packages/workofstan/backyard)
[![Latest Stable Version](https://img.shields.io/packagist/v/workofstan/backyard.svg)](https://packagist.org/packages/workofstan/backyard)
[![Lint Code Base](https://github.com/WorkOfStan/backyard/workflows/Lint%20Code%20Base/badge.svg)](https://github.com/WorkOfStan/backyard/actions/workflows/linter.yml)
[![PHP Composer + PHPUnit + PHPStan](https://github.com/WorkOfStan/backyard/workflows/PHP%20Composer%20+%20PHPUnit/badge.svg)](https://github.com/WorkOfStan/backyard/actions/workflows/php-composer-phpunit.yml)

## Requirements
* [PHP 5.3.0 or higher](http://www.php.net/) (i.e. not used [] instead of array() as this short syntax can be used only since PHP 5.4)

## Installation

You can use **Composer** or simply **Download the Release**

### Composer

The preferred method is via [composer](https://getcomposer.org). Follow the
[installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have
composer installed.

Once composer is installed, execute the following command in your project root to install this library:

```sh
composer require workofstan/backyard:^3.3.0
```

Finally, be sure to include the autoloader:

```php
require_once '/path/to/your-project/vendor/autoload.php';
```

### Download the Release

If you abhor using composer, you can download the package in its entirety. The [Releases](https://github.com/WorkOfStan/backyard/releases) page lists all stable versions. Download any file
with the name `backyard-[RELEASE_NAME].zip` for a package including this library and its dependencies.

Uncompress the zip file you download, and include the autoloader in your project:

```php
require_once '/path/to/backyard/vendor/autoload.php';
```

## Deployment

After the autoloader is included you may create the `backyard` object with default configuration:
```php
$backyard = new \WorkOfStan\Backyard\Backyard();
```

Or you may configure it with following options:
```php
$backyard = new \WorkOfStan\Backyard\Backyard(
    array(//default values
        //logger relevant that SHOULD be configured
        'logging_level'             => 5,       //log up to the level set here, default=5 = debug//logovat az do urovne zde uvedene: 0=unknown/default_call 1=fatal 2=error 3=warning 4=info 5=debug/default_setting 6=speed  //aby se zalogovala alespoň missing db musí být logování nejníže defaultně na 1 //1 as default for writing the missing db at least to the standard ErrorLog
        'mail_for_admin_enabled'    => false,   //fatal error may just be written in log //$backyardMailForAdminEnabled = "rejthar@gods.cz";//on production, it is however recommended to set an e-mail, where to announce fatal errors
        'error_log_message_type'    => 0,       //parameter message_type http://cz2.php.net/manual/en/function.error-log.php for my_error_log; default is 0, i.e. to send message to PHP's system logger; recommended is however 3, i.e. append to the file destination set either in field $this->BackyardConf['logging_file or in table system
        'logging_file'              => '',      //soubor, do kterého má my_error_log() zapisovat

        //logger relevant other
        'logging_level_name'        => array(0 => 'unknown', 1 => 'fatal', 'error', 'warning', 'info', 'debug', 'speed'),
        'logging_level_page_speed'  => 5,       //úroveň logování, do které má být zapisována rychlost vygenerování stránky
        'die_graciously_verbose'    => true,    //show details by die_graciously() on screen (it is always in the error_log); on production it is recomended to be set to to false due security
        'log_monthly_rotation'      => true,    //true, pokud má být přípona .log.Y-m.log (výhodou je měsíční rotace); false, pokud má být jen .log (výhodou je sekvenční zápis chyb přes my_error_log a jiných PHP chyb)
        'log_standard_output'       => false,   //true, pokud má zároveň vypisovat na obrazovku; false, pokud má vypisovat jen do logu
        'log_profiling_step'        => false,   //110812, my_error_log neprofiluje rychlost //$PROFILING_STEP = 0.008;//110812, my_error_log profiluje čas mezi dvěma měřenými body vyšší než udaná hodnota sec
        'error_hacked'              => true,    //ERROR_HACK parameter is reflected
        'error_hack_from_get'       => 0,       //in this field, the value of $_GET['ERROR_HACK'] shall be set below

        //geo relevant
        'geo_rough_distance_limit' => 1, //float //to quickly get rid off too distant POIs; 1 ~ 100km
        'geo_maximum_meters_from_poi' => 2500, //float //distance considered to be overlapping with the device position // 2500 m is considered exact location due to mobile phone GPS caching
        'geo_poi_list_table_name' => 'poi_list', //string //name of table with POI coordinates
    )
);
```


## Notes

NB: BackyardMysqli creates no Backyard->Mysqli object (as e.g. Backyard->Json does) because it is not used by LIB itself and more importantly user of LIB may create any number of those.
Example of usage:

```php
$backyard = new WorkOfStan\Backyard\Backyard(array('logging_level' => 3));
$logger = $backyard->BackyardError;
$dbLink = new WorkOfStan\Backyard\BackyardMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE, $logger);
```

## class BackyardBriefApiClient

Very simple JSON RESTful API client.
It just sends JSON and returns what is to be returned with few optional decorators and error logging.
* `sendJsonLoad` - sends JSON and returns whatever is returned with second OPTIONAL parameter with HTTP verb `GET`,`PUT`,`DELETE`
* `getJsonArray` - sends JSON and returns array decoded from response JSON
* `getArrayArray` - encode array to a JSON and returns array decoded from response JSON


## About previous versions
*Their code is disabled and therefore secured.*

### backyard 1 usage

This array MUST be created by the application before invoking backyard 1
```php
$backyardDatabase = array(
    'dbhost' => 'localhost',
    'dbuser' => 'user',
    'dbpass' => '',
    'dbname' => 'default',
);
```

Invoking backyard 1
```php
require_once __DIR__ . '/lib/backyard/deploy/functions.php';
```


### backyard 2 usage

The array $backyardDatabase (see above) SHOULD be created ONLY IF there is a table \`system\` (or different name stated in $backyardDatabase['system_table_name']) with fields containing backyard system info.

Usage:
```php
require_once __DIR__ . '/lib/backyard/src/backyard_system.php';
```
Requires the basic LIB library. All other LIB components to be included by
```php
require_once (__BACKYARDROOT__."/backyard_XXX.php");
```

**Recommendation**

To be in control of the logging, set following before requiring LIB
```php
$backyardConf['logging_level'] = 3;         //so that only fatal, error, warning are logged
$backyardConf['error_log_message_type'] = 3;//so that logging does not go to PHP system logger but to the monthly rotated file specified on the next line
$backyardConf['logging_file'] = '/var/www/www.alfa.gods.cz/logs/error_php.log';
$backyardConf['mail_for_admin_enabled']    = 'your@e-mail.address';   //fatal error are announced to this e-mail
```

Once your application is *production ready*, set following before requiring LIB
```php
$backyardConf['die_graciously_verbose'] = false;    //so that description contained within die_graciously() is not revealed on screen
$backyardConf['error_hacked']           = false;    //so that *ERROR_HACK* GET parameter is ignored (and 3rd party can't *debug* your application
```


src/emulator.php get_data in a defined manner (@TODO - better describe)

src/emulate.php is an envelope for emulator.php

Geolocation functions described in src/backyard_geo.php .
Expected structure of geo related tables is in sql/poi.sql .

### Naming conventions (2013-05-04)
1. Naming conventions
    - I try to produce long, self-explaining method names.
    - Comments formatted as Phpdoc, JSDoc
    - I prefer to tag the variable type. I write rather entityA (array of entities) than simple entities. For an instance of song object, rather than song I name the variable songO.
    - Some examples:
        - variable, method, function, elementId – camelCase
        - class name – UpperCamelCase
        - url – hyphened-text
        - file, database_column, database_table – underscored_text
        - constant – BIG_LETTERS
2. Comments
    - Primary language of comments is English.
    - Deprecated or obsolete code blocks are commented with prefix of the letter “x”. I may add reason for making the code obsolete as in the following:
    - //Xhe’s got id from the beginning: $_SESSION["id"] = User::$himself->getId();
