<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

//echo "uploads 1<br />";
require_once('../config.php');

//echo "uploads 4<br />";
$user = new User;
//echo "uploads 5<br />";
$orbit = new Orbit;
//echo "uploads 6<br />";

// cliqcliq Quickpic support
if (isset($_REQUEST['context']) and ($_REQUEST['context'] == sha1(PATH . BASE . DB_DSN . DB_TYPE))) {
	header('Content-Type: application/x-plist');
	
	$file = $_FILES['upload_file'];
	move_uploaded_file($file['tmp_name'], correctWinPath(PATH . SHOEBOX . $file['name']));
	
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	?>
	<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
	<plist version="1.0">
	<dict>
	<key>success</key>
	<true/>
	</dict>
	</plist>
	<?php
}

$user->hasPermission('upload', true);
//echo "uploads 8<br />";

if (!empty($_FILES)) {
	$filename = $_FILES['user_file']['name'][0];
	$tmp_file = $_FILES['user_file']['tmp_name'][0];
	copy($tmp_file, correctWinPath(PATH . SHOEBOX . $filename));
	unlink($tmp_file);
	
	exit();
}
//echo "uploads 9<br />";

if (isset($_GET['success']) and ($_GET['success'] == 1)) {
	$location = LOCATION . BASE. ADMINFOLDER . 'shoebox' . URL_CAP;
	headerLocationRedirect($location);
	exit();
}

//echo "uploads 9.1<br />";
$orbit->hook('shoebox');
//echo "uploads 10<br />";

define('TAB', 'upload');
define('TITLE', 'FSIP Upload');
require_once(PATH . INCLUDES . 'admin/admin_header.php');
//echo "uploads 11<br />";

// cliqcliq Quickpic support
if(preg_match('#iphone|ipad#si', $_SERVER['HTTP_USER_AGENT']) and !isset($_GET['success'])){
	?>
	<script type="text/javascript">
		launchQuickpic('<?php echo sha1(PATH . BASE . DB_DSN . DB_TYPE); ?>');
	</script>
	<?php
}

?>

<div class="actions"><a href="<?php echo BASE . ADMINFOLDER . 'shoebox' . URL_CAP; ?>"><button>Go to shoebox</button></a></div>
<h1><img src="<?php echo BASE . IMGFOLDER; ?>icons/upload.png" alt="" /> Upload</h1>

<div class="span-24 last">
	<div class="span-18 append-1">
		<form enctype="multipart/form-data" action="" method="post" style="padding-top: 1em;">
			<img src="<?php echo BASE . IMGFOLDER; ?>upload_box.png" alt="" style="position: absolute; z-index: -25;" />
			<div style="height: 380px; margin-bottom: 1.5em;">
				<input type="file" multiple="multiple" id="upload" />
			</div>
		</form>
	</div>
	<div class="span-5 append-top last">

		<h3>Status</h3>
		<p>You have uploaded <span id="upload_count_text">0 files</span> this session.</p>
				
		<h3>Instructions</h3>
		<p>Drag images from a folder on your computer or directly from most applications into the grey retaining area.</p>
	
		<p>You can also browse your computer and select the files you wish to upload by clicking the &#8220;Choose File&#8221; button.</p>
	
		<p>Once you&#8217;ve finished uploading, go to your <a href="<?php echo BASE . ADMINFOLDER . 'shoebox' . URL_CAP; ?>">shoebox</a> to process your files.</p>

		<h3>File size limit</h3>
		<p>
			<?php

				$smallestSize = array('post_max_size', 'upload_max_filesize', 'memory_limit');
				$smallestSize = array_map('ini_get', $smallestSize);
				$smallestSize = array_map('convertToBytes', $smallestSize);
				sort($smallestSize); //sort the numbers before converting back to a string
				$smallestSize = array_map('convertBytesToShortString', $smallestSize);
				echo $smallestSize[0]. '<span class="quiet"> (<a href="../docs/faq.md#file-size-limit-uploads">Why?</a>)</span>';

				$sizes = array('post_max_size', 'upload_max_filesize', 'memory_limit');
				$sizes = array_map('ini_get', $sizes);
				$sizes = array_map('convertToBytes', $sizes);
				$sizes = array_map('convertBytesToShortString', $sizes);
				$size_info = '<span class="max_sizes">Max post: '.$sizes[0]."<br />Max upload: ".$sizes[1]."<br />Max memory: ".$sizes[2]."</span>";
				echo "<br /><br />$size_info";
			?>
		</p>

	</div>
</div>

<?php
require_once(PATH . INCLUDES . 'admin/admin_footer.php');
?>