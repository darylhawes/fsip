<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

//
// MODIFY THE DEFINITIONS BELOW
//

// Server type
$server_type = '';

// Folder path
$path = '/Applications/MAMP/htdocs/fsip/';

// URL base path (if installed at top-level of domain, use '/')
$base = '/fsip/';

// Database data source name (DSN including protocol)
$db_dsn = 'mysql:host=localhost;dbname=fsip_test';

// Database type (DSN protocol)
$db_type = 'mysql';

// Database user username (leave empty for SQLite)
$db_user = 'root';

// Database user password (leave empty for SQLite)
$db_pass = 'root';

// Database table prefix
$table_prefix = 'fsip_';

// Subdirectory prefix
$folder_prefix = '';

// URL rewriting (supports Apache mod_rewrite, Microsoft URL Rewrite 2, and compatible)
$url_rewrite = false;

// Global password salt
$salt = '';


//
// DO NOT MODIFY BELOW THIS LINE
//

// Valid extensions, separate by |
$img_ext = 'gif|jpg|jpeg|png|pdf|svg';

// Length, an integer in seconds, to remember a user's previous login
$user_remember = 1209600;

// Template extension
$temp_ext = '.html';

// Default query limit (can be overwritten)
$limit = 20;

// Date formatting
$date_format = 'M j, Y \a\t g:i a';

// Palette size
$palette_size = 8;

// Color tolerance (higher numbers varies colors more)
$color_tolerance = 60;


if ($url_rewrite) {
	define('URL_CAP', '/');
	define('URL_ID', '/');
	define('URL_ACT', '/');
	define('URL_AID', '/');
	define('URL_PAGE', '/page');
	define('URL_RW', '/');
} else {
	define('URL_CAP', '.php');
	define('URL_ID', '.php?id=');
	define('URL_ACT', '.php?act=');
	define('URL_PAGE', '.php?page=');
	define('URL_AID', '&id=');
	define('URL_RW', '');
}


$server_address = $_SERVER['SERVER_NAME'];
if (isset ($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != "80") {
	$server_address .= ':'.$_SERVER['SERVER_PORT'];
}

define('SERVER_TYPE', $server_type);
define('PATH', $path);
define('BASE', $base);
define('DOMAIN', $server_address);
define('LOCATION', 'http://' . DOMAIN);
define('DB_DSN', $db_dsn);
define('DB_TYPE', $db_type);
@define('DB_USER', $db_user);
@define('DB_PASS', $db_pass);
define('TABLE_PREFIX', $table_prefix);
define('FOLDER_PREFIX', $folder_prefix);
define('SALT', $salt);
define('IMG_EXT', $img_ext);
define('USER_REMEMBER', $user_remember);
define('TEMP_EXT', $temp_ext);
define('LIMIT', $limit);
define('DATE_FORMAT', $date_format);
define('PALETTE_SIZE', $palette_size);
define('COLOR_TOLERANCE', $color_tolerance);

define('CACHE', FOLDER_PREFIX . 'cache/');
define('EXTENSIONS', FOLDER_PREFIX . 'extensions/');
define('SHOEBOX', FOLDER_PREFIX . 'shoebox/');
define('THEMES', FOLDER_PREFIX . 'themes/');
define('WATERMARKS', FOLDER_PREFIX . 'watermarks/');
define('USERFOLDER', FOLDER_PREFIX . 'user/');
define('ADMINFOLDER', FOLDER_PREFIX . 'admin/');
define('DOCS', ADMINFOLDER . 'docs/');
define('LIB', FOLDER_PREFIX . 'lib/');
define('CLASSES', LIB . 'classes/');
define('INCLUDES', LIB . 'includes/');
define('JS', LIB . 'js/');
define('IMGFOLDER', LIB . 'images/');
define('DATAFOLDER', FOLDER_PREFIX . 'data/');
define('IMAGEDATA', DATAFOLDER . 'images/');
define('DB', DATAFOLDER . 'db/');

require_once(PATH . LIB . 'fsip_lib.php');
?>