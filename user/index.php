<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

$user = new User;

// Require that there is a valid user logged in or redirect the user to the login page.
if ($user->isLoggedIn(true)) {
	$location = LOCATION . BASE. 'user/profile' . URL_CAP;
	headerLocationRedirect($location);
}

exit();
?>