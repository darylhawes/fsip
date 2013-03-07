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

$user->perm(true, 'pages');

if(!empty($_GET['id'])){
	$page_id = $fsip->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$page_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['page_id'])){
	$page_id = $fsip->findID($_POST['page_id']);
	
	$page = new Page($page_id);
	
	if(!empty($_POST['page_delete']) and ($_POST['page_delete'] == 'delete')){
		if($page->delete()){
			$fsip->addNote('The page has been deleted.', 'success');
		}
	}
	elseif(!empty($_POST['page_recover']) and ($_POST['page_recover'] == 'recover')){
		if($page->recover()){
			$fsip->addNote('The page has been recovered.', 'success');
		}
	}
	else{
		$page->attachUser($user);
		
		$page_title = trim($_POST['page_title']);
		
		if(!empty($_POST['page_title_url'])){
			$page_title_url = $fsip->makeURL($_POST['page_title_url']);
		}
		else{
			$page_title_url = $fsip->makeURL($page_title);
		}
		
		$page_text_raw = $_POST['page_text_raw'];
		$page_text = $page_text_raw;
		
		$page_excerpt_raw = $_POST['page_excerpt_raw'];
		$page_excerpt = $page_excerpt_raw;
		
		$page_markup = @$_POST['page_markup'];
		$page_markup_ext = @$_POST['page_markup_ext'];
		
		// Configuration: page_markup
		if(!empty($_page['page_markup'])){
			$page_markup_ext = $_page['page_markup'];
			$page_text = $orbit->hook('markup_' . $page_markup_ext, $page_text_raw, $page_text_raw);
			$page_title = $orbit->hook('markup_title_' . $page_markup_ext, $page_title, $page_title);
			$page_excerpt = $orbit->hook('markup_' . $page_markup_ext, $page_excerpt_raw, $page_excerpt);
		}
		elseif($fsip->returnConf('web_markup')){
			$page_markup_ext = $fsip->returnConf('web_markup_ext');
			$page_text = $orbit->hook('markup_' . $page_markup_ext, $page_text_raw, $page_text_raw);
			$page_title = $orbit->hook('markup_title_' . $page_markup_ext, $page_title, $page_title);
			$page_excerpt = $orbit->hook('markup_' . $page_markup_ext, $page_excerpt_raw, $page_excerpt);
		}
		else{
			$page_markup_ext = '';
			$page_text = $fsip->nl2br($page_text_raw);
			$page_excerpt = $fsip->nl2br($page_excerpt_raw);
		}
		
		$page_images = implode(', ', $fsip->findIDRef($page_text));
		
		$page_words = $fsip->countWords($_POST['page_text_raw']);
		
		$fields = array('page_title' => $fsip->makeUnicode($page_title),
			'page_title_url' => $page_title_url,
			'page_text' => $fsip->makeUnicode($page_text),
			'page_text_raw' => $fsip->makeUnicode($page_text_raw),
			'page_excerpt' => $fsip->makeUnicode($page_excerpt),
			'page_excerpt_raw' => $fsip->makeUnicode($page_excerpt_raw),
			'page_markup' => $page_markup_ext,
			'page_images' => $page_images,
			'page_category' => $fsip->makeUnicode(@$_POST['page_category']),
			'page_words' => $page_words);
		
		$page->updateFields($fields);
	}
	unset($page_id);
}
else{
	$fsip->deleteEmptyRow('pages', array('page_title', 'page_text_raw'));
}

// CREATE PAGE
if(!empty($page_act) and ($page_act == 'add')){
	$page_id = $fsip->addRow(null, 'pages');
}

define('TAB', 'features');

