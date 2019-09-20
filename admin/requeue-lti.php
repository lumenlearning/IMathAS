<?php

namespace OHM\Admin;

use Sanitize;

require '../init.php';
require("requeue-lti-lib.php");

if ($GLOBALS['myrights'] < 100) {
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
echo '<h1>View and ReQueue LTI Grade Passback</h1>';

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
    $lms = Sanitize::simpleString($_POST['lms']);

    $assessmentids = RequeueLtiLib::getCourseAssessmentIds($cid, $aid);
    if (empty($assessmentids)) {
        echo '<h2>unable to find assessment ids</h2>';
    } else {
        $course = RequeueLtiLib::getCourseName($cid);
        echo '<h2>' . $course['name'] . ' (' . $course['id'] . ')</h2>';

        $records = RequeueLtiLib::getAssessmentRecords($assessmentids, $uid);
    }
    if (empty($records)) {
        var_dump($assessmentids);
        echo '<h2>unable to find assessment records for assessment ids: ' . implode(', ', $assessmentids). '</h2>';
    } else {
        echo '<ol>';
        $current_assessment = '';
        foreach ($records as $us) {
            if ($current_assessment != $us['aid']) {
                $assessment = RequeueLtiLib::getAssessmentName($us['aid']);
                echo '<lh><h3>Assessment V' .  $us['ver'] . ': ';
                echo $assessment['name'] . ' (' . $us['aid'] . ')</h3></lh>';
                $current_assessment = $us['aid'];
            }
            echo '<li>User ID: ' . $us['uid'] . '<ul>';
            echo '<li>LTI sourced_id: ' . Sanitize::encodeStringForDisplay($us['sourcedid']) . '</li>';
            echo '<li>Score: ' . getpts($us['scores']) . '</li>';
            $grade = RequeueLtiLib::reCalcandupdateLTIgrade($us['aid'], $us['scores']);
            echo '<li>Grade: ' . $grade . '</li>';

            if ($queue === true && addToLTIQueue($us['sourcedid'], $grade, true)) {
                if ($lms==true and $uid) {
                    echo '<li>LMS Grade: Recheck in a few minutes to pull a new grade after requeue</li>';
                }
                echo '<li>Added to queue</li>';
            } else {
                if ($lms==true and $uid) {
                    $lms_grade = RequeueLtiLib::requestLMSGrade($us['sourcedid']);
                    echo "<li>LMS Grade: $lms_grade</li>";
                }
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
    echo 'Check LMS Grade (single user only) <input type="checkbox" name="lms" value="true" /><br />';
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

