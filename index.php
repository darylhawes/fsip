<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

if (file_exists('config.php')) {
	require_once('config.php');
} else {
	header('Location: admin/install.php');
	echo "<h1>Redirecting</h1>";
	echo "<p>It appears that you have not installed FSIP yet. ";
	echo " You are being redirected to the installation page. ";
	echo " If you're still here after a few seconds please ";
	echo '<a href="admin/install.php">'."click here</a></p>";
}

//echo "in index 1<br />";

global $db;
$db->recordStat('home');
//echo "in index 2<br />";

$with_id = null;

if (isset($_GET['with'])) {
	$with_id = findID($_GET['with'], true);
}
//echo "in index 3<br />";
if (!isset($with_id) and !empty($_GET['with'])) {
	Debugger::addError('No matching image was found with that ID in our database.', 'Try searching for the image you were seeking.', null, null, 404); 
}

$image_ids = new Find('images');
//echo "in index 4<br />";

// no difference between first page and subsequent. 
// Here is a good place to insert a user preference for how many items to show per page. DEH
//
//page($page=Page number, $limit=Number of items per page, $first=Number 
//   of items on the first page (if different)) {
//$image_ids->page(null, 12, null); 
$image_ids->page(null, 12, 1); //DEH mod


if ($with_id) {
	$image_ids->with($with_id);
}
$image_ids->published();
$image_ids->privacy('public');
$image_ids->sort('image_published', 'DESC');
$image_ids->find();

if ($image_ids->count == 0) {
	addNote('No images have been found in our database matching your query. Perhaps you would like to <a href="' . 
			PATH . ADMINFOLDER . 'upload' . URL_CAP . '">upload</a>? Or maybe you have some images waiting to <a href="' . 
			PATH . ADMINFOLDER . 'library' . URL_CAP . '">publish</a>?');
}
//echo "in index 5<br />";

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

//echo "in index 6<br />";

$images = new Image($image_ids);

//echo "Image Ids: <br />";
//print_r($image_ids);
//echo "Images: <br />";
//print_r($images);

$images->formatTime();
//echo "in index 6.2<br />";
$images->getSizes();
//echo "in index 6.3<br />";
$images->getEXIF();
//echo "in index 6.4<br />";
$images->getColorkey(950, 15);
//echo "in index 6.5<br />";
$images->getSets();
//echo "in index 6.6<br />";
$images->getTags();
//echo "in index 6.7<br />";
$images->getRights();
//echo "in index 6.8<br />";
$images->getPages();
//echo "in index 6.9<br />";
$images->getComments();
//echo "in index 6.10<br />";
$images->addSequence('medium_last', 3);
//echo "in index 6.11<br />";
$images->hook();

//echo "in index 7 - header<br />";
$header = new Canvas;
//echo "in index 7.1<br />";
$header->load('header');
//echo "in index 7.2<br />";
$header->assignArray($template_variable);
//echo "in index 7.3<br />";

// Why "Welcome" and not an admin customizable page title?
$header->setTitle('Welcome'); 
$header->display();

//echo "in index 8<br />";
$page_ids = new Find('pages');
$page_ids->find();

$pages = new Page($page_ids);

$set_ids = new Find('sets');
$set_ids->find();

$sets = new Set($set_ids);

//echo "in index 9 - directory<br />";
$directory = new Canvas;
$directory->load('directory');
$directory->assignArray($template_variable);
$directory->loop($pages);
$directory->loop($sets);
$directory->display();
//echo "in index 9.5<br />";

//print_r($images);
//echo "in index 10 - indexmain<br />";
$index = new Canvas;
if ($image_ids->page == 1) {
//echo "index 10.1 - loading index page<br />";
	$index->load('index');
} else {
//echo "in index 10 - loading index_sub page<br />";
	$index->load('index_sub');
}
$index->assignArray($template_variable);
$index->loop($images);
$index->display();
//echo "end index 10.5<br />";

$footer = new Canvas;
$footer->load('footer');
$footer->assignArray($template_variable);
$footer->display();

?>