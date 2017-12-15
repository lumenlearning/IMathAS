<?php

namespace OHM;

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

	private $courseRequiresStudentPayment; // boolean
	private $studentPaymentStatus; // string (not_paid, in_trial, can_extend, etc)
	private $trialExpiresInSeconds; // integer
	private $apiUserMessage; // string (message to be displayed to the user)
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
