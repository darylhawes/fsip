<?php

class DB {

	protected $db;

	public $db_type;
	public $db_version;

	public $debugSQL;
	private $tables;
	
	/**
	 * Initiates a new db class database object
	 *
	 * @return void
	 **/
	public function __construct() {
//		$this->setDebugSQL(true); // add SQL calls to addNotes perhaps? In debug add and use new addDebugNote method.
		$this->setDebugSQL(false);
//echo "creating db object";
		// Get tables
		$this->tables = getTables();

		// Initiate database connection
		if (defined('DB_TYPE') and defined('DB_DSN')) {
			// Determine database type
			$this->db_type = DB_TYPE;
	
			if ($this->db_type == 'mssql') {
				// $this->db = new PDO(DB_DSN);
			} elseif($this->db_type == 'mysql') {
				$this->db = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => true, PDO::FETCH_ASSOC => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT));
			} elseif($this->db_type == 'pgsql') {
				$this->db = new PDO(DB_DSN, DB_USER, DB_PASS);
				$this->db->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
			} elseif($this->db_type == 'sqlite') {
				$this->db = new PDO(DB_DSN, null, null, array(PDO::ATTR_PERSISTENT => false, PDO::FETCH_ASSOC => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT));
				$this->db->sqliteCreateFunction('ACOS', 'acos', 1);
				$this->db->sqliteCreateFunction('COS', 'cos', 1);
				$this->db->sqliteCreateFunction('RADIANS', 'deg2rad', 1);
				$this->db->sqliteCreateFunction('SIN', 'sin', 1);
			}
			
