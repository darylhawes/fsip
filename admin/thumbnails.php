<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/
require_once('../config.php');

global $db;

$user = new User;
$user->hasPermission('thumbnails', true);

if (!empty($_GET['id'])) {
	$size_id = findID($_GET['id']);
}

if (!empty($_GET['act'])) {
	$size_act = $_GET['act'];
}

// SAVE CHANGES
if (!empty($_POST['size_id'])) {
	$size_id = findID($_POST['size_id']);
	
	// Delete size
	if (@$_POST['size_delete'] == 'delete') {
		$db->deleteRow('sizes', $size_id);
	} else {  // Update size
		// Check for file append, prepend duplicates--will overwrite
		$query = $db->prepare('SELECT size_title FROM sizes WHERE size_append = :size_append AND size_prepend = :size_prepend AND size_id != ' . $size_id);
		$query->execute(array(':size_append' => @$_POST['size_append'], ':size_prepend' => @$_POST['size_prepend']));
		$sizes = $query->fetchAll();
		
		if (count($sizes) > 0) {
			$size_title = $sizes[0]['size_title'];
			addNote('The thumbnail &#8220;' . $size_title . '&#8221; already uses these prepend and append to filename settings.', 'error');
		} else {
			if (@$_POST['size_watermark'] == 'watermark') {
				$size_watermark = 1;
			} else {
				$size_watermark = 0;
			}
		
			$fields = array('size_title' => makeUnicode($_POST['size_title']),
				'size_label' => preg_replace('#[^a-z]#si', '', makeUnicode($_POST['size_label'])),
				'size_height' => $_POST['size_height'],
				'size_width' => $_POST['size_width'],
				'size_type' => $_POST['size_type'],
				'size_append' => @$_POST['size_append'],
				'size_prepend' => @$_POST['size_prepend'],
				'size_watermark' => $size_watermark);
		
			$db->updateRow($fields, 'sizes', $size_id);
		}
	}
	
	// Build size
	if ((@$_POST['size_build'] == 'build') and (countNotes('error') == 0)) {
		// Store to build thumbnails
		$_SESSION['fsip']['maintenance']['size_id'] = $size_id;
		
		sleep(1);
		
		$location = LOCATION . BASE. ADMINFOLDER . 'maintenance' . URL_CAP . '#build-thumbnail';
		headerLocationRedirect($location);
		exit();
	}
	
	if (countNotes('error') == 0) {
		unset($size_id);
	}
} else {
	$db->deleteEmptyRow('sizes', array('size_title'));
}

// CREATE SIZE
if (!empty($size_act) and ($size_act == 'build')) {
	$size_id = $db->addRow(null, 'sizes');
}

define('TAB', 'settings');

