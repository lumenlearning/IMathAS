<?php

use PHPUnit\Framework\TestCase;


/*
 * This tests ohm_getfeedbacktxtmultans() in /assessment/libs/ohm_macros.php.
 */

final class GetFeedbackTxtMultansTest extends TestCase
{
    static function setUpBeforeClass(): void
    {
        // This normally contains a list of allowed macros defined by
        // IMathAS, but we declare it as an empty array here since we're
        // loading ohm_macros.php in isolation for testing.
        $GLOBALS['allowedmacros'] = [];

        // Needed for _(), used by ohm_getfeedbacktxtmultans.
        require_once(__DIR__ . '/../../../../../../i18n/i18n.php');

        // This file contains OHM 2 "macros" as PHP functions instead of a class.
        require_once(__DIR__ . '/../../../../../../assessment/libs/ohm_macros.php');
    }


    /*
     * Empty answers - Single part
     */

    public function test_single_part_null_answer(): void
    {
        $feedback = ohm_getfeedbacktxtmultans(null, ['yes', 'no'], '4');
        $this->assertEquals([], $feedback);
    }

    public function test_single_part_empty_answer(): void
    {
        $feedback = ohm_getfeedbacktxtmultans('', ['yes', 'no'], '4');
        $this->assertEquals([], $feedback);
    }

    public function test_single_part_NA_answer(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => _("No answer selected. Try again.")
            ]
        ];

        $feedback = ohm_getfeedbacktxtmultans('NA', ['yes', 'no'], '4');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Empty answers -- Multi-part
     */

    public function test_multi_part_null_answer(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $feedback = ohm_getfeedbacktxtmultans($studentAnswers, ['yes', 'no'], '4', $partNumber);
            $this->assertEquals([], $feedback);
        }
    }

    public function test_multi_part_empty_answer(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = '';

            $feedback = ohm_getfeedbacktxtmultans($studentAnswers, ['yes', 'no'], '4', $partNumber);
            $this->assertEquals([], $feedback);
        }
    }

    public function test_multi_part_NA_answer(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = 'NA';

            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => _("No answer selected. Try again.")
                ]
            ];

            $feedback = ohm_getfeedbacktxtmultans($studentAnswers, ['yes', 'no'], '4', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    /*
     * All available feedback - Single part
     */

    public function test_single_part_all_feedback(): void
    {
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        $expectedFeedback = [
            'qn0-0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow'
            ],
            'qn0-1' => [
                'correctness' => 'correct',
                'feedback' => 'More meow'
            ],
            'qn0-2' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow again'
            ],
            'qn0-3' => [
                'correctness' => 'correct',
                'feedback' => 'Meow loudly'
            ],
            'qn0-4' => [
                'correctness' => 'correct',
                'feedback' => 'Meow!'
            ],
        ];

        // Answers as an array.
        $feedback = ohm_getfeedbacktxtmultans([1, 3, 4], $feedbacksPossible, '1,3,4');
        $this->assertEquals($expectedFeedback, $feedback);

        // Answers as a comma separated string.
        $feedback = ohm_getfeedbacktxtmultans('1,3,4', $feedbacksPossible, '1,3,4');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * All available feedback - Multi-part
     */

    public function test_multi_part_all_feedback(): void
    {
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = [1, 3, 4];

            $qnIndex = 'qn' . (1000 + $partNumber);
            $expectedFeedback = [
                $qnIndex . '-0' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow'
                ],
                $qnIndex . '-1' => [
                    'correctness' => 'correct',
                    'feedback' => 'More meow'
                ],
                $qnIndex . '-2' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow again'
                ],
                $qnIndex . '-3' => [
                    'correctness' => 'correct',
                    'feedback' => 'Meow loudly'
                ],
                $qnIndex . '-4' => [
                    'correctness' => 'correct',
                    'feedback' => 'Meow!'
                ],
            ];

            $feedback = ohm_getfeedbacktxtmultans($studentAnswers, $feedbacksPossible, '1,3,4', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    /*
     * Answer 0 selected -- Single part
     */

    public function test_single_part_zero_selected(): void
    {
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        $expectedFeedback = [
            'qn0-0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow'
            ],
            'qn0-1' => [
                'correctness' => 'correct',
                'feedback' => 'More meow'
            ],
            'qn0-2' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow again'
            ],
            'qn0-3' => [
                'correctness' => 'correct',
                'feedback' => 'Meow loudly'
            ],
            'qn0-4' => [
                'correctness' => 'correct',
                'feedback' => 'Meow!'
            ],
        ];

        // Answer as an array.
        $feedback = ohm_getfeedbacktxtmultans([0], $feedbacksPossible, '1,3,4');
        $this->assertEquals($expectedFeedback, $feedback);

        // Answer as a comma separated string. (with one element)
        $feedback = ohm_getfeedbacktxtmultans('0', $feedbacksPossible, '1,3,4');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Answer 0 selected -- Multi-part
     */

    public function test_multi_part_zero_selected(): void
    {
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = [0];

            $qnIndex = 'qn' . (1000 + $partNumber);
            $expectedFeedback = [
                $qnIndex . '-0' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow'
                ],
                $qnIndex . '-1' => [
                    'correctness' => 'correct',
                    'feedback' => 'More meow'
                ],
                $qnIndex . '-2' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow again'
                ],
                $qnIndex . '-3' => [
                    'correctness' => 'correct',
                    'feedback' => 'Meow loudly'
                ],
                $qnIndex . '-4' => [
                    'correctness' => 'correct',
                    'feedback' => 'Meow!'
                ],
            ];

            $feedback = ohm_getfeedbacktxtmultans($studentAnswers, $feedbacksPossible, '1,3,4', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }
}
