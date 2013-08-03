<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
$user->hasPermission('admin', true);

$valid = false;

if (isset($_REQUEST['min'])) {
	$_SESSION['fsip']['maintenance']['series']['min'] = $_REQUEST['min'];
	$valid = true;
}
if (isset($_REQUEST['max'])) {
	$_SESSION['fsip']['maintenance']['series']['max'] = $_REQUEST['max'];
	$valid = true;
}

if (!empty($_REQUEST['series'])) {
	if ($valid == true) {
		$location = LOCATION . BASE. ADMINFOLDER . 'maintenance' . URL_CAP . '#rebuild-thumbnail-series';
		headerLocationRedirect($location);
		exit();
	} else {
		addNote('You must select a valid series when rebuilding thumbnails by series.', 'error');
		$location = LOCATION . BASE. ADMINFOLDER . 'maintenance' . URL_CAP;
		headerLocationRedirect($location);
		exit();
	}
}

if (empty($_POST['image_id'])) {
	$image_ids = range($_SESSION['fsip']['maintenance']['series']['min'], $_SESSION['fsip']['maintenance']['series']['max']);
	$image_ids = new Find('images', $image_ids, null, null, false);
	$image_ids->find();
	echo json_encode($image_ids->ids);
} else {
	$image = new Image($_POST['image_id']);
	$image->deSizeImage();
	$image->sizeImage();
}

?>