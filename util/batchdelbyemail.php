<?php

require("../init.php");
require("../includes/unenroll.php");
			
if ($myrights<100) {exit;}

$todelemail = "netsparker@example.com";

$updstm = $DBH->prepare("UPDATE imas_instr_acct_reqs SET status=?,reqdata=? WHERE userid=?");
$del1stm = $DBH->prepare("DELETE FROM imas_users WHERE id=:id");
			
$query = 'SELECT ir.status,ir.reqdata,ir.reqdate,iu.id ';
$query .= 'FROM imas_instr_acct_reqs AS ir JOIN imas_users AS iu ';
$query .= 'ON ir.userid=iu.id WHERE iu.email=:email';
$stm = $DBH->prepare($query);
$stm->execute(array(':email'=>$todelemail));
$cnt = 0;
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
	//update instr_acct_reqs
	$reqdata = json_decode($row['reqdata'], true);
	if (!isset($reqdata['actions'])) {
		$reqdata['actions'] = array();
	}
	$reqdata['actions'][] = array(
		'by'=>$userid,
		'on'=>time(),
		'status'=>10);
	$updstm->execute(array(10, json_encode($reqdata), $row['id']));
	
	//unenroll from auto-enroll courses
	if (isset($CFG['GEN']['enrollonnewinstructor'])) {
		foreach ($CFG['GEN']['enrollonnewinstructor'] as $rcid) {
			unenrollstu($rcid, array($row['id']));
		}
	}
	//delete user account
	$del1stm->execute(array(':id'=>$row['id']));
	$cnt++;
}
echo "DONE with $cnt";	 
	

	


