<?php

namespace GodsDev\Backyard;

use GodsDev\Backyard\BackyardError;

/* * ****************************************************************************
 * HTTP FUNCTIONS
 */

if (!function_exists('apache_request_headers')) {

    /**
     * array apache_request_headers ( void )
     * apache_request_headers replacement for nginx 
     * http://www.php.net/manual/en/function.getallheaders.php#99814
     * 
     * @return array
     */
    function apache_request_headers() {
        $out = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $out[$key] = $value;
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }

}

class BackyardHttp {

    protected $BackyardError = null;

    /**
     * 
     * @param BackyardError $BackyardError
     */
    public function __construct(BackyardError $BackyardError) {
        //error_log("debug: " . __CLASS__ . ' ' . __METHOD__);
        $this->BackyardError = $BackyardError;
        //@todo set $this->post etc from $_POST, $_GET and $_SERVER to make the functions below testable in isolation
    }

    /**
     * http://www.cyberciti.biz/faq/php-redirect/
     * PHP Redirect Code
     * void movePage($num,$url)
     * 
     * @staticvar array $http
     * @param int $num
     * @param string $url
     * @param bool $stopCodeExecution default true
     */
    public function movePage($num, $url, $stopCodeExecution = true) {
        static $http = array(
            100 => "HTTP/1.1 100 Continue",
            101 => "HTTP/1.1 101 Switching Protocols",
            200 => "HTTP/1.1 200 OK",
            201 => "HTTP/1.1 201 Created",
            202 => "HTTP/1.1 202 Accepted",
            203 => "HTTP/1.1 203 Non-Authoritative Information",
            204 => "HTTP/1.1 204 No Content",
            205 => "HTTP/1.1 205 Reset Content",
            206 => "HTTP/1.1 206 Partial Content",
            300 => "HTTP/1.1 300 Multiple Choices",
            301 => "HTTP/1.1 301 Moved Permanently",
            302 => "HTTP/1.1 302 Found",
            303 => "HTTP/1.1 303 See Other",
            304 => "HTTP/1.1 304 Not Modified",
            305 => "HTTP/1.1 305 Use Proxy",
            307 => "HTTP/1.1 307 Temporary Redirect",
            400 => "HTTP/1.1 400 Bad Request",
            401 => "HTTP/1.1 401 Unauthorized",
            402 => "HTTP/1.1 402 Payment Required",
            403 => "HTTP/1.1 403 Forbidden",
            404 => "HTTP/1.1 404 Not Found",
            405 => "HTTP/1.1 405 Method Not Allowed",
            406 => "HTTP/1.1 406 Not Acceptable",
            407 => "HTTP/1.1 407 Proxy Authentication Required",
            408 => "HTTP/1.1 408 Request Time-out",
            409 => "HTTP/1.1 409 Conflict",
            410 => "HTTP/1.1 410 Gone",
            411 => "HTTP/1.1 411 Length Required",
            412 => "HTTP/1.1 412 Precondition Failed",
            413 => "HTTP/1.1 413 Request Entity Too Large",
            414 => "HTTP/1.1 414 Request-URI Too Large",
            415 => "HTTP/1.1 415 Unsupported Media Type",
            416 => "HTTP/1.1 416 Requested range not satisfiable",
            417 => "HTTP/1.1 417 Expectation Failed",
            500 => "HTTP/1.1 500 Internal Server Error",
            501 => "HTTP/1.1 501 Not Implemented",
            502 => "HTTP/1.1 502 Bad Gateway",
            503 => "HTTP/1.1 503 Service Unavailable",
            504 => "HTTP/1.1 504 Gateway Time-out"
        );
        header($http[$num]);
        header("Location: {$url}");
        $this->BackyardError->log(5, "Redirect to {$url} with {$num} status");
        if ($stopCodeExecution) {
            exit; //default behaviour expects that no code should be interpreted after redirection
        }
    }

    /**
     * 
     * @param string $nameOfTheParameter
     * @return string or false
     */
    public function retrieveFromPostThenGet($nameOfTheParameter) {
        $result = false; //default value
        if (isset($_POST[$nameOfTheParameter])) {
            $result = $_POST[$nameOfTheParameter];
        } else {
            if (isset($_GET[$nameOfTheParameter])) {
                $result = $_GET[$nameOfTheParameter];
            }
        }
        $this->BackyardError->log((($result) ? (5) : (6)), "Retrieved parameter {$nameOfTheParameter}: " . print_r($result, true), array(16));
        return $result;
    }

