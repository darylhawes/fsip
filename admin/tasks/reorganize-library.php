<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

$user->perm(true);

$id = $fsip->findID(@$_POST['image_id']);

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
	
	if ($fsip->returnConf('image_hdm') == true) {
		if ($fsip->returnConf('image_hdm_format') == 'yyyy/mm/dd') {
			$dir = substr($image['image_uploaded'], 0, 10);
			$dir = str_replace('-', '/', $dir);
		} elseif($fsip->returnConf('image_hdm_format') == '1000') {
			if ($image['image_id'] < 1000) {
				$dir = '0000';
			} else {
				$dir = substr($image['image_id'], 0, -3) . '000';
			}
		}
		
		$dir .= '/';
	}
	
	$path = $fsip->correctWinPath(PATH . IMAGEDATA . $dir);
	$dest = $path . $image['image_id'] . '.' . $image['image_ext'];
	
	if ($src != $dest) {
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}
		
		$success = true;
		
		rename($src, $dest);
		
		foreach($sizes as $size) {
			$src = $size['size_file'];
			$dest = $fsip->correctWinPath(PATH . IMAGEDATA . $dir . $size['size_prepend'] . $image['image_id'] . $size['size_append'] . '.' . $image['image_ext']);
			
			rename($src, $dest);
		}
		
		$images->updateFields(array('image_directory' => $dir));
	}
}

?>