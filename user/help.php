<?php

require_once('../config.php');
require_once('../extensions/markdown/functions/markdown.php');
require_once('../extensions/markdown/functions/smartypants.php');

/*$page = "README";
if (isset($_GET['page'])) {
	$page = $_GET['page'];
}
*/

//if ($page == "fsip_rebuild_all_html_docs") {

	foreach(glob(PATH . DOCS .'*.md') as $md_doc) {
		rebuildDoc($md_doc);
	}
	echo rebuildREADME();
	exit;
//}

/*
if ($page == "README") {
	$outfile = PATH . 'README.html';
	if (file_exists($outfile)) {
		header('Location: '. LOCATION . BASE .'README.html');
		echo "<h1>Redirecting</h1><p>You are being redirected. If you're still here after a few seconds please ".'<a href="'. LOCATION . BASE . 'README.html' .'">'."click here</a></p>";
		exit();
	}
	echo rebuildREADME();
	exit;
} else { //page is in the docs/ folder
	$outfile = PATH . "docs/" . $page.'.html';
	if (file_exists($outfile)) {
		header('Location: '. LOCATION . BASE . "docs/" .$page.'.html');
		echo "<h1>Redirecting</h1><p>You are being redirected. If you're still here after a few seconds please ".'<a href="'. LOCATION . BASE  . "docs/" . $page.'.html">'."click here</a></p>";
		exit();
	}
	$infile = PATH . "docs/". $page.'.md';
	echo  rebuildDoc($infile);
	exit;
}*/

/// FUNCTIONS
function rebuildREADME() {
	$infile = PATH . 'README.md';
	$page_contents = file_get_contents($infile);
	if ($page_contents === false) { 
	    // file open failure
	    echo "error opening file $infile";
	    exit;
	} 
	$page_contents = convert_and_put_contents(PATH . 'README.html', $page_contents);
	return $page_contents;
}

function rebuildDoc($infile) {
	$page_contents = file_get_contents($infile);
	if ($page_contents === false) { 
	    // file open failure
	    echo "error opening file $infile";
	    exit;
	} 
	$page_contents = convert_and_put_contents($infile. '.html', $page_contents);
	return $page_contents;
}

function convert_and_put_contents($out, $text) {
	// Markdown
	$parser = new Markdown_Parser;
	$text = $parser->transform($text);

	// SmartyPants text
	$parser = new Markdown_SmartyPants;
	$text = $parser->transform($text);

	$result = file_put_contents($out, $text);

	if ($result === false) { 
		// file open failure
		echo "Error writing file $out";
	} 
	return $text;
}
?>