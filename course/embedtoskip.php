<?php
require("../validate.php");

if (!isset($teacherid)) {
	echo "Must be a teacher to access this page";
	exit;
}

$id = intval($_GET['id']);

$query = "SELECT intro FROM imas_assessments WHERE id=$id";
$result = mysql_query($query) or die("Query failed :$query " . mysql_error());
$row = mysql_fetch_row($result);

$text = preg_replace('/[PAGE[^\]]*]/sm','',$text);
$text = preg_replace('/<p[^>]*>(\s|&nbsp;)*(\[QUESTION.*?\])(\s|&nbsp;)*<\/p>/sm', ' $2 ', $row[0]);
$text = preg_replace('/<p[^>]*>(\s|&nbsp;)*(\[PAGE.*?\])(\s|&nbsp;)*<\/p>/sm', '', $text);
$text = preg_replace('/<p[^>]*>(\s|&nbsp;)*<span[^>]*>(\s|&nbsp;)*(\[QUESTION.*?\])(\s|&nbsp;)*<\/span>(\s|&nbsp;)*<\/p>/sm', ' $3 ', $text);
$text = preg_replace('/<p[^>]*>(\s|&nbsp;)*<span[^>]*>(\s|&nbsp;)*(\[PAGE.*?\])(\s|&nbsp;)*<\/span>(\s|&nbsp;)*<\/p>/sm', '', $text);

$sp = preg_split('/\[QUESTION\s*(\d+)\]/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

$n = 0;
$out = '';

while (isset($sp[$n])) {
	$text = trim($sp[$n]);
	$qn = array($sp[$n+1]);
	$n+=2;
	while (isset($sp[$n]) && trim($sp[$n])=='' && isset($sp[$n+1])) {
		$qn[1] = $sp[$n+1];
		$n+=2;
	}
	if (isset($sp[$n]) && !isset($sp[$n+1])) { //last item in the set
		$text .= trim($sp[$n]);
		$n++;
	}
	$out .= '<p>';
	if (count($qn)==1) {
		$out .= '[Q '.$qn[0].']';
	} else {
		$out .= '[Q '.implode('-',$qn).']';
	}
	$out .= '</p>';
	
	$out .= $text;
}

$out = addslashes($out);
$query = "UPDATE imas_assessments SET intro='$out',displaymethod='SkipAround' WHERE id='$id'";
mysql_query($query) or die("Query failed :$query " . mysql_error());

?>
