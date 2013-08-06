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
$path = '';

// URL base path (if installed at top-level of domain, use '/')
$base = '';

// Database data source name (DSN including protocol)
$db_dsn = '';

// Database type (DSN protocol)
$db_type = '';

// Database user username (leave empty for SQLite)
$db_user = '';

// Database user password (leave empty for SQLite)
$db_pass = '';

// Database table prefix
$table_prefix = '';

// Subdirectory prefix
$folder_prefix = '../';

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


global $config_defaults;
$config_defaults = array(
	'theme_id'=>'1',
	'theme_folder'=>'fsipDefault',
	'web_name'=>'',
	'web_title'=>'(Untitled Site)',
	'web_title_format'=>'emdash2',
	'web_description'=>'',
	'web_email'=>'',
	'web_timezone'=>'America/New_York',
	'shoe_exif'=>true,
	'shoe_iptc'=>true,
	'shoe_geo'=>null,
	'image_markup'=>null,
	'image_markup_ext'=>'',
	'thumb_imagick'=>null,
	'thumb_compress'=>null,
	'thumb_compress_tol'=>100,
	'thumb_watermark'=>null,
	'thumb_watermark_pos'=>'nw',
	'thumb_watermark_margin'=>'',
	'image_original'=>null,
	'tag_alpha'=>null,
	'comm_enabled'=>null,
	'comm_email'=>null,
	'comm_mod'=>null,
	'comm_markup'=>null,
	'comm_markup_ext'=>'bbcode',
	'rights_default'=>null,
	'rights_default_id'=>'',
	'stat_enabled'=>true,
	'stat_ignore_user'=>null,
	'canvas_remove_unused'=>null,
	'maint_reports'=>null,
	'maint_debug'=>true,
	'maint_disable'=>null,
	'page_size_id'=>'1',
	'page_size_label'=>'admin',
	'shoe_max'=>null,
	'shoe_max_count'=>'',
	'image_hdm'=>null,
	'image_hdm_format'=>'yyyy\/mm\/dd',
	'web_markup'=>null,
	'web_markup_ext'=>'',
	'bulk_delete'=>null,
	'thumb_metadata'=>null,
	'page_div_wrap'=>null,
	'page_div_wrap_class'=>'',
	'comm_allow_html'=>null,
	'comm_allow_html_tags'=>'',
	'guest_remember'=>null,
	'guest_remember_time'=>'86400',
	'syndication_cache_time'=>'15',
	'syndication_summary_only'=>null,
	'sphinx_enabled'=>null,
	'sphinx_server'=>'',
	'sphinx_port'=>'',
	'sphinx_index'=>'',
	'sphinx_max_exec'=>'',
	'maint_debug_admin_only'=>true
);

require_once('../lib/fsip_lib.php');
?>