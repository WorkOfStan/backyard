<?php
//backyard 2 compliant
/**
 * Initiation of $page_timestamp must be the first thing a page will do 
 * Store "time" for benchmarking.
 * Inspired by sb_functions.php in sphpblog
 * 
 * @return float
 */
function backyard_getmicrotime() {
    if (version_compare(phpversion(), '5.0.0') == -1) {
        list($usec, $sec) = explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    } else {
        return( microtime(true) );
    }
}

/**
 * 
 * @global float $backyardPage_timestamp
 * @return float
 */
function backyard_getRunningTime() {//111105, because $RUNNING_TIME got updated only when my_error_log makes a row
    global $backyardPage_timestamp;
    return round(backyard_getmicrotime() - $backyardPage_timestamp, 4);
}

/**
 * Returns "Page Generated in x.xxxx seconds"
 * @global array $backyardLangString
 * @global float $backyardPage_timestamp
 * @return string
 */
function backyard_pageGeneratedIn() {
    global $backyardLangString, $backyardPage_timestamp;
    $str = str_replace('%s', round(backyard_getmicrotime() - $backyardPage_timestamp, 4), $backyardLangString['page_generated_in']);
    my_error_log(round(backyard_getmicrotime() - $backyardPage_timestamp, 4), 6, 6);
    return $str;
}
