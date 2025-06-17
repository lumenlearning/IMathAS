<?php

require_once(__DIR__ . '/../init.php');
$placeinhead .= '<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>';
$placeinhead .= "<link title='lux' rel=\"stylesheet\" type=\"text/css\" href=\"https://lux.lumenlearning.com/use-lux/1.0.2/lux-components.min.css\">";
$placeinhead .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">';
$placeinhead .= '<style>
    .filter-container {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 10px;
        align-items: center;
    }
    .filter-item {
        margin-right: 20px;
        margin-bottom: 10px;
    }
    .filter-label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .results-container {
        margin-top: 20px;
    }
    .results-section {
        margin-bottom: 30px;
    }
    .results-title {
        font-size: 1.2em;
        font-weight: bold;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #ddd;
    }
    .results-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .results-table th, .results-table td {
        padding: 8px;
        text-align: left;
        border: 1px solid #ddd;
    }
    .results-table th {
        background-color: #f2f2f2;
    }
    .csv-download-btn {
        margin-left: 10px;
        padding: 5px 10px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .csv-download-btn:hover {
        background-color: #45a049;
    }
</style>';

if ($GLOBALS['myrights'] < 100) {
    echo "You're not authorized to view this page.";
    include(__DIR__ . '/../footer.php');
    exit;
}

// Function to export multiple CSV files as a ZIP archive
function exportCSVsToZip($filesData, $zipName = 'zip_')
{
    // Create a temporary directory
    $tempDir = sys_get_temp_dir() . '/csv_export_' . uniqid();
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Create CSV files in the temporary directory
    foreach ($filesData as $filename => $data) {
        $filepath = $tempDir . '/' . $filename;
        $f = fopen($filepath, 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write data to the file
        foreach ($data as $row) {
            fputcsv($f, $row);
        }

        fclose($f);
    }

    // Create a ZIP file
    $zipFilename = $zipName . date('Y-m-d') . '.zip';
    $zipFilepath = $tempDir . '/' . $zipFilename;

    $zip = new ZipArchive();
    if ($zip->open($zipFilepath, ZipArchive::CREATE) !== TRUE) {
        die("Cannot create ZIP file");
    }

    // Add CSV files to the ZIP
    foreach ($filesData as $filename => $data) {
        $zip->addFile($tempDir . '/' . $filename, $filename);
    }

    $zip->close();

    // Send the ZIP file to the browser
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
    header('Content-Length: ' . filesize($zipFilepath));
    header('Pragma: no-cache');
    header('Expires: 0');

    readfile($zipFilepath);

    // Clean up temporary files
    foreach ($filesData as $filename => $data) {
        unlink($tempDir . '/' . $filename);
    }
    unlink($zipFilepath);
    rmdir($tempDir);
}

$showResults = false;

$paramSource = $_GET;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paramSource = $_POST;
}

// get query params from the paramSource
$startDate = isset($paramSource['start_date']) ? Sanitize::simpleString($paramSource['start_date']) : '';
$endDate = isset($paramSource['end_date']) ? Sanitize::simpleString($paramSource['end_date']) : '';
$startModDate = isset($paramSource['start_mod_date']) ? Sanitize::simpleString($paramSource['start_mod_date']) : '';
$endModDate = isset($paramSource['end_mod_date']) ? Sanitize::simpleString($paramSource['end_mod_date']) : '';
// onlyInt will cast '' to 0, so checking against !empty is required
$minId = isset($paramSource['min_id']) && !empty($paramSource['min_id']) ? Sanitize::onlyInt($paramSource['min_id']) : null;
$maxId = isset($paramSource['max_id']) && !empty($paramSource['max_id']) ? Sanitize::onlyInt($paramSource['max_id']) : null;
// !empty(0) is false, so must check against explicit '0' to ensure that value is interpreted as integer 0
$minAssessmentUsage = isset($paramSource['min_assessment_usage']) && (!empty($paramSource['min_assessment_usage']) || $paramSource['min_assessment_usage'] === '0') ? Sanitize::onlyInt($paramSource['min_assessment_usage']) : null;
$maxAssessmentUsage = isset($paramSource['max_assessment_usage']) && (!empty($paramSource['max_assessment_usage']) || $paramSource['max_assessment_usage'] === '0') ? Sanitize::onlyInt($paramSource['max_assessment_usage']) : null;

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

?>
    <div class="breadcrumb">
        <?php echo $breadcrumbbase; ?>
        <a href="../admin/admin2.php">Admin</a> >
        <a href="../util/utils.php">Utilities</a> >
        <a href="?">Question Report</a>
    </div>

    <h1>Question Report</h1>

    <div class="filter-container">
        <h2>Filter Questions (Optional)</h2>
        <form method="post" action="question-report.php">
            <div class="filter-row">
                <div class="filter-item">
                    <label class="filter-label" for="start_date">Creation Date Range:</label>
                    <input type="text" id="start_date" name="start_date" class="js-flatpickr" placeholder="Start Date" value="<?php echo $startDate; ?>">
                    to
                    <input type="text" id="end_date" name="end_date" class="js-flatpickr" placeholder="End Date" value="<?php echo $endDate; ?>">
                </div>
            </div>

            <div class="filter-row">
                <div class="filter-item">
                    <label class="filter-label" for="start_mod_date">Last Modified Date Range:</label>
                    <input type="text" id="start_mod_date" name="start_mod_date" class="js-flatpickr" placeholder="Start Date" value="<?php echo $startModDate; ?>">
                    to
                    <input type="text" id="end_mod_date" name="end_mod_date" class="js-flatpickr" placeholder="End Date" value="<?php echo $endModDate; ?>">
                </div>
            </div>

            <div class="filter-row">
                <div class="filter-item">
                    <label class="filter-label" for="min_id">Question ID Range:</label>
                    <input type="number" id="min_id" name="min_id" placeholder="Min ID" value="<?php echo $minId; ?>">
                    to
                    <input type="number" id="max_id" name="max_id" placeholder="Max ID" value="<?php echo $maxId; ?>">
                </div>
            </div>

            <div class="filter-row">
                <div class="filter-item">
                    <label class="filter-label" for="min_assessment_usage">Assessment Usage Range:</label>
                    <input type="number" id="min_assessment_usage" name="min_assessment_usage" placeholder="Min Usage" value="<?php echo $minAssessmentUsage; ?>">
                    to
                    <input type="number" id="max_assessment_usage" name="max_assessment_usage" placeholder="Max Usage" value="<?php echo $maxAssessmentUsage; ?>">
                </div>
            </div>

            <div class="filter-row">
                <button type="submit" class="button button--primary">Generate Report</button>
            </div>
        </form>
    </div>

<?php if ($showResults): ?>
    <div class="results-container">
        <h2>Report Results</h2>

        <div class="results-section">
            <div class="results-title">Summary</div>
            <div style="margin-bottom: 10px;">
                <a href="?export=csv<?php
                echo !empty($startDate) ? '&start_date=' . urlencode($startDate) : '';
                echo !empty($endDate) ? '&end_date=' . urlencode($endDate) : '';
                echo !empty($startModDate) ? '&start_mod_date=' . urlencode($startModDate) : '';
                echo !empty($endModDate) ? '&end_mod_date=' . urlencode($endModDate) : '';
                echo isset($minId) ? '&min_id=' . urlencode($minId) : '';
                echo isset($maxId) ? '&max_id=' . urlencode($maxId) : '';
                echo isset($minAssessmentUsage) ? '&min_assessment_usage=' . urlencode($minAssessmentUsage) : '';
                echo isset($maxAssessmentUsage) ? '&max_assessment_usage=' . urlencode($maxAssessmentUsage) : '';
                ?>" class="csv-download-btn">Download CSV Files (ZIP)</a>
            </div>
            <p>Total questions matching criteria: <?php echo $totalQuestions; ?></p>
            <p>Unique users: <?php echo count($users); ?></p>
            <p>Unique groups: <?php echo count($groups); ?></p>
        </div>

        <div class="results-section">
            <div class="results-title">Distribution of User Rights</div>
            <table class="results-table">
                <thead>
                <tr>
                    <th>User Rights</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Private (0)</td>
                    <td><?php echo $userRightsDistribution['0']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['0'] / $totalQuestions) * 100, 2) : 0; ?>%</td>
                </tr>
                <tr>
                    <td>Outdated (1)</td>
                    <td><?php echo $userRightsDistribution['1']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['1'] / $totalQuestions) * 100, 2) : 0; ?>%</td>
                </tr>
                <tr>
                    <td>Allow use by all (2)</td>
                    <td><?php echo $userRightsDistribution['2']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['2'] / $totalQuestions) * 100, 2) : 0; ?>%</td>
                </tr>
                <tr>
                    <td>Allow use by all and modifications by group (3)</td>
                    <td><?php echo $userRightsDistribution['3']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['3'] / $totalQuestions) * 100, 2) : 0; ?>%</td>
                </tr>
                <tr>
                    <td>Allow use by all and modifications by all (4)</td>
                    <td><?php echo $userRightsDistribution['4']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['4'] / $totalQuestions) * 100, 2) : 0; ?>%</td>
                </tr>
                <tr>
                    <td>Unspecified</td>
                    <td><?php echo $userRightsDistribution['Unspecified']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['Unspecified'] / $totalQuestions) * 100, 2) : 0; ?>%</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="results-section">
            <div class="results-title">Distribution of Question Types</div>
            <table class="results-table">
                <thead>
                <tr>
                    <th>Question Type</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($questionTypeDistribution as $type => $count): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($type); ?></td>
                        <td><?php echo $count; ?></td>
                        <td><?php echo $totalQuestions > 0 ? round(($count / $totalQuestions) * 100, 2) : 0; ?>%</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script>
    $(document).ready(function() {
        $(".js-flatpickr").flatpickr({
            dateFormat: "Y-m-d",
        });
    });
</script>

<?php
include(__DIR__ . '/../footer.php');
?>
