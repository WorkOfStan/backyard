<?php

declare(strict_types=1);

namespace WorkOfStan\Backyard\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use WorkOfStan\Backyard\BackyardBriefApiClient;

class BackyardBriefApiClientTest extends TestCase
{
    public function testInvalidJsonWithDefaultLogger(): void
    {
        $client = $this->getMockBuilder(BackyardBriefApiClient::class)
            ->setConstructorArgs(array('https://example.invalid/api'))
            ->onlyMethods(array('sendJsonLoad'))
            ->getMock();
        $client->expects($this->once())->method('sendJsonLoad')->with('{}')->willReturn('invalid json');

        $this->assertSame(array(), $client->getJsonArray('{}'));
    }

    public function testInvalidJsonWithSuppliedLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')
            ->with('json decode failed for invalid json that resulted from {}');
        $client = $this->getMockBuilder(BackyardBriefApiClient::class)
            ->setConstructorArgs(array('https://example.invalid/api', null, $logger))
            ->onlyMethods(array('sendJsonLoad'))
            ->getMock();
        $client->expects($this->once())->method('sendJsonLoad')->with('{}')->willReturn('invalid json');

        $this->assertSame(array(), $client->getJsonArray('{}'));
    }
}
