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
	header('Location: ' . LOCATION . BASE . ADMIN . 'image' . URL_ID . $image_id . URL_RW);
	exit();
}

header('Location: ' . BASE . ADMIN . 'library' . URL_CAP);
exit();

?>