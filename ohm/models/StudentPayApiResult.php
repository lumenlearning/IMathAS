<?php

namespace OHM;

class StudentPayApiResult
{

	private $courseRequiresStudentPayment; // boolean
	private $studentPaymentStatus; // string (not_paid, in_trial, can_extend, etc)
	private $apiUserMessage; // string (message to be displayed to the user)

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

}