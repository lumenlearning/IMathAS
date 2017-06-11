<?php
require("../init.php");

if (!isset($teacherid)) {
	echo "Must be a teacher to access this page";
	exit;
}

$id = intval($_GET['id']);

//DB $query = "SELECT intro FROM imas_assessments WHERE id=$id";
//DB $result = mysql_query($query) or die("Query failed :$query " . mysql_error());
//DB $row = mysql_fetch_row($result);
$stm = $DBH->prepare("SELECT intro FROM imas_assessments WHERE id=:id");
$stm->execute(array(':id'=>$id));
$row = $stm->fetch(PDO::FETCH_NUM);

$text = preg_replace('/[PAGE[^\]]*]/sm','',$row[0]);
$text = preg_replace('/<p[^>]*>(\s|&nbsp;)*(\[QUESTION.*?\])(\s|&nbsp;)*<\/p>/sm', ' $2 ', $text);
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

//DB $out = addslashes($out);
//DB $query = "UPDATE imas_assessments SET intro='$out',displaymethod='SkipAround' WHERE id='$id'";
//DB mysql_query($query) or die("Query failed :$query " . mysql_error());
$stm = $DBH->prepare("UPDATE imas_assessments SET intro=:intro,displaymethod='SkipAround' WHERE id=:id");
$stm->execute(array(':intro'=>$out, ':id'=>$id));

?>
