<?php
@set_time_limit(0);
ini_set("max_input_time", "600");
ini_set("max_execution_time", "600");
ini_set("memory_limit", "104857600");
ini_set("upload_max_filesize", "10485760");
ini_set("post_max_size", "10485760");
date_default_timezone_set('America/Los_Angeles');
if (isset($_GET['public'])) {
	require("../../init_without_validate.php");
	$ispublic = true;
} else {
	require("../../init.php");
	$ispublic = false;
}
require("../../includes/filehandler.php");
/*$placeinhead = "<script type=\"text/javascript\">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-30468975-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>";*/

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
	"purpose"=>array(
		'req'=>1,
		'short'=>'Purpose',
		'long'=>'What the intent of this resource?  Select at least a primary intent.  You can optionally select a secondary intent.',
		'type'=>'selecttwoother',
		'arr'=>array(
			"I"=>"General intro of topic",
			"CD"=>"Concept development",
			"GP"=>"Guided practice",
			"CA"=>"Contextual application",
			"IR"=>"Interactive reference (livescript, video)",
			"SR"=>"Static reference (book, handout)",
			"PP"=>"Practice problems",
			"WU"=>"Wrap up of topic"
			)
		),
	"topic"=>array(
		'req'=>1,
		'short'=>'Topic(s)',
		'long'=>'Select at least a primary topic.  You can optionally a secondary topic, or specify an unlisted topic.',
		'type'=>'selecttwoother',
		'hascat'=>true,
		'arr'=>array(
			'WN'=>'Whole Numbers',
			'WN-PV'=>'Place value, rounding, comparing whole nums',
			'WN-ADD'=>'Adding and subtracting whole numbers',
			'WN-MULT'=>'Multiplying whole numbers',
			'WN-DIV'=>'Division whole numbers',
			'WN-EXP'=>'Exponents and roots whole numbers',
			'WN-ORDOP'=>'Order of operations whole numbers',
			'FR'=>'Fractions',
			'FR-OV'=>'Intro to fractions and mixed numbers',
			'FR-SIMP'=>'Simplifying fractions',
			'FR-MULT'=>'Multiplying fractions',
			'FR-DIV'=>'Dividing fractions',
			'FR-ADD'=>'Add / Subt fractions with like denom',
			'FR-LCM'=>'Least common multiple',
			'FR-ADDUL'=>'Add / Subt with unlike denom',
			'FR-ORDOP'=>'Order of Ops with fractions',
			'DEC'=>'Decimals',
			'DEC-OV'=>'Intro to decimals. Rounding. Ordering.',
			'DEC-CONV'=>'Converting decimals to/from fractions',
			'DEC-ADD'=>'Adding and subtracting decimals',
			'DEC-MULT'=>'Multiplying decimals',
			'DEC-DIV'=>'Dividing decimals',
			'PER'=>'Percents',
			'PER-OV'=>'Intro to percents',
			'PER-SOLV'=>'Solving percent problems',
			'PER-APP'=>'Percent applications',
			'PRO'=>'Rates, ratios, proportions',
			'PRO-RAT'=>'Ratios and rates',
			'PRO-PRO'=>'Proportions',
			'MEA'=>'Measurement',
			'MEA-VOL'=>'Units of measure for length, area, volume',
			'MEA-US'=>'Converting units - US customary',
			'MEA-MET'=>'Converting units - metric',
			'MEA-RAT'=>'Converting rates',
			'GEO'=>'Geometry',
			'GEO-PER'=>'Perimeter',
			'GEO-AR'=>'Area',
			'GEO-VOL'=>'Volume',
			'GEO-PRO'=>'Proportional geometry',
			'STAT'=>'Statistics',
			'STAT-MMM'=>'Mean, median, mode',
			'STAT-GR'=>'Bar and line graphs',
			'STAT-PIE'=>'Pie charts',
			'INT'=>'Integers',
			'INT-OV'=>'Signed numbers',
			'INT-ADD'=>'Add / Subtract signed numbers',
			'INT-MULT'=>'Multiply / Divide signed numbers',
			'ALG'=>'Beginnings of algebra',
			'ALG-VAR'=>'Variables and evaluating formulas',
			'ALG-SIMP'=>'Simplifying expressions',
			'ALG-EQN'=>'Writing equations / pattern building',
			'ALG-1ST'=>'Solving one-step equations with whole numbers',
			'ALG-2ST'=>'Solving two-step equations with whole numbers',
			'ALG-NST'=>'Solving equations requiring simplification',
			'ALG-FRA'=>'Solving equations with fractions',
			'ALG-DEC'=>'Solving equations with decimals',
			'ALG-INT'=>'Solving equations with signed numbers',
			'GR'=>'Graphing',
			'GR-PL'=>'The coordinate plane',
			'GR-EQN'=>'Plotting points to graph an equation'
			)
		),
	"type"=>array(
		'req'=>1,
		'short'=>'Type of activity',
		'long'=>'Is this something for the instructor to use with the class, or students to work on individually, or for students to work on in groups',
		'type'=>'select',
		'arr'=>array(
			"T"=>"Instructor led",
			"I"=>"Student individual work",
			"G"=>"Student group work",
			"E"=>"Student, either individual or group"
			),
		'def'=>'E'
		),
	"whereused"=>array(
		'req'=>1,
		'short'=>'Where work is done',
		'long'=>'',
		'type'=>'select',
		'arr'=>array(
			"I"=>"In class",
			"H"=>"Homework",
			"B"=>"Both in and out of class",
			"E"=>"Either in and out of class",
			"O"=>"Online"
			),
		'def'=>'I'
		),
	"time"=>array(
		'req'=>1,
		'short'=>"Time required",
		'long'=>'',
		'type'=>'select',
		'arr'=>array(
			"Z"=>"0 to 10 minutes",
			"T"=>"10 to 30 minutes",
			"S"=>"30 to 60 minutes",
			"L"=>"Longer"
			),
		'def'=>'T'
		),
	'tech'=>array(
		'req'=>1,
		'short'=>'Technology needed',
		'long'=>'(check all that apply)',
		'type'=>'checkbox',
		'arr'=>array(
			'NN'=>'None needed (i.e. technology optional)',
			'NA'=>'None allowed',
			'SC'=>'Simple four-function calculator',
			'GC'=>'Graphing calculator',
			'I'=>'Internet access',
			'O'=>'Other'
			),
		'other'=>'othertech'
		),
	'equip'=>array(
		'req'=>0,
		'short'=>'Equipment needed',
		'long'=>'Please tell us anything outside of the ordinary that students must bring or that you supply,  e.g.  manipulatives, geoboards, large sticky graph paper, transparencies and pens, dots, M & M\'s... (leave blank if no equipment needed)',
		'type'=>'textarea',2,
		'c'=>80
		),
	'sugg'=>array(
		'req'=>0,
		'short'=>'Suggestions for implementation',
		'long'=>'(short - 150 words) How you introduce students to the task is often as important as the specifics of the task itself.  For example do you scaffold it or not?  Do you allow individuals time to get going before you invite folks to share ideas or not?  Do you encourage multiple methods of solution or not?  If your activity is managed in a very specific manner and this space doesn\'t allow you to fully explain your method, please feel free to put further detailed directions in a document and include it in the materials you provide...  See below.',
		'type'=>'textarea',
		'r'=>3,
		'c'=>80
		),
	'dev'=>array(
		'req'=>1,
		'short'=>'Task developer(s)',
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
		),
	'credit'=>array(
		'req'=>0,
		'short'=>'Credit',
		'long'=>'If this task is based on an original idea of someone else, please acknowledge that source here and on the task document itself.',
		'type'=>'input',
		'c'=>60
		),
	'license'=>array(
		'req'=>1,
		'short'=>'License',
		'long'=>'A copyright license means "all rights reserved", so people need to get your permission before using or modifying your work.  An open <a href="http://creativecommons.org/licenses/by/3.0/" target="_blank">Creative Commons Attribution (CC-BY)</a> license is a "some rights reserved" license, which says that people can use, modify, and redistribute the materials as long as they give credit to the original author.',
		'type'=>'select',
		'arr'=>array(
			"C"=>"Copyright",
			"CC"=>"Open (CC-BY)"
			),
		'def'=>'CC'
		)
	);



