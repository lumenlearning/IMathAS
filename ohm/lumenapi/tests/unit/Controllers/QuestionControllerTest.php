<?php

namespace Tests\Unit\Controllers;

use App\Http\Middleware\JwtMiddleware;
use App\Repositories\Interfaces\AssessmentRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Repositories\ohm\AssessmentRepository;
use App\Repositories\ohm\QuestionSetRepository;
use Illuminate\Http\Request;
use Mockery;
use PDO;
use ReflectionClass;
use Tests\TestCase;

use App\Http\Controllers\QuestionController;

class QuestionControllerTest extends TestCase
{
    private QuestionController $questionController;
    private AssessmentRepositoryInterface  $assessmentRepository;
    private QuestionSetRepositoryInterface $questionSetRepository;

    private PDO $pdo;

    private array $imasQuestionSet_dbRow = [
        'id' => '42',
        'uniqueid' => '1491933600157156',
        'adddate' => '1491933600',
        'lastmoddate' => '1491933931',
        'ownerid' => '1',
        'author' => 'Mad Hatter',
        'userights' => '0',
        'license' => '1',
        'description' => '🙃',
        'qtype' => 'number',
        'control' => '$a = rand(1,10);' . "\r\n" . '$answer = $a;',
        'qcontrol' => '',
        'qtext' => 'Why is a raven like a writing desk?',
        'answer' => '',
        'solution' => '',
        'extref' => '',
        'hasimg' => '0',
        'deleted' => '0',
        'avgtime' => '0',
        'ancestors' => '',
        'ancestorauthors' => '',
        'otherattribution' => '',
        'importuid' => '',
        'replaceby' => '0',
        'broken' => '0',
        'solutionopts' => '6',
        'sourceinstall' => '',
        'meantimen' => '0',
        'meantime' => '0',
        'vartime' => '0',
        'meanscoren' => '0',
        'meanscore' => '0',
        'varscore' => '1111',
    ];

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
     * scoreQuestion
     */

    public function testScoreQuestion(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn($this->imasQuestionSet_dbRow);

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
        $this->assertEquals('10', $responseData['correctAnswers']['answer']);
        $this->assertNull($responseData['correctAnswers']['answers']);
    }

    /*
     * getScore
     */

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
            ->andReturn($this->imasQuestionSet_dbRow);

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
        $this->assertEquals('10', $scoreResponse['correctAnswers']['answer']);
        $this->assertNull($scoreResponse['correctAnswers']['answers']);
    }
}
