<?php

namespace GodsDev\Backyard;

class BackyardTime
{

    private $PageTimestamp = null;

    public function __construct()
    {
        $this->PageTimestamp = $this->getmicrotime(); //initialisation
    }

    /**
     * Initiation of $page_timestamp must be the first thing a page will do 
     * Store "time" for benchmarking.
     * Inspired by sb_functions.php in sphpblog
     * 
     * @return float
     */
    public function getmicrotime()
    {
        if (version_compare(phpversion(), '5.0.0') == -1) {
            list($usec, $sec) = explode(' ', microtime());
            return ((float) $usec + (float) $sec);
        } else {
            return( microtime(true) );
        }
    }

    /**
     * 
     * @return float
     */
    public function getPageTimestamp()
    {
        return $this->PageTimestamp;
    }

    /**
     * 
     * @return float
     */
    public function getRunningTime()
    {//111105, because $RUNNING_TIME got updated only when my_error_log makes a row
        return round($this->getmicrotime() - $this->PageTimestamp, 4);
    }

    /**
     * If called with 'Page Generated in %s seconds', it returns "Page Generated in x.xxxx seconds"
     *
     * @param string $langStringPageGeneratedIn instead of $backyardLangString['page_generated_in']
     * @return string
     */
    public function pageGeneratedIn($langStringPageGeneratedIn = '%s')
    {
        $str = str_replace('%s', round($this->getmicrotime() - $this->PageTimestamp, 4), $langStringPageGeneratedIn);
        //$this->BackyardError->log(6, round($this->getmicrotime() - $this->PageTimestamp, 4), array(6));
        return $str;
    }

}

/**
 * Random seed initiation for mt_rand()
 */
/**
 * // Note: As of PHP 4.2.0, there is no need to seed the random number generator with srand() or mt_srand() as this is now done automatically.
 * // www.su.cz má PHP 4.1.2 so: seed with microseconds
  function make_seed() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
  }
  mt_srand(make_seed());
 */
