<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

$user = new User;

if ($user->perm()) {
	$location = LOCATION . BASE. ADMINFOLDER . 'dashboard' . URL_CAP;
	headerLocationRedirect($location);
} else {
	$location = LOCATION . BASE. 'login' . URL_CAP;
	headerLocationRedirect($location);
}


exit();

?>