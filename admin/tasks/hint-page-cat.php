<?php

/**
 * FSIP based on Alkaline
 * 
 *
 * http://www.alkalineapp.com/
 * Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
 *
 * @package FSIP
 * @subpackage admin
 * @since 1.2
 */

require_once('../../config.php');

$hint = strip_tags($_GET['term']);

$categories = hintPageCategory($hint);

echo json_encode($categories);

/**
 * List category by search, for suggestions
 *
 * @param string $hint Search string
 * @return array
 */
function hintPageCategory($hint) {
	$hint_lower = strtolower($hint);
	
	if (!empty($hint)) {
		$sql = 'SELECT DISTINCT(pages.page_category) FROM pages WHERE LOWER(pages.page_category) LIKE :hint_lower ORDER BY pages.page_category ASC';
	} else {
		$sql = 'SELECT DISTINCT(pages.page_category) FROM pages ORDER BY pages.page_category ASC';
	}
	global $db;
	$query = $db->prepare($sql);
	$query->execute(array(':hint_lower' => $hint_lower . '%'));
	$pages = $query->fetchAll();
	
	$categories_list = array();

	foreach($pages as $page) {
		$categories_list[] = $page['page_category'];
	}
	
	return $categories_list;
}
?>