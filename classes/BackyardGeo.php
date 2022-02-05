<?php

namespace WorkOfStan\Backyard;

use Psr\Log\LoggerInterface;
use WorkOfStan\Backyard\BackyardMysqli;

class BackyardGeo
{

    /**
     *
     * @var array<mixed>
     */
    protected $backyardConf = array();

    /**
     *
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     *
     * @param LoggerInterface $logger
     * @param array<array<mixed>> $backyardConfConstruct
     */
    public function __construct(LoggerInterface $logger, array $backyardConfConstruct = array())
    {
        //@todo do not use $this->BackyardConf but set the class properties right here accordingly;
        //@todo also provide means to set the values otherwise later
        $this->backyardConf = array_merge(
            array(//default values
                //to quickly get rid off too distant POIs; 1 ~ 100km
                'geo_rough_distance_limit' => 1, //float
                //distance considered to be overlapping with the device position
                // 2500 m is considered exact location due to mobile phone GPS caching
                'geo_maximum_meters_from_poi' => 2500, //float
                //name of table with POI coordinates
                'geo_poi_list_table_name' => 'poi_list', //string
            ),
            $backyardConfConstruct
        );

        $this->logger = $logger;
    }
    /**     * ***************************************************************************
     * GEOLOCATION FUNCTIONS
     *
     * ***** History
     * 2013-07-30, v.1 - taken from functions.php of "Píchačky 2012" project
     * 2014-02-18, v.2 - taken from checkin2013 and revamped to be more universal
     * 2014-05-05, v.3 - modified for backyard 2
     * 2015-03-10, v.4 - necessary variables put into backyard 2 standard __BACKYARDROOT__ . "/conf/conf.php";
     *
     *
     * ***** Description
     * Given that global variable $backyardConf['geo_poi_list_table_name'] defined in conf/conf.php
     * and potentially customized in the application using backyard 2
     * contains name of the table with points of interest then function ..
     * array backyard_getClosestPOI (float $lat, float $long, int|string $poiCategory, object $poiConnection)
     * .. returns associative array of the closest POI.
     *
     * To do that it uses following material functions:
     * array backyard_getListOfPOINearby (float $lat, float $long, int|string $poiCategory, object $poiConnection)
     * float backyard_calculateDistanceFromLatLong( array('latitude','longitude') $point1,
     *                                              array('latitude','longitude') $point2, string $uom='km')
     *
     *
     * Global variable $backyardConf contains following relevant fields:
     * //float //to quickly get rid off too distant POIs; 1 ~ 100km
     * $backyardConf['geo_rough_distance_limit']=1;
     * //float //distance considered to be overlapping with the device position
     * // 2500 m is considered exact location due to mobile phone GPS caching
     * //used only by application not by backyard_geo.php script
     * $backyardConf['geo_maximum_meters_from_poi']=2500;
     * //string //name of table with POI coordinates
     * $backyardConf['geo_poi_list_table_name']
     *
     * @TODO 4 - dovysvětlit -
     * Voláno bylo takto:
      https://github.com/GodsDev/repo1/blob/master/checkin/api_2.php
     * s GET parametry lat=&lng=&event=odpichGPS a odpovědí byl JSON vypočítaný jako:
      $jsonResult = GetStatusJSON ($userId,$inputLat,$inputLong)
      /definováno také v https://github.com/GodsDev/repo1/blob/master/checkin/functions.php /

      Voláno bylo z javascript funkce:
      function odpichGPS(lat, lng)
      v https://github.com/GodsDev/repo1/blob/master/checkin/js/js.js volané tamtéž.
     *
     * Resp.
     * https://github.com/GodsDev/repo1/blob/master/checkin/src/js/geo.js
     * VIZ http://free.t-mobile.cz/ms/test/budpripojen/mobilni-internet.php !!
     *
     * Resp.
     * js/geo.js
     *
     * @todo 4 - consider not indexing lat and long in MySQL table
     * @todo 4 - consider renaming long as column in MySQL table (because long is reserved word)
     * @todo 4 - consider spatial indexes in MySQL table
     *
     *
     */

