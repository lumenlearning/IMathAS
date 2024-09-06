<?php
declare(strict_types=1);

namespace OHM\Util;

use OHM\Controlers\Assessments;
use OHM\Controlers\LTI;
use PDO;
use Sanitize;

require("../init.php");

if ($GLOBALS['myrights'] < 20) {
    redirect_home();
}

if (empty($_GET['cid']) || empty($_GET['aid'])) {
    redirect_home();
}

require("../header.php");

if ($_GET['cid']) {
    resyncGrades();
}

printf('<a href="%s/course/isolateassessgrade.php?cid=%s&aid=%s">&lt;&lt; Back',
    $GLOBALS['basesiteurl'], Sanitize::onlyInt($_GET['cid']),
    Sanitize::onlyInt($_GET['aid']));

require("../footer.php");


function resyncGrades(): void
{
    $cid = Sanitize::onlyInt($_GET['cid']);
    $aid = Sanitize::onlyInt($_GET['aid']);
    $course = Assessments::getCourseName($cid);

    $assessmentIds = Assessments::getCourseAssessmentIds($cid, $aid);
    if (empty($assessmentIds)) {
        displayError(sprintf('No assessment IDs found for course ID %d.', $cid));
        return;
    }

    $records = Assessments::getAssessmentRecords($assessmentIds);
    if (empty($records)) {
        displayError(
            sprintf('Unable to find assessment records for assessment IDs: %s',
                implode(', ', array_column($assessmentIds, 'aid'))
            )
        );
        return;
    }

    $studentIds = getStudentIdsInCourse($cid);

    error_log(sprintf('User requested a course grade resync. %s',
        json_encode([
            'userId' => $GLOBALS['userid'],
            'username' => $GLOBALS['username'],
            'courseId' => $cid,
            'courseName' => $course['name'],
        ])
    ));

    printf('<h2>%s</h2>', $course['name']);

    $adminDetailedLog = [];
    $adminDetailedLogMaxCount = 200;

    $totalQueued = 0;
    $totalNotQueued = 0;
    $errorLogIds = array();
    $current_assessment = '';
    $assessmentName = '';
    foreach ($records as $us) {
        if (!in_array($us['uid'], $studentIds)) {
            continue;
        }

        if ($current_assessment != $us['aid']) {
            $current_assessment = $us['aid'];
            $assessment = Assessments::getAssessmentName((int)$us['aid']);
            $assessmentName = $assessment['name'];
        }

        $grade = LTI::reCalcandupdateLTIgrade((int)$us['aid'], $us['scores'], $us['ver']);
        $score = Assessments::getScoreForDisplay($us['scores']);
        $logInfo = createLogInfo($cid, $us, $assessmentName, $grade, $score);

        // Generate the hash using the same method as LTI::addToLTIQueue() for easier debugging.
        $logInfo['ltiqueue_hash'] = empty($us['sourcedid']) ? null : md5($us['sourcedid']);

        if (empty($us['sourcedid'])) {
            $logInfo['failReason'] = 'Assessment record does not have a sourcedid.'
                . ' Did the LMS provide a sourcedid? Are sourcedids enabled from the LMS (course and LMS-wide)?';
            $message = 'Failed to queue LMS grade. ' . json_encode($logInfo);
            error_log($message);
            $errorLogIds[] = $logInfo['debugId'];
            $totalNotQueued++;

            if (isAdminUser() && $adminDetailedLogMaxCount > count($adminDetailedLog)) {
                $adminDetailedLog[] = $message;
            }
        } elseif (LTI::addToLTIQueue($us['sourcedid'], $grade, true, $us['uid'], $us['aid'])) {
            $message = 'Queued LMS grade. ' . json_encode($logInfo);
            error_log($message);
            $totalQueued++;

            if (isAdminUser() && $adminDetailedLogMaxCount > count($adminDetailedLog)) {
                $adminDetailedLog[] = $message;
            }
        } else {
            $logInfo['failReason'] = 'LTI::addToLTIQueue did not insert a new row into imas_ltiqueue.'
                . ' Possible hash collision? The new hash would\'ve been: ' . $logInfo['ltiqueue_hash'];
            $message = 'Failed to queue LMS grade. ' . json_encode($logInfo);
            error_log($message);
            $errorLogIds[] = $logInfo['debugId'];
            $totalNotQueued++;

            if (isAdminUser() && $adminDetailedLogMaxCount > count($adminDetailedLog)) {
                $adminDetailedLog[] = $message;
            }
        }
    }

    echo '<ul>';
    printf('<li>Queued %d grade(s) for LMS resync.</li>', $totalQueued);
    echo '</ul>';
    echo '<p>Please allow up to 30 minutes for grades to sync.</p>';
    if (0 < $totalNotQueued) {
        dumpErrors($totalNotQueued, $errorLogIds);
    }

    /*
     * Admin-only functionality below this line.
     */

    if (!isAdminUser() || empty($adminDetailedLog)) {
        return;
    }
?>
    <hr/>
    <h1 style="font-size: 1.5em; color: #ff4444;">Everything below this line is
        shown to admin users only.</h1>
    <h2>Notes</h2>
    <ul>
        <li>You may search the LTI logfile for <code>ltiqueue_hash</code> values
            noted below for possible grade passback errors.</li>
        <li>If a <code>sourcedid</code> is not present, there will also be no
            <code>ltiqueue_hash</code>.</li>
    </ul>
    <h2>Detailed Requeue Log (max <?php echo $adminDetailedLogMaxCount ?> entries)</h2>
    <p>Search for the word "fail", case insensitively.</p>
    <p>These are also logged using PHP's <code>error_log()</code> with no max
        logging count.</p>
<?php
    echo '<ol>';
    foreach ($adminDetailedLog as $logItem) {
        echo '<li>' . $logItem . '</li>';
    }
    echo '</ol>';
}

