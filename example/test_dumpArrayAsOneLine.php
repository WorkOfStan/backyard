<?php

require_once '../src/backyard_array.php';

$myArray = array ('a', 'b', 'c');
echo backyard_dumpArrayAsOneLine($myArray);
echo "<hr/>";

$myArray = array ('a' => 'alfa', 'b' => 'fdsfs', 'c' => 3.14);
echo backyard_dumpArrayAsOneLine($myArray);
echo "<hr/>";

$myArray = array(
    "foo" => "bar",
    42    => 24,
    "multi" => array(
         "dimensional" => array(
             "array" => "foo"
         )
    )
);
echo backyard_dumpArrayAsOneLine($myArray);
echo "<hr/>";

$myArray = array ();
echo backyard_dumpArrayAsOneLine($myArray);
echo "<hr/>";

$myArray = array(         10, // key = 0
                    5    =>  6,
                    3    =>  7, 
                    'a'  =>  4,
                            11, // key = 6 (maximum of integer-indices was 5)
                    '8'  =>  2, // key = 8 (integer!)
                    '02' => 77, // key = '02'
                    0    => 12  // the value 10 will be overwritten by 12
                  );
echo backyard_dumpArrayAsOneLine($myArray);
echo "<hr/>";

