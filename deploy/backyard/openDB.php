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
 *
 ** 
 * TODO  
 * 
 * 
 */
 //Open Database
	$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
	mysql_select_db($dbname, $conn);
