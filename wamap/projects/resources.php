<?php
@set_time_limit(0);
ini_set("max_input_time", "600");
ini_set("max_execution_time", "600");
ini_set("memory_limit", "104857600");
ini_set("upload_max_filesize", "10485760");
ini_set("post_max_size", "10485760");
date_default_timezone_set('America/Los_Angeles');
require("../../init.php");
require("../../includes/filehandler.php");
$placeinhead = "<script type=\"text/javascript\">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-30468975-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>";

$questions = array(
	"title"=>array(
		'req'=>1,
		'short'=>'Title',
		'long'=>'',
		'type'=>'input',
		'c'=>80
		),
	"descr"=>array(
		'req'=>1,
		'short'=>'Brief description',
		'long'=>'(2-3 sentences)',
		'type'=>'textarea',
		'r'=>2,
		'c'=>80
		),
	"cool"=>array(
		'req'=>0,
		'short'=>'What makes it great',
		'long'=>'What makes this resource unique or exceptional?',
		'type'=>'textarea',
		'r'=>2,
		'c'=>80
		),
	"level"=>array(
		'req'=>1,
		'short'=>'Level',
		'long'=>'(check all that apply).',
		'type'=>'checkbox',
		'arr'=>array(
			"P"=>"PreAlgebra",
			"B"=>"Beginning Algebra",
			"I"=>"Intermediate Algebra",
			"PC"=>"Pre-calculus 1",
			"TR"=>"Trig",
			"MS"=>"Math in Society",
			"S"=>"Statistics",
			"F"=>"Business Algebra",
			"ME"=>"Math for Educators",
			"C"=>"Calculus",
			"BC"=>"Business Calc",
			"M"=>"Multivariable Calc",
			"LA"=>"Linear Alegebra",
			"O"=>"Other"
			),
		'other'=>"otherlevel"
		),
	"type"=>array(
		'req'=>1,
		'short'=>'Type',
		'long'=>'What kind of thing is this?',
		'type'=>'radio',
		'arr'=>array(
			"TB"=>"Textbook",
			"RC"=>"Resource collection",
			"B"=>"Blog",
			"OW"=>"Other website",
			"OT"=>"Online software",
			"PT"=>"Downloaded software"
			)
		),
	'cost'=>array(
		'req'=>1,
		'short'=>'Cost',
		'long'=>'Is there a cost associated?  If free and you know, specify whether the resource is just free, or openly licensed.',
		'type'=>'select',
		'arr'=>array(
			"C"=>"Costs",
			"F"=>"Free",
			"FO"=>"Free and Open"
			)
		),
	'equip'=>array(
		'req'=>0,
		'short'=>'Equipment needed',
		'long'=>'Is there anything unusual needed to utilize this resource?',
		'type'=>'textarea',2,
		'c'=>80
		),
	'sugg'=>array(
		'req'=>0,
		'short'=>'Suggestions for implementation',
		'long'=>'(short - 150 words) How would you use this resource? If there are detailed instructions, feel free to attach them as a file below.',
		'type'=>'textarea',
		'r'=>3,
		'c'=>80
		),
	'detail'=>array(
		'req'=>0,
		'short'=>'More detailed instructions available?',
		'long'=>'',
		'type'=>'radio',
		'arr'=>array(
			"Y"=>"Yes",
			"N"=>"No"
			)
		),
	'dev'=>array(
		'req'=>1,
		'short'=>'Submitter',
		'long'=>'',
		'type'=>'input',
		'c'=>80
		),
	'college'=>array(
		'req'=>1,
		'short'=>'College',
		'long'=>'',
		'type'=>'input',
		'c'=>80
		),
	'contact'=>array(
		'req'=>0,
		'short'=>'Contact Info',
		'long'=>'(optional)',
		'type'=>'input',
		'c'=>80
		)

	);


if (isset($_GET['filterby'])) {
	//DB $_SESSION['pfilter-'.$_GET['filterby']] = stripslashes($_GET['filterval']);
	$_SESSION['pfilter-'.$_GET['filterby']] = $_GET['filterval'];
}

