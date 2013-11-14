<?php
if (!function_exists('my_error_log')) {
    include_once 'functions_my_error_log_dummy.php';
}

/******************************************************************************
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
 * @param type $num
 * @param type $url
 */
function movePage($num, $url) {
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
    //die_graciously("100","Redirect to {$url} with {$num} status failed.");//aby se predeslo nepresmerovani pri predchozim vypisu hlavicek
    my_error_log("Redirect to {$url} with {$num} status", 5);
    //if($ERROR_HACK==5)
    //   echo "Redirect to {$url} with {$num} status";
    exit; //po redirektu jiz neni zadouci vykonavat kod
}

if (!function_exists('RetrieveFromPostThenGet')) {
    /**
     * 
     * @param string $nameOfTheParameter
     * @return string or false
     */
    function RetrieveFromPostThenGet($nameOfTheParameter) {
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
} else {
    my_error_log("RetrieveFromPostThenGet defined outside functions.php", 3, 0);//@TODO 3 - až už žádné nebudou, tak dát mimo !function_exists container
}



if (!function_exists('curPageURL')) {
    /**
     * Source: http://www.webcheatsheet.com/PHP/get_current_page_url.php
     * Usage: echo curPageURL();
     * Added into dada/fb/lib.php: 2010-11-03
     * 
     * @param type $includeTheQueryPart
     * @return string
     */
    function curPageURL($includeTheQueryPart = true) {
        if ($includeTheQueryPart) {//added 120819
            $endGame = $_SERVER["REQUEST_URI"];
        } else {
            $endGame = $_SERVER["SCRIPT_NAME"]; //without the query part
        }
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]))
            if ($_SERVER["HTTPS"] == "on") {
                $pageURL .= "s";
            }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $endGame;
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $endGame;
        }
        return $pageURL;
    }
} else {
    my_error_log("curPageURL defined outside fucntions.php", 3, 0);//@TODO 3 - až už žádné nebudou, tak dát mimo !function_exists container
}

