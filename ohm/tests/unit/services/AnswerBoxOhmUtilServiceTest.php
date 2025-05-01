<?php

namespace OHM\tests\unit\services;

require_once __DIR__ . '/../../../../assess2/questions/answerboxes/AnswerBoxParams.php';

use IMathAS\assess2\questions\answerboxes\AnswerBoxParams;
use OHM\Services\AnswerBoxOhmUtilService;
use PHPUnit\Framework\TestCase;

/**
 * @covers AnswerBoxOhmUtilService
 */
final class AnswerBoxOhmUtilServiceTest extends TestCase
{
    private $answerBoxOhmUtilService;

    function setUp(): void
    {
        $this->answerBoxOhmUtilService = new AnswerBoxOhmUtilService();
    }

    /*
     * formatAndReturnQuestionVariables
     */

    function testFormatAndReturnQuestionVariables_SinglePart(): void
    {
        $optionVariablesAndValues = [
            'noshuffle' => 'all',
            'questions' => [
                'First choice',
                'Second choice',
                'Third choice',
                'All of the above'
            ],
            'randkeys' => [0, 1, 2, 3],
            'answer' => 0,
            'scoremethod' => '',
            'meow' => 'loudly',
        ];
        $variableNameRemap = [
            'questions' => 'choices',
            'randkeys' => 'shuffledChoicesIndex',
        ];
        $answerBoxParams = new AnswerBoxParams();
        $answerBoxParams->setIsMultiPartQuestion(false);

        $optionVariablesReturned = $this->answerBoxOhmUtilService->formatAndReturnQuestionVariables(
            $optionVariablesAndValues, $variableNameRemap, $answerBoxParams);

        $questionVars = $optionVariablesReturned['qn0'];
        $this->assertEquals('all', $questionVars['noshuffle']);
        $this->assertEquals('First choice', $questionVars['choices'][0]);
        $this->assertEquals('Second choice', $questionVars['choices'][1]);
        $this->assertEquals('Third choice', $questionVars['choices'][2]);
        $this->assertEquals('All of the above', $questionVars['choices'][3]);
        $this->assertEquals([0, 1, 2, 3], $questionVars['shuffledChoicesIndex']);
        $this->assertEquals(0, $questionVars['answer']);
        $this->assertEquals('', $questionVars['scoremethod']);
    }

    function testFormatAndReturnQuestionVariables_MultiPart(): void
    {
        $optionVariablesAndValues = [
            'noshuffle' => 'all',
            'questions' => [
                'First choice',
                'Second choice',
                'Third choice',
                'All of the above'
            ],
            'randkeys' => [0, 1, 2, 3],
            'answer' => 0,
            'scoremethod' => '',
            'meow' => 'loudly',
        ];
        $variableNameRemap = [
            'questions' => 'choices',
            'randkeys' => 'shuffledChoicesIndex',
        ];
        $answerBoxParams = new AnswerBoxParams();
        $answerBoxParams->setIsMultiPartQuestion(true);
        $answerBoxParams->setQuestionPartNumber(1);

        $optionVariablesReturned = $this->answerBoxOhmUtilService->formatAndReturnQuestionVariables(
            $optionVariablesAndValues, $variableNameRemap, $answerBoxParams);

        $questionVars = $optionVariablesReturned['qn1001'];
        $this->assertEquals('all', $questionVars['noshuffle']);
        $this->assertEquals('First choice', $questionVars['choices'][0]);
        $this->assertEquals('Second choice', $questionVars['choices'][1]);
        $this->assertEquals('Third choice', $questionVars['choices'][2]);
        $this->assertEquals('All of the above', $questionVars['choices'][3]);
        $this->assertEquals([0, 1, 2, 3], $questionVars['shuffledChoicesIndex']);
        $this->assertEquals(0, $questionVars['answer']);
        $this->assertEquals('', $questionVars['scoremethod']);
    }
}