<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

if ($user->perm()) {
	$location = LOCATION . BASE. ADMINFOLDER . 'dashboard' . URL_CAP;
	$fsip::headerLocationRedirect($location);
} else {
	$location = LOCATION . BASE. 'login' . URL_CAP;
	$fsip::headerLocationRedirect($location);
}


exit();

?>