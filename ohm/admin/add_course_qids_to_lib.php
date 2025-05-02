<?php

require __DIR__ . '/../../init.php';
require_once __DIR__ . "/../../header.php";

if ($GLOBALS['myrights'] < 100) {
    echo "You're not authorized to view this page.";
    include(__DIR__ . '/../footer.php');
    exit;
}

/*
 * Breadcrumbs
 */

$curBreadcrumb = $GLOBALS['breadcrumbbase']
    . ' <a href="/admin/admin2.php">' . _('Admin')
    . '</a> &gt; Add course QIDs to a library';
echo '<div class=breadcrumb>', $curBreadcrumb, '</div>';

/*
 * Sanitize all form input
 */

$course_id = Sanitize::onlyInt($_POST['course_id']) ?: null;
$library_id = Sanitize::onlyInt($_POST['library_id']) ?: null;

/*
 * Generation library options for form <select>.
 */

$libraries = getLibraries();
$libraryOptions = '';
foreach ($libraries as $library) {
    $selected = $library['id'] == $library_id ? " selected" : "";
    $option = 'Library ID ' . $library['id'] . ': ' . $library['name'];
    $libraryOptions .= sprintf('<option value="%d"%s>%s</option>' . "\n",
        $library['id'], $selected, $library['name']);
}

/*
 * Display the form.
 */

?>
    <h1>Add course QIDs to a library</h1>

    <p>
        Use this page to add all questions used in a specific course
        to a library.
    </p>

    <p>
        <u>Notes</u>:
    </p>

    <ul>
        <li>This will look at all assessments in a course.</li>
        <li>Questions already in the selected library will be skipped.</li>
    </ul>

    <form method="POST">
        <p>
            <label>Course ID with questions:
                <input type="text"
                       value="<?php echo $course_id; ?>"
                       name="course_id"/>
            </label>
        </p>
        <p>
            <label>Select a library:
                <select name="library_id">
                    <?php echo $libraryOptions; ?>
                </select>
            </label>
        </p>
        <p>
            <input type="submit"
                   name="add_course_qids_button"
                   value="Add questions to selected library"/>
        </p>
    </form>
<?php

/*
 * Validate form input.
 */

if (empty($course_id) || empty($library_id)) {
    echo '<p>Please complete all required fields.</p>';
    require __DIR__ . '/../../footer.php';
    return;
}

$errors = [];

if (!isValidCourse($course_id)) {
    $errors[] = 'Invalid course ID: ' . $course_id;
}
if (!isValidLibrary($library_id)) {
    $errors[] = 'Invalid library ID: ' . $library_id;
}

/*
 * Form input error reporting
 */

if (!empty($errors)) {
    echo '<p>Please correct the following error(s):</p>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>', $error, '</li>';
    }
    echo '</ul>';

    require __DIR__ . '/../../footer.php';
    return;
}

/*
 * Add course questions to library
 */

if (isset($_POST['add_course_qids_button'])) {
    addCourseQuestionsToLibrary($course_id, $library_id);
}

require __DIR__ . '/../../footer.php';
return;

/*
 * Functions
 */

/**
 * Determine if a course ID is valid.
 *
 * @param int $courseId The course ID. (from imas_courses)
 * @return bool True if the course ID is valid. False if not.
 */
function isValidCourse(int $courseId): bool
{
    $query = 'SELECT 1 FROM imas_courses WHERE id = :courseId';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(['courseId' => $courseId]);

    return ($stm->rowCount() === 1);
}

/**
 * Determine if a library ID is valid.
 *
 * @param int $libraryId The library ID. (from imas_libraries)
 * @return bool True if the library ID is valid. False if not.
 */
function isValidLibrary(int $libraryId): bool
{
    $query = 'SELECT 1 FROM imas_libraries WHERE id = :libraryId';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(['libraryId' => $libraryId]);

    return ($stm->rowCount() === 1);
}

