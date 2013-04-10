<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('config.php');

global $db;
$db->recordStat('slideshow');

$image_ids = new Find('images');
$image_ids->sort('images.image_published', 'DESC');
$image_ids->privacy('public');
$image_ids->find();

$images = new Image($image_ids);
$images->formatTime();
$images->getSizes();
$images->getEXIF();
$images->getTags();
$images->getRights();
$images->getComments();

$header = new Canvas;
$header->load('slide_header');
$header->setTitle('Slideshow');
$header->display();

$slideshow = new Canvas;
$slideshow->load('slide');
$slideshow->slideshow();
$slideshow->loop($images);
$slideshow->display();

$header = new Canvas;
$header->load('slide_footer');
$header->display();

?>