<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

//echo "users 1<br />";
require_once('../config.php');
//echo "users 2<br />";
$user = new User;
//echo "users 3<br />";
global $db;

$user->hasPermission('users', true);
//echo "users 4<br />";

if (!empty($_GET['id'])) {
	$user_db_id = findID($_GET['id']);
}
//echo "users 5<br />";

$user_db_act = 'none';
if (!empty($_GET['act'])) {
//echo "GET act is not empty, set user_db_act to ".$_GET['act'];
	$user_db_act = $_GET['act'];
}
if (!empty($_POST['act'])) {
//echo "POST act is not empty, set user_db_act to ".$_GET['act'];
	$user_db_act = $_POST['act'];
}

//echo "users 6 - user_db_act = $user_db_act<br />";
//echo "users 6.1 - the full GET object is<br />";
//print_r($_GET);
//echo "<br />";

// SAVE USER UPDATES, PASSWORD RESET, ETC
if (!empty($_POST['user_id'])) {
//echo "users 7<br />";
	$user_db_id = findID($_POST['user_id']);
	if (isset($_POST['user_delete']) and $_POST['user_delete'] == 'delete') {
		$db->deleteRow('users', $user_db_id);
	} else {
		if ($_POST['user_reset_pass'] == 'reset_pass') {
			$rand = randInt();
			echo $rand;
			$pass = substr(sha1($rand), 0, 8);
			email($_POST['user_email'], 'Password reset', 'Your password has been reset:' . "\r\n\n" . $pass . "\r\n\n" . LOCATION . BASE . ADMINFOLDER);
			$_POST['user_pass'] = $pass;
		}
		
		$permissions = array();
		
		if (@$_POST['user_permission_upload'] == 'true') { $permissions[] = 'upload'; $permissions[] = 'library'; }
		if (@$_POST['user_permission_shoebox'] == 'true') { $permissions[] = 'shoebox'; $permissions[] = 'library'; }
		if (@$_POST['user_permission_library'] == 'true') { $permissions[] = 'images'; $permissions[] = 'library'; }
		if (@$_POST['user_permission_editor'] == 'true') { $permissions[] = 'editor'; $permissions[] = 'features'; }
		if (@$_POST['user_permission_tags'] == 'true') { $permissions[] = 'tags'; $permissions[] = 'features'; }
		if (@$_POST['user_permission_sets'] == 'true') { $permissions[] = 'sets'; $permissions[] = 'features'; }
		if (@$_POST['user_permission_pages'] == 'true') { $permissions[] = 'pages'; $permissions[] = 'features'; }
		if (@$_POST['user_permission_rights'] == 'true') { $permissions[] = 'rights'; $permissions[] = 'features'; }
		if (@$_POST['user_permission_comments'] == 'true') { $permissions[] = 'comments'; }
		if (@$_POST['user_permission_statistics'] == 'true') { $permissions[] = 'statistics'; }
		if (@$_POST['user_permission_thumbnails'] == 'true') { $permissions[] = 'thumbnails'; $permissions[] = 'settings'; }
		if (@$_POST['user_permission_users'] == 'true') { $permissions[] = 'users'; $permissions[] = 'settings'; }
		if (@$_POST['user_permission_guests'] == 'true') { $permissions[] = 'guests'; $permissions[] = 'settings'; }
		if (@$_POST['user_permission_themes'] == 'true') { $permissions[] = 'themes'; $permissions[] = 'settings'; }
		if (@$_POST['user_permission_extensions'] == 'true') { $permissions[] = 'extensions'; $permissions[] = 'settings'; }
		if (@$_POST['user_permission_configuration'] == 'true') { $permissions[] = 'configuration'; $permissions[] = 'settings'; }
		if (@$_POST['user_permission_maintenance'] == 'true') { $permissions[] = 'maintenance'; $permissions[] = 'settings'; }
		
		$permissions = array_unique($permissions);
		
		$fields = array('user_realname' => makeUnicode($_POST['user_realname']),
			'user_username' => $_POST['user_username'],
			'user_email' => $_POST['user_email'],
			'user_uri' => $_POST['user_uri'],
			'user_permissions' => serialize($permissions));
		if (!empty($_POST['user_pass']) and $_POST['user_pass'] != '********') {
			$fields['user_pass'] = sha1($_POST['user_pass']);
		}

		$db->updateRow($fields, 'users', $user_db_id);
	}
	unset($user_db_id);
} 

