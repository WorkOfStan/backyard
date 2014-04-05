<?php
//backyard 2 compliant
if (!function_exists('my_error_log')) {
    include_once 'backyard_my_error_log_dummy.php';
}

require_once __BACKYARDROOT__."/conf/geo.php";//$backyardGeo

/******************************************************************************
 * GEOLOCATION FUNCTIONS
 * 
 ****** History
 * 2013-07-30, v.1 - vzato z functions.php v Píchačky 2012
 * 2014-02-18, v.2 - vzato z checkin2013 a předěláno do univerzálnější podoby
 * 2014-05-05, v.3 - modified for backyard 2
 * 
 * 
 ****** Description
 * Given that global variable $backyardGeo['table_name'] defined in conf/geo.php 
 * and potentially customized in conf/conf_private.php
 * contains name of the table with points of interest then function ..
 * array backyard_getClosestPOI (float $lat, float $long, int $poiCategory)
 * .. returns associative array of the closest POI.
 * 
 * To do that it uses following material functions:
 * array backyard_getListOfPOINearby (float $lat, float $long, int $poiCategory){
 * float backyard_calculateDistanceFromLatLong( array('latitude','longitude') $point1, array('latitude','longitude') $point2, string $uom='km')
 * 
 * And also following support functions are used:
 * function my_error_log($message, $level = 0, $error_number = 0) .. logging
 * mixed backyard_mysqlQueryArray(string $query, bool $justOneRow, resource $link_identifier) .. MySQL query. Therefore backyard_mysql.php is expected to be included in advance.
 * 
 * Global variable $backyardGeo contains also
 * $backyardGeo['rough_distance_limit']=1;          //to quickly get rid off too distant POIs
 * $backyardGeo['maximum_meters_from_poi']=2500;    //distance considered to be overlapping with the device position, not used in backyard_geo.php
 * 
 * 
 * @TODO 4 - dovysvětlit -
 * Voláno bylo takto:
https://github.com/GodsDev/repo1/blob/master/checkin/api_2.php s GET parametry lat=&lng=&event=odpichGPS a odpovědí byl JSON vypočítaný jako:
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
 * 
 */

/**
 * Returns associative array of the closest POI.
 * Given that global variable $backyardGeo['table_name'] defined in conf/geo.php 
 * and potentially customized in conf/conf_private.php
 * contains name of the table with points of interest.
 * 
 * @global array $backyardGeo
 * @param float $lat
 * @param float $long
 * @param mixed $poiCategory (may be integer or string with comma separated integers) according to table poi_list - set in conf/geo.php
 * @return array
 */
function backyard_getClosestPOI ($lat,$long,$poiCategory){
    global $backyardGeo;
    my_error_log("Looking for closest POI: lat={$lat} long={$long}",4);
    $uom = 'm';
    $startPoint = array(
        'latitude'  => $lat,           //current
        'longitude' => $long
    );
    
    $listOfPOINearby = backyard_getListOfPOI($poiCategory);
    if(!$listOfPOINearby) return false;
    
    $listOfPOINearbyPreprocessed = array();
    $roughtDistance = array();
    
    foreach ($listOfPOINearby as $key => $row) {
        $listOfPOINearbyPreprocessed[$key]=array(
            'poi_id'        => $row['poi_id'],            
            'category'      => $row['category'],
            'typ'           => $row['typ'],
            'mesto'         => $row['mesto'],
            'PSC'           => $row['PSC'],
            'adresa'        => $row['adresa'],
            'lng'           => $row['long'],
            'lat'           => $row['lat'],
            'roughDistance' => abs($long - $row['long'])+abs($lat - $row['lat'])
        );
        $roughDistance[$key]=$listOfPOINearbyPreprocessed[$key]['roughDistance'];        
    }
    
    array_multisort($roughDistance, SORT_ASC, $listOfPOINearbyPreprocessed);
    
    $distanceArray = array();
    $listOfPOINearbyProcessed = array();
    foreach($listOfPOINearbyPreprocessed as $key => $row) {
        if($row['roughDistance']<$backyardGeo['rough_distance_limit']){
            $distance = backyard_calculateDistanceFromLatLong (
                $startPoint,
                array(
                    'latitude' => $row['lat'],
                    'longitude' => $row['lng']
                ),
                $uom
            );
            $listOfPOINearbyPreprocessed[$key]['distance']=$distance;
            $distanceArray[$key]=$distance;
            $listOfPOINearbyProcessed[$key]=$listOfPOINearbyPreprocessed[$key];            
        } //else too distant
    }
    array_multisort($distanceArray, SORT_ASC, $listOfPOINearbyProcessed);
    if(!$listOfPOINearbyProcessed) return false;
    return array(
        'distance_m'    =>  (int)floor($listOfPOINearbyProcessed[0]['distance']), 
        'poi_id'        =>  $listOfPOINearbyProcessed[0]['poi_id'], 
        'type_address'  =>  $listOfPOINearbyProcessed[0]['typ'].' '.$listOfPOINearbyProcessed[0]['adresa'],
        'address'       =>  $listOfPOINearbyProcessed[0]['adresa'],
        'city'          =>  $listOfPOINearbyProcessed[0]['mesto'],
        'type'          =>  $listOfPOINearbyProcessed[0]['typ'],
        'lng'           =>  (float)$listOfPOINearbyProcessed[0]['lng'],
        'lat'           =>  (float)$listOfPOINearbyProcessed[0]['lat']           
    );
}

