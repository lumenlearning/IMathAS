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
     * Determine if a user is opted out of course assessments.
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
}
