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

$image_ids = new Find('images');
$image_ids->sort('image_uploaded', 'DESC');
$image_ids->page(1, 100);
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes();

if (returnConf('post_size_label')) {
	$label = 'image_src_' . returnConf('post_size_label');
} else {
	$label = 'image_src_admin';
}

foreach($images->images as $image) {
	$image['image_title'] = makeHTMLSafe($image['image_title']);
	echo '<a href="' . $image[$label] . '"><img src="' . $image['image_src_square'] .'" alt="' . $image['image_title']  . '" class="frame" id="image-' . $image['image_id'] . '" /></a>';
	echo '<div class="none uri_rel image-' . $image['image_id'] . '">' . $image['image_uri_rel'] . '</div>';
}

?>