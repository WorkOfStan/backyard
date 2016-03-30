<?php

namespace GodsDev\Backyard\Test;

//use GodsDev\DefaultDeviceConnector;

class BackyardTest extends \PHPUnit_Framework_TestCase {
    protected $backyard = NULL;

    protected function setUp() {
        global $backyardConf;
        $this->backyard = new \GodsDev\Backyard\Backyard($backyardConf);
    }

//    public function testGetCurPageUrlIncludeQueryPart() {
//        //$backyard = new \GodsDev\Backyard\Backyard();
//        $this->assertEquals('', backyard_getCurPageURL());
//    }
//
//    public function testGetCurPageUrlWithoutQueryPart() {
//        //$backyard = new \GodsDev\Backyard\Backyard();
//        //error_log(print_r($_SERVER));
//        $this->assertEquals('', backyard_getCurPageURL(false));
//    }

    public function testBackyardJson() {
        $this->setUp();
        $orig = '{"status": "123", "text": "abc"}';
        $expected = '{"status":"123","text":"abc"}';
        
        $this->assertEquals($expected, $this->backyard->Json->backyard_minifyJSON($orig));
    }
    
    
}
