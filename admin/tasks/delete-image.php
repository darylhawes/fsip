<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

$user->perm(true);

if (!empty($_POST['image_id'])) {
	$fsip->convertToIntegerArray($_POST['image_id']);
	$image = new Image($_POST['image_id']);
	$image->delete();
}

?>