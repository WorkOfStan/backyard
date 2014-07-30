<?php
//backyard 2 compliant
if (!function_exists('my_error_log')) {
    require_once dirname(__FILE__) . '/backyard_my_error_log_dummy.php';
}

/* * ****************************************************************************
 * JSON FUNCTIONS
 */

/**
 * @desc Minify JSON and if JSON is not valid it report it in the my_error_log and outputs a preformatted error500 json string
 * @param type $jsonInput
 * @param type $logLevel - optional - default is not to be verbose
 * @return string
 */
function backyard_minifyJSON($jsonInput, $logLevel = 5) {
    $jsonOutput = json_encode(json_decode($jsonInput)); //optimalizace pro výstup
    if ($jsonOutput == 'null') {
        my_error_log("ERROR IN JSON: {$jsonInput}", 1, 16);
        $jsonOutput = '{"status": "500", "error": "Internal error"}'; //error output
    } else {
        my_error_log("JSON input: {$jsonInput}", $logLevel, 16);
        my_error_log("JSON output: {$jsonOutput}", $logLevel, 16);
    }
    return $jsonOutput;
}

/**
 * @desc Output JSON
 * @param string $jsonString to be minified
 * @param bool $exitAfterOutput  - optional - default is to let the script continue
 * @param int $logLevel - optional - default is not to be verbose
 */
function backyard_outputJSON($jsonString, $exitAfterOutput = false, $logLevel = 5) {
    header("Content-type: application/json");
    echo(backyard_MinifyJSON($jsonString, $logLevel)); //jako json
    if ($exitAfterOutput) {
        exit;
    }
}

/**
 * Clean comments of json content and decode it with json_decode(). 
 * Work like the original php json_decode() function with the same params 
 * http://www.php.net/manual/en/function.json-decode.php#112735
 * 
 * @param   string  $json2decode    The json string being decoded 
 * @param   bool    $assoc   When TRUE, returned objects will be converted into associative arrays. 
 * @param   integer $depth   User specified recursion depth. (>=5.3) 
 * @param   integer $options Bitmask of JSON decode options. (>=5.4) 
 * @return  array or NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit. 
 */
function backyard_jsonCleanDecode($json2decode, $assoc = false, $depth = 512, $options = 0) {
    // search and remove comments like /* */ and //
    $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json2decode);

    if (version_compare(phpversion(), '5.4.0', '>=')) {
        $json = json_decode($json, $assoc, $depth, $options);
    } elseif (version_compare(phpversion(), '5.3.0', '>=')) {
        $json = json_decode($json, $assoc, $depth);
    } else {
        $json = json_decode($json, $assoc);
    }
    if (is_null($json)) {
        my_error_log("Invalid JSON: " . $json2decode, 5);
        return false; //invalid JSON
    }
    return $json;
}

/**
 * @desc Retrieves JSON from $url and puts it into associative array
 * @param string $url
 * @return array|bool array if cURL($url) returns JSON else false
 */
function backyard_getJsonAsArray($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $json = curl_exec($ch);
    if (!$json) {
        error_log("Curl error: " . curl_error($ch) . " on {$url}");
        return false;
    }
    curl_close($ch);
    $jsonArray = backyard_jsonCleanDecode($json, true);
    if (!$jsonArray) {
        //error_log("Trouble with decoding JSON from {$url}");
        return false;
    }
    return $jsonArray;
}
