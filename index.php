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

$with_id = null;

if (isset($_GET['with'])) {
	$with_id = $fsip->findID($_GET['with'], true);
}
if (!isset($with_id) and !empty($_GET['with'])) {
	$fsip->addError('No image was found.', 'Try searching for the image you were seeking.', null, null, 404); 
}

$image_ids = new Find('images');

// no difference between first page and subsequent. 
// Here is a good place to insert a user preference for how many items to show per page. DEH
//$image_ids->page(null, 12, null); // 	public function page($page=Page number, $limit=Number of items per page, $first=Number of items on the first page (if different)) {
$image_ids->page(null, 12, 1); //DEH mod


if ($with_id) { 
	$image_ids->with($with_id); 
}
$image_ids->published();
$image_ids->privacy('public');
$image_ids->sort('image_published', 'DESC');
$image_ids->find();

$template_variables = array();
$template_variable['Page_Next'] = $image_ids->page_next;
$template_variable['Page_Previous'] = $image_ids->page_previous;
$template_variable['Page_Next_URI'] = $image_ids->page_next_uri;
$template_variable['Page_Previous_URI'] = $image_ids->page_previous_uri;
$template_variable['Page_Current'] = $image_ids->page;
$template_variable['Page_Count'] = $image_ids->page_count;
$template_variable['Page_Navigation_String'] = $image_ids->page_navigation_string;
$template_variable['Published_Public_Image_Count'] = $image_ids->published_public_image_count;
$template_variable['Total_Image_Count'] = $image_ids->total_image_count;

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
$header->assignArray($template_variable);
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
$directory->assignArray($template_variable);
$directory->loop($pages);
$directory->loop($sets);
$directory->display();

$index = new Canvas;
if ($image_ids->page == 1) {
	$index->load('index');
} else {
	$index->load('index_sub');
}
$index->assignArray($template_variable);
$index->loop($images);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->assignArray($template_variable);
$footer->display();

?>