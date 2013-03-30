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
	private $dbpointer;
	
	/**
	 * Initiate User object
	 *
	 */
	public function __construct() {
		$this->dbpointer = getDB();

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
		$this->dbpointer = null;
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
	public function access($key=null) {
		// Logout
		unset($_SESSION['fsip']['guest']);
		
		// Error checking
		if (empty($key)) {
			setcookie('guest_id', false, time()+$seconds, '/');
			setcookie('guest_key', false, time()+$seconds, '/');
			return false; 
		}
		
		$key = strip_tags($key);
		
		$query = $this->dbpointer->prepare('SELECT * FROM guests WHERE guest_key = :guest_key;');
		$query->execute(array(':guest_key' => $key));
		$guests = $query->fetchAll();
		$guest = $guests[0];
		
		if (!$guest) {
			addError('Guest not found.', 'You are not authorized for this material.', null, null, 401);
		}
		
		if (returnConf('guest_remember')) {
			$seconds = returnConf('guest_remember_time');
			$key = sha1(PATH . BASE . DB_DSN . DB_TYPE . $guest['guest_key']);
			setcookie('guest_id', $guest['guest_id'], time()+$seconds, '/');
			setcookie('guest_key', $key, time()+$seconds, '/');
		}
		
		$_SESSION['fsip']['guest'] = $guest;
	}

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
		$query = $this->dbpointer->prepare('SELECT * FROM users WHERE user_username = :username AND user_pass = :password;');
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

		$query = $this->dbpointer->prepare('SELECT * FROM users WHERE user_id = :user_id AND user_key = :user_key;');
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
		
		// Remove guest access??
		unset($_SESSION['fsip']['guest']);
		
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
		return $this->dbpointer->updateRow($fields, 'users', $this->user['user_id']);
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
	 * Verify user has permission to access module
	 *
	 * @param bool $required Redirects (on failure) if true
	 * @param string $permission Permission string
	 * @return void
	 */
	public function perm($required=false, $permission=null) {
//echo "checking user perm<br />";
		if (empty($this->user)) {
//echo "checking user perm, user NOT logged in<br />";
			// user not logged in
			if ($required === true) {
				$_SESSION['fsip']['destination'] = location();
				session_write_close();
				
				$location = LOCATION . BASE . 'login' . URL_CAP;
				headerLocationRedirect($location);
				exit();
			} else {
				return false;
			}
		} else {
//echo "checking user perm, user IS logged in<br />";
			// $this->user is not empty, user is logged in
			if (empty($permission)) {
//echo "checking user perm, empty permission so returning true<br />";
				return true;
			} elseif($this->user['user_id'] == 1) {
//echo "checking user perm, userid is 1 so returning true<br />";
				return true;
			} elseif(in_array($permission, $this->user['user_permissions'])) {
//echo "checking user perm, permission is in user permission array returning true<br />";
				return true;
			} else {
				if ($required === true) {
//echo "checking user perm, debugger adding error<br />";
					addError(E_USER_ERROR, 'You do not have permission to access this module', null, null, 401);
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
		if (!$this->perm(true)) { return false; }
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
		if (!$this->perm(true)) { return false; }
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
		if (!$this->perm(true)) { return false; }
		returnForm($this->user['user_preferences'], $name, $default);
	}
	
	/**
	 * Save preferences
	 *
	 * @return void
	 */
	public function savePref() {
		if (!$this->perm(true)) { return false; }
		
		$fields = array('user_preferences' => serialize($this->user['user_preferences']));
		
		// Update database
		return $this->dbpointer->updateFields($fields);
	}
	
	/**
	 * Update user table
	 *
	 * @param string $fields Associative array of columns and fields
	 * @param bool $overwrite 
	 * @return void
	 */
	public function updateFields($fields=array(), $overwrite=true) {
		if (!$this->perm(true)) { return false; }
		
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
		return $this->dbpointer->updateRow($fields, 'users', $this->user['user_id']);
	}

}

?>