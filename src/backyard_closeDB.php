<?php
/**
 * Name: closeDB.php
 * Project: LIB/Part of Library In Backyard
 * 
 ** 
 * Purpose: 
 * uzavření databáze - párový include k openDB.php
 * 
 * 
 ** 
 * History
 * 131208, generic link identifier $conn changed to $backyardConnection ($conn temporarily left here for backward compatibility)
 *
 ** 
 * TODO  
 * 
 * 
 */
/* OBSOLETE
if(isset($conn)){//OBSOLETE, keep for backward compatibility    
	mysql_close($conn);
}
 */
if(isset($backyardConnection)){
    mysql_close($backyardConnection);    
}
