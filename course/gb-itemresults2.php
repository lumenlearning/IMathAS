<?php
//IMathAS: Display a summary of student results on a particular question
//(c) 2011 David Lippman

// Currently works for choices, multans, and non-randomized free response questions
// and multipart containing those.

// does NOT work for randomized questions or matching.

require_once "../init.php";

if (!isset($teacherid) && !isset($tutorid)) {
	require_once "../header.php";
	echo "You need to log in as a teacher or tutor to access this page";
	require_once "../footer.php";
	exit;
}

$cid = intval($_GET['cid']);
$aid = Sanitize::onlyInt($_GET['aid']); //imas_assessments id

//pull questionset ids
$qsids = array();
$stm = $DBH->prepare("SELECT id,questionsetid FROM imas_questions WHERE assessmentid=:assessmentid");
$stm->execute(array(':assessmentid'=>$aid));
while ($row = $stm->fetch(PDO::FETCH_NUM)) {
	$qsids[$row[0]] = $row[1];
}

//pull question data
$qsdata = array();
$query_placeholders = Sanitize::generateQueryPlaceholders($qsids);
$stm = $DBH->prepare("SELECT * FROM imas_questionset WHERE id IN ($query_placeholders)");
$stm->execute(array_values($qsids)); //INT from DB
while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
	$qsdata[$row['id']] = $row;
}

//pull assessment_sessions data
//look for this question in the itemorder (may be multiple times)
//get the answer they gave on the (first or last) attempt
//if multiple choice, multiple answer, or matching, use the question code and seed
//   to backtrack to original option
//tally results, grouping by result
//output results.  For numeric/function, sort by frequency

$query = "SELECT scoreddata,ver FROM imas_assessment_records WHERE assessmentid = :assessmentid";
$stm = $DBH->prepare($query);
$stm->execute(array(':assessmentid' => $aid));

$sessionCount = 0;
$qdata = array();

while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
    $GLOBALS['assessver'] = $row['ver'];

    $scoredData = json_decode(Sanitize::gzexpand($row['scoreddata']), true);

    $scoredAssessmentIndex = $scoredData['scored_version'];
    $scoredAssessment = $scoredData['assess_versions'][$scoredAssessmentIndex];
    $questions = $scoredAssessment['questions'];

    for ($qn = 0; $qn < count($questions); $qn++) {
        $scoredQuestionIndex = $questions[$qn]['scored_version'];
        $scoredQuestion = $questions[$qn]['question_versions'][$scoredQuestionIndex];
        $questionId = $scoredQuestion['qid'];

        if (0 == count($scoredQuestion['tries'])) {
            continue;
        }

        $qscore = array();
        $qatt = array();

        for ($pn = 0; $pn < count($scoredQuestion['tries']); $pn++) {
            $scoredTryIndex = $scoredQuestion['scored_try'][$pn];
            if (-1 == $scoredTryIndex) {
                // No answer/attempt found.
                $qscore[$pn] = 0;
                $qatt[$pn] = '';
            } else {
                $scoredTry = $scoredQuestion['tries'][$pn][$scoredTryIndex];
                $qscore[$pn] = $scoredTry['raw'];
                $qatt[$pn] = $scoredTry['stuans'];
            }
        }

        // Is this is a single part question?
        if (1 == count($scoredQuestion['answeights'])) {
            $qatt = $qatt[0];
            $qscore = $qscore[0];
        }

        $qdata[$questionId][] = array($qatt, $qscore);
    }
}



