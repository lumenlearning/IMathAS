<link rel="stylesheet" href="views/question-report/question-report.css" type="text/css"/>
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
                <input type="text" id="start_date" name="start_date" class="js-flatpickr" placeholder="Start Date"
                       value="<?php echo $startDate; ?>">
                to
                <input type="text" id="end_date" name="end_date" class="js-flatpickr" placeholder="End Date"
                       value="<?php echo $endDate; ?>">
            </div>
        </div>

        <div class="filter-row">
            <div class="filter-item">
                <label class="filter-label" for="start_mod_date">Last Modified Date Range:</label>
                <input type="text" id="start_mod_date" name="start_mod_date" class="js-flatpickr"
                       placeholder="Start Date" value="<?php echo $startModDate; ?>">
                to
                <input type="text" id="end_mod_date" name="end_mod_date" class="js-flatpickr" placeholder="End Date"
                       value="<?php echo $endModDate; ?>">
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
                <input type="number" id="min_assessment_usage" name="min_assessment_usage" placeholder="Min Usage"
                       value="<?php echo $minAssessmentUsage; ?>">
                to
                <input type="number" id="max_assessment_usage" name="max_assessment_usage" placeholder="Max Usage"
                       value="<?php echo $maxAssessmentUsage; ?>">
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
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['0'] / $totalQuestions) * 100, 2) : 0; ?>
                        %
                    </td>
                </tr>
                <tr>
                    <td>Outdated (1)</td>
                    <td><?php echo $userRightsDistribution['1']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['1'] / $totalQuestions) * 100, 2) : 0; ?>
                        %
                    </td>
                </tr>
                <tr>
                    <td>Allow use by all (2)</td>
                    <td><?php echo $userRightsDistribution['2']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['2'] / $totalQuestions) * 100, 2) : 0; ?>
                        %
                    </td>
                </tr>
                <tr>
                    <td>Allow use by all and modifications by group (3)</td>
                    <td><?php echo $userRightsDistribution['3']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['3'] / $totalQuestions) * 100, 2) : 0; ?>
                        %
                    </td>
                </tr>
                <tr>
                    <td>Allow use by all and modifications by all (4)</td>
                    <td><?php echo $userRightsDistribution['4']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['4'] / $totalQuestions) * 100, 2) : 0; ?>
                        %
                    </td>
                </tr>
                <tr>
                    <td>Unspecified</td>
                    <td><?php echo $userRightsDistribution['Unspecified']; ?></td>
                    <td><?php echo $totalQuestions > 0 ? round(($userRightsDistribution['Unspecified'] / $totalQuestions) * 100, 2) : 0; ?>
                        %
                    </td>
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
    $(document).ready(function () {
        $(".js-flatpickr").flatpickr({
            dateFormat: "Y-m-d",
        });
    });
</script>