if (isset($_GET['sql'])) {
	$sql = "CREATE TABLE arithmetic ) "."\n";
	$sql .= 'id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, '."\n";
	$sql .= 'ownerid INT(10) UNSIGNED NOT NULL, '."\n";
	$sql .= 'postedon INT(10) UNSIGNED NOT NULL, '."\n";
	$sql .= 'lastmod INT(10) UNSIGNED NOT NULL, '."\n";
	foreach ($questions as $key=>$arr) {
		if ($arr['type']=='textarea') {
			$sql .= $key .' TEXT NOT NULL DEFAULT \'\','."\n";
		} else {
			$sql .= $key .' VARCHAR(255) NOT NULL DEFAULT \'\','."\n";
		}
		if (isset($arr['other'])) {
			$sql .= $arr['other'] .' TEXT NOT NULL DEFAULT \'\','."\n";
		}
	}
	$sql .= 'files TEXT NOT NULL DEFAULT \'\')'."\n";
	echo $sql;
	exit;

	/*

CREATE TABLE IF NOT EXISTS `arithmetic_ratings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `taskid` int(10) unsigned NOT NULL,
  `rating` tinyint(1) unsigned NOT NULL default '0',
  `comment` text NOT NULL,
  `rateon` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `taskid` (`taskid`),
  KEY `userid` (`userid`)
)*/
}

