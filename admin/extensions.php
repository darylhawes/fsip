<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

$user->perm(true, 'extensions');

if(!empty($_GET['id'])){
	$extension_id = $fsip->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$extension_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['extension_id'])){
	$extension_id = $fsip->findID($_POST['extension_id']);
	
	// Reset extension
	if(@$_POST['extension_reset'] == 'reset'){
		$fields = array('extension_preferences' => '');
		$bool = $fsip->updateRow($fields, 'extensions', $extension_id);
		if($bool === true){
			$fsip->addNote('You successfully reset the extension.', 'success');
			$reset = 1;
		}
	}
	
	// Disable extension
	if(@$_POST['extension_disable'] == 'disable'){
		$fields = array('extension_status' => 0);
		$bool = $fsip->updateRow($fields, 'extensions', $extension_id);
		if($bool === true){
			$fsip->addNote('You successfully disabled the extension.', 'success');
			$disable = 1;
		}
	}
	
	// Enable extension
	if(@$_POST['extension_enable'] == 'enable'){
		$fields = array('extension_status' => 1);
		$bool = $fsip->updateRow($fields, 'extensions', $extension_id);
		if($bool === true){
			$fsip->addNote('You successfully enabled the extension.', 'success');
			$enable = 1;
		}
	}
	
	// Save extension, if no other action taken
	if((@$reset != 1) or (@$disable != 1) or (@$enable != 1)){
		$orbit = new Orbit($extension_id);
		$orbit->hook('config_save');
	}
	
	// If not only resetting, return to Extensions page
	if((@$reset != 1) or (@$disable == 1) or (@$enable != 1)){
		unset($extension_id);
	}
}

// Configuration: maint_disable
if($fsip->returnConf('maint_disable')){
	$fsip->addNote('All extensions have been disabled.', 'notice');
}

// Load current extensions
$extensions = $fsip->getTable('extensions');

// Seek all extensions
$seek_extensions = $fsip->seekDirectory(PATH . EXTENSIONS, '');

$extension_ids = array();
$extension_uids = array();
$extension_builds = array();
$extension_folders = array();
$extension_classes = array();

foreach($extensions as $extension){
	$extension_ids[] = $extension['extension_id'];
	$extension_uids[] = $extension['extension_uid'];
	$extension_builds[] = $extension['extension_build'];
	$extension_folders[] = $extension['extension_folder'];
	$extension_classes[] = $extension['extension_class'];
}


// Determine which themes have been removed, delete rows from table
$extension_deleted = array();

foreach($extensions as $extension){
	$extension_folder = PATH . EXTENSIONS . $extension['extension_folder'];
	if(!in_array($extension_folder, $seek_extensions)){
		$extension_deleted[] = $extension['extension_id'];
	}
}

$fsip->deleteRow('extensions', $extension_deleted);

// Determine which extensions are new, install them
$extensions_installed = array();
$extensions_updated = array();

foreach($seek_extensions as &$extension_folder){
	if(strpos($fsip->getFilename($extension_folder), '.') === 0){ continue; }
	
	$extension_folder = $fsip->getFilename($extension_folder);
	if(!in_array($extension_folder, $extension_folders)){
		$data = file_get_contents(PATH . EXTENSIONS . $extension_folder . '/extension.xml');
		if(empty($data)){ $fsip->addNote('Could not install a new extension. Its XML file is missing or corrupted.', 'error'); continue; }
		
		$xml = new SimpleXMLElement($data);
		
		if(in_array($xml->class, $extension_classes)){
			$fsip->addNote('Could not install a new extension. Its class name interferes with a pre-existing extension.', 'error');
		}
		
		require_once(PATH . EXTENSIONS . $extension_folder . '/' . $xml->file . '.php');
		
		$extension_methods = get_class_methods(strval($xml->class));
		$extension_hooks = array();
		
		foreach($extension_methods as $method){
			if(strpos($method, 'orbit_') === 0){
				$extension_hooks[] = substr($method, 6);
			}
		}
		
		$fields = array('extension_uid' => $xml->uid,
			'extension_class' => $xml->class,
			'extension_title' => $xml->title,
			'extension_file' => $xml->file,
			'extension_folder' => $extension_folder,
			'extension_status' => 1,
			'extension_build' => $xml->build,
			'extension_version' => $xml->version,
			'extension_hooks' => serialize($extension_hooks),
			'extension_description' => $xml->description,
			'extension_creator_name' => $xml->creator->name,
			'extension_creator_uri' => $xml->creator->uri);
		$extension_intalled_id = $fsip->addRow($fields, 'extensions');
		$extensions_installed[] = $extension_intalled_id;
	}
	else{
		$data = file_get_contents(PATH . EXTENSIONS . $extension_folder . '/extension.xml');
		$xml = new SimpleXMLElement($data);
		$keys = array_keys($extension_classes, $xml->class);
		foreach($keys as $key){
			if($xml->build != $extension_builds[$key]){
				$id = $extension_ids[$key];
		
				require_once(PATH . EXTENSIONS . $extension_folder . '/' . $xml->file . '.php');
		
				$extension_methods = get_class_methods(strval($xml->class));
				$extension_hooks = array();
		
				foreach($extension_methods as $method){
					if(strpos($method, 'orbit_') === 0){
						$extension_hooks[] = substr($method, 6);
					}
				}
		
				$fields = array('extension_class' => $xml->class,
					'extension_title' => $xml->title,
					'extension_file' => $xml->file,
					'extension_folder' => $extension_folder,
					'extension_build' => $xml->build,
					'extension_version' => $xml->version,
					'extension_hooks' => serialize($extension_hooks),
					'extension_description' => $xml->description,
					'extension_creator_name' => $xml->creator->name,
					'extension_creator_uri' => $xml->creator->uri);
				$fsip->updateRow($fields, 'extensions', $id);
				$extensions_updated[] = $id;
			}
		}
	}
}

