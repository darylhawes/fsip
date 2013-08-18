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

require_once(PATH . INCLUDES . '/admin_header.php');

echo '<div class="phpinfo">';

ob_start();
phpinfo(INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES);
$html = ob_get_contents();
ob_end_clean();

/// Delete styles from output
$html = preg_replace('#(\n?<style[^>]*?>.*?</style[^>]*?>)|(\n?<style[^>]*?/>)#is', '', $html);
$html = preg_replace('#(\n?<head[^>]*?>.*?</head[^>]*?>)|(\n?<head[^>]*?/>)#is', '', $html);
/// Delete DOCTYPE from output
$html = preg_replace('/<!DOCTYPE html PUBLIC.*?>/is', '', $html);
/// Delete body and html tags
$html = preg_replace('/<html.*?>.*?<body.*?>/is', '', $html);
$html = preg_replace('/<\/body><\/html>/is', '', $html);

echo $html;

echo '</div>';

require_once(PATH . INCLUDES . '/admin_footer.php');

?>