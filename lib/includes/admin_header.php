<?php 
/**
 * FSIP based on Alkaline
 * 
 *
 * @package FSIP
 * @author Daryl Hawes
 * @version 1.2
 * @since 1.2
 */

//echo "in admin header<br />";
if (!empty($user) and $user->hasPermission('admin', false)) { 
	global $db;
	$badges = $db->getBadges();
} 
$web_title = returnConf('web_title');

if ($web_title  == "(Untitled Site)" && (!isset($installing) || $installing === false) ) {
	addNote('Welcome to FSIP. You should begin by <a href="' . BASE . ADMINFOLDER . 'configuration' . URL_CAP . '">configuring your site with a custom title.</a> ');
}

$pg_title = (defined('TITLE') ? TITLE. ' - '. $web_title  : 'FSIP'. ' - '. $web_title );
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="base" content="<?php echo LOCATION . BASE; ?>" />
	<meta name="folder_prefix" content="<?php echo FOLDER_PREFIX; ?>" />
	<meta name="permissions" content="<?php if (!empty($user) and $user->isLoggedIn() and !empty($user->user['user_permissions'])) { echo @implode(', ', $user->user['user_permissions']); } ?>" />
	<title><?php echo $pg_title; ?></title>
	<link rel="stylesheet" href="<?php echo BASE . INCLUDES; ?>css/blueprint/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . INCLUDES; ?>css/blueprint/print.css" type="text/css" media="print" />	
	<!--[if lt IE 8]><link rel="stylesheet" href="<?php echo BASE . INCLUDES; ?>css/blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	<link rel="stylesheet" href="<?php echo BASE . INCLUDES; ?>css/formalize.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . INCLUDES; ?>css/jquery-ui/jquery-ui-1.8.7.custom.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . INCLUDES; ?>css/fsip.css" type="text/css" media="screen, projection" />
	<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo BASE . JS; ?>jquery/excanvas.min.js"></script><![endif]-->
	<link rel="shortcut icon" href="<?php echo BASE . IMGFOLDER; ?>favicon.ico" />
	<script src="<?php echo BASE . JS; ?>fsip.packed.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>fsip.js" type="text/javascript"></script>
</head>
<body id="fsip">
	<div id="header_holder">
		<div class="container">
			<div id="userbar" class="span-24 right">
<?php
				if (!empty($user) and $user->isLoggedIn()) {
?>
					<div id="userbar_home">
						<strong>
							<?php $title = returnConf('web_title'); echo (!empty($title) ? $title : ''); ?>
						</strong> &#0160;
						<a href="<?php echo BASE; ?>" target="<?php if($user->readPref('home_target')){ echo '_blank'; } ?>">Launch</a>
					</div>
					
					<div id="userbar_user">
						<strong>
							<a href="<?php echo BASE . USERFOLDER . 'profile' . URL_CAP; ?>"><?php echo $user->user['user_username'];?></a>									</strong> &#0160;
						</strong> &#0160;
						<a href="<?php echo BASE . ADMINFOLDER . 'search' . URL_ACT . 'me' . URL_RW; ?>">My Images</a>,
						<a href="<?php echo BASE . ADMINFOLDER . 'comments' . URL_ACT . 'me' . URL_RW; ?>">Comments</a> &#0160;
						<a href="<?php echo BASE . USERFOLDER . 'preferences' . URL_CAP; ?>">Preferences</a> &#0160;
						<a href="<?php echo BASE . 'logout' . URL_CAP; ?>">Logout</a>
					</div>
<?php
				}
?>
			</div>
			<div id="header" class="span-24 last">
				<div class="span-6 append-1">
					<a href="<?php echo BASE . ADMINFOLDER; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>fsip.png" alt="FSIP" class="logo" /></a>
				</div>
				<div id="panels" class="span-17 last">
<?php
					if (!empty($user) and $user->isLoggedIn()) {
?>
						<div id="search_panel" class="span-17 append-1">
							<form action="<?php echo BASE . ADMINFOLDER . 'search' . URL_CAP; ?>" method="post">
								<select name="search_type" id="search_type">
									<option value="images">Images</option>
									<option value="comments">Comments</option>
								</select>
								<input type="search" name="q" results="10" />
								<input type="submit" value="Search" />
							</form>
							<!-- <a href="<?php echo BASE . ADMINFOLDER . 'library' . URL_CAP; ?>#advanced" class="advanced_link">Advanced Search</a> -->
						</div>
<?php
					}
?>
				</div>
			</div>
			<div id="navigation" class="span-24 last">
				<ul>
