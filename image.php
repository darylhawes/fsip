<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$fsip->recordStat('image');
$fsip->addComments();

$id = $fsip->findID($_GET['id'], true);
if (!$id) { 
	$fsip->addError('No image was found.', 'Try searching for the image you were seeking.', null, null, 404); 
}

$image_ids = new Find('images', $id);
$image_ids->published();
$image_ids->privacy('public');
$image_ids->find();

if (!$image_ids->ids) { $fsip->addError('No image was found.', 'Try searching for the image you were seeking.', null, null, 404); }

$images = new Image($image_ids);
$images->updateViews();
$images->formatTime();
$images->getSizes(array('large', 'medium'));
$images->getEXIF();
$images->getColorkey(950, 15);
$images->getTags();
$images->getPages();
$images->getSets();
$images->getRights();
$images->getComments(true);
$images->hook();
$image = $images->images[0];

$header = new Canvas;

$header->load('header');
$header->setTitle(@$image['image_title']);
$header->assign('Canonical', $image['image_uri']);
$header->display();

$content = new Canvas;
$content->wrapForm();
$content->load('image');
$content->loop($images);
$content->display();

$breadcrumb = array('Images' => '/');

$footer = new Canvas;
$footer->load('footer');
$footer->setBreadcrumb($breadcrumb);
$footer->display();

?>