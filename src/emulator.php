<?php
/**
 * Name: Emulator
 * Project: LIB/Part of Library In Backyard
 * 
 ** 
 * Purpose: 
 * Content-proxy for spoofing HTTP headers
 * May be included or called through HTTP API.
 * 
 * HTTP API call example
 * http://www.alfa.gods.cz/lib/emulator.php?original=0&url=http://dadastrip.cz/test/&useragent=NokiaN97/test&custom=
 * 
 * 
 * Include example
 * $URLinfo = array (
    'URL' => 'http://dadastrip.cz/test/', //URL
    'USER-AGENT' => 'NokiaN97/test', //User-agent
 *  'CUSTOM-HEADERS' => '\'x-red-ip: 8.8.8.8\', \'x-anything: some-value\'', //Custom headers //@TODO - ověřit, že to tak vážně funguje, myslím, že jsem to měnil...
    'RETURN-ORIGINAL' => false //Standard behaviour is to absolutize relative links; if ==true, then returns the unchanged mark-up
 *     );   
 * include ('emulator.php');
 * echo "$result"; //and $resultOriginal contains the unchanged mark-up
 * 
 * Output for HTTP API call:
 * Content-type with absolutized references (according to the original content-type)
 * Note that outputing the original mark-up may generate record into the error.log because of request to remote files on the local server.
 * 
 * @TODO - sladit naming convention
 * ****************************
 * 			1. Naming conventions
	* I try to produce long, self-explaining method names.
	* Comments formatted as Phpdoc, JSDoc
	*  I prefer to tag the variable type. I write rather entityA (array of entities) than simple entities. For an instance of song object, rather than song I name the variable songO.
Some examples:
	* variable, method – camelCase
	* class name – UpperCamelCase
	* url – hyphened-text
	* file – underscored_text
	* constant – BIG_LETTERS
			1. Comments
	* Primary language of comments is English.
	* Deprecated or obsolete code blocks are commented with prefix of the letter “x”. I may add reason for making the code obsolete as in the following:
        //Xhe’s got id from the beginning: $_SESSION["id"] = User::$himself->getId();
********************************
 * 
 ** 
 * History //@TODO - (update $VERSION['emulator'])
 * 2012-04-12, v.0.1 - vývoj dle magic-link.php v.0.4
 * 2012-04-16, v.0.2 - custom headers
 * 2012-04-30, v.0.3 - default behaviour is to absolutize references, may be changed by parameter ORIGINAL; $customArray = explode('|',$customHeaders);//$customHeaders must be delimited by pipe without trailing spaces (comma is bad for accept header) ; get_data returns array
 * 2012-04-30, v.0.4 - force content-type
 * 2012-12-23, v.0.4.1 - scheme je case insensitive; pokud include s $URLinfo, tak nenačítám functions.php knihovnu; přidat možnost vícenásobného include pomocí "if (!function_exists" a změny pořadí
 * 2012-12-24, v.0.4.2 - E413: Undefined scheme - added into AbsolutizeReferenceWithinMarkup
 * 
 ** 
 * TODO
 *  
 */

/**
 * Load Scripts & init
 */
if (!isset($URLinfo))//121223, predpoklad, že functions.php už bylo voláno ve správném kontextu. Když totiž include, tak hledá functions ve svém adresáři a nikoli v adresáři relativním k emulator.php
    require_once ("./functions.php"); //require the basic LIB library; all other LIB components to be included by require_once (__ROOT__."/lib/XXX.php");
/* database *//*
require_once ("tableName.php"); //configuration of database connection of that script
require_once (__ROOT__."/lib/connectDB.php");
include (__ROOT__."/lib/openDB.php"); //ale asi neni nutne, protoze dotazy do db fungovaly i bez toho...snad si to pamatovalo otevreni z functions.php, prestoze tam bylo i uzavreno
mysql_query("SET CHARACTER SET utf8");//aby se správně zapisovalo UTF-8 //http://php.vrana.cz/mysql-4-1-kodovani.php
*/
my_error_log("Knihovny pripojeny", 6, 6);

