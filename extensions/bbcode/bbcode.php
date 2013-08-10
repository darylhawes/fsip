<?php

class BBCode extends Orbit {
	/**
	 * Desc
	 *
	 * @return 
	 */
 	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Desc
	 *
	 * @return 
	 */
 	public function __destruct() {
		parent::__destruct();
	}
	
	/**
	 * Desc
	 *
	 * @return 
	 */
 	public function orbit_markup_bbcode($page_text_raw) {
		require_once('classes/BBCodeParser.php');
		
		$parser = new HTML_BBCodeParser();
		return $parser->qparse($page_text_raw);
		// return $page_text_raw;
	}
	
	/**
	 * Desc
	 *
	 * @return 
	 */
 	public function orbit_config() {
		?>
		<p>For more information on BBCode, including its syntax, visit <a href=""></a>.</p>
		<?php
	}
	
	/**
	 * Desc
	 *
	 * @return 
	 */
 	public function orbit_markup_html() {
		echo '<option value="bbcode">BBCode</option>';
	}
}

?>