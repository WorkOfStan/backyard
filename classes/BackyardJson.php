<?php

namespace WorkOfStan\Backyard;

use Psr\Log\LoggerInterface;

/**
 * JSON FUNCTIONS
 */
class BackyardJson
{
    /** @var BackyardHttp */
    protected $backyardHttp;
    /** @var LoggerInterface */
    protected $logger;

    /**
     *
     * @param LoggerInterface $logger
     * @param BackyardHttp $backyardHttp
     */
    public function __construct(LoggerInterface $logger, BackyardHttp $backyardHttp)
    {
        $this->logger = $logger;
        $this->backyardHttp = $backyardHttp;
    }

    /**
     * @desc Minify JSON and if JSON is not valid it reports it
     * in the my_error_log and outputs a preformatted error500 json string
     *
     * @param string $jsonInput
     * @param int $logLevel - optional - default is not to be verbose
     * @return string
     */
    public function minifyJSON($jsonInput, $logLevel = 5)
    {
        $jsonOutput = json_encode(json_decode($jsonInput)); // optimalizace pro výstup
        if ($jsonOutput == 'null' || $jsonOutput === false || !is_string($jsonOutput)) {
            $this->logger->log(1, "ERROR IN JSON: {$jsonInput}", array(16));
            return '{"status": "500", "error": "Internal error"}'; //error output
        }
        $this->logger->log($logLevel, "JSON input: {$jsonInput}", array(16));
        $this->logger->log($logLevel, "JSON output: {$jsonOutput}", array(16));
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
    public function outputJSON($jsonString, $exitAfterOutput = false, $logLevel = 5)
    {
        header("Content-type: application/json");
        $minifiedJson = $this->minifyJSON($jsonString, $logLevel);
        if ($minifiedJson === '{"status": "500", "error": "Internal error"}') { //error output from minifyJSON
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
     * @param   int     $depth   User specified recursion depth. (>=5.3)
     * @param   int     $options Bitmask of JSON decode options. (>=5.4)
     * @return  array<mixed>|false array or false is returned if the json cannot be decoded
     *                              or if the encoded data is deeper than the recursion limit.
     */
    public function jsonCleanDecode($json2decode, $assoc = false, $depth = 512, $options = 0)
    {
        // Ensure $depth is a positive integer
        $depth = max(1, (int) $depth);

        // search and remove comments like /* */ and //
        $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json2decode);
        if (is_null($json)) {
            $this->logger->log(5, "Invalid JSON: " . $json2decode);
            return false; // invalid JSON
        }

        if (version_compare((string) phpversion(), '5.4.0', '>=')) {
            $decodedJson = json_decode($json, $assoc, $depth, $options);
        } elseif (version_compare((string) phpversion(), '5.3.0', '>=')) {
            $decodedJson = json_decode($json, $assoc, $depth);
        } else {
            $decodedJson = json_decode($json, $assoc);
        }

        // Ensure the returned value is an array or false
        if (is_array($decodedJson) || $decodedJson === false) {
            return $decodedJson;
        }

        $this->logger->log(5, "Invalid JSON: " . $json2decode);
        return false; // invalid JSON
    }

    /**
     * @desc Retrieves JSON from $url and puts it into associative array
     *
     * @param string $url
     * @param string $useragent OPTIONAL
     * @param int $timeout OPTIONAL
     * @param string|false $customHeaders OPTIONAL
     * @param array<mixed> $postArray OPTIONAL
     * @return array<mixed>|false array if cURL($url) returns JSON else false
     */
    public function getJsonAsArray(
        $url,
        $useragent = 'PHP/cURL',
        $timeout = 5,
        $customHeaders = false,
        array $postArray = array()
    ) {
        $result = $this->backyardHttp->getData($url, $useragent, $timeout, $customHeaders, $postArray);
        $json = $result['message_body'];
        if (!$json || !is_string($json)) {
            $this->logger->log(2, "No valid content on {$url}");
            return false;
        }
        $jsonArray = $this->jsonCleanDecode($json, true);
        if (!$jsonArray) {
            $this->logger->log(2, "Trouble with decoding JSON from {$url}");
            return false;
        }
        return $jsonArray;
    }
}
