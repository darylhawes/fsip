<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$orbit = new Orbit;
$user = new User;

$user->perm(true, 'shoebox');

// PROCESS SUBMITTED IMAGES
if(!empty($_POST['image_ids'])){
	$image_ids = explode(',', $_POST['image_ids']);
	array_pop($image_ids);
	
	$fsip->convertToIntegerArray($image_ids);
	
	foreach($image_ids as $image_id){
		$image = new Image($image_id);
		if(@$_POST['image-' . $image_id . '-delete'] == 'delete'){
			if($image->delete()){
				$fsip->addNote('Your image has been deleted.', 'success');
			}
		}
		else{
			$image_title = $fsip->makeUnicode(@$_POST['image-' . $image_id . '-title']);
			$image_description_raw = $fsip->makeUnicode(@$_POST['image-' . $image_id . '-description-raw']);
			
			if($fsip->returnConf('web_markup')){
				$image_markup_ext = $fsip->returnConf('web_markup_ext');
				$image_title = $orbit->hook('markup_title_' . $image_markup_ext, $image_title, $image_title);
				$image_description = $orbit->hook('markup_' . $image_markup_ext, $image_description_raw, $image_description_raw);
			}
			else{
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
	
	if($user->returnPref('shoe_to_bulk') === true){
		Find::clearMemory();

		$new_image_ids = new Find('images');
		$new_image_ids->_ids($image_ids);
		$new_image_ids->saveMemory();
		
		session_write_close();
		
		header('Location: ' . BASE . ADMIN . 'features' . URL_ACT . 'bulk' . URL_RW);
	}
	else{
		header('Location: ' . BASE . ADMIN . 'library' . URL_CAP);
	}
	exit();
}

// New posts
$files = $fsip->seekDirectory(PATH . SHOEBOX, 'txt|mdown|md|markdown|textile');
$p_count = count($files);

foreach($files as $file){
	$post = new Post();
	$post->attachUser($user);
	$post->import($file);
}

// New images
$files = $fsip->seekDirectory(PATH . SHOEBOX);
$i_count = count($files);

if(($i_count == 0) and ($p_count == 0)){
	$fsip->addNote('There are no files in your shoebox.', 'error');
	header('Location: ' . BASE . ADMIN . 'upload' . URL_CAP);
	exit();
}
elseif(($i_count == 0) and ($p_count > 0)){
	$fsip->addNote('You have successfully imported ' . $fsip->returnFullCount($p_count, 'post') . '.', 'success');
	header('Location: ' . BASE . ADMIN . 'posts' . URL_CAP);
	exit();
}
elseif($p_count > 0){
	$fsip->addNote('You have also successfully imported ' . $fsip->returnFullCount($p_count, 'post') . '.', 'success');
}

define('TAB', 'shoebox');
define('TITLE', 'Shoebox');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/shoebox.png" alt="" /> Shoebox (<?php echo $i_count; ?>)</h1>

<div class="none get_location_set"><?php echo @$_SESSION['fsip']['location']; ?></div>

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