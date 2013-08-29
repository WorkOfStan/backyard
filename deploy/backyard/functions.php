<?php
/**
 * Name: functions.php
 * Project: LIB/Part of Library In Backyard
 * 
 **
 * Purpose: 
 * Library of functions shared accross the whole dadastrip portal
 * //řšč
 * 
 * List of functions
 * 
 * 
 * 
 **
 * History 
 * 2012-08-07, ERROR_HACK netiskne jen <hr/> jako mezery, ale i PHP_EOL
 * 2011-12-30, www-alfa-gods-cz server set
 * 2011-11-19, $RUNNING_TIME vypsan my_error_log do standardouptput
 * 2011-11-05, function GetRunningTime (){//111105, because $RUNNING_TIME got updated only when my_error_log makes a row
 * 2011-10-01, mute my_error_log output before my_error_log is initialized
 * 2011-09-23, __ROOT__ constant
 * 2011-09-22, if (!function_exists('apache_request_headers')), PHP Redirect Code function movePage($num,$url)
 * 2010-11-21, překonvertováno z ISO 8859-2 do UTF-8
 * 2011-08-05, Load language musi byt driv a dvojite
 * 2011-08-11, Vypis chyb na obrazovku boldem, pokud fatal nebo error
 * 2011-08-12, zaveden ERROR_HACK pro podrobnejsi vypis na zkoumane strance a $PROFILING_STEP pro hledání dlouho trvajících operací
 * 2012-09-18, zaveden $ERROR_HACK přímo jako proměnná, aby bylo možné $ERROR_HACK nastavit ve stránce před require functions.php
 * 2012-09-19, curPageURL and 3 preceeding functions; $myErrorLogMessageType
 * 2012-11-13, free-gods-cz server set
 * 2012-12-29, added Array functions and JSON functions created in Pichacky2012 project and whole file formatted for better reading
 * 2013-01-08, my_error_log(,1); tedy fatal pošle také mail na rejthar@gods.cz
 * 2013-01-24, relevantní z languages/universal/strings.php převedeno do conf.php
 * 2013-04-28, ERROR NUMBER LIST appended
 * 2013-05-22, values for thisserver() externalized to conf.php ; default value for $logging_level in order to write into error_log if db connection is not set ; die_graciously() is defined here (potential colission with external definition); $myErrorLogMessageType changed to $libErrorLogMessageType
 * 
 * 
 * 
 ** 
 * TODO
 * @TODO - oddělit functions init a functions functions
 * @TODO - autorství a původ fce vždy přímo k funkci
 * @TODO - uspořádat dle oblastí
 * @TODO - list of functions přesunout výše a to dle reality

 * 
 ** 
 * 
 * 


  // -------------------------------
  // functions.php Code by S.Rejthar (and J.Štefanides)
  // -------------------------------

  // emoticons_check_tags($smile_path)
  // emoticons_load_tags ()
  // emoticons_load ()
  // emoticons_show ()
  // GetRunningTime (){//111105, because $RUNNING_TIME got updated only when my_error_log makes a row
  // my_error_log ($message, $level=0, $error_number=0) // error_log() modified to log necessary debug information
  // thisserver() //returns string identifying the current server [cz-rozhled|en-rozhled|localhost] if known, otherwise FALSE
  // $thisserverHost=thisserver();
  // make_mysql_query ($mysql_query_string) // mysql_query() with error message management (errors are level 1; logging all queries with level 5)
  // page_generated_in ()  // Returns "Page Generated x.xxxx in seconds"
  // dadaize($sentence) //returns the string with shuffled words (delimiter is a space)
  // random_id($random_id_length = 10) //returns the custom length unique id; default is 10, 2010-11-21
  //
  // fix_xml($text)
  // fix_html_input($text)
  // encode_wml_entity($string)
  // decode_wml_entity($string)
  // strip_diacritics($string)

  // if (!function_exists('apache_request_headers'))
  // movePage($num,$url)
 *
 */


/**
 * Initialization
 */

/**
 * Initiation of $page_timestamp must be the first thing a page will do 
 * Store "time" for benchmarking.
 * Inspired by sb_functions.php in sphpblog
 * 
 */
