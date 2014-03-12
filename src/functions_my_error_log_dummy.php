<?php

if (!defined('__BACKYARDROOT__')) {
    define('__BACKYARDROOT__', dirname(__FILE__));
}

if (!function_exists('my_error_log')) {
    function my_error_log($message, $level = 0, $error_number = 0){
        if($level<=3){
            error_log($message);
        }
    }
}

/**
 * 
 * @param type $errorNumber
 * @param type $errorString
 * @param type $feedbackButtonMarkup
 * @return boolean
 */
function backyard_dieGraciously($errorNumber, $errorString, $feedbackButtonMarkup = false) {
    $libShowErrorString = false;
    my_error_log("Die with error {$errorNumber} - {$errorString}", 1);
    if ($feedbackButtonMarkup) {
        echo("<html><body>" . str_replace(urlencode("%CUSTOM_VALUE%"), urlencode("Error {$errorNumber} - {$errorString}"), $feedbackButtonMarkup)); //<html><body> na začátku pomůže, pokud ještě výstup nezačal
    }
    die("Error {$errorNumber}".(($libShowErrorString)?" - {$errorString}":"")); //@TODO 4 - jen $errorNumber v hezkém layoutu
    return false;
}
