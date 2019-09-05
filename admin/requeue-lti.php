<?php
require '../init.php';
if ($myrights<100) {
    echo 'You must be an admin';
    exit;
}

/*
 * user score
 *      sourcedid
 *      aid
 *      scores
 * course
 *
 * imas_assessment_records
 *      assessmentid (aid)
 *      userid
 *      lti_sourcedid (sourcedid)
 *      score   (single scores)
 *
 * imas_courses
 *      id
 *      ownerid
 *      name
 *
 * imas_ltiqueue
 *      hash
 *      sourcedid
 *      grade
 *
 */
require("../header.php");
echo '<h1>Reprocess LTI Requests</h1>';

/* add to queue? */
$queue = false;
if ($_POST['queue']) {
    $queue = true;
}
//global $DBH, $CFG, $aidtotalpossible;
if ($_POST['cid']) {
    $cid = filter_input(INPUT_POST, 'cid', FILTER_SANITIZE_NUMBER_INT);
    $aid = filter_input(INPUT_POST, 'aid', FILTER_SANITIZE_NUMBER_INT);

    if (empty($aid)) {
        $assessmentids = getCourseAssessmentIds($cid);
    } else {
        $assessmentids[] = $aid;
    }
    if (empty($assessmentids)) {
        echo '<h2>unable to find assessment ids</h2>';
    } else {
        $course = getCourse($cid);
        echo '<h2>' . $course['name'] . ' (' . $course['id'] . ')</h2>';

        $records = getAssessmentRecords($assessmentids);
    }
    if (empty($records)) {
        echo '<h2>unable to find assessment records for assessment ids: ' . implode(', ', $assessmentids). '</h2>';
    } else {
        echo '<ol>';
        $current_assessment = '';
        foreach ($records as $us) {
            if ($current_assessment != $us['aid']) {
                $assessment = getAssessment($us['aid']);
                echo '<lh><h3>Assessment: ' . $assessment['name'] . '</h3></lh>';
                $current_assessment = $us['aid'];
            }
            updateLTI($us['sourcedid'], $us['aid'], $us['scores'], $queue);
        }
        echo '</ol>';
        if ($queue == false) repostForm();
    }
} elseif ($_POST['sourcedid'] && $_POST['aid'] && $_POST['scores']) {
    $sourcedid = filter_input(INPUT_POST, 'sourcedid', FILTER_SANITIZE_NUMBER_INT);
    $aid = filter_input(INPUT_POST, 'aid', FILTER_SANITIZE_NUMBER_INT);
    $scores = filter_input(INPUT_POST, 'scores', FILTER_SANITIZE_NUMBER_FLOAT);
    if (empty($sourcedid) || empty($aid) || empty($scores)) {
        echo 'invalid values';
    } else {
        echo '<ol>';
        $assessment = getAssessment($aid);
        echo '<lh><h3>Assessment: ' . $assessment['name'] . '</h3></lh>';
        updateLTI($sourcedid, $aid, $scores, $queue);
        echo '</ol>';
        if ($queue == false) repostForm();
    }

} elseif ($_POST) {
    echo "unable to process your request, please try again";
} else {

    /*
     * Initial form
     */
    echo '<form method="post">';
    echo '<h2>Course Assessments</h2>';
    echo 'Course ID: <input type="text" name="cid" required /><br />';
    echo 'Assessment ID: <input type="text" name="aid" /> *optional<br />';
    echo '<button type="submit">Search</button>';

    echo '</form>';

    echo '<form method="post">';
    echo '<h2>Individual</h2>';
    echo 'sourcedid <input type="text" name="sourcedid"><br />';
    echo 'aid <input type="text" name="aid"><br />';
    echo 'score <input type="text" name="scores"><br />';
    echo '<button type="submit">Search</button>';
    echo '</form>';
}

require("../footer.php");

function repostForm() {
    echo '<form method="post">';
    foreach ($_POST as $key=>$value) {

        echo '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
    }
    echo '<input type="hidden" name="queue" value="true" />';
    echo '<button type="submit">Add to Queue</button>';
    echo '</form>';
}

function updateLTI($sourcedid, $aid, $scores, $queue = false) {
    echo '<li>sourcedid ' . $sourcedid . '<ul>';
    echo '<li>Calculate grade with score ' . $scores . '</li>';
    $grade = calcandupdateLTIgrade($sourcedid, $aid, $scores);
    echo '<li>grade = ' . $grade . '</li>';

    if ($queue === true && addToLTIQueue($sourcedid,$grade)) {
        echo '<li>Added to queue</li>';
    } else {
        echo '<li>NOT added to queue</li>';
    }
    echo '</ul></li>';
}

