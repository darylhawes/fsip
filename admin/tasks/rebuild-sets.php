<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
$user->userHasPermission('admin', true);

$id = findID(@$_POST['image_id']);

if (empty($id)) {
	$sets = new Find('sets');
	$sets->find();
	
	echo json_encode($sets->ids);
} else {
	$set = new Set(intval($id));
	$set->rebuild();
}

?>