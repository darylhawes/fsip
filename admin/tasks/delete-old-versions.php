<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
global $db;

$user->hasPermission('admin', true);

$id = findID(@$_POST['image_id']);

if (empty($id)) {
	$olds = array();
	
	$query = $db->prepare('SELECT DISTINCT versions.version_id FROM versions WHERE versions.version_similarity > :version_similarity AND versions.version_created < :version_created;');
	$query->execute(array(':version_similarity' => 65, ':version_created' => date('Y-m-d H:i:s', strtotime('-2 weeks'))));
	$versions1 = $query->fetchAll();
	
	$query->execute(array(':version_similarity' => 95, ':version_created' => date('Y-m-d H:i:s', strtotime('-6 months'))));
	$versions2 = $query->fetchAll();
	
	$versions = array_merge($versions1, $versions2);
	
	$version_ids = array();
	
	foreach($versions as $version){
		$version_ids[] = $version['version_id'];
	}
	
	echo json_encode($version_ids);
} else {
	$db->exec('DELETE FROM versions WHERE version_id = ' . intval($id));
}

?>