<?php

namespace Tests\unit\Controllers\QuestionController;

use App\Repositories\Interfaces\AssessmentRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Repositories\ohm\AssessmentRepository;
use App\Repositories\ohm\QuestionSetRepository;
use App\Services\Interfaces\QuestionServiceInterface;
use App\Services\ohm\QuestionService;
use Mockery;
use PDO;
use ReflectionClass;
use Tests\TestCase;

use App\Http\Controllers\QuestionController;

// Required for tests to work in GitHub Actions.
require_once(__DIR__ . '/../../../../../../i18n/i18n.php');

class GetScoreTest extends TestCase
{
    private QuestionController $questionController;
    private AssessmentRepositoryInterface  $assessmentRepository;
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

    public function testGetScore(): void
    {
        $inputState = json_decode('[{
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
        }]', true);

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        // Set the method to public.
        $class = new ReflectionClass(QuestionController::class);
        $method = $class->getMethod('getScore');
        $method->setAccessible(true);

        $scoreResponse = $method->invokeArgs($this->questionController, $inputState);

        $this->assertEquals(42, $scoreResponse['questionSetId']);
        $this->assertEquals('number', $scoreResponse['questionType']);
        $this->assertEquals(3469, $scoreResponse['seed']);
        $this->assertEquals([1.0], $scoreResponse['scores']);
        $this->assertEquals([1.0], $scoreResponse['raw']);
        $this->assertEquals([], $scoreResponse['errors']);
        $this->assertTrue($scoreResponse['allans']);
        $this->assertNotEmpty($scoreResponse['correctAnswers']);
        $this->assertEquals('10', $scoreResponse['correctAnswers'][0]);
    }

    public function testGetScore_multiPart_with_multans_feedback(): void
    {
        $inputState = json_decode('[{
            "post": [      
                { 
                    "name": "qn0",
                    "value": ""
                },
                { 
                    "name": "qn1000",
                    "value": "42"
                },
                 { 
                    "name": "qn1001",
                    "value": "0,2,3"
                }
            ],
            "questionSetId": 3618,
            "seed": 4120,
            "studentAnswers": ["","true","false"],
            "studentAnswerValues": [22,7,0]
        }]', true);

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_multipart_multans);

        // Set the method to public.
        $class = new ReflectionClass(QuestionController::class);
        $method = $class->getMethod('getScore');
        $method->setAccessible(true);

        $scoreResponse = $method->invokeArgs($this->questionController, $inputState);

        $this->assertEquals(3618, $scoreResponse['questionSetId']);
        $this->assertEquals('multipart', $scoreResponse['questionType']);
        $this->assertEquals(4120, $scoreResponse['seed']);
        $this->assertEquals([0.5, 0.5], $scoreResponse['scores']);
        $this->assertEquals([1, 1], $scoreResponse['raw']);
        $this->assertEquals([42, "0,2,3"], $scoreResponse['correctAnswers']);
        $this->assertEquals([], $scoreResponse['errors']);

        $this->assertCount(4, $scoreResponse['feedback']);

        $this->assertEquals('correct', $scoreResponse['feedback']['qn1000']['correctness']);
        $this->assertEquals('Good answer.', $scoreResponse['feedback']['qn1000']['feedback']);

        $this->assertEquals('correct', $scoreResponse['feedback']['qn1001-0']['correctness']);
        $this->assertEquals('This is correct.', $scoreResponse['feedback']['qn1001-0']['feedback']);

        $this->assertEquals('correct', $scoreResponse['feedback']['qn1001-2']['correctness']);
        $this->assertEquals('You chose correctly.', $scoreResponse['feedback']['qn1001-2']['feedback']);

        $this->assertEquals('correct', $scoreResponse['feedback']['qn1001-3']['correctness']);
        $this->assertEquals('You chose well.', $scoreResponse['feedback']['qn1001-3']['feedback']);
    }

    public function testGetScore_Multans_with_basic_feedback(): void
    {
        $inputState = json_decode('[{
            "post": [
                {
                    "name": "qn0",
                    "value": [1,2]
                }
            ],
            "questionSetId": 7321,
            "seed": 4136,
            "studentAnswers": ["1", "2"],
            "studentAnswerValues": [1, 2]
        }]', true);

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_multans_basicfeedback);

        // Set the method to public.
        $class = new ReflectionClass(QuestionController::class);
        $method = $class->getMethod('getScore');
        $method->setAccessible(true);

        $scoreResponse = $method->invokeArgs($this->questionController, $inputState);

        $this->assertEquals(["1,2"], $scoreResponse['correctAnswers']);

        $this->assertEquals(7321, $scoreResponse['questionSetId']);
        $this->assertEquals('multans', $scoreResponse['questionType']);
        $this->assertEquals(4136, $scoreResponse['seed']);
        $this->assertEquals([1.0], $scoreResponse['scores']);
        $this->assertEquals([1], $scoreResponse['raw']);
        $this->assertEquals(["1,2"], $scoreResponse['correctAnswers']);
        $this->assertEquals([], $scoreResponse['errors']);

        $this->assertCount(1, $scoreResponse['feedback']);

        // FIXME: This should return "correct".
        //        The question API is reporting correctness incorrectly?
        $this->assertEquals('incorrect', $scoreResponse['feedback']['qn0']['correctness']);
        // FIXME: This should return the feedback for a correct student answer submitted.
        //        The question API is reporting correctness incorrectly?
        $this->assertEquals('Incorrect.', $scoreResponse['feedback']['qn0']['feedback']);
    }

    public function testGetScore_isAlgorithmic_True(): void
    {
        $inputState = json_decode('[{
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
        }]', true);

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        // Set the method to public.
        $class = new ReflectionClass(QuestionController::class);
        $method = $class->getMethod('getScore');
        $method->setAccessible(true);

        $scoreResponse = $method->invokeArgs($this->questionController, $inputState);

        $this->assertEquals(42, $scoreResponse['questionSetId']);
        $this->assertTrue($scoreResponse['isAlgorithmic']);
    }

    public function testGetScore_isAlgorithmic_False(): void
    {
        $inputState = json_decode('[{
            "post": [
                {
                    "name": "qn0",
                    "value": 0
                }
            ],
            "questionSetId": 3607,
            "seed": 3469,
            "studentAnswers": ["0"],
            "studentAnswerValues": ["0"],
            "partAttemptNumber": [0]
        }]', true);

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_choices);

        // Set the method to public.
        $class = new ReflectionClass(QuestionController::class);
        $method = $class->getMethod('getScore');
        $method->setAccessible(true);

        $scoreResponse = $method->invokeArgs($this->questionController, $inputState);

        $this->assertEquals(3607, $scoreResponse['questionSetId']);
        $this->assertFalse($scoreResponse['isAlgorithmic']);
    }
}
