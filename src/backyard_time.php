<?php
//backyard 2 compliant .. TBD

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



