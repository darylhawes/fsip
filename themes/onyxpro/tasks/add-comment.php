<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('../../../config.php');

$dbpointer = getDB();

if(!empty($_POST['image_id'])){
	$id = $_POST['image_id'];
}

$comment_text = 'comment_' . $id . '_text';
$comment_author_name = 'comment_' . $id . '_author_name';
$comment_author_email = 'comment_' . $id . '_author_email';
$comment_author_uri = 'comment_' . $id . '_author_uri';

$_POST[$comment_text] = $_POST['text'];
$_POST[$comment_author_name] = $_POST['author_name'];
$_POST[$comment_author_email] = $_POST['author_email'];
$_POST[$comment_author_uri] = $_POST['author_uri'];

$comment_id = addComments();

if ($comment_id > 0) {
	$comment = $dbpointer->getRow('comments', $comment_id);
	echo $comment['comment_text'];
}

?>