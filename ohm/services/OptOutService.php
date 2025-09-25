<?php

namespace OHM\Services;

use PDO;
use RuntimeException;

class OptOutService
{
    /* @var PDO */
    private $DBH;

    /**
     * @param PDO $dbh A PDO instance. (like $GLOBALS['DBH']
     */
    public function __construct(PDO $dbh)
    {
        $this->DBH = $dbh;
    }

    /**
     * Determine if a user is opted out of course assessments by user ID and course ID.
     *
     * @param int $userId The user's ID from imas_users.
     * @param int $courseId The course ID for the assessments.
     * @return bool True if the user is opted out of assessments. False if not.
     * @throws RuntimeException Thrown if userId or courseId are empty.
     */
    public function isOptedOutOfAssessments(int $userId, int $courseId): bool
    {
        if (empty($userId) || empty($courseId)) {
            throw new RuntimeException('User ID or course ID not specified for opt-out check');
        }

        $stm = $this->DBH->prepare(
            'SELECT is_opted_out_assessments FROM imas_students WHERE userid = :userId AND courseid = :courseId');
        $result = $stm->execute([':userId' => $userId, ':courseId' => $courseId]);

        if ($result === false) {
            $errors = print_r($this->DBH->errorInfo(), true);
            error_log(sprintf('ERROR: Failed to lookup assessment opt-out status for user ID %d in course ID %d. Details: %s',
                $userId, $courseId, $errors));
            // Default behavior: allow the user through to assessments.
            return false;
        }

        $isOptedOut = $stm->fetchColumn(0);

        return 1 == $isOptedOut;
    }

    /**
     * For all students in a CSV file, opt them out of assessments.
     *
     * This only works for students with valid enrollments. (rows in
     * imas_students)
     *
     * Expected CSV format is:
     *     groupId,courseId,studentFirstName,studentLastName,email
     *
     * Student matching process:
     * 1. Match on Group ID + Course ID + Student Name + Email.
     * 2. Match on Group ID + Course ID + Student Name
     * 3. If no match, add to results CSV.
     *
     * @param string $csvFileName The full path to the CSV file.
     * @return array An associative array of opted out students.
     *               Example: [
     *                   'totalStudentsOptedOut' => 0,
     *                   'totalStudentsNotFound' => 0,
     *                   'studentsOptedOutCsvFilename' => 'students_opted_out_1757444256.csv',
     *                   'studentsNotFoundCsvFilename' => 'opt_out_students_not_found_1757444256.csv',
     *               ]
     */
    public function optOutAllStudentsByCsv(string $csvFileName): array
    {
        $totalStudentsOptedOut = 0;
        $totalStudentsNotFound = 0;

        /*
         * This generates CSV files for the user to download.
         * OHM's load balancers currently use sticky sessions. If/when this changes, we should
         * save files to S3 instead. These files are not needed after downloaded by users.
         */

        $filenameTime = time();

        $studentsOptedOutCsvFilename = sprintf('students_opted_out_%s.csv', $filenameTime);
        $studentsOptedOutCsvFh = fopen(__DIR__ . '/../../filestore/' . $studentsOptedOutCsvFilename, 'w');
        fputcsv($studentsOptedOutCsvFh,
            ['group_id', 'course_id', 'student_first_name', 'student_last_name', 'student_email', 'ohm_enrollment_id', 'notes']);

        $studentsNotFoundCsvFilename = sprintf('opt_out_students_not_found_%s.csv', $filenameTime);
        $studentsNotFoundCsvFh = fopen(__DIR__ . '/../../filestore/' . $studentsNotFoundCsvFilename, 'w');
        fputcsv($studentsNotFoundCsvFh,
            ['group_id', 'course_id', 'student_first_name', 'student_last_name', 'student_email', 'errors']);

        /*
         * Go through the provided CSV file and opt out any students we can find.
         */

        $this->DBH->beginTransaction(); // If processing stops early, don't save partial changes.

        $csvFh = fopen($csvFileName, "r");
        while ($csvline = fgetcsv($csvFh, 10000)) {
            $groupId = $csvline[0];
            $courseId = $csvline[1];
            $studentFirstName = $csvline[2];
            $studentLastName = $csvline[3];
            $studentEmail = $csvline[4];

            // Ignore invalid CSV rows, like headers.
            if (!is_numeric($groupId) || !is_numeric($courseId)) {
                continue;
            }

            // Attempt to get current state using all information.
            $currentOptOutState = $this->getOptOutStatus(
                $groupId, $courseId, $studentFirstName, $studentLastName, $studentEmail);

            // Attempt to get current state without email address.
            if (is_null($currentOptOutState)) {
                $currentOptOutState = $this->getOptOutStatus(
                    $groupId, $courseId, $studentFirstName, $studentLastName);
            }

            // Failed to find the student?
            if (is_null($currentOptOutState)) {
                $newCsvRow = array_merge($csvline, ['Unable to find student enrollment record.']);
                fputcsv($studentsNotFoundCsvFh, $newCsvRow);
                $totalStudentsNotFound++;
                continue;
            }

            // Is the student already opted out?
            if ($currentOptOutState['optOutState']) {
                $enrollmentId = $currentOptOutState['enrollmentId'];
                $newCsvRow = array_merge($csvline, [$enrollmentId, 'Student was already opted out.']);
                fputcsv($studentsOptedOutCsvFh, $newCsvRow);
                $totalStudentsOptedOut++;
                continue;
            }

            // Opt the student out of assessments.
            $optOutUpdated = $this->setStudentOptedOutByEnrollmentId($currentOptOutState['enrollmentId'], true);
            if ($optOutUpdated['optOutStateIsUpdated']) {
                $enrollmentId = $currentOptOutState['enrollmentId'];
                $newCsvRow = array_merge($csvline, [$enrollmentId]);
                fputcsv($studentsOptedOutCsvFh, $newCsvRow);
                $totalStudentsOptedOut++;
            } else {
                $errors = implode(', ', $optOutUpdated['errors']);
                $newCsvRow = array_merge($csvline, [$errors]);
                fputcsv($studentsNotFoundCsvFh, $newCsvRow);
                $totalStudentsNotFound++;
            }
        }

        $this->DBH->commit();

        return [
            'totalStudentsOptedOut' => $totalStudentsOptedOut,
            'totalStudentsNotFound' => $totalStudentsNotFound,
            'studentsOptedOutCsvFilename' => $studentsOptedOutCsvFilename,
            'studentsNotFoundCsvFilename' => $studentsNotFoundCsvFilename,
        ];
    }

