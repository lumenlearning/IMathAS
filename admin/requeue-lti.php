<?php
require '../init.php';
if ($myrights<100) {
    echo 'You must be an admin';
    exit;
}
echo md5('2493-30181-549952-97676-c391c0e0d270ab7e2c1f5fe7c707d91ed2cbb078:|:https://tcc.instructure.com/api/lti/v1/tools/2493/grade_passback:|:a7ef7e0c1b8c363c6a9a4793f419331b8917a80f:|:u');

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
 *  imas_assessment_sessions
 *      assessmentid (aid)
 *      userid
 *      lti_sourcedid (sourcedid)
 *      bestscores   (comma separated scores)
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
    $cid = Sanitize::onlyInt($_POST['cid']);
    $aid = Sanitize::onlyInt($_POST['aid']);
    $uid = Sanitize::onlyInt($_POST['uid']);

    $assessmentids = getCourseAssessmentIds($cid, $aid);
    if (empty($assessmentids)) {
        echo '<h2>unable to find assessment ids</h2>';
    } else {
        $course = getCourse($cid);
        echo '<h2>' . $course['name'] . ' (' . $course['id'] . ')</h2>';

        $records = getAssessmentRecords($assessmentids, $uid);
    }
    if (empty($records)) {
        echo '<h2>unable to find assessment records for assessment ids: ' . implode(', ', $assessmentids). '</h2>';
    } else {
        echo '<ol>';
        $current_assessment = '';
        foreach ($records as $us) {
            if ($current_assessment != $us['aid']) {
                $assessment = getAssessment($us['aid']);
                echo '<lh><h3>Assessment: ' . $assessment['name'] . ' (' . $us['aid'] . ')</h3></lh>';
                $current_assessment = $us['aid'];
            }
            echo '<li>User ID: ' . $us['uid'] . '<ul>';
            echo '<li>sourcedid: ' . $us['sourcedid'] . '</li>';
            echo '<li>Score: ' . $us['scores'] . '</li>';
            $grade = calcandupdateLTIgrade($us['aid'], $us['scores']);
            echo '<li>Grade: ' . $grade . '</li>';

            if ($queue === true && addToLTIQueue($us['sourcedid'],$grade)) {
                echo '<li>Added to queue</li>';
            } else {
                echo '<li>NOT added to queue</li>';
            }
            echo '</ul></li>';
        }
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
    echo 'Assessment ID: <input type="text" name="aid" /><br />';
    echo 'User ID: <input type="text" name="uid" /> *optional<br />';
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
}

function getCourseAssessmentIds($cid, $aid=null) {
    global $DBH;
    $query = "SELECT id as aid, ver"
        ." FROM imas_assessments WHERE courseid = :courseid ";
    $bind[':courseid'] = $cid;
    if (!empty($aid)) {
        $query .= ' AND id = :aid';
        $bind[':aid'] = $aid;
    }
    $stm = $DBH->prepare($query);
    $stm->execute($bind);
    if ($stm->rowCount()==0) {
        return false;
    } else {
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
}

function getAssessmentRecords($assessments, $uid = null) {
    foreach ($assessments as $assess) {
        if ($assess['ver'] == 2) {
            $assess2[] = $assess['aid'];
        } else {
            $assess1[] = $assess['aid'];
        }
    }
    $results = array();
    if (!empty($assess2)) {
        $results = array_merge($results, getAssessmentResults($assess2, $uid, 2));
    }
    if (!empty($assess1)) {
        $results = array_merge($results, getAssessmentResults($assess1, $uid, 1));
    }
    return $results;
}
function getAssessmentResults($aids, $uid = null, $ver = 1) {
    global $DBH;
    if ($ver == 2) {
        $query = "SELECT userid as uid, assessmentid as aid, lti_sourcedid as sourcedid, score as scores "
            . " FROM imas_assessment_records WHERE assessmentid IN (:assessmentids)";
    } else {
        $query = "SELECT userid as uid, assessmentid as aid, lti_sourcedid as sourcedid, bestscores as scores "
            . " FROM imas_assessment_sessions WHERE assessmentid IN (:assessmentids)";
    }
    $bind[':assessmentids'] = implode(",", $aids);
    if (!empty($uid)) {
        $query .= " AND userid = :userid";
        $bind[':userid'] = $uid;
    }
    $stm = $DBH->prepare($query);
    $stm->execute($bind);
    if ($stm->rowCount() > 0) {
        return $results = $stm->fetchAll(PDO::FETCH_ASSOC);
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

/*
 * iMathAS functions
 */
function calcandupdateLTIgrade($aid,$scores) {
    global $DBH;
    $stm = $DBH->prepare("SELECT ptsposs,itemorder,defpoints FROM imas_assessments WHERE id=:id");
    $stm->execute(array(':id'=>$aid));
    $line = $stm->fetch(PDO::FETCH_ASSOC);
    if ($line['ptsposs']==-1) {
        $line['ptsposs'] = updatePointsPossible($aid, $line['itemorder'], $line['defpoints']);
    }
    $aidposs = $line['ptsposs'];
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
INSERT INTO imas_ltiqueue (hash, sourcedid, grade) VALUES ("def6d0f03bcfb5ca0db6037aafa27978", "2493-30181-549952-97676-c391c0e0d270ab7e2c1f5fe7c707d91ed2cbb078:|:https://tcc.instructure.com/api/lti/v1/tools/2493/grade_passback:|:a7ef7e0c1b8c363c6a9a4793f419331b8917a80f:|:u", 1);
