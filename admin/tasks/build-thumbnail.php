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

if (!empty($_SESSION['fsip']['maintenance']['size_id'])) {
	if (empty($_POST['image_id'])) {
		$image_ids = new Find('images', null, null, null, false);
		$image_ids->find();
		echo json_encode($image_ids->ids);
	} else {
		$image = new Image($_POST['image_id']);
		$image->sizeImage(null, intval($_SESSION['fsip']['maintenance']['size_id']));
	}
}

?>