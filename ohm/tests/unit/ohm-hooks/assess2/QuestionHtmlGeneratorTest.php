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
    public $question; // [Question] The question object to be returned by getQuestion().
    public $feedback; // [?array] The feedback for the question.
    public $evaledqtextwithoutanswerbox; // [String] The evaluated text for the question without answerbox evaled

    public function setUp(): void
    {
        $question = new Question(
            '$evaledqtext',
            ['$jsParams'],
            ['$answeights'],
            '$evaledsoln',
            '$detailedSolutionContent',
            ['$displayedAnswersForParts'],
            ['$externalReferences']
        );
        $feedback = ['Correct!', 'Incorrect.'];
        $evaledqtextwithoutanswerbox = '<p>What is the answer?</p>ANSWERBOX_PLACEHOLDER';

        $this->evaledqtextwithoutanswerbox = $evaledqtextwithoutanswerbox;
        $this->question = $question;
        $this->feedback = $feedback;

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
        $this->assertNotEmpty($this->question->getExtraData()['lumenlearning']['json']['text']);
        $this->assertEquals($this->evaledqtextwithoutanswerbox, $this->question->getExtraData()['lumenlearning']['json']['text']);
    }
}
