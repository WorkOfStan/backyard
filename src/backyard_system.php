<?php
/**
 * Name: backyard_system.php
 * Project: LIB/Part of Library In Backyard v.2
 * 
 **
 * Purpose: 
 * Library of useful functions
 * //řšč
 ** 
 * For history and TODO see the backyard_system.php file
 *
 */


/**
 * Initialization
 */
require_once 'backyard_time.php';
$backyardPage_timestamp = getmicrotime(); //Initiation of $page_timestamp must be the first thing a page will do.

/**
 * http://www.php.net/manual/en/function.require-once.php#104265   
 * Use a combination of dirname(__FILE__) and subsequent calls to itself until you reach to the home of your '/index.php'. Then, attach this variable (that contains the path) to your included files. 
 * After this, if you copy paste your codes to another servers, it will still run, without requiring any further re-configurations.
 */
define('__BACKYARDROOT__', dirname(__FILE__));

$libErrorLogMessageType = 3; //default log zapisuje do adresáře log; po spuštění functions.php je možné nastavit např. na 0 a směrovat tak do default logu        
$logging_level=1;//default for writing the missing db at least to the standard ErrorLog

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


//před require_once (__ROOT__."/lib/conf.php"); !
function thisserver() { //returns string identifying the current server [cz-rozhled|en-rozhled|localhost] if known, otherwise FALSE
    global $libThisServerArray;
    if(!is_array($libThisServerArray))die_graciously (550, 'libThisServerArray not set in lib/conf.php');
    $result = false;
    /*
    if (preg_match("/www.alfa.gods.cz$/", $_SERVER['SERVER_NAME'])) {
        $result = "www-alfa-gods-cz";
    }
    if (preg_match("/free.gods.cz$/", $_SERVER['SERVER_NAME'])) {
        $result = "free-gods-cz";
    }
    if (preg_match("/godsapps.eu$/", $_SERVER['SERVER_NAME'])) {
        $result = "godsapps";
    }
    if (preg_match("/dadastrip.cz$/", $_SERVER['SERVER_NAME'])) {
        $result = "cz-rozhled";
    }
    if (preg_match("/dadastrip.com$/", $_SERVER['SERVER_NAME'])) {
        $result = "en-rozhled";
    }
    if (preg_match("/^localhost$|^127.0.0.1$|ntbr905.rdm.cz$|^192.168.1.214$/", $_SERVER['SERVER_NAME'])) {
        $result = "localhost";
    }
     * 
     */
    foreach ($libThisServerArray as $value){
        if (preg_match("/{$value['domain_preg_match']}$/", $_SERVER['SERVER_NAME'])) {
            $result = $value['short_name'];
            break;
        }        
    }
    if (!$result) {
        //@TODO - před čím to má varovat???//my_error_log("Host {$_SERVER['SERVER_NAME']} not set in libThisServerArray", 1, 12);
    } //a zaroven by mohlo mi poslat e-mail
    return $result;
}

require_once 'functions_mysql.php';

if (!isset($ERROR_HACK)) $ERROR_HACK=0; //120918, aby bylo možné nastavit ERROR_HACK jako proměnnou ve stránce před zavoláním functions.php
require_once (__BACKYARDROOT__."/conf/conf.php"); //@TODO a proč je pak ještě na řádce 215?
if(isset($backyardDatabase)){
//130124, obsoleted by conf.php//require_once ("connectDB.php");
    include (__BACKYARDROOT__."/openDB.php");
//##### SYSTEM VARIABLE initialization
//$timestamp_null - konstanta pro nenastavené datum v položce v databázi
//$language - použitý jazyk
//$logging_level - úroveň logování 
//$logging_file - soubor, do kterého má my_error_log() zapisovat
//$logging_level_page_speed - úroveň logování, do které má být zapisována rychlost vygenerování stránky
//$timestamp_null = "NULL";//Není použitelné protože `= "NULL"' je něco jiného než `IS NULL'
    $timestamp_null = "1990-01-01 22:40:16"; //rozhled.cz: Verze MySQL: 4.0.24_Debian-10sarge2-log asi nepodporuje TIMESTAMP(14) aby mohlo být NULL
//@TODO - comments výše vztáhnout k realizaci níže
    $language = 'cs'; //default_language zatím načítat nebudu
    $mysql_query_string = "SELECT `variable`, `value` FROM `{$backyardDatabase['dbname']}`.`{$backyardDatabase['system_table_name']}` WHERE `language` LIKE '$language'";
    $mysql_query_result = backyard_mysql_query($mysql_query_string, $backyardConnection, false) or die_graciously(552,$lang_string['lib_to_be_set']);
    while ($dadasys = mysql_fetch_array($mysql_query_result, MYSQL_ASSOC)) {
        //print_r ($dadasys);//debug
        switch ($dadasys['variable']) {
            case "logging_level":
                $logging_level = $dadasys['value']; //logovat az do urovne zde uvedene: 0=unknown/default_call 1=fatal 2=error 3=warning 4=info 5=debug/default_setting 6=speed 
                break;
            case "logging_file":
                $logging_file = $dadasys['value'];
                break;
            case "logging_level_page_speed":
                $logging_level_page_speed = $dadasys['value'];
                break;
            case "timestamp_null":
                $timestamp_null = $dadasys['value'];
                break;
        }
    }
include (__BACKYARDROOT__."/closeDB.php");
}
if ($ERROR_HACK < $logging_level) $ERROR_HACK = $logging_level; //120918
    
//my_error_log("Host is ".thisserver(),5,12);//takže se neznámý server zapíše do logu při každém dotazu, známý server se zapíše jen při debug level
$thisserverHost = thisserver(); //takže se neznámý server zapíše do logu při každém dotazu


if (!(isset($logging_level))) $logging_level = 5; //logovat az do urovne zde uvedene - default=5 = debug
$level_name = array(0 => 'unknown', 1 => 'fatal', 'error', 'warning', 'info', 'debug', 'speed');
//print_r($level_name);exit;//debug
//##### /SYSTEM VARIABLE initialization
// Load Language
require_once (__BACKYARDROOT__."/conf/conf.php");//@TODO 4 - require_once (__ROOT__ . "/languages/universal/strings.php"); //Až zavednu více jazyků, tak namísto `universal' bude jméno jazyka


$username = "anonymous"; //Až zavedu uživatele, tak se tam budou zapisovat. (do my_error_log)

if (isset($_GET['ERROR_HACK']) && $_GET['ERROR_HACK'] != "" && is_numeric($_GET['ERROR_HACK'])) {
    my_error_log("ERROR_HACK aplikovan na stranku", $_GET['ERROR_HACK']);
}

$RUNNING_TIME = 0; //110812, k profilování rychlosti

/**
 * Core
 */


/******************************************************************************
 * Logging and profiling FUNCTIONS
 */


require_once 'backyard_error_log.php';







include_once 'backyard_crypt.php';
require_once 'backyard_my_error_log_dummy.php';//required AFTER my_error_log is defined; contains backyard_dieGraciously()
require_once 'functions_encoding.php';
require_once 'backyard_http.php';
require_once 'backyard_array.php';
require_once 'backyard_json.php';