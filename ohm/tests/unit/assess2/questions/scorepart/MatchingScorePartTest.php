<?php

namespace OHM\Tests\Unit\assess2\questions\scorepart;

use IMathAS\assess2\questions\models\ScoreQuestionParams;
use IMathAS\assess2\questions\ScoreEngine;
use Mockery;
use PDO;
use Rand;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Controllers\QuestionController\DbFixtures;

/**
 * @group ohm
 */
class MatchingScorePartTest extends TestCase
{
    private PDO $pdo;

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
        require_once __DIR__ . '/../../../../../../assess2/questions/ScoreEngine.php';
        require_once __DIR__ . '/../../../../../../assess2/questions/answerboxhelpers.php';
        require_once __DIR__ . '/../../../../../../assessment/interpret5.php';
        require_once __DIR__ . '/../../../../../../assessment/macros.php';
        require_once __DIR__ . '/../../../../../../includes/Rand.php';
        require_once __DIR__ . '/../../../../../../includes/sanitize.php';
        require_once __DIR__ . '/../../../../../../ohm/lumenapi/tests/unit/Controllers/QuestionController/DbFixtures.php';

        $GLOBALS['RND'] = new Rand();
        $GLOBALS['myrights'] = 10;

        $this->pdo = Mockery::mock(PDO::class);
    }

    function testGetResult_shufflingIsDisabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['meows'];

        $scoreEngine = new ScoreEngine($this->pdo, $GLOBALS['RND']);

        /*
         * First question.
         */

        // MatchingScorePart looks directly at these values to get given answers.
        $_POST = [
            'qn1-0' => 0,
            'qn1-1' => 1,
            'qn1-2' => 2,
            'qn1-3' => 3,
            'qn1-4' => 4,
            'qn1-5' => 5,
        ];

        $scoreQuestionParams = new ScoreQuestionParams();
        $scoreQuestionParams
            ->setUserRights(10)
            ->setRandWrapper($GLOBALS['RND'])
            ->setQuestionNumber(1)
            ->setQuestionData(DbFixtures::imas_QuestionSet_dbRow_matching_single_part)
            ->setAssessmentId(0)
            ->setDbQuestionSetId(1610)
            ->setQuestionSeed(1234)
            ->setGivenAnswer('0|1|2|3|4|5')
            ->setAttemptNumber(1)
            ->setAllQuestionAnswers(['0|1|2|3|4|5'])
            ->setAllQuestionAnswersAsNum([])
            ->setPartsToScore(null)
            ->setQnpointval(1);

        $scoreResult = $scoreEngine->scoreQuestion($scoreQuestionParams);

        $this->assertEquals([], $scoreResult['errors']);
        $this->assertEquals([1], $scoreResult['scores']);

        /*
         * Same question, different seed.
         */

        // MatchingScorePart looks directly at these values to get given answers.
        $_POST = [
            'qn1-0' => 0,
            'qn1-1' => 1,
            'qn1-2' => 2,
            'qn1-3' => 3,
            'qn1-4' => 4,
            'qn1-5' => 5,
        ];

        $scoreQuestionParams = new ScoreQuestionParams();
        $scoreQuestionParams
            ->setUserRights(10)
            ->setRandWrapper($GLOBALS['RND'])
            ->setQuestionNumber(1)
            ->setQuestionData(DbFixtures::imas_QuestionSet_dbRow_matching_single_part)
            ->setAssessmentId(0)
            ->setDbQuestionSetId(1610)
            ->setQuestionSeed(4321)
            ->setGivenAnswer('0|1|2|3|4|5')
            ->setAttemptNumber(1)
            ->setAllQuestionAnswers(['0|1|2|3|4|5'])
            ->setAllQuestionAnswersAsNum([2, 3])
            ->setPartsToScore(null)
            ->setQnpointval(1);

        $scoreResult = $scoreEngine->scoreQuestion($scoreQuestionParams);

        $this->assertEquals([], $scoreResult['errors']);
        // The same answer should be scored as correct.
        $this->assertEquals([1], $scoreResult['scores']);
    }

    function testGetResult_shufflingIsEnabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['matching'];

        $scoreEngine = new ScoreEngine($this->pdo, $GLOBALS['RND']);

        /*
         * First question.
         */

        // MatchingScorePart looks directly at these values to get given answers.
        $_POST = [
            'qn1-0' => 1,
            'qn1-1' => 2,
            'qn1-2' => 5,
            'qn1-3' => 4,
            'qn1-4' => 7,
            'qn1-5' => 3,
        ];

        $scoreQuestionParams = new ScoreQuestionParams();
        $scoreQuestionParams
            ->setUserRights(10)
            ->setRandWrapper($GLOBALS['RND'])
            ->setQuestionNumber(1)
            ->setQuestionData(DbFixtures::imas_QuestionSet_dbRow_matching_single_part)
            ->setAssessmentId(0)
            ->setDbQuestionSetId(1610)
            ->setQuestionSeed(1234)
            ->setGivenAnswer('0|1|2|3|4|5')
            ->setAttemptNumber(1)
            ->setAllQuestionAnswers(['0|1|2|3|4|5'])
            ->setAllQuestionAnswersAsNum([])
            ->setPartsToScore(null)
            ->setQnpointval(1);

        $scoreResult = $scoreEngine->scoreQuestion($scoreQuestionParams);

        $this->assertEquals([], $scoreResult['errors']);
        $this->assertEquals([1], $scoreResult['scores']);

        /*
         * Same question, different seed.
         */

        // MatchingScorePart looks directly at these values to get given answers.
        $_POST = [
            'qn1-0' => 6,
            'qn1-1' => 3,
            'qn1-2' => 1,
            'qn1-3' => 4,
            'qn1-4' => 0,
            'qn1-5' => 7,
        ];

        $scoreQuestionParams = new ScoreQuestionParams();
        $scoreQuestionParams
            ->setUserRights(10)
            ->setRandWrapper($GLOBALS['RND'])
            ->setQuestionNumber(1)
            ->setQuestionData(DbFixtures::imas_QuestionSet_dbRow_matching_single_part)
            ->setAssessmentId(0)
            ->setDbQuestionSetId(1610)
            ->setQuestionSeed(4321)
            ->setGivenAnswer(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'])
            ->setAttemptNumber(1)
            ->setAllQuestionAnswers(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'])
            ->setAllQuestionAnswersAsNum([])
            ->setPartsToScore(null)
            ->setQnpointval(1);

        $scoreResult = $scoreEngine->scoreQuestion($scoreQuestionParams);

        $this->assertEquals([], $scoreResult['errors']);
        // A different answer should be scored as correct.
        $this->assertEquals([1], $scoreResult['scores']);
    }
}