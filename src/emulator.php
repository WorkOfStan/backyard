<?php
// phpcs:ignoreFile
error_log(__FILE__ . ' is obsolete - consider its rewriting');
die('LIB2'); //security die
//backyard 2 compliant
/**
 * Name: Emulator
 * Project: LIB/Part of Library In Backyard
 *
 * *
 * Purpose:
 * Content-proxy for spoofing HTTP headers
 * May be included or called through HTTP API.
 *
 * HTTP API call example
 * ./lib/backyard/src/emulator.php?original=0&url=http://dadastrip.cz/test/&useragent=NokiaN97/test&custom=
 * Output for HTTP API call:
 * Content-type with absolutized references (according to the original content-type)
 * Note that outputing the original mark-up may generate record into the error.log because of request to remote files on the local server.
 *
 *
 * Include example
 * $URLinfo = array (
  'URL' => 'http://dadastrip.cz/test/', //URL
  'USER-AGENT' => 'NokiaN97/test', //User-agent
  'CUSTOM-HEADERS' => '\'x-red-ip: 8.8.8.8\', \'x-anything: some-value\'', //Custom headers //@TODO - ověřit, že to tak vážně funguje, myslím, že jsem to měnil...
  'RETURN-ORIGINAL' => false //Standard behaviour is to absolutize relative links; if ==true, then returns the unchanged mark-up
 *     );
 * include ('./lib/backyard/src/emulator.php');
 * $payload = getEmulatedPayload($URLinfo);
 * echo $payload['message_output'];//while $payload['CONTENT-TYPE'] contains the content_type and $payload['message_body'] the original message
 *
 * *
 * History //@TODO - (update $VERSION['emulator'])
 * 2012-04-12, v.0.1 - vývoj dle magic-link.php v.0.4
 * 2012-04-16, v.0.2 - custom headers
 * 2012-04-30, v.0.3 - default behaviour is to absolutize references, may be changed by parameter ORIGINAL; $customArray = explode('|',$customHeaders);//$customHeaders must be delimited by pipe without trailing spaces (comma is bad for accept header) ; get_data returns array
 * 2012-04-30, v.0.4 - force content-type
 * 2012-12-23, v.0.4.1 - scheme je case insensitive; pokud include s $URLinfo, tak nenačítám functions.php knihovnu; přidat možnost vícenásobného include pomocí "if (!function_exists" a změny pořadí
 * 2012-12-24, v.0.4.2 - E413: Undefined scheme - added into AbsolutizeReferenceWithinMarkup
 * 2014-03-12, v.0.5 - moved to backyard/src
 *
 * *
 * TODO
 *
 */
/**
 * Load Scripts & init
 */
require_once __DIR__ . "/backyard_http.php";

if (!isset($URLinfo)) {//if it is direct call or include without setting parameters
    $URLinfo = array(); //init
    $URLinfo['RETURN-ORIGINAL'] = false; //default
    $URLinfo['FORCE-CONTENT-TYPE'] = false; //default
    //put all the relevant GET parameters into the $URLinfo array
    //$headers = apache_request_headers();//HTTP header
    $headers = $_GET; //GET headers
    foreach ($headers as $header => $value) {
        switch ($header) {//all accepted parameters
            case "original":
                my_error_log("RETURN-ORIGINAL: " . ($URLinfo['RETURN-ORIGINAL'] = (($value > 0) ? (true) : (false))), 5, 16); //log and set
                break;
            case "forcect":
                my_error_log("FORCE-CONTENT-TYPE: " . ($URLinfo['FORCE-CONTENT-TYPE'] = $value), 5, 16); //log and set
                break;
            case "useragent":
                my_error_log("USER-AGENT: " . ($URLinfo['USER-AGENT'] = urldecode($value)), 5, 16); //log and set
                break;
            case "url":
                my_error_log("URL: " . ($URLinfo['URL'] = $value), 5, 16); //log and set
                break;
            case "custom":
                my_error_log("CUSTOM-HEADERS: " . ($URLinfo['CUSTOM-HEADERS'] = urldecode($value)), 5, 16); //log and set //@TODO - kontrola validity
                break;
            default:
                my_error_log("Unprocessed: $header: $value (isnt it hacking?)", 3, 13); //potential hack warning
        }
    }
}

/**
 * End of Load Scripts & init
 */
/**
 * FUNCTIONS
 */

