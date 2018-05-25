<?php
error_log(__FILE__ . ' is obsolete - consider its rewriting');
//@TODO  compare with social.login.php working with MyReport
/**
 * Name: social.login.php
 * Project: LIB/Part of Library In Backyard
 * 
 * * 
 * Purpose: 
 * Plugin for social login through server side OAUTH services Facebook, Google+ and potentially others
 * 
 * * Output tohoto plugin:
 * $googleUserProfile = unset or array
 * $facebookUserProfile = unset or array
 * 
 * Vstupní volání:
 * require_once 'social.login.php';//@TODO 3 - přesunout do LIB
  $tempResult=socialLoginPseudoConstructor($ownerId, $ownerLanguage);//poslední použití $ownerLanguage
  $ownerId=$tempResult['internal_id'];
  $userLanguage=$tempResult['user_language'];
  .. s tím, že $ownerId, $ownerLanguage nebo $userLanguage jsou proměnné bez vazby na jmenný prostor social.login.php
 * 
 * Prerekvizity:
 * login_google.php
 * login_facebook.php
 * $apiCredentials v conf.php
 * $availableLanguages
 *     global $dbname,$tableNameOwners;
 * 
 *  tabulka $dbname kde $tableNameOwners je například `stakan_owners`
  CREATE TABLE IF NOT EXISTS `stakan_owners` (
  `owner_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `owner_email` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `owner_signature` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `owner_login` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `owner_notification` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `owner_language` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  `last_access` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type_id` int(11) NOT NULL,
  PRIMARY KEY (`owner_id`),
  KEY `type_id` (`type_id`),
  KEY `owner_notification` (`owner_notification`),
  FULLTEXT KEY `owner_login` (`owner_login`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

  kde zatím je nastaveno:
  //@TODO 3 - default je zapnout notifikace - k udělání jejich rozšířenou denní verzi
  //@TODO 2 - zatím natvrdo nastaveno type_id=2 .. později inteligentě rozhazovat dle zdrojů


 * 
 * 
 * * 
 * History
 * 2013-03-26, v.0.0.1, first draft from another project (Stakan)
 * 2013-03-26, v.0.0.2, function pseudoConstructor and made ready for MyReport
 * 2013-03-26, v.0.0.3, robustní vůči změně e-mailu v bázickém authVector + možnost zalogování s jiným social účtem na tu samou e-mail adresu
 * 2013-04-09, v.0.0.4, záznam i timezone při založení nebo last_access
 * 2013-05-03, v.0.1, my_error_log compression
 * 2013-05-10, v.0.2, setcookie
 * 2013-12-08, v.0.3, from stakan1 put into backyard
 * 2014-04-15, v.1.S.0, social.login Stakan přibližujeme k MyReport
 *
 * * 
 * TODO  
 * @TODO 2 - sjednotit stakan1/social.login a myreport/social.login a přesunout do LIB (a na GITHUB jako samostatný projekt)
 * 
 * ** Description
 * 130510: cookie muís vymazat logout tlačítko, např. onclick='document.cookie=\"logintimestamp=\";informAboutEid(68);'
 * 
 */
if (!defined('__BACKYARDROOT__')){
    die('backyard must be initialized beforehand');
//require_once ("../lib/functions.php"); //require the basic LIB library; all other LIB components to be included by require_once (__ROOT__."/lib/XXX.php");//@TODO - jinak, aby univerzálně
}

if (!isset($apiCredentials)){
    die('apiCredentials must be preinitialized');
//require_once ("conf.php");//configures $apiCredentials //@TODO - specify here what must be configured there
}

/* * *****************************************************************************
 *  Social login  //@TODO 3 - standardize this into LIB
 */
if (!isset($_REQUEST['fbloginproceed']))
    require_once (__BACKYARDROOT__ . "/login_google.php"); //@TODO 2 .. social.login pak bude v LIB, tak cesta bude ten samý adresář, ovšem conf.php v LIB bude cesta ke google-library
if (!isset($_GET['code']) || isset($_GET['state']))
    require_once (__BACKYARDROOT__ . "/login_facebook.php"); //@TODO 2 .. social.login pak bude v LIB, tak cesta bude ten samý adresář, ovšem conf.php v LIB bude cesta k facebook-library
