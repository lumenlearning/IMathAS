<?php

namespace OHM\Includes;

require_once(__DIR__ . "/../exceptions/StudentPaymentException.php");

use OHM\Exceptions\StudentPaymentException;

/**
 * Class StudentPaymentDb - Database operations related to student payment for assessments.
 *
 * In most cases, this class not used directly. The StudentPayment is usually used instead.
 *
 * @see StudentPayment
 *
 * @package OHM
 */
class StudentPaymentDb
{

	private $dbh;

	private $groupId;
	private $courseId;
	private $studentId;

	/**
	 * StudentPaymentDb constructor.
	 *
	 * @param $groupId integer The student's group ID. (MySQL imas_groups. ID column)
	 * @param $courseId integer The course ID. (MySQL table 'imas_courses', ID column)
	 * @param $studentId integer The the student's ID. (MySQL table 'imas_users', ID column)
	 */
	public function __construct($groupId, $courseId, $studentId)
	{
		$this->groupId = $groupId;
		$this->courseId = $courseId;
		$this->studentId = $studentId;

		if (isset($GLOBALS['DBH'])) {
			$this->dbh = $GLOBALS['DBH'];
		}
	}

	/**
	 * Database handle setter. Used within MOM transactions and unit tests.
	 * @param $dbh object The database handle object to use.
	 */
	public function setDbh($dbh)
	{
		$this->dbh = $dbh;
	}

	/**
	 * Get a student's group ID.
	 *
	 * @return integer The student's group ID.
	 * @throws StudentPaymentException Thrown if unable to get student's group ID.
	 */
	public function getStudentGroupId()
	{
		$stm = $this->dbh->prepare("SELECT groupid FROM imas_users WHERE id=:studentid");
		$stm->execute(array(':studentid' => $this->studentId));
		$result = $stm->fetchColumn(0);

		if (null == $result || 1 > $result) {
			throw new StudentPaymentException(sprintf("Unable to get group ID for user ID %d.",
				$this->studentId));
		}

		return $result;
	}

	/**
	 * Get the "enrollment ID" for a student in this course.
	 *
	 * @return int The student's enrollment ID for this course.
	 * @throws StudentPaymentException Thrown if unable to get student's enrollment ID.
	 */
	public function getStudentEnrollmentId()
	{
		$stm = $this->dbh->prepare("SELECT id FROM imas_students WHERE userid=:studentid AND courseid=:courseid");
		$stm->execute(array(':studentid' => $this->studentId, ':courseid' => $this->courseId));
		$result = $stm->fetchColumn(0);

		if (null == $result) {
			throw new StudentPaymentException(sprintf(
				"Unable to get student enrollment ID for course ID %d for student ID %d.", $this->courseId,
				$this->studentId));
		}

		return $result;
	}

