<?php
//backyard 2 compliant
if (!function_exists('my_error_log')) {
    require_once 'backyard_my_error_log_dummy.php';
}

/******************************************************************************
 * ARRAY FUNCTIONS
 */


/**
 * A version of in_array() that supports wildcards in the haystack.
 * 
 * http://www.php.net/manual/en/function.in-array.php#67159
 * //Unfortunately, fnmatch() is not available on Windows or other non-POSIX compliant systems.
 * 
 * Example:
 * $haystack = array('*krapplack.de');
 * $needle = 'www.krapplack.de';
 * echo backyard_inArrayWildcards($needle, $haystack); # outputs "true" * 
 * 
 * @param string $needle
 * @param array $haystack
 * @return boolean
 */
function backyard_inArrayWildcards($needle, $haystack) {
    foreach ($haystack as $value) {
        if (true === fnmatch($value, $needle)) {
            return true;
        }
    }
    return false;
}


/**
 * Returns array named $columnName from $myArray
 * @param array $myArray
 * @param string $columnName
 * @return array
 */
function backyard_getOneColumnFromArray($myArray, $columnName) {
    if (!is_array($myArray)){
        return array(); //empty array more consistant than false
    }
    $result = array();
    foreach ($myArray as $key => $row) {
        $result[$key] = $row[$columnName];
    }
    return $result;
}

/**
 * Returns array $myArray without column named in $columnName
 * @param array $myArray
 * @param string $columnName
 * @return array
 */
function backyard_removeOneColumnFromArray($myArray, $columnName) {
    if (!is_array($myArray)){
        return array(); //empty array more consistant than false
    }
    $result = array();
    foreach ($myArray as $key => $row) {
        foreach ($row as $key2 => $row2){
            if($key2 != $columnName){
                $result[$key][$key2] = $row[$key2];
            }
        }
    }
    return $result;
}


/**
 * Return $myArray as a one-line string
 * @param array $myArray
 * @return string
 */
function backyard_dumpArrayAsOneLine($myArray) {
    return (
            preg_replace(
                    '/\n/', ' ', preg_replace(
                            '/\r/', ' ', preg_replace(
                                    '/\s\s+/', ' ', print_r($myArray, true)
                                    )
                            )
                    )
            );
}

/**
 * Returns first row with exact match //@TODO 4 - přidat parametr na vrácení všech rows s exact match
 * 
 * @param string $searchedValue
 * @param array $searchedArray
 * @param string $columnName
 * @param bool $allExactMatches - default false; if true function returns array with all exact matches
 * @return mixed (string if found, false otherwise)
 */
function backyard_arrayVlookup($searchedValue, $searchedArray, $columnName, $allExactMatches = false) {
    if (!is_array($searchedArray)) {
        my_error_log("ArrayVlookup: array parameter is not an array", 2);
        return false;
    }
    
    $allMatchingRows = array();//used only if $allExactMatches === true
    
    foreach ($searchedArray as $key => $row) {
        if (isset($row[$columnName])) {
            if ($row[$columnName] == $searchedValue){
                if($allExactMatches){
                    $allMatchingRows[$key] = $row;
                } else {
                    return $row;
                }
            }
        } else {
            my_error_log("ArrayVlookup: $columnName not in " . print_r($row, true), 3);
        }
    }
    return $allExactMatches ? $allMatchingRows : false;
}
