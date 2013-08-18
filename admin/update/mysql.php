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

// example of build define in fsip_lib.php
//define('FSIP_BUILD', '2013080400'); // YYYYMMDDXX, used by update system when applying database updates

require_once('../config.php');

global $db;
$current_build = FSIP_BUILD;

if ($current_build < 0) {
	$queries = array();
	$queries[] = "ALTER TABLE `images` ADD `image_modified` datetime default NULL;";
	$queries[] = "ALTER TABLE `rights` ADD `right_created` datetime default NULL;";
	$queries[] = "ALTER TABLE `images` ADD `image_tags` text;";
	$queries[] = "ALTER TABLE `pages` ADD `page_category` varchar(255) default NULL;";
	$queries[] = "ALTER TABLE `users` ADD `user_uri` varchar(255) default NULL;";
	$queries[] = "ALTER TABLE `tags` ADD `tag_parents` text;";
	$queries[] = "ALTER TABLE `guests` ADD `guest_inclusive` tinyint(3) unsigned default NULL;";
	$queries[] = "ALTER TABLE `sizes` CHANGE `size_title` `size_title` varchar(255) default NULL;";
	$queries[] = "ALTER TABLE `sizes` CHANGE `size_label` `size_label` varchar(255) default NULL;";
	$queries[] = "ALTER TABLE `sizes` CHANGE `size_height` `size_height` smallint(5) UNSIGNED default NULL;";
	$queries[] = "ALTER TABLE `sizes` CHANGE `size_width` `size_width` smallint(5) UNSIGNED default NULL;";
	$queries[] = "ALTER TABLE `sizes` CHANGE `size_append` `size_append` varchar(16) default NULL;";
	$queries[] = "ALTER TABLE `sizes` CHANGE `size_prepend` `size_prepend` varchar(16) default NULL;";
	$queries[] = "ALTER TABLE `sizes` CHANGE `size_watermark` `size_watermark` tinyint(3) UNSIGNED default NULL;";
	$queries[] = "ALTER TABLE `guests` CHANGE `guest_title` `guest_title` varchar(255) default NULL;";
	$queries[] = "ALTER TABLE `pages` CHANGE `page_title` `page_title` varchar(255) default NULL;";
	$queries[] = "ALTER TABLE `rights` CHANGE `right_title` `right_title` varchar(255) default NULL;";
	$queries[] = "ALTER TABLE `sets` CHANGE `set_title` `set_title` varchar(255) default NULL;";
	$queries[] = "ALTER TABLE `users` CHANGE `user_username` `user_username` varchar(32) default NULL;";
	$queries[] = "ALTER TABLE `users` CHANGE `user_pass` `user_pass` varchar(40) default NULL;";
	$queries[] = "CREATE TABLE `citations` (`citation_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `page_id` smallint(5) unsigned DEFAULT NULL, `citation_type` varchar(255) DEFAULT NULL, `citation_uri` tinytext, `citation_uri_requested` tinytext, `citation_description` tinytext, `citation_title` tinytext, `citation_site_name` tinytext, `citation_created` datetime DEFAULT NULL, `citation_modified` datetime DEFAULT NULL, PRIMARY KEY (`citation_id`)) DEFAULT CHARSET=utf8;";
	$queries[] = "ALTER TABLE `comments` ADD `comment_response` smallint(5) unsigned default NULL;";
	$queries[] = "ALTER TABLE `comments` ADD `comment_modified` datetime DEFAULT NULL;";
	$queries[] = "ALTER TABLE `comments` ADD `comment_deleted` datetime DEFAULT NULL;";
	$queries[] = "ALTER TABLE `guests` ADD `guest_inclusive` tinyint(3) unsigned DEFAULT NULL;";
	$queries[] = "ALTER TABLE `images` ADD `image_deleted` datetime DEFAULT NULL;";
	$queries[] = "ALTER TABLE `images` ADD `image_tags` text;";
	$queries[] = "ALTER TABLE `images` ADD `image_related` text;";
	$queries[] = "ALTER TABLE `images` ADD `image_related_hash` varchar(16) DEFAULT NULL;";
	$queries[] = "ALTER TABLE `images` ADD `image_directory` varchar(255) DEFAULT NULL;";
	$queries[] = "ALTER TABLE `images` ADD `image_tag_count` smallint(5) unsigned default NULL;";
	$queries[] = "ALTER TABLE `pages` ADD `page_deleted` datetime DEFAULT NULL;";
	$queries[] = "ALTER TABLE `pages` ADD `page_excerpt` text;";
	$queries[] = "ALTER TABLE `pages` ADD `page_excerpt_raw` text;";
	$queries[] = "ALTER TABLE `pages` ADD `page_category` varchar(255) DEFAULT NULL;";
	$queries[] = "ALTER TABLE `rights` ADD `right_deleted` datetime DEFAULT NULL;";
	$queries[] = "ALTER TABLE `rights` ADD `right_markup` varchar(255) DEFAULT NULL;";
	$queries[] = "ALTER TABLE `rights` ADD `right_description_raw` text;";
	$queries[] = "ALTER TABLE `sets` ADD `set_deleted` datetime DEFAULT NULL;";
	$queries[] = "ALTER TABLE `sets` ADD `set_markup` varchar(255) DEFAULT NULL;";
	$queries[] = "ALTER TABLE `sets` ADD `set_description_raw` text;";
	$queries[] = "ALTER TABLE `sizes` ADD `size_modified` datetime DEFAULT NULL;";
	$queries[] = "ALTER TABLE `tags` ADD `tag_parents` text;";
	$queries[] = "ALTER TABLE `users` ADD `user_uri` varchar(255) DEFAULT NULL;";
	$queries[] = "ALTER TABLE `users` ADD `user_post_count` mediumint(8) unsigned DEFAULT NULL;";
	$queries[] = "ALTER TABLE `users` ADD `user_comment_count` mediumint(8) unsigned DEFAULT NULL;";
	$queries[] = "CREATE TABLE `items` (`item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `item_table` varchar(255) NOT NULL, `item_table_id` int(10) unsigned NOT NULL, PRIMARY KEY (`item_id`)) DEFAULT CHARSET=utf8;";
	$queries[] = "CREATE TABLE `versions` (`version_id` int(10) unsigned NOT NULL AUTO_INCREMENT, `post_id` mediumint(8) unsigned DEFAULT NULL, `page_id` smallint(5) unsigned DEFAULT NULL, `user_id` smallint(5) unsigned DEFAULT NULL, `version_title` varchar(255) DEFAULT NULL, `version_text_raw` text, `version_created` datetime DEFAULT NULL, `version_similarity` tinyint(3) unsigned DEFAULT NULL, PRIMARY KEY (`version_id`)) DEFAULT CHARSET=utf8;";
	$queries[] = "CREATE TABLE `rights` (`right_id` tinyint(3) unsigned NOT NULL auto_increment, `right_title` varchar(255) NOT NULL, `right_uri` varchar(255) default NULL, `right_image` varchar(255) default NULL, `right_description` text, `right_description_raw` text,  `right_created` datetime default NULL, `right_modified` datetime default NULL, `right_image_count` int(10) unsigned default NULL, `right_deleted` datetime DEFAULT NULL, `right_markup` varchar(255) DEFAULT NULL, PRIMARY KEY (`right_id`)) DEFAULT CHARSET=utf8;";
	$queries[] = "CREATE TABLE `sets` (`set_id` smallint(5) unsigned NOT NULL auto_increment, `set_title` varchar(255) NOT NULL, `set_title_url` varchar(255) default NULL, `set_type` varchar(255) default NULL, `set_description` text, `set_description_raw` text, `set_images` text, `set_views` int(10) unsigned default NULL, `set_image_count` smallint(5) unsigned default NULL, `set_call` text, `set_request` text, `set_modified` datetime default NULL, `set_created` datetime default NULL, `set_markup` varchar(255) DEFAULT NULL, `set_deleted` datetime DEFAULT NULL, PRIMARY KEY (`set_id`)) DEFAULT CHARSET=utf8;";
	$queries[] = "ALTER TABLE `sets` DROP `right_markup`;";

print "DEBUG: About to run some db queries:<br />";
print_r($queries);

	foreach($queries as $query) {
		$query = trim($query);
		if (!empty($query)) {
			$db->exec($query);
		}
	}

	addNote('You have successfully updated the FSIP database.', 'success');
}

if ($current_build < 2013080401) {
	// do edits for next build
}
?>