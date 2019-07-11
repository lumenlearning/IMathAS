<?php

namespace OHM\Assessments;

class PaymentLib
{
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

		if (!isset($_SERVER['HTTP_REFERER'])) {
			return true;
		}

		if (isset($_SERVER['HTTP_REFERER']) && !strpos($_SERVER['HTTP_REFERER'], 'showtest.php')) {
			return true;
		}

		return false;
	}
}
