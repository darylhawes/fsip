<?php

/**
 * FSIP based on Alkaline
 * 
 *
 * @package FSIP
 * @author Daryl Hawes
 * @version 1.2
 * @since 1.2
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