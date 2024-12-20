<?php

namespace WorkOfStan\Backyard\Test;

use PHPUnit\Framework\TestCase;
use WorkOfStan\Backyard\Backyard;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-06-25 at 18:53:30.
 */
class BackyardTest extends TestCase
{
    /**
     * @var Backyard
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        error_reporting(E_ALL); // incl E_NOTICE
        $this->object = new Backyard(array('logging_level' => 4));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // no action
    }

    /**
     * @return void
     */
    public function testBackyardJsonMinifyJson(): void
    {
        //$this->Backyard = new \WorkOfStan\Backyard\Backyard(array());
        $orig = '{"status": "1230", "text": "abc"}';
        $expected = '{"status":"1230","text":"abc"}';

        $this->assertEquals($expected, $this->object->Json->minifyJSON($orig));
    }
}
