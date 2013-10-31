<?php
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
