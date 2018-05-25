<?php

namespace GodsDev\Backyard;

use Psr\Log\LoggerInterface;
use GodsDev\Backyard\BackyardHttp;

/**
 * JSON FUNCTIONS
 */
class BackyardJson {

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $BackyardError = null;
    
    /**
     *
     * @var \GodsDev\Backyard\BackyardHttp
     */
    protected $BackyardHttp = null;

    /**
     * 
     * @param LoggerInterface $backyardError
     * @param BackyardHttp $backyardHttp
     */
    public function __construct(LoggerInterface $backyardError, BackyardHttp $backyardHttp) {
        $this->BackyardError = $backyardError;
        $this->BackyardHttp = $backyardHttp;
    }

    /**
     * @desc Minify JSON and if JSON is not valid it report it in the my_error_log and outputs a preformatted error500 json string
     * 
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
        if($minifiedJson === '{"status": "500", "error": "Internal error"}'){ //error output from minifyJSON
            header("HTTP/1.1 500 Internal Server Error", true, 500);
        }
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
     * @param   bool    $assoc   When true, returned objects will be converted into associative arrays. 
     * @param   integer $depth   User specified recursion depth. (>=5.3) 
     * @param   integer $options Bitmask of JSON decode options. (>=5.4) 
     * @return  array or null is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit. 
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
     * 
     * @param string $url
     * @return array|bool array if cURL($url) returns JSON else false
     * 
     */
    public function getJsonAsArray($url, $useragent = 'PHP/cURL', $timeout = 5, $customHeaders = false, $postArray = array()) {
        $result = $this->BackyardHttp->getData($url, $useragent, $timeout, $customHeaders, $postArray);
        $json = $result['message_body'];
        if (!$json) {
            $this->BackyardError->log(2, "No content on {$url}");
            return false;
        }
        $jsonArray = $this->jsonCleanDecode($json, true);
        if (!$jsonArray) {
            $this->BackyardError->log(2, "Trouble with decoding JSON from {$url}");
            return false;
        }
        return $jsonArray;
    }

}
