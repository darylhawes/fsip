<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;

$dbpointer = getDB();

$user->perm(true);

$id = findID(@$_POST['image_id']);

if (empty($id)) {
	$query = $dbpointer->prepare('SELECT DISTINCT tags.tag_id FROM tags;');
	$query->execute();
	$tags = $query->fetchAll();
	
	$query = $dbpointer->prepare('SELECT DISTINCT tags.tag_id FROM tags, links WHERE tags.tag_id = links.tag_id;');
	$query->execute();
	$tags_in_use = $query->fetchAll();
	
	$orphans = array();
	
	foreach($tags as $tag) {
		if (!in_array($tag, $tags_in_use)) {
			$orphans[] = $tag;
		}
	}
	
	$tag_ids = array();
	
	foreach($orphans as $orphan) {
		$tag_ids[] = $orphan['tag_id'];
	}
	
	echo json_encode($tag_ids);
} else {
	$dbpointer->exec('DELETE FROM tags WHERE tag_id = ' . intval($id));
}

?>