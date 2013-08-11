<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
$user->hasPermission('admin', true);

if (empty($_POST['image_id'])) {
	$count = $_SESSION['fsip']['tasks'];
	
	for($i=1; $i <= $count; $i++) {
		$tasks[] = $i;
	}
	
	echo removeNullFromJSON(json_encode($tasks));
} else {
	$prbit = new Orbit;
	$prbit->executeTask($_POST['image_id']);
}

?>