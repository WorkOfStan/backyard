<?php

namespace GodsDev\Backyard;

use GodsDev\Backyard\BackyardError;

/* * ****************************************************************************
 * Database (MySQL) FUNCTIONS
 *
 * TODO create TestBackyardMysqli.php
 * TODO compare admins vs user for throw new \Exception vs dieGraciously and if migrated to Exception:
 * use Psr\Log\LoggerInterface instad of GodsDev\Backyard\BackyardError
 *
 */

class BackyardMysqli extends \mysqli
{

    /**
     *
     * @var BackyardError
     */
    protected $BackyardError = null;

    /**
     * \mysqli wrapper with logger
     * Sets the connection charset to utf-8 and collation to utf8_general_ci
     *
     * @param string $host_port accepts either hostname (or IPv4) or hostname:port
     * To open as persistent use: $connection = new backyard_mysqli('p:' . $dbhost, $dbuser, $dbpass, $dbname);
     * @param string $user username
     * @param string $pass password
     * @param string $db database name
     * @param BackyardError $BackyardError PSR-3 logger
     *
     * @todo add IPv6 , e.g ::1 as $host_port
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
            $this->BackyardError->dieGraciously(
                '5013',
                "Connect Error ({$this->connect_errno}) {$this->connect_error} | {$tempErrorString}"
            );
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
     * @param int $errorLogOutput optional default=1 turn-off=0
     *   It is int in order to be compatible with
     *   parameter $resultmode (int) of method mysqli::query()
     * @return mixed \mysqli_result|false
     */
    public function query($sql, $errorLogOutput = 1)
    {
        $ERROR_LOG_OUTPUT = (bool) $errorLogOutput;
        if ($ERROR_LOG_OUTPUT) {
            $this->BackyardError->log(5, "Start of query {$sql}", array(11));
        }
        if (empty($sql) || !is_string($sql)) {
            if ($ERROR_LOG_OUTPUT) {
                $this->BackyardError->log(1, "No mysql_query_string set. End of query", array(11)); //debug
            }
            return false;
        }
        $result = parent::query($sql);
        if ($this->errno != 0) {
            if ($ERROR_LOG_OUTPUT) {
                $this->BackyardError->log(1, "{$this->errno} : {$this->error} /with query: {$sql}", array(11));
            }
        }
        if ($ERROR_LOG_OUTPUT) {
            $this->BackyardError->log(6, "End of query {$sql}", array(11));
        }
        return $result;
    }

    /**
     * Make a MySQL query and if the result is non empty,
     * transforms the query result into a one or two dimensional array.
     *
     * temporary note: Replaces customMySQLQuery()
     *
     * @param string $sql
     * @param boolean $justOneRow (default = false)
     * @return mixed
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
    public function nextIncrement($table, $metricDimension, $primaryDimension = '', $primaryDimensionValue = 0)
    {
        $result = 1; // default value
        $query = "SELECT `{$metricDimension}` FROM `{$table}` "
            . (((bool) $primaryDimension && $metricDimension != $primaryDimension) // conditional dimension: WHERE
            ? ("WHERE `{$primaryDimension}` =" . (int) $primaryDimensionValue . " ") : (""))
            . " ORDER BY `{$metricDimension}` DESC LIMIT 0 , 1;";
        $mysql_query_array = $this->queryArray($query, true);
        if ($mysql_query_array) {
            $result += (int) $mysql_query_array["$metricDimension"];
        }
        return $result;
    }
}
