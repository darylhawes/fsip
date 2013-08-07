<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/


require_once('../config.php');

$_POST = array_map('strip_tags', $_POST);

// Check that variables that we plan to use exist or clear them out to avoid PHP warnings.
if (!isset($_POST['install']))           { $_POST['install'] = ""; }
if (!isset($_POST['install_db_type']))   { $_POST['install_db_type'] = ""; }
if (!isset($_POST['install_db_prefix'])) { $_POST['install_db_prefix'] = ""; }
if (!isset($_POST['install_db_name']))   { $_POST['install_db_name'] = ""; }
if (!isset($_POST['install_db_user']))   { $_POST['install_db_user'] = ""; }
if (!isset($_POST['install_db_host']))   { $_POST['install_db_host'] = ""; }
if (!isset($_POST['install_db_pass']))   { $_POST['install_db_pass'] = ""; }
if (!isset($_POST['install_db_port']))   { $_POST['install_db_port'] = ""; }
if (!isset($_POST['install_db_file']))   { $_POST['install_db_file'] = ""; }
if (!isset($_POST['install_db_empty']))  { $_POST['install_db_empty'] = ""; }
if (!isset($_POST['install_name']))      { $_POST['install_name'] = ""; }
if (!isset($_POST['install_user']))      { $_POST['install_user'] = ""; }
if (!isset($_POST['install_pass']))      { $_POST['install_pass'] = ""; }
if (!isset($_POST['install_email']))     { $_POST['install_email'] = ""; }


// Diagnostic checks
//DEH - if dir not exist try to create it and set permissions THEN warn.
if (Files::checkFilePerm(PATH . DB) != '0777') {
	addNote('WARNING: Database folder at ('. PATH . DB .') may not be writable.<br />', 'notice');
}
if (Files::checkFilePerm(PATH . IMGFOLDER) != '0777') {
	addNote('WARNING:Images folder at ('. PATH . IMGFOLDER .') may not be writable.<br />', 'notice');
}
if (Files::checkFilePerm(PATH . SHOEBOX) != '0777') {
	addNote('WARNING:Shoebox folder at ('. PATH . SHOEBOX .') may not be writable.<br />', 'notice');
}
if (Files::checkFilePerm(PATH . CACHE) != '0777') {
	addNote('WARNING:Cache folder at ('. PATH . CACHE .') may not be writable.<br />', 'notice');
}
if (Files::checkFilePerm(PATH . 'config.php') == '0777') {
	addNote('WARNING:Configuration file at ('.PATH.'config.php)  should not be writable.<br />', 'notice');
}

///// Configuration setup

