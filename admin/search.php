<?php

/**
 * FSIP based on Alkaline
 * 
 *
 * http://www.alkalineapp.com/
 * Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
 *
 * @package FSIP
 * @subpackage admin
 * @since 1.2
 */

require_once('../config.php');

$user = new User;
$user->hasPermission('admin', true);

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
	headerLocationRedirect($location);
	exit();
} else {
	$location = LOCATION . BASE. ADMINFOLDER . $table . URL_ACT . 'results' . URL_RW;
	headerLocationRedirect($location);
	exit();
}

?>