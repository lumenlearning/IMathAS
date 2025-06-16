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

function questionsToCSVArrays($questions) {
    $arrays = array(
            // Column Headers
            array('Question ID', 'User Rights', 'Owner ID', 'Creation Date', 'Last Modified Date', 'Group ID')
    );

    // Add data rows
    foreach ($questions as $question) {
        $row = array(
            $question['id'],
            $question['userights'],
            $question['ownerid'],
            date('Y-m-d H:i:s', $question['adddate']),
            date('Y-m-d H:i:s', $question['lastmoddate']),
            $question['groupid']
        );
        $arrays[] = $row;
    }

    return $arrays;
}

function usersToCSVArrays($users) {
    $arrays = array(
            // Column Headers
            array('ID', 'Name', 'Rights', 'Group Name')
    );

    // Add data rows
    foreach ($users as $user) {
        $row = array(
            $user['id'],
            $user['FirstName'] . ' ' . $user['LastName'],
            $user['rights'],
            $user['groupname']
        );
        $arrays[] = $row;
    }

    return $arrays;
}

function groupsToCSVArrays($groups) {
    $arrays = array(
            // Column Headers
            array('ID', 'Name', 'Group Type')
    );

    // Add data rows
    foreach ($groups as $group) {
        $row = array(
            $group['id'],
            $group['name'],
            $group['grouptype'],
        );
        $arrays[] = $row;
    }

    return $arrays;
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
$noAssessment = isset($paramSource['no_assessment']);

// if the request is a form POST or a CSV export, then the data needs to be queried
if (isset($_GET['export']) && $_GET['export'] === 'csv' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportService = new OHM\Services\QuestionReportService(
        $DBH,
        $startModDate,
        $endDate,
        $startModDate,
        $endModDate,
        $noAssessment
    );

    $report = $reportService->generateReport();

    $questions = $report['questions'];
    $totalQuestions = count($questions);

    $users = $report['users'];
    $groups = $report['groups'];
    $userRightsDistribution = $report['userRightsDistribution'];
}

// Check if CSV export is requested
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $questionsArrays = questionsToCSVArrays($questions);
    $usersArrays = usersToCSVArrays($users);
    $groupsArrays = groupsToCSVArrays($groups);

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
    <h2>Filter Questions</h2>
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
                <input type="checkbox" id="no_assessment" name="no_assessment" <?php echo $noAssessment ? 'checked' : ''; ?>>
                <label for="no_assessment">Only show questions not in any assessment</label>
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
                echo $noAssessment ? '&no_assessment=1' : '';
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