if ($apiCredentials['facebook']['auth'])
    my_error_log("$facebookUserProfile: " . backyard_dumpArrayAsOneLine($facebookUserProfile), 5, 16); //debug
if ($apiCredentials['google']['auth'])
    my_error_log("$googleUserProfile: " . backyard_dumpArrayAsOneLine($googleUserProfile), 5, 16);    //debug
/**
 *  /Social login 
 * Output tohoto plugin:
 * $googleUserProfile = unset or array
 * $facebookUserProfile = unset or array
 */

function socialLoginPseudoConstructor($internalId, $userLanguage) {
    if (!($internalId && $userLanguage)) {
        $tempResult = getInternalUserId(); //je to jediné volání této fce
        if (!$internalId)
            $internalId = $tempResult['user_id']; //pokud nebylo správně hack_owner_id, tak $ownerId z databáze dle social login
        if (!$userLanguage)
            $userLanguage = $tempResult['user_language']; //_REQUEST['owner_language'] overrides what is set in the database    
    } //else keep $ownerId && $ownerLanguage previously defined    
    $userLanguage = getInternalUserLanguage($userLanguage, $internalId); //je to jediné volání této fce//@TODO 2 - stejně arbitrárně pojmenované jako ve výkoném kódu require_once 'social.login.php'; ... asi volat zároveň
    return array('user_language' => $userLanguage, 'internal_id' => $internalId);
}

/* * *****************************************************************************
 *  Setting user language 
 */

function getInternalUserLanguage($ownerLanguage, $ownerId) {
    global $availableLanguages, $availableLanguageFallback, $facebookUserProfile, //@TODO 2 - nehodí to chybu pokud $fb nebo $gl nebude definován??
    //$dbname,
    $tableNameOwners,
    $backyardDatabase;

    $userLanguage = false;
    if ($ownerLanguage) {
        if (in_array($ownerLanguage, $availableLanguages)) {
            $userLanguage = $ownerLanguage;
        }
    }
    my_error_log("After ownerLanguage: userLanguage = {$userLanguage}", 5, 16);
    if (!$userLanguage && $ownerId) {//use owner preference ; Note: $ownerId is always set - either false or number
        $query = "SELECT `owner_language` FROM `{$backyardDatabase['dbname']}`.`{$tableNameOwners}` WHERE `owner_id` = {$ownerId}";
        $mysqlQueryResultArray = backyard_mysqlQueryArray($query, true);
        if ($mysqlQueryResultArray && $mysqlQueryResultArray['owner_language'] != '') {
            $userLanguage = $mysqlQueryResultArray['owner_language'];
            if (!in_array($userLanguage, $availableLanguages)) {
                $userLanguage = false;
            }
        }
    }
    my_error_log("After ownerId: userLanguage = {$userLanguage}", 5, 16);
    if (!$userLanguage && isset($facebookUserProfile['locale'])) {//or use owner preference in Facebook
        //if (in_array(($tempLocale = strtok($user_profile['locale'], '_')), $availableLanguages)) {
        if (in_array(($tempLocale = strtok($facebookUserProfile['locale'], '_')), $availableLanguages)) {
            $userLanguage = $tempLocale;
        }
    }
    my_error_log("After fb user_profile: userLanguage = {$userLanguage}", 5, 16);
//debug//print_r(Language::getBestMatch(array('en', 'cs')));exit;
    if (!$userLanguage) {//or use info in browser
        $tempLocale = Language::getBestMatch($availableLanguages);
        $userLanguage = (is_null($tempLocale)) ? $availableLanguageFallback : $tempLocale;
    }
    my_error_log("After _SERVER['HTTP_ACCEPT_LANGUAGE'] best match: userLanguage = {$userLanguage}", 5, 16);

    return $userLanguage;
}

//ponechat tam//include_once "lang_{$userLanguage}.php";//@TODO 4 - action=fb_login může změnit jazyk, pokud má uživatel jiný jazyk ve stakan než v fb; tak část může být jiným jazykem, protože v action fb_login renegociuji jayzk, ale až tam
/**
 *  /Setting user language 
 */
