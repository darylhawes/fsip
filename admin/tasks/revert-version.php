<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;

$user->perm(true);

if (!empty($_POST['id'])) {
	revertVersion($_POST['id']);
}

/**
 * Revert to title and text of a previous version
 *
 * @param int $version_id 
 * @return bool True if successful
 */
function revertVersion($version_id) {
	if(empty($version_id)) { 
		return false; 
	}
	if (!$version_id = intval($version_id)) { 
		return false; 
	}
	$dbpointer = getDB();
	$version = $dbpointer->getRow('versions', $version_id);
	
	if (empty($version)) { 
		return false; 
	}
	
	if (!empty($version['page_id'])) {
		$page = new Page($version['page_id']);
		$fields = array('page_title' => $version['version_title'],
			'page_text_raw' => $version['version_text_raw']);
		return $page->updateFields($fields, null, false);
	}
}


?>