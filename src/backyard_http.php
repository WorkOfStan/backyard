<?php
//backyard 2 compliant
if (!function_exists('my_error_log')) {
    require_once dirname(__FILE__) . '/backyard_my_error_log_dummy.php';
}

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
function backyard_movePage($num, $url, $stopCodeExecution = true) {
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
    header("Location: $url");
    my_error_log("Redirect to {$url} with {$num} status", 5);
    if ($stopCodeExecution){
        exit; //default behaviour expects that no code should be interpreted after redirection
    }
}

/**
 * 
 * @param string $nameOfTheParameter
 * @return string or false
 */
function backyard_retrieveFromPostThenGet($nameOfTheParameter) {
    $result = false; //default value
    if (isset($_POST[$nameOfTheParameter])) {
        $result = $_POST[$nameOfTheParameter];
    } else {
        if (isset($_GET[$nameOfTheParameter])) {
            $result = $_GET[$nameOfTheParameter];
        }
    }
    my_error_log("Retrieved parameter {$nameOfTheParameter}: " . print_r($result, true), (($result) ? (5) : (6)), 16);
    return $result;
}

/**
 * Source: http://www.webcheatsheet.com/PHP/get_current_page_url.php
 * Usage: echo curPageURL();
 * Added into dada/fb/lib.php: 2010-11-03
 * 
 * @param type $includeTheQueryPart
 * @return string
 */
function backyard_getCurPageURL($includeTheQueryPart = true) {
    if ($includeTheQueryPart) {//added 120819
        $endGame = $_SERVER["REQUEST_URI"];
    } else {
        $endGame = $_SERVER["SCRIPT_NAME"]; //without the query part
    }
    $isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
    $port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
    $port = ($port) ? ':' . $_SERVER["SERVER_PORT"] : '';
    $pageURL = ($isHTTPS ? 'https://' : 'http://') . $_SERVER["SERVER_NAME"] . $port . $endGame;
    return $pageURL;
}

/**
 * gets data from a URL through cURL
 * @param string $url
 * @param string $useragent default = 'PHP/cURL'
 * @param int $timeout [seconds] default =5
 * @param string||false $customHeaders default = false; string of HTTP headers delimited by pipe without trailing spaces
 * @return array or false
 */
function backyard_getData($url, $useragent = 'PHP/cURL', $timeout = 5, $customHeaders = false) {
    my_error_log("backyard_getData({$url},{$useragent},{$timeout});", 5, 16);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    if ($customHeaders) {
        $customArray = explode('|', $customHeaders); //$customHeaders must be delimited by pipe without trailing spaces (comma is bad for accept header)
        $tempOptSer = curl_setopt($ch, CURLOPT_HTTPHEADER, $customArray);
        if (!$tempOptSer) {
            my_error_log("Custom headers {$customHeaders} FAILED to be set", 2, 16);
        }
    }
    /* cannot be activated when in safe_mode or an open_basedir is set
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
      curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
     */
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = array();
    $data['message_body'] = curl_exec($ch); //@TODO - pokud je 301 nebo 302, tak vypsat hlavičky do kodu a udělat z toho proklik anebo možná jít o krok dál a rovnou stáhnout i následující - je potřeba počítat do 5 redirektů
    //@TODO - ovšem získat Location (a zachovat normální message_body výstup) není triviální, viz http://stackoverflow.com/questions/4062819/curl-get-redirect-url-to-a-variable
    /* how to DEBUG some wrong content that force redirection - such as http://www.alfa.gods.cz/lib/emulator.php?url=http%3A%2F%2Fpic4mms.com%2F&original=1 * /
      header("Content-type: text/plain");//debug
      print_r(str_replace("i", "E", $data['MARKUP']));//debug
      exit;
      /* */
    if (!$data['message_body']) {
        my_error_log("Curl error: " . curl_error($ch) . " on {$url}", 2);
    }
    $data['CONTENT-TYPE'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);  //@TODO - original, to be made obsolete by CONTENT_TYPE
    $data['CONTENT_TYPE'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $data['HTTP_CODE'] = curl_getinfo($ch, CURLINFO_HTTP_CODE); //0 when timeout
    //$data['REDIRECT_URL'] = curl_getinfo($ch,CURLINFO_REDIRECT_URL);//added to PHP 5.3.7 - but not documented
    curl_close($ch);
    return $data;
}

/**
 * Purpose: 
 * využíváno jen K:\Work\alfa.gods.cz\www\tools\check-pages.php
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
 * @param type $URL_STRING
 * @return int|string
 */
function backyard_getHTTPstatusCode($URL_STRING) {
    $localDNSserver = array('81.31.47.101'); //@TODO - make configurable!
    $url = parse_url($URL_STRING);
    if ($url['scheme'] != 'http') {
        my_error_log("Scheme: {$url['scheme']} not supported by GetHTTPstatusCode", 4, 16); //debug
        return 0;
    }
    $host = $url['host'];
    $port = (isset($url['port']) ? $url['port'] : 80);
    $path = (isset($url['path']) ? $url['path'] : '/');
    my_error_log("url: " . print_r($url, TRUE), 4, 16); //debug

    $request = "HEAD $path HTTP/1.1\r\n"
            . "Host: $host\r\n"
            . "Connection: close\r\n"
            . "\r\n";

    my_error_log("IPv4 is " . $address = gethostbyname($host), 5, 16); //set & log
    if (in_array($address, $localDNSserver)) {//gethostbyname returns this IP address on www.alfa.gods.cz if domain name does not exist
        return 'DNS_error';
    }

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    $socketResult = @socket_connect($socket, $address, $port);
    if ($socketResult) {

        socket_write($socket, $request, strlen($request));
        $socketRead = socket_read($socket, 1024);
        $response = explode(' ', $socketRead);
        my_error_log("HEAD HTTP response: " . print_r($response, TRUE), 4, 16); //debug
        //120427, if the result is not number, maybe the server doesn't understand HEAD, let's try GET
        if (!is_numeric($response[1])) {
            $request = "GET $path HTTP/1.1\r\n"
                    . "Host: $host\r\n"
                    . "Connection: close\r\n"
                    . "\r\n";

            socket_write($socket, $request, strlen($request));
            $response = explode(' ', socket_read($socket, 1024));
            my_error_log("GET HTTP response: " . print_r($response, TRUE), 4, 16); //debug
            if (!is_numeric($response[1])) {
                my_error_log("REQUEST = $request RETURNED RESPONSE = {$response[1]} INSTEAD OF HTTP status", 3);
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
        error_log("socket_connect to $host $path failed with {$socketLastError}: {$socketLastErrorString}"); //debug        
    }

    socket_close($socket);
    my_error_log("result=" . $result = (isset($response[1]) ? ($response[1]) : ($socketLastErrorString)), 5, 16); //set & log 
    return $result;
}

function backyard_getHTTPstatusCodeByUA($URL_STRING, $userAgent = "GetStatusCode/1.1") {
    $url = parse_url($URL_STRING);
    if ($url['scheme'] != 'http') {
        my_error_log("Scheme: {$url['scheme']} not supported by GetHTTPstatusCode", 4, 16); //debug
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
        my_error_log("HTTP response: " . print_r($response, TRUE), 4, 16); //debug
    } else {
        my_error_log("socket_connect to $host $path failed", 3, 13); //debug        
    }

    socket_close($socket);
    return $response[1];
}