if (@$_POST['install'] == 'Install') {
	$type = $_POST['install_db_type'];
	$name = $_POST['install_db_name'];
	$username = $_POST['install_db_user'];
	$password = $_POST['install_db_pass'];
	
	if (!$config = file_get_contents(PATH . ADMINFOLDER . 'config_default.php', false)) {
		addNote('Cannot find default configuration file.', 'error');
	}

	$config = Files::replaceVar('$base', $_POST['install_base'], $config);
	$config = Files::replaceVar('$path', $_POST['install_path'], $config);
	
	if ( is_null($_POST['install_server'])) { 
		$_POST['install_server'] = ""; 
	}
	if ($_POST['install_server'] == 'win') {
		$config = Files::replaceVar('$server_type', 'win', $config);
	}
	
	if ($_POST['install_db_type'] == 'mysql') {
		if (empty($name)) {
			addNote('A database name is required for MySQL.', 'error');
		}
		if (empty($username)) {
			addNote('A database username is required for MySQL.', 'error');
		}
		
		$dsn = 'mysql:';
		
		if (!empty($_POST['install_db_host'])) {
			$dsn .= 'host=' . $_POST['install_db_host'] . ';';
		} else {
			$dsn .= 'host=localhost;';
		}
		
		if (!empty($_POST['install_db_port'])) {
			$dsn .= 'port=' . intval($_POST['install_db_port']) . ';';
		}
		
		$dsn .= 'dbname=' . $_POST['install_db_name'];

		$config = Files::replaceVar('$db_dsn', $dsn, $config);
		$config = Files::replaceVar('$db_type', 'mysql', $config);
		$config = Files::replaceVar('$db_user', $username, $config);
		$config = Files::replaceVar('$db_pass', $password, $config);
	} elseif($_POST['install_db_type'] == 'sqlite') {
		if (!empty($_POST['install_db_file'])) {
			$path = $_POST['install_db_file'];
		} else {
			$path = PATH . DB . 'fsip.db';
			$path = correctWinPath($path);
			
			$rand = randInt();
			$rand = substr(md5($rand), 0, 8);
			$path_new = PATH . DB . 'fsip_' . $rand . '.db';
			$path_new = correctWinPath($path_new);
			
			if (copy($path, $path_new)) {
				unlink($path);
				$path = $path_new;
				chmod($path, 0777);
			}
		}
		
		$path = correctWinPath($path);
		
		$dsn = 'sqlite:' . $path;
		
		$config = Files::replaceVar('$db_dsn', $dsn, $config);
		$config = Files::replaceVar('$db_type', 'sqlite', $config);
		
		if ((Files::checkFilePerm($path) != '0777') and (SERVER_TYPE != 'win')) {
			addNote('Your SQLite database is not writable (chmod 777).', 'error');
		}
	} elseif ($_POST['install_db_type'] == 'pgsql') {
		if (empty($name)) {
			addNote('A database name is required for PostgreSQL.', 'error');
		}
		if (empty($username)) {
			addNote('A database username is required for PostgreSQL.', 'error');
		}
		
		$dsn = 'pgsql:';
		
		if (!empty($_POST['install_db_host'])) {
			$dsn .= 'host=' . $_POST['install_db_host'] . ';';
		} else {
			$dsn .= 'host=localhost;';
		}
		
		if (!empty($_POST['install_db_port'])) {
			$dsn .= 'port=' . intval($_POST['install_db_port']) . ';';
		}
		
		$dsn .= 'dbname=' . $_POST['install_db_name'];
		
		$config = Files::replaceVar('$db_dsn', $dsn, $config);
		$config = Files::replaceVar('$db_type', 'pgsql', $config);
		$config = Files::replaceVar('$db_user', $username, $config);
		$config = Files::replaceVar('$db_pass', $password, $config);
	}
	
	if (!empty($_POST['install_db_prefix'])) {
		$config = Files::replaceVar('$table_prefix', $_POST['install_db_prefix'], $config);
	}
}


///// Database setup

