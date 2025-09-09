<?php
/**
 * This takes a CSV file uploaded by an admin user.
 * The CSV file contains a list of students to opt out of assessments.
 *
 * After opting students out of assessments, two CSV files will be made
 * available for download:
 * - Students successfully opted out.
 * - Students whose enrollments could not be found.
 */

use OHM\Services\OptOutService;

require __DIR__ . '/../../init.php';
require_once __DIR__ . "/../../header.php";

if ($GLOBALS['myrights'] < 100) {
    echo "You're not authorized to view this page.";
    include(__DIR__ . '/../footer.php');
    exit;
}

const MAX_CSV_FILE_SIZE = 1_024_000; // in bytes

/*
 * Breadcrumbs
 */

$curBreadcrumb = $GLOBALS['breadcrumbbase']
        . ' <a href="/admin/admin2.php">' . _('Admin')
        . '</a> &gt; Mass opt out';
echo '<div class=breadcrumb>', $curBreadcrumb, '</div>';

/*
 * Sanitize all form input
 */

$csvFile = $_FILES["csv_file"];

/*
 * Display the form.
 */

?>
    <h1>Opt students out of course assessments</h1>

    <p>
        Use this page to opt a list of students out of course assessments
        by uploading a CSV File.
    </p>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_csv_file"/>
        <p>
            <label>Select CSV file to upload:</label>
            <input type="file" name="csv_file" id="csv_file">
        </p>
        <p>
            <button type="submit"
                    onClick="this.form.submit(); this.disabled=true; this.innerHTML='Uploading CSV file...'"
            >Upload CSV file
            </button>
        </p>
    </form>
<?php

if (!isset($_POST['action']) || 'upload_csv_file' != $_POST['action']) {
    require __DIR__ . '/../../footer.php';
    return;
}

/*
 * Validate form input.
 */

if (empty($_FILES)) {
    echo '<p>ERROR: Please select a CSV file to upload.</p>';
    require __DIR__ . '/../../footer.php';
    return;
}

// Check for upload errors.
if (!empty($_FILES['csv_file']['error'])) {
    // https://www.php.net/manual/en/filesystem.constants.php#constant.upload-err-cant-write
    switch ($_FILES['csv_file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            echo '<p>ERROR: No file was uploaded.</p>';
            return;
        case UPLOAD_ERR_INI_SIZE: // Size restriction in php.ini.
        case UPLOAD_ERR_FORM_SIZE: // Size restriction in <form>.
            echo '<p>ERROR: File is too large.</p>';
            return;
        case UPLOAD_ERR_CANT_WRITE:
            echo '<p>ERROR: Server error - unable to write to disk.</p>';
            return;
        case UPLOAD_ERR_PARTIAL:
            echo '<p>ERROR: File was only partially uploaded.</p>';
            return;
        default:
            // This should never happen.
            echo '<p>ERROR: Unknown error.</p>';
            return;
    }
}

// Check submitted CSV file size.
if (MAX_CSV_FILE_SIZE < $_FILES["csv_file"]["size"]) {
    printf("<p>ERROR: CSV file is too large. Maximum size: %s bytes</p>", number_format(MAX_CSV_FILE_SIZE));
    require __DIR__ . '/../../footer.php';
    return;
}

$optOutService = new OptOutService($GLOBALS['DBH']);

// Check submitted file type.
$csvFileName = $_FILES['csv_file']['tmp_name']; // We don't need to know the user-provided filename.
if (!$optOutService->isCsvFile($csvFileName)) {
    echo '<p>ERROR: Uploaded file is not a CSV file.</p>';
    require __DIR__ . '/../../footer.php';
    return;
}

// Opt out the students listed in the CSV file.
$optOutResults = $optOutService->optOutAllStudentsByCsv($csvFileName);

/*
 * Display opt-out results.
 */

echo '<h1>Results of processing your opt-out CSV file</h1>';
echo '<ul>';
printf('<li>Total students opted out: %d</li>', $optOutResults['totalStudentsOptedOut']);
printf('<li>Total student enrollments not found: %d</li>', $optOutResults['totalStudentsNotFound']);
echo '</ul>';

echo '<h1>Download CSV files</h1>';
echo '<p>These CSV files contain the results of processing your uploaded CSV file.</p>';
printf('<li><a href="%s/filestore/%s">%s</a></li>', $GLOBALS['basesiteurl'],
        $optOutResults['studentsOptedOutCsvFilename'], $optOutResults['studentsOptedOutCsvFilename']);
printf('<li><a href="%s/filestore/%s">%s</a></li>', $GLOBALS['basesiteurl'],
        $optOutResults['studentsNotFoundCsvFilename'], $optOutResults['studentsNotFoundCsvFilename']);

require __DIR__ . '/../../footer.php';