function getmicrotime() {
    if (version_compare(phpversion(), '5.0.0') == -1) {
        list($usec, $sec) = explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    } else {
        return( microtime(true) );
    }
}
//asi nesmysl//global $page_timestamp;
$page_timestamp = getmicrotime();


/**
 * http://www.php.net/manual/en/function.require-once.php#104265   
 * Use a combination of dirname(__FILE__) and subsequent calls to itself until you reach to the home of your '/index.php'. Then, attach this variable (that contains the path) to your included files. 
 * After this, if you copy paste your codes to another servers, it will still run, without requiring any further re-configurations.
 */
define('__ROOT__', dirname(dirname(__FILE__)));


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
        my_error_log("Host {$_SERVER['SERVER_NAME']} not set in libThisServerArray", 1, 12);
    } //a zaroven by mohlo mi poslat e-mail
    return $result;
}


if (!isset($ERROR_HACK)) $ERROR_HACK=0; //120918, aby bylo možné nastavit ERROR_HACK jako proměnnou ve stránce před zavoláním functions.php
require_once (__ROOT__."/lib/conf.php");
//130124, obsoleted by conf.php//require_once ("connectDB.php");
include (__ROOT__."/lib/openDB.php");
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
$mysql_query_string = "SELECT `variable`, `value` FROM `$dbname`.`system` WHERE `language`='$language'";
$mysql_query_result = make_mysql_query($mysql_query_string, false) or die_graciously(552,$lang_string['lib_to_be_set']);
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
include (__ROOT__."/lib/closeDB.php");
if ($ERROR_HACK < $logging_level) $ERROR_HACK = $logging_level; //120918
    
//my_error_log("Host is ".thisserver(),5,12);//takže se neznámý server zapíše do logu při každém dotazu, známý server se zapíše jen při debug level
$thisserverHost = thisserver(); //takže se neznámý server zapíše do logu při každém dotazu


if (!(isset($logging_level))) $logging_level = 5; //logovat az do urovne zde uvedene - default=5 = debug
$level_name = array(0 => 'unknown', 1 => 'fatal', 'error', 'warning', 'info', 'debug', 'speed');
//print_r($level_name);exit;//debug
//##### /SYSTEM VARIABLE initialization
// Load Language
require_once (__ROOT__."/lib/conf.php");//@TODO 4 - require_once (__ROOT__ . "/languages/universal/strings.php"); //Až zavednu více jazyků, tak namísto `universal' bude jméno jazyka


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

/**
 * 
 * @global type $page_timestamp
 * @return type
 */
function GetRunningTime() {//111105, because $RUNNING_TIME got updated only when my_error_log makes a row
    global $page_timestamp;
    return round(getmicrotime() - $page_timestamp, 4);
}


/**
 * Writes information to be logged by application to its own log (common to the whole host by default)
 * 
 * @global string $username
 * @global type $logging_level
 * @global array $level_name
 * @global type $logging_file
 * @global type $page_timestamp
 * @global type $logging_level_page_speed
 * @global type $RUNNING_TIME
 * @global type $ERROR_HACK
 * @global int $libErrorLogMessageType
 * @param string $message - zpráva k vypsání - při použití error_number bude obsahovat doplňující info
 * @param type $level - úroveň chyby
 * @param type $error_number - číslo chyby, dle které lze chybu vyhodnotit .. bude zapsaná v admin návodu apod. - zatím nepoužito
 * @return type
 * 
 * 
 * ****** ERROR NUMBER LIST **********
 *  0 Unspecified
 *  1 Reserved
 *  2 Reserved
 *  3 Reserved
 *  4 Reserved
 *  5 Reserved
 *  6 Speed
 *  7 Reserved
 *  8 Reserved
 *  9 Reserved
 *  10 Authentization
 *  11 MySQL
 *  12 Domain name
 *  13 Tampered URL or ID
 *  14 Improve this functionality
 *  15 Page was refreshed with the same URL therefore action imposed by URL is ignored
 *  16 Logging values
 *  17 Missing input value
 *  18 Setting of a system value
 *  19 Redirecting
 *  20 Facebook API
 *  21 HTTP communication
 *  22 E-mail
 *  23 Algorithm flow
 *  24 Third party API
 *    
 *  1001 Establish correct error_number
 */
