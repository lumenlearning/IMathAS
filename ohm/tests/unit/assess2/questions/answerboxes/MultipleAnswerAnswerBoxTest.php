<?php

namespace OHM\Tests\Unit\assess2\questions\answerboxes;

use IMathAS\assess2\questions\answerboxes\AnswerBoxParams;
use IMathAS\assess2\questions\answerboxes\MultipleAnswerAnswerBox;
use IMathAS\assess2\questions\QuestionHtmlGenerator;
use PHPUnit\Framework\TestCase;
use Rand;

/**
 * Keeping this test in a group (using "@group ohm") allows for excluding these
 * tests from the group of MOM tests, where some functions are already defined
 * before we require_once the same file.
 *
 * This is also why our "require_once" statements are in the class' setUp()
 * method and not at the top of this PHP file.
 *
 * @group ohm
 * @covers MultipleAnswerAnswerBox
 */
final class MultipleAnswerAnswerBoxTest extends TestCase
{
    const MULTANS_QUESTION_CONTROL = '$questions = ["Birds", "Reptiles", "Dogs", "Cats", "Rodents"]
$answer = "0,3"
';

    function setUp(): void
    {
        /*
         * requires and require_onces typically go at the top of a file, but due to how some
         * functions in MOM are defined in global scope and required/require_onced in many
         * places, it was necessary to place the following require_onces here to avoid
         * attempts to define functions a second time.
         *
         * In combination with "@group ohm" at the class level, this prevents the following
         * files from being require'd until this test class is executed.
         */
        require_once __DIR__ . '/../../../../../../i18n/i18n.php';
        require_once __DIR__ . '/../../../../../../includes/Rand.php';
        require_once __DIR__ . '/../../../../../../includes/sanitize.php';
        require_once __DIR__ . '/../../../../../../assess2/questions/answerboxhelpers.php';
        require_once __DIR__ . '/../../../../../../assess2/questions/QuestionHtmlGenerator.php';
        require_once __DIR__ . '/../../../../../../assessment/interpret5.php';

        $GLOBALS['RND'] = new Rand();
    }

    public function testGenerate_ShufflingIsEnabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['multans'];

        eval(interpret('control', 'multans', self::MULTANS_QUESTION_CONTROL, 1, 42));

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
            ->setAnswerType('multans')
            ->setQuestionNumber(1)
            ->setIsMultiPartQuestion(false)
            ->setIsConditional(false)
            ->setAssessmentId(1)
            ->setStudentLastAnswers('')
            ->setColorboxKeyword(null)
            ->setCorrectAnswerWrongFormat(false);

        $answerBoxGenerator = new MultipleAnswerAnswerBox($answerBoxParams);
        $answerBoxGenerator->generate();

        $questionOptionVars = $answerBoxGenerator->getQuestionOptionVariables();
        $shuffledChoicesIdx = $questionOptionVars['qn0']['shuffledChoicesIndex'];
        $choicesIdxBeforeShuffling = array_keys($shuffledChoicesIdx);

        $this->assertNotEquals($choicesIdxBeforeShuffling, $shuffledChoicesIdx);
    }


    public function testGenerate_ShufflingIsDisabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['meows'];

        eval(interpret('control', 'multans', self::MULTANS_QUESTION_CONTROL, 1, 42));

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
            ->setAnswerType('multans')
            ->setQuestionNumber(1)
            ->setIsMultiPartQuestion(false)
            ->setIsConditional(false)
            ->setAssessmentId(1)
            ->setStudentLastAnswers('')
            ->setColorboxKeyword(null)
            ->setCorrectAnswerWrongFormat(false);

        $answerBoxGenerator = new MultipleAnswerAnswerBox($answerBoxParams);
        $answerBoxGenerator->generate();

        $questionOptionVars = $answerBoxGenerator->getQuestionOptionVariables();
        $shuffledChoicesIdx = $questionOptionVars['qn0']['shuffledChoicesIndex'];
        $choicesIdxBeforeShuffling = array_keys($shuffledChoicesIdx);

        $this->assertEquals($choicesIdxBeforeShuffling, $shuffledChoicesIdx);
    }
}