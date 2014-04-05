<?php

$backyardGeo = array(
    'rough_distance_limit' => 1,
    'maximum_meters_from_poi' => 2500,
    'table_name' => 'poi_list'
);

if(file_exists(__BACKYARDROOT__."/conf/conf_private.php")) include_once (__BACKYARDROOT__."/conf/conf_private.php");//conf_private.php should be in .gitignore so that each developer may redefine its development environment