			if (is_object($this->db)) {
				$this->db_version = $this->db->getAttribute(PDO::ATTR_SERVER_VERSION);
			}
		}
	} // end __construct

	/**
	 * Terminates object, closes the database connection
	 *
	 * @return void
	 **/
	public function __destruct() {
		$this->db = null;
	}
	
	/**
	 * Prepares and executes SQL statement
	 *
	 * @param string $query Query
	 * @return int Number of affected rows
	 */
	public function exec($query) {
		if (!$this->db) { 
			// This error message may mean that we're not installed properly. Offer the user a link to setup their installation.
			echo "<h1>ERROR: No database connection.</h1> <p><strong>You may not have FSIP configured properly. </strong></p><p>Try to <a href=".LOCATION . BASE."admin/install.php>install</a> again?</p>";
			Debugger::addError(E_USER_ERROR, 'No database connection'); 
			exit;
		}
		
		$this->prequery($query);
		$response = $this->db->exec($query);
		if ($this->debugSQL) {
			echo "DEBUG: SQL called - $query<br />";
		}
		$this->postquery($query);
		if ($this->debugSQL) {
			echo "DEBUG: SQL postquery - $query<br />";
		}
		
		return $response;
	}
	
	/**
	 * Prepares a statement for execution and returns a statement object
	 *
	 * @param string $query Query
	 * @return PDOStatement
	 */
	public function prepare($query) {
//echo "db prepare 1<br />";
		if (!$this->db) {
			// This error message may mean that we're not installed properly. Offer the user a link to setup their installation.
			echo "<h1>ERROR: No database connection.</h1> <p><strong>You may not have FSIP configured properly. </strong></p><p>Try to <a href=".LOCATION . BASE."admin/install.php>install</a> again?</p>";
			Debugger::addError(E_USER_ERROR, 'No database connection'); 
			exit;
		}
	
		$this->prequery($query);
		$response = $this->db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		if ($this->debugSQL) {
			echo "DEBUG: SQL prepared and responded - $query<br />";
		}
		$this->postquery($query);
		if ($this->debugSQL) {
			echo "DEBUG: SQL postquery - $query<br />";
		}

		if (!$response) { 
			Debugger::addError(E_USER_ERROR, 'Invalid query, check database log and connection');
		}

//echo "db prepare complete and returning<br />";
		return $response;
	}
	
	/**
	 * Translate query for different database types
	 *
	 * @param string $query Query
	 * @return string Translated query
	 */
	public function prequery(&$query) {
		$_SESSION['fsip']['debug']['queries']++;
		
		if (TABLE_PREFIX != '') {
			// Add table prefix
			$query = preg_replace('#(FROM|JOIN)\s+([\sa-z0-9_\-,]*)\s*(WHERE|GROUP|HAVING|ORDER)?#se', "'\\1 '.DB::appendTablePrefix('\\2').' \\3'", $query);
			$query = preg_replace('#([a-z]+[a-z0-9-\_]*)\.#si', TABLE_PREFIX . '\\1.', $query);
			$query = preg_replace('#(INSERT INTO|UPDATE)\s+(\w+)#si', '\\1 ' . TABLE_PREFIX . '\\2', $query);
			$query = preg_replace('#TABLE ([[:punct:]]*)(\w+)#s', 'TABLE \\1' . TABLE_PREFIX . '\\2', $query);
		}
		
		if ($this->db_type == 'mssql') {
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
		} elseif($this->db_type == 'pgsql') {
			$query = preg_replace('#LIMIT[[:space:]]+([0-9]+),[[:space:]]*([0-9]+)#si', 'LIMIT \2 OFFSET \1', $query);
			$query = str_replace('HOUR(', 'EXTRACT(HOUR FROM ', $query);
			$query = str_replace('DAY(', 'EXTRACT(DAY FROM ', $query);
			$query = str_replace('MONTH(', 'EXTRACT(MONTH FROM ', $query);
			$query = str_replace('YEAR(', 'EXTRACT(YEAR FROM ', $query);
		} elseif($this->db_type == 'sqlite') {
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
	protected function appendTablePrefix($tables_to_append='') {
		if (strpos($tables_to_append, ',') === false) {
			$tables_to_append = trim($tables_to_append);
			$tables_to_append = TABLE_PREFIX . $tables_to_append;
		} else {
			$tables_to_append = explode(',', $tables_to_append);
			$tables_to_append = array_map('trim', $tables_to_append);
			$tta = array();
//			print_r($tables_to_append);
			foreach($tables_to_append as $table) {
//				$table = TABLE_PREFIX . $tables_to_append;
				$tta[] = TABLE_PREFIX . $table;
			}
//			print_r($tta);
//			$tables_to_append = implode(', ', $tables_to_append);
			$tables_to_append = implode(', ', $tta);
		}
		return $tables_to_append;
	}
	
	/**
	 * Determine if query was successful; if not, log it using report()
	 *
	 * @param string $query
	 * @param string $db 
	 * @return bool True if successful
	 */
	public function postquery(&$query, $db=null) {
		if (empty($db)) { 
			$db = $this->db;
		}
		
		$error = $db->errorInfo();
		
		if (isset($error[2])) {
			$code = $error[0];
			$message = $query . ' ' . ucfirst(preg_replace('#^Error\:[[:space:]]+#si', '', $error[2])) . ' (' . $code . ').';
			
			if (substr($code, 0, 2) == '00') {
				$this->report($message, $code);
			} elseif($code == '23000') {
				$this->report($message, $code);
				return false;
			} else {
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
	public function removeNull($input) {
		return str_replace(':null', ':""', $input);
	}

	/**
	 * Sanitize table and column names (to prevent SQL injection attacks)
	 *
	 * @param string $string 
	 * @return string
	 */
	public static function sanitize($string) {
		return preg_replace('#(?:(?![a-z0-9_\.-\s]).)*#si', '', $string);
	}

	public function getInfo() {
		$info = array();
		
		// Exclude tables
		unset($this->tables['rights']);
		unset($this->tables['exifs']);
		unset($this->tables['extensions']);
		unset($this->tables['themes']);
		unset($this->tables['sizes']);
		unset($this->tables['rights']);
		unset($this->tables['versions']);
		unset($this->tables['citations']);
		unset($this->tables['items']);
		
		// Run helper function
		foreach($this->tables as $table => $selector) {
			$info[] = array('table' => $table, 'count' => self::countTable($table));
		}
		
		foreach($info as &$table) {
			if ($table['count'] == 1) {
				$table['display'] = preg_replace('#s$#si', '', $table['table']);
			} else {
				$table['display'] = $table['table'];
			}
		}
		
		return $info;
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
	public function getTable($table, $ids=null, $limit=null, $page=1, $order_by=null) {
		if (empty($table)) {
			return false;
		}
		if (!is_int($page) or ($page < 1)) {
			$page = 1;
		}
		
		$table = $this->sanitize($table);
		
		$sql_params = array();
		
		$order_by_sql = '';
		$limit_sql = '';
		
		if (!empty($order_by)) {
			if (is_string($order_by)) {
				$order_by = $this->sanitize($order_by);
				$order_by_sql = ' ORDER BY ' . $order_by;
			} elseif(is_array($order_by)) {
				foreach($order_by as &$by) {
					$by = $this->sanitize($by);
				}
				$order_by_sql = ' ORDER BY ' . implode(', ', $order_by);
			}
		}
		
		if (!empty($limit)) {
			$limit = intval($limit);
			$page = intval($page);
			$limit_sql = ' LIMIT ' . ($limit * ($page - 1)) . ', ' . $limit;
		}
		
		if (empty($ids)) {
			$query = $this->prepare('SELECT * FROM ' . $table . $order_by_sql . $limit_sql . ';');
		} else {
			$ids = convertToIntegerArray($ids);
			$field = $this->tables[$table];
			
			$query = $this->prepare('SELECT * FROM ' . $table . ' WHERE (' . $field . ' IN (' . implode(', ', $ids) . '))' . $order_by_sql . $limit_sql . ';');
		}
		
		$query->execute($sql_params);
		$contents = $query->fetchAll();
		
		$contents_ordered = array();
		
		if (!empty($ids)) {
			// Ensure posts array correlates to post_ids array
			foreach($ids as $id) {
				foreach($contents as $content) {
					if ($id == $content[$field]) {
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
	public function getRow($table, $id) {
		// Error checking
		if (empty($id)) { return false; }
		if (!($id = intval($id))) { return false; }
		
		$table = $this->getTable($table, $id);
		if (count($table) != 1) { return false; }
		return $table[0];
	}
	
	/**
	 * Add row (includes updating default fields)
	 *
	 * @param array $fields Associative array of key (column) and value (field)
	 * @param string $table Table name
	 * @return int|false Row ID or error
	 */
	public function addRow($fields=null, $table) {
		// Error checking
		if (empty($table) or (!is_array($fields) and isset($fields))) {
			return false;
		}
		
		if (empty($fields)) {
			$fields = array();
		}
		
		$table = $this->sanitize($table);
		$now = date('Y-m-d H:i:s');
		
		// Add default fields
		switch($table) {
			case 'comments':
				if (empty($fields['comment_created'])) { $fields['comment_created'] = $now; }
				if (empty($fields['comment_modified'])) { $fields['comment_modified'] = $now; }
				break;
			case 'guests':
				if (empty($fields['guest_views'])) { $fields['guest_views'] = 0; }
				if (empty($fields['guest_created'])) { $fields['guest_created'] = $now; }
				break;
			case 'rights':
				if (empty($fields['right_created'])) { $fields['right_created'] = $now; }
				if (empty($fields['right_modified'])) { $fields['right_modified'] = $now; }
				break;
			case 'pages':
				if (empty($fields['page_views'])) { $fields['page_views'] = 0; }
				if (empty($fields['page_created'])) { $fields['page_created'] = $now; }
				if (empty($fields['page_modified'])) { $fields['page_modified'] = $now; }
				break;
			case 'citations':
				if (empty($fields['citation_created'])) { $fields['citation_created'] = $now; }
				if (empty($fields['citation_modified'])) { $fields['citation_modified'] = $now; }
				break;
			case 'sets':
				if (empty($fields['set_views'])) { $fields['set_views'] = 0; }
				if (empty($fields['set_created'])) { $fields['set_created'] = $now; }
				if (empty($fields['set_modified'])) { $fields['set_modified'] = $now; }
				break;
			case 'sizes':
				if (!isset($fields['size_title'])) { $fields['size_title'] = ''; }
				break;
			case 'users':
				if (empty($fields['user_created'])) { $fields['user_created'] = $now; }
				break;
			default:
				break;
		}
		
		$field = $this->tables[$table];
		unset($fields[$field]);
		
		if (count($fields) > 0) {
			$columns = array_keys($fields);
			$values = array_values($fields);
		
			$value_slots = array_fill(0, count($values), '?');
		
			// Add row to database
			$query = $this->prepare('INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $value_slots) . ');');
		} else {
			$values = array();
			$query = $this->prepare('INSERT INTO ' . $table . ' (' . $this->tables[$table] . ') VALUES (?);');
			$values = array(PDO::PARAM_NULL);
		}
		
		if (!$query->execute($values)) {
			return false;
		}
		
		// Return ID
		$id = intval($this->db->lastInsertId(TABLE_PREFIX . $table . '_' . $field . '_seq'));
		
		if ($id == 0) {
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
	public function updateRow($fields, $table, $ids=null, $default=true) {
		// Error checking
		if (empty($fields) or empty($table) or !is_array($fields)) {
			return false;
		}
		
		$table = $this->sanitize($table);
		
		$ids = convertToIntegerArray($ids);
		$field = $this->tables[$table];
		$now = date('Y-m-d H:i:s');
		
		// Add default fields
		if ($default === true) {
			switch($table) {
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
			}
		}
		
		$columns = array_keys($fields);
		$values = array_values($fields);

		// Add row to database
		$query = $this->prepare('UPDATE ' . $table . ' SET ' . implode(' = ?, ', $columns) . ' = ? WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . ';');
		if (!$query->execute($values)) {
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
	public function deleteRow($table, $ids=null) {
		if (empty($table) or empty($ids)) {
			return false;
		}
		
		$table = $this->sanitize($table);
		
		$ids = convertToIntegerArray($ids);
		$field = $this->tables[$table];
		
		// Delete row
		$query = 'DELETE FROM ' . $table . ' WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . ';';
		
		if (!$this->exec($query)) {
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
	public function deleteEmptyRow($table, $fields) {


		if (empty($table) or empty($fields)) {
			return false;
		}

		$table = $this->sanitize($table);

		$fields = convertToArray($fields);
		
		$conditions = array();
		foreach($fields as $field) {
			$conditions[] = '(' . $field . ' = ? OR ' . $field . ' IS NULL)';
		}
		
		$sql_params = array_fill(0, count($fields), '');
		
		// Delete empty rows
		$query = $this->prepare('DELETE FROM ' . $table . ' WHERE ' . implode(' OR ', $conditions) . ';');

		if (!$query->execute($sql_params)) {
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
	function countTable($table) {
		$table = $this->sanitize($table);
		
		$field = $this->tables[$table];
		if (empty($field)) 
		{ 
			return false;
		}
		
		$sql = '';
		
		// Don't show deleted items
		$with_deleted_columns = array('images', 'comments', 'sets', 'pages', 'rights');
		if (in_array($table, $with_deleted_columns)) {
			$show_deleted = false;
			if (Files::isInAdminPath() === true) {
				$user = new User();
				if (!empty($user) and $user->hasPermission('admin', false)) {
					if ($user->returnPref('recovery_mode') === true) {
						$show_deleted = true;
					}
				}
			}
			
			if ($show_deleted === false) {
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
		if (!returnConf('stat_enabled')) {
			return false;
		}
		
		if (returnConf('stat_ignore_user')) {
			$user = new User();
			if($user->isLoggedIn()){
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

	/**
	 * Update count of single field
	 *
	 * @param string $count_table 
	 * @param string $result_table 
	 * @param string $result_field 
	 * @param string $result_id 
	 * @return bool True if successful
	 */
	public function updateCount($count_table, $result_table, $result_field, $result_id) {
		$result_id = intval($result_id);
		
		$count_table = $this->sanitize($count_table);
		$result_table = $this->sanitize($result_table);
		
		$count_id_field = $this->tables[$count_table];
		$result_id_field = $this->tables[$result_table];
		
		// Get count
		$query = $this->prepare('SELECT COUNT(' . $count_id_field . ') AS count FROM ' . $count_table . ' WHERE ' . $result_id_field  . ' = :result_id AND ' . substr($count_id_field, 0, -2) . 'deleted IS NULL;');
		
		if (!$query->execute(array(':result_id' => $result_id))) {
			return false;
		}
		
		$counts = $query->fetchAll();
		$count = $counts[0]['count'];
		
		// Update row
		$query = $this->prepare('UPDATE ' . $result_table . ' SET ' . $result_field . ' = :count WHERE ' . $result_id_field . ' = :result_id;');
		
		if (!$query->execute(array(':count' => $count, ':result_id' => $result_id))) {
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
	public function updateCounts($count_table, $result_table, $result_field) {
		$count_table = $this->sanitize($count_table);
		$result_table = $this->sanitize($result_table);
		
		$count_id_field = $this->tables[$count_table];
		$result_id_field = $this->tables[$result_table];
		
		$results = $this->getTable($result_table);
		
		// Get count
		$select = $this->prepare('SELECT COUNT(' . $count_id_field . ') AS count FROM ' . $count_table . ' WHERE ' . $result_id_field  . ' = :result_id AND ' . substr($count_id_field, 0, -2) . 'deleted IS NULL;');
		
		// Update row
		$update = $this->prepare('UPDATE ' . $result_table . ' SET ' . $result_field . ' = :count WHERE ' . $result_id_field . ' = :result_id;');
		
		foreach($results as $result) {
			$result_id = $result[$result_id_field];
			if (!$select->execute(array(':result_id' => $result_id))) {
				return false;
			}
		
			$counts = $select->fetchAll();
			$count = $counts[0]['count'];
		
			if (!$update->execute(array(':count' => $count, ':result_id' => $result_id))) {
				return false;
			}
		}
		
		return true;
	}
	
	public function setDebugSQL($debug) {
		$this->debugSQL = $debug;
	}

	/**
	 * Get FSIP Dashboard header badges listing number of outstanding/new items
	 *
	 * @return array Associate array of fields and integers
	 */
	public function getBadges() {
//echo "in get badges<br />";
		$badges = array();
//echo "in get badges2<br />";
		$badges['images'] = Files::countDirectory(PATH . SHOEBOX);

		$comment_ids = new Find('comments');
		$comment_ids->status(0);
//echo "<br />getBadges test1<br />";
		$comment_ids->find();
//echo "getBadges test2<br />";
		
		$badges['comments'] = $comment_ids->count;
		
		return $badges;
	}

	/**
	 * Get array of tags
	 *
	 * @param bool $show_hidden_tags Include hidden tags
	 * @return array Associative array of tags
	 */
	public function getTags($show_hidden_tags=false, $published_only=false, $public_only=false) {
		$sql = '';
		
		if ($published_only === true) {
			$sql .= ' AND images.image_published <= "' . date('Y-m-d H:i:s') . '"';
		}
		
		if ($public_only === true) {
			$sql .= ' AND images.image_privacy = 1';
		}
	
		if (returnConf('tag_alpha')) {
			$query = $this->prepare('SELECT tags.tag_name, tags.tag_id, images.image_id FROM tags, links, images WHERE tags.tag_id = links.tag_id AND links.image_id = images.image_id AND images.image_deleted IS NULL ' . $sql . ' ORDER BY tags.tag_name;');
		} else {
			$query = $this->prepare('SELECT tags.tag_name, tags.tag_id, images.image_id FROM tags, links, images WHERE tags.tag_id = links.tag_id AND links.image_id = images.image_id AND images.image_deleted IS NULL ' . $sql . ' ORDER BY tags.tag_id ASC;');
		}
		$query->execute();
		$tags = $query->fetchAll();
		
		if ($show_hidden_tags !== true) {
			$tags_new = array();
			foreach($tags as $tag) {
				if ($tag['tag_name'][0] != '!') {
					$tags_new[] = $tag;
				}
			}
			$tags = $tags_new;
		}
		
		$tag_ids = array();
		$tag_names = array();
		$tag_counts = array();
		$tag_uniques = array();
		
		foreach($tags as $tag) {
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

}
?>