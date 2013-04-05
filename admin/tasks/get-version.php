<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
$user->userHasPermission('admin', true);

$dbpointer = getDB();

if (!empty($_POST['id'])) {
	$version = $dbpointer->getRow('versions', $_POST['id']);
	echo removeNull(json_encode($version));
}

?>