<?php
					if (@!defined('TAB') or (@TAB == 'dashboard') or (@TAB == 'upload') or (@TAB == 'shoebox') or (@TAB == 'library') or (@TAB == 'comments') or (@TAB == 'features') or (@TAB == 'settings')) {
						?>
						<li id="tab_dashboard">
							<a href="<?php echo BASE . ADMINFOLDER . 'dashboard' . URL_CAP; ?>"<?php if (@TAB == 'dashboard') { echo ' class="selected"'; } ?>>Dashboard &#9662;</a>
							<ul>
								<li id="sub_statistics"><a href="<?php echo BASE . ADMINFOLDER; ?>statistics<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/stats.png" alt="" /> Statistics</a></li>
								<li id="sub_preferences"><a href="<?php echo BASE . USERFOLDER; ?>preferences<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/preferences.png" alt="" /> Preferences</a></li>
							</ul>
						</li>
						<?php if ($badges['images'] > 0 ) { ?>
						<li id="tab_shoebox" class="alt">
							<a href="<?php echo BASE . ADMINFOLDER . 'shoebox' . URL_CAP; ?>"<?php if (@TAB == 'shoebox') { echo ' class="selected"'; } ?>>Shoebox</a>
						</li>
						<?php } else { ?>
						<li id="tab_upload">
							<a href="<?php echo BASE . ADMINFOLDER . 'upload' . URL_CAP; ?>"<?php if (@TAB == 'upload') { echo ' class="selected"'; } ?>>Upload</a>
						</li>
						<?php } ?>
						<li id="tab_library">
							<a href="<?php echo BASE . ADMINFOLDER . 'library' . URL_CAP; ?>"<?php if (@TAB == 'library') { echo ' class="selected"'; } ?>>Images<?php if($badges['images'] > 0){ echo ' (' . number_format($badges['images']) . ')'; } ?></a>
						</li>
						<li id="tab_comments">
							<a href="<?php echo BASE . ADMINFOLDER . 'comments' . URL_CAP; ?>"<?php if (@TAB == 'comments') { echo ' class="selected"'; } ?>>Comments<?php if($badges['comments'] > 0){ echo ' (' . number_format($badges['comments']) . ')'; } ?></a>
						</li>
						<li id="tab_features">
							<a href="<?php echo BASE . ADMINFOLDER . 'features' . URL_CAP; ?>"<?php if (@TAB == 'features') { echo ' class="selected"'; } ?>>Editor <span>&#9662;</span></a>
							<ul>
								<li id="sub_tags"><a href="<?php echo BASE . ADMINFOLDER . 'tags' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/tags.png" alt="" /> Tags</a></li>
								<li id="sub_sets"><a href="<?php echo BASE . ADMINFOLDER. 'sets' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/sets.png" alt="" /> Sets</a></li>
								<!--<li id="sub_pages"><a href="<?php echo BASE . ADMINFOLDER . 'pages' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/pages.png" alt="" /> Pages</a></li>-->
								<li id="sub_rights"><a href="<?php echo BASE . ADMINFOLDER . 'rights' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/rights.png" alt="" /> Rights</a></li>
							</ul>
						</li>
						<li id="tab_settings">
							<a href="<?php echo BASE . ADMINFOLDER . 'settings' . URL_CAP; ?>"<?php if (@TAB == 'settings') { echo ' class="selected"'; } ?>>Settings <span>&#9662;</span></a>
							<ul>
								<li id="sub_users"><a href="<?php echo BASE . ADMINFOLDER . 'users' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/users.png" alt="" /> Users</a></li>
								<!--<li id="sub_guests"><a href="<?php echo BASE . ADMINFOLDER . 'guests' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/guests.png" alt="" /> Guests</a></li>-->
								<li id="sub_thumbnails"><a href="<?php echo BASE . ADMINFOLDER . 'thumbnails' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/thumbnails.png" alt="" /> Thumbnails</a></li>
								<li id="sub_themes"><a href="<?php echo BASE . ADMINFOLDER . 'themes' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/themes.png" alt="" /> Themes</a></li>
								<li id="sub_extensions"><a href="<?php echo BASE . ADMINFOLDER . 'extensions' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/extensions.png" alt="" /> Extensions</a></li>
								<li id="sub_configuration"><a href="<?php echo BASE . ADMINFOLDER . 'configuration' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/configuration.png" alt="" /> Configuration</a></li>
								<li id="sub_maintenance"><a href="<?php echo BASE . ADMINFOLDER . 'maintenance' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/maintenance.png" alt="" /> Maintenance</a></li>
								<li id="sub_phpinfo"><a href="<?php echo BASE . ADMINFOLDER . 'phpinfo' . URL_CAP; ?>"><img src="<?php echo BASE . IMGFOLDER; ?>minis/phpinfo.png" alt="" /> PHP Info</a></li>
							</ul>
						</li>
						<li id="tab_help">
							<a href="<?php echo BASE . ADMINFOLDER . 'help' . URL_CAP; ?>"<?php if(@TAB == 'help'){ echo ' class="selected"'; } ?>>Help</a>
						</li>
<?php
					} else {
?>
						<li><a href="" class="selected"><?php echo TAB; ?></a></li>
<?php
					}

?>
				</ul>
			</div>
		</div>
	</div>
	<div class="container">
		<div id="content" class="span-24 last">
			<?php echo returnNotes(); ?>