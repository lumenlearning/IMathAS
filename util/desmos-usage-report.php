<?php

use Course\Includes\ContentTracker;
use Desmos\Models\DesmosItem;

require_once(__DIR__ . '/../init.php');

if ($myrights < 100) {
    echo "You don't permission to view this page.";
    exit;
}

$pagetitle = 'Desmos Usage Overview';
$curBreadcrumb = $breadcrumbbase
    . ' <a href="../admin/userreports.php">' . _('User Reports') . '</a> &gt; '
    . $pagetitle;

$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/DatePicker.js\"></script>\n";
$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/tablesorter.js\"></script>\n";
$placeinhead .= "<link title='lux' rel=\"stylesheet\" type=\"text/css\" href=\"https://lux.lumenlearning.com/use-lux/1.0.0/lux-components.min.css\">\n";
require("../header.php");
echo '<div class=breadcrumb>', $curBreadcrumb, '</div>';
echo '<div id="headeradmin" class="pagetitle"><h1>', $pagetitle, '</h1></div>';

// FIXME: Remove this after creating indexes on imas_content_track in prod.
echo "<p style='color: red;'>This report is DISABLED in production until indexes are created. See OHM-119.<br/>";
echo "Current env: " . getenv('CONFIG_ENV') . "</p>";
if ('production' == getenv('CONFIG_ENV')) {
    exit;
}

$startDate = getDateTime($_POST['startDate'], false);
$endDate = getDateTime($_POST['endDate'], true);
outputDateForm($startDate, $endDate);

if ('generate_report' == $_POST['action']) {
    generateReport($startDate, $endDate);
}

require("../footer.php");


/**
 * Convert a date in string format to a DateTime object.
 *
 * If no date string is provided, the current date is used.
 * If no time is provided, the start of the day is used.
 *
 * @param string|null $unsafeDateString A date/time string.
 * @param bool $isEndDate True if this date should have an end of day time.
 * @return DateTime
 */
function getDateTime(?string $unsafeDateString, bool $isEndDate): DateTime
{
    if (empty($unsafeDateString)) {
        $dt = DateTime::createFromFormat('U', time());
        $dt->setTime(0, 0, 0);
    } else {
        $unixtime = strtotime($unsafeDateString);
        $dt = DateTime::createFromFormat('U', $unixtime);
    }

    if (true === $isEndDate) {
        $dt->setTime(23, 59, 59);
    }

    return $dt;
}


/**
 * Output a form allowing the user to select a date range for the report.
 *
 * @param DateTime $startDate The report start date.
 * @param DateTime $endDate The report end date.
 */
function outputDateForm(DateTime $startDate, DateTime $endDate): void
{
    ?>
    <div class="lux-component">
        <form method="POST" class="form lux-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="generate_report"/>
            <div class="form-group">
                <div class="controls">
                    <label for="startDate" class="form-label">Start Date:</label>
                    <input id="startDate"
                        name="startDate"
                        type="text"
                        class="form-input has-icon icon--suffix icon--calendar"
                        onClick="displayDatePicker('startDate', this); return false"
                        value="<?php echo $startDate->format('m/d/Y'); ?>"/>
                </div>
                <div class="controls">
                    <label for="endDate" class="form-label">End Date:</label>
                    <input id="endDate"
                        name="endDate"
                        type="text"
                        class="form-input has-icon icon--suffix icon--calendar"
                        onClick="displayDatePicker('endDate', this); return false"
                        value="<?php echo $endDate->format('m/d/Y'); ?>"/>
                </div>
                <div class="controls">
                    <button id="desmos_form_submit_button"
                            name="submitbtn"
                            type="submit"
                            class="button button--primary"
                            value="Submit">Update
                    </button>
                </div>
            </div>
        </form>
    </div>
    <?php
}


/**
 * Generate and output the report.
 *
 * @param DateTime $startDate
 * @param DateTime $endDate
 */
function generateReport(DateTime $startDate, DateTime $endDate): void
{
    // $totalDesmosItems includes copies (itemid_chain_size > 1)
    $totalDesmosItemsByGroup = DesmosItem::getTotalItemsCreatedByAllGroups($startDate, $endDate, false);

    // $totalDesmosItems only includes items that are not copies (itemid_chain_size == 1)
    $totalDesmosItemsAuthoredByGroup = DesmosItem::getTotalItemsCreatedByAllGroups($startDate, $endDate, true);

    $uniqueStudentViewsByGroup = ContentTracker::countUniqueStudentsByGroup("desmosview", $startDate, $endDate, false, null);
    $uniqueStudentLtiViewsByGroup = ContentTracker::countUniqueStudentsByGroup("desmosview", $startDate, $endDate, true, null);
    $uniqueTeacherViewsByGroup = ContentTracker::countUniqueTeachersByGroup("desmosview", $startDate, $endDate, false, null);
    $uniqueTeacherAddsByGroup = ContentTracker::countUniqueTeachersByGroup("desmosadd", $startDate, $endDate, false, null);
    $uniqueTeacherCopiesByGroup = ContentTracker::countUniqueTeachersByGroup("desmoscopy", $startDate, $endDate, false, null);
    $uniqueTeacherEditsByGroup = ContentTracker::countUniqueTeachersByGroup("desmosedit", $startDate, $endDate, false, null);
    outputSummaryTable($totalDesmosItemsByGroup, $uniqueStudentViewsByGroup, $uniqueTeacherViewsByGroup);
    outputGroupReportTable($totalDesmosItemsByGroup, $totalDesmosItemsAuthoredByGroup, $uniqueStudentViewsByGroup,
        $uniqueStudentLtiViewsByGroup, $uniqueTeacherAddsByGroup, $uniqueTeacherCopiesByGroup, $uniqueTeacherEditsByGroup);
}


