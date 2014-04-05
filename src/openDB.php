<?php
/**
 * Name: ---.php
 * Project: LIB/Part of Library In Backyard
 * 
 ** 
 * Purpose: 
 * 
 * 
 * 
 ** 
 * History
 * 2013-02-24, přidán link identifier do mysql_select_db
 * 131208, generic link identifier $conn changed to $backyardConnection ($conn temporarily left here for backward compatibility)
 *
 ** 
 * TODO  
 * 
 * 
 */
 //Open Database
/* OBSOLETE
	$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql'); //OBSOLETE, keep for backward compatibility    
	mysql_select_db($dbname, $conn); //OBSOLETE, keep for backward compatibility    
*/
if(isset($backyardDatabase)){
    $backyardConnection = mysql_connect($backyardDatabase['dbhost'], $backyardDatabase['dbuser'], $backyardDatabase['dbpass']) 
            or die ('Error connecting to mysql');
    mysql_select_db($backyardDatabase['dbname'], $backyardConnection);    
}        
