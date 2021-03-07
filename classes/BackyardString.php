<?php

namespace WorkOfStan\Backyard;

use Psr\Log\LoggerInterface;

class BackyardString
{

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;

    /**
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Created by stefanidesj, May 2006
     *
     * @param string $string
     * @return string
     */
    public function stripDiacritics($string)
    {
        $trans = array(
            "á" => "a", "ä" => "a", "č" => "c", "ď" => "d", "é" => "e", "ě" => "e", "ë" => "e",
            "í" => "i", "&#239;" => "i", "ň" => "n", "ó" => "o", "ö" => "o", "ř" => "r", "š" => "s", "ť" => "t",
            "ú" => "u", "ů" => "u", "ü" => "u", "ý" => "y", "&#255;" => "y", "ž" => "z",
            "Á" => "A", "Ä" => "A", "Č" => "C", "Ď" => "D", "É" => "E", "Ě" => "E", "Ë" => "E",
            "Í" => "I", "&#207;" => "I", "Ň" => "N", "Ó" => "O", "Ö" => "O", "Ř" => "R", "Š" => "S", "Ť" => "T",
            "Ú" => "U", "Ů" => "U", "Ü" => "U", "Ý" => "Y", "&#376;" => "Y", "Ž" => "Z"
        );
        // return strtr(iconv("utf-8", "iso-8859-2", $string),  $trans);
        return strtr($string, $trans);
    }
}
