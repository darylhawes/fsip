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

$includes = $fsip->getincludes();
$include_count = count($includes);

define('TAB', 'settings');
define('TITLE', 'Theme Includes');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Theme Includes (<?php echo $include_count; ?>)</h1>

<table>
	<tr>
		<th>include</th>
		<th class="center">Canvas tag</th>
	</tr>
	<?php
	
	foreach($includes as $include){
		echo '<tr>';
		echo '<td><strong>' . $include . '</strong></td>';
		echo '<td class="center">{include:' . preg_replace('#\..+#si', '', ucwords($include)) . '}</td>';
		echo '</tr>';
	}

	?>
</table>
	
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>