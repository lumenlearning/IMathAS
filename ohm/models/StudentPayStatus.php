<?php

namespace OHM\Models;

/**
 * Class StudentPayInfo Allows for simple DTOs to hold student payment status and
 * "does this course require student payment" status.
 *
 * This class exists to hide the details of massaging Lumenistration API responses
 * into what we want/need.
 */
class StudentPayStatus
{

	private $courseRequiresStudentPayment; // boolean
	private $studentPaymentTypeRequired; // string
	private $studentHasValidAccessCode; // boolean
	private $studentIsInTrial; // boolean
	private $studentTrialTimeRemainingSeconds; // integer
    private bool $studentIsOptedOut = false; // imas_students.is_opted_out_assessments
	private $studentPaymentRawStatus; // string
	private $courseDirectPayAmountInCents; // integer
	private $schoolLogoUrl; // string
	private $schoolReceiptText; // string
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
	 * @return mixed
	 */
	public function getStudentPaymentTypeRequired()
	{
		return $this->studentPaymentTypeRequired;
	}

	/**
	 * @param mixed $studentPaymentTypeRequired
	 */
	public function setStudentPaymentTypeRequired($studentPaymentTypeRequired)
	{
		$this->studentPaymentTypeRequired = $studentPaymentTypeRequired;
	}

	/**
	 * The student has a valid access code for assessments.
	 *
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
	 * The student is currently in a trial for assessments.
	 *
	 * @return boolean
	 */
	public function getStudentIsInTrial()
	{
		return $this->studentIsInTrial;
	}

	/**
	 * @param boolean $studentIsInTrial
	 */
	public function setStudentIsInTrial($studentIsInTrial)
	{
		$this->studentIsInTrial = $studentIsInTrial;
	}

	/**
	 * @return integer
	 */
	public function getStudentTrialTimeRemainingSeconds()
	{
		return $this->studentTrialTimeRemainingSeconds;
	}

	/**
	 * @param integer $studentTrialTimeRemainingSeconds If the student is currently in a trial.
	 */
	public function setStudentTrialTimeRemainingSeconds($studentTrialTimeRemainingSeconds)
	{
		$this->studentTrialTimeRemainingSeconds = $studentTrialTimeRemainingSeconds;
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
	 * @return mixed
	 */
	public function getCourseDirectPayAmountInCents()
	{
		return $this->courseDirectPayAmountInCents;
	}

	/**
	 * @param mixed $courseDirectPayAmountInCents
	 */
	public function setCourseDirectPayAmountInCents($courseDirectPayAmountInCents)
	{
		$this->courseDirectPayAmountInCents = $courseDirectPayAmountInCents;
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
	 * A message to be displayed to the user, as returned from the student payment API.
	 *
	 * @return string
	 */
	public function getUserMessage()
	{
		return $this->userMessage;
	}

	/**
	 * @param string $userMessage
	 */
	public function setUserMessage($userMessage)
	{
		$this->userMessage = $userMessage;
	}

    /**
     * @return bool True if the student is opted out of assessments. False if not.
     */
    public function getStudentIsOptedOut(): bool
    {
        return $this->studentIsOptedOut;
    }

    /**
     * @param bool $studentIsOptedOut True if the student is opted out of assessments. False if not.
     * @return StudentPayStatus $this
     */
    public function setStudentIsOptedOut(bool $studentIsOptedOut): StudentPayStatus
    {
        $this->studentIsOptedOut = $studentIsOptedOut;
        return $this;
    }
}
