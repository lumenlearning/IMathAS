<?php

use PHPUnit\Framework\TestCase;


/*
 * This tests "private" functions in /assessment/libs/ohm_macros.php.
 */

final class OhmMacrosTest extends TestCase
{
    static function setUpBeforeClass(): void
    {
        // This normally contains a list of allowed macros defined by
        // IMathAS, but we declare it as an empty array here since we're
        // loading ohm_macros.php in isolation for testing.
        $GLOBALS['allowedmacros'] = [];

        // This file contains OHM 2 macros as PHP functions instead of a class.
        require_once(__DIR__ . '/../../../../../../assessment/libs/ohm_macros.php');
    }

    /*
     * _getFeedbackIndex
     */

    public function testGetFeedbackIndex_Null(): void
    {
        $qnIndex = _getFeedbackIndex(null);
        $this->assertEquals('qn0', $qnIndex);
    }

    public function testGetFeedbackIndex_MultiPart0(): void
    {
        $qnIndex = _getFeedbackIndex(0);
        $this->assertEquals('qn1000', $qnIndex);
    }

    public function testGetFeedbackIndex_MultiPart3(): void
    {
        $qnIndex = _getFeedbackIndex(3);
        $this->assertEquals('qn1003', $qnIndex);
    }

    /*
     * _getAllFeedbacksWithCorrectness
     */

    public function testGetAllFeedbacksWithCorrectness_SinglePart_OneCorrect(): void
    {
        $feedbacksPossible = ['Correct!', 'Nope!', 'Try again.', 'Lol. Nope!'];
        $indexedFeedback = _getAllFeedbacksWithCorrectness($feedbacksPossible, [0]);

        $expectedFeedback = [
            'qn0-0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct!'
            ],
            'qn0-1' => [
                'correctness' => 'incorrect',
                'feedback' => 'Nope!'
            ],
            'qn0-2' => [
                'correctness' => 'incorrect',
                'feedback' => 'Try again.'
            ],
            'qn0-3' => [
                'correctness' => 'incorrect',
                'feedback' => 'Lol. Nope!'
            ]
        ];

        $this->assertEquals($expectedFeedback, $indexedFeedback);
    }

    public function testGetAllFeedbacksWithCorrectness_SinglePart_ThreeCorrect(): void
    {
        $feedbacksPossible = ['Correct!', 'Yay!', 'Try again.', 'Very good.', 'Nope!'];
        $indexedFeedback = _getAllFeedbacksWithCorrectness($feedbacksPossible, [0, 1, 3]);

        $expectedFeedback = [
            'qn0-0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct!'
            ],
            'qn0-1' => [
                'correctness' => 'correct',
                'feedback' => 'Yay!'
            ],
            'qn0-2' => [
                'correctness' => 'incorrect',
                'feedback' => 'Try again.'
            ],
            'qn0-3' => [
                'correctness' => 'correct',
                'feedback' => 'Very good.'
            ],
            'qn0-4' => [
                'correctness' => 'incorrect',
                'feedback' => 'Nope!'
            ],
        ];

        $this->assertEquals($expectedFeedback, $indexedFeedback);
    }

    public function testGetAllFeedbacksWithCorrectness_MultiPart_OneCorrect(): void
    {
        $feedbacksPossible = ['Correct!', 'Nope!', 'Try again.', 'Lol. Nope!'];
        $indexedFeedback = _getAllFeedbacksWithCorrectness($feedbacksPossible, [0], 2);

        $expectedFeedback = [
            'qn1002-0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct!'
            ],
            'qn1002-1' => [
                'correctness' => 'incorrect',
                'feedback' => 'Nope!'
            ],
            'qn1002-2' => [
                'correctness' => 'incorrect',
                'feedback' => 'Try again.'
            ],
            'qn1002-3' => [
                'correctness' => 'incorrect',
                'feedback' => 'Lol. Nope!'
            ]
        ];

        $this->assertEquals($expectedFeedback, $indexedFeedback);
    }

    public function testGetAllFeedbacksWithCorrectness_MultiPart_ThreeCorrect(): void
    {
        $feedbacksPossible = ['Correct!', 'Yay!', 'Try again.', 'Very good.', 'Nope!'];
        $indexedFeedback = _getAllFeedbacksWithCorrectness($feedbacksPossible, [0, 1, 3], 0);

        $expectedFeedback = [
            'qn1000-0' => [
                'correctness' => 'correct',
                'feedback' => 'Correct!'
            ],
            'qn1000-1' => [
                'correctness' => 'correct',
                'feedback' => 'Yay!'
            ],
            'qn1000-2' => [
                'correctness' => 'incorrect',
                'feedback' => 'Try again.'
            ],
            'qn1000-3' => [
                'correctness' => 'correct',
                'feedback' => 'Very good.'
            ],
            'qn1000-4' => [
                'correctness' => 'incorrect',
                'feedback' => 'Nope!'
            ],
        ];

        $this->assertEquals($expectedFeedback, $indexedFeedback);
    }
}
