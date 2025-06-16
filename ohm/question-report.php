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
</style>';
require_once("../header.php");

if ($GLOBALS['myrights'] < 100) {
    echo "You're not authorized to view this page.";
    include(__DIR__ . '/../footer.php');
    exit;
}

// Initialize variables with sanitized inputs
$startDate = isset($_POST['start_date']) ? Sanitize::simpleString($_POST['start_date']) : '';
$endDate = isset($_POST['end_date']) ? Sanitize::simpleString($_POST['end_date']) : '';
$startModDate = isset($_POST['start_mod_date']) ? Sanitize::simpleString($_POST['start_mod_date']) : '';
$endModDate = isset($_POST['end_mod_date']) ? Sanitize::simpleString($_POST['end_mod_date']) : '';
$notInAssessment = isset($_POST['not_in_assessment']);

// Process form submission
$showResults = false;
$totalQuestions = 0;
$userRightsDistribution = [];
$uniqueUsers = [];
$uniqueGroups = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $showResults = true;

    // Build the query
    $query = "SELECT qs.id, qs.userights, qs.ownerid, qs.adddate, qs.lastmoddate, u.groupid 
              FROM imas_questionset AS qs 
              JOIN imas_users AS u ON qs.ownerid = u.id
              WHERE qs.deleted=0";

    $params = [];

    // Add date filters
    if (!empty($startDate)) {
        $query .= " AND qs.adddate >= :start_date";
        $params[':start_date'] = strtotime($startDate);
    }

    if (!empty($endDate)) {
        $query .= " AND qs.adddate <= :end_date";
        $params[':end_date'] = strtotime($endDate . ' 23:59:59');
    }

    if (!empty($startModDate)) {
        $query .= " AND qs.lastmoddate >= :start_mod_date";
        $params[':start_mod_date'] = strtotime($startModDate);
    }

    if (!empty($endModDate)) {
        $query .= " AND qs.lastmoddate <= :end_mod_date";
        $params[':end_mod_date'] = strtotime($endModDate . ' 23:59:59');
    }

    // Add not in assessment filter
    if ($notInAssessment) {
        $query .= " AND qs.id NOT IN (SELECT DISTINCT questionsetid FROM imas_questions)";
    }

    // Execute the query
    $stmt = $DBH->prepare($query);
    $stmt->execute($params);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalQuestions = count($questions);

    // Process the results
    $userRightsDistribution = [
        '0' => 0, // Private
        '2' => 0, // Allow Use By All
        '3' => 0, // Allow use by all and modifications by group
        '4' => 0, // Allow use by all and modifications by all
        'Unspecified' => 0
    ];

    foreach ($questions as $question) {
        // Count by user rights
        if (isset($userRightsDistribution[$question['userights']])) {
            $userRightsDistribution[$question['userights']]++;
        } else {
            $userRightsDistribution['Unspecified']++;
        }

        // Track unique users
        if (!in_array($question['ownerid'], $uniqueUsers)) {
            $uniqueUsers[] = $question['ownerid'];
        }

        // Track unique groups
        if (!empty($question['groupid']) && !in_array($question['groupid'], $uniqueGroups)) {
            $uniqueGroups[] = $question['groupid'];
        }
    }

    // Get user details
    $uniqueUserDetails = [];
    if (!empty($uniqueUsers)) {
        $placeholders = str_repeat('?,', count($uniqueUsers) - 1) . '?';
        $query = "SELECT u.id, u.FirstName, u.LastName, u.rights, u.groupid, g.name AS groupname 
                  FROM imas_users AS u 
                  LEFT JOIN imas_groups AS g ON u.groupid = g.id 
                  WHERE u.id IN ($placeholders)";
        $stmt = $DBH->prepare($query);
        $stmt->execute($uniqueUsers);
        $uniqueUserDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get group details
    $uniqueGroupDetails = [];
    if (!empty($uniqueGroups)) {
        $placeholders = str_repeat('?,', count($uniqueGroups) - 1) . '?';
        $query = "SELECT id, name, grouptype 
                  FROM imas_groups 
                  WHERE id IN ($placeholders)";
        $stmt = $DBH->prepare($query);
        $stmt->execute($uniqueGroups);
        $uniqueGroupDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
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
    <form method="post" action="">
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
                <input type="checkbox" id="not_in_assessment" name="not_in_assessment" <?php echo $notInAssessment ? 'checked' : ''; ?>>
                <label for="not_in_assessment">Only show questions not in any assessment</label>
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
            <p>Total questions matching criteria: <?php echo $totalQuestions; ?></p>
            <p>Unique users: <?php echo count($uniqueUsers); ?></p>
            <p>Unique groups: <?php echo count($uniqueGroups); ?></p>
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
            <div class="results-title">Unique Groups (<?php echo count($uniqueGroupDetails); ?>)</div>
            <?php if (!empty($uniqueGroupDetails)): ?>
                <table class="results-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Group Type</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($uniqueGroupDetails as $group): ?>
                        <tr>
                            <td><?php echo $group['id']; ?></td>
                            <td><?php echo $group['name']; ?></td>
                            <td><?php echo $group['grouptype']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No groups found.</p>
            <?php endif; ?>
        </div>

        <div class="results-section">
            <div class="results-title">Unique Users (<?php echo count($uniqueUserDetails); ?>)</div>
            <?php if (!empty($uniqueUserDetails)): ?>
                <table class="results-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Rights</th>
                        <th>Group</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($uniqueUserDetails as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['FirstName'] . ' ' . $user['LastName']; ?></td>
                            <td><?php echo $user['rights']; ?></td>
                            <td><?php echo $user['groupname']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
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