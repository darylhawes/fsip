<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;

$user->hasPermission('admin', true);

if (!empty($_POST['image_id'])) {
	convertToIntegerArray($_POST['image_id']);
	$image = new Image($_POST['image_id']);
	$image->delete();
}

?>