    /**
     * 1 ~ 100km
     * @param float $clientLng
     * @param float $clientLat
     * @param float $poiLng
     * @param float $poiLat
     * @return float
     */
    public function getRoughDistance($clientLng, $clientLat, $poiLng, $poiLat)
    {
        $result = abs($clientLng - $poiLng) + abs($clientLat - $poiLat);
        $this->logger->log(
            5,
            "client({$clientLng}, {$clientLat}) poi({$poiLng}, {$poiLat}) roughDistance = {$result}"
        );
        return $result;
    }

    /**
     * Returns associative array of the closest POI.
     * Given that global variable $backyardConf['geo_poi_list_table_name'] defined in conf/conf.php
     * and potentially customized by application
     * contains name of the table with points of interest.
     *
     * @param float $lat (in database as double)
     * @param float $long (in database as double)
     * @param mixed $poiCategory (integer|string with comma separated integers)
     *                           according to table set in $backyardConf['geo_poi_list_table_name']
     * @param BackyardMysqli $poiConnection
     * @return array<mixed>|false false if empty
     */
    public function getClosestPOI($lat, $long, $poiCategory, BackyardMysqli $poiConnection)
    {
        $this->logger->log(4, "Looking for closest POI: lat={$lat} long={$long}");
        $uom = 'm';
        //current
        $startPoint = array(
            'latitude' => $lat,
            'longitude' => $long
        );

        $listOfPOINearby = $this->getListOfPOI($poiCategory, $poiConnection);
        if (!$listOfPOINearby) {
            return false;
        }

        $listOfPOINearbyPreprocessed = array();
        $roughDistance = array();

        foreach ($listOfPOINearby as $key => $row) {
            $listOfPOINearbyPreprocessed[$key] = array(
                'poi_id' => $row['poi_id'],
                'category' => $row['category'],
                //'typ' => $row['typ'], //@todo - add category_name from related table
                'mesto' => $row['mesto'],
                'PSC' => $row['PSC'],
                'adresa' => $row['adresa'],
                'lng' => $row['long'],
                'lat' => $row['lat'],
                // abs($long - $row['long']) + abs($lat - $row['lat'])
                'roughDistance' => $this->getRoughDistance($long, $lat, $row['long'], $row['lat'])
            );
            $roughDistance[$key] = $listOfPOINearbyPreprocessed[$key]['roughDistance'];
        }

        $this->logger->log(
            4,
            'Count of rows listOfPOINearbyPreprocessed: ' . count($listOfPOINearbyPreprocessed),
            array(11)
        );
        array_multisort($roughDistance, SORT_ASC, $listOfPOINearbyPreprocessed);

        $distanceArray = array();
        $listOfPOINearbyProcessed = array();
        foreach ($listOfPOINearbyPreprocessed as $key => $row) {
            if ($row['roughDistance'] < $this->backyardConf['geo_rough_distance_limit']) {
                $distance = $this->calculateDistanceFromLatLong(
                    $startPoint,
                    array(
                        'latitude' => $row['lat'],
                        'longitude' => $row['lng']
                    ),
                    $uom
                );
                $listOfPOINearbyPreprocessed[$key]['distance'] = $distance;
                $distanceArray[$key] = $distance;
                $listOfPOINearbyProcessed[$key] = $listOfPOINearbyPreprocessed[$key];
            } //else too distant
        }
        array_multisort($distanceArray, SORT_ASC, $listOfPOINearbyProcessed);
        if (!$listOfPOINearbyProcessed) {
            return false;
        }
        $this->logger->log(
            4,
            'Count of rows listOfPOINearbyProcessed: ' . count($listOfPOINearbyProcessed),
            array(11)
        );
        return array(
            'distance_m' => array_key_exists('distance', $listOfPOINearbyProcessed[0]) ?
            (int) floor($listOfPOINearbyProcessed[0]['distance']) :
            (int) $this->backyardConf['geo_rough_distance_limit'],
            'poi_id' => $listOfPOINearbyProcessed[0]['poi_id'],
            //@todo category name instead of category id
            'type_address' => $listOfPOINearbyProcessed[0]['category'] . ' ' . $listOfPOINearbyProcessed[0]['adresa'],
            'address' => $listOfPOINearbyProcessed[0]['adresa'],
            'city' => $listOfPOINearbyProcessed[0]['mesto'],
            'type' => $listOfPOINearbyProcessed[0]['category'], //@todo category name instead of category id
            'lng' => (float) $listOfPOINearbyProcessed[0]['lng'],
            'lat' => (float) $listOfPOINearbyProcessed[0]['lat']
        );
    }

