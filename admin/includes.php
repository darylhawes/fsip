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

require_once('../config.php');

$user = new User;
$user->hasPermission('admin', true);

$includes = Files::getThemeIncludes();
$include_count = count($includes);

define('TAB', 'settings');
define('TITLE', 'Theme Includes');
require_once(PATH . INCLUDES . '/admin_header.php');

?>

<h1>Theme Includes (<?php echo $include_count; ?>)</h1>

<table>
	<tr>
		<th>include</th>
		<th class="center">Canvas tag</th>
	</tr>
<?php

	foreach($includes as $include) {
		echo '<tr>';
		echo '<td><strong>' . $include . '</strong></td>';
		echo '<td class="center">{include:' . preg_replace('#\..+#si', '', ucwords($include)) . '}</td>';
		echo '</tr>';
	}

?>
</table>

<?php
require_once(PATH . INCLUDES . '/admin_footer.php');
?>