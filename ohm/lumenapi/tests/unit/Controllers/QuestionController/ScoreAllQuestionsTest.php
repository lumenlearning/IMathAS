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

class ScoreAllQuestionsTest extends TestCase
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

    public function testScoreAllQuestions_byExternalId(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getByExternalId')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        // When both a questionSetId and externalId are requested, only
        // the externalId should be used.
        $request = Request::create('/api/v1/questions/score', 'POST',
            json_decode('[
                {
                    "post": [
                        {
                            "name": "qn0",
                            "value": "10"
                        }
                    ],
                    "externalId": "a741e53b-d37a-49aa-88cf-c8226b7cc170",
                    "seed": 3469,
                    "studentAnswers": ["10"],
                    "studentAnswerValues": ["10"],
                    "partAttemptNumber": [0]
                },
                {
                    "post": [
                        {
                            "name": "qn0",
                            "value": "8"
                        }
                    ],
                    "externalId": "4aa944ce-ccea-4966-a00f-bb9f89a2f1d8",
                    "seed": 5106,
                    "studentAnswers": ["8"],
                    "studentAnswerValues": ["8"],
                    "partAttemptNumber": [0]
                }
            ]', true)
        );

        $response = $this->questionController->scoreAllQuestions($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());

        $question1 = $responseData[0];
        $this->assertEquals('a741e53b-d37a-49aa-88cf-c8226b7cc170', $question1['externalId']);
        $this->assertEquals('number', $question1['questionType']);
        $this->assertEquals(3469, $question1['seed']);
        $this->assertEquals([1.0], $question1['scores']);
        $this->assertEquals([1.0], $question1['raw']);
        $this->assertEquals([], $question1['errors']);
        $this->assertTrue($question1['allans']);
        $this->assertNotEmpty($question1['correctAnswers']);
        $this->assertEquals('10', $question1['correctAnswers'][0]);

        $question2 = $responseData[1];
        $this->assertEquals('4aa944ce-ccea-4966-a00f-bb9f89a2f1d8', $question2['externalId']);
        $this->assertEquals('number', $question2['questionType']);
        $this->assertEquals(5106, $question2['seed']);
        $this->assertEquals([1.0], $question2['scores']);
        $this->assertEquals([1.0], $question2['raw']);
        $this->assertEquals([], $question2['errors']);
        $this->assertTrue($question2['allans']);
        $this->assertNotEmpty($question2['correctAnswers']);
        $this->assertEquals('8', $question2['correctAnswers'][0]);
    }
}
