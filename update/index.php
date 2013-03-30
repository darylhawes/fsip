<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/


require_once('../config.php');

$dbpointer = getDB();

$builds = array(918, 928, 1000, 1100, 1200);

foreach($builds as $build) {
	// Import default SQL
	$queries = file_get_contents(PATH . 'update/' . $build . '/' . $dbpointer->db_type . '.sql');
	$queries = explode("\n", $queries);

	foreach($queries as $query) {
		$query = trim($query);
		if (!empty($query)) {
			$dbpointer->exec($query);
		}
	}
}

addNote('You have successfully updated FSIP. You should now remove the /update directory.', 'success');

$location = LOCATION . BASE . ADMINFOLDER;
headerLocationRedirect($location);
exit();

?>