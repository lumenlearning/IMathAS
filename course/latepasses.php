<?php
//IMathAS:  Manage LatePasses
//(c) 2007 David Lippman

	require_once "../init.php";


	if (!(isset($teacherid))) {
		require_once "../header.php";
		echo "You need to log in as a teacher to access this page";
		require_once "../footer.php";
		exit;
	}
	$cid = Sanitize::courseId($_GET['cid']);

	if (isset($_POST['hours'])) {
		if (isset($_POST['latepass'])) {
			$stm = $DBH->prepare("UPDATE imas_students SET latepass=:latepass WHERE userid=:userid AND courseid=:courseid");
			foreach ($_POST['latepass'] as $uid=>$lp) {
				$stm->execute(array(':latepass'=>$lp, ':userid'=>$uid, ':courseid'=>$cid));
			}
		}
		$stm = $DBH->prepare("UPDATE imas_courses SET latepasshrs=:latepasshrs WHERE id=:id");
		$stm->execute(array(':latepasshrs'=>max(1,intval($_POST['hours'])), ':id'=>$cid));
		header('Location: ' . $GLOBALS['basesiteurl'] . "/course/listusers.php?cid=$cid" . "&r=" . Sanitize::randomQueryStringParam());
		exit;
	}

	$sections = [];
	$query = "SELECT imas_users.id,imas_users.LastName,imas_users.FirstName,imas_students.section,imas_students.latepass ";
	$query .= "FROM imas_users,imas_students WHERE ";
	$query .= "imas_users.id=imas_students.userid AND imas_students.courseid=:courseid ";
	$query .= "ORDER BY imas_users.LastName,imas_users.FirstName";
	$stm = $DBH->prepare($query);
	$stm->execute([':courseid'=>$cid]);
	$sturows = [];
	$hasemptysec = false;
	while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
		$sturows[] = $row;
		if (is_null($row['section'])) {
			$hasemptysec = true;
		} else {
			$sections[] =  $row['section'];
		}
	}

	$sections = array_unique($sections);
	sort($sections);
	
	$hassections = (count($sections)>1 || (count($sections)>0 && $hasemptysec));

	if ($hassections) {
		$stm = $DBH->prepare("SELECT usersort FROM imas_gbscheme WHERE courseid=:courseid");
		$stm->execute(array(':courseid'=>$cid));
		if ($stm->fetchColumn(0)==0) {
			$sortorder = "sec";
			usort($sturows, function ($a,$b) {
				if ($a['section'] !== $b['section']) {
					return $a['section'] <=> $b['section'];
				}
				if ($a['LastName'] !== $b['LastName']) {
					return strcasecmp($a['LastName'], $b['LastName']);
				}
				return strcasecmp($a['FirstName'], $b['FirstName']);
			});
		} else {
			$sortorder = "name";
		}
	}

    require_once "../header.php";
    echo "<div class=breadcrumb>$breadcrumbbase ";
    if (empty($_COOKIE['fromltimenu'])) {
        echo " <a href=\"course.php?cid=$cid\">".Sanitize::encodeStringForDisplay($coursename)."</a> &gt; ";
    }
	echo "<a href=\"listusers.php?cid=$cid\">Roster</a> ";
	echo "&gt; Manage LatePasses</div>";

	echo "<form id=\"mainform\" method=post action=\"latepasses.php?&cid=$cid\">";

?>
<div id="headerlatepasses" class="pagetitle"><h1>Manage LatePasses</h1></div>

<script type="text/javascript">
function onenter(e,field) {
	if (window.event) {
		var key = window.event.keyCode;
	} else if (e.which) {
		var key = e.which;
	}
	if (key==13) {
		var curtr = $(e.target).closest("tr");
		var to = curtr.next("tr:visible").find("input");
		if (to.length) {
			to[0].focus();
		}
        return false;
	} else {
		return true;
	}
}
function onarrow(e,field) {
	if (window.event) {
		var key = window.event.keyCode;
	} else if (e.which) {
		var key = e.which;
	}

	if (key==40 || key==38) {
		e.preventDefault();
		var curtr = $(e.target).closest("tr");
		var to;
		if (key == 38) {
			to = curtr.prev("tr:visible").find("input");
		} else {
			to = curtr.next("tr:visible").find("input");
		}
		if (to.length) {
			to[0].focus();
		}
		return false;
	} else {
		return true;
	}
}

