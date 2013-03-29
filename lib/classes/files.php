<?php

class Files {

	/**
	 * Initiates new File management object
	 *
	 * @return void
	 **/
	public function __construct() {
		//echo "constructing new Files object<br />";
	}

	public function __destruct() {
		//
	}

	// FILE HANDLING
	
	/**
	 * Browse a local directory (non-recursive) for filenames
	 *
	 * @param string $dir Full path to directory
	 * @param string $ext File extensions to seek
	 * @return array Full paths of files
	 */
	public static function seekDirectory($dir=null, $ext=IMG_EXT) {
		// Error checking
		if (empty($dir)) {
			return false;
		}
		
		// Windows-friendly
		$dir = correctWinPath($dir);
		
		$files = array();
		$ignore = array('.', '..');
		
		// Open listing
		$handle = opendir($dir);
		
		// Seek directory
		while($filename = readdir($handle)) {
			if (!in_array($filename, $ignore)) { 
			//DEH old (broken?) recursive directory seek that might help with user directory subfolders?
			// shouldn't it be: $files=	self::seekDirectory($dir . $filename . '/', $extension); ?

				// Recusively check directories
				/*
				if(is_dir($dir . '/' . $filename)){
					self::seekDirectory($dir . $filename . '/', $files);
				}
				*/
				
				if (!empty($ext)) {
					// Find files with proper extensions
					if (preg_match('#^([^\.]+.*\.(' . $ext . '){1,1})$#si', $filename)) {
						$files[] = $dir . $filename;
					}
				} else {
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
	public static function countDirectory($dir=null, $ext=IMG_EXT) {
		// Error checking
		if (empty($dir)) {
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
	public static function getFilename($file) {
		$matches = array();
		
		// Windows cheat
		$file = str_replace('\\', '/', $file);
		
		preg_match('#^(.*/)?(?:$|(.+?)(?:(\.[^.]*$)|$))#si', $file, $matches);
		
		if (count($matches) < 1) {
			return false;
		}
		
		$filename = $matches[2];
		
		if (isset($matches[3])) {
			$filename .= $matches[3];
		}
		
		return $filename;
	}
	
	/**
	 * Delete a directory and its contents.
	 *
	 * @param string $dir Full path to directory
	 * @param bool $recursive Delete subdirectories
	 * @param int $age Delete contents older than $age seconds old
	 * @return void
	 */
	public static function emptyDirectory($dir=null, $recursive=true, $age=0) {
		// Error checking
		if (empty($dir)) {
			return false;
		}
		
		if ($age != 0) {
			$now = time();
		}
		
		$ignore = array('.', '..');
		
		// Open listing
		$handle = opendir($dir);
		
		// Seek directory
		while($filename = readdir($handle)) {
			if (!in_array($filename, $ignore)) {
				// Delete directories
				if (is_dir($dir . '/' . $filename) and ($recursive !== false)) {
					self::emptyDirectory($dir . $filename . '/');
					@rmdir($dir . $filename . '/');
				} else {
					// Delete files
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
	public static function checkPerm($file) {
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
	public function replaceVar($var, $replacement, $subject) {
		$replacement = str_replace('\\', '\\\\\\\\', $replacement);
		return preg_replace('#^\s*(' . str_replace('$', '\$', $var) . ')\s*=(.*)$#mi', '\\1 = \'' . $replacement . '\';', $subject);
	}

	/**
	 * Get array of all includes
	 *
	 * @return array Array of includes
	 */
	public function getThemeIncludes() {
		$theme_includes = self::seekDirectory(PATH . INCLUDES, '.*');
		
		foreach($theme_includes as &$include) {
			$include = self::getFilename($include);
		}
		
		return $theme_includes;
	}
	

	/**
	 * Determine if the current script being worked on is an admin file or not
	 *
	 * @return boolean True is current script is in admin folder
	 */
	public static function isInAdminPath() {
		// Check if in Dashboard
		if (strpos($_SERVER['SCRIPT_FILENAME'], PATH . ADMINFOLDER) === 0) {
			return true;
			exit;
		}
		return false;
	}
	
} // end class Files

?>