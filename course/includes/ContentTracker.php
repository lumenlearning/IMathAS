<?php
/**
 * Repo iMathAS: Content Tracker
 */

namespace Course\Includes;

require_once(__DIR__ . '/../../vendor/autoload.php');

use DateTime;
use PDO;

class ContentTracker
{
    const STUDENTS = [10];
    const TEACHERS = [20,40,75,100];

    public static function addTracking($item, $track_type, $info = '')
    {
        $query = "INSERT INTO imas_content_track (userid,courseid,type,typeid,viewtime,info) VALUES "
            . "(:userid, :courseid, :type, :typeid, :viewtime, :info)";
        $stm = $GLOBALS['DBH']->prepare($query);
        $stm->execute(array(
            ':userid'=>$GLOBALS['userid'],
            ':courseid'=>$item->courseid,
            ':type'=>$track_type,
            ':typeid'=>$item->typeid,
            ':viewtime'=>time(),
            ':info' => $info
        ));
    }
    public static function findActions($cid, $typeid, $types)
    {
        $query_placeholders = \Sanitize::generateQueryPlaceholders($types);
        $query = "SELECT type,typeid,viewtime,info FROM imas_content_track "
            . "WHERE courseid=? AND typeid=? AND type IN ($query_placeholders) ORDER BY viewtime DESC";
        $stm = $GLOBALS['DBH']->prepare($query);
        array_unshift($types, $cid, $typeid);
        $stm->execute($types);
        return $stm->fetchAll(\PDO::FETCH_NUM);
    }

    /**
     * Count the number of STUDENTS tracked, grouped by school.
     *
     * @param array<string> $types The item tracking types.
     *                                Example: ['desmosview', 'desmoscalc']
     * @param DateTime $startTimestamp The beginning date range.
     * @param DateTime $endTimestamp The ending date range.
     * @param bool $ltiOnly True to count LTI students. False to count all students.
     * @param int|null $groupId A school's group ID. If null, all schools are returned.
     * @param PDO|null $dbh A database connection.
     * @return array Associative array of groupIds and counts.
     * @see self::STUDENTS
     */
    public static function countUniqueStudentsByGroup(array $types,
                                                      DateTime $startTimestamp,
                                                      DateTime $endTimestamp,
                                                      bool $ltiOnly,
                                                      ?int $groupId = null,
                                                      ?PDO $dbh = null
    ): array
    {
        return ContentTracker::countUniqueUsersByGroup($types, self::STUDENTS,
            $startTimestamp, $endTimestamp, $ltiOnly, $groupId, $dbh);
    }

    /**
     * Count the number of TEACHERS tracked, grouped by school.
     *
     * @param array<string> $types The item tracking types.
     *                                Example: ['desmosview', 'desmoscalc']
     * @param DateTime $startTimestamp The beginning date range.
     * @param DateTime $endTimestamp The ending date range.
     * @param bool $ltiOnly True to count LTI teachers. False to count all teachers.
     * @param int|null $groupId A school's group ID. If null, all schools are returned.
     * @param PDO|null $dbh A database connection.
     * @return array Associative array of groupIds and counts.
     * @see self::TEACHERS
     */
    public static function countUniqueTeachersByGroup(array $types,
                                                      DateTime $startTimestamp,
                                                      DateTime $endTimestamp,
                                                      bool $ltiOnly,
                                                      ?int $groupId = null,
                                                      ?PDO $dbh = null
    ): array
    {
        return ContentTracker::countUniqueUsersByGroup($types, self::TEACHERS,
            $startTimestamp, $endTimestamp, $ltiOnly, $groupId, $dbh);
    }

