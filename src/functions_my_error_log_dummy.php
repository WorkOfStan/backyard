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

if (!function_exists('die_graciously')) {
    /**
     * 
     * @param type $errorNumber
     * @param type $errorString
     * @param type $feedbackButtonMarkup
     * @return boolean
     */
    function die_graciously($errorNumber, $errorString, $feedbackButtonMarkup = false) {
        //global $DEFAULT_VALUES;
//        global $libShowErrorString;
        $libShowErrorString = false;
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
}