    /**
     * Returns the list of POI from the requested category(-ies)
     * @todo - limit by lat/lng not to return all POIs
     *
     * @param int|string $poiCategory (may be integer or string with comma separated integers)
     * @param BackyardMysqli $poiConnection
     * @return array<array<mixed>>|false
     *
     * bacykard_getListOfPOINearby ($poiCategory, $lat , $long) might be created to preselect from the database
     * only those that do not overpass the perpendicular backyardGeo['rough_distance_limit']
     */
    public function getListOfPOI($poiCategory, BackyardMysqli $poiConnection)
    {
        $poiCategorySecured = is_int($poiCategory) ? $poiCategory : preg_replace("/[^0-9,]/", '', $poiCategory);
        $query = "SELECT * FROM `{$this->backyardConf['geo_poi_list_table_name']}`"
            . " WHERE `category` IN ( " . (string) $poiCategorySecured . " )";
        $listOfPOINearby = $poiConnection->queryArray($query);
        if (!$listOfPOINearby) {
            $this->logger->log(2, 'No result for query ' . $query, array(11));
        } else {
            $this->logger->log(4, 'Count of rows listOfPOINearby: ' . count($listOfPOINearby), array(11));
        }
        return $listOfPOINearby;
    }

    /**
     * @desc Calculates distnace between $point1 and $point2 denominated in $uom (unit of measurement)
     * http://forums.phpfreaks.com/topic/150365-counting-distance-between-2-gps-points/
     * @param array<float> $point1 ['latitude','longitude']
     * @param array<float> $point2 ['latitude','longitude']
     * @param string $uom 'km','m','miles','yards','yds','feet','ft','nm' - default is km
     * @return float distance
     * @throws \Exception If unknown unit of measurement is used
     */
    public function calculateDistanceFromLatLong($point1, $point2, $uom = 'km')
    {
        //  Uses Haversine formula to calculate the great circle distance
        //  between two points identified by longitude and latitude
        switch (strtolower($uom)) {
            case 'km':
                $earthMeanRadius = 6371.009; // km
                break;
            case 'm':
                $earthMeanRadius = 6371.009 * 1000; // km
                break;
            case 'miles':
                $earthMeanRadius = 3958.761; // miles
                break;
            case 'yards':
            case 'yds':
                $earthMeanRadius = 3958.761 * 1760; // miles
                break;
            case 'feet':
            case 'ft':
                $earthMeanRadius = 3958.761 * 1760 * 3; // miles
                break;
            case 'nm':
                $earthMeanRadius = 3440.069; // miles
                break;
        }
        if (!isset($earthMeanRadius)) {
            throw new \Exception('Unknown unit of measurement');
        }
        $deltaLatitude = deg2rad($point2['latitude'] - $point1['latitude']);
        $deltaLongitude = deg2rad($point2['longitude'] - $point1['longitude']);
        $a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2) +
            cos(deg2rad($point1['latitude'])) * cos(deg2rad($point2['latitude'])) *
            sin($deltaLongitude / 2) * sin($deltaLongitude / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthMeanRadius * $c;
    }
}
