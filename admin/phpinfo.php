<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

$user = new User;
$user->userHasPermission('admin', true);

require_once(PATH . INCLUDES . 'admin/admin_header.php');

phpinfo();

require_once(PATH . INCLUDES . 'admin/admin_footer.php');

?>