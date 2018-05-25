<?php

namespace GodsDev\Backyard;

use GodsDev\Backyard\BackyardError; // not use Psr\Log\LoggerInterface; because method dieGraciously is used

//@TODO create TestBackyardMysqli.php

/* * ****************************************************************************
 * Database (MySQL) FUNCTIONS
 */

/**
 * __construct
 * @param string $host_port accepts either hostname (or IPv4) or hostname:port
 * @param string $user
 * @param string $pass
 * @param string $db
 * @param BackyardError $BackyardError
 * 
 * To open as persistent use: $connection = new backyard_mysqli('p:' . $dbhost, $dbuser, $dbpass, $dbname);
 * class backyard_mysqli based on my_mysqli from https://github.com/GodsDev/repo1/blob/58fa783d4c7128579b729465dc36b45568f9ddb1/myreport/src/mreport_functions.php as of 120914
 * Sets the connection charset to utf-8 and collation to utf8_general_ci
 * @todo add IPv6 , e.g ::1 as $host_port
 */
class BackyardMysqli extends \mysqli
{

    protected $BackyardError = null;

    /**
     * 
     * @param string $host_port accepts either hostname (or IPv4) or hostname:port
     * @param string $user
     * @param string $pass
     * @param string $db
     * @param BackyardError $BackyardError
     */
    public function __construct($host_port, $user, $pass, $db, BackyardError $BackyardError)
    {
        //error_log("debug: " . __CLASS__ . ' ' . __METHOD__);
        $this->BackyardError = $BackyardError;

        $temp = explode(":", $host_port);

        if ($temp[0] === 'p') {
            $host = 'p:' . (string) $temp[1];
            if (isset($temp[2])) {
                $port = (int) $temp[2];
            }
        } else {
            $host = (string) $temp[0];
            if (isset($temp[1])) {
                $port = (int) $temp[1];
            }
        }

        if (isset($port)) {
            if ($host === 'localhost') {
                $host = "127.0.0.1"; //localhost uses just the default port
            }
            $tempErrorString = "Connecting to $host, $user, pass, $db, $port";
            $this->BackyardError->log(5, $tempErrorString); //debug
            parent::__construct($host, $user, $pass, $db, $port);
        } else {
            $tempErrorString = "Connecting to $host, $user, pass, $db";
            $this->BackyardError->log(5, $tempErrorString); //debug
            parent::__construct($host, $user, $pass, $db);
        }

        if ($this->connect_error) {
            $this->BackyardError->dieGraciously('5013', "Connect Error ({$this->connect_errno}) {$this->connect_error} | {$tempErrorString}");
        }

        //change character set to utf8
        if (!$this->set_charset("utf8")) {
            $this->BackyardError->log(2, sprintf("Error loading character set utf8: %s\n", $this->error));
        }
    }

    /**
     * Query method
     * if everything is OK, return the mysqli_result object
     * that is returned from parent query method
     * 
     * 120914, inspired by http://www.blrf.net/blog/223/code/php/extending-mysqli-class-with-example/
     * 
     * @param string $sql SQL to execute
     * @return mysqli_result Object|false
     * @throws DBQueryException
     */
    public function query($sql, $ERROR_LOG_OUTPUT = true)
    {
        //111010 - function is called even before error_log is initialized, therefore it is necessary to mute my_error_log, hence call make_mysql_query($sql,false); 160625 - is it still necessary?
        if ($ERROR_LOG_OUTPUT) {
            $this->BackyardError->log(5, "Start of query {$sql}", array(11));
        }
        if (empty($sql) || !is_string($sql)) {
            if ($ERROR_LOG_OUTPUT) {
                $this->BackyardError->log(1, "No mysql_query_string set. End of query", array(11)); //debug
                //my_error_log("End of query", 6, 11);                
            }
            return false;
        }
        //if ($sql != "") {
        // here, we could log the query to sql.log file
        // note that, no error check is being made for this file
        //file_put_contents('/tmp/sql.log', $sql . "\n", FILE_APPEND);
        //if ($ERROR_LOG_OUTPUT) {
        //    my_error_log($sql, 5, 11);
        //}
        $result = parent::query($sql);
        if ($this->errno != 0) {
            if ($ERROR_LOG_OUTPUT) {
                $this->BackyardError->log(1, "{$this->errno} : {$this->error} /with query: {$sql}", array(11));
            }
        }
        //}
        if ($ERROR_LOG_OUTPUT) {
            $this->BackyardError->log(6, "End of query {$sql}", array(11));
        }
        return $result;
    }

    /**
     * Make a MySQL query and if the result is non empty, transforms the query result into a one or two dimensional array.
     * temporary note: Replaces customMySQLQuery()
     * @param string $sql
     * @param boolean $justOneRow (default = false)
     * @return 
     *      false - if no results
     *      one dimensional array - if $justOneRow == true
     *      two dimensional array - if $justOneRow == false
     */
    public function queryArray($sql, $justOneRow = false)
    {
        $result = false;
        $mysqlQueryResult = $this->query($sql); //$ERROR_LOG_OUTPUT = true by default
        //transforming the query result into an array
        if ($mysqlQueryResult == false || $mysqlQueryResult->num_rows == 0) {
            $this->BackyardError->log(5, "Query returned no results", array(16));
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
        return $result; //returns two dimensional array or false
    }

    /**
     * Find the next available id within selected dimension.
     * It may be conditioned by an integer value of another dimension
     * (Replacement for int function findFirstAvailableIdInRelevantTable($table, $ownerId, $relevantMetric))
     * 
     * @param string $table
     * @param string $metricDimension
     * @param string $primaryDimension [optional]
     * @param int $primaryDimensionValue [optional]
     * @return int
     */
    public function nextIncrement($table, $metricDimension, $primaryDimension = false, $primaryDimensionValue = false)
    {
        $result = 1; //default value
        $query = "SELECT `{$metricDimension}` FROM `{$table}` "
            . (($primaryDimension && $metricDimension != $primaryDimension) ? ("WHERE  `{$primaryDimension}` =" . (int) $primaryDimensionValue . " ") : (""))
            . " ORDER BY `{$metricDimension}` DESC LIMIT 0 , 1;";
        $mysql_query_array = $this->queryArray($query, true);
        if ($mysql_query_array) {
            $result += (int) $mysql_query_array["$metricDimension"];
        }
        return $result;
    }

}
