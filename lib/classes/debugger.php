<?php

class Debugger {
	
	/**
	 * Initiates Debugger class for debugging, logging and messaging methods.
	 *
	 * @return void
	 **/
	public function __construct() {
		//
	} // end __construct
	
	/**
	 * Terminates object, closes the database connection
	 *
	 * @return void
	 **/
	public function __destruct() {
		// nothing to do
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
	public static function addError($severity, $message=null, $filename=null, $line_number=null, $http_headers=null) {

		if (!(error_reporting() & $severity)) {
			// This error code is not included in error_reporting
			// return;
		}

		// Is exception?
		if (is_object($severity)) {
			$message = $severity->getMessage();
			$filename = $severity->getFile();
			$line_number = $severity->getLine();
			$severity = E_USER_ERROR;
		}
		
		if (is_string($severity)) {
			if (!is_array($http_headers)) {
				$http_headers_wrong_format = $http_headers;
				$http_headers = array();
				if (is_int($http_headers_wrong_format)) {
					$http_headers[] = $http_headers_wrong_format;
				}
				if (is_string($http_headers_wrong_format)) {
					$http_headers[] = $http_headers_wrong_format;
				}
			}
			foreach ($http_headers as $header) {
				if (!headers_sent()) {
					if (is_string($header)) {
						header($header, true);
					} elseif (is_integer($header)) {
						if ($header == 100) {
							header('HTTP/1.0 100 Continue', true);
							header('Status: 100 Continue', true);
						} elseif ($header == 101) {
							header('HTTP/1.0 101 Switching Protocols', true);
							header('Status: 101 Switching Protocols', true);
						} elseif ($header == 200) {
							header('HTTP/1.0 200 OK', true);
							header('Status: 200 OK', true);
						} elseif ($header == 201) {
							header('HTTP/1.0 201 Created', true);
							header('Status: 201 Created', true);
						} elseif ($header == 202) {
							header('HTTP/1.0 202 Accepted', true);
							header('Status: 202 Accepted', true);
						} elseif ($header == 203) {
							header('HTTP/1.0 203 Non-Authoritative Information', true);
							header('Status: 203 Non-Authoritative Information', true);
						} elseif ($header == 204) {
							header('HTTP/1.0 204 No Content', true);
							header('Status: 204 No Content', true);
						} elseif ($header == 205) {
							header('HTTP/1.0 205 Reset Content', true);
							header('Status: 205 Reset Content', true);
						} elseif ($header == 206) {
							header('HTTP/1.0 206 Partial Content', true);
							header('Status: 206 Partial Content', true);
						} elseif ($header == 300) {
							header('HTTP/1.0 300 Multiple Choices', true);
							header('Status: 300 Multiple Choices', true);
						} elseif ($header == 301) {
							header('HTTP/1.0 301 Moved Permanently', true);
							header('Status: 301 Moved Permanently', true);
						} elseif ($header == 302) {
							header('HTTP/1.0 302 Moved Temporarily', true);
							header('Status: 302 Moved Temporarily', true);
						} elseif ($header == 303) {
							header('HTTP/1.0 303 See Other', true);
							header('Status: 303 See Other', true);
						} elseif ($header == 304) {
							header('HTTP/1.0 304 Not Modified', true);
							header('Status: 304 Not Modified', true);
						} elseif ($header == 305) {
							header('HTTP/1.0 305 Use Proxy', true);
							header('Status: 305 Use Proxy', true);
						} elseif ($header == 307) {
							header('HTTP/1.0 307 Temporary Redirect', true);
							header('Status: 307 Temporary Redirect', true);
						} elseif ($header == 400) {
							header('HTTP/1.0 400 Bad Request', true);
							header('Status: 400 Bad Request', true);
						} elseif ($header == 401) {
							header('HTTP/1.0 401 Unauthorized', true);
							header('Status: 401 Unauthorized', true);
						} elseif ($header == 402) {
							header('HTTP/1.0 402 Payment Required', true);
							header('Status: 402 Payment Required', true);
						} elseif ($header == 403) {
							header('HTTP/1.0 403 Forbidden', true);
							header('Status: 403 Forbidden', true);
						} elseif ($header == 404) {
							header('HTTP/1.0 404 Not Found', true);
							header('Status: 404 Not Found', true);
						} elseif ($header == 405) {
							header('HTTP/1.0 405 Method Not Allowed', true);
							header('Status: 405 Method Not Allowed', true);
						} elseif ($header == 406) {
							header('HTTP/1.0 406 Not Acceptable', true);
							header('Status: 406 Not Acceptable', true);
						} elseif ($header == 407) {
							header('HTTP/1.0 407 Proxy Authentication Required', true);
							header('Status: 407 Proxy Authentication Required', true);
						} elseif ($header == 408) {
							header('HTTP/1.0 408 Request Timeout', true);
							header('Status: 408 Request Timeout', true);
						} elseif ($header == 409) {
							header('HTTP/1.0 409 Conflict', true);
							header('Status: 409 Conflict', true);
						} elseif ($header == 410) {
							header('HTTP/1.0 410 Gone', true);
							header('Status: 410 Gone', true);
						} elseif ($header == 411) {
							header('HTTP/1.0 411 Length Required', true);
							header('Status: 411 Length Required', true);
						} elseif ($header == 412) {
							header('HTTP/1.0 412 Precondition Failed', true);
							header('Status: 412 Precondition Failed', true);
						} elseif ($header == 413) {
							header('HTTP/1.0 413 Request Entity Too Large', true);
							header('Status: 413 Request Entity Too Large', true);
						} elseif ($header == 414) {
							header('HTTP/1.0 414 Request URI Too Large', true);
							header('Status: 414 Request URI Too Large', true);
						} elseif ($header == 415) {
							header('HTTP/1.0 415 Unsupported Media Type', true);
							header('Status: 415 Unsupported Media Type', true);
						} elseif ($header == 416) {
							header('HTTP/1.0 416 Request Range Not Satisfiable', true);
							header('Status: 416 Request Range Not Satisfiable', true);
						} elseif ($header == 417) {
							header('HTTP/1.0 417 Expectation Failed', true);
							header('Status: 417 Expectation Failed', true);
						} elseif ($header == 500) {
							header('HTTP/1.0 500 Internal Server Error', true);
							header('Status: 500 Internal Server Error', true);
						} elseif ($header == 501) {
							header('HTTP/1.0 501 Not Implemented', true);
							header('Status: 501 Not Implemented', true);
						} elseif ($header == 502) {
							header('HTTP/1.0 502 Bad Gateway', true);
							header('Status: 502 Bad Gateway', true);
						} elseif ($header == 503) {
							header('HTTP/1.0 503 Service Unavailable', true);
							header('Status: 503 Service Unavailable', true);
						} elseif ($header == 504) {
							header('HTTP/1.0 504 Gateway Timeout', true);
							header('Status: 504 Gateway Timeout', true);
						} elseif ($header == 505) {
							header('HTTP/1.0 505 HTTP Version Not Supported', true);
							header('Status: 505 HTTP Version Not Supported', true);
						} else {
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
			chdir(PATH); //error.php in root folder / PATH
			require('error.php');
			ob_flush();
			
			// Quit
			exit();
		}
		
		switch($severity) {
			case E_USER_NOTICE:
				$_SESSION['fsip']['errors'][] = array('constant' => $severity, 'severity' => 'notice', 'message' => $message, 'filename' => $filename, 'line_number' => $line_number);
				break;
			case E_USER_WARNING:
				$_SESSION['fsip']['errors'][] = array('constant' => $severity, 'severity' => 'warning', 'message' => $message, 'filename' => $filename, 'line_number' => $line_number);
				break;
			case E_USER_ERROR:
				try {
					throw new ErrorException($message, 0, E_USER_ERROR, $filename, $line_number);
				}
				catch(ErrorException $e) {
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
	public static function addException($e) {
		throw new FSIPException($e);
	}
	
	/**
	 * Return errors as HTML-formatted string
	 *
	 * @return void|string HTML-formatted notifications 
	 */
	public static function getErrors() {
		if (!isset($_SESSION['fsip']) || !isset($_SESSION['fsip']['errors'])) { 
			return; 
		}

		// check if admin user should be only one to see errors 
		if (returnConf('maint_debug_admin_only') !== false) {
			$user = new User();
			if (!empty($user) and !$user->isAdmin()) {
				// we're supposed to show this to admin users only, and the current user is not an admin, return
				return;
			}
		}

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
	 * Return debug data as string
	 *
	 * @return void|string with debug info and page execution time 
	 */
	public static function getDebugString() {
	//TODO DEH - beef this function up with greater detail 
	//TODO DEH add a new core function as addDebugNote and return those messages here
		if (returnConf('maint_debug') !== false) {
			// check if admin user should be only one to see errors 
			if (returnConf('maint_debug_admin_only') !== false) {
				$user = new User();
				if (!empty($user) and !$user->isAdmin()) {
					// we're supposed to show this to admin users only, and the current user is not an admin, return
					return;
				}
			}
			$_SESSION['fsip']['debug']['execution_time'] = microtime(true) - $_SESSION['fsip']['debug']['start_time'];
			return 'Execution time: ' . round($_SESSION['fsip']['debug']['execution_time'], 3) . ' seconds. Queries: ' . $_SESSION['fsip']['debug']['queries']  . '. ';
		}
	}
	
	/**
	 * Add message to error log
	 *
	 * @param string $message 
	 * @param string $number 
	 * @return void
	 */
	public function report($message, $number=null) {
		if (isset($_SESSION['fsip']['warning']) and ($_SESSION['fsip']['warning'] == $message))
		{ 
			return false;
		}
		
		$_SESSION['fsip']['warning'] = $message;
		
		// Format message
		$message = date('Y-m-d H:i:s') . "\t" . $message;
		if(!empty($number)){ $message .= ' (' . $number . ')'; }
		$message .= "\n";
		
		// Write message
		$handle = fopen(Files::correctWinPath(PATH . DB . 'log.txt'), 'a');
		if (@fwrite($handle, $message) === false) {
			$this->addError(E_USER_ERROR, 'Cannot write to report file');
		}
		fclose($handle);
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
		chdir(PATH . ADMINFOLDER); // error.php in /admin folder
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

	// RECORD STATISTIC
	// Record a visitor to statistics
	public function recordStat($page_type=null) {
		if (!returnConf('stat_enabled')) {
			return false;
		}
		
		if (returnConf('stat_ignore_user')) {
			$user = new User();
			if ($user->isLoggedIn(false)) {
				return;
			}
		}
		
		if (empty($_SESSION['fsip']['duration_start']) or ((time() - @$_SESSION['fsip']['duration_recent']) > 3600)) {
			$duration = 0;
			$_SESSION['fsip']['duration_start'] = time();
		} else {
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
		} else {
			$local = 1;
		}

		if ((BASE != '/') and (stripos($page, BASE) === 0)) {
			$page = substr($page, strlen(BASE) - 1);
		}
		
		$query = $this->db->prepare('INSERT INTO stats (stat_session, stat_date, stat_duration, stat_referrer, stat_page, stat_page_type, stat_local) VALUES (:stat_session, :stat_date, :stat_duration, :stat_referrer, :stat_page, :stat_page_type, :stat_local);');
		
		$query->execute(array(':stat_session' => session_id(), ':stat_date' => date('Y-m-d H:i:s'), ':stat_duration' => $duration, ':stat_referrer' => $referrer, ':stat_page' => $page, ':stat_page_type' => $page_type, ':stat_local' => $local));
		
		if (isset($_SESSION['fsip']['guest'])) {
			$_SESSION['fsip']['guest']['guest_views']++;
			$this->db->exec('UPDATE guests SET guest_views = ' . $_SESSION['fsip']['guest']['guest_views'] . ' WHERE guest_id = ' . $_SESSION['fsip']['guest']['guest_id'] . ';');
		}
	}
}