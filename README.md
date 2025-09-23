# Library In Backyard

**Collection of useful functions**

[![Total Downloads](https://img.shields.io/packagist/dt/workofstan/backyard.svg)](https://packagist.org/packages/workofstan/backyard)
[![Latest Stable Version](https://img.shields.io/packagist/v/workofstan/backyard.svg)](https://packagist.org/packages/workofstan/backyard)
[![Lint Code Base](https://github.com/WorkOfStan/backyard/actions/workflows/linter.yml/badge.svg)](https://github.com/WorkOfStan/backyard/actions/workflows/linter.yml)
[![PHP Composer + PHPUnit + PHPStan](https://github.com/WorkOfStan/backyard/actions/workflows/php-composer-dependencies.yml/badge.svg)](https://github.com/WorkOfStan/backyard/actions/workflows/php-composer-dependencies.yml)

## Requirements

- [PHP 5.3.0 or higher](http://www.php.net/) (i.e. not used [] instead of array() as this short syntax can be used only since PHP 5.4)

## Installation

You can use **Composer** or simply **Download the Release**

### Composer

The preferred method is via [composer](https://getcomposer.org). Follow the
[installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have
composer installed.

Once composer is installed, execute the following command in your project root to install this library:

```sh
composer require workofstan/backyard:^3.3.1
```

Finally, be sure to include the autoloader:

```php
require_once '/path/to/your-project/vendor/autoload.php';
```

### Download the Release

If you abhor using composer, you can download the package in its entirety. The [Releases](https://github.com/WorkOfStan/backyard/releases) page lists all stable versions.
Download any file with the name `backyard/archive/[TAG].zip` for a package including this library and its dependencies.

Uncompress the ZIP file you download, and include the autoloader in your project:

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
        'log_profiling_step'        => false,   //110812, my_error_log neprofiluje rychlost //$PROFILING_STEP = 0.008;//110812, my_error_log profiluje čas mezi dvěma měřenými body vyšší než udaná hodnota sec

        //geo relevant
        'geo_rough_distance_limit' => 1, //float //to quickly get rid off too distant POIs; 1 ~ 100km
        'geo_maximum_meters_from_poi' => 2500, //float //distance considered to be overlapping with the device position // 2500 m is considered exact location due to mobile phone GPS caching
        'geo_poi_list_table_name' => 'poi_list', //string //name of table with POI coordinates
    )
);
```

## Notes

NB: BackyardMysqli creates no Backyard->Mysqli object (as e.g. `Backyard->Json` does) because it is not used by LIB itself and more importantly user of LIB may create any number of those.
Example of usage:

```php
$backyard = new WorkOfStan\Backyard\Backyard(array('logging_level' => 3));
$logger = $backyard->BackyardError;
$dbLink = new WorkOfStan\Backyard\BackyardMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE, $logger);
```

## class BackyardBriefApiClient

Very simple JSON RESTful API client.
It just sends JSON and returns what is to be returned with few optional decorators and error logging.

- `sendJsonLoad` - sends JSON and returns whatever is returned with second OPTIONAL parameter with HTTP verb `GET`,`PUT`,`DELETE`
- `getJsonArray` - sends JSON and returns array decoded from response JSON
- `getArrayArray` - encode array to a JSON and returns array decoded from response JSON
