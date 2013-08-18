<?php

/**
 * FSIP based on Alkaline
 * 
 *
 * http://www.alkalineapp.com/
 * Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
 *
 * @package FSIP
 * @subpackage admin
 * @since 1.2
 */

if (file_exists('../config.php')) {
	require_once('../config.php');
} else {
	require_once('config_default.php');
}

if (session_id() == '') { session_start(); }

$e = $_SESSION['fsip']['exception'];

define('TAB', 'Error');
define('TITLE', 'FSIP Error');
require_once(PATH . INCLUDES . '/admin_header.php');

?>

<h2>Error</h2>

<p><strong><?php echo $e->getPublicMessage(); ?></strong></p>

<ol>
<?php

$trace = $e->getPublicTrace();

foreach($trace as $stack) {
?>
	<li>
		<?php echo $stack['class']; ?> <?php echo str_replace('->', '&#8594;', $stack['type']); ?> <?php echo $stack['function']; ?>
		<span class="quiet">(<?php echo $stack['file']; ?>, line <?php echo $stack['line']; ?>)</span>
	</li>
<?php
}

?>
</ol>
<?php

require_once(PATH . INCLUDES . '/admin_footer.php');

?>