<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$hint = strip_tags($_GET['term']);

$geo = new Geo;
$places = $geo->hint($hint);

echo json_encode($places);

?>