<?php

require_once __DIR__ . '/../../includes/Ohm2MacroBackport.php';

use PHPUnit\Framework\TestCase;

/**
 * @covers backportFeedback
 */
final class Ohm2MacroBackportTest extends TestCase
{
	/*
	 * Basic feedback function with 3 args
	 */
	public function test_backportFeedbackControl_feedback_basic_3args()
	{
		$input = "ohm_getfeedbacktxt(0, 1, 2);";
		$expected = "getfeedbacktxt(0, 1, 2);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Basic feedback function with 4 args (drops last)
	 */
	public function test_backportFeedbackControl_feedback_basic_4args_truncated()
	{
		$input = "ohm_getfeedbacktxt(0, 1, 2, 3);";
		$expected = "getfeedbacktxt(0, 1, 2);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Essay feedback with 2 args
	 */
	public function test_backportFeedbackControl_feedback_essay_2args()
	{
		$input = "ohm_getfeedbacktxtessay(0, 1);";
		$expected = "getfeedbacktxtessay(0, 1);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Essay feedback with 3 args (drops last)
	 */
	public function test_backportFeedbackControl_feedback_essay_3args_truncated()
	{
		$input = "ohm_getfeedbacktxtessay(0, 1, 2);";
		$expected = "getfeedbacktxtessay(0, 1);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Numeric feedback with 5 args
	 */
	public function test_backportFeedbackControl_feedback_numeric_5args()
	{
		$input = "ohm_getfeedbacktxtnumber(0, 1, 2, 3, 4);";
		$expected = "getfeedbacktxtnumber(0, 1, 2, 3, 4);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Numeric feedback with 6 args (drops last)
	 */
	public function test_backportFeedbackControl_feedback_numeric_6args_truncated()
	{
		$input = "ohm_getfeedbacktxtnumber(0, 1, 2, 3, 4, 5);";
		$expected = "getfeedbacktxtnumber(0, 1, 2, 3, 4);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Calculated feedback with 3 args
	 */
	public function test_backportFeedbackControl_feedback_calculated_3args()
	{
		$input = "ohm_getfeedbacktxtcalculated(0, 1, 2);";
		$expected = "getfeedbacktxtcalculated(0, 1, 2);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Calculated feedback with 8 args
	 */
	public function test_backportFeedbackControl_feedback_calculated_8args()
	{
		$input = "ohm_getfeedbacktxtcalculated(0, 1, 2, 3, 4, 5, 6, 7);";
		$expected = "getfeedbacktxtcalculated(0, 1, 2, 3, 4, 5, 6, 7);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Calculated feedback with 9 args (drops last)
	 */
	public function test_backportFeedbackControl_feedback_calculated_9args_truncated()
	{
		$input = "ohm_getfeedbacktxtcalculated(0, 1, 2, 3, 4, 5, 6, 7, 8);";
		$expected = "getfeedbacktxtcalculated(0, 1, 2, 3, 4, 5, 6, 7);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Numeric function feedback with 3 args
	 */
	public function test_backportFeedbackControl_feedback_numfunc_3args()
	{
		$input = "ohm_getfeedbacktxtnumfunc(0, 1, 2);";
		$expected = "getfeedbacktxtnumfunc(0, 1, 2);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Numeric function feedback with 8 args
	 */
	public function test_backportFeedbackControl_feedback_numfunc_8args()
	{
		$input = "ohm_getfeedbacktxtnumfunc(0, 1, 2, 3, 4, 5, 6, 7);";
		$expected = "getfeedbacktxtnumfunc(0, 1, 2, 3, 4, 5, 6, 7);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Numeric function feedback with 9 args (drops last)
	 */
	public function test_backportFeedbackControl_feedback_numfunc_9args_truncated()
	{
		$input = "ohm_getfeedbacktxtnumfunc(0, 1, 2, 3, 4, 5, 6, 7, 8);";
		$expected = "getfeedbacktxtnumfunc(0, 1, 2, 3, 4, 5, 6, 7);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Basic feedback with 4 args (replaces arg3 with $thisq)
	 */
	public function test_backportFeedbackControl_feedback_basic_4args_thisq()
	{
		$input = "ohm_getfeedbackbasic(0, 1, 2, 3);";
		$expected = "getfeedbackbasic(1, 2, \$thisq);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Basic feedback with 5 args (replaces arg3 with $thisq)
	 */
	public function test_backportFeedbackControl_feedback_basic_5args_thisq()
	{
		$input = "ohm_getfeedbackbasic(0, 1, 2, 3, 4);";
		$expected = "getfeedbackbasic(1, 2, \$thisq, 4);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Basic feedback with multiline input (3 args)
	 */
	public function test_backportFeedbackControl_feedback_basic_multiline_3args()
	{
		$input = "ohm_getfeedbacktxt(0,
                   1,
                   2);";
		$expected = "getfeedbacktxt(0, 1, 2);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);

		$result_no_whitespace = preg_replace('/\s+/', '', $result);
		$expected_no_whitespace = preg_replace('/\s+/', '', $expected);

		$this->assertEquals($expected_no_whitespace, $result_no_whitespace);
	}

	/*
	 * Basic feedback with multiline input (4 args, drops last)
	 */
	public function test_backportFeedbackControl_feedback_basic_multiline_4args()
	{
		$input = "ohm_getfeedbacktxt(0,
                   1,
                   2,
				   3);";
		$expected = "getfeedbacktxt(0, 1, 2);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);

		$result_no_whitespace = preg_replace('/\s+/', '', $result);
		$expected_no_whitespace = preg_replace('/\s+/', '', $expected);

		$this->assertEquals($expected_no_whitespace, $result_no_whitespace);
	}

	/*
	 * Basic feedback with multiline input (4 args, replaces arg3 with $thisq)
	 */
	public function test_backportFeedbackControl_feedback_basic_multiline_4args_thisq()
	{
		$input = "ohm_getfeedbackbasic(0,
                   1,
                   2,
				   3);";
		$expected = "getfeedbackbasic(1,
                   2,
                   \$thisq);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);

		$result_no_whitespace = preg_replace('/\s+/', '', $result);
		$expected_no_whitespace = preg_replace('/\s+/', '', $expected);

		$this->assertEquals($expected_no_whitespace, $result_no_whitespace);
	}

	/*
	 * Basic feedback with multiline input (5 args, replaces arg3 with $thisq)
	 */
	public function test_backportFeedbackControl_feedback_basic_multiline_5args_thisq()
	{
		$input = "ohm_getfeedbackbasic(0,
                   1,
                   2,
				   3,
				   4);";

		$expected = "getfeedbackbasic(1,
					 2,
					 \$thisq,
					 4);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);

		$result_no_whitespace = preg_replace('/\s+/', '', $result);
		$expected_no_whitespace = preg_replace('/\s+/', '', $expected);

		$this->assertEquals($expected_no_whitespace, $result_no_whitespace);
	}

	/*
	 * Numeric function feedback with various argument types
	 */
	public function test_backportFeedbackControl_feedback_numfunc_mixed_argtypes()
	{
		$input = "ohm_getfeedbacktxtnumfunc(0, \"1\", \$2, \$three, \$four[4], '5', six, 7);";
		$expected = "getfeedbacktxtnumfunc(0, \"1\", $2, \$three, \$four[4], '5', six, 7);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Basic feedback with various argument types
	 */
	public function test_backportFeedbackControl_feedback_basic_mixed_argtypes()
	{
		$input = "ohm_getfeedbackbasic(0, \"1\", \$2, \$three, \$four[4]);";
		$expected = "getfeedbackbasic(\"1\", \$2, \$thisq, \$four[4]);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Basic feedback without spaces between arguments
	 */
	public function test_backportFeedbackControl_feedback_basic_no_spaces()
	{
		$input = "ohm_getfeedbackbasic(0,1,2,3,4);";
		$expected = "getfeedbackbasic(1,2,\$thisq,4);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Multiple feedback calls on single line
	 */
	public function test_backportFeedbackControl_multiple_feedback_calls_inline()
	{
		$input = "ohm_getfeedbacktxt(0, 1, 2) . ohm_getfeedbacktxtnumfunc(3, 4, 5);";
		$expected = "getfeedbacktxt(0, 1, 2) . getfeedbacktxtnumfunc(3, 4, 5);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Multiple basic feedback calls on single line
	 */
	public function test_backportFeedbackControl_multiple_basic_feedback_calls_inline()
	{
		$input = "ohm_getfeedbackbasic(0, 1, 2, 3) . ohm_getfeedbackbasic(4, 5, 6, 7);";
		$expected = "getfeedbackbasic(1, 2, \$thisq) . getfeedbackbasic(5, 6, \$thisq);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Feedback with commas in double-quoted string
	 */
	public function test_backportFeedbackControl_feedback_with_doublequoted_commas()
	{
		$input = "ohm_getfeedbacktxt(0, \"1, one\", 2);";
		$expected = "getfeedbacktxt(0, \"1, one\", 2);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Basic feedback with commas in double-quoted string
	 */
	public function test_backportFeedbackControl_basic_feedback_with_doublequoted_commas()
	{
		$input = "ohm_getfeedbackbasic(0, \"1, one\", 2, 3);";
		$expected = "getfeedbackbasic(\"1, one\", 2, \$thisq);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Feedback with commas in single-quoted string
	 */
	public function test_backportFeedbackControl_feedback_with_singlequoted_commas()
	{
		$input = "ohm_getfeedbacktxt(0, '1, one', 2);";
		$expected = "getfeedbacktxt(0, '1, one', 2);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Basic feedback with commas in single-quoted string
	 */
	public function test_backportFeedbackControl_basic_feedback_with_singlequoted_commas()
	{
		$input = "ohm_getfeedbackbasic(0, '1, one', 2, 3);";
		$expected = "getfeedbackbasic('1, one', 2, \$thisq);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Feedback with commas in mixed quoted strings
	 */
	public function test_backportFeedbackControl_feedback_with_mixed_quoted_commas()
	{
		$input = "ohm_getfeedbacktxt(0, '1, one', \"2, two\");";
		$expected = "getfeedbacktxt(0, '1, one', \"2, two\");";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Basic feedback with commas in mixed quoted strings
	 */
	public function test_backportFeedbackControl_basic_feedback_with_mixed_quoted_commas()
	{
		$input = "ohm_getfeedbackbasic(0, '1, one', \"2, two\", 3);";
		$expected = "getfeedbackbasic('1, one', \"2, two\", \$thisq);";
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Feedback with complex multiline quoted strings
	 */
	public function test_backportFeedbackControl_feedback_complex_quoted_strings()
	{
		$input = 'ohm_getfeedbacktxt("First line\nSecond line", \'Single quoted\nstring\', "String with \"nested\" quotes");';
		$expected = 'getfeedbacktxt("First line\nSecond line", \'Single quoted\nstring\', "String with \"nested\" quotes");';
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Feedback with apostrophes in strings
	 */
	public function test_backportFeedbackControl_feedback_with_apostrophes()
	{
		$input = 'ohm_getfeedbacktxt("First line\nSecond line", \'Single quoted\nstring\', "This string\'s got an apostrophe");';
		$expected = 'getfeedbacktxt("First line\nSecond line", \'Single quoted\nstring\', "This string\'s got an apostrophe");';
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Multiple macros with complex multiline arguments
	 */
	public function test_backportFeedbackControl_multiple_macros_complex_args()
	{
		$input = <<<'PHP'
			ohm_getfeedbacktxt("Complex,
				argument's", 'Another,
				argument', "Last one") .
			ohm_getfeedbackbasic("First,
				argument", "Second's", "Third",
				"Fourth");
			PHP;
		$expected = 'getfeedbacktxt("Complex, argument\'s", \'Another, argument\', "Last one") . getfeedbackbasic("Second\'s", "Third", $thisq);';
		
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		
		$result_no_whitespace = preg_replace('/\s+/', '', $result);
		$expected_no_whitespace = preg_replace('/\s+/', '', $expected);
		$this->assertEquals($expected_no_whitespace, $result_no_whitespace);
	}

	/*
	 * Multiple macros with complex multiline arguments and other surrounding code
	 */
	public function test_backportFeedbackControl_multiple_macros_surrounding_code()
	{
		$input = <<<'PHP'
			$var = 'some other code';
			ohm_getfeedbacktxt("Complex,
				argument's", 'Another,
				argument', "Last one") .
			ohm_getfeedbackbasic("First,
				argument", "Second's", "Third",
				"Fourth");
			$var2 = "some more code";
			PHP;
		$expected = '$var = \'some other code\'; getfeedbacktxt("Complex, argument\'s", \'Another, argument\', "Last one") . getfeedbackbasic("Second\'s", "Third", $thisq); $var2 = "some more code";';
		
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		
		$result_no_whitespace = preg_replace('/\s+/', '', $result);
		$expected_no_whitespace = preg_replace('/\s+/', '', $expected);
		$this->assertEquals($expected_no_whitespace, $result_no_whitespace);
	}

	/*
	 * Non-feedback function call remains unchanged
	 */
	public function test_backportFeedbackControl_non_feedback_function()
	{
		$input = "ohm_thisisnotfeedback(0, 1, 2, 3, 4);";
		$expected = $input;
		$result = Ohm2MacroBackport::backportFeedbackControl($input);
		$this->assertEquals($expected, $result);
	}

	/*
	 * Answerbox variable handling
	 */
	public function test_backportFeedbackQuestionText_answerbox_feedback()
	{
		$input = <<<PHP
					\$answerbox
					\$answerbox[0]
					\$answerbox[1]
					PHP;
		$expected = <<<PHP
					\$answerbox
					<p>\$feedback</p>
					\$answerbox[0]
					<p>\$feedback[0]</p>
					\$answerbox[1]
					<p>\$feedback[1]</p>
					PHP;			
		$result = Ohm2MacroBackport::backportFeedbackQuestionText($input);

		$result_no_whitespace = preg_replace('/\s+/', '', $result);
		$expected_no_whitespace = preg_replace('/\s+/', '', $expected);

		$this->assertEquals($expected_no_whitespace, $result_no_whitespace);
	}

	/*
	 * Multiple macros with complex multiline arguments and other surrounding code
	 */
	public function test_containsOhm2Macro_multiple_macros_surrounding_code()
	{
		$input = <<<'PHP'
			$var = 'some other code';
			ohm_getfeedbacktxt("Complex,
				argument's", 'Another,
				argument', "Last one") .
			ohm_getfeedbackbasic("First,
				argument", "Second's", "Third",
				"Fourth");
			$var2 = "some more code";
			PHP;
		
		$result = Ohm2MacroBackport::containsOhm2Macro($input);
		$expected = true;

		$this->assertEquals($expected, $result);
	}

	/*
	 * Multiple macros with complex multiline arguments and other surrounding code
	 */
	public function test_containsOhm2Macro_multiple_macros_surrounding_code_negative()
	{
		$input = <<<'PHP'
			$var = 'some other code';
			getfeedbacktxt("Complex,
				argument's", 'Another,
				argument', "Last one") .
			getfeedbackbasic("First,
				argument", "Second's", "Third",
				"Fourth");
			$var2 = "some more code";
			PHP;
		
		$result = Ohm2MacroBackport::containsOhm2Macro($input);
		$expected = false;

		$this->assertEquals($expected, $result);
	}
}
