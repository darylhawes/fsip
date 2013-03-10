<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$fsip->recordStat('tag');

$id = $fsip->findID($_GET['id']);
if (!$id) { $fsip->addError('No tag was found.', 'Try searching for the images you were seeking.', null, null, 404); }

$tag = $fsip->getRow('tags', $id);

if (!$tag) { $fsip->addError('No tag was found.', 'Try searching for the images you were seeking.', null, null, 404); }

$image_ids = new Find('images');
$image_ids->page(null, 0);
$image_ids->published();
$image_ids->privacy('public');
$image_ids->tags($id);
$image_ids->find();

$images = new Image($image_ids);
$images->formatTime();
$images->getSizes();
$images->getColorkey(670, 10);
$images->getTags();
$images->getRights();

$header = new Canvas;
$header->load('header');
$header->setTitle('#' . $tag['tag_name']);
$header->display();

$content = new Canvas;
$content->load('tag');
$content->assign('Page_Next', $image_ids->page_next);
$content->assign('Page_Previous', $image_ids->page_previous);
$content->assign('Page_Current', $image_ids->page);
$content->assign('Page_Count', $image_ids->page_count);
$content->loop($images);
$content->assignArray($tag);
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>