//echo "users 9<br />";

define('TAB', 'settings');

//echo "users 10<br />";

// GET LIST OF USERS TO VIEW OR GET A USER TO EDIT
if ($user_db_act == 'none') {
//echo "users act==none<br />";
//print_r($db);
	// Update image counts
	$db->updateCounts('images', 'users', 'user_image_count');
	$db->updateCounts('comments', 'users', 'user_comment_count');
//echo "<br />users 11.1<br />";
	
	$user_dbs = $db->getTable('users');
	$user_db_count = @count($user_dbs);
//echo "users 11.2<br />";
	
	define('TITLE', 'FSIP Users');

//echo "users 11.3<br />Requiring:".PATH . INCLUDES . 'admin/admin_header.php';
	require_once(PATH . INCLUDES . 'admin/admin_header.php');
//echo "users 11.4<br />";
	
	// Include table of users from html include file.
	require_once(PATH . INCLUDES . "admin/users/view_inc.html");

//echo "users 11.5, about to include footer<br />";
	require_once(PATH . INCLUDES . 'admin/admin_footer.php');
//echo "users 11.6 after footer include<br />";

} else if ($user_db_act == 'view_user' || $user_db_act == 'add') {
//echo "users 12<br />";

	if ($user_db_act == 'view_user') {
		// save_string is what the button's named at the end of the form
		$save_string = 'Save changes';

		// If we're editing a user then first we need to fetch their information

		// Update image count
		$db->updateCount('images', 'users', 'user_image_count', $user_db_id);

		// Get user
		$user_db = $db->getRow('users', $user_db_id);
		$user_db_perms = unserialize($user_db['user_permissions']);
		$user_db = makeHTMLSafe($user_db);
		$user_image_count = $user_db['user_image_count'];

		if (empty($user_image_count)) {
			$user_image_count = 0;
		}

		if (!empty($user_db['user_realname'])) {
			define('TITLE', 'Edit User: ' . $user_db['user_realname']);
		} else {
			define('TITLE', 'Edit User');
		}

	} else {
		$save_string = 'Add user';
		define('TITLE', 'Add User');
	}

	require_once(PATH . INCLUDES . 'admin/admin_header.php');

	if ($user_db_act == 'view_user') {
?>	
		<div class="actions">
			<a href="mailto:<?php echo $user_db['user_email']; ?>"><button>Email user</button></a>
			<a href="<?php echo BASE . ADMINFOLDER . 'search' . URL_ACT . 'users' . URL_AID . $user_db['user_id'] . URL_RW; ?>"><button>View user's images (<?php echo $user_image_count; ?>)</button></a>
		</div>
<?php
		echo '<h1><img src="' . BASE . IMGFOLDER . 'icons/users.png" alt="" /> User: ' . $user_db['user_realname'] . '</h1>';
	} else {
		echo '<h1><img src="' . BASE . IMGFOLDER . 'icons/users.png" alt="" /> New User</h1>';
	}		
	
	require_once(PATH . INCLUDES . "admin/users/edit_or_add_inc.html");
	
//echo "users , about to include footer<br />";
	require_once(PATH . INCLUDES . 'admin/admin_footer.php');

} else if ($user_db_act == 'save') {
//echo "IN ACT = SAVE 1 <br />";
	// Create a new table row for the new user
	$user_db_id = $db->addRow(null, 'users');
//echo "IN ACT = SAVE 2<br />";

	// Save the new user's data into the new row in our database
	$permissions = array();
	
	if (@$_POST['user_permission_upload'] == 'true') { $permissions[] = 'upload'; $permissions[] = 'library'; }
	if (@$_POST['user_permission_shoebox'] == 'true') { $permissions[] = 'shoebox'; $permissions[] = 'library'; }
	if (@$_POST['user_permission_library'] == 'true') { $permissions[] = 'images'; $permissions[] = 'library'; }
	if (@$_POST['user_permission_editor'] == 'true') { $permissions[] = 'editor'; $permissions[] = 'features'; }
	if (@$_POST['user_permission_tags'] == 'true') { $permissions[] = 'tags'; $permissions[] = 'features'; }
	if (@$_POST['user_permission_sets'] == 'true') { $permissions[] = 'sets'; $permissions[] = 'features'; }
	if (@$_POST['user_permission_pages'] == 'true') { $permissions[] = 'pages'; $permissions[] = 'features'; }
	if (@$_POST['user_permission_rights'] == 'true') { $permissions[] = 'rights'; $permissions[] = 'features'; }
	if (@$_POST['user_permission_comments'] == 'true') { $permissions[] = 'comments'; }
	if (@$_POST['user_permission_statistics'] == 'true') { $permissions[] = 'statistics'; }
	if (@$_POST['user_permission_thumbnails'] == 'true') { $permissions[] = 'thumbnails'; $permissions[] = 'settings'; }
	if (@$_POST['user_permission_users'] == 'true') { $permissions[] = 'users'; $permissions[] = 'settings'; }
	if (@$_POST['user_permission_guests'] == 'true') { $permissions[] = 'guests'; $permissions[] = 'settings'; }
	if (@$_POST['user_permission_themes'] == 'true') { $permissions[] = 'themes'; $permissions[] = 'settings'; }
	if (@$_POST['user_permission_extensions'] == 'true') { $permissions[] = 'extensions'; $permissions[] = 'settings'; }
	if (@$_POST['user_permission_configuration'] == 'true') { $permissions[] = 'configuration'; $permissions[] = 'settings'; }
	if (@$_POST['user_permission_maintenance'] == 'true') { $permissions[] = 'maintenance'; $permissions[] = 'settings'; }
//echo "IN ACT = SAVE 3<br />";
	
	$permissions = array_unique($permissions);
	
	$fields = array('user_realname' => makeUnicode($_POST['user_realname']),
		'user_username' => $_POST['user_username'],
		'user_email' => $_POST['user_email'],
		'user_uri' => $_POST['user_uri'],
		'user_permissions' => serialize($permissions));
	if (!empty($_POST['user_pass']) and $_POST['user_pass'] != '********') {
		$fields['user_pass'] = sha1($_POST['user_pass']);
	}
	$db->updateRow($fields, 'users', $user_db_id);
//echo "IN ACT = SAVE 3<br />";
	unset($user_db_id);

//echo "IN ACT = SAVE 4<br />";

	// Update image counts
	$db->updateCounts('images', 'users', 'user_image_count');
//echo "IN ACT = SAVE 5<br />";
	$db->updateCounts('comments', 'users', 'user_comment_count');
//echo "IN ACT = SAVE 6<br />";
	
	$user_dbs = $db->getTable('users');
	$user_db_count = @count($user_dbs);
//echo "IN ACT = SAVE 6<br />";

	define('TITLE', 'FSIP Added new user: '. $_POST['user_username']);

	require_once(PATH . INCLUDES . 'admin/admin_header.php');
	
	// Include table of users from html include file.
	require_once(PATH . INCLUDES . "admin/users/view_inc.html");
//echo "users in act = save, about to include footer<br />";
	
	require_once(PATH . INCLUDES . 'admin/admin_footer.php');
}

?>