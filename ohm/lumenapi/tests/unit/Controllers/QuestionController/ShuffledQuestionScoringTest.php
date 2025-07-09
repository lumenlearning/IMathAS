<?php

namespace Tests\unit\Controllers\QuestionController;

// Required for tests to work in GitHub Actions.
require_once(__DIR__ . '/../../../../../../i18n/i18n.php');

use App\Http\Controllers\QuestionController;

use App\Repositories\Interfaces\AssessmentRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Repositories\ohm\AssessmentRepository;
use App\Repositories\ohm\QuestionSetRepository;
use App\Services\Interfaces\QuestionServiceInterface;
use App\Services\ohm\QuestionService;
use Illuminate\Http\Request;
use Mockery;
use PDO;
use Tests\TestCase;

class ShuffledQuestionScoringTest extends TestCase
{
    private QuestionController $questionController;
    private AssessmentRepositoryInterface $assessmentRepository;
    private QuestionSetRepositoryInterface $questionSetRepository;
    private QuestionServiceInterface $questionService;

    private PDO $pdo;

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }

        $this->assessmentRepository = Mockery::mock(AssessmentRepository::class);
        $this->questionSetRepository = Mockery::mock(QuestionSetRepository::class);
        $this->questionService = Mockery::mock(QuestionService::class);
        $this->questionController = new QuestionController($this->assessmentRepository,
            $this->questionSetRepository, $this->questionService);

        $this->pdo = Mockery::mock(PDO::class);
        $this->questionController->setPdo($this->pdo);
    }

    /*
     * Question type: choices
     */

    public function testScoreQuestion_choices_shufflingIsDisabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['meows'];

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_choices);

        /*
         * First question.
         */

        $request = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": "0"
                    }
                ],
                "questionSetId": 3607,
                "seed": 1234,
                "studentAnswers": ["0"],
                "studentAnswerValues": ["0"]
            }', true)
        );

        $response = $this->questionController->scoreQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(3607, $responseData['questionSetId']);
        $this->assertEquals('choices', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertEquals([1.0], $responseData['scores']);
        $this->assertEquals([1.0], $responseData['raw']);
        $this->assertEquals([], $responseData['errors']);
        $this->assertNotEmpty($responseData['correctAnswers']);
        $this->assertEquals('0', $responseData['correctAnswers'][0]);

        /*
         * Second question.
         */

        $secondRequest = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": "0"
                    }
                ],
                "questionSetId": 3607,
                "seed": 4321,
                "studentAnswers": ["0"],
                "studentAnswerValues": ["0"]
            }', true)
        );

        $secondResponse = $this->questionController->scoreQuestion($secondRequest);
        $secondResponseData = $secondResponse->getData(true);

        $this->assertEquals(200, $secondResponse->getStatusCode());
        $this->assertEquals(3607, $secondResponseData['questionSetId']);
        $this->assertEquals('choices', $secondResponseData['questionType']);
        $this->assertEquals(4321, $secondResponseData['seed']);
        $this->assertEquals([1.0], $secondResponseData['scores']);
        $this->assertEquals([1.0], $secondResponseData['raw']);
        $this->assertEquals([], $secondResponseData['errors']);
        $this->assertNotEmpty($secondResponseData['correctAnswers']);
        $this->assertEquals('0', $secondResponseData['correctAnswers'][0]);

        // With shuffling disabled, the correct answers and feedback should match.
        $this->assertEquals($responseData['correctAnswers'][0], $secondResponseData['correctAnswers'][0]);
        $this->assertEquals($responseData['feedback'], $secondResponseData['feedback']);
    }

    public function testScoreQuestion_choices_shufflingIsEnabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['choices'];

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_choices);

        /*
         * First question.
         */

        $request = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": "2"
                    }
                ],
                "questionSetId": 3607,
                "seed": 1234,
                "studentAnswers": ["2"],
                "studentAnswerValues": ["2"]
            }', true)
        );

        $response = $this->questionController->scoreQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(3607, $responseData['questionSetId']);
        $this->assertEquals('choices', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertEquals([1.0], $responseData['scores']);
        $this->assertEquals([1.0], $responseData['raw']);
        $this->assertEquals([], $responseData['errors']);
        $this->assertNotEmpty($responseData['correctAnswers']);
        $this->assertEquals('2', $responseData['correctAnswers'][0]);

        /*
         * Second question.
         */

        $secondRequest = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": "0"
                    }
                ],
                "questionSetId": 3607,
                "seed": 4321,
                "studentAnswers": ["0"],
                "studentAnswerValues": ["0"]
            }', true)
        );

        $secondResponse = $this->questionController->scoreQuestion($secondRequest);
        $secondResponseData = $secondResponse->getData(true);

        $this->assertEquals(200, $secondResponse->getStatusCode());
        $this->assertEquals(3607, $secondResponseData['questionSetId']);
        $this->assertEquals('choices', $secondResponseData['questionType']);
        $this->assertEquals(4321, $secondResponseData['seed']);
        $this->assertEquals([1.0], $secondResponseData['scores']);
        $this->assertEquals([1.0], $secondResponseData['raw']);
        $this->assertEquals([], $secondResponseData['errors']);
        $this->assertNotEmpty($secondResponseData['correctAnswers']);
        $this->assertEquals('0', $secondResponseData['correctAnswers'][0]);

        // With shuffling disabled, the correct answers and feedback should differ.
        $this->assertNotEquals($responseData['correctAnswers'][0], $secondResponseData['correctAnswers'][0]);
        $this->assertNotEquals($responseData['feedback'], $secondResponseData['feedback']);
    }

    /*
     * Question type: multans
     */

    public function testScoreQuestion_multans_shufflingIsDisabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['meows'];

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_multans_basicfeedback);

        /*
         * First question.
         */

        $request = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": [2,3]
                    }
                ],
                "questionSetId": 7321,
                "seed": 1234,
                "studentAnswers": ["2","3"],
                "studentAnswerValues": [2,3]
            }', true)
        );

        $response = $this->questionController->scoreQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(7321, $responseData['questionSetId']);
        $this->assertEquals('multans', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertEquals([1.0], $responseData['scores']);
        $this->assertEquals([1.0], $responseData['raw']);
        $this->assertEquals([], $responseData['errors']);
        $this->assertNotEmpty($responseData['correctAnswers']);
        $this->assertEquals('2,3', $responseData['correctAnswers'][0]);

        /*
         * Second question.
         */

        $secondRequest = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": [2,3]
                    }
                ],
                "questionSetId": 7321,
                "seed": 4321,
                "studentAnswers": ["2","3"],
                "studentAnswerValues": [2,3]
            }', true)
        );

        $secondResponse = $this->questionController->scoreQuestion($secondRequest);
        $secondResponseData = $secondResponse->getData(true);

        $this->assertEquals(200, $secondResponse->getStatusCode());
        $this->assertEquals(7321, $secondResponseData['questionSetId']);
        $this->assertEquals('multans', $secondResponseData['questionType']);
        $this->assertEquals(4321, $secondResponseData['seed']);
        $this->assertEquals([1.0], $secondResponseData['scores']);
        $this->assertEquals([1.0], $secondResponseData['raw']);
        $this->assertEquals([], $secondResponseData['errors']);
        $this->assertNotEmpty($secondResponseData['correctAnswers']);
        $this->assertEquals('2,3', $secondResponseData['correctAnswers'][0]);

        // With shuffling disabled, the correct answers and feedback should match.
        $this->assertEquals($responseData['correctAnswers'][0], $secondResponseData['correctAnswers'][0]);
        $this->assertEquals($responseData['feedback'], $secondResponseData['feedback']);
    }

    public function testScoreQuestion_multans_shufflingIsEnabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['multans'];

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_multans_basicfeedback);

        /*
         * First question.
         */

        $request = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": [1,5]
                    }
                ],
                "questionSetId": 7321,
                "seed": 1234,
                "studentAnswers": ["1","5"],
                "studentAnswerValues": [1,5]
            }', true)
        );

        $response = $this->questionController->scoreQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(7321, $responseData['questionSetId']);
        $this->assertEquals('multans', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertEquals([1.0], $responseData['scores']);
        $this->assertEquals([1.0], $responseData['raw']);
        $this->assertEquals([], $responseData['errors']);
        $this->assertNotEmpty($responseData['correctAnswers']);
        $this->assertEquals('1,5', $responseData['correctAnswers'][0]);

        /*
         * Second question.
         */

        $secondRequest = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": [0,3]
                    }
                ],
                "questionSetId": 7321,
                "seed": 4321,
                "studentAnswers": ["0","3"],
                "studentAnswerValues": [0,3]
            }', true)
        );

        $secondResponse = $this->questionController->scoreQuestion($secondRequest);
        $secondResponseData = $secondResponse->getData(true);

        $this->assertEquals(200, $secondResponse->getStatusCode());
        $this->assertEquals(7321, $secondResponseData['questionSetId']);
        $this->assertEquals('multans', $secondResponseData['questionType']);
        $this->assertEquals(4321, $secondResponseData['seed']);
        $this->assertEquals([1.0], $secondResponseData['scores']);
        $this->assertEquals([1.0], $secondResponseData['raw']);
        $this->assertEquals([], $secondResponseData['errors']);
        $this->assertNotEmpty($secondResponseData['correctAnswers']);
        $this->assertEquals('0,3', $secondResponseData['correctAnswers'][0]);

        // With shuffling disabled, the correct answers should differ.
        $this->assertNotEquals($responseData['correctAnswers'][0], $secondResponseData['correctAnswers'][0]);
    }
}