<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$orbit = new Orbit;
$user = new User;

$user->perm(true, 'pages');

if(!empty($_GET['id'])){
	$page_id = $alkaline->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$page_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['page_id'])){
	$page_id = $alkaline->findID($_POST['page_id']);
	if(!empty($_POST['page_delete']) and ($_POST['page_delete'] == 'delete')){
		$alkaline->deleteRow('pages', $page_id);
	}
	else{
		$page_title = trim($_POST['page_title']);
		
		if(!empty($_POST['page_title_url'])){
			$page_title_url = $alkaline->makeURL($_POST['page_title_url']);
		}
		else{
			$page_title_url = $alkaline->makeURL($page_title);
		}
		
		$page_text_raw = $_POST['page_text_raw'];
		$page_text = $page_text_raw;
		
		$page_markup = @$_POST['page_markup'];
		$page_markup_ext = @$_POST['page_markup_ext'];
		
		if($page_markup == 'markup'){
			$page_text = $orbit->hook('markup_' . $page_markup_ext, $page_text_raw, $page_text);
		}
		else{
			$page_markup_ext = '';
			$page_text = $alkaline->nl2br($page_text_raw);
		}
		
		$page_images = implode(', ', $alkaline->findIDRef($page_text));
		
		$page_words = $alkaline->countWords($_POST['page_text_raw']);
		
		$fields = array('page_title' => $alkaline->makeUnicode($page_title),
			'page_title_url' => $page_title_url,
			'page_text_raw' => $alkaline->makeUnicode($page_text_raw),
			'page_markup' => $page_markup_ext,
			'page_images' => $page_images,
			'page_text' => $alkaline->makeUnicode($page_text),
			'page_category' => $alkaline->makeUnicode(@$_POST['page_category']),
			'page_words' => $page_words);
		
		$alkaline->updateRow($fields, 'pages', $page_id);
	}
	unset($page_id);
}
else{
	$alkaline->deleteEmptyRow('pages', array('page_title', 'page_text_raw'));
}

// CREATE PAGE
if(!empty($page_act) and ($page_act == 'add')){
	$page_id = $alkaline->addRow(null, 'pages');
}

define('TAB', 'features');