/**
 * Get the user IDs for students enrolled in a course.
 *
 * @param int $courseId The course ID.
 * @return array An array of user IDs from imas_users.
 */
function getStudentIdsInCourse(int $courseId): array {
    $stm = $GLOBALS['DBH']->prepare("SELECT userid FROM `imas_students` WHERE courseid = :courseId");
    $stm->execute([':courseId' => $courseId]);

    $userIds = [];
    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
        $userIds[] = $row['userid'];
    }
    return $userIds;
}

/**
 * Output a failure count and list of error log IDs for the user to provide
 * to support.
 *
 * @param int $totalNotQueued The total number of failures to queue LMS passbacks.
 * @param array $errorLogIds An array of error_log IDs.
 */
function dumpErrors(int $totalNotQueued, array $errorLogIds): void
{
    ?>
    <ul>
        <li>Failed to queue <?php echo $totalNotQueued; ?> grade(s) for LMS
            resync.
        </li>
        <li>Please provide the following information in your support ticket:
        </li>
        <ul>
            <li>Failure log debugId
                list: <?php echo implode(', ', $errorLogIds); ?>
            </li>
        </ul>
    </ul>
    <?php
}

/**
 * Create an associative array of useful info for logging purposes.
 *
 * @param int $courseId The course ID for the assessment.
 * @param array $assessmentRecord The assessment record being queued for LMS sending.
 * @param string $assessmentName The assessment's name.
 * @param mixed $grade The grade being sent to the LMS.
 * @param mixed $score The student's score for the assessment. This should
 *                     be calculated from the student's "bestscores" data.
 * @return array An associative array of useful info.
 */
function createLogInfo(int $courseId, array $assessmentRecord,
                       string $assessmentName, $grade, $score): array
{
    $logInfo = array();

    // A user-exposed, searchable log ID. When provided by a user, search for
    // this ID. Hopefully, the surrounding log entries will be useful.
    $logInfo['debugId'] = preg_replace('/[^a-z0-9]/i', '', uniqid('', true));

    // Info about the user who is currently logged in and requesting an LMS resync.
    $logInfo['loggedInUserId'] = $GLOBALS['userid'];
    $logInfo['loggedInUsername'] = $GLOBALS['username'];
    $logInfo['loggedInUserRights'] = $GLOBALS['myrights'];

    // Info about the student and their assessment results.
    $logInfo['courseId'] = $courseId;
    $logInfo['assessmentName'] = $assessmentName;
    $logInfo['assessmentId'] = $assessmentRecord['aid'];
    $logInfo['assessmentVersion'] = $assessmentRecord['ver'];
    $logInfo['userId'] = $assessmentRecord['uid'];
    $logInfo['ltiSourcedId'] = $assessmentRecord['sourcedid'];
    $logInfo['score'] = $score;
    $logInfo['grade'] = $grade;

    return $logInfo;
}


function redirect_home(): void
{
    header('Location: ' . $GLOBALS['basesiteurl'] . "/ltihome.php");
    exit;
}

function displayError(string $message): void
{
    printf('<p>ERROR: %s</p>', Sanitize::encodeStringForDisplay($message));
    error_log('ERROR: ' . $message);
}

function isAdminUser(): bool
{
    if (!isset($GLOBALS['myrights'])) {
        return false;
    }
    return 100 == $GLOBALS['myrights'];
}