<?php
/**
 * Name: login_facebook.php
 * Project: LIB/Part of Library In Backyard
 * 
 ** 
 * Purpose: 
 * Social login do Facebook
 * 
 * 
 ** 
 * History
 * 2013-02-24, my_error_log[debug] added to note the successful start of the FB authentication
 * 2013-03-10, if(!isset($facebookUserProfile)) die_graciously ('403', 'Server lost connection to Facebook during an on-going session');if(session_id() && !$facebookUserProfile['id'])my_error_log ("session_id() is not empty while facebookUserProfile['id'] is empty - it seems than facebook library cannot communicate with Facebook",1);
 * 2013-03-12, correctly set to report as my_error_log(ERROR) unreachability of Facebook backend from the server
 * 2013-05-03, compression of debugging
 *
 ** TODO  
 * 
 * 
 */

//$myErrorLogMessageType=0;
//$ERROR_HACK=5;
if (!function_exists('removeqsvar')) {
//In http://stackoverflow.com/questions/1251582/beautiful-way-to-remove-get-variables-with-php see http://stackoverflow.com/a/1251650
function removeqsvar($url, $varname) {
//    return preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','$1',$url);
    //$result = preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','$1',$url);
    $result = preg_replace( "/&{2,}/", "&", preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','$1',$url));
    //@TODO -   aby odstranilo i proměnnou neurčenou, tedy ?var= nebo i jen ?var
    //$newURL = parse_url($result);
    //if(empty($newURL['query']) && substr($result, -1) == '?')$result=substr_replace($result ,"",-1);//@TODO - určitě by šlo optimalizovat (aby nezůstal otazník na konci)    
    //$result = unparse_url($newURL);
    return $result;    
}
}

if (!function_exists('unparse_url')) {
function unparse_url($parsed_url) { //http://www.php.net/manual/en/function.parse-url.php#106731
  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
  $pass     = ($user || $pass) ? "$pass@" : ''; 
  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
  return "$scheme$user$pass$host$port$path$query$fragment"; 
}
}

if (!function_exists('addqsvar')) {
function addqsvar($url, $varname, $value='') {
    //$result = $url; 
    $newURL = parse_url($url);
    if(empty($newURL['query'])){ //@TODO - aby zohlednňovalo i fragment .. http://cz2.php.net/manual/en/function.parse-url.php
        //$result = "{$result}?{$varname}={$value}";
        $newURL['query']="{$varname}={$value}";
    } else {
        //$result = "{$result}&{$varname}={$value}";
        $newURL['query'].="&{$varname}={$value}";
    }
    return unparse_url($newURL);//1209200022    
}
}


/**
 *  Resources
 *  header('P3P: CP="CAO PSA OUR"');//http://stackoverflow.com/questions/8026505/facebook-web-app-getting-error-the-state-does-not-match-you-may-be-a-victim-o  
 *         //http://stackoverflow.com/questions/5699214/logout-problem-with-facebook-application
    //http://stackoverflow.com/questions/4265844/delete-facebook-session-cookie-from-my-application-on-users-logout/4776187#4776187
    //http://php.net/manual/en/function.session-destroy.php
 * http://developers.facebook.com/docs/authentication/server-side/

 * 
 */

if(!isset($apiCredentials['facebook']) 
   //     || parse_url(curPageURL(true),PHP_URL_SCHEME) != 'http' //@TODO -- anebo by šlo location scheme hsot _SERVER["REQUEST_URI"] .. jako v login_google a parametr, který by to pak přesměroval zas an https
        ){ 
    my_error_log('Facebook app credentials missing'
            //.' or scheme '.parse_url(curPageURL(true),PHP_URL_SCHEME).' is not http'
            , 1);
} else {
    //zahajeno constructorem new Facebook//if(session_id()=='')session_start ();my_error_log('Session_id='.session_id(),5,16);        
    $apiCredentials['facebook']['auth']=false;    
    // facebook-src is expected to be in lib folder and all scripts should run from the "root" folder
    //https://github.com/facebook/facebook-php-sdk
    require_once __BACKYARDROOT__.'/../../facebook-php-sdk/src/facebook.php';    my_error_log('Facebook auth successfully initiated: sessionId='.session_id().' Passed the line '.__LINE__,5,6);
    $facebook = new Facebook(array(// Create our Application instance
        'appId'  => $apiCredentials['facebook']['appId'],
        'secret' => $apiCredentials['facebook']['secret']//,
        //'sharedSession' => true //if your app shares the domain with other apps
    ));             my_error_log('sessionId='.session_id().' Passed the line '.__LINE__,5,6);
    my_error_log('facebook='.  backyard_dumpArrayAsOneLine($facebook).' Passed the line '.__LINE__,5,6);
    if(isset($_GET['fblogout'])){       my_error_log('Passed the line '.__LINE__,5,6);
        $_SESSION = array();    // Unset all of the session variables.        
        if (ini_get("session.use_cookies")) {// If it's desired to kill the session, also delete the session cookie. Note: This will destroy the session, and not just the session data!
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }   my_error_log('Passed the line '.__LINE__,5,6);
        session_destroy();   my_error_log('Passed the line '.__LINE__,5,6);
        //my info: sessions after session_destroy stále zůstává - je potřeba redirect. Možná stačí méně.
        //redirect w/o fblogout
        $redirectTo= str_replace("fblogout","",backyard_getCurPageURL());  my_error_log('Passed the line '.__LINE__,5,6);
        backyard_movePage(302,$redirectTo);//@TODO - proběhne opravdu nebo kvůli nějakým headers neproběhne?        
        my_error_log('Passed the line '.__LINE__,5,6);
    }    my_error_log('Passed the line '.__LINE__,5,6);
    $facebookUser = $facebook->getUser();// Get User ID
    my_error_log('facebookUser: '. backyard_dumpArrayAsOneLine($facebookUser).' Passed the line '.__LINE__,5,6);
    if(isset($_REQUEST['fbloginproceed']) && $facebookUser == 0)my_error_log ("Facebook server cannot be reached from backend", 2);
    //if(isset($_REQUEST['fbloginproceed'])){print_r(parse_url(curPageURL (true)));echo str_replace('https://','http://',curPageURL(true)); exit;}
    //if(isset($_REQUEST['fbloginproceed']) && parse_url(curPageURL(true),PHP_URL_SCHEME) == 'https')movePage(302, str_replace(':443','',str_replace('https://','http://',curPageURL(true))));//@TODO - asi to není nejbezpečnější, ale https se fb nezaloguje - snad kvůli cookies?
    if(isset($_REQUEST['fbloginproceed']) && parse_url(backyard_getCurPageURL(true),PHP_URL_SCHEME) == 'https'){        
        my_error_log('fbloginproceed and ssl', 5, 16);
        $parsedUrl = parse_url(
                addqsvar(  //magic-security vynucuje HTTPS, ale lze vynutit HTTP
                addqsvar(backyard_getCurPageURL(true),'fbloginproceedssl',1)//1 aby fungovalo removeqsvar
                 ,'fh', md5($_SERVER['REMOTE_ADDR'].date('W').'3PoOiaVCrj')  ) //magic-security vynucuje HTTPS, ale lze vynutit HTTP
                );
        $parsedUrl['scheme']='http'; $parsedUrl['port']=80;
//            echo "<br/>GET: ".nl2br(print_r($_GET,true));  
//    echo "Session id: ".session_id();echo "<br/>Session: ".nl2br(print_r($_SESSION,true));  
//        echo ($facebookUser);echo "<a href='".unparse_url($parsedUrl)."'>Go on fbloginproceed and ssl</a>";
        movePage(302, unparse_url($parsedUrl));//@TODO - asi to není nejbezpečnější, ale https se fb nezaloguje - snad kvůli cookies?
  //      exit;
        //echo "hereiam";exit;
    }
    if(isset($_REQUEST['fbloginproceed'])){
        my_error_log('fbloginproceed over http', 5, 16);
//            echo "<br/>GET: ".nl2br(print_r($_GET,true));  
//    echo "Session id: ".session_id();echo "<br/>Session: ".nl2br(print_r($_SESSION,true));  
//                echo ($facebookUser);echo "<a href='".removeqsvar(removeqsvar(removeqsvar(curPageURL(true),'code'),'state'),'fbloginproceed')."'>Go on fbloginproceed over http</a>";
        backyard_movePage(302, removeqsvar(removeqsvar(removeqsvar(backyard_getCurPageURL(true),'code'),'state'),'fbloginproceed'));
//        exit;
    }
    if(isset($_REQUEST['fbloginproceedssl']) //&& !isset($_REQUEST['fbloginproceed'])
             && !isset($_REQUEST['fbloginstart'])
            ){
        my_error_log('fbloginproceedssl over http', 5, 16);
        echo backyard_getCurPageURL(true)."<br/>";//@TODO - vážně tu je echo??
        $parsedUrl = parse_url(
                removeqsvar( //konec vyjimky pro magic-security
                removeqsvar(backyard_getCurPageURL(true),'fbloginproceedssl')
                  , 'fh')      //konec vyjimky pro magic-security
                );
        $parsedUrl['scheme']='https'; $parsedUrl['port']=443;
            echo "<br/>GET: ".nl2br(print_r($_GET,true));  //@TODO - vážně tu je echo??
    echo "Session id: ".session_id();echo "<br/>Session: ".nl2br(print_r($_SESSION,true));  
        echo ($facebookUser);//@TODO - vážně tu je echo??
        //echo "<a href='".addqsvar(unparse_url($parsedUrl),'shouldbeok','1')."'>Go on fbloginproceedssl over http</a>";
//        echo "<a href='".unparse_url($parsedUrl)."'>Go on fbloginproceedssl over http</a>";
        //movePage(302, addqsvar(unparse_url($parsedUrl),'shouldbeok','1'));        
        backyard_movePage(302, unparse_url($parsedUrl));        
        //echo unparse_url($parsedUrl);
//        exit;
    } /**/           
    
    
    // We may or may not have this data based on whether the user is logged in.
    // If we have a $user id here, it means we know the user is logged into
    // Facebook, but we don't know if the access token is valid. An access
    // token is invalid if the user logged out of Facebook.
    if ($facebookUser) {
        try {// Proceed knowing you have a logged in user who's authenticated.
            $facebookUserProfile = $facebook->api('/me'); my_error_log('fbUserProfile: '.backyard_dumpArrayAsOneLine($facebookUserProfile).' Passed the line '.__LINE__,5,6);
            $facebookUserProfile['img'] = "http://graph.facebook.com/{$facebookUserProfile['id']}/picture";

            if($facebookUserProfile){   my_error_log('Passed the line '.__LINE__,5,6);
            
               if(isset($_REQUEST['proceedfblogout'])){ //calling $facebook->getLogoutUrl only in case of actual logout prevents error: CSRF state token does not match one provided.
                $params = array( 'next' => backyard_getCurPageURL(false).'?fblogout' );
                $apiCredentials['facebook']['logoutUrl'] = $facebook->getLogoutUrl($params); my_error_log('Passed the line '.__LINE__,5,6);
                backyard_movePage(302,$apiCredentials['facebook']['logoutUrl']);
                exit;
               }
                $apiCredentials['facebook']['logoutUrl']=backyard_getCurPageURL(false).'?proceedfblogout';
                $apiCredentials['facebook']['auth']=true;      
            }  my_error_log('Passed the line '.__LINE__,5,6);
            
        } catch (FacebookApiException $e) {  my_error_log('Passed the line '.__LINE__,5,6);
            my_error_log("Facebook cannot be re-reached: {$e}",2);
            $facebookUser = null;
        }  my_error_log('Passed the line '.__LINE__,5,6);
        if(isset($facebookUserProfile)){
            my_error_log("facebookUserProfile=".  backyard_dumpArrayAsOneLine($facebookUserProfile), 5, 16);
        } else {
            my_error_log("facebookUserProfile is not set", 5, 16);
        }
        //uvidíme//if(!isset($facebookUserProfile)) die_graciously ('403', 'Server lost connection to Facebook during an on-going session');
/*        if($facebookUserProfile){   my_error_log('Passed the line '.__LINE__,5,6);

           if(isset($_REQUEST['proceedfblogout'])){ //calling $facebook->getLogoutUrl only in case of actual logout prevents error: CSRF state token does not match one provided.
            //$nextUrl = removeqsvar(removeqsvar($apiCredentials['facebook']['redirectUri'], 'state'),'code');
            //if($nextUrl != $apiCredentials['facebook']['redirectUri']) movePage (302, $nextUrl);
            $params = array( 'next' => curPageURL(false).'?fblogout' );
            $apiCredentials['facebook']['logoutUrl'] = $facebook->getLogoutUrl($params); my_error_log('Passed the line '.__LINE__,5,6);
            movePage(302,$apiCredentials['facebook']['logoutUrl']);
            exit;
           }
            $apiCredentials['facebook']['logoutUrl']=curPageURL(false).'?proceedfblogout';
            $apiCredentials['facebook']['auth']=true;      
        }  my_error_log('Passed the line '.__LINE__,5,6); */
    } else {  my_error_log('Passed the line '.__LINE__,5,6);
        //$apiCredentials['facebook']['redirectUri'] = str_replace('&state=','?xstate=',str_replace('&code=','&xcode=',str_replace('?state=','?xstate=',$apiCredentials['facebook']['redirectUri'])));
       if(isset($_REQUEST['fbloginstart']) || parse_url(backyard_getCurPageURL(true),PHP_URL_SCHEME) == 'http'){
        $apiCredentials['facebook']['redirectUri'] = removeqsvar(removeqsvar(removeqsvar($apiCredentials['facebook']['redirectUri'], 'state'),'code'),'fbloginstart');
        $apiCredentials['facebook']['redirectUri'] = addqsvar($apiCredentials['facebook']['redirectUri'], 'fbloginproceed','1');//value 1 je jedno
        $params = array(
            'scope' => 'email',//'read_stream, friends_likes',
            'redirect_uri' => $apiCredentials['facebook']['redirectUri']
        );//If the $params array is not specified, or empty, basic permissions are requested, and the current URL is used as the redirect_uri.
        $apiCredentials['facebook']['loginUrl'] = $facebook->getLoginUrl($params);  my_error_log('Passed the line '.__LINE__,5,6);
        if(isset($_REQUEST['fbloginstart'])){
            //die("<a href='{$apiCredentials['facebook']['loginUrl']}'>go login</a>");
            backyard_movePage(302, $apiCredentials['facebook']['loginUrl']);
        }
       } else {
            $parsedUrl = parse_url(
                            addqsvar(  //magic-security vynucuje HTTPS, ale lze vynutit HTTP                    
                    addqsvar(addqsvar(backyard_getCurPageURL(true), 'fbloginstart', '1'),'fbloginproceedssl','2')
                 ,'fh', md5($_SERVER['REMOTE_ADDR'].date('W').'3PoOiaVCrj')  ) //magic-security vynucuje HTTPS, ale lze vynutit HTTP
                                    );
            
            $parsedUrl['scheme']='http'; $parsedUrl['port']=80;
            //print_r($parsedUrl);exit;
  //          echo "<br/>GET: ".nl2br(print_r($_GET,true));  
//            echo "Session id: ".session_id();echo "<br/>Session: ".nl2br(print_r($_SESSION,true));  
            //echo "<a href='".unparse_url($parsedUrl)."'>Go on fbloginproceed and ssl</a>";
           
           $apiCredentials['facebook']['loginUrl'] = unparse_url($parsedUrl);  my_error_log('Passed the line '.__LINE__,5,6);
           //movePage(302, $apiCredentials['facebook']['loginUrl']);
       }
    }// /else
    //if(session_id() && !$facebookUserProfile['id'])my_error_log ("session_id() is not empty while facebookUserProfile['id'] is empty - it seems than facebook library cannot communicate with Facebook",1);//@TODO 1 - opravit !$facebookUserProfile['id'] .. ať nehází Notice při nezalogovaném .. mělo by být vyřešeno na následujících dvou řádcích
//    $temp_fbid = isset($facebookUserProfile['id']);if($temp_fbid)$temp_fbid=!empty($facebookUserProfile['id']);
//    if(session_id() && !$temp_fbid)my_error_log ("session_id() is not empty while facebookUserProfile['id'] is empty - it seems than facebook library cannot communicate with Facebook",1);//@TODO 1 - opravit !$facebookUserProfile['id'] .. ať nehází Notice při nezalogovaném    
    //@TODO 1 - teď to hází chybu i když se člověk ani nepokusil zalogovat :( 130311
my_error_log('Passed the line '.__LINE__,5,6);
    /**
 * output is:
 * $facebook = object
 * $facebookUser = result of $facebook->getUser();
 * $facebookUserProfile = $facebook->api('/me');
     * $apiCredentials
 */
} my_error_log('End of fb auth: Passed the line '.__LINE__,5,6);

/* TEMPLATE
  if($apiCredentials['facebook']['auth']){
      $fbAuthMarkUp = "<a href='{$apiCredentials['facebook']['logoutUrl']}".(isset($_GET['ERROR_HACK'])?"&ERROR_HACK={$_GET['ERROR_HACK']}":"")."'  data-role='button' data-theme='f' data-icon='alert' class='fb-button'>".localisationString('facebook_logout', "Logout from Facebook")."</a>";//red button //@TODO 2 - class=fb-button
  } else {
        $fbAuthMarkUp = "<a href='{$apiCredentials['facebook']['loginUrl']}".(isset($_GET['ERROR_HACK'])?"&ERROR_HACK={$_GET['ERROR_HACK']}":"")."' data-role='button' class='fb-button' data-theme='c'>".localisationString('facebook_login', "Login with Facebook")."</a>";//@TODO 2 - class=fb-button      
  }      
 * 
 */

