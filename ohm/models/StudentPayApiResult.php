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
	private $apiUserMessage; // string (message to be displayed to the user)
	private $errors; // array of strings (errors returned from the API)

	/**
	 * @return mixed
	 */
	public function getCourseRequiresStudentPayment()
	{
		return $this->courseRequiresStudentPayment;
	}

	/**
	 * @param mixed $courseRequiresStudentPayment
	 */
	public function setCourseRequiresStudentPayment($courseRequiresStudentPayment)
	{
		$this->courseRequiresStudentPayment = $courseRequiresStudentPayment;
	}

	/**
	 * @return mixed
	 */
	public function getStudentPaymentStatus()
	{
		return $this->studentPaymentStatus;
	}

	/**
	 * @param mixed $studentPaymentStatus
	 */
	public function setStudentPaymentStatus($studentPaymentStatus)
	{
		$this->studentPaymentStatus = $studentPaymentStatus;
	}

	/**
	 * @return mixed
	 */
	public function getApiUserMessage()
	{
		return $this->apiUserMessage;
	}

	/**
	 * @param mixed $apiUserMessage
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