<?php

namespace WorkOfStan\Backyard;

use Psr\Log\LoggerInterface;
use RuntimeException;
use UnexpectedValueException;

/**
 * Very simple JSON RESTful API client
 * It just sends (by HTTP POST) JSON and returns what is to be returned with few optional decorators and error logging.
 *
 * @todo Probably should be refactored using backyard json and http
 *
 * @author rejth
 */
class BackyardBriefApiClient
{
    /** @var string */
    private $apiUrl;
    /** @var string|null */
    private $appLogFolder;
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * @param string $apiUrl
     * @param string|null $appLogFolder OPTIONAL string without trailing / or if null, the applogs will not be created
     * @param \Psr\Log\LoggerInterface|null $logger OPTIONAL but really recommended
     */
    public function __construct($apiUrl, $appLogFolder = null, ?LoggerInterface $logger = null)
    {
        //error_log("debug: " . __CLASS__ . ' ' . __METHOD__);
        $this->logger = is_null($logger) ? new \Psr\Log\NullLogger() : $logger;
        $this->apiUrl = $apiUrl;
        $this->appLogFolder = $appLogFolder;
    }

    /**
     * Each call returns string starting with timestamp
     * and ending with unique identifier based on the current time in microseconds.
     *
     * @return string
     */
    private function getCommunicationId()
    {
        return uniqid(date("Y-m-d-His_"));
    }

    /**
     * Send a JSON to the API and returns whatever is to return.
     *
     * @param string $json
     * @param string $httpVerb POST default, or PUT/DELETE/GET
     * @return mixed <b>TRUE</b> on success or <b>FALSE</b> on failure. However, if the <b>CURLOPT_RETURNTRANSFER</b>
     * option is set, it will return the result on success, <b>FALSE</b> on failure.
     */
    public function sendJsonLoad($json, $httpVerb = 'POST')
    {
        $communicationId = $this->getCommunicationId();
        $this->logCommunication($json, $httpVerb, $communicationId);
        $ch = curl_init($this->apiUrl);

        if ($ch === false) {
            $this->logger->error("Curl initialization failed.");
            return false;
        }

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_SSL_VERIFYPEER => false,
            //accepts also private SSL certificates
            //@todo try without that option and if it fails, it may try with this option and inform about it
            CURLOPT_SSL_VERIFYHOST => 0, // 81: MUST be 0 (instead of false) or 2 (instead of true)
        ));
        switch ($httpVerb) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
            // no break
            case 'GET':
            case 'DELETE':
                curl_setopt_array($ch, array(
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                ));
                if (in_array($httpVerb, array('GET', 'DELETE'))) {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpVerb);
                }
                break;
            case 'PUT':
                curl_setopt_array($ch, array(
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Content-Length: ' . (string) strlen($json)
                    ),
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                ));
                break;
            default:
                $this->logger->error("Unknown verb {$httpVerb}");
                return false;
        }
        $result = curl_exec($ch);
        if ($result !== false) {
            $this->logCommunication((string) $result, 'resp', $communicationId);
        } elseif (!is_null($this->logger)) {
            $this->logger->error("Curl failed with (" . curl_errno($ch) . ") " . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    /**
     *
     * @param string $message
     * @param string $filePrefix
     * @param string $communicationId
     * @return bool Returns <b><code>TRUE</code></b> on logging or <b><code>FALSE</code></b> on not logging.
     */
    private function logCommunication($message, $filePrefix, $communicationId)
    {
        if (!$this->appLogFolder) {
            return false;
        }
        return error_log(
            $message,
            3,
            "{$this->appLogFolder}/{$filePrefix}-"
            . ($communicationId ? $communicationId : $this->getCommunicationId()) . ".json"
        );
    }

    /**
     * Sends JSON and return array decoded from the received JSON response.
     *
     * @param string $json
     * @return array<mixed>
     * @throws \Exception
     */
    public function getJsonArray($json)
    {
        $response = $this->sendJsonLoad($json);
        $result = is_string($response) ? json_decode($response, true) : null;
        if ($result === null && !is_null($this->logger)) {
            $this->logger->error("json decode failed for "
                . substr((string) print_r($response, true), 0, 100)
                . " that resulted from " . substr($json, 0, 100));
        }
        if (is_null($result)) {
            return array();
        } elseif (!is_array($result)) {
            throw new UnexpectedValueException('$result must be of type array');
        }
        return $result;
    }

    /**
     * Translates array to JSON, send it to API and return array decoded from the received JSON response.
     *
     * @param array<mixed> $arr
     * @return array<mixed>
     * @throws \Exception
     */
    public function getArrayArray(array $arr)
    {
        $json = json_encode($arr);
        if ($json === false) {
            throw new RuntimeException('Json_encode of array failed.');
        }
        return $this->getJsonArray($json);
    }
}