// GET PAGES TO VIEW OR PAGE TO EDIT
if(empty($page_id)){
	$pages = $alkaline->getTable('pages', null, null, null, 'page_modified DESC');
	$page_count = @count($pages);
	
	define('TITLE', 'Alkaline Pages');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'pages' . URL_ACT . 'add' . URL_RW; ?>"><button>Add page</button></a></div>
	
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/pages.png" alt="" /> Pages (<?php echo $page_count; ?>)</h1>
	
	<p>Pages are freeform areas for text-based content.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Title</th>
			<th class="center">Views</th>
			<th class="center">Words</th>
			<th>Created</th>
			<th>Last modified</th>
		</tr>
		<?php

		foreach($pages as $page){
			echo '<tr>';
				echo '<td><a href="' . BASE . ADMIN . 'pages' . URL_ID . $page['page_id'] . URL_RW . '"><strong>' . $page['page_title'] . '</strong></a><br /><a href="' . BASE . 'page' . URL_ID . $page['page_title_url'] . URL_RW . '" class="nu">' . $page['page_title_url'] . '</td>';
				echo '<td class="center">' . number_format($page['page_views']) . '</td>';
				echo '<td class="center">' . number_format($page['page_words']) . '</td>';
				echo '<td>' . $alkaline->formatTime($page['page_created']) . '</td>';
				echo '<td>' . $alkaline->formatRelTime($page['page_modified']) . '</td>';
			echo '</tr>';
		}

		?>
	</table>
	
	<?php
	require_once(PATH . ADMIN . 'includes/footer.php');
}
else{
	$page = $alkaline->getRow('pages', $page_id);
	$page = $alkaline->makeHTMLSafe($page);
	
	if(!empty($page['page_title'])){	
		define('TITLE', 'Alkaline Page: &#8220;' . $page['page_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Page');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'pages' . URL_AID .  $page['page_id'] . URL_RW; ?>"><button>View images</button></a> <a href="<?php echo BASE; ?>page<?php echo URL_ID . $page['page_id'] . '-' . @$page['page_title_url'] . URL_RW; ?>"><button>Launch page</button></a></div>
	
	<?php
	
	if(empty($page['page_title'])){
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/pages.png" alt="" /> New Page</h1>';
	}
	else{
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/pages.png" alt="" /> Page: ' . $page['page_title'] . '</h1>';
	}
	
	?>

	<form id="page" action="<?php echo BASE . ADMIN . 'pages' . URL_CAP; ?>" method="post">
		<div class="span-24 last">
			<div class="span-15 append-1">
				<input type="text" id="page_title" name="page_title" placeholder="Title" <?php if(empty($post['post_title'])){ echo 'autofocus="autofocus"'; }; ?> value="<?php echo @$page['page_title']; ?>" class="title notempty" />
				<textarea id="page_text_raw" name="page_text_raw" placeholder="Text" style="height: 500px;"  class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"><?php echo @$page['page_text_raw']; ?></textarea>
			</div>
			<div class="span-8 last">
				<p>
					<label for="page_title_url">Custom URL:</label><br />
					<input type="text" id="page_title_url" name="page_title_url" value="<?php echo @$page['page_title_url']; ?>" style="width: 300px;" /><br />
					<span class="quiet"><?php echo 'page' . URL_ID; ?><span id="page_title_url_link"></span></span>
				</p>
				
				<p>
					<label for="page_category">Category:</label><br />
					<input type="text" id="page_category" name="page_category" class="page_category" value="<?php echo @$page['page_category']; ?>" />
				</p>
				
				<hr />
				
				<table>
					<tr>
						<td><input type="checkbox" id="page_markup" name="page_markup" value="markup" <?php if(!empty($page['page_markup'])){ echo 'checked="checked"'; } ?> /></td>
						<td><label for="page_markup">Markup this page using <select name="page_markup_ext" title="<?php echo @$page['page_markup']; ?>"><?php $orbit->hook('markup_html'); ?></select>.</label></td>
					</tr>
					<tr>
						<td><input type="checkbox" id="page_delete" name="page_delete" value="delete" /></td>
						<td>
							<label for="page_delete">Delete this page.</label><br />
							This action cannot be undone.
						</td>
					</tr>
				</table>
			</div>
		</div>
		<p>
			<span class="switch">&#9656;</span> <a href="#" class="show">Show recent images</a> <span class="quiet">(click to add at cursor position)</span>
		</p>
		<div class="reveal image_click">
			<?php
			
			$image_ids = new Find('images');
			$image_ids->sort('image_uploaded', 'DESC');
			$image_ids->page(1, 100);
			$image_ids->find();
			
			$images = new Image($image_ids);
			$images->getSizes('square');
			
			if($alkaline->returnConf('page_size_label')){
				$label = 'image_src_' . $alkaline->returnConf('page_size_label');
			}
			else{
				$label = 'image_src_admin';
			}
			
			if($alkaline->returnConf('page_div_wrap')){
				echo '<div class="none wrap_class">' . $alkaline->returnConf('page_div_wrap_class') . '</div>';
			}
			
			foreach($images->images as $image){
				$image['image_title'] = $alkaline->makeHTMLSafe($image['image_title']);
				echo '<a href="' . $image[$label] . '"><img src="' . $image['image_src_square'] .'" alt="' . $image['image_title']  . '" class="frame" id="image-' . $image['image_id'] . '" /></a>';
				echo '<div class="none uri_rel image-' . $image['image_id'] . '">' . $image['image_uri_rel'] . '</div>';
			}
		
			?>
		</div>
		
		<p><input type="hidden" name="page_id" value="<?php echo $page['page_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></p>
	</form>

	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');
}

?>