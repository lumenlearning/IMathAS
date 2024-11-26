<?php

use OHM\Includes\ReadReplicaDb;

require __DIR__ . '/../../init.php';
$placeinhead = "<script type=\"text/javascript\" src=\"$staticroot/ohm/admin/transfer_scores.js?v=101624\"></script>";
require_once __DIR__ . "/../../header.php";

if ($GLOBALS['myrights'] < 100) {
    echo "You're not authorized to view this page.";
    include(__DIR__ . '/../footer.php');
    exit;
}

// Run all queries on the read replica DB.
$GLOBALS['DBH'] = ReadReplicaDb::getPdoInstance();

/*
 * Breadcrumbs
 */

$curBreadcrumb = $GLOBALS['breadcrumbbase']
    . ' <a href="/admin/admin2.php">' . _('Admin') . '</a> &gt; Transfer Assessment Scores';
echo '<div class=breadcrumb>', $curBreadcrumb, '</div>';

/*
 * Sanitize all form input
 */

$course_id = Sanitize::onlyInt($_POST['course_id']) ?: null;
$source_student_uid = Sanitize::onlyInt($_POST['source_student_uid']) ?: null;
$target_student_uid = Sanitize::onlyInt($_POST['target_student_uid']) ?: null;
?>
    <h1>Transfer Assessment Scores (between two students)</h1>

    <p>
        Use this page to transfer all grades for one student to another
        student <em>within a single course ID</em>.
    </p>

    <p>
        <u>Notes</u>:
    </p>

    <ul>
        <li>Queries are run on the read replica DB.</li>
        <li>This page is <em>read-only</em>.</li>
        <ul>
            <li>YOU must copy/paste generated SQL from this page to make changes.</li>
        </ul>
    </ul>

    <form method="POST">
        <p>
            <label>Course ID:
                <input type="text"
                       value="<?php echo $course_id; ?>"
                       name="course_id"/>
            </label>
        </p>
        <p>
            <label>
                Source student user ID:
                <input type="text"
                       value="<?php echo $source_student_uid; ?>"
                       name="source_student_uid"/>
                (the student with scores)
            </label>
        </p>
        <p>
            <label>
                Target student user ID:
                <input type="text"
                       value="<?php echo $target_student_uid; ?>"
                       name="target_student_uid"/>
                (the student to transfer scores to)
            </label>
        </p>
        <p>
            <input type="submit" name="display_grades_button" value="Display Grades"/>
            <input type="submit" name="generate_sql_button" value="Generate SQL"/>
        </p>
    </form>
<?php

/*
 * Form validations
 */

if (empty($course_id) || empty($source_student_uid) || empty($target_student_uid)) {
    echo '<p>Please complete all required fields.</p>';
    require __DIR__ . '/../../footer.php';
    return;
}

$errors = [];

if (!isValidCourse($course_id)) {
    $errors[] = 'Invalid course ID.';
}
if (!isEnrolledUser($course_id, $source_student_uid)) {
    $errors[] = 'Source user ID is not enrolled in course ID ' . $course_id;
}
if (!isEnrolledUser($course_id, $target_student_uid)) {
    $errors[] = 'Target user ID is not enrolled in course ID ' . $course_id;
}

/*
 * Form input error reporting
 */

if (!empty($errors)) {
    echo '<p>Please correct the following error(s):</p>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>', $error, '</li>';
    }
    echo '</ul>';

    require __DIR__ . '/../../footer.php';
    return;
}

/*
 * Display student grades
 */

if (isset($_POST['display_grades_button'])) {
    displaySearchResults($course_id, $source_student_uid, $target_student_uid);
}

/*
 * Output SQL statements
 */

if (isset($_POST['generate_sql_button'])) {
    generateGradeUpdateSql($course_id, $source_student_uid, $target_student_uid);
}

require __DIR__ . '/../../footer.php';
return;

/*
 * Functions
 */

/**
 * Determine if a course ID is valid.
 *
 * @param int $courseId The course ID. (from imas_courses)
 * @return bool True if the course ID is valid. False if not.
 */
