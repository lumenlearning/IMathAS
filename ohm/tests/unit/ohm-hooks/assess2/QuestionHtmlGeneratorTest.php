<?php

namespace OHM\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../../../../assess2/questions/models/Question.php');
use IMathAS\assess2\questions\models\Question;

/**
 * Covers OHM hook file: /ohm-hooks/assess2/questions/question_html_generator.php
 *
 * The file under test contains only function definitions.
 * In other words, the tested file has no class.
 */
final class QuestionHtmlGeneratorTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
    }

    public $question; // [Question] The question object to be returned by getQuestion().
    public $feedback = ['Correct!', 'Incorrect.']; // [?array] The feedback for the question.

    public $toevalqtxtp = '<p>What is the answer?</p>';
    public $toevalqtxt; // [String] The raw, unevaluated question text used to generate $question
    public $qtextvars = []; // [array] The question text variable names


    public function setUp(): void
    {
        $this->question = new Question(
            '$evaledqtext',
            ['$jsParams'],
            ['$answeights'],
            '$evaledsoln',
            '$detailedSolutionContent',
            ['$displayedAnswersForParts'],
            ['$externalReferences']
        );
        $this->toevalqtxt = $this->toevalqtxtp . '&nbsp;$answerbox[0]&nbsp;$answerbox[1]';

        $question = $this->question;
        $feedback = $this->feedback;
        $toevalqtxt = $this->toevalqtxt;
        $qtextvars = $this->qtextvars;

        // require with each new test for scoping reasons on the anonymous function and $this
        require(__DIR__ . '/../../../../../ohm-hooks/assess2/questions/question_html_generator.php');
        $onGetQuestion(); // ignore Undefined variable warning
    }

    /*
     * $onGetQuestion
     *
     * Template: public function testOnGetQuestion_(): void
        {
            $this->assertTrue(true);
        }
    */

    public function testOnGetQuestion_setsFeedbackOnQuestion(): void
    {
        $this->assertEquals($this->feedback, $this->question->getExtraData()['lumenlearning']['feedback']);
    }

    public function testOnGetQuestion_setsJsonTextOnQuestion(): void
    {
        $this->assertArrayHasKey('text', $this->question->getExtraData()['lumenlearning']['json']);
        $this->assertIsString($this->question->getExtraData()['lumenlearning']['json']['text']);
        $count = substr_count($this->question->getExtraData()['lumenlearning']['json']['text'], $this->toevalqtxtp);
        $this->assertEquals(1, $count);
    }

    public function testOnGetQuestion_createsPlaceholdersForAnswerboxes(): void
    {
        $text = $this->question->getExtraData()['lumenlearning']['json']['text'];

        $original_count = substr_count($this->toevalqtxt, '$answerbox');
        $count = substr_count($text, 'ANSWERBOX_PLACEHOLDER');
        $final_count = substr_count($text, '$answerbox');

        // I.e. this is testing that onGetQuestion replaced $answerbox twice with ANSWERBOX_PLACEHOLDER
        $this->assertEquals(0, $final_count);
        $this->assertEquals($original_count, $count);
    }

    public function testOnGetQuestion_leveragesEvalWithVarInitInstanceMethod(): void
    {
        $text = $this->question->getExtraData()['lumenlearning']['json']['text'];
        // count will be the number of replacements made
        preg_replace('/evalWithVarInit/', '', $text, -1, $count);

        // I.e. this is testing that the text value passed through the evalWithVarInit method of $this
        // In this case, $this is the current Test Class (see evalWithVarInit below)
        $this->assertEquals(1, $count);
    }

    /*
     * Helper Methods
    */
    public function evalWithVarInit($toevalcode, $vars): String {
        return $toevalcode . 'evalWithVarInit';
    }
}