    /**
     * Inspired by http://www.webcheatsheet.com/PHP/get_current_page_url.php
     * 
     * @param bool $includeTheQueryPart
     * @return string
     */
    public function getCurPageURL($includeTheQueryPart = true) {
        if ($includeTheQueryPart) {
            $endGame = $_SERVER["REQUEST_URI"]; //including RewriteRule result
        } else {
            $endGame = $_SERVER["SCRIPT_NAME"]; //without the query part and RewriteRule result
        }
        $isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
        $port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
        $port = ($port) ? ':' . $_SERVER["SERVER_PORT"] : '';
        $pageURL = ($isHTTPS ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . $port . $endGame;
        return $pageURL;
    }

    /**
     * gets data from a URL through cURL with optional POST
     * 
     * @param string $url
     * @param string $useragent default = 'PHP/cURL'
     * @param int $timeout [seconds] default =5
     * @param string|false $customHeaders default = false; string of HTTP headers delimited by pipe without trailing spaces
     * @param array $postArray OPTIONAL array of parameters to be POST-ed as the normal application/x-www-form-urlencoded string
     * @param string $customRequest OPTIONAL fills in CURLOPT_CUSTOMREQUEST
     * @return array ('message_body', 'HTTP_CODE', 'CONTENT_TYPE', 'HEADER_FIELDS', ['REDIRECT_URL',])
     */
    public function getData($url, $useragent = 'PHP/cURL', $timeout = 5, $customHeaders = false, $postArray = array(), $customRequest = null) {
        $this->BackyardError->log(5, "backyard getData({$url},{$useragent},{$timeout},{$customHeaders},".(empty($postArray)?'[]':(count($postArray).' fields')).",{$customRequest});", array(16));        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        if(!is_null($customRequest) && is_string($customRequest) && in_array($customRequest, array('GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'))){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
        }
        if ($customHeaders) {
            if (!is_string($customHeaders)) {
                $this->BackyardError->log(2, 'customHeaders string expected, got ' . gettype($customHeaders));
            }
            $customArray = explode('|', $customHeaders); //$customHeaders must be delimited by pipe without trailing spaces (comma is bad for accept header)
            $tempOptSer = curl_setopt($ch, CURLOPT_HTTPHEADER, $customArray);
            if (!$tempOptSer) {
                $this->BackyardError->log(2, "Custom headers {$customHeaders} FAILED to be set", array(16));
            }
        }

        $data = array();
        if ($postArray) {
            $data['POST'] = http_build_query($postArray);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data['POST']);
        }

        /* cannot be activated when in safe_mode or an open_basedir is set
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
         */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //writes connection info to STDERR, only for debug//curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //accepts also private SSL certificates //@todo it could be possible to try without that option and if it fails, it may try with this option and inform about it

        $response = curl_exec($ch);
        //@TODO - if response is 301 or 302, then dump header into output and make it clickable or maybe download the next step automatically - it is necessary however to stop after 5 redirects
        /* how to DEBUG some wrong content that force redirection - such as http://www.alfa.gods.cz/lib/emulator.php?url=http%3A%2F%2Fpic4mms.com%2F&original=1 * /
          header("Content-type: text/plain");//debug
          print_r(str_replace("i", "E", $data['MARKUP']));//debug
          exit;
          /* */

        //    if($response === false){
        //        $curlError = curl_error($ch);// to prevent some previous curl_error($ch) be reported
        //        my_error_log("Curl error: {$curlError}", 2); //@todo if curl_exec($ch) === false then some operation on $response below are meaningless
        //    }

        
        // http://stackoverflow.com/a/9183272
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $data['HTTP_CODE'] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE); //0 when timeout        
        if ($response) {
            $data['message_body'] = substr($response, $header_size);
        }
        if (!$response || (!$data['message_body'] && !in_array($data['HTTP_CODE'], array(301, 302)))) {//redirects may have empty body
            $this->BackyardError->log(2, "Curl error: " . curl_error($ch) . " on {$url} with HTTP_CODE={$data['HTTP_CODE']}");
            if (count($data) > 1) {
                $this->BackyardError->log(2, 'data:' . print_r($data, true));
            }
            if ($response) {
                $this->BackyardError->log(2, 'response: ' . $response);
            }
        }

