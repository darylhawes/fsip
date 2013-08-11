<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
$user->hasPermission('admin', true);

$id = findID(@$_POST['image_id']);

if (empty($id)) {
	$image_ids = new Find('images', null, null, null, false);
	$image_ids->find();
	echo json_encode($image_ids->ids);
} else {
	$images = new Image($id);
	$sizes = $images->getSizes();
	$image = $images->images[0];
	$src = $image['image_file'];
	
	$dir = '';
	
	if (returnConf('image_hdm') == true) {
		if (returnConf('image_hdm_format') == 'yyyy/mm/dd') {
			$dir = substr($image['image_uploaded'], 0, 10);
			$dir = str_replace('-', '/', $dir);
		} elseif(returnConf('image_hdm_format') == '1000') {
			if ($image['image_id'] < 1000) {
				$dir = '0000';
			} else {
				$dir = substr($image['image_id'], 0, -3) . '000';
			}
		}
		
		$dir .= '/';
	}
	
	$path = Files::correctWinPath(PATH . IMAGEDATA . $dir);
	$dest = $path . $image['image_id'] . '.' . $image['image_ext'];
	
	if ($src != $dest) {
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}
		
		$success = true;
		
		rename($src, $dest);
		
		foreach($sizes as $size) {
			$src = $size['size_file'];
			$dest = Files::correctWinPath(PATH . IMAGEDATA . $dir . $size['size_prepend'] . $image['image_id'] . $size['size_append'] . '.' . $image['image_ext']);
			
			rename($src, $dest);
		}
		
		$images->updateFields(array('image_directory' => $dir));
	}
}

?>