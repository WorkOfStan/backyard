<?php

namespace WorkOfStan\Backyard;

use Psr\Log\LoggerInterface;

/**
 * ARRAY FUNCTIONS
 */
class BackyardArray
{
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Note http://php.net/manual/en/function.array-key-exists.php#107786
     * If you want to take the performance advantage of isset() while keeping the null element correctly detected, use:

      if (isset(..) || array_key_exists(...))
      {
      ...
      }
     *
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
     * @param array<string> $haystack
     * @return bool
     */
    public function inArrayWildcards($needle, array $haystack)
    {
        foreach ($haystack as $value) {
            if (true === fnmatch($value, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns array named $columnName from $myArray
     * Ignores rows where the field $columnName is not set
     *
     * @param array<array<mixed>> $myArray at least two-dimensional
     * @param string $columnName
     * @param bool $columnAlwaysExpected default false; true => function logs the missing column in a row as an error
     * @return array<mixed>
     */
    public function getOneColumnFromArray(array $myArray, $columnName, $columnAlwaysExpected = false)
    {
        if (!is_array($myArray)) {
            return array(); // empty array more consistent than false
        }
        $result = array();
        foreach ($myArray as $key => $row) {
            if (is_array($row) && (isset($row[$columnName]) || array_key_exists($columnName, $row))) {
                $result[$key] = $row[$columnName];
            } elseif ($columnAlwaysExpected) {
                $this->logger->warning("getOneColumnFromArray: {$columnName} not in " . print_r($row, true));
            }
        }
        return $result;
    }

    /**
     * Returns array $myArray without column named in $columnName
     *
     * @param array<array<mixed>> $myArray
     * @param string $columnName
     * @return array<array<mixed>>
     */
    public function removeOneColumnFromArray(array $myArray, $columnName)
    {
        if (!is_array($myArray)) {
            return array(); //empty array more consistent than false
        }
        $result = array();
        foreach ($myArray as $key => $row) {
            foreach ($row as $key2 => $row2) {
                if ($key2 != $columnName) {
                    $result[$key][$key2] = $row[$key2];
                }
            }
        }
        return $result;

        //@todo measure if the following pattern wouldn't be faster while having exactly the same results
//        foreach($myArray as $k => $v) {
//            unset($myArray[$k][$columnName]);
//        }
    }

    /**
     * Return $myArray as a one-line string
     *
     * @param array<mixed> $myArray
     * @return string
     */
    public function dumpArrayAsOneLine(array $myArray)
    {
        return (string) preg_replace(
            '/\n/',
            ' ',
            (string) preg_replace(
                '/\r/',
                ' ',
                (string) preg_replace(
                    '/\s\s+/',
                    ' ',
                    print_r($myArray, true)
                )
            )
        );
    }

    /**
     * Returns first row with exact match //@TODO 4 - přidat parametr na vrácení všech rows s exact match
     * Useful for at least 2-dimensional arrays
     *
     * @param mixed $searchedValue
     * @param array<array<mixed>> $searchedArray
     * @param string $columnName
     * @param bool $allExactMatches - default false; if true function returns array with all exact matches
     * @param bool $columnAlwaysExpected - default true; false: the missing column in a row isn't logged as an error
     * @return array<mixed>|false (array if found, false otherwise)
     */
    public function arrayVlookup(
        $searchedValue,
        array $searchedArray,
        $columnName,
        $allExactMatches = false,
        $columnAlwaysExpected = true
    ) {
        if (!is_array($searchedArray)) {
            $this->logger->error("ArrayVlookup: second parameter is not an array");
            return false;
        }

        $allMatchingRows = array(); // used only if $allExactMatches === true

        foreach ($searchedArray as $key => $row) {
            if (isset($row[$columnName]) || array_key_exists($columnName, $row)) {
                if ($row[$columnName] == $searchedValue) {
                    if ($allExactMatches) {
                        $allMatchingRows[$key] = $row;
                    } else {
                        return $row;
                    }
                }
            } elseif ($columnAlwaysExpected) {
                $this->logger->warning("ArrayVlookup: {$columnName} not in " . print_r($row, true));
            }
        }
        return $allExactMatches ? $allMatchingRows : false;
    }

    /**
     * http://www.codeproject.com/Questions/780780/PHP-Finding-differences-in-two-multidimensional-ar
      [NOTE BY danbrown AT php DOT net: The array_diff_assoc_recursive function is a
      combination of efforts from previous notes deleted.
      Contributors included (Michael Johnson), (jochem AT iamjochem DAWT com),
      (sc1n AT yahoo DOT com), and (anders DOT carlsson AT mds DOT mdh DOT se).]
     *
     * @param array<mixed> $array1
     * @param array<mixed> $array2
     * @return array<mixed>|int (array|0)
     */
    public function arrayDiffAssocRecursive(array $array1, array $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!(isset($array2[$key]) || array_key_exists($key, $array2))) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->arrayDiffAssocRecursive($value, $array2[$key]);
                    if ($new_diff != false) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!(isset($array2[$key]) || array_key_exists($key, $array2)) || $array2[$key] != $value) {
                $difference[$key] = $value;
            }
        }
        return isset($difference) ? $difference : 0;
    }
}
