<?php

use OHM\Includes\ReadReplicaDb;

require __DIR__ . '/../../init.php';
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
    . ' <a href="/admin/admin2.php">' . _('Admin') . '</a> &gt; Add ltiuser to new org';
echo '<div class=breadcrumb>', $curBreadcrumb, '</div>';

/*
 * Sanitize all form input
 */

$course_id = Sanitize::onlyInt($_POST['course_id']) ?: null;
$old_org = Sanitize::simpleASCII($_POST['old_org']) ?: null;
$new_org = Sanitize::simpleASCII($_POST['new_org']) ?: null;
?>

    <h1>Add ltiuser to new org</h1>

    <p>
        Use this page to insert new rows in <code>imas_ltiusers</code> for
        users who have <em>NOT</em> been duplicated yet, but with the new
        <code>org</code> value.
    </p>

    <p>
        <u>Use case</u>:
    </p>

    <ul>
        <li>New LTI credentials were added for OHM, causing students to launch
            into OHM with all of the following in <code>imas_ltiusers</code>:
        </li>
        <ul>
            <li>The same <code>ltiuserid</code>.</li>
            <li>A different (new) <code>org</code> value.</li>
        </ul>
        <li>Resulting in duplicate student users in LTI courses in OHM.</li>
    </ul>

    <p>
        See also:
        <a href="https://lumenlearning.atlassian.net/browse/SUP-1666"
           target="_blank">SUP-1666</a>
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
                Old org:
                <input type="text"
                       value="<?php echo $old_org; ?>"
                       name="old_org"
                       size="60"/>
                (<code>imas_ltiusers.org</code> column value)
            </label>
        </p>
        <p>
            <label>
                New org:
                <input type="text"
                       value="<?php echo $new_org; ?>"
                       name="new_org"
                       size="60"/>
                (<code>imas_ltiusers.org</code> column value)
            </label>
        </p>
        <p>
            <input type="submit" name="display_users_button" value="Display Users"/>
            <input type="submit" name="generate_sql_button" value="Generate SQL"/>
        </p>
    </form>

<?php

/*
 * Form validations
 */

if (empty($course_id) || empty($old_org) || empty($new_org)) {
    echo '<p>Please complete all required fields.</p>';
    require __DIR__ . '/../../footer.php';
    return;
}

$errors = [];

