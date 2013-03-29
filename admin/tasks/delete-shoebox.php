<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');

$user = new User;
$fm = getFileManager();

$user->perm(true, 'maintenance');

$fm->emptyDirectory(PATH . SHOEBOX);

?>