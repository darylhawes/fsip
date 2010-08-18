<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(empty($_POST['photo_id'])){
	$photo_ids = new Find();
	$photo_ids->exec();
	echo json_encode($photo_ids->photo_ids);
}
else{
	$photo = new Photo($_POST['photo_id']);
	$photo->deSizePhoto();
	$photo->sizePhoto();
}

?>