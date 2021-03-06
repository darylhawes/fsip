<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

$user->perm(true);

$fsip->setCallback();

// Vitals
$stats = new Stat(strtotime('-30 days'));
$stats->getDaily();

$views = array();

foreach($stats->stats as $stat){
	$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
}

$views = json_encode($views);

$visitors = array();

foreach($stats->stats as $stat){
	$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
}

$visitors = json_encode($visitors);

define('TAB', 'dashboard');
define('TITLE', 'Dashboard');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="actions">
	<a href="<?php echo BASE . ADMIN . 'upload' . URL_CAP; ?>"><button>Upload file</button></a>
</div>

<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/dashboard.png" alt="" /> Dashboard</h1>

<div class="span-24 last">
	<div class="span-16 append-2">
		<?php
		if($user->returnConf('stat_enabled') !== false){
			?>
			<div id="statistics_holder" class="statistics_holder"></div>
			<div id="statistics_views" title="<?php echo $views; ?>"></div>
			<div id="statistics_visitors" title="<?php echo $visitors; ?>"></div>
			<?php
		}
		?>
	</div>
	<div class="span-6 prepend-top last">
		<h3>Hello</h3>
		<p><?php echo ($user->user['user_last_login']) ? 'Welcome back! You last logged in on:  ' .  $fsip->formatTime($user->user['user_last_login'], 'l, F j \a\t g:i a') : 'Welcome to FSIP. You should begin by <a href="' . BASE . ADMIN . 'preferences' . URL_CAP . '">configuring your preferences</a> and <a href="' . BASE . ADMIN . 'upload' . URL_CAP . '">uploading some content</a>.'; ?></p>

		<h3>Census</h3>
		<table class="census">
			<?php
			$tables = $fsip->getInfo();
			foreach($tables as $table){
				echo '<tr><td class="right">' . number_format($table['count']) . '</td><td><a href="' . BASE . ADMIN . $table['table'] . URL_CAP . '">' . $table['display'] . '</a></td></tr>';
				
				if($table['table'] == 'images'){ $image_count = $table['count']; }
			}
			?>
		</table>

		<h3>FSIP</h3>
		<p>You are running FSIP <?php echo FSIP::version; ?>.</p>
	</div>
</div>

