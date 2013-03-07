<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$fsip->recordStat('page');

$id = $fsip->findID($_GET['id']);
if(!$id){ $fsip->addError('No page was found.', 'Try searching for the page you were seeking.', null, null, 404); }

$pages = new Page($id);
$pages->formatTime();
$pages->updateViews();
$page = $pages->pages[0];

if(!$page){ $fsip->addError('No page was found.', 'Try searching for the page you were seeking.', null, null, 404); }

$header = new Canvas;
$header->load('header');
$header->setTitle($page['page_title']);
$header->assign('Canonical', $page['page_uri']);
$header->display();

$content = new Canvas;
$content->load('page');
$content->loop($pages);
$content->assignArray($page);
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>