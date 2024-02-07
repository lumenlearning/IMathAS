<?php

namespace Tests\Unit\Controllers\QuestionController;

use App\Repositories\Interfaces\AssessmentRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Repositories\ohm\AssessmentRepository;
use App\Repositories\ohm\QuestionSetRepository;
use Illuminate\Http\Request;
use Mockery;
use PDO;
use Tests\TestCase;

use App\Http\Controllers\QuestionController;

// Required for tests to work in GitHub Actions.
require_once(__DIR__ . '/../../../../../../i18n/i18n.php');

class ScoreQuestionTest extends TestCase
{
    private QuestionController $questionController;
    private AssessmentRepositoryInterface  $assessmentRepository;
    private QuestionSetRepositoryInterface $questionSetRepository;

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
        $this->questionController = new QuestionController($this->assessmentRepository,
            $this->questionSetRepository);

        $this->pdo = Mockery::mock(PDO::class);
        $this->questionController->setPdo($this->pdo);
    }

    public function testScoreQuestion(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        $request = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": "10"
                    }
                ],
                "questionSetId": 42,
                "seed": 3469,
                "studentAnswers": ["10"],
                "studentAnswerValues": ["10"],
                "partAttemptNumber": [0]
            }', true)
        );

        $response = $this->questionController->scoreQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(42, $responseData['questionSetId']);
        $this->assertEquals('number', $responseData['questionType']);
        $this->assertEquals(3469, $responseData['seed']);
        $this->assertEquals([1.0], $responseData['scores']);
        $this->assertEquals([1.0], $responseData['raw']);
        $this->assertEquals([], $responseData['errors']);
        $this->assertTrue($responseData['allans']);
        $this->assertNotEmpty($responseData['correctAnswers']);
        $this->assertEquals('10', $responseData['correctAnswers'][0]);
    }

    public function testScoreQuestion_byUniqueId(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getByUniqueId')
            ->withArgs(['1491933600157156'])
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        $request = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": "10"
                    }
                ],
                "ohmUniqueId": "1acsve483f4",
                "seed": 3469,
                "studentAnswers": ["10"],
                "studentAnswerValues": ["10"],
                "partAttemptNumber": [0]
            }', true)
        );

        $response = $this->questionController->scoreQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(42, $responseData['questionSetId']);
        $this->assertEquals('1acsve483f4', $responseData['ohmUniqueId']);
        $this->assertEquals('number', $responseData['questionType']);
        $this->assertEquals(3469, $responseData['seed']);
        $this->assertEquals([1.0], $responseData['scores']);
        $this->assertEquals([1.0], $responseData['raw']);
        $this->assertEquals([], $responseData['errors']);
        $this->assertTrue($responseData['allans']);
        $this->assertNotEmpty($responseData['correctAnswers']);
        $this->assertEquals('10', $responseData['correctAnswers'][0]);
    }

    public function testScoreQuestion_byUniqueIdAndQuestionSetId(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getByUniqueId')
            ->withArgs(['1491933600157156'])
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        // When both a questionSetId and ohmUniqueId are requested, only
        // the ohmUniqueId should be used.
        $request = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": "10"
                    }
                ],
                "questionSetId": 424242,
                "ohmUniqueId": "1acsve483f4",
                "seed": 3469,
                "studentAnswers": ["10"],
                "studentAnswerValues": ["10"],
                "partAttemptNumber": [0]
            }', true)
        );

        $response = $this->questionController->scoreQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(42, $responseData['questionSetId']);
        $this->assertEquals('1acsve483f4', $responseData['ohmUniqueId']);
        $this->assertEquals('number', $responseData['questionType']);
        $this->assertEquals(3469, $responseData['seed']);
        $this->assertEquals([1.0], $responseData['scores']);
        $this->assertEquals([1.0], $responseData['raw']);
        $this->assertEquals([], $responseData['errors']);
        $this->assertTrue($responseData['allans']);
        $this->assertNotEmpty($responseData['correctAnswers']);
        $this->assertEquals('10', $responseData['correctAnswers'][0]);
    }

    /**
     * Usage of OHM1 basic/txt feedback macros requires shuffling to be disabled.
     */
    public function testScoreQuestion_with_ohm1_macro(): void
    {
        $request = Request::create('/api/v1/question/score', 'POST',
            json_decode('{
                "post": [
                    {
                        "name": "qn0",
                        "value": 0
                    }
                ],
                "questionSetId": 3607,
                "seed": 3469,
                "studentAnswers": [0],
                "studentAnswerValues": ["0"]
            }', true)
        );

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_with_ohm1_macro);

        $response = $this->questionController->scoreQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(3607, $responseData['questionSetId']);
        $this->assertEquals('choices', $responseData['questionType']);
        $this->assertEquals(3469, $responseData['seed']);
        $this->assertEquals([1.0], $responseData['scores']);
        $this->assertEquals([1.0], $responseData['raw']);
        $this->assertTrue($responseData['allans']);
        $this->assertNotEmpty($responseData['correctAnswers']);
        $this->assertEquals('0', $responseData['correctAnswers'][0]);
        $this->assertCount(2, $responseData['errors']);
        $this->assertContains(
            'Warning: Feedback may be available but is not being returned due to the usage of OHM1 macros!',
            $responseData['errors']
        );
        $this->assertStringContainsString('Warning: OHM1 feedback = ', $responseData['errors'][1]);
    }
}