    /**
     * Determine if a file is a CSV file.
     *
     * @param string $filename The path and filename to check.
     * @return bool True if file is a CSV file. False if not.
     */
    public function isCsvFile(string $filename): bool
    {
        $uploadedFileType = mime_content_type($filename);
        return 'text/csv' == $uploadedFileType;
    }

    /**
     * Set a students's assessment opt out status by their user and course IDs.
     *
     * @param int $userId The student's user ID.
     * @param int $courseId The student's course ID.
     * @param bool $newOptedOutState The new opt out state.
     * @return array An associative array with a change result, if any, or an error message.
     *               Example: [
     *                   "optOutStateIsUpdated": false,
     *                   "errors": [
     *                       "Enrollment record not found for enrollment ID: 42"
     *                   ]
     *               ]
     */
    public function setStudentOptedOut(int $userId, int $courseId, bool $newOptedOutState): array
    {
        $query = 'UPDATE imas_students SET is_opted_out_assessments = :optedOut
                  WHERE userid = :userId AND courseid = :courseId';
        $stm = $this->DBH->prepare($query);
        $stm->execute([
            ':userId' => $userId,
            ':courseId' => $courseId,
            ':optedOut' => $newOptedOutState,
        ]);

        if (1 === $stm->rowCount()) {
            return [
                'optOutStateIsUpdated' => true,
                'errors' => [],
            ];
        } else {
            return [
                'optOutStateIsUpdated' => false,
                'errors' => [
                    sprintf('Enrollment record not found for user ID %d and course ID %d.', $userId, $courseId),
                ],
            ];
        }
    }

    /**
     * Update a student's assessment opt out status.
     *
     * This will enable or disable a student's access to course assesments.
     *
     * This is done by updating their enrollment row in imas_students to 0 or 1.
     *
     * @param int $enrollmentId The enrollment ID for the student.
     * @param bool $newOptedOutState True to opt the student out. False to opt in.
     * @return array An associative array with a change result, if any, or an error message.
     *               Example: [
     *                   "optOutStateIsUpdated": false,
     *                   "errors": [
     *                       "Enrollment record not found for enrollment ID: 42"
     *                   ]
     *               ]
     */
    private function setStudentOptedOutByEnrollmentId(int $enrollmentId, bool $newOptedOutState): array
    {
        $query = 'UPDATE imas_students SET is_opted_out_assessments = :optedOut WHERE id = :enrollmentId';
        $stm = $this->DBH->prepare($query);
        $stm->execute([
            ':enrollmentId' => $enrollmentId,
            ':optedOut' => $newOptedOutState,
        ]);

        if (1 === $stm->rowCount()) {
            return [
                'optOutStateIsUpdated' => true,
                'errors' => [],
            ];
        } else {
            return [
                'optOutStateIsUpdated' => false,
                'errors' => ['Enrollment record not found for enrollment ID: ' . $enrollmentId],
            ];
        }
    }

    /**
     * Get a student's current opt out (of course assessments) status by:
     * - Course ID
     * - Course owner's group ID
     * - Student's first and last name
     * - Student's email address (optional)
     *
     * @param int $groupId The course owner's group ID.
     * @param int $courseId The course ID.
     * @param string $studentFirstName The student's first name.
     * @param string $studentLastName The student's last name.
     * @param ?string $studentEmail The student's email. (optional)
     * @return array|null Null if nothing found. If enrollment is found:
     *                    [
     *                        'enrollmentId' => 42,
     *                        'optOutState' => false,
     *                    ]
     */
    private function getOptOutStatus(
        int     $groupId,
        int     $courseId,
        string  $studentFirstName,
        string  $studentLastName,
        ?string $studentEmail = null
    ): ?array
    {
        if (is_null($studentEmail)) {
            $queryEmail = '';
            $executeEmailParams = [];
        } else {
            $queryEmail = 'AND su.email = :email';
            $executeEmailParams = [':email' => $studentEmail];
        }

        $query = "SELECT
	e.id,
    e.is_opted_out_assessments
FROM imas_students AS e
	JOIN imas_users AS su ON su.id = e.userid
	JOIN imas_courses AS c ON c.id = e.courseid
    JOIN imas_users AS tu ON tu.id = c.ownerid
WHERE tu.groupid = :groupId
	AND c.id = :courseId
    AND su.FirstName = :firstName
    AND su.LastName = :lastName
    $queryEmail
";
        $stm = $this->DBH->prepare($query);
        $stm->execute(
            array_merge([
                ':groupId' => $groupId,
                ':courseId' => $courseId,
                ':firstName' => $studentFirstName,
                ':lastName' => $studentLastName,
            ], $executeEmailParams)
        );

        if (0 === $stm->rowCount()) {
            return null;
        }

        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $enrollmentId = $row['id'];
        $optOutState = !!$row['is_opted_out_assessments'];

        return [
            'enrollmentId' => $enrollmentId,
            'optOutState' => $optOutState,
        ];
    }
}