$extensions_installed_count = count($extensions_installed);
if($extensions_installed_count > 0){
	if($extensions_installed_count == 1){
		$notification = 'You have successfully installed 1 extension.';
	}
	else{
		$notification = 'You have successfully installed ' . $extensions_installed_count . ' extensions.';
	}
	
	$fsip->addNote($notification, 'success');
	
	$extensions = $fsip->getTable('extensions');
}

$extensions_updated_count = count($extensions_updated);
if($extensions_updated_count > 0){
	if($extensions_updated_count == 1){
		$notification = 'You have successfully updated 1 extension.';
	}
	else{
		$notification = 'You have successfully updated ' . $extensions_updated_count . ' extensions.';
	}
	
	$fsip->addNote($notification, 'success');
	
	$extensions = $fsip->getTable('extensions');
}

define('TAB', 'settings');

if(empty($extension_id)){
	// Check for updates
	$latest_extensions = @$fsip->boomerang('latest-extensions');
	if(!empty($latest_extensions)){
		foreach($latest_extensions as $latest_extension){
			foreach($extensions as &$extension){
				if($extension['extension_uid'] == $latest_extension['extension_uid']){
					if($latest_extension['extension_build'] > $extension['extension_build']){
						$fields = array('extension_build_latest' => $latest_extension['extension_build'],
							'extension_version_latest' => $latest_extension['extension_version']);
						$fsip->updateRow($fields, 'extensions', $extension['extension_id']);
					}
					else{
						if(!empty($extension['extension_build_latest']) or !empty($extension['extension_version_latest'])){
							$fields = array('extension_build_latest' => '',
								'extension_version_latest' => '');
							$fsip->updateRow($fields, 'extensions', $extension['extension_id']);
						}
					}
				}
			}
		}
	}
	
	$extensions = $fsip->getTable('extensions', null, null, null, array('extension_status DESC', 'extension_title ASC'));
	$extensions_count = @count($extensions);
	
	define('TITLE', 'Extensions');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>

	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/extensions.png" alt="" /> Extensions (<?php echo @$extensions_count; ?>)</h1>
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
			echo '<td><strong class="large"><a href="' . BASE . ADMIN . 'extensions' . URL_ID . $extension['extension_id'] . URL_RW . '">' . $extension['extension_title'] . '</a></strong>';
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

	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	// Get extension
	$extension = $fsip->getRow('extensions', $extension_id);
	$extension = $fsip->makeHTMLSafe($extension);
	
	if($extension['extension_status'] > 0){
		$orbit = new Orbit($extension_id);
		$orbit->hook('config_load');
	
		define('TITLE', 'Extension: &#8220;' . $extension['extension_title']  . '&#8221;');
		require_once(PATH . ADMIN . 'includes/header.php');
	
		?>
	
		<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/extensions.png" alt="" /> Extension: <?php echo $extension['extension_title']; ?></h1>
	
		<form id="extension" action="<?php echo BASE . ADMIN; ?>extensions<?php echo URL_CAP; ?>" method="post">
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
					<td><input type="hidden" name="extension_id" value="<?php echo $extension['extension_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $fsip->back(); ?>">cancel</a></td>
				</tr>
			</table>
		</form>
	
		<?php
	
		require_once(PATH . ADMIN . 'includes/footer.php');
	}
	else{
		define('TITLE', 'Extension: &#8220;' . $extension['extension_title']  . '&#8221;');
		require_once(PATH . ADMIN . 'includes/header.php');
		
		?>
		
		<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/extensions.png" alt="" /> <?php echo $extension['extension_title']; ?></h1>
		
		<form id="extension" action="<?php echo BASE . ADMIN; ?>extensions<?php echo URL_CAP; ?>" method="post">
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
					<td><input type="hidden" name="extension_id" value="<?php echo $extension['extension_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $fsip->back(); ?>">cancel</a></td>
				</tr>
			</table>
		</form>
		
		<?php
		
		require_once(PATH . ADMIN . 'includes/footer.php');
	}
	
}
?>