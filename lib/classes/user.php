<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

/**
 * @author Budin Ltd. <contact@budinltd.com>
 * @copyright Copyright (c) 2010-2012, Budin Ltd.
 * @version 1.1
 */

class User {
	public $user;
	private $db;
	
	/**
	 * Initiate User object
	 *
	 */
	public function __construct() {
		global $db;
		$this->db = $db;

		// Login user by session data
		if (!empty($_SESSION['fsip']['user'])) {
			$this->user = $_SESSION['fsip']['user'];
		} elseif (!empty($_COOKIE['uid']) and !empty($_COOKIE['key'])) {
			// Login user by ID, key
			$user_id = strip_tags($_COOKIE['uid']);
			$user_key = strip_tags($_COOKIE['key']);
			unset($_SESSION['fsip']['guest']);
			self::authByCookie($user_id, $user_key);
		}
	}
	
	/**
	 * Terminate User object, save user in session
	 *
	 * @return void
	 */
	public function __destruct() {
		// Store user to session data
		if (isset($this->user)) {
			$_SESSION['fsip']['user'] = $this->user;
		}
		$this->db = null;
	}
	
	/**
	 * Perform Orbit hook
	 *
	 * @param Orbit $orbit 
	 * @return void
	 */
	public function hook($orbit=null) {
		if (!is_object($orbit)) {
			$orbit = new Orbit;
		}
		
		$this->user = $orbit->hook('user', $this->user, $this->user);
	}
	
	// GUESTS
	
	/**
	 * Authenticate guest access
	 *
	 * @param string $key Guest access key
	 * @return void Redirects if unsuccessful
	 */
//	public function access($key=null) {
		// Logout
/*		unset($_SESSION['fsip']['guest']);
		
		// Error checking
		if (empty($key)) {
			setcookie('guest_id', false, time()+$seconds, '/');
			setcookie('guest_key', false, time()+$seconds, '/');
			return false; 
		}
		
		$key = strip_tags($key);
		
		$query = $this->db->prepare('SELECT * FROM guests WHERE guest_key = :guest_key;');
		$query->execute(array(':guest_key' => $key));
		$guests = $query->fetchAll();
		$guest = $guests[0];
		
		if (!$guest) {
			Debugger::addError('Guest not found.', 'You are not authorized for this material.', null, null, 401);
		}
		
		if (returnConf('guest_remember')) {
			$seconds = returnConf('guest_remember_time');
			$key = sha1(PATH . BASE . DB_DSN . DB_TYPE . $guest['guest_key']);
			setcookie('guest_id', $guest['guest_id'], time()+$seconds, '/');
			setcookie('guest_key', $key, time()+$seconds, '/');
		}
		
		$_SESSION['fsip']['guest'] = $guest;*/
//	}

	// AUTHENTICATION
	
