<?php

namespace OHM;

require_once(__DIR__ . "/StudentPaymentApi.php");
require_once(__DIR__ . "/StudentPaymentDb.php");
require_once(__DIR__ . "/../models/StudentPayStatus.php");
require_once(__DIR__ . "/../models/StudentPayApiResult.php");

/**
 * Class StudentPayment - Determine if a student has a valid access code for a course.
 *
 * If cached information is available in MySQL, it will be used.
 * If not, an API call will be made to the student payment API. The results will be cached in MySQL and returned.
 *
 * @package OHM
 * @see StudentPaymentApi Used for interaction with the student payments API.
 * @see StudentPaymentDb Used for OHM db interaction related to student payments.
 */
class StudentPayment
{

	private $studentPaymentApi;
	private $studentPaymentDb;

	private $groupId;
	private $courseId;
	private $studentId;

	public function __construct($groupId, $courseId, $studentId)
	{
		$this->groupId = $groupId;
		$this->courseId = $courseId;
		$this->studentId = $studentId;

		$this->studentPaymentApi = new StudentPaymentApi($groupId, $courseId, $studentId);
		$this->studentPaymentDb = new StudentPaymentDb($groupId, $courseId, $studentId);
	}

	/**
	 * StudentPaymentApi object setter. Used during testing.
	 * @param object $studentPaymentApi The StudentPaymentApi object to use for API operations.
	 */
	public function setStudentPaymentApi($studentPaymentApi)
	{
		$this->studentPaymentApi = $studentPaymentApi;
	}

	/**
	 * StudentPaymentDb object setter. Used during testing.
	 * @param StudentPaymentDb $studentPaymentDb The StudentPaymentDb object to use for DB operations.
	 */
	public function setStudentPaymentDb($studentPaymentDb)
	{
		$this->studentPaymentDb = $studentPaymentDb;
	}

	/**
	 * Determine if payment is required for this course and, if required, has the student provided * a valid
	 * activation code.
	 *
	 * If cached data exists, it will be returned instead of making an API call to the student payment API.
	 *
	 * Current known status list:
	 *
	 *   "not_paid" -- Not in trial and has not paid for access.
	 *   "in_trial" -- In trial.
	 *   "can_extend" -- End of trial but can extend by 24 hours or 1 quiz attempt.
	 *   "expired" -- Trial is over, cannot extend, must pay for access.
	 *   "paid" -- Has activation code and can access freely.
	 *
	 * @return StudentPayStatus A StudentPayStatus object.
	 * @see StudentPayStatus Contains student payment API status constants.
	 */
	public function getCourseAndStudentPaymentInfo()
	{
		$studentPayStatus = new StudentPayStatus();

		// Are student payments enabled at the group level?
		$groupMayRequirePayment = $this->studentPaymentDb->getGroupRequiresStudentPayment();
		if (!$groupMayRequirePayment) {
			$studentPayStatus->setCourseRequiresStudentPayment(false);
			return $studentPayStatus;
		}

		// If the group uses student payments, determine if the course requires student payment.
		$studentPayStatus = $this->getCoursePayStatus($studentPayStatus);
		if (!$studentPayStatus->getCourseRequiresStudentPayment()) {
			return $studentPayStatus;
		}

		// Get the student's "has an access code" status and return it.
		$studentPayStatus = $this->getStudentPayStatusCacheFirst($studentPayStatus);
		return $studentPayStatus;
	}

	/**
	 * Determine if a course requires student payment.
	 *
	 * IMPORTANT NOTES:
	 * - As of 2017 Nov 28, OHM is the authoritative source for this information.
	 * - In the future, we may obtain this from the student payment API.
	 *   - See THIS commit diff for previously WORKING code to make that happen.
	 *
	 * @param $studentPayStatus StudentPayStatus The StudentPayStatus object to update.
	 * @return StudentPayStatus $studentPayStatus The same StudentPayStatus object with updated data.
	 */
	public function getCoursePayStatus($studentPayStatus)
	{
		// Note: In the future, the student payment API may become the authoritative source for this data.
		// When this happens, search this entire file for the variable named $sadFace.
		return $this->getCoursePayStatusFromDatabase($studentPayStatus);
	}

	/**
	 * Determine if a course requires student payment. (get directly from database)
	 *
	 * @param $studentPayStatus StudentPayStatus The StudentPayStatus object to update.
	 * @return StudentPayStatus $studentPayStatus The same StudentPayStatus object with updated data.
	 */
	public function getCoursePayStatusFromDatabase($studentPayStatus)
	{
		$courseRequiresStudentPay = $this->studentPaymentDb->getCourseRequiresStudentPayment();

		if (null != $courseRequiresStudentPay) {
			$studentPayStatus->setCourseRequiresStudentPayment($courseRequiresStudentPay);
		} else {
			// IMPORTANT NOTE -- As of 2017 Nov 28:
			// If we have no value in the database, NULL == course does not require access code.
			$studentPayStatus->setCourseRequiresStudentPayment(false);
		}

		return $studentPayStatus;
	}

