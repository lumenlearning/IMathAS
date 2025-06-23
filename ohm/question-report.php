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

$reportService = new OHM\Services\QuestionReportService(
    $DBH,
    $paramSource
);

// if the request is a form POST or a CSV export, then the data needs to be queried
if (isset($_GET['export']) && $_GET['export'] === 'csv' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $report = $reportService->generateReport();

    $questions = $report['questions'];
    $totalQuestions = count($questions);

    $users = $report['users'];
    $groups = $report['groups'];
    $userRightsDistribution = $report['userRightsDistribution'];
    $questionTypeDistribution = $report['questionTypeDistribution'];
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') { // CSV export request
    $reportService->exportCSVsToZip('question-report-');
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') { // Form submission request
    $showResults = true;
}

require_once("../header.php");

include(__DIR__ . "/views/question-report/show-question-report.php");

include(__DIR__ . '/../footer.php');
?>
