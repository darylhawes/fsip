<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

$user->perm(true);

$image_ids = new Find('images');
$image_ids->sort('image_uploaded', 'DESC');
$image_ids->page(1, 100);
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes();

if($fsip->returnConf('post_size_label')){
	$label = 'image_src_' . $fsip->returnConf('post_size_label');
}
else{
	$label = 'image_src_admin';
}

foreach($images->images as $image){
	$image['image_title'] = $fsip->makeHTMLSafe($image['image_title']);
	echo '<a href="' . $image[$label] . '"><img src="' . $image['image_src_square'] .'" alt="' . $image['image_title']  . '" class="frame" id="image-' . $image['image_id'] . '" /></a>';
	echo '<div class="none uri_rel image-' . $image['image_id'] . '">' . $image['image_uri_rel'] . '</div>';
}

?>