//$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
//require_once (__ROOT__."/lib/class_HTMLPage.php");//* If $CONTENT_TYPE == 'text/html' , be sure to set style.css in the same folder and have /jq/jquery-1.6.2.min.js present
/**
 * End of Load Scripts & init
 */


/**
 * FUNCTIONS (vs.  Cannot redeclare &  Call to undefined function)
 */
if (!function_exists('get_data')) {
/* gets the data from a URL */
function get_data($url,$useragent,$customHeaders=false)
{
    my_error_log("function get_data started with url={$url} useragent={$useragent} customHeaders={$customHeaders}",5,16);
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
  if ($customHeaders) {
      //print_r($customHeaders);//debug
      $customArray = explode('|',$customHeaders);//$customHeaders must be delimited by pipe without trailing spaces (comma is bad for accept header)
      //print_r($customArray);//debug
      //exit;//debug
      //if (
              curl_setopt($ch, CURLOPT_HTTPHEADER, $customArray);
      //        ) {
      //  my_error_log('Custom headers $customHeaders were set ok',5,16);
      //} else {
      //  my_error_log('Custom headers $customHeaders FAILED to be set',2,16);
      //}
  }
  /* $customHeaders == 'X-Apple-Tz: 0',
    'X-Apple-Store-Front: 143444,12' */
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  $data = array();
  $data['MARKUP'] = curl_exec($ch); //@TODO - pokud je 301 nebo 302, tak vypsat hlavičky do kodu a udělat z toho proklik anebo možná jít o krok dál a rovnou stáhnout i následující - je potřeba počítat do 5 redirektů
  /* how to DEBUG some wrong content that force redirection - such as http://www.alfa.gods.cz/lib/emulator.php?url=http%3A%2F%2Fpic4mms.com%2F&original=1 * /
  header("Content-type: text/plain");//debug
  print_r(str_replace("i", "E", $data['MARKUP']));//debug
  exit;
  /* */
  
  $data['CONTENT-TYPE'] = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
  curl_close($ch);
  return $data;
}
}

if (!function_exists('AbsolutizeReferenceWithinMarkup')) {
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
 * @param string $markupToBeProcessd Mark-up with relative URLs
 * @return string returns absolutized mark-up
 */
function AbsolutizeReferenceWithinMarkup($targetURL,$markupToBeProcessed){
//PROCESSING
   $url = parse_url($targetURL);
   if(isset($url['scheme'])) {if(strtolower($url['scheme']) == 'http') {//121223, scheme je case insensitive
    $host = $url['host'];
    $port = (isset($url['port'])?$url['port']:80);
    $path = (isset($url['path'])?$url['path']:'/');
    $directory = substr($targetURL, 0, strlen($targetURL)-strlen(strrchr($targetURL,'/'))+1);
    my_error_log("url: ".print_r($url,TRUE),4,16);//debug
   } else {
     my_error_log("E412: Unsupported scheme.", 2, 21);
     die ("E412: Unsupported scheme.");   //@TODO - works with http only, so far
   }} else {
     my_error_log("E413: Undefined scheme.", 2, 21);
     die ("E413: Undefined scheme.");   //@TODO - works with http only, so far       
   }

$patterns = array();
$replacements = array();

//PATHs referencing to the root directory of the original server
$patterns[1] = '/ (href|src|action)=(\'|")?\//i';
$replacements[1] = ' $1=$2'.$url['scheme'].'://'.$host.'/'; 

//PATHs referencing to the active directory of the original server
$patterns[9] = '/ (href|src|action)=(\'|")?(?!http)([\.A-Za-z0-9_\-])/i'; //ale různé od http* (tj. nemusí to být scheme, ale třeba začátek jména stránky)
$replacements[9] = ' $1=$2'.$directory.'$3'; 

ksort($patterns);
ksort($replacements);
//X $markupToBeProcessed = preg_replace($patterns, $replacements, $markupToBeProcessed);
return preg_replace($patterns, $replacements, $markupToBeProcessed);
// $markupToBeProcessed contains the modified mark-up
// /PROCESSING
}
}

