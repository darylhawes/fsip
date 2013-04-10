<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$hint = strip_tags($_GET['term']);

$tags = hintTag($hint);

echo json_encode($tags);

/**
 * List tags by search, for suggestions
 *
 * @param string $hint Search string
 * @return array
 */
function hintTag($hint) {
	$hint_lower = strtolower($hint);
	
	$sql = 'SELECT DISTINCT(tags.tag_name) FROM tags WHERE LOWER(tags.tag_name) LIKE :hint_lower ORDER BY tags.tag_name ASC';
	global $db;
	$query = $db->prepare($sql);
	$query->execute(array(':hint_lower' => $hint_lower . '%'));
	$tags = $query->fetchAll();
	
	$tags_list = array();
	
	foreach($tags as $tag) {
		$tags_list[] = $tag['tag_name'];
	}
	
	return $tags_list;
}
?>