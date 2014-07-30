<?php
//backyard 2 compliant
if (!function_exists('my_error_log')) {
    require_once 'backyard_my_error_log_dummy.php';
}

/* * ****************************************************************************
 * Database (MySQL) FUNCTIONS
 */

/**
 * mysql_query() with error message management and link identifier
 * Replaces make_mysql_query($mysql_query_string, $ERROR_LOG_OUTPUT = true)
 * @param string $mysql_query_string
 * @param resource $link_identifier [optional]
 * @param boolean $ERROR_LOG_OUTPUT [optional]
 * @return a resource on success, or <b>FALSE</b> on error
 */
function backyard_mysql_query($mysql_query_string, $link_identifier = NULL, $ERROR_LOG_OUTPUT = true) {
    //111010 - function is called even before error_log is initialized, therefore it is necessary to mute my_error_log, hence call make_mysql_query($sql,false);
    if ($ERROR_LOG_OUTPUT) {
        my_error_log("Start of query", 6, 11);
    }
    if (!$mysql_query_string) {
        if ($ERROR_LOG_OUTPUT) {
            my_error_log("mysql_query_string is empty", 1, 11); //debug               
        }
        return false;
    }
    if ($link_identifier == NULL) {
        $mysql_query_result = mysql_query($mysql_query_string);
    } else {
        $mysql_query_result = mysql_query($mysql_query_string, $link_identifier);
    }
    if (!$mysql_query_result) {
        my_error_log(mysql_errno() . ": " . mysql_error() . " /with query: $mysql_query_string", 1, 11);
    } elseif ($ERROR_LOG_OUTPUT) {
        my_error_log("$mysql_query_string", 5, 11); //debug
    }
    return $mysql_query_result;
}

/**
 * Make a MySQL query and if the result is non empty, transforms the query result into a one or two dimensional array.
 * Replaces customMySQLQuery()
 * @param string $query
 * @param bool $justOneRow [optional]
 * @param resource $link_identifier [optional]
 * @return mixed: array one or two dimensional or false
 */
function backyard_mysqlQueryArray($query, $justOneRow = false, $link_identifier = NULL) {
    $mysql_query_result = backyard_mysql_query($query, $link_identifier) or die_graciously('E100', "{$query} " . mysql_error()); // End script with a specific error message if mysql query fails
    if (is_bool($mysql_query_result)) {
        return $mysql_query_result; //For other type of SQL statements, INSERT, UPDATE, DELETE, DROP, etc, mysql_query() returns TRUE on success or FALSE on error.
    }
    if (!mysql_num_rows($mysql_query_result)) {//either false or zero
        my_error_log("Query {$query} returned no results", 5, 16);
        return false;
    }
    $result = array();
    while ($one_row = mysql_fetch_array($mysql_query_result, MYSQL_ASSOC)) {
        if ($justOneRow) {
            if (mysql_num_rows($mysql_query_result) > 1) {
                my_error_log("query={$query} returned more than one row, but only one row is requested", 11, 3);
            }
            return $one_row; //returns one dimensional array
        }
        $result[] = $one_row;
    }
    return $result; //returns two dimensional array
}

/**
 * class backyard_mysqli based on my_mysqli from https://github.com/GodsDev/repo1/blob/58fa783d4c7128579b729465dc36b45568f9ddb1/myreport/src/mreport_functions.php as of 120914
 */
class backyard_mysqli extends mysqli {

    public function __construct($host, $user, $pass, $db) {
        parent::__construct($host, $user, $pass, $db);

        if (mysqli_connect_error()) {
            backyard_dieGraciously(
                    '5013', //@TODO 3 -  test die_graciously
                    'Connect Error (' . mysqli_connect_errno() . ') '
                    . mysqli_connect_error());
        } //120914
    }

    //120914
    // http://www.blrf.net/blog/223/code/php/extending-mysqli-class-with-example/
    /**
     * Query method
     * if everything is OK, return the mysqli_result object
     * that is returned from parent query method
     * @param string $sql SQL to execute
     * @return mysqli_result Object
     * @throws DBQueryException
     */
    public function query($sql, $ERROR_LOG_OUTPUT = true) { //S.R. upravuji dle functions.php ... make_mysql_query
        if ($ERROR_LOG_OUTPUT) {
            my_error_log("Start of query", 6, 11);
        }
        if ($sql != "") {
            // here, we could log the query to sql.log file
            // note that, no error check is being made for this file
            //file_put_contents('/tmp/sql.log', $sql . "\n", FILE_APPEND);
            if ($ERROR_LOG_OUTPUT) {
                my_error_log($sql, 5, 11);
            }
            $result = @parent::query($sql); //parent query method called with @ operator, to supress error messages
            if ($this->errno != 0) {
                if ($ERROR_LOG_OUTPUT) {
                    my_error_log("{$this->errno} : {$this->error} /with query: {$sql}", 1, 11);
                }
            }
        } else {
            $result = false;
            if ($ERROR_LOG_OUTPUT) {
                my_error_log("No mysql_query_string set", 1, 11); //debug
            }
        }
        if ($ERROR_LOG_OUTPUT) {
            my_error_log("End of query", 6, 11);
        }
        return $result;
    }

    /**
     * 
     * @param string $sql
     * @param boolean $justOneRow (default = false)
     * @return 
     *      false - if no results
     *      one dimensional array - if $justOneRow == true
     *      two dimensional array - if $justOneRow == false
     */
    public function queryArray($sql, $justOneRow = false) {
        $result = false;
        $mysqlQueryResult = $this->query($sql); //$ERROR_LOG_OUTPUT = true by default
        //transforming the query result into an array
        if ($mysqlQueryResult == false || $mysqlQueryResult->num_rows == 0) {
            my_error_log("Query returned no results", 5, 16);
        } else {
            $result = array();
            while ($one_row = $mysqlQueryResult->fetch_assoc()) {
                if ($justOneRow) {
                    $mysqlQueryResult->close(); //free result set
                    return $one_row; //returns one dimensional array
                }
                $result[] = $one_row;
            }
        }
        if ($mysqlQueryResult != false) {
            $mysqlQueryResult->close(); //free result set
        }
        return $result; //returns two dimensional array
    }

}
