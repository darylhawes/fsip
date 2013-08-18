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

$user = new User;
$user->hasPermission('admin', true);

if (!empty($_POST['uri'])) {
	$citation = loadCitation($_POST['uri'], $_POST['field'], $_POST['field_id']);
	if ($citation != false) {
		echo removeNullFromJSON(json_encode($citation));
	}
}

?>