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

if (empty($_POST['image_file'])) {
	$image_files = $fsip->seekDirectory(PATH . SHOEBOX);
	$image_files = array_reverse($image_files);
	if ($fsip->returnConf('shoe_max')) {
		$image_files = array_splice($image_files, 0, $fsip->returnConf('shoe_max_count'));
	}
	$image_files = array_map('base64_encode', $image_files);
	echo json_encode($image_files);
} else {
	$image = new Image();
	$image->attachUser($user);
	$image->import(base64_decode($_POST['image_file']));
	$image->getSizes('admin');
	$image->updateRelated();
	$tags = $image->getTags(true);
	$image = $image->images[0];
	$tag_names = array();
	foreach($tags as $tag) {
		$tag_names[] = $tag['tag_name'];
	}
	
	if ($user->returnPref('shoe_pub') === true) {
		$image['image_published'] = 'Now';
	}
	
	$image['image_tags'] = $tag_names;
	echo $fsip->removeNull(json_encode($image));
}

?>