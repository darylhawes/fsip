<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

if($user->perm()){
	header('Location: ' . LOCATION . BASE . ADMIN . 'dashboard' . URL_CAP);
	exit();
}
else{
	header('Location: ' . LOCATION . BASE . ADMIN . 'login' . URL_CAP);
	exit();
}

?>