	/**
	 * Login user by username, password
	 *
	 * @param string $username 
	 * @param string $password 
	 * @param bool $remember 
	 * @return bool True if successful
	 */
	public function auth($username='', $password='', $remember=false) {
		// Error checking
		if (empty($username) or empty($password)) {
			return false;
		} 
		
		// Check database
		$query = $this->db->prepare('SELECT * FROM users WHERE user_username = :username AND user_pass = :password;');
		$query->execute(array(':username' => $username, ':password' => sha1($password . SALT)));
		$this->user = $query->fetchAll();
		
		if (!self::prep($remember)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Login user by ID, key
	 *
	 * @param string $user_id 
	 * @param string $user_key 
	 * @param bool $remember 
	 * @return bool True if successful
	 */
	protected function authByCookie($user_id=0, $user_key='', $remember=true) {
		// Error checking
		if (empty($user_id) or empty($user_key)) { return false ; }

		$query = $this->db->prepare('SELECT * FROM users WHERE user_id = :user_id AND user_key = :user_key;');
		$query->execute(array(':user_id' => $user_id, ':user_key' => $user_key));
		$this->user = $query->fetchAll();

		if (!self::prep($remember)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Prepare user for functionality
	 *
	 * @param bool $remember 
	 * @return bool True if successful
	 */
	private function prep($remember=false) {		
		// If overlapping users exist, destroy object
		if (count($this->user) != 1) {
			unset($this->user);
			return false;
		}
		
		// If user exists, store their row
		$this->user = $this->user[0];
		
//		unset($_SESSION['fsip']['guest']);
		
		$key = '';
		
		// Store "remember me" data
		if ($remember == true) {
			$key = $this->user['user_id'] . $this->user['user_username'] . $this->user['user_pass'] . DB_DSN . time();
			$key = sha1($key . SALT);
			setcookie('uid', $this->user['user_id'], time()+USER_REMEMBER, BASE);
			setcookie('key', $key, time()+USER_REMEMBER, BASE);
		}
		
		// Destroy sensitive information from object
		unset($this->user['user_pass']);
		unset($this->user['user_key']);
		
		// Create arrays
		$this->user['user_permissions'] = unserialize($this->user['user_permissions']);
		$this->user['user_preferences'] = unserialize($this->user['user_preferences']);
		
		// Save in session
		$_SESSION['fsip']['user'] = $this->user;
		
		// Update database
		$fields = array('user_last_login' => date('Y-m-d H:i:s'), 'user_key' => $key);
		return $this->db->updateRow($fields, 'users', $this->user['user_id']);
	}
	
	/**
	 * Logout user, destroy "remember me" data
	 *
	 * @return void
	 */
	public function deauth() {
		unset($this->user);
		
		$now = time();
		
		setcookie('uid', '', $now-42000, BASE);
		setcookie('key', '', $now-42000, BASE);
		
		// Destroy session
		$_SESSION = array();
		
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', $now-42000, BASE);
		}
		
		session_destroy();
		session_start();
	}
	
	// PERMISSIONS
	
	
	/**
	 * Is there a valid user currently logged in?
	 *
	 * @param bool $required Redirects (on failure) if true
	 * @return bool true if there is a user logged in
	 */
	 public function isLoggedIn($required=false) {
		if (!empty($this->user)) {
			return true;
		}
		if ($required === true) {
			redirectToLogin();
		}
		return false;
	 }

	/**
	 * Verify user has permission to access module
	 *
	 * @param string $permission Permission string
	 * @param bool $required Redirects (on failure) if true
	 * @param int $userid The userid of the user we are checking a permission against, if not the currently logged in user.
	 * @return void
	 */
	public function hasPermission($permission=null, $required=false, $userid=null) {
//echo "checking user perm<br />";
		if (empty($this->user) && $userid == null) {
//echo "checking user perm, user NOT logged in<br />";
			// user not logged in
			if ($required === true) {
				redirectToLogin();
			} else {
				return false;
			}
		} else {
//echo "checking user perm, user IS logged in<br />";
			// $this->user is not empty, a user is logged in
			if ($userid != null and is_numeric($userid)) {
				// We're checking a permission on a user who is not currently logged in
				
				// TODO DEH - implement this case
			}

			if (empty($permission)) {
//echo "checking user perm, empty permission so returning true<br />";
				return true;
			} elseif ($this->isAdmin()) {
//echo "checking if isAdmin is true so returning true<br />";
				return true;
			} elseif (in_array($permission, $this->user['user_permissions'])) {
//echo "checking user perm, permission is in user permission array returning true<br />";
				return true;
			} else {
				if ($required === true) {
//echo "checking user perm, debugger adding error<br />";
					Debugger::addError(E_USER_ERROR, 'You do not have permission to access this area of the site.', null, null, 401);
					exit();
				} else {
					return false;
				}
			}
		}
	}
	
	// PREFERENCES
	
	/**
	 * Set preference key
	 *
	 * @param string $name 
	 * @param string $unset 
	 * @return void
	 */
	public function setPref($name='', $unset='') {
		if (!$this->isLoggedIn(true)) { return false; }
		setForm($this->user['user_preferences'], $name, $unset);
	}
	
	/**
	 * Read preference key and return value in HTML
	 *
	 * @param string $name 
	 * @param string $check 
	 * @return void
	 */
	public function readPref($name='', $check=true) {
		if (!$this->isLoggedIn(true)) { return false; }
		readForm($this->user['user_preferences'], $name, $check);
	}
	
	/**
	 * Read preference key and return value
	 *
	 * @param string $name 
	 * @param string $default 
	 * @return void
	 */
	public function returnPref($name='', $default=null) {
		if (!$this->isLoggedIn(true)) { return false; }
		returnForm($this->user['user_preferences'], $name, $default);
	}
	
	/**
	 * Save preferences
	 *
	 * @return void
	 */
	public function savePref() {
		if (!$this->isLoggedIn(true)) { return false; }
		
		$fields = array('user_preferences' => serialize($this->user['user_preferences']));
		
		// Update database
		return $this->db->updateFields($fields);
	}
	
	/**
	 * Update user table
	 *
	 * @param string $fields Associative array of columns and fields
	 * @param bool $overwrite 
	 * @return void
	 */
	public function updateFields($fields=array(), $overwrite=true) {
		if (!$this->isLoggedIn(true)) { return false; }
		
		// Verify each key has changed; if not, unset the key
		foreach($fields as $key => $value) {
			if ($fields[$key] == $this->user[$key]) {
				unset($fields[$key]);
			}
			if (!empty($this->user[$key]) and ($overwrite === false)) {
				unset($fields[$key]);
			}
		}
		
		// If no keys have changed, break
		if (count($fields) == 0) { return false; }
		
		// Update database
		return $this->db->updateRow($fields, 'users', $this->user['user_id']);
	}

	// NEW METHODS

	/**
	 * Determine if a user is the superuser or not. The superuser has complete access to 
	 *  the site and all functions.
	 *
	 * @param int userid May be left null to mean the currently logged in user
	 * @return bool True if user is superuser, false if not
	 */
	public function isSuperUser($userid=null) {
		// If there is no user logged in and no user specified then the answer is no
		if (empty($this->user) && $userid == null) {
			return false;
		}
		if ($userid == 1) {
			return true;
		}
		if ($userid == null and $this->user['user_id'] == 1) {
			return true;
		} 
		return false;
	}


	/**
	 * Determine if a user is an admin user or not. Allows access to 
	 *   the /admin section but does not grant full access to all permissions.
	 *
	 * @param int userid May be left null to mean the currently logged in user
	 * @return bool True if user is an admin, false if not
	 */
	public function isAdmin($userid=null) {
		// If there is no user logged in and no user specified then the answer is no
		if (empty($this->user) && $userid == null) {
			return false;
		}
		// if the user in question is a superuser then they are also a defacto admin
		if ($this->isSuperUser($userid)) {
			return true;
		}
		// if the user in question is the currently logged in user
		if ($userid == null) {
			if (in_array('admin', $this->user['user_permissions'])) {
				return true;
			} else {
				return false;
			}
		}
		// check database for the specified user's permissions
		$query = $this->db->prepare('SELECT user_permissions FROM users WHERE user_id = :user_id;');
		$query->execute(array(':user_id' => $user_id));
		$userperms = $query->fetchAll();
		$userperms = unserialize($userperms);
		if (in_array('admin', $userperms)) {
			return true;
		}
		// none of our checks have been able to determine that this user is an admin in the system
		return false;
	}

	/**
	 * Determine if a user is an image reviewer or not.
	 *
	 * @param int userid May be left null to mean the currently logged in user
	 * @return bool True if user is an image reviewer, false if not
	 */
	public function isImageReviewer($userid=null) {
		// 'reviwer' permission will determine if a user is allowed to publish images to the live site
		return $this->userHasSpecificPermission($userid, 'reviewer');
	}

	/**
	 * Determine if a user is an image contributor or not.
	 *
	 * @param int userid May be left null to mean the currently logged in user
	 * @return bool True if user is an image contributor, false if not
	 */
	public function isImageContributor($userid=null) {
		// 'contributor' permission will determine if the user is allowed to upload files
		return $this->userHasSpecificPermission($userid, 'contributor');
	}


	/**
	 * Determine if a user has a named permission.
	 *
	 * @param int userid May be left null to mean the currently logged in user
	 * @param int userid May be left null to mean the currently logged in user
	 * @return bool True if user is an image reviewer, false if not
	 */
	private function userHasSpecificPermission($userid=null, $permission_sought=null) {
		// If there is no user logged in and no user specified then the answer is no
		if (empty($this->user) && $userid == null) {
			return false;
		}
		// if the user in question is an admin or superuser then they are allowed access to anything
		if ($this->isAdmin($userid)) {
			return true;
		}
		if ($permission_sought == 'admin') {
			// we've failed our isAdmin check and we're looking to see if the user has admin capabilities!
			return false;
		}
		// if the user in question is the currently logged in user
		if ($userid == null) {
			if (in_array($permission_sought, $this->user['user_permissions'])) {
				return true;
			} else {
				return false;
			}
		}
		// check database for the specified user's permissions
		$query = $this->db->prepare('SELECT user_permissions FROM users WHERE user_id = :user_id;');
		$query->execute(array(':user_id' => $user_id));
		$userperms = $query->fetchAll();
		$userperms = unserialize($userperms);
		if (in_array($permission_sought, $userperms)) {
			return true;
		}
		// none of our checks have been able to determine that this user has the sought permission
		return false;
	}

}
?>