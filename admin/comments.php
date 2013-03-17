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

$user->perm(true, 'editor');

if (!empty($_GET['id'])) {
	$comment_id = $fsip->findID($_GET['id']);
}

if (!empty($_GET['act'])) {
	$comment_act = $_GET['act'];
	if ($comment_act == 'search') {
		Find::clearMemory();
		
		$comment_ids = new Find('comments');
		$comment_ids->find();
		$comment_ids->saveMemory();
		
		$location = LOCATION . BASE. ADMINFOLDER . 'comments' . URL_ACT . 'results' . URL_RW;
		$fsip::headerLocationRedirect($location);
		exit();
	}
}

// SAVE CHANGES
if (!empty($_POST['comment_id'])) {
	$comment_id = $fsip->findID($_POST['comment_id']);
	
	$comment = new Comment($comment_id);
	
	if (!empty($_POST['raw_response'])) {
		$comment_text_raw = $_POST['raw_response'];
		
		if (!empty($_POST['image_id'])) {
			$id = $fsip->findID($_POST['image_id']);
			$id_type = 'image_id';
		}
		
		// Configuration: comm_markup
		if ($fsip->returnConf('web_markup')) {
			$comm_markup_ext = $fsip->returnConf('web_markup_ext');
			$comment_text = $orbit->hook('markup_' . $comm_markup_ext, $comment_text_raw, $comment_text_raw);
		} else {
			$comm_markup_ext = '';
			$comment_text = $fsip->nl2br($comment_text_raw);
		}
		
		$fields = array($id_type => $id,
			'comment_response' => $comment_id,
			'comment_status' => 1,
			'comment_text' => $fsip->makeUnicode($comment_text),
			'comment_text_raw' => $fsip->makeUnicode($comment_text_raw),
			'comment_markup' => $comm_markup_ext,
			'user_id' => $user->user['user_id'],
			'comment_author_name' => $user->user['user_name'],
			'comment_author_uri' => $user->user['user_uri'],
			'comment_author_email' => $user->user['user_email'],
			'comment_author_ip' => $_SERVER['REMOTE_ADDR']);
		
		$fields = $orbit->hook('comment_add', $fields, $fields);
		
		if (!$comment_id = $fsip->addRow($fields, 'comments')) {
			$fsip->addNote('The response could not be added.', 'error');
		} else {
			// Update comment counts
			if ($id_type == 'image_id') {
				$fsip->updateCount('comments', 'images', 'image_comment_count', $id);
			}
			
			$fsip->addNote('The response was successfully added.', 'success');
		}
	}
	
	if (isset($_POST['comment_delete']) and ($_POST['comment_delete'] == 'delete')) {
		if ($comment->delete()) {
			$fsip->addNote('The comment has been deleted.', 'success');
		}
		
		// Update comment counts
		if (!empty($_POST['image_id'])) {
			$id = $fsip->findID($_POST['image_id']);
			$id_type = 'image_id';
		}
		
		if ($id_type == 'image_id') {
			$fsip->updateCount('comments', 'images', 'image_comment_count', $id);
		}
	} elseif (isset($_POST['comment_recover']) and ($_POST['comment_recover'] == 'recover')) {
		if ($comment->recover()){
			$fsip->addNote('The comment has been recovered.', 'success');
		}
		
		// Update comment counts
		if (!empty($_POST['image_id'])) {
			$id = $fsip->findID($_POST['image_id']);
			$id_type = 'image_id';
		}
		
		if ($id_type == 'image_id') {
			$fsip->updateCount('comments', 'images', 'image_comment_count', $id);
		}
	} elseif (!empty($_POST['comment_quick'])) {
		if ($_POST['comment_quick'] == 'go_image') {
			$location = LOCATION . BASE. ADMINFOLDER . 'image' . URL_ID . $comment->comments[0]['image_id'] . URL_RW;
			$fsip::headerLocationRedirect($location);
			exit();
		} elseif($_POST['comment_quick'] == 'publish') {
			$fields = array('comment_status' => 1);
			$comment->updateFields($fields);
		} elseif($_POST['comment_quick'] == 'unpublish') {
			$fields = array('comment_status' => 0);
			$comment->updateFields($fields);
		} elseif($_POST['comment_quick'] == 'spam') {
			$fields = array('comment_status' => -1);
			$comment->updateFields($fields);
		} elseif($_POST['comment_quick'] == 'delete') {
			if($comment->delete()){
				$fsip->addNote('The comment has been deleted.', 'success');
			}

			// Update comment counts
			if (!empty($_POST['image_id'])) {
				$id = $fsip->findID($_POST['image_id']);
				$id_type = 'image_id';
			}

			if ($id_type == 'image_id') {
				$fsip->updateCount('comments', 'images', 'image_comment_count', $id);
			}
		}
	} else {
		$comment_text_raw = $_POST['comment_text_raw'];
		$comment_text = $comment_text_raw;
		
		// Configuration: comm_markup
		if (!empty($_POST['comm_markup'])) {
			$comment_markup_ext = $_POST['comm_markup'];
			$comment_text = $orbit->hook('markup_' . $comment_markup_ext, $comment_text_raw, $comment_text);
		} elseif($fsip->returnConf('comm_markup')) {
			$comment_markup_ext = $fsip->returnConf('comm_markup_ext');
			$comment_text = $orbit->hook('markup_' . $comment_markup_ext, $comment_text_raw, $comment_text);
		} else {
			$comment_markup_ext = '';
			$comment_text = $fsip->nl2br($comment_text_raw);
		}
		
		
		if (@$_POST['comment_spam'] == 'spam') {
			$comment_status = -1;
		} else {
			$comment_status = 1;
		}
		
		$fields = array('comment_text_raw' => $fsip->makeUnicode($comment_text_raw),
			'comment_text' => $fsip->makeUnicode($comment_text),
			'comment_status' => $comment_status);
		
		$comment->updateFields($fields);
	}
	
	if (!empty($_REQUEST['go'])) {
		$comment_ids = new Find('comments');
		$comment_ids->memory();
		$comment_ids->with($comment_id);
		$comment_ids->offset(1);
		$comment_ids->page(null, 1);
		$comment_ids->find();
		
		if ($_REQUEST['go'] == 'next') {
			$_SESSION['fsip']['go'] = 'next';
			if (!empty($comment_ids->ids_after[0])) {
				$comment_id = $comment_ids->ids_after[0];
			} else {
				unset($_SESSION['fsip']['go']);
				unset($comment_id);
			}
		} else {
			$_SESSION['fsip']['go'] = 'previous';
			if (!empty($comment_ids->ids_before[0])) {
	 			$comment_id = $comment_ids->ids_before[0];
			} else {
				unset($_SESSION['fsip']['go']);
				unset($comment_id);
			}
		}
	} else {
		unset($_SESSION['fsip']['go']);
		unset($comment_id);
	}
}

