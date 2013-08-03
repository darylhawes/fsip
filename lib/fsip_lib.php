<?php

define('FSIP_VERSION', '1.2');
define('FSIP_BUILD', '1300');
define('COPYRIGHT', '');
define('POWERED_BY', 'Powered by <a href="http://github.com/darylhawes/fsip">FSIP</a> based on <a href="http://www.alkalineapp.com/">Alkaline</a> under MIT license.');

if (function_exists('__autoload')) {
	return;
} else {
	function __autoload($class) {
		$file = strtolower($class) . '.php';
		if (file_exists(PATH . CLASSES . $file)) {
			require_once(PATH . CLASSES . $file);
		}
	}
}

// Begin a session, if one does not yet exist
if (session_id() == '') { 
	session_start(); 
}

// Disable magic_quotes
if (get_magic_quotes_gpc()) {
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	while(list($key, $val) = each($process)) {
		foreach($val as $k => $v) {
			unset($process[$key][$k]);
			if (is_array($v)) {
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			} else {
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}

// Set back link
if (!empty($_SERVER['HTTP_REFERER']) and ($_SERVER['HTTP_REFERER'] != LOCATION . $_SERVER['REQUEST_URI'])) {
	$_SESSION['fsip']['back'] = $_SERVER['HTTP_REFERER'];
} 

// This section was wrapped in a (if class == fsip) statement to remove old Orbit class extensions.
//unset($_SESSION['fsip']['extensions']);

// Log-in GUESTS via cookie
/*if (!empty($_COOKIE['guest_key']) and !empty($_COOKIE['guest_id']) and empty($_SESSION['fsip']['guest'])) {
	$query = $this->db->prepare('SELECT * FROM guests WHERE guest_id = :guest_id LIMIT 0, 1;');
	$query->execute(array(':guest_id' => $_COOKIE['guest_id']));
	$guests = $query->fetchAll();
	$guest = $guests[0];
	
	if ($_COOKIE['guest_key'] == sha1(PATH . BASE . DB_DSN . DB_TYPE . $guest['guest_key'])) {
		$this->access($guest['guest_key']);
	}
}*/

// Set a few globals
$debugger = new Debugger;
$db = new DB();

// DEBUG INFO

// Set error handlers
set_error_handler(array($debugger, 'addError'), E_ALL);
set_exception_handler(array($debugger, 'addException'));

// Set error reporting
if (ini_get('error_reporting') > 30719) {
	error_reporting(E_ALL);
}

if (!headers_sent()) {
	header('Cache-Control: no-cache, must-revalidate', false);
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT', false);
}

$_SESSION['fsip']['debug']['start_time'] = microtime(true);
$_SESSION['fsip']['debug']['queries'] = 0;

loadConf();

if (empty($_SESSION['fsip']['config'])) {
	$_SESSION['fsip']['config'] = array();
}

if ($timezone = returnConf('web_timezone')) {
	date_default_timezone_set($timezone);
} else {
	date_default_timezone_set('GMT');
}

// If php has access to less than 128M memory attempt to increase it and add a notification warning if unable to.
$mem = ini_get('memory_limit');
if (substr($mem, -1) == 'M') {
	if (substr($mem, 0, strlen($mem) - 1) < 128) {
		if (!ini_set('memory_limit', '128M')) {
			addNote("Warning: Memory is less than 128MB. You may have trouble with some features.");
		}
	}
}


/////////////////////// FSIP CORE LIBRARY FUNCTIONS //////////////////////////////////

//////////////// TIME FORMATTING

/**
 * Make time more human-readable
 *
 * @param string $time Time
 * @param string $format Format (as in date();)
 * @param string $empty If null or empty input time, return this string
 * @return string|false Time or error
 */
function formatTime($time=null, $format=null, $empty=false) {
	// Error checking
	if(empty($time) or ($time == '0000-00-00 00:00:00')) {
		return $empty;
	}
	if (empty($format)) {
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
function formatRelTime($time, $format=null, $empty=false, $round=null) {
	// Error checking
	if (empty($time) or ($time == '0000-00-00 00:00:00')) {
		return $empty;
	}
	if (empty($format)) {
		$format = DATE_FORMAT;
	}
	
	if (!is_integer($time)) {
		$time = str_ireplace(' at ', ' ', $time);
		$time = str_ireplace(' on ', ' ', $time);
	
		$time = strtotime($time);
	}
	
	$now = time();
	$seconds = $now - $time;
	$day = $now - strtotime(date('Y-m-d', $time));
	$month = $now - strtotime(date('Y-m', $time));
	
	if (is_integer($round)) {
		$seconds = round($seconds, $round);
	}
	
	if (empty($seconds)) {
		$span = 'just now';
	} else {
		switch($seconds) {
			case(empty($seconds) or ($seconds < 15)):
				$span = 'just now';
				break;
			case($seconds < 3600):
				$minutes = intval($seconds / 60);
				if($minutes < 2) { 
					$span = 'a minute ago';
				} else { 
					$span = $minutes . ' minutes ago'; 
				}
				break;
			case($seconds < 86400):
				$hours = intval($seconds / 3600);
				if($hours < 2) { 
					$span = 'an hour ago'; 
				} else { 
					$span = $hours . ' hours ago'; 
				}
				break;
			case($seconds < 2419200):
				$days = floor($day / 86400);
				if ($days < 2) { 
					$span = 'yesterday'; 
				} else { 
					$span = $days . ' days ago'; 
				}
				break;
			case($seconds < 29030400):
				$months = floor($month / 2419200);
				if($months < 2) { 
					$span = 'a month ago'; 
				} else { 
					$span = $months . ' months ago'; 
				}
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
function numberToMonth($num) {
	$int = intval($num);
	switch($int) {
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
function numberToWords($num, $power = 0, $powsuffix = '') {
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
	if (substr($num, 0, 1) == '-') {
		$ret = $_sep . $_minus;
		$num = substr($num, 1);
	}

	// strip excessive zero signs and spaces
	$num = trim($num);
	$num = preg_replace('/^0+/', '', $num);

	if (strlen($num) > 3) {
		$maxp = strlen($num)-1;
		$curp = $maxp;
		for($p = $maxp; $p > 0; --$p) { // power
			// check for highest power
			if (isset($_exponent[$p])) {
				// send substr from $curp to $p
				$snum = substr($num, $maxp - $curp, $curp - $p + 1);
				$snum = preg_replace('/^0+/', '', $snum);
				if ($snum !== '') {
					$cursuffix = $_exponent[$power][count($_exponent[$power])-1];
					if($powsuffix != ''){
						$cursuffix .= $_sep . $powsuffix;
					}

					$ret .= toWords($snum, $p, $cursuffix);
				}
				$curp = $p - 1;
				continue;
			}
		}
		$num = substr($num, $maxp - $curp, $curp - $p + 1);
		if ($num == 0) {
			return $ret;
		}
	} elseif($num == 0 || $num == '') {
		return $_sep . $_digits[0];
	}

	$h = $t = $d = 0;

	switch(strlen($num)) {
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

	if ($h) {
		$ret .= $_sep . $_digits[$h] . $_sep . 'hundred';

		// in English only - add ' and' for [1-9]01..[1-9]99
		// (also for 1001..1099, 10001..10099 but it is harder)
		// for now it is switched off, maybe some language purists
		// can force me to enable it, or to remove it completely
		// if(($t + $d) > 0)
		//   $ret .= $_sep . 'and';
	}

	// ten, twenty etc.
	switch ($t) {
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
		switch($d) {
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

	if ($t != 1 && $d > 0) { // add digits only in <0>,<1,9> and <21,inf>
		// add minus sign between [2-9] and digit
		if ($t > 1) {
			$ret .= '-' . $_digits[$d];
		} else {
			$ret .= $_sep . $_digits[$d];
		}
	}

	if ($power > 0) {
		if (isset($_exponent[$power])) {
			$lev = $_exponent[$power];
		}

		if (!isset($lev) || !is_array($lev)) {
			return null;
		}

		$ret .= $_sep . $lev[0];
	}

	if ($powsuffix != '') {
		$ret .= $_sep . $powsuffix;
	}

	return $ret;
}

//////////////// TYPE CONVERSION

/**
 * Convert a possible string to boolean
 *
 * @param mixed $input
 * @param mixed $default Return if unknown
 * @return boolean
 */
function convertToBool(&$input, $default='') {
	if (is_bool($input)) {
		return $input;
	} elseif(is_string($input)) {
		if($input == 'true') {
			return true;
		} elseif($input == 'false') {
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
function convertToArray(&$input) {
	if (is_string($input)) {
		$find = strpos($input, ',');
		if ($find === false) {
			$input = array($input);
		} else {
			$input = explode(',', $input);
			$input = array_map('trim', $input);
		}
	} elseif(is_int($input)) {
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
function convertToIntegerArray(&$input) {
	if (is_int($input)) {
		$input = array($input);
	} elseif(is_string($input)) {
		$find = strpos($input, ',');
		if ($find === false) {
			$input = array(intval($input));
		} else {
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
function convertToBytes(&$input) {
	if (is_string($input)) {
		if (stripos($input, 'K') !== false) {
			$input = intval($input) * 1000;
		} elseif (stripos($input, 'M') !== false) {
			$input = intval($input) * 1000000;
		} elseif (stripos($input, 'G') !== false) {
			$input = intval($input) * 1000000000;
		}
	}
	
	return intval($input);
}

/**
 * Convert the bytes converted PHP configuration string to shortened string
 *
 * @param mixed $input 
 * @return array
 */
function convertBytesToShortString($a) {
	$unim = array("B","KB","MB","GB","TB","PB");
	$c = 0;
	while ($a>=1024) {
		$c++;
		$a = $a/1024;
	}
	return number_format($a,($c ? 2 : 0),".",".").$unim[$c];
}	

/**
 * Change filename extension
 *
 * @param string $file Filename
 * @param string $ext Desired extension
 * @return string Changed filename
 */
function changeExt($file, $ext) {
	$file = preg_replace('#\.([a-z0-9]*?)$#si', '.' . $ext, $file);
	return $file;
}


//////////////// FORMAT STRINGS

/**
 * Convert to Unicode (UTF-8)
 *
 * @param string $string 
 * @return string
 */
function makeUnicode($string) {
	return mb_detect_encoding($string, 'UTF-8') == 'UTF-8' ? $string : utf8_encode($string);
}

/**
 * Make HTML-safe quotations
 *
 * @param string $input 
 * @return string
 */
function makeHTMLSafe($input) {
	if (is_string($input)) {
		$input = makeHTMLSafeHelper($input);
	}
	if (is_array($input)) {
		foreach($input as &$value) {
			$value = makeHTMLSafe($value);
		}
	}
	
	return $input;
}

function makeHTMLSafeHelper($string) {
	$string = htmlentities($string, ENT_QUOTES, 'UTF-8', false);
	return $string;
}

/**
 * Reverse HTML-safe quotations
 *
 * @param string $input 
 * @return string
 */
function reverseHTMLSafe($input) {
	if (is_string($input)) {
		$input = reverseHTMLSafeHelper($input);
	}
	if (is_array($input)) {
		foreach($input as &$value) {
			$value = reverseHTMLSafe($value);
		}
	}
	
	return $input;
}

function reverseHTMLSafeHelper($string) {
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
function makeFilenameSafe($str) {
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
function reverseFilenameSafe($str) {
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
function stripTags($var) {
	if (is_string($var)) {
		$var = trim(strip_tags($var));
	} elseif (is_array($var)) {
		foreach($var as $key => $value) {
			$var[$key] = stripTags($value);
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
function closeTags($html){
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
function countWords($string) {
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
function randInt($min=null, $max=null) { 
	if (function_exists('mt_rand')) {
		if (empty($max)) { 
			$max = mt_getrandmax();
		}
		$num = mt_rand($min, $max);
	} else {
		if (empty($max)) { 
			$max = getrandmax(); 
		}
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
// was function getType($var) 
function fsip_getType($var) {
	if (is_array($var)) { return 'array'; }
	if (is_bool($var)) { return 'boolean'; }
	if (is_float($var)) { return 'float'; }
	if (is_int($var)) { return 'integer'; }
	if (is_null($var)) { return 'NULL'; }
	if (is_numeric($var)) { return 'numeric'; }
	if (is_object($var)) { return 'object'; }
	if (is_resource($var)) { return 'resource'; }
	if (is_string($var)) { return 'string'; }
	return 'unknown';
}


//////////////// FORM HANDLING

/**
 * Set form option
 *
 * @param string $array 
 * @param string $name 
 * @param string $unset 
 * @return void
 */
function setForm(&$array, $name, $unset='') {
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
function readForm($array=null, $name, $check=true) {
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
function returnForm($array, $name, $default=null){
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
function setConf($name, $unset='') {
	return setForm($_SESSION['fsip']['config'], $name, $unset);
}

/**
 * Return HTML-formatted configuration key
 *
 * @param string $name 
 * @param string $check 
 * @return string
 */
function readConf($name, $check=true) {
	return readForm($_SESSION['fsip']['config'], $name, $check);
}

/**
 * Return configuration key
 *
 * @param string $name 
 * @return string
 */
function returnConf($name) {
	return makeHTMLSafe(returnForm($_SESSION['fsip']['config'], $name));
}

/**
 * Save configuration to database as a json string
 *
 * @return ?
 */
function saveConf() {
	global $db;
	$json_config_string = json_encode(reverseHTMLSafe($_SESSION['fsip']['config']));
	return $db->exec('UPDATE config SET json = ' . $json_config_string);
}

/**
 * Load configuration from database and convert from json string to array contents
 *
 * @return null
 */
function loadConf() {
	$query = $db->prepare('SELECT json FROM config');
	$query->execute();
	$json_config_strings = $query->fetchAll();
	$json_config_string = @$json_config_strings[0];

	$_SESSION['fsip']['config'] = json_decode($json_config_string, true);
}


//////////////// COMMENTS

/**
 * Add comments from $_POST data
 *
 * @return int|false Comment ID or false on failure
 */
function addComments() {
	global $db;
	// Configuration: comm_enabled
	if (!returnConf('comm_enabled')) {
		return false;
	}
	
	if (!empty($_POST['image_id'])) {
		$id = findID($_POST['image_id']);
		$id_type = 'image_id';
	}
	
	// Configuration: comm_mod
	if (returnConf('comm_mod')) {
		$comment_status = 0;
	} else {
		$comment_status = 1;
	}
	
	$comment_text_raw = $_POST['comment_' . $id .'_text'];
	
	if (empty($comment_text_raw)) {
		return false;
	}
	
	$orbit = new Orbit;
	
	// Configuration: comm_markup
	if (returnConf('comm_markup')) {
		$comm_markup_ext = returnConf('comm_markup_ext');
		$comment_text = $orbit->hook('markup_' . $comm_markup_ext, $comment_text_raw, null);
	} else {
		$comm_markup_ext = '';
		$comment_text = fsip_nl2br($comment_text_raw);
	}
	
	if (returnConf('comm_allow_html')) {
		$comment_text = strip_tags($comment_text, returnConf('comm_allow_html_tags'));
	} else {
		$comment_text = strip_tags($comment_text);
	}
	
	$fields = array($id_type => $id,
		'comment_status' => $comment_status,
		'comment_text' => makeUnicode($comment_text),
		'comment_text_raw' => makeUnicode($comment_text_raw),
		'comment_markup' => $comm_markup_ext,
		'comment_author_name' => strip_tags($_POST['comment_' . $id .'_author_name']),
		'comment_author_uri' => strip_tags($_POST['comment_' . $id .'_author_uri']),
		'comment_author_email' => strip_tags($_POST['comment_' . $id .'_author_email']),
		'comment_author_ip' => $_SERVER['REMOTE_ADDR']);
	
	$fields = $orbit->hook('comment_add', $fields, $fields);
	
	if (!$comment_id = $db->addRow($fields, 'comments')) {
		return false;
	}
	
	if (returnConf('comm_email')) {
		email(0, 'New comment', 'A new comment has been submitted:' . "\r\n\n" . strip_tags($comment_text) . "\r\n\n" . LOCATION . BASE . ADMINFOLDER . 'comments' . URL_ID . $comment_id . URL_RW);
	}
	
	if ($id_type == 'image_id') {
		$db->updateCount('comments', 'images', 'image_comment_count', $id);
	}
	
	return $comment_id;
}



//////////////// URL HANDLING

/**
 * Redirect to another URL using header(). 
 * There must not have been any output printed prior in order for this to work.
 *
 * @param string $location The URL to redirect to.
 */	
function headerLocationRedirect($location) {
	header('Location: ' . $location);
	echo "<h1>Redirecting</h1>";
	echo "<p>You are being redirected. If you're still here after a few seconds please ";
	echo '<a href="'. $location .'">'."click here</a></p>";
}

/**
 * Find ID number from string
 *
 * @param string $string Input string
 * @param string $numeric_required If true, will return false if number not found
 * @return int|string|false ID, string, or error
 */
function findID($string, $numeric_required=false) {
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
function findIDRef($str) {
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
function findMetaRef($html) {
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
function makeURL($string) {
	$string = html_entity_decode($string, 1, 'UTF-8');
	$string = removeAccents($string);
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
function removeAccents($string) {
	if (!preg_match('/[\x80-\xff]/', $string)) {
		return $string;
	}

	if (seems_utf8($string)) {
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

function seems_utf8($str) {
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
function minimizeURL($url) {
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
function magicURL($page) {
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
function fitString($string, $length=50) {
	$length = intval($length);
	if ($length < 3) 
	{ 
		return false;
	}
	
	$string = trim($string);
	if (strlen($string) > $length) {
		$string = rtrim(substr($string, 0, $length - 3)) . '&#0133;';
		$string = closeTags($string);
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
function fitStringByWord($string, $length=50) {
	$length = intval($length);
	if ($length < 3) { return false; }
	
	$string = trim($string);
	if (strlen($string) > $length) {
		$space = strpos($string, ' ', $length);
		if($space !== false){
			$string = substr($string, 0, $space) . '&#0133;';
			$string = closeTags($string);
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
// was function nl2br($str) 
function fsip_nl2br($str) {
	$str = fsip_nl2br($str);
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
function returnCount($count, $singular, $plural=null) {
	if (empty($plural)) {
		$plural = $singular . 's';
	}
	
	if ($count == 1) {
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
function returnFullCount($count, $singular, $plural=null) {
	$count = number_format($count) . ' ' . returnCount($count, $singular, $plural);
	
	return $count;
}

/**
 * If Windows Server, make path Windows-friendly
 *
 * @param string $path
 * @return string
 */
function correctWinPath($path) {
	if (SERVER_TYPE == 'win') {
		$path = str_replace('/', '\\', $path);
	}
	return $path;
}

/**
 * Compare two strings
 *
 * @param string $string1 
 * @param string $string2 
 * @return string
 */
function compare($string1, $string2) {
	require_once(PATH . CLASSES . 'text_diff/Diff.php');
	require_once(PATH . CLASSES . 'text_diff/Diff/Renderer/inline.php');
	
	$lines1 = explode("\n", $string1);
	$lines2 = explode("\n", $string2);
	
	$diff     = new Text_Diff('auto', array($lines1, $lines2));
	$renderer = new Text_Diff_Renderer_inline();
	return fsip_nl2br($renderer->render($diff));
}

// REDIRECT HANDLING

/**
 * Current page for redirects (removes all GET variables except page)
 *
 * @param array $get Append to URL (GET variables as associative array)
 * @return string
 */
function location($get=null) {
	$location = LOCATION;
	$location .= preg_replace('#\?.*$#si', '', $_SERVER['REQUEST_URI']);
	
	// Retain page data
	preg_match('#page=[0-9]+#si', $_SERVER['REQUEST_URI'], $matches);
	if (!empty($matches[0])) {
		$location .= '?' . $matches[0];
		if (!empty($params)) {
			$location .= '&' . http_build_query($get);
		}
	} elseif(!empty($params)) {
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
function locationFull($get=null) {
	if (!empty($array) and !is_array($get))
	{ 
		return false; 
	}
	$location = LOCATION . $_SERVER['REQUEST_URI'];
	if (!empty($get)) {
		if (preg_match('#\?.*$#si', $location)) {
			$location .= '&' . http_build_query($get);
		} else {
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
function setCallback($page=null) {
	if (!empty($page)) {
		$_SESSION['fsip']['callback'] = $page;
	} else {
		$_SESSION['fsip']['callback'] = location();
	}
}

/**
 * Send to callback location
 *
 * @param string $url Fallback URL if callback URL isn't set
 * @return void
 */
function callback($url=null) {
	unset($_SESSION['fsip']['go']);
	if(!empty($_SESSION['fsip']['callback'])) {
		$location = $_SESSION['fsip']['callback'];
		headerLocationRedirect($location);
	} elseif(!empty($url)) {
		$location= $url;
		headerLocationRedirect($location);
	} else {
		$location = LOCATION . BASE . ADMINFOLDER . 'dashboard/';
		headerLocationRedirect($location);
	}
	exit();
}

/**
 * Send back (for cancel links)
 *
 * @return void
 */
function back() {
	if (!empty($_SESSION['fsip']['back'])) {
		echo $_SESSION['fsip']['back'];
	} elseif (!empty($_SERVER['HTTP_REFERER'])) {
		echo $_SERVER['HTTP_REFERER'];
	} else {
		echo LOCATION . BASE . ADMINFOLDER . 'dashboard/';
	}
}

/**
 * Sift through a URI (http://www.whatever.com/this/) for just the domain (www.whatever.com)
 * 
 * @param string $uri
 * @return string
 */
function siftDomain($uri) {
	$domain = preg_replace('#https?://([^/]*).*#si', '$1', $uri);
	return $domain;
}

//////////////// MAIL

/**
 * Send email
 *
 * @param int|string $to If integer, looks up email address from users table; else, an email address
 * @param string $subject 
 * @param string $message 
 * @return True if successful
 */
function email($to=0, $subject=null, $message=null) {
	global $db;
	if (empty($subject) or empty($message))
	{ 
		return false;
	}
	
	if ($to == 0) {
		$to = returnConf('web_email'); // Get the core email address from site settings.
	}
	
	if (is_int($to) or preg_match('#[0-9]+#s', $to)) {
		$query = $db->prepare('SELECT user_email FROM users WHERE user_id = ' . $to);
		$query->execute();
		$user = $query->fetch();
		$to = $user['user_email'];
	}
	
	$source = strip_tags(returnConf('web_title'));
	
	if (empty($source))
	{
		$source = 'FSIP'; 
	}

	$subject = $source . ': ' . $subject;
	$message = $message . "\r\n\n" . '-- ' . $source;
	$headers = 'From: ' . returnConf('web_email') . "\r\n" .
		'Reply-To: ' . returnConf('web_email') . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
	
	return mail($to, $subject, $message, $headers);
}


//////////////// CITATIONS (links to our images from other websites? Similar to blog trackbacks?)
/**
 * Load a citation
 *
 * @param string $uri URI of citation
 * @param string $field Field for ID entry
 * @param int $field_id ID to enter
 * @return array Associative array of newly created citation row
 */
function loadCitation($uri, $field, $field_id) {
	global $db;
	if ((strpos($uri, 'http://') !== 0) and (strpos($uri, 'https://') !== 0)) 
	{ 
		return false;
	}
	
	// Check if exists
	$sql = 'SELECT * FROM citations WHERE citation_uri_requested = :citation_uri_requested';
	
	$query = $db->prepare($sql);
	$query->execute(array(':citation_uri_requested' => $uri));
	$citations = $query->fetchAll();
	
	foreach($citations as $citation){
		if($citation[$field] == $field_id)
		{ 
			return $citation;
		}
	}
	
	$domain = siftDomain($uri);
	
	$ico_file = PATH . CACHE . 'favicons/' . makeFilenameSafe($domain) . '.ico';
	$png_file = PATH . CACHE . 'favicons/' . makeFilenameSafe($domain) . '.png';
	
	if (count($citations) == 0) {
		ini_set('default_socket_timeout', 1);
		$html = @file_get_contents($uri, null, null, 0, 7500);
		ini_restore('default_socket_timeout');
		
		if ($html == false) { 
			return false;
		}
		if (!preg_match('#Content-Type:\s*text/html#si', implode(' ', $http_response_header))) { 
			return false;
		}
		
		if (!file_exists($png_file)) {
			if (!file_exists(PATH . CACHE . 'favicons/')) {
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
	
		foreach($metas[0] as $meta) {
			if (preg_match('#property="og:(.*?)"#si', $meta, $property)) {
				preg_match('#content="(.*?)"#si', $meta, $content);
				$html5_meta[$property[1]] = $content[1];
			}
		}
	
		$save_fields = array('url', 'description', 'title', 'site_name');
		$fields = array('citation_uri_requested' => $uri,
			$field => $field_id);
	
		foreach($html5_meta as $property => $content) {
			if (in_array($property, $save_fields)) {
				if($property == 'url')
				{ 
					$property = 'uri'; 
				}
				$field = 'citation_' . $property;
				$fields[$field] = makeUnicode(html_entity_decode($content, ENT_QUOTES, 'UTF-8'));
			}
		}
		
		if (empty($fields['citation_title'])) {
			preg_match('#<title>(.*?)</title>#si', $html, $match);
			$fields['citation_title'] = $match[1];
		}
		
		if (empty($fields['citation_description'])) {
			preg_match('#<meta[^>]*name="description"[^>]*content="([^>]*)"[^>]*>#si', $html, $match);
			if (empty($match[1])) {
				preg_match('#<meta[^>]*content="([^>]*)"[^>]*name="description"[^>]*>#si', $html, $match);
			}
			
			if (!empty($match[1])) {
				$fields['citation_description'] = $match[1];
			}
		}
	} else {
		$fields = array();
		
		foreach($citations[0] as $key => $value) {
			if (is_int($key)) {
				continue;
			}
			$fields[$key] = $value;
		}
		
		unset($fields['citation_id']);
		$fields[$field] = $field_id;
		
		if (file_exists(PATH . CACHE . 'favicons/' . makeFilenameSafe($domain) . '.png')) {
			$favicon_found = true;
		}
	}
	
	$fields['citation_id'] = $db->addRow($fields, 'citations');
	
	if (empty($fields['citation_site_name'])) {
		$fields['citation_site_name'] = $domain;
	}
	
	if (file_exists($png_file)) {
		$fields['citation_favicon_uri'] = LOCATION . BASE . CACHE . 'favicons/' . makeFilenameSafe($domain) . '.png';
	}
	
	return $fields;
}


//////////////// NOTIFICATIONS

/**
 * Add a notification to the current $_SESSION. These notifications are displayed
 * to the user with a call to returnNotes().
 *
 * @param string $message Message
 * @param string $type Notification type (usually 'success', 'error', or 'notice')
 * @return void
 */
function addNote($message, $type=null) {
	$message = strval($message);
	$type = strval($type);
	
	if (!empty($message)) {
		$_SESSION['fsip']['notifications'][] = array('message' => $message, 'type' => $type);
	}
}

/**
 * Check on number of notifications stored in the current $_SESSION
 *
 * @param string $type Notification type
 * @return int Number of notifications
 */
function countNotes($type=null) {
	if ( isset($_SESSION['fsip']) && isset($_SESSION['fsip']['notifications']) ) {
		if (!empty($type)) {
			$notifications = @$_SESSION['fsip']['notifications'];
			$count = @count($notifications);
			if ($count > 0) {
				$count = 0;
				foreach($notifications as $notification) {
					if ($notification['type'] == $type) {
						$count++;
					}
				}
				if ($count > 0) {
					return $count;
				}
			}			
		} else {
			$count = @count($_SESSION['fsip']['notifications']);
			if ($count > 0) {
				return $count;
			}
		}
	}
	
	return 0;
}

/**
 * Return a string of the notifications currently in the $_SESSION and then remove them.
 *
 * @param string $type Notification type
 * @return string HTML-formatted notifications 
 */
function returnNotes($type = null) {
	if (!isset($_SESSION['fsip']) || !isset($_SESSION['fsip']['notifications']) ) { return; }
	
	$count = count($_SESSION['fsip']['notifications']);
	
	if ($count == 0) { return; }
	
	$return = '';
	
	// Determine unique types
	$types = array();
	foreach($_SESSION['fsip']['notifications'] as $notifications) {
		$types[] = $notifications['type'];
	}
	$types = array_unique($types);
	
	// Produce HTML for display
	foreach($types as $type) {
		$return = '<p class="' . $type . '">';
		$messages = array();
		foreach($_SESSION['fsip']['notifications'] as $notification) {
			if ($notification['type'] == $type) {
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
////////////////  SHOW HTML OF CORE APPLICATION DATA


/**
 * Get HTML <select> of all rights
 *
 * @param string $name Name and ID of <select>
 * @param integer $right_id Default or selected right_id
 * @return string
 */
function showRights($name, $right_id=null) {
	global $db;

	if (empty($name)) {
		return false;
	}
	
	$query = $db->prepare('SELECT right_id, right_title FROM rights WHERE right_deleted IS NULL;');
	$query->execute();
	$rights = $query->fetchAll();
	
	$html = '<select name="' . $name . '" id="' . $name . '"><option value=""></option>';
	
	foreach($rights as $right) {
		$html .= '<option value="' . $right['right_id'] . '"';
		if ($right['right_id'] == $right_id) {
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
function showSizes($name, $size_id=null) {
	global $db;

	if (empty($name)) {
		return false;
	}
	
	$query = $db->prepare('SELECT size_id, size_title FROM sizes;');
	$query->execute();
	$sizes = $query->fetchAll();
	
	$html = '<select name="' . $name . '" id="' . $name . '">';
	
	foreach($sizes as $size) {
		$html .= '<option value="' . $size['size_id'] . '"';
		if ($size['size_id'] == $size_id) {
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
function showPrivacy($name, $privacy_id=1) {
	if (empty($name)) {
		return false;
	}
	
	$privacy_levels = array(1 => 'Public', 2 => 'Protected', 3 => 'Private');
	
	$html = '<select name="' . $name . '" id="' . $name . '">';
	
	foreach($privacy_levels as $privacy_level => $privacy_label) {
		$html .= '<option value="' . $privacy_level . '"';
		if ($privacy_level == $privacy_id) {
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
function showSets($name, $set_id=null, $static_only=false) {

	if (empty($name)) {
		return false;
	}
	global $db;
	
	if ($static_only === true) {
		$query = $db->prepare('SELECT set_id, set_title FROM sets WHERE set_type = :set_type AND set_deleted IS NULL;');
		$query->execute(array(':set_type' => 'static'));
	} else {
		$query = $db->prepare('SELECT set_id, set_title FROM sets WHERE set_deleted IS NULL;');
		$query->execute();
	}
	$sets = $query->fetchAll();
	
	$html = '<select name="' . $name . '" id="' . $name . '">';
	
	foreach($sets as $set) {
		$html .= '<option value="' . $set['set_id'] . '"';
		if ($set['set_id'] == $set_id) {
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
function showThemes($name, $theme_id=null) {

	if (empty($name)) {
		return false;
	}
	global $db;
	
	$query = $db->prepare('SELECT theme_id, theme_title FROM themes;');
	$query->execute();
	$themes = $query->fetchAll();
	
	$html = '<select name="' . $name . '" id="' . $name . '">';
	
	foreach($themes as $theme) {
		$html .= '<option value="' . $theme['theme_id'] . '"';
		if ($theme['theme_id'] == $theme_id) {
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
function showEXIFNames($name, $exif_name=null) {

	if (empty($name)) {
		return false;
	}
	global $db;
	
	$query = $db->prepare('SELECT DISTINCT exif_name FROM exifs ORDER BY exif_name ASC;');
	$query->execute();
	$exifs = $query->fetchAll();
	
	$html = '<select name="' . $name . '" id="' . $name . '"><option value=""></option>';
	
	foreach($exifs as $exif) {
		$html .= '<option value="' . $exif['exif_name'] . '"';
		if ($exif['exif_name'] == $exif_name) {
			$html .= ' selected="selected"';
		}
		$html .= '>' . $exif['exif_name'] . '</option>';
	}
	
	$html .= '</select>';
	
	return $html;
}

////////////////  GETTERS FOR BASE APPLICATION VARIABLES

/**
 * Desc
 *
 * @return 
 */
function getTables() {
	if (!isset($tables)) {
//		echo "setting tables<br />";
		// Write tables
		$tables = array('images' => 'image_id', 'tags' => 'tag_id', 'sets' => 'set_id', 'pages' => 'page_id', 'rights' => 'right_id', 'exifs' => 'exif_id', 'extensions' => 'extension_id', 'themes' => 'theme_id', 'sizes' => 'size_id', 'users' => 'user_id', 'guests' => 'guest_id',  'comments' => 'comment_id', 'versions' => 'version_id', 'citations' => 'citation_id', 'items' => 'item_id');
		$tables_cache = array('comments', 'extensions', 'images', 'pages', 'rights', 'sets', 'sizes');
		$tables_index = array('comments', 'images', 'pages', 'rights', 'sets', 'tags');
//		print_r($tables);
//		echo "<br />";
	}
	return $tables;
}

/**
 * Desc
 *
 * @return 
 */
function getTablesCache() {
	if (!isset($tables_cache)) {
//		echo "setting tables cache<br />";
		// Write tables cache
		$tables_cache = array('comments', 'extensions', 'images', 'pages', 'rights', 'sets', 'sizes');
//		print_r($tables_cache);
//		echo "<br />";
	}
	return $tables_cache;
}

/**
 * Desc
 *
 * @return 
 */
function getTablesIndex() {
	if (!isset($tables_index)) {
//		echo "setting tables index<br />";
		// Write tables index
		$tables_index = array('comments', 'images', 'pages', 'rights', 'sets', 'tags');
//		print_r($tables_index);
//		echo "<br />";
	}
	return $tables_index;
}

/**
 * Is there a valid user currently logged in?
 *
 * @return bool true if there is a user logged in
 */
 function redirectToLogin() {
	$_SESSION['fsip']['destination'] = location();
	session_write_close();
	
	$location = LOCATION . BASE . 'login' . URL_CAP;
	headerLocationRedirect($location);
	exit();
}
?>