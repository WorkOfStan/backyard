<?php
error_log(__FILE__ . ' is obsolete - consider its rewriting');
/**
 * Name: class_HTMLPage.php
 * Project: LIB/Part of Library In Backyard
 * 
 *
 * Purpose: 
 * class HTMLPage
 *
 *
 * public properties
 * contentType
 * header
 * footer
 * body
 * 
 * 
 * 
 * public methods
 * __construct($TITLE="GODS rules",$CONTENT_TYPE="text/html")
 * startPage(){//Vypise Content-type HTTP header a header HTML stránky
 * endPage(){//Vypise tělo a patičku HTML stránky, čímž ukončí HTML výstup
 * addToBody($add){////append k $this->body
 * 
 * 
 * History
 * 2011-08-07, version 0.0, pro SMS game
 * 2011-08-13, version 0.1, součást prototypu pro GODS, použití je ovšem obecnější, zatím podporuje pouze Content-type: text/html
 * 2011-08-28, version 0.2, text/vnd.wap.wml   
 * 2011-09-04, version 0.3, /jq is in the root directory, not in the upper directory
 * 2011-09-28, version 0.4, jQuery may not be loaded
 * 2011-09-29, version 0.4.1, style may not be loaded
 * 2012-08-07, version 0.5, LOAD_JQUERYMOBILE added
 * 2012-08-27, version 0.5.1, beforeViewport string added 
 * 2012-10-23, version 0.6, version of jqm may be changed; name of css may be changed; removed http from linked URIs to be adaptible to https root; addToHeader added
 * 2012-11-05, v.0.6.1, elseif($LOAD_JQ == '1.8.2') added
 * 2012-11-06, v.0.6.2, style.css je na konci po jqueryui.css
 * 2013-01-04, v.0.7, manifest="offline.manifest"
 * 2013-05-02, v.0.8.1, jQM 1.3.1
 * 2013-05-04, v.0.8.2, text/html; charset=UTF-8 moved to HTTP headers following the GooglePageSpeed recommendation; endPage vyplivne všechno
 * 2013-10-06, v.0.9, method currentBodyOutput added
 * 2014-07-30, v.0.9.1, link jQ 1.6.2,1.8.2,1.11.1 to CDN
 *
 *  
 * @TODO - sladit
 *   Naming convention:
 *   class, method, function NameExample
 *   $EXAMPLE_OF_A_VARIABLE
 *   sql_field
 *   CSS_CLASS     
 * 
 *  
 * TODO
 * @TODO LOAD_JQUERYMOBILE verzi jquery sjednotit s JQ, 120807
 * @TODO more flexibility where jQ is loaded from
 * 
 * 
 * 
 */

/**
 * Creates HTML/WML page
 * __construct($TITLE='GODS rules', $CONTENT_TYPE='text/html', $LOAD_JQ=1, $LOAD_STYLE=1, $LOAD_JQUERYMOBILE=0, $beforeViewport='', $manifestCache='')
 * 
 * Methods:
 * addToBody
 * addToHeader
 * startPage - outputs page header
 * endPage - outputs body
 */
class HTMLPage {

    public $contentType = 'text/html', $header = '', $footer = '', $body = '';
    protected $title, $headerWasOutputed, $style;