define('TAB', 'comments');

// GET COMMENTS TO VIEW OR PILE TO EDIT
if (empty($comment_id)) {
	$comment_ids = new Find('comments');
	$comment_ids->page(null, 50);
	if (isset($comment_act) and ($comment_act == 'results')) { 
		$comment_ids->memory(); 
	}
	$comment_ids->find();
	
	$comments = new Comment($comment_ids);
	$comments->formatTime();
	$comments->comments = $fsip->stripTags($comments->comments);
	
	$image_ids = $comments->image_ids;
	
	$images = new Image($image_ids);
	$images->getSizes('square');
	
	define('TITLE', 'FSIP Comments');
	require_once(PATH . INCLUDES . '/admin_header.php');

	?>
	
	<h1><img src="<?php echo BASE . IMGFOLDER; ?>icons/comments.png" alt="" /> Comments (<?php echo $comments->comment_count; ?>)</h1>
	
	<form action="<?php echo BASE . ADMINFOLDER . 'comments' . URL_ACT . 'search' . URL_RW; ?>" method="post">
		<p style="margin-bottom: 0;">
			<input type="search" name="q" style="width: 30em; margin-left: 0;" results="10" /> <input type="submit" value="Search" />
		</p>

		<p>
			<span class="switch">&#9656;</span> <a href="#" class="show" style="line-height: 2.5em;">Show options and presets</a>
		</p>
		
		<div class="reveal span-24 last">
			<div class="span-15 append-1">
				<table>
					<tr>
						<td class="right middle"><label for="status">Status:</label></td>
						<td class="quiet">
							<select id="status" name="status">
								<option value="">All</option>
								<option value="1">Live</option>
								<option value="0">Pending</option>
								<option value="-1">Spam</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right middle"><label for="response">Response:</label></td>
						<td class="quiet">
							<select id="response" name="response">
								<option value="">All</option>
								<option value="true">Yes</option>
								<option value="false">No</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right middle"><label>Date created:</label></td>
						<td class="quiet">
							between <input type="text" class="date" name="created_begin" style="width: 10em;" />
							and <input type="text" class="date" name="created_end" style="width: 10em;" />
						</td>
					</tr>
				</table>
			</div>
			<div class="span-8 last">
				<h3>Presets</h3>
				
				<ul>
					<li><a href="<?php echo BASE . ADMINFOLDER . 'comments' . URL_ACT . 'live' . URL_RW; ?>">Live comments</a></li>
					<li><a href="<?php echo BASE . ADMINFOLDER . 'comments' . URL_ACT . 'pending' . URL_RW; ?>">Pending comments</a></li>
					<li><a href="<?php echo BASE . ADMINFOLDER . 'comments' . URL_ACT . 'spam' . URL_RW; ?>">Spam comments</a></li>
				</ul>
			</div>
		</div>
	</form>
	
<?php
	// Configuration: comm_enabled
	if (!$fsip->returnConf('comm_enabled')) {
?>
		<p class="notice">New comments have been disabled. You can enabled comments in your <a href="<?php echo BASE . ADMINFOLDER . 'configuration' . URL_CAP; ?>">configuration</a>.</p><br />
<?php
	}
?>
			
	<table>
		<tr>
			<th style="width:25px"></th>
			<th>Comment</th>
			<th></th>
			<th>Created</th>
		</tr>
<?php
	
		foreach($comments->comments as $comment) {
			echo '<tr class="ro">';
			echo '<td>';
			$key = array_search($comment['image_id'], $image_ids);
			if (is_int($key)) {
				echo '<img src="' . $images->images[$key]['image_src_square'] . '" title="' . $images->images[$key]['image_title'] . '" class="frame_mini" />';
			}
			echo '</td>';
			echo '<td class="status' . $comment['comment_status'] . '">';
			echo '<div class="actions"><button class="tip" title=\'<form action="" method="post"><select name="comment_quick">';
			if ($comment['comment_status'] == 0) {
				echo '<option value="publish">Publish</option>';
				echo '<option value="spam">Mark as spam</option>';
				echo '<option value="delete">Delete</option>';
			} elseif ($comment['comment_status'] == 1) {
				echo '<option value="unpublish">Unpublish</option>';
				echo '<option value="delete">Delete</option>';
			} elseif ($comment['comment_status'] == -1) {
				echo '<option value="publish">Publish</option>';
				echo '<option value="delete">Delete</option>';
			}
			if (is_int($key)) {
				echo '<option value="go_image">Go to image</option>';
			}
			echo '</select> <input type="hidden" name="comment_id" value="' . $comment['comment_id'] . '" /><input type="submit" value="Do" /></form>\'></button></div>';
			echo '<strong><a href="' . BASE . ADMINFOLDER . 'comments' . URL_ID . $comment['comment_id'] . URL_RW . '" class="large tip" title="' . $fsip->makeHTMLSafe($fsip->fitStringByWord(strip_tags($comment['comment_text']), 150)) . '">';
			echo $fsip->fitStringByWord(strip_tags($comment['comment_text']), 50);
			echo '</a></strong><br /><span class="quiet">';
			
			if (!empty($comment['user_id'])) {
				echo '<img src="' . BASE . IMGFOLDER . 'icons/user.png" alt="" /> <a href="' . BASE . ADMINFOLDER . 'comments' . URL_ACT . 'user' . URL_AID . $comment['user_id'] . URL_RW . '" class="nu">' . $comment['comment_author_name'] . '</a>';
			} elseif(!empty($comment['comment_author_name'])) {
				echo '<a href="' . BASE . ADMINFOLDER . 'comments' . URL_CAP . '?q=' . urlencode($comment['comment_author_name']) . '" class="nu">' . $comment['comment_author_name'] . '</a>';
			} else {
				'<em>Anonymous</em>';
			}
		
			if (!empty($comment['comment_author_ip']) and empty($comment['user_id'])) {
				echo ' (<a href="' . BASE . ADMINFOLDER . 'comments' . URL_CAP . '?q=' . urlencode($comment['comment_author_ip']) . '" class="nu">' . $comment['comment_author_ip'] . '</a>)';
			}
			echo '</span></td><td></td><td>' . $comment['comment_created_format'] . '</td></tr>';
		
		}
	
?>
	</table>
	
<?php
	
	if ($comment_ids->page_count > 1) {
?>
		<p>
<?php
			if (!empty($comment_ids->page_previous)) {
				for($i = 1; $i <= $comment_ids->page_previous; ++$i) {
					$page_uri = 'page_' . $i . '_uri';
					echo '<a href="' . $comment_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
				}
			}
?>
			<span class="page_no">Page <?php echo $comment_ids->page; ?> of <?php echo $comment_ids->page_count; ?></span>
<?php
			if (!empty($comment_ids->page_next)) {
				for($i = $comment_ids->page_next; $i <= $comment_ids->page_count; ++$i) {
					$page_uri = 'page_' . $i . '_uri';
					echo '<a href="' . $comment_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
				}
			}
?>
		</p>
<?php
	}
	
	require_once(PATH . INCLUDES . '/admin_footer.php');
	
} else {
	$comment = $fsip->getRow('comments', $comment_id);
	$comment = $fsip->makeHTMLSafe($comment);
	
	define('TITLE', 'FSIP Comment');
	require_once(PATH . INCLUDES . '/admin_header.php');
	
	$email_action = '';
	
	if (!empty($comment['comment_author_email'])) {
		$email_action = '<a href="mailto:' . $comment['comment_author_email'] . '"><button>Email author</button></a>';
	}
	
?>
	
	<?php if ($comment['image_id'] != 0) { ?>
		<div class="actions">
			<?php echo $email_action; ?>
			<a href="<?php echo BASE . ADMINFOLDER . 'images' . URL_ID . $comment['image_id'] . URL_RW; ?>"><button>Go to image</button></a>
			<a href="<?php echo BASE . 'image' . URL_ID . $comment['image_id'] . URL_RW; ?>"><button>Launch image</button></a>
		</div>
	<?php } ?>
	
	<h1><img src="<?php echo BASE . IMGFOLDER; ?>icons/comments.png" alt="" /> Comment</h1>

	<form action="<?php echo BASE . ADMINFOLDER . 'comments' . URL_CAP; ?>" method="post">
		<div class="span-24 last">
			<div class="span-15 append-1">
				<textarea id="comment_text_raw" name="comment_text_raw" placeholder="Text" style="height: 300px;" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"><?php echo @$comment['comment_text_raw']; ?></textarea>
				
				<p>
					<span class="switch">&#9656;</span> <a href="#" class="show">Leave response</a> <span class="quiet">(response will become a new comment)</span>
				</p>
				<div class="reveal">
					<textarea id="raw_response" name="raw_response" style="height: 150px;" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"></textarea>
				</div>
			</div>
			<div class="span-8 last">
				<p>
					<label for="comment_text">Author:</label><br />
					<span class="quiet">
<?php

					if (!empty($comment['comment_author_name'])) {
						if ($comment['user_id']) {
							echo '<img src="' . BASE . IMGFOLDER . 'icons/user.png" alt="" /> <a href="' . BASE . ADMINFOLDER . 'comments' . URL_ACT . 'user' . URL_AID . $comment['user_id'] . URL_RW . '" class="nu"> ';
						}
						echo '<a href="">' . $comment['comment_author_name'] . '</a>';
					} else {
						echo '<em>Anonymous</em>';
					}

?>
					</span>
				</p>
<?php
				if (!empty($comment['comment_author_email'])) {
?>
					<p>
						<label>Email:</label><br />
						<span class="quiet">
							<a href="mailto:<?php echo $comment['comment_author_email']; ?>"><?php echo $comment['comment_author_email']; ?></a>
						</span>
					</p>
<?php
				}
				?>
<?php		
				if (!empty($comment['comment_author_uri'])) {
?>
					<p>
						<label>Web site:</label><br />
						<span class="quiet">
							<a href="<?php echo $comment['comment_author_uri']; ?>"><?php echo $fsip->fitString($fsip->minimizeURL($comment['comment_author_uri']), 100); ?></a>
						</span>
					</p>
<?php
				}

?>
				<p>
					<label for="comment_ip_address">IP address:</label><br />
					<span class="quiet"><?php echo $comment['comment_author_ip']; ?></span>
				</p>
				
				<hr />
				
				<table>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="comment_spam" name="comment_spam" value="spam" <?php if($comment['comment_status'] == -1){ echo 'checked="checked"'; } ?>/></td>
						<td><strong><label for="comment_spam">Mark this comment as spam.</label></strong></td>
					</tr>
					<?php if(empty($comment['comment_deleted'])){ ?>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="comment_delete" name="comment_delete" value="delete" /></td>
						<td>
							<strong><label for="comment_delete">Delete this comment.</label></strong>
						</td>
					</tr>
					<?php } else { ?>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="comment_recover" name="comment_recover" value="recover" /></td>
						<td>
							<strong><label for="comment_recover">Recover this comment.</label></strong>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
		<p>
			<input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>" />
			<input type="hidden" name="image_id" value="<?php echo $comment['image_id']; ?>" />
			<input type="hidden" id="comm_markup" name="comm_markup" value="<?php echo $comment['comment_markup']; ?>" />
			<input type="submit" value="<?php echo (($comment['comment_status'] == 0) ? 'Publish' : 'Save changes'); ?>" />
			and
			<select name="go">
				<option value="">return to previous screen</option>
				<option value="next" <?php echo $fsip->readForm($_SESSION['fsip'], 'go', 'next'); ?>>go to next comment</option>
				<option value="previous" <?php echo $fsip->readForm($_SESSION['fsip'], 'go', 'previous'); ?>>go to previous comment</option>
			</select>
			or <a href="<?php echo $fsip->back(); ?>">cancel</a>
		</p>
	</form>

<?php
	
	require_once(PATH . INCLUDES . '/admin_footer.php');
	
}

?>