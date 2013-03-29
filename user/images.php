<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');

// GET PHOTO
if($image_id = findID($_GET['id'])){
	$location = LOCATION . BASE . ADMINFOLDER . 'image' . URL_ID . $image_id . URL_RW;
	headerLocationRedirect($location);
	exit();
}

$location = BASE . ADMINFOLDER . 'library' . URL_CAP;
headerLocationRedirect($location);
exit();

?>