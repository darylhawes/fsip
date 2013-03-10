<?php

class Tumblr extends Orbit {
	public $tumblr;
	public $tumblr_username;
	
	private $tumblr_password;
	
	public function __construct(){
		parent::__construct();
		
		$this->tumblr_active = $this->returnPref('tumblr_active');
		$this->tumblr_auto = $this->returnPref('tumblr_auto');
		$this->tumblr_transmit = $this->returnPref('tumblr_transmit');
		$this->tumblr_format_image = $this->returnPref('tumblr_format_image');
		$this->tumblr_last_image_id = $this->returnPref('tumblr_last_image_id');
		$this->tumblr_last_image_time = $this->returnPref('tumblr_last_image_time');
		
		require_once('classes/TumblrAPI.php');
		
		$this->tumblr_name = $this->returnPref('tumblr_name');
		$this->tumblr_oauth_token = $this->returnPref('tumblr_oauth_token');
		$this->tumblr_oauth_secret = $this->returnPref('tumblr_oauth_secret');
		
		/*
		if(!empty($this->tumblr_email) and !empty($this->tumblr_password)){
			ini_set('default_socket_timeout', 1);
			$this->tumblr = new TumblrAPI();
			ini_restore('default_socket_timeout');
			$this->tumblr->init($this->tumblr_email, $this->tumblr_password, 'Tumblr by Alkaline Labs');
			$this->tumblr->init_cache(60, PATH . CACHE);
		}
		*/
		
		if (!empty($this->tumblr_oauth_token) and !empty($this->tumblr_oauth_secret)) {
			ini_set('default_socket_timeout', 1);
			$this->tumblr = new Tumblr_TumblrAPI('4P6gWXvDKLeuRy0hTLdMXxADrclI2QMbNQXPa3O78jeap7005S',
				'Kx8nJZplPAtEH7bgXBItlQMW9CsAsCDhIELU4ktXKQ1Cf7Akc9',
				$this->tumblr_oauth_token,
				$this->tumblr_oauth_secret);
			ini_restore('default_socket_timeout');
		} else {
			ini_set('default_socket_timeout', 1);
			$this->tumblr = new Tumblr_TumblrAPI('4P6gWXvDKLeuRy0hTLdMXxADrclI2QMbNQXPa3O78jeap7005S',
				'Kx8nJZplPAtEH7bgXBItlQMW9CsAsCDhIELU4ktXKQ1Cf7Akc9');
			ini_restore('default_socket_timeout');
		}
	}
	
	public function __destruct() {
		parent::__destruct();
	}
	
