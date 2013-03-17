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

$user->perm(true);

Find::clearMemory();

if (!empty($_REQUEST['search_type'])) {
	$table = $_REQUEST['search_type'];
} else {
	$table = 'images';
}

$ids = new Find($table);
$ids->find();
$ids->saveMemory();

if ($table == 'images') {
	$location = LOCATION . BASE. ADMINFOLDER . 'results' . URL_CAP;
	$fsip::headerLocationRedirect($location);
	exit();
} else {
	$location = LOCATION . BASE. ADMINFOLDER . $table . URL_ACT . 'results' . URL_RW;
	$fsip::headerLocationRedirect($location);
	exit();
}

?>