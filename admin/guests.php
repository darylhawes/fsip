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

$user->perm(true, 'guests');

if(!empty($_GET['id'])){
	$guest_id = $fsip->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$guest_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['guest_id'])){
	$guest_id = $fsip->findID($_POST['guest_id']);
	if($_POST['guest_delete'] == 'delete'){
		$fsip->deleteRow('guests', $guest_id);
	}
	else{
		$guest_sets = @$_POST['guest_sets'];
		
		if($guest_sets == 'all'){
			$guest_sets = '';
		}
		else{
			$guest_sets = @$_POST['guest_sets_select'];
		}
		
		if(@$_POST['guest_inclusive'] == 'inclusive'){
			$guest_inclusive = 1;
		}
		else{
			$guest_inclusive = 0;
		}
		
		$fields = array('guest_title' => $fsip->makeUnicode(@$_POST['guest_title']),
			'guest_key' => @$_POST['guest_key'],
			'guest_sets' => $guest_sets,
			'guest_inclusive' => $guest_inclusive);
		if(@$_POST['guest_reset_view_count'] == 'reset_view_count'){
			$fields['guest_views'] = 0;
		}
		
		$fsip->updateRow($fields, 'guests', $guest_id);
	}
	unset($guest_id);
}
else{
	$fsip->deleteEmptyRow('guests', array('guest_title', 'guest_key'));
}

// CREATE GUEST
if(!empty($guest_act) and ($guest_act == 'add')){
	$guest_id = $fsip->addRow(null, 'guests');
}

define('TAB', 'settings');

// GET GUEST TO VIEW OR GUEST TO EDIT
if(empty($guest_id)){
	$guests = $fsip->getTable('guests');
	$guest_count = @count($guests);
	
	define('TITLE', 'Guests');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'guests' . URL_ACT . 'add' . URL_RW; ?>"><button>Add guest</button></a></div>

	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/guests.png" alt="" /> Guests (<?php echo $guest_count; ?>)</h1>
	
	<p>Guests can use an access key to view some or all protected images in your library.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Title</th>
			<th class="center">Views</th>
			<th>Last login</th>
		</tr>
		<?php
	
		foreach($guests as $guest){
			echo '<tr class="ro">';
				echo '<td><strong class="large"><a href="' . BASE . ADMIN . 'guests' . URL_ID . $guest['guest_id'] . URL_RW . '">' . $guest['guest_title'] . '</a></strong></td>';
				echo '<td class="center">' . number_format($guest['guest_views']) . '</td>';
				echo '<td>' . $fsip->formatTime($guest['guest_last_login'], null, '<em>Never</em>') . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>
	
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	// Get guest
	$guest = $fsip->getRow('guests', $guest_id);
	$guest = $fsip->makeHTMLSafe($guest);
	
	// Save credentials
	$_SESSION['fsip']['guest'] = $guest;
	
	if(!empty($guest['guest_title'])){	
		define('TITLE', 'Guest: ' . $guest['guest_title']);
	}
	else{
		define('TITLE', 'Guest');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions">
		<a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'guests' . URL_AID . $guest['guest_id'] . URL_RW; ?>"><button>View images</button></a>
		<a href="<?php echo BASE . 'access' . URL_ID .  $guest['guest_key'] . URL_RW; ?>"><button>Simulate guest</button></a>
	</div>
	
	<?php
	
	if(empty($guest['guest_title'])){
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/guests.png" alt="" /> New Guest</h1>';
	}
	else{
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/guests.png" alt="" /> Guest: ' . $guest['guest_title'] . '</h1>';
	}
	
	?>
	
	<form id="guest" action="<?php echo BASE . ADMIN . 'guests' . URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="guest_title">Title:</label></td>
				<td><input type="text" id="guest_title" name="guest_title" value="<?php echo $guest['guest_title']; ?>" class="m notempty" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="guest_key">Key:</label></td>
				<td>
					<input type="text" id="guest_key" name="guest_key" value="<?php echo $guest['guest_key']; ?>" class="s notempty" /><br />
					<span class="quiet"><?php echo LOCATION . BASE . 'access' . URL_ID; ?><span id="guest_key_link"></span></span>
				</td>
			</tr>
			<tr>
				<td class="right"><label for="guest_sets">Privileges:</label></td>
				<td>
					<table>
						<tr>
							<td class="right" style="width: 5%;"><input type="radio" name="guest_sets" value="all" id="guest_sets_all" <?php if(empty($guest['guest_sets'])){ echo 'checked="checked" '; } ?>/></td>
							<td><label for="guest_sets_all">Grant access to all protected images</label></td>
						</tr>
						<tr>
							<td class="right" style="width: 5%;"><input type="radio" name="guest_sets" value="select" id="guest_sets_select" <?php if(!empty($guest['guest_sets'])){ echo 'checked="checked" '; } ?>/></td>
							<td><label for="guest_sets_select">Restrict access to the protected images in the set: &#0160; <?php echo $fsip->showSets('guest_sets_select', @$guest['guest_sets']); ?></label></td>
						</tr>
						<tr>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td class="right" style="width: 5%;"><input type="checkbox" id="guest_inclusive" name="guest_inclusive" value="inclusive" <?php if($guest['guest_inclusive'] == 1){ echo 'checked="checked" '; } ?> /></td>
							<td><strong><label for="guest_inclusive">Display both public and protected images to this guest.</label></strong></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="guest_reset_view_count" name="guest_reset_view_count" value="reset_view_count" /></td>
				<td><strong><label for="guest_reset_view_count">Reset view count.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="guest_delete" name="guest_delete" value="delete" /></td>
				<td><strong><label for="guest_delete">Delete this guest.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="guest_id" value="<?php echo $guest['guest_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $fsip->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>