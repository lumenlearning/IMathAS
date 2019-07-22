<?php

namespace OHM\Assessments;

class PaymentLib
{
	const REFERERS_INDICATING_QUIZ_TAKING = array(
		'showtest.php',				// Assessments version 1
		'assess2/',					// Assessments version 2
		'process_activation.php',
	);

	/**
	 * Get the assessment version for a course.
	 *
	 * As of 2019 July 12, this can be one of: 1, 2
	 *
	 * @param $courseId integer The course version number.
	 * @return mixed integer|null The assessment version.
	 */
	public static function getAssessmentVersion($courseId)
	{
		/* @var $stm \PDOStatement */
		$stm = $GLOBALS['DBH']->prepare('SELECT UIver FROM imas_courses WHERE id = :courseId');
		$stm->execute(array(':courseId' => $courseId));

		return $stm->fetchColumn(0);
	}

	/**
	 * Determine if a user is starting an assessment.
	 *
	 * tl;dr: If this function returns true, and student payments are enabled,
	 * then a payment page should be displayed instead of the assessment.
	 *
	 * Details:
	 *
	 * If the user is not clicking links from within an assessment that point to things
	 * within the same assessment, this should return true.
	 *
	 * We do this by checking URL query arguments and referring URLs.
	 */
	public static function isStartingAssessment()
	{
		if (isset($_REQUEST['begin_ohm_assessment'])) {
			return true;
		}

		if (isset($_REQUEST['activationCodeErrors'])) {
			return true;
		}

		// This handles bookmarks to assessment pages.
		if (!isset($_SERVER['HTTP_REFERER'])) {
			return true;
		}

		// This handles question navigation during quiz-taking. Questions
		// in assessment pages link back to the same URL.
		if (isset($_SERVER['HTTP_REFERER']) && !self::isTakingQuizByReferer()) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if a user is currently taking a quiz, based on referer URL.
	 *
	 * @return bool
	 */
	private static function isTakingQuizByReferer()
	{
		foreach (self::REFERERS_INDICATING_QUIZ_TAKING as $refererString) {
			if (strpos($_SERVER['HTTP_REFERER'], $refererString)) {
				return true;
			}
		}

		return false;
	}
}
