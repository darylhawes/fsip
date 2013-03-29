<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$dbpointer = getDB();

$user = new User;
$user->perm(true);

$markup = returnConf('web_markup_ext');

$query = $dbpointer->prepare('SELECT image_id FROM images WHERE image_markup != :image_markup;');
$query->execute(array(':image_markup' => $markup));
$images = $query->fetchAll();

$image_ids = array();

foreach($images as $image) {
	$image_ids[] = $image['image_id'];
}

if (count($image_ids) > 0) {
	$query = $dbpointer->prepare('UPDATE images SET image_description_raw = image_description, image_markup = :image_markup WHERE (image_id IN (' . implode(', ', $image_ids) . '));');
	$query->execute(array(':image_markup' => $markup));
}

?>