<div class="span-24 prepend-top last">
	<div class="actions">
		<a href="<?php echo BASE . ADMIN . 'atom' . URL_CAP; ?>" class="tip" title="Keep track of new comments and daily stats from your newsreader."><button>Subscribe to dashboard</button></a>
	</div>
	
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/timeline.png" alt="" /> Timeline</h1><br />
	
	<?php

	$timestamps = array();
	$items = array();
	$types = array();

	$comment_ids = new Find('comments');
	$comment_ids->sort('comments.comment_created', 'DESC');
	$comment_ids->page(1, 60);
	$comment_ids->find();

	$comments = new Comment($comment_ids);

	for($i=0; $i < $comments->comment_count; $i++){
		if(empty($comments->comments[$i]['comment_created'])){ continue; }
		$timestamps[] = strtotime($comments->comments[$i]['comment_created']);
		$items[] = $comments->comments[$i];
		$types[] = 'comment';
	}

	$image_ids = new Find('images');
	$image_ids->sort('images.image_modified', 'DESC');
	$image_ids->page(1, 60);
	$image_ids->find();

	$images = new Image($image_ids);
	$images->getSizes('square');

	for($i=0; $i < $images->image_count; $i++){
		if(empty($images->images[$i]['image_modified'])){ continue; }
		$timestamps[] = strtotime($images->images[$i]['image_modified']);
		$items[] = $images->images[$i];
		$types[] = 'image';
	}

	array_multisort($timestamps, SORT_DESC, $items, $types);

	if(count($items) == 0){
		echo '<p>Sart enjoying your new Free Stock Image Project site to populate the timeline.</p>';
	}
	else{
		$timeline = array();
		$modified_last = '';

		for($i=0; $i < 60; $i++){
			if(!isset($types[$i])){ continue; }
	
			$type = $types[$i];
	
			$modified = $fsip->formatRelTime($timestamps[$i]);
	
			if($modified != $modified_last){
				$timeline[$modified] = array();
				$modified_last = $modified;
			}
	
			ob_start();
	
			if($type == 'comment'){
				echo '<p><strong><a href="' . BASE . ADMIN . 'comments' . URL_ID . $items[$i]['comment_id'] . URL_RW . '" class="large tip" title="' . $fsip->makeHTMLSafe($fsip->fitStringByWord(strip_tags($items[$i]['comment_text']), 150)) . '">';
				echo $fsip->fitStringByWord(strip_tags($items[$i]['comment_text']), 50);
				echo '</a></strong><br /><span class="quiet">';
				
				if(!empty($items[$i]['user_id'])){
					echo '<img src="' . BASE . ADMIN . 'images/icons/user.png" alt="" /> <a href="' . BASE . ADMIN . 'comments' . URL_ACT . 'user' . URL_AID . $items[$i]['user_id'] . URL_RW . '" class="nu">' . $items[$i]['comment_author_name'] . '</a>';
				}
				elseif(!empty($items[$i]['comment_author_name'])){
					echo '<a href="' . BASE . ADMIN . 'comments' . URL_CAP . '?q=' . urlencode($items[$i]['comment_author_name']) . '" class="nu">' . $items[$i]['comment_author_name'] . '</a>';
				}
				else{
					'<em>Anonymous</em>';
				}

				if(!empty($items[$i]['comment_author_ip']) and empty($items[$i]['user_id'])){
					echo ' (<a href="' . BASE . ADMIN . 'comments' . URL_CAP . '?q=' . urlencode($items[$i]['comment_author_ip']) . '" class="nu">' . $items[$i]['comment_author_ip'] . '</a>)';
				}
				
				echo '</span></p>';
		
				$timeline[$modified][] = ob_get_contents();
			}
			elseif($type == 'image'){
				echo '<a href="' . BASE . ADMIN . 'image' . URL_ID . $items[$i]['image_id'] . URL_RW . '" class="nu">
					<img src="' . $items[$i]['image_src_square'] . '" alt="" title="' . $fsip->makeHTMLSafe($items[$i]['image_title']) . '" class="frame tip" />
				</a>';
		
				$timeline[$modified][] = ob_get_contents();
			}
	
			ob_end_clean();
		}
		
		echo '<table>';

		foreach($timeline as $modified => $items){
			echo '<tr><td class="right" style="width:15%;"><strong class="quiet">' . ucfirst($modified) . '</strong></td><td>' . "\n";
			foreach($items as $item){
				echo $item . "\n";
			}
			echo '</td></tr>' . "\n";
		}

		echo '</table>';
	}

	?>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

// Delete old cache
$fsip->emptyDirectory(PATH . CACHE, false, 3600);

// Anonymous usage reports 
$now = time();
if(($user->returnConf('maint_reports') === true) && ($user->returnConf('maint_reports_time') < ($now - 604800))){
	$data = http_build_query(
	    array(
			'unique' => sha1($_SERVER['HTTP_HOST']),
			'views' => $stats->views,
			'visitors' => $stats->visitors,
			'build' => FSIP::build,
			'version' => FSIP::version,
			'http_server' => preg_replace('#\/.*#si', '', $_SERVER['SERVER_SOFTWARE']),
			'http_server_version' => preg_replace('#.*?\/([0-9.]*).*#si', '\\1', $_SERVER['SERVER_SOFTWARE']),
			'db_server' => $fsip->db_type,
			'db_server_version' => $fsip->db_version,
			'php_version' => phpversion(),
			'image_count' => $image_count,
	    )
	);

	$opts = array(
		'http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded; charset=utf-8',
			'content' => $data
		)
	);

/*
//DEH - disabling all boomerang features
	$context = stream_context_create($opts);
	$bool = file_get_contents('http://www.alkalineapp.com/boomerang/usage/', false, $context);
	
	if($bool == 'true'){
		$alkaline->setConf('maint_reports_time', $now);
		$alkaline->saveConf();
	}
*/
}

?>