    /**
     *
     * @param string $TITLE [optional]
     * @param string $CONTENT_TYPE [optional] If $CONTENT_TYPE == 'text/html' , be sure to set style.css in the same folder and have /jq/jquery-1.6.2.min.js present
     * @param mixed $LOAD_JQ [optional]
     * @param mixed $LOAD_STYLE [optional]
     * @param mixed $LOAD_JQUERYMOBILE [optional]
     * @param string $beforeViewport [optional]
     * @param string $manifestCache [optional]
     * 
     * If $CONTENT_TYPE == 'text/html' , be sure to set style.css in the same folder and have /jq/jquery-1.6.2.min.js present
     * 
     */
    public function __construct($TITLE = 'GODS rules', $CONTENT_TYPE = 'text/html', $LOAD_JQ = 1, $LOAD_STYLE = 1, $LOAD_JQUERYMOBILE = 0, $beforeViewport = '', $manifestCache = '') {
        $this->contentType = $CONTENT_TYPE;
        $this->headerWasOutputed = false;
        switch ($CONTENT_TYPE) {
            case 'text/html'://HTML5 with JQuery
                if (!empty($manifestCache)){
                    $manifestCache = " manifest=\"{$manifestCache}\"";
                }
                $this->title = $TITLE;
                //$this->header = "<!DOCTYPE html><html><head><title>".($this->title)."</title>";
                $this->header = "<!DOCTYPE html><html{$manifestCache}><head><title>" . ($this->title) . "</title>";
                //$this->header .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
                if ($beforeViewport != '') {
                    $this->header .= $beforeViewport; //@TODO - kontrola validity
                }
                //if($LOAD_JQUERYMOBILE == 1 || $LOAD_JQUERYMOBILE == '1.2.0'){            
                if ($LOAD_JQUERYMOBILE) {
                    $this->header .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
                    if ($LOAD_JQUERYMOBILE == '1.3.1' || $LOAD_JQUERYMOBILE == '1.3.1-local') {
                        $this->header .= '<link rel="stylesheet" href="css/themes-1.3.1/custom.min.css" />  ';
                    } else {
                        $this->header .= '<link rel="stylesheet" href="css/themes/custom.min.css" />  ';
                    }
                    //$this->header .= '<link rel="stylesheet" href="http://code.jquery.com/mobile/1.1.0/jquery.mobile.structure-1.1.0.min.css" /> ';
                    if ($LOAD_JQUERYMOBILE == 1) {
                        $this->header .= '<link rel="stylesheet" href="//code.jquery.com/mobile/1.1.1/jquery.mobile.structure-1.1.1.min.css" />';
                    } elseif ($LOAD_JQUERYMOBILE == '1.2.0') {
                        //$this->header .= '<link rel="stylesheet" href="//code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" />';//when not using theme
                        $this->header .= '<link rel="stylesheet" href="//code.jquery.com/mobile/1.2.0/jquery.mobile.structure-1.2.0.min.css" />'; //when using theme
                    } elseif ($LOAD_JQUERYMOBILE == '1.3.1') {
                        $this->header .= '<link rel="stylesheet" href="//code.jquery.com/mobile/1.3.1/jquery.mobile.structure-1.3.1.min.css" />'; //when using theme
                    } elseif ($LOAD_JQUERYMOBILE == '1.3.1-local') {
                        $this->header .= '<link rel="stylesheet" href="css/jquery.mobile.structure-1.3.1.min.css" />'; //when using theme                    
                    } else {
                        my_error_log("LOAD_JQUERYMOBILE={$LOAD_JQUERYMOBILE} - undefined", 2);
                    }
                }
                $this->style = $LOAD_STYLE;
                /* if ($LOAD_STYLE == 1) {$this->header .= '<link href="./style.css" rel="STYLESHEET" type="text/css">';}
                  elseif (substr($LOAD_STYLE,-4,4) == '.css') {
                  if(filter_var($LOAD_STYLE, FILTER_SANITIZE_URL)){
                  $this->header .= '<link href="'.filter_var($LOAD_STYLE, FILTER_SANITIZE_URL).'" rel="STYLESHEET" type="text/css">';
                  } else {
                  my_error_log("CSS URI was expected: {$LOAD_STYLE}",2);
                  }
                  } */
                $this->header .= '<meta http-equiv="Content-Script-Type" content="text/javascript">';
                //if (($LOAD_JQ == 1)&&($LOAD_JQUERYMOBILE == 0)) {$this->header .= '<script src="/jq/jquery-1.6.2.min.js"></script>';}
                if (($LOAD_JQ) && ($LOAD_JQUERYMOBILE == 0)) {
                    if ($LOAD_JQ == 1) {
                        $this->header .= '<script src="//code.jquery.com/jquery-1.6.2.min.js"></script>';
                    } elseif ($LOAD_JQ == '1.8.2') {
                        $this->header .= '<script src="//code.jquery.com/jquery-1.8.2.min.js"></script>';
                    } elseif ($LOAD_JQ == '1.11.1') {
                        $this->header .= '<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>';
                    }
                }
                if ($LOAD_JQUERYMOBILE == 1) {
                    $this->header .= '<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>';
                    $this->header .= '<script src="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.js"></script>';
                } elseif ($LOAD_JQUERYMOBILE == '1.2.0') {
                    $this->header .= '<script src="//code.jquery.com/jquery-1.8.2.min.js"></script>';
                    $this->header .= '<script src="//code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>';
                } elseif ($LOAD_JQUERYMOBILE == '1.3.1') {
                    $this->header .= '<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>';
                    $this->header .= '<script src="//code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>';
                } elseif ($LOAD_JQUERYMOBILE == '1.3.1-local') {
                    $this->header .= '<script src="js/jquery-1.9.1.min.js"></script>';
                    $this->header .= '<script src="js/jquery.mobile-1.3.1.min.js"></script>';
                }
                //$this->header .= '</head><body>';
                $this->footer = "</body></html>";
                break;

            case 'text/vnd.wap.wml'://WML 1.1 tested for SED750i
                $this->title = $TITLE;
                $this->header .= '<?xml version="1.0" encoding="UTF-8"?' . '>';
                $this->header .= '<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">';
                $this->header .= '<wml><head><meta forua="true" http-equiv="Cache-Control" content="max-age=15"/>';
                //$this->header .= '</head>';
                //$this->header .= '<card id="card" title="'.($this->title).'">';
                //$this->header .= '<p>';
                $this->footer = "</p></card></wml>";
                break;

            default:
                my_error_log("Undefined CONTENT_TYPE={$CONTENT_TYPE}", 1);
        }
    }

