<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;

// GET PHOTO
if($image_id = $fsip->findID($_GET['id'])){
	$location = LOCATION . BASE . ADMINFOLDER . 'image' . URL_ID . $image_id . URL_RW;
	$fsip::headerLocationRedirect($location);
	exit();
}

$location = BASE . ADMINFOLDER . 'library' . URL_CAP;
$fsip::headerLocationRedirect($location);
exit();

?>