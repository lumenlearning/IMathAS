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

    private array $imasQuestionSet_dbRow_number = [
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

    private array $imasQuestionSet_dbRow_choices = [
        'id' => '3607',
        'uniqueid' => '1661894316883503',
        'adddate' => '1661894316',
        'lastmoddate' => '1661990728',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userights' => '2',
        'license' => '1',
        'description' => 'Multiple Choice Test 1 with Feedback',
        'qtype' => 'choices',
        'control' => 'loadlibrary("ohm_macros")
 
 $questions[0] = "Sportsball"
 $feedbacktxt[0] = "This is correct. Way to go."
 $questions[1] = "Blernsball"
 $feedbacktxt[1] = "Sorry, Option B is incorrect. Try again."
 $questions[2] = "Calvin Ball"
 $feedbacktxt[2] = "Sorry, Option C is not the right answer. Try again."
 $questions[3] = "Quidditch"
 $feedbacktxt[3] = "Sorry, Option D was the wrong choice. Try again."
 $displayformat = "vert"
 $noshuffle = "all"
 $answer = 0
 
 $feedback = ohm_getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer)',
        'qcontrol' => '',
        'qtext' => '<p>What is your favorite sport?</p>\r\n<p>$answerbox</p>\r\n<p>$feedback</p>\r\n',
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
        'varscore' => '0',
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
            ->andReturn($this->imasQuestionSet_dbRow_number);

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
            ->andReturn($this->imasQuestionSet_dbRow_number);

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

    public function testGetQuestion_withFeedback(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn($this->imasQuestionSet_dbRow_choices);

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
        $this->assertNotEmpty($responseData['feedback']);
        $this->assertEquals('correct', $responseData['feedback'][0]['correctness']);
        $this->assertEquals('This is correct. Way to go.', $responseData['feedback'][0]['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback'][1]['correctness']);
        $this->assertEquals('Sorry, Option B is incorrect. Try again.', $responseData['feedback'][1]['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback'][2]['correctness']);
        $this->assertEquals('Sorry, Option C is not the right answer. Try again.', $responseData['feedback'][2]['feedback']);
        $this->assertEquals('incorrect', $responseData['feedback'][3]['correctness']);
        $this->assertEquals('Sorry, Option D was the wrong choice. Try again.', $responseData['feedback'][3]['feedback']);
    }

    public function testGetQuestion_withoutFeedback(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn($this->imasQuestionSet_dbRow_number);

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
        $this->assertEmpty($responseData['feedback']);
    }
}