if ((@$_POST['install'] == 'Install') and (countNotes('error') == 0)) {
	global $db; // already defined in config.php, reset and use global db here.

	// First check to see if can connect
	$db = new PDO($dsn, $username, $password);
	$error = $db->errorInfo();
	if (!empty($error[0])) {
		addNote('The database could not be contacted. ' . $error[0] . ' Check your settings and try again.', 'error');
	} else {
		function appendTableName($query) {
			if (!empty($_POST['install_db_prefix'])) {
				return preg_replace('#TABLE ([[:punct:]]*)(\w+)#s', 'TABLE \\1' . $_POST['install_db_prefix'] . '\\2', $query);
			} else {
				return $query;
			}
		}
		
		// Import empty DB SQL
		if (@$_POST['install_db_empty'] == 1) {
			$queries = file_get_contents(PATH . DB . 'empty.sql');
			$queries = explode("\n", $queries);

			foreach($queries as $query) {
				$query = trim($query);
				if (!empty($query)) {
					$query = appendTableName($query);
					$db->exec($query);
				}
			}
		}
		
		// Import default SQL
		$queries = file_get_contents(PATH . DB . $type . '.sql');
		$queries = explode("\n", $queries);
		
		foreach($queries as $query) {
			$query = trim($query);
			if(!empty($query)) {
				$query = appendTableName($query);
				$db->exec($query);
			}
		}
		
		// Add admin user
		$query = $db->prepare('INSERT INTO ' . $_POST['install_db_prefix'] . 'users (user_username, user_pass, user_realname, user_email, user_created, user_image_count, user_preferences) VALUES (?, ?, ?, ?, ?, ?, ?);');
		
		$query->execute(array($_POST['install_user'], sha1($_POST['install_pass']), $_POST['install_name'], $_POST['install_email'], date('Y-m-d H:i:s'), 0, 'a:6:{s:10:"page_limit";s:3:"100";s:13:"recent_images";b:1;s:8:"shoe_pub";b:1;s:8:"post_pub";b:1;s:19:"recent_images_limit";s:2:"25";s:11:"home_target";b:1;}'));
		
		$query->closeCursor();
		
		// Add admin thumbnails
		
		$query = $db->prepare('INSERT INTO ' . $_POST['install_db_prefix'] . 'sizes (size_title, size_label, size_height, size_width, size_type, size_append) VALUES (?, ?, ?, ?, ?, ?);');
		$query->execute(array('Dashboard (L)', 'admin',  590, 590, 'scale', '_admin'));
		$query->execute(array('Dashboard (S)', 'square', 80, 80, 'fill', '_sq'));
		$query->execute(array('Large', 'large', 950, 950, 'scale', '_l'));
		$query->execute(array('Medium', 'medium', 270, 270, 'scale', '_m'));
		$query->execute(array('Small', 'small', 100, 100, 'fill', '_s'));
		
		$query->closeCursor();
		
		// Add default theme
		
//		$query = $db->prepare('INSERT INTO ' . $_POST['install_db_prefix'] . 'themes (theme_uid, theme_title, theme_build, theme_version, theme_folder, theme_creator_name, theme_creator_uri) VALUES (?, ?, ?, ?, ?, ?, ?);');
//		$query->execute(array('225b134b655901223d2f2ee26599b71763b1e5fe', 'P1', 1, '1.0', 'p1', 'Wilkes & Barre', 'http://www.wilkesandbarre.com/'));
		$query = $db->prepare('INSERT INTO ' . $_POST['install_db_prefix'] . 'themes (theme_uid, theme_title, theme_build, theme_version, theme_folder, theme_creator_name, theme_creator_uri) VALUES (?, ?, ?, ?, ?, ?, ?);');
		$query->execute(array('FSIP_DEFAULT', 'fsipDefault', 1, '0.1', 'fsipDefault', 'Daryl Hawes', 'http://fsip.sdelargy.com/'));
		
		$query->closeCursor();
		
//// INSTALL DEFAULT SITE CONFIG SETTINGS

		setConf('theme_id', '1');
		setConf('theme_folder', 'fsipDefault');
		setConf('web_name', '');
		setConf('web_title', '(Untitled Site)');
		setConf('web_title_format', 'emdash2');
		setConf('web_description', '');
		setConf('web_email', '');
		setConf('web_timezone', 'America\/New_York');
		setConf('shoe_exif', true);
		setConf('shoe_iptc', true);
		setConf('shoe_geo', null);
		setConf('image_markup', null);
		setConf('image_markup_ext', '');
		setConf('thumb_imagick', null);
		setConf('thumb_compress', null);
		setConf('thumb_compress_tol', 100);
		setConf('thumb_watermark', null);
		setConf('thumb_watermark_pos', 'nw');
		setConf('thumb_watermark_margin', '');
		setConf('image_original', null);
		setConf('tag_alpha', null);
		setConf('comm_enabled', null);
		setConf('comm_email', null);
		setConf('comm_mod', null);
		setConf('comm_markup', null);
		setConf('comm_markup_ext', 'bbcode');
		setConf('rights_default', null); //DEH here is where we should add a default right of none_reserved
		setConf('rights_default_id', '');
		setConf('stat_enabled', true);
		setConf('stat_ignore_user', '');
		setConf('canvas_remove_unused', null);
		setConf('maint_reports', null);
		setConf('maint_debug', true);
		setConf('maint_disable', null);
		setConf('page_size_id', '1');
		setConf('page_size_label', 'admin');
		setConf('shoe_max', null);
		setConf('shoe_max_count', '');
		setConf('image_hdm', null);
		setConf('image_hdm_format', 'yyyy\/mm\/dd');
		setConf('web_markup', null);
		setConf('web_markup_ext', '');
		setConf('bulk_delete', null);
		setConf('thumb_metadata', null);
		setConf('page_div_wrap', null);
		setConf('page_div_wrap_class', '');
		setConf('comm_allow_html', null);
		setConf('comm_allow_html_tags', '');
		setConf('guest_remember', null);
		setConf('guest_remember_time', '86400');
		setConf('syndication_cache_time', '15');
		setConf('syndication_summary_only', null);
		setConf('sphinx_enabled', null);
		setConf('sphinx_server', '');
		setConf('sphinx_port', '');
		setConf('sphinx_index', '');
		setConf('sphinx_max_exec', '');
		setConf('maint_debug_admin_only', true);

		saveConf();
	} // end if can connect to db
}