if (!function_exists('emulateURL')) {        
function emulateURL ($URLinfo){
    $result = false;//init
    if(isset($URLinfo['URL'])){
        $userAgent = ((isset($URLinfo['USER-AGENT']))?($URLinfo['USER-AGENT']):((isset($_SERVER['HTTP_USER_AGENT']))?($_SERVER['HTTP_USER_AGENT']):('')));//either the assigned User-agent or the current one
        $result = get_data($URLinfo['URL'],$userAgent,(isset($URLinfo['CUSTOM-HEADERS'])?($URLinfo['CUSTOM-HEADERS']):false));
    } //otherwise return false
    return $result;
}
}

/**
 * /FUNCTIONS (vs.  Cannot redeclare &  Call to undefined function)
 */


if (!isset($URLinfo)){//if it is direct call or include without setting parameters
    $URLinfo = array (); //init
    $URLinfo['RETURN-ORIGINAL'] = false; //default
    $URLinfo['FORCE-CONTENT-TYPE'] = false; //default

    //put all the relevant GET parameters into the $URLinfo array
    //$headers = apache_request_headers();//HTTP header
    $headers = $_GET;//GET headers
    foreach ($headers as $header => $value) {
      switch ($header) {//all accepted parameters
          case "original":
            my_error_log("RETURN-ORIGINAL: ".($URLinfo['RETURN-ORIGINAL'] = (($value>0)?(true):(false))), 5, 16);//log and set          
            break;
         case "forcect":
            my_error_log("FORCE-CONTENT-TYPE: ".($URLinfo['FORCE-CONTENT-TYPE'] = $value), 5, 16);//log and set          
            break;
         case "useragent":
            my_error_log("USER-AGENT: ".($URLinfo['USER-AGENT'] = urldecode($value)), 5, 16);//log and set          
            break;
          case "url":
            my_error_log("URL: ".($URLinfo['URL'] = $value), 5, 16);//log and set
            break;
          case "custom":
            my_error_log("CUSTOM-HEADERS: ".($URLinfo['CUSTOM-HEADERS'] = urldecode($value)), 5, 16);//log and set //@TODO - kontrola validity
            break;        
          default:
            my_error_log("Unprocessed: $header: $value", 3, 13);//potential hack warning
       }  
    }
}


//Get the mark-up of the URL
$resultOriginal = emulateURL($URLinfo);

//Modify mark-up, so that relative URIs are replaced by absolute URIs unless RETURN-ORIGINAL == true
$result = ($URLinfo['RETURN-ORIGINAL'])?($resultOriginal['MARKUP']):(AbsolutizeReferenceWithinMarkup($URLinfo['URL'], $resultOriginal['MARKUP']));




//http://www.plus2net.com/php_tutorial/script-self.php
$file = $_SERVER["SCRIPT_NAME"]; // or $file = $_SERVER["SCRIPT_FILENAME"];
$break = Explode('/', $file);
my_error_log("emulator executed by: ".($pfile = $break[count($break) - 1]), 5, 16); 

if ($pfile == "emulator.php") {//if the file executing the code is this script, then it is not included 
    my_error_log("Direct output.",5,16);
    
    if($URLinfo['FORCE-CONTENT-TYPE']){
        header("Content-type: ".$URLinfo['FORCE-CONTENT-TYPE']);
    } elseif(isset($resultOriginal['CONTENT-TYPE'])){
        header("Content-type: ".$resultOriginal['CONTENT-TYPE']);
    } else {header("Content-type: text/html");}
    if(!$result){//stop if URL not set
        $result = "E101: Uniform resource locator missing.";
    }
    echo $result;
}


// **** END
/* HTML output management *//* 
    $pageInstance->startPage();
    $pageInstance->endPage();
    my_error_log("HTML vystup ukoncen", 6, 6);
*/
/* database *//*
my_error_log("DB uzavrit", 0, 6);
include (__ROOT__."/lib/closeDB.php"); //uzavření přístupu do db, dále již žádné SQL requesty nebudou ani v rámci funkcí
my_error_log("DB zavrena", 6, 6);
 */
my_error_log ("End of page generating",6,6); //Zápis rychlosti vygenerování stránky do logu