    public function startPage() {//Vypise Content-type HTTP header a header HTML stránky
        if ($this->contentType == 'text/html') {
            header("Content-type: text/html; charset=utf-8");
        } else {
            header("Content-type: " . $this->contentType);
        }
        switch ($this->contentType) {
            case 'text/html'://HTML5 with JQuery
                if ($this->style == 1) {
                    $this->header .= '<link href="./style.css" rel="STYLESHEET" type="text/css" />';
                } elseif (substr($this->style, -4, 4) == '.css') {
                    if (filter_var($this->style, FILTER_SANITIZE_URL)) {
                        $this->header .= '<link href="' . filter_var($this->style, FILTER_SANITIZE_URL) . '" rel="STYLESHEET" type="text/css" />';
                    } else {
                        my_error_log("CSS URI was expected: {$this->style}", 2);
                    }
                }
                $this->header .= '</head><body>';
                break;

            case 'text/vnd.wap.wml'://WML 1.1 tested for SED750i
                $this->header .= '</head>';
                $this->header .= '<card id="card" title="' . ($this->title) . '">';
                $this->header .= '<p>';
                break;
        } //default was already solved in constructor

        echo ($this->header);
        $this->headerWasOutputed = true;
    }

    public function outputCurrentBody() {//Vypise dosavadní tělo
        echo ($this->body);
        @ob_flush(); //'@' sign to avoid the following message: Notice: ob_flush(): failed to flush buffer. No buffer to flush.
        flush(); // http://php.vrana.cz/vysypani-vystupu.php      
        $this->body = '';
    }

    public function endPage() {//Vypise tělo a patičku HTML stránky, čímž ukončí HTML výstup
        echo ($this->body . $this->footer);
        @ob_flush(); //'@' sign to avoid the following message: Notice: ob_flush(): failed to flush buffer. No buffer to flush.
        flush(); // http://php.vrana.cz/vysypani-vystupu.php      
    }

    public function addToBody($add) {//append k $this->body 
        $this->body .= $add;
    }

    public function addToHeader($add) {//append k $this->body 
        if ($this->headerWasOutputed) {
            my_error_log("Header was already out. Following cannot be added: {$add}", 2);
        } else {
            $this->header .= $add;
        }
    }

}
