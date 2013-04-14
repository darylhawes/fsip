<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;

$user->userHasPermission('maintenance', true);

Files::emptyDirectory(PATH . SHOEBOX);

?>