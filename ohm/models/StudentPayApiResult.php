<?php

namespace OHM;

class StudentPayApiResult
{

	const NOT_PAID = "trial_not_started";
	const IN_TRIAL = "in_trial";
	const CAN_EXTEND = "can_extend";
	const ALL_TRIALS_EXPIRED = "expired";
	const TRIAL_STARTED = "trial_started";
	const PAID = "paid";

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