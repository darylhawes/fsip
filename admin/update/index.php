<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

print "DEBUG: including file ". './' . $db->db_type . '.php';

include('./' . $db->db_type . '.php');

echo "DEBUG: redirect would happen here to admin folder";
$location = LOCATION . BASE . ADMINFOLDER;
//headerLocationRedirect($location);
exit();
		
		
?>