    /**
     * Count the number of unique users tracked, grouped by school.
     *
     * @param array<string> $types The item tracking types.
     *                                Example: ['desmosview', 'desmoscalc']
     * @param array<int> $rights The user rights to search on.
     * @param DateTime $startTimestamp The beginning date range.
     * @param DateTime $endTimestamp The ending date range.
     * @param bool $ltiOnly True to count LTI users. False to count all users.
     * @param int|null $groupId A school's group ID. If null, all schools are returned.
     * @param PDO|null $dbhOverride A database connection.
     * @return array Associative array of groupIds and counts.
     */
    protected static function countUniqueUsersByGroup(array $types,
                                                      array $rights,
                                                      DateTime $startTimestamp,
                                                      DateTime $endTimestamp,
                                                      bool $ltiOnly,
                                                      ?int $groupId = null,
                                                      ?PDO $dbhOverride = null
    ): array
    {
        $dbh = is_null($dbhOverride) ? $GLOBALS['DBH'] : $dbhOverride;

        $typesSanitized = array_map('Sanitize::simpleString', $types);
        $typeList = "'" . implode("', '", $typesSanitized) . "'";
        $rightsList = implode(',', array_map('intval', $rights));
        $groupIdSql = is_null($groupId) ? '' : 'AND tu.groupid = ' . $groupId;
        $ltiUsers = false === $ltiOnly ? '' : 'AND (su.SID LIKE "lti-%" OR tu.SID LIKE "lti-%"'; // $rightsList should limit this.
        $query = sprintf("SELECT
                COUNT(DISTINCT su.id) AS user_count,
                tg.id AS group_id
            FROM imas_users AS su
                JOIN imas_content_track AS ct
                    FORCE INDEX (viewtime) -- Full table scans for ct.viewtime without this!
                    ON ct.userid = su.id
                JOIN imas_courses AS c ON c.id = ct.courseid
                JOIN imas_users AS tu ON tu.id = c.ownerid
                JOIN imas_groups AS tg ON tg.id = tu.groupid
            WHERE su.rights IN ($rightsList)
                AND ct.type IN ($typeList)
                AND ct.viewtime >= :startTimestamp
                AND ct.viewtime <= :endTimestamp
                $groupIdSql
                $ltiUsers
            GROUP BY tg.id
");
        $params = [
            ':startTimestamp' => $startTimestamp->getTimestamp(),
            ':endTimestamp' => $endTimestamp->getTimestamp()
        ];

        $stm = $dbh->prepare($query);
        $stm->execute($params);

        $totalCounts = [];
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $totalCounts[$row['group_id']] = $row['user_count'];
        }
        return $totalCounts;
    }

    /**
     * Count the total number of unique {$types} across ALL users.
     *
     * @param array<string> $types The item tracking types.
     *                                Example: ['desmosview', 'desmoscalc']
     * @param DateTime $startTimestamp The beginning date range.
     * @param DateTime $endTimestamp The ending date range.
     * @param bool $ltiOnly True to count LTI users. False to count all users.
     * @param PDO|null $dbhOverride A database connection.
     * @return int The total number of unique users.
     */
    public static function countTotalUniqueUsers(array $types,
                                                    DateTime $startTimestamp,
                                                    DateTime $endTimestamp,
                                                    bool $ltiOnly,
                                                    ?PDO $dbhOverride = null
    ): int
    {
        $dbh = is_null($dbhOverride) ? $GLOBALS['DBH'] : $dbhOverride;

        $typesSanitized = array_map('Sanitize::simpleString', $types);
        $typeList = "'" . implode("', '", $typesSanitized) . "'";
        $ltiUsers = false === $ltiOnly ? '' : 'AND (u.SID LIKE "lti-%"'; // $rightsList should limit this., typeid

        $query = "SELECT COUNT(userid)
            FROM (
                SELECT userid
                FROM imas_content_track AS ct
                        FORCE INDEX (viewtime) -- Full table scans for ct.viewtime without this!
                    JOIN imas_users AS u ON u.id = ct.userid
                WHERE
                    ct.type IN ($typeList)
                    AND ct.viewtime >= :startTimestamp
                    AND ct.viewtime <= :endTimestamp
                    $ltiUsers
                GROUP BY ct.userid
            ) AS total_views
        ";

        $params = [
            ':startTimestamp' => $startTimestamp->getTimestamp(),
            ':endTimestamp' => $endTimestamp->getTimestamp()
        ];

        $stm = $dbh->prepare($query);
        $stm->execute($params);

        $result = $stm->fetch(PDO::FETCH_NUM)[0];
        return intval($result);
    }
}