// GET SIZES TO VIEW OR SIZE TO EDIT
if (empty($size_id)) {
	$sizes = $db->getTable('sizes', null, null, null, 'size_title ASC');
	$size_count = @count($sizes);
	
	define('TITLE', 'FSIP Thumbnails');
	require_once(PATH . INCLUDES . 'admin/admin_header.php');

?>
	
	<div class="actions"><a href="<?php echo BASE . ADMINFOLDER . 'thumbnails' . URL_ACT; ?>build<?php echo URL_RW; ?>"><button>Build thumbnail</button></a></div>

	<h1><img src="<?php echo BASE . IMGFOLDER; ?>icons/thumbnails.png" alt="" /> Thumbnails (<?php echo $size_count; ?>)</h1>
	
	<p>Thumbnails are resized versions of each image in your library.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Title</th>
			<th class="center">Dimensions (W&#0215;H)</th>
			<th class="center">Type</th>
			<th class="center">Canvas tag</th>
		</tr>
<?php
	
		foreach($sizes as $size) {
			echo '<tr class="ro">';
				echo '<td><strong class="large"><a href="' . BASE . ADMINFOLDER . 'thumbnails' . URL_ID . $size['size_id'] . URL_RW . '">' . $size['size_title'] . '</a></strong></td>';
				echo '<td class="center">' . $size['size_width'] . ' &#0215; ' . $size['size_height'] . '</td>';
				echo '<td class="center">' . ucwords($size['size_type']) . '</td>';
				echo '<td class="center">{Image_Src_' . ucwords($size['size_label']) . '}</td>';
			echo '</tr>';
		}
	
?>
	</table>

<?php
	
	require_once(PATH . INCLUDES . 'admin/admin_footer.php');
	
} else {
	// Get sizes set
	$size = $db->getRow('sizes', $size_id);
	$size = makeHTMLSafe($size);

	// Dashboard thumbnail warning
	if (($size['size_label'] == 'admin') or ($size['size_label'] == 'square')) {
		addNote('This thumbnail is crucial to the proper functioning of your dashboard. Modify at your own risk.', 'error');
	}
	
	if (!empty($size['size_title'])) {
		define('TITLE', 'Thumbnail: &#8220;' . ucwords($size['size_title'])  . '&#8221;');
	}
	require_once(PATH . INCLUDES . 'admin/admin_header.php');
	
	if (empty($size['size_title'])) {
		echo '<h1><img src="' . BASE . IMGFOLDER . 'icons/thumbnails.png" alt="" /> New Thumbnail</h1>';
	} else {
		echo '<h1><img src="' . BASE . IMGFOLDER . 'icons/thumbnails.png" alt="" /> Thumbnail: ' . $size['size_title'] . '</h1>';
	}
	
?>
	
	<p>All fields are required except append to and prepend to filename&#8212;use one or both.</p>
	
	<form action="<?php echo BASE . ADMINFOLDER; ?>thumbnails<?php echo URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="size_title">Title:</label></td>
				<td><input type="text" id="size_title" name="size_title" value="<?php echo $size['size_title']; ?>" class="m notempty" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="size_label">Label:</label></td>
				<td>
					<input type="text" id="size_label" name="size_label" value="<?php echo @$size['size_label']; ?>" class="s notempty" />
				</td>
			</tr>
			<tr>
				<td class="right middle"><label>Dimensions:</label></td>
				<td><input type="text" id="size_width" name="size_width" value="<?php echo $size['size_width']; ?>" class="nonzero" style="width: 4em;" /> pixels (width) &#0215; <input type="text" id="size_height" name="size_height" value="<?php echo $size['size_height']; ?>" class="nonzero" style="width: 4em;" /> pixels (height)</td>
			</tr>
			<tr>
				<td class="right"><label>Type:</label></td>
				<td>
					<table>
						<tr>
							<td style="width: 10px;"><input type="radio" name="size_type" value="scale" id="size_type_scale" <?php if(($size['size_type'] == 'scale') or (empty($size['size_type']))){ echo 'checked="checked" '; } ?> /></td>
							<td>
								<label for="size_type_scale">Scale image</label><br />
								Scales to the restricting dimension&#8212;standard thumbnails
							</td>
						</tr>
						<tr>
							<td style="width: 10px;"><input type="radio" name="size_type" value="fill" id="size_type_fill" <?php if($size['size_type'] == 'fill'){ echo 'checked="checked" '; } ?> /></td>
							<td>
								<label for="size_type_fill">Fill canvas</label><br />
								Fills the thumbnail, crops excess&#8212;good for arranging in grids
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="size_append">Append to filename:</label></td>
				<td>
					<input type="text" id="size_append" name="size_append" value="<?php echo @$size['size_append']; ?>" style="width: 5em;" /><br />
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="size_prepend">Prepend to filename:</label></td>
				<td>
					<input type="text" id="size_prepend" name="size_prepend" value="<?php echo @$size['size_prepend']; ?>" style="width: 5em;" />
				</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="size_watermark" name="size_watermark" value="watermark" <?php if($size['size_watermark'] == 1){ echo 'checked="checked"'; } ?> /></td>
				<td>
					<label for="size_watermark">Apply watermark to this thumbnail size.</label>
					<div class="quiet none" id="size_watermark_note">You can apply a custom watermark by uploading a file here:<br />
					<span id="size_watermark_link"></span></div>
				</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="size_build" name="size_build" value="build" <?php if(empty($size['size_title'])){ echo 'checked="checked"'; } ?> /></td>
				<td><label for="size_build">Build thumbnails of this size.</label> This action cannot be undone.</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="size_delete" name="size_delete" value="delete" /></td>
				<td><label for="size_delete">Delete this thumbnail size.</label> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="size_id" value="<?php echo $size['size_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

<?php
	
	require_once(PATH . INCLUDES . 'admin/admin_footer.php');
	
}

?>