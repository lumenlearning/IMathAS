<?php

namespace OHM;

require_once(__DIR__ . "/../../includes/sanitize.php");
require_once(__DIR__ . "/../exceptions/StudentPaymentException.php");
require_once(__DIR__ . "/../models/StudentPayApiResult.php");
require_once(__DIR__ . "/StudentPaymentDb.php");
require_once(__DIR__ . "/CurlRequest.php");

/**
 * Class StudentPaymentApi - Get student payment / access code information from the student payment API.
 *
 * This class is typically not used directly. (@see StudentPayment)
 *
 * @package OHM
 */
class StudentPaymentApi
{

	private $curl;
	private $studentPaymentDb;

	private $groupId;
	private $courseId;
	private $studentId;

	/**
	 * StudentPayment constructor.
	 * @param $groupId integer The student's group ID. (MySQL imas_groups. ID column)
	 * @param $courseId integer The course ID. (MySQM imas_courses, ID column)
	 * @param $studentId integer The student's user ID. (MySQL imas_users table, ID column)
	 */
	public function __construct($groupId, $courseId, $studentId)
	{
		$this->groupId = $groupId;
		$this->courseId = $courseId;
		$this->studentId = $studentId;

		$this->studentPaymentDb = new StudentPaymentDb($groupId, $courseId, $studentId);
	}

	/**
	 * Curl object setter. Used during testing.
	 * @param $curlHandle object The curl object to use for HTTP requests.
	 */
	public function setCurl($curlHandle)
	{
		$this->curl = $curlHandle;
	}

	/**
	 * StudentPaymentDb object setter. Used during testing.
	 * @param object $studentPaymentDb The StudentPaymentDb object to use for DB operations.
	 */
	public function setStudentPaymentDb($studentPaymentDb)
	{
		$this->studentPaymentDb = $studentPaymentDb;
	}

	/**
	 * Get the activation status for a student's enrollment in a course.
	 *
	 * @return StudentPayApiResult A StudentPayApiResult object.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function getActivationStatusFromApi()
	{
		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		if (null == $this->curl) {
			$requestUrl = $GLOBALS['student_pay_api']['base_url'] . '/student_pay?' .
				\Sanitize::generateQueryStringFromMap(array(
					'group_id' => $this->groupId,
					'course_id' => $this->courseId,
					'enrollment_id' => $enrollmentId
				));
			$this->curl = new CurlRequest($requestUrl);
		}

		$headers = array('Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret']);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$result = $this->curl->execute();

		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		if (0 == $status) {
			throw new StudentPaymentException("Unable to connect to student payment API.");
		} elseif (200 != $status) {
			throw new StudentPaymentException("Unexpected status returned from student payment API: " . $status);
		}
		$this->curl->close();

		$apiResponse = json_decode($result, true);

		if (null == $apiResponse) {
			throw new StudentPaymentException("Unexpected content returned from student payment API. " . $result);
		}

		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setCourseRequiresStudentPayment($apiResponse['course_requires_student_payment']);
		$studentPayApiResult->setStudentPaymentStatus($apiResponse['student_status']);
		return $studentPayApiResult;
	}

	/**
	 * Activate a code.
	 *
	 * @param $activationCode string The activation code.
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function activateCode($activationCode)
	{
		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		if (null == $this->curl) {
			$this->curl = new CurlRequest($GLOBALS['student_pay_api']['base_url'] . '/student_pay/activation_code');
		}

		$requestData = array(
			'group_id' => $this->groupId,
			'course_id' => $this->courseId,
			'enrollment_id' => $enrollmentId,
			'code' => $activationCode
		);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($requestData));
		$result = $this->curl->execute();

		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		if (0 == $status) {
			throw new StudentPaymentException("Unable to connect to student payment API.");
		} elseif (200 != $status && 204 != $status) {
			throw new StudentPaymentException("Unexpected status returned from student payment API: " . $status);
		}
		$this->curl->close();

		$apiResponse = json_decode($result, true);

		if (null == $apiResponse) {
			throw new StudentPaymentException("Unexpected content returned from student payment API: " . $result);
		}

		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus($apiResponse['status']);
		$studentPayApiResult->setApiUserMessage($apiResponse['message']);

		return $studentPayApiResult;
	}

	/**
	 * Update an existing activation to begin a trial.
	 *
	 * @return StudentPayApiResult An instance of StudentPayApiResult.
	 * @throws StudentPaymentException Thrown on student payment API errors.
	 */
	public function beginTrial()
	{
		$enrollmentId = $this->studentPaymentDb->getStudentEnrollmentId();

		if (null == $this->curl) {
			$this->curl = new CurlRequest($GLOBALS['student_pay_api']['base_url'] . '/student_pay/trial');
		}

		$requestData = array(
			'group_id' => $this->groupId,
			'course_id' => $this->courseId,
			'enrollment_id' => $enrollmentId
		);

		$headers = array(
			'Authorization: Bearer ' . $GLOBALS['student_pay_api']['jwt_secret'],
			'Content-Type: application/json',
		);
		$this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
		$this->curl->setOption(CURLOPT_TIMEOUT, $GLOBALS['student_pay_api']['timeout']);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
		$this->curl->setOption(CURLOPT_POSTFIELDS, json_encode($requestData));
		$result = $this->curl->execute();

		$status = $this->curl->getInfo(CURLINFO_HTTP_CODE);

		if (0 == $status) {
			throw new StudentPaymentException("Unable to connect to student payment API.");
		} elseif (200 != $status && 204 != $status) {
			throw new StudentPaymentException("Unexpected status returned from student payment API: " . $status);
		}
		$this->curl->close();

		$apiResponse = json_decode($result, true);

		if (null == $apiResponse) {
			throw new StudentPaymentException("Unexpected content returned from student payment API: " . $result);
		}

		$studentPayApiResult = new StudentPayApiResult();
		$studentPayApiResult->setStudentPaymentStatus($apiResponse['status']);
		$studentPayApiResult->setApiUserMessage($apiResponse['message']);

		return $studentPayApiResult;
	}

}
