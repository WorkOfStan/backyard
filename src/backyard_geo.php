<?php
/**
 * Name: functions_geo.php
 * Project: Backyard
 * 
 ****** Purpose: 
 * Geo Functions pro tento projekt
 * 
 * 
 ****** History
 *  2013-07-30, v.1 - vzato z functions.php v Píchačky 2012
 *  2014-02-18, v.2 - vzato z checkin2013 a předěláno do univerzálnější podoby
 * 
 * 
 ****** Description
 * array getClosestPOI (float $lat, float $long,int $poiCategory)
 * float calculateDistanceFromLatLong( array('latitude','longitude') $point1, array('latitude','longitude') $point2, string $uom='km')
 */

/**
 * @TODO přepsat do srozumitelna ..
 * https://www.alfa.gods.cz/bugzilla/show_bug.cgi?id=509#c9
geomodul:

použito checkin (původní codename byl píchačky)
popsáno v https://github.com/GodsDev/repo1/blob/master/checkin/readme.php
ale nyní by ti stačily info zde níže:


když si do db dáš tu přílohu POI TMCZ do table pichacky_poi , 
tak by ti z https://github.com/GodsDev/repo1/blob/master/checkin/functions.php
měly fungovat funkce
function nejblizsiPOI ($lat,$long)
která volá
function GetListOfPOINearby($lat, $long)
a
function calculateDistanceFromLatLong()

s tím, že v tom samém PHP je definováno
$backyardGeo['rough_distance_limit']=0.5;
$backyardGeo['maximum_meters_from_poi']=2500;
.. které je použito přes global $backyardGeo;

Dále jsou v nich použity funkce:
function my_error_log($message, $level = 0, $error_number = 0) .. logování .. pro tento případ OPTIONAL
function customMySQLQuery($query, $justOneRow = false) .. volá $query, vrací pole
z https://github.com/GodsDev/repo1/blob/master/lib/functions.php

Voláno bylo takto:
https://github.com/GodsDev/repo1/blob/master/checkin/api_2.php s GET parametry lat=&lng=&event=odpichGPS a odpovědí byl JSON vypočítaný jako:
$jsonResult = GetStatusJSON ($userId,$inputLat,$inputLong)
/definováno také v https://github.com/GodsDev/repo1/blob/master/checkin/functions.php /

Voláno bylo z javascript funkce:
function odpichGPS(lat, lng)
v https://github.com/GodsDev/repo1/blob/master/checkin/js/js.js volané tamtéž.
 */


//podmíněné definování funkcí použitých v geo funkcích pro případ, že https://github.com/GodsDev/repo1/blob/master/lib/functions.php není included
if (!function_exists('my_error_log')) {
    include_once 'functions_my_error_log_dummy.php';
}

require_once __BACKYARDROOT__."/conf/geo.php";//$backyardGeo

/**
 * 
 * @global type $backyardGeo
 * @param float $lat
 * @param float $long
 * @param int (@TODO array) $poiCategory dle tabulky poi_list - nastavené v conf/geo.php
 * @return array
 */
function getClosestPOI ($lat,$long,$poiCategory){
    global $backyardGeo;
    my_error_log("nejblizsiPOI: lat={$lat} long={$long}",4);
    $uom = 'm';
    $startPoint = array(
        'latitude'  => $lat,           //current
        'longitude' => $long
    );
    
    $listOfPOINearby = GetListOfPOINearby($lat, $long, $poiCategory);
    if(!$listOfPOINearby) return false;
    
    $listOfPOINearbyPreprocessed = array();
    $roughtDistance = array();
    
    foreach ($listOfPOINearby as $key => $row) {
        $listOfPOINearbyPreprocessed[$key]=array(
            'poi_id' => $row['poi_id'],            
            'category' => $row['category'],
            'typ' => $row['typ'],
            'mesto' => $row['mesto'],
            'PSC' => $row['PSC'],
            'adresa' => $row['adresa'],
            'lng' => $row['long'],
            'lat' => $row['lat'],
            'roughDistance' => abs($long - $row['long'])+abs($lat - $row['lat'])
        );
        $roughDistance[$key]=$listOfPOINearbyPreprocessed[$key]['roughDistance'];        
    }
    
    array_multisort($roughDistance, SORT_ASC, $listOfPOINearbyPreprocessed);
    
    $distanceArray = array();
    $listOfPOINearbyProcessed = array();
    foreach($listOfPOINearbyPreprocessed as $key => $row) {
        if($row['roughDistance']<$backyardGeo['rough_distance_limit']){
            $distance = calculateDistanceFromLatLong (
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
        } //else více jak cca 50km
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
 *  Vrátí seznam relevantních POI (Lat, long, poiid) z table pichacky_tm_poi (R/O: PoiID, Name of POI, Lat, Long)
 * False/Array getListOfPOINearby (Lat, long, poiid)
 * ... Vybirat podle hranicnich bodu s velkym rozptylem, at nenacitam vsechny poi
 * @param type $lat
 * @param type $long
 * @return array 
 */
function GetListOfPOINearby ($lat, $long, $poiCategory){
    //@TODO - $category nechť je i jako array
    //@TODO - validity check
    global $backyardGeo;    
    $listOfPOINearby = false;//default value ukazující, že něco selhalo
    //$query = "SELECT * FROM `pichacky_poi` WHERE `category` =1 AND (`typ` ='Prodejna' OR `typ` ='Admin. budova + Prodejna') ";//@TODO přidat where category==1 neboť to jsou TMCZ objekty //@TODO .. rozlišit kategorie dle použité app, tj. jiné pro skauty a jiné pro T-Check, nyní pro skauty
    $query = "SELECT * FROM `{$backyardGeo['table_name']}` WHERE `category` =" . (int)$poiCategory;
    $listOfPOINearby = customMySQLQuery($query);
    if (!$listOfPOINearby){
        my_error_log('No result for query '.$query,2,11);
    }
    return $listOfPOINearby;
}

/**
 * http://forums.phpfreaks.com/topic/150365-counting-distance-between-2-gps-points/    
 * @param array('latitude','longitude') $point1
 * @param array('latitude','longitude') $point2
 * @param string $uom 'km','m','miles','yards','yds','feet','ft','nm'd
 * @return float
 */
function calculateDistanceFromLatLong($point1,$point2,$uom='km') {
        //      Use Haversine formula to calculate the great circle distance
        //              between two points identified by longitude and latitude
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
}       //      function calculateDistanceFromLatLong()
