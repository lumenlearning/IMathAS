<?php
require_once "../init.php";
if ($myrights<100) {exit;}
if ((isset($_POST['submit']) && $_POST['submit']=="Message") || isset($_GET['masssend'])) {
	$cid = $CFG['GEN']['sendquestionproblemsthroughcourse'];
	$teacherid = true;
	$calledfrom = "itemsearch";
	require_once "../course/masssend.php";
	exit;
}
$pagetitle = _('Search through inline and link text items')
require_once "../header.php";
echo '<h1>Search through inline and link text items</h1>';
echo '<form method="post"><p>Search: <input type="text" name="search" size="40" value="'.Sanitize::encodeStringForDisplay($_POST['search']).'"> <input type="submit" value="Search"/></p>';
if (isset($_POST['search'])) {
	echo '<p>';
	echo '<input type="submit" name="submit" value="Message"></p><p>';

	$srch = $_POST['search'];
	$query = "SELECT DISTINCT imas_users.*,imas_courses.id AS cid,imas_groups.name AS groupname FROM imas_users JOIN imas_courses ON imas_users.id=imas_courses.ownerid JOIN imas_groups ON imas_groups.id=imas_users.groupid WHERE imas_courses.id IN ";
	$query .= "(SELECT courseid FROM imas_inlinetext WHERE text LIKE :srch) OR imas_courses.id IN ";
	$query .= "(SELECT courseid FROM imas_linkedtext WHERE text LIKE :srchB OR summary LIKE :srchC) ORDER BY imas_groups.name,imas_users.LastName";
	$stm = $DBH->prepare($query);
	$stm->execute(array(':srch'=>"%$srch%", ':srchB'=>$srch, ':srchC'=>$srch));
	$lastperson = '';
	echo "Count: ".$stm->rowCount();
	while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
		$thisperson = $row['LastName'].', '.$row['FirstName'];
		if ($thisperson != $lastperson) {
			echo '<br/><input type="checkbox" name="checked[]" value="'.Sanitize::encodeStringForDisplay($row['id']).'" checked="checked"> <span class="pii-full-name">'.Sanitize::encodeStringForDisplay($thisperson) .'</span> ('.Sanitize::encodeStringForDisplay($row['groupname']).')';
			$lastperson= $thisperson;
		}
		echo ' <a href="../course/course.php?cid='.Sanitize::courseId($row['cid']).'" target="_blank">'.Sanitize::courseId($row['cid']).'</a>';
	}
	echo '</p>';

}
echo '</form>';
require_once "../footer.php";
?>
