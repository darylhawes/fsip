<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/
/*
require_once('config.php');
$user = new User();

$user->access($_REQUEST['id']);

session_write_close();

$location = LOCATION . BASE;
headerLocationRedirect($location);
exit();
*/
require_once('config.php');
$location = LOCATION . BASE;
headerLocationRedirect($location);
exit();

?>