/**
 * function AbsolutizeReferenceWithinMarkup
 *
 * Purpose:
 * Create mean to alter relative URIs within fetched markup to point to the original locations
 *
 * Description:
 * Replace relative URI within attributes action, src and href (case-insensitive)
 * by an absolute URI targeting to the original location.
 * The referenced items will be however called with HTTP headers from the user's browser.
 * (May not work because the counter hot linking measures on the target site.)
 * Items referenced from remote CSS or JS use the remote file base.
 * This script modifies the original mark-up. Hence, changes done by javascript are not modified.
 * Click on any link leads on an unmodified original destination.
 * (@TODO - correct the language so that the elements are called by appropriate denominations.)
 *
 * @param string $targetURL Target URL to establish scheme, domain and path
 * @param string $markupToBeProcessed Mark-up with relative URLs
 * @return string returns absolutized mark-up
 */
function AbsolutizeReferenceWithinMarkup($targetURL, $markupToBeProcessed)
{
    $url = parse_url($targetURL);
    if (isset($url['scheme'])) {
        if (strtolower($url['scheme']) == 'http') {//121223, scheme je case insensitive
            $host = $url['host'];
            $port = (isset($url['port']) ? $url['port'] : 80);
            $path = (isset($url['path']) ? $url['path'] : '/');
            $directory = substr($targetURL, 0, strlen($targetURL) - strlen(strrchr($targetURL, '/')) + 1);
            my_error_log("url: " . print_r($url, true), 4, 16); //debug
        } else {
            my_error_log("E412: Unsupported scheme.", 2, 21);
            die("E412: Unsupported scheme.");   //@TODO - works with http only, so far
        }
    } else {
        my_error_log("E413: Undefined scheme.", 2, 21);
        die("E413: Undefined scheme.");   //@TODO - works with http only, so far
    }

    $patterns = array();
    $replacements = array();

    //PATHs referencing to the root directory of the original server
    $patterns[1] = '/ (href|src|action)=(\'|")?\//i';
    $replacements[1] = ' $1=$2' . $url['scheme'] . '://' . $host . '/';

    //PATHs referencing to the active directory of the original server
    $patterns[9] = '/ (href|src|action)=(\'|")?(?!http)([\.A-Za-z0-9_\-])/i'; //ale různé od http* (tj. nemusí to být scheme, ale třeba začátek jména stránky)
    $replacements[9] = ' $1=$2' . $directory . '$3';

    ksort($patterns);
    ksort($replacements);
    return preg_replace($patterns, $replacements, $markupToBeProcessed);
}

function emulateURL($URLinfo)
{
    if (!isset($URLinfo['URL'])) {
        return false;
    }
    $userAgent = ((isset($URLinfo['USER-AGENT'])) ? ($URLinfo['USER-AGENT']) : ((isset($_SERVER['HTTP_USER_AGENT'])) ? ($_SERVER['HTTP_USER_AGENT']) : (''))); //either the assigned User-agent or the current one
    return backyard_getData($URLinfo['URL'], $userAgent, 10, (isset($URLinfo['CUSTOM-HEADERS']) ? ($URLinfo['CUSTOM-HEADERS']) : false));
}

function getEmulatedPayload($URLinfo)
{
    $payload = emulateURL($URLinfo); //Get the mark-up of the URL
    if (!$payload) {
        return false;
    }
    $payload['message_output'] = $payload['message_body'];
    if (!$URLinfo['RETURN-ORIGINAL'] && preg_match("/^text/i", $payload['CONTENT-TYPE'])) {
        $payload['message_output'] = AbsolutizeReferenceWithinMarkup($URLinfo['URL'], $payload['message_body']); //Modify mark-up, so that relative URIs are replaced by absolute URIs unless RETURN-ORIGINAL == true
    }
    return $payload;
}
/**
 * /FUNCTIONS
 */
//http://www.plus2net.com/php_tutorial/script-self.php
$file = $_SERVER["SCRIPT_NAME"]; // or $file = $_SERVER["SCRIPT_FILENAME"];
$break = Explode('/', $file);
my_error_log("emulator executed by: " . ($pfile = $break[count($break) - 1]), 5, 16);

if ($pfile == "emulator.php") {//if the file executing the code is this script, then following is not executed
    my_error_log("Direct output.", 5, 16);
    $payload = getEmulatedPayload($URLinfo);
    if (!$payload) {
        die("E101: No result.");
    }

    if ($URLinfo['FORCE-CONTENT-TYPE']) {
        header("Content-type: " . $URLinfo['FORCE-CONTENT-TYPE']);
    } elseif (isset($payload['CONTENT-TYPE'])) {
        header("Content-type: " . $payload['CONTENT-TYPE']);
    } else {
        header("Content-type: text/html");
    }
    if (!$payload['message_output']) {//stop if URL not set
        die("E102: No result.");
    }
    echo $payload['message_output'];
}
