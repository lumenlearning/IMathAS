<?php

namespace OHM\Includes;

use OHM\Models\StudentPayApiResult;
use OHM\Models\StudentPayStatus;
use OHM\Exceptions\StudentPaymentException;
use OHM\Services\OptOutService;

/**
 * Class StudentPayment - Determine if a student has a valid activation code for a course.
 *
 * This class attempts to intelligently get student payment status from the most appropriate location.
 *
 * If cached information is available in MySQL, it will be used.
 * If not, an API call will be made to the student payment API. The results will be cached in MySQL and returned.
 *
 * Note:
 *   If caching is not required or desired, it is okay to use the appropriate
 *   classes directly. The appropriate classes are listed at the end of this phpdoc.
 *
 * @package OHM
 * @see StudentPaymentApi Used for interaction with the student payments API.
 * @see StudentPaymentDb Used for OHM db interaction related to student payments.
 */
class StudentPayment
{

	private $studentPaymentApi;
	private $studentPaymentDb;
    private OptOutService $optOutService;

	private $studentGroupId;
	private $courseId;
	private $studentUserId;

    /**
     * StudentPayment constructor.
     * @param int $studentGroupId The student's user ID.
     * @param int $courseId The course ID.
     * @param int $studentUserId The student's group ID.
     * @param int|null $courseOwnerGroupId The course owner's group ID.
     * @param int|null $courseOwnerUserId The course owner's user ID.
     * @param StudentPaymentApi|null $studentPaymentApi
     * @param StudentPaymentDb|null $studentPaymentDb
     */
    public function __construct($studentGroupId,
                                $courseId,
                                $studentUserId,
                                ?int $courseOwnerGroupId = null,
                                ?int $courseOwnerUserId = null,
                                StudentPaymentApi $studentPaymentApi = null,
                                StudentPaymentDb $studentPaymentDb = null,
                                OptOutService $optOutService = null
    )
	{
        $this->courseId = $courseId;
		$this->studentUserId = $studentUserId;
        $this->studentGroupId = $studentGroupId;

        $this->studentPaymentApi = $studentPaymentApi ??
            new StudentPaymentApi($studentGroupId, $courseId, $studentUserId, $courseOwnerGroupId, $courseOwnerUserId);
        $this->studentPaymentDb = $studentPaymentDb ??
            new StudentPaymentDb($studentGroupId, $courseId, $studentUserId, $courseOwnerGroupId, $courseOwnerUserId);
        $this->optOutService = $optOutService ??
            new OptOutService($GLOBALS['DBH']);
	}

	/**
	 * Determine if payment is required for this course and, if required, has the student provided * a valid
	 * activation code.
	 *
	 * If cached data exists, it will be returned instead of making an API call to the student payment API.
	 *
	 * Current known status list:
	 *
	 *   "not_paid" -- Not in trial and has not paid for access to assessments.
	 *   "in_trial" -- In trial.
	 *   "can_extend" -- End of trial but can extend by 24 hours or one quiz attempt.
	 *   "expired" -- Trial is over, cannot extend, must pay for access.
	 *   "paid" -- Has activation code and can access freely.
	 *
	 * @return StudentPayStatus A StudentPayStatus object.
	 * @see StudentPayStatus Contains student payment API status constants.
	 * @throws StudentPaymentException Thrown if unable to get payment information.
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

		// Get the student's "has an activation code" status and return it.
		$studentPayStatus = $this->getStudentPayStatusCacheFirst($studentPayStatus);

        // Determine if the student has been opted out of assessments.
        // If the student has paid or provided an access code, we opt them back in here.
        $isOptedOut = $this->optOutService->isOptedOutOfAssessments($this->studentUserId, $this->courseId);
        if ($isOptedOut && $studentPayStatus->getStudentHasValidAccessCode()) {
            $this->optOutService->setStudentOptedOut($this->studentUserId, $this->courseId, false);
            $isOptedOut = false;
        }
        $studentPayStatus->setStudentIsOptedOut($isOptedOut);

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
			// If we have no value in the database, NULL == course does not require activation code.
			$studentPayStatus->setCourseRequiresStudentPayment(false);
		}

		return $studentPayStatus;
	}

	/**
	 * Determine if a student has a valid activation code. This will attempt to get data from MySQL
	 * before hitting the student payment API.
	 *
	 * Note: A student's activation code status is only stored in the database if they have a valid
	 * activation code. For "in trial" status, we always need to hit the API to get their remaining
	 * trial time.
	 *
	 * @param $studentPayStatus StudentPayStatus The StudentPayStatus object to update.
	 * @return StudentPayStatus $studentPayStatus The same StudentPayStatus object with updated data.
	 * @throws StudentPaymentException Thrown if unable to get activation status.
	 */
	public function getStudentPayStatusCacheFirst($studentPayStatus)
	{
		$studentHasAccessCode = $this->studentPaymentDb->getStudentHasActivationCode();

		// If the database has what we want, return the data immediately.
		if (null != $studentHasAccessCode && 1 == $studentHasAccessCode) {
			$studentPayStatus->setStudentHasValidAccessCode($studentHasAccessCode);
			return $studentPayStatus;
		}

		// Get the student's activation code status from the student payment API.
		$studentPayApiResult = $this->studentPaymentApi->getActivationStatusFromApi();
		$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, $studentPayStatus);

		// If the student has a valid activation code, let's store that state (in MySQL) to minimize API traffic.
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

		// Student payment type required
		$studentPayStatus->setStudentPaymentTypeRequired($studentPayApiResult->getAccessType());

		// Direct payment required for course (if direct_pay enabled)
		$studentPayStatus->setCourseDirectPayAmountInCents($studentPayApiResult->getPaymentAmountInCents());