function my_error_log($message, $level = 0, $error_number = 0) {// error_log() modified to log necessary debug information
    //mozna by stalo za to prepsat i jmeno te puvodni, aby se treba i sphpblog psal tam, kde to vidim
    //mohla by být zavedena čtvrtá vstupní proměnná $line=''
    //$line - mělo by být vždy voláno jako basename(__FILE__)."#".__LINE__ , takže bude jasné, ze které řádky source souboru to bylo voláno
    // Ve výsledku do logu zapíše:
    //[Timestamp: d-M-Y H:i:s] [Logging level] [$error_number] [$_SERVER['SCRIPT_FILENAME']] [username@gethostbyaddr($_SERVER['REMOTE_ADDR'])] [sec od startu stránky] $message
    global $username; //Až zavedu uživatele, tak se tam budou zapisovat. (do my_error_log)
    $monthly_rotation = true; //true, pokud má být přípona .log.Y-m.log (výhodou je měsíční rotace); false, pokud má být jen .log (výhodou je sekvenční zápis chyb přes my_error_log a jiných PHP chyb)
    $standardoutput = false; //true, pokud má zároveň vypisovat na obrazovku; false, pokud má vypisovat jen do logu
    //$standardoutput = true;//debug

    global $logging_level, $level_name, $logging_file, $page_timestamp, $logging_level_page_speed, $RUNNING_TIME, $ERROR_HACK, $libErrorLogMessageType,$libMailForAdminEnabled;

    $PROFILING_STEP = false; //110812, my_error_log neprofiluje rychlost
    //$PROFILING_STEP = 0.008;//110812, my_error_log profiluje čas mezi dvěma měřenými body vyšší než udaná hodnota sec  

    $result = true; //pripadne by mohlo byt resetovano pri volani error_log na false
    if (isset($_GET['ERROR_HACK']) && $_GET['ERROR_HACK'] != "" && is_numeric($_GET['ERROR_HACK'])) {//přidat ještě podmínku povolení z db
        $standardoutput = true;
        if ($_GET['ERROR_HACK'] > $logging_level) {
            $logging_level = $_GET['ERROR_HACK'];
        }
    }
    if ($ERROR_HACK > $logging_level) $logging_level = $ERROR_HACK; //120918
        
    //gethostbyaddr($_SERVER['REMOTE_ADDR'])// co udělá s IP, která nelze přeložit? nebylo by lepší logovat přímo IP?

    if (($level <= $logging_level) //logovat 0=unknown/default 1=fatal 2=error 3=warning 4=info 5=debug 6=speed dle $level
            //|| ($level == "6") //speed logovat vždy když je ukázaná
            || (($error_number == "6") && ($logging_level_page_speed <= $logging_level)) //speed logovat vždy když je ukázaná, resp. dle nastavení $logging_level_page_speed
    ) {
        $RUNNING_TIME_PREVIOUS = $RUNNING_TIME;
        //$RUNNING_TIME=round( getmicrotime() - $page_timestamp, 4 );
        //if ($PROFILING_STEP && (($RUNNING_TIME-$RUNNING_TIME_PREVIOUS) > $PROFILING_STEP )) {
        if ((( ($RUNNING_TIME = round(getmicrotime() - $page_timestamp, 4)) - $RUNNING_TIME_PREVIOUS) > $PROFILING_STEP ) && $PROFILING_STEP) {
            $message = "SLOWSTEP " . $message; //110812, PROFILING
        }

        if ($standardoutput)
            echo ((($level <= 2) ? "<b>" : "") . "{$message} [{$RUNNING_TIME}]" . (($level <= 2) ? "</b>" : "") . "<hr/>" . PHP_EOL); //110811, if fatal or error then bold//111119, RUNNING_TIME

        $message_prefix = "[" . date("d-M-Y H:i:s") . "] [" . $level_name[$level] . "] [" . $error_number . "] [" . $_SERVER['SCRIPT_FILENAME'] . "] [" . $username . "@" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "] [" . $RUNNING_TIME . "] [" . $_SERVER["REQUEST_URI"] . "] ";
        if (!$logging_file) {//$logging_file není inicializován
            $result = error_log($message_prefix . "(error: logging_file not set!) $message"); //zapisuje do default souboru
            //zaroven by mohlo poslat mail nebo tak neco .. vypis na obrazovku je asi az krajni reseni
        } else {
            $messageType = 3;
            if ($libErrorLogMessageType == 0)
                $messageType = $libErrorLogMessageType;
            if ($monthly_rotation) {
                //echo ("m_r is true");//debug
                $result = error_log($message_prefix . "$message" . (($messageType != 0) ? (PHP_EOL) : ('')), $messageType, "$logging_file" . "." . date("Y-m") . ".log"); //zapisuje do souboru, který rotuje po měsíci
            } else {
                //echo ("m_r is false");//debug
                $result = error_log($message_prefix . "$message\r\n", $messageType, "$logging_file"); //zapisuje do souboru
            }
        }
        if($level == 1 && $libMailForAdminEnabled){//mailto admin, 130108
          $resultMail = error_log($message_prefix . "$message\r\n", 1, $libMailForAdminEnabled);
        }
    }
    /* Alternative way:
      Logging levels
      Log level   Description                                                                       Set bit
      Warning     Identifies critical errors.                                                       None required
      Debug       Provides additional information for programmers and Technical Product Support.    0 (zero)
      Information Provides information on the health of the system.                                 1
      Trace       Provides detailed information on the execution of the code.                       2

      Log Mask values and logging levels
      LogMask   Bit value Messages included
      0         00000000  Warnings
      1         00000001  Warnings and Debug
      2         00000010  Warnings and Information
      3         00000011  Warnings, Debug and Information
      4         00000100  Warnings and Trace
      7         00000111  Warnings, Debug, Information and Trace
     */

    return $result;
}

