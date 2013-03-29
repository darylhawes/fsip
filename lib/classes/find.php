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

class Find {
	public $admin;
	public $cache;
	public $ids;
	public $ids_after;
	public $ids_before;
	public $count = 0;
	public $count_result = 0;
	public $offset_length;
	public $order;
	public $finds;
	public $first;
	public $first_reverse;
	public $last;
	public $last_modified;
	public $last_reverse;
	public $published_public_image_count;
	public $total_image_count;
	public $page;
	public $page_begin;
	public $page_count;
	public $page_limit;
	public $page_limit_current;
	public $page_limit_first;
	public $page_next;
	public $page_next_uri;
	public $page_previous;
	public $page_previous_uri;
	public $page_navigation_string;
	public $sets;
	public $table;
	public $table_id;
	public $table_prefix;
	public $tags;
	public $with;
	public $query;
	
	private $call;
	private $dbpointer;
	
	protected $sql;
	protected $sql_conds;
	protected $sql_limit;
	protected $sql_sorts;
	protected $sql_from;
	protected $sql_tables;
	protected $sql_join;
	protected $sql_join_type;
	protected $sql_join_tables;
	protected $sql_join_on;
	protected $sql_group_by;
	protected $sql_having;
	protected $sql_having_fields;
	protected $sql_params;
	protected $sql_order_by;
	protected $sql_where;
	
	private $tables;
	private $tables_cache;
	private $tables_index;
	
	/**
	 * Initiates Find class
	 *
	 * @param string Table to perform search
	 * @param string|array|int $ids Limit results to select IDs in table
	 * @param bool $auto_guest Set guest access restrictions
	 * @param bool $process_request Automatically employ the $_REQUEST array to issue methods (for searches)
	 * @param bool $ignore_deleted Ignore "deleted" table rows (except in recovery mode)
	 */
	public function __construct($table=null, $ids=null, $auto_guest=true, $process_request=true, $ignore_deleted=true) {
//echo "contructing find";
		$this->dbpointer = getDB();
//echo "dbpointer has been set";
//print_r($this->dbpointer);
		// Error handling
		if (empty($table)) { return false; }
		
		// Store data to object
		$this->call = array();
		$this->ids = array();
		$this->table = $table;
		$this->tables = getTables();
		$this->tables_cache = getTablesCache();
		$this->tables_index = getTablesIndex();
		$this->table_id = $this->tables[$table];
//echo "this table id = $this->table_id<br />";
		$this->table_prefix = substr($this->table_id, 0, -2);
//echo "this table prefix = $this->table_prefix<br />";
		$this->page = 1;
		$this->page_limit = LIMIT;
		$this->page_limit_first = LIMIT;
		$this->sql = 'SELECT ' . $this->table . '.' . $this->table_id . ' AS ' . $this->table_id;
//echo "this sql = $this->sql<br />";
		$this->sql_conds = array();
		$this->sql_limit = '';
		$this->sql_sorts = array();
		$this->sql_from = '';
		$this->sql_tables = array($this->table);
		$this->sql_join = '';
		$this->sql_join_type = '';
		$this->sql_join_tables = array();
		$this->sql_join_on = array();
		$this->sql_group_by = ' GROUP BY ' . $this->table . '.' . $this->table_id;
		$this->sql_having = '';
		$this->sql_injection = '';
		$this->sql_having_fields = array();
		$this->sql_params = array();
		$this->sql_order_by = '';
		$this->sql_where = '';
		
		// Cache these tables
		if (in_array($this->table, $this->tables_cache)) {
			$this->sql .= ', ' . $this->table . '.' . $this->table_prefix . 'modified AS last_modified';
			$this->cache = true;
		}
		
		// Optional starter set
		if (isset($ids)) {
//echo "there are ids: $ids<br />";
			$ids = convertToIntegerArray($ids);
			$this->sql_conds[] = $this->table . '.' . $this->table_id . ' IN (' . implode(', ', $ids) . ')';
//echo "sql conds are now set to:<br />";
//print_r($this->sql_conds);
		}
		
		// Don't show deleted items
		$with_deleted_columns = array('images', 'comments', 'sets', 'pages', 'rights');
		if (in_array($this->table, $with_deleted_columns)) {
			$show_deleted = false;
			
			if ($ignore_deleted === false) {
				$show_deleted = true;
			} elseif ($this->admin == true) {
				$user = new User();
				if (!empty($user) and $user->perm()) {
					if ($user->returnPref('recovery_mode') === true) {
						$show_deleted = true;
					}
				}
			}
			
			if ($show_deleted === false) {
//echo "show deleted is false and therefore calling null method<br />";
				$this->null($this->table . '.' . $this->table_prefix . 'deleted');
			}
		}
		
		if (($auto_guest == true) and ($this->table == 'images')) {
			// Guest access
			if (isset($_SESSION['fsip']['guest']) and ($this->admin != true)) {
				if ($_SESSION['fsip']['guest']['guest_inclusive']) {
					$this->privacy(2, true, false);
				} else {
					$this->privacy(2, false, false);
				}
				if (!empty($_SESSION['fsip']['guest']['guest_sets'])) {
					$this->sets(intval($_SESSION['fsip']['guest']['guest_sets']));
				}
			}
		}

		if ($process_request == true) {
			if (!empty($_REQUEST)) {
				// Process browser requests
				$_REQUEST = array_map('strip_tags', $_REQUEST);
			}

			// Smart search
			if (!empty($_REQUEST['act'])) {
				$this->smart($_REQUEST['act']);
			}

			// Title and description
			if (!empty($_REQUEST['q'])) { 
				$this->_search($_REQUEST['q']);
			}

			// Tags
			if (!empty($_REQUEST['tags'])) {
				$this->_tags($_REQUEST['tags']);
			}
			
			// Category
			if (!empty($_REQUEST['category'])) {
				$this->_category($_REQUEST['category']);
			}

			// Rights set
			if (!empty($_REQUEST['rights'])) {
				$this->_rights(intval($_REQUEST['rights']));
			}
			
			// Date created
			if (!empty($_REQUEST['created_begin']) or !empty($_REQUEST['created_end'])) {
				$this->_created($_REQUEST['created_begin'], $_REQUEST['created_end']);
			}
			
			// Date modified
			if (!empty($_REQUEST['modified_begin']) or !empty($_REQUEST['modified_end'])) {
				$this->_modified($_REQUEST['modified_begin'], $_REQUEST['modified_end']);
			}
		
			// Date taken
			if (!empty($_REQUEST['taken_begin']) or !empty($_REQUEST['taken_end'])) {
				$this->_taken($_REQUEST['taken_begin'], $_REQUEST['taken_end']);
			}

			// Date uploaded
			if (!empty($_REQUEST['uploaded_begin']) or !empty($_REQUEST['uploaded_end'])) {
				$this->_uploaded($_REQUEST['uploaded_begin'], $_REQUEST['uploaded_end']);
			}

			// Location
			if (!empty($_REQUEST['location'])) {
				$this->_location($_REQUEST['location'], $_REQUEST['location_proximity']);
			}

			// Primary color
			if (!empty($_REQUEST['color'])) {
				switch($_REQUEST['color']) {
					case 'blue':
						$this->_hsl(170, 235, 1, 100, 1, 100);
						break;
					case 'red':
						$this->_hsl(345, 10, 1, 100, 1, 100);
						break;
					case 'yellow':
						$this->_hsl(50, 75, 1, 100, 1, 100);
						break;
					case 'green':
						$this->_hsl(75, 170, 1, 100, 1, 100);
						break;
					case 'purple':
						$this->_hsl(235, 300, 1, 100, 1, 100);
						break;
					case 'orange':
						$this->_hsl(10, 50, 1, 100, 1, 100);
						break;
					case 'brown':
						$this->_hsl(null, null, null, null, 1, 20);
						break;
					case 'pink':
						$this->_hsl(300, 345, 1, 100, 1, 100);
						break;
					default:
						break;
				}
			}

			// Views
			if (!empty($_REQUEST['views'])) {
				switch($_REQUEST['views_operator']) {
					case 'greater':
						$this->_views($_REQUEST['views'], null);
						break;
					case 'less':
						$this->_views(null, $_REQUEST['views']);
						break;
					case 'equal':
						$this->_views($_REQUEST['views'], $_REQUEST['views']);
						break;
				}
			}

			// Orientation
			if (!empty($_REQUEST['orientation'])) {
				switch($_REQUEST['orientation']) {
					case 'portrait':
						$this->_ratio(1, null, null);
						break;
					case 'landscape':
						$this->_ratio(null, 1, null);
						break;
					case 'square':
						$this->_ratio(null, null, 1);
						break;
				}
			}

			// Privacy
			if (!empty($_REQUEST['privacy'])) {
				$this->_privacy($_REQUEST['privacy']);
			}

			// Published
			if (!empty($_REQUEST['published'])) {
				switch($_REQUEST['published']) {
					case 'published':
						$this->_published(true);
						break;
					case 'unpublished':
						$this->_published(false);
						break;
				}
			}

			// Sort
			if (!empty($_REQUEST['sort'])) {
				switch($_REQUEST['sort']) {
					case 'taken':
						$this->_sort($this->table . '.' . $this->table_prefix . 'taken', $_REQUEST['sort_direction']);
						$this->_notnull($this->table . '.' . $this->table_prefix . 'taken');
						break;
					case 'published':
						$this->_sort($this->table . '.' . $this->table_prefix . 'published', $_REQUEST['sort_direction']);
						$this->_notnull($this->table . '.' . $this->table_prefix . 'published');
						break;
					case 'uploaded':
						$this->_sort($this->table . '.' . $this->table_prefix . 'uploaded', $_REQUEST['sort_direction']);
						break;
					case 'updated':
						$this->_sort($this->table . '.' . $this->table_prefix . 'modified', $_REQUEST['sort_direction']);
						$this->_notnull($this->table . '.' . $this->table_prefix . 'modified');
						break;
					case 'title':
						$this->_sort($this->table . '.' . $this->table_prefix . 'title', $_REQUEST['sort_direction']);
						$this->_notnull($this->table . '.' . $this->table_prefix . 'title');
						break;
					case 'views':
						$this->_sort($this->table . '.' . $this->table_prefix . 'views', $_REQUEST['sort_direction']);
						break;
					default:
						break;
				}
			}
			
			// Status
			if (isset($_REQUEST['status'])) {
				$this->_status($_REQUEST['status']);
			}
			
			// Response
			if (isset($_REQUEST['response'])) {
				$this->_response($_REQUEST['response']);
			}
			
			// Image association
			if (!empty($_REQUEST['image'])) {
				$this->_image($_REQUEST['image']);
			}
		} //end if process request is true
	} //end object construction
	
