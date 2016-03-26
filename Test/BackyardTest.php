<?php

namespace GodsDev\Backyard\Test;

//use GodsDev\DefaultDeviceConnector;

class BackyardTest extends \PHPUnit_Framework_TestCase {
    protected $backyard = NULL;

    public function __construct() {
        global $backyardConf;
        $this->backyard = new \GodsDev\Backyard\Backyard();
    }

    public function testGetCurPageUrlIncludeQueryPart() {
        //$backyard = new \GodsDev\Backyard\Backyard();
        $this->assertEquals('', backyard_getCurPageURL());
    }

    public function testGetCurPageUrlWithoutQueryPart() {
        //$backyard = new \GodsDev\Backyard\Backyard();
        //error_log(print_r($_SERVER));
        $this->assertEquals('', backyard_getCurPageURL(false));
    }

}
