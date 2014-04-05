<?php
/**
 * Purpose: 
 * Library of useful functions
 * //řšč
 * 
 * Globální proměnné:
 * $backyardConf          ... načtení z tohoto, alternativně z db na next line
 * $backyardDatabase
 * $RUNNING_TIME
 * $ERROR_HACK
 * $backyardPage_timestamp
 * 
 * Constant:
 * __BACKYARDROOT__
 * 
 ** 
 * TODO
 * @TODO - oddělit functions init a functions functions
 * @TODO - autorství a původ fce vždy přímo k funkci
 * @TODO - uspořádat dle oblastí
 * @TODO - list of functions přesunout výše a to dle reality
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
 */
