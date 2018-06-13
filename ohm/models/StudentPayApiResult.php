<?php

namespace OHM\Models;

/**
 * Class StudentPayApiResult Represents a Lumenistration API response.
 * @package OHM
 */
class StudentPayApiResult
{

	const NO_TRIAL_NO_ACTIVATION = "trial_not_started";
	const IN_TRIAL = "in_trial";
	const CAN_EXTEND = "can_extend";
	const ALL_TRIALS_EXPIRED = "expired";
	const IS_ACTIVATED = "has_access";

	const START_TRIAL_SUCCESS = "trial_started";
	const EXTEND_TRIAL_SUCCESS = "extended";
	const ACTIVATION_SUCCESS = "activation_code_claimed";

	const ACCESS_TYPE_NOT_REQUIRED = "not_required";
	const ACCESS_TYPE_ACTIVATION_CODE = "activation_code";
	const ACCESS_TYPE_DIRECT_PAY = "direct_pay";

	private $courseRequiresStudentPayment; // boolean
	private $studentPaymentStatus; // string (not_paid, in_trial, can_extend, etc)
	private $trialExpiresInSeconds; // integer
	private $accessType; // string (none, direct_pay, activation_code, etc)
	private $apiUserMessage; // string (message to be displayed to the user)
	private $paymentInfo; // array/map (raw data received after successful Stripe payment)
	private $paymentAmountInCents; // integer (direct pay cost for a course)
	private $schoolLogoUrl; // string
	private $schoolReceiptText; // string
	private $errors; // array of strings (errors returned from the API)

	/**
	 * @return boolean
	 */
	public function getCourseRequiresStudentPayment()
	{
		return $this->courseRequiresStudentPayment;
	}

	/**
	 * @param boolean $courseRequiresStudentPayment
	 */
	public function setCourseRequiresStudentPayment($courseRequiresStudentPayment)
	{
		$this->courseRequiresStudentPayment = $courseRequiresStudentPayment;
	}

	/**
	 * @return string
	 */
	public function getStudentPaymentStatus()
	{
		return $this->studentPaymentStatus;
	}

	/**
	 * @param string $studentPaymentStatus
	 */
	public function setStudentPaymentStatus($studentPaymentStatus)
	{
		$this->studentPaymentStatus = $studentPaymentStatus;
	}

	/**
	 * @return integer
	 */
	public function getTrialExpiresInSeconds()
	{
		return $this->trialExpiresInSeconds;
	}

	/**
	 * @param integer $trialExpiresInSeconds
	 */
	public function setTrialExpiresInSeconds($trialExpiresInSeconds)
	{
		$this->trialExpiresInSeconds = $trialExpiresInSeconds;
	}

	/**
	 * @return mixed
	 */
	public function getAccessType()
	{
		return $this->accessType;
	}

	/**
	 * @param mixed $accessType
	 */
	public function setAccessType($accessType)
	{
		$this->accessType = $accessType;
	}

	/**
	 * @return string
	 */
	public function getApiUserMessage()
	{
		return $this->apiUserMessage;
	}

	/**
	 * @param string $apiUserMessage
	 */
	public function setApiUserMessage($apiUserMessage)
	{
		$this->apiUserMessage = $apiUserMessage;
	}

	/**
	 * @return mixed
	 */
	public function getPaymentInfo()
	{
		return $this->paymentInfo;
	}

	/**
	 * @param mixed $paymentInfo
	 */
	public function setPaymentInfo($paymentInfo)
	{
		$this->paymentInfo = $paymentInfo;
	}

	/**
	 * @return mixed
	 */
	public function getPaymentAmountInCents()
	{
		return $this->paymentAmountInCents;
	}

	/**
	 * @param mixed $paymentAmountInCents
	 */
	public function setPaymentAmountInCents($paymentAmountInCents)
	{
		$this->paymentAmountInCents = $paymentAmountInCents;
	}

	/**
	 * @return mixed
	 */
	public function getSchoolLogoUrl()
	{
		return $this->schoolLogoUrl;
	}

	/**
	 * @param mixed $schoolLogoUrl
	 */
	public function setSchoolLogoUrl($schoolLogoUrl)
	{
		$this->schoolLogoUrl = $schoolLogoUrl;
	}

	/**
	 * @return mixed
	 */
	public function getSchoolReceiptText()
	{
		return $this->schoolReceiptText;
	}

	/**
	 * @param mixed $schoolReceiptText
	 */
	public function setSchoolReceiptText($schoolReceiptText)
	{
		$this->schoolReceiptText = $schoolReceiptText;
	}

	/**
	 * @return mixed
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @param mixed $errors
	 */
	public function setErrors($errors)
	{
		$this->errors = $errors;
	}

}