        // $fields contains array of string which are lines of response header
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', substr($response, 0, $header_size) //message header
        ));
        $retVal = array();
        // http://stackoverflow.com/a/4243667
        foreach ($fields as $field) {
            if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', function($matches) {
                    return strtoupper($matches[0]);
                }, strtolower(trim($match[1])));
                if (isset($retVal[$match[1]])) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        // $retVal contains array("header_name": "header_value")
        $data['HEADER_FIELDS'] = $retVal;        
        if (isset($retVal['Location'])) {
            $data['REDIRECT_URL'] = $retVal['Location'];
        }

        $data['CONTENT_TYPE'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        return $data;
    }

    /**
     * Purpose: 
     * využíváno jen K:\Work\alfa.gods.cz\www\tools\check-pages.php
     * 
     * @todo .. is this just special case of getHTTPstatusCodeByUA ?? .. reuse and reduce
     * 
     * php_sockets required for socket_create
     *
     * *
     * History 
     * 120215, function GetHTTPstatusCode ($URL_STRING){ .. from r.godsapps.eu/index.php   
     * 120215, function get_data ($URL_STRING, $User-agent){ .. from r.godsapps.eu/magic-link.php
     * //120427, if the result is not number, maybe the server doesn't understand HEAD, let's try GET
     * 120427,   if($address != "81.31.47.101"){//gethostbyname returns this IP address on www.alfa.gods.cz if domain name does not exist  //@TODO - zautoamtizovat správnou IP adresu  
     * 140714, moved to backyard_http
     * 
     * 
     * @param string $URL_STRING
     * @return int|string
     */
    public function getHTTPstatusCode($URL_STRING) {
        $localDNSserver = array('81.31.47.101'); //@TODO - make configurable!
        $url = parse_url($URL_STRING);
        if (!isset($url['scheme'])) {
            $this->BackyardError->log(4, "No scheme present", array(16)); //debug
            return 0;
        }
        if ($url['scheme'] != 'http') {
            $this->BackyardError->log(4, "Scheme: {$url['scheme']} not supported by GetHTTPstatusCode", array(16)); //debug
            return 0;
        }
        $host = $url['host'];
        $port = (isset($url['port']) ? $url['port'] : 80);
        $path = (isset($url['path']) ? $url['path'] : '/');
        $this->BackyardError->log(4, "url: " . print_r($url, true), array(16)); //debug

        $request = "HEAD $path HTTP/1.1\r\n"
                . "Host: $host\r\n"
                . "Connection: close\r\n"
                . "\r\n";

        $this->BackyardError->log(5, "IPv4 is " . $address = gethostbyname($host), array(16)); //set & log
        if (in_array($address, $localDNSserver)) {//gethostbyname returns this IP address on www.alfa.gods.cz if domain name does not exist
            return 'DNS_error';
        }

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $socketResult = socket_connect($socket, $address, $port);
        if ($socketResult) {

            socket_write($socket, $request, strlen($request));
            $socketRead = socket_read($socket, 1024);
            $response = explode(' ', $socketRead);
            $this->BackyardError->log(4, "HEAD HTTP response: " . print_r($response, true), array(16)); //debug
            //120427, if the result is not number, maybe the server doesn't understand HEAD, let's try GET
            if (!is_numeric($response[1])) {
                $request = "GET $path HTTP/1.1\r\n"
                        . "Host: $host\r\n"
                        . "Connection: close\r\n"
                        . "\r\n";

                socket_write($socket, $request, strlen($request));
                $response = explode(' ', socket_read($socket, 1024));
                $this->BackyardError->log(4, "GET HTTP response: " . print_r($response, true), array(16)); //debug
                if (!is_numeric($response[1])) {
                    $this->BackyardError->log(3, "REQUEST = $request RETURNED RESPONSE = {$response[1]} INSTEAD OF HTTP status");
                }
            } elseif ($response[1] > 300 && $response[1] < 400) {
                $tempPosition = strpos($socketRead, "Location:");
                $tempLocation = substr($socketRead, $tempPosition + strlen("Location:"));
                $tempResponse = explode(PHP_EOL, $tempLocation);
                $response[1] .= " follow to " . trim($tempResponse[0]);
            }
        } else {
            $socketLastError = socket_last_error($socket);
            $socketLastErrorString = trim(iconv(mb_detect_encoding(socket_strerror($socketLastError), mb_detect_order(), true), "UTF-8", socket_strerror($socketLastError))); //http://stackoverflow.com/questions/7979567/php-convert-any-string-to-utf-8-without-knowing-the-original-character-set-or
            $this->BackyardError->log(3, "socket_connect to $host $path failed with {$socketLastError}: {$socketLastErrorString}"); //debug        
        }

        socket_close($socket);
        $this->BackyardError->log(5, "result=" . $result = (isset($response[1]) ? ($response[1]) : ($socketLastErrorString)), array(16)); //set & log 
        return $result;
    }

    public function getHTTPstatusCodeByUA($URL_STRING, $userAgent = "GetStatusCode/1.1") {
        $url = parse_url($URL_STRING);
        if (!isset($url['scheme'])) {
            $this->BackyardError->log(4, "No scheme present", array(16)); //debug
            return 0;
        }
        if ($url['scheme'] != 'http') {
            $this->BackyardError->log(4, "Scheme: {$url['scheme']} not supported by GetHTTPstatusCode", array(16)); //debug
            return 0;
        }

        $host = $url['host'];
        $port = $url['port'];
        $path = $url['path'];
        if (!$port) {
            $port = 80;
        }

        $request = "HEAD $path HTTP/1.1\r\n"
                . "Host: $host\r\n"
                . "User-agent: $userAgent\r\n"
                . "Connection: close\r\n"
                . "\r\n";

        $address = gethostbyname($host);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (socket_connect($socket, $address, $port)) {

            socket_write($socket, $request, strlen($request));

            $response = explode(' ', socket_read($socket, 1024));
            $this->BackyardError->log(4, "HTTP response: " . print_r($response, true), array(16)); //debug
        } else {
            $this->BackyardError->log(3, "socket_connect to $host $path failed", array(13)); //debug        
        }

        socket_close($socket);
        return $response[1];
    }

}
