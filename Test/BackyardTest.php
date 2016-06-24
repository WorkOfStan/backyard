<?php

namespace GodsDev\Backyard\Test;

//use GodsDev\DefaultDeviceConnector;

class BackyardTest extends \PHPUnit_Framework_TestCase {
    protected $Backyard = NULL;

//    protected function setUp() {
//        global $backyardConf;
//        $this->Backyard = new \GodsDev\Backyard\Backyard($backyardConf);
//    }

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

    public function testBackyardJsonMinifyJson() {
//        $this->setUp();
        $this->Backyard = new \GodsDev\Backyard\Backyard(array());
        $orig = '{"status": "123", "text": "abc"}';
        $expected = '{"status":"123","text":"abc"}';
        
        $this->assertEquals($expected, $this->Backyard->BackyardJson->minifyJSON($orig));
    }
    
    public function testOtherClass() {
        $this->Backyard = new \GodsDev\Backyard\Backyard(array());
        $orig = '{"status": "123", "text": "abc"}';
        $expected = '{"status":"123","text":"abc"}';
        
        //$this->assertEquals($expected, $this->Backyard->BackyardHttp->backyard_minifyJSON($orig));
    }

    
}
