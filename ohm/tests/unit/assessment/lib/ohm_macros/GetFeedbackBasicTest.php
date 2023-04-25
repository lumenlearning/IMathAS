<?php

use PHPUnit\Framework\TestCase;


/*
 * This tests ohm_getfeedbackbasic() in /assessment/libs/ohm_macros.php.
 */

final class GetFeedbackBasicTest extends TestCase
{
    static function setUpBeforeClass(): void
    {
        // This normally contains a list of allowed macros defined by
        // IMathAS, but we declare it as an empty array here since we're
        // loading ohm_macros.php in isolation for testing.
        $GLOBALS['allowedmacros'] = [];

        // Needed by ohm_getfeedbackbasic for checking answers with symbols for correctness.
        require_once(__DIR__ . '/../../../../../../assessment/displayq2.php');

        // This file contains OHM 2 "macros" as PHP functions instead of a class.
        require_once(__DIR__ . '/../../../../../../assessment/libs/ohm_macros.php');
    }

    /*
     * Empty answers - Single part
     */

    public function test_single_part_null_answer(): void
    {
        $feedback = ohm_getfeedbackbasic(null, "Correct!", "Nope!", 'meow');
        $this->assertEquals([], $feedback);
    }

    public function test_single_part_empty_answer(): void
    {
        $feedback = ohm_getfeedbackbasic('', "Correct!", "Nope!", 'meow');
        $this->assertEquals([], $feedback);
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
            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", 'meow', $partNumber);
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

            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", 'meow', $partNumber);
            $this->assertEquals([], $feedback);
        }
    }

    /*
     * String question -- Single part
     */

    public function test_single_part_string_correct(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct!'
            ]
        ];

        $feedback = ohm_getfeedbackbasic('meow', "Correct!", "Nope!", 'meow');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_string_incorrect(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Nope!'
            ]
        ];

        $feedback = ohm_getfeedbackbasic('woof', "Correct!", "Nope!", 'meow');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * String question -- Multi-part
     */

    public function test_multi_part_string_correct(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = 'meow';

            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'correct',
                    'feedback' => 'Correct!'
                ]
            ];

            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", 'meow', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_string_incorrect(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = 'screech';

            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Nope!'
                ]
            ];

            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", 'meow', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    /*
     * Multiple choice questions -- Single part
     */

    public function test_single_part_choices_correct(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct!'
            ]
        ];

        $feedback = ohm_getfeedbackbasic(1, "Correct!", "Nope!", 1);
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_choices_incorrect(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Nope!'
            ]
        ];

        $feedback = ohm_getfeedbackbasic(2, "Correct!", "Nope!", 3);
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_choices_zero_selected(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Nope!'
            ]
        ];

        $feedback = ohm_getfeedbackbasic(0, "Correct!", "Nope!", 1);
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Multiple choice questions -- Multi-part
     */

    public function test_multi_part_choices_correct(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = 1;

            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'correct',
                    'feedback' => 'Correct!'
                ]
            ];

            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", 1, $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_choices_incorrect(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = 2;

            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Nope!'
                ]
            ];

            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", 3, $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_choices_zero_selected(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = 0;

            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Nope!'
                ]
            ];

            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", 1, $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    /*
     * Multiple answer question -- Single part
     */

    public function test_single_part_multans_correct(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct!'
            ]
        ];

        $feedback = ohm_getfeedbackbasic([1, 4], "Correct!", "Nope!", "1,4");
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_multans_incorrect(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Nope!'
            ]
        ];

        $feedback = ohm_getfeedbackbasic([2, 3], "Correct!", "Nope!", "1,4");
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_multans_zero_selected(): void
    {
        $expectedFeedback = [
            'qn0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Nope!'
            ]
        ];

        $feedback = ohm_getfeedbackbasic([0], "Correct!", "Nope!", "1,4");
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Multiple answer question -- Multi-part
     */

    public function test_multi_part_multans_correct(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = [1, 4];

            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'correct',
                    'feedback' => 'Correct!'
                ]
            ];

            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", "1,4", $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_multans_incorrect(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = [2, 3];

            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Nope!'
                ]
            ];

            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", "1,4", $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_multans_zero_selected(): void
    {
        // Answers for all parts of the multi-part question.
        $studentAnswers = [null, null, null, null, null, null, null, null];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $studentAnswers[$partNumber] = [0];

            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Nope!'
                ]
            ];

            $feedback = ohm_getfeedbackbasic($studentAnswers, "Correct!", "Nope!", "1,4", $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }
}
