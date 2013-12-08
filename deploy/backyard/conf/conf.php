<?php
/**
 * Name: lib/conf.php
 * Project: LIB/Part of Library In Backyard
 * 
 ** 
 * Purpose: 
 * Configuration of LIB which may be different on different environments
 * Furthermore, conf_private.php should be in .gitignore so that each developer may redefine its development environment.
 * Each project using LIB MAY and SHOULD use its own conf.php to define table names, server white lists, social credentials and other project pseudo-constants.
 * 
 * For deployment:
 * create table system in the used database following the lib_template/system.sql . Note that logging_file path MUST be working on your system.
 * This library must be in folder called lib. (@TODO - maybe change??)
 * 
 ** 
 * History
 * 2013-01-24, created instead of language/universal/string.php and connectDB.php
 * 2013-05-22, added $libShowErrorString and $libThisServerArray
 * 2013-11-01, this is default not to be changed, all changes to be done in conf_private.php 
 *
 ** 
 * TODO  
 * 
 * 
 */


/**
 * DEFAULT Language strings
 * It is possible to redefine at a later stage when the user language is known.
 */
if(!isset($lang_string))$lang_string=array();
$lang_string['dada_general_error'] = 'An error has occured. Please, contact <a href="mailto:app-support@gods.cz">administrator</a>.';
$lang_string['dada_general_error_page'] = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>General error</title></head><body>'.$lang_string['dada_general_error'].'<hr/><a href="/">Homepage</a></body></html>';
//$lang_string['dada_general_error_page'] = $lang_string['godsapps_general_error_page'] = $lang_string['dada_general_error_page'];
$lang_string['page_generated_in'] = 'Stránka generována %s sekund';
$lang_string['lib_to_be_set'] = 'LIBrary In Backyard to be set. Check all sections in backyard/conf/conf.php';

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
//$libErrorLogMessageType = 0; //parameter message_type http://cz2.php.net/manual/en/function.error-log.php for my_error_log; default is 3, i.e. append to the file destination set in table system; it is however possible to set equal to 0 to send message to PHP's system logger

$libMailForAdminEnabled = false;//fatal error may just be written in log
//$libMailForAdminEnabled = "rejthar@gods.cz";//on production, it is however recommended to set an e-mail, where to announce fatal errors

$libShowErrorString = true;//show details by die_graciously() on screen (it is always in the error_log); on production it is recomended to be set to to false due security

/**
 * Server and database
 */
//set short_name for relevant domains
$libThisServerArray = array(
    array('domain_preg_match' => '^localhost$|^127.0.0.1$', 'short_name' => 'localhost')
);

//set connect strings to database for all the short_named servers
//@todo - use it only as an array backyardDatabase from the four credentials below in order not to confuse it with system credentials of the application that is using backyard    
    $dbhost = 'localhost';  //OBSOLETE, keep for backward compatibility
    $dbuser = 'user';       //OBSOLETE, keep for backward compatibility
    $dbpass = '';           //OBSOLETE, keep for backward compatibility
    $dbname = 'default';    //OBSOLETE, keep for backward compatibility    
$backyardDatabase = array(
    'dbhost' => 'localhost',
    'dbuser' => 'user',
    'dbpass' => '',
    'dbname' => 'default',
);

if(file_exists(__BACKYARDROOT__."/conf/conf_private.php")) include_once (__BACKYARDROOT__."/conf/conf_private.php");//conf_private.php should be in .gitignore so that each developer may redefine its development environment