/**
 * Output the report summary table.
 *
 * @param array $totalDesmosItemsByGroup As returned by DesmosItem::getTotalItemsCreatedByAllGroups.
 * @param array $uniqueStudentViewsByGroup As returned by ContentTracker::countUniqueStudentsByGroup.
 * @param array $uniqueTeacherViewsByGroup As returned by ContentTracker::countUniqueTeachersByGroup.
 */
function outputSummaryTable(array $totalDesmosItemsByGroup,
                            array $uniqueStudentViewsByGroup,
                            array $uniqueTeacherViewsByGroup
): void
{
    // Sum of all unique student views across all schools
    $uniqueStudentViews = 0;
    foreach ($uniqueStudentViewsByGroup as $group) {
        $uniqueStudentViews += $group['user_count'];
    }

    // Sum of all unique teacher views across all schools
    $uniqueTeacherViews = 0;
    foreach ($uniqueTeacherViewsByGroup as $group) {
        $uniqueTeacherViews += $group['user_count'];
    }

    // Sum of all Desmos items created across all schools
    $totalDesmosItems = 0;
    foreach ($totalDesmosItemsByGroup as $group) {
        $totalDesmosItems += $group['total_items'];
    }
    ?>
    <table>
        <thead>
        <tr>
            <td colspan="2">Totals across all schools</td>
        </tr>
        </thead>
        <tr>
            <td>Unique student views</td>
            <td><?php echo $uniqueStudentViews ?></td>
        </tr>
        <tr>
            <td>Unique teacher views</td>
            <td><?php echo $uniqueTeacherViews ?></td>
        </tr>
        <tr>
            <td>Interactives created</td>
            <td><?php echo $totalDesmosItems ?></td>
        </tr>
    </table>
    <?php
}


/**
 * Output the group report.
 *
 * @param array $totalDesmosItemsByGroup As returned by DesmosItem::getTotalItemsCreatedByAllGroups.
 * @param array $totalDesmosItemsAuthoredByGroup As returned by DesmosItem::getTotalItemsCreatedByAllGroups.
 * @param array $uniqueStudentViewsByGroup As returned by ContentTracker::countUniqueStudentsByGroup.
 * @param array $uniqueStudentLtiViewsByGroup As returned by ContentTracker::countUniqueStudentsByGroup.
 * @param array $uniqueTeacherAddsByGroup As returned by ContentTracker::countUniqueTeachersByGroup.
 * @param array $uniqueTeacherCopiesByGroup As returned by ContentTracker::countUniqueTeachersByGroup.
 * @param array $uniqueTeacherEditsByGroup As returned by ContentTracker::countUniqueTeachersByGroup.
 */
function outputGroupReportTable(array $totalDesmosItemsByGroup,
                                array $totalDesmosItemsAuthoredByGroup,
                                array $uniqueStudentViewsByGroup,
                                array $uniqueStudentLtiViewsByGroup,
                                array $uniqueTeacherAddsByGroup,
                                array $uniqueTeacherCopiesByGroup,
                                array $uniqueTeacherEditsByGroup
): void
{
    ?>
    <p>Total schools: <?php echo count($totalDesmosItemsByGroup); ?></p>

    <table id="reportTable">
        <thead>
        <tr>
            <th>Name</th>
            <th>Student Users (view)</th>
            <th>LTI</th>
            <th>Interactives Authored (not copied)</th>
            <th>Total Desmos Interactives</th>
            <th>Teacher Users (add)</th>
            <th>Teacher Users (copy)</th>
            <th>Teacher Users (edit)</th>
        </tr>
        </thead>
    <?php

    foreach ($totalDesmosItemsByGroup as $groupId => $group) {
        $totalItems = $group['total_items'] ?? 0;
        $totalAuthored = $totalDesmosItemsAuthoredByGroup[$groupId]['total_items'] ?? 0;
        $uniqueStudentViews = $uniqueStudentViewsByGroup[$groupId] ?? 0;
        $uniqueStudentLtiViews = $uniqueStudentLtiViewsByGroup[$groupId] ?? 0;
        $uniqueTeacherAdds = $uniqueTeacherAddsByGroup[$groupId] ?? 0;
        $uniqueTeacherCopies = $uniqueTeacherCopiesByGroup[$groupId] ?? 0;
        $uniqueTeacherEdits = $uniqueTeacherEditsByGroup[$groupId] ?? 0;

        echo "<tr>\n";
        printf("<td>%s</td>\n",
            Sanitize::encodeStringForDisplay($totalDesmosItemsByGroup[$groupId]['group_name']));
        printf("  <td>%s</td>\n", $uniqueStudentViews);
        printf("  <td>%s</td>", $uniqueStudentLtiViews);
        printf("  <td>%s</td>\n", $totalAuthored);
        printf("  <td>%s</td>", $totalItems);
        printf("  <td>%s</td>\n", $uniqueTeacherAdds);
        printf("  <td>%s</td>\n", $uniqueTeacherCopies);
        printf("  <td>%s</td>\n", $uniqueTeacherEdits);
        echo "</tr>\n";
    }

    echo '</table>';
    echo '<script type="text/javascript">initSortTable("reportTable", Array("S", "S", "S", "S", "S"), true);</script>';
}
