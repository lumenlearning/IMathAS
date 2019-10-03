<?php
namespace OHM\Controlers;
use \PDO;
class Assessments
{

    public static function getCourseAssessmentIds(int $cid, int $aid = null)
    {
        global $DBH;
        $query = "SELECT id as aid, ver"
            . " FROM imas_assessments WHERE courseid = :courseid ";
        $bind[':courseid'] = $cid;
        if (!empty($aid)) {
            $query .= ' AND id = :aid';
            $bind[':aid'] = $aid;
        }
        $stm = $DBH->prepare($query);
        $stm->execute($bind);
        if ($stm->rowCount() == 0) {
            return false;
        } else {
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    public static function getAssessmentRecords(array $assessments, int $uid = null)
    {
        foreach ($assessments as $assess) {
            if ($assess['ver'] == 2) {
                $assess2[] = $assess['aid'];
            } else {
                $assess1[] = $assess['aid'];
            }
        }
        $results = array();
        if (!empty($assess2)) {
            $results = array_merge(
                $results,
                self::getAssessmentResults(
                    $assess2,
                    $uid,
                    2
                )
            );
        }
        if (!empty($assess1)) {
            $results = array_merge(
                $results,
                self::getAssessmentResults($assess1, $uid, 1)
            );
        }
        return $results;
    }
    public static function getAssessmentResults(array $aids, int $uid = null, int $ver = null)
    {
        global $DBH;
        if ($ver == 2) {
            $query = "SELECT 2 as ver, userid as uid, assessmentid as aid, lti_sourcedid as sourcedid, score as scores "
                . " FROM imas_assessment_records WHERE assessmentid IN (:assessmentids)";
        } else {
            $query = "SELECT 1 as ver, userid as uid, assessmentid as aid, lti_sourcedid as sourcedid, bestscores as scores "
                . " FROM imas_assessment_sessions WHERE assessmentid IN (:assessmentids)";
        }
        $bind[':assessmentids'] = implode(",", $aids);
        if (!empty($uid)) {
            $query .= " AND userid = :userid";
            $bind[':userid'] = $uid;
        }
        $stm = $DBH->prepare($query);
        $stm->execute($bind);
        if ($stm->rowCount() > 0) {
            return $results = $stm->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    public static function getCourseName(int $courseid)
    {
        global $DBH;
        $query = "SELECT name, id "
            . " FROM imas_courses WHERE imas_courses.id = :courseid ";
        $stm = $DBH->prepare($query);
        $stm->execute(array(':courseid' => $courseid));
        if ($stm->rowCount() == 0) {
            return false;
        } else {
            return $stm->fetch(PDO::FETCH_ASSOC);
        }
    }
    public static function getAssessmentName(int $aid)
    {
        global $DBH;
        $query = "SELECT name, id "
            . " FROM imas_assessments WHERE id = :aid ";
        $stm = $DBH->prepare($query);
        $stm->execute(array(':aid' => $aid));
        if ($stm->rowCount() == 0) {
            return false;
        } else {
            return $stm->fetch(PDO::FETCH_ASSOC);
        }
    }
    public static function getpts(string $sc)
    {
        if (strpos($sc, '~')===false) {
            if ($sc>0) {
                return $sc;
            } else {
                return 0;
            }
        } else {
            $sc = explode('~', $sc);
            $tot = 0;
            foreach ($sc as $s) {
                if ($s>0) {
                    $tot+=$s;
                }
            }
            return round($tot, 1);
        }
    }
    /**
     * Function for calculating points possible for an assessment
     * (c) IMathAS 2018
     *
     * @param int $aid       assessment id from imas_assessments.id
     * @param int $itemorder order in syllabus?
     * @param int $defpoints number of points per question?
     *
     * @return array
     */
    public static function updatePointsPossible(int $aid, $itemorder = null, $defpoints = null)
    {
        global $DBH;

        if ($itemorder === null || $defpoints === null) {
            $stm = $DBH->prepare("SELECT itemorder,defpoints FROM imas_assessments WHERE id=?");
            $stm->execute(array($aid));
            list($itemorder,$defpoints) = $stm->fetch(PDO::FETCH_NUM);
        }

        $stm = $DBH->prepare("SELECT id,points FROM imas_questions WHERE assessmentid=? AND points<9999");
        $stm->execute(array($aid));
        $questionpointdata = array();
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $questionpointdata[$row['id']] = $row['points'];
        }
        $poss = self::calcPointsPossible($itemorder, $questionpointdata, $defpoints);

        $stm = $DBH->prepare("UPDATE imas_assessments SET ptsposs=? WHERE id=?");
        $stm->execute(array($poss, $aid));
        return $poss;
    }

    public static function calcPointsPossible($itemorder, $questionpointdata, $defpoints) {
        if (is_array($itemorder)) {
            $aitems = $itemorder;
        } else {
            $aitems = explode(',', $itemorder);
        }

        $totalpossible = 0;
        foreach ($aitems as $v) {
            if (strpos($v,'~')!==FALSE) {
                $sub = explode('~',$v);
                if (strpos($sub[0],'|')===false) { //backwards compat
                    $totalpossible += (isset($questionpointdata[$sub[0]]))?$questionpointdata[$sub[0]]:$defpoints;
                } else {
                    $grpparts = explode('|',$sub[0]);
                    if ($grpparts[0]==count($sub)-1) { //handle diff point values in group if n=count of group
                        for ($i=1;$i<count($sub);$i++) {
                            $totalpossible += (isset($questionpointdata[$sub[$i]]))?$questionpointdata[$sub[$i]]:$defpoints;
                        }
                    } else {
                        $totalpossible += $grpparts[0]*((isset($questionpointdata[$sub[1]]))?$questionpointdata[$sub[1]]:$defpoints);
                    }
                }
            } else {
                $totalpossible += (isset($questionpointdata[$v]))?$questionpointdata[$v]:$defpoints;
            }
        }
        return $totalpossible;
    }
}