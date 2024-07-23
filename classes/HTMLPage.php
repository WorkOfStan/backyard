<?php

/**
 * Project: LIB/Part of Library In Backyard
 * Purpose:
 * legacy class HTMLPage for simple HTML generation
 * use Latté instead for anything future-proof
 *
 * public properties
 *   contentType
 *   header
 *   footer
 *   body
 *
 * public methods
 *   __construct($TITLE="Backyard rocks",$CONTENT_TYPE="text/html")
 *   startPage(){//Vypise Content-type HTTP header a header HTML stránky
 *   endPage(){//Vypise tělo a patičku HTML stránky, čímž ukončí HTML výstup
 *   addToBody($add){//append k $this->body
 *
 *
 * History
 *   2011-08-07, version 0.0, pro SMS game
 *   2011-08-13, version 0.1, součást prototypu pro GODS, použití je ovšem obecnější,
 *     zatím podporuje pouze Content-type: text/html
 *   2011-08-28, version 0.2, text/vnd.wap.wml
 *   2011-09-04, version 0.3, /jq is in the root directory, not in the upper directory
 *   2011-09-28, version 0.4, jQuery may not be loaded
 *   2011-09-29, version 0.4.1, style may not be loaded
 *   2012-08-07, version 0.5, LOAD_JQUERYMOBILE added
 *   2012-08-27, version 0.5.1, beforeViewport string added
 *   2012-10-23, version 0.6, version of jqm may be changed; name of css may be changed;
 *     removed http from linked URIs to be adaptible to https root; addToHeader added
 *   2012-11-05, v.0.6.1, elseif($LOAD_JQ == '1.8.2') added
 *   2012-11-06, v.0.6.2, style.css je na konci po jqueryui.css
 *   2013-01-04, v.0.7, manifest="offline.manifest"
 *   2013-05-02, v.0.8.1, jQM 1.3.1
 *   2013-05-04, v.0.8.2, text/html; charset=UTF-8 moved to HTTP headers following the GooglePageSpeed recommendation;
 *     endPage vyplivne všechno
 *   2013-10-06, v.0.9, method currentBodyOutput added
 *   2014-07-30, v.0.9.1, link jQ 1.6.2,1.8.2,1.11.1 to CDN
 *   2022-02-12, style improvement;
 *     WML output strip diacritics in order to ensure backward compatibility without extensive testing
 *
 *
 * TODO
 * - LOAD_JQUERYMOBILE verzi jquery sjednotit s JQ, 120807
 * - more flexibility where jQ is loaded from
 * - update naming convention to 2022
 * - consider adding Tracy\Debugger and Tracy\ILogger (PHP>=5.3) instead of error_log
 *
 ***********************************************/

namespace WorkOfStan\Backyard;

class HTMLPage
{
    /** @var string */
    public $contentType = 'text/html';
    /** @var string */
    public $header = '';
    /** @var string */
    public $footer = '';
    /** @var string */
    public $body = '';
    /** @var string */
    protected $title;
    /** @var bool */
    protected $headerWasOutputed;
    /** @var int|string */
    protected $style;

