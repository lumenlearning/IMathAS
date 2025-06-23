<?php

use OHM\Includes\ReadReplicaDb;

require_once(__DIR__ . '/../init.php');
$placeinhead .= '<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>';
$placeinhead .= "<link title='lux' rel=\"stylesheet\" type=\"text/css\" href=\"https://lux.lumenlearning.com/use-lux/1.0.2/lux-components.min.css\">";
$placeinhead .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">';

if ($GLOBALS['myrights'] < 100) {
    echo "You're not authorized to view this page.";
    include(__DIR__ . '/../footer.php');
    exit;
}

// Run all queries on the read replica DB.
$GLOBALS['DBH'] = ReadReplicaDb::getPdoInstance();

$showResults = false;

$paramSource = $_GET;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paramSource = $_POST;
}

// get string query params from the paramSource
function getStringFromParams($paramSource, $paramName) : string {
    return isset($paramSource[$paramName]) ? Sanitize::simpleString($paramSource[$paramName]) : '';
}

// get integer query params from the paramSource
function getIntegerFromParams($paramSource, $paramName) : int | null {
    // onlyInt will cast '' to 0, so checking against !empty is required
    // !empty(0) is false, so must check against explicit '0' to ensure that value is interpreted as integer 0
    return isset($paramSource[$paramName]) && (!empty($paramSource[$paramName]) || $paramSource[$paramName] === '0') ? Sanitize::onlyInt($paramSource[$paramName]) : null;
}

$startDate = getStringFromParams($paramSource, 'start_date');
$endDate = getStringFromParams($paramSource, 'end_date');
$startModDate = getStringFromParams($paramSource, 'start_mod_date');
$endModDate = getStringFromParams($paramSource, 'end_mod_date');
$minId = getIntegerFromParams($paramSource, 'min_id');
$maxId = getIntegerFromParams($paramSource, 'max_id');
$minAssessmentUsage = getIntegerFromParams($paramSource, 'min_assessment_usage');
$maxAssessmentUsage = getIntegerFromParams($paramSource, 'max_assessment_usage');

// if the request is a form POST or a CSV export, then the data needs to be queried
if (isset($_GET['export']) && $_GET['export'] === 'csv' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportService = new OHM\Services\QuestionReportService(
        $DBH,
        $startDate,
        $endDate,
        $startModDate,
        $endModDate,
        $minId,
        $maxId,
        $minAssessmentUsage,
        $maxAssessmentUsage
    );

    $report = $reportService->generateReport();

    $questions = $report['questions'];
    $totalQuestions = count($questions);

    $users = $report['users'];
    $groups = $report['groups'];
    $userRightsDistribution = $report['userRightsDistribution'];
    $questionTypeDistribution = $report['questionTypeDistribution'];
}

// Check if CSV export is requested
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $questionsArrays = $reportService->questionsToCSVArrays();
    $usersArrays = $reportService->usersToCSVArrays();
    $groupsArrays = $reportService->groupsToCSVArrays();

    // Export to ZIP containing all CSV files
    exportCSVsToZip([
        'questions.csv' => $questionsArrays,
        'users.csv' => $usersArrays,
        'groups.csv' => $groupsArrays
    ], $zipName = 'question-report');

    exit();
}

require_once("../header.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $showResults = true;
}

include(__DIR__ . "/views/question-report/show-question-report.php");

include(__DIR__ . '/../footer.php');
?>