/**
 * Returns "Page Generated x.xxxx in seconds"
 * @global type $lang_string
 * @global type $page_timestamp
 * @return type
 */
function page_generated_in() {
    global $lang_string, $page_timestamp;
    $str = str_replace('%s', round(getmicrotime() - $page_timestamp, 4), $lang_string['page_generated_in']);
    my_error_log(round(getmicrotime() - $page_timestamp, 4), 6, 6);
    return ( $str );
}


/******************************************************************************
 * Database (MySQL) FUNCTIONS
 */

/**
 * mysql_query() with error message management
 * @param type $mysql_query_string
 * @param type $ERROR_LOG_OUTPUT
 * @return type
 */
function make_mysql_query($mysql_query_string, $ERROR_LOG_OUTPUT = true) {
    //111010 - function is called even before error_log is initialized, therefore it is necessary to mute my_error_log, hence call make_mysql_query($sql,false);
    //071106 - no occurence within fucntion make_mysql_query so probably superfluous//    global $page_timestamp;
    if ($ERROR_LOG_OUTPUT) my_error_log("Start of query", 6, 11);
    if ((isset($mysql_query_string)) && ($mysql_query_string != "")) {
        $mysql_query_result = mysql_query($mysql_query_string);
        if ($ERROR_LOG_OUTPUT) my_error_log("$mysql_query_string", 5, 11); //debug
        if (!$mysql_query_result) my_error_log(mysql_errno() . ": " . mysql_error() . " /with query: $mysql_query_string", 1, 11);
    } else {
        $mysql_query_result = false;
        if ($ERROR_LOG_OUTPUT) my_error_log("No mysql_query_string set", 1, 11); //debug
    }
    if ($ERROR_LOG_OUTPUT) my_error_log("End of query", 6, 11);
    return ($mysql_query_result);
}


if (!function_exists('customMySQLQuery')) {
    /**
     * 
     * @param type $query
     * @param type $justOneRow
     * @return array one or two dimensional or false
     */
    function customMySQLQuery($query, $justOneRow = false) {
        $result = false;
        $mysql_query_result = make_mysql_query($query) or die_graciously('E100', "{$query} " . mysql_error()); // End script with a specific error message if mysql query fails
        //transforming the query result into an array            
        if (mysql_num_rows($mysql_query_result) > 0) {
            $result = array();
            while ($one_row = mysql_fetch_array($mysql_query_result, MYSQL_ASSOC)) {
                if ($justOneRow){
                    if(mysql_num_rows($mysql_query_result) > 1){
                        my_error_log("query={$query} returned more than one row, but only one row is requested", 11, 3);
                    }
                    return $one_row; //returns one dimensional array
                }
                $result[] = $one_row;
            }
        } else {
            my_error_log("Query returned no results", 5, 16);
        }
        return $result; //returns two dimensional array
    }
} else {
    my_error_log("customMySQLQuery defined outside functions.php", 3, 0);//@TODO 3 - až už žádné nebudou, tak dát mimo !function_exists container
}