function getCourseAssessmentIds($cid) {
    global $DBH;
    $query = "SELECT id as aid"
        ." FROM imas_assessments WHERE courseid = :courseid ";
    $stm = $DBH->prepare($query);
    $stm->execute(array(':courseid'=>$cid));
    if ($stm->rowCount()==0) {
        return false;
    } else {
        return array_keys($stm->fetchAll(PDO::FETCH_UNIQUE));
    }
}

function getAssessmentRecords($assessmentids) {
    global $DBH;
    $query = "SELECT assessmentid as aid, lti_sourcedid as sourcedid, score as scores "
        ." FROM imas_assessment_records WHERE assessmentid IN (:assessmentids)";
    $stm = $DBH->prepare($query);
    $stm->execute(array(':assessmentids'=>implode(",",$assessmentids)));
    if ($stm->rowCount()==0) {
        return false;
    } else {
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
}
function getCourse($courseid) {
    global $DBH;
    $query = "SELECT name, id "
        ." FROM imas_courses WHERE imas_courses.id = :courseid ";
    $stm = $DBH->prepare($query);
    $stm->execute(array(':courseid'=>$courseid));
    if ($stm->rowCount()==0) {
        return false;
    } else {
        return $stm->fetch(PDO::FETCH_ASSOC);
    }
}
function getAssessment($aid) {
    global $DBH;
    $query = "SELECT name, id "
        ." FROM imas_assessments WHERE id = :aid ";
    $stm = $DBH->prepare($query);
    $stm->execute(array(':aid'=>$aid));
    if ($stm->rowCount()==0) {
        return false;
    } else {
        return $stm->fetch(PDO::FETCH_ASSOC);
    }
}
function getCourseAssessments($assessmentids = null) {
    global $DBH;
    $query = "SELECT imas_courses.name as course, imas_courses.id as cid, "
        ." imas_assessments.name as assessment, imas_assessments.id as aid "
        ." FROM imas_courses JOIN imas_assessments ON imas_courses.id = imas_assessments.courseid ";
    if ($assessmentids) {
        $query .= " WHERE imas_assessments.id IN (:assessmentids)";
    }
    $stm = $DBH->prepare($query);
    $stm->execute(array(':assessmentids'=>implode(",",$assessmentids)));
    if ($stm->rowCount()==0) {
        return false;
    } else {
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
}

/*
 * iMathAS functions
 */
function calcandupdateLTIgrade($sourcedid,$aid,$scores,$sendnow=false,$aidposs=-1) {
    global $DBH, $aidtotalpossible;
    if ($aidposs == -1) {
        if (isset($aidtotalpossible[$aid])) {
            $aidposs = $aidtotalpossible[$aid];
        } else {
            $stm = $DBH->prepare("SELECT ptsposs,itemorder,defpoints FROM imas_assessments WHERE id=:id");
            $stm->execute(array(':id'=>$aid));
            $line = $stm->fetch(PDO::FETCH_ASSOC);
            if ($line['ptsposs']==-1) {
                $line['ptsposs'] = updatePointsPossible($aid, $line['itemorder'], $line['defpoints']);
            }
            $aidposs = $line['ptsposs'];
        }
    }
    $allans = true;
    if (is_array($scores)) {
        // old assesses
        $total = 0;
        for ($i =0; $i < count($scores);$i++) {
            if ($allans && strpos($scores[$i],'-1')!==FALSE) {
                $allans = false;
            }
            if (getpts($scores[$i])>0) { $total += getpts($scores[$i]);}
        }
    } else {
        // new assesses
        $total = $scores;
    }
    $grade = min(1, max(0,$total/$aidposs));
    $grade = number_format($grade,8);
    return $grade;
}
function addToLTIQueue($sourcedid, $grade, $sendnow=false) {
    global $DBH, $CFG;

    $LTIdelay = 60*(isset($CFG['LTI']['queuedelay'])?$CFG['LTI']['queuedelay']:5);

    $query = 'INSERT INTO imas_ltiqueue (hash, sourcedid, grade, failures, sendon) ';
    $query .= 'VALUES (:hash, :sourcedid, :grade, 0, :sendon) ON DUPLICATE KEY UPDATE ';
    $query .= 'grade=VALUES(grade),sendon=VALUES(sendon),failures=0 ';

    $stm = $DBH->prepare($query);
    $stm->execute(array(
        ':hash' => md5($sourcedid),
        ':sourcedid' => $sourcedid,
        ':grade' => $grade,
        ':sendon' => (time() + ($sendnow?0:$LTIdelay))
    ));

    return ($stm->rowCount()>0);
}