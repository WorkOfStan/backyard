<?php

namespace WorkOfStan\Backyard\Test;

use PHPUnit\Framework\TestCase;
use WorkOfStan\Backyard\BackyardCrypt;
use WorkOfStan\Backyard\BackyardError;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-06-25 at 17:57:39.
 */
class BackyardCryptTest extends TestCase
{
    /**
     * @var BackyardCrypt
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
        $this->object = new BackyardCrypt(new BackyardError(array('logging_level' => 4)));
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
     * @covers WorkOfStan\Backyard\BackyardCrypt::randomId
     *
     * @return void
     */
    public function testRandomId(): void
    {
        $orig = $this->object->randomId();
        $expected = $this->object->randomId();

        $this->assertNotEquals($expected, $orig);
    }

    /**
     * @covers WorkOfStan\Backyard\BackyardCrypt::randomId
     *
     * @return void
     */
    public function testRandomIdDefault10(): void
    {
        $orig = $this->object->randomId();
        $expected = 10;

        $this->assertEquals($expected, strlen($orig));
    }

    /**
     * @covers WorkOfStan\Backyard\BackyardCrypt::randomId
     *
     * @return void
     */
    public function testRandomIdLength32(): void
    {
        $orig = $this->object->randomId(32);
        $expected = 32;

        $this->assertEquals($expected, strlen($orig));
    }

    /**
     * @covers WorkOfStan\Backyard\BackyardCrypt::randomId
     *
     * @return void
     */
    public function testRandomIdLength1024(): void
    {
        $orig = $this->object->randomId(1024);
        $expected = 1024;
        //error_log($orig);
        $this->assertEquals($expected, strlen($orig));
    }
}
