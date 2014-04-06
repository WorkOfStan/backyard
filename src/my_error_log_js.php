<?php
/**
 * Name: ---.php
 * Project: LIB/Part of Library In Backyard
 * 
 ** 
 * Purpose: 
 * 
 * 
 * 
 ** 
 * History
 * 
 *
 ** 
 * TODO  
 * 
 * 
 */

//121229, companion for javascript:my_error_log to be included in the main project script after include(lib/function.php)
if(!(function_exists('my_error_log') && function_exists('backyard_retrieveFromPostThenGet'))){
    error_log("my_error_log_js called or included without proper BACKYARD library initialisation");
    exit;
}
$tempMyErrorLogMessage=backyard_retrieveFromPostThenGet('my_error_log_message');
$tempMyErrorLogLevel=backyard_retrieveFromPostThenGet('my_error_log_level');
if($tempMyErrorLogMessage && $tempMyErrorLogLevel){
    my_error_log($tempMyErrorLogMessage, $tempMyErrorLogLevel);
    exit;
} elseif ($tempMyErrorLogMessage){
    my_error_log("No my_error_log_level parameter set for {$tempMyErrorLogMessage}");
    exit;
}//else nothing and continue with includer script
