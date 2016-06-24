<?php
namespace GodsDev\Backyard;
//@todo SHOULDN'T IT BE GodsDev\Backyard\Json ?


class BackyardErrorLog {

/**
 * Error_log() modified to log necessary debug information by application to its own log (common to the whole host by default).
 * 
 * <b>ERROR NUMBER LIST</b>
 *  0 Unspecified<br/>
 *  1-5 Reserved
 *  6 Speed<br/>
 *  7-9 Reserved<br/>
 *  10 Authentization<br/>
 *  11 MySQL<br/>
 *  12 Domain name<br/>
 *  13 Tampered URL or ID<br/>
 *  14 Improve this functionality<br/>
 *  15 Page was refreshed with the same URL therefore action imposed by URL is ignored<br/>
 *  16 Logging values<br/>
 *  17 Missing input value<br/>
 *  18 Setting of a system value<br/>
 *  19 Redirecting<br/>
 *  20 Facebook API<br/>
 *  21 HTTP communication<br/>
 *  22 E-mail<br/>
 *  23 Algorithm flow<br/>
 *  24 Third party API<br/>
 *  1001 Establish correct error_number
 * 
 * @global float $backyardPage_timestamp
 * @global float $RUNNING_TIME
 * @global int $ERROR_HACK
 * @global array $backyardConf
 * 
 * @param string $message Zpráva k vypsání - při použití error_number bude obsahovat doplňující info
 * @param int $level Úroveň chyby
 * @param int $error_number Číslo chyby, dle které lze chybu vyhodnotit .. bude zapsaná v admin návodu apod. - zatím nepoužito
 * @return bool
 * 
 * 
 */
public function my_error_log($message, $level = 0, $error_number = 0) {
    //mozna by stalo za to prepsat i jmeno te puvodni, aby se treba i sphpblog psal tam, kde to vidim
    //mohla by být zavedena čtvrtá vstupní proměnná $line=''
    //$line - mělo by být vždy voláno jako basename(__FILE__)."#".__LINE__ , takže bude jasné, ze které řádky source souboru to bylo voláno
    // Ve výsledku do logu zapíše:
    //[Timestamp: d-M-Y H:i:s] [Logging level] [$error_number] [$_SERVER['SCRIPT_FILENAME']] [username@gethostbyaddr($_SERVER['REMOTE_ADDR'])] [sec od startu stránky] $message
    global
    //$username,                  //Až zavedu uživatele, tak se budou zapisovat do my_error_log
    $backyardPage_timestamp,
    $RUNNING_TIME,
    $ERROR_HACK,
    $backyardConf
    ;
    $username = 'anonymous'; //placeholder

    $result = true; //pripadne by mohlo byt resetovano pri volani error_log na false
    //if ($ERROR_HACK > $backyardConf['logging_level']){//$ERROR_HACK may be set anytime in the code
    //    $backyardConf['logging_level'] = $ERROR_HACK; //120918
    //}

    if (($level <= max(array(
                $backyardConf['logging_level'],
                $backyardConf['error_hack_from_get'], //set potentially as GET parameter
                $ERROR_HACK, //set as variable in the application script
            ))
            ) //logovat 0=unknown/default 1=fatal 2=error 3=warning 4=info 5=debug 6=speed dle $level
            || (($error_number == "6") && ($backyardConf['logging_level_page_speed'] <= $backyardConf['logging_level'])) //speed logovat vždy když je ukázaná, resp. dle nastavení $logging_level_page_speed
    ) {
        $RUNNING_TIME_PREVIOUS = $RUNNING_TIME;
        if ((( ($RUNNING_TIME = round(backyard_getmicrotime() - $backyardPage_timestamp, 4)) - $RUNNING_TIME_PREVIOUS) > $backyardConf['log_profiling_step'] ) && $backyardConf['log_profiling_step']) {
            $message = "SLOWSTEP " . $message; //110812, PROFILING
        }

        if ($backyardConf['log_standard_output']) {
            echo ((($level <= 2) ? "<b>" : "") . "{$message} [{$RUNNING_TIME}]" . (($level <= 2) ? "</b>" : "") . "<hr/>" . PHP_EOL); //110811, if fatal or error then bold//111119, RUNNING_TIME
        }

        $message_prefix = "[" . date("d-M-Y H:i:s") . "] [" . $backyardConf['logging_level_name'][$level] . "] [" . $error_number . "] [" . $_SERVER['SCRIPT_FILENAME'] . "] ["
                . $username . "@" 
                . (isset($_SERVER['REMOTE_ADDR'])?gethostbyaddr($_SERVER['REMOTE_ADDR']):'-')//phpunit test does not set REMOTE_ADDR
                . "] [" . $RUNNING_TIME . "] [" 
                . (isset($_SERVER["REQUEST_URI"])?$_SERVER["REQUEST_URI"]:'-')//phpunit test does not set REQUEST_URI
                . "] ";
        //gethostbyaddr($_SERVER['REMOTE_ADDR'])// co udělá s IP, která nelze přeložit? nebylo by lepší logovat přímo IP?
        if (($backyardConf['error_log_message_type'] == 3) && !$backyardConf['logging_file']) {//$logging_file not set and it should be
            $result = error_log($message_prefix . "(error: logging_file should be set!) $message"); //zapisuje do default souboru
            //zaroven by mohlo poslat mail nebo tak neco .. vypis na obrazovku je asi az krajni reseni
        } else {
            $messageType = 3;
            if ($backyardConf['error_log_message_type'] == 0) {
                $messageType = $backyardConf['error_log_message_type'];
            }
            if ($backyardConf['log_monthly_rotation']) {
                $result = error_log("{$message_prefix}{$message}" . (($messageType != 0) ? (PHP_EOL) : ('')), $messageType, "{$backyardConf['logging_file']}." . date("Y-m") . ".log"); //zapisuje do souboru, který rotuje po měsíci
            } else {
                $result = error_log("{$message_prefix}{$message}\r\n", $messageType, "{$backyardConf['logging_file']}"); //zapisuje do souboru
            }
        }
        if ($level == 1 && $backyardConf['mail_for_admin_enabled']) {//mailto admin, 130108
            error_log($message_prefix . "$message\r\n", 1, $backyardConf['mail_for_admin_enabled']);
        }
    }
    return $result;
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
}