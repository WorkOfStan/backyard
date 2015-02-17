<?php
//backyard 2 compliant
if (!defined('__BACKYARDROOT__')) {
    define('__BACKYARDROOT__', __DIR__);
}

if (!function_exists('my_error_log')) {

    function my_error_log($message, $level = 0, $error_number = 0) {
        if ($level <= 3) {
            error_log($message);
        }
    }

}

if (!isset($backyardConf['die_graciously_verbose'])) {
    $backyardConf['die_graciously_verbose'] = false;
}

/**
 * 
 * @param string $errorNumber
 * @param string $errorString
 * @param string $feedbackButtonMarkup
 * @return void (die)
 */
function backyard_dieGraciously($errorNumber, $errorString, $feedbackButtonMarkup = false) {
    global $backyardConf;
    my_error_log("Die with error {$errorNumber} - {$errorString}", 1);
    if ($feedbackButtonMarkup) {
        echo("<html><body>" . str_replace(urlencode("%CUSTOM_VALUE%"), urlencode("Error {$errorNumber} - "
                        . (($backyardConf['die_graciously_verbose']) ? " - {$errorString}" : "")
                ), $feedbackButtonMarkup)); //<html><body> na začátku pomůže, pokud ještě výstup nezačal
    }
    die("Error {$errorNumber}" . (($backyardConf['die_graciously_verbose']) ? " - {$errorString}" : ""));
}
