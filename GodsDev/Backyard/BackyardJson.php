<?php

namespace GodsDev\Backyard;
//@todo SHOULDN'T IT BE GodsDev\Backyard\Json ?


class BackyardJson {

    protected $BackyardError = NULL;

    public function __construct(
    BackyardError $BackyardError) {
        //error_log("debug: " . __CLASS__ . ' ' . __METHOD__);
        $this->BackyardError = $BackyardError;
    }

    /* * ****************************************************************************
 * JSON FUNCTIONS
 */

/**
 * @desc Minify JSON and if JSON is not valid it report it in the my_error_log and outputs a preformatted error500 json string
 * @param string $jsonInput
 * @param int $logLevel - optional - default is not to be verbose
 * @return string
 */
public function minifyJSON($jsonInput, $logLevel = 5) {
    $jsonOutput = json_encode(json_decode($jsonInput)); //optimalizace pro výstup
    if ($jsonOutput == 'null') {
        $this->BackyardError->log(1, "ERROR IN JSON: {$jsonInput}", array(16));
        $jsonOutput = '{"status": "500", "error": "Internal error"}'; //error output
    } else {
        $this->BackyardError->log($logLevel, "JSON input: {$jsonInput}", array(16));
        $this->BackyardError->log($logLevel, "JSON output: {$jsonOutput}", array(16));
    }
    return $jsonOutput;
}

/**
 * @desc Output JSON
 * @param string $jsonString to be minified
 * @param bool $exitAfterOutput  - optional - default is to let the script continue
 * @param int $logLevel - optional - default is not to be verbose
 * @return string minified JSON (works only if $exitAfterOuput === false)
 * 
 * @todo - add posibility to return HTTP status codes other than 200  
 */
public function outputJSON($jsonString, $exitAfterOutput = false, $logLevel = 5) {
    header("Content-type: application/json");
    $minifiedJson = $this->minifyJSON($jsonString, $logLevel);
    echo($minifiedJson);
    if ($exitAfterOutput) {
        exit;
    }
    return $minifiedJson;
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
public function jsonCleanDecode($json2decode, $assoc = false, $depth = 512, $options = 0) {
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
        $this->BackyardError->log(5, "Invalid JSON: " . $json2decode);
        return false; //invalid JSON
    }
    return $json;
}

/**
 * @desc Retrieves JSON from $url and puts it into associative array
 * @param string $url
 * @return array|bool array if cURL($url) returns JSON else false
 * 
 * 
 * @todo - use BackyardHttp
 */
public function getJsonAsArray($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $json = curl_exec($ch);
    if (!$json) {
        $this->BackyardError->log(2, "Curl error: " . curl_error($ch) . " on {$url}");
        return false;
    }
    curl_close($ch);
    $jsonArray = $this->jsonCleanDecode($json, true);
    if (!$jsonArray) {
        $this->BackyardError->log(2, "Trouble with decoding JSON from {$url}");
        return false;
    }
    return $jsonArray;
}
}