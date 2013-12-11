<?php

if (!function_exists('my_error_log')) {
    include_once 'functions_my_error_log_dummy.php';
}

/******************************************************************************
 * ARRAY FUNCTIONS
 */


/**
 * this function allows wildcards in the array to be searched
 * 
 * http://www.php.net/manual/en/function.in-array.php#67159
 * //I needed a version of in_array() that supports wildcards in the haystack. Here it is:
 * $haystack = array('*krapplack.de');
 * $needle = 'www.krapplack.de';
 * echo my_inArray($needle, $haystack); # outputs "true"
 * //Unfortunately, fnmatch() is not available on Windows or other non-POSIX compliant systems.
 * 
 * @param string $needle
 * @param array $haystack
 * @return boolean
 */
function inArrayWildcards($needle, $haystack) {
    foreach ($haystack as $value) {
        if (true === fnmatch($value, $needle)) {
            return true;
        }
    }
    return false;
}


/**
 * 
 * @param array $myArray
 * @param string $columnName
 * @return type
 */
function GetOneColumnFromArray($myArray, $columnName) {
    if (!is_array($myArray))
        return array(); //konzistentnější než false
    $result = array();
    foreach ($myArray as $key => $row) {
        $result[$key] = $row[$columnName];
    }
    return $result;
}

/**
 * 
 * @param array $myArray
 * @return type
 */
function DumpArrayAsOneLine($myArray) {
    //@TODO - ozkoušeet, aby tak váženě fungovalo
    return (preg_replace('/\n/', ' ', preg_replace('/\r/', ' ', preg_replace('/\s\s+/', ' ', print_r($myArray, true))
                    ))
            );
}

/**
 * Returns first row with exact match //@TODO 4 - přidat parametr na vrácení všech rows s exact match
 * 
 * @param string $searchedValue
 * @param array $searchedArray
 * @param string $columnName
 * @return mixed
 */
function ArrayVlookup($searchedValue, $searchedArray, $columnName) {
    //debug//echo "searching $searchedValue in the column $columnName in the array ".print_r($searchedArray,true);exit;
    if (!is_array($searchedArray)) {
        my_error_log("ArrayVlookup: array parameter is not an array", 2);
    } else {
        foreach ($searchedArray as $key => $row) {
            if (isset($row[$columnName])) {
                if ($row[$columnName] == $searchedValue)
                    return $row;
            } else {
                my_error_log("ArrayVlookup: $columnName not in " . print_r($row, true), 3);
            }
        }
    }
    return false;
}
