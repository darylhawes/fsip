<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

global $db;

$user = new User;
$user->hasPermission('themes', true);

updateThemes();

// Load up the freshly updated Themes
$themes = $db->getTable('themes');
$theme_count = @count($themes);

define('TAB', 'settings');
define('TITLE', 'FSIP Themes');
require_once(PATH . INCLUDES . '/admin_header.php');

?>

<div class="actions"><a href="<?php echo BASE . ADMINFOLDER . 'configuration' . URL_CAP; ?>"><button>Change theme</button></a></div>

<h1><img src="<?php echo BASE . IMGFOLDER; ?>icons/themes.png" alt="" /> Themes (<?php echo $theme_count; ?>)</h1>

<!-- DEH remove dead link. Perhaps there is room for a theme library in the future, but there is a LOT of work to be done before thinking about that.
<p>Themes change the look and feel of your library. You can browse and download additional themes at the <a href="http://www.alkalineapp.com/users/">Alkaline Lounge</a>.</p>
-->

<p>
	<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
</p>

<table class="filter">
	<tr>
		<th>Theme</th>
		<th class="center">Preview</th>
		<th class="center">Version</th>
		<th class="center">Update</th>
	</tr>
	<?php

	foreach($themes as $theme) {
		echo '<tr class="ro">';
		echo '<td><strong class="large">' . $theme['theme_title'] . '</strong>';
		
		if (!empty($theme['theme_creator_name'])) {
			echo ' \ ';
			if (!empty($theme['theme_creator_uri'])) {
				echo '<a href="' . $theme['theme_creator_uri'] . '" class="nu">' . $theme['theme_creator_name'] . '</a>';
			} else {
				echo $theme['theme_creator_name'];
			}
		}
		
		echo '</td>';
		echo '<td class="center"><a href="' . BASE . '?theme=' . $theme['theme_folder'] . '">Preview</a></td>';
		echo '<td class="center">' . $theme['theme_version'] . ' <span class="small">(' . $theme['theme_build'] . ')</span></td>';

/* DEH remove remote services
		if (!empty($theme['theme_build_latest'])) {
			echo '<td class="center"><a href="http://www.alkalineapp.com/users/themes/">Download</a>';
			if (!empty($theme['theme_version_latest'])) {
				echo ' (v' . $theme['theme_version_latest'] .')';
			}
			echo '</td>';
		} else {
		*/
			echo '<td class="center quiet">&#8212;</td>';
//		}
		echo '</tr>';
	}

	?>
</table>

<?php

require_once(PATH . INCLUDES . '/admin_footer.php');

?>