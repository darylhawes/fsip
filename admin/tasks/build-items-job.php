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

ini_set('error_reporting', 0);
ini_set('display_errors', 0);

chdir(__DIR__);

require_once('../../config.php');

global $db;

// Deny external execution
if(!isset($argv)){ exit(); };

$table_ids = array();
$tables_index = getTablesIndex();

foreach($tables_index as $key => $value) {
	$table_ids[] = ++$key;
}
	
foreach($table_ids as $id) {
	$table = $tables_index[--$id];
	
	$ids = new Find($table);
	$ids->find();
	
	$query = $db->prepare('SELECT item_table_id FROM items WHERE item_table = :item_table;');
	$query->execute(array(':item_table' => $table));
	$items = $query->fetchAll();
	
	$item_table_ids = array();
	
	foreach($items as $item) {
		$item_table_ids[] = $item['item_table_id'];
	}
	
	foreach($ids->ids as $item_id) {
		if (in_array($item_id, $item_table_ids)) { 
			continue; 
		}

		$fields = array('item_table' => $tables_index[$id],
			'item_table_id' => $item_id);
		$db->addRow($fields, 'items');
	}
	
	$delete_ids = array();
	
	foreach($item_table_ids as $item_id) {
		if (in_array($item_id, $ids->ids)) { 
			continue; 
		}
		$delete_ids[] = $item_id;
	}
	
	$query = $db->prepare('DELETE FROM items WHERE item_table = :item_table AND item_table_id IN (' . implode(', ', $delete_ids) . ')');
	$query->execute(array(':item_table' => $table));
}

?>