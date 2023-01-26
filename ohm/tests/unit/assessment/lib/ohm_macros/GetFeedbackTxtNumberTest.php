<?php

use PHPUnit\Framework\TestCase;


/*
 * This tests ohm_getfeedbacktxtnumber() in /assessment/libs/ohm_macros.php.
 */

final class GetFeedbackTxtNumberTest extends TestCase
{
    static function setUpBeforeClass(): void
    {
        // This normally contains a list of allowed macros defined by
        // IMathAS, but we declare it as an empty array here since we're
        // loading ohm_macros.php in isolation for testing.
        $GLOBALS['allowedmacros'] = [];

        // Needed for _()
        require_once(__DIR__ . '/../../../../../../i18n/i18n.php');

        // This file contains OHM 2 "macros" as PHP functions instead of a class.
        require_once(__DIR__ . '/../../../../../../assessment/libs/ohm_macros.php');
    }

    /*
     * Empty answers - Single part
     */

    public function test_single_part_null_answer(): void
    {
        $feedback = ohm_getfeedbacktxtnumber(null, [42, 1], ['Correct.'], 'Incorrect', '.001');
        $this->assertEquals([], $feedback);
    }

    /*
     * Empty answers - Multi-part
     */

    public function test_multi_part_null_answer(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $feedback = ohm_getfeedbacktxtnumber(null, [42, 1], ['Correct.'], 'Incorrect', '.001', $partNumber);
            $this->assertEquals([], $feedback);
        }
    }

    /*
     * Single part
     */

    public function test_single_part_correct(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct.'
            ]
        ];

        $feedback = ohm_getfeedbacktxtnumber(42, [42, 1], ['Correct.'], 'Incorrect', '.001');
        $this->assertEquals($expectedFeedback, $feedback);

        $feedback = ohm_getfeedbacktxtnumber(41.999, [42, 1], ['Correct.'], 'Incorrect', '.001');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_partial_credit(): void
    {
        /*
         * The student's provided answer falls within defined tolerance.
         */

        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Correct.'
            ]
        ];

        $feedback = ohm_getfeedbacktxtnumber(41.999, [42, 0.5], ['Correct.'], 'Incorrect', '.001');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_incorrect(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Incorrect'
            ]
        ];

        $feedback = ohm_getfeedbacktxtnumber(40, [42, 1], ['Correct.'], 'Incorrect', '.001');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_not_a_number(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'This answer does not appear to be a valid number.'
            ]
        ];

        $feedback = ohm_getfeedbacktxtnumber('meow', [42, 1], ['Correct.'], 'Incorrect', '.001');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_correct_answer_is_zero(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct.'
            ]
        ];

        $feedback = ohm_getfeedbacktxtnumber(0, [0, 1], ['Correct.'], 'Incorrect', '.001');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Multi-part
     */

    public function test_multi_part_correct(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'correct',
                    'feedback' => 'Correct.'
                ]
            ];

            $feedback = ohm_getfeedbacktxtnumber(42, [42, 1], ['Correct.'], 'Incorrect', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);

            $feedback = ohm_getfeedbacktxtnumber(41.999, [42, 1], ['Correct.'], 'Incorrect', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_partial_credit(): void
    {
        /*
         * The student's provided answer falls within defined tolerance.
         */

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Correct.'
                ]
            ];

            $feedback = ohm_getfeedbacktxtnumber(41.999, [42, 0.5], ['Correct.'], 'Incorrect', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_incorrect(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Incorrect'
                ]
            ];

            $feedback = ohm_getfeedbacktxtnumber(40, [42, 1], ['Correct.'], 'Incorrect', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_not_a_number(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => 'This answer does not appear to be a valid number.'
                ]
            ];

            $feedback = ohm_getfeedbacktxtnumber('meow', [42, 1], ['Correct.'], 'Incorrect', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_correct_answer_is_zero(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'correct',
                    'feedback' => 'Correct.'
                ]
            ];

            $feedback = ohm_getfeedbacktxtnumber(0, [0, 1], ['Correct.'], 'Incorrect', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }
}
