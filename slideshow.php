<?php

/**
 * FSIP based on Alkaline
 * 
 *
 * http://www.alkalineapp.com/
 * Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
 *
 * @package FSIP
 * @since 1.2
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