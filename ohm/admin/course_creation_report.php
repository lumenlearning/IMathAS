<?php

use OHM\Includes\ReadReplicaDb;

require __DIR__ . '/../../init.php';

$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/DatePicker.js\"></script>\n";
$placeinhead .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"course_creation_report.css\">\n";
$placeinhead .= "<link title='lux' rel=\"stylesheet\" type=\"text/css\" href=\"https://lux.lumenlearning.com/use-lux/1.0.2/lux-components.min.css\">\n";
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
        . ' <a href="/admin/admin2.php">' . _('Admin')
        . '</a> &gt; Course creation report';
echo '<div class=breadcrumb>', $curBreadcrumb, '</div>';

/*
 * Form handling
 */

$startDate = getDateTime($_POST['startDate']);

if ('generate_report' == $_POST['action']) {
    generateReport($startDate);
} else {
    displayForm($startDate);
}

/*
 * Functions
 */

/**
 * Display the form used to generate reports.
 *
 * @param DateTime $startDate The starting date for reports.
 * @return void
 */
function displayForm(DateTime $startDate): void
{
    ?>
    <h1>Course creation reports</h1>

    <p>
        This generates CSV reports of how courses were created. Specifically, by
        one of the following methods:
    </p>

    <ul>
        <li>Created from a blank course</li>
        <li>Copied from a template course</li>
        <li>Copied a non-template course</li>
    </ul>

    <p>
        Note: This will take some time and places a load on the (replica) DB. Please wait for it to complete.
    </p>

    <div class="lux-component">
        <form method="POST" class="lux-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="generate_report"/>
            <div>
                <label for="startDate" class="form-label">Report on courses created since:</label>
                <input id="startDate"
                       name="startDate"
                       type="text"
                       class="form-input has-icon icon--suffix icon--calendar"
                       style="width: 8.5em;"
                       onClick="displayDatePicker('startDate', this); return false"
                       value="<?php echo $startDate->format('m/d/Y'); ?>"/>
            </div>
            <button id="generate_report_form_submit_button"
                    name="submitbtn"
                    type="submit"
                    class="button button--primary u-margin-vertical-sm"
                    onClick="this.form.submit(); this.disabled=true; this.innerHTML='Generating reports, please wait...';"
                    value="Submit">Generate report
            </button>
        </form>
    </div>
    <?php
}

/**
 * Convert a date in string format to a DateTime object.
 *
 * If no date string is provided, the current date is used.
 * If no time is provided, the start of the day is used.
 *
 * @param string|null $unsafeDateString A date/time string.
 * @return DateTime
 */
function getDateTime(?string $unsafeDateString): DateTime
{
    if (empty($unsafeDateString)) {
        $dt = DateTime::createFromFormat('U', time());
        $dt->setTime(0, 0, 0);
    } else {
        $unixtime = strtotime($unsafeDateString);
        $dt = DateTime::createFromFormat('U', $unixtime);
    }

    return $dt;
}

/**
 * Generate reports of courses by creation method.
 *
 * @param DateTime $startDate Get courses created after this time.
 * @return void
 */
function generateReport(DateTime $startDate): void
{
    $templateCourseIds = getTemplateCourseIds();

    printf('<p>Searched <span class="outsideDateRange">all</span> available courses for template courses. Found %d.'
            . ' (includes all template courses <span class="outsideDateRange">outside</span> of your specified date range)</p>',
            $templateCourseIds['totalTemplateCourseCount']);

    echo '<ul>';
    printf("<li>Loaded %d global template course IDs.</li>\n", count($templateCourseIds['globalTemplateCourseIds']));
    printf("<li>Loaded %d non-global template course IDs.</li>\n", count($templateCourseIds['nonGlobalTemplateCourseIds']));
    printf("<li>Memory used by this process up to this point: %s</li>\n", number_format(memory_get_usage()));
    echo '</ul>';

    echo '<hr/>';

    echo '<h1>Beginning report generation.</h1>';

    printf('<p>Generating reports for courses created since: <span id="specifiedDateRange">%s</span></p>', $startDate->format('Y-m-d @ H:i:s T'));
    echo '<p>The numbers below are <span class="insideDateRange">within</span> your specified date range.</p>';
    echo '<p>(scroll down for CSV downloads)</p>';

    generateReportCsvFiles($startDate, $templateCourseIds['globalTemplateCourseIds'], $templateCourseIds['nonGlobalTemplateCourseIds']);
}

