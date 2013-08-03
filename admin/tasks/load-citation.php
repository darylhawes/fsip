<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
$user->hasPermission('admin', true);

if (!empty($_POST['uri'])) {
	$citation = loadCitation($_POST['uri'], $_POST['field'], $_POST['field_id']);
	if ($citation != false) {
		echo removeNull(json_encode($citation));
	}
}

?>