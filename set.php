<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$fsip->recordStat('set');

$id = $fsip->findID($_GET['id']);
if (!$id) { $fsip->addError('No set was found.', 'Try searching for the set you were seeking.', null, null, 404); }

$set = new Set($id);
$set = @$set->sets[0];
if (!$set) { $fsip->addError('No set was found.', 'Try searching for the set you were seeking.', null, null, 404); }

$set['set_created'] = $fsip->formatTime($set['set_created']);
$set['set_modified'] = $fsip->formatTime($set['set_modified']);

$image_ids = new Find('images');
$image_ids->page(null, 0);
$image_ids->published();
$image_ids->privacy('public');
$image_ids->sets(intval($set['set_id']));
$image_ids->find();

$images = new Image($image_ids);
$images->formatTime();
$images->getSizes('small');
$images->getEXIF();
$images->getTags();
$images->getRights();

$header = new Canvas;
$header->load('header');
$header->setTitle(@$set['set_title']);
$header->assign('Canonical', $set['set_uri']);
$header->display();

$content = new Canvas;
$content->load('set');
$content->loop($images);
$content->assignArray($set);
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>