/**
 * Get all global template course IDs.
 *
 * This will be used for lookups when generating the course creation report.
 *
 * @return array An associative array of global and non-global template course IDs.
 */
function getTemplateCourseIds(): array
{
    $query = 'SELECT id,istemplate FROM imas_courses WHERE istemplate > 0';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute();

    $totalCourses = $stm->rowCount();

    $globalTemplateCourseIds = [];
    $nonGlobalTemplateCourseIds = [];

    while ($dbRow = $stm->fetch(PDO::FETCH_ASSOC)) {
        // This follows the logic from admin/coursebrowser.php.
        if (
                $dbRow['istemplate'] & 2 // template for user's group
                || $dbRow['istemplate'] & 32 // template for user's super-group
        ) {
            $nonGlobalTemplateCourseIds[] = $dbRow['id'];
        } elseif ($dbRow['istemplate'] & 1) {
            $globalTemplateCourseIds[] = $dbRow['id'];
        } else {
            // Any course marked as a template where istemplate&1 is false
            // is not a global template course. This includes group templates
            // super-group templates, and contributed templates.
            $nonGlobalTemplateCourseIds[] = $dbRow['id']; // "contributed course"
        }
    }

    return [
            'totalTemplateCourseCount' => $totalCourses,
            'globalTemplateCourseIds' => $globalTemplateCourseIds,
            'nonGlobalTemplateCourseIds' => $nonGlobalTemplateCourseIds
    ];
}

/**
 * Generate CSV files reporting course creation counts by creation method.
 *
 * @param DateTime $startDate Get courses created after this time.
 * @param array $globalTemplateCourseIds An array of global template course IDs.
 * @param array $nonGlobalTemplateCourseIds An array of non-global template course IDs.
 * @return void
 */
