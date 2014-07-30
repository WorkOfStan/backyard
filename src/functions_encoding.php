<?php

/* * ****************************************************************************
 * Encoding FUNCTIONS
 */

/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $text
 * @return type
 */
function fix_xml($text) {
    $text = str_replace("&", "&amp;", $text);
    $text = str_replace("<", "&lt;", $text);
    $text = str_replace(">", "&gt;", $text);
    $text = str_replace("\"", "&quot;", $text);

    return $text;
}

/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $text
 * @return type
 */
function fix_html_input($text) {
    $text = str_replace("\"", "&quot;", $text);
    $text = str_replace("<", "&lt;", $text);
    $text = str_replace(">", "&gt;", $text);
    return $text;
}

/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $string
 * @return type
 */
function encode_wml_entity($string) {
// encode &amp; first. pouzito v administraci, ale proc preklada jen '&' ??
    $string = str_replace("&", "&amp;", $string);
    $output = "";
// see e.g. http://www1.tip.nl/~t876506/utf8tbl.html for details how the hex utf-8 translation works..
    for ($i = 0; $i < strlen($string); $i++) {
        $char = substr($string, $i, 1);
///my_error_log($char);
        if (ord($char[0]) > 127) {
///my_error_log(">127");
            $utf = iconv("iso-8859-2", "utf-8", $char);
            $utf_char = dechex((ord($utf[0]) - 192) * 64 + ord($utf[1]) - 128);
///my_error_log($utf_char);
            if (strlen($utf_char) < 4) {
                $utf_char = "0" . $utf_char;
            }
            if (strlen($utf_char) < 4) {
                $utf_char = "0" . $utf_char;
            }
            $output .= "&#x$utf_char;";
        } else {
///my_error_log("ascii");
            $output .= $char;
        }
    }
//error_log("encoding $string to $output");
    return $output;
}

/**
 * internal function for decode_wml_entity()
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $dec
 * @return type
 */
function unichr($dec) {
    if ($dec < 128) {
        $utf = chr($dec);
    } else if ($dec < 2048) {
        $utf = chr(192 + (($dec - ($dec % 64)) / 64));
        $utf .= chr(128 + ($dec % 64));
    } else {
        $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
        $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
        $utf .= chr(128 + ($dec % 64));
    }
    return $utf;
}

/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $string
 * @return type
 */
function decode_wml_entity($string) {
    return (preg_replace('/&#x([a-f0-9]+);/mei', "unichr(0x\\1)", $string));
}

/**
 * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
 * 
 * @param type $string
 * @return type
 */
function strip_diacritics($string) {
    $trans = array("á" => "a", "ä" => "a", "č" => "c", "ď" => "d", "é" => "e", "ě" => "e", "ë" => "e", "í" => "i", "&#239;" => "i", "ň" => "n", "ó" => "o", "ö" => "o", "ř" => "r", "š" => "s", "ť" => "t", "ú" => "u", "ů" => "u", "ü" => "u", "ý" => "y", "&#255;" => "y", "ž" => "z", "Á" => "A", "Ä" => "A", "Č" => "C", "Ď" => "D", "É" => "E", "Ě" => "E", "Ë" => "E", "Í" => "I", "&#207;" => "I", "Ň" => "N", "Ó" => "O", "Ö" => "O", "Ř" => "R", "Š" => "S", "Ť" => "T", "Ú" => "U", "Ů" => "U", "Ü" => "U", "Ý" => "Y", "&#376;" => "Y", "Ž" => "Z");
    // return strtr(iconv("utf-8", "iso-8859-2", $string),  $trans); 
    return strtr($string, $trans);
}
