<?php
error_log(__FILE__ . ' is obsolete and will be removed in next release');
die('LIB2');//security die
/**
 * Name: login_google.php
 * Project: LIB/Part of Library In Backyard
 * 
 * * 
 * Purpose: 
 * Social login do Google(+)
 * 
 * 
 * * 
 * History
 * 2013-05-03, v.1 - compression of debugging
 * 2014-04-06, v.2 - updated to work with backyard 2 and https://github.com/google/google-api-php-client/
 *
 * * TODO  
 * 
 * 
 */
//$ERROR_HACK=5;
//$myErrorLogMessageType=0;
if (!function_exists('removeqsvar')) {

//In http://stackoverflow.com/questions/1251582/beautiful-way-to-remove-get-variables-with-php see http://stackoverflow.com/a/1251650
    function removeqsvar($url, $varname) {
//    return preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','$1',$url);
        //$result = preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','$1',$url);
        $result = preg_replace("/&{2,}/", "&", preg_replace('/([?&])' . $varname . '=[^&]+(&|$)/', '$1', $url));
        //@TODO -   aby odstranilo i proměnnou neurčenou, tedy ?var= nebo i jen ?var
        //$newURL = parse_url($result);
        //if(empty($newURL['query']) && substr($result, -1) == '?')$result=substr_replace($result ,"",-1);//@TODO - určitě by šlo optimalizovat (aby nezůstal otazník na konci)    
        //$result = unparse_url($newURL);
        return $result;
    }

}

