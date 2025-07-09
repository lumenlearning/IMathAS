<?php

namespace OHM\Tests\Unit\assess2\questions\answerboxes;

use IMathAS\assess2\questions\answerboxes\AnswerBoxParams;
use IMathAS\assess2\questions\answerboxes\MatchingAnswerBox;
use IMathAS\assess2\questions\QuestionHtmlGenerator;
use PHPUnit\Framework\TestCase;
use Rand;

require_once __DIR__ . '/../../../../../../includes/Rand.php';
require_once __DIR__ . '/../../../../../../includes/sanitize.php';
require_once __DIR__ . '/../../../../../../assess2/questions/answerboxhelpers.php';
require_once __DIR__ . '/../../../../../../assessment/interpret5.php';

/**
 * @covers MatchingAnswerBox
 */
final class MatchingAnswerBoxTest extends TestCase
{
    const MATCHING_QUESTION_CONTROL = '$questions = array("One","Two","Three","Four","Five","Six")
$answers = array("A","B","C","D","E","F")
$displayformat = "select"
';

    function setUp(): void
    {
        $GLOBALS['RND'] = new Rand();
    }

    public function testGenerate_ShufflingIsEnabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['matching'];

        eval(interpret('control', 'matching', self::MATCHING_QUESTION_CONTROL, 1, 42));

        $questionWriterVars = array();
        foreach (QuestionHtmlGenerator::ALLOWED_QUESTION_WRITER_VARS as $optionKey) {
            if (!isset(${$optionKey})) {
                continue;
            }
            $questionWriterVars[$optionKey] = ${$optionKey};
        }
        $varsForAnswerBoxGenerator = array();
        foreach (QuestionHtmlGenerator::VARS_FOR_ANSWERBOX_GENERATOR as $vargenKey) {
            if (!isset(${$vargenKey})) {
                continue;
            }
            $varsForAnswerBoxGenerator[$vargenKey] = ${$vargenKey};
        }

        $answerBoxParams = new AnswerBoxParams();
        $answerBoxParams
            ->setQuestionWriterVars($questionWriterVars)
            ->setVarsForAnswerBoxGenerator($varsForAnswerBoxGenerator)
            ->setAnswerType('matching')
            ->setQuestionNumber(1)
            ->setIsMultiPartQuestion(false)
            ->setIsConditional(false)
            ->setAssessmentId(1)
            ->setStudentLastAnswers('')
            ->setColorboxKeyword(null)
            ->setCorrectAnswerWrongFormat(false);

        $answerBoxGenerator = new MatchingAnswerBox($answerBoxParams);
        $answerBoxGenerator->generate();

        $questionOptionVars = $answerBoxGenerator->getQuestionOptionVariables();
        $shuffledChoicesIdx = $questionOptionVars['qn0']['shuffledQuestionChoicesIndex'];
        $choicesIdxBeforeShuffling = array_keys($shuffledChoicesIdx);

        $this->assertNotEquals($choicesIdxBeforeShuffling, $shuffledChoicesIdx);
    }


    public function testGenerate_ShufflingIsDisabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['meows'];

        eval(interpret('control', 'matching', self::MATCHING_QUESTION_CONTROL, 1, 42));

        $questionWriterVars = array();
        foreach (QuestionHtmlGenerator::ALLOWED_QUESTION_WRITER_VARS as $optionKey) {
            if (!isset(${$optionKey})) {
                continue;
            }

            if ('answerformat' == $optionKey) {
                $answerformat = str_replace(' ', '', $answerformat);
            }

            $questionWriterVars[$optionKey] = ${$optionKey};
        }
        $varsForAnswerBoxGenerator = array();
        foreach (QuestionHtmlGenerator::VARS_FOR_ANSWERBOX_GENERATOR as $vargenKey) {
            if (!isset(${$vargenKey})) {
                continue;
            }
            $varsForAnswerBoxGenerator[$vargenKey] = ${$vargenKey};
        }

        $answerBoxParams = new AnswerBoxParams();
        $answerBoxParams
            ->setQuestionWriterVars($questionWriterVars)
            ->setVarsForAnswerBoxGenerator($varsForAnswerBoxGenerator)
            ->setAnswerType('matching')
            ->setQuestionNumber(1)
            ->setIsMultiPartQuestion(false)
            ->setIsConditional(false)
            ->setAssessmentId(1)
            ->setStudentLastAnswers('')
            ->setColorboxKeyword(null)
            ->setCorrectAnswerWrongFormat(false);

        $answerBoxGenerator = new MatchingAnswerBox($answerBoxParams);
        $answerBoxGenerator->generate();

        $questionOptionVars = $answerBoxGenerator->getQuestionOptionVariables();
        $shuffledChoicesIdx = $questionOptionVars['qn0']['shuffledQuestionChoicesIndex'];
        $choicesIdxBeforeShuffling = array_keys($shuffledChoicesIdx);

        $this->assertEquals($choicesIdxBeforeShuffling, $shuffledChoicesIdx);
    }
}