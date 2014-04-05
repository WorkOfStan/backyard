<?php

/**
 * Writes information to be logged by application to its own log (common to the whole host by default)
 * 
 * @global string $username
 * @global type $logging_level
 * @global array $level_name
 * @global type $logging_file
 * @global type $page_timestamp
 * @global type $logging_level_page_speed
 * @global type $RUNNING_TIME
 * @global type $ERROR_HACK
 * @global int $libErrorLogMessageType
 * @param string $message - zpráva k vypsání - při použití error_number bude obsahovat doplňující info
 * @param type $level - úroveň chyby
 * @param type $error_number - číslo chyby, dle které lze chybu vyhodnotit .. bude zapsaná v admin návodu apod. - zatím nepoužito
 * @return type
 * 
 * 
 * ****** ERROR NUMBER LIST **********
 *  0 Unspecified
 *  1 Reserved
 *  2 Reserved
 *  3 Reserved
 *  4 Reserved
 *  5 Reserved
 *  6 Speed
 *  7 Reserved
 *  8 Reserved
 *  9 Reserved
 *  10 Authentization
 *  11 MySQL
 *  12 Domain name
 *  13 Tampered URL or ID
 *  14 Improve this functionality
 *  15 Page was refreshed with the same URL therefore action imposed by URL is ignored
 *  16 Logging values
 *  17 Missing input value
 *  18 Setting of a system value
 *  19 Redirecting
 *  20 Facebook API
 *  21 HTTP communication
 *  22 E-mail
 *  23 Algorithm flow
 *  24 Third party API
 *    
 *  1001 Establish correct error_number
 */
function my_error_log($message, $level = 0, $error_number = 0) {// error_log() modified to log necessary debug information
    //mozna by stalo za to prepsat i jmeno te puvodni, aby se treba i sphpblog psal tam, kde to vidim
    //mohla by být zavedena čtvrtá vstupní proměnná $line=''
    //$line - mělo by být vždy voláno jako basename(__FILE__)."#".__LINE__ , takže bude jasné, ze které řádky source souboru to bylo voláno
    // Ve výsledku do logu zapíše:
    //[Timestamp: d-M-Y H:i:s] [Logging level] [$error_number] [$_SERVER['SCRIPT_FILENAME']] [username@gethostbyaddr($_SERVER['REMOTE_ADDR'])] [sec od startu stránky] $message
    global $username; //Až zavedu uživatele, tak se tam budou zapisovat. (do my_error_log)
    $monthly_rotation = true; //true, pokud má být přípona .log.Y-m.log (výhodou je měsíční rotace); false, pokud má být jen .log (výhodou je sekvenční zápis chyb přes my_error_log a jiných PHP chyb)
    $standardoutput = false; //true, pokud má zároveň vypisovat na obrazovku; false, pokud má vypisovat jen do logu
    //$standardoutput = true;//debug

    global $logging_level, $level_name, $logging_file, $page_timestamp, $logging_level_page_speed, $RUNNING_TIME, $ERROR_HACK, $libErrorLogMessageType,$libMailForAdminEnabled;

    $PROFILING_STEP = false; //110812, my_error_log neprofiluje rychlost
    //$PROFILING_STEP = 0.008;//110812, my_error_log profiluje čas mezi dvěma měřenými body vyšší než udaná hodnota sec  

    $result = true; //pripadne by mohlo byt resetovano pri volani error_log na false
    if (isset($_GET['ERROR_HACK']) && $_GET['ERROR_HACK'] != "" && is_numeric($_GET['ERROR_HACK'])) {//přidat ještě podmínku povolení z db
        $standardoutput = true;
        if ($_GET['ERROR_HACK'] > $logging_level) {
            $logging_level = $_GET['ERROR_HACK'];
        }
    }
    if ($ERROR_HACK > $logging_level) $logging_level = $ERROR_HACK; //120918
        
    //gethostbyaddr($_SERVER['REMOTE_ADDR'])// co udělá s IP, která nelze přeložit? nebylo by lepší logovat přímo IP?

    if (($level <= $logging_level) //logovat 0=unknown/default 1=fatal 2=error 3=warning 4=info 5=debug 6=speed dle $level
            //|| ($level == "6") //speed logovat vždy když je ukázaná
            || (($error_number == "6") && ($logging_level_page_speed <= $logging_level)) //speed logovat vždy když je ukázaná, resp. dle nastavení $logging_level_page_speed
    ) {
        $RUNNING_TIME_PREVIOUS = $RUNNING_TIME;
        //$RUNNING_TIME=round( getmicrotime() - $page_timestamp, 4 );
        //if ($PROFILING_STEP && (($RUNNING_TIME-$RUNNING_TIME_PREVIOUS) > $PROFILING_STEP )) {
        if ((( ($RUNNING_TIME = round(getmicrotime() - $page_timestamp, 4)) - $RUNNING_TIME_PREVIOUS) > $PROFILING_STEP ) && $PROFILING_STEP) {
            $message = "SLOWSTEP " . $message; //110812, PROFILING
        }

        if ($standardoutput)
            echo ((($level <= 2) ? "<b>" : "") . "{$message} [{$RUNNING_TIME}]" . (($level <= 2) ? "</b>" : "") . "<hr/>" . PHP_EOL); //110811, if fatal or error then bold//111119, RUNNING_TIME

        $message_prefix = "[" . date("d-M-Y H:i:s") . "] [" . $level_name[$level] . "] [" . $error_number . "] [" . $_SERVER['SCRIPT_FILENAME'] . "] [" . $username . "@" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "] [" . $RUNNING_TIME . "] [" . $_SERVER["REQUEST_URI"] . "] ";
        if (!$logging_file) {//$logging_file není inicializován
            $result = error_log($message_prefix . "(error: logging_file not set!) $message"); //zapisuje do default souboru
            //zaroven by mohlo poslat mail nebo tak neco .. vypis na obrazovku je asi az krajni reseni
        } else {
            $messageType = 3;
            if ($libErrorLogMessageType == 0)
                $messageType = $libErrorLogMessageType;
            if ($monthly_rotation) {
                //echo ("m_r is true");//debug
                $result = error_log($message_prefix . "$message" . (($messageType != 0) ? (PHP_EOL) : ('')), $messageType, "$logging_file" . "." . date("Y-m") . ".log"); //zapisuje do souboru, který rotuje po měsíci
            } else {
                //echo ("m_r is false");//debug
                $result = error_log($message_prefix . "$message\r\n", $messageType, "$logging_file"); //zapisuje do souboru
            }
        }
        if($level == 1 && $libMailForAdminEnabled){//mailto admin, 130108
          $resultMail = error_log($message_prefix . "$message\r\n", 1, $libMailForAdminEnabled);
        }
    }
    /* Alternative way:
      Logging levels
      Log level   Description                                                                       Set bit
      Warning     Identifies critical errors.                                                       None required
      Debug       Provides additional information for programmers and Technical Product Support.    0 (zero)
      Information Provides information on the health of the system.                                 1
      Trace       Provides detailed information on the execution of the code.                       2

      Log Mask values and logging levels
      LogMask   Bit value Messages included
      0         00000000  Warnings
      1         00000001  Warnings and Debug
      2         00000010  Warnings and Information
      3         00000011  Warnings, Debug and Information
      4         00000100  Warnings and Trace
      7         00000111  Warnings, Debug, Information and Trace
     */

    return $result;
}

