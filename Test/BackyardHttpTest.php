<?php

namespace WorkOfStan\Backyard\Test;

use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;
use WorkOfStan\Backyard\BackyardHttp;
use WorkOfStan\Backyard\BackyardError;

//@todo - put into separate group as it needs access to internet

// TODO when "phpunit/phpunit": "<7.0" then use https://github.com/phpstan/phpstan-phpunit instead of Webmozart here

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-06-25 at 17:57:39.
 */
class BackyardHttpTest extends TestCase
{
    /**
     * @var BackyardHttp
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
        $this->object = new BackyardHttp(new BackyardError(array('logging_level' => 4)));
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
//    /**
//     * @covers WorkOfStan\Backyard\BackyardHttp::movePage
//     * @todo   Implement testMovePage().
//     */
//    public function testMovePage()
//    {
//        // Remove the following lines when you implement this test.
//        $this->markTestIncomplete(
//            'This test has not been implemented yet.'
//        );
//    }
//
//    /**
//     * @covers WorkOfStan\Backyard\BackyardHttp::retrieveFromPostThenGet
//     * @todo   Implement testRetrieveFromPostThenGet().
//     */
//    public function testRetrieveFromPostThenGet()
//    {
//        // Remove the following lines when you implement this test.
//        $this->markTestIncomplete(
//            'This test has not been implemented yet.'
//        );
//    }
//
//    /**
//     * @covers WorkOfStan\Backyard\BackyardHttp::getCurPageURL
//     * @todo   Implement testGetCurPageURL().
//     */
//    public function testGetCurPageURL()
//    {
//        // Remove the following lines when you implement this test.
//        $this->markTestIncomplete(
//            'This test has not been implemented yet.'
//        );
//    }

    /**
     * @covers WorkOfStan\Backyard\BackyardHttp::getData
     *
     * @return void
     */
    public function testGetDataContent(): void
    {
        $url = 'http://dadastrip.cz/test/';
        $useragent = 'PHP/phpunit-testing';
        $timeout = 5;
        $customHeaders = 'x-wap-profile: http://no.web.com/|x-other-header: foo';
        $postArray = array();
        $expected = array(
            'HTTP_CODE' => 200,
            'message_body' => '=== HTTP headers ===<br/>
<b>User-Agent:</b> PHP/phpunit-testing <br/>
<b>Host:</b> dadastrip.cz <br/>
<b>Accept:</b> */* <br/>
<b>x-wap-profile:</b> http://no.web.com/ <br/>
<b>x-other-header:</b> foo <br/>
</body></html>',
            'CONTENT_TYPE' => 'text/html'
        );

        $result = $this->object->getData($url, $useragent, $timeout, $customHeaders, $postArray);
        //remove first two lines because they contain timestamp and source IP and hence are changing unnecessarily
        //also remove X-Forwarded-For header as it also contains source IP
        Assert::string($result['message_body']);
        $result['message_body'] = $this->pregReplaceString(
            '~<b>X-Forwarded-For:<\/b> :{2}[a-f]+.[0-9\.]+ <br\/>~',
            '',
            $this->pregReplaceString('/^.+\n/', '', $this->pregReplaceString('/^.+\n/', '', $result['message_body']))
        );
        $this->assertEquals($expected['HTTP_CODE'], $result['HTTP_CODE'], 'HTTP_CODE differ');

        $tempExpected = explode('<br/>', $this->pregReplaceString('/\s+/', '', $expected['message_body']));
        asort($tempExpected);
        $tempResult = explode('<br/>', $this->pregReplaceString('/\s+/', '', $result['message_body']));
        asort($tempResult);
        $this->assertEquals(
            implode('|', $tempExpected),
            implode('|', $tempResult),
            'Header sets differ'
        );

        //   $this->assertEquals($expected['REDIRECT_URL'], $result['REDIRECT_URL']);
        $this->assertEquals($expected['CONTENT_TYPE'], $result['CONTENT_TYPE']);
    }

    /**
     * @return void
     */
    public function testGetDataRedirect(): void
    {
        //@todo incl. recursion (if there is)
        $url = 'http://dadastrip.cz/test';
        $expected = array(
            'HTTP_CODE' => 301,
            'message_body' => '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>301 Moved Permanently</title>
</head><body>
<h1>Moved Permanently</h1>
<p>The document has moved <a href="http://dadastrip.cz/test/">here</a>.</p>
</body></html>
',
            'REDIRECT_URL' => 'http://dadastrip.cz/test/',
            'CONTENT_TYPE' => 'text/html; charset=iso-8859-1'
        );
        $result = $this->object->getData(
            $url,
            $useragent = 'PHP/cURL',
            $timeout = 5,
            $customHeaders = false,
            $postArray = array()
        );
        $this->assertEquals($expected['HTTP_CODE'], $result['HTTP_CODE']);
        //Assert::string($expected['message_body']);
        Assert::string($result['message_body']);
        $this->assertEquals(
            preg_replace('/\s+/', '', $expected['message_body']),
            preg_replace('/\s+/', '', $result['message_body'])
        );
        $this->assertEquals($expected['REDIRECT_URL'], $result['REDIRECT_URL']);
        $this->assertEquals($expected['CONTENT_TYPE'], $result['CONTENT_TYPE']);
    }
    //@todo - make test if the method remains in Backyard
//    /**
//     * @covers WorkOfStan\Backyard\BackyardHttp::getHTTPstatusCode
//     * @todo   Implement testGetHTTPstatusCode().
//     */
//    public function testGetHTTPstatusCode()
//    {
//        $url = '{"status": "123", "text": "abc"}';
//        $expected = '{"status":"123","text":"abc"}';
//
//        $this->assertEquals($expected, $this->object->getHTTPstatusCode($url));
//    }
//
//    /**
//     * @covers WorkOfStan\Backyard\BackyardHttp::getHTTPstatusCodeByUA
//     * @todo   Implement testGetHTTPstatusCodeByUA().
//     */
//    public function testGetHTTPstatusCodeByUA()
//    {
//        $url = '{"status": "123", "text": "abc"}';
//        $userAgent = 'sth';
//        $expected = '{"status":"123","text":"abc"}';
//
//        $this->assertEquals($expected, $this->object->getHTTPstatusCodeByUA($url, $userAgent));
//    }

    /**
     * $subject is expected to be string, so the function returns string
     *
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param string $subject
     * @param int $limit
     * @param ?int $count
     * @return string
     * @throws \Exception
     * @throws InvalidArgumentException
     */
    private function pregReplaceString($pattern, $replacement, $subject, $limit = -1, &$count = null): string
    {
        $result = \preg_replace($pattern, $replacement, $subject, $limit, $count);
        if (is_null($result)) {
            throw new \Exception('error (null) preg_replace');
        }
        //Assert::string($result);
        return $result;
    }
}