function getInternalUserId() {
    global $apiCredentials, $facebookUserProfile, $googleUserProfile; //@TODO 2 - nehodí to chybu pokud $fb nebo $gl nebude definován??
    $authId = false;
    //$userLanguage = '';
    //$userId=false;
    if ($apiCredentials['facebook']['auth']) {
        //rozhodnutí, zda rovnou přesměrovat: //@TODO 2 - takový filtr i u všech ostatních stránek //@TODO 2 - také fb_login provést vnitro-PHP výpočtem
        $authType = 'fb';
        $authName = $facebookUserProfile['name'];
        $authMail = $facebookUserProfile['email'];
        $authId = $facebookUserProfile['id'];
        //@TODO 2 - vylepšit security 
    } elseif ($apiCredentials['google']['auth']) {
        $authType = 'gl';
        $authName = $googleUserProfile['name']; //['me']['displayName'];
        $authMail = $googleUserProfile['email']; //['user']['email'];
        //$authId=$googleUserProfile['me']['id'];
        $authId = $googleUserProfile['user']['id']; // $googleUserProfile['me']['id']; je aplikovatelné jen když je definované G+
    } //@TODO 2 - nějak zde předat fb a gl id - které je tím správným identifikátorem!
    my_error_log("authId: " . backyard_dumpArrayAsOneLine($authId), 5);
    if ($authId) {
        //return( externalLogin($authType, $authId, $authName, $authMail) );

        global $availableLanguages, $userLanguage,
        //$dbname, 
        $tableNameOwners, $ownerId;
        global $backyardDatabase;
        //@TODO 2 - bacha na to, že při změně jména či mailu ve fb se nezaloguje --- změnit na na pozadí a přes fb_id
        global $timezone;
        $timezone = mysql_real_escape_string($timezone);
        $authVector = array('auth_type' => $authType,
            'auth_id' => $authId,
            'name' => $authName, 'mail' => $authMail);
        $authString = serialize($authVector);
        //TADY DAT SOFISTIKOVANEJSI LOGIKU
        if (!isset($ownerId))
            $ownerId = false; //@TODO 2 - aby nespoléhalo na $ownerId global
        $query = "SELECT * FROM `{$backyardDatabase['dbname']}`.`$tableNameOwners` WHERE (`owner_login` LIKE '%{$authType}%' AND `owner_login` LIKE '%{$authId}%') OR `owner_login` LIKE '%{$authMail}%'"; //výběr na hrubo
        $mysqlQueryResultArrayMoreLines = backyard_mysqlQueryArray($query);
        //@TODO - skonci pokud prazdny
        if ($mysqlQueryResultArrayMoreLines) {//zjistovat jen pokud neprazdny vysledek
            my_error_log("mysqlQueryResultArrayMoreLines=" . backyard_dumpArrayAsOneLine($mysqlQueryResultArrayMoreLines), 5, 16);
            $mysqlQueryResultArrayOwnerLogins = backyard_getOneColumnFromArray($mysqlQueryResultArrayMoreLines, 'owner_login');
            my_error_log("mysqlQueryResultArrayOwnerLogins=" . backyard_dumpArrayAsOneLine($mysqlQueryResultArrayOwnerLogins), 5, 16);
            foreach ($mysqlQueryResultArrayOwnerLogins as $keyLogin => $valueLogin) {
                $valueLoginArray = unserialize($valueLogin);
                my_error_log("valueLoginArray=" . backyard_dumpArrayAsOneLine($valueLoginArray), 5, 16);
                if (isset($valueLoginArray['auth_type'])) {//it is an array with just one authVector
                    //emulate two level array
                    $tempArray = $valueLoginArray;
                    $valueLoginArray = array();
                    $valueLoginArray[0] = $tempArray;
                }
                my_error_log("valueLoginArray=" . backyard_dumpArrayAsOneLine($valueLoginArray), 5, 16);
                foreach ($valueLoginArray as $keyLoginVector => $valueLoginVector) {
                    my_error_log("valueLoginVector=" . backyard_dumpArrayAsOneLine($valueLoginVector), 5, 16);
                    if ($valueLoginVector['auth_type'] == $authType && $valueLoginVector['auth_id'] == $authId) {//if previously logged in with same social service
                        if ($valueLoginVector['mail'] != $authMail) {
                            //@TODO 3 - upozornit oba maily, že došlo ke změně
                            //@TODO 3 - updatovat mail
                            my_error_log("Zmena mailu pro type={$authType} id={$authId} z={$authMail} na={$valueLoginVector['mail']}", 2, 13);
                        }
                        $ownerId = $mysqlQueryResultArrayMoreLines[$keyLogin]['owner_id'];
                        break 2; //exit both foreach
                    }
                    if ($valueLoginVector['mail'] == $authMail) {//if previously logged in with another account of the same social service which sports the same e-mail or previously logged in with another social service
                        $ownerId = $mysqlQueryResultArrayMoreLines[$keyLogin]['owner_id'];
                        my_error_log("Use type={$authType} id={$authId} mail={$authMail} to login as type={$valueLoginVector['auth_type']} id={$valueLoginVector['auth_id']} mail={$valueLoginVector['mail']}", 3, 13);
                        //@TODO 2 - add this identity=authVector to this account, tj. vytvořit víceúrovňové login vectory
                        //@TODO 3 - až poté, co to z mailu uživatel odsouhlasí??                
                        break 2; //exit both foreach                
                    }
                }
            }
        } else {
            //$ownerId zustane nezmeneno vuci global or false
            my_error_log("No similar authVector", 4);
        }

        if ($ownerId) {
            //login
            my_error_log("{$authType} login for ownerId={$ownerId}", 5, 10);
            setcookie('logintype', $authType, time() + 60 * 60 * 24 * 30); //130510 pro autologin ve standalone; plus trvá 30 dnů, aby ukazovala preferenci//@TODO 3 - funguje dle timezone?
            setcookie('logintimestamp', time(), time() + 3600); //130510 pro autologin ve standalone//@TODO 3 - funguje dle timezone?
            //if(strtotime($mysqlQueryResultArrayMoreLines[$keyLogin]['last_access']) <= strtotime("-15 minutes")){
            if (strtotime('now') - strtotime($mysqlQueryResultArrayMoreLines[$keyLogin]['last_access']) > 15 * 60) {//@TODO 2 - ověřit, zda počítá správně více jak 15 minut
                my_error_log("ownerId={$ownerId} revisited", 5);
                $query = "UPDATE `{$backyardDatabase['dbname']}`.`$tableNameOwners` SET `last_access` = CURRENT_TIMESTAMP, `olson_timezone` = '{$timezone}' WHERE `$tableNameOwners`.`owner_id` = {$ownerId};";
                $tempUpdateQueryResult = backyard_mysql_query($query) or backyard_dieGraciously('E137', "{$query}"); // End script with a specific error message if mysql query fails                     
            } else {//debug
                my_error_log("ownerId={$ownerId} sessioning from " . date("Y-m-d H:i:s", strtotime($mysqlQueryResultArrayMoreLines[$keyLogin]['last_access'])) . " till " . date("Y-m-d H:i:s", strtotime('now')), 5);
            }
        } else {
            //create
            $ownerId = findFirstAvailableIdInRelevantTable($tableNameOwners, $ownerId, 'owner_id');
            $query = "INSERT INTO `{$backyardDatabase['dbname']}`.`$tableNameOwners` "
                    . "(`owner_id`, `owner_name`, `owner_email`, `owner_signature`, `owner_login`, `owner_notification`, `created`, `type_id`, `olson_timezone`) " //@TODO 4 - owner_language nastavit dle prvního přístupu, ať se uživateli nemění dle použitého browseru - ale $userLanguage ještě není nastaven, tak by se muselo promísit zalogování a nastavení jazyka
                    . "VALUES ({$ownerId}, '{$authName}', '{$authMail}', '{$authName}', '{$authString}' , '3', CURRENT_TIMESTAMP, 2, '{$timezone}');";
            ////@TODO 3 - default je zapnout notifikace - k udělání jejich rozšířenou denní verzi//130409 - default notifikací je denní summary=3
            //@TODO 2 - zatím natvrdo nastaveno type_id=2 .. později inteligentě rozhazovat dle zdrojů
            $mysql_query_result = backyard_mysql_query($query) or backyard_dieGraciously('E131', "{$query}"); // End script with a specific error message if mysql query fails                     

            my_error_log("{$authType} create {$authVector['name']} {$authVector['mail']}", 4, 10);
        }
        //renegotiate the userLanguage //@TODO 4 - zoptimalizovat dotaz, protože (a) login už query provedl a (b) create owner_language nenastavuje, resp. víme jak
        $query = "SELECT `owner_language` FROM `{$backyardDatabase['dbname']}`.`$tableNameOwners` WHERE `owner_id` = {$ownerId}";
        $mysqlQueryResultArray = backyard_mysqlQueryArray($query, true);
        if ($mysqlQueryResultArray && $mysqlQueryResultArray['owner_language'] != '') {
            if (($userLanguage != $mysqlQueryResultArray['owner_language']) && (in_array($mysqlQueryResultArray['owner_language'], $availableLanguages))) {
                $userLanguage = $mysqlQueryResultArray['owner_language'];
                my_error_log("After {$authType}_login renegotiation: userLanguage = {$userLanguage}", 5, 16);
                //include_once "lang_{$userLanguage}.php";//@TODO 4 - action=fb_login může změnit jazyk, pokud má uživatel jiný jazyk ve stakan než v fb; tak část může být jiným jazykem, protože v action fb_login renegociuji jayzk, ale až tam            
                //@TODO 2 - nemá být include_lang až pozdeji?
                //!!include_once lang v externalLogin neudělá globální proměnnou!
            } //if there is nonsense in owner_language field, $userLanguage remains empty (and is filled in later, e.g. After ownerId: userLanguage =; After fb user_profile: userLanguage = en ; the same result is fed into language option in settings)
        }
        my_error_log("user_language = {$userLanguage}, user_id = {$ownerId}", 5);
        return array('user_language' => $userLanguage, 'user_id' => $ownerId);
    } else {
        return array('user_language' => '', 'user_id' => false); //default
    }
}

