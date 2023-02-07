<?php

use PHPUnit\Framework\TestCase;


/*
 * This tests ohm_getfeedbacktxtcalculated() in /assessment/libs/ohm_macros.php.
 */

final class GetFeedbackTxtCalculatedTest extends TestCase
{
    static function setUpBeforeClass(): void
    {
        // This normally contains a list of allowed macros defined by
        // IMathAS, but we declare it as an empty array here since we're
        // loading ohm_macros.php in isolation for testing.
        $GLOBALS['allowedmacros'] = [];

        // Needed by ohm_getfeedbacktxtnumfunc for checking answer format.
        require_once(__DIR__ . '/../../../../../../assessment/displayq2.php');

        // This file contains OHM 2 "macros" as PHP functions instead of a class.
        require_once(__DIR__ . '/../../../../../../assessment/libs/ohm_macros.php');
    }

    /*
     * Empty answers - Single part
     */

    public function test_single_part_null_answer(): void
    {
        $feedback = ohm_getfeedbacktxtcalculated(null, '', [42, 1], ['Correct.'], 'Incorrect', '', '', '.001');
        $this->assertEquals([], $feedback);
    }

    /*
     * Empty answers - Multi-part
     */

    public function test_multi_part_null_answer(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $feedback = ohm_getfeedbacktxtcalculated(null, '', [42, 1], ['Correct.'], 'Incorrect', '', '', '.001', $partNumber);
            $this->assertEquals([], $feedback);
        }
    }

    /*
     * Correct answers - Single part
     */

    public function test_single_part_correct(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct.'
            ]
        ];

        // One possible correct answer.
        $feedback = ohm_getfeedbacktxtcalculated('1/2', '0.5', ['1/2', 1], ['Correct.'], 'Incorrect', 'fraction', '', '.001');
        $this->assertEquals($expectedFeedback, $feedback);

        // Two possible correct answers.
        $feedback = ohm_getfeedbacktxtcalculated('0.5', '0.5', ['1/2', 1, '0.5', 1], ['Correct.', 'Correct.'], 'Incorrect', ['fraction', 'decimal'], '', '.001');
        $this->assertEquals($expectedFeedback, $feedback);

        // Correct answer, format not defined.
        $feedback = ohm_getfeedbacktxtcalculated('2/5', '0.5', ['1/2', 1, '0.5', 1, '0.4', 1], ['Correct.', 'Correct.'], 'Incorrect', ['fraction', 'decimal', ''], '', '.001');
        $this->assertEquals($expectedFeedback, $feedback);

        // Correct answer is within tolerance.
        $feedback = ohm_getfeedbacktxtcalculated(41.999, '41.999', [42, 1], ['Correct.'], 'Incorrect', '', '', '|.001');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Correct answers - Multi-part
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

            // One possible correct answer.
            $feedback = ohm_getfeedbacktxtcalculated('1/2', '0.5', ['1/2', 1], ['Correct.'], 'Incorrect', 'fraction', '', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);

            // Two possible correct answers.
            $feedback = ohm_getfeedbacktxtcalculated('0.5', '0.5', ['1/2', 1, '0.5', 1], ['Correct.', 'Correct.'], 'Incorrect', ['fraction', 'decimal'], '', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);

            // Correct answer, format not defined.
            $feedback = ohm_getfeedbacktxtcalculated('2/5', '0.5', ['1/2', 1, '0.5', 1, '0.4', 1], ['Correct.', 'Correct.'], 'Incorrect', ['fraction', 'decimal', ''], '', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);

            // Correct answer is within tolerance.
            $feedback = ohm_getfeedbacktxtcalculated(41.999, '41.999', [42, 1], ['Correct.'], 'Incorrect', '', '', '|.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    /*
     * Partially correct - Single part
     */

    public function test_single_part_partial_credit(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Correct answer, wrong format.'
            ]
        ];

        $feedback = ohm_getfeedbacktxtcalculated('0.5', '0.5', ['1/2', 1, '1/2', 0.5], ['Correct.', 'Correct answer, wrong format.'], 'Incorrect', ['fraction', ''], '', '.001');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Partially correct - Multi-part
     */

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
                    'feedback' => 'Correct answer, wrong format.'
                ]
            ];

            $feedback = ohm_getfeedbacktxtcalculated('0.5', 0.5, ['1/2', 1, '1/2', 0.5], ['Correct.', 'Correct answer, wrong format.'], 'Incorrect.', ['fraction', ''], '', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    /*
     * Incorrect answers - Single part
     */

    public function test_single_part_incorrect(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Incorrect'
            ]
        ];

        $feedback = ohm_getfeedbacktxtcalculated('1/4', '0.25', ['1/2', 1, '1/2', 0.5], ['Correct.', 'Correct answer, wrong format.'], 'Incorrect', ['fraction', ''], '', '.001');
        $this->assertEquals($expectedFeedback, $feedback);

        $feedback = ohm_getfeedbacktxtcalculated('0.2', '0.2', ['1/2', 1, '1/2', 0.5], ['Correct.', 'Correct answer, wrong format.'], 'Incorrect', ['fraction', ''], '', '.001');
        $this->assertEquals($expectedFeedback, $feedback);

        $feedback = ohm_getfeedbacktxtcalculated(40, '40', [42, 1], ['Correct.', 'Correct answer, wrong format.'], 'Incorrect', '', '', '.001');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Incorrect answers - Multi-part
     */

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

            $feedback = ohm_getfeedbacktxtcalculated('1/4', '0.25', ['1/2', 1, '1/2', 0.5], ['Correct.', 'Correct answer, wrong format.'], 'Incorrect', ['fraction', ''], '', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);

            $feedback = ohm_getfeedbacktxtcalculated('0.2', '0.2', ['1/2', 1, '1/2', 0.5], ['Correct.', 'Correct answer, wrong format.'], 'Incorrect', ['fraction', ''], '', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);

            $feedback = ohm_getfeedbacktxtcalculated(40, '40', [42, 1], ['Correct.', 'Correct answer, wrong format.'], 'Incorrect', '', '', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    /*
     * Correct answer is zero - Single part
     */

    public function test_single_part_correct_answer_is_zero(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct.'
            ]
        ];

        $feedback = ohm_getfeedbacktxtcalculated(0, '0', [0, 1], ['Correct.'], 'Incorrect', '', '', '.001');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Correct answer is zero - Multi-part
     */

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

            $feedback = ohm_getfeedbacktxtcalculated(0, '0', [0, 1], ['Correct.'], 'Incorrect', '', '', '.001', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }
}
