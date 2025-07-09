<?php

namespace OHM\Tests\Unit\assess2\questions\scorepart;

use IMathAS\assess2\questions\models\ScoreQuestionParams;
use IMathAS\assess2\questions\ScoreEngine;
use Mockery;
use PDO;
use Rand;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Controllers\QuestionController\DbFixtures;

require_once __DIR__ . '/../../../../../../assess2/questions/ScoreEngine.php';
require_once __DIR__ . '/../../../../../../assess2/questions/answerboxhelpers.php';
require_once __DIR__ . '/../../../../../../assessment/interpret5.php';
require_once __DIR__ . '/../../../../../../includes/Rand.php';
require_once __DIR__ . '/../../../../../../ohm/lumenapi/tests/unit/Controllers/QuestionController/DbFixtures.php';

class ChoicesScorePartTest extends TestCase
{
    private PDO $pdo;

    function setUp(): void
    {
        $GLOBALS['RND'] = new Rand();

        $this->pdo = Mockery::mock(PDO::class);
    }

    function testGetResult_shufflingIsDisabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['meows'];

        $scoreEngine = new ScoreEngine($this->pdo, $GLOBALS['RND']);

        /*
         * First question.
         */

        $scoreQuestionParams = new ScoreQuestionParams();
        $scoreQuestionParams
            ->setUserRights(10)
            ->setRandWrapper($GLOBALS['RND'])
            ->setQuestionNumber(1)
            ->setQuestionData(DbFixtures::imas_QuestionSet_dbRow_choices)
            ->setAssessmentId(0)
            ->setDbQuestionSetId(3607)
            ->setQuestionSeed(1234)
            ->setGivenAnswer('0')
            ->setAttemptNumber(1)
            ->setAllQuestionAnswers(['0'])
            ->setAllQuestionAnswersAsNum([0])
            ->setPartsToScore(null)
            ->setQnpointval(1);

        $scoreResult = $scoreEngine->scoreQuestion($scoreQuestionParams);

        $this->assertEquals([], $scoreResult['errors']);
        $this->assertEquals([1], $scoreResult['scores']);

        /*
         * Same question, different seed.
         */

        $scoreQuestionParams = new ScoreQuestionParams();
        $scoreQuestionParams
            ->setUserRights(10)
            ->setRandWrapper($GLOBALS['RND'])
            ->setQuestionNumber(1)
            ->setQuestionData(DbFixtures::imas_QuestionSet_dbRow_choices)
            ->setAssessmentId(0)
            ->setDbQuestionSetId(3607)
            ->setQuestionSeed(4321)
            ->setGivenAnswer('0')
            ->setAttemptNumber(1)
            ->setAllQuestionAnswers(['0'])
            ->setAllQuestionAnswersAsNum([0])
            ->setPartsToScore(null)
            ->setQnpointval(1);

        $scoreResult = $scoreEngine->scoreQuestion($scoreQuestionParams);

        $this->assertEquals([], $scoreResult['errors']);
        // The same answer should be scored as correct.
        $this->assertEquals([1], $scoreResult['scores']);
    }

    function testGetResult_shufflingIsEnabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['choices'];

        $scoreEngine = new ScoreEngine($this->pdo, $GLOBALS['RND']);

        /*
         * First question.
         */

        $scoreQuestionParams = new ScoreQuestionParams();
        $scoreQuestionParams
            ->setUserRights(10)
            ->setRandWrapper($GLOBALS['RND'])
            ->setQuestionNumber(1)
            ->setQuestionData(DbFixtures::imas_QuestionSet_dbRow_choices)
            ->setAssessmentId(0)
            ->setDbQuestionSetId(3607)
            ->setQuestionSeed(1234)
            ->setGivenAnswer('2')
            ->setAttemptNumber(1)
            ->setAllQuestionAnswers(['2'])
            ->setAllQuestionAnswersAsNum([2])
            ->setPartsToScore(null)
            ->setQnpointval(1);

        $scoreResult = $scoreEngine->scoreQuestion($scoreQuestionParams);

        $this->assertEquals([], $scoreResult['errors']);
        $this->assertEquals([1], $scoreResult['scores']);

        /*
         * Same question, different seed.
         */

        $scoreQuestionParams = new ScoreQuestionParams();
        $scoreQuestionParams
            ->setUserRights(10)
            ->setRandWrapper($GLOBALS['RND'])
            ->setQuestionNumber(1)
            ->setQuestionData(DbFixtures::imas_QuestionSet_dbRow_choices)
            ->setAssessmentId(0)
            ->setDbQuestionSetId(3607)
            ->setQuestionSeed(4321)
            ->setGivenAnswer('0')
            ->setAttemptNumber(1)
            ->setAllQuestionAnswers(['0'])
            ->setAllQuestionAnswersAsNum([0])
            ->setPartsToScore(null)
            ->setQnpointval(1);

        $scoreResult = $scoreEngine->scoreQuestion($scoreQuestionParams);

        $this->assertEquals([], $scoreResult['errors']);
        // A different answer should be scored as correct.
        $this->assertEquals([1], $scoreResult['scores']);
    }
}