    /**
     * Creates HTML/WML page
     *
     * Simple usage: addToBody('content'); startPage - outputs page header; endPage - outputs body
     *
     * @param string $TITLE [optional]
     * @param string $CONTENT_TYPE [optional] If $CONTENT_TYPE == 'text/html' ,
     *   be sure to set style.css in the same folder and have /jq/jquery-1.6.2.min.js present
     * @param int|string $LOAD_JQ [optional]
     * @param int|string $LOAD_STYLE [optional]
     * @param int|string $LOAD_JQUERYMOBILE [optional]
     * @param string $beforeViewport [optional]
     * @param string $manifestCache [optional]
     *
     * If $CONTENT_TYPE == 'text/html' , be sure to set style.css in the same folder
     * and have /jq/jquery-1.6.2.min.js present
     *
     */
    public function __construct(
        $TITLE = 'Backyard rocks',
        $CONTENT_TYPE = 'text/html',
        $LOAD_JQ = 1,
        $LOAD_STYLE = 1,
        $LOAD_JQUERYMOBILE = 0,
        $beforeViewport = '',
        $manifestCache = ''
    ) {
        $this->contentType = $CONTENT_TYPE;
        $this->headerWasOutputed = false;
        switch ($CONTENT_TYPE) {
            case 'text/html': // HTML5 with JQuery
                if (!empty($manifestCache)) {
                    $manifestCache = " manifest=\"{$manifestCache}\"";
                }
                $this->title = $TITLE;
                $this->header = "<!DOCTYPE html><html{$manifestCache}><head><title>" . ($this->title) . "</title>";
                if ($beforeViewport != '') {
                    $this->header .= $beforeViewport; // @TODO validity check
                }
                if ($LOAD_JQUERYMOBILE) {
                    $this->header .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
                    if ($LOAD_JQUERYMOBILE == '1.3.1' || $LOAD_JQUERYMOBILE == '1.3.1-local') {
                        $this->header .= '<link rel="stylesheet" href="css/themes-1.3.1/custom.min.css" />  ';
                    } else {
                        $this->header .= '<link rel="stylesheet" href="css/themes/custom.min.css" />  ';
                    }
                    if ($LOAD_JQUERYMOBILE == 1) {
                        $this->header .= '<link rel="stylesheet" '
                            . 'href="//code.jquery.com/mobile/1.1.1/jquery.mobile.structure-1.1.1.min.css" />';
                    } elseif ($LOAD_JQUERYMOBILE == '1.2.0') {
                        $this->header .= '<link rel="stylesheet" ' // when using theme
                            . 'href="//code.jquery.com/mobile/1.2.0/jquery.mobile.structure-1.2.0.min.css" />';
                    } elseif ($LOAD_JQUERYMOBILE == '1.3.1') {
                        $this->header .= '<link rel="stylesheet" ' // when using theme
                            . 'href="//code.jquery.com/mobile/1.3.1/jquery.mobile.structure-1.3.1.min.css" />';
                    } elseif ($LOAD_JQUERYMOBILE == '1.3.1-local') {
                        $this->header .= '<link rel="stylesheet" '
                            . 'href="css/jquery.mobile.structure-1.3.1.min.css" />'; // when using theme
                    } else {
                        error_log("LOAD_JQUERYMOBILE={$LOAD_JQUERYMOBILE} - undefined");
                    }
                }
                $this->style = $LOAD_STYLE;
                $this->header .= '<meta http-equiv="Content-Script-Type" content="text/javascript">';
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
                    $this->header .= '<script src="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.js">'
                        . '</script>';
                } elseif ($LOAD_JQUERYMOBILE == '1.2.0') {
                    $this->header .= '<script src="//code.jquery.com/jquery-1.8.2.min.js"></script>';
                    $this->header .= '<script src="//code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js">'
                        . '</script>';
                } elseif ($LOAD_JQUERYMOBILE == '1.3.1') {
                    $this->header .= '<script src="//code.jquery.com/jquery-1.9.1.min.js"></script>';
                    $this->header .= '<script src="//code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js">'
                        . '</script>';
                } elseif ($LOAD_JQUERYMOBILE == '1.3.1-local') {
                    $this->header .= '<script src="js/jquery-1.9.1.min.js"></script>';
                    $this->header .= '<script src="js/jquery.mobile-1.3.1.min.js"></script>';
                }
                $this->footer = "</body></html>";
                break;

            case 'text/vnd.wap.wml': // WML 1.1 tested for SED750i
                $this->title = $TITLE;
                $this->header .= '<?xml version="1.0" encoding="UTF-8"?' . '>';
                $this->header .= '<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" '
                    . '"http://www.wapforum.org/DTD/wml_1.1.xml">';
                $this->header .= '<wml><head><meta forua="true" http-equiv="Cache-Control" content="max-age=15"/>';
                $this->footer = "</p></card></wml>";
                break;

            default:
                error_log("Undefined CONTENT_TYPE=$CONTENT_TYPE");
        }
    }

    /**
     * Vypise Content-type HTTP header a header HTML stránky
     *
     * @return void
     */
    public function startPage()
    {
        if ($this->contentType == 'text/html') {
            header("Content-type: text/html; charset=utf-8");
        } elseif ($this->contentType == 'text/vnd.wap.wml') {
            header("Content-type: text/vnd.wap.wml; charset=utf-8");
        } else {
            header("Content-type: " . $this->contentType);
        }
        switch ($this->contentType) {
            case 'text/html': // HTML5 with JQuery
                if ($this->style == 1) {
                    $this->header .= '<link href="./style.css" rel="STYLESHEET" type="text/css" />';
                } elseif (substr((string) $this->style, -4, 4) == '.css') {
                    if (filter_var($this->style, FILTER_SANITIZE_URL)) {
                        $this->header .= '<link href="' . filter_var($this->style, FILTER_SANITIZE_URL)
                            . '" rel="STYLESHEET" type="text/css" />';
                    } else {
                        error_log("CSS URI was expected: {$this->style}");
                    }
                }
                $this->header .= '</head><body>';
                break;

            case 'text/vnd.wap.wml': // WML 1.1 tested for SED750i
                $this->header .= '</head>';
                $this->header .= '<card id="card" title="' . $this->treatForWml($this->title) . '">';
                $this->header .= '<p>';
                break;
        } // default was already solved in constructor

        echo($this->header);
        $this->headerWasOutputed = true;
    }

    /**
     * Outputs (and resets) the current contents of $this->body
     *
     * @return void
     */
    public function outputCurrentBody()
    {
        echo($this->body);
        // '@' sign to avoid the following message: Notice: ob_flush(): failed to flush buffer. No buffer to flush.
        @ob_flush();
        flush(); // http://php.vrana.cz/vysypani-vystupu.php
        $this->body = '';
    }

    /**
     * Vypise tělo a patičku HTML stránky, čímž ukončí HTML výstup
     *
     * @return void
     */
    public function endPage()
    {
        echo ($this->body . $this->footer);
        // '@' sign to avoid the following message: Notice: ob_flush(): failed to flush buffer. No buffer to flush.
        @ob_flush();
        flush(); // http://php.vrana.cz/vysypani-vystupu.php
    }

    /**
     * basic functions to use in phpportal (MPS/stefanidesj, May 2006)
     * Pre-process texts to be displayed in a WML browser
     *
     * @param string $text
     * @return string
     */
    public function fixXml($text)
    {
        return str_replace(
            "\"",
            "&quot;",
            str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("&", "&amp;", $text)))
        );
    }

    /**
     * Wrapper to make sure that the output doesn't break in a WML browser
     * Strip diacritics as a simple way
     *
     * @param string $string
     * @return string
     */
    private function treatForWml($string)
    {
        $trans = array(
            "á" => "a", "ä" => "a", "č" => "c", "ď" => "d", "é" => "e", "ě" => "e", "ë" => "e",
            "í" => "i", "&#239;" => "i", "ň" => "n", "ó" => "o", "ö" => "o", "ř" => "r", "š" => "s", "ť" => "t",
            "ú" => "u", "ů" => "u", "ü" => "u", "ý" => "y", "&#255;" => "y", "ž" => "z",
            "Á" => "A", "Ä" => "A", "Č" => "C", "Ď" => "D", "É" => "E", "Ě" => "E", "Ë" => "E",
            "Í" => "I", "&#207;" => "I", "Ň" => "N", "Ó" => "O", "Ö" => "O", "Ř" => "R", "Š" => "S", "Ť" => "T",
            "Ú" => "U", "Ů" => "U", "Ü" => "U", "Ý" => "Y", "&#376;" => "Y", "Ž" => "Z"
        );
        return strtr($string, $trans);
    }

    /**
     * append k $this->body
     *
     * @param string $add
     * @return void
     */
    public function addToBody($add)
    {
        $this->body .= ($this->contentType === 'text/vnd.wap.wml') ? $this->treatForWml($add) : $add;
    }

    /**
     * append k $this->header
     *
     * @param string $add
     * @return void
     */
    public function addToHeader($add)
    {
        if ($this->headerWasOutputed) {
            error_log("Header was already out. Following cannot be added: {$add}");
        } else {
            $this->header .= $add;
        }
    }
}
