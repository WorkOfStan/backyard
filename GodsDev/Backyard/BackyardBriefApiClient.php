<?php

namespace GodsDev\Backyard;

/**
 * Very simple JSON RESTful API client
 * It just sends (by HTTP POST) JSON and returns what is to be returned with few optional decorators and error logging.
 * 
 * @todo Probably should be refactored using backyard json and http
 *
 * @author rejth
 */
class BackyardBriefApiClient {

    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger;

    /**
     *
     * @var string
     */
    private $apiUrl;

    /**
     *
     * @var string or null
     */
    private $appLogFolder;

    /**
     * 
     * @param string $apiUrl
     * @param mixed $appLogFolder OPTIONAL string without trailing / or if null then the applogs will not be saved at all
     * @param \Psr\Log\LoggerInterface $logger OPTIONAL but really recommanded
     */
    public function __construct($apiUrl, $appLogFolder = null, \Psr\Log\LoggerInterface $logger = null) {
        //error_log("debug: " . __CLASS__ . ' ' . __METHOD__);
        $this->logger = $logger;
        $this->apiUrl = $apiUrl;
        $this->appLogFolder = $appLogFolder;
    }

    /**
     * Send a JSON to the API and returns whatever is to return
     * 
     * @param string $json
     * @return mixed <b>TRUE</b> on success or <b>FALSE</b> on failure. However, if the <b>CURLOPT_RETURNTRANSFER</b>
     * option is set, it will return
     * the result on success, <b>FALSE</b> on failure.
     */
    public function sendJsonLoad($json) {
        $communicationId = uniqid(date("Y-m-d-His_"));
        $this->logCommunication($json, 'call', $communicationId);
        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                //'Authorization: '.$authToken,
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => $json, //json_encode($postData)
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false, //accepts also private SSL certificates //@todo it could be possible to try without that option and if it fails, it may try with this option and inform about it            
        ));
        $result = curl_exec($ch);
        if ($result) {
            $this->logCommunication($result, 'resp', $communicationId);
        } else {
            $this->logger->error("Curl failed with (" . curl_errno($ch) . ") " . curl_error($ch));
        }
        return $result;
    }

    /**
     * 
     * @param string $message
     * @param string $filePrefix
     * @param string $communicationId
     * @return boolean
     */
    private function logCommunication($message, $filePrefix, $communicationId) {
        if (!$this->appLogFolder) {
            return false;
        }
        if (!$communicationId) {
            $communicationId = uniqid(date("Y-m-d-His_"));
        }
        return error_log($message, 3, "{$this->appLogFolder}/{$filePrefix}-{$communicationId}.json");
    }

    /**
     * Sends JSON and return array decoded from the received JSON response
     * 
     * @param string $json
     * @return array
     */
    public function getJsonArray($json) {
        $response = $this->sendJsonLoad($json);
        $result = json_decode($response, true);
        if (!$result) {
            $this->logger->error("json decode failed for " . substr($response, 0, 100) . " that resulted from " . substr($json, 0, 100));
        }
        return $result;
    }

    /**
     * Translates array to JSON, send it to API and return array decoded from the received JSON response
     * 
     * @param array $arr
     * @return array
     */
    public function getArrayArray(array $arr) {
        return $this->getJsonArray(json_encode($arr));
    }

}
