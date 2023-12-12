<?php

namespace OHM\Includes;

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

	private $courseId;
	private $studentUserId;
	private $studentGroupId;
	private $courseOwnerUserId;
	private $courseOwnerGroupId;

	/**
	 * StudentPaymentDb constructor.
	 *
	 * @param $studentGroupId integer The student's group ID.
	 * @param $courseId integer The course ID.
	 * @param $studentUserId integer The enrolled student's user ID.
	 * @param $courseOwnerGroupId int|null The course owner's group ID.
	 * @param $courseOwnerUserId int|null The course owner's user ID.
	 */
	public function __construct($studentGroupId, $courseId, $studentUserId,
								?int $courseOwnerGroupId, ?int $courseOwnerUserId)
	{
		$this->courseId = $courseId;
		$this->studentUserId = $studentUserId;
		$this->studentGroupId = $studentGroupId;
		$this->courseOwnerUserId = $courseOwnerUserId;
		$this->courseOwnerGroupId = $courseOwnerGroupId;

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
		$stm->execute(array(':studentid' => $this->studentUserId));
		$result = $stm->fetchColumn(0);

		if (null == $result || 1 > $result) {
			throw new StudentPaymentException(sprintf("Unable to get group ID for user ID %d.",
				$this->studentUserId));
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
		$stm->execute(array(':studentid' => $this->studentUserId, ':courseid' => $this->courseId));
		$result = $stm->fetchColumn(0);

		if (null == $result) {
			throw new StudentPaymentException(sprintf(
				"Unable to get student enrollment ID for course ID %d for student ID %d.", $this->courseId,
				$this->studentUserId));
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
		$stm->execute(array(':studentid' => $this->studentUserId, ':courseid' => $this->courseId));
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
		$stm->execute(array(':hascode' => $hasCode, ':studentid' => $this->studentUserId, ':courseid' => $this->courseId));
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
		$success = $stm->execute(array(':required' => $studentPaymentRequired, ':courseid' => $this->courseId));

		if (!$success) {
			throw new StudentPaymentException("Unable to set payment setting for course ID: " . $this->courseId);
		}
	}

	/**
	 * Get the group ID that should be used for paywall decisions.
	 *
	 * Ideally, this should always be the course owner's group ID.
	 * Valid group IDs are row IDs from imas_groups.
	 *
	 * The group ID returned is prioritized in this order, based on available
	 * information passed in on instantiation:
	 *
	 * 1. The course owner's group ID, using the value of $this->courseOwnerGroupId.
	 * 2. The course owner's group ID, obtained using $this->courseOwnerUserId.
	 * 3. The course owner's group ID, obtained using $this->courseId.
	 * 4. The student's group ID, using the value of $this->$studentGroupId.
	 * 5. The student's group ID, obtained using $this->studentUserId.
	 *
	 * This method exists due to the various ways this class is instantiated
	 * throughout OHM, with varying amounts of information provided.
	 *
	 * In cases where the course owner's group ID is not available, we return
	 * the student's group ID if possible.
	 *
	 * @return int The group ID to use for paywall decisions.
	 * @throws StudentPaymentException Thrown if unable to get the group ID for payments.
	 */
	public function getGroupIdForPayments(): int
	{
		if (isset($this->courseOwnerGroupId) && 0 < $this->courseOwnerGroupId) {
			return $this->courseOwnerGroupId;
		}

		if (isset($this->courseOwnerUserId) && 0 < $this->courseOwnerUserId) {
			return $this->getGroupIdByUserId($this->courseOwnerUserId);
		}

		if (isset($this->courseId) && 0 < $this->courseId) {
			return $this->getCourseOwnerGroupId();
		}

		if (isset($this->studentGroupId) && 0 < $this->studentGroupId) {
			return $this->studentGroupId;
		}

		if (isset($this->studentUserId) && 0 < $this->studentUserId) {
			return $this->getStudentGroupId();
		}

		throw new StudentPaymentException(
			"Unable to get course owner's group ID for payments."
			. ' (StudentPaymentDB is lacking: courseOwnerGroupId, courseId, studentGroupId, and studentUserId information)'
		);
	}

	/**
	 * Determine if this group MAY require student payment for assessments.
	 *
	 * This is done by looking at student_pay_enabled in imas_groups.
	 *
	 * @return bool True if student payment may be required. False if not.
	 * @throws StudentPaymentException Thrown if unable to get student payment value.
	 */
	public function getGroupRequiresStudentPayment()
	{
		$groupIdForPayments = $this->getGroupIdForPayments();

		$stm = $this->dbh->prepare("SELECT name, student_pay_enabled FROM imas_groups WHERE id=:groupid");
		$stm->execute(array(':groupid' => $groupIdForPayments));
		$result = $stm->fetch(\PDO::FETCH_ASSOC);

		$studentPayRequired = $result['student_pay_enabled'];

		if (null == $studentPayRequired) {
			throw new StudentPaymentException(sprintf(
				"Unable to determine if group ID %d (%s) may require student payment for course ID %d.",
				$groupIdForPayments, $result['name'], $this->courseId));
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

		$groupIdForPayments = $this->getGroupIdForPayments();

		$stm = $this->dbh->prepare("UPDATE imas_groups SET student_pay_enabled = :required WHERE id=:groupid");
		$stm->execute(array(':required' => $studentPaymentRequired, ':groupid' => $groupIdForPayments));
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
	 * @throws StudentPaymentException Thrown if there is no course ID available.
	 */
	function getCourseOwnerGroupId()
	{
		if (!empty($this->courseOwnerGroupId)) {
			return $this->courseOwnerGroupId;
		}

		if (empty($this->courseId)) {
			throw new StudentPaymentException("Unable to get course owner's group ID. The course ID is unknown.");
		}

		$pdoStatement = $this->dbh->prepare("SELECT u.groupid FROM imas_courses AS c
											JOIN imas_users AS u ON u.id = c.ownerid
											WHERE c.id = :id");
		$pdoStatement->execute(array(':id' => $this->courseId));
		$results = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
		$groupId = $results['groupid'];

		if (is_null($groupId) || 0 >= $groupId) {
			return 0;
		}

		return $groupId;
	}

    /**
     * Get a course owner's group name.
     *
     * @return string The course owner's group name.
     * @throws StudentPaymentException Thrown if the course ID is not known.
     */
    function getCourseOwnerGroupName()
    {
        $courseOwnerGroupId = $this->getCourseOwnerGroupId();
        if (0 == $courseOwnerGroupId) {
            return 'Default Group (group ID == 0)';
        }

        $pdoStatement = $this->dbh->prepare("SELECT name FROM imas_groups WHERE id = :id");
        $pdoStatement->execute(array(':id' => $courseOwnerGroupId));
        $results = $pdoStatement->fetch(\PDO::FETCH_ASSOC);

        return $results['name'];
    }

	/**
	 * Get a user's group ID by their user ID.
	 *
	 * @param int $userId The user's ID from imas_users.
	 * @return int The user's group ID.
	 */
	function getGroupIdByUserId(int $userId): int
	{
		$pdoStatement = $this->dbh->prepare("SELECT groupid FROM imas_users WHERE id = :id");
		$pdoStatement->execute([':id' => $userId]);
		$results = $pdoStatement->fetch(\PDO::FETCH_ASSOC);

		return $results['groupid'];
	}

	/**
	 * Get the Lumen GUID for a group ID.
	 *
	 * @return string The group's Lumen GUID.
	 */
	function getGroupGuid($groupId)
	{
		$pdoStatement = $this->dbh->prepare("SELECT lumen_guid FROM imas_groups WHERE id = :id");
		$pdoStatement->execute(array(':id' => $groupId));
		$results = $pdoStatement->fetch(\PDO::FETCH_ASSOC);

		return $results['lumen_guid'];
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
		$groupId = $this->getGroupIdForPayments();

		$pdoStatement = $this->dbh->prepare("SELECT g.lumen_guid FROM imas_groups AS g
											WHERE g.id = :id");
		$pdoStatement->execute(array(':id' => $groupId));
		$results = $pdoStatement->fetch(\PDO::FETCH_ASSOC);
		$lumenGuid = $results['lumen_guid'];

		return $lumenGuid;
	}
}
