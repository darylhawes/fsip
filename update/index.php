<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/


require_once('../config.php');
echo "in update/index.php 1<br />";
global $db;

$builds = array(918, 928, 1000, 1100, 1200);
echo "in update/index.php 2<br />";

foreach($builds as $build) {
	// Import default SQL
	$queries = file_get_contents(PATH . 'update/' . $build . '/' . $db->db_type . '.sql');
	$queries = explode("\n", $queries);
echo "in update/index.php 3<br />";

	foreach($queries as $query) {
echo "in update/index.php 3.1: $query<br />";
		$query = trim($query);
		if (!empty($query)) {
			$db->exec($query);
		}
	}
}
echo "in update/index.php 4<br />";

addNote('You have successfully updated FSIP. You should now remove the /update directory.', 'success');

$location = LOCATION . BASE . ADMINFOLDER;
headerLocationRedirect($location);
exit();

?>