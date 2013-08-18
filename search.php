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
	
	$location = LOCATION . BASE . 'results' . URL_CAP;
	headerLocationRedirect($location);
	exit();
}

$db->recordStat('home');

$header = new Canvas;
$header->load('header');
$header->setTitle('Search');
$header->display();

$content = new Canvas;
$content->load('search');
$content->assign('EXIF_Names', showEXIFNames('exif_name'));
$content->assign('Rights', showRights('rights'));
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>