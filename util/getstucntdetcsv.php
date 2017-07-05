<?php
	require("../init.php");
	if ($myrights<40) {
		exit;
	}
	$now = time();

	function exportascsv($arr) {
		$line = '';
		foreach ($arr as $val) {
			 # remove any windows new lines, as they interfere with the parsing at the other end
			  $val = str_replace("\r\n", "\n", $val);
			  $val = str_replace("\n", " ", $val);
			  $val = str_replace(array("<BR>",'<br>','<br/>'), ' ',$val);
			  $val = str_replace("&nbsp;"," ",$val);

			  # if a deliminator char, a double quote char or a newline are in the field, add quotes
			  if(preg_match("/[\,\"\n\r]/", $val)) {
				  $val = '"'.str_replace('"', '""', $val).'"';
			  }
			  $line .= $val.',';
		}
		# strip the last deliminator
		$line = substr($line, 0, -1);
		$line .= "\n";
		echo $line;

	}
	header('Content-type: text/csv');
	header("Content-Disposition: attachment; filename=\"userreport.csv\"");
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');


	$start = $now - 60*60*24*30;
	$end = $now;
	if (isset($_GET['start'])) {
		$parts = explode('-',$_GET['start']);
		if (count($parts)==3) {
			$start = mktime(0,0,0,$parts[0],$parts[1],$parts[2]);
		}
	} else if (isset($_GET['days'])) {
		$start = $now - 60*60*24*intval($_GET['days']);
	}

	if (isset($_GET['end'])) {
		$parts = explode('-',$_GET['end']);
		if (count($parts)==3) {
			$end = mktime(0,0,0,$parts[0],$parts[1],$parts[2]);
		}
	}

	exportascsv(array('Enrollments from '.date('M j, Y',$start).' to '.date('M j, Y',$end)));

	exportascsv(array('Course Name',
		'Course ID',
		'Course Active Students',
		'Is LTI',
		'Template',
		'Instructor',
		'Email',
		'Total Active Students for Instructor',
		'Institution',
		'Total Active Students at Institution',
		'Total Active Instructors at Institution',
		'Lumen Customer',
		'Supergroup'
		));



	if (isset($CFG['GEN']['guesttempaccts'])) {
		$skipcid = $CFG['GEN']['guesttempaccts'];
	} else {
		$skipcid = array();
	}
	
	//pull template courses
	$stm = $DBH->query("SELECT id,name FROM imas_courses WHERE (istemplate&1)=1 OR (istemplate&2)=2 ORDER BY name");
	$templates = array();
	while ($row = $stm->fetch(PDO::FETCH_NUM)) {
		$templates[$row[0]] = $row[1];
	}                                  
	$templateids = array_keys($templates);

	//DB $query = "SELECT id FROM imas_courses WHERE (istemplate&4)=4";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	//DB while ($row = mysql_fetch_row($result)) {
	$stm = $DBH->query("SELECT id FROM imas_courses WHERE (istemplate&4)=4");
	while ($row = $stm->fetch(PDO::FETCH_NUM)) {
		$skipcid[] = $row[0];
	}
	$skipcids = implode(',',$skipcid);

	$grpnames = array();
	//DB $query = 'SELECT id,name FROM imas_groups WHERE 1';
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	//DB while ($row = mysql_fetch_row($result)) {
	$stm = $DBH->query('SELECT id,name FROM imas_groups WHERE 1');
	while ($row = $stm->fetch(PDO::FETCH_NUM)) {
		$grpnames[$row[0]] = $row[1];
	}
	
	$lticourses = array();
	$stm = $DBH->query('SELECT courseid,contextid FROM imas_lti_courses WHERE 1');
	while ($row = $stm->fetch(PDO::FETCH_NUM)) {
		$lticourses[$row[0]] = $row[1];
	}

	$query = "SELECT g.name AS gname,u.LastName,u.FirstName,c.id,c.name AS cname,COUNT(DISTINCT s.id) AS cnt,u.email,g.parent,g.grouptype,c.ancestors FROM imas_students AS s JOIN imas_teachers AS t ";
	$query .= "ON s.courseid=t.courseid AND s.lastaccess>$start ";
	if ($end != $now) {
		$query .= "AND s.lastaccess<$end ";
	}
	$query .= "JOIN imas_courses AS c ON t.courseid=c.id ";
	$query .= "JOIN imas_users as u ";
	$query .= "ON u.id=t.userid JOIN imas_groups AS g ON g.id=u.groupid ";
	$query .= "GROUP BY u.id,c.id ORDER BY g.name,u.LastName,u.FirstName,c.name";

	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	$stm = $DBH->query($query);
	$lastgroup = '';  $lastparent = ''; $grpcnt = 0; $grpdata = array();  $lastuser = ''; $userdata = array(); $grpinstrcnt = 0;
	$lastemail; $instrstucnt = 0;
	$seencid = array();
	//DB while ($row = mysql_fetch_row($result)) {
	while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
		if ($row['LastName'].', '.$row['FirstName']!=$lastuser) {
			if ($lastuser != '') {
				foreach ($userdata as $d) {
					$d[] = $lastuser;
					$d[] = $lastemail;
					$d[] = $instrstucnt;
					$grpdata[] = $d;
				}
			}
			$userdata = array();
			$lastuser = $row['LastName'].', '.$row['FirstName'];
			$lastemail = $row['email'];
			$instrstucnt = 0;
			$grpinstrcnt++;
		}
		if ($row['gname'] != $lastgroup) {
			if ($lastgroup != '') {
				foreach ($grpdata as $d) {
					$d[] = $lastgroup;
					$d[] = $grpcnt;
					$d[] = $grpinstrcnt;
					$d[] = $lastiscust;
					$d[] = $lastparent;
					exportascsv($d);
				}
			}
			$grpcnt = 0;  $grpdata = array(); $grpinstrcnt = 0;
			$lastgroup = $row['gname'];
			$lastparent = (($row['parent']>0)?$grpnames[$row['parent']]:"");
			$lastiscust = (($row['grouptype']==1)?'Y':'N');
		}
		$islti = (isset($lticourses[$row['id']])?'Y':'N');
		if (!in_array($row['id'],$seencid)) {
			$grpcnt += $row['cnt'];
			$instrstucnt += $row['cnt'];
			$seencid[] = $row['id'];
			$templatematches = array_intersect(explode(',', $row['ancestors']), $templateids);
			if (count($templatematches)>0) {
				$sourcetemplate = $templates[array_pop($templatematches)];
			} else {
				$sourcetemplate = '';
			}
			$userdata[] = array($row['cname'],$row['id'],$row['cnt'],$islti,$sourcetemplate);
		} else {
			$userdata[] = array($row['cname'],$row['id'],$row['cnt'].'(*)',$islti,'');
			//$userdata .= "<sup>*</sup>";
		}
		//$userdata .= "</li>";
	}

	foreach ($userdata as $d) {
		$d[] = $lastuser;
		$d[] = $lastemail;
		$d[] = $instrstucnt;
		$grpdata[] = $d;
	}
	foreach ($grpdata as $d) {
		$d[] = $lastgroup;
		$d[] = $grpcnt;
		$d[] = $grpinstrcnt;
		$d[] = $lastiscust;
		$d[] = $lastparent;
		exportascsv($d);
	}

?>
