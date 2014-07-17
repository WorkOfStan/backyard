Library In Backyard
===================
**Collection of useful functions**


backyard 1 usage
-------------------

This array MUST be created by the application before invoking backyard     
```sh
$backyardDatabase = array(
    'dbhost' => 'localhost',
    'dbuser' => 'user',
    'dbpass' => '',
    'dbname' => 'default',
);
```


backyard 2 usage
-------------------

All backyard functions are named as backyard_camelCase 

The array $backyardDatabase (see above) SHOULD be created ONLY IF there is a table \`system\` (or different name stated in $backyardDatabase['system_table_name']) with fields containing backyard system info.

Usage:
```sh
require_once dirname(__FILE__) . '/lib/backyard/src/backyard_system.php';
```
Requires the basic LIB library. All other LIB components to be included by
```sh
require_once (__BACKYARDROOT__."/backyard_XXX.php");
```

**Recommendation**

To be in control of the logging, set following before requiring LIB
```sh
$backyardConf['logging_level'] = 3;         //so that only fatal, error, warning are logged
$backyardConf['error_log_message_type'] = 3;//so that logging does not go to PHP system logger but to the monthly rotated file specified on the next line
$backyardConf['logging_file'] = '/var/www/www.alfa.gods.cz/logs/error_php.log';
$backyardConf['mail_for_admin_enabled']    = 'your@e-mail.address';   //fatal error are announced to this e-mail
```

Once your application is *production ready*, set following before requiring LIB
```sh
$backyardConf['die_graciously_verbose'] = false;    //so that description contained within die_graciously() is not revealed on screen
$backyardConf['error_hacked']           = false;    //so that *ERROR_HACK* GET parameter is ignored (and 3rd party can't *debug* your application
```


src/emulator.php get_data in a defined manner (@TODO - better describe)

src/emulate.php is an envelope for emulator.php