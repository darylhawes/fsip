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

//define('FSIP_BUILD', '2013080400'); // YYYYMMDDXX, used by update system when applying database updates
require_once('../config.php');

global $db;
$current_build = FSIP_BUILD;

if ($current_build < 0) {

	$queries = array();
	$queries[] = "ALTER TABLE \"images\" ADD \"image_modified\" TIMESTAMP;";
	$queries[] = "ALTER TABLE \"rights\" ADD \"right_created\" TIMESTAMP;";
	$queries[] = "ALTER TABLE \"images\" ADD \"image_tags\" TEXT;";
	$queries[] = "ALTER TABLE \"pages\" ADD \"page_category\" TEXT;";
	$queries[] = "ALTER TABLE \"users\" ADD \"user_uri\" TEXT;";
	$queries[] = "ALTER TABLE \"tags\" ADD \"tag_parents\" TEXT;";
	$queries[] = "ALTER TABLE \"guests\" ADD \"guest_inclusive\" INTEGER;";
	$queries[] = "CREATE TABLE \"citations\" (\"citation_id\" SERIAL PRIMARY KEY, \"page_id\" INTEGER, \"citation_type\" TEXT, \"citation_uri\" TEXT, \"citation_uri_requested\" TEXT, \"citation_description\" TEXT, \"citation_title\" TEXT, \"citation_site_name\" TEXT, \"citation_created\" TIMESTAMP, \"citation_modified\" TIMESTAMP);";
	$queries[] = "ALTER TABLE \"comments\" ADD \"comment_response\" INTEGER;";
	$queries[] = "ALTER TABLE \"comments\" ADD \"comment_modified\" TIMESTAMP;";
	$queries[] = "ALTER TABLE \"comments\" ADD \"comment_deleted\" TIMESTAMP;";
	$queries[] = "ALTER TABLE \"guests\" ADD \"guest_inclusive\" INTEGER;";
	$queries[] = "ALTER TABLE \"images\" ADD \"image_deleted\" TIMESTAMP;";
	$queries[] = "ALTER TABLE \"images\" ADD \"image_tags\" TEXT;";
	$queries[] = "ALTER TABLE \"images\" ADD \"image_related\" TEXT;";
	$queries[] = "ALTER TABLE \"images\" ADD \"image_related_hash\" TEXT;";
	$queries[] = "ALTER TABLE \"images\" ADD \"image_directory\" TEXT;";
	$queries[] = "ALTER TABLE \"images\" ADD \"image_tag_count\" INTEGER;";
	$queries[] = "ALTER TABLE \"pages\" ADD \"page_deleted\" TIMESTAMP;";
	$queries[] = "ALTER TABLE \"pages\" ADD \"page_excerpt\" TEXT;";
	$queries[] = "ALTER TABLE \"pages\" ADD \"page_excerpt_raw\" TEXT;";
	$queries[] = "ALTER TABLE \"pages\" ADD \"page_category\" TEXT;";
	$queries[] = "ALTER TABLE \"rights\" ADD \"right_deleted\" TIMESTAMP;";
	$queries[] = "ALTER TABLE \"rights\" ADD \"right_markup\" TEXT;";
	$queries[] = "ALTER TABLE \"rights\" ADD \"right_description_raw\" TEXT;";
	$queries[] = "ALTER TABLE \"sets\" ADD \"set_deleted\" TIMESTAMP;";
	$queries[] = "ALTER TABLE \"sets\" ADD \"set_markup\" TEXT;";
	$queries[] = "ALTER TABLE \"sets\" ADD \"set_description_raw\" TEXT;";
	$queries[] = "ALTER TABLE \"sizes\" ADD \"size_modified\" TIMESTAMP;";
	$queries[] = "ALTER TABLE \"tags\" ADD \"tag_parents\" TEXT;";
	$queries[] = "ALTER TABLE \"users\" ADD \"user_uri\" TEXT;";
	$queries[] = "ALTER TABLE \"users\" ADD \"user_post_count\" INTEGER;";
	$queries[] = "ALTER TABLE \"users\" ADD \"user_comment_count\" INTEGER;";
	$queries[] = "CREATE TABLE \"items\" (\"item_id\" SERIAL PRIMARY KEY, \"item_table\" TEXT, \"item_table_id\" INTEGER, PRIMARY KEY (\"item_id\"));";
	$queries[] = "CREATE TABLE \"versions\" (\"version_id\" SERIAL PRIMARY KEY, \"post_id\" INTEGER, \"page_id\" INTEGER, \"user_id\" INTEGER, \"version_title\" TEXT, \"version_text_raw\" TEXT, \"version_created\" TIMESTAMP, \"version_similarity\" INTEGER);";
	$queries[] = "CREATE TABLE \"rights\" (\"right_id\" SERIAL PRIMARY KEY, \"right_title\" TEXT, \"right_uri\" TEXT, \"right_image\" TEXT, \"right_description\" TEXT, \"right_description_raw\" TEXT, \"right_markup\" TEXT, \"right_created\" TIMESTAMP, \"right_modified\" TIMESTAMP, \"right_image_count\" INTEGER, \"right_deleted\" TIMESTAMP);";

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