$scorebarwidth = 60;
$placeinhead = ' <style type="text/css">
.scorebarinner {
	height:10px;
	font-size:80%;
	display:-moz-inline-box;
	display:inline-block;
	position:relative;
	left:0px;
	top:0px;

}
</style>';
$useeqnhelper = 0;
require_once "../assessment/header.php";
echo "<div class=breadcrumb>$breadcrumbbase <a href=\"course.php?cid=".Sanitize::courseId($_GET['cid'])."\">".Sanitize::encodeStringForDisplay($coursename)."</a> ";
echo "&gt; <a href=\"gradebook.php?stu=0&cid=$cid\">Gradebook</a> ";
echo "&gt; Item Results</div>";
echo '<div id="headergb-itemanalysis" class="pagetitle"><h1>Item Results: ';
$stm = $DBH->prepare("SELECT defpoints,name,itemorder,tutoredit FROM imas_assessments WHERE id=:id");
$stm->execute(array(':id'=>$aid));
list ($defpoints, $aname, $itemorder,$tutoredit) = $stm->fetch(PDO::FETCH_NUM);
echo Sanitize::encodeStringForDisplay($aname) . '</h1></div>';
if (isset($tutorid) && $tutoredit==2) {
	echo 'You do not have access to view scores for this assessment.';
	require_once "../footer.php";
	exit;
}
$itemarr = array();
$itemnum = array();
foreach (explode(',',$itemorder) as $k=>$itel) {
	if (strpos($itel,'~')!==false) {
		$sub = explode('~',$itel);
		if (strpos($sub[0],'|')!==false) {
			array_shift($sub);
		}
		foreach ($sub as $j=>$itsub) {
			$itemarr[] = $itsub;
			$itemnum[$itsub] = ($k+1).'-'.($j+1);
		}
	} else {
		$itemarr[] = $itel;
		$itemnum[$itel] = ($k+1);
	}
}
echo '<p style="color:#f00;">Warning: Results are not accurate or meaningful for randomized questions</p>';

require_once '../assess2/AssessStandalone.php';
$a2 = new AssessStandalone($DBH);
foreach ($qsdata as $k=>$v) {
    $a2->setQuestionData($k, $v);
}

$questions = array_keys($qdata);
foreach ($itemarr as $k=>$q) {
	echo '<div style="border:1px solid #000;padding:10px;margin-bottom:10px;clear:left;">';
	echo '<p><span style="float:right">(Question ID '.Sanitize::onlyInt($qsids[$q]).')</span><b>'.Sanitize::encodeStringForDisplay($qsdata[$qsids[$q]]['description']).'</b></p>';
	echo '<br class="clear"/>';
	echo '<div style="float:left;width:35%;">';
	showresults($q,$qsdata[$qsids[$q]]['qtype']);
	echo '</div>';
	echo '<div style="float:left;width:60%;margin-left:10px;">';
    $state = array(
		'seeds' => array($k => 0),
		'qsid' => array($k => $qsids[$q])
	);
	$a2->setState($state);
    $res = $a2->displayQuestion($k, ['showhints'=>false]);
    echo $res['html'];
	echo '</div>';
	echo '<br class="clear"/>';
	echo '</div>';
}
require_once "../footer.php";

function sandboxeval($control, $qtype) {
	try {
		eval(interpret('control', $qtype, $control));
	} catch (Throwable $t) {
		if ($GLOBALS['myrights']>10) {
			echo '<p>Caught error in evaluating a function in a question: ';
			echo Sanitize::encodeStringForDisplay($t->getMessage());
			echo '</p>';
		}
	}
	if ($qtype=='multipart' && !is_array($anstypes)) {
		$anstypes = explode(',',$anstypes);
	}
	if ($qtype=='multipart' && count($anstypes)==1) {
		//if it's multipart but only one part, treat like it was
		//just a singlepart question of that type
		//matches handling of stuanswers.
		$qtype = $anstypes[0];
		if (isset($answer) && is_array($answer)) {
			$answer = $answer[0];
		}
		if (isset($answers) && is_array($answers)) {
			$answers = $answers[0];
		}
	}
	if ($qtype=='choices' || $qtype=='multans' || $qtype=='multipart') {
		if (isset($choices) && !isset($questions)) {
			$questions =& $choices;
		}
	}
	return array(
		isset($anstypes)?$anstypes:array(),
		isset($questions)?$questions:array(),
		isset($answer)?$answer:"",
		isset($answers)?$answers:""
	);
}

