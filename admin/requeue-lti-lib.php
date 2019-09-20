<?php

namespace OHM\Admin;

require '../init.php';
require_once("../includes/ltioutcomes.php");

use PDO;
use Sanitize;

if ($GLOBALS['myrights'] < 100) {
    echo 'You must be an admin';
    exit;
}

/*
 * user score
 *      sourcedid
 *      aid
 *      scores
 * course
 *
 * imas_assessment_records
 *      assessmentid (aid)
 *      userid
 *      lti_sourcedid (sourcedid)
 *      score   (single scores)
 *
 *  imas_assessment_sessions
 *      assessmentid (aid)
 *      userid
 *      lti_sourcedid (sourcedid)
 *      bestscores   (comma separated scores)
 *
 * imas_courses
 *      id
 *      ownerid
 *      name
 *
 * imas_ltiqueue
 *      hash
 *      sourcedid
 *      grade
 *
 */
class RequeueLtiLib
{
	public static function requestLMSGrade(string $sourcedid)
	{
		global $DBH;

		list($lti_sourcedid, $ltiurl, $ltikey, $keytype) = explode(':|:', $sourcedid);

		$secret = '';
		if (strlen($lti_sourcedid) > 1 && strlen($ltiurl) > 1 && strlen($ltikey) > 1) {
			if ($keytype == 'c') {
				$keyparts = explode('_', $ltikey);
				$stm = $DBH->prepare("SELECT ltisecret FROM imas_courses WHERE id=:id");
				$stm->execute(array(':id' => $keyparts[1]));
				if ($stm->rowCount() > 0) {
					$secret = $stm->fetchColumn(0);
				}
			} else {
				$stm = $DBH->prepare("SELECT password FROM imas_users WHERE SID=:SID AND (rights=11 OR rights=76 OR rights=77)");
				$stm->execute(array(':SID' => $ltikey));
				if ($stm->rowCount() > 0) {
					$secret = $stm->fetchColumn(0);
				}
			}
		}
		if ($secret != '') {
			//echo "<p>Calling $ltiurl with $ltikey, $secret, and $lti_sourcedid</p>";
			$value = sendLTIOutcome('read', $ltikey, $secret, $ltiurl, $lti_sourcedid, 0, true);
			if (isset($value[1])) {
				$grade = preg_replace('/.*textString\>([\d\.]*)\<\/textString.*/', '$1', $value[1]);
				if (!empty($grade)) {
					return $grade;
				} else {
					return Sanitize::encodeStringForDisplay($value[1]);
				}
			} else {
				return "unable to read LTI grade";
			}
		} else {
			return "Unable to lookup secret";
		}
	}

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
			$results = array_merge($results,
				self::getAssessmentResults($assess2, $uid, 2));
		}
		if (!empty($assess1)) {
			$results = array_merge($results,
				self::getAssessmentResults($assess1, $uid, 1));
		}
		return $results;
	}

	public static function getAssessmentResults(array $aids, int $uid = null, int $ver = 1)
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

	/*
	 * iMathAS functions
	 */
	public static function getpts(array $scs)
	{
		$tot = 0;
		foreach (explode(',', $scs) as $sc) {
			$qtot = 0;
			if (strpos($sc, '~') === false) {
				if ($sc > 0) {
					$qtot = $sc;
				}
			} else {
				$sc = explode('~', $sc);
				foreach ($sc as $s) {
					if ($s > 0) {
						$qtot += $s;
					}
				}
			}
			$tot += round($qtot, 1);
		}
		return $tot;
	}

	public static function reCalcandupdateLTIgrade(int $aid, $scores)
	{
		global $DBH;
		$stm = $DBH->prepare("SELECT ptsposs,itemorder,defpoints FROM imas_assessments WHERE id=:id");
		$stm->execute(array(':id' => $aid));
		$line = $stm->fetch(PDO::FETCH_ASSOC);
		if ($line['ptsposs'] == -1) {
			$line['ptsposs'] = updatePointsPossible($aid, $line['itemorder'], $line['defpoints']);
		}
		$aidposs = $line['ptsposs'];
		$allans = true;
		if (is_array($scores)) {
			// old assesses
			$total = 0;
			for ($i = 0; $i < count($scores); $i++) {
				if ($allans && strpos($scores[$i], '-1') !== FALSE) {
					$allans = false;
				}
				if (getpts($scores[$i]) > 0) {
					$total += getpts($scores[$i]);
				}
			}
		} else {
			// new assesses
			$total = getpts($scores);
		}
		$grade = min(1, max(0, $total / $aidposs));
		$grade = number_format($grade, 8);
		return $grade;
	}
}
