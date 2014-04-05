<?php
//backyard 2 compliant
/**
 * Default configuration for backyard_geo.php.
 * May be changed by override in conf_private.php.
 * 
 * DO NOT CHANGE THE DEFAULT VALUES HERE
 */

$backyardGeo = array(
    'rough_distance_limit' => 1,        //float //to quickly get rid off too distant POIs; 1 ~ 100km
    'maximum_meters_from_poi' => 2500,  //float //distance considered to be overlapping with the device position
    'table_name' => 'poi_list'          //string
);

/**
 * Following structure is expected in the table with points of interest reffered to in the table_name
 * 
--
-- Table structure for table `poi_list`
--

CREATE TABLE IF NOT EXISTS `poi_list` (
  `poi_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(11) NOT NULL,
  `typ` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `mesto` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `PSC` char(6) COLLATE utf8_czech_ci NOT NULL,
  `adresa` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `long` double NOT NULL COMMENT 'X',
  `lat` double NOT NULL COMMENT 'Y',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`poi_id`),
  KEY `lat` (`lat`),
  KEY `long` (`long`),
  KEY `category` (`category`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
 *
 */

if(file_exists(__BACKYARDROOT__."/conf/conf_private.php")) include_once (__BACKYARDROOT__."/conf/conf_private.php");//conf_private.php should be in .gitignore so that each developer may redefine its development environment
