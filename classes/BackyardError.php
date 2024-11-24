<?php

namespace WorkOfStan\Backyard;

use Psr\Log\LoggerInterface;
use Seablast\Logger\ErrorLogFailureException;
use Seablast\Logger\Logger;
use Seablast\Logger\LoggerTime;

/**
 * BackyardError wraps \Seablast\Logger\Logger implementation.
 * However adds method dieGraciously.
 */
class BackyardError extends Logger implements LoggerInterface
{
    /** @var array<mixed> int,string,bool,array */
    protected $backyardConf = array();
    /** @var LoggerTime */
    protected $backyardTime;

    /**
     *
     * @param array<mixed> $backyardConfConstruct
     * @param LoggerTime $backyardTime
     */
    public function __construct(array $backyardConfConstruct = array(), LoggerTime $backyardTime = null)
    {
        $this->backyardTime = ($backyardTime === null) ? (new LoggerTime()) : $backyardTime;
        // phpcs:disable Generic.Files.LineLength
        $this->backyardConf = array_merge(
            array(//default values
                'logging_level' => 5, //log up to the level set here, default=5 = debug//logovat az do urovne zde uvedene: 0=unknown/default_call 1=fatal 2=error 3=warning 4=info 5=debug/default_setting 6=speed  //aby se zalogovala alespoň missing db musí být logování nejníže defaultně na 1 //1 as default for writing the missing db at least to the standard ErrorLog
                'logging_level_name' => array(0 => 'unknown', 1 => 'fatal', 'error', 'warning', 'info', 'debug', 'speed'),
                'logging_file' => '', //soubor, do kterého má my_error_log() zapisovat
                'logging_level_page_speed' => 5, //úroveň logování, do které má být zapisována rychlost vygenerování stránky
                'error_log_message_type' => 0, //parameter message_type http://cz2.php.net/manual/en/function.error-log.php for my_error_log; default is 0, i.e. to send message to PHP's system logger; recommended is however 3, i.e. append to the file destination set either in field $this->BackyardConf['logging_file or in table system
                'die_graciously_verbose' => true, //show details by die_graciously() on screen (it is always in the error_log); on production it is recomended to be set to to false due security
                'mail_for_admin_enabled' => false, //fatal error may just be written in log //$backyardMailForAdminEnabled = "rejthar@gods.cz";//on production, it is however recommended to set an e-mail, where to announce fatal errors
                'log_monthly_rotation' => true, //true, pokud má být přípona .log.Y-m.log (výhodou je měsíční rotace); false, pokud má být jen .log (výhodou je sekvenční zápis chyb přes my_error_log a jiných PHP chyb)
                'log_standard_output' => false, //true, pokud má zároveň vypisovat na obrazovku; false, pokud má vypisovat jen do logu
                'log_profiling_step' => false, //110812, my_error_log neprofiluje rychlost //$PROFILING_STEP = 0.008;//110812, my_error_log profiluje čas mezi dvěma měřenými body vyšší než udaná hodnota sec
                'error_hacked' => true, //ERROR_HACK parameter is reflected
                'error_hack_from_get' => 0, //in this field, the value of $_GET['ERROR_HACK'] shall be set below
            ),
            $backyardConfConstruct
        );
        // phpcs:enable
        foreach (array('log_standard_output', 'error_hacked', 'error_hack_from_get') as $obsoleteProperty) {
            if (array_key_exists($obsoleteProperty, $backyardConfConstruct)) {
                $this->notice($obsoleteProperty . ' is set but ignored.');
            }
        }
        parent::__construct($this->backyardConf, $this->backyardTime);
    }

    /**
     * Error_log() modified to log necessary debug information by application to its own log
     * Logs with an arbitrary level.
     * Compliant with PSR-3 http://www.php-fig.org/psr/psr-3/
     *
     * Following gets written to log:
     * [Timestamp: d-M-Y H:i:s] [Logging level] [$error_number] [$_SERVER['SCRIPT_FILENAME']]
     * [username@gethostbyaddr($_SERVER['REMOTE_ADDR'])] [sec since page start] $message
     *
     * @global float $RUNNING_TIME
     * @global int $ERROR_HACK
     *
     * @param int $level Error level
     * @param string $message Message to be logged
     * @param array<int> $context OPTIONAL To enable error log filtering 'error_number' field expected
     *  or the first element element expected containing number of error category
     *
     * @return void
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
     */
    public function log($level, $message, array $context = array())
    {
        global $RUNNING_TIME;
        //try {
        parent::log($level, $message, $context);
        $RUNNING_TIME = $this->getLastRunningTime();
        //    return true;
        //} catch (ErrorLogFailureException $ex) { // as Logger::log() returns void
        //    return false;
        //}
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

    /**
     *
     * @param string $errorNumber
     * @param string $errorString
     * @param string $feedbackButtonMarkup
     * @return void (die)
     */
    public function dieGraciously($errorNumber, $errorString, $feedbackButtonMarkup = '')
    {
        $this->log(1, "Die with error {$errorNumber} - {$errorString}");
        if ((bool) $feedbackButtonMarkup) {
            echo(
            "<html><body>"
            . str_replace(
                urlencode("%CUSTOM_VALUE%"),
                urlencode(
                    "Error {$errorNumber} - "
                    . (($this->backyardConf['die_graciously_verbose']) ? " - {$errorString}" : "")
                ),
                $feedbackButtonMarkup
            )
            ); //<html><body> na začátku pomůže, pokud ještě výstup nezačal
        }
        die("Error {$errorNumber}" . (($this->backyardConf['die_graciously_verbose']) ? " - {$errorString}" : ""));
    }
}