/**
 * Returns the list of POI from the requested category(-ies)
 * @global array $backyardGeo
 * @param mixed $poiCategory (may be integer or string with comma separated integers)
 * @return mixed (array or false)
 * 
 * bacykard_getListOfPOINearby ($poiCategory, $lat , $long) might be created to preselect from the database only those that do not overpass the perpendicular backyardGeo['rough_distance_limit']
 */
function backyard_getListOfPOI ($poiCategory){
    global $backyardGeo;
    if(is_int($poiCategory)){
        $poiCategorySecured = $poiCategory;
    } else {
        $poiCategorySecured = preg_replace("/[^0-9,]/",'',$poiCategory);
    }
    //$query = "SELECT * FROM `pichacky_poi` WHERE `category` =1 AND (`typ` ='Prodejna' OR `typ` ='Admin. budova + Prodejna') ";//@TODO přidat where category==1 neboť to jsou TMCZ objekty //@TODO .. rozlišit kategorie dle použité app, tj. jiné pro skauty a jiné pro T-Check, nyní pro skauty
    $query = "SELECT * FROM `{$backyardGeo['table_name']}` WHERE `category` IN ( " . $poiCategorySecured . " )";
    $listOfPOINearby = customMySQLQuery($query);
    if (!$listOfPOINearby){
        my_error_log('No result for query '.$query,2,11);
    }
    return $listOfPOINearby;
}

/**
 * @desc Calculates distnace between $point1 and $point2 denominated in $uom (unit of measurement)
 * http://forums.phpfreaks.com/topic/150365-counting-distance-between-2-gps-points/    
 * @param array('latitude','longitude') $point1
 * @param array('latitude','longitude') $point2
 * @param string $uom 'km','m','miles','yards','yds','feet','ft','nm' - default is km
 * @return float
 */
function backyard_calculateDistanceFromLatLong($point1,$point2,$uom='km') {
    //  Uses Haversine formula to calculate the great circle distance
    //  between two points identified by longitude and latitude
    switch (strtolower($uom)) {
        case 'km'       :
            $earthMeanRadius = 6371.009; // km
            break;
        case 'm'        :
            $earthMeanRadius = 6371.009 * 1000; // km
            break;
        case 'miles'    :
            $earthMeanRadius = 3958.761; // miles
            break;
        case 'yards'    :
        case 'yds'      :
            $earthMeanRadius = 3958.761 * 1760; // miles
            break;
        case 'feet'     :
        case 'ft'       :
            $earthMeanRadius = 3958.761 * 1760 * 3; // miles
            break;
        case 'nm'       :
            $earthMeanRadius = 3440.069; // miles
            break;
    }
    $deltaLatitude = deg2rad($point2['latitude'] - $point1['latitude']);
    $deltaLongitude = deg2rad($point2['longitude'] - $point1['longitude']);
    $a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2) +
         cos(deg2rad($point1['latitude'])) * cos(deg2rad($point2['latitude'])) *
         sin($deltaLongitude / 2) * sin($deltaLongitude / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthMeanRadius * $c;
    return $distance;
}   //      function calculateDistanceFromLatLong()
