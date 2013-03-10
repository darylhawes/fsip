<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$fsip->recordStat('home');

if (!empty($_GET['with']) {
	$with_id = $fsip->findID($_GET['with'], true);
}
if (!$with_id and !empty($_GET['with'])) { $fsip->addError('No image was found.', 'Try searching for the image you were seeking.', null, null, 404); }

$image_ids = new Find('images');
$image_ids->page(null, 12, 1);
if ($with_id) { $image_ids->with($with_id); }
$image_ids->published();
$image_ids->privacy('public');
$image_ids->sort('image_published', 'DESC');
$image_ids->find();

$images = new Image($image_ids);
$images->formatTime();
$images->getSizes();
$images->getEXIF();
$images->getColorkey(950, 15);
$images->getSets();
$images->getTags();
$images->getRights();
$images->getPages();
$images->getComments();
$images->addSequence('medium_last', 3);
$images->hook();

$header = new Canvas;
$header->load('header');
$header->setTitle('Welcome');
$header->display();

$page_ids = new Find('pages');
$page_ids->find();

$pages = new Page($page_ids);

$set_ids = new Find('sets');
$set_ids->find();

$sets = new Set($set_ids);

$directory = new Canvas;
$directory->load('directory');
$directory->loop($pages);
$directory->loop($sets);
$directory->display();

$index = new Canvas;

if ($image_ids->page == 1) {
	$index->load('index');
} else {
	$index->load('index_sub');
}

$index->assign('Page_Next', $image_ids->page_next);
$index->assign('Page_Previous', $image_ids->page_previous);
$index->assign('Page_Next_URI', $image_ids->page_next_uri);
$index->assign('Page_Previous_URI', $image_ids->page_previous_uri);
$index->assign('Page_Current', $image_ids->page);
$index->assign('Page_Count', $image_ids->page_count);
$index->loop($images);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>