	public function __destruct() {
//
	}
	
	/**
	 * Save methods (by appending an underscore to the method name) to memory for saving methods to sets, saved searches, etc.
	 *
	 * @param string $method Method name
	 * @param array $arguments Method arguments
	 * @return mixed
	 */
	public function __call($method, $arguments) {
		// Error checking
		if (substr($method, 0, 1) != '_') {
			return false;
		}
		
		// Determine real method
		$method = substr($method, 1);
		
		// Error checking
		if (!method_exists($this, $method)) {
			return false;
		}
		if (@count($arguments) < 1){
			$arguments = null;
		}
		
		// Execute method
		call_user_func_array(array($this, $method), $arguments);
		
		// Remove unsaveable methods
		$nosave_methods = array('page');
		
		if (in_array($method, $nosave_methods)) {
			return;
		}
			
		// Save to memory
		$this->call[] = array($method => $arguments);
	}
	
	/**
	 * Retrieve page numbers on demand
	 *
	 * @param $name page_#_uri
	 */
	public function __get($name) {
		if (substr($name, 0, 5) != 'page_') { return; }
		if (substr($name, -4) != '_uri') { return; }
		
		$page_number = intval(substr($name, 5, -4));
		
		return $this->magicURL($page_number);
	}
	
	/**
	 * Translate $this->ids array to comma-separated string
	 *
	 * @return string Comma-separated IDs
	 */
	public function __toString() {
        return implode(', ', $this->ids);
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
		
		$this->ids = $orbit->hook('find', $this->ids, $this->ids);
		$this->ids = $orbit->hook('find_' . $this->table, $this->ids, $this->ids);
	}