if (!isValidCourse($course_id)) {
    $errors[] = 'Invalid course ID.';
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

if (isset($_POST['display_users_button'])) {
    displayUsers($course_id, $old_org, $new_org);
}

/*
 * Output SQL statements
 */

if (isset($_POST['generate_sql_button'])) {
    generateSql($course_id, $old_org, $new_org);
}

require __DIR__ . '/../../footer.php';
return;

/*
 * Functions
 */

function displayUsers(int $courseId, string $oldOrg, string $newOrg): void
{
    /*
     * Get total enrollments for the course, ignoring duplicates.
     */

    $totalRealEnrollments = getTotalEnrollmentsIgnoreDupes($courseId);

    /*
     * Get users that will be skipped -- because they currently have
     * two records, one for each LTI credentials.
     */

    $usersWithDupes = getLtiUsersWithDuplicateOrgs($courseId);
    $totalUsersWithDupes = count($usersWithDupes);

    /*
     * Get users that will be skipped -- because they are already
     * using new LTI credentials.
     */

    $usersUsingNewLtiCreds = getLtiUsersByOrgWithoutDuplicates(
        $courseId, $newOrg);
    $totalUsersUsingNewLtiCreds = count($usersUsingNewLtiCreds);

    /*
     * Get users we want to INSERT new rows for -- because they
     * haven't used * new LTI credentials yet.
     */

    $usersUsingOldLtiCreds = getLtiUsersByOrgWithoutDuplicates(
        $courseId, $oldOrg);
    $totalUsersUsingOldLtiCreds = count($usersUsingOldLtiCreds);

    $totalUsersFound = $totalUsersWithDupes
        + $totalUsersUsingNewLtiCreds
        + $totalUsersUsingOldLtiCreds;

    ?>
    <style>
        .count-column {
            text-align: right;
            vertical-align: top;
        }

        .validation-step {
            color: #cc7400;
            font-weight: bold;
        }

        .ltiuserRow {
            white-space: nowrap;
        }
    </style>

    <h1>Student Counts</h1>

    <table class="gb">
        <thead>
        <tr>
            <th>Count</th>
            <th></th>
        </tr>
        </thead>
        <tr>
            <td class="count-column"><?php echo $totalUsersWithDupes; ?></td>
            <td>Users with TWO entries per <code>ltiuserid</code>
                in <code>imas_ltiusers</code>.
            </td>
        </tr>
        <tr>
            <td class="count-column"><?php echo $totalUsersUsingNewLtiCreds; ?></td>
            <td>Users with ONE entry per <code>ltiuserid</code>
                in <code>imas_ltiusers</code>. (new LTI creds)
            </td>
        </tr>
        <tr>
            <td class="count-column"><?php echo $totalUsersUsingOldLtiCreds; ?></td>
            <td>Users with ONE entry per <code>ltiuserid</code>
                in <code>imas_ltiusers</code>. (old LTI creds)
            </td>
        </tr>
        <tr>
            <td>
                <hr/>
            </td>
            <td>
                <hr/>
            </td>
        </tr>
        <tr>
            <td class="count-column"><?php echo $totalUsersFound; ?></td>
            <td>Total users found (SUM of above rows) --
                <span class="validation-step">VALIDATION STEP 1 of 3</span>
            </td>
        </tr>
        <tr>
            <td class="count-column"><?php echo $totalRealEnrollments; ?></td>
            <td>Total enrollments (obtained by separate SQL statement) --
                <span class="validation-step">VALIDATION STEP 2 of 3</span>
            </td>
        </tr>
        <tr>
            <td class="count-column">☝️</td>
            <td>The above two counts must be equal. --
                <span class="validation-step">VALIDATION STEP 3 of 3</span>
            </td>
        </tr>
    </table>

    <h1>User Lists</h1>

    <h2>Users using only OLD LTI credentials</h2>

    <ul>
        <li>SQL will be generated to update these users'
            <code>imas_ltiusers.org</code> column with the new org value.
        </li>
    </ul>

    <table class="gb">
    <thead>
    <tr>
        <th>#</th>
        <th>OHM User ID</th>
        <th>Student Name</th>
        <th>imas_ltiusers.id</th>
        <th>imas_ltiusers.ltiuserid</th>
        <th>org</th>
    </tr>
    </thead>
    <?php

    $rowClass = 'odd';
    foreach ($usersUsingOldLtiCreds as $idx => $oldCredsUser) {
        $rowClass = $rowClass == 'even' ? 'odd' : 'even';
        echo '<tr class="ltiuserRow ' . $rowClass . '">';
        echo '<td>' . $idx + 1 . '</td>';
        echo '<td>' . $oldCredsUser['userid'] . '</td>';
        echo '<td>' . $oldCredsUser['student_name'] . '</td>';
        echo '<td>' . $oldCredsUser['id'] . '</td>';
        echo '<td>' . $oldCredsUser['ltiuserid'] . '</td>';
        echo '<td>' . $oldCredsUser['org'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    ?>
    <h2>Users using only NEW LTI credentials</h2>

    <table class="gb">
        <thead>
        <tr>
            <th>#</th>
            <th>OHM User ID</th>
            <th>Student Name</th>
            <th>imas_ltiusers.id</th>
            <th>imas_ltiusers.ltiuserid</th>
            <th>org</th>
        </tr>
        </thead>
    <?php

    $rowClass = 'odd';
    foreach ($usersUsingNewLtiCreds as $idx => $newCredsUser) {
        $rowClass = $rowClass == 'even' ? 'odd' : 'even';
        echo '<tr class="ltiuserRow ' . $rowClass . '">';
        echo '<td>' . $idx + 1 . '</td>';
        echo '<td>' . $newCredsUser['userid'] . '</td>';
        echo '<td>' . $newCredsUser['student_name'] . '</td>';
        echo '<td>' . $newCredsUser['id'] . '</td>';
        echo '<td>' . $newCredsUser['ltiuserid'] . '</td>';
        echo '<td>' . $newCredsUser['org'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

function generateSql(int $courseId, string $oldOrg, string $newOrg): void
{
    $ltiusers = getLtiUsersByOrgWithoutDuplicates($courseId, $oldOrg);

    echo '<h1>Generated SQL</h1>';
    echo '<textarea style="width: 100%;" rows="30" readonly>';
    foreach ($ltiusers as $idx => $ltiuser) {
        $sql = sprintf(
            'INSERT INTO imas_ltiusers
                    (userid, ltiuserid, org) VALUES (%d, "%s", "%s");',
            $ltiuser['userid'],
            $ltiuser['ltiuserid'],
            $newOrg
        );
        // Strip whitespace. It was there for easier reading during dev.
        $sql = str_replace(PHP_EOL, '', $sql);
        $sql = preg_replace('/\s+/', ' ', $sql);

        echo sprintf('-- (%d) %s' . "\n", $idx + 1, $ltiuser['student_name']);
        echo $sql . "\n\n";
    }
    echo '</textarea>';
}

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
 * Get a count of total unique users enrolled in a course, ignoring
 * duplicates.
 *
 * @param int $courseId The course ID. (from imas_courses)
 * @return int The total number of students enrolled in the course.
 */
function getTotalEnrollmentsIgnoreDupes(int $courseId): int
{
    $query = "SELECT COUNT(*) AS total_enrolled FROM (
            SELECT
                u.id AS userid,
                lu.ltiuserid,
                COUNT(lu.ltiuserid) AS org_count,
                lu.org,
                CONCAT(u.LastName, ', ', u.FirstName) AS student_name
            FROM imas_ltiusers AS lu
                JOIN imas_users AS u ON u.id = lu.userid
                JOIN imas_students AS e ON e.userid = lu.userid
            WHERE e.courseid = :courseId
            GROUP BY (lu.ltiuserid)
        ) AS s1";
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(['courseId' => $courseId]);

    return $stm->fetchColumn(0);
}

/**
 * Get all users from imas_ltiusers by course ID who have TWO rows
 * for their imas_ltiusers.ltiuser column.
 *
 * @param int $courseId The course ID. (from imas_courses)
 * @return array The full result set returned from the DB.
 */
function getLtiUsersWithDuplicateOrgs(int $courseId): array
{
    $query = "SELECT * FROM (
            SELECT
                u.id AS userid,
                lu.ltiuserid,
                COUNT(lu.ltiuserid) AS org_count,
                lu.org,
                CONCAT(u.LastName, ', ', u.FirstName) AS student_name
            FROM imas_ltiusers AS lu
                JOIN imas_users AS u ON u.id = lu.userid
                JOIN imas_students AS e ON e.userid = lu.userid
            WHERE e.courseid = :courseId
            GROUP BY (lu.ltiuserid)
        ) AS s1
        WHERE org_count > 1";
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(['courseId' => $courseId]);

    return $stm->fetchAll();
}

/**
 * Get all users from imas_ltiusers by course and org, who have only
 * ONE row for their imas_ltiusers.ltiuser column.
 *
 * @param int $courseId The course ID. (from imas_courses)
 * @param string $org The org string.
 * @return array The full result set returned from the DB.
 */
function getLtiUsersByOrgWithoutDuplicates(int $courseId, string $org): array
{
    $query = "SELECT s1.* FROM (
            SELECT
                lu.id,
                u.id AS userid,
                lu.ltiuserid,
                COUNT(lu.ltiuserid) AS org_count,
                lu.org,
                CONCAT(u.LastName, ', ', u.FirstName) AS student_name
            FROM imas_ltiusers AS lu
                JOIN imas_users AS u ON u.id = lu.userid
                JOIN imas_students AS e ON e.userid = lu.userid
            WHERE e.courseid = :courseId
            GROUP BY (lu.ltiuserid)
        ) AS s1
        WHERE org_count = 1
            AND org = :org
        ORDER BY student_name, org";
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute([
        'courseId' => $courseId,
        'org' => $org,
    ]);

    return $stm->fetchAll();
}