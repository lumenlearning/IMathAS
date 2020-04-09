<?php
/**
 * Repo iMathAS: TeacherAuditLog
 */

class TeacherAuditLog
{
    const STUDENTS = [10];
    const TEACHERS = [20,40,75,100];
    const ACTIONS = [
        "Assessment Settings Change",
        "Mass Assessment Settings Change",
        "Mass Assessment Date Change",
        "Question Settings Change",
        "Clear Attempts",
        "Clear Scores",
        "Delete Item",
        "Unenroll",
        "Grade Override"
    ];

    public static function addTracking($courseid, $action, $itemid = null, $metadata = array())
    {
        if (!in_array($action, self::ACTIONS)) {
            //log exception
            return false;
        }
        //always include calling file as source to metadata
        $metadata = ['source'=>basename(debug_backtrace()[0]['file'])]+$metadata;

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
    public static function findActionsByCourse($cid)
    {
        $query = "SELECT id, userid, courseid, action, itemid, metadata, created_at FROM imas_teacher_audit_log "
            . "WHERE courseid=? ORDER BY created_at DESC";
        $stm = $GLOBALS['DBH']->prepare($query);
        $stm->execute([$cid]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }
    public static function findCourseItemAction($cid, $itemid, $action)
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
    public static function findCourseAction($cid, $action)
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
}
