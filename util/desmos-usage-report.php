<?php

use Course\Includes\ContentTracker;
use Desmos\Models\DesmosItem;
use OHM\Includes\ReadReplicaDb;

require_once(__DIR__ . '/../init.php');

if ($myrights < 100) {
    echo "You don't permission to view this page.";
    exit;
}

$pagetitle = 'Desmos Usage Report';
$curBreadcrumb = $breadcrumbbase
    . ' <a href="../admin/userreports.php">' . _('User Reports') . '</a> &gt; '
    . $pagetitle;

$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/DatePicker.js\"></script>\n";
$placeinhead .= "<script type=\"text/javascript\" src=\"$imasroot/javascript/tablesorter.js\"></script>\n";
$placeinhead .= "<link title='lux' rel=\"stylesheet\" type=\"text/css\" href=\"https://lux.lumenlearning.com/use-lux/1.0.2/lux-components.min.css\">\n";
require("../header.php");
echo '<div class=breadcrumb>', $curBreadcrumb, '</div>';
echo '<div id="headeradmin" class="pagetitle"><h1>', $pagetitle, '</h1></div>';
echo '<p><u>Note</u>: Data is queried from the OHM read replica DB.</p>';

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
        <form method="POST" class="lux-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="generate_report"/>
            <div>
                <label for="startDate" class="form-label">Start Date:</label>
                <input id="startDate"
                    name="startDate"
                    type="text"
                    class="form-input has-icon icon--suffix icon--calendar"
                    style="width: 8.5em;"
                    onClick="displayDatePicker('startDate', this); return false"
                    value="<?php echo $startDate->format('m/d/Y'); ?>"/>
            </div>
            <div class="u-margin-vertical-sm">
                <label for="endDate" class="form-label">End Date:</label>
                <input id="endDate"
                    name="endDate"
                    type="text"
                    class="form-input has-icon icon--suffix icon--calendar"
                    style="width: 8.5em;"
                    onClick="displayDatePicker('endDate', this); return false"
                    value="<?php echo $endDate->format('m/d/Y'); ?>"/>
            </div>
            <button id="desmos_form_submit_button"
                    name="submitbtn"
                    type="submit"
                    class="button button--primary u-margin-vertical-sm"
                    value="Submit">Update
            </button>
        </form>
    </div>
    <?php
}


/**
 * Generate and output the report.
 *
 * @param DateTime $startDate
 * @param DateTime $endDate
 * @throws Exception Thrown if unable to connect to the database.
 */
function generateReport(DateTime $startDate, DateTime $endDate): void
{
    $dbh = ReadReplicaDb::getPdoInstance();

    $uniqueStudentViewsAllGroups = ContentTracker::countUniqueStudentsByGroup(["desmosview", "desmoscalc"], $startDate, $endDate, false, null, $dbh);

    $totalUniqueStudentViews = 0;
    foreach ($uniqueStudentViewsAllGroups as $groupId => $totalViews) {
        $totalUniqueStudentViews += $totalViews;
    }

    printf("<p>Total unique student views across all schools ('desmosview', 'desmoscalc'): <b>%d</b></p>", $totalUniqueStudentViews);
}
