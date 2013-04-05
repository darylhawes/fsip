<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

$user = new User;

if ($user->userIsLoggedIn()) {
	$page = "dashboard";
} else {
	$page = "login";
}

$location = LOCATION . BASE . USERFOLDER . $page . URL_CAP;
headerLocationRedirect($location);
exit();

?>