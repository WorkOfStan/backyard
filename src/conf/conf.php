<?php
//backyard 2 compliant
/**
 * Default configuration for backyard 2
 * May be changed by override in conf_private.php.
 * 
 * DO NOT CHANGE THE DEFAULT VALUES HERE
 **
 * Purpose: 
 * Configuration of LIBackyard which may be different on different environments
 * Furthermore, conf_private.php should be in .gitignore so that each developer may redefine its development environment.
 * Each project using Backyard MAY and SHOULD use its own conf.php to define table names, server white lists, social credentials and other project pseudo-constants.
 * 
 * For deployment:
 * either set $backyardConf or
 * create table system in the used database following the resources/lib_template/system.sql . Note that logging_file path MUST be working on your system.
 * 
 ** 
 * History
 * 2013-01-24, created instead of language/universal/string.php and connectDB.php
 * 2013-05-22, added $libShowErrorString and $libThisServerArray
 * 2013-11-01, this is default not to be changed, all changes to be done in conf_private.php
 * 2014-04-05, modified for backyard 2
 *
 */

/**
 * DEFAULT Language strings
 * It is possible to redefine at a later stage when the user language is known.
 */
if(!isset($backyardLangString)){
    $backyardLangString=array();
}
$backyardLangString['general_error'] = 'An error has occured. Please, contact <a href="mailto:app-support@gods.cz">administrator</a>.';
$backyardLangString['general_error_page'] = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>General error</title></head><body>'
        . $backyardLangString['general_error'].'<hr/><a href="/">Homepage</a></body></html>';
$backyardLangString['page_generated_in'] = 'Stránka generována %s sekund';
$backyardLangString['lib_to_be_set'] = 'LIBrary In Backyard 2 to be set. Check all sections in backyard/conf/conf.php';

/** 
 * Set timezone
 */
//  putenv("TZ=CET"); //setting of CET time on iPage  //na dadastrip.cz však generuje: Warning: putenv() [function.putenv]: Safe Mode warning: Cannot set environment variable 'TZ' - it's not in the allowed list in /data/cust/dadastrip/www.dadastrip.cz/lib/functions.php on line 46
//ale MySQL je stejně v -4 :((
//maybe try//date_default_timezone_set('Europe/Kiev');
//maybe try//ini_set('date.timezone', 'Europe/Kiev');

/**
 * Error log settings
 */
$backyardConfDefault = array(
    'language'                  => 'cs',    //použitý jazyk  
    'logging_level'             => 5,    //úroveň logování//logovat az do urovne zde uvedene - default=5 = debug //logovat az do urovne zde uvedene: 0=unknown/default_call 1=fatal 2=error 3=warning 4=info 5=debug/default_setting 6=speed  //aby se zalogovala alespoň missing db musí být logování nejníže defaultně na 1 //1 as default for writing the missing db at least to the standard ErrorLog
    'logging_level_name'        => array(0 => 'unknown', 1 => 'fatal', 'error', 'warning', 'info', 'debug', 'speed'),
    'logging_file'              => '',      //soubor, do kterého má my_error_log() zapisovat
    'logging_level_page_speed'  => 5,    //úroveň logování, do které má být zapisována rychlost vygenerování stránky
    'error_log_message_type'    => 3,   //default log zapisuje do adresáře log; po spuštění functions.php je možné nastavit např. na 0 a směrovat tak do default logu //parameter message_type http://cz2.php.net/manual/en/function.error-log.php for my_error_log; default is 3, i.e. append to the file destination set in table system; it is however possible to set equal to 0 to send message to PHP's system logger       
    'die_graciously_verbose'    => true,    //show details by die_graciously() on screen (it is always in the error_log); on production it is recomended to be set to to false due security
    'mail_for_admin_enabled'    => false,   //fatal error may just be written in log //$backyardMailForAdminEnabled = "rejthar@gods.cz";//on production, it is however recommended to set an e-mail, where to announce fatal errors
    'log_monthly_rotation'      => true,    //true, pokud má být přípona .log.Y-m.log (výhodou je měsíční rotace); false, pokud má být jen .log (výhodou je sekvenční zápis chyb přes my_error_log a jiných PHP chyb)
    'log_standard_output'       => false,   //true, pokud má zároveň vypisovat na obrazovku; false, pokud má vypisovat jen do logu
    'log_profiling_step'        => false,   //110812, my_error_log neprofiluje rychlost //$PROFILING_STEP = 0.008;//110812, my_error_log profiluje čas mezi dvěma měřenými body vyšší než udaná hodnota sec
    'error_hacked'              => true,    //ERROR_HACK parameter is reflected
);

if(!isset($backyardConf)){
    $backyardConf=array();
}
foreach ($backyardConfDefault AS $key => $value){
    if(!isset($backyardConf[$key])){
        $backyardConf[$key] = $value;
    }
}
    
if(file_exists(__BACKYARDROOT__."/conf/conf_private.php")) include_once (__BACKYARDROOT__."/conf/conf_private.php");//conf_private.php should be in .gitignore so that each developer may redefine its development environment

if (isset($backyardDatabase)){
    /* this array must be created by the application before invoking backyard     
    $backyardDatabase = array(
        'dbhost' => 'localhost',
        'dbuser' => 'user',
        'dbpass' => '',
        'dbname' => 'default',
    );
    */
    if(!isset($backyardDatabase['system_table_name'])){
        $backyardDatabase['system_table_name'] = 'system';
    }

    include (__BACKYARDROOT__."/openDB.php");
    $mysql_query_string = "SELECT `variable`, `value` FROM `{$backyardDatabase['dbname']}`.`{$backyardDatabase['system_table_name']}` WHERE `language` LIKE '{$backyardConf['language']}'";
    $mysql_query_result = backyard_mysql_query($mysql_query_string, $backyardConnection, false) or die_graciously(552,$backyardLangString['lib_to_be_set']);
    $tempArray = mysql_fetch_array($mysql_query_result, MYSQL_ASSOC);
    foreach($tempArray AS $key => $value){
        switch ($key) {
            case "logging_level":
                $backyardConf['logging_level']=$value;
                break;
            case "logging_file":
                 $backyardConf['logging_file']=$value;
                break;
            case "logging_level_page_speed":
                 $backyardConf['logging_level_page_speed']=$value;
                break;
        }
    }
    include (__BACKYARDROOT__."/closeDB.php");        
}

if ($backyardConf['error_hacked'] && isset($_GET['ERROR_HACK']) && $_GET['ERROR_HACK'] != "" && is_numeric($_GET['ERROR_HACK'])) {    
    $backyardConf['log_standard_output'] = true;
    if ((int)$_GET['ERROR_HACK'] > $backyardConf['logging_level']) {
        $backyardConf['logging_level'] = (int)$_GET['ERROR_HACK'];
    }
    my_error_log("ERROR_HACK aplikovan na stranku", $_GET['ERROR_HACK']);    
}

/* 140406 disabled because it effectively made ignore the lower $backyardConf['logging_level']
if ($ERROR_HACK < $backyardConf['logging_level']) {
    $ERROR_HACK = $backyardConf['logging_level']; //120918
}
 * 
 */