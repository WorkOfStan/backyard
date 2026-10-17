<?php

declare(strict_types=1);

namespace WorkOfStan\Backyard\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use stdClass;
use UnexpectedValueException;
use WorkOfStan\Backyard\BackyardGeo;
use WorkOfStan\Backyard\BackyardMysqli;

class BackyardGeoTest extends TestCase
{
    /**
     * @param array<mixed>|false $rows
     */
    private function createConnection($rows): BackyardMysqli
    {
        $connection = $this->getMockBuilder(BackyardMysqli::class)
            ->disableOriginalConstructor()
            ->onlyMethods(array('queryArray'))
            ->getMock();
        $connection->expects($this->once())->method('queryArray')
            ->with('SELECT * FROM `poi_list` WHERE `category` IN (7)')
            ->willReturn($rows);

        return $connection;
    }

    /**
     * @return array<string, mixed>
     */
    private function createPoi(float $longitude = 0.001, int $id = 1): array
    {
        return array(
            'poi_id' => $id,
            'category' => 7,
            'mesto' => 'Test city',
            'PSC' => '123 45',
            'adresa' => 'Test street 1',
            'long' => $longitude,
            'lat' => 0.0
        );
    }

    public function testGetClosestPoiReturnsFalseForDistantResults(): void
    {
        $geo = new BackyardGeo(new NullLogger());
        $connection = $this->createConnection(array($this->createPoi(2.0)));

        $this->assertFalse($geo->getClosestPOI(0.0, 0.0, 7, $connection));
    }

    public function testGetClosestPoiReturnsFalseForEmptyResults(): void
    {
        $geo = new BackyardGeo(new NullLogger());
        foreach (array(false, array()) as $rows) {
            $this->assertFalse($geo->getClosestPOI(0.0, 0.0, 7, $this->createConnection($rows)));
        }
    }

    public function testGetClosestPoiSelectsNearestAndCalculatesDistance(): void
    {
        $geo = new BackyardGeo(new NullLogger());
        $connection = $this->createConnection(array($this->createPoi(0.1, 2), $this->createPoi()));

        $result = $geo->getClosestPOI(0.0, 0.0, 7, $connection);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['poi_id']);
        $this->assertSame(111, $result['distance_m']);
        $this->assertSame('7 Test street 1', $result['type_address']);
        $this->assertSame('Test street 1', $result['address']);
        $this->assertSame('Test city', $result['city']);
        $this->assertSame(7, $result['type']);
        $this->assertSame(0.001, $result['lng']);
        $this->assertSame(0.0, $result['lat']);
    }

    public function testGetClosestPoiValidatesSelectedAddressValues(): void
    {
        $geo = new BackyardGeo(new NullLogger());
        $stream = fopen('php://memory', 'r+');
        $this->assertIsResource($stream);

        try {
            foreach (array('category', 'adresa') as $field) {
                foreach (array(array(), new stdClass(), $stream, null) as $value) {
                    $row = $this->createPoi();
                    $row[$field] = $value;
                    $connection = $this->createConnection(array($row));

                    try {
                        $geo->getClosestPOI(0.0, 0.0, 7, $connection);
                        $this->fail('Expected invalid ' . $field . ' to be rejected');
                    } catch (UnexpectedValueException $exception) {
                        $this->assertSame(
                            'category and adresa MUST be scalar to be cast to string',
                            $exception->getMessage()
                        );
                    }
                }
            }
        } finally {
            fclose($stream);
        }
    }

    public function testGetListOfPoiRejectsNonArrayRows(): void
    {
        $geo = new BackyardGeo(new NullLogger());
        $connection = $this->createConnection(array('invalid row'));
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('listOfPOINearby MUST be array of arrays');

        $geo->getListOfPOI(7, $connection);
    }
}
