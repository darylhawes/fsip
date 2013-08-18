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

if (empty($_POST['image_file'])) {
	$image_files = Files::seekDirectory(PATH . SHOEBOX);
	$image_files = array_reverse($image_files);
	if (returnConf('shoe_max')) {
		$image_files = array_splice($image_files, 0, returnConf('shoe_max_count'));
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
	echo removeNullFromJSON(json_encode($image));
}

?>