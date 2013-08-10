<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

$user = new User;
$user->hasPermission('extensions', true);
//echo "extensions 1<br />";

global $db;


if (!empty($_GET['id'])) {
	$extension_id = findID($_GET['id']);
}

if (!empty($_GET['act'])) {
	$extension_act = $_GET['act'];
}
//echo "extensions 2<br />";

// SAVE CHANGES
if (!empty($_POST['extension_id'])) {
	$extension_id = findID($_POST['extension_id']);
	
	// Reset extension
	if (@$_POST['extension_reset'] == 'reset') {
		$fields = array('extension_preferences' => '');
		$bool = $db->updateRow($fields, 'extensions', $extension_id);
		if ($bool === true) {
			addNote('You successfully reset the extension.', 'success');
			$reset = 1;
		}
	}
	
	// Disable extension
	if (@$_POST['extension_disable'] == 'disable') {
		$fields = array('extension_status' => 0);
		$bool = $db->updateRow($fields, 'extensions', $extension_id);
		if ($bool === true) {
			addNote('You successfully disabled the extension.', 'success');
			$disable = 1;
		}
	}
	
	// Enable extension
	if (@$_POST['extension_enable'] == 'enable') {
		$fields = array('extension_status' => 1);
		$bool = $db->updateRow($fields, 'extensions', $extension_id);
		if ($bool === true) {
			addNote('You successfully enabled the extension.', 'success');
			$enable = 1;
		}
	}
	
	// Save extension, if no other action taken
	if ((@$reset != 1) or (@$disable != 1) or (@$enable != 1)) {
		$orbit = new Orbit($extension_id);
		$orbit->hook('config_save');
	}
	
	// If not only resetting, return to Extensions page
	if ((@$reset != 1) or (@$disable == 1) or (@$enable != 1)) {
		unset($extension_id);
	}
}

// Configuration: maint_disable
if (returnConf('maint_disable')) {
	addNote('All extensions have been disabled.', 'notice');
}
//echo "extensions 3<br />";

updateExtensions();
// load up current extensions
$extensions = $db->getTable('extensions');

define('TAB', 'settings');

if (empty($extension_id)) {
	$extensions = $db->getTable('extensions', null, null, null, array('extension_status DESC', 'extension_title ASC'));
	$extensions_count = @count($extensions);
	
	define('TITLE', 'FSIP Extensions');
	require_once(PATH . INCLUDES . '/admin_header.php');

	?>

	<h1><img src="<?php echo BASE . IMGFOLDER; ?>icons/extensions.png" alt="" /> Extensions (<?php echo @$extensions_count; ?>)</h1>
	<!-- //DEH removing dead remote user lounge links
	<p>Extensions add new functionality to your FSIP installation. You can browse and download additional extensions at the <a href="http://www.alkalineapp.com/users/">Alkaline Lounge</a>.</p>
	-->
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Extension</th>
			<th class="center">Status</th>
			<th class="center">Version</th>
			<th class="center">Update</th>
		</tr>
		<?php
	
		foreach($extensions as $extension){
			echo '<tr class="ro">';
			echo '<td><strong class="large"><a href="' . BASE . ADMINFOLDER . 'extensions' . URL_ID . $extension['extension_id'] . URL_RW . '">' . $extension['extension_title'] . '</a></strong>';
			if(!empty($extension['extension_creator_name'])){
				echo ' \ ';
				if(!empty($extension['extension_creator_uri'])){
					echo '<a href="' . $extension['extension_creator_uri'] . '" class="nu">' . $extension['extension_creator_name'] . '</a>';
				}
				else{
					echo $extension['extension_creator_name'];
				}
			}
			echo '<br /><span class="quiet">' . $extension['extension_description'] . '</span></td>';
			echo '<td class="center">';
			echo (($extension['extension_status'] == 1) ? 'Enabled' : 'Disabled');
			echo '</td>';
/*
//DEH remove the currently dead remote extension version checking
			echo '<td class="center">' . $extension['extension_version'] . '</td>';
			if(!empty($extension['extension_build_latest'])){
				echo '<td class="center"><a href="http://www.alkalineapp.com/users/extensions/">Download</a>';
				if(!empty($extension['extension_version_latest'])){
					echo ' (v' . $extension['extension_version_latest'] .')';
				}
				echo '</td>';
			}
			else{
*/
				echo '<td class="center quiet">&#8212;</td>';
//			}
			echo '</tr>';
		}
	
		?>
	</table>
	
	<?php

	require_once(PATH . INCLUDES . '/admin_footer.php');
	
}
else{
	// Get extension
	$extension = $db->getRow('extensions', $extension_id);
	$extension = makeHTMLSafe($extension);
	
	if($extension['extension_status'] > 0){
		$orbit = new Orbit($extension_id);
		$orbit->hook('config_load');
	
		define('TITLE', 'Extension: &#8220;' . $extension['extension_title']  . '&#8221;');
		require_once(PATH . INCLUDES . '/admin_header.php');
	
		?>
	
		<h1><img src="<?php echo BASE . IMGFOLDER; ?>icons/extensions.png" alt="" /> Extension: <?php echo $extension['extension_title']; ?></h1>
	
		<form id="extension" action="<?php echo BASE . ADMINFOLDER; ?>extensions<?php echo URL_CAP; ?>" method="post">
			<div>
				<?php $orbit->hook('config'); ?>
			</div>
		
			<table>
				<tr>
					<td class="right"><input type="checkbox" id="extension_reset" name="extension_reset" value="reset" /></td>
					<td><strong><label for="extension_reset">Reset this extension.</label></strong> This action cannot be undone.</td>
				</tr>
				<tr>
					<td class="right"><input type="checkbox" id="extension_disable" name="extension_disable" value="disable" /></td>
					<td><strong><label for="extension_disable">Disable this extension.</label></strong></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="hidden" name="extension_id" value="<?php echo $extension['extension_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo back(); ?>">cancel</a></td>
				</tr>
			</table>
		</form>
	
		<?php
	
		require_once(PATH . INCLUDES . '/admin_footer.php');
	}
	else{
		define('TITLE', 'Extension: &#8220;' . $extension['extension_title']  . '&#8221;');
		require_once(PATH . INCLUDES . '/admin_header.php');
		
		?>
		
		<h1><img src="<?php echo BASE . IMGFOLDER; ?>icons/extensions.png" alt="" /> <?php echo $extension['extension_title']; ?></h1>
		
		<form id="extension" action="<?php echo BASE . ADMINFOLDER; ?>extensions<?php echo URL_CAP; ?>" method="post">
			<table>
				<tr>
					<td class="right"><input type="checkbox" id="extension_reset" name="extension_reset" value="reset" /></td>
					<td><strong><label for="extension_reset">Reset this extension.</label></strong> This action cannot be undone.</td>
				</tr>
				<tr>
					<td class="right"><input type="checkbox" id="extension_enable" name="extension_enable" value="enable" /></td>
					<td><strong><label for="extension_enable">Enable this extension.</label></strong></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="hidden" name="extension_id" value="<?php echo $extension['extension_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo back(); ?>">cancel</a></td>
				</tr>
			</table>
		</form>
		
		<?php
	
		require_once(PATH . INCLUDES . '/admin_footer.php');
	}
	
}
?>