function doonblur(value) {
	value = value.replace(/[^\d\.\+\-]/g,'');
	if (value=='') {return ('');}
	return (eval(value));
}

function sendtoall(type) {
	var tosend = parseInt(document.getElementById("toall").value);
	$("input[name^=latepass]:visible").each(function(i,el) {
		if (type == 0) {
			el.value = parseInt(el.value) + tosend;
		} else {
			el.value = tosend;
		}
	});
}
function updatesec(el) {
	var sel = el.value;
	if (sel == 'all') {
		$("tbody tr").show();
	} else {
		$("tbody tr").hide();
		$("tr."+sel).show();
	}
}
</script>
<?php

		$stm = $DBH->prepare("SELECT latepasshrs FROM imas_courses WHERE id=:id");
		$stm->execute(array(':id'=>$cid));
		$hours = $stm->fetchColumn(0);
		echo '<p>Students can redeem LatePasses for automatic extensions to assessments where allowed by the instructor. ';
		echo 'In each assessment\'s settings, an instructor can specify whether LatePasses are allowed, ';
		echo 'limit the number of passes allowed, limit whether they can be used after the due date, ';
		echo 'or specify a hard date after which LatePasses are not allowed.</p>';
		echo "<p><label>Late Passes extend the due date by <input type=text size=3 name=\"hours\" id=\"hours\" value=\"" . Sanitize::encodeStringForDisplay($hours) . "\"/> hours</label></p>";
		echo "<p><label>To all students: <input type=\"text\" size=\"3\" value=\"1\" id=\"toall\"/> latepasses</label> ";
		echo '<button type=button onClick="sendtoall(0);">Add</button> <button type=button onclick="sendtoall(1)">Replace</button><p>';
		echo "<table id=myTable class=gb><thead><tr><th>Name</th>";
		if ($hassections) {
			echo '<th>Section<br><select onchange="updatesec(this)">';
			echo '<option value="all">'._('All').'</option>';
			if ($hasemptysec) {
				echo '<option value="s-N">'._('None').'</option>';
			}
			foreach ($sections as $i=>$sn) {
				echo '<option value="s-'.intval($i).'">'.Sanitize::encodeStringForDisplay($sn).'</option>';
			}
			echo '</select></th>';
		}
		echo "<th>LatePasses Remaining</th></tr></thead><tbody>";
		foreach ($sturows as $i=>$row) {
			$i++;
			if (is_null($row['section'])) {
				$secid = 'N';
			} else {
				$secid = intval(array_search($row['section'], $sections));
			}
			echo "<tr class='s-".$secid."'>";
			echo "<td><span class='pii-full-name' id='n$i'>" . Sanitize::encodeStringForDisplay($row['LastName']) . ", " . Sanitize::encodeStringForDisplay($row['FirstName']) . "</span></td>";
			if ($hassections) {
				echo "<td>" . Sanitize::encodeStringForDisplay($row['section']) . "</td>";
			}

			echo "<td><input type=text size=3 name=\"latepass[" . Sanitize::encodeStringForDisplay($row['id']) . "]\" value=\"" . Sanitize::encodeStringForDisplay($row['latepass']) . "\"";
			echo " onkeypress=\"return onenter(event,this)\" onkeydown=\"onarrow(event,this)\" onblur=\"this.value = doonblur(this.value);\" aria-labelledby='n$i'/></td>";
			echo "</tr>";
		}

		echo "</tbody></table>";

		echo '<div class="submit"><input type="submit" value="'._('Save Changes').'"></div>';

?>

</form>

<?php
	require_once "../footer.php";
?>
