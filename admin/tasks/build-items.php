<?php

/*
// FSIP based on Alkaline
// Alkaline Copyright (c) 2010-2012 by Budin Ltd. Released to the wild under MIT license.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'fsip.php');

$fsip = new FSIP;
$user = new User;

$user->perm(true, 'maintenance');

$id = $fsip->findID(@$_POST['image_id']);

if(empty($id)){
	$ids = array();
	
	foreach($fsip->tables_index as $key => $value){
		$ids[] = ++$key;
	}
	
	echo json_encode($ids);
}
else{
	$table = $fsip->tables_index[--$id];
	
	$ids = new Find($table);
	$ids->find();
	
	$query = $fsip->prepare('SELECT item_table_id FROM items WHERE item_table = :item_table;');
	$query->execute(array(':item_table' => $table));
	$items = $query->fetchAll();
	
	$item_table_ids = array();
	
	foreach($items as $item){
		$item_table_ids[] = $item['item_table_id'];
	}
	
	foreach($ids->ids as $item_id){
		if(in_array($item_id, $item_table_ids)){ continue; }
		
		$fields = array('item_table' => $fsip->tables_index[$id],
			'item_table_id' => $item_id);
		$fsip->addRow($fields, 'items');
	}
	
	$delete_ids = array();
	
	foreach($item_table_ids as $item_id){
		if(in_array($item_id, $ids->ids)){ continue; }
		$delete_ids[] = $item_id;
	}
	
	$query = $fsip->prepare('DELETE FROM items WHERE item_table = :item_table AND item_table_id IN (' . implode(', ', $delete_ids) . ')');
	$query->execute(array(':item_table' => $table));
}

?>