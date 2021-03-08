<?php

namespace WorkOfStan\Backyard\Test;

use WorkOfStan\Backyard\BackyardString;
use WorkOfStan\Backyard\BackyardError;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2018-09-11 at 14:30:53.
 */
class BackyardStringTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var BackyardString
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        error_reporting(E_ALL); // incl E_NOTICE
        $this->object = new BackyardString(new BackyardError(array('logging_level' => 4)));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        // no action
    }

    /**
     * @covers WorkOfStan\Backyard\BackyardString::stripDiacritics
     *
     * @return void
     */
    public function testStripDiacritics()
    {
        $this->assertEquals('scr', $this->object->stripDiacritics('ščř'));
        $this->assertEquals(
            'Prilis zlutoucky kun upel dabelske ody.',
            $this->object->stripDiacritics('Příliš žluťoučký kůň úpěl ďábelské ódy.')
        );
        $this->assertEquals(
            'PRILIS ZLUTOUCKY KUN UPEL DABELSKE ODY.',
            $this->object->stripDiacritics('PŘÍLIŠ ŽLUŤOUČKÝ KŮŇ ÚPĚL ĎÁBELSKÉ ÓDY.')
        );
        $this->assertEquals('Blizkost', $this->object->stripDiacritics('Blízkost'));
    }
}
