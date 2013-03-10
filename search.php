<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'fsip.php');

if (isset($_REQUEST['type'])) {
	$type = $_REQUEST['type'];
	if ($type == 'images') {
		$ids = new Find('images');
		$ids->sort('images.image_published', 'DESC');
		$ids->published();
		$ids->privacy('public');
	}
	$ids->find();
	$ids->saveMemory();
	
	$_SESSION['fsip']['search']['table'] = $type;
	
	header('Location: ' . LOCATION . BASE . 'results' . URL_CAP);
	exit();
}

$fsip = new FSIP;
$fsip->recordStat('home');

$header = new Canvas;
$header->load('header');
$header->setTitle('Search');
$header->display();

$content = new Canvas;
$content->load('search');
$content->assign('EXIF_Names', $fsip->showEXIFNames('exif_name'));
$content->assign('Rights', $fsip->showRights('rights'));
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>