/******************************************************************************
 * Genuine DADA FUNCTIONS
 */

/**
 * 
 * @param type $sentence
 * @return type
 */
function dadaize($sentence) {//returns the string with shuffled words (delimiter is a space)
    $temparray = explode(' ', $sentence);
    shuffle($temparray);
    return implode(' ', $temparray);
}

/******************************************************************************
 * Utilities FUNCTIONS
 */

/**
 * returns the custom length unique id; default is 10
 * http://phpgoogle.blogspot.com/2007/08/four-ways-to-generate-unique-id-by-php.html
 * @param type $random_id_length
 * @return type
 */
function random_id($random_id_length = 10) {
    //generate a random id encrypt it and store it in $rnd_id 
    $rnd_id = crypt(uniqid(rand(), 1));

    //to remove any slashes that might have come 
    $rnd_id = strip_tags(stripslashes($rnd_id));

    //Removing any . or / and reversing the string 
    $rnd_id = str_replace(".", "", $rnd_id);
    $rnd_id = strrev(str_replace("/", "", $rnd_id));

    //finally I take the first 10 characters from the $rnd_id 
    $rnd_id = substr($rnd_id, 0, $random_id_length);
    my_error_log("Random id is " . $rnd_id, 5, 16);
    return ($rnd_id);
}

//if (!function_exists('die_graciously')) {
    /**
     * 
     * @param type $errorNumber
     * @param type $errorString
     * @param type $feedbackButtonMarkup
     * @return boolean
     */
    function die_graciously($errorNumber, $errorString, $feedbackButtonMarkup = false) {
        //global $DEFAULT_VALUES;
        global $libShowErrorString;
        my_error_log("Die with error {$errorNumber} - {$errorString}", 1);
        if ($feedbackButtonMarkup) {
            //echo("<html><body>".str_replace(urlencode("%CUSTOM_VALUE%"),urlencode("Error {$errorNumber} - {$errorString}"),$DEFAULT_VALUES['FEEDBACK_BUTTON_MARKUP']));//<html><body> na začátku pomůže, pokud ještě výstup nezačal
            echo("<html><body>" . str_replace(urlencode("%CUSTOM_VALUE%"), urlencode("Error {$errorNumber} - {$errorString}"), $feedbackButtonMarkup)); //<html><body> na začátku pomůže, pokud ještě výstup nezačal
        } else {
            //die below is enough
        }
        die("Error {$errorNumber}".(($libShowErrorString)?" - {$errorString}":"")); //@TODO 4 - jen $errorNumber v hezkém layoutu
        return false;
    }
//} else {
//    my_error_log("die_graciously defined outside functions.php", 3, 0);//@TODO 3 - až už žádné nebudou, tak dát mimo !function_exists container
//}


/******************************************************************************
 * Encoding FUNCTIONS
 */

/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $text
 * @return type
 */
function fix_xml($text) {
    $text = str_replace("&", "&amp;", $text);
    $text = str_replace("<", "&lt;", $text);
    $text = str_replace(">", "&gt;", $text);
    $text = str_replace("\"", "&quot;", $text);

    return $text;
}


/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $text
 * @return type
 */
function fix_html_input($text) {
    $text = str_replace("\"", "&quot;", $text);
    $text = str_replace("<", "&lt;", $text);
    $text = str_replace(">", "&gt;", $text);
    return $text;
}


/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $string
 * @return type
 */
function encode_wml_entity($string) {
// encode &amp; first. pouzito v administraci, ale proc preklada jen '&' ??
    $string = str_replace("&", "&amp;", $string);
    $output = "";
// see e.g. http://www1.tip.nl/~t876506/utf8tbl.html for details how the hex utf-8 translation works..
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
///my_error_log($char);
        if (ord($char[0]) > 127) {
///my_error_log(">127");
            $utf = iconv("iso-8859-2", "utf-8", $char);
            $utf_char = dechex((ord($utf[0]) - 192) * 64 + ord($utf[1]) - 128);
///my_error_log($utf_char);
            if (strlen($utf_char) < 4)
                $utf_char = "0" . $utf_char;
            if (strlen($utf_char) < 4)
                $utf_char = "0" . $utf_char;
            $output .= "&#x$utf_char;";
        } else {
///my_error_log("ascii");
            $output .= $char;
        }
    }
