<?php

require_once('../config.php');
require_once('../extensions/markdown/functions/markdown.php');
require_once('../extensions/markdown/functions/smartypants.php');

$page = "README";
if (isset($_GET['page'])) {
	$page = $_GET['page'];
}

if ($page == "fsip_rebuild_all_html_docs") {
	rebuildREADME();

	foreach(glob(PATH . DOCS.'*/{*.md}') as $md_doc)
	{  
		rebuildDoc($md_doc);
	}  
	exit;
}


if ($page == "README") {
	$outfile = PATH . 'README.html';
	if (file_exists($outfile)) {
		header('Location: '. LOCATION .'README.html');
	}
	$page_text = rebuildREADME();
} else {
	$outfile = PATH .'docs/'. $page.'.html';
	if (file_exists($outfile)) {
		header('Location: '. LOCATION .'docs/'.$page.'.html');
	}
	$infile = PATH .'docs/'. $page.'.md';
	$page_text = rebuildDoc();
}

/// FUNCTIONS
function rebuildREADME() {
	$infile = PATH . 'README.md';
	$page_contents = file_get_contents($infile);
	if ($page_contents === false) { 
	    // file open failure
	    echo "error opening file $infile";
	    exit;
	} 
	convert_and_put_contents(PATH . 'README.html', $page_contents);
	return $page_contents;
}

function rebuildDoc($infile) {
	$page_contents = file_get_contents($infile);
	if ($page_contents === false) { 
	    // file open failure
	    echo "error opening file $infile";
	    exit;
	} 
	convert_and_put_contents(PATH .'docs/'. $file_name. '.html', $page_contents);
	return $page_contents;
}

function convert_and_put_contents($out, $text) {
	$text = convertMD($text);
	$result = file_put_contents($out, $text);
	echo $result;

	if ($result === false) { 
		// file open failure
		echo "Error writing file $out";
	} 
	return $result;
}

function convertMD($page_text) {
	// Markdown
	$parser = new Markdown_Parser;
	$page_text = $parser->transform($page_text);

	// SmartyPants text
	$parser = new Markdown_SmartyPants;
	$page_text = $parser->transform($page_text);
	return $page_text;
}
?>