if (!function_exists('unparse_url')) {

    function unparse_url($parsed_url) { //http://www.php.net/manual/en/function.parse-url.php#106731
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}

if (!function_exists('addqsvar')) {

    function addqsvar($url, $varname, $value = '') {
        //$result = $url; 
        $newURL = parse_url($url);
        if (empty($newURL['query'])) { //@TODO - aby zohlednňovalo i fragment .. http://cz2.php.net/manual/en/function.parse-url.php
            //$result = "{$result}?{$varname}={$value}";
            $newURL['query'] = "{$varname}={$value}";
        } else {
            //$result = "{$result}&{$varname}={$value}";
            $newURL['query'].="&{$varname}={$value}";
        }
        return unparse_url($newURL); //1209200022    
    }

}


if (!isset($apiCredentials['google'])) {
    my_error_log('Google app credentials missing', 1);
} else {

    $apiCredentials['google']['auth'] = false;

    /**
     *  Google Login API 
     */
    my_error_log("Google API starts", 6, 6);
//$Google_authUrl=true;
// https://code.google.com/p/google-api-php-client/source/browse/trunk/examples/plus/index.php
// google-api-php-client is expected to be in lib folder and all scripts should run from the "root" folder
// available now from https://github.com/google/google-api-php-client.git
    set_include_path(get_include_path() . PATH_SEPARATOR . __BACKYARDROOT__ . '/../../google-api-php-client/src'); //otherwise I get Fatal error: require_once() [function.require]: Failed opening required 'Google/Auth/AssertionCredentials.php' (include_path='.;C:\php\pear') in K:\Work\godsdev\repo1\myreport\src\lib\google-api-php-client\src\Google\Client.php on line 18
    require_once __BACKYARDROOT__ . '/../../google-api-php-client/src/Google/Client.php';
    require_once __BACKYARDROOT__ . '/../../google-api-php-client/src/Google/Service/Plus.php';
    require_once __BACKYARDROOT__ . '/../../google-api-php-client/src/Google/Service/Oauth2.php'; //http://stackoverflow.com/questions/8706288/using-google-api-for-php-need-to-get-the-users-email
    my_error_log("Google API libraries required", 6, 6); //@TODO 3 - před tímto my_error se změní vypisovaný čas z GMT+2 na GMT

    if (session_id() == '') {
        session_start(); //@TODO - odladit konflikt více session: As of PHP 4.3.3, calling session_start() after the session was previously started will result in an error of level E_NOTICE. Also, the second session start will simply be ignored.
    }
//if(isset($_REQUEST['glloginproceed']))movePage(302, removeqsvar(removeqsvar(curPageURL(true), 'code'),'glloginproceed'));
//my_error_log('Session_id='.session_id(),5,16);
//my_error_log("apiCredentials: ".(print_r($apiCredentials,true)),5);  
    $apiCredentials['google']['redirectUri'] = removeqsvar($apiCredentials['google']['redirectUri'], 'code'); // http://www.violato.net/blog/php/125-fatal-error-uncaught-exception-apiauthexception-with-message-error-fetching-oauth2-access-token-message-invalidrequest
//        $apiCredentials['google']['redirectUri'] = addqsvar($apiCredentials['google']['redirectUri'], 'glloginproceed','1');//value 1 je jedno
    my_error_log("apiCredentials: " . backyard_dumpArrayAsOneLine($apiCredentials), 5, 24);

    $client = new Google_Client();
//$client = new apiClient();
    $client->setApplicationName($apiCredentials['google']['applicationName']); //@TODO 3 - kde se to vyskytuje? Mělo by být language dependant //http://javadoc.google-api-java-client.googlecode.com/hg/1.0.10-alpha/com/google/api/client/googleapis/GoogleHeaders.html  :  Sets the "User-Agent" header for the given application name of the form "[company-id]-[app-name]-[app-version]" into the given HTTP headers.
// Visit https://code.google.com/apis/console to generate your
// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
    $client->setClientId($apiCredentials['google']['clientId']);
    $client->setClientSecret($apiCredentials['google']['clientSecret']);
    $client->setRedirectUri($apiCredentials['google']['redirectUri']); //Chyba:redirect_uri_mismatch: The redirect URI in the request: http://dadastrip.cz/test did not match a registered redirect URI ... pokud není ve vyjmenovaných
//$client->setRedirectUri(removeqsvar($apiCredentials['google']['redirectUri'],'code'));// http://www.violato.net/blog/php/125-fatal-error-uncaught-exception-apiauthexception-with-message-error-fetching-oauth2-access-token-message-invalidrequest //Chyba:redirect_uri_mismatch: The redirect URI in the request: http://dadastrip.cz/test did not match a registered redirect URI ... pokud není ve vyjmenovaných 
    $client->setScopes(array('https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/plus.me'));      // Important! //http://stackoverflow.com/questions/8706288/using-google-api-for-php-need-to-get-the-users-email
//@TODO -140406 - při volání z localhost:8080 si app vyžádala Offline access - což je špatně!
// $client->setScopes(array('userinfo.email', 'plus.me'));
//$client->setDeveloperKey($apiCredentials['google']['serverDeveloperApiKey']);
    my_error_log("Google client set", 6, 6);
//$plus = new Google_PlusService($client);
    $plus = new Google_Service_Plus($client);
    my_error_log('$plus = new', 6, 6);
//$oauth2 = new apiOauth2Service($client); //http://stackoverflow.com/questions/8706288/using-google-api-for-php-need-to-get-the-users-email  
    $oauth2 = new Google_Service_Oauth2($client);
    //$client->authenticate($_GET['code']);//// Authenticate the user, $_GET['code'] is used internally:
    my_error_log('$oauth2 = new', 6, 6);

    if (isset($_REQUEST['gllogout'])) {
        my_error_log("SESSION['access_token'] will be unset", 5, 6);
        unset($_SESSION['access_token']);
        my_error_log("SESSION['access_token'] unset", 5, 6);
        session_destroy(); //120918
        $redirectTo = str_replace("gllogout", "", backyard_getCurPageURL()); //120918
        backyard_movePage(302, $redirectTo); //120918
    }

    my_error_log("Let us check _GET[code]", 6, 6);
    if (isset($_GET['code'])) {
        my_error_log("to be authenticated _GET[code]={$_GET['code']}", 6, 6);
        $client->authenticate($_GET['code']);
        //$client->authenticate();
        //PHP Fatal error:  Uncaught exception 'apiAuthException' with message 'Error fetching OAuth2 access token, message: 'redirect_uri_mismatch'' in /var/www/www.alfa.gods.cz/google-api-php-client/src/auth/apiOAuth2.php:105\nStack trace:\n#0 /var/www/www.alfa.gods.cz/google-api-php-client/src/apiClient.php(138): apiOAuth2->authenticate(Array)\n#1 /var/www/www.alfa.gods.cz/myreport/login_google.php(108): apiClient->authenticate('4/q9qkNo-_oaYxh...')\n#2 /var/www/www.alfa.gods.cz/myreport/magic-report.php(172): require_once('/var/www/www.al...')\n#3 {main}\n  thrown in /var/www/www.alfa.gods.cz/google-api-php-client/src/auth/apiOAuth2.php on line 105, referer: https://accounts.google.com/o/oauth2/auth?scope=https://www.googleapis.com/auth/userinfo.email+https://www.googleapis.com/auth/plus.me&response_type=code&access_type=offline&redirect_uri=https://www.alfa.gods.cz:443/myreport/magic-report.php&approval_prompt=force&client_id=844783748503.apps.googleusercontent.com&hl=cs&from_login=1&as=-3f2a8ec6fa31c2c0&authuser=0&pli=1
        //.. znamená, že cílová URL sice v Redirect URIs je, ale je jiná nyní než byla iniciovaná na začátku
        my_error_log("authenticated _GET[code]", 6, 6);
        $_SESSION['access_token'] = $client->getAccessToken();
        my_error_log('Dojde k header: ' . 'Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], 5);
        //header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
        //die("<a href='".parse_url(curPageURL(false),PHP_URL_SCHEME).'://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']."'>finish login</a>");
        header('Location: ' . filter_var(parse_url(backyard_getCurPageURL(false), PHP_URL_SCHEME) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], FILTER_SANITIZE_URL));
    }

    if (isset($_SESSION['access_token']) && $_SESSION['access_token'] && (strlen($_SESSION['access_token']) > 2)) {
        $client->setAccessToken($_SESSION['access_token']);
        my_error_log("accessToken set", 6, 6);
    }
    $googleUserProfile = false;
    if (!$client->isAccessTokenExpired() && $client->getAccessToken() && (strlen($client->getAccessToken()) > 2)) {
        $googleUserProfile = array();
        my_error_log('G+ je zalogovan, nyni ziskat info o zalogovanem', 5, 6);
        try { //http://stackoverflow.com/questions/9054656/uncaught-exception-apiserviceexception-with-message-error-calling-get
            $googleUserProfile['me'] = $plus->people->get('me');
        } catch (apiServiceException $e) { //when user is not registered in G+
            // Handle exception. You can also catch Exception here.
            // You can also get the error code from $e->getCode();
            $e->getCode();
            my_error_log("User is not registered for Google+; error: $e", 4);
        }
        // Will get id (number), email (string) and verified_email (boolean):
        $googleUserProfile['user'] = $oauth2->userinfo->get(); //http://stackoverflow.com/questions/8706288/using-google-api-for-php-need-to-get-the-users-email
        // These fields are currently filtered through the PHP sanitize filters.
        // See http://www.php.net/manual/en/filter.filters.sanitize.php
        $googleUserProfile['email'] = filter_var($googleUserProfile['user']['email'], FILTER_SANITIZE_EMAIL);  //http://stackoverflow.com/questions/8706288/using-google-api-for-php-need-to-get-the-users-email  
        if (isset($googleUserProfile['me'])) {
            $googleUserProfile['url'] = filter_var($googleUserProfile['me']['url'], FILTER_VALIDATE_URL);
            $googleUserProfile['img'] = filter_var($googleUserProfile['me']['image']['url'], FILTER_VALIDATE_URL);
            $googleUserProfile['name'] = filter_var($googleUserProfile['me']['displayName'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH); //140406, beware it may be empty string!
        } else {
            //$googleUserProfile['me'] = false;
            $googleUserProfile['url'] = false;
            $googleUserProfile['img'] = false;
            $googleUserProfile['name'] = $googleUserProfile['email'];
        }
        if ($googleUserProfile['name'] == '' || $googleUserProfile['email'] == '') {
            my_error_log("Google credentials may have failed: gurl={$googleUserProfile['url']} gname={$googleUserProfile['name']} gemail={$googleUserProfile['email']}", 3);
        }
        my_error_log("gurl={$googleUserProfile['url']} gimg={$googleUserProfile['img']} gname={$googleUserProfile['name']} gemail={$googleUserProfile['email']} ", 5, 16);
        //$Google_personMarkup = "<a rel='me' href='$Google_url'>$Google_name</a><div><img src='$Google_img'>Email: $Google_email</div>";
        /*
          $optParams = array('maxResults' => 100);
          $activities = $plus->activities->listActivities('me', 'public', $optParams);
          $activityMarkup = '';
          foreach($activities['items'] as $activity) {
          // These fields are currently filtered through the PHP sanitize filters.
          // See http://www.php.net/manual/en/filter.filters.sanitize.php
          $url = filter_var($activity['url'], FILTER_VALIDATE_URL);
          $title = filter_var($activity['title'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
          $content = filter_var($activity['object']['content'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
          $activityMarkup .= "<div class='activity'><a href='$url'>$title</a><div>$content</div></div>";
          }
         */

        // The access token may have been updated lazily.
        $_SESSION['access_token'] = $client->getAccessToken();
        my_error_log('g access token nastaven', 5);
        $apiCredentials['google']['auth'] = true;
        $apiCredentials['google']['logoutUrl'] = backyard_getCurPageURL(true) . (($_SERVER["REQUEST_URI"] == $_SERVER["SCRIPT_NAME"]) ? '?gllogout' : '&gllogout');
    } else { //140406, it seems that Google has upgraded all accounts to G+
        my_error_log("G+ nezalogovan, tak definuji login URL", 6, 6);
        $apiCredentials['google']['loginUrl'] = $client->createAuthUrl();
    }

//}//if !$user
//if(!isset($Google_authUrl))$Google_authUrl=false;//tj. false = jsem zalogovan

    /*
      <?php if(isset($personMarkup)): ?>
      <div class="me"><?php print $personMarkup ?></div>
      <?php endif ?>

      <?php if(isset($activityMarkup)): ?>
      <div class="activities">Your Activities: <?php print $activityMarkup ?></div>
      <?php endif ?>

      <?php
      if(isset($authUrl)) {
      print "<a class='login' href='$authUrl'>Connect Me!</a>";
      } else {
      print "<a class='logout' href='?logout'>Logout</a>";
      }
      ?>
     */
    /**
     *  /Google Login API
     * Output:
     * array $googleUserProfile
     * bool $apiCredentials['google']['auth']
     * url $apiCredentials['google']['loginUrl']
     */
}

/*
  if($apiCredentials['google']['auth']) {
      $googleAuthMarkUp="<a href='{$apiCredentials['google']['logoutUrl']}".(isset($_GET['ERROR_HACK'])?"&ERROR_HACK={$_GET['ERROR_HACK']}":"")."'  data-role='button' data-theme='f' data-icon='alert' class='fb-button'>"
          .localisationString('google_logout')."</a>";//red button //@TODO 2 - class=google-button
  } else {
      $googleAuthMarkUp="<a href='{$apiCredentials['google']['loginUrl']}".(isset($_GET['ERROR_HACK'])?"&ERROR_HACK={$_GET['ERROR_HACK']}":"")."' data-role='button' class='fb-button' data-theme='c'>"
            .localisationString('google_login')."</a>";//@TODO 2 - class=fb-button
  }
*/
