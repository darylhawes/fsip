<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

$user = new User;

// Require permission to access the dashboard or redirect the user to the login page.
if ($user->hasPermission('dashboard', true)) {
	$location = LOCATION . BASE. ADMINFOLDER . 'dashboard' . URL_CAP;
	headerLocationRedirect($location);
}

exit();

?>