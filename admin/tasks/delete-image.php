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

if (!empty($_POST['image_id'])) {
	convertToIntegerArray($_POST['image_id']);
	$image = new Image($_POST['image_id']);
	$image->delete();
}

?>