	/**
	 * Find by date taken
	 *
	 * @param string $begin Date begin
	 * @param string $end Date end
	 * @return bool True if successful
	 */
	public function taken($begin=null, $end=null) {
		// Error checking
		if (empty($begin) and empty($end)) { return false; }
		
		// Set begin date
		if (!empty($begin)) {
			if (is_int($begin)) { $begin = strval($begin); }
			if (strlen($begin) == 4) { $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'taken >= :image_taken_begin';
			$this->sql_params[':image_taken_begin'] = $begin . ' 00:00:00';
		}
		
		// Set end date
		if (!empty($end)) {
			if (is_int($end)) { $end = strval($end); }
			if (strlen($end) == 4) { $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'taken <= :image_taken_end';
			$this->sql_params[':image_taken_end'] = $end . ' 23:59:59"';
		}
		
		return true;
	}
	
	/**
	 * Find by date modified
	 *
	 * @param string $begin Date begin
	 * @param string $end Date end
	 * @return bool True if successful
	 */
	public function modified($begin=null, $end=null) {
		// Error checking
		if (empty($begin) and empty($end)) { return false; }
		
		// Set begin date
		if (!empty($begin)) {
			if (is_int($begin)) { $begin = strval($begin); }
			if (strlen($begin) == 4) { $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'modified >= :image_modified_begin';
			$this->sql_params[':image_modified_begin'] = $begin . ' 00:00:00';
		}
		
		// Set end date
		if (!empty($end)) {
			if (is_int($end)) { $end = strval($end); }
			if (strlen($end) == 4) { $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'modified <= :image_modified_end';
			$this->sql_params[':image_modified_end'] = $end . ' 23:59:59"';
		}
		
		return true;
	}
	
	/**
	 * Find by date published or publish status
	 *
	 * @param string|bool $begin Date begin or publish status
	 * @param string $end Date end
	 * @return bool True if successful
	 */
	public function published($begin=true, $end=null) {
		// Error checking
		if (!isset($begin) and empty($end)) { return false; }
		if (!empty($_SESSION['fsip']['preview'])) { return; }
		
		// Set status
		if ($begin === 'false') { 
			$published = false;
		} elseif($begin === 'true') { 
			$published = true;
		}
		
		$now = date('Y-m-d H:i:s');
		
		if ($begin === true) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'published < :image_published';
			$this->sql_params[':image_published'] = $now;
			return true;
		}
		if ($begin === false) {
			$this->sql_conds[] = '(' . $this->table . '.' . $this->table_prefix . 'published > :image_published OR ' . $this->table . '.' . $this->table_prefix . 'published IS NULL)';
			$this->sql_params[':image_published'] = $now;
			return true;
		}
		
		// Set auto-interval
		if (!empty($begin) and empty($end)) {
			if (is_int($begin)) { $begin = strval($begin); }
			if (strlen($begin) == 4) { $end = $begin . '-12-31'; $begin .= '-01-01'; }
			if ((strlen($begin) == 6) or (strlen($begin) == 7)) { $end = $begin . '-31'; $begin .= '-01'; }
			
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'published >= :image_published_begin';
			$this->sql_params[':image_published_begin'] = $begin . ' 00:00:00';
			
			if (empty($end)) { $end = $begin; }
			
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'published <= :image_published_end';
			$this->sql_params[':image_published_end'] = $end . ' 23:59:59"';
		} elseif(!empty($begin) and !empty($end)) {
			// Set interval
			if (is_int($begin)) { $begin = strval($begin); }
			if (strlen($begin) == 4) { $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'published >= :image_published_begin';
			$this->sql_params[':image_published_begin'] = $begin . ' 00:00:00';
			
			if (is_int($end)) { $end = strval($end); }
			if (strlen($end) == 4) { $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'published <= :image_published_end';
			$this->sql_params[':image_published_end'] = $end . ' 23:59:59"';
		} elseif(!empty($end)) {
			if (is_int($end)) { $end = strval($end); }
			if (strlen($end) == 4) { $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'published <= :image_published_end';
			$this->sql_params[':image_published_end'] = $end . ' 23:59:59"';
		}
		
		return true;
	}
	
	/**
	 * Find by date created
	 *
	 * @param string $begin Date begin 
	 * @param string $end Date end
	 * @return bool True if successful
	 */
	public function created($begin=null, $end=null) {
		// Error checking
		if (empty($begin) and empty($end)) { return false; }
		
		// Set begin date
		if (!empty($begin)) {
			if (!is_int($begin)) {
				if (strlen($begin) == 4) { $begin .= '-01-01'; }
				$begin = strtotime($begin);
			}
			$begin = date('Y-m-d H:i:s', $begin);
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'created >= :post_created_begin';
			$this->sql_params[':post_created_begin'] = $begin . ' 00:00:00';
		}
		
		// Set end date
		if (!empty($end)) {
			if (!is_int($end)) {
				if (strlen($end) == 4) { 
					$end .= '-01-01'; 
				}
				$end = strtotime($end);
			}
			$end = date('Y-m-d H:i:s', $end);
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'created <= :post_created_end';
			$this->sql_params[':post_created_end'] = $end . ' 23:59:59"';
		}
		
		return true;
	}
	
	/**
	 * Find by date uploaded
	 *
	 * @param string $begin Date begin
	 * @param string $end Date end
	 * @return bool True if successful
	 */
	public function uploaded($begin=null, $end=null) {
		// Error checking
		if (empty($begin) and empty($end)) { return false; }
		
		// Set begin date
		if (!empty($begin)) {
			if (is_int($begin)) { $begin = strval($begin); }
			if (strlen($begin) == 4) { $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'uploaded >= :image_uploaded_begin';
			$this->sql_params[':image_uploaded_begin'] = $begin . ' 00:00:00';
		}
		
		// Set end date
		if (!empty($end)) {
			if (is_int($end)) { $end = strval($end); }
			if (strlen($end) == 4) { $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'uploaded <= :image_uploaded_end';
			$this->sql_params[':image_uploaded_end'] = $end . ' 23:59:59"';
		}
		
		return true;
	}
	
	/**
	 * Find by number of views
	 *
	 * @param string $min Minimum views
	 * @param string $max Maximum views
	 * @return bool True if successful
	 */
	public function views($min=null, $max=null) {
		// Error checking
		if (empty($max) and empty($min)) { return false; }
		
		$min = intval($min);
		$max = intval($max);
		
		// Set maximum views
		if (!empty($max)) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'views <= ' . $max;
		}
		
		// Set minimum views
		if (!empty($min)) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'views >= ' . $min;
		}
		
		return true;
	}
	
	/**
	 * Find by tag search
	 *
	 * @param string $tags Tag search, can include boolean operators
	 * @return bool True if successful
	 */
	public function tags($tags=null) {
		// Error checking
		if (empty($tags)) { return false; }
		
		if (!preg_match('/(NOT|OR|AND)/', $tags)) {
			$pieces = array($tags, 'AND');
		} else {
			$pieces = preg_split('/(NOT|OR|AND)/', $tags, null, PREG_SPLIT_DELIM_CAPTURE);
		}
		$pieces = array_map('trim', $pieces);

		$any = array();
		$all = array();
		$not = array();

		for($i = 0; $i < count($pieces); ++$i) {
			if ((@$pieces[$i - 1] == 'NOT') and !in_array($pieces[$i], $any) and !in_array($pieces[$i], $all) and !in_array($pieces[$i], $not) and !empty($pieces[$i])) {
				$not[] = $pieces[$i];
			}
			if (((@$pieces[$i + 1] == 'OR') or (@$pieces[$i - 1] == 'OR')) and !in_array($pieces[$i], $any) and !in_array($pieces[$i], $any) and !in_array($pieces[$i], $all) and !in_array($pieces[$i], $not) and !empty($pieces[$i])) {
				$any[] = $pieces[$i];
			}
			if (((@$pieces[$i + 1] == 'AND') or (@$pieces[$i - 1] == 'AND') or (@$pieces[$i + 1] == 'NOT')) and !in_array($pieces[$i], $any) and !in_array($pieces[$i], $all) and !in_array($pieces[$i], $not) and !empty($pieces[$i])) {
				$all[] = $pieces[$i];
			}
		}
		
		$any_count = count($any) - count(array_keys($pieces, 'OR'));
		
		$this->anyTags($any, $any_count);
		$this->allTags($all);
		$this->notTags($not);
		
		return true;
	}
	
	/**
	 * Find by string|int|array joined by OR
	 *
	 * @param string $tags Tags to search for
	 * @param string $count Minimum number of tags to find
	 * @return bool True if successful
	 */
	public function anyTags($tags=null, $count=1) {
		// Error checking
		if (empty($tags)) { return false; }
		
		convertToArray($tags);
		
		if (($this->table == 'images') and empty($fields)) {
			// Find tags in database
			if (is_numeric($tags[0])) {
				convertToIntegerArray($tags);
				$query = $this->dbpointer->prepare('SELECT tags.tag_id FROM tags WHERE tags.tag_id = ' . implode(' OR tags.tag_id = ', $tags) . ';');
				$query->execute();
			} else {
				$sql_params = array();
				$tag_count = count($tags);
			
				// Grab tag IDs
				for($j=0; $j<$tag_count; ++$j) {
					$sql_params[':tag' . $j] = '%' . strtolower($tags[$j]) . '%';
				}
			
				$sql_param_keys = array_keys($sql_params);
			
				$query = $this->dbpointer->prepare('SELECT tags.tag_id FROM tags WHERE LOWER(tags.tag_name) LIKE ' . implode(' OR LOWER(tags.tag_name) LIKE ', $sql_param_keys) . ';');
				$query->execute($sql_params);
			}
		
			$this->tags = $query->fetchAll();
		
			$tag_ids = array();	
			foreach($this->tags as $tag) {
				$tag_ids[] = $tag['tag_id'];
			}
		
			// Join tables
			$this->sql_join_on[] = $this->table . '.' . $this->table_prefix . 'id = links.image_id';
			$this->sql_join_tables[] = 'links';
			$this->sql_join_type = 'INNER JOIN';
		
			$this->sql_having_fields[] = 'COUNT(*) >= ' . intval($count);
		
			// Set tags to find
			$this->sql_conds[] = '(links.tag_id = ' . implode(' OR links.tag_id = ', $tag_ids) . ')';
		
			return true;
		}
	}
	
	/**
	 * Find by tags joined by AND
	 *
	 * @param string|int|array $tags Tags to search for
	 * @return bool True if successful
	 */
	public function allTags($tags=null) {
		// Error checking
		if (empty($tags)) { return false; }
		
		$tag_count = count($tags);
		
		convertToArray($tags);
		
		// Find images with these tags in database
		if (intval($tags[0])) {
			convertToIntegerArray($tags);
			$query = $this->dbpointer->prepare('SELECT ' . $this->table . '.' .$this->table_prefix . 'id FROM images, links WHERE ' . $this->table . '.' .$this->table_prefix . 'id = links.image_id AND (links.tag_id = ' . implode(' OR links.tag_id = ', $tags) . ');');
			$query->execute();
		} else {
			$sql_params = array();
			$tag_count = count($tags);
			
			// Grab tag IDs
			for($j=0; $j<$tag_count; ++$j) {
				$sql_params[':tag' . $j] = '%' . strtolower($tags[$j]) . '%';
			}
			
			$sql_param_keys = array_keys($sql_params);
			
			$query = $this->dbpointer->prepare('SELECT ' . $this->table . '.' .$this->table_prefix . 'id FROM images, links, tags WHERE ' . $this->table . '.' .$this->table_prefix . 'id = links.image_id AND links.tag_id = tags.tag_id AND (LOWER(tags.tag_name) LIKE ' . implode(' OR LOWER(tags.tag_name) LIKE ', $sql_param_keys) . ');');
			$query->execute($sql_params);
		}
		
		$this->images = $query->fetchAll();
		
		// Comset image IDs
		$include_ids = array();	
		foreach($this->images as $image) {
			if (array_key_exists($image[$this->table_prefix . 'id'], $include_ids)) {
				$include_ids[$image[$this->table_prefix . 'id']]++;
			} else {
				$include_ids[$image[$this->table_prefix . 'id']] = 1;
			}
		}
		foreach($include_ids as $image_id => $count) {
			if ($count < $tag_count) {
				unset($include_ids[$image_id]);
			}
		}
		$include_ids = array_keys($include_ids);
		
		// Set fields to search
		if (count($include_ids) > 0) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (' . implode(', ', $include_ids) . ')';
		} else {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (NULL)';
		}
		
		return true;
	}
	
	/**
	 * Find by tags joined by NOT
	 *
	 * @param string|int|array $tags Tags to search for
	 * @return bool True if successful
	 */
	public function notTags($tags=null) {
		// Error checking
		if (empty($tags)) { return false; }
		
		convertToArray($tags);
		
		// Find images with these tags in database
		if (intval($tags[0])) {
			convertToIntegerArray($tags);
			$query = $this->dbpointer->prepare('SELECT ' . $this->table . '.' .$this->table_prefix . 'id FROM images, links WHERE ' . $this->table . '.' .$this->table_prefix . 'id = links.image_id AND (links.tag_id = ' . implode(' OR links.tag_id = ', $tags) . ');');
			$query->execute();
		} else {
			$sql_params = array();
			$tag_count = count($tags);
			
			// Grab tag IDs
			for($j=0; $j<$tag_count; ++$j) {
				$sql_params[':tag' . $j] = '%' . strtolower($tags[$j]) . '%';
			}
			
			$sql_param_keys = array_keys($sql_params);
			
			$query = $this->dbpointer->prepare('SELECT ' . $this->table . '.' .$this->table_prefix . 'id FROM images, links, tags WHERE ' . $this->table . '.' .$this->table_prefix . 'id = links.image_id AND links.tag_id = tags.tag_id AND (LOWER(tags.tag_name) LIKE ' . implode(' OR LOWER(tags.tag_name) LIKE ', $sql_param_keys) . ');');
			$query->execute($sql_params);
		}
		$this->images = $query->fetchAll();
		
		// Comset image IDs
		$exclude_ids = array();	
		foreach($this->images as $image) {
			$exclude_ids[] = $image[$this->table_prefix . 'id'];
		}
		$exclude_ids = array_unique($exclude_ids);
		
		if (count($exclude_ids) > 0) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id NOT IN (' . implode(', ', $exclude_ids) . ')';
		}
		
		return true;
	}
	
	/**
	 * Find by set
	 *
	 * @param int|string $set Set ID or set title
	 * @return void
	 */
	public function sets($set=null) {
		// Error checking
		if (empty($set)) { return false; }
		if (intval($set)) { $set = intval($set); }
		
		// Determine input type
		if (is_string($set)) {
			$query = $this->dbpointer->prepare('SELECT set_id, set_call, set_type, set_images, set_image_count FROM sets WHERE LOWER(set_title) LIKE :set_title_lower LIMIT 0, 1;');
			$query->execute(array(':set_title_lower' => strtolower($set)));
		} elseif(is_int($set)) {
			$query = $this->dbpointer->prepare('SELECT set_id, set_call, set_type, set_images, set_image_count FROM sets WHERE set_id = ' . $set . ' LIMIT 0, 1;');
			$query->execute();
		} else {
			return false;
		}
		
		$sets = $query->fetchAll();
		
		if (@count($sets) != 1) {
			return false;
		}
		
		$set = $sets[0];
		
		// If auto, apply stored functions
		if ($set['set_type'] == 'auto') {
			$ids = new Find('images', null, false, false);
			$ids->memory(unserialize($set['set_call']));
			$ids->find();
			
			$set_images = implode(', ', $ids->ids);
			
			// Update set if images have changed
			if ($set_images != $set['set_images']) {
				$fields = array('set_image_count' => $ids->count,
					'set_images' => $set_images);
				$this->updateRow($fields, 'sets', $set['set_id'], false);
			}
			
			if (!empty($ids->ids)) {
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (' . implode(', ', $ids->ids) . ')';
				foreach($ids->sql_sorts as $sql){
					$this->sql_sorts[] = $sql;
				}
			} else {
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (NULL)';
			}
		} elseif($set['set_type'] == 'static') { // If static, use stored image IDs
			if (!empty($set['set_images'])) {
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (' . $set['set_images'] . ')';
				$this->order = $this->convertToIntegerArray($set['set_images']);
			} else {
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (NULL)';
			}
		}
	}
	
	/**
	 * Find by whether response or not
	 *
	 * @param bool $response Is a response
	 * @return bool True if successful
	 */
	public function response($response=true) {
		$response = $this->convertToBool($response);
		
		if ($response === true) {
			$this->notnull($this->table . '.' . $this->table_prefix . 'response');
			return true;
		} elseif($response === false) {
			$this->null($this->table . '.' . $this->table_prefix . 'response');
			return true;
		}
		
		return false;
	}
	
	/**
	 * Find by title URL
	 *
	 * @param bool $title Title
	 * @return bool True if successful
	 */
	public function title($title) {
		$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'title_url = :title_url';
		$this->sql_params[':title_url'] = $title;
		
		return true;
	}
	
	// MEMORY
	
	/**
	 * Recall memory
	 *
	 * @param array $call set_call field, else uses recent memory
	 * @return bool True if successful
	 */
	public function memory($call=null) {
		if (empty($call)){
			if (!$call = $this->recentMemory()) {
				return false;
			}
		}
		
		foreach($call as $ring) {
			$method = key($ring);
			$arguments = $ring[$method];
			call_user_func_array(array($this, $method), $arguments);
		}
		
		return true;
	}
	
	/**
	 * Find by rights set
	 *
	 * @param int|string $right Right ID or right title
	 * @return void
	 */
	public function rights($right=null) {
		// Error checking
		if (empty($right)) { return false; }
		if (intval($right)) { $right = intval($right); }
		
		// Determine input type
		if (is_string($right)) {
			$query = $this->dbpointer->prepare('SELECT right_id FROM rights WHERE LOWER(right_title) LIKE :lower_right_title LIMIT 0, 1;');
			$query->execute(array(':lower_right_title' => strtolower($right)));
		} elseif(is_int($right)) {
			$query = $this->dbpointer->prepare('SELECT right_id FROM rights WHERE right_id = ' . $right . ' LIMIT 0, 1;');
			$query->execute();
		} else {
			return false;
		}
		
		$rights = $query->fetchAll();
		
		if (@count($rights) != 1) {
			return false;
		}
		
		$right = $rights[0];
		
		$this->sql_conds[] = $this->table . '.right_id = ' . $right['right_id'];
		
		return true;
	}
	
	/**
	 * Find by user (who uploaded or created the item)
	 *
	 * @param int|array $users User IDs
	 * @return bool True if successful
	 */
	public function user($users=null) {
		// Error checking
		if (empty($users)) { return false; }
		
		$users = convertToIntegerArray($users);
		
		$users_sql = array();
		
		foreach($users as $user) {
			$users_sql[] = $this->table . '.user_id = ' . $user;
		}
		
		$this->sql_conds[] = '(' . implode(' OR ', $users_sql) . ')';
		
		return true;
	}
	
	/**
	 * Find by categories
	 *
	 * @param string|array $categories Categories
	 * @return bool True if successful
	 */
	public function category($categories=null) {
		// Error checking
		if (empty($categories)) { return false; }
		
		$categories = convertToArray($categories);
		
		$categories_sql = array();
		
		foreach($categories as $category) {
			$categories_sql[] = $this->table . '.' . $this->table_prefix . 'category = ' . $category;
		}
		
		$this->sql_conds[] = '(' . implode(' OR ', $categories_sql) . ')';
		
		return true;
	}
	
	/**
	 * Find by search
	 * Images: image title, image description, image geography, and image tags
	 * Comments: comment text, comment author name, comment author URI, comment author email, comment author IP
	 *
	 * @param string $search Search query
	 * @param string|array $fields Required for tables not built into FSIP (or for overriding built-in search parameters)
	 * @return bool True if successful
	 */
	public function search($search=null, $fields=null) {
		// Error checking
		if (empty($search)) { return false; }
		
		$this->query = $search;
		
		if ($this->returnConf('sphinx_enabled')) {
			if ($table = array_search($this->table, $this->tables_index)) {
				if (class_exists('SphinxClient', false)) {
					$sphinx = new SphinxClient;
					$sphinx->setSortMode(SPH_SORT_RELEVANCE);
					if ($this->returnConf('sphinx_server')) {
						if ($this->returnConf('sphinx_port')) {
							$s->setServer($this->returnConf('sphinx_server'), $this->returnConf('sphinx_port'));
						} else {
							$s->setServer($this->returnConf('sphinx_server'));
						}
					}
					if ($this->returnConf('sphinx_max_exec')) {
						$s->setMaxQueryTime($this->returnConf('sphinx_max_exec'));
					}
					$response = $sphinx->query($search);
					
					if (empty($response['matches'])) {
						$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IS NULL';
						return true;
					}

					$results = $response['matches'];
					
					$ids = array();
					$table_results = array();

					foreach($results as $result) {
						if($result['attrs']['table'] == $table) {
							$ids[] = $result['attrs']['table_id'];
						}
						if(isset($table_results[$result['attrs']['table']])) {
							++$table_results[$result['attrs']['table']];
						}
						else {
							$table_results[$result['attrs']['table']] = 1;
						}
					}
					
					foreach($table_results as $table => $count) {
						$table = $this->tables_index[$table];
						$this->finds[] = array('find_table' => $table,
											'find_count' => $count);
					}
					
					if (count($ids) > 0) {
						$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (' . implode(', ', $ids) . ')';
						$this->order = $this->convertToIntegerArray($ids);
					} else {
						$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IS NULL';
					}

					return true;
				}
			}
		}
		
		// Prepare input
		$search_lower = strtolower($search);
		$search_lower = preg_replace('#[^a-z0-9]#si', '%', $search_lower);
		
		$search_lower_tags = explode('%', $search_lower);
		sort($search_lower_tags);
		$search_lower_tags = '%' . implode('%', $search_lower_tags) . '%';
		
		$search_lower = '%' . $search_lower . '%';
		
		$ids = array();
		
		if (($this->table == 'images') and empty($fields)) {
			// Search title, description
			$query = $this->dbpointer->prepare('SELECT images.image_id FROM images WHERE (LOWER(images.image_title) LIKE :image_title OR LOWER(images.image_description_raw) LIKE :image_description_raw OR LOWER(images.image_geo) LIKE :image_geo OR LOWER(images.image_tags) LIKE :image_tags)');
			$query->execute(array(':image_title' => $search_lower, ':image_description_raw' => $search_lower, ':image_geo' => $search_lower, ':image_tags' => $search_lower_tags));
			$images = $query->fetchAll();
		
			foreach($images as $image) {
				$ids[] = $image[$this->table_prefix . 'id'];
			}
		} elseif (($this->table == 'comments') and empty($fields)) {
			$query = $this->dbpointer->prepare('SELECT comments.comment_id FROM comments WHERE (LOWER(comment_text) LIKE :comment_text) OR (LOWER(comment_author_name) LIKE :comment_author_name) OR (LOWER(comment_author_uri) LIKE :comment_author_uri) OR (LOWER(comment_author_email) LIKE :comment_author_email) OR (LOWER(comment_author_ip) LIKE :comment_author_ip);');
			$query->execute(array(':comment_text' => $search_lower, ':comment_author_name' => $search_lower, ':comment_author_uri' => $search_lower, ':comment_author_email' => $search_lower, ':comment_author_ip' => $search_lower));
			$comments = $query->fetchAll();

			foreach($comments as $comment) {
				$ids[] = $comment['comment_id'];
			}
		} else {
			if (is_string($fields)) { $fields = array($fields); }
			
			$field_count = count($fields);
			if ($field_count > 0) {
				$query = $this->dbpointer->prepare('SELECT ' . $this->table . '.' . $this->table_prefix . 'id FROM ' . $this->table . ' WHERE (LOWER(' .  implode(' LIKE ?)) OR (LOWER(', $fields) . ' LIKE ?));');
			
				$search_array = array_fill(0, $field_count, $search_lower);
			
				$query->execute($search_array);
				$rows = $query->fetchAll();

				foreach($rows as $row) {
					$ids[] = $row[$this->table_prefix . 'id'];
				}
			}
		}
		
		if (count($ids) > 0) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (' . implode(', ', $ids) . ')';
		} else {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IS NULL';
		}
		
		return true;
	}
	
	/**
	 * Find by negative search
	 * Images: image title, image description, image geography, and image tags
	 * Comments: comment text, comment author name, comment author URI, comment author email, comment author IP
	 *
	 * @param string $search Search query
	 * @param string|array $fields Required for tables not built into FSIP (or for overriding built-in search parameters)
	 * @return bool True if successful
	 */
	public function hide($search=null, $fields=null) {
		// Error checking
		if (empty($search)) { return false; }
		
		// Prepare input
		$search_lower = strtolower($search);
		$search_lower = preg_replace('#\s#', '%', $search_lower);
		$search_lower = '%' . $search_lower . '%';
		
		$ids = array();
		
		if (($this->table == 'images') and empty($fields)) {
			// Search title, description
			$query = $this->dbpointer->prepare('SELECT images.image_id FROM images WHERE (LOWER(images.image_title) LIKE :image_title_lower OR LOWER(images.image_description) LIKE :image_description_lower OR LOWER(images.image_geo) LIKE :image_geo_lower)');
			$query->execute(array(':image_title_lower' => $search_lower, ':image_description_lower' => $search_lower, ':image_geo_lower' => $search_lower));
			$images = $query->fetchAll();
		
			foreach($images as $image) {
				$ids[] = $image[$this->table_prefix . 'id'];
			}
		
			// Search tags
			$query = $this->dbpointer->prepare('SELECT images.image_id FROM images, links, tags WHERE images.image_id = links.image_id AND links.tag_id = tags.tag_id AND (LOWER(tags.tag_name) LIKE :tag_name_lower);');
			$query->execute(array(':tag_name_lower' => $search_lower));
		
			$images = $query->fetchAll();
		
			foreach($images as $image) {
				$ids[] = $image[$this->table_prefix . 'id'];
			}
		} elseif (($this->table == 'comments') and empty($fields)) {
			$query = $this->dbpointer->prepare('SELECT comments.comment_id FROM comments WHERE (LOWER(comment_text) LIKE :comment_text) OR (LOWER(comment_author_name) LIKE :comment_author_name) OR (LOWER(comment_author_uri) LIKE :comment_author_uri) OR (LOWER(comment_author_email) LIKE :comment_author_email) OR (LOWER(comment_author_ip) LIKE :comment_author_ip);');
			$query->execute(array(':comment_text' => $search_lower, ':comment_author_name' => $search_lower, ':comment_author_uri' => $search_lower, ':comment_author_email' => $search_lower, ':comment_author_ip' => $search_lower));
			$comments = $query->fetchAll();

			foreach($comments as $comment) {
				$ids[] = $comment['comment_id'];
			}
		} else {
			if (is_string($fields)) { $fields = array($fields); }
			
			$field_count = count($fields);
			if ($field_count > 0) {
				$query = $this->dbpointer->prepare('SELECT ' . $this->table . '.' . $this->table_prefix . 'id FROM ' . $this->table . ' WHERE (LOWER(' .  implode(' LIKE ?)) OR (LOWER(', $fields) . ' LIKE ?));');
			
				$search_array = array_fill(0, $field_count, $search_lower);
			
				$query->execute($search_array);
				$rows = $query->fetchAll();

				foreach($rows as $row) {
					$ids[] = $row[$this->table_prefix . 'id'];
				}
			}
		}
		
		if (count($ids) > 0) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id NOT IN (' . implode(', ', $ids) . ')';
		}
		
		return true;
	}
	
	/**
	 * Find by privacy levels
	 *
	 * @param int|string|array $privacy Privacy ID or string
	 * @param string $all Also include all images of lower privacy levels
	 * @param bool $auto_guest Automatically handle guest privacy
	 * @return bool True if successful
	 */
	public function privacy($privacy=null, $all=true, $auto_guest=true) {
		// Error checking
		if (empty($privacy)) { return false; }
		//  if(intval($privacy)) { $privacy = intval($privacy); }
		if ($this->table != 'images') { return false; }
		if (!empty($_SESSION['fsip']['preview'])) { return; }
		
		if (!empty($_SESSION['fsip']['guest']) and ($auto_guest == true) and ($this->admin != true)) { return; }
		
		// Convert strings
		if (is_string($privacy)) {
			$privacy = strtolower($privacy);
			$levels = array('public' => 1, 'protected' => 2, 'private' => 3);
			$levels2 = array('1' => 1, '2' => 2, '3' => 3);
			if (array_key_exists($privacy, $levels)) {
				$privacy = $levels[$privacy];
			} elseif(array_key_exists($privacy, $levels2)) {
				$privacy = $levels2[$privacy];
			} else {
				return false;
			}
		}
		
		if (is_integer($privacy)) {
			// Set fields to search
			if ($all == true) {
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'privacy <= ' . $privacy;
			} else {
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'privacy = ' . $privacy;
			}
		} elseif(is_array($privacy)) {
			convertToIntegerArray($privacy);
			
			// Set fields to search
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'privacy IN (' . implode(', ', $privacy) . ')';
		} else {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Find by color (HSL)
	 *
	 * @param string $h_min 
	 * @param string $h_max 
	 * @param string $s_min 
	 * @param string $s_max 
	 * @param string $l_min 
	 * @param string $l_max 
	 * @return bool True if successful
	 */
	public function hsl($h_min, $h_max, $s_min, $s_max, $l_min, $l_max) {
		// Error checking
		if (!isset($h_min) and !isset($h_max) and !isset($s_min) and !isset($s_max) and !isset($l_min) and !isset($l_max)) { return false; }
		
		// H - Hue
		if (isset($h_min) and isset($h_max)) {
			
			if ($h_min > $h_max) {
				$this->sql_conds[] = '(' . $this->table . '.' .$this->table_prefix . 'color_h <= ' . intval($h_max) . ' OR ' . $this->table . '.' .$this->table_prefix . 'color_h >= ' . intval($h_min) . ')';
			} else {
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'color_h >= ' . intval($h_min);
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'color_h <= ' . intval($h_max);
			}
		}
		
		// S - Saturation
		if (isset($s_min) and isset($s_max)) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'color_s >= ' . intval($s_min);
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'color_s <= ' . intval($s_max);
		}
		
		// L - Lightness
		if (isset($l_min) and isset($l_max)) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'color_l >= ' . intval($l_min);
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'color_l <= ' . intval($l_max);
		}
		
		return true;
	}
	
	/**
	 * Find by image ratio (width/height)
	 *
	 * @param string|float|int $min Minimum ratio (0)
	 * @param string|float|int $max Maximum ratio (infinite)
	 * @param string|float|int $equal Search for precise ratio (1 = square)
	 * @return bool True if successful
	 */
	public function ratio($min=null, $max=null, $equal=null) {
		if (empty($min) and empty($max) and empty($equal)) {
			return false;
		}
		
		if (!empty($min)) {
			$min = floatval($min);
			if ($this->dbpointer->db_type == 'pgsql') {
				$this->sql_conds[] = '(CAST(' . $this->table . '.' .$this->table_prefix . 'width AS FLOAT) / CAST(' . $this->table . '.' .$this->table_prefix . 'height AS FLOAT)) < ' . $min;
			} else {
				$this->sql_conds[] = '(' . $this->table . '.' .$this->table_prefix . 'width / ' . $this->table . '.' .$this->table_prefix . 'height) < ' . $min;
			}
		}
		if(!empty($max)) {
			$max = floatval($max);
			if ($this->dbpointer->db_type == 'pgsql') {
				$this->sql_conds[] = '(CAST(' . $this->table . '.' .$this->table_prefix . 'width AS FLOAT) / CAST(' . $this->table . '.' .$this->table_prefix . 'height AS FLOAT)) >' . $max;
			} else {
				$this->sql_conds[] = '(' . $this->table . '.' .$this->table_prefix . 'width / ' . $this->table . '.' .$this->table_prefix . 'height) > ' . $max;
			}
		}
		if (!empty($equal)) {
			$equal = floatval($equal);
			if ($this->dbpointer->db_type == 'pgsql') {
				$this->sql_conds[] = '(CAST(' . $this->table . '.' .$this->table_prefix . 'width AS FLOAT) / CAST(' . $this->table . '.' .$this->table_prefix . 'height AS FLOAT)) = ' . $equal;
			} else {
				$this->sql_conds[] = '(' . $this->table . '.' .$this->table_prefix . 'width / ' . $this->table . '.' .$this->table_prefix . 'height) = ' . $equal;
			}
		}
		
		return true;
	}
	
	/**
	 * Find by pages
	 *
	 * @param int|array $id Page IDs
	 * @return bool True if successful
	 */
	public function pages($id=null) {
		if (empty($id)) { return false; }
		if (!intval($id)) { return false; }
		
		$id = intval($id);
		
		$pages = $this->getTable('pages', $id);
		
		$ids = array();
		
		foreach($pages as $page) {
			$ids_on_page = explode(', ', $page['page_images']);
			foreach($ids_on_page as $image_id) {
				$ids[] = $image_id;
			}
		}
		
		$ids = array_unique($ids);
		
		if (count($ids) > 0) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (' . implode(', ', $ids) . ')';
		} else {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (NULL)';
		}
		
		return true;
	}
	
	/**
	 * Find by image association
	 *
	 * @param int|array $image_ids Image IDs
	 * @return bool True if successful
	 */
	public function image($image_ids=null) {
		// Error checking
		if (empty($image_ids)) { return false; }
		
		$image_ids = convertToIntegerArray($image_ids);
		
		if (count($image_ids) > 0) {
			$this->sql_conds[] = $this->table . '.image_id IN (' . implode(', ', $image_ids) . ')';
		}
		
		return true;
	}
	
	/**
	 * Find by ID numbers
	 *
	 * @param int|array $image_ids IDs
	 * @return bool True if successful
	 */
	public function ids($ids=null) {
		// Error checking
		if (empty($ids)) { return false; }
		
		$ids = convertToIntegerArray($ids);
		
		if (!(count($ids) > 0)) { return false; }
		
		$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'id IN (' . implode(', ', $ids) . ')';
		
		return true;
	}
	
	/**
	 * Find by comment status
	 *
	 * @param int|string $status Comment status
	 * @return bool True if successful
	 */
	public function status($status=null) {
		// Error checking
		if (!isset($status)) { return false; }
		
		// Convert strings
		if (is_string($status)) {
			$levels = array('spam' => -1, '-1' => -1, 'unpublished' => 0, '0' => 0, 'published' => 1, '1' => 1);
			if (array_key_exists($status, $levels)) {
				$status = $levels[$status];
			} else {
				return false;
			}
			
			// Set fields to search
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'status = ' . $status;
		} elseif(is_integer($status)) {
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'status = ' . $status;
			
		} elseif(is_array($status)) {
			convertToIntegerArray($status);
			
			// Set fields to search
			$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'status IN (' . implode(', ', $status) . ')';
		} else {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Find by EXIF value
	 *
	 * @param string $value EXIF value
	 * @param string $name EXIF name (key)
	 * @return bool True if successful
	 */
	public function exifs($value, $name=null) {
		if (empty($value)) { return false; }
		
		// Add EXIFs to find
		$this->sql_tables[] = 'exifs';
		$this->sql_conds[] = 'exifs.image_id = ' . $this->table . '.' .$this->table_prefix . 'id';
		
		// Search EXIFs
		if (empty($name)) {
			$this->sql_conds[] = '(LOWER(exifs.exif_value) LIKE :exif_value)';
			$this->sql_params[':exif_value'] = '%' . strtolower($value) . '%';
		} else {
			$this->sql_conds[] = '(LOWER(exifs.exif_value) LIKE :exif_value AND LOWER(exifs.exif_name) LIKE :exif_name)';
			$this->sql_params[':exif_name'] = '%' . strtolower($name) . '%';
			$this->sql_params[':exif_value'] = '%' . strtolower($value) . '%';
		}
		
		return true;
	}
	
	/**
	 * Find by guest simulation
	 *
	 * @param int $id Guest ID
	 * @return bool True if successful
	 */
	public function guest($id=null) {
		if (empty($id)) { return false; }
		
		$guest = $this->getRow('guests', $id);
		
		if ($guest === false) {
			return false;
		}
		
		if (empty($guest['guest_sets'])) {
			$this->privacy('protected');
		} else {
			$this->sets(intval($guest['guest_sets']));
			$this->privacy('protected');
		}
		
		return true;
	}
	
	/**
	 * Smart searches, use GET[id] values where necessary
	 *
	 * @param string $kind Untagged, unpublished, displayed, modified, nonpublic, untitled, views, tags, guests, sets, me, users, rights, pages
	 * @return bool True if successful
	 */
	protected function smart($kind) {
		if (empty($kind)) {
			return false;
		}
		
		switch($kind) {
			case 'untagged':
				$this->_special('untagged');
				break;
			case 'unpublished':
				$this->_published(false);
				break;
			case 'live':
				$this->_status(1);
				break;
			case 'spam':
				$this->_status(-1);
				break;
			case 'pending':
				$this->_status(0);
				break;
			case 'displayed':
				$this->_published(true);
				$this->_privacy('public');
				break;
			case 'modified':
				$this->_sort($this->table_prefix . 'modified', 'DESC');
				break;
			case 'nonpublic':
				$this->_special('nonpublic');
				break;
			case 'untitled':
				$this->_special('untitled');
				break;
			case 'uncategorized':
				$this->_special('uncategorized');
				break;
			case 'views':
				$this->_sort($this->table_prefix . 'views', 'DESC');
				break;
			case 'tags':
				$this->_allTags(@intval($_GET['id']));
				break;
			case 'guests':
				$this->_guest(@intval($_GET['id']));
				break;
			case 'sets':
				$this->_sets(@intval($_GET['id']));
				break;
			case 'me':
				$this->_user(@intval($_SESSION['fsip']['user']['user_id']));
				break;
			case 'users':
				$this->_user(@intval($_GET['id']));
				break;
			case 'rights':
				$this->_rights(@intval($_GET['id']));
				break;
			case 'pages':
				$this->_pages(@intval($_GET['id']));
				break;
			default:
				return false;
				break;
		}
		
		return true;
	}
	
	/**
	 * Special searches
	 *
	 * @param string $kind Unpublished, untitled, uncategorized
	 * @return void
	 */
	protected function special($kind) {
		if (empty($kind)) {
			return false;
		}
		
		switch($kind) {
			case 'untagged':
				// Join tables
				$this->sql_join_on[] = $this->table . '.' . $this->table_prefix . 'id = links.image_id';
				$this->sql_join_tables[] = 'links';
				$this->sql_join_type = 'LEFT OUTER JOIN';
				
				// Set tags to find
				$this->sql_conds[] = 'links.link_id IS NULL';
				break;
			case 'untitled':
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'title IS NULL';
				break;
			case 'uncategorized':
				$this->sql_conds[] = $this->table . '.' . $this->table_prefix . 'category IS NULL';
				break;
			case 'nonpublic':
				$this->privacy(array(2, 3));
			default:
				return false;
				break;
		}
		
		return true;
	}
	
	/**
	 * Locate page that contains a particular ID
	 *
	 * @param int $id ID
	 * @return bool True if successful
	 */
	public function with($id) {
		// Error checking
		if (empty($id)) { return false; }
		if (!$id = intval($id)) { return false; }
		
		$this->with = $id;
		
		return true;
	}
	
	/**
	 * Paginate results
	 *
	 * @param int $page Page number
	 * @param int $limit Number of items per page
	 * @param int $first Number of items on the first page (if different)
	 * @return bool True if successful
	 */
	public function page($page=null, $limit=null, $first=null) {
		// Error checking
		if ($limit === 0) { return false; }
		if (empty($page)) {
			if (!empty($_REQUEST['page'])) { 
				$page = intval($_REQUEST['page']); 
			} else { 
				$page = 1; 
			}
		} else {
			$page = intval($page);
		}
		if ($page < 1) { return false; }
		if (empty($limit)) { $limit = LIMIT; }
		if (empty($first)) { $first = $limit; }
		
		// Store data to object
		$this->page = $page;
		$this->page_limit = intval($limit);
		$this->page_limit_first = intval($first);
		
		// Set SQL limit
		if ($page == 1) {
			$this->page_limit_curent = $this->page_limit_first; 
		} else { 
			$this->page_limit_curent = $this->page_limit; 
		}

		$this->page_begin = (($page - 1) * $this->page_limit_curent) - $this->page_limit_curent + $this->page_limit_first;
		$this->sql_limit = ' LIMIT ' . $this->page_begin . ', ' . $this->page_limit_curent;
		
		return true;
	}
	
	/**
	 * Set number of offset items (items that appear just before and after the requested page)
	 *
	 * @param int $length Number of items
	 * @return bool True if successful
	 */
	public function offset($length) {
		// Error checking
		if (!($length = intval($length))) { return false; }
		
		$this->offset_length = $length;
	}
	
	/**
	 * Find by location
	 *
	 * @param string $geo City name or latitude, longitude
	 * @param string $radius Search radius (in miles)
	 * @return bool True if successful
	 */
	public function location($geo='', $radius=50) {
		$place = new Geo($geo);
		
		if (!($radius = floatval($radius))) { return false; }
		
		$lat = $place->city['city_lat'];
		$long = $place->city['city_long'];
		
		$this->sql_conds[] = $this->table_prefix . 'geo_lat <= ' . ceil($lat + $radius);
		$this->sql_conds[] = $this->table_prefix . 'geo_lat >= ' . ceil($lat - $radius);
		$this->sql_conds[] = $this->table_prefix . 'geo_long <= ' . ceil($long + $radius);
		$this->sql_conds[] = $this->table_prefix . 'geo_long >= ' . ceil($long - $radius);
		$this->sql_conds[] = '3959 * acos(cos(radians(' . $lat . ')) * cos(radians(image_geo_lat)) * cos(radians(image_geo_long) - radians(' . $long . ')) + sin(radians(' . $lat . ')) * sin(radians(image_geo_lat))) <= ' . $radius;
		
		return true;
	}
	
	/**
	 * Sort results by table column
	 *
	 * @param string $column Table column
	 * @param string $sort Sort order (ASC or DESC)
	 * @return bool True if successful
	 */
	public function sort($column, $sort='ASC') {
		// Error checking
		if (empty($column)) { return false; }
		
		$column = $this->dbpointer->sanitize($column);
		
		$column = strtolower($column);
		$sort = strtoupper($sort);
		
		// More error checking
		if (($sort != 'ASC') and ($sort != 'DESC')) {
			return false;
		}
		
		// Set column, sort
		$this->sql_sorts[] = $column . ' ' . $sort;
		
		return true;
	}
	
	/**
	 * Find by table fields not null
	 *
	 * @param string $field Table field
	 * @return bool True if successful
	 */
	public function notnull($field) {
		if (empty($field)) { return false; }
		
		$field = $this->dbpointer->sanitize($field);
		
		$this->sql_conds[] = $field . ' IS NOT NULL';
		
		return true;
	}
	
	/**
	 * Find by table fields null
	 *
	 * @param string $field Table field
	 * @return bool True if successful
	 */
	public function null($field) {
//echo "in FIND class's NULL method field: $field<br />";
		if (empty($field)) { return false; }
		
		$field = $this->dbpointer->sanitize($field);
//echo "sql_conds before =<br />";
//print_r($this->sql_conds);
//echo "<br />";
		
		$this->sql_conds[] = $field . ' IS NULL';
//echo "sql_conds after =<br />";
//print_r($this->sql_conds);
//echo "<br />";

		return true;
	}
	
	/**
	 * Execute Find class to determine class variables
	 *
	 * @return array Result IDs
	 */
	public function find() {
//echo "in find method of find class 1<br />";
		// Prepare SQL
		$this->sql_from = ' FROM ' . implode(', ', $this->sql_tables);
//echo "in find method of find class 2. FROM: $this->sql_from <br />";

		if (count($this->sql_conds) > 0) {
			$this->sql_where = ' WHERE ' . implode(' AND ', $this->sql_conds);
//echo "in find method of find class 3. WHERE: $this->sql_where<br />";
//echo "sql_conds =<br />";
//print_r($this->sql_conds);
		}
		
		if (count($this->sql_sorts) > 0) {
			$this->sql_order_by = ' ORDER BY ' . implode(', ', $this->sql_sorts);
//echo "in find method of find class 3.1. ORDER BY: $this->sql_order_by<br />";
			if (($this->dbpointer->db_type == 'pgsql') or ($this->dbpointer->db_type == 'mssql')) {
				$sql_sorts = str_ireplace(' ASC', '', $this->sql_sorts);
				$sql_sorts = str_ireplace(' DESC', '', $this->sql_sorts);
				$this->sql_group_by .= ', ' . implode(', ', $sql_sorts);
			}
		} elseif (empty($this->order)) {
			$this->sql_order_by = ' ORDER BY ' . $this->table . '.' .$this->table_prefix . 'id DESC';
//echo "in find method of find class 3.2. ORDER BY: $this->sql_order_by<br />";
			if (($this->dbpointer->db_type == 'pgsql') or ($this->dbpointer->db_type == 'mssql')) {
				$this->sql_group_by .= ', ' . $this->table . '.' .$this->table_prefix . 'id';
			}
		}
		
		if ((count($this->sql_join_on) > 0) and (count($this->sql_join_tables) > 0) and (!empty($this->sql_join_type))) {
			$this->sql_join = ' ' . $this->sql_join_type . ' ' . implode(', ', $this->sql_join_tables) . ' ON ' . implode(', ', $this->sql_join_on);
//echo "in find method of find class 3.3. JOIN: $this->sql_join<br />";
		}
		
		if (count($this->sql_having_fields) > 0) {
			$this->sql_having = ' HAVING ' . implode(', ', $this->sql_having_fields);
//echo "in find method of find class 3.4. HAVING: $this->sql_having<br />";
		}

		// Prepare query without limit
		$this->sql .= $this->sql_from . $this->sql_join . $this->sql_where . $this->sql_group_by . $this->sql_having . $this->sql_order_by;
//echo "in find method of find class 4. SQL = $this->sql<br />";
//print_r($this->dbpointer);
		// Execute query without limit
		$query = $this->dbpointer->prepare($this->sql);
//echo "in find method of find class 5. sql was: $this->sql<br />";
//echo "in find method of find class 5.1 query was: $query<br />";
		$query->execute($this->sql_params);
//echo "in find method of find class 5.2 params were:<br /><code>";
//print_r($this->sql_params);
//echo "</code><br />";

		$images = $query->fetchAll();
//echo "in find method of find class 6, images are:<br /><code>";
//print_r($images);
//echo "</code><br />";
		// Grab images.ids of results
		$ids = array();
		foreach($images as $image) {
//echo "in find method of find class 6.5 adding image id:".intval($image[$this->table_prefix . 'id'])."<br />";
			$ids[] = intval($image[$this->table_prefix . 'id']);
		}
//echo "in find method of find class 7<br />";
		
		// Determine number of images
		$this->count = count($images);
		
		// Determine where "with" image id is placed in pages
		if (!empty($this->with)) {
			$key = array_search($this->with, $ids);
			if ($key === false) {
				return false;
			}
			
			if (empty($this->page_limit)) { $this->page_limit = LIMIT; }
			if (empty($this->page_limit_first)) { $this->page_limit_first = $this->page_limit; }
			
			if ($key < $this->page_limit_first) {
				$page = 1;
			} else {
				$page = intval(ceil((($key + 1) - $this->page_limit_first) / $this->page_limit) + 1);
			}
			
			$this->page($page, $this->page_limit, $this->page_limit_first);
		}
//echo "in find method of find class 8<br />";
		
		// Determine pagination
		if (!empty($this->page)) {
			$this->page_count = ceil(($this->count - $this->page_limit_first) / $this->page_limit) + 1;
			if ($this->page < $this->page_count) {
				$this->page_next = $this->page + 1;
			}
			if ($this->page > 1) {
				$this->page_previous = $this->page - 1;
			}
		}
//echo "in find method of find class 9<br />";
		
		// Add order, limit
		$this->sql .= $this->sql_limit;
		
		// Execute query with order, limit
		$query = $this->dbpointer->prepare($this->sql);
		$query->execute($this->sql_params);
		$images = $query->fetchAll();
		
		// Grab images.ids of results
		$this->ids = array();
		foreach($images as $image) {
			$this->ids[] = intval($image[$this->table_prefix . 'id']);
			if ($this->cache === true) {
				$last_modified = strtotime($image['last_modified']);
				if ($last_modified > $this->last_modified) {
					$this->last_modified = $last_modified;
				}
			}
		}
		
		if (!empty($this->order)) {
			$replacement_ids = array();
			foreach($this->order as $image_id) {
				if (in_array($image_id, $this->ids)) {
					$replacement_ids[] = $image_id;
				}
			}
			
			$replacement_append_ids = array();
			foreach($this->ids as $image_id) {
				if (!in_array($image_id, $this->order)) {
					$replacement_append_ids[] = $image_id;
				}
			}
			
			$this->ids = array_merge($replacement_ids, $replacement_append_ids);
		}
		
		// Count number of images in this found set of images
		$this->count_result = count($this->ids);
		
		// Determine offset images
		if (!empty($this->page_limit)) {
			if (!empty($this->offset_length)) {
				$offset = $this->page_begin - $this->offset_length;
				
				if ($offset < 0) {
					$length = $this->offset_length + $offset;
					$offset = 0;
				} else {
					$length = $this->offset_length;
				}
				
				$this->ids_before = array_slice($ids, $offset, $length, true);
				$this->ids_before = array_reverse($this->ids_before);
				
				if ($this->page == 1) {
					$offset = $this->page_begin + $this->page_limit_first;
				} else {
					$offset = $this->page_begin + $this->page_limit;
				}
				
				$this->ids_after = array_slice($ids, $offset, $this->offset_length, true);
			} else {
				$this->ids_before = array_slice($ids, 0, $this->page_begin, true);
				
				if ($this->page == 1) {
					$offset = $this->page_begin + $this->page_limit_first;
				} else {
					$offset = $this->page_begin + $this->page_limit;
				}
				$this->ids_after = array_slice($ids, $offset, null, true);
			}
			
			$this->ids_before = array_merge($this->ids_before);
			$this->ids_after = array_merge($this->ids_after);
		}
		
		// Determine keys of images
		$this->first = $this->page_begin + 1;
		$this->last = $this->page_begin + $this->page_limit;
		$this->first_reverse = $this->count - $this->first + 1;
		$this->last_reverse = $this->page_begin + $this->page_limit;
		
		// Determine URLs of image pages
		if (!empty($this->page_next)) {
			$this->page_next_uri = $this->magicURL($this->page_next);
		}
		
		if (!empty($this->page_previous)) {
			$this->page_previous_uri = $this->magicURL($this->page_previous);
		}

		// Create a page_navigation_string for templates to insert.
		$pnavstr = "";
		$this->page_navigation_string = $pnavstr;

		// Create a total_image_count variable for templates to insert.
		$query = $this->dbpointer->prepare('SELECT COUNT(*) as count FROM images WHERE 1;');
		$query->execute();
		$ttlImages = $query->fetchAll();
		$this->total_image_count = $ttlImages[0]['count'];
	
		// Create a published_public_image_count variable for templates to insert.
		$now = date('Y-m-d H:i:s');
		$query = $this->dbpointer->prepare("SELECT COUNT(*) as count FROM images WHERE image_published > 1 AND image_published < '$now'");
		$query->execute();
		$pubImages = $query->fetchAll();
		$this->published_public_image_count = $pubImages[0]['count'];		

		if ($this->page_count > 1) {
			$pnavstr .= "<p>";
			if (!empty($this->page_previous)) {
				for($i = 1; $i <= $this->page_previous; ++$i) {
					$page_uri = 'page_' . $i . '_uri';
					$pnavstr .= '<a href="' . $this->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
				}
			}
			$pnavstr .= '<span class="page_no">Page '. $this->page .' of '. $this->page_count. '</span>';
			if (!empty($this->page_next)) {
				for($i = $this->page_next; $i <= $this->page_count; ++$i){
					$page_uri = 'page_' . $i . '_uri';
					$pnavstr .=  '<a href="' . $this->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
				}
			}
		$pnavstr .= "</p>";
		$this->page_navigation_string = $pnavstr;
		}

		// Return images.ids
		return $this->ids;
	}

	
	// SEARCH MEMORY
	
	/**
	 * Save memory (after executing)
	 *
	 * @return void
	 */
	public function saveMemory() {
		$table = $this->table;
		
		$_SESSION['fsip']['search']['table'] = $table;
		$_SESSION['fsip']['search'][$table]['request'] = $_REQUEST;
		$_SESSION['fsip']['search'][$table]['call'] = $this->call;
		$_SESSION['fsip']['search'][$table]['ids'] = $this->ids;
	}
	
	/**
	 * Execute recent memory
	 *
	 * @return string|false
	 */
	public function recentMemory() {
		$table = $this->table;
		
		if (empty($_SESSION['fsip']['search'][$table]['call'])) {
			return false;
		}
		
		return $_SESSION['fsip']['search'][$table]['call'];
	}
	
	/**
	 * Clear the memory
	 *
	 * @return void
	 */
	public static function clearMemory() {
		unset($_SESSION['fsip']['search']);
	}

} //end class

?>