	/**
	 * Determine if a student has a valid access code. This will attempt to get data from MySQL
	 * before hitting the student payment API.
	 *
	 * Note: A student's access code status is only stored in the database if they have a valid
	 * access code. For "in trial" status, we always need to hit the API to get their remaining
	 * trial time.
	 *
	 * @param $studentPayStatus StudentPayStatus The StudentPayStatus object to update.
	 * @return StudentPayStatus $studentPayStatus The same StudentPayStatus object with updated data.
	 */
	public function getStudentPayStatusCacheFirst($studentPayStatus)
	{
		$studentHasAccessCode = $this->studentPaymentDb->getStudentHasActivationCode();

		// If the database has what we want, return the data immediately.
		if (null != $studentHasAccessCode) {
			$studentPayStatus->setStudentHasValidAccessCode($studentHasAccessCode);
			return $studentPayStatus;
		}

		// Get the student's access code status from the student payment API.
		$studentPayApiResult = $this->studentPaymentApi->getActivationStatusFromApi();
		$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, $studentPayStatus);

		// If the student has a valid access code, let's store that state (in MySQL) to minimize API traffic.
		if ($studentPayStatus->getStudentHasValidAccessCode()) {
			$this->studentPaymentDb->setStudentHasActivationCode($studentPayStatus->getStudentHasValidAccessCode());
		}

		return $studentPayStatus;
	}

	/**
	 * Map the values from a student payment API response object to a StudentPayStatus object.
	 *
	 * @param $studentPayApiResult StudentPayApiResult An instance of StudentPayApiResult.
	 * @param $studentPayStatus StudentPayStatus An instance of StudentPayStatus.
	 * @return StudentPayStatus The updated StudentPayStatus object.
	 */
	public function mapApiResultToPayStatus($studentPayApiResult, $studentPayStatus)
	{
		// Student payment raw status
		$studentPayStatus->setStudentPaymentRawStatus($studentPayApiResult->getStudentPaymentStatus());

		// Course requires payment
		$studentPayStatus->setCourseRequiresStudentPayment($studentPayApiResult->getCourseRequiresStudentPayment());

		// Response from API appropriate for display to the user
		$studentPayStatus->setUserMessage($studentPayApiResult->getApiUserMessage());

		// Student has valid access code
		if (StudentPayApiResult::PAID == $studentPayApiResult->getStudentPaymentStatus()) {
			$studentPayStatus->setStudentHasValidAccessCode(true);
		} else {
			$studentPayStatus->setStudentHasValidAccessCode(false);
		}

		// Student is in trial
		if (StudentPayApiResult::IN_TRIAL == $studentPayApiResult->getStudentPaymentStatus()
			|| StudentPayApiResult::TRIAL_STARTED == $studentPayApiResult->getStudentPaymentStatus()) {
			$studentPayStatus->setStudentIsInTrial(true);
			$studentPayStatus->setStudentTrialTimeRemainingSeconds($studentPayApiResult->getTrialExpiresInSeconds());
		} else {
			$studentPayStatus->setStudentIsInTrial(false);
		}

		// Override API value for "course requires access code" with DB value. (API always returns false)
		$sadFaceOverride = $this->getCoursePayStatusFromDatabase($studentPayStatus);
		$studentPayStatus->setCourseRequiresStudentPayment($sadFaceOverride->getCourseRequiresStudentPayment());

		return $studentPayStatus;
	}

	/**
	 * Activate an access code.
	 *
	 * This will contact the student payment API and attempt to activate the student-provided access code.
	 *
	 * @param $accessCode string The access code.
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 */
	public function activateCode($accessCode)
	{
		$studentPayApiResult = $this->studentPaymentApi->activateCode($accessCode);
		$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());

		if ("ok" == $studentPayApiResult->getStudentPaymentStatus()) {
			$studentPayStatus->setStudentHasValidAccessCode(true);
		}

		return $studentPayStatus;
	}

	/**
	 * Begin a trial.
	 *
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 */
	public function beginTrial()
	{
		$studentPayApiResult = $this->studentPaymentApi->beginTrial();
		$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());

		return $studentPayStatus;
	}

	/**
	 * Extend a trial.
	 *
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 */
	public function extendTrial()
	{
		return $this->beginTrial();
	}

	/**
	 * Record the fact that the user has started an assessment while under trial. This is for metrics.
	 *
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 */
	public function logBeginAssessmentDuringTrial()
	{
		$studentPayApiResult = $this->studentPaymentApi->logBeginAssessmentDuringTrial();
		$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());

		return $studentPayStatus;
	}


	/**
	 * Record the fact that the user has declined an assessments trial. This is for metrics.
	 *
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 */
	public function logDeclineTrial()
	{
		$studentPayApiResult = $this->studentPaymentApi->logDeclineTrial();
		$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());

		return $studentPayStatus;
	}
}

