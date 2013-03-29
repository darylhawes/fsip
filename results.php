<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('config.php');

$dbpointer = getDB();

$dbpointer->recordStat('home');

if (empty($_SESSION['fsip']['search']['table'])) {
	$location = LOCATION . BASE . 'search' . URL_CAP;
	headerLocationRedirect($location);
	exit();
}

if ($_SESSION['fsip']['search']['table'] == 'images') {
	$image_ids = new Find('images', $_SESSION['fsip']['search']['images']['ids']);
	$image_ids->page();
	$image_ids->find();

	$images = new Image($image_ids);
	$images->formatTime();
	$images->getSizes();

	$count = $image_ids->count;
	$model = $image_ids;
	$loop = $images;

	$content = new Canvas;
	$content->load('results-images');
}

$header = new Canvas;
$header->load('header');
$header->setTitle('Search Results (' . $count . ')');
$header->display();

$content->assign('Results_Count', $count, true);
$content->assign('Page_Next', $model->page_next);
$content->assign('Page_Previous', $model->page_previous);
$content->assign('Page_Next_URI', $model->page_next_uri);
$content->assign('Page_Previous_URI', $model->page_previous_uri);
$content->assign('Page_Current', $model->page);
$content->assign('Page_Count', $model->page_count);
$content->loop($loop);
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();