/**
 * further usage
 * if(!$userLanguage && isset($facebookUserProfile['locale'])){//or use owner preference in Facebook
  if(isset($apiCredentials['google']['auth'])){
  if(isset($apiCredentials['facebook']['auth'])){
 */
/*
  function externalLogin ($authType,$authId,$authName,$authMail){
  global $availableLanguages, $userLanguage, $dbname, $tableNameOwners, $ownerId;
  //@TODO 2 - bacha na to, že při změně jména či mailu ve fb se nezaloguje --- změnit na na pozadí a přes fb_id
  $authVector = array('auth_type' => $authType,
  'auth_id' => $authId,
  'name' => $authName, 'mail' => $authMail);
  $authString = serialize ($authVector);
  $query = "SELECT * FROM  `$dbname`.`$tableNameOwners` WHERE `owner_login` LIKE '{$authString}'";
  $mysqlQueryResultArray = customMySQLQuery($query,true);
  if ($mysqlQueryResultArray){
  //login
  $ownerId = $mysqlQueryResultArray['owner_id'];
  my_error_log("{$authType} login for ownerId={$ownerId}", 5, 10);

  if(strtotime($mysqlQueryResultArray['last_access']) <= strtotime("-15 minutes")){
  my_error_log("ownerId={$ownerId} revisited", 5);
  $query = "UPDATE `$dbname`.`$tableNameOwners` SET `last_access` = CURRENT_TIMESTAMP WHERE `$tableNameOwners`.`owner_id` = {$ownerId};";
  $tempUpdateQueryResult = make_mysql_query($query) or die_graciously('E137',"{$query}"); // End script with a specific error message if mysql query fails
  } else {//debug
  my_error_log("ownerId={$ownerId} sessioning", 5);
  }
  } else {
  //create
  $ownerId = findFirstAvailableIdInRelevantTable($tableNameOwners, $ownerId, 'owner_id');
  $query = "INSERT INTO `$dbname`.`$tableNameOwners` (`owner_id`, `owner_name`, `owner_email`, `owner_signature`, `owner_login`, `owner_notification`, `created`, `type_id`) " //@TODO 4 - owner_language nastavit dle prvního přístupu, ať se uživateli nemění dle použitého browseru - ale $userLanguage ještě není nastaven, tak by se muselo promísit zalogování a nastavení jazyka
  ."VALUES ({$ownerId}, '{$authName}', '{$authMail}', '{$authName}', '{$authString}' , 'on', CURRENT_TIMESTAMP, 2);";
  ////@TODO 3 - default je zapnout notifikace - k udělání jejich rozšířenou denní verzi
  //@TODO 2 - zatím natvrdo nastaveno type_id=2 .. později inteligentě rozhazovat dle zdrojů
  $mysql_query_result=make_mysql_query($query) or die_graciously('E131',"{$query}"); // End script with a specific error message if mysql query fails

  my_error_log("{$authType} create {$authVector['name']} {$authVector['mail']}", 4, 10);
  }
  //renegotiate the userLanguage //@TODO 4 - zoptimalizovat dotaz, protože (a) login už query provedl a (b) create owner_language nenastavuje, resp. víme jak
  $query = "SELECT `owner_language` FROM `$dbname`.`$tableNameOwners` WHERE `owner_id` = {$ownerId}";
  $mysqlQueryResultArray = customMySQLQuery($query,true);
  if($mysqlQueryResultArray && $mysqlQueryResultArray['owner_language']!=''){
  if (($userLanguage!=$mysqlQueryResultArray['owner_language']) && (in_array($mysqlQueryResultArray['owner_language'], $availableLanguages))) {
  $userLanguage = $mysqlQueryResultArray['owner_language'];
  my_error_log("After {$authType}_login renegotiation: userLanguage = {$userLanguage}",5,16);
  //include_once "lang_{$userLanguage}.php";//@TODO 4 - action=fb_login může změnit jazyk, pokud má uživatel jiný jazyk ve stakan než v fb; tak část může být jiným jazykem, protože v action fb_login renegociuji jayzk, ale až tam
  //@TODO 2 - nemá být include_lang až pozdeji?
  //!!include_once lang v externalLogin neudělá globální proměnnou!
  }
  }
  my_error_log("user_language = {$userLanguage}, user_id = {$ownerId}",5);
  return array('user_language' => $userLanguage, 'user_id' => $ownerId);
  }
 */

