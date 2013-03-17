<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

$user->perm(true, 'maintenance');

// Cache
require_once(PATH . CLASSES . 'cache_lite/Lite.php');

// Set a few options
$options = array(
    'cacheDir' => PATH . CACHE,
    'lifeTime' => 999999999999999,
);

// Create a Cache_Lite object
$cache = new Cache_Lite($options);

if (!$report = $cache->get('diagnostic_report')) {
	$fsip->addNote('Your diagnostic report could not be submitted. Please try again.', 'error');
	$location = LOCATION . BASE. ADMINFOLDER . 'settings' . URL_CAP;
	$fsip::headerLocationRedirect($location);
}

$data = http_build_query(
    array(
		'domain' => $_SERVER['HTTP_HOST'],
        'report' => $report
    )
);

$opts = array(
	'http' => array(
		'method' => 'POST',
		'header' => 'Content-type: application/x-www-form-urlencoded; charset=utf-8',
		'content' => $data
	)
);

/* DEH - remove dead remote services
$context = stream_context_create($opts);
$body = file_get_contents('http://www.alkalineapp.com/boomerang/report/', false, $context);

if($body == 'true'){
	$fsip->addNote('Your diagnostic report has been submitted. Please <a href="http://www.alkalineapp.com/support/">submit a bug report</a> if you have not already done so.', 'success');
}
else{
*/
	$fsip->addNote('Your diagnostic report could not be submitted at this time.', 'error');
//}

$location = LOCATION . BASE. ADMINFOLDER;
$fsip::headerLocationRedirect($location);

?>