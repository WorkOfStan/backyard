<?php
//140717, inkorporováno do backyard_http.php
error_log('where is functions2.php used?');
die('Contact administrator, please, concerning error f2.');
/**
 * Name: Functions library 2
 * Project: LIB/Part of Library In Backyard
 * 
 ** 
 * Purpose: 
 * využíváno jen K:\Work\alfa.gods.cz\www\tools\check-pages.php
 * 
 * php_sockets required for socket_create
 *
 **
 * History 
 * 120215, function GetHTTPstatusCode ($URL_STRING){ .. from r.godsapps.eu/index.php   
 * 120215, function get_data ($URL_STRING, $User-agent){ .. from r.godsapps.eu/magic-link.php
 * //120427, if the result is not number, maybe the server doesn't understand HEAD, let's try GET
 * 120427,   if($address != "81.31.47.101"){//gethostbyname returns this IP address on www.alfa.gods.cz if domain name does not exist  //@TODO - zautoamtizovat správnou IP adresu  
 * 
 *
 ** TODO  
 * 
 * 
 */
   
function GetHTTPstatusCode ($URL_STRING){
    $localDNSserver = array('81.31.47.101');
    $url = parse_url($URL_STRING);
    if ($url['scheme'] == 'http') {

        //X if(!isset($url['path']))$url['path']='/';    
        $host = $url['host'];
        $port = (isset($url['port'])?$url['port']:80);
        $path = (isset($url['path'])?$url['path']:'/');
        my_error_log("url: ".print_r($url,TRUE),4,16);//debug
        //X if(!$port)
        //X    $port = 80;

        $request = "HEAD $path HTTP/1.1\r\n"
              ."Host: $host\r\n"
              ."Connection: close\r\n"
              ."\r\n";

        my_error_log("IPv4 is ".$address = gethostbyname($host),5,16);//set & log
        //if($address != "81.31.47.101"){//gethostbyname returns this IP address on www.alfa.gods.cz if domain name does not exist
        if(!in_array($address, $localDNSserver)){//gethostbyname returns this IP address on www.alfa.gods.cz if domain name does not exist
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            $socketResult = @socket_connect($socket, $address, $port);
            if ($socketResult) {

                socket_write($socket, $request, strlen($request));
                $socketRead = socket_read($socket, 1024);
                $response = explode(' ', $socketRead);
                my_error_log("HEAD HTTP response: ".print_r($response,TRUE),4,16);//debug
        
                //120427, if the result is not number, maybe the server doesn't understand HEAD, let's try GET
                if (!is_numeric($response[1])){
                    $request = "GET $path HTTP/1.1\r\n"
                        ."Host: $host\r\n"
                        ."Connection: close\r\n"
                        ."\r\n";

                    socket_write($socket, $request, strlen($request));
                    $response = explode(' ', socket_read($socket, 1024));
                    my_error_log("GET HTTP response: ".print_r($response,TRUE),4,16);//debug
                    if (!is_numeric($response[1])){
                        my_error_log("REQUEST = $request RETURNED RESPONSE = {$response[1]} INSTEAD OF HTTP status",3);
                    } 
                } elseif($response[1]>300 && $response[1]<400) {
                    //var_dump($socketRead);
                    $tempPosition = strpos($socketRead,"Location:");
                    $tempLocation = substr($socketRead,$tempPosition+strlen("Location:"));
                    $tempResponse = explode(PHP_EOL,$tempLocation);
                    //die (var_dump(trim($tempResponse[0])));
                    $response[1] .= " follow to ".trim($tempResponse[0]);
                }     
            } else {
                $socketLastError = socket_last_error($socket);
                $socketLastErrorString = trim(iconv(mb_detect_encoding(socket_strerror($socketLastError), mb_detect_order(), true), "UTF-8", socket_strerror($socketLastError)));//http://stackoverflow.com/questions/7979567/php-convert-any-string-to-utf-8-without-knowing-the-original-character-set-or
                //my_error_log("socket_connect to $host $path failed with {$socketLastError}",3,13);//debug        
                error_log("socket_connect to $host $path failed with {$socketLastError}: {$socketLastErrorString}");//debug        
            }

            //print "<p>Response: ". $response[1] ."</p>\r\n";

            socket_close($socket);
            my_error_log("result=".$result = (isset($response[1])?($response[1]):($socketLastErrorString)),5,16);//set & log 
            return $result;
        }//if($address != "81.31.47.101")
        else {
            return 'DNS_error';
        }
   } else {
       my_error_log("Scheme: {$url['scheme']} not supported by GetHTTPstatusCode",4,16);//debug
       return 0;
   }
}

function GetHTTPstatusCodeByUA ($URL_STRING, $userAgent = "GetStatusCode/1"){
   $url = parse_url($URL_STRING);
   if ($url['scheme'] == 'http') {
    
    $host = $url['host'];
    $port = $url['port'];
    $path = $url['path'];
    if(!$port)
        $port = 80;

    $request = "HEAD $path HTTP/1.1\r\n"
              ."Host: $host\r\n"
              ."User-agent: $userAgent\r\n"              
              ."Connection: close\r\n"
              ."\r\n";

    $address = gethostbyname($host);
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (socket_connect($socket, $address, $port)) {

        socket_write($socket, $request, strlen($request));

        $response = explode(' ', socket_read($socket, 1024));
        my_error_log("HTTP response: ".print_r($response,TRUE),4,16);//debug
    } else {
        my_error_log("socket_connect to $host $path failed",3,13);//debug        
    }

    //print "<p>Response: ". $response[1] ."</p>\r\n";

    socket_close($socket);
    return $response[1];
   } else {
       my_error_log("Scheme: {$url['scheme']} not supported by GetHTTPstatusCode",4,16);//debug
       return 0;
   }
}

require_once 'functions_http.php';

