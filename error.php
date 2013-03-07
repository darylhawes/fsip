<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$fsip->recordStat('error');

$header = new Canvas;
$header->load('header');
$header->setTitle('Welcome');
$header->display();

$index = new Canvas;
$index->load('error');
$index->assignArray($_SESSION['fsip']['error']);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>