<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');

$image_ids = new Find('images');
$image_ids->privacy('public', true);
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes('medium');
echo json_encode($images);

?>