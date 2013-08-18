<?php

/**
 * FSIP based on Alkaline
 * 
 *
 * @package FSIP
 * @author Daryl Hawes
 * @version 1.2
 * @since 1.2
 */
require_once('./../config.php');

$orbit = new Orbit;
$user = new User;

$user->isLoggedIn(true);

if (!empty($_POST['act']) and !empty($_POST['object'])) {
	$_SESSION['fsip']['preview']['act'] = $_POST['act'];
	$object = $_POST['object'];
	
	$block = $_SESSION['fsip']['preview']['act'] . 's';
	$object[$block] = 1;
	
	if($block == 'images') {
		if (!empty($object['image_markup'])) {
			$image_markup_ext = $object['image_markup'];
			$object['image_description'] = $orbit->hook('markup_' . $image_markup_ext, $object['image_description_raw'], $object['image_description_raw']);
			$object['image_title'] = $orbit->hook('markup_title_' . $image_markup_ext, $object['image_title'], $object['image_title']);
			$object['image_excerpt'] = $orbit->hook('markup_' . $image_markup_ext, $object['image_excerpt_raw'], $object['image_excerpt_raw']);
		} elseif($fsip->returnConf('web_markup')) {
			$image_markup_ext = $fsip->returnConf('web_markup_ext');
			$object['image_description'] = $orbit->hook('markup_' . $image_markup_ext, $object['image_description_raw'], $object['image_description_raw']);
			$object['image_title'] = $orbit->hook('markup_title_' . $image_markup_ext, $object['image_title'], $object['image_title']);
			$object['image_excerpt'] = $orbit->hook('markup_' . $image_markup_ext, $object['image_excerpt_raw'], $object['image_excerpt_raw']);
		} else {
			$image_markup_ext = '';
			$object['image_description'] = $fsip->nl2br($object['image_description_raw']);
			$object['image_excerpt'] = $fsip->nl2br($object['image_excerpt_raw']);
		}
	} elseif($block == 'pages') {
		if (!empty($object['page_markup_ext'])) {
			$page_markup_ext = $object['page_markup_ext'];
			$object['page_text'] = $orbit->hook('markup_' . $page_markup_ext, $object['page_text_raw'], $object['page_text_raw']);
			$object['page_title'] = $orbit->hook('markup_title_' . $page_markup_ext, $object['page_title'], $object['page_title']);
			$object['page_excerpt'] = $orbit->hook('markup_' . $page_markup_ext, $object['page_excerpt_raw'], $object['page_excerpt_raw']);
		} elseif($fsip->returnConf('web_markup')) {
			$page_markup_ext = $fsip->returnConf('web_markup_ext');
			$object['page_text'] = $orbit->hook('markup_' . $page_markup_ext, $object['page_text_raw'], $object['page_text_raw']);
			$object['page_title'] = $orbit->hook('markup_title_' . $page_markup_ext, $object['page_title'], $object['page_title']);
			$object['page_excerpt'] = $orbit->hook('markup_' . $page_markup_ext, $object['page_excerpt_raw'], $object['page_excerpt_raw']);
		} else {
			$page_markup_ext = '';
			$object['page_text'] = fsip_nl2br($object['page_text_raw']);
			$object['page_excerpt'] = fsip_nl2br($object['page_excerpt_raw']);
		}
	}
	
	$id_label = $_POST['act'] . '_id';
	$id = $object[$id_label];
	
	$_SESSION['fsip']['preview']['id'] = $id;
	$_SESSION['fsip']['preview']['object'] = $object;
	exit();
}

if (empty($_SESSION['fsip']['preview']['act'])) {
	$location = LOCATION . BASE . ADMINFOLDER);
	headerLocationRedirect($location);
}

$_GET['id'] = $_SESSION['fsip']['preview']['id'];

chdir(PATH);
require_once('./' . $_SESSION['fsip']['preview']['act'] . '.php');

unset($_SESSION['fsip']['preview']);

?>