//error_log("encoding $string to $output");
    return $output;
}


/**
 * internal function for decode_wml_entity()
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $dec
 * @return type
 */
function unichr($dec) {
    if ($dec < 128) {
        $utf = chr($dec);
    } else if ($dec < 2048) {
        $utf = chr(192 + (($dec - ($dec % 64)) / 64));
        $utf .= chr(128 + ($dec % 64));
    } else {
        $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
        $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
        $utf .= chr(128 + ($dec % 64));
    }
    return $utf;
}

/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $string
 * @return type
 */
function decode_wml_entity($string) {
    return (preg_replace('/&#x([a-f0-9]+);/mei', "unichr(0x\\1)", $string));
}

/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $string
 * @return type
 */
function strip_diacritics($string) {
    $trans = array("á" => "a", "ä" => "a", "č" => "c", "ď" => "d", "é" => "e", "ě" => "e", "ë" => "e", "í" => "i", "&#239;" => "i", "ň" => "n", "ó" => "o", "ö" => "o", "ř" => "r", "š" => "s", "ť" => "t", "ú" => "u", "ů" => "u", "ü" => "u", "ý" => "y", "&#255;" => "y", "ž" => "z", "Á" => "A", "Ä" => "A", "Č" => "C", "Ď" => "D", "É" => "E", "Ě" => "E", "Ë" => "E", "Í" => "I", "&#207;" => "I", "Ň" => "N", "Ó" => "O", "Ö" => "O", "Ř" => "R", "Š" => "S", "Ť" => "T", "Ú" => "U", "Ů" => "U", "Ü" => "U", "Ý" => "Y", "&#376;" => "Y", "Ž" => "Z");
    // return strtr(iconv("utf-8", "iso-8859-2", $string),  $trans); 
    return strtr($string, $trans);
}

/******************************************************************************
 * HTTP FUNCTIONS
 */

if (!function_exists('apache_request_headers')) {
    /**
     * array apache_request_headers ( void )
     * apache_request_headers replacement for nginx 
     * http://www.php.net/manual/en/function.getallheaders.php#99814
     * 
     * @return array
     */
    function apache_request_headers() {
        $out = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $out[$key] = $value;
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }
}

/**
 * http://www.cyberciti.biz/faq/php-redirect/
 * PHP Redirect Code
 * void movePage($num,$url)
 * 
 * @staticvar array $http
 * @param type $num
 * @param type $url
 */
function movePage($num, $url) {
    static $http = array(
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out"
    );
    header($http[$num]);
    header("Location: $url");
    //die_graciously("100","Redirect to {$url} with {$num} status failed.");//aby se predeslo nepresmerovani pri predchozim vypisu hlavicek
    my_error_log("Redirect to {$url} with {$num} status", 5);
    //if($ERROR_HACK==5)
    //   echo "Redirect to {$url} with {$num} status";
    exit; //po redirektu jiz neni zadouci vykonavat kod
}

if (!function_exists('RetrieveFromPostThenGet')) {
    /**
     * 
     * @param string $nameOfTheParameter
     * @return string or false
     */
    function RetrieveFromPostThenGet($nameOfTheParameter) {
        $result = false; //default value
        if (isset($_POST[$nameOfTheParameter])) {
            $result = $_POST[$nameOfTheParameter];
        } else {
            if (isset($_GET[$nameOfTheParameter])) {
                $result = $_GET[$nameOfTheParameter];
            }
        }
        my_error_log("Retrieved parameter {$nameOfTheParameter}: " . print_r($result, true), (($result) ? (5) : (6)), 16);
        return $result;
    }
} else {
    my_error_log("RetrieveFromPostThenGet defined outside functions.php", 3, 0);//@TODO 3 - až už žádné nebudou, tak dát mimo !function_exists container
}



