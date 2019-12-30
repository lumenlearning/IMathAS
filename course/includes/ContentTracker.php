<?php
/**
 * Repo iMathAS: Content Tracker
 */

namespace Course\Includes;

class ContentTracker
{
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
}