	public function orbit_config() {
		?>
		<p>Update your <a href="http://www.tumblr.com/">Tumblr</a>.</p>
		<?php
		if ($this->tumblr_active) {
			$this->tumblr_format_image = $this->makeHTMLSafe($this->tumblr_format_image);
			?>
			<table>
				<tr>
					<td class="right"><label>Name:</label></td>
					<td><a href="http://<?php echo $this->tumblr_name; ?>.tumblr.com/"><?php echo $this->tumblr_name; ?></a> &#0160; <a href="<?php echo $this->locationFull(array('unlink' => 'tumblr')); ?>"><button>Unlink from Tumblr</button></a></td>
				</tr>
				<tr>
					<td class="right middle"><label for="tumblr_transmit">Transmit:</label></td>
					<td>
						<select name="tumblr_transmit" id="tumblr_transmit">
							<option value="images" <?php echo $this->readPref('tumblr_transmit', 'images'); ?>>Images only</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right"><label for="tumblr_format_image">Image format:</label></td>
					<td>
						<textarea type="text" id="tumblr_format_image" name="tumblr_format_image" style="width: 30em;" class="code"><?php echo $this->tumblr_format_image; ?></textarea><br />
						<p class="quiet">Your image will automatically be posted, you can use the text area above to write an optional caption. Use Canvas tags such as <code>{Image_Title}</code> and <code>{Image_URI}</code> above.</p>
					</td>
				</tr>
				<tr>
					<td class="right"><input type="checkbox" id="tumblr_auto" name="tumblr_auto" value="auto" <?php if($this->tumblr_auto == 'auto'){ echo 'checked="checked"'; } ?> /></td>
					<td><strong><label for="tumblr_auto">Enable automatic mode.</label></strong> When you publish, your Tumblog will be automatically updated.</td>
				</tr>
			</table>
			<?php
		} else {
			?>
			<table>
				<tr>
					<td class="right"><label>Title:</label></td>
					<td>
						<a href="<?php echo $this->locationFull(array('link' => 'tumblr')); ?>"><button>Link to Tumblr</button></a><br /><br />
						<span class="quiet">Note: Link will be to the Tumblr account you are currently logged into.</span>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function orbit_config_load() {
		if (!empty($_GET['from'])) {
			switch($_GET['from']) {
				case 'tumblr':
					$tumblr_access_token = $this->tumblr->getAccessToken($_GET['oauth_verifier']);
					
					$this->tumblr_active = true;
					$this->setPref('tumblr_active', true);
					
					$user = $this->tumblr->post('authenticate');
					$xml = simplexml_load_string($user);
					$this->setPref('tumblr_name', (string)$xml->tumblelog['name']);
					
					$this->setPref('tumblr_oauth_token', $tumblr_access_token['oauth_token']);
					$this->setPref('tumblr_oauth_secret', $tumblr_access_token['oauth_token_secret']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Tumblr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['link'])) {
			switch($_GET['link']) {
				case 'tumblr':
					$tumblr_token = $this->tumblr->getRequestToken($this->locationFull(array('from' => 'tumblr')));
					$tumblr_authorize_uri = $this->tumblr->getAuthorizeURL($tumblr_token['oauth_token']);
					
					$this->setPref('tumblr_oauth_token', $tumblr_token['oauth_token']);
					$this->setPref('tumblr_oauth_secret', $tumblr_token['oauth_token_secret']);
					$this->savePref();
					
					header('Location: ' . $tumblr_authorize_uri);
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['unlink'])) {
			switch($_GET['unlink']) {
				case 'tumblr':
					$this->tumblr_active = false;
					$this->setPref('tumblr_active', false);
					$this->setPref('tumblr_name', '');
					$this->setPref('tumblr_oauth_token', '');
					$this->setPref('tumblr_oauth_secret', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your Tumblr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		/*
		if(!empty($_GET['from'])){
			switch($_GET['from']){
				case 'tumblr':
					$tumblr_access_token = $this->tumblr->getAccessToken($_GET['oauth_verifier']);
					
					$this->tumblr_active = true;
					$this->setPref('tumblr_active', true);
					
					$user = $this->tumblr->get('account/verify_credentials');
					$this->setPref('tumblr_screen_name', $user->screen_name);
					
					$this->setPref('tumblr_oauth_token', $tumblr_access_token['oauth_token']);
					$this->setPref('tumblr_oauth_secret', $tumblr_access_token['oauth_token_secret']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Tumblr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'tumblr':
					$this->tumblr_active = false;
					$this->setPref('tumblr_active', false);
					$this->setPref('tumblr_email', '');
					$this->setPref('tumblr_password', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your Tumblr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		*/
	}
	
	public function orbit_config_save() {
		$now = time();
		$this->setPref('tumblr_last_image_time', $now);
		
		$this->setPref('tumblr_auto', @$_POST['tumblr_auto']);
		
		$this->setPref('tumblr_transmit', @$_POST['tumblr_transmit']);
		$this->setPref('tumblr_format_image', @$_POST['tumblr_format_image']);
		
		$this->savePref();
	}
	
	public function orbit_image($images, $override=false) {
		if (($this->tumblr_auto != 'auto') && ($override === false)) { return; }
		if (strpos($this->tumblr_transmit, 'image') === false) { return; }
		if (count($images) < 1) { return; }
		
		$now = time();
		
		foreach($images as $image) {
			$image_published = strtotime($image['image_published']);
			
			if (empty($image_published)) { continue; }
			if ($image_published > $now) { continue; }
			if ($override !== true) {
				if($image_published <= $this->tumblr_last_image_time){ continue; }
				if($image['image_privacy'] != 1){ continue; }
			}
			
			$this->storeTask(array($this, 'upload_image'), $image);
		}
		
		$this->setPref('tumblr_last_image_time', $now);
		$this->savePref();
	}
		
	public function upload_image($image) {
		// Format caption
		$canvas = new Canvas($this->tumblr_format_image);
		$canvas->assignArray($image);
		$canvas->generate();
		
		// Reformat relative links
		$canvas->template = str_ireplace('href="/', 'href="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('href=\'/', 'href=\'' . LOCATION . '/', $canvas->template);

		$canvas->template = str_ireplace('src="/', 'src="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('src=\'/', 'src=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = trim($canvas->template);
		
		// Send to Tumblr
		$parameters = array('type' => 'photo',
			'format' => 'html',
			'tags' => $image_tags,
			'source' => LOCATION . $image['image_src'],
			'caption' => $canvas->template,
			'click-through-url' => $image['image_uri']);
		
		$this->tumblr->post('write', $parameters);
	}
		
	public function orbit_send_html_image() {
		echo '<option value="tumblr">Tumblr</option>';
	}
		
	public function orbit_send_tumblr_image($images) {
		return $this->orbit_image($images, true);
	}
}

?>