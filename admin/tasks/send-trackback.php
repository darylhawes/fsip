<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

$user->perm(true);

if(!empty($_REQUEST['id'])){
	$posts = new Post($_REQUEST['id']);
	$bool = $posts->sendTrackbacks();
}

if($bool === true){
	$fsip->addNote('A trackback has successfully been sent.', 'success');
}
else{
	$fsip->addNote('A trackback could not be sent.', 'error');
}

header('Location: ' . BASE . ADMIN . 'posts' . URL_ID . $posts->posts[0]['post_id'] . URL_RW);

?>