if (isset($_GET['modify'])) {
	if (isset($_POST['title'])) {
		//submitting
		$tosave = array();
		foreach ($questions as $key=>$arr) {
			if ($arr['type']=='input' || $arr['type']=='textarea' || $arr['type']=='radio' || $arr['type']=='select') {
				$tosave[$key] = $_POST[$key];
			} else if ($arr['type']=='checkbox') {
				if (isset($_POST[$key])) {
					$tosave[$key] = implode(',',$_POST[$key]);
				} else {
					$tosave[$key] = '';
				}
			} else if ($arr['type']=='selecttwowother') {
				$tosave[$key] = ';'.$_POST[$key.'-0'].';'.$_POST[$key.'-1'].';'.str_replace(';','',$_POST[$key.'-other']);
			}
			if (($arr['type']=='radio' || $arr['type']=='checkbox') && isset($arr['other'])) {
				$tosave[$arr['other']] = $_POST[$arr['other']];
			}
		}
		if ($_GET['modify']=='new') {
			$tosave['ownerid'] = $userid;
			$tosave['postedon'] = time();
			$tosave['lastmod'] = time();
			$keys = implode(',',array_keys($tosave));
			//DB $vals = "'".implode("','",array_values($tosave))."'";
			$phs = ':'.implode(',:', array_keys($tosave));
			//DB $query = "INSERT INTO resources ($keys) VALUES ($vals)";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			//DB $_GET['modify'] = mysql_insert_id();
			$stm = $DBH->prepare("INSERT INTO resources ($keys) VALUES ($phs)");
			$stm->execute($tosave);
			$_GET['modify'] = $DBH->lastInsertId();
			$files = array();
		} else {
			$sets = array();
			$qarr = array();
			foreach ($tosave as $k=>$v) {
				$sets[] = "$k=:$k";
				$qarr[":$k"] = $v;
			}
			$sets[] = 'lastmod='.time();
			$sets = implode(',',$sets);
			//DB $query = "UPDATE resources SET $sets WHERE id='{$_GET['modify']}'";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			$stm = $DBH->prepare("UPDATE resources SET $sets WHERE id=:id");
			$stm->execute($qarr + array(':id'=>$_GET['modify']));
			//DB $query = "SELECT files FROM resources WHERE id='{$_GET['modify']}'";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			//DB $files = mysql_result($result,0,0);
			$stm = $DBH->prepare("SELECT files FROM resources WHERE id=:id");
			$stm->execute(array(':id'=>$_GET['modify']));
			$files = $stm->fetchColumn(0);
			if ($files=='') {
				$files = array();
			} else {
				$files = explode('@@',$files);
			}
		}
		if (isset($_POST['filedesc'])) {
			foreach ($_POST['filedesc'] as $i=>$v) {
				//DB $files[2*$i] = stripslashes(str_replace('@@','@',$v));
				$files[2*$i] = str_replace('@@','@',$v);
			}
			for ($i=count($files)/2-1;$i>=0;$i--) {
				if (isset($_POST['filedel'][$i])) {
					if ($files[2*$i+1][0]=='#' || deletefilebykey('resources/'.$_GET['modify'].'/'.$files[2*$i+1])) {
						array_splice($files,2*$i,2);
					}
				}
			}
		}
		$i = 0;
		while (isset($_POST['newfiledesc-'.$i])) {
			if (isset($_POST['newweblink-'.$i]) && substr($_POST['newweblink-'.$i],0,4)=='http') {
				if (!isset($_FILES['newfile-'.$i]) || !is_uploaded_file($_FILES['newfile-'.$i]['tmp_name'])) {
					if (trim($_POST['newfiledesc-'.$i])=='') {
						$_POST['newfiledesc-'.$i] = $_POST['newweblink-'.$i];
					}
					//DB $files[] = stripslashes($_POST['newfiledesc-'.$i]);
					$files[] = $_POST['newfiledesc-'.$i];
					$files[] = '#'.$_POST['newweblink-'.$i];
				}
			}
			$i++;
		}

		if (isset($_FILES['newfile-0'])) {
			$i = 0;
			$badextensions = array(".php",".php3",".php4",".php5",".bat",".com",".pl",".p");
			while (isset($_POST['newfiledesc-'.$i])) {
				if (isset($_FILES['newfile-'.$i]) && is_uploaded_file($_FILES['newfile-'.$i]['tmp_name'])) {
					$userfilename = preg_replace('/[^\w\.]/','',basename($_FILES['newfile-'.$i]['name']));
					if (trim($_POST['newfiledesc-'.$i])=='') {
						$_POST['newfiledesc-'.$i] = $userfilename;
					}
					$_POST['newfiledesc-'.$i] = str_replace('@@','@',$_POST['newfiledesc-'.$i]);
					$extension = strtolower(strrchr($userfilename,"."));
					if (!in_array($extension,$badextensions) && storeuploadedfile('newfile-'.$i,'resources/'.$_GET['modify'].'/'.$userfilename,"public")) {
						//DB $files[] = stripslashes($_POST['newfiledesc-'.$i]);
						$files[] = $_POST['newfiledesc-'.$i];
						$files[] = $userfilename;
					}

				}
				$i++;
			}
		}
		//DB $files = addslashes(implode('@@',$files));
		$files = implode('@@',$files);
		//DB $query = "UPDATE resources SET files='$files' WHERE id='{$_GET['modify']}'";
		//DB mysql_query($query) or die("Query failed : $query " . mysql_error());
		$stm = $DBH->prepare("UPDATE resources SET files=:files WHERE id=:id");
		$stm->execute(array(':files'=>$files, ':id'=>$_GET['modify']));
        header('Location: ' . $GLOBALS['basesiteurl'] . "/wamap/projects/resources.php");
		exit;
	} else {
		//adding / modifying a task form
		if ($_GET['modify'] != 'new') {
			//DB $query = "SELECT * from resources WHERE id='{$_GET['modify']}'";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			//DB $line = mysql_fetch_array($result, MYSQL_ASSOC);
			$stm = $DBH->prepare("SELECT * from resources WHERE id=:id");
			$stm->execute(array(':id'=>$_GET['modify']));
			$line = $stm->fetch(PDO::FETCH_ASSOC);
		} else {
			//DB $query = "SELECT name FROM imas_groups WHERE id='$groupid'";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			//DB $college = mysql_result($result,0,0);
			$stm = $DBH->prepare("SELECT name FROM imas_groups WHERE id=:id");
			$stm->execute(array(':id'=>$groupid));
			$college = $stm->fetchColumn(0);
			//DB $query = "SELECT email FROM imas_users WHERE id='$userid'";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			//DB $email = mysql_result($result,0,0);
			$stm = $DBH->prepare("SELECT email FROM imas_users WHERE id=:id");
			$stm->execute(array(':id'=>$userid));
			$email = $stm->fetchColumn(0);
			$line = array('dev'=>$userfullname,'college'=>$college,'contact'=>$email);
		}
		$placeinhead .= '<script type="text/javascript" src="validate.js?v=2"></script>';
		$placeinhead .= '<link rel="stylesheet" href="tasks.css" type="text/css" />';
		require("../../header.php");
		echo '<div class="breadcrumb"><a href="resources.php">Resource List</a> &gt; Add/Modify Resource</div>';
		echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"resources.php?modify=".Sanitize::encodeUrlParam($_GET['modify'])."\" onsubmit=\"return validateForm(this);\">\n";
		foreach ($questions as $key=>$arr) {
			echo '<p>';
			echo $arr['short'].' <i>'.$arr['long'].'</i><br/>';
			if ($arr['type']=='input') {
				echo '<input type="text" name="'.Sanitize::encodeStringForDisplay($key).'" size="'.Sanitize::encodeStringForDisplay($arr['c']).'" value="'.Sanitize::encodeStringForDisplay($line[$key]).'" ';
				if ($arr['req']==1) { echo ' class="req" title="'.$arr['short'].'"';}
				echo '/>';
			} else if ($arr['type']=='textarea') {
				echo '<textarea name="'.$key.'" rows="'.$arr['r'].'" cols="'.$arr['c'].'" ';
				if ($arr['req']==1) { echo ' class="req" title="'.$arr['short'].'"';}
				echo '>'.Sanitize::encodeStringForDisplay($line[$key]).'</textarea>';
			} else if ($arr['type']=='radio') {
				foreach ($arr['arr'] as $k=>$v) {
					echo '<input type="radio" name="'.$key.'" value="'.Sanitize::encodeStringForDisplay($k).'" ';
					if ($k==$line[$key] || ((!isset($line[$key]) || $line[$key]=='') && $k==$arr['def'])) {echo 'checked="checked"';}
					if ($arr['req']==1) { echo ' class="req" title="'.$arr['short'].'"';}
					echo '/> '.$v;
					if (isset($arr['other']) && $v=='Other') {
						echo ', please specify: <input type="text" name="'.Sanitize::encodeStringForDisplay($arr['other']).'" value="'.Sanitize::encodeStringForDisplay($line[$arr['other']]).'" />';
					}
					echo '<br/>';
				}

			} else if ($arr['type']=='select') {
				echo '<select name="'.$key.'" ';
				if ($arr['req']==1) { echo ' class="req" title="'.$arr['short'].'"';}
				echo '>';
				foreach ($arr['arr'] as $k=>$v) {
					echo '<option value="'.$k.'" ';
					if ($k==$line[$key] || ((!isset($line[$key]) || $line[$key]=='') && $k==$arr['def'])) {echo 'selected="selected"';}

					echo '/> '.Sanitize::encodeStringForDisplay($v).'</option>';
				}
				echo '</select>';
			} else if ($arr['type']=='checkbox') {
				if ($line[$key]!='') {
					$line[$key] = explode(',',$line[$key]);
				} else {
					$line[$key] = array();
				}
				foreach ($arr['arr'] as $k=>$v) {
					echo '<input type="checkbox" name="'.$key.'[]" value="'.Sanitize::encodeStringForDisplay($k).'" ';
					if (in_array($k,$line[$key])) {echo 'checked="checked"';}
					echo '/> '.Sanitize::encodeStringForDisplay($v);
					if (isset($arr['other']) && $v=='Other') {
						echo ', please specify: <input type="text" name="'.Sanitize::encodeStringForDisplay($arr['other']).'" value="'.Sanitize::encodeStringForDisplay($line[$arr['other']]).'" />';
					}
					echo '<br/>';
				}

			} else if ($arr['type']=='selecttwowother') {
				if ($line[$key]!='') {
					if (strpos($line[$key],';')===false) {
						$line[$key] = array('','',$line[$key]);
					} else {
						$line[$key] = explode(';',substr($line[$key],1));
					}
				} else {
					$line[$key] = array('','','');
				}
				for ($c=0;$c<2;$c++) {
					if ($c==0) {echo 'Primary: ';} else {echo '<br/>Secondary: ';}
					echo '<select name="'.$key.'-'.$c.'"';
					if ($arr['req']==1 && $c==0) { echo ' class="req" title="'.$arr['short'].'"';}
					echo '>';
					echo '<option value="" ';
					if ($line[$key][$c]=='') {echo 'selected="selected"';}
					echo '>None Selected</option>';
					foreach ($arr['arr'] as $k=>$v) {
						echo '<option value="'.Sanitize::encodeStringForDisplay($k).'" ';
						if ($line[$key][$c]==$k) {echo 'selected="selected"';}
						echo '>'.Sanitize::encodeStringForDisplay($v).'</option>';
					}
					echo '</select> ';
				}
				echo '<br/>Other: <input type="text" name="'.Sanitize::encodeStringForDisplay($key).'-other" value="'.Sanitize::encodeStringForDisplay($line[$key][2]).'" size="40"/>';
				echo '<br/>';
			}
			echo '</p>';

		}

		echo "<p>Links or files: <i>When possible, please include editable (Word, TeX, etc.) versions of the files.</i><br/>";
		if ($line['files']!='') {
			$files = explode('@@',$line['files']);
			for ($i=0;$i<count($files)/2;$i++) {
				echo '<input type="text" name="filedesc['.$i.']" value="'.Sanitize::encodeStringForDisplay($files[2*$i]).'" size="40"/> ';
				if ($files[2*$i+1][0]!='#') {
					echo '<a href="'.getuserfileurl('resources/'.Sanitize::encodeStringForJavascript($_GET['modify']).'/'.Sanitize::encodeStringForJavascript($files[2*$i+1])).'" target="_blank">View</a> ';
				} else {
					echo '<a href="'.substr($files[2*$i+1],1).'" target="_blank">Open Web Link</a> ';
				}
				echo 'Delete? <input type="checkbox" name="filedel['.$i.']" value="1"/><br/>';
			}
		}
		echo 'Description: <input type="text" name="newfiledesc-0" size="40"/> ';
		echo 'Web link: <input type="input" name="newweblink-0" size="50"/> or ';
		echo 'File: <input type="file" name="newfile-0" /> <br/>';
		echo '<a href="#" onclick="addnewfile(this);return false;">Add another file/link</a></p>';
		echo '<p><input type="submit" value="Save"/></p>';
		echo '</form></body></html>';
		exit;
	}
} else if (isset($_GET['remove'])) {
	//DB $query = "SELECT ownerid,files FROM resources WHERE id='{$_GET['remove']}'";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	$stm = $DBH->prepare("SELECT ownerid,files FROM resources WHERE id=:id");
	$stm->execute(array(':id'=>$_GET['remove']));
	list($ownerid,$files) = $stm->fetch(PDO::FETCH_NUM);
	if ($ownerid==$userid || $myrights==100 || $userid==745) {
		//DB $files = mysql_result($result,0,1);
		if ($files != '') {
			for ($i=0;$i<count($files)/2;$i++) {
				if ($files[2*$i+1][0]!='#') {
					deletefilebykey('resources/'.$_GET['modify'].'/'.$files[2*$i+1]);
				}
			}
		}
		//DB $query = "DELETE FROM resources WHERE id='{$_GET['remove']}'";
		//DB mysql_query($query) or die("Query failed : $query " . mysql_error());
		$stm = $DBH->prepare("DELETE FROM resources WHERE id=:id");
		$stm->execute(array(':id'=>$_GET['remove']));
	}
	header('Location: ' . $GLOBALS['basesiteurl'] . "/wamap/projects/resources.php");
	exit;
} else if (isset($_GET['saverating']) && isset($_POST['rating'])) {
	$_POST['comments'] = preg_replace("/\n\n\n+/","\n\n",$_POST['comments']);
	$_POST['comments'] = strip_tags($_POST['comments']);
	$_POST['comments'] = str_replace("\n","<br/>",$_POST['comments']);
	//DB $query = "SELECT id FROM resources_ratings WHERE taskid='{$_POST['taskid']}' AND userid='$userid'";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	$stm = $DBH->prepare("SELECT id FROM resources_ratings WHERE taskid=:taskid AND userid=:userid");
	$stm->execute(array(':taskid'=>$_POST['taskid'], ':userid'=>$userid));
	$now = time();
	//DB if (mysql_num_rows($result)>0) {
		//DB $id = mysql_result($result,0,0);
	if ($stm->rowCount()>0) {
		$id = $stm->fetchColumn(0);
		//DB $query = "UPDATE resources_ratings SET rating='{$_POST['rating']}',comment='{$_POST['comments']}',rateon=$now WHERE id=$id";
		//DB mysql_query($query) or die("Query failed : $query " . mysql_error());
		$stm = $DBH->prepare("UPDATE resources_ratings SET rating=:rating,comment=:comment,rateon=:rateon WHERE id=:id");
		$stm->execute(array(':rating'=>$_POST['rating'], ':comment'=>$_POST['comments'], ':rateon'=>$now, ':id'=>$id));
	} else {//insert
		//DB $query = "INSERT INTO resources_ratings (rating,comment,rateon,userid,taskid) VALUES ";
		//DB $query .= "('{$_POST['rating']}','{$_POST['comments']}',$now,'$userid','{$_POST['taskid']}')";
		//DB mysql_query($query) or die("Query failed : $query " . mysql_error());
		$query = "INSERT INTO resources_ratings (rating,comment,rateon,userid,taskid) VALUES ";
		$query .= "(:rating, :comment, :rateon, :userid, :taskid)";
		$stm = $DBH->prepare($query);
		$stm->execute(array(':rating'=>$_POST['rating'], ':comment'=>$_POST['comments'], ':rateon'=>$now, ':userid'=>$userid, ':taskid'=>$_POST['taskid']));
	}
	echo getratingsfor(Sanitize::onlyInt($_POST['taskid']));

} else if (isset($_GET['id'])) {
	$placeinhead .= '<link rel="stylesheet" href="tasks.css" type="text/css" />';
	$placeinhead .= '<script type="text/javascript">
		var ratingssaveurl = "'. $GLOBALS['basesiteurl'] . '/wamap/projects/resources.php?saverating=true";
		</script>';
	$placeinhead .= '<script type="text/javascript" src="validate.js?v=2"></script>';
	require("../../header.php");
	echo '<div class="breadcrumb"><a href="resources.php">Resource List</a> &gt; View Resource</div>';
	echo '<div id="ratingholder">';
	echo getratingsfor(Sanitize::onlyInt($_GET['id']));
	echo '</div>';

	//DB $query = "SELECT resources.*,iu.LastName,iu.FirstName FROM resources JOIN imas_users AS iu ON resources.ownerid=iu.id WHERE resources.id='{$_GET['id']}'";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	//DB $line = mysql_fetch_array($result, MYSQL_ASSOC);
	$stm = $DBH->prepare("SELECT resources.*,iu.LastName,iu.FirstName FROM resources JOIN imas_users AS iu ON resources.ownerid=iu.id WHERE resources.id=:id");
	$stm->execute(array(':id'=>$_GET['id']));
	$line = $stm->fetch(PDO::FETCH_ASSOC);
	echo '<table class="gb"><tbody>';
	foreach ($questions as $key=>$arr) {
		$key = Sanitize::simpleString($key);
		if ((trim($line[$key])=='' || $line[$key]=='N') && !isset($arr['showalways'])) { continue;}
		echo '<tr><td class="r">'.$arr['short'].'</td><td>';
		if ($arr['type']=='input' || $arr['type']=='textarea') {
			echo Sanitize::encodeStringForDisplay($line[$key]);
		} else if ($arr['type']=='radio' || $arr['type']=='select') {
			echo $arr['arr'][$line[$key]];
		} else if ($arr['type']=='checkbox') {
			$line[$key] = explode(',',$line[$key]);
			$out = array();
			foreach ($line[$key] as $v) {
				$out[] = $arr['arr'][$v];
			}
			echo implode(', ',$out);
		} else if ($arr['type']=='selecttwowother') {
			if (strpos($line[$key],';')===false) {
				$line[$key] = array('','',$line[$key]);
			} else {
				$line[$key] = explode(';',substr($line[$key],1));
			}
			$out = array();
			for ($c=0;$c<2;$c++) {
				if ($line[$key][$c] != '') {
					$out[] = $arr['arr'][$line[$key][$c]];
				}
			}
			if ($line[$key][2]!='') {
				$out[] = $line[$key][2];
			}
			echo Sanitize::encodeStringForDisplay(implode('; ',$out));
		}
		if (($arr['type']=='radio' || $arr['type']=='checkbox') && isset($arr['other']) && $line[$arr['other']]!='') {
			echo ': '.Sanitize::encodeStringForDisplay($line[$arr['other']]);
		}
		echo '</td></tr>';
	}


	if ($line['files']!='') {
		$canpreview = array('doc','docx','xls','xlsx','html','ppt','pptx','pdf');
		echo '<tr><td class="r">Files:</td><td>';
		$fl = explode('@@',$line['files']);
		for ($i=0;$i<count($fl)/2;$i++) {
			//if (count($fl)>2) {echo '<li>';}
			if ($i>0) {echo '<br/>';}
			if ($fl[2*$i+1][0]!='#') {
				$url = getuserfileurl('resources/'.$line['id'].'/'.$fl[2*$i+1]);
			} else {
				$url = substr($fl[2*$i+1],1);
			}
			echo '<a href="'.Sanitize::encodeStringForDisplay($url).'" target="_blank">';

			/*if (isset($itemicons[$extension])) {
				echo "<img alt=\"$extension\" src=\"$imasroot/img/{$itemicons[$extension]}\" class=\"mida\"/> ";
			} else {
				echo "<img alt=\"doc\" src=\"$imasroot/img/doc.png\" class=\"mida\"/> ";
			}*/
			echo Sanitize::encodeStringForDisplay($fl[2*$i]).'</a>';
			if ($fl[2*$i+1][0]!='#') {
				$extension = ltrim(strtolower(strrchr($fl[2*$i+1],".")),'.');
				if (in_array($extension,$canpreview)) {
					echo ' <a style="font-size: 70%" href="#" onclick="GB_show(\'Preview\',\'http://docs.google.com/viewer?embedded=true&url='.Sanitize::encodeUrlParam($url).'\',700,780);return false;">Preview</a>';
				}
			}
			//if (count($fl)>2) {echo '</li>';}
		}
		echo '</td></tr>';
	}

	echo '<tr><td class="r">Posted</td><td>'.date("F j, Y, g:i a", $line['postedon']).' by '.Sanitize::encodeStringForDisplay($line['FirstName'].' '.$line['LastName']).'</td></tr>';
	echo '<tr><td class="r">Last Updated</td><td>'.date("F j, Y, g:i a", $line['lastmod']).'</td></tr>';
	echo '</tbody></table>';
	echo '</body></html>';
} else {
	$nologo = true;
	$address = $GLOBALS['basesiteurl'] . "/resources.php";

	$placeinhead .= '<script type="text/javascript">
		function chgfilter(el) {
			window.location.href = "'.$address.'?filterby="+el.id+"&filterval="+el.value;
		}
		</script>';
	$placeinhead .= '<link rel="stylesheet" href="tasks.css" type="text/css" />';
	$placeinhead .= '<script type="text/javascript" src="'.$imasroot.'/javascript/tablesorter.js"></script>';

	require("../../header.php");
	echo '<div class="cp">';
	foreach ($questions as $key=>$arr) {
		//generate search pulldowns
		if (isset($arr['searchby']) && $arr['searchby']==false) {continue;}
		if (!isset($_SESSION['pfilter-'.$key]) && isset($arr['searchdef'])) {
			$_SESSION['pfilter-'.$key] = $arr['searchdef'];
		}
		if ($arr['type'] == 'radio' || $arr['type'] == 'select' || $arr['type'] == 'checkbox' || $arr['type']=='selecttwowother') {
			echo '<span class="nowrap">'.$arr['short'] . ': ';
			echo '<select id="'.$key.'" onchange="chgfilter(this)">';
			echo '<option value="-1" ';
			if (!isset($_SESSION['pfilter-'.$key])) {echo 'selected="selected"';}
			echo '>All</option>';
			foreach ($arr['arr'] as $k=>$v) {
				echo '<option value="'.Sanitize::encodeStringForDisplay($k).'" ';
				if ($k == $_SESSION['pfilter-'.$key]) {
					echo 'selected="selected"';
				}
				echo '>'.Sanitize::encodeStringForDisplay($v).'</option>';
			}
			echo '</select></span>&nbsp; ';
		}
	}
	echo '</div>';
	echo '<p>';
	if ($myrights>10) {
		echo '<a href="resources.php?modify=new">Add a Resource</a> ';
	}
	echo '</p>';
	echo '<p>';
	echo '<table class="gb" id="myTable"><thead><tr>';
	echo '<th>Title</th><th>Type</th><th>Rating</th><th>Posted</th><th>Updated</th><th>Posted By</th><th></th>';
	echo '</tr></thead><tbody>';
	$query = "SELECT resources.*,iu.FirstName,iu.LastName,count(ar.rating) AS ratingcnt,avg(ar.rating) AS ratingavg FROM ";
	$query .= "resources LEFT JOIN resources_ratings AS ar ON ar.taskid=resources.id ";
	$query .= "JOIN imas_users AS iu ON resources.ownerid=iu.id WHERE 1 ";
	$qarr = array();
	foreach ($questions as $key=>$arr) {
		if (isset($arr['searchby']) && $arr['searchby']==false) {continue;}
		if (isset($_SESSION['pfilter-'.$key]) && $_SESSION['pfilter-'.$key]!='-1') {
			if ($arr['type'] == 'radio' || $arr['type'] == 'select' ) {
				//DB $query .= 'AND arithmetic.'. $key .'=\''.addslashes($_SESSION['pfilter-'.$key]).'\' ';
				$query .= 'AND resources.'. $key .'=? ';
				$qarr[] = $_SESSION['pfilter-'.$key];
			} else if ($arr['type'] == 'checkbox') {
				//DB $query .= 'AND arithmetic.'. $key .' LIKE \'%'.addslashes($_SESSION['pfilter-'.$key]).'%\' ';
				$query .= 'AND resources.'. $key .' LIKE ? ';
				$qarr[] = '%'.$_SESSION['pfilter-'.$key].'%';
			} else if ($arr['type'] == 'selecttwoother') {
				//DB $query .= 'AND arithmetic.'. $key .' LIKE \'%;'.addslashes($_SESSION['pfilter-'.$key]).';%\' ';
				$query .= 'AND resources.'. $key .' LIKE ? ';
				$qarr[] = '%;'.$_SESSION['pfilter-'.$key].';%';
			}
		}
	}
	$query .= "GROUP BY resources.id ORDER BY id DESC";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	$stm = $DBH->prepare($query);
	$stm->execute($qarr);
	$i = 0;
	$lines = array();
	$ratetimescnt = 0;
	$totcnt = 0;
	//DB while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	while ($line = $stm->fetch(PDO::FETCH_ASSOC)) {
		$lines[] = $line;
		if ($line['ratingcnt']>0) {
			$ratetimescnt += $line['ratingavg']*$line['ratingcnt'];
			$totcnt += $line['ratingcnt'];
		}
	}
	if ($totcnt>0) {
		$ratingavg = $ratetimescnt/$totcnt;
	}
	foreach ($lines as $line) {
		$line['type'] = $questions['type']['arr'][$line['type']];


		if ($line['ratingavg']==null) {$line['ratingavg'] = 0;}

		echo '<tr ';
		if ($i%2==0) { echo 'class="even"';} else {echo 'class="odd"';}
		echo '>';
		$i++;
		echo '<td><a href="resources.php?id='.Sanitize::encodeUrlParam($line['id']).'">'.Sanitize::encodeStringForDisplay($line['title']).'</a></td>';

		echo '<td>'.Sanitize::encodeStringForDisplay($line['type']).'</td>';
		if ($line['ratingcnt']>0) {
			$v = $line['ratingcnt']/($line['ratingcnt'] + 5);
			$baysian = round($line['ratingavg']*$v + $ratingavg*(1-$v) - 1/$line['ratingcnt'],3);
		} else {
			$baysian = 0;
		}
		echo '<td class="nowrap"><span class="inline-rating" order="sortby'.$baysian.'"><ul class="star-rating">';
		echo '<li class="current-rating" style="width:'.(round(20*$line['ratingavg'])).'%">Currently '.round($line['ratingavg'],1).'/5 stars</li>';
		echo '</ul></span> ('.Sanitize::encodeStringForDisplay($line['ratingcnt']).')</td>';
		echo '<td class="nowrap">'.date('n/j/y',$line['postedon']).'</td>';
		echo '<td class="nowrap">'.date('n/j/y',$line['lastmod']).'</td><td class="nowrap">';
		//echo $line['LastName']. ', '.$line['FirstName'].'</td><td>';
		$dev = explode(',',$line['dev']);
		echo Sanitize::encodeStringForDisplay($dev[0]);
		if (count($dev)>1) {
			echo ' et al.';
		}
		echo '</td><td>';
		if ($line['ownerid']==$userid || $myrights==100 || $userid==745) {
			echo '<span style="font-size: 70%" class="nowrap"><a href="resources.php?modify='.Sanitize::encodeUrlParam($line['id']).'">Modify</a> | ';
			echo '<a href="resources.php?remove='.Sanitize::encodeStringForDisplay($line['id']).'" onclick="return confirm(\'Are you SURE you want to delete this item?\');">Remove</a></span>';
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
	echo '<script type="text/javascript">
			initSortTable("myTable",Array("S","S","B","D","D","S",false),true);
		</script>';
	echo '</p>';
	echo '</body></html>';

}

function getratingsfor($id) {
	global $DBH,$userid;
	//DB $query = "SELECT tr.rating,tr.comment,tr.userid,iu.FirstName,iu.LastName,tr.rateon FROM resources_ratings AS tr JOIN imas_users AS iu ON tr.userid=iu.id ";
	//DB $query .= "WHERE tr.taskid='$id' ORDER BY tr.rateon DESC";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	$query = "SELECT tr.rating,tr.comment,tr.userid,iu.FirstName,iu.LastName,tr.rateon FROM resources_ratings AS tr JOIN imas_users AS iu ON tr.userid=iu.id ";
	$query .= "WHERE tr.taskid=:taskid ORDER BY tr.rateon DESC";
	$stm = $DBH->prepare($query);
	$stm->execute(array(':taskid'=>$id));
	$ratings = array();
	$i = 0;
	$myrating = -1;
	$totrat = 0;
	//DB while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
	while ($line = $stm->fetch(PDO::FETCH_ASSOC)) {
		if ($line['userid']==$userid) {$myrating = $i;}
		$ratings[$i] = array($line['rating'],$line['comment'],$line['FirstName'].' '.$line['LastName'],Sanitize::onlyInt($line['rateon']));
		$totrat += Sanitize::onlyInt($line['rating']);
		$i++;
	}

	$out = '<div class="arating">';
	if ($i>0) {
		$totrat /= $i;
		$out .= '<b>Average Rating:</b> <span class="inline-rating"><ul class="star-rating">';
		$out .= '<li class="current-rating" style="width:'.(round(20*$totrat)).'%">Currently '.round($totrat,1).'/5 stars</li>';
		$out .= '</ul></span> ('.$i.' rating'.(($i>1)?'s)':')');
	} else {
		$out .= 'No ratings yet';
	}
	$out .= '</div>';

	if ($myrating==-1) { //not yet rated
		$rating = 0;
		$comments = '';
	} else {
		$rating = $ratings[$myrating][0];
		$comments = $ratings[$myrating][1];
	}
	$out .= '<div id="ratingentry" class="arating">';
	if ($myrating==-1) {
		$out .= '<b>Rate this resource</b>: ';
	} else {
		$out .= '<b>Your rating</b>: ';
	}
	$out .= '<span class="inline-rating"><ul class="star-rating">
		<li id="current-rating" class="current-rating" style="width:'.(20*$rating).'%;">Currently '.Sanitize::onlyInt($rating).'/5 Stars.</li>
		<li><a href="#" title="1 star out of 5" class="one-star" onclick="return recordrating(1);">1</a></li>
		<li><a href="#" title="2 stars out of 5" class="two-stars" onclick="return recordrating(2);">2</a></li>
		<li><a href="#" title="3 stars out of 5" class="three-stars" onclick="return recordrating(3);">3</a></li>
		<li><a href="#" title="4 stars out of 5" class="four-stars" onclick="return recordrating(4);">4</a></li>
		<li><a href="#" title="5 stars out of 5" class="five-stars" onclick="return recordrating(5);">5</a></li>
		</ul></span>';
	$out .= '<input type="hidden" id="rating" name="rating" value="'.Sanitize::encodeStringForDisplay($rating).'"/>';
	$out .= '<input type="hidden" name="taskid" value="'.Sanitize::encodeStringForDisplay($id).'"/>';
	$out .= '<br/>Comments:<br/>';
	$out .= '<textarea rows="4" style="width:90%" name="comments">'.str_replace('<br/>',"\n",Sanitize::encodeStringForDisplay($comments)).'</textarea>';
	if ($myrating==-1) {
		$out .= '<br/><input type="button" value="Save Rating" onclick="saverating()"/>';
	} else {
		$out .= '<br/><i>Last updated '.time_elapsed_string($ratings[$myrating][3]).'</i>';
		$out .= '<br/><input type="button" value="Update Rating" onclick="saverating()"/>';
	}
	$out .= '<span id="ratingsavenotice"></span>';
	if (isset($_POST['rating'])) {
		$out .= ' <i style="color:red;">Rating Saved</i>';
	}
	$out .= '</div>';

	foreach ($ratings as $i=>$rating) {
		if ($i==$myrating) {continue;}
		$out .= '<div class="arating">';
		$out .= '<span class="inline-rating"><ul class="star-rating">';
		$out .= '<li class="current-rating" style="width:'.(20*$rating[0]).'%">Currently '.Sanitize::encodeStringForDisplay($rating[0]).'/5 stars</li>';
		$out .= '</ul></span>';
		if ($rating[1]!='') {
			$out .= '<br/>';
			if (strlen($rating[1])>200) {
				$out .= Sanitize::encodeStringForDisplay(substr($rating[1],0,140));
				$out .= '<span style="display:none;" id="hiddencomment'.$i.'">';
				$out .= Sanitize::encodeStringForDisplay(substr($rating[1],140));
				$out .= '</span>';
				$out .= ' <a href="#" onclick="commentshowhide(this,'.$i.');return false;">[more...]</a>';
			} else {
				$out .= Sanitize::encodeStringForDisplay($rating[1]);
			}
		}
		$out .= '<br/><i>'.Sanitize::encodeStringForDisplay($rating[2]).', '.time_elapsed_string($rating[3]).'</i>';
		$out .= '</div>';
	}
	return $out;
}

function time_elapsed_string($ptime) {
	//from http://www.zachstronaut.com/posts/2009/01/20/php-relative-date-time-string.html
    $etime = time() - $ptime;

    if ($etime < 1) {
        return 'a second ago';
    }

    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
                );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
        }
    }
}