	/**
	 * Determine if the student has a valid access code for this course.
	 *
	 * @return boolean True if yes. False if no. Null if unknown.
	 */
	public function getStudentHasActivationCode()
	{
		$stm = $this->dbh->prepare("SELECT has_valid_access_code FROM imas_students
										WHERE userid=:studentid AND courseid=:courseid");
		$stm->execute(array(':studentid' => $this->studentId, ':courseid' => $this->courseId));
		$result = $stm->fetchColumn(0);

		return $result;
	}

	/**
	 * Set the "student has a valid access code" setting for this course.
	 *
	 * @param $hasCode boolean True for yes. False for no. Null for unknown.
	 * @throws StudentPaymentException Thrown if a non-boolean argument is given.
	 */
	public function setStudentHasActivationCode($hasCode)
	{
		if ("boolean" != gettype($hasCode)) {
			throw new StudentPaymentException("Invalid non-boolean value: " . $hasCode);
		}

		$stm = $this->dbh->prepare("UPDATE imas_students SET has_valid_access_code = :hascode
										WHERE userid=:studentid AND courseid=:courseid");
		$stm->execute(array(':hascode' => $hasCode, ':studentid' => $this->studentId, ':courseid' => $this->courseId));
	}

	/**
	 * Determine if this course requires student payment for assessments.
	 *
	 * If this method returns null, an API call to the student payment API will be required.
	 * This method does not perform that API call.
	 *
	 * @return bool True if student payment is required. False if not. Null if unknown.
	 */
	public function getCourseRequiresStudentPayment()
	{
		$stm = $this->dbh->prepare("SELECT student_pay_required FROM imas_courses WHERE id=:courseid");
		$stm->execute(array(':courseid' => $this->courseId));
		$result = $stm->fetchColumn(0);

		return $result;
	}

	/**
	 * Set the "is student payment required" setting on a course.
	 *
	 * @param bool $studentPaymentRequired True if payment is required. False if not.
	 * @throws StudentPaymentException Thrown if a non-boolean argument is given.
	 */
	public function setCourseRequiresStudentPayment($studentPaymentRequired)
	{
		if ("boolean" != gettype($studentPaymentRequired)) {
			throw new StudentPaymentException("Invalid non-boolean value: " . $studentPaymentRequired);
		}

		$stm = $this->dbh->prepare("UPDATE imas_courses SET student_pay_required = :required WHERE id=:courseid");
		$stm->execute(array(':required' => $studentPaymentRequired, ':courseid' => $this->courseId));
	}

	/**
	 * Determine if this group MAY require student payment for assessments.
	 *
	 * @return bool True if student payment may be required. False if not.
	 * @throws StudentPaymentException Thrown if unable to get student payment value.
	 */
	public function getGroupRequiresStudentPayment()
	{
		if (!isset($this->groupId) || null == $this->groupId) {
			$this->groupId = $this->getStudentGroupId();
		}

		$stm = $this->dbh->prepare("SELECT name, student_pay_enabled FROM imas_groups WHERE id=:groupid");
		$stm->execute(array(':groupid' => $this->groupId));
		$result = $stm->fetch(\PDO::FETCH_ASSOC);

		$studentPayRequired = $result['student_pay_enabled'];

		if (null == $studentPayRequired) {
			throw new StudentPaymentException(sprintf(
				"Unable to determine if group ID %d (%s) may require student payment for course ID %d.",
				$this->groupId, $result['name'], $this->courseId));
		}

		return $studentPayRequired;
	}

	/**
	 * Set the "is student payment required" setting for a group.
	 *
	 * @param bool $studentPaymentRequired True if payment is required. False if not.
	 * @throws StudentPaymentException Thrown if a non-boolean argument is given.
	 */
	public function setGroupRequiresStudentPayment($studentPaymentRequired)
	{
		if ("boolean" != gettype($studentPaymentRequired)) {
			throw new StudentPaymentException("Invalid non-boolean value: " . $studentPaymentRequired);
		}

		if (!isset($this->groupId) || null == $this->groupId) {
			$this->groupId = $this->getStudentGroupId();
		}

		$stm = $this->dbh->prepare("UPDATE imas_groups SET student_pay_enabled = :required WHERE id=:groupid");
		$stm->execute(array(':required' => $studentPaymentRequired, ':groupid' => $this->groupId));
	}

	/**
	 * Toggle student payments for all courses belonging to a group ID.
	 *
	 * @param $groupId integer The group ID.
	 * @param $paymentSetting boolean True or false; the payment setting to apply to the courses.
	 * @return boolean True on success.
	 */
	public function setStudentPaymentAllCoursesByGroupId($groupId, $paymentSetting)
	{
		$stm = $this->dbh->prepare("UPDATE imas_courses AS c
										JOIN imas_users AS u ON c.ownerid = u.id
										SET student_pay_required = :paymentSetting
											WHERE u.groupid = :groupId");
		$stm->execute(array(
			':groupId' => $groupId,
			':paymentSetting' => $paymentSetting
		));

		return true;
	}

	/**
	 * Get a course owner's group ID.
	 *
	 * @return integer The course ID. If none, 0 will be returned
	 */
	function getCourseOwnerGroupId()
	{
		$sth = $this->dbh->prepare("SELECT u.groupid FROM imas_courses AS c
											JOIN imas_users AS u ON u.id = c.ownerid
											WHERE c.id = :id");
		$sth->execute(array(':id' => $this->courseId));
		$results = $sth->fetch(\PDO::FETCH_ASSOC);
		$groupId = $results['groupid'];

		if (is_null($groupId) || 0 >= $groupId) {
			return 0;
		}

		return $groupId;
	}

	/**
	 * Get the group's Lumen GUID. If none found, null is returned.
	 *
	 * @return string|null The group's Lumen GUID. Null if not found.
	 * @throws StudentPaymentException Thrown if this class is instantiated
	 * without a group ID.
	 */
	function getLumenGuid()
	{
		if (is_null($this->groupId)) {
			throw new StudentPaymentException("Group ID is undefined."
				. " Please provide a group ID when instantiating this class.");
		}

		$sth = $this->dbh->prepare("SELECT g.lumen_guid FROM imas_groups AS g
											WHERE g.id = :id");
		$sth->execute(array(':id' => $this->groupId));
		$results = $sth->fetch(\PDO::FETCH_ASSOC);
		$lumenGuid = $results['lumen_guid'];

		return $lumenGuid;
	}
}