//FUNCTION GENERAL
// http://www.dzone.com/snippets/detect-user-preferred-language
//+ Jonas Raoni Soares Silva
//@ http://jsfromhell.com
class Language {

    private static $language = null;

    public static function get() {
        new Language;
        return self::$language;
    }

    public static function getBestMatch($langs = array()) {
        foreach ($langs as $n => $v)
            $langs[$n] = strtolower($v);
        $r = array();
        foreach (self::get() as $l => $v) {
            ($s = strtok($l, '-')) != $l && $r[$s] = 0;
            if (in_array($l, $langs))
                return $l;
        }
        foreach ($r as $l => $v)
            if (in_array($l, $langs))
                return $l;
        return null;
    }

    private function __construct() {
        if (self::$language !== null)
            return;
        if (($list = strtolower((isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? ($_SERVER["HTTP_ACCEPT_LANGUAGE"]) : (''))))) {//120908, když chybí
            if (preg_match_all('/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/', $list, $list)) {
                self::$language = array_combine($list[1], $list[2]);
                foreach (self::$language as $n => $v)
                    self::$language[$n] = +$v ? +$v : 1;
                arsort(self::$language);
            }
        } else
            self::$language = array();
    }

}

//print_r(Language::get()); //languages ordered by preference
//print_r(Language::getBestMatch(array('pt-br', 'pt', 'en'))); //retrieves the best match given a list of available languages

if (!function_exists('findFirstAvailableIdInRelevantTable')) {
    my_error_log('defining findFirstAvailableIdInRelevantTable conditionally', 3); //@TODO 2 - anebo move jako obecnou funkci do functions??

    function findFirstAvailableIdInRelevantTable($table, $ownerId, $relevantMetric) {
        global
        //$dbname
        $backyardDatabase
        ;
        $result = 1; //default value
        $query = "SELECT `{$relevantMetric}` FROM  `{$backyardDatabase['dbname']}`.`{$table}` "
                . (($relevantMetric == 'owner_id') ? ("") : ("WHERE  `owner_id` ={$ownerId} "))
                . " ORDER BY `{$relevantMetric}` DESC LIMIT 0 , 1;";
        $mysql_query_result = backyard_mysql_query($query) or backyard_dieGraciously('E106', "$query"); // End script with a specific error message if mysql query fails
        //transforming the query result into an array            
        if (mysql_num_rows($mysql_query_result) > 0) {
            $one_row = mysql_fetch_array($mysql_query_result, MYSQL_ASSOC);
            $result += $one_row["$relevantMetric"];
        }
        return $result;
    }

}