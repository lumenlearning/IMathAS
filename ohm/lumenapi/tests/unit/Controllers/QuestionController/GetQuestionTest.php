<?php

namespace Tests\unit\Controllers\QuestionController;

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

use App\Http\Controllers\QuestionController;

// Required for tests to work in GitHub Actions.
require_once(__DIR__ . '/../../../../../../i18n/i18n.php');

class GetQuestionTest extends TestCase
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

    public function testGetQuestion_withFeedback_singlePart(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_choices);

        $request = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 3607,
                                  "seed": 4120
                              }', true)
        );

        $response = $this->questionController->getQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(3607, $responseData['questionSetId']);
        $this->assertEquals('choices', $responseData['questionType']);
        $this->assertEquals(4120, $responseData['seed']);
        $this->assertEquals([], $responseData['errors']);

        $this->assertIsArray($responseData['feedback']);
        $this->assertCount(4, $responseData['feedback']);

        $this->assertEquals('correct', $responseData['feedback']['qn0-0']['correctness']);
        $this->assertEquals('This is correct. Way to go.', $responseData['feedback']['qn0-0']['feedback']);

        $this->assertEquals('incorrect', $responseData['feedback']['qn0-1']['correctness']);
        $this->assertEquals('Sorry, Option B is incorrect. Try again.', $responseData['feedback']['qn0-1']['feedback']);

        $this->assertEquals('incorrect', $responseData['feedback']['qn0-2']['correctness']);
        $this->assertEquals('Sorry, Option C is not the right answer. Try again.', $responseData['feedback']['qn0-2']['feedback']);

        $this->assertEquals('incorrect', $responseData['feedback']['qn0-3']['correctness']);
        $this->assertEquals('Sorry, Option D was the wrong choice. Try again.', $responseData['feedback']['qn0-3']['feedback']);
    }

    public function testGetQuestion_withFeedback_multiPart(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_multipart_choices);

        $request = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 3609,
                                  "seed": 4120
                              }', true)
        );

        $response = $this->questionController->getQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(3609, $responseData['questionSetId']);
        $this->assertEquals('multipart', $responseData['questionType']);
        $this->assertEquals(4120, $responseData['seed']);
        $this->assertEquals([], $responseData['errors']);

        $this->assertIsArray($responseData['feedback']);
        $this->assertCount(12, $responseData['feedback']);

        $this->assertEquals('correct', $responseData['feedback']['qn1000-0']['correctness']);
        $this->assertEquals('Excellent choice.', $responseData['feedback']['qn1000-0']['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback']['qn1000-1']['correctness']);
        $this->assertEquals('Nope.', $responseData['feedback']['qn1000-1']['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback']['qn1000-2']['correctness']);
        $this->assertEquals('Try again.', $responseData['feedback']['qn1000-2']['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback']['qn1000-3']['correctness']);
        $this->assertEquals('Not even close.', $responseData['feedback']['qn1000-3']['feedback']);

        $this->assertEquals('incorrect', $responseData['feedback']['qn1001-0']['correctness']);
        $this->assertEquals('Nope!', $responseData['feedback']['qn1001-0']['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback']['qn1001-1']['correctness']);
        $this->assertEquals('No.', $responseData['feedback']['qn1001-1']['feedback']);
        $this->assertEquals('correct', $responseData['feedback']['qn1001-2']['correctness']);
        $this->assertEquals('Correct.', $responseData['feedback']['qn1001-2']['feedback']);

        $this->assertEquals('correct', $responseData['feedback']['qn1002-0']['correctness']);
        $this->assertEquals('Correct. Hawaiian pizza is always valid pizza.', $responseData['feedback']['qn1002-0']['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback']['qn1002-1']['correctness']);
        $this->assertEquals('Incorrect.', $responseData['feedback']['qn1002-1']['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback']['qn1002-2']['correctness']);
        $this->assertEquals('Try again.', $responseData['feedback']['qn1002-2']['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback']['qn1002-3']['correctness']);
        $this->assertEquals('All days are acceptable pizza days.', $responseData['feedback']['qn1002-3']['feedback']);
    }

    public function testGetQuestion_withoutFeedback(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        $request = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 42,
                                  "seed": 1234
                              }', true)
        );

        $response = $this->questionController->getQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(42, $responseData['questionSetId']);
        $this->assertEquals('number', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertEquals([], $responseData['errors']);
        $this->assertNull($responseData['feedback']);
    }

    public function testGetQuestion_byUniqueId(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getByUniqueId')
            ->withArgs(['1491933600157156'])
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        $request = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "ohmUniqueId": "1acsve483f4",
                                  "seed": 1234
                              }', true)
        );

        $response = $this->questionController->getQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(42, $responseData['questionSetId']);
        $this->assertEquals('1acsve483f4', $responseData['ohmUniqueId']);
        $this->assertEquals('number', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertEquals([], $responseData['errors']);
    }

    public function testGetQuestion_byUniqueIdAndQuestionSetId(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getByUniqueId')
            ->withArgs(['1491933600157156'])
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        // When both a questionSetId and ohmUniqueId are requested, only
        // the ohmUniqueId should be used.
        $request = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 424242,
                                  "ohmUniqueId": "1acsve483f4",
                                  "seed": 1234
                              }', true)
        );

        $response = $this->questionController->getQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(42, $responseData['questionSetId']);
        $this->assertEquals('1acsve483f4', $responseData['ohmUniqueId']);
        $this->assertEquals('number', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertEquals([], $responseData['errors']);
    }
}
