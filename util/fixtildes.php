<?php

require("../init.php");

if ($myrights<40) {
	echo 'You do not have the authority for this action';
	exit;
}

if (empty($_GET['id'])) {
	echo 'Supply the assessment ID, like fixtildes.php?id=###';
	exit;
}

$aid = Sanitize::onlyInt($_GET['id']);

$stm = $DBH->prepare("SELECT ic.ownerid FROM imas_courses AS ic JOIN imas_assessments AS ia ON ia.courseid=ic.id WHERE ia.id=?");
$stm->execute(array($aid));
$owner = $stm->fetchColumn(0);

if ($owner != $userid) {
	echo 'You can only run this on your own assessments';
	exit;
}

$query = "UPDATE imas_assessment_sessions SET ";
$query .= "lastanswers=REPLACE(lastanswers, '&tilde;', '%tilde;'),";
$query .= "bestlastanswers=REPLACE(bestlastanswers, '&tilde;', '%tilde;') ";
$query .= "WHERE assessmentid=?";
$stm = $DBH->prepare($query);
$stm->execute(array($aid));
echo $stm->rowCount().' records updated';
