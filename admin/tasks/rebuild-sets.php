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

require_once('../../config.php');

$user = new User;
$user->hasPermission('admin', true);

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