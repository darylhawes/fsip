<?php

/**
 * FSIP based on Alkaline
 * 
 *
 * http://www.alkalineapp.com/
 * Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
 *
 * @package FSIP
 * @since 1.2
 */

require_once('config.php');

$user = new User;

if($user->deauth()){
	addNote('You successfully logged out.', 'success');
}

$location = LOCATION . BASE  . 'login' . URL_CAP;
headerLocationRedirect($location);
exit();

?>