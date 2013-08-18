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

require_once('./../../config.php');

$image_ids = new Find('images');
$image_ids->privacy('public', true);
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes('medium');
echo json_encode($images);

?>