<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
$user->hasPermission('admin', true);

global $db;

if (!empty($_POST['id'])) {
	$version = $db->getRow('versions', $_POST['id']);
	echo removeNullFromJSON(json_encode($version));
}

?>