// GET PAGES TO VIEW OR PAGE TO EDIT
if(empty($page_id)){
	$page_ids = new Find('pages');
	$page_ids->sort('page_modified', 'DESC');
	$page_ids->find();
	
	$pages = new Page($page_ids);
	$pages->hook();
	
	define('TITLE', 'Pages');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'pages' . URL_ACT . 'add' . URL_RW; ?>"><button>Add page</button></a></div>
	
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/pages.png" alt="" /> Pages (<?php echo $pages->page_count; ?>)</h1>
	
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

		foreach($pages->pages as $page){
			echo '<tr class="ro">';
				echo '<td><strong class="large"><a href="' . BASE . ADMIN . 'pages' . URL_ID . $page['page_id'] . URL_RW . '" class="tip" title="' . htmlentities($fsip->fitStringByWord(strip_tags($page['page_text']), 150)) . '">' . $page['page_title'] . '</a></strong><br /><a href="' . BASE . 'page' . URL_ID . $page['page_title_url'] . URL_RW . '" class="nu quiet">' . $page['page_title_url'] . '</td>';
				echo '<td class="center">' . number_format($page['page_views']) . '</td>';
				echo '<td class="center">' . number_format($page['page_words']) . '</td>';
				echo '<td>' . $fsip->formatTime($page['page_created']) . '</td>';
				echo '<td>' . ucfirst($fsip->formatRelTime($page['page_modified'])) . '</td>';
			echo '</tr>';
		}

		?>
	</table>
	
	<?php
	require_once(PATH . ADMIN . 'includes/footer.php');
}
else{
	$pages = new Page($page_id);
	$pages->getCitations();
	$pages->getVersions();
	$page = $pages->pages[0];
	$page = $fsip->makeHTMLSafe($page);
	
	if(!empty($page['page_title'])){	
		define('TITLE', 'Page: &#8220;' . $page['page_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Page');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions">
		<button id="preview">Preview page</button>
		<a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'pages' . URL_AID .  $page['page_id'] . URL_RW; ?>"><button>View images</button></a>
		<a href="<?php echo BASE; ?>page<?php echo URL_ID . $page['page_id'] . '-' . @$page['page_title_url'] . URL_RW; ?>"><button>Launch page</button></a>
	</div>
	
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
				<input type="text" id="page_title" name="page_title" placeholder="Title" <?php if(empty($post['page_title'])){ echo 'autofocus="autofocus"'; }; ?> value="<?php echo @$page['page_title']; ?>" class="title notempty" />
				<textarea id="page_text_raw" name="page_text_raw" placeholder="Text" style="height: 500px;" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"><?php echo @$page['page_text_raw']; ?></textarea>
				<p class="info_bar">
					
				</p>
				<p class="slim">
					<span class="switch">&#9656;</span> <a href="#" class="show">Show page&#8217;s excerpt</a>
				</p>
				<div class="reveal">
					<textarea id="page_excerpt_raw" name="page_excerpt_raw" style="height: 150px;" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"><?php echo @$page['page_excerpt_raw']; ?></textarea>
				</div>
			</div>
			<div class="span-8 last">
				<p>
					<label for="page_category">Category:</label><br />
					<input type="text" id="page_category" name="page_category" class="page_category" value="<?php echo @$page['page_category']; ?>" />
				</p>
				
				<p>
					<label for="page_title_url">Custom URL:</label><br />
					<input type="text" id="page_title_url" name="page_title_url" class="l" value="<?php echo @$page['page_title_url']; ?>" /><br />
					<span class="quiet"><?php echo 'page' . URL_ID; ?><span id="page_title_url_link"></span></span>
				</p>
				
					<p>
						<span class="switch">&#9656;</span> <a href="#" class="show">Show citations</a> <span class="quiet">(<span id="citation_count"><?php echo count($pages->citations); ?></span>)</span>
					</p>

					<div class="reveal">
						<table id="citations">
							<?php

							foreach($pages->citations as $citation){
								echo '<tr><td style="width:16px;">';
								if(!empty($citation['citation_favicon_uri'])){
									echo '<img src="' . $citation['citation_favicon_uri'] . '" height="16" width="16" alt="" />';
								}
								echo '</td><td>';
								echo '<a href="';
								if(!empty($citation['citation_uri'])){
									echo $citation['citation_uri'];
								}
								else{
									echo $citation['citation_uri_requested'];
								}
								echo '" title="';
								if(!empty($citation['citation_description'])){
									echo $citation['citation_description'];
								}
								echo '" class="tip" target="_new">&#8220;' . $citation['citation_title'] . '&#8221;</a>';
								if(!empty($citation['citation_site_name'])){
									echo ' <span class="quiet">(' . $citation['citation_site_name'] . ')</span>';
								}
								else{
									echo ' <span class="quiet">(' . $fsip->siftDomain($citation['citation_uri_requested']) . ')</span>';
								}
								echo '</td></tr>';
							}

							?>
						</table>
					</div>
				
				<hr />
				
				<table>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="page_markup" name="page_markup" value="markup" <?php if(!empty($page['page_markup'])){ echo 'checked="checked"'; } ?> /></td>
						<td><label for="page_markup">Markup this page using <select name="page_markup_ext" title="<?php echo @$page['page_markup']; ?>"><?php $orbit->hook('markup_html'); ?></select>.</label></td>
					</tr>
					<?php if(empty($page['page_deleted'])){ ?>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="page_delete" name="page_delete" value="delete" /></td>
						<td>
							<label for="page_delete">Delete this page.</label>
						</td>
					</tr>
					<?php } else{ ?>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="page_recover" name="page_recover" value="recover" /></td>
						<td>
							<strong><label for="page_recover">Recover this page.</label></strong>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
		
		<?php if(count($pages->versions) > 0){ ?>
		<p class="slim">
			<span class="switch">&#9656;</span> <a href="#" class="show">Compare to previous version</a>
		</p>
		<div class="reveal">
			<p>
				<label for="version_id">Show differences from:</label>
				<select id="version_id">
				<?php
				
				$i = 0;
				
				foreach($pages->versions as $version){
					$i++;
					$similarity = $version['version_similarity'];
					
					if($similarity > 95){ $similarity = 'minor change'; }
					elseif($similarity > 65){ $similarity = 'moderate change'; }
					else{ $similarity = 'major change'; }
					
					echo '<option value="' . $version['version_id'] . '"';
					if($i == 2){ echo ' selected="selected"'; }
					echo '>' . ucfirst($fsip->formatRelTime($version['version_created'])) . ' (#' . $version['version_id'] . ', ' . $similarity . ')</option>';
				}
				
				?>
				</select>
				<button id="compare">Compare</button>
			</p>
			<p id="comparison">
				
			</p>
		</div>
		<?php } ?>
		
		<p>
			<span class="switch">&#9656;</span> <a href="#" class="show">Display recent images</a> <span class="quiet">(click to add at cursor position)</span>
		</p>
		<div id="recent_images" class="reveal image_click">
			<div class="search_bar">
				<input type="search" class="recent_image_search" name="q" placeholder="Search" results="10" />
				<input type="submit" value="Load" />
			</div>
			<div class="load">
				<?php
	
				$image_ids = new Find('images');
				$image_ids->sort('image_uploaded', 'DESC');
				$image_ids->post(1, 100);
				$image_ids->find();
	
				$images = new Image($image_ids);
				$images->getSizes();
	
				if($fsip->returnConf('post_size_label')){
					$label = 'image_src_' . $fsip->returnConf('post_size_label');
				}
				else{
					$label = 'image_src_admin';
				}
	
				if($fsip->returnConf('post_div_wrap')){
					echo '<div class="none wrap_class">' . $fsip->returnConf('post_div_wrap_class') . '</div>';
				}
	
				foreach($images->images as $image){
					$image['image_title'] = $fsip->makeHTMLSafe($image['image_title']);
					echo '<a href="' . $image[$label] . '"><img src="' . $image['image_src_square'] .'" alt="' . $image['image_title']  . '" class="frame" id="image-' . $image['image_id'] . '" /></a>';
					echo '<div class="none uri_rel image-' . $image['image_id'] . '">' . $image['image_uri_rel'] . '</div>';
				}

				?>
			</div><br />
		</div>
		
		<input type="hidden" id="page_id" name="page_id" value="<?php echo $page['page_id']; ?>" />
		<input type="hidden" id="page_citations" name="page_citations" value="<?php foreach($pages->citations as $citation){ echo $citation['citation_uri_requested']; } ?>" />
		
		<p>
			<input type="submit" value="Save changes" /> or <a href="<?php echo $fsip->back(); ?>">cancel</a>
		</p>
	</form>

	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');
}

?>