<?php

namespace Tests\Unit\Controllers;

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

// Required for tests to work in GitHub Actions.
require_once(__DIR__ . '/../../../../../i18n/i18n.php');

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

    private array $imasQuestionSet_dbRow_multipart = [
        'id' => '3609',
        'uniqueid' => '1665009644605959',
        'adddate' => '1665009644',
        'lastmoddate' => '1665018423',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userrights' => '0',
        'license' => '1',
        'description' => 'Multipart, multiple inputs per part',
        'qtype' => 'multipart',
        'control' => 'loadlibrary("ohm_macros")

$anstypes = "choices,choices,choices,number"

// Part 1 - Choose the best color
$choices[0] = array("Purple", "Red", "Blue", "Green")
$answer[0] = "0"
$colorfeedbacks[0] = "Excellent choice."
$colorfeedbacks[1] = "Nope."
$colorfeedbacks[2] = "Try again."
$colorfeedbacks[3] = "Not even close."

// Part 2 - Cup storage. Are you an upper or a downer?
$choices[1] = array("Up", "Down", "Sideways")
$answer[1] = "2"
$numbersfeedbacks[0] = "Nope!"
$numbersfeedbacks[1] = "No."
$numbersfeedbacks[2] = "Correct."

// Part 3 - Is Hawaiian a valid pizza option?
$choices[2] = array("Yes", "No", "Sometimes", "Only on Tuesdays")
$answer[2] = "0"
$pizzafeedbacks = array(
  "Correct. Hawaiian pizza is always valid pizza.",
  "Incorrect.",
  "Try again.",
  "All days are acceptable pizza days."
)

// Part 4 - What is the answer to life, the universe, and everything?
$answer[3] = "42"

$feedback = mergearrays(
  ohm_getfeedbacktxt($stuanswer[$thisq], $colorfeedbacks, $answer[0], 0),
  ohm_getfeedbacktxt($stuanswer[$thisq], $numbersfeedbacks, $answer[1], 1),
  ohm_getfeedbacktxt($stuanswer[$thisq], $pizzafeedbacks, $answer[2], 2),
  ohm_getfeedbackbasic("Correct!", "Not correct.", $thisq, 3)
)',
        'qcontrol' => '',
        'qtext' => 'Choose the best color:
$answerbox[0]

Cup storage. Are you an upper or a downer?
$answerbox[1]

Is Hawaiian a valid pizza option?
$answerbox[2]

What is the answer to life, the universe, and everything?
$answerbox[3]
',
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
        'varscore' => '0'
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

    public function testGetQuestion_withFeedback_singlePart(): void
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
            ->andReturn($this->imasQuestionSet_dbRow_multipart);

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
        $this->assertNotEmpty($responseData['feedback']);

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
