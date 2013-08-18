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

require_once('../config.php');

$user = new User;

// Require that there is a valid user logged in or redirect the user to the login page.
if ($user->isLoggedIn(true)) {
	$location = LOCATION . BASE. 'user/profile' . URL_CAP;
	headerLocationRedirect($location);
}

exit();
?>