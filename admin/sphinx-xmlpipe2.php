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

error_reporting(E_ALL);
ini_set('display_errors', true);

$path = explode('/', __FILE__);
$path = array_splice($path, 0, -2);
$path = implode('/', $path) . '/';

header('Content-Type: application/xml');

require_once($path . 'config.php');

global $db;

$xml = new XMLWriter();

$xml->openMemory();
$xml->setIndent(true);
$xml->startDocument('1.0', 'utf-8');
$xml->startElementNS('sphinx', 'docset', null);

$xml->startAttributeNS('sphinx', 'sphinx', 'http://sphinxsearch.com/');
$xml->endAttribute();

	$xml->startElementNS('sphinx', 'schema', null);
		
		$xml->startElementNS('sphinx', 'field', null);
			$xml->startAttribute('name');
			$xml->text('title');
			$xml->endAttribute();
		$xml->endElement();
		
		$xml->startElementNS('sphinx', 'field', null);
			$xml->startAttribute('name');
			$xml->text('text');
			$xml->endAttribute();
		$xml->endElement();
		
		$xml->startElementNS('sphinx', 'attr', null);
			$xml->startAttribute('name');
			$xml->text('table');
			$xml->endAttribute();
			$xml->startAttribute('type');
			$xml->text('int');
			$xml->endAttribute();
			$xml->startAttribute('bits');
			$xml->text('8');
			$xml->endAttribute();
		$xml->endElement();
		
		$xml->startElementNS('sphinx', 'attr', null);
			$xml->startAttribute('name');
			$xml->text('table_id');
			$xml->endAttribute();
			$xml->startAttribute('type');
			$xml->text('int');
			$xml->endAttribute();
			$xml->startAttribute('bits');
			$xml->text('32');
			$xml->endAttribute();
		$xml->endElement();

	$xml->endElement();
	
	$item_ids = new Find('items');
	$item_ids->find();
	
	$count = 0;
	
	while($count <= $item_ids->count) {
		$ids = array_slice($item_ids->ids, $count, 1000);
		
		$items = $db->getTable('items', $ids);
		
		$item_ids = array();
		$item_table_ids = array();
		$item_tables = array();
		
		foreach($items as $item) {
			$item_table = $item['item_table'];
			$table_int = array_search($item_table, getTablesIndex());
			
			$item_ids[$table_int][] = $item['item_id'];
			$item_table_ids[$table_int][] = $item['item_table_id'];
			$item_tables[$table_int][] = $item['item_table'];
		}
		
		$item_count = count($item_ids);
		
		for($i=0; $i < $item_count; $i++) {
			if ($item_tables[$i][0] == 'comments') {
				$comments = $db->getTable('comments', $item_table_ids[$i]);
				$comment_count = count($comments);
				
				$table_int = array_search('comments', getTablesIndex());
				
				for($j=0; $j < $comment_count; $j++) {
					$xml->startElement('sphinx:document');
						$xml->startAttribute('id');
						$xml->text($item_ids[$i][$j]);
						$xml->endAttribute();
						$xml->startElement('title');
						$xml->text('');
						$xml->endElement();
						$xml->startElement('text');
						$xml->text($comments[$j]['comment_text'] . "\n\n" . $comments[$j]['comment_author_name'] . "\n\n" . $comments[$j]['comment_author_uri'] . "\n\n" . $comments[$j]['comment_author_email'] . "\n\n" . $comments[$j]['comment_author_ip']);
						$xml->endElement();
						$xml->startElement('table');
						$xml->text($table_int);
						$xml->endElement();
						$xml->startElement('table_id');
						$xml->text($item_table_ids[$i][$j]);
						$xml->endElement();
					$xml->endElement();
				}
			} elseif($item_tables[$i][0] == 'images') {
				$images = $db->getTable('images', $item_table_ids[$i]);
				$image_count = count($images);
				
				$table_int = array_search('images', getTablesIndex());
				
				for($j=0; $j < $image_count; $j++){
					$xml->startElement('sphinx:document');
						$xml->startAttribute('id');
						$xml->text($item_ids[$i][$j]);
						$xml->endAttribute();
						$xml->startElement('title');
						$xml->text($images[$j]['image_title']);
						$xml->endElement();
						$xml->startElement('text');
						$xml->text($images[$j]['image_description'] . "\n\n" . $images[$j]['image_tags'] . "\n\n" . $images[$j]['image_geo']);
						$xml->endElement();
						$xml->startElement('table');
						$xml->text($table_int);
						$xml->endElement();
						$xml->startElement('table_id');
						$xml->text($item_table_ids[$i][$j]);
						$xml->endElement();
					$xml->endElement();
				}
			} elseif($item_tables[$i][0] == 'pages') {
				$pages = $db->getTable('pages', $item_table_ids[$i]);
				$page_count = count($pages);
				
				$table_int = array_search('pages', getTablesIndex());
				
				for($j=0; $j < $page_count; $j++){
					$xml->startElement('sphinx:document');
						$xml->startAttribute('id');
						$xml->text($item_ids[$i][$j]);
						$xml->endAttribute();
						$xml->startElement('title');
						$xml->text($pages[$j]['page_title']);
						$xml->endElement();
						$xml->startElement('text');
						$xml->text($posts[$j]['page_category'] . "\n\n" . $pages[$j]['page_description']);
						$xml->endElement();
						$xml->startElement('table');
						$xml->text($table_int);
						$xml->endElement();
						$xml->startElement('table_id');
						$xml->text($item_table_ids[$i][$j]);
						$xml->endElement();
					$xml->endElement();
				}
			} elseif($item_tables[$i][0] == 'rights') {
				$rights = $db->getTable('rights', $item_table_ids[$i]);
				$right_count = count($rights);
				
				$table_int = array_search('rights', getTablesIndex());
				
				for($j=0; $j < $right_count; $j++){
					$xml->startElement('sphinx:document');
						$xml->startAttribute('id');
						$xml->text($item_ids[$i][$j]);
						$xml->endAttribute();
						$xml->startElement('title');
						$xml->text($rights[$j]['right_title']);
						$xml->endElement();
						$xml->startElement('text');
						$xml->text($rights[$j]['right_description']);
						$xml->endElement();
						$xml->startElement('table');
						$xml->text($table_int);
						$xml->endElement();
						$xml->startElement('table_id');
						$xml->text($item_table_ids[$i][$j]);
						$xml->endElement();
					$xml->endElement();
				}
			} elseif($item_tables[$i][0] == 'sets') {
				$sets = $db->getTable('sets', $item_table_ids[$i]);
				$set_count = count($sets);
				
				$table_int = array_search('sets', getTablesIndex());
				
				for($j=0; $j < $set_count; $j++){
					$xml->startElement('sphinx:document');
						$xml->startAttribute('id');
						$xml->text($item_ids[$i][$j]);
						$xml->endAttribute();
						$xml->startElement('title');
						$xml->text($sets[$j]['set_title']);
						$xml->endElement();
						$xml->startElement('text');
						$xml->text($sets[$j]['set_description']);
						$xml->endElement();
						$xml->startElement('table');
						$xml->text($table_int);
						$xml->endElement();
						$xml->startElement('table_id');
						$xml->text($item_table_ids[$i][$j]);
						$xml->endElement();
					$xml->endElement();
				}
			}
			elseif($item_tables[$i][0] == 'tags'){
				$tags = $db->getTable('tags', $item_table_ids[$i]);
				$tag_count = count($tags);
				
				$table_int = array_search('tags', getTablesIndex());
				
				for($j=0; $j < $tag_count; $j++){
					$xml->startElement('sphinx:document');
						$xml->startAttribute('id');
						$xml->text($item_ids[$i][$j]);
						$xml->endAttribute();
						$xml->startElement('title');
						$xml->text($tags[$j]['tag_name']);
						$xml->endElement();
						$xml->startElement('text');
						$xml->text('');
						$xml->endElement();
						$xml->startElement('table');
						$xml->text($table_int);
						$xml->endElement();
						$xml->startElement('table_id');
						$xml->text($item_table_ids[$i][$j]);
						$xml->endElement();
					$xml->endElement();
				}
			}
		}
		
		$count = $count + 1000;
	}
		
$xml->endElement();
$xml->endDocument();

echo $xml->outputMemory();

?>