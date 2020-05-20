<?php
/**
 * Repo iMathAS: TeacherAuditLog
 */

class TeacherAuditLog
{
    const STUDENTS = [10];
    const TEACHERS = [20,40,75,100];
    /**
     * Maps to ENUM
     */
    const ACTIONS = [
        "Assessment Settings Change",
        "Mass Assessment Settings Change",
        "Mass Date Change",
        "Question Settings Change",
        "Clear Attempts",
        "Clear Scores",
        "Delete Item",
        "Unenroll",
        "Change Grades"
    ];

    /**
     * @param  int      $courseid resolves to imas_courses.id
     * @param  string   $action   must match the ACTIONS const
     * @param  int|null $itemid   depends on the "action" of tracking
     * @param  array    $metadata extra details to store such as grade
     * @return bool whether tracking has been added or not
     */
    public static function addTracking(int $courseid, string $action, ?int $itemid = null, array $metadata = []): bool
    {
        if (!in_array($action, self::ACTIONS)) {
            //log exception
            return false;
        }
        //always include calling file as source to metadata
        $metadata = ['source'=>parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)]+$metadata;

        $query = "INSERT INTO imas_teacher_audit_log (userid,courseid,action,itemid,metadata) VALUES "
            . "(:userid, :courseid, :action, :itemid, :metadata)";
        $stm = $GLOBALS['DBH']->prepare($query);
        return $stm->execute(array(
            ':userid'=>$GLOBALS['userid'],
            ':courseid'=>$courseid,
            ':action'=>$action,
            ':itemid'=>$itemid,
            ':metadata' => json_encode($metadata)
        ));
    }

    /**
     * @param  int $cid resolves to imas_courses.id
     * @return array associative array of logs
     */
    public static function findActionsByCourse(int $cid): array
    {
        $query = "SELECT id, userid, courseid, action, itemid, metadata, created_at FROM imas_teacher_audit_log "
            . "WHERE courseid=? ORDER BY created_at DESC";
        $stm = $GLOBALS['DBH']->prepare($query);
        $stm->execute([$cid]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param  int $cid    resolves to imas_courses.id
     * @param  int $itemid depends on the "action" of tracking
     * @param  int $action must match the ACTIONS const
     * @return array associative array of logs
     */
    public static function findCourseItemAction(int $cid, int $itemid, int $action): array
    {
        $query = "SELECT id, userid, courseid, action, itemid, metadata, created_at FROM imas_teacher_audit_log "
            . "WHERE courseid=? AND itemid=? AND action=? ORDER BY created_at DESC";
        $stm = $GLOBALS['DBH']->prepare($query);
        $stm->execute([
            $cid,
            $itemid,
            $action
        ]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param  int    $cid    resolves to imas_courses.id
     * @param  string $action must match the ACTIONS const
     * @return array associative array of logs
     */
    public static function findCourseAction(int $cid, string $action): array
    {
        $query = "SELECT id, userid, courseid, action, itemid, metadata, created_at FROM imas_teacher_audit_log "
            . "WHERE courseid=? AND action=? ORDER BY created_at DESC";
        $stm = $GLOBALS['DBH']->prepare($query);
        $stm->execute([
            $cid,
            $action
        ]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param  array $cid     resolves to imas_courses.id
     * @param  array $actions must match the ACTIONS const
     * @return array associative array of course action counts with courseid as key
     */
    public static function countActionsByCourse(array $cid, array $actions): array
    {
        $ph1 = \Sanitize::generateQueryPlaceholders($cid);
        $ph2 = \Sanitize::generateQueryPlaceholders($actions);
        $query = "SELECT courseid, action, count(action) as itemcount FROM imas_teacher_audit_log "
            . "WHERE courseid in ($ph1) AND action in ($ph2) GROUP BY courseid, action";
        $stm = $GLOBALS['DBH']->prepare($query);
        $stm->execute(array_merge($cid,$actions));

        $courses = array();
        while ($row = $stm->fetch(\PDO::FETCH_ASSOC)) {
            $courses[$row['courseid']]['courseid'] = $row['courseid'];
            $action = substr($row['action'], strpos($row['action'], " ") + 1);
            $courses[$row['courseid']][$action] = $row['itemcount'];
        }
        return $courses;
    }

    /**
     * @param  array      $actions        must match the ACTIONS const
     * @param  DateTime   $startTimestamp starting date range
     * @param  DateTime   $endTimestamp   ending date range
     * @param  array|null $teacher        imas_users.id of teacher
     * @return array associative array of teacher actions with teacher userid as key
     */
    public static function countActionsByTeacher(
        array $actions,
        DateTime $startTimestamp,
        DateTime $endTimestamp,
        ?array $teacher = null
    ): array
    {
        $ph = \Sanitize::generateQueryPlaceholders($actions);
        $query = "SELECT g.name, u.FirstName, u.LastName, l.userid, l.action, count(l.action) as itemcount 
            FROM imas_teacher_audit_log as l JOIN imas_users as u ON l.userid = u.id
            LEFT JOIN imas_groups AS g ON u.groupid=g.id
            WHERE l.action in ($ph) AND l.created_at >= ? AND l.created_at <= ? 
            GROUP BY l.userid, l.action";
        $stm = $GLOBALS['DBH']->prepare($query);
        $params = array_merge($actions, [$startTimestamp->format("Y-m-d H:i:s"),$endTimestamp->format("Y-m-d H:i:s")]);

        $stm->execute($params);

        $teachers = array();
        while ($row = $stm->fetch(\PDO::FETCH_ASSOC)) {
            $teachers[$row['userid']]['userid'] = $row['userid'];
            $teachers[$row['userid']]['firstName'] = $row['FirstName'];
            $teachers[$row['userid']]['lastName'] = $row['LastName'];
            $teachers[$row['userid']]['group'] = $row['name'];
            $action = substr($row['action'], strpos($row['action'], " ") + 1);
            $teachers[$row['userid']][$action] = $row['itemcount'];
        }
        return $teachers;

    }
}