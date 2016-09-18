<?php
error_log('Obsolete LIB 2 initiated. Exiting.');
die('LIB2');
//namespace GodsDev\backyard;


/*
160208
class backyard , která jako parametr vezme pole s nastavením /je možné kusy předefinovat za běhu?/
ta v sobě namapuje další classy s funkcema
co classa to soubor
autoloading jen v případě potřeby

$backyard = new backyard (array $backyardConf);
anebo možná spíš
$backyard = new backyard ();
$backyard->conf($backyardConf);

Volání metod

$backyard->Http->getData(string $url);
$backyard->Json->outputJSON(string $json);

.. kde Http a Json jsou soubory Http.php a Json.php obsahující classy Http a Json v namespace GodsDev\backyard 

*/


/**
 * Name: backyard_system.php
 * Project: LIB/Part of Library In Backyard v.2
 * *
 * Purpose: 
 * Library of useful functions
 * //řšč
 * * 
 * For history and TODO see the backyard_readme.php file
 */

/**
 * Initialization
 */
require_once __DIR__ . '/backyard_time.php';
$backyardPage_timestamp = backyard_getmicrotime(); //Initiation of $page_timestamp must be the first thing a page will do.

/**
 * http://www.php.net/manual/en/function.require-once.php#104265   
 * Use a combination of dirname(__FILE__) and subsequent calls to itself until you reach to the home of your '/index.php'. Then, attach this variable (that contains the path) to your included files. 
 * After this, if you copy paste your codes to another servers, it will still run, without requiring any further re-configurations.
 */
define('__BACKYARDROOT__', __DIR__);

/**
 * Random seed initiation for mt_rand()
 */
/**
 * // Note: As of PHP 4.2.0, there is no need to seed the random number generator with srand() or mt_srand() as this is now done automatically.
 * // www.su.cz má PHP 4.1.2 so: seed with microseconds
  function make_seed() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
  }
  mt_srand(make_seed());
 */

require_once __BACKYARDROOT__ . '/backyard_error_log.php';
require_once __BACKYARDROOT__ . '/backyard_mysql.php';

if (!isset($ERROR_HACK)) {//120918, aby bylo možné nastavit ERROR_HACK jako proměnnou ve stránce před zavoláním functions.php
    $ERROR_HACK = 0;
}
require_once __BACKYARDROOT__ . "/conf/conf.php";

$RUNNING_TIME = 0; //110812, k profilování rychlosti

include_once __BACKYARDROOT__ . '/backyard_crypt.php';
require_once __BACKYARDROOT__ . '/backyard_my_error_log_dummy.php'; //required AFTER my_error_log is defined; contains backyard_dieGraciously()
include_once __BACKYARDROOT__ . '/functions_encoding.php';
require_once __BACKYARDROOT__ . '/backyard_http.php';
require_once __BACKYARDROOT__ . '/backyard_array.php';
require_once __BACKYARDROOT__ . '/backyard_json.php';