/**
 * Get all question libraries.
 *
 * @return array An array of library IDs and names.
 */
function getLibraries(): array
{
    $query = 'SELECT id, name FROM imas_libraries';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute();

    return $stm->fetchAll();
}

/**
 * Get question data for all questions in a course.
 *
 * This returns:
 * - id -- imas_questionset.id
 * - question_type -- The question type
 * - assessment_name -- The name of the assessment the question is in
 *
 * @param int $courseId The course ID.
 * @return array An array of question data. This includes:
 */
function getCourseQuestions(int $courseId): array
{
    $query = 'SELECT
	qs.id AS id,
    qs.qtype AS question_type,
	a.name AS assessment_name
FROM imas_questions AS q
	JOIN imas_assessments AS a ON a.id = q.assessmentid
    JOIN imas_questionset AS qs ON qs.id = q.questionsetid
WHERE a.courseid = :courseId';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute(['courseId' => $courseId]);

    return $stm->fetchAll();
}

/**
 * Add all course questions to a library.
 *
 * @param int $courseId The course ID.
 * @param int $libraryId The library ID.
 * @return int The number of questions added to the library.
 */
function addCourseQuestionsToLibrary(int $courseId, int $libraryId): int
{
    $addedQuestionCount = 0;
    $skippedQuestionsCount = 0;

    $questions = getCourseQuestions($courseId);

    echo '<ol>';
    foreach ($questions as $question) {
        $skipQuestion = isQuestionInLibrary($libraryId, $question['id']);
        $actionText = $skipQuestion
            ? '<span style="color: #808080;">Skipping, already in library</span>'
            : '<span style="color: #00bb00;">Adding</span>';

        printf('<li>Assessment: %s -- Question ID %d, %s -- %s',
            $question['assessment_name'], $question['id'], $question['question_type'], $actionText);

        if ($skipQuestion) {
            $skippedQuestionsCount++;
            continue;
        }

        addQuestionToLibrary($libraryId, $question['id']);
        $addedQuestionCount++;
    }
    echo '</ol>';

    echo '<p>Results:</p>';
    echo '<ul>';
    echo '<li>Added ' . $addedQuestionCount . ' questions.</li>';
    echo '<li>Skipped ' . $skippedQuestionsCount . ' questions.</li>';
    echo '</ul>';

    return $addedQuestionCount;
}

/**
 * Determine if a question is in a library.
 *
 * @param int $questionsetId The question's ID. (from imas_questionset)
 * @param int $libraryId The library's ID. (from imas_libraries)
 * @return bool True if the question is in the library. False if not.
 */
function isQuestionInLibrary(int $libraryId, int $questionsetId): bool
{
    $query = 'SELECT id FROM imas_library_items WHERE qsetid = :questionsetId AND libid = :libraryId';
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute([
        'questionsetId' => $questionsetId,
        'libraryId' => $libraryId
    ]);

    return ($stm->rowCount() > 0);
}

/**
 * Add a question to a library.
 *
 * @param int $libraryId The library ID. (from imas_libraries)
 * @param int $questionsetId The question ID. (from imas_questionset)
 * @return int The ID of the row inserted into imas_library_items.
 */
function addQuestionToLibrary(int $libraryId, int $questionsetId): int
{
    $query = 'INSERT INTO imas_library_items (libid, qsetid, ownerid, junkflag, lastmoddate, deleted)' .
        sprintf(' VALUES (:libraryId, :questionsetId, :ownerId, :junkFlag, :updatedAt, :isDeleted)');
    $stm = $GLOBALS['DBH']->prepare($query);
    $stm->execute([
        'libraryId' => $libraryId,
        'questionsetId' => $questionsetId,
        'ownerId' => $GLOBALS['userid'],
        'junkFlag' => 0,
        'updatedAt' => time(),
        'isDeleted' => 0
    ]);

    return $GLOBALS['DBH']->lastInsertId();
}