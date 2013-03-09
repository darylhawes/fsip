<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

/**
 * @author Budin Ltd. <contact@budinltd.com>
 * @copyright Copyright (c) 2010-2012, Budin Ltd.
 * @version 1.0
 */

function __autoload($class) {
	$file = strtolower($class) . '.php';
	if (file_exists(PATH . CLASSES . $file)) {
		require_once(PATH . CLASSES . $file);
	}
}

class FSIP {
	const build = 1294;
	const copyright = 'Powered by <a href="http://github.com/darylhawes/fsip">FSIP</a> based on <a href="http://www.alkalineapp.com/">Alkaline</a> under MIT license.';
	const product = 'FSIP';
	const version = '1.1.2.3';
	
	public $admin;
		
	public $db_type;
	public $db_version;
	
	public $tables;
	public $tables_cache;
	public $tables_index;
	
	protected $db;
	protected $notifications;
	
	/**
	 * Initiates FSIP
	 *
	 * @return void
	 **/
	public function __construct(){
		// Set error handlers
		set_error_handler(array($this, 'addError'), E_ALL);
		set_exception_handler(array($this, 'addException'));
		
		// Set error reporting
		if(ini_get('error_reporting') > 30719){
			error_reporting(E_ALL);
		}
				
		// Disable magic_quotes
		if(get_magic_quotes_gpc()){
			$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
			while(list($key, $val) = each($process)){
				foreach($val as $k => $v){
					unset($process[$key][$k]);
					if(is_array($v)){
						$process[$key][stripslashes($k)] = $v;
						$process[] = &$process[$key][stripslashes($k)];
					}
					else{
						$process[$key][stripslashes($k)] = stripslashes($v);
					}
				}
			}
			unset($process);
		}
		
		// Determine class
		$class = get_class($this);
		
		// Begin a session, if one does not yet exist
		if(session_id() == ''){ session_start(); }
		
		// Debug info
		$chief_classes = array('FSIP', 'XMLRPC');
		if(in_array(get_class($this), $chief_classes)){
			// Send browser headers
			if(!headers_sent()){
				header('Cache-Control: no-cache, must-revalidate', false);
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT', false);
			}
			
			$_SESSION['fsip']['debug']['start_time'] = microtime(true);
			$_SESSION['fsip']['debug']['queries'] = 0;
			if($contents = file_get_contents($this->correctWinPath(PATH . 'config.json'))){
				$_SESSION['fsip']['config'] = json_decode($contents, true);
			}	
			
			if(empty($_SESSION['fsip']['config'])){
				$_SESSION['fsip']['config'] = array();
			}
			
			if($timezone = $this->returnConf('web_timezone')){
				date_default_timezone_set($timezone);
			}
			else{
				date_default_timezone_set('GMT');
			}
		}
		
		// Write tables
		$this->tables = array('images' => 'image_id', 'tags' => 'tag_id', 'sets' => 'set_id', 'pages' => 'page_id', 'rights' => 'right_id', 'exifs' => 'exif_id', 'extensions' => 'extension_id', 'themes' => 'theme_id', 'sizes' => 'size_id', 'users' => 'user_id', 'guests' => 'guest_id', 'posts' => 'post_id', 'comments' => 'comment_id', 'versions' => 'version_id', 'citations' => 'citation_id', 'items' => 'item_id', 'trackbacks' => 'trackback_id');
		$this->tables_cache = array('comments', 'extensions', 'images', 'pages', 'posts', 'rights', 'sets', 'sizes');
		$this->tables_index = array('comments', 'images', 'pages', 'posts', 'rights', 'sets', 'tags');
		
		// Check if in Dashboard
		if(strpos($_SERVER['SCRIPT_FILENAME'], PATH . ADMIN) === 0){
			$this->adminpath = true;
		}
		
		// Set back link
		if(!empty($_SERVER['HTTP_REFERER']) and ($_SERVER['HTTP_REFERER'] != LOCATION . $_SERVER['REQUEST_URI'])){
			$_SESSION['fsip']['back'] = $_SERVER['HTTP_REFERER'];
		} 
		
		// Initiate database connection, if necessary
		$no_db_classes = array('Canvas');
		
		if(!in_array($class, $no_db_classes)){
			if(defined('DB_TYPE') and defined('DB_DSN')){
				// Determine database type
				$this->db_type = DB_TYPE;
		
				if($this->db_type == 'mssql'){
					// $this->db = new PDO(DB_DSN);
				}
				elseif($this->db_type == 'mysql'){
					$this->db = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => true, PDO::FETCH_ASSOC => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT));
				}
				elseif($this->db_type == 'pgsql'){
					$this->db = new PDO(DB_DSN, DB_USER, DB_PASS);
					$this->db->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
				}
				elseif($this->db_type == 'sqlite'){
					$this->db = new PDO(DB_DSN, null, null, array(PDO::ATTR_PERSISTENT => false, PDO::FETCH_ASSOC => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT));
			
					$this->db->sqliteCreateFunction('ACOS', 'acos', 1);
					$this->db->sqliteCreateFunction('COS', 'cos', 1);
					$this->db->sqliteCreateFunction('RADIANS', 'deg2rad', 1);
					$this->db->sqliteCreateFunction('SIN', 'sin', 1);
				}
				
				if(is_object($this->db)){
					$this->db_version = $this->db->getAttribute(PDO::ATTR_SERVER_VERSION);
				}
			}
		}
		
		// Delete saved Orbit extension session references
		if($class == 'FSIP'){
			unset($_SESSION['fsip']['extensions']);
			
			// Log-in guests via cookie
			if(!empty($_COOKIE['guest_key']) and !empty($_COOKIE['guest_id']) and empty($_SESSION['fsip']['guest'])){
				$query = $this->prepare('SELECT * FROM guests WHERE guest_id = :guest_id LIMIT 0, 1;');
				$query->execute(array(':guest_id' => $_COOKIE['guest_id']));
				$guests = $query->fetchAll();
				$guest = $guests[0];
				
				if($_COOKIE['guest_key'] == sha1(PATH . BASE . DB_DSN . DB_TYPE . $guest['guest_key'])){
					$this->access($guest['guest_key']);
				}
			}
		}
	}
	
	/**
	 * Terminates object, closes the database connection
	 *
	 * @return void
	 **/
	public function __destruct(){
		$this->db = null;
	}
	
	// DATABASE
	
	/**
	 * Prepares and executes SQL statement
	 *
	 * @param string $query Query
	 * @return int Number of affected rows
	 */
	public function exec($query){
		if(!$this->db){ $this->addError(E_USER_ERROR, 'No database connection'); }
		
		$this->prequery($query);
		$response = $this->db->exec($query);
		$this->postquery($query);
		
		return $response;
	}
	
	/**
	 * Prepares a statement for execution and returns a statement object
	 *
	 * @param string $query Query
	 * @return PDOStatement
	 */
	public function prepare($query){
		if(!$this->db){ $this->addError(E_USER_ERROR, 'No database connection'); }
		
		$this->prequery($query);
		$response = $this->db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$this->postquery($query);
		
		if(!$response){ $this->addError(E_USER_ERROR, 'Invalid query, check database log and connection'); }
		
		return $response;
	}
	
	/**
	 * Translate query for different database types
	 *
	 * @param string $query Query
	 * @return string Translated query
	 */
	public function prequery(&$query){
		$_SESSION['fsip']['debug']['queries']++;
		
		if(TABLE_PREFIX != ''){
			// Add table prefix
			$query = preg_replace('#(FROM|JOIN)\s+([\sa-z0-9_\-,]*)\s*(WHERE|GROUP|HAVING|ORDER)?#se', "'\\1 '.FSIP::appendTablePrefix('\\2').' \\3'", $query);
			$query = preg_replace('#([a-z]+[a-z0-9-\_]*)\.#si', TABLE_PREFIX . '\\1.', $query);
			$query = preg_replace('#(INSERT INTO|UPDATE)\s+(\w+)#si', '\\1 ' . TABLE_PREFIX . '\\2', $query);
			$query = preg_replace('#TABLE ([[:punct:]]*)(\w+)#s', 'TABLE \\1' . TABLE_PREFIX . '\\2', $query);
		}
		
		if($this->db_type == 'mssql'){
			/*
			preg_match('#GROUP BY (.*) ORDER BY#si', $query, $match);
			$find = @$match[0];
			if(!empty($find)){
				$replace = $find;
				$replace = str_replace('stat_day', 'DAY(stat_date)', $replace);
				$replace = str_replace('stat_month', 'MONTH(stat_date)', $replace);
				$replace = str_replace('stat_year', 'YEAR(stat_date)', $replace);
				$query = str_replace($find, $replace, $query);
			}
			
			if(preg_match('#SELECT (?:.*) LIMIT[[:space:]]+([0-9]+),[[:space:]]*([0-9]+)#si', $query, $match)){
				$query = preg_replace('#LIMIT[[:space:]]+([0-9]+),[[:space:]]*([0-9]+)#si', '', $query);
				$offset = @$match[1];
				$limit = @$match[2];
				preg_match('#FROM (.+?)(?:\s|,)#si', $query, $match);
				$table = @$match[1];
				$query = str_replace('SELECT ', 'SELECT TOP 999999999999999999 ROW_NUMBER() OVER (ORDER BY ' . $this->tables[$table]  . ' ASC) AS row_number,', $query);
				$query = 'SELECT * FROM (' . $query . ') AS temp WHERE temp.row_number > ' . $offset . ' AND temp.row_number <= ' . ($offset + $limit);
			}
			*/
		}
		elseif($this->db_type == 'pgsql'){
			$query = preg_replace('#LIMIT[[:space:]]+([0-9]+),[[:space:]]*([0-9]+)#si', 'LIMIT \2 OFFSET \1', $query);
			$query = str_replace('HOUR(', 'EXTRACT(HOUR FROM ', $query);
			$query = str_replace('DAY(', 'EXTRACT(DAY FROM ', $query);
			$query = str_replace('MONTH(', 'EXTRACT(MONTH FROM ', $query);
			$query = str_replace('YEAR(', 'EXTRACT(YEAR FROM ', $query);
		}
		elseif($this->db_type == 'sqlite'){
			$query = str_replace('HOUR(', 'strftime("%H",', $query);
			$query = str_replace('DAY(', 'strftime("%d",', $query);
			$query = str_replace('MONTH(', 'strftime("%m",', $query);
			$query = str_replace('YEAR(', 'strftime("%Y",', $query);
		}
		
		$query = trim($query);
	}
	
	/**
	 * Append table prefix to table names (before executing query)
	 *
	 * @param string $tables Comma-separated tables
	 * @return string Comma-separated tables
	 */
	protected function appendTablePrefix($tables){
		if(strpos($tables, ',') === false){
			$tables = trim($tables);
			$tables = TABLE_PREFIX . $tables;
		}
		else{
			$tables = explode(',', $tables);
			$tables = array_map('trim', $tables);
			foreach($tables as &$table){
				$table = TABLE_PREFIX . $table;
			}
			$tables = implode(', ', $tables);
		}
		return $tables;
	}
	
	/**
	 * Determine if query was successful; if not, log it using report()
	 *
	 * @param string $query
	 * @param string $db 
	 * @return bool True if successful
	 */
	public function postquery(&$query, $db=null){
		if(empty($db)){ $db = $this->db; }
		
		$error = $db->errorInfo();
		
		if(isset($error[2])){
			$code = $error[0];
			$message = $query . ' ' . ucfirst(preg_replace('#^Error\:[[:space:]]+#si', '', $error[2])) . ' (' . $code . ').';
			
			if(substr($code, 0, 2) == '00'){
				$this->report($message, $code);
			}
			elseif($code == '23000'){
				$this->report($message, $code);
				return false;
			}
			else{
				$this->report($message, $code);
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Remove nulls from a JSON string
	 *
	 * @param string $input JSON input
	 * @return string JSON ouput
	 */
	public function removeNull($input){
		return str_replace(':null', ':""', $input);
	}
	
	/**
	 * Retrieve data from http://www.alkalineapp.com/
	 *
	 * @param string $request Request
	 * @return string Response
	 */
	 /* DEH disable fetches from missing remote services
	public function boomerang($request){
		ini_set('default_socket_timeout', 1);
		$contents = @file_get_contents('http://www.alkalineapp.com/boomerang/' . $request . '/');
		ini_restore('default_socket_timeout');
		
		if(empty($contents)){
			$this->addNote('Alkaline could not connect to <a href="http://www.alkalineapp.com/">alkalineapp.com</a> to retrieve data.', 'notice');
		}
		
		$reply = self::removeNull(json_decode($contents, true));
		return $reply;
	}	*/
	
	// GUESTS
	
	/**
	 * Authenticate guest access
	 *
	 * @param string $key Guest access key
	 * @return void Redirects if unsuccessful
	 */
	public function access($key=null){
		// Logout
		unset($_SESSION['fsip']['guest']);
		
		// Error checking
		if(empty($key)){
			setcookie('guest_id', false, time()+$seconds, '/');
			setcookie('guest_key', false, time()+$seconds, '/');
			return false; 
		}
		
		$key = strip_tags($key);
		
		$query = $this->prepare('SELECT * FROM guests WHERE guest_key = :guest_key;');
		$query->execute(array(':guest_key' => $key));
		$guests = $query->fetchAll();
		$guest = $guests[0];
		
		if(!$guest){
			$this->addError('Guest not found.', 'You are not authorized for this material.', null, null, 401);
		}
		
		if($this->returnConf('guest_remember')){
			$seconds = $this->returnConf('guest_remember_time');
			$key = sha1(PATH . BASE . DB_DSN . DB_TYPE . $guest['guest_key']);
			setcookie('guest_id', $guest['guest_id'], time()+$seconds, '/');
			setcookie('guest_key', $key, time()+$seconds, '/');
		}
		
		$_SESSION['fsip']['guest'] = $guest;
	}
	
	// NOTIFICATIONS
	
	/**
	 * Add a notification
	 *
	 * @param string $message Message
	 * @param string $type Notification type (usually 'success', 'error', or 'notice')
	 * @return void
	 */
	public function addNote($message, $type=null){
		$message = strval($message);
		$type = strval($type);
		
		if(!empty($message)){
			$_SESSION['fsip']['notifications'][] = array('message' => $message, 'type' => $type);
		}
	}
	
	/**
	 * Check notifications
	 *
	 * @param string $type Notification type
	 * @return int Number of notifications
	 */
	public function countNotes($type=null) {
		if ( isset($_SESSION['fsip']) && isset($_SESSION['fsip']['notifications']) ) {
			if (!empty($type)){
				$notifications = @$_SESSION['fsip']['notifications'];
				$count = @count($notifications);
				if($count > 0){
					$count = 0;
					foreach($notifications as $notification){
						if($notification['type'] == $type){
							$count++;
						}
					}
					if($count > 0){
						return $count;
					}
				}			
			}
			else {
				$count = @count($_SESSION['fsip']['notifications']);
				if($count > 0){
					return $count;
				}
			}
		}
		
		return 0;
	}
	
	/**
	 * View notifications
	 *
	 * @param string $type Notification type
	 * @return string HTML-formatted notifications 
	 */
	public function returnNotes($type = null) {
		if (!isset($_SESSION['fsip']) || !isset($_SESSION['fsip']['notifications']) ) { return; }
		
		$count = count($_SESSION['fsip']['notifications']);
		
		if ($count == 0) { return; }
		
		$return = '';
		
		// Determine unique types
		$types = array();
		foreach($_SESSION['fsip']['notifications'] as $notifications){
			$types[] = $notifications['type'];
		}
		$types = array_unique($types);
		
		// Produce HTML for display
		foreach($types as $type){
			$return = '<p class="' . $type . '">';
			$messages = array();
			foreach($_SESSION['fsip']['notifications'] as $notification){
				if($notification['type'] == $type){
					$messages[] = $notification['message'];
				}
			}
			$messages = array_unique($messages);
			$return .= implode(' ', $messages) . '</p>';
		}
		
		$return .= '<br />';

		// Dispose of messages
		unset($_SESSION['fsip']['notifications']);
		
		return $return;
	}
	
	// FILE HANDLING
	
	/**
	 * Browse a local directory (non-recursive) for filenames
	 *
	 * @param string $dir Full path to directory
	 * @param string $ext File extensions to seek
	 * @return array Full paths of files
	 */
	public function seekDirectory($dir=null, $ext=IMG_EXT){
		// Error checking
		if(empty($dir)){
			return false;
		}
		
		// Windows-friendly
		$dir = $this->correctWinPath($dir);
		
		$files = array();
		$ignore = array('.', '..');
		
		// Open listing
		$handle = opendir($dir);
		
		// Seek directory
		while($filename = readdir($handle)){
			if(!in_array($filename, $ignore)){ 
				// Recusively check directories
				/*
				if(is_dir($dir . '/' . $filename)){
					self::seekDirectory($dir . $filename . '/', $files);
				}
				*/
				
				if(!empty($ext)){
					// Find files with proper extensions
					if(preg_match('#^([^\.]+.*\.(' . $ext . '){1,1})$#si', $filename)){
						$files[] = $dir . $filename;
					}
				}
				else{
					$files[] = $dir . $filename;
				}
			}
	    }
	
		// Close listing
		closedir($handle);
		
		return $files;
	}
	
	/**
	 * Browse a local directory (non-recursive) for file count
	 *
	 * @param string $dir Full path to directory
	 * @param string $ext File extensions to seek
	 * @return int Number of files
	 */
	public function countDirectory($dir=null, $ext=IMG_EXT){
		// Error checking
		if(empty($dir)){
			return false;
		}
		
		$files = self::seekDirectory($dir, $ext);
		
		return count($files);
	}
	
	/**
	 * Determine a filename from a path
	 *
	 * @param string $file Full or relative file path
	 * @return string|false Filename (including extension) or error
	 */
	public function getFilename($file){
		$matches = array();
		
		// Windows cheat
		$file = str_replace('\\', '/', $file);
		
		preg_match('#^(.*/)?(?:$|(.+?)(?:(\.[^.]*$)|$))#si', $file, $matches);
		
		if(count($matches) < 1){
			return false;
		}
		
		$filename = $matches[2];
		
		if(isset($matches[3])){
			$filename .= $matches[3];
		}
		
		return $filename;
	}
	
	/**
	 * Empty a directory
	 *
	 * @param string $dir Full path to directory
	 * @param bool $recursive Delete subdirectories
	 * @param int $age Delete contents older than $age seconds old
	 * @return void
	 */
	public function emptyDirectory($dir=null, $recursive=true, $age=0){
		// Error checking
		if(empty($dir)){
			return false;
		}
		
		if($age != 0){
			$now = time();
		}
		
		$ignore = array('.', '..');
		
		// Open listing
		$handle = opendir($dir);
		
		// Seek directory
		while($filename = readdir($handle)){
			if(!in_array($filename, $ignore)){
				// Delete directories
				if(is_dir($dir . '/' . $filename) and ($recursive !== false)){
					self::emptyDirectory($dir . $filename . '/');
					@rmdir($dir . $filename . '/');
				}
				// Delete files
				else{
					if($age != 0){
						if($now < (filemtime($dir . $filename) + $age)){
							continue;
						}
					}
					chmod($dir . $filename, 0777);
					unlink($dir . $filename);
				}
			}
	    }
	
		// Close listing
		closedir($handle);
	}
	
	/**
	 * Check file permissions
	 *
	 * @param string $file Full path to file
	 * @return string Octal value (e.g., 0644)
	 */
	public function checkPerm($file){
		return substr(sprintf('%o', @fileperms($file)), -4);
	}
	
	/**
	 * Replace a variable's value in a PHP file (for installation)
	 *
	 * @param string $var Variable (e.g., $var)
	 * @param string $replacement Full line replacement (e.g., $var = 'dog';)
	 * @param string $subject Subject input
	 * @return string Subject output
	 */
	public function replaceVar($var, $replacement, $subject){
		$replacement = str_replace('\\', '\\\\\\\\', $replacement);
		return preg_replace('#^\s*(' . str_replace('$', '\$', $var) . ')\s*=(.*)$#mi', '\\1 = \'' . $replacement . '\';', $subject);
	}
	
	// TYPE CONVERSION
	
	/**
	 * Convert a possible string to boolean
	 *
	 * @param mixed $input
	 * @param mixed $default Return if unknown
	 * @return boolean
	 */
	public function convertToBool(&$input, $default=''){
		if(is_bool($input)){
			return $input;
		}
		elseif(is_string($input)){
			if($input == 'true'){
				return true;
			}
			elseif($input == 'false'){
				return false;
			}
		}
		
		return $default;
	}
	
	/**
	 * Convert a possible string or integer into an array
	 *
	 * @param mixed $input
	 * @return array
	 */
	public function convertToArray(&$input){
		if(is_string($input)){
			$find = strpos($input, ',');
			if($find === false){
				$input = array($input);
			}
			else{
				$input = explode(',', $input);
				$input = array_map('trim', $input);
			}
		}
		elseif(is_int($input)){
			$input = array($input);
		}
		return $input;
	}
	
	/**
	 * Convert a possible string or integer into an array of integers
	 *
	 * @param mixed $input 
	 * @return array
	 */
	public function convertToIntegerArray(&$input){
		if(is_int($input)){
			$input = array($input);
		}
		elseif(is_string($input)){
			$find = strpos($input, ',');
			if($find === false){
				$input = array(intval($input));
			}
			else{
				$input = explode(',', $input);
				$input = array_map('trim', $input);
			}
		}
		return $input;
	}
	
	/**
	 * Convert a PHP configuration string to bytes
	 *
	 * @param mixed $input 
	 * @return array
	 */
	public function convertToBytes(&$input){
		if(is_string($input)){
			if(stripos($input, 'K') !== false){
				$input = intval($input) * 1000;
			}
			elseif(stripos($input, 'M') !== false){
				$input = intval($input) * 1000000;
			}
			elseif(stripos($input, 'G') !== false){
				$input = intval($input) * 1000000000;
			}
		}
		
		return intval($input);
	}
	
	/**
	 * Change filename extension
	 *
	 * @param string $file Filename
	 * @param string $ext Desired extension
	 * @return string Changed filename
	 */
	public function changeExt($file, $ext){
		$file = preg_replace('#\.([a-z0-9]*?)$#si', '.' . $ext, $file);
		return $file;
	}
	
	// TIME FORMATTING
	
	/**
	 * Make time more human-readable
	 *
	 * @param string $time Time
	 * @param string $format Format (as in date();)
	 * @param string $empty If null or empty input time, return this string
	 * @return string|false Time or error
	 */
	public function formatTime($time=null, $format=null, $empty=false){
		// Error checking
		if(empty($time) or ($time == '0000-00-00 00:00:00')){
			return $empty;
		}
		if(empty($format)){
			$format = DATE_FORMAT;
		}
		
		$time = str_replace('tonight', 'today', $time);
		$time = @strtotime($time);
		$time = date($format, $time);
		
		$ampm = array(' am', ' pm');
		$ampm_correct = array(' a.m.', ' p.m.');
		
		$time = str_replace($ampm, $ampm_correct, $time);
		
		return $time;
	}
	
	/**
	 * Make time relative
	 *
	 * @param string $time Time
	 * @param string $format Format (as in date();)
	 * @param string $empty If null or empty input time, return this string
	 * @param int $round Digits of rounding (as in round();)
	 * @return string|false Time or error
	 */
	public function formatRelTime($time, $format=null, $empty=false, $round=null){
		// Error checking
		if(empty($time) or ($time == '0000-00-00 00:00:00')){
			return $empty;
		}
		if(empty($format)){
			$format = DATE_FORMAT;
		}
		
		if(!is_integer($time)){
			$time = str_ireplace(' at ', ' ', $time);
			$time = str_ireplace(' on ', ' ', $time);
		
			$time = strtotime($time);
		}
		
		$now = time();
		$seconds = $now - $time;
		$day = $now - strtotime(date('Y-m-d', $time));
		$month = $now - strtotime(date('Y-m', $time));
		
		if(is_integer($round)){
			$seconds = round($seconds, $round);
		}
		
		if(empty($seconds)){
			$span = 'just now';
		}
		else{
			switch($seconds){
				case(empty($seconds) or ($seconds < 15)):
					$span = 'just now';
					break;
				case($seconds < 3600):
					$minutes = intval($seconds / 60);
					if($minutes < 2){ $span = 'a minute ago'; }
					else{ $span = $minutes . ' minutes ago'; }
					break;
				case($seconds < 86400):
					$hours = intval($seconds / 3600);
					if($hours < 2){ $span = 'an hour ago'; }
					else{ $span = $hours . ' hours ago'; }
					break;
				case($seconds < 2419200):
					$days = floor($day / 86400);
					if($days < 2){ $span = 'yesterday'; }
					else{ $span = $days . ' days ago'; }
					break;
				case($seconds < 29030400):
					$months = floor($month / 2419200);
					if($months < 2){ $span = 'a month ago'; }
					else{ $span = $months . ' months ago'; }
					break;
				default:
					$span = date($format, $time);
					break;
			}
		}
		
		return $span;
	}
	
	/**
	 * Convert numerical month to written month (U.S. English)
	 *
	 * @param string|int $num Numerical month (e.g., 01)
	 * @return string|false Written month (e.g., January) or error
	 */
	public function numberToMonth($num){
		$int = intval($num);
		switch($int){
			case 1:
				return 'January';
				break;
			case 2:
				return 'February';
				break;
			case 3:
				return 'March';
				break;
			case 4:
				return 'April';
				break;
			case 5:
				return 'May';
				break;
			case 6:
				return 'June';
				break;
			case 7:
				return 'July';
				break;
			case 8:
				return 'August';
				break;
			case 9:
				return 'September';
				break;
			case 10:
				return 'October';
				break;
			case 11:
				return 'November';
				break;
			case 12:
				return 'December';
				break;
			default:
				return false;
				break;
		}
	}
	
	/**
	 * Convert number to words (U.S. English)
	 *
	 * @param string $num
	 * @param string $power
	 * @param string $powsuffix
	 * @return string
	 */
	public function numberToWords($num, $power = 0, $powsuffix = ''){
		$_minus = 'minus'; // minus sign
		
	    $_exponent = array(
	        0 => array(''),
	        3 => array('thousand'),
	        6 => array('million'),
	        9 => array('billion'),
	       12 => array('trillion'),
	       15 => array('quadrillion'),
	       18 => array('quintillion'),
	       21 => array('sextillion'),
	       24 => array('septillion'),
	       27 => array('octillion'),
	       30 => array('nonillion'),
	       33 => array('decillion'),
	       36 => array('undecillion'),
	       39 => array('duodecillion'),
	       42 => array('tredecillion'),
	       45 => array('quattuordecillion'),
	       48 => array('quindecillion'),
	       51 => array('sexdecillion'),
	       54 => array('septendecillion'),
	       57 => array('octodecillion'),
	       60 => array('novemdecillion'),
	       63 => array('vigintillion'),
	       66 => array('unvigintillion'),
	       69 => array('duovigintillion'),
	       72 => array('trevigintillion'),
	       75 => array('quattuorvigintillion'),
	       78 => array('quinvigintillion'),
	       81 => array('sexvigintillion'),
	       84 => array('septenvigintillion'),
	       87 => array('octovigintillion'),
	       90 => array('novemvigintillion'),
	       93 => array('trigintillion'),
	       96 => array('untrigintillion'),
	       99 => array('duotrigintillion'),
	       // 100 => array('googol') - not latin name
	       // 10^googol = 1 googolplex
	      102 => array('trestrigintillion'),
	      105 => array('quattuortrigintillion'),
	      108 => array('quintrigintillion'),
	      111 => array('sextrigintillion'),
	      114 => array('septentrigintillion'),
	      117 => array('octotrigintillion'),
	      120 => array('novemtrigintillion'),
	      123 => array('quadragintillion'),
	      126 => array('unquadragintillion'),
	      129 => array('duoquadragintillion'),
	      132 => array('trequadragintillion'),
	      135 => array('quattuorquadragintillion'),
	      138 => array('quinquadragintillion'),
	      141 => array('sexquadragintillion'),
	      144 => array('septenquadragintillion'),
	      147 => array('octoquadragintillion'),
	      150 => array('novemquadragintillion'),
	      153 => array('quinquagintillion'),
	      156 => array('unquinquagintillion'),
	      159 => array('duoquinquagintillion'),
	      162 => array('trequinquagintillion'),
	      165 => array('quattuorquinquagintillion'),
	      168 => array('quinquinquagintillion'),
	      171 => array('sexquinquagintillion'),
	      174 => array('septenquinquagintillion'),
	      177 => array('octoquinquagintillion'),
	      180 => array('novemquinquagintillion'),
	      183 => array('sexagintillion'),
	      186 => array('unsexagintillion'),
	      189 => array('duosexagintillion'),
	      192 => array('tresexagintillion'),
	      195 => array('quattuorsexagintillion'),
	      198 => array('quinsexagintillion'),
	      201 => array('sexsexagintillion'),
	      204 => array('septensexagintillion'),
	      207 => array('octosexagintillion'),
	      210 => array('novemsexagintillion'),
	      213 => array('septuagintillion'),
	      216 => array('unseptuagintillion'),
	      219 => array('duoseptuagintillion'),
	      222 => array('treseptuagintillion'),
	      225 => array('quattuorseptuagintillion'),
	      228 => array('quinseptuagintillion'),
	      231 => array('sexseptuagintillion'),
	      234 => array('septenseptuagintillion'),
	      237 => array('octoseptuagintillion'),
	      240 => array('novemseptuagintillion'),
	      243 => array('octogintillion'),
	      246 => array('unoctogintillion'),
	      249 => array('duooctogintillion'),
	      252 => array('treoctogintillion'),
	      255 => array('quattuoroctogintillion'),
	      258 => array('quinoctogintillion'),
	      261 => array('sexoctogintillion'),
	      264 => array('septoctogintillion'),
	      267 => array('octooctogintillion'),
	      270 => array('novemoctogintillion'),
	      273 => array('nonagintillion'),
	      276 => array('unnonagintillion'),
	      279 => array('duononagintillion'),
	      282 => array('trenonagintillion'),
	      285 => array('quattuornonagintillion'),
	      288 => array('quinnonagintillion'),
	      291 => array('sexnonagintillion'),
	      294 => array('septennonagintillion'),
	      297 => array('octononagintillion'),
	      300 => array('novemnonagintillion'),
	      303 => array('centillion'),
	      309 => array('duocentillion'),
	      312 => array('trecentillion'),
	      366 => array('primo-vigesimo-centillion'),
	      402 => array('trestrigintacentillion'),
	      603 => array('ducentillion'),
	      624 => array('septenducentillion'),
	     // bug on a earthlink page: 903 => array('trecentillion'),
	     2421 => array('sexoctingentillion'),
	     3003 => array('millillion'),
	     3000003 => array('milli-millillion')
	        );
		
	    $_digits = array(
	        0 => 'zero', 'one', 'two', 'three', 'four',
	        'five', 'six', 'seven', 'eight', 'nine'
	    );
		
	    $_sep = ' '; // word seperator
	
        $ret = '';

        // add a minus sign
        if(substr($num, 0, 1) == '-'){
            $ret = $_sep . $_minus;
            $num = substr($num, 1);
        }

        // strip excessive zero signs and spaces
        $num = trim($num);
        $num = preg_replace('/^0+/', '', $num);

        if(strlen($num) > 3){
            $maxp = strlen($num)-1;
            $curp = $maxp;
            for($p = $maxp; $p > 0; --$p){ // power
                // check for highest power
                if(isset($_exponent[$p])){
                    // send substr from $curp to $p
                    $snum = substr($num, $maxp - $curp, $curp - $p + 1);
                    $snum = preg_replace('/^0+/', '', $snum);
                    if($snum !== ''){
                        $cursuffix = $_exponent[$power][count($_exponent[$power])-1];
                        if($powsuffix != ''){
                            $cursuffix .= $_sep . $powsuffix;
                        }

                        $ret .= $this->toWords($snum, $p, $cursuffix);
                    }
                    $curp = $p - 1;
                    continue;
                }
            }
            $num = substr($num, $maxp - $curp, $curp - $p + 1);
            if($num == 0){
                return $ret;
            }
        }
		elseif($num == 0 || $num == ''){
            return $_sep . $_digits[0];
        }

        $h = $t = $d = 0;

        switch(strlen($num)){
        case 3:
            $h = (int)substr($num, -3, 1);

        case 2:
            $t = (int)substr($num, -2, 1);

        case 1:
            $d = (int)substr($num, -1, 1);
            break;

        case 0:
            return;
            break;
        }

        if($h){
            $ret .= $_sep . $_digits[$h] . $_sep . 'hundred';

            // in English only - add ' and' for [1-9]01..[1-9]99
            // (also for 1001..1099, 10001..10099 but it is harder)
            // for now it is switched off, maybe some language purists
            // can force me to enable it, or to remove it completely
            // if(($t + $d) > 0)
            //   $ret .= $_sep . 'and';
        }

        // ten, twenty etc.
        switch ($t){
        case 9:
        case 7:
        case 6:
            $ret .= $_sep . $_digits[$t] . 'ty';
            break;

        case 8:
            $ret .= $_sep . 'eighty';
            break;

        case 5:
            $ret .= $_sep . 'fifty';
            break;

        case 4:
            $ret .= $_sep . 'forty';
            break;

        case 3:
            $ret .= $_sep . 'thirty';
            break;

        case 2:
            $ret .= $_sep . 'twenty';
            break;

        case 1:
            switch($d){
            case 0:
                $ret .= $_sep . 'ten';
                break;

            case 1:
                $ret .= $_sep . 'eleven';
                break;

            case 2:
                $ret .= $_sep . 'twelve';
                break;

            case 3:
                $ret .= $_sep . 'thirteen';
                break;

            case 4:
            case 6:
            case 7:
            case 9:
                $ret .= $_sep . $_digits[$d] . 'teen';
                break;

            case 5:
                $ret .= $_sep . 'fifteen';
                break;

            case 8:
                $ret .= $_sep . 'eighteen';
                break;
            }
            break;
        }

        if($t != 1 && $d > 0){ // add digits only in <0>,<1,9> and <21,inf>
            // add minus sign between [2-9] and digit
            if($t > 1){
                $ret .= '-' . $_digits[$d];
            }
			else{
                $ret .= $_sep . $_digits[$d];
            }
        }

        if($power > 0){
            if(isset($_exponent[$power])){
                $lev = $_exponent[$power];
            }

            if(!isset($lev) || !is_array($lev)){
                return null;
            }

            $ret .= $_sep . $lev[0];
        }

        if($powsuffix != ''){
            $ret .= $_sep . $powsuffix;
        }

        return $ret;
    }
	
	// FORMAT STRINGS
	
	/**
	 * Convert to Unicode (UTF-8)
	 *
	 * @param string $string 
	 * @return string
	 */
	public function makeUnicode($string){
		return mb_detect_encoding($string, 'UTF-8') == 'UTF-8' ? $string : utf8_encode($string);
	}
	
	/**
	 * Sanitize table and column names (to prevent SQL injection attacks)
	 *
	 * @param string $string 
	 * @return string
	 */
	public function sanitize($string){
		return preg_replace('#(?:(?![a-z0-9_\.-\s]).)*#si', '', $string);
	}
	
	/**
	 * Make HTML-safe quotations
	 *
	 * @param string $input 
	 * @return string
	 */
	public function makeHTMLSafe($input){
		if(is_string($input)){
			$input = self::makeHTMLSafeHelper($input);
		}
		if(is_array($input)){
			foreach($input as &$value){
				$value = self::makeHTMLSafe($value);
			}
		}
		
		return $input;
	}
	
	private function makeHTMLSafeHelper($string){
		$string = htmlentities($string, ENT_QUOTES, 'UTF-8', false);
		return $string;
	}
	
	/**
	 * Reverse HTML-safe quotations
	 *
	 * @param string $input 
	 * @return string
	 */
	public function reverseHTMLSafe($input){
		if(is_string($input)){
			$input = self::reverseHTMLSafeHelper($input);
		}
		if(is_array($input)){
			foreach($input as &$value){
				$value = self::reverseHTMLSafe($value);
			}
		}
		
		return $input;
	}
	
	private function reverseHTMLSafeHelper($string){
		$string = preg_replace('#\&\#0039\;#s', '\'', $string);	
		$string = preg_replace('#\&\#0034\;#s', '"', $string);
		return $string;
	}
	
	/**
	 * Make a string unique, and filename safe
	 *
	 * @param string $str 
	 * @return string
	 */
	public function makeFilenameSafe($str){
		$data = base64_encode($str);
	    $data = str_replace(array('+','/','='),array('-','_',''), $data);
	    return $data;
	}
	
	/**
	 * Reverse unique string
	 *
	 * @param string $str 
	 * @return string
	 */
	public function reverseFilenameSafe($str) {
	    $data = str_replace(array('-','_'),array('+','/'), $str);
	    $mod4 = strlen($data) % 4;
	    if ($mod4) {
	        $data .= substr('====', $mod4);
	    }
	    return base64_decode($data);
	}
	
	
	/**
	 * Strip tags from string or array
	 *
	 * @param string|array $var
	 * @return string|array
	 */
	public function stripTags($var){
		if(is_string($var)){
			$var = trim(strip_tags($var));
		}
		elseif(is_array($var)){
			foreach($var as $key => $value){
				$var[$key] = self::stripTags($value);
			}
		}
		return $var;
	}
	
	/**
	 * Close open HTML tags
	 *
	 * @param string $html 
	 * @return string
	 */
	public function closeTags($html){
	    preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
	    $openedtags = $result[1];
	    preg_match_all('#</([a-z]+)>#iU', $html, $result);
	    $closedtags = $result[1];
	    $len_opened = count($openedtags);
	    if (count($closedtags) == $len_opened) {
	        return $html;
	    }
	    $openedtags = array_reverse($openedtags);
	    for ($i=0; $i < $len_opened; $i++) {
	        if (!in_array($openedtags[$i], $closedtags)) {
	            $html .= '</'.$openedtags[$i].'>';
	        } else {
	            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
	        }
	    }
	    return $html;
	}
	
	/**
	 * Count the number of words in a string (more reliable than str_word_count();)
	 *
	 * @param string $string 
	 * @return int Word count
	 */
	public function countWords($string){
		$string = strip_tags($string);
		preg_match_all("/\S+/", $string, $matches); 
	    return count($matches[0]);
	}
	
	
	/**
	 * Return random integer using best-available algorithm
	 *
	 * @param string $min 
	 * @param string $max 
	 * @return void
	 */
	public function randInt($min=null, $max=null){
		if(function_exists('mt_rand')){
			if(empty($max)){ $max = mt_getrandmax(); }
			$num = mt_rand($min, $max);
		}
		else{
			if(empty($max)){ $max = getrandmax(); }
			$num = rand($min, $max);
		}
		
		return $num;
	}
	
	/**
	 * Returns the type of the variable as a comparable string
	 *
	 * @param mixed $var Variable
	 * @return string Type of variable
	 */
	function getType($var){
		if(is_array($var)){ return 'array'; }
		if(is_bool($var)){ return 'boolean'; }
		if(is_float($var)){ return 'float'; }
		if(is_int($var)){ return 'integer'; }
		if(is_null($var)){ return 'NULL'; }
		if(is_numeric($var)){ return 'numeric'; }
		if(is_object($var)){ return 'object'; }
		if(is_resource($var)){ return 'resource'; }
		if(is_string($var)){ return 'string'; }
		return 'unknown';
	}
	
	// COMMENTS
	
	/**
	 * Add comments from $_POST data
	 *
	 * @return int|false Comment ID or false on failure
	 */
	public function addComments(){
		// Configuration: comm_enabled
		if(!$this->returnConf('comm_enabled')){
			return false;
		}
		
		if(empty($_POST['image_id']) and empty($_POST['post_id'])){
			return false;
		}
		
		if(!empty($_POST['image_id'])){
			$id = self::findID($_POST['image_id']);
			$id_type = 'image_id';
		}
		elseif(!empty($_POST['post_id'])){
			$id = self::findID($_POST['post_id']);
			$id_type = 'post_id';
		}
		
		// Configuration: comm_mod
		if($this->returnConf('comm_mod')){
			$comment_status = 0;
		}
		else{
			$comment_status = 1;
		}
		
		$comment_text_raw = $_POST['comment_' . $id .'_text'];
		
		if(empty($comment_text_raw)){
			return false;
		}
		
		$orbit = new Orbit;
		
		// Configuration: comm_markup
		if($this->returnConf('comm_markup')){
			$comm_markup_ext = $this->returnConf('comm_markup_ext');
			$comment_text = $orbit->hook('markup_' . $comm_markup_ext, $comment_text_raw, null);
		}
		else{
			$comm_markup_ext = '';
			$comment_text = $this->nl2br($comment_text_raw);
		}
		
		if($this->returnConf('comm_allow_html')){
			$comment_text = strip_tags($comment_text, $this->returnConf('comm_allow_html_tags'));
		}
		else{
			$comment_text = strip_tags($comment_text);
		}
		
		$fields = array($id_type => $id,
			'comment_status' => $comment_status,
			'comment_text' => $this->makeUnicode($comment_text),
			'comment_text_raw' => $this->makeUnicode($comment_text_raw),
			'comment_markup' => $comm_markup_ext,
			'comment_author_name' => strip_tags($_POST['comment_' . $id .'_author_name']),
			'comment_author_uri' => strip_tags($_POST['comment_' . $id .'_author_uri']),
			'comment_author_email' => strip_tags($_POST['comment_' . $id .'_author_email']),
			'comment_author_ip' => $_SERVER['REMOTE_ADDR']);
		
		$fields = $orbit->hook('comment_add', $fields, $fields);
		
		if(!$comment_id = $this->addRow($fields, 'comments')){
			return false;
		}
		
		if($this->returnConf('comm_email')){
			$this->email(0, 'New comment', 'A new comment has been submitted:' . "\r\n\n" . strip_tags($comment_text) . "\r\n\n" . LOCATION . BASE . ADMIN . 'comments' . URL_ID . $comment_id . URL_RW);
		}
		
		if($id_type == 'image_id'){
			$this->updateCount('comments', 'images', 'image_comment_count', $id);
		}
		elseif($id_type == 'post_id'){
			$this->updateCount('comments', 'posts', 'post_comment_count', $id);
		}
		
		return $comment_id;
	}
	
	// TRACKBACKS
	
	/**
	 * Add trackbacks from $_REQUEST data
	 *
	 * @return string XML response
	 */
	public function addTrackbacks(){
		// Configuration: trackback_enabled
		if(!$this->returnConf('trackback_enabled')){
			$xml = '<?xml version="1.0" encoding="utf-8"?>';
			$xml .= '<response>';
			$xml .= '<error>1</error>';
			$xml .= '<message>Trackbacks on this Web site are disabled.</message>';
			$xml .= '</response>';
			return $xml;
		}
		
		if(isset($_REQUEST['id'])){ $id = $this->findID(strip_tags($_REQUEST['id'])); }
		if(isset($_REQUEST['title'])){ $title = strip_tags($_REQUEST['title']); }
		if(isset($_REQUEST['excerpt'])){ $excerpt = strip_tags($_REQUEST['excerpt']); }
		if(isset($_REQUEST['url'])){ $uri = strip_tags($_REQUEST['url']); }
		if(isset($_REQUEST['blog_name'])){ $blog_name = strip_tags($_REQUEST['blog_name']); }
		
		if(empty($uri)){
			$xml = '<?xml version="1.0" encoding="utf-8"?>';
			$xml .= '<response>';
			$xml .= '<error>1</error>';
			$xml .= '<message>No URL sent.</message>';
			$xml .= '</response>';
			return $xml;
		}
		
		if(empty($id)){
			$xml = '<?xml version="1.0" encoding="utf-8"?>';
			$xml .= '<response>';
			$xml .= '<error>1</error>';
			$xml .= '<message>No post ID sent.</message>';
			$xml .= '</response>';
			return $xml;
		}
		
		// Get favicon
		$domain = $this->siftDomain($uri);
		
		$ico_file = PATH . CACHE . 'favicons/' . $this->makeFilenameSafe($domain) . '.ico';
		$png_file = PATH . CACHE . 'favicons/' . $this->makeFilenameSafe($domain) . '.png';
		
		if(!file_exists($png_file)){
			if(!file_exists(PATH . CACHE . 'favicons/')){
				@mkdir(PATH . CACHE . 'favicons/', 0777, true);
			}
			
			ini_set('default_socket_timeout', 1);
			$favicon = @file_get_contents('http://www.google.com/s2/u/0/favicons?domain=' . $domain);
			ini_restore('default_socket_timeout');
			
			$favicon = imagecreatefromstring($favicon);
			imagealphablending($favicon, false);
			imagesavealpha($favicon, true);
			imagepng($favicon, $png_file);
			imagedestroy($favicon);
		}
		
		// Check if duplicate
		$query = $this->prepare('SELECT * FROM trackbacks WHERE post_id = :post_id AND trackback_uri = :trackback_uri;');
		
		if(!$query->execute(array(':post_id' => $id, ':trackback_uri' => $uri))){
			$xml = '<?xml version="1.0" encoding="utf-8"?>';
			$xml .= '<response>';
			$xml .= '<error>1</error>';
			$xml .= '<message>Internal server error.</message>';
			$xml .= '</response>';
			return $xml;
		}
		
		$trackbacks = $query->fetchAll();
		
		if(count($trackbacks) > 0){
			$xml = '<?xml version="1.0" encoding="utf-8"?>';
			$xml .= '<response>';
			$xml .= '<error>1</error>';
			$xml .= '<message>Duplicate submission.</message>';
			$xml .= '</response>';
			return $xml;
		}
		
		// Store to database
		$fields = array('post_id' => $id,
			'trackback_title' => $this->makeUnicode($title),
			'trackback_uri' => $uri,
			'trackback_excerpt' => $this->makeUnicode($excerpt),
			'trackback_blog_name' => $this->makeUnicode($blog_name),
			'trackback_ip' => $_SERVER['REMOTE_ADDR']);
		
		if(!$trackback_id = $this->addRow($fields, 'trackbacks')){
			$xml = '<?xml version="1.0" encoding="utf-8"?>';
			$xml .= '<response>';
			$xml .= '<error>1</error>';
			$xml .= '<message>Internal server error.</message>';
			$xml .= '</response>';
			return $xml;
		}
		
		if($this->returnConf('trackback_email')){
			$this->email(0, 'New trackback', 'A new trackback has been submitted:' . "\r\n\n" . $uri . "\r\n\n" . LOCATION . BASE . ADMIN . 'posts' . URL_ID . $id . URL_RW);
		}
		
		$this->updateCount('trackbacks', 'posts', 'post_trackback_count', $id);
		
		// If no errors
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<response>';
		$xml .= '<error>0</error>';
		$xml .= '</response>';
		
		return $xml;
	}
	
	// VERSIONS
	
	/**
	 * Revert to title and text of a previous version
	 *
	 * @param int $version_id 
	 * @return bool True if successful
	 */
	public function revertVersion($version_id){
		if(empty($version_id)){ return false; }
		if(!$version_id = intval($version_id)){ return false; }
		
		$version = $this->getRow('versions', $version_id);
		
		if(empty($version)){ return false; }
		
		if(!empty($version['post_id'])){
			$post = new Post($version['post_id']);
			$fields = array('post_title' => $version['version_title'],
				'post_text_raw' => $version['version_text_raw']);
			return $post->updateFields($fields, null, false);
		}
		elseif(!empty($version['page_id'])){
			$page = new Page($version['page_id']);
			$fields = array('page_title' => $version['version_title'],
				'page_text_raw' => $version['version_text_raw']);
			return $page->updateFields($fields, null, false);
		}
	}
	
	// TABLE COUNTING
	
	/**
	 * Update count of single field
	 *
	 * @param string $count_table 
	 * @param string $result_table 
	 * @param string $result_field 
	 * @param string $result_id 
	 * @return bool True if successful
	 */
	public function updateCount($count_table, $result_table, $result_field, $result_id){
		$result_id = intval($result_id);
		
		$count_table = $this->sanitize($count_table);
		$result_table = $this->sanitize($result_table);
		
		$count_id_field = $this->tables[$count_table];
		$result_id_field = $this->tables[$result_table];
		
		// Get count
		$query = $this->prepare('SELECT COUNT(' . $count_id_field . ') AS count FROM ' . $count_table . ' WHERE ' . $result_id_field  . ' = :result_id AND ' . substr($count_id_field, 0, -2) . 'deleted IS NULL;');
		
		if(!$query->execute(array(':result_id' => $result_id))){
			return false;
		}
		
		$counts = $query->fetchAll();
		$count = $counts[0]['count'];
		
		// Update row
		$query = $this->prepare('UPDATE ' . $result_table . ' SET ' . $result_field . ' = :count WHERE ' . $result_id_field . ' = :result_id;');
		
		if(!$query->execute(array(':count' => $count, ':result_id' => $result_id))){
			return false;
		}
		
		return true;
	}
	
	/**
	 * Update count of entire column
	 *
	 * @param string $count_table 
	 * @param string $result_table 
	 * @param string $result_field 
	 * @return bool True if successful
	 */
	public function updateCounts($count_table, $result_table, $result_field){
		$count_table = $this->sanitize($count_table);
		$result_table = $this->sanitize($result_table);
		
		$count_id_field = $this->tables[$count_table];
		$result_id_field = $this->tables[$result_table];
		
		$results = $this->getTable($result_table);
		
		// Get count
		$select = $this->prepare('SELECT COUNT(' . $count_id_field . ') AS count FROM ' . $count_table . ' WHERE ' . $result_id_field  . ' = :result_id AND ' . substr($count_id_field, 0, -2) . 'deleted IS NULL;');
		
		// Update row
		$update = $this->prepare('UPDATE ' . $result_table . ' SET ' . $result_field . ' = :count WHERE ' . $result_id_field . ' = :result_id;');
		
		foreach($results as $result){
			$result_id = $result[$result_id_field];
			if(!$select->execute(array(':result_id' => $result_id))){
				return false;
			}
		
			$counts = $select->fetchAll();
			$count = $counts[0]['count'];
		
			if(!$update->execute(array(':count' => $count, ':result_id' => $result_id))){
				return false;
			}
		}
		
		return true;
	}
	
	// RETRIEVE LIBRARY DATA
	
	/**
	 * Get all table row counts
	 *
	 * @return array Tables and their row counts
	 */
	public function getInfo(){
		$info = array();
		
		// Get tables
		$tables = $this->tables;
		
		// Exclude tables
		unset($tables['rights']);
		unset($tables['exifs']);
		unset($tables['extensions']);
		unset($tables['themes']);
		unset($tables['sizes']);
		unset($tables['rights']);
		unset($tables['versions']);
		unset($tables['citations']);
		unset($tables['items']);
		unset($tables['trackbacks']);
		
		// Run helper function
		foreach($tables as $table => $selector){
			$info[] = array('table' => $table, 'count' => self::countTable($table));
		}
		
		foreach($info as &$table){
			if($table['count'] == 1){
				$table['display'] = preg_replace('#s$#si', '', $table['table']);
			}
			else{
				$table['display'] = $table['table'];
			}
		}
		
		return $info;
	}
	
	/**
	 * Get FSIP Dashboard header badges
	 *
	 * @return array Associate array of fields and integers
	 */
	public function getBadges(){
		$badges = array();
		
		$badges['images'] = $this->countDirectory(PATH . SHOEBOX);
		$badges['posts'] = $this->countDirectory(PATH . SHOEBOX, 'txt|mdown|md|markdown|textile');

		$comment_ids = new Find('comments');
		$comment_ids->status(0);
		$comment_ids->find();
		
		$badges['comments'] = $comment_ids->count;
		
		return $badges;
	}
	
	/**
	 * Get array of tags
	 *
	 * @param bool $show_hidden_tags Include hidden tags
	 * @return array Associative array of tags
	 */
	public function getTags($show_hidden_tags=false, $published_only=false, $public_only=false){
		$sql = '';
		
		if($published_only === true){
			$sql .= ' AND images.image_published <= "' . date('Y-m-d H:i:s') . '"';
		}
		
		if($public_only === true){
			$sql .= ' AND images.image_privacy = 1';
		}
	
		if($this->returnConf('tag_alpha')){
			$query = $this->prepare('SELECT tags.tag_name, tags.tag_id, images.image_id FROM tags, links, images WHERE tags.tag_id = links.tag_id AND links.image_id = images.image_id AND images.image_deleted IS NULL ' . $sql . ' ORDER BY tags.tag_name;');
		}
		else{
			$query = $this->prepare('SELECT tags.tag_name, tags.tag_id, images.image_id FROM tags, links, images WHERE tags.tag_id = links.tag_id AND links.image_id = images.image_id AND images.image_deleted IS NULL ' . $sql . ' ORDER BY tags.tag_id ASC;');
		}
		$query->execute();
		$tags = $query->fetchAll();
		
		if($show_hidden_tags !== true){
			$tags_new = array();
			foreach($tags as $tag){
				if($tag['tag_name'][0] != '!'){
					$tags_new[] = $tag;
				}
			}
			$tags = $tags_new;
		}
		
		$tag_ids = array();
		$tag_names = array();
		$tag_counts = array();
		$tag_uniques = array();
		
		foreach($tags as $tag){
			$tag_names[] = $tag['tag_name'];
			$tag_ids[$tag['tag_name']] = $tag['tag_id'];
		}
		
		$tag_counts = array_count_values($tag_names);
		$tag_count_values = array_values($tag_counts);
		$tag_count_high = 0;
		
		foreach($tag_count_values as $value){
			if($value > $tag_count_high){
				$tag_count_high = $value;
			}
		}
		
		$tag_uniques = array_unique($tag_names);
		$tags = array();
		
		foreach($tag_uniques as $tag){
			$tags[] = array('id' => $tag_ids[$tag],
				'size' => round(((($tag_counts[$tag] - 1) * 3) / $tag_count_high) + 1, 2),
				'name' => $tag,
				'count' => $tag_counts[$tag]);
		}
		
		return $tags;
	}
	
	/**
	 * Load a citation
	 *
	 * @param string $uri URI of citation
	 * @param string $field Field for ID entry
	 * @param int $field_id ID to enter
	 * @return array Associative array of newly created citation row
	 */
	public function loadCitation($uri, $field, $field_id){
		if((strpos($uri, 'http://') !== 0) and (strpos($uri, 'https://') !== 0)){ return false; }
		
		// Check if exists
		$sql = 'SELECT * FROM citations WHERE citation_uri_requested = :citation_uri_requested';
		
		$query = $this->prepare($sql);
		$query->execute(array(':citation_uri_requested' => $uri));
		$citations = $query->fetchAll();
		
		foreach($citations as $citation){
			if($citation[$field] == $field_id){ return $citation; }
		}
		
		$domain = $this->siftDomain($uri);
		
		$ico_file = PATH . CACHE . 'favicons/' . $this->makeFilenameSafe($domain) . '.ico';
		$png_file = PATH . CACHE . 'favicons/' . $this->makeFilenameSafe($domain) . '.png';
		
		if(count($citations) == 0){
			ini_set('default_socket_timeout', 1);
			$html = @file_get_contents($uri, null, null, 0, 7500);
			ini_restore('default_socket_timeout');
			
			if($html == false){ return false; }
			if(!preg_match('#Content-Type:\s*text/html#si', implode(' ', $http_response_header))){ return false; }
			
			if(!file_exists($png_file)){
				if(!file_exists(PATH . CACHE . 'favicons/')){
					@mkdir(PATH . CACHE . 'favicons/', 0777, true);
				}
				
				ini_set('default_socket_timeout', 1);
				$favicon = @file_get_contents('http://www.google.com/s2/u/0/favicons?domain=' . $domain);
				ini_restore('default_socket_timeout');
				
				$favicon = imagecreatefromstring($favicon);
				imagealphablending($favicon, false);
				imagesavealpha($favicon, true);
				imagepng($favicon, $png_file);
				imagedestroy($favicon);
				
				/*
			
				preg_match('#<link[^>]*rel="shortcut icon"[^>]*href="([^>]*)"[^>]*>#si', $html, $match);
				preg_match('#<link[^>]*href="([^>]*)"[^>]*rel="shortcut icon"[^>]*>#si', $html, $match2);
				
				if(isset($match[1])){
					$favicon_uri = $match[1];
				}
				elseif(isset($match2[1])){
					$favicon_uri = $match2[1];
				}
				else{
					$favicon_uri = 'http://' . $domain . '/favicon.ico';
				}
			
				if($favicon_uri[0] == '/'){
					$favicon_uri = 'http://' . $domain . $favicon_uri;
				}
			
				@copy($favicon_uri, $ico_file);
			
				if(file_exists($ico_file)){
					$thumbnail = new Thumbnail($ico_file);
					$thumbnail->resize(16, 16);
					$thumbnail->save($png_file);
					
					//require_once(PATH . CLASSES . 'ico/ico.php');
					//
					// $ico = new Ico($ico_file);
					// $favicon = $ico->GetIcon(0);
					// if($favicon != false){
					// 	imagepng($favicon, $png_file);
					// 	imagedestroy($favicon);
					// }
					// @unlink($ico_file);
				}
				*/
			}
		
			preg_match_all('#<meta.*?>#', $html, $metas);
		
			$html5_meta = array();
		
			foreach($metas[0] as $meta){
				if(preg_match('#property="og:(.*?)"#si', $meta, $property)){
					preg_match('#content="(.*?)"#si', $meta, $content);
					$html5_meta[$property[1]] = $content[1];
				}
			}
		
			$save_fields = array('url', 'description', 'title', 'site_name');
			$fields = array('citation_uri_requested' => $uri,
				$field => $field_id);
		
			foreach($html5_meta as $property => $content){
				if(in_array($property, $save_fields)){
					if($property == 'url'){ $property = 'uri'; }
					$field = 'citation_' . $property;
					$fields[$field] = $this->makeUnicode(html_entity_decode($content, ENT_QUOTES, 'UTF-8'));
				}
			}
			
			if(empty($fields['citation_title'])){
				preg_match('#<title>(.*?)</title>#si', $html, $match);
				$fields['citation_title'] = $match[1];
			}
			
			if(empty($fields['citation_description'])){
				preg_match('#<meta[^>]*name="description"[^>]*content="([^>]*)"[^>]*>#si', $html, $match);
				if(empty($match[1])){
					preg_match('#<meta[^>]*content="([^>]*)"[^>]*name="description"[^>]*>#si', $html, $match);
				}
				
				if(!empty($match[1])){
					$fields['citation_description'] = $match[1];
				}
			}
		}
		else{
			$fields = array();
			
			foreach($citations[0] as $key => $value){
				if(is_int($key)){ continue; }
				$fields[$key] = $value;
			}
			
			unset($fields['citation_id']);
			$fields[$field] = $field_id;
			
			if(file_exists(PATH . CACHE . 'favicons/' . $this->makeFilenameSafe($domain) . '.png')){
				$favicon_found = true;
			}
		}
		
		$fields['citation_id'] = $this->addRow($fields, 'citations');
		
		if(empty($fields['citation_site_name'])){
			$fields['citation_site_name'] = $domain;
		}
		
		if(file_exists($png_file)){
			$fields['citation_favicon_uri'] = LOCATION . BASE . CACHE . 'favicons/' . $this->makeFilenameSafe($domain) . '.png';
		}
		
		return $fields;
	}
	
	/**
	 * List tags by search, for suggestions
	 *
	 * @param string $hint Search string
	 * @return array
	 */
	public function hintTag($hint){
		$hint_lower = strtolower($hint);
		
		$sql = 'SELECT DISTINCT(tags.tag_name) FROM tags WHERE LOWER(tags.tag_name) LIKE :hint_lower ORDER BY tags.tag_name ASC';
		
		$query = $this->prepare($sql);
		$query->execute(array(':hint_lower' => $hint_lower . '%'));
		$tags = $query->fetchAll();
		
		$tags_list = array();
		
		foreach($tags as $tag){
			$tags_list[] = $tag['tag_name'];
		}
		
		return $tags_list;
	}
	
	/**
	 * List page category by search, for suggestions
	 *
	 * @param string $hint Search string
	 * @return array
	 */
	public function hintPostCategory($hint){
		$hint_lower = strtolower($hint);
		
		if(!empty($hint)){
			$sql = 'SELECT DISTINCT(posts.post_category) FROM posts WHERE LOWER(posts.post_category) LIKE :hint_lower ORDER BY posts.post_category ASC';
		}
		else{
			$sql = 'SELECT DISTINCT(posts.post_category) FROM posts ORDER BY posts.post_category ASC';
		}
		
		$query = $this->prepare($sql);
		$query->execute(array(':hint_lower' => $hint_lower . '%'));
		$posts = $query->fetchAll();
		
		$categories_list = array();
		
		foreach($posts as $post){
			$categories_list[] = $post['post_category'];
		}
		
		return $categories_list;
	}
	
	/**
	 * List category by search, for suggestions
	 *
	 * @param string $hint Search string
	 * @return array
	 */
	public function hintPageCategory($hint){
		$hint_lower = strtolower($hint);
		
		if(!empty($hint)){
			$sql = 'SELECT DISTINCT(pages.page_category) FROM pages WHERE LOWER(pages.page_category) LIKE :hint_lower ORDER BY pages.page_category ASC';
		}
		else{
			$sql = 'SELECT DISTINCT(posts.post_category) FROM posts ORDER BY posts.post_category ASC';
		}
		
		$query = $this->prepare($sql);
		$query->execute(array(':hint_lower' => $hint_lower . '%'));
		$pages = $query->fetchAll();
		
		$categories_list = array();
		
		foreach($pages as $page){
			$categories_list[] = $page['page_category'];
		}
		
		return $categories_list;
	}
	
	
	/**
	 * Get array of all includes
	 *
	 * @return array Array of includes
	 */
	public function getIncludes(){
		$includes = self::seekDirectory(PATH . INCLUDES, '.*');
		
		foreach($includes as &$include){
			$include = self::getFilename($include);
		}
		
		return $includes;
	}
	
	/**
	 * Get HTML <select> of all rights
	 *
	 * @param string $name Name and ID of <select>
	 * @param integer $right_id Default or selected right_id
	 * @return string
	 */
	public function showRights($name, $right_id=null){
		if(empty($name)){
			return false;
		}
		
		$query = $this->prepare('SELECT right_id, right_title FROM rights WHERE right_deleted IS NULL;');
		$query->execute();
		$rights = $query->fetchAll();
		
		$html = '<select name="' . $name . '" id="' . $name . '"><option value=""></option>';
		
		foreach($rights as $right){
			$html .= '<option value="' . $right['right_id'] . '"';
			if($right['right_id'] == $right_id){
				$html .= ' selected="selected"';
			}
			$html .= '>' . $right['right_title'] . '</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	/**
	 * Get HTML <select> of all sizes
	 *
	 * @param string $name Name and ID of <select>
	 * @param integer $size_id Default or selected size_id
	 * @return string
	 */
	public function showSizes($name, $size_id=null){
		if(empty($name)){
			return false;
		}
		
		$query = $this->prepare('SELECT size_id, size_title FROM sizes;');
		$query->execute();
		$sizes = $query->fetchAll();
		
		$html = '<select name="' . $name . '" id="' . $name . '">';
		
		foreach($sizes as $size){
			$html .= '<option value="' . $size['size_id'] . '"';
			if($size['size_id'] == $size_id){
				$html .= ' selected="selected"';
			}
			$html .= '>' . $size['size_title'] . '</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	/**
	 * Get HTML <select> of all privacy levels
	 *
	 * @param string $name Name and ID of <select>
	 * @param integer $privacy_id Default or selected privacy_id
	 * @return string
	 */
	public function showPrivacy($name, $privacy_id=1){
		if(empty($name)){
			return false;
		}
		
		$privacy_levels = array(1 => 'Public', 2 => 'Protected', 3 => 'Private');
		
		$html = '<select name="' . $name . '" id="' . $name . '">';
		
		foreach($privacy_levels as $privacy_level => $privacy_label){
			$html .= '<option value="' . $privacy_level . '"';
			if($privacy_level == $privacy_id){
				$html .= ' selected="selected"';
			}
			$html .= '>' . $privacy_label . '</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	
	
	/**
	 * Get HTML <select> of all sets
	 *
	 * @param string $name Name and ID of <select>
	 * @param integer $set_id Default or selected set_id
	 * @param bool $static_only Display on static sets
	 * @return string
	 */
	public function showSets($name, $set_id=null, $static_only=false){
		if(empty($name)){
			return false;
		}
		
		if($static_only === true){
			$query = $this->prepare('SELECT set_id, set_title FROM sets WHERE set_type = :set_type AND set_deleted IS NULL;');
			$query->execute(array(':set_type' => 'static'));
		}
		else{
			$query = $this->prepare('SELECT set_id, set_title FROM sets WHERE set_deleted IS NULL;');
			$query->execute();
		}
		$sets = $query->fetchAll();
		
		$html = '<select name="' . $name . '" id="' . $name . '">';
		
		foreach($sets as $set){
			$html .= '<option value="' . $set['set_id'] . '"';
			if($set['set_id'] == $set_id){
				$html .= ' selected="selected"';
			}
			$html .= '>' . $set['set_title'] . '</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	/**
	 * Get HTML <select> of all themes
	 *
	 * @param string $name Name and ID of <select>
	 * @param integer $theme_id Default or selected theme_id
	 * @return string
	 */
	public function showThemes($name, $theme_id=null){
		if(empty($name)){
			return false;
		}
		
		$query = $this->prepare('SELECT theme_id, theme_title FROM themes;');
		$query->execute();
		$themes = $query->fetchAll();
		
		$html = '<select name="' . $name . '" id="' . $name . '">';
		
		foreach($themes as $theme){
			$html .= '<option value="' . $theme['theme_id'] . '"';
			if($theme['theme_id'] == $theme_id){
				$html .= ' selected="selected"';
			}
			$html .= '>' . $theme['theme_title'] . '</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	/**
	 * Get HTML <select> of all EXIF names
	 *
	 * @param string $name Name and ID of <select>
	 * @param integer $exif_name Default or selected exif_name
	 * @return string
	 */
	public function showEXIFNames($name, $exif_name=null){
		if(empty($name)){
			return false;
		}
		
		$query = $this->prepare('SELECT DISTINCT exif_name FROM exifs ORDER BY exif_name ASC;');
		$query->execute();
		$exifs = $query->fetchAll();
		
		$html = '<select name="' . $name . '" id="' . $name . '"><option value=""></option>';
		
		foreach($exifs as $exif){
			$html .= '<option value="' . $exif['exif_name'] . '"';
			if($exif['exif_name'] == $exif_name){
				$html .= ' selected="selected"';
			}
			$html .= '>' . $exif['exif_name'] . '</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	
	// TABLE AND ROW MANIPULATION
	
	/**
	 * Get table
	 *
	 * @param string $table Table name
	 * @param string|int|array $ids Row IDs
	 * @param string $limit
	 * @param string $page 
	 * @param string $order_by 
	 * @return array
	 */
	public function getTable($table, $ids=null, $limit=null, $page=1, $order_by=null){
		if(empty($table)){
			return false;
		}
		if(!is_int($page) or ($page < 1)){
			$page = 1;
		}
		
		$table = $this->sanitize($table);
		
		$sql_params = array();
		
		$order_by_sql = '';
		$limit_sql = '';
		
		if(!empty($order_by)){
			if(is_string($order_by)){
				$order_by = $this->sanitize($order_by);
				$order_by_sql = ' ORDER BY ' . $order_by;
			}
			elseif(is_array($order_by)){
				foreach($order_by as &$by){
					$by = $this->sanitize($by);
				}
				$order_by_sql = ' ORDER BY ' . implode(', ', $order_by);
			}
		}
		
		if(!empty($limit)){
			$limit = intval($limit);
			$page = intval($page);
			$limit_sql = ' LIMIT ' . ($limit * ($page - 1)) . ', ' . $limit;
		}
		
		if(empty($ids)){
			$query = $this->prepare('SELECT * FROM ' . $table . $order_by_sql . $limit_sql . ';');
		}
		else{
			$ids = self::convertToIntegerArray($ids);
			$field = $this->tables[$table];
			
			$query = $this->prepare('SELECT * FROM ' . $table . ' WHERE (' . $field . ' IN (' . implode(', ', $ids) . '))' . $order_by_sql . $limit_sql . ';');
		}
		
		$query->execute($sql_params);
		$contents = $query->fetchAll();
		
		// Delete extra users on standard licenses
		if(($table == 'users') and (count($contents) > 1)){
			$this->deleteDisallowedUsers();
		}
		
		$contents_ordered = array();
		
		if(!empty($ids)){
			// Ensure posts array correlates to post_ids array
			foreach($ids as $id){
				foreach($contents as $content){
					if($id == $content[$field]){
						$contents_ordered[] = $content;
					}
				}
			}
			$contents = $contents_ordered;
		}
		
		return $contents;
	}
	
	/**
	 * Get row
	 *
	 * @param string $table Table name
	 * @param string|int $id Row ID
	 * @return array
	 */
	public function getRow($table, $id){
		// Error checking
		if(empty($id)){ return false; }
		if(!($id = intval($id))){ return false; }
		
		$table = $this->getTable($table, $id);
		if(count($table) != 1){ return false; }
		return $table[0];
	}
	
	/**
	 * Add row (includes updating default fields)
	 *
	 * @param array $fields Associative array of key (column) and value (field)
	 * @param string $table Table name
	 * @return int|false Row ID or error
	 */
	public function addRow($fields=null, $table){
		// Error checking
		if(empty($table) or (!is_array($fields) and isset($fields))){
			return false;
		}
		
		if(empty($fields)){
			$fields = array();
		}
		
		$table = $this->sanitize($table);
		$now = date('Y-m-d H:i:s');
		
		// Add default fields
		switch($table){
			case 'comments':
				if(empty($fields['comment_created'])){ $fields['comment_created'] = $now; }
				if(empty($fields['comment_modified'])){ $fields['comment_modified'] = $now; }
				break;
			case 'guests':
				if(empty($fields['guest_views'])){ $fields['guest_views'] = 0; }
				if(empty($fields['guest_created'])){ $fields['guest_created'] = $now; }
				break;
			case 'rights':
				if(empty($fields['right_created'])){ $fields['right_created'] = $now; }
				if(empty($fields['right_modified'])){ $fields['right_modified'] = $now; }
				break;
			case 'pages':
				if(empty($fields['page_views'])){ $fields['page_views'] = 0; }
				if(empty($fields['page_created'])){ $fields['page_created'] = $now; }
				if(empty($fields['page_modified'])){ $fields['page_modified'] = $now; }
				break;
			case 'posts':
				if(empty($fields['post_views'])){ $fields['post_views'] = 0; }
				if(empty($fields['post_created'])){ $fields['post_created'] = $now; }
				if(empty($fields['post_modified'])){ $fields['post_modified'] = $now; }
				break;
			case 'citations':
				if(empty($fields['citation_created'])){ $fields['citation_created'] = $now; }
				if(empty($fields['citation_modified'])){ $fields['citation_modified'] = $now; }
				break;
			case 'sets':
				if(empty($fields['set_views'])){ $fields['set_views'] = 0; }
				if(empty($fields['set_created'])){ $fields['set_created'] = $now; }
				if(empty($fields['set_modified'])){ $fields['set_modified'] = $now; }
				break;
			case 'sizes':
				if(!isset($fields['size_title'])){ $fields['size_title'] = ''; }
				break;
			case 'trackbacks':
				if(empty($fields['trackback_created'])){ $fields['trackback_created'] = $now; }
				break;
			case 'users':
				if(empty($fields['user_created'])){ $fields['user_created'] = $now; }
				break;
			default:
				break;
		}
		
		$field = $this->tables[$table];
		unset($fields[$field]);
		
		if(count($fields) > 0){
			$columns = array_keys($fields);
			$values = array_values($fields);
		
			$value_slots = array_fill(0, count($values), '?');
		
			// Add row to database
			$query = $this->prepare('INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $value_slots) . ');');
		}
		else{
			$values = array();
			$query = $this->prepare('INSERT INTO ' . $table . ' (' . $this->tables[$table] . ') VALUES (?);');
			$values = array(PDO::PARAM_NULL);
		}
		
		if(!$query->execute($values)){
			return false;
		}
		
		// Return ID
		$id = intval($this->db->lastInsertId(TABLE_PREFIX . $table . '_' . $field . '_seq'));
		
		if($id == 0){
			return false;
		}
		
		return $id;
	}
	
	/**
	 * Update row
	 *
	 * @param string $fields Associative array of key (column) and value (field)
	 * @param string $table Table name
	 * @param string|array $ids Row IDs
	 * @param string $default Include default fields (e.g., update modified dates)
	 * @return bool True if successful
	 */
	public function updateRow($fields, $table, $ids=null, $default=true){
		// Error checking
		if(empty($fields) or empty($table) or !is_array($fields)){
			return false;
		}
		
		$table = $this->sanitize($table);
		
		$ids = self::convertToIntegerArray($ids);
		$field = $this->tables[$table];
		$now = date('Y-m-d H:i:s');
		
		// Add default fields
		if($default === true){
			switch($table){
				case 'images':
					$fields['image_modified'] = $now;
					break;
				case 'comments':
					$fields['comment_modified'] = $now;
					break;
				case 'rights':
					$fields['right_modified'] = $now;
					break;
				case 'citations':
					$fields['citation_modified'] = $now;
				case 'sets':
					$fields['set_modified'] = $now;
					break;
				case 'pages':
					$fields['page_modified'] = $now;
					break;
				case 'posts':
					$fields['post_modified'] = $now;
					break;
			}
		}
		
		$columns = array_keys($fields);
		$values = array_values($fields);

		// Add row to database
		$query = $this->prepare('UPDATE ' . $table . ' SET ' . implode(' = ?, ', $columns) . ' = ? WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . ';');
		if(!$query->execute($values)){
			return false;
		}
		
		return true;
	}
	
	/**
	 * Delete row
	 *
	 * @param string $table Table name
	 * @param string|int|array $ids Row IDs
	 * @return bool True if successful
	 */
	public function deleteRow($table, $ids=null){
		if(empty($table) or empty($ids)){
			return false;
		}
		
		$table = $this->sanitize($table);
		
		$ids = self::convertToIntegerArray($ids);
		$field = $this->tables[$table];
		
		// Delete row
		$query = 'DELETE FROM ' . $table . ' WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . ';';
		
		if(!$this->exec($query)){
			return false;
		}
		
		return true;
	}
	
	/**
	 * Delete empty rows
	 *
	 * @param string $table Table name
	 * @param string|array $fields Fields to check for empty values (if any are empty, deletion will occur) 
	 * @return bool True if successful
	 */
	public function deleteEmptyRow($table, $fields){
		if(empty($table) or empty($fields)){
			return false;
		}
		
		$table = $this->sanitize($table);
		
		$fields = self::convertToArray($fields);
		
		$conditions = array();
		foreach($fields as $field){
			$conditions[] = '(' . $field . ' = ? OR ' . $field . ' IS NULL)';
		}
		
		$sql_params = array_fill(0, count($fields), '');
		
		// Delete empty rows
		$query = $this->prepare('DELETE FROM ' . $table . ' WHERE ' . implode(' OR ', $conditions) . ';');
		
		if(!$query->execute($sql_params)){
			return false;
		}
		
		return true;
	}
	
	/**
	 * Count table rows
	 *
	 * @param string $table Table name
	 * @return int Number of rows
	 */
	function countTable($table){
		$table = $this->sanitize($table);
		
		$field = $this->tables[$table];
		if (empty($field)) { return false; }
		
		$sql = '';
		
		// Don't show deleted items
		$with_deleted_columns = array('images', 'posts', 'comments', 'sets', 'pages', 'rights');
		if (in_array($table, $with_deleted_columns)) {
			$show_deleted = false;
			if($this->adminpath === true) {
				$user = new User();
				if(!empty($user) and $user->perm()){
					if($user->returnPref('recovery_mode') === true){
						$show_deleted = true;
					}
				}
			}
			
			if ($show_deleted === false){
				$sql = ' WHERE ' . $table . '.' . substr($field, 0, -2) . 'deleted IS NULL';
			}
		}
		
		$query = $this->prepare('SELECT COUNT(' . $table . '.' . $field . ') AS count FROM ' . $table . $sql . ';');
		$query->execute();
		$count = $query->fetch();
		
		$count = intval($count['count']);
		return $count;
	}
	
	// RECORD STATISTIC
	// Record a visitor to statistics
	public function recordStat($page_type=null) {
		if (!$this->returnConf('stat_enabled')) {
			return false;
		}
		
		if ($this->returnConf('stat_ignore_user')) {
			$user = new User();
			if($user->perm(false)){
				return;
			}
		}
		
		if (empty($_SESSION['fsip']['duration_start']) or ((time() - @$_SESSION['fsip']['duration_recent']) > 3600)) {
			$duration = 0;
			$_SESSION['fsip']['duration_start'] = time();
		} else{
			$duration = time() - $_SESSION['fsip']['duration_start'];
		}
		
		// Ignore bots
		if (stripos($_SERVER['HTTP_USER_AGENT'], 'bot') !== false) { return; }
		if (stripos($_SERVER['HTTP_USER_AGENT'], 'spider') !== false) { return; }
		if (stripos($_SERVER['HTTP_USER_AGENT'], 'slurp') !== false) { return; }
		if (stripos($_SERVER['HTTP_USER_AGENT'], 'crawl') !== false) { return; }
		
		$_SESSION['fsip']['duration_recent'] = time();
		
		$referrer = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;
		$page = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : null;
		
		if (stripos($referrer, LOCATION . BASE) === false) {
			$local = 0;
		} else{
			$local = 1;
		}
		
		if ((BASE != '/') and (stripos($page, BASE) === 0)) {
			$page = substr($page, strlen(BASE) - 1);
		}
		
		$query = $this->prepare('INSERT INTO stats (stat_session, stat_date, stat_duration, stat_referrer, stat_page, stat_page_type, stat_local) VALUES (:stat_session, :stat_date, :stat_duration, :stat_referrer, :stat_page, :stat_page_type, :stat_local);');
		
		$query->execute(array(':stat_session' => session_id(), ':stat_date' => date('Y-m-d H:i:s'), ':stat_duration' => $duration, ':stat_referrer' => $referrer, ':stat_page' => $page, ':stat_page_type' => $page_type, ':stat_local' => $local));
		
		if (isset($_SESSION['fsip']['guest'])) {
			$_SESSION['fsip']['guest']['guest_views']++;
			$this->exec('UPDATE guests SET guest_views = ' . $_SESSION['fsip']['guest']['guest_views'] . ' WHERE guest_id = ' . $_SESSION['fsip']['guest']['guest_id'] . ';');
		}
	}
	
	// FORM HANDLING
	
	/**
	 * Set form option
	 *
	 * @param string $array 
	 * @param string $name 
	 * @param string $unset 
	 * @return void
	 */
	public function setForm(&$array, $name, $unset='') {
		if (isset($_POST[$name])) {
			$value = $_POST[$name];
			if (empty($value)) {
				$array[$name] = '';
			} elseif($value == 'true') {
				$array[$name] = true;
			} else {
				$array[$name] = $value;
			}
		}
		else{
			$array[$name] = $unset;
		}
	}
	
	/**
	 * Retrieve HTML-formatted form option
	 *
	 * @param string $array 
	 * @param string $name 
	 * @param string $check 
	 * @return string
	 */
	public function readForm($array=null, $name, $check=true) {
		if (is_array($array)) {
			if (isset($array[$name])) {
				$value = $array[$name];
			} else {
				$value = null;
			}
		} else {
			$value = $name;
		}
		
		if (!isset($value)) {
			return false;
		} elseif ($check === true) {
			if ($value === true) {
				return 'checked="checked"';
			}
		} elseif (!empty($check)) {
			if ($value == $check) {
				return 'selected="selected"';
			}
		} else {
			return 'value="' . $value . '"';
		}
	}
	
	/**
	 * Return form option
	 *
	 * @param string $array 
	 * @param string $name 
	 * @param string $default 
	 * @return string
	 */
	public function returnForm($array, $name, $default=null){
		if(!isset($array[$name])){
			if(isset($default)){
				return $default;
			}
			else{
				return false;
			}
		}
		$value = $array[$name];
		return $value;
	}
	
	// CONFIGURATION HANDLING
	
	/**
	 * Set configuration key
	 *
	 * @param string $name 
	 * @param string $unset 
	 * @return void
	 */
	public function setConf($name, $unset='') {
		return self::setForm($_SESSION['fsip']['config'], $name, $unset);
	}
	
	/**
	 * Return HTML-formatted configuration key
	 *
	 * @param string $name 
	 * @param string $check 
	 * @return string
	 */
	public function readConf($name, $check=true) {
		return self::readForm($_SESSION['fsip']['config'], $name, $check);
	}
	
	/**
	 * Return configuration key
	 *
	 * @param string $name 
	 * @return string
	 */
	public function returnConf($name) {
		return self::makeHTMLSafe(self::returnForm($_SESSION['fsip']['config'], $name));
	}
	
	/**
	 * Save configuration
	 *
	 * @return int|false Bytes written or error
	 */
	public function saveConf() {
		return file_put_contents($this->correctWinPath(PATH . 'config.json'), json_encode(self::reverseHTMLSafe($_SESSION['fsip']['config'])));
	}
	
	// URL HANDLING
	
	/**
	 * Find ID number from string
	 *
	 * @param string $string Input string
	 * @param string $numeric_required If true, will return false if number not found
	 * @return int|string|false ID, string, or error
	 */
	public function findID($string, $numeric_required=false) {
		$matches = array();
		if (is_numeric($string)) {
			$id = intval($string);
		} elseif (preg_match('#^([0-9]+)#s', $string, $matches)) {
			$id = intval($matches[1]);
		} elseif ($numeric_required === true) {
			return false;
		} else {
			$id = $string;
		}
		return $id;
	}
	
	/**
	 * Find image IDs (in <a>, <img>, etc.) from a string
	 *
	 * @param string $str Input string
	 * @return array Image IDs
	 */
	public function findIDRef($str) {
		preg_match_all('#["\']{1}(?=' . LOCATION . '/|/)[^"\']*?([0-9]+)[^/.]*\.(?:' . IMG_EXT . ')#si', $str, $matches, PREG_SET_ORDER);
		
		$image_ids = array();
		
		foreach ($matches as $match) {
			$image_ids[] = intval($match[1]);
		}
		
		$image_ids = array_unique($image_ids);
		
		return $image_ids;
	}
	
	/**
	 * Find meta references from an HTML string
	 *
	 * @param string $html Input HTML string
	 * @return array Associate array of data (site_name, title, url)
	 */
	public function findMetaRef($html) {
		$array = array();
		
		preg_match_all('#<meta.*?>#', $html, $metas);
		foreach ($metas[0] as $meta) {
			if(stripos($meta, 'property="og:site_name"') !== false) {
				preg_match('#content="(.*?)"#si', $meta, $match);
				$array['site_name'] = $match[1];
			} elseif(stripos($meta, 'property="og:title"') !== false) {
				preg_match('#content="(.*?)"#si', $meta, $match);
				$array['title'] = $match[1];
			} elseif(stripos($meta, 'property="og:url"') !== false) {
				preg_match('#content="(.*?)"#si', $meta, $match);
				$array['url'] = $match[1];
			}
		}
		
		return $array;
	}
	
	/**
	 * Make a URL-friendly string (removes special characters, replaces spaces)
	 *
	 * @param string $string
	 * @return string
	 */
	public function makeURL($string) {
		$string = html_entity_decode($string, 1, 'UTF-8');
		$string = self::removeAccents($string);
		$string = strtolower($string);
		$string = preg_replace('#([^a-zA-Z0-9]+)#s', '-', $string);
		$string = preg_replace('#^(\-)+#s', '', $string);
		$string = preg_replace('#(\-)+$#s', '', $string);
		return $string;
	}
	
	/**
	 * Converts all accent characters to ASCII characters.
	 *
	 * If there are no accent characters, then the string given is just returned.
	 *
	 * @param string $string Text that might have accent characters
	 * @return string Filtered string with replaced "nice" characters.
	 */
	public function removeAccents($string) {
		if (!preg_match('/[\x80-\xff]/', $string)) {
			return $string;
		}

		if ($this->seems_utf8($string)) {
			$chars = array(
			// Decompositions for Latin-1 Supplement
			chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
			chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
			chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
			chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
			chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
			chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
			chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
			chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
			chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
			chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
			chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
			chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
			chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
			chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
			chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
			chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
			chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
			chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
			chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
			chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
			chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
			chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
			chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
			chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
			chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
			chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
			chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
			chr(195).chr(191) => 'y',
			// Decompositions for Latin Extended-A
			chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
			chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
			chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
			chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
			chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
			chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
			chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
			chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
			chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
			chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
			chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
			chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
			chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
			chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
			chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
			chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
			chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
			chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
			chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
			chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
			chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
			chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
			chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
			chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
			chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
			chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
			chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
			chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
			chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
			chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
			chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
			chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
			chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
			chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
			chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
			chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
			chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
			chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
			chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
			chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
			chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
			chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
			chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
			chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
			chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
			chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
			chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
			chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
			chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
			chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
			chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
			chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
			chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
			chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
			chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
			chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
			chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
			chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
			chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
			chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
			chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
			chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
			chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
			chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
			// Euro Sign
			chr(226).chr(130).chr(172) => 'E',
			// GBP (Pound) Sign
			chr(194).chr(163) => '');

			$string = strtr($string, $chars);
		} else{
	        // Assume ISO-8859-1 if not UTF-8
	        $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
	            .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
	            .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
	            .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
	            .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
	            .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
	            .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
	            .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
	            .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
	            .chr(252).chr(253).chr(255);

	        $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

	        $string = strtr($string, $chars['in'], $chars['out']);
	        $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
	        $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
	        $string = str_replace($double_chars['in'], $double_chars['out'], $string);
	    }
	    
	    return $string;
	}
	
	public function seems_utf8($str) {
		$length = strlen($str);
		for ($i=0; $i < $length; $i++) {
			$c = ord($str[$i]);
			if ($c < 0x80) $n = 0; // 0bbbbbbb
			elseif (($c & 0xE0) == 0xC0) $n=1; // 110bbbbb
			elseif (($c & 0xF0) == 0xE0) $n=2; // 1110bbbb
			elseif (($c & 0xF8) == 0xF0) $n=3; // 11110bbb
			elseif (($c & 0xFC) == 0xF8) $n=4; // 111110bb
			elseif (($c & 0xFE) == 0xFC) $n=5; // 1111110b
			else return false; // Does not match any model
			for ($j=0; $j<$n; $j++) { 
				// n bytes matching 10bbbbbb follow ?
				if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80)) {
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Minimize URL for display purposes
	 *
	 * @param string $url
	 * @return string
	 */
	public function minimizeURL($url) {
		$url = preg_replace('#^https?\:\/\/www\.#s', '', $url);
		$url = preg_replace('#^https?\:\/\/#s', '', $url);
		$url = preg_replace('#^www\.#s', '', $url);
		$url = preg_replace('#\/$#s', '', $url);
		return $url;
	}
	
	/**
	 * Change page number on current URL
	 *
	 * @param string $page 
	 * @return void
	 */
	public function magicURL($page) {
		$uri = $_SERVER['REQUEST_URI'];
		
		if ((URL_RW == '/') and !strpos($uri, '?')) {
			$uri = @preg_replace('#with/[^/]*(/)?#si', '', $uri);
			$uri = @preg_replace('#(\?)?page\=[0-9]+#si', '', $uri);
			if(preg_match('#page[0-9]+#si', $uri)){
				$uri = preg_replace('#(/)?page[0-9]+(/)?#si', '\\1page' . $page . '\\2', $uri);
			} else {
				$last_pos = strlen($uri) - 1;
				if ($uri[$last_pos] != '/') {
					$uri .= '/';
				}
				$uri .= 'page' . $page . '/';
			}
		} else {
			$uri = @preg_replace('#[?&]{1,1}with=[^&]*(&)?#si', '\\1', $uri);
			$uri = @preg_replace('#[\?\&]?page\=[0-9]+#si', '', $uri);
			$uri = @preg_replace('#\/page[0-9]+(/)?#si', '', $uri);

			if (strpos($uri, '?')) {
				$uri .= '&';
			} else {
				$uri .= '?';
			}
			
			$uri .= 'page=' . $page;
		}
		
		$uri = LOCATION . $uri;
		return $uri;
	}
	
	/**
	 * Trim long strings
	 *
	 * @param string $string 
	 * @param string $length Maximum character length
	 * @return string
	 */
	public function fitString($string, $length=50) {
		$length = intval($length);
		if ($length < 3) { return false; }
		
		$string = trim($string);
		if (strlen($string) > $length) {
			$string = rtrim(substr($string, 0, $length - 3)) . '&#0133;';
			$string = self::closeTags($string);
		}
		return $string;
	}
	
	/**
	 * Trim strings, end on a whole word
	 *
	 * @param string $string 
	 * @param string $length Maximum character length
	 * @return string 
	 */
	public function fitStringByWord($string, $length=50) {
		$length = intval($length);
		if ($length < 3) { return false; }
		
		$string = trim($string);
		if (strlen($string) > $length) {
			$space = strpos($string, ' ', $length);
			if($space !== false){
				$string = substr($string, 0, $space) . '&#0133;';
				$string = self::closeTags($string);
			}
		}
		return $string;
	}
	
	/**
	 * Apply nl2br() when you don't know if <p> tags are being used
	 *
	 * @param string $str
	 * @return string
	 */
	public function nl2br($str) {
		$str = nl2br($str);
		$str = str_replace('</p><br /><br />', '</p>', $str);
		$str = str_replace('</ul><br /><br />', '</ul>', $str);
		$str = str_replace('</ol><br /><br />', '</ol>', $str);
		return $str;
	}
	
	/**
	 * Choose between singular and plural forms of a string
	 *
	 * @param string $count Count
	 * @param string $singular Singular form
	 * @param string $plural Plural form
	 * @return string
	 */
	public function returnCount($count, $singular, $plural=null){
		if(empty($plural)){
			$plural = $singular . 's';
		}
		
		if($count == 1){
			return $singular;
		}
		
		return $plural;
	}
	
	/**
	 * Choose between singular and plural forms of a string and include count
	 *
	 * @param string $count Count
	 * @param string $singular Singular form
	 * @param string $plural Plural form
	 * @return string
	 */
	public function returnFullCount($count, $singular, $plural=null){
		$count = number_format($count) . ' ' . self::returnCount($count, $singular, $plural);
		
		return $count;
	}
	
	/**
	 * If Windows Server, make path Windows-friendly
	 *
	 * @param string $path
	 * @return string
	 */
	public function correctWinPath($path){
		if(SERVER_TYPE == 'win'){
			$path = str_replace('/', '\\', $path);
		}
		return $path;
	}
	
	// REDIRECT HANDLING
	
	/**
	 * Current page for redirects (removes all GET variables except page)
	 *
	 * @param array $get Append to URL (GET variables as associative array)
	 * @return string
	 */
	public function location($get=null){
		$location = LOCATION;
		$location .= preg_replace('#\?.*$#si', '', $_SERVER['REQUEST_URI']);
		
		// Retain page data
		preg_match('#page=[0-9]+#si', $_SERVER['REQUEST_URI'], $matches);
		if(!empty($matches[0])){
			$location .= '?' . $matches[0];
			if(!empty($params)){
				$location .= '&' . http_build_query($get);
			}
		}
		elseif(!empty($params)){
			$location .= '?' . http_build_query($get);
		}
		
		return $location;
	}
	
	/**
	 * Current page for redirects
	 *
	 * @param array $get Append to URL (GET variables as associative array)
	 * @return string URL
	 */
	public function locationFull($get=null){
		if(!empty($array) and !is_array($get)){ return false; }
		$location = LOCATION . $_SERVER['REQUEST_URI'];
		if(!empty($get)){
			if(preg_match('#\?.*$#si', $location)){
				$location .= '&' . http_build_query($get);
			}
			else{
				$location .= '?' . http_build_query($get);
			}
		}
		
		return $location;
	}
	
	/**
	 * Set callback location
	 *
	 * @param string $page 
	 * @return void
	 */
	public function setCallback($page=null){
		if(!empty($page)){
			$_SESSION['fsip']['callback'] = $page;
		}
		else{
			$_SESSION['fsip']['callback'] = self::location();
		}
	}
	
	/**
	 * Send to callback location
	 *
	 * @param string $url Fallback URL if callback URL isn't set
	 * @return void
	 */
	public function callback($url=null){
		unset($_SESSION['fsip']['go']);
		if(!empty($_SESSION['fsip']['callback'])){
			header('Location: ' . $_SESSION['fsip']['callback']);
		}
		elseif(!empty($url)){
			header('Location: ' . $url);
		}
		else{
			header('Location: ' . LOCATION . BASE . ADMIN . 'dashboard/');
		}
		exit();
	}
	
	/**
	 * Send back (for cancel links)
	 *
	 * @return void
	 */
	public function back(){
		if(!empty($_SESSION['fsip']['back'])){
			echo $_SESSION['fsip']['back'];
		}
		elseif(!empty($_SERVER['HTTP_REFERER'])){
			echo $_SERVER['HTTP_REFERER'];
		}
		else{
			echo LOCATION . BASE . ADMIN . 'dashboard/';
		}
	}
	
	/**
	 * Sift through a URI (http://www.whatever.com/this/) for just the domain (www.whatever.com)
	 * 
	 * @param string $uri
	 * @return string
	 */
	public function siftDomain($uri){
		$domain = preg_replace('#https?://([^/]*).*#si', '$1', $uri);
		return $domain;
	}
	
	// MAIL
	
	/**
	 * Send email
	 *
	 * @param int|string $to If integer, looks up email address from users table; else, an email address
	 * @param string $subject 
	 * @param string $message 
	 * @return True if successful
	 */
	public function email($to=0, $subject=null, $message=null){
		if(empty($subject) or empty($message)){ return false; }
		
		if($to == 0){
			$to = $this->returnConf('web_email');
		}
		
		if(is_int($to) or preg_match('#[0-9]+#s', $to)){
			$query = $this->prepare('SELECT user_email FROM users WHERE user_id = ' . $to);
			$query->execute();
			$user = $query->fetch();
			$to = $user['user_email'];
		}
		
		$source = strip_tags($this->returnConf('web_title'));
		
		if(empty($source)){ $source = 'FSIP'; }
		
		$subject = $source . ': ' . $subject;
		$message = $message . "\r\n\n" . '-- ' . $source;
		$headers = 'From: ' . $this->returnConf('web_email') . "\r\n" .
			'Reply-To: ' . $this->returnConf('web_email') . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		
		return mail($to, $subject, $message, $headers);
	}
	
	// DEBUGGING AND LOGGING
	
	/**
	 * Set errors
	 *
	 * @param int|string|Exception $severity Severity (PHP error constant), title (user-generated), or exception (OO-code generated)
	 * @param string $message 
	 * @param string $filename
	 * @param int $line_number
	 * @param int|string|array $http_headers Index array of HTTP headers to send (if an item is an integer, send as status code)
	 * @return void
	 */
	public static function addError($severity, $message=null, $filename=null, $line_number=null, $http_headers=null){
		if(!(error_reporting() & $severity)){
			// This error code is not included in error_reporting
			// return;
		}
		
		// Is exception?
		if(is_object($severity)){
			$message = $severity->getMessage();
			$filename = $severity->getFile();
			$line_number = $severity->getLine();
			$severity = E_USER_ERROR;
		}
		
		if(is_string($severity)){
			if(!is_array($http_headers)){
				$http_headers_wrong_format = $http_headers;
				$http_headers = array();
				if(is_int($http_headers_wrong_format)){
					$http_headers[] = $http_headers_wrong_format;
				}
				if(is_string($http_headers_wrong_format)){
					$http_headers[] = $http_headers_wrong_format;
				}
			}
			foreach($http_headers as $header){
				if(!headers_sent()){
					if(is_string($header)){
						header($header, true);
					}
					elseif(is_integer($header)){
						if($header == 100){
							header('HTTP/1.0 100 Continue', true);
							header('Status: 100 Continue', true);
						}
						elseif($header == 101){
							header('HTTP/1.0 101 Switching Protocols', true);
							header('Status: 101 Switching Protocols', true);
						}
						elseif($header == 200){
							header('HTTP/1.0 200 OK', true);
							header('Status: 200 OK', true);
						}
						elseif($header == 201){
							header('HTTP/1.0 201 Created', true);
							header('Status: 201 Created', true);
						}
						elseif($header == 202){
							header('HTTP/1.0 202 Accepted', true);
							header('Status: 202 Accepted', true);
						}
						elseif($header == 203){
							header('HTTP/1.0 203 Non-Authoritative Information', true);
							header('Status: 203 Non-Authoritative Information', true);
						}
						elseif($header == 204){
							header('HTTP/1.0 204 No Content', true);
							header('Status: 204 No Content', true);
						}
						elseif($header == 205){
							header('HTTP/1.0 205 Reset Content', true);
							header('Status: 205 Reset Content', true);
						}
						elseif($header == 206){
							header('HTTP/1.0 206 Partial Content', true);
							header('Status: 206 Partial Content', true);
						}
						elseif($header == 300){
							header('HTTP/1.0 300 Multiple Choices', true);
							header('Status: 300 Multiple Choices', true);
						}
						elseif($header == 301){
							header('HTTP/1.0 301 Moved Permanently', true);
							header('Status: 301 Moved Permanently', true);
						}
						elseif($header == 302){
							header('HTTP/1.0 302 Moved Temporarily', true);
							header('Status: 302 Moved Temporarily', true);
						}
						elseif($header == 303){
							header('HTTP/1.0 303 See Other', true);
							header('Status: 303 See Other', true);
						}
						elseif($header == 304){
							header('HTTP/1.0 304 Not Modified', true);
							header('Status: 304 Not Modified', true);
						}
						elseif($header == 305){
							header('HTTP/1.0 305 Use Proxy', true);
							header('Status: 305 Use Proxy', true);
						}
						elseif($header == 307){
							header('HTTP/1.0 307 Temporary Redirect', true);
							header('Status: 307 Temporary Redirect', true);
						}
						elseif($header == 400){
							header('HTTP/1.0 400 Bad Request', true);
							header('Status: 400 Bad Request', true);
						}
						elseif($header == 401){
							header('HTTP/1.0 401 Unauthorized', true);
							header('Status: 401 Unauthorized', true);
						}
						elseif($header == 402){
							header('HTTP/1.0 402 Payment Required', true);
							header('Status: 402 Payment Required', true);
						}
						elseif($header == 403){
							header('HTTP/1.0 403 Forbidden', true);
							header('Status: 403 Forbidden', true);
						}
						elseif($header == 404){
							header('HTTP/1.0 404 Not Found', true);
							header('Status: 404 Not Found', true);
						}
						elseif($header == 405){
							header('HTTP/1.0 405 Method Not Allowed', true);
							header('Status: 405 Method Not Allowed', true);
						}
						elseif($header == 406){
							header('HTTP/1.0 406 Not Acceptable', true);
							header('Status: 406 Not Acceptable', true);
						}
						elseif($header == 407){
							header('HTTP/1.0 407 Proxy Authentication Required', true);
							header('Status: 407 Proxy Authentication Required', true);
						}
						elseif($header == 408){
							header('HTTP/1.0 408 Request Timeout', true);
							header('Status: 408 Request Timeout', true);
						}
						elseif($header == 409){
							header('HTTP/1.0 409 Conflict', true);
							header('Status: 409 Conflict', true);
						}
						elseif($header == 410){
							header('HTTP/1.0 410 Gone', true);
							header('Status: 410 Gone', true);
						}
						elseif($header == 411){
							header('HTTP/1.0 411 Length Required', true);
							header('Status: 411 Length Required', true);
						}
						elseif($header == 412){
							header('HTTP/1.0 412 Precondition Failed', true);
							header('Status: 412 Precondition Failed', true);
						}
						elseif($header == 413){
							header('HTTP/1.0 413 Request Entity Too Large', true);
							header('Status: 413 Request Entity Too Large', true);
						}
						elseif($header == 414){
							header('HTTP/1.0 414 Request URI Too Large', true);
							header('Status: 414 Request URI Too Large', true);
						}
						elseif($header == 415){
							header('HTTP/1.0 415 Unsupported Media Type', true);
							header('Status: 415 Unsupported Media Type', true);
						}
						elseif($header == 416){
							header('HTTP/1.0 416 Request Range Not Satisfiable', true);
							header('Status: 416 Request Range Not Satisfiable', true);
						}
						elseif($header == 417){
							header('HTTP/1.0 417 Expectation Failed', true);
							header('Status: 417 Expectation Failed', true);
						}
						elseif($header == 500){
							header('HTTP/1.0 500 Internal Server Error', true);
							header('Status: 500 Internal Server Error', true);
						}
						elseif($header == 501){
							header('HTTP/1.0 501 Not Implemented', true);
							header('Status: 501 Not Implemented', true);
						}
						elseif($header == 502){
							header('HTTP/1.0 502 Bad Gateway', true);
							header('Status: 502 Bad Gateway', true);
						}
						elseif($header == 503){
							header('HTTP/1.0 503 Service Unavailable', true);
							header('Status: 503 Service Unavailable', true);
						}
						elseif($header == 504){
							header('HTTP/1.0 504 Gateway Timeout', true);
							header('Status: 504 Gateway Timeout', true);
						}
						elseif($header == 505){
							header('HTTP/1.0 505 HTTP Version Not Supported', true);
							header('Status: 505 HTTP Version Not Supported', true);
						}
						else{
							header('HTTP/1.0 ' . $header, true);
							header('Status: ' . $header, true);
						}
					}
				}
			}
			
			// Write to session
			$_SESSION['fsip']['error'] = array('error_title' => $severity, 'error_message' => $message);
			session_write_close();
			
			// Get error page
			ob_start();
			chdir(PATH);
			require('error.php');
			ob_flush();
			
			// Quit
			exit();
		}
		
		switch($severity){
			case E_USER_NOTICE:
				$_SESSION['fsip']['errors'][] = array('constant' => $severity, 'severity' => 'notice', 'message' => $message, 'filename' => $filename, 'line_number' => $line_number);
				break;
			case E_USER_WARNING:
				$_SESSION['fsip']['errors'][] = array('constant' => $severity, 'severity' => 'warning', 'message' => $message, 'filename' => $filename, 'line_number' => $line_number);
				break;
			case E_USER_ERROR:
				try{
					throw new ErrorException($message, 0, E_USER_ERROR, $filename, $line_number);
				}
				catch(ErrorException $e){
					self::addException($e);
				}
			default:
				$_SESSION['fsip']['errors'][] = array('constant' => $severity, 'severity' => 'warning', 'message' => $message, 'filename' => $filename, 'line_number' => $line_number);
				break;
		}
		
		return true;
	}
	
	/**
	 * Add Exception
	 *
	 * @param Exception $e 
	 * @return void
	 */
	public static function addException($e){
		throw new FSIPException($e);
	}
	
	/**
	 * Display errors
	 *
	 * @return void|string HTML-formatted notifications 
	 */
	public static function returnErrors() {
		if (!isset($_SESSION['fsip']) || !isset($_SESSION['fsip']['errors'])) { return; }
		//TODO check if admin user should be only one to see errors and if admin is logged in else return
		//				$user = new User();
//				if(!empty($user) and $user->perm()){
//
		$count = @count($_SESSION['fsip']['errors']);
		
		if (empty($count)) { return; }
		
		// Determine unique types
		$types = array();
		foreach($_SESSION['fsip']['errors'] as $error) {
			$types[] = $error['severity'];
		}
		$types = array_unique($types);
		
		$overview = array();
		$list = array();
		
		// Produce HTML for display
		foreach($types as $type) {
			$i = 0;
			
			foreach($_SESSION['fsip']['errors'] as $error) {
				if($error['severity'] == $type){
					$i++;
				}
			}
			
			if($i == 1) {
				$overview[] = $i . ' ' . $type;
			}
			else {
				$overview[] = $i . ' ' . $type . 's';
			}
		}
		
		foreach($_SESSION['fsip']['errors'] as $error) {
			$item = '<li><strong>' . ucwords($error['severity']) .':</strong> ' . $error['message'];
			if(!empty($error['filename'])){
				$item .= ' (' . $error['filename'] . ', line ' . $error['line_number'] .')';
			} 
			$item .= '.</li>';
			$list[] = $item;
		}
		
		// Dispose of messages
		unset($_SESSION['fsip']['errors']);
		
		return '<span>(<a href="#" class="show">' . implode(', ', $overview) . '</a>)</span><div class="reveal"><ol class="errors">' . implode("\n", $list) . '</ol></div>';
	}
	
	/**
	 * Return debug array
	 *
	 * @return array
	 */
	public function debug(){
		$_SESSION['fsip']['debug']['execution_time'] = microtime(true) - $_SESSION['fsip']['debug']['start_time'];
		return $_SESSION['fsip']['debug'];
	}
	
	/**
	 * Add message to error log
	 *
	 * @param string $message 
	 * @param string $number 
	 * @return void
	 */
	public function report($message, $number=null){
		if(isset($_SESSION['fsip']['warning']) and ($_SESSION['fsip']['warning'] == $message)){ return false; }
		
		$_SESSION['fsip']['warning'] = $message;
		
		// Format message
		$message = date('Y-m-d H:i:s') . "\t" . $message;
		if(!empty($number)){ $message .= ' (' . $number . ')'; }
		$message .= "\n";
		
		// Write message
		$handle = fopen($this->correctWinPath(PATH . DB . 'log.txt'), 'a');
		if(@fwrite($handle, $message) === false){
			$this->addError(E_USER_ERROR, 'Cannot write to report file');
		}
		fclose($handle);
	}
		
	/**
	 * Compare two strings
	 *
	 * @param string $string1 
	 * @param string $string2 
	 * @return string
	 */
	public function compare($string1, $string2) {
		require_once(PATH . CLASSES . 'text_diff/Diff.php');
		require_once(PATH . CLASSES . 'text_diff/Diff/Renderer/inline.php');
		
		$lines1 = explode("\n", $string1);
		$lines2 = explode("\n", $string2);
		
		$diff     = new Text_Diff('auto', array($lines1, $lines2));
		$renderer = new Text_Diff_Renderer_inline();
		return nl2br($renderer->render($diff));
	}
}

class FSIPException extends Exception implements Serializable {
	public $public_trace;
	public $public_message;
	
	public function __construct($e) {
		parent::__construct($e);
		
		$this->public_trace = $this->getTrace();
		
		if (is_object($this->public_trace[0]['args'][0])) {
			$this->public_message = $this->public_trace[0]['args'][0]->message;
		} else {
			$this->public_message = $this->message;
		}
		
		$_SESSION['fsip']['exception'] = $this;
		session_write_close();
		
		// Get error page
		ob_start();
		chdir(PATH . ADMIN);
		require('error.php');
		ob_flush();
		
		session_start();
		unset($_SESSION['fsip']['exception']);
	
		// Quit
		exit();
		break;
	}
	
	public function serialize() {
		return serialize(array($this->validator, $this->arguments, $this->code, $this->message));
	}

	public function unserialize($serialized) {
		list($this->validator, $this->arguments, $this->code, $this->message) = unserialize($serialized);
	}
	
	public function getPublicMessage() {
		return $this->public_message;
	}
	
	public function getPublicTrace() {
		if (is_object($this->public_trace[0]['args'][0])) {
			$trace = $this->public_trace[0]['args'][0]->getTrace();
		} else {
			$trace = $this->public_trace;
		}
		
		return $trace;
	}
}

?>