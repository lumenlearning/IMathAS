<?php

require("../init.php");

if ($myrights<100 && ($myspecialrights&64)!=64) {exit;}

$upd = $DBH->prepare("UPDATE imas_instr_acct_reqs SET reqdata=?,status=10 WHERE userid=?");

$query = 'SELECT userid,reqdata FROM imas_instr_acct_reqs WHERE status>0 AND status<10 AND reqdate < ?';
$stm = $DBH->prepare($query);
$stm->execute(array(time() - 30*24*60*60));
$todel = [];
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
    $todel[] = $row['userid'];

    $reqdata = json_decode($row['reqdata'], true);
    if (!isset($reqdata['actions'])) {
        $reqdata['actions'] = array();
    }
    $reqdata['actions'][] = array(
        'by'=>$userid,
        'on'=>time(),
        'status'=>10,
        'note'=>'autoold');
    $upd->execute(array(json_encode($reqdata), $row['userid']));
}

// remove from instructor autoenroll
if (isset($CFG['GEN']['enrollonnewinstructor'])) {
    require("../includes/unenroll.php");
    foreach ($CFG['GEN']['enrollonnewinstructor'] as $rcid) {
        unenrollstu($rcid, $todel);
    }
}

$todellist = implode(',', $todel);

if (count($todel)>0) {
    $DBH->query("UPDATE imas_users SET rights=10 WHERE id IN ($todellist)");
}

echo count($todel).' account requests auto-denied because old';

/*
$stm = $DBH->query("SELECT iu.id FROM imas_users AS iu 
  LEFT JOIN imas_students AS istu ON istu.userid=iu.id WHERE iu.id IN ($todellist) AND istu.courseid IS NULL");
$nostu = $stm->fetchAll(PDO::FETCH_COLUMN,0);
$stm = $DBH->query("SELECT iu.id FROM imas_users AS iu 
  LEFT JOIN imas_tutors AS istu ON istu.userid=iu.id WHERE iu.id IN ($todellist) AND istu.courseid IS NULL");
$notutor = $stm->fetchAll(PDO::FETCH_COLUMN,0);
$stm = $DBH->query("SELECT iu.id FROM imas_users AS iu 
  LEFT JOIN imas_teachers AS istu ON istu.userid=iu.id WHERE iu.id IN ($todellist) AND istu.courseid IS NULL");
$noteach = $stm->fetchAll(PDO::FETCH_COLUMN,0);

$noany = array_values(array_intersect($nostu, $notutor, $noteach));
// for ones that are not in anything, delete account completely?

*/