function isValidCourse(int $courseId): bool
{
    $query = 'SELECT 1 FROM imas_courses WHERE id = :courseId';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(['courseId' => $courseId]);

    return ($stm->rowCount() === 1);
}

/**
 * Determine if a user is enrolled in a course.
 *
 * @param int $courseId The course ID. (from imas_courses)
 * @param int $userId The student user ID. (from imas_users)
 * @return bool
 */
function isEnrolledUser(int $courseId, int $userId): bool
{
    $query = 'SELECT 1 FROM imas_students WHERE userid = :userId AND courseid = :courseId';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute([
        'userId' => $userId,
        'courseId' => $courseId,
    ]);

    return ($stm->rowCount() === 1);
}

/**
 * Display all assessment grades for two students in a course.
 *
 * @param int $courseId The course ID. (from imas_courses)
 * @param int $sourceStudentUid The source student user ID. (from imas_users)
 * @param int $targetStudentUid The target student user ID. (from imas_users)
 * @return void
 */
function displaySearchResults(int $courseId, int $sourceStudentUid, int $targetStudentUid): void
{
    $sourceUser = getUserDetails($sourceStudentUid);
    $targetUser = getUserDetails($targetStudentUid);

    ?>
    <p>
        Source user: <?php echo $sourceUser['name']; ?>
        - <?php echo $sourceUser['email']; ?>
        <br/>
        Target user: <?php echo $targetUser['name']; ?>
        - <?php echo $targetUser['email']; ?>
    </p>

    <div id="noConflicts">
        <h2 style="color: #00c200;">✅ No grade conflicts.</h2>
    </div>

    <div id="hasConflicts" style="display: none;">
        <h2 style="color: #ff3535;">⚠️ <span id="conflictCount">0</span> grade conflict(s).</h2>
    </div>

    <div id="noExceptions">
        <h2>ℹ️ No exceptions for source user.</h2>
    </div>

    <div id="hasExceptions" style="display: none;">
        <h2 style="color: #cc7400;">ℹ️ <span id="exceptionCount">0</span> exception(s) for source user.</h2>
    </div>

    <p>
        Times listed below grades are start times (ST) and last change (LC) times.
    </p>

    <table class="gb">
    <thead>
    <tr>
        <th>Assessment ID</th>
        <th><?php echo $sourceStudentUid ?> current<br/>grade (source)</th>
        <th><?php echo $targetStudentUid ?> current<br/>grade (target)</th>
        <th>Conflict?</th>
        <th>Assessment Name</th>
    </tr>
    </thead>
    <?php

    /*
     * Get grades.
     */

    $assessments = getCourseAssessments($courseId);
    $sourceUserGrades = getCourseGradesForStudent($courseId, $sourceStudentUid);
    $targetUserGrades = getCourseGradesForStudent($courseId, $targetStudentUid);

    /*
     * Get exceptions.
     */

    $assessmentIds = array_map(function ($assessment) {
        return $assessment['id'];
    }, $assessments);

    $sourceExceptions = getAllStudentExceptions($sourceStudentUid, $assessmentIds);
    $totalExceptions = count($sourceExceptions);

    $targetExceptions = getallStudentExceptions($targetStudentUid, $assessmentIds);

    /*
     * Display grades.
     */

    $totalConflicts = 0;
    $rowClass = 'odd';
    foreach ($assessments as $assessment) {
        $rowClass = $rowClass == 'even' ? 'odd' : 'even';
        $assessmentId = $assessment['id'];

        $sourceGrade = getGradeByAssessmentId($assessmentId, $sourceUserGrades);
        $targetGrade = getGradeByAssessmentId($assessmentId, $targetUserGrades);

        $isConflicted = '';
        if (!is_null($sourceGrade) && !is_null($targetGrade)) {
            $isConflicted = '<span style="color: #ff3535; font-weight: bold;">CONFLICT</span>';
            ++$totalConflicts;
        }

        $sourceData = formatGradeColumn($sourceGrade, $assessmentId, $sourceExceptions);
        $targetData = formatGradeColumn($targetGrade, $assessmentId, $targetExceptions);

        echo '<tr class="' . $rowClass . '" style="vertical-align: top;">';
        echo '<td>' . $assessmentId . '</td>';
        echo '<td>' . $sourceData . '</td>';
        echo '<td>' . $targetData . '</td>';
        echo '<td>' . $isConflicted . '</td>';
        echo '<td>' . $assessment['name'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    // This will allow for updating the DOM after we've counted
    // grade conflicts and exceptions.
    echo '<script>';
    echo 'var totalConflicts = ' . $totalConflicts . ';';
    echo 'var totalExceptions = ' . $totalExceptions . ';';
    echo '</script>';
}

function generateGradeUpdateSql(int $courseId, int $sourceStudentUid, int $targetStudentUid): void
{
    $assessments = getCourseAssessments($courseId);
    $sourceUserGrades = getCourseGradesForStudent($courseId, $sourceStudentUid);
    $targetUserGrades = getCourseGradesForStudent($courseId, $targetStudentUid);

    $errors = [];
    $generatedSql = [];
    $generatedLumenAdminCsv = [];
    $totalConflicts = 0;

    /*
     * Get all exceptions.
     */

    $assessmentIds = array_map(function ($assessment) {
        return $assessment['id'];
    }, $assessments);

    $exceptions = getAllStudentExceptions($sourceStudentUid, $assessmentIds);
    $totalExceptions = count($exceptions);

    /*
     * For Lumen Admin.
     */

    $oldEnrollment = getEnrollment($sourceStudentUid, $courseId);
    $newEnrollment = getenrollment($targetStudentUid, $courseId);

    $generatedLumenAdminCsv[] = "\n-- For Lumen Admin. (update ActivationCode records or EnrollmentLog events)\n";
    $generatedLumenAdminCsv[] = "course_id,old_user_id,new_user_id,old_enrollment_id,new_enrollment_id";
    $generatedLumenAdminCsv[] = sprintf('%d,%d,%d,%d,%d',
        $courseId, $sourceStudentUid, $targetStudentUid, $oldEnrollment['id'], $newEnrollment['id']);

    /*
     * Update assessment records and exceptions.
     */

    $generatedSql[] = "\n-- Update assessment records and exceptions.\n";
    foreach ($assessments as $assessment) {
        $assessmentId = $assessment['id'];
        $sourceGrade = getGradeByAssessmentId($assessmentId, $sourceUserGrades);
        $targetGrade = getGradeByAssessmentId($assessmentId, $targetUserGrades);

        $isConflicted = !is_null($sourceGrade) && !is_null($targetGrade);
        if ($isConflicted) {
            $errors[] = 'Skipping conflicted assessment ID: ' . $assessmentId;
            ++$totalConflicts;
            continue;
        }

        /*
         * Transfer exceptions to target user.
         *
         * We do this first because exceptions can exist without a
         * matching row in imas_assessment_records.
         *
         * Example: Student used a late pass for an assessment before
         *          starting the assessment.
         */
        foreach ($exceptions as $exception) {
            if ($exception['assessmentid'] == $assessmentId) {
                // The assessment ID is included here for visual aid during statement copy/paste/execution.
                $generatedSql[] = sprintf(
                    'UPDATE imas_exceptions SET userid = %d WHERE id = %d AND assessmentid = %d;',
                    $targetStudentUid, $exception['id'], $assessmentId
                );
            }
        }

        // Don't do anything else if we have no assessment record for
        // this assignment.
        if (is_null($sourceGrade)) {
            continue;
        }

        // Transfer grades to target user.
        $sql = sprintf('UPDATE imas_assessment_records
            SET userid = %d WHERE userid = %d AND assessmentid = %d;',
            $targetStudentUid, $sourceStudentUid, $assessmentId);

        // Strip whitespace. (the above string is easier to read during dev!)
        $sql = str_replace(PHP_EOL, '', $sql);
        $sql = preg_replace('/\s+/', ' ', $sql);
        $generatedSql[] = $sql;
    }

    /*
     * Update payment status.
     */

    $generatedSql[] = "\n-- Update payment status.\n";

    $sql = sprintf('UPDATE imas_students
        SET has_valid_access_code = %d WHERE userid = %d AND courseid = %d;',
        $oldEnrollment['has_valid_access_code'], $targetStudentUid, $courseId);

    // Strip whitespace.
    $sql = str_replace(PHP_EOL, '', $sql);
    $sql = preg_replace('/\s+/', ' ', $sql);
    $generatedSql[] = $sql;

    /*
     * Delete old enrollment.
     */

    $generatedSql[] = "\n-- Delete old enrollment.\n";

    $sql = sprintf('DELETE FROM imas_students
        WHERE userid = %d AND courseid = %d;', $sourceStudentUid, $courseId);

    // Strip whitespace.
    $sql = str_replace(PHP_EOL, '', $sql);
    $sql = preg_replace('/\s+/', ' ', $sql);
    $generatedSql[] = $sql;

    /*
     * Display errors
     */

    if (!empty($errors)) {
        echo '<h1 style="color: #ff3535;">Errors</h1>';
        echo '<ol>';
        foreach ($errors as $error) {
            echo '<li>', $error, '</li>';
        }
        echo '</ol>';
    }

    /*
     * Display conflict and exception counts.
     */

    if (0 == $totalConflicts) {
        echo '<h2 style="color: #00c200;">✅ No grade conflicts.</h2>';
    } else {
        echo '<h2 style="color: #ff3535;">⚠️ ' . $totalConflicts . ' grade conflict(s).</h2>';
    }

    if (0 == $totalExceptions) {
        echo '<h2>ℹ️ 0 exception(s) for source user.</h2>';
    } else {
        echo '<h2 style="color: #cc7400;">ℹ️ ' . $totalExceptions . ' exception(s) for source user.</h2>';
    }

    /*
     * Display Lumen Admin SQL
     */

    echo '<h1>Lumen Admin ActivationCode / EnrollmentLog</h1>';
    echo '<textarea style="width: 100%;" rows="5" readonly>';
    foreach ($generatedLumenAdminCsv as $csvline) {
        echo $csvline . "\n";
    }
    echo '</textarea>';


    /*
     * Display generated SQL
     */

    echo '<h1>Generated SQL</h1>';
    echo '<textarea style="width: 100%;" rows="30" readonly>';
    foreach ($generatedSql as $sql) {
        echo $sql . "\n";
    }
    echo '</textarea>';
}

/**
 * Get a user's course enrollment.
 *
 * @param int $userId The user's ID. (from imas_users)
 * @param int $courseId The course ID. (from imas_courses)
 * @return array The enrollment record.
 */
function getEnrollment(int $userId, int $courseId): array
{
    $query = 'SELECT id, has_valid_access_code
        FROM imas_students WHERE userid = :userId AND courseid = :courseId';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute([
        'userId' => $userId,
        'courseId' => $courseId,
    ]);
    return $stm->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get all exceptions for a student, given an array of assessment IDs.
 *
 * @param int $userId The user's ID. (from imas_users)
 * @param array $assessmentIds An array of assessment IDs. (from imas_assessments)
 * @return array All imas_exceptions rows found.
 */
function getAllStudentExceptions(int $userId, array $assessmentIds): array
{
    // PDO does not support named parameters for "WHERE IN ()" clauses. :(
    $placeholdersAsArray = array_map(fn($ids): string => '?', $assessmentIds);
    $placeholders = implode(',', $placeholdersAsArray);

    $query = "SELECT * FROM imas_exceptions
        WHERE userid = ? AND assessmentid IN ($placeholders);";

    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(array_merge([$userId], $assessmentIds));
    return $stm->fetchAll();
}

/**
 * Format text for display in a grade column.
 *
 * @param array|null $gradeData Grade data as an array, like:
 *                      ['score' => 40, 'lastchange' => 1728954146]
 * @param int $assessmentId The assessment ID.
 * @param array $allExceptions Exceptions as returned by getAllStudentExceptions().
 * @return string A string ready to display in a table cell.
 * @see getAllStudentExceptions()
 */
function formatGradeColumn(?array $gradeData,
                           int    $assessmentId,
                           array  $allExceptions
): string
{
    // Handle cases where a row in imas_assessment_records does not
    // exist, but we still want to display exception information.
    // Example: Student used a late pass before starting an assessment.
    if (empty($gradeData)) {
        $gradeData = [];
    }

    $exceptionsFound = 0;
    foreach ($allExceptions as $exception) {
        if ($exception['assessmentid'] == $assessmentId) {
            ++$exceptionsFound;
        }
    }

    $exceptionText = '';
    if (0 < $exceptionsFound) {
        $exceptionText = '<sup style="color: #cc7400;">e';
        if (1 < $exceptionsFound) {
            $exceptionText .= '(' . $exceptionsFound . ')';
        }
        $exceptionText .= '</sup>';
    }

    $score = $gradeData['score'] ?? '';
    $startTimestamp = empty($gradeData['starttime']) ? ''
        : 'ST: ' . date('Y-m-d g:i:s a', $gradeData['starttime']);
    $lastChangeTimestamp = empty($gradeData['lastchange']) ? ''
        : 'LC: ' . date('Y-m-d g:i:s a', $gradeData['lastchange']);

    // If we have an exception but no score or assessment data, display
    // something in place of a score because just "e" looks weird.
    if ('' == $score && 0 < $exceptionsFound) {
        $score = '<span style="color: #0b8080;">(no score or assess record)</span>';
    }

    $columnString = $score . $exceptionText . '<br/>'
        . '<span style="color: #696969; font-size: 0.8em; white-space: nowrap;">'
        . $startTimestamp
        . '<br/>'
        . $lastChangeTimestamp
        . '</span>';
    return $columnString;
}

/**
 * Get grade data for a specific assessment from an array of grade data.
 *
 * @param int $assessmentId The assessment ID to get the grade for.
 * @param array $allGrades An associative array of grades as returned by getCourseGradesForStudent().
 *                 Example: ['assessmentid' => 42, 'score' => 90, 'lastchange' => 1728954146]
 * @return array|null The grade. Null if none found.
 * @see getCourseGradesForStudent()
 */
function getGradeByAssessmentId(int $assessmentId, array $allGrades): ?array
{
    foreach ($allGrades as $grade) {
        if ($grade['assessmentid'] == $assessmentId) {
            return $grade;
        }
    }
    return null;
}

/**
 * Get a user's imas_users record.
 *
 * @param int $userId The user's ID. (from imas_users)
 * @return array|null The user's data. Null if not found.
 */
function getUserDetails(int $userId): ?array
{
    $query = "SELECT id, CONCAT(FirstName, ' ', LastName) AS name, email
        FROM imas_users WHERE id = :userId";
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(['userId' => $userId]);
    return $stm->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get all assessment names in a course.
 *
 * @param int $courseId The course ID. (from imas_courses)
 * @return array The entire result set of assessment names.
 */
function getCourseAssessments(int $courseId): array
{
    $query = 'SELECT id, name FROM imas_assessments
        WHERE courseid = :courseId ORDER BY id';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(['courseId' => $courseId]);
    return $stm->fetchAll();
}

/**
 * Get all assessment scores for a user in a course.
 *
 * @param int $courseId The course ID. (from imas_courses)
 * @param int $studentUserId The student's user ID. (from imas_users)
 * @return array The entire result set of assessment scores for the user.
 */
function getCourseGradesForStudent(int $courseId, int $studentUserId): array
{
    $query = 'SELECT
        ar.assessmentid,
        ar.score,
        ar.starttime,
        ar.lastchange
    FROM imas_assessment_records AS ar
        JOIN imas_assessments AS a ON a.id = ar.assessmentid
        JOIN imas_courses AS c ON c.id = a.courseid
    WHERE c.id = :courseId
    	AND ar.userid = :userId';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute([
        'courseId' => $courseId,
        'userId' => $studentUserId,
    ]);
    return $stm->fetchAll();
}