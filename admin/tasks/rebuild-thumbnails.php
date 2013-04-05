<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
$user->userHasPermission('admin', true);

if (empty($_POST['image_id'])) {
	$image_ids = new Find('images', null, null, null, false);
	$image_ids->find();
	echo json_encode($image_ids->ids);
} else {
	$image = new Image($_POST['image_id']);
	$image->deSizeImage();
	$image->sizeImage();
}

?>