define('TAB', 'Installation');
define('TITLE', 'FSIP Installation');

require_once(PATH . INCLUDES . 'admin/admin_header.php');
if ((@$_POST['install'] == 'Install') and (countNotes('error') == 0)) {
	
	?>
	
	<p class="large"><strong>Almost there.</strong> Copy and paste the text below into a text editor and save it as &#8220;config.php&#8221; to your hard disk. Then upload this file (overwriting the file that is already there) to your fsip root directory in order to complete your installation.</p>
	
	<p>Once you&#8217;re done, <a href="<?php echo BASE . ADMINFOLDER; ?>">log in to access your Dashboard</a>.</p>
	
	<textarea style="height: 30em;" class="code"><?php echo $config; ?></textarea>
	
<?php
} else {	
?>

	<p class="large"><strong>Welcome to FSIP.</strong> You&#8217;re halfway there, simply complete the fields below and follow the remaining instructions.</p>

	<form input="" method="post">
		<h3>Your Server OS</h3>
		
		<p>Not sure? We&#8217;ve automatically determined your server OS for you.</p>
	
		<table>
			<tr>
				<td class="right middle">
					<input type="radio" name="install_server" id="install_server_x" value="x" <?php if(SERVER_TYPE != 'win'){ echo 'checked="checked"'; } ?> />
				</td>
				<td>
					<label for="install_server_x">Linux, UNIX, OS X Server, or similar</label>
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<input type="radio" name="install_server" id="install_server_win" value="win" <?php if(SERVER_TYPE == 'win'){ echo 'checked="checked"'; } ?> />
				</td>
				<td>
					<label for="install_server_win">Windows&#0174; Server 2008 or similar</label>
				</td>
			</tr>
		</table>
		
		<h3>Your File Structure</h3>
	
		<p>Where did you install FSIP relative to your domain name?</p>
	
		<table>
			<tr>
				<td class="right pad">
					<label for="install_path">Full path:</label>
				</td>
				<td>
					<input type="text" name="install_path" id="install_path" class="m" value="<?php echo PATH; ?>" /><br />
					<span class="quiet">For example, /var/www/public_html/<em>yourdomain.com</em>/</span>
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_base">URL base:</label>
				</td>
				<td>
					<input type="text" name="install_base" id="install_base" class="s" value="<?php echo BASE; ?>" /><br />
					<span class="quiet">For example, http://<em>www.yourdomain.com</em>/stockphoto/ would be <strong>/stockphoto/</strong></span>
				</td>
			</tr>
		</table>
	
		<h3>Your Database Server</h3>
	
		<table>
			<tr>
				<td class="right middle">
					<label for="install_db_type">Database type:</label>
				</td>
				<td>
					<select name="install_db_type" id="install_db_type">
						<?php
						
						$php_pdo_drivers = @PDO::getAvailableDrivers();
						foreach($php_pdo_drivers as $driver){
							switch($driver){
								case 'odbc':
									// echo '<option value="mssql">Microsoft SQL Server</option>';
									break;
								case 'mysql':
									echo '<option value="mysql">MySQL</option>';
									break;
								case 'pgsql':
									echo '<option value="pgsql">PostgreSQL</option>';
									break;
								case 'sqlite':
									echo '<option value="sqlite">SQLite</option>';
									break;
								default:
									break;
							}
						}
						
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_prefix">Database table prefix:</label>
				</td>
				<td>
					<input type="text" name="install_db_prefix" id="install_db_prefix" value="<?php echo @$_POST['install_db_prefix'] ?>" class="xs" /> <span class="quiet">(optional)</span>
				</td>
			</tr>
			<tr>
				<td style="text-align: right;">
					<input type="checkbox" name="install_db_empty" id="install_db_empty" value="1">
				</td>
				<td>
					<label for="install_db_empty" style="font-weight: normal;">Delete FSIP database contents if they already exist.</label>
				</td>
			</tr>
		</table>
	
		<h4>MySQL and PostgreSQL</h4>
	
		<table>
			<tr>
				<td class="right pad">
					<label for="install_db_name">Database name:</label>
				</td>
				<td>
					<input type="text" name="install_db_name" id="install_db_name" value="<?php echo @$_POST['install_db_name'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_user">Database username:</label>
				</td>
				<td>
					<input type="text" name="install_db_user" id="install_db_user" value="<?php echo @$_POST['install_db_user'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_pass">Database password:</label>
				</td>
				<td>
					<input type="text" name="install_db_pass" id="install_db_pass" value="<?php echo @$_POST['install_db_pass'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_host">Database host:</label>
				</td>
				<td>
					<input type="text" name="install_db_host" id="install_db_host" value="<?php echo @$_POST['install_db_host'] ?>" class="m" /> <span class="quiet">(optional)</span>
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_port">Database port:</label>
				</td>
				<td>
					<input type="text" name="install_db_port" id="install_db_port" value="<?php echo @$_POST['install_db_port'] ?>" class="xs" /> <span class="quiet">(optional)</span>
				</td>
			</tr>
		</table>
	
		<h4>SQLite</h4>
	
		<table>
			<tr>
				<td class="right pad">
					<label for="install_db_file">Database file (full path):</label>
				</td>
				<td>
					<input type="text" name="install_db_file" id="install_db_file" value="<?php echo @$_POST['install_db_file'] ?>" class="m" /> <span class="quiet">(optional)</span><br />
					<span class="quiet">
						Defaults to <pre><?php echo DB; ?>fsip.db</pre>. Your database file must be writable (<pre>chmod 777</pre>).<br />
						For security purposes, this file will be renamed during installation.
					</span>
				</td>
			</tr>
		</table>
	
		<h3>Your Admin Account</h3>
	
		<p>Don&#8217;t worry, you can change these details later through your FSIP Dashboard.</p>
	
		<table>
			<tr>
				<td class="right middle">
					<label for="install_name">Name:</label>
				</td>
				<td>
					<input type="text" name="install_name" id="install_name" value="<?php echo @$_POST['install_name'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<label for="install_user">Username:</label>
				</td>
				<td>
					<input type="text" name="install_user" id="install_user" value="<?php echo @$_POST['install_user'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<label for="install_pass">Password:</label>
				</td>
				<td>
					<input type="password" name="install_pass" id="install_pass" value="<?php echo @$_POST['install_pass'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<label for="install_email">Email:</label>
				</td>
				<td>
					<input type="text" name="install_email" id="install_email" value="<?php echo @$_POST['install_email'] ?>" class="m" />
				</td>
			</tr>
			<!-- <tr>
				<td style="text-align: right;">
					<input type="checkbox" name="install_welcome" id="install_welcome" value="1" checked="checked">
				</td>
				<td>
					<label for="install_welcome" style="font-weight: normal;">Send me a welcome email containing my username and password.</label>
				</td>
			</tr> -->
		</table>
		
		<h3>Install FSIP</h3>
	
		<p>This may take several moments, please be patient. Do not interrupt the process by stopping the page from loading or closing your Web browser.</p><p><input type="submit" name="install" value="Install" /></p>
	</form>

	<?php
}

require_once(PATH . INCLUDES . '/admin_footer.php');

?>