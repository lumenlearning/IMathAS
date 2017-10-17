<?php

namespace OHM;

/**
 * Class StudentPayInfo - A simple DTO to hold student payment status and
 * "does this course require student payment" status.
 */
class StudentPayStatus
{

	const NOT_PAID = "not_paid";
	const PAID = "paid";
	const IN_TRIAL = "in_trial";
	const CAN_EXTEND = "can_extend";
	const ALL_TRIALS_EXPIRED = "expired";

	private $courseRequiresStudentPayment; // boolean
	private $studentHasValidAccessCode; // boolean
	private $studentPaymentRawStatus; // string
	private $userMessage; // string

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
	 * @return boolean
	 */
	public function getStudentHasValidAccessCode()
	{
		return $this->studentHasValidAccessCode;
	}

	/**
	 * @param boolean $studentHasValidAccessCode
	 */
	public function setStudentHasValidAccessCode($studentHasValidAccessCode)
	{
		$this->studentHasValidAccessCode = $studentHasValidAccessCode;
	}

	/**
	 * The student's payment status as returned by the student payment API.
	 *
	 * @return string
	 */
	public function getStudentPaymentRawStatus()
	{
		return $this->studentPaymentRawStatus;
	}

	/**
	 * @param string $studentPaymentRawStatus
	 */
	public function setStudentPaymentRawStatus($studentPaymentRawStatus)
	{
		$this->studentPaymentRawStatus = $studentPaymentRawStatus;
	}

	/**
	 * A message to be displayed to the user, as returned from the student payment API.
	 *
	 * @return mixed
	 */
	public function getUserMessage()
	{
		return $this->userMessage;
	}

	/**
	 * @param mixed $userMessage
	 */
	public function setUserMessage($userMessage)
	{
		$this->userMessage = $userMessage;
	}

}