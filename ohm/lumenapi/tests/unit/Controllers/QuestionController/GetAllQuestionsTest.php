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

class GetAllQuestionsTest extends TestCase
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

    /*
     * getAllQuestions
     */

    public function testGetAllQuestions_byUniqueId(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getByUniqueId')
            ->andReturn(DbFixtures::imas_QuestionSet_dbRow_number);

        $request = Request::create('/api/v1/questions', 'POST',
            json_decode('[
                {
                    "uniqueId": "1491933600157156",
                    "seed": 3469
                },
                {
                    "uniqueId": "1661894316883503",
                    "seed": 5106
                }
            ]', true)
        );

        $response = $this->questionController->getAllQuestions($request);
        $responseData = $response->getData(true);

        $question1 = $responseData[0];
        $this->assertEquals('1491933600157156', $question1['uniqueId']);
        $this->assertEquals('number', $question1['questionType']);
        $this->assertEquals(3469, $question1['seed']);
        $this->assertEquals([], $question1['errors']);

        $question2 = $responseData[1];
        $this->assertEquals('1661894316883503', $question2['uniqueId']);
        $this->assertEquals('number', $question2['questionType']);
        $this->assertEquals(5106, $question2['seed']);
        $this->assertEquals([], $question2['errors']);
    }
}