function generateReportCsvFiles(DateTime $startDate, array $globalTemplateCourseIds, array $nonGlobalTemplateCourseIds): void
{
    $startUnixtime = $startDate->getTimestamp();

    $csvFileBlankTemplateFilename = 'courses_created_from_blank_template.csv';
    $csvFileGlobalTemplateCopyFilename = 'courses_created_from_global_template_copy.csv';
    $csvFileNonGlobalTemplateCopyFilename = 'courses_created_from_contributed_course_copy.csv';
    $csvFileNonTemplateCourseCopyFilename = 'courses_created_from_normal_course_copy.csv';

    // OHM's load balancers currently use sticky sessions. If/when this changes, we should
    // save files to S3 instead. These files are not needed after downloaded by users.
    $csvFileBlankTemplateFh = fopen(__DIR__ . '/../../filestore/' . $csvFileBlankTemplateFilename, 'w');
    $csvFileGlobalTemplateCopyFh = fopen(__DIR__ . '/../../filestore/' . $csvFileGlobalTemplateCopyFilename, 'w');
    $csvFileNonGlobalTemplateCopyFh = fopen(__DIR__ . '/../../filestore/' . $csvFileNonGlobalTemplateCopyFilename, 'w');
    $csvFileNonTemplateCourseCopyFh = fopen(__DIR__ . '/../../filestore/' . $csvFileNonTemplateCourseCopyFilename, 'w');

    // Courses created from blank templates have no ancestry, so we don't need those columns here.
    $csvHeadersForDbColumns = ['course_id', 'course_name', 'ancestors', 'level', 'group_name'];
    fputcsv($csvFileBlankTemplateFh, $csvHeadersForDbColumns);

    $headersForCourseCopies = array_merge($csvHeadersForDbColumns, [
            'ancestry_has_global_template_ids', 'ancestry_has_non_global_template_ids',
            'first_ancestor_course_id', 'last_ancestor_course_id',
            'all_global_template_course_ids_in_ancestry', 'all_non_global_template_course_ids_in_ancestry']);
    fputcsv($csvFileGlobalTemplateCopyFh, $headersForCourseCopies);
    fputcsv($csvFileNonGlobalTemplateCopyFh, $headersForCourseCopies);
    fputcsv($csvFileNonTemplateCourseCopyFh, $headersForCourseCopies);

    $query = 'SELECT
    c.id AS course_id,
    c.name AS course_name,
    c.ancestors,
    c.level,
    g.name AS group_name
FROM imas_courses AS c
    JOIN imas_users AS u ON u.id = c.ownerid
    JOIN imas_groups AS g ON g.id = u.groupid
WHERE c.created_at >= :startUnixtime
';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(['startUnixtime' => $startUnixtime]);

    $totalCoursesInDateRange = $stm->rowCount();

    $totalFromBlankTemplates = 0;
    $totalFromGlobalTemplates = 0;
    $totalFromNonGlobalTemplates = 0;
    $totalFromNonTemplateCourseCopies = 0;
    $totalNonTemplateCopiesWithTemplatesInAncestry = 0;
    $totalNonTemplateCopiesWithoutTemplatesInAncestry = 0;
    $totalNonTemplateCopiesWithNoAncestors = 0;

    while ($dbRow = $stm->fetch(PDO::FETCH_ASSOC)) {
        $courseId = $dbRow['course_id'];
        $ancestors = $dbRow['ancestors'];

        // No ancestors
        if ('' == $ancestors) {
            fputcsv($csvFileBlankTemplateFh, $dbRow);
            $totalFromBlankTemplates++;
            continue;
        }

        $commaIdx = strpos($ancestors, ',');

        // Single ancestor
        if (!$commaIdx) {
            if (in_array($courseId, $globalTemplateCourseIds)) {
                // Copied from a global template course.
                fputcsv($csvFileGlobalTemplateCopyFh, array_merge($dbRow, ['YES', '', $ancestors, $ancestors, $ancestors, '']));
                $totalFromGlobalTemplates++;
            } elseif (in_array($courseId, $nonGlobalTemplateCourseIds)) {
                // Copied from a non-global template course.
                fputcsv($csvFileNonGlobalTemplateCopyFh, array_merge($dbRow, ['', 'YES', $ancestors, $ancestors, '', $ancestors]));
                $totalFromNonGlobalTemplates++;
            } else {
                // Copied from a normal (non-template) course.
                fputcsv($csvFileNonTemplateCourseCopyFh, array_merge($dbRow, ['', '', $ancestors, $ancestors, '', '']));
                $totalFromNonTemplateCourseCopies++;
                $totalNonTemplateCopiesWithNoAncestors++;
            }
            continue;
        }

        /*
         * Multiple ancestors
         */

        $ancestorList = explode(',', $ancestors);
        $hasGlobalTemplateAncestor = '';
        $hasNonGlobalTemplateAncestor = '';

        // Get all template course IDs from ancestor list
        $globalTemplateAncestorsFound = [];
        $nonGlobalTemplateAncestorsFound = [];
        foreach ($ancestorList as $ancestor) {
            if (in_array($ancestor, $globalTemplateCourseIds)) {
                $globalTemplateAncestorsFound[] = $ancestor;
                if (empty($hasGlobalTemplateAncestor)) $hasGlobalTemplateAncestor = 'YES';
            }
            if (in_array($ancestor, $nonGlobalTemplateCourseIds)) {
                $nonGlobalTemplateAncestorsFound[] = $ancestor;
                if (empty($hasNonGlobalTemplateAncestor)) $hasNonGlobalTemplateAncestor = 'YES';
            }
        }

        // Build up fields for csv line
        $oldestAncestor = $ancestorList[array_key_last($ancestorList)];
        $mostRecentAncestor = $ancestorList[0];
        $csvData = array_merge($dbRow,
                [
                        $hasGlobalTemplateAncestor,
                        $hasNonGlobalTemplateAncestor,
                        $oldestAncestor,
                        $mostRecentAncestor,
                        implode(',', $globalTemplateAncestorsFound),
                        implode(',', $nonGlobalTemplateAncestorsFound)
                ]
        );

        // Which type of course is the most recent ancestor?
        if (in_array($mostRecentAncestor, $globalTemplateCourseIds)) {
            // Course was copied from a global template course.
            $totalFromGlobalTemplates++;
            fputcsv($csvFileGlobalTemplateCopyFh, $csvData);
        } elseif (in_array($mostRecentAncestor, $nonGlobalTemplateCourseIds)) {
            // Course was copied from a non-global template course.
            $totalFromNonGlobalTemplates++;
            fputcsv($csvFileNonGlobalTemplateCopyFh, $csvData);
        } else {
            // Course was copied from a normal (non-templatee) course.
            $totalFromNonTemplateCourseCopies++;
            fputcsv($csvFileNonTemplateCourseCopyFh, $csvData);

            if ($hasGlobalTemplateAncestor) {
                $totalNonTemplateCopiesWithTemplatesInAncestry++;
            }
            if ($hasNonGlobalTemplateAncestor) {
                $totalNonTemplateCopiesWithoutTemplatesInAncestry++;
            }
            if (!$hasGlobalTemplateAncestor && !$hasNonGlobalTemplateAncestor) {
                $totalNonTemplateCopiesWithoutTemplatesInAncestry++;
            }
        }
    }

    fclose($csvFileBlankTemplateFh);
    fclose($csvFileGlobalTemplateCopyFh);
    fclose($csvFileNonGlobalTemplateCopyFh);
    fclose($csvFileNonTemplateCourseCopyFh);

    echo '<ul>';
    printf("<li>%d total courses created.</li>\n", $totalCoursesInDateRange);
    printf("<li>%d courses created by starting with a blank course.</li>\n", $totalFromBlankTemplates);
    printf("<li>%d courses created by copying a global template course.</li>\n", $totalFromGlobalTemplates);
    printf("<li>%d courses created by copying a non-global template course. This includes:</li>\n", $totalFromNonGlobalTemplates);
    ?>
    <ul>
        <li>A course owner's group template course.</li>
        <li>A course owner's super-group template course.</li>
        <li>A contributed course.</li>
    </ul>
    <?php
    printf("<li>%d courses created by copying a normal (non-template) course.</li>\n", $totalFromNonTemplateCourseCopies);
    echo '<ul>';
    echo '<li>Courses may contain both global and non-global templates in their ancestry. The following two counts may overlap.</li>';
    echo '<ul>';
    printf("<li>%d contain global template courses in their ancestry.</li>", $totalNonTemplateCopiesWithTemplatesInAncestry);
    printf("<li>%d contain non-global template courses in their ancestry.</li>", $totalNonTemplateCopiesWithoutTemplatesInAncestry);
    echo '</ul>';
    printf("<li>%d have no ancestors.</li>", $totalNonTemplateCopiesWithNoAncestors);
    echo '</ul>';
    printf("<li>Memory used by this process up to this point: %s</li>\n", number_format(memory_get_usage()));
    echo '</ul>';

    ?>
    <div class="boringInlineBorder">
        <ul>
            <li>How OHM identifies template courses: (first match wins; there is no overlap)</li>
            <ol>
                <li>Is the course a template course? If yes:</li>
                <ol>
                    <li>Is the course a template course for the user's group?</li>
                    <li>Is the course a template course for the user's super-group?</li>
                    <li>Is the course a global template course?</li>
                    <li>If none of the above matched, then it's a "contributed course" template.</li>
                </ol>
                <li>If the course is not a template course of any kind, it is not listed when clicking
                    "Copy a template course" when creating a new course.
                </li>
            </ol>
            <li>Courses may also be created by:</li>
            <ul>
                <li>Starting with a blank course.</li>
                <li>Copying a non-template course.</li>
            </ul>
        </ul>
    </div>
    <?php

    echo '<h2>Report downloads:</h2>';

    echo '<ul>';
    printf('<li><a href="%s/filestore/%s">%s</a></li>',
            $GLOBALS['basesiteurl'], $csvFileBlankTemplateFilename, $csvFileBlankTemplateFilename);
    printf('<li><a href="%s/filestore/%s">%s</a></li>',
            $GLOBALS['basesiteurl'], $csvFileGlobalTemplateCopyFilename, $csvFileGlobalTemplateCopyFilename);
    printf('<li><a href="%s/filestore/%s">%s</a></li>',
            $GLOBALS['basesiteurl'], $csvFileNonGlobalTemplateCopyFilename, $csvFileNonGlobalTemplateCopyFilename);
    printf('<li><a href="%s/filestore/%s">%s</a></li>',
            $GLOBALS['basesiteurl'], $csvFileNonTemplateCourseCopyFilename, $csvFileNonTemplateCourseCopyFilename);
    echo '</ul>';
}