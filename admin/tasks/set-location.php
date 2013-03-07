<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;

$hint = strip_tags($_POST);

$geo = new Geo('(' . $_POST['latitude'] . ', ' . $_POST['longitude'] . ')');
$geo = strval($geo);

$_SESSION['fsip']['location'] = $geo;
echo $geo;

?>