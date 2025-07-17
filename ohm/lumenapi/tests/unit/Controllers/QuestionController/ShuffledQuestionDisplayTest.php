<?php

namespace Tests\unit\Controllers\QuestionController;

// Required for tests to work in GitHub Actions.
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

require_once(__DIR__ . '/../../../../../../i18n/i18n.php');

class ShuffledQuestionDisplayTest extends TestCase
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

    public function testGetQuestion_choices_shufflingIsDisabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['meows'];

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_choices);

        /*
         * First question request.
         */

        $request = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 3607,
                                  "seed": 1234
                              }', true)
        );

        $response = $this->questionController->getQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(3607, $responseData['questionSetId']);
        $this->assertEquals('choices', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertNotEmpty($responseData['html']);

        /*
         * Second question request.
         */

        $secondRequest = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 3607,
                                  "seed": 4321
                              }', true)
        );
        $secondResponse = $this->questionController->getQuestion($secondRequest);
        $secondResponseData = $secondResponse->getData(true);

        $this->assertEquals(3607, $secondResponseData['questionSetId']);
        $this->assertEquals('choices', $secondResponseData['questionType']);
        $this->assertEquals(4321, $secondResponseData['seed']);
        $this->assertNotEmpty($secondResponseData['html']);

        // With shuffling disabled, the HTML content should match.
        $this->assertEquals($responseData['html'], $secondResponseData['html']);
    }

    public function testGetQuestion_choices_shufflingIsEnabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['choices'];

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_choices);

        /*
         * First question request.
         */

        $request = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 3607,
                                  "seed": 1234
                              }', true)
        );

        $response = $this->questionController->getQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(3607, $responseData['questionSetId']);
        $this->assertEquals('choices', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertNotEmpty($responseData['html']);

        /*
         * Second question request.
         */

        $secondRequest = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 3607,
                                  "seed": 4321
                              }', true)
        );
        $secondResponse = $this->questionController->getQuestion($secondRequest);
        $secondResponseData = $secondResponse->getData(true);

        $this->assertEquals(3607, $secondResponseData['questionSetId']);
        $this->assertEquals('choices', $secondResponseData['questionType']);
        $this->assertEquals(4321, $secondResponseData['seed']);
        $this->assertNotEmpty($secondResponseData['html']);

        // With shuffling enabled, the HTML content should differ.
        $this->assertNotEquals($responseData['html'], $secondResponseData['html']);
        // Note: Feedback returned by QuestionController->getQuestion is not currently shuffled.
    }

    /*
     * Question type: multans
     */

    public function testGetQuestion_multans_shufflingIsDisabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['meows'];

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_multans_basicfeedback);

        /*
         * First question request.
         */

        $request = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 7321,
                                  "seed": 1234
                              }', true)
        );

        $response = $this->questionController->getQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(7321, $responseData['questionSetId']);
        $this->assertEquals('multans', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertNotEmpty($responseData['html']);

        /*
         * Second question request.
         */

        $secondRequest = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 7321,
                                  "seed": 4321
                              }', true)
        );
        $secondResponse = $this->questionController->getQuestion($secondRequest);
        $secondResponseData = $secondResponse->getData(true);

        $this->assertEquals(7321, $secondResponseData['questionSetId']);
        $this->assertEquals('multans', $secondResponseData['questionType']);
        $this->assertEquals(4321, $secondResponseData['seed']);
        $this->assertNotEmpty($secondResponseData['html']);

        // With shuffling disabled, the HTML content should match.
        $this->assertEquals($responseData['html'], $secondResponseData['html']);
    }

    public function testGetQuestion_multans_shufflingIsEnabled(): void
    {
        $GLOBALS['ONLY_SHUFFLE_QUESTION_TYPES'] = ['multans'];

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_multans_basicfeedback);

        /*
         * First question request.
         */

        $request = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 7321,
                                  "seed": 1234
                              }', true)
        );

        $response = $this->questionController->getQuestion($request);
        $responseData = $response->getData(true);

        $this->assertEquals(7321, $responseData['questionSetId']);
        $this->assertEquals('multans', $responseData['questionType']);
        $this->assertEquals(1234, $responseData['seed']);
        $this->assertNotEmpty($responseData['html']);

        /*
         * Second question request.
         */

        $secondRequest = Request::create('/api/v1/question', 'POST',
            json_decode('{
                                  "questionSetId": 7321,
                                  "seed": 4321
                              }', true)
        );
        $secondResponse = $this->questionController->getQuestion($secondRequest);
        $secondResponseData = $secondResponse->getData(true);

        $this->assertEquals(7321, $secondResponseData['questionSetId']);
        $this->assertEquals('multans', $secondResponseData['questionType']);
        $this->assertEquals(4321, $secondResponseData['seed']);
        $this->assertNotEmpty($secondResponseData['html']);

        // With shuffling enabled, the HTML content should differ.
        $this->assertNotEquals($responseData['html'], $secondResponseData['html']);
        // Note: Feedback returned by QuestionController->getQuestion is not currently shuffled.
    }

    /*
     * Question type: matching
     *
     * TODO: Implement answer and feedback shuffling for matching type questions.
     */
}