if (!function_exists('curPageURL')) {
    /**
     * Source: http://www.webcheatsheet.com/PHP/get_current_page_url.php
     * Usage: echo curPageURL();
     * Added into dada/fb/lib.php: 2010-11-03
     * 
     * @param type $includeTheQueryPart
     * @return string
     */
    function curPageURL($includeTheQueryPart = true) {
        if ($includeTheQueryPart) {//added 120819
            $endGame = $_SERVER["REQUEST_URI"];
        } else {
            $endGame = $_SERVER["SCRIPT_NAME"]; //without the query part
        }
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]))
            if ($_SERVER["HTTPS"] == "on") {
                $pageURL .= "s";
            }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $endGame;
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $endGame;
        }
        return $pageURL;
    }
} else {
    my_error_log("curPageURL defined outside fucntions.php", 3, 0);//@TODO 3 - až už žádné nebudou, tak dát mimo !function_exists container
}


/******************************************************************************
 * ARRAY FUNCTIONS
 */


/**
 * this function allows wildcards in the array to be searched
 * 
 * http://www.php.net/manual/en/function.in-array.php#67159
 * //I needed a version of in_array() that supports wildcards in the haystack. Here it is:
 * $haystack = array('*krapplack.de');
 * $needle = 'www.krapplack.de';
 * echo my_inArray($needle, $haystack); # outputs "true"
 * //Unfortunately, fnmatch() is not available on Windows or other non-POSIX compliant systems.
 * 
 * @param type $needle
 * @param type $haystack
 * @return boolean
 */
function inArrayWildcards($needle, $haystack) {
    foreach ($haystack as $value) {
        if (true === fnmatch($value, $needle)) {
            return true;
        }
    }
    return false;
}


/**
 * 
 * @param type $myArray
 * @param type $columnName
 * @return type
 */
function GetOneColumnFromArray($myArray, $columnName) {
    if (!is_array($myArray))
        return array(); //konzistentnější než false
    $result = array();
    foreach ($myArray as $key => $row) {
        $result[$key] = $row[$columnName];
    }
    return $result;
}

/**
 * 
 * @param type $myArray
 * @return type
 */
function DumpArrayAsOneLine($myArray) {
    //@TODO - ozkoušeet, aby tak váženě fungovalo
    return (preg_replace('/\n/', ' ', preg_replace('/\r/', ' ', preg_replace('/\s\s+/', ' ', print_r($myArray, true))
                    ))
            );
}

/**
 * 
 * @param type $searchedValue
 * @param type $searchedArray
 * @param type $columnName
 * @return boolean
 */
function ArrayVlookup($searchedValue, $searchedArray, $columnName) {
    //returns first row with exact match  //@TODO 4 - přidat parametr na vrácení všech rows s exact match
    //debug//echo "searching $searchedValue in the column $columnName in the array ".print_r($searchedArray,true);exit;
    if (!is_array($searchedArray)) {
        my_error_log("ArrayVlookup: array parameter is not an array", 2);
    } else {
        foreach ($searchedArray as $key => $row) {
            if (isset($row[$columnName])) {
                if ($row[$columnName] == $searchedValue)
                    return $row;
            } else {
                my_error_log("ArrayVlookup: $columnName not in " . print_r($row, true), 3);
            }
        }
    }
    return false;
}

/******************************************************************************
 * JSON FUNCTIONS
 */

/**
 * Minify JSON and if JSON is not valid it report it in the my_error_log and outputs a preformatted error500 json string
 * @param type $jsonInput
 * @param type $logLevel - optional - default is not to be verbose
 * @return string
 */
function MinifyJSON($jsonInput, $logLevel = 5) {
    $jsonOutput = json_encode(json_decode($jsonInput)); //optimalizace pro výstup
    if ($jsonOutput == 'null') {
        my_error_log("ERROR IN JSON: {$jsonInput}", 1, 16);
        $jsonOutput = '{"status": "500", "error": "Internal error"}'; //error output
    } else {
        my_error_log("JSON input: {$jsonInput}", $logLevel, 16);
        my_error_log("JSON output: {$jsonOutput}", $logLevel, 16);
    }
    return $jsonOutput;
}


/**
 * @desc Output JSON
 * @param string $jsonString to be minified
 * @param bool $exitAfterOutput  - optional - default is to let the script continue
 * @param int $logLevel - optional - default is not to be verbose
 */
function OutputJSON($jsonString, $exitAfterOutput = false, $logLevel = 5) {
    header("Content-type: application/json");
    echo(MinifyJSON($jsonString, $logLevel)); //jako json
    if ($exitAfterOutput)
        exit;
}