if (isset($_GET['filterby'])) {
	//DB $_SESSION['pfilter-'.$_GET['filterby']] = stripslashes($_GET['filterval']);
	$_SESSION['pfilter-'.$_GET['filterby']] = $_GET['filterval'];
}

if (isset($_GET['modify']) && !$ispublic) {
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
			} else if ($arr['type']=='selecttwoother') {
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
			$keys = implode(',', array_keys($tosave)); //safe values, provided by us
			//DB $vals = "'".implode("','",array_values($tosave))."'";
			$phs = ':'.implode(',:', array_keys($tosave));
			//DB $query = "INSERT INTO arithmetic ($keys) VALUES ($vals)";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			//DB $_GET['modify'] = mysql_insert_id();
			$stm = $DBH->prepare("INSERT INTO arithmetic ($keys) VALUES ($phs)");
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
			//DB $query = "UPDATE arithmetic SET $sets WHERE id='{$_GET['modify']}'";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			$stm = $DBH->prepare("UPDATE arithmetic SET $sets WHERE id=:id");
			$stm->execute($qarr + array(':id'=>$_GET['modify']));
			//DB $query = "SELECT files FROM arithmetic WHERE id='{$_GET['modify']}'";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			//DB $files = mysql_result($result,0,0);
			$stm = $DBH->prepare("SELECT files FROM arithmetic WHERE id=:id");
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
					if ($files[2*$i+1][0]=='#' || deletefilebykey('arithmetic/'.$_GET['modify'].'/'.$files[2*$i+1])) {
						array_splice($files,2*$i,2);
					}
				}
			}
		}
		$i = 0;
		while (isset($_POST['newfiledesc-'.$i])) {
			if (isset($_POST['newweblink-'.$i]) && substr($_POST['newweblink-'.$i],0,4)=='http') {
				if (!isset($_FILES['newfile-'.$i]) || !is_uploaded_file($_FILES['newfile-'.$i]['tmp_name'])) {
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
					if (!in_array($extension,$badextensions) && storeuploadedfile('newfile-'.$i,'arithmetic/'.$_GET['modify'].'/'.$userfilename,"public")) {
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
		//DB $query = "UPDATE arithmetic SET files='$files' WHERE id='{$_GET['modify']}'";
		//DB mysql_query($query) or die("Query failed : $query " . mysql_error());
		$stm = $DBH->prepare("UPDATE arithmetic SET files=:files WHERE id=:id");
		$stm->execute(array(':files'=>$files, ':id'=>$_GET['modify']));
		header('Location: ' . $GLOBALS['basesiteurl'] . "/wamap/projects/arithmetic.php");
		exit;
	} else {
		//adding / modifying a task form
		if ($_GET['modify'] != 'new') {
			//DB $query = "SELECT * from arithmetic WHERE id='{$_GET['modify']}'";
			//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
			//DB $line = mysql_fetch_array($result, MYSQL_ASSOC);
			$stm = $DBH->prepare("SELECT * from arithmetic WHERE id=:id");
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
		echo '<div class="breadcrumb"><a href="arithmetic.php">Task List</a> &gt; Add/Modify Task</div>';
		echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"arithmetic.php?modify=" . Sanitize::encodeStringForDisplay($_GET['modify']) . "\" onsubmit=\"return validateForm(this);\">\n";
		foreach ($questions as $key=>$arr) {
			echo '<p>';
			echo $arr['short'].' <i>'.$arr['long'].'</i><br/>';
			if ($arr['type']=='input') {
				echo '<input type="text" name="' . Sanitize::encodeStringForDisplay($key) . '" size="' . Sanitize::encodeStringForDisplay($arr['c']) . '" value="' . Sanitize::encodeStringForDisplay($line[$key]) . '" ';
				if ($arr['req']==1) { echo ' class="req" title="'.$arr['short'].'"';}
				echo '/>';
			} else if ($arr['type']=='textarea') {
				echo '<textarea name="'.$key.'" rows="'.$arr['r'].'" cols="'.$arr['c'].'" ';
				if ($arr['req']==1) { echo ' class="req" title="'.$arr['short'].'"';}
				echo '>' . Sanitize::encodeStringForDisplay($line[$key]) . '</textarea>';
			} else if ($arr['type']=='radio') {
				foreach ($arr['arr'] as $k=>$v) {
					echo '<input type="radio" name="'.$key.'" value="'.$k.'" ';
					if ($k==$line[$key] || ((!isset($line[$key]) || $line[$key]=='') && $k==$arr['def'])) {echo 'checked="checked"';}
					if ($arr['req']==1) { echo ' class="req" title="'.$arr['short'].'"';}
					echo '/> '.$v;
					if (isset($arr['other']) && $v=='Other') {
						echo ', please specify: <input type="text" name="' . Sanitize::encodeStringForDisplay($arr['other']) . '" value="' . Sanitize::encodeStringForDisplay($line[$arr['other']]) . '" />';
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

					echo '/> '.$v.'</option>';
				}
				echo '</select>';
			} else if ($arr['type']=='checkbox') {
				if ($line[$key]!='') {
					$line[$key] = explode(',',$line[$key]);
				} else {
					$line[$key] = array();
				}
				foreach ($arr['arr'] as $k=>$v) {
					echo '<input type="checkbox" name="'.$key.'[]" value="'.$k.'" ';
					if (in_array($k,$line[$key])) {echo 'checked="checked"';}
					echo '/> '.$v;
					if (isset($arr['other']) && $v=='Other') {
						echo ', please specify: <input type="text" name="' . $arr['other'] . '" value="' . Sanitize::encodeStringForDisplay($line[$arr['other']]) . '" />';
					}
					echo '<br/>';
				}

			} else if ($arr['type']=='selecttwoother') {
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
					$firstgroup = true;
					foreach ($arr['arr'] as $k=>$v) {
						if (isset($arr['hascat']) && $arr['hascat']==true && strpos($k,'-')===false) {
							if (!$firstgroup) { echo '</optgroup>';}
							echo '<optgroup label="'.$v.'">';
						} else {
							echo '<option value="'.$k.'" ';
							if ($line[$key][$c]==$k) {echo 'selected="selected"';}
							echo '>'.$v.'</option>';
						}
					}
					if (!$firstgroup) { echo '</optgroup>';}
					echo '</select> ';
				}
				echo '<br/>Other: <input type="text" name="' . Sanitize::encodeStringForDisplay($key) . '-other" value="' . Sanitize::encodeStringForDisplay($line[$key][2]) . '" size="40" />';
				echo '<br/>';
			}
			echo '</p>';

		}

		echo "<p>Files: <i>When possible, please include editable (Word, TeX, etc.) versions of the files.</i><br/>";
		if ($line['files']!='') {
			$files = explode('@@',$line['files']);
			for ($i=0;$i<count($files)/2;$i++) {
				echo '<input type="text" name="filedesc['.$i.']" value="'.$files[2*$i].'" size="40"/> ';
				if ($files[2*$i+1][0]!='#') {
					echo '<a href="' . getuserfileurl('arithmetic/' . Sanitize::encodeStringForDisplay($_GET['modify']) . '/' . $files[2*$i+1]) . '" target="_blank">View</a> ';
				} else {
					echo '<a href="' . substr($files[2*$i+1],1) . '" target="_blank">Open Web Link</a> ';
				}
				echo 'Delete? <input type="checkbox" name="filedel['.$i.']" value="1"/><br/>';
			}
		}
		echo 'Description: <input type="text" name="newfiledesc-0" size="40"/> ';
		echo 'File: <input type="file" name="newfile-0" /> or Web link: <input type="input" name="newweblink-0" /><br/>';
		echo '<a href="#" onclick="addnewfile(this);return false;">Add another file</a></p>';
		echo '<p><input type="submit" value="Save"/></p>';
		echo '</form></body></html>';
		exit;
	}
} else if (isset($_GET['summary'])) {
	//DB $query = "SELECT topic,purpose FROM arithmetic WHERE 1";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	$stm = $DBH->query("SELECT topic,purpose FROM arithmetic WHERE 1");
	$cnts = array();
	//DB while ($row = mysql_fetch_row($result)) {
	while ($row = $stm->fetch(PDO::FETCH_NUM)) {
		$tp = explode(';',$row[0]);
		if (count($tp)<3) {continue;}
		for ($i=1;$i<3;$i++) {
			if (!isset($cnts[$tp[$i]])) {
				$cnts[$tp[$i]] = array();
			}
		}
		$pp = explode(';',$row[1]);
		if (count($pp)<3) {continue;}
		for ($i=1;$i<3;$i++) {
			for ($j=1;$j<3;$j++) {
				if (!isset($cnts[$tp[$i]][$pp[$j]])) {
					$cnts[$tp[$i]][$pp[$j]] = 1;
				} else {
					$cnts[$tp[$i]][$pp[$j]]++;
				}
			}
		}
	}
	$placeinhead .= '<script type="text/javascript" src="validate.js?v=2"></script>';
	$placeinhead .= '<link rel="stylesheet" href="tasks.css" type="text/css" />';
	require("../../header.php");
	echo '<div class="breadcrumb"><a href="arithmetic.php'.($ispublic?'?public=true':'').'">Task List</a> &gt; Topic coverage summary</div>';
	echo '<p>Topic Coverage Summary</p>';

	echo '<table class="gb"><thead><tr><th>Topic</th>';
	foreach ($questions['purpose']['arr'] as $k=>$v) {
		echo '<th>'.$v.'</th>';
	}
	echo '</tr></thead><tbody>';
	foreach ($questions['topic']['arr'] as $tk=>$tv) {
		if (strpos($tk,'-')===false) {
		   echo '<tr><td colspan="'.(count($questions['purpose']['arr'])+1).'"><b>'.$tv.'</b></td></tr>';

		} else {
			echo '<tr><td>'.$tv.'</td>';
			foreach ($questions['purpose']['arr'] as $k=>$v) {
				echo '<td>';
				if (isset($cnts[$tk]) && isset($cnts[$tk][$k])) {
					echo $cnts[$tk][$k];
				} else {
					echo '-';
				}
				echo '</td>';
			}
			echo '</tr>';
		}
	}
	echo '</tbody>';
	echo '</table>';
	echo '</body></html>';
	exit;
} else if (isset($_GET['remove']) && !$ispublic) {
	//DB $query = "SELECT ownerid,files FROM arithmetic WHERE id='{$_GET['remove']}'";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	$stm = $DBH->prepare("SELECT ownerid,files FROM arithmetic WHERE id=:id");
	$stm->execute(array(':id'=>$_GET['remove']));
	list($ownerid,$files) = $stm->fetch(PDO::FETCH_NUM);
	if ($ownerid==$userid || $myrights==100 || $userid==745) {
		//DB $files = mysql_result($result,0,1);
		if ($files != '') {
			$files = explode('@@',$files);
			for ($i=0;$i<count($files)/2;$i++) {
				if ($files[2*$i+1][0]!='#') {
					deletefilebykey('arithmetic/'.$_GET['modify'].'/'.$files[2*$i+1]);
				}
			}
		}
		//DB $query = "DELETE FROM arithmetic WHERE id='{$_GET['remove']}'";
		//DB mysql_query($query) or die("Query failed : $query " . mysql_error());
		$stm = $DBH->prepare("DELETE FROM arithmetic WHERE id=:id");
		$stm->execute(array(':id'=>$_GET['remove']));
	}
	header('Location: ' . $GLOBALS['basesiteurl'] . "/wamap/projects/arithmetic.php");
	exit;
} else if (isset($_GET['saverating']) && isset($_POST['rating']) && !$ispublic) {
	$_POST['comments'] = preg_replace("/\n\n\n+/","\n\n",$_POST['comments']);
	$_POST['comments'] = strip_tags($_POST['comments']);
	$_POST['comments'] = str_replace("\n","<br/>",$_POST['comments']);
	//DB $query = "SELECT id FROM arithmetic_ratings WHERE taskid='{$_POST['taskid']}' AND userid='$userid'";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	$stm = $DBH->prepare("SELECT id FROM arithmetic_ratings WHERE taskid=:taskid AND userid=:userid");
	$stm->execute(array(':taskid'=>$_POST['taskid'], ':userid'=>$userid));
	$now = time();
	//DB if (mysql_num_rows($result)>0) {
		//DB $id = mysql_result($result,0,0);
	if ($stm->rowCount()>0) {
		$id = $stm->fetchColumn(0);
		//DB $query = "UPDATE arithmetic_ratings SET rating='{$_POST['rating']}',comment='{$_POST['comments']}',rateon=$now WHERE id=$id";
		//DB mysql_query($query) or die("Query failed : $query " . mysql_error());
		$stm = $DBH->prepare("UPDATE arithmetic_ratings SET rating=:rating,comment=:comment,rateon=:rateon WHERE id=:id");
		$stm->execute(array(':rating'=>$_POST['rating'], ':comment'=>$_POST['comments'], ':rateon'=>$now, ':id'=>$id));
	} else {//insert
		//DB $query = "INSERT INTO arithmetic_ratings (rating,comment,rateon,userid,taskid) VALUES ";
		//DB $query .= "('{$_POST['rating']}','{$_POST['comments']}',$now,'$userid','{$_POST['taskid']}')";
		//DB mysql_query($query) or die("Query failed : $query " . mysql_error());
		$query = "INSERT INTO arithmetic_ratings (rating,comment,rateon,userid,taskid) VALUES ";
		$query .= "(:rating, :comment, :rateon, :userid, :taskid)";
		$stm = $DBH->prepare($query);
		$stm->execute(array(':rating'=>$_POST['rating'], ':comment'=>$_POST['comments'], ':rateon'=>$now, ':userid'=>$userid, ':taskid'=>$_POST['taskid']));
	}

    echo getratingsfor(Sanitize::onlyInt($_POST['taskid']));

} else if (isset($_GET['id'])) {
	$placeinhead .= '<link rel="stylesheet" href="tasks.css" type="text/css" />';
	$placeinhead .= '<script type="text/javascript">
		var ratingssaveurl = "'. $GLOBALS['basesiteurl'] . '/wamap/projects/arithmetic.php?saverating=true";
		</script>';
	$placeinhead .= '<script type="text/javascript" src="validate.js?v=2"></script>';
	require("../../header.php");
	echo '<div class="breadcrumb"><a href="arithmetic.php'.($ispublic?'?public=true':'').'">Task List</a> &gt; View Task</div>';
	echo '<div id="ratingholder">';
	echo getratingsfor(Sanitize::onlyInt($_GET['id']));
	echo '</div>';

	//DB $query = "SELECT arithmetic.*,iu.LastName,iu.FirstName FROM arithmetic JOIN imas_users AS iu ON arithmetic.ownerid=iu.id WHERE arithmetic.id='{$_GET['id']}'";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	//DB $line = mysql_fetch_array($result, MYSQL_ASSOC);
	$stm = $DBH->prepare("SELECT arithmetic.*,iu.LastName,iu.FirstName FROM arithmetic JOIN imas_users AS iu ON arithmetic.ownerid=iu.id WHERE arithmetic.id=:id");
	$stm->execute(array(':id'=>$_GET['id']));
	$line = $stm->fetch(PDO::FETCH_ASSOC);
	echo '<table class="gb"><tbody>';
	foreach ($questions as $key=>$arr) {
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
		} else if ($arr['type']=='selecttwoother') {
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
			echo ': ' . Sanitize::encodeStringForDisplay($line[$arr['other']]);
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
				$url = getuserfileurl('arithmetic/'.$line['id'].'/'.$fl[2*$i+1]);
			} else {
				$url = substr($fl[2*$i+1],1);
			}
			echo '<a href="' . Sanitize::url($url) . '" target="_blank">';

			/*if (isset($itemicons[$extension])) {
				echo "<img alt=\"$extension\" src=\"$imasroot/img/{$itemicons[$extension]}\" class=\"mida\"/> ";
			} else {
				echo "<img alt=\"doc\" src=\"$imasroot/img/doc.png\" class=\"mida\"/> ";
			}*/
			echo $fl[2*$i].'</a>';
			if ($fl[2*$i+1][0]!='#') {
				$extension = ltrim(strtolower(strrchr($fl[2*$i+1],".")),'.');
				if (in_array($extension,$canpreview)) {
					echo ' <a style="font-size: 70%" href="#" onclick="GB_show(\'Preview\',\'http://docs.google.com/viewer?embedded=true&url='.urlencode($url).'\',700,780);return false;">Preview</a>';
				}
			}
			//if (count($fl)>2) {echo '</li>';}
		}
		echo '</td></tr>';
	}

	echo '<tr><td class="r">Posted</td><td>'.date("F j, Y, g:i a", $line['postedon']).' by ' . Sanitize::encodeStringForDisplay($line['FirstName']) . ' ' . Sanitize::encodeStringForDisplay($line['LastName']) . '</td></tr>';
	echo '<tr><td class="r">Last Updated</td><td>'.date("F j, Y, g:i a", $line['lastmod']).'</td></tr>';
	echo '</tbody></table>';
	echo '</body></html>';
} else {
	$nologo = true;
	$address = $GLOBALS['basesiteurl'] . "/wamap/projects/arithmetic.php".($ispublic?'?public=true':'?pb=true');

	$placeinhead .= '<script type="text/javascript">
		function chgfilter(el) {
			window.location.href = "'.$address.'&filterby="+el.id+"&filterval="+el.value;
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
		if ($arr['type'] == 'radio' || $arr['type'] == 'select' || $arr['type'] == 'checkbox' || $arr['type']=='selecttwoother') {
			echo '<span class="nowrap">'.$arr['short'] . ': ';
			echo '<select id="'.$key.'" onchange="chgfilter(this)">';
			echo '<option value="-1" ';
			if (!isset($_SESSION['pfilter-'.$key])) {echo 'selected="selected"';}
			echo '>All</option>';
			$firstgroup = true;
			foreach ($arr['arr'] as $k=>$v) {
				if (isset($arr['hascat']) && $arr['hascat']==true && strpos($k,'-')===false) {
					if (!$firstgroup) { echo '</optgroup>';}
					echo '<optgroup label="'.$v.'">';
				} else {
					echo '<option value="'.$k.'" ';
					if ($k == $_SESSION['pfilter-'.$key]) {
						echo 'selected="selected"';
					}
					echo '>'.$v.'</option>';
				}
			}
			if (!$firstgroup) { echo '</optgroup>';}
			echo '</select></span>&nbsp; ';

		}
	}
	echo '</div>';
	echo '<p>';
	if ($myrights>10 && !$ispublic) {
		echo '<a href="arithmetic.php?modify=new">Add a Resource</a> | ';
	}
	echo '<a href="arithmetic.php?summary=true'.($ispublic?'&amp;public=true':'').'">Show coverage summary</a></p>';
	echo '<p>';
	echo '<table class="gb" id="myTable"><thead><tr>';
	echo '<th>Title</th><th>Topic</th><th>Rating</th><th>Posted</th><th>Updated</th><th>Developer</th><th></th>';
	echo '</tr></thead><tbody>';
	$query = "SELECT arithmetic.*,iu.FirstName,iu.LastName,count(ar.rating) AS ratingcnt,avg(ar.rating) AS ratingavg FROM ";
	$query .= "arithmetic LEFT JOIN arithmetic_ratings AS ar ON ar.taskid=arithmetic.id ";
	$query .= "JOIN imas_users AS iu ON arithmetic.ownerid=iu.id WHERE 1 ";
	$qarr = array();
	foreach ($questions as $key=>$arr) {
		if (isset($arr['searchby']) && $arr['searchby']==false) {continue;}
		if (isset($_SESSION['pfilter-'.$key]) && $_SESSION['pfilter-'.$key]!='-1') {
			if ($arr['type'] == 'radio' || $arr['type'] == 'select' ) {
				//DB $query .= 'AND arithmetic.'. $key .'=\''.addslashes($_SESSION['pfilter-'.$key]).'\' ';
				$query .= 'AND arithmetic.'. $key .'=? ';
				$qarr[] = $_SESSION['pfilter-'.$key];
			} else if ($arr['type'] == 'checkbox') {
				//DB $query .= 'AND arithmetic.'. $key .' LIKE \'%'.addslashes($_SESSION['pfilter-'.$key]).'%\' ';
				$query .= 'AND arithmetic.'. $key .' LIKE ? ';
				$qarr[] = '%'.$_SESSION['pfilter-'.$key].'%';
			} else if ($arr['type'] == 'selecttwoother') {
				//DB $query .= 'AND arithmetic.'. $key .' LIKE \'%;'.addslashes($_SESSION['pfilter-'.$key]).';%\' ';
				$query .= 'AND arithmetic.'. $key .' LIKE ? ';
				$qarr[] = '%;'.$_SESSION['pfilter-'.$key].';%';
			}
		}
	}
	$query .= "GROUP BY arithmetic.id ORDER BY id DESC";
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
		if (strpos($line['topic'],';')===false) {
			$line['topic'] = array('','',$line['topic']);
		} else {
			$line['topic'] = explode(';',substr($line['topic'],1));
		}
		$out = array();
		for ($c=0;$c<2;$c++) {
			if ($line['topic'][$c] != '') {
				$out[] = $questions['topic']['arr'][$line['topic'][$c]];
			}
		}
		if ($line['topic'][2]!='') {
			$out[] = $line['topic'][2];
		}
		$line['topic'] =  implode('; ',$out);
		if ($line['ratingavg']==null) {$line['ratingavg'] = 0;}

		echo '<tr ';
		if ($i%2==0) { echo 'class="even"';} else {echo 'class="odd"';}
		echo '>';
		$i++;
		echo '<td><a href="arithmetic.php?id=' . Sanitize::onlyInt($line['id']) . ($ispublic ? '&amp;public=true' : '') . '">' . Sanitize::encodeStringForDisplay($line['title']) . '</a></td>';

		echo '<td>'.$line['topic'].'</td>';
		if ($line['ratingcnt']>0) {
			$v = $line['ratingcnt']/($line['ratingcnt'] + 5);
			$baysian = round($line['ratingavg']*$v + $ratingavg*(1-$v) - 1/$line['ratingcnt'],3);
		} else {
			$baysian = 0;
		}
		echo '<td class="nowrap"><span class="inline-rating" order="sortby'.$baysian.'"><ul class="star-rating">';
		echo '<li class="current-rating" style="width:'.(round(20*$line['ratingavg'])).'%">Currently '.round($line['ratingavg'],1).'/5 stars</li>';
		echo '</ul></span> ('.$line['ratingcnt'].')</td>';
		echo '<td class="nowrap">'.date('n/j/y',$line['postedon']).'</td>';
		echo '<td class="nowrap">'.date('n/j/y',$line['lastmod']).'</td><td class="nowrap">';
		//echo $line['LastName']. ', '.$line['FirstName'].'</td><td>';
		$dev = explode(',',$line['dev']);
		echo $dev[0];
		if (count($dev)>1) {
			echo ' et al.';
		}
		echo '</td><td>';
		if ($line['ownerid']==$userid || $myrights==100 || $userid==745) {
			echo '<span style="font-size: 70%" class="nowrap"><a href="arithmetic.php?modify=' . Sanitize::onlyInt($line['id']) . '">Modify</a> | ';
			echo '<a href="arithmetic.php?remove=' . Sanitize::onlyInt($line['id']) . '" onclick="return confirm(\'Are you SURE you want to delete this item?\');">Remove</a></span>';
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
	global $DBH,$userid, $ispublic;
	//DB $query = "SELECT tr.rating,tr.comment,tr.userid,iu.FirstName,iu.LastName,tr.rateon FROM arithmetic_ratings AS tr JOIN imas_users AS iu ON tr.userid=iu.id ";
	//DB $query .= "WHERE tr.taskid='$id' ORDER BY tr.rateon DESC";
	//DB $result = mysql_query($query) or die("Query failed : $query " . mysql_error());
	$query = "SELECT tr.rating,tr.comment,tr.userid,iu.FirstName,iu.LastName,tr.rateon FROM arithmetic_ratings AS tr JOIN imas_users AS iu ON tr.userid=iu.id ";
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
		$totrat += $line['rating'];
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
	if (!$ispublic) {
		$out .= '<div id="ratingentry" class="arating">';
		if ($myrating==-1) {
			$out .= '<b>Rate this task</b>: ';
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
	}
	foreach ($ratings as $i=>$rating) {
		if ($i==$myrating) {continue;}
		$out .= '<div class="arating">';
		$out .= '<span class="inline-rating"><ul class="star-rating">';
		$out .= '<li class="current-rating" style="width:'.(20*$rating[0]).'%">Currently '.Sanitize::onlyInt($rating[0]).'/5 stars</li>';
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
		$out .= '<br/><i>'.Sanitize::encodeStringForDisplay($rating[2]).', '.Sanitize::encodeStringForDisplay(time_elapsed_string($rating[3])).'</i>';
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