function showresults($q,$qtype) {
	global $qdata,$qsids,$qsdata;
	//eval(interpret('control',$qtype,$qsdata[$qsids[$q]][1]));
	list($anstypes, $questions, $answer, $answers) = sandboxeval($qsdata[$qsids[$q]]['control'], $qtype);

	if ($qtype=='choices' || $qtype=='multans' || $qtype=='multipart') {
		if ($qtype=='multipart') {
			foreach ($anstypes as $i=>$type) {
				if ($type=='choices' || $type=='multans') {
					if (isset($questions[$i]) && is_array($questions[$i])) {
						$ql = $questions[$i];
					} else {
						$ql = $questions;
					}
					if ($type=='multans') {
						$al = $answers;
					} else if ($type=='choices') {
						$al = $answer;
					}
					disp($q,$type,$i,$al,$ql);
				} else {
					$al = $answer;
					disp($q,$type,$i,$al);
				}

			}
		} else {
			if ($qtype=='multans') {
				$al = $answers;
			} else if ($qtype=='choices') {
				$al = $answer;
			}
			disp($q,$qtype,-1,$al,$questions);
		}
	} else {
		disp($q,$qtype,-1,$answer);
	}
}

function disp($q,$qtype,$part=-1,$answer='',$questions=array()) {
	global $qdata,$qsdata,$qsids,$scorebarwidth;
	$res = array();
	$correct = array();
	if (is_array($answer)) {
		$answer = $answer[$part] ?? '';
	}
	$answer = explode(',',$answer);

	if (array_key_exists($q, $qdata)) {
        foreach ($qdata[$q] as $varr) {
            if ($part > -1) {
                $v = $varr[0][$part];
            } else {
                $v = $varr[0];
            }
            $v = explode('|', $v); //sufficient for choices and multans
            foreach ($v as $vp) {
                if ($part > -1) {
                    if ($varr[1][$part] > 0) {
                        $correct[] = $vp;
                    }
                } else {
                    if ($varr[1] > 0) {
                        $correct[] = $vp;
                    }
                }
                if ($vp !== '') {
                    $res[] = $vp;
                }
            }
        }
    }
	$res = array_count_values($res);
	if (count($res)>0) {
		$restot = max($res);
	} else {
		$restot = 1;
	}
	if ($part>-1) {echo "Part ".($part+1);}
	echo '<table class="gridded">';
	echo '<thead>';
	echo '<tr><td>Answer</td><td>Count of students</td></tr>';
	echo '</thead><tbody>';
	if ($qtype=='choices' || $qtype=='multans') {
		for ($k=0;$k<count($questions);$k++) {
			if (!isset($res[$k])) {
				continue;
			}
			echo '<tr><td>' . Sanitize::encodeStringForDisplay($questions[$k]) . '</td><td>' . Sanitize::encodeStringForDisplay($res[$k]);
			echo ' <span class="scorebarinner" style="';
			if (in_array($k,$answer)) {
				echo 'background:#9f9;';
			} else {
				echo 'background:#f99;';
			}
			echo 'width:'.round($scorebarwidth*$res[$k]/$restot).'px;"';
			echo '>&nbsp;</span>';
			echo '</td></tr>';
		}

	} else {
		arsort($res);
		foreach ($res as $ans=>$cnt) {
			echo '<tr><td>' . Sanitize::encodeStringForDisplay($ans) . '</td><td>' . Sanitize::encodeStringForDisplay($cnt);
			echo ' <span class="scorebarinner" style="';

			if (in_array($ans,$correct)) {
				echo 'background:#9f9;';
			} else {
				echo 'background:#f99;';
			}
			echo 'width:'.round($scorebarwidth*$cnt/$restot).'px;"';
			echo '>&nbsp;</span>';
			echo '</td></tr>';
		}

	}
	echo '</tbody></table>';
}



?>
