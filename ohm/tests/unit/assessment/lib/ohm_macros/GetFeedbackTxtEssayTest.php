<?php

use PHPUnit\Framework\TestCase;


/*
 * This tests ohm_getfeedbacktxtessay() in /assessment/libs/ohm_macros.php.
 */

final class GetFeedbackTxtEssayTest extends TestCase
{
    static function setUpBeforeClass(): void
    {
        // This normally contains a list of allowed macros defined by
        // IMathAS, but we declare it as an empty array here since we're
        // loading ohm_macros.php in isolation for testing.
        $GLOBALS['allowedmacros'] = [];

        // This file contains OHM 2 "macros" as PHP functions instead of a class.
        require_once(__DIR__ . '/../../../../../../assessment/libs/ohm_macros.php');
    }

    /*
     * Empty answers -- Single part
     */

    public function test_single_part_null_answer(): void
    {
        $feedback = ohm_getfeedbacktxtessay(null, 'Correct.');
        $this->assertEquals([], $feedback);
    }

    public function test_single_part_empty_answer(): void
    {
        $feedback = ohm_getfeedbacktxtessay('', 'Correct.');
        $this->assertEquals([], $feedback);
    }

    /*
     * Empty answers -- Multi-part
     */

    public function test_multi_part_null_answer(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $feedback = ohm_getfeedbacktxtessay(null, 'Correct.', $partNumber);
            $this->assertEquals([], $feedback);
        }
    }

    public function test_multi_part_empty_answer(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $feedback = ohm_getfeedbacktxtessay('', 'Correct.', $partNumber);
            $this->assertEquals([], $feedback);
        }
    }

    /*
     * Essay text provided -- Single part
     */

    public function test_single_part_answer_provided(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct.'
            ]
        ];

        $feedback = ohm_getfeedbacktxtessay('Meow.', 'Correct.');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Essay text provided -- Multi-part
     */

    public function test_multi_part_answer_provided(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'correct',
                    'feedback' => 'Correct.'
                ]
            ];

            $feedback = ohm_getfeedbacktxtessay('Meow.', 'Correct.', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }
}
