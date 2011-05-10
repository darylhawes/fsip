ALTER TABLE `sizes` CHANGE `size_title` `size_title` varchar(255) default NULL;
ALTER TABLE `sizes` CHANGE `size_label` `size_label` varchar(255) default NULL;
ALTER TABLE `sizes` CHANGE `size_height` `size_height` smallint(5) UNSIGNED default NULL;
ALTER TABLE `sizes` CHANGE `size_width` `size_width` smallint(5) UNSIGNED default NULL;
ALTER TABLE `sizes` CHANGE `size_append` `size_append` varchar(16) default NULL;
ALTER TABLE `sizes` CHANGE `size_prepend` `size_prepend` varchar(16) default NULL;
ALTER TABLE `sizes` CHANGE `size_watermark` `size_watermark` tinyint(3) UNSIGNED default NULL;

ALTER TABLE `guests` CHANGE `guest_title` `guest_title` varchar(255) default NULL;
ALTER TABLE `pages` CHANGE `page_title` `page_title` varchar(255) default NULL;
ALTER TABLE `posts` CHANGE `post_title` `post_title` varchar(255) default NULL;
ALTER TABLE `rights` CHANGE `right_title` `right_title` varchar(255) default NULL;
ALTER TABLE `sets` CHANGE `set_title` `set_title` varchar(255) default NULL;
ALTER TABLE `users` CHANGE `user_user` `user_user` varchar(32) default NULL;
ALTER TABLE `users` CHANGE `user_pass` `user_pass` varchar(40) default NULL;
