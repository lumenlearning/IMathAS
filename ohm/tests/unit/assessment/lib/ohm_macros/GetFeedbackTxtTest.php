<?php

use PHPUnit\Framework\TestCase;


/*
 * This tests ohm_getfeedbacktxt() in /assessment/libs/ohm_macros.php.
 */

final class GetFeedbackTxtTest extends TestCase
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
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        $expectedFeedback = [
            'qn0-0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow'
            ],
            'qn0-1' => [
                'correctness' => 'incorrect',
                'feedback' => 'More meow'
            ],
            'qn0-2' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow again'
            ],
            'qn0-3' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow loudly'
            ],
            'qn0-4' => [
                'correctness' => 'correct',
                'feedback' => 'Meow!'
            ],
        ];

        $feedback = ohm_getfeedbacktxt(null, $feedbacksPossible, 4);
        $this->assertEquals($expectedFeedback, $feedback);
    }

    public function test_single_part_empty_answer(): void
    {
        $feedback = ohm_getfeedbacktxt('', ['yes', 'no'], 4);
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

        $feedback = ohm_getfeedbacktxt('NA', ['yes', 'no'], 4);
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Empty answers -- Multi-part
     */

    public function test_multi_part_null_answer(): void
    {
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $qnIndex = 'qn' . (1000 + $partNumber);
            $expectedFeedback = [
                $qnIndex . '-0' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow'
                ],
                $qnIndex . '-1' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'More meow'
                ],
                $qnIndex . '-2' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow again'
                ],
                $qnIndex . '-3' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow loudly'
                ],
                $qnIndex . '-4' => [
                    'correctness' => 'correct',
                    'feedback' => 'Meow!'
                ],
            ];

            $feedback = ohm_getfeedbacktxt(null, $feedbacksPossible, 4, $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    public function test_multi_part_empty_answer(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $feedback = ohm_getfeedbacktxt('', ['yes', 'no'], 4, $partNumber);
            $this->assertEquals([], $feedback);
        }
    }

    public function test_multi_part_NA_answer(): void
    {
        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $expectedFeedback = [
                'qn' . (1000 + $partNumber) => [
                    'correctness' => 'incorrect',
                    'feedback' => _("No answer selected. Try again.")
                ]
            ];

            $feedback = ohm_getfeedbacktxt('NA', ['yes', 'no'], 4, $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    /*
     * One correct answer -- Single part
     */

    public function test_single_part_one_correct(): void
    {
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        $expectedFeedback = [
            'qn0-0' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow'
            ],
            'qn0-1' => [
                'correctness' => 'incorrect',
                'feedback' => 'More meow'
            ],
            'qn0-2' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow again'
            ],
            'qn0-3' => [
                'correctness' => 'incorrect',
                'feedback' => 'Meow loudly'
            ],
            'qn0-4' => [
                'correctness' => 'correct',
                'feedback' => 'Meow!'
            ],
        ];

        $feedback = ohm_getfeedbacktxt(4, $feedbacksPossible, 4);
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * One correct answer -- Multi-part
     */

    public function test_multi_part_one_correct(): void
    {
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
            $qnIndex = 'qn' . (1000 + $partNumber);
            $expectedFeedback = [
                $qnIndex . '-0' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow'
                ],
                $qnIndex . '-1' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'More meow'
                ],
                $qnIndex . '-2' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow again'
                ],
                $qnIndex . '-3' => [
                    'correctness' => 'incorrect',
                    'feedback' => 'Meow loudly'
                ],
                $qnIndex . '-4' => [
                    'correctness' => 'correct',
                    'feedback' => 'Meow!'
                ],
            ];

            $feedback = ohm_getfeedbacktxt(4, $feedbacksPossible, 4, $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }

    /*
     * Three correct answers -- Single part
     */

    public function test_single_part_three_correct(): void
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

        $feedback = ohm_getfeedbacktxt(4, $feedbacksPossible, '1 or 3 or 4');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Three correct answers -- Multi-part
     */

    public function test_multi_part_three_correct(): void
    {
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
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

            $feedback = ohm_getfeedbacktxt(4, $feedbacksPossible, '1 or 3 or 4', $partNumber);
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

        $feedback = ohm_getfeedbacktxt(0, $feedbacksPossible, '1 or 3 or 4');
        $this->assertEquals($expectedFeedback, $feedback);
    }

    /*
     * Answer 0 selected -- Multi-part
     */

    public function test_multi_part_zero_selected(): void
    {
        $feedbacksPossible = ['Meow', 'More meow', 'Meow again', 'Meow loudly', 'Meow!'];

        // Test different part numbers.
        foreach ([0, 2, 4, 7] as $partNumber) {
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

            $feedback = ohm_getfeedbacktxt(0, $feedbacksPossible, '1 or 3 or 4', $partNumber);
            $this->assertEquals($expectedFeedback, $feedback);
        }
    }
}
