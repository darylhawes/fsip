CREATE TABLE `citations` (`citation_id` int(11) unsigned NOT NULL AUTO_INCREMENT, `page_id` smallint(5) unsigned DEFAULT NULL, `citation_type` varchar(255) DEFAULT NULL, `citation_uri` tinytext, `citation_uri_requested` tinytext, `citation_description` tinytext, `citation_title` tinytext, `citation_site_name` tinytext, `citation_created` datetime DEFAULT NULL, `citation_modified` datetime DEFAULT NULL, PRIMARY KEY (`citation_id`)) DEFAULT CHARSET=utf8;

ALTER TABLE `comments` ADD `comment_response` smallint(5) unsigned default NULL;
ALTER TABLE `comments` ADD `comment_modified` datetime DEFAULT NULL;
ALTER TABLE `comments` ADD `comment_deleted` datetime DEFAULT NULL;

ALTER TABLE `guests` ADD `guest_inclusive` tinyint(3) unsigned DEFAULT NULL;
ALTER TABLE `images` ADD `image_deleted` datetime DEFAULT NULL;
ALTER TABLE `images` ADD `image_tags` text;
ALTER TABLE `images` ADD `image_related` text;
ALTER TABLE `images` ADD `image_related_hash` varchar(16) DEFAULT NULL;
ALTER TABLE `images` ADD `image_directory` varchar(255) DEFAULT NULL;
ALTER TABLE `images` ADD `image_tag_count` smallint(5) unsigned default NULL;
ALTER TABLE `pages` ADD `page_deleted` datetime DEFAULT NULL;
ALTER TABLE `pages` ADD `page_excerpt` text;
ALTER TABLE `pages` ADD `page_excerpt_raw` text;
ALTER TABLE `pages` ADD `page_category` varchar(255) DEFAULT NULL;
ALTER TABLE `rights` ADD `right_deleted` datetime DEFAULT NULL;
ALTER TABLE `rights` ADD `right_markup` varchar(255) DEFAULT NULL;
ALTER TABLE `rights` ADD `right_description_raw` text;
ALTER TABLE `sets` ADD `set_deleted` datetime DEFAULT NULL;
ALTER TABLE `sets` ADD `set_markup` varchar(255) DEFAULT NULL;
ALTER TABLE `sets` ADD `set_description_raw` text;
ALTER TABLE `sizes` ADD `size_modified` datetime DEFAULT NULL;
ALTER TABLE `tags` ADD `tag_parents` text;
ALTER TABLE `users` ADD `user_uri` varchar(255) DEFAULT NULL;
ALTER TABLE `users` ADD `user_post_count` mediumint(8) unsigned DEFAULT NULL;
ALTER TABLE `users` ADD `user_comment_count` mediumint(8) unsigned DEFAULT NULL;

CREATE TABLE `items` (`item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `item_table` varchar(255) NOT NULL, `item_table_id` int(10) unsigned NOT NULL, PRIMARY KEY (`item_id`)) DEFAULT CHARSET=utf8;
CREATE TABLE `versions` (`version_id` int(10) unsigned NOT NULL AUTO_INCREMENT, `post_id` mediumint(8) unsigned DEFAULT NULL, `page_id` smallint(5) unsigned DEFAULT NULL, `user_id` smallint(5) unsigned DEFAULT NULL, `version_title` varchar(255) DEFAULT NULL, `version_text_raw` text, `version_created` datetime DEFAULT NULL, `version_similarity` tinyint(3) unsigned DEFAULT NULL, PRIMARY KEY (`version_id`)) DEFAULT CHARSET=utf8;