		// Response from API appropriate for display to the user
		$studentPayStatus->setUserMessage($studentPayApiResult->getApiUserMessage());

		// School branding information
		$studentPayStatus->setSchoolLogoUrl($studentPayApiResult->getSchoolLogoUrl());

		// Currently, invalid code error messages are returned by the API in a different place.
		if (!empty($studentPayApiResult->getErrors())) {
			$allErrors = implode(' ', $studentPayApiResult->getErrors());
			$studentPayStatus->setUserMessage($allErrors);
		}

		// Student has valid activation code
		$validHasAccessCode = array(StudentPayApiResult::IS_ACTIVATED, StudentPayApiResult::ACTIVATION_SUCCESS);
		if (in_array($studentPayApiResult->getStudentPaymentStatus(), $validHasAccessCode)) {
			$studentPayStatus->setStudentHasValidAccessCode(true);
		} else {
			$studentPayStatus->setStudentHasValidAccessCode(false);
		}

		// Student is in trial
		$validIsInTrial = array(StudentPayApiResult::IN_TRIAL, StudentPayApiResult::START_TRIAL_SUCCESS,
			StudentPayApiResult::EXTEND_TRIAL_SUCCESS);
		if (in_array($studentPayApiResult->getStudentPaymentStatus(), $validIsInTrial)) {
			$studentPayStatus->setStudentIsInTrial(true);
		} else {
			$studentPayStatus->setStudentIsInTrial(false);
		}
		$studentPayStatus->setStudentTrialTimeRemainingSeconds($studentPayApiResult->getTrialExpiresInSeconds());

		// Override API value for "course requires activation code" with DB value. (API always returns false)
		$sadFaceOverride = $this->getCoursePayStatusFromDatabase($studentPayStatus);
		$studentPayStatus->setCourseRequiresStudentPayment($sadFaceOverride->getCourseRequiresStudentPayment());

		return $studentPayStatus;
	}

	/**
	 * Activate a code.
	 *
	 * This will contact the student payment API and attempt to activate the student-provided activation code.
	 *
	 * @param $activationCode string The activation code.
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 * @throws StudentPaymentException Thrown if unable to activate a code.
	 */
	public function activateCode($activationCode)
	{
		$studentPayApiResult = $this->studentPaymentApi->activateCode($activationCode);
		$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());

		if (StudentPayApiResult::ACTIVATION_SUCCESS == $studentPayApiResult->getStudentPaymentStatus()) {
			$this->studentPaymentDb->setStudentHasActivationCode(true);
			$studentPayStatus->setStudentHasValidAccessCode(true);
            $this->optOutService->setStudentOptedOut($this->studentUserId, $this->courseId, false);
		}

		return $studentPayStatus;
	}

	/**
	 * Begin a trial.
	 *
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 * @throws StudentPaymentException Thrown if unable to begin a trial.
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
	 * @throws StudentPaymentException Thrown if unable to extend a trial.
	 */
	public function extendTrial()
	{
		return $this->beginTrial();
	}

	/**
	 * Record the fact that the user is taking an assessment while under trial. This is for metrics.
	 *
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 */
	public function logTakeAssessmentDuringTrial()
	{
		$studentPayStatus = null;
		try {
			$studentPayApiResult = $this->studentPaymentApi->logTakeAssessmentDuringTrial();
			$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());
		} catch (StudentPaymentException $e) {
			// Don't allow metrics logging failures to halt the experience!
			error_log("Exception while logging student payment event: Student taking assessment. "
				. $e->getMessage());
			error_log($e->getTraceAsString());
		}

		return $studentPayStatus;
	}

	/**
	 * Record the fact that the user has seen the activation code page. This is for metrics.
	 *
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 */
	public function logActivationPageSeen()
	{
		$studentPayStatus = null;
		try {
			$studentPayApiResult = $this->studentPaymentApi->logActivationPageSeen();
			$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());
		} catch (StudentPaymentException $e) {
			// Don't allow metrics logging failures to halt the experience!
			error_log("Exception while logging student payment event: Activation page seen. "
				. $e->getMessage());
			error_log($e->getTraceAsString());
		}

		return $studentPayStatus;
	}

	/**
	 * Record the fact that the user has seen the direct payment page. This is for metrics.
	 *
	 * @return StudentPayStatus An instance of StudentPayStatus.
	 */
	public function logDirectPaymentPageSeen()
	{
		$studentPayStatus = null;
		try {
			$studentPayApiResult = $this->studentPaymentApi->logDirectPaymentPageSeen();
			$studentPayStatus = $this->mapApiResultToPayStatus($studentPayApiResult, new StudentPayStatus());
		} catch (StudentPaymentException $e) {
			// Don't allow metrics logging failures to halt the experience!
			error_log("Exception while logging student payment event: Direct payment page seen. "
				. $e->getMessage());
			error_log($e->getTraceAsString());
		}

		return $studentPayStatus;
	}

	/**
	 * Get institution data for the group by its ID. (ID specified in class constructor)
	 *
	 * @return LumenistrationInstitution An instance of LumenistrationInstitution.
	 */
	public function getInstitutionData()
	{
		$lumenistrationInstitution = null;
		try {
			$lumenistrationInstitution = $this->studentPaymentApi->getInstitutionData();
		} catch (StudentPaymentException $e) {
			// Don't allow metrics logging failures to halt the experience!
			error_log(
				sprintf("Exception while getting institution details from Lumenistration API for group ID %d. %s",
					$this->studentGroupId, $e->getMessage()));
			error_log($e->getTraceAsString());
		}

		return $lumenistrationInstitution;
	}

}

