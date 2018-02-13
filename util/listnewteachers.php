<?php

require("../init.php");

if ($myrights<100 && ($myspecialrights&(32+64))==0) {
	exit;
}

$outputFormat = isset($_GET['format']) ? strtolower($_GET['format']) : 'html';

$now = time();
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

$stm = $DBH->prepare("SELECT time,log FROM imas_log WHERE log LIKE :log AND time>:start AND time<:end");
$stm->execute(array(':log'=>"New Instructor Request%",':start'=>$start,':end'=>$end));
$reqdates = array();
while ($reqdata = $stm->fetch(PDO::FETCH_ASSOC)) {
	$log = explode('::',substr($reqdata['log'], 24));
	$reqdates[Sanitize::onlyInt($log[0])] = $reqdata['time'];
}

if (count($reqdates)==0) {
    htmlHeader();
	echo "No requests found";
	require("../footer.php");
} else {
	$ph = Sanitize::generateQueryPlaceholders($reqdates);

	$query = "SELECT u.id,u.rights,g.name,u.LastName,u.FirstName,u.SID,u.email,COUNT(DISTINCT s.id) AS scnt FROM imas_users as u ";
	$query .= "LEFT JOIN imas_groups AS g ON g.id=u.groupid ";
	$query .= "LEFT JOIN imas_courses AS t ON u.id=t.ownerid ";
	$query .= "LEFT JOIN imas_students AS s ON s.courseid=t.id ";
	$query .= "WHERE u.id IN ($ph) GROUP BY u.id ORDER BY g.name,u.LastName";
	$stm = $DBH->prepare($query);
	$stm->execute(array_keys($reqdates));

	if ('html' == $outputFormat) {
		htmlHeader();
		outputHtml($stm, $reqdates);
		require("../footer.php");
	} elseif ('csv' == $outputFormat) {
		outputCsv($stm, $reqdates);
	}
}


function htmlHeader()
{
	extract($GLOBALS, EXTR_SKIP | EXTR_REFS); // Sadface. :(

	$placeinhead = '<script type="text/javascript" src="' . $imasroot . '/javascript/tablesorter.js"></script>';
	require("../header.php");

	echo '<h2>New Instructor Account Requests from ';
	echo date('M j, Y', $start) . ' to ' . date('M j, Y', $end) . '</h2>';
	?>
    <a style="float: right; padding-bottom: 10px;"
       href="<?php echo $GLOBALS['basesiteurl']; ?>/util/listnewteachers.php?<?php echo generateCsvQueryArgs(); ?>">Download
        CSV file</a>
	<?php
}


function outputHtml($stm, $reqdates)
{
	?>
    <table class="gb" id="myTable">
        <thead>
        <tr>
            <th>Group</th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Req Date</th>
            <th>Status</th>
            <th>Student count</th>
        </tr>
        </thead>
        <tbody>
	<?php
		$alt = 0;
		while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
			if ($row['name']===null) {
				$row['name'] = _('Default');
			}
			if ($alt==0) {echo "<tr class=even>"; $alt=1;} else {echo "<tr class=odd>"; $alt=0;}
			echo '<td>'.$row['name'].'</td>';
			echo '<td>';
			echo '<a href="../admin/userdetails.php?id='.$row['id'].'" target="_blank">';
			echo $row['LastName'].', '.$row['FirstName'].'</a></td>';
			echo '<td>'.$row['SID'].'</td>';
			echo '<td>'.$row['email'].'</td>';
			echo '<td>'.tzdate('n/j/y', $reqdates[$row['id']]).'</td>';
			if ($row['rights']==0 || $row['rights']==12) {
				echo '<td>Pending</td>';
			} else if ($row['rights']<20) {
				echo '<td>Student</td>';
			} else {
				echo '<td>Active</td>';
			}
			echo '<td>'.$row['scnt'].'</td>';
			echo '</tr>';
	}
	echo '</tbody></table>';
	echo '<script type="text/javascript">
			initSortTable("myTable",Array("S","S","S","S","D","S","N"),true);
		</script>';
}


function outputCsv($stm, $reqdates)
{
	header('Content-type: text/csv');
	header('Content-Disposition: attachment; filename="new_teacher_requests.csv"');

	$stdout = fopen('php://output', 'w');

	$headers = array(
		'userid',
		'username',
		'name',
		'group_name',
		'email',
		'request_date',
		'status',
		'student_count'
	);
	fputcsv($stdout, $headers);

	while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
		$status = null;
		if ($row['rights'] == 0 || $row['rights'] == 12) {
			$status = 'Pending';
		} else if ($row['rights'] < 20) {
			$status = 'Student';
		} else {
			$status = 'Active';
		}

		$data = array(
			$row['id'],
			$row['SID'],
			$row['LastName'] . ', ' . $row['FirstName'],
			$row['name'],
			$row['email'],
			tzdate('n/j/y', $reqdates[$row['id']]),
			$status,
			$row['scnt']
		);

		fputcsv($stdout, $data);
		fflush($stdout);
	}

	fclose($stdout);
}


function generateCsvQueryArgs()
{
    $args = array_merge($_GET, array('format' => 'csv'));
	return Sanitize::generateQueryStringFromMap($args);
}
