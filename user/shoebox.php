<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');

$orbit = new Orbit;

$user = new User;
$user->hasPermission('shoebox', true);

// PROCESS SUBMITTED IMAGES
if (!empty($_POST['image_ids'])) {
	$image_ids = explode(',', $_POST['image_ids']);
	array_pop($image_ids);
	
	convertToIntegerArray($image_ids);
	
	foreach($image_ids as $image_id) {
		$image = new Image($image_id);
		if (@$_POST['image-' . $image_id . '-delete'] == 'delete') {
			if ($image->delete()) {
				addNote('Your image has been deleted.', 'success');
			}
		} else {
			$image_title = makeUnicode(@$_POST['image-' . $image_id . '-title']);
			$image_description_raw = makeUnicode(@$_POST['image-' . $image_id . '-description-raw']);
			
			if ($fsip->returnConf('web_markup')) {
				$image_markup_ext = $fsip->returnConf('web_markup_ext');
				$image_title = $orbit->hook('markup_title_' . $image_markup_ext, $image_title, $image_title);
				$image_description = $orbit->hook('markup_' . $image_markup_ext, $image_description_raw, $image_description_raw);
			} else {
				$image_markup_ext = '';
				$image_description = $fsip->nl2br($image_description_raw);
			}
			
			$fields = array('image_title' => $image_title,
				'image_description_raw' => $image_description_raw,
				'image_description' => $image_description,
				'image_geo' => $fsip->makeUnicode(@$_POST['image-' . $image_id . '-geo']),
				'image_published' => @$_POST['image-' . $image_id . '-published'],
				'image_privacy' => @$_POST['image-' . $image_id . '-privacy'],
				'right_id' => @$_POST['right-' . $image_id . '-id']);
			$image->updateFields($fields);
			$image->updateTags(json_decode(@$_POST['image-' . $image_id . '-tags_input']));
		}
	}
	
	$fsip->addNote('Your shoebox has been processed.', 'success');
	
	if ($user->returnPref('shoe_to_bulk') === true) {
		Find::clearMemory();

		$new_image_ids = new Find('images');
		$new_image_ids->_ids($image_ids);
		$new_image_ids->saveMemory();
		
		session_write_close();
		
		$location: = BASE . ADMINFOLDER . 'features' . URL_ACT . 'bulk' . URL_RW);
		headerLocationRedirect($location);
	} else {
		$location = BASE . ADMINFOLDER . 'library' . URL_CAP);
		headerLocationRedirect($location);
	}
	exit();
}

// New images DEH - here is where we should be able to seek through subdirectories based on userid
$files = Files::seekDirectory(PATH . SHOEBOX);
$i_count = count($files);

if ($i_count == 0) {
	$fsip->addNote('There are no files in your shoebox.', 'error');
	$location = . BASE . ADMINFOLDER . 'upload' . URL_CAP);
	headerLocationRedirect($location);
	exit();
}

define('TAB', 'shoebox');
define('TITLE', 'Shoebox');
require_once(PATH . ADMINFOLDER . 'includes/header.php');

?>

<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/shoebox.png" alt="" /> Shoebox (<?php echo $i_count; ?>)</h1>

<div class="none get_location_set"><?php if (isset($_SESSION['fsip']) && isset($_SESSION['fsip']['location']) ) { echo @$_SESSION['fsip']['location']; } ?></div>

<form action="" method="post">
	<div id="privacy_html" class="none">
		<?php echo $fsip->showPrivacy('image--privacy'); ?>
	</div>
	
	<div id="rights_html" class="none">
		<?php echo $fsip->showRights('right--id'); ?>
	</div>
	
	<div id="shoebox_images">
		
	</div>

	<p id="progress">
	
	</p>

	<p>
		<input id="shoebox_image_ids" type="hidden" name="image_ids" value="" />
		<input id="shoebox_add" type="submit" value="Save changes" /> or <a href="<?php echo $fsip->back(); ?>">cancel</a>
	</p>
</form>
	
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>