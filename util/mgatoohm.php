<?php

require('../init.php');
if ($myrights < 100) { exit; }

require('../header.php');

// if no text, show form
if (!isset($_POST['text'])) {
    ?>
    <script type="text/javascript">
	var curlibs = '0';
	function libselect() {
		window.open('../course/libtree2.php?libtree=popup&cid=<?php echo $cid ?>&selectrights=1&select=parent&type=radio','libtree','width=400,height='+(.7*screen.height)+',scrollbars=1,resizable=1,status=1,top=20,left='+(screen.width-420));
	}
	function setlib(libs) {
		document.getElementById("parent").value = libs;
		curlibs = libs;
	}
	function setlibnames(libn) {
		document.getElementById("libnames").innerHTML = libn;
    }
    </script>
    <?php
    echo '<h1>MGA to OHM</h1>';
    echo '<form method="post">';
    echo '<p>Paste in MGA contents, not including the table of contents</p>';
    echo '<textarea name=text style="width:100%;height:12em"></textarea>';
    echo '<p>Parent library:
        <span id="libnames">Root</span>
        <input type=hidden name="parent" id="parent"  value="0">
        <input type=button value="Select Parent" onClick="libselect()">
    </p>';
    echo '<p>Send email confirmation to: <input name=email type=email /></p>';
    echo '<p><button type=submit>Import</button></p>';
    require('../footer.php');
    exit;
}

// functions 
function preptext($txt) {
    $txt = preg_replace_callback('/\(img (http\S+)\s*(.*)\)/', function($m) {
        return '<img src="'.$m[1].'" alt="'.Sanitize::encodeStringForDisplay($m[2]).'" />';
    }, $txt);
    // escape dollar signs followed by letter
    $txt = preg_replace('/\$([a-zA-Z])/', '\\\\$$1', $txt);
    return $txt;
}

function storeQuestion($qdata, $lib, $libinfo, $qn) {
    global $userid,$csvrow,$DBH;

    //[SLO enumeration] [SLO description][Q question number] [(w/feedback) if has feedback]

    //Description:
    //0.1.1 Demonstrate an understanding of financial aid options Q1 (w/feedback)

    $descr = $libinfo['num']. ' ' . $libinfo['slo'] . ' Q'.$qn;

    if (count($qdata['feedbacks']) > 0) {
        $descr .= ' (w/feedback)';
    }
    $code = "//start randomization code - Tutorial Style question\n{$qdata['comments']}\n//end randomization code - Tutorial Style question\n\n";
    foreach ($qdata['choices'] as $k=>$v) {
        $code .= '$questions['.$k.'] = "'.str_replace('"', '&quot;', $v).'"' . "\n";
    }
    foreach ($qdata['feedbacks'] as $k=>$v) {
        $code .= '$feedbacktxt['.$k.'] = "'.str_replace('"', '&quot;', $v).'"' . "\n";
    }
    $code .= '$displayformat = "vert"' . "\n";
    $code .= '$noshuffle = "none"' . "\n";
    $code .= '$answer = ' . $qdata['answer'] . "\n";

    $code .= "\n\n//end stored values - Tutorial Style question\n\n//end retained code - Tutorial Style question\n\n";

    $code .= '$feedback = getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer)' . "\n";

    $qtext = trim($qdata['prompt']) . "\n\n" . '$answerbox' . "\n\n" . '$feedback';

    $mt = microtime();
	$uqid = substr($mt,11).substr($mt,2,6);
    $stm = $DBH->prepare('INSERT INTO imas_questionset (uniqueid,adddate,lastmoddate,ownerid,author,description,qtype,control,qtext,otherattribution) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $stm->execute([
        $uqid,
        time(),
        time(),
        $userid,
        'Lumen Learning',
        $descr,
        'choices',
        $code,
        $qtext,
        $libinfo['guid']
    ]);
    $qsid = $DBH->lastInsertId();
    $csvrow[] = [
        $libinfo['num'], 
        $libinfo['slo'], 
        $libinfo['guid'], 
        $qn, 
        trim(preg_replace('/\s+/m',' ',$qdata['prompt']))
    ];
    $stm = $DBH->prepare('INSERT INTO imas_library_items (libid,qsetid,ownerid,lastmoddate) VALUES (?,?,?,?)');
    $stm->execute([$lib, $qsid, $userid, time()]);
}

// start processing
$root = intval($_POST['parent']);

$lastparent = [0 => $root];

$input = explode("\n", str_replace("\r\n","\n", $_POST['text']));
$csvrow = [];
$curlib = -1;
$curquestion = null;
$comments = '';
$qn = 1;
while (($row = array_shift($input)) !== null) {
    if (preg_match('/^(#+)([\d\.]+)(.*)/', $row, $matches)) {
        // is a library header
        $level = strlen($matches[1]);
        $libnum = $matches[2];
        $libslo = trim($matches[3]);
        $libname = $libnum .' '.array_shift($input);
        $libguid = substr(array_shift($input), 1);
        $libinfo = ['slo'=>$libslo, 'num'=>$libnum, 'guid'=>$libguid];
        $qn = 1;

        $stm = $DBH->prepare('SELECT id FROM imas_libraries WHERE name=?');
        $stm->execute(array($libname));
        $curlib = $stm->fetchColumn(0);
        if ($curlib === false) {
            $stm = $DBH->prepare('INSERT INTO imas_libraries (uniqueid,adddate,lastmoddate,name,ownerid,userights,parent,groupid,sortorder,federationlevel) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $mt = microtime();
			$uqid = substr($mt,11).substr($mt,2,6);
            $stm->execute([
                $uqid,
                time(),
                time(),
                $libname,
                $userid,
                8, //userights
                $lastparent[$level-1],
                $groupid,
                0, //sort order
                0 //fed level
            ]);
            $curlib = $DBH->lastInsertId();
        }
        $lastparent[$level] = $curlib;
    } else {
        if (trim($row) == '') {
            if ($curquestion !== null) {
                storeQuestion($curquestion, $lastparent[$level], $libinfo, $qn);
                $qn++;
                $curquestion = null;
                $comments = '';
            } else {
                continue;
            }
        } else {
            if ($curquestion === null) { // new
                $curquestion = [];
                $curquestion['comments'] = '';
                $curquestion['prompt'] = preptext($row);
                while (($row = array_shift($input)) !== null) {
                    if ($row[0]=='~') {
                        $curquestion['type'] = substr($row, 1);
                        break;
                    } else if (substr($row,0,2)=='//') { // comments
                        $curquestion['comments'] .= $row . "\n";
                        continue;
                    } else {
                        $curquestion['prompt'] .= "\n\n".preptext($row);
                    }
                }
                $curquestion['choices'] = [];
                $curquestion['feedbacks'] = [];
            } else {
                if (preg_match('/^\d\.\s*(.*?)(\*?)\s*$/', $row, $matches)) {
                    $curquestion['choices'][] = preptext($matches[1]);
                    if ($matches[2] == '*') {
                        $curquestion['answer'] = count($curquestion['choices']) - 1;
                    }
                } else if (preg_match('/^~feedback:\s*(.*)/', $row, $matches)) {
                    $curquestion['feedbacks'][] = preptext($matches[1]);
                } else if (substr($row,0,2)=='//') { // comments
                    $curquestion['comments'] .= $row . "\n";
                } else if ($row[0]=='~') { // comments
                    $curquestion['comments'] .= '// Question GUID: '.substr($row,1) . "\n";;
                }
            }
        }
    }
}
if ($curquestion !== null) {
    storeQuestion($curquestion, $lastparent[$level], $libinfo, $qn);
}

$f = fopen('php://memory', 'r+');
foreach ($csvrow as $row) {
    fputcsv($f, $row);
}
rewind($f);
$csv = stream_get_contents($f);

require_once('../includes/filehandler.php');
$filename = date('Y-m-d').'-'.uniqid().'.csv';
storecontenttofile($csv, 'mgatoohm/'.$filename, 'public');
$url = getuserfileurl('mgatoohm/'.$filename);

if (!empty($_POST['email'])) {
    require('../includes/email.php');
    $message = '<p>MGA to OHM conversion from '.date("Y-m-d H:i:s").'</p>';
    $message .= '<p>Results CSV: '.$url.'</p>';
    send_email($_POST['email'], $sendfrom, 'MGA to OHM conversion', $message);
}

echo "Done! <a href=\"$url\">Get Results CSV</a>";
require('../footer.php');

