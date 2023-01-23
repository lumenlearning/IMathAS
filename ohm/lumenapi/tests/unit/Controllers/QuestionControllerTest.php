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

    private array $imasQuestionSet_dbRow_multipart_choices = [
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
  ohm_getfeedbackbasic($stuanswer[$thisq], "Correct!", "Not correct.", $answer[3], 3)
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

    private array $imasQuestionSet_dbRow_multipart_multans = [
        'id' => '3618',
        'uniqueid' => '1668057407498824',
        'adddate' => '1668057407',
        'lastmoddate' => '1668066745',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userights' => '0',
        'license' => '1',
        'description' => 'Multipart: multans + number',
        'qtype' => 'multipart',
        'control' => 'loadlibrary("ohm_macros")

$anstypes = "number,multans"

$choices = [
  "Correct",
  "Not correct",
  "Correct",
  "Not correct",
  "Correct"
]

// Both $answer and $answers are declared here, for testing.
$answer[0] = 42
$answers[1] = "0,2,4"

$multansFeedbacks = array(
  "You chose well.",
  "Nope.",
  "You chose correctly.",
  "lol, no.",
  "This is correct."
)

$feedback = mergearrays(
  ohm_getfeedbackbasic($stuanswers[$thisq], "Good answer.", "Wrong answer.", $answer[0], 0),
  ohm_getfeedbacktxtmultans($stuanswers[$thisq], $multansFeedbacks, $answers[1], 1)
)
',
        'qcontrol' => '',
        'qtext' => 'What is the answer to life, the universe, and everything?
$answerbox[0]

Choose the correct answers:
$answerbox[1]
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

    private array $imasQuestionSet_dbRow_with_ohm1_macro = [
        'id' => '3607',
        'uniqueid' => '1661894316883503',
        'adddate' => '1661894316',
        'lastmoddate' => '1661990728',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userights' => '2',
        'license' => '1',
        'description' => 'Multiple Choice Test 10 with Feedback - Staging QID 638',
        'qtype' => 'choices',
        'control' => '$questions[0] = "Something like that."
$feedbacktxt[0] = "This is correct. Way to go."
$questions[1] = "No, sorry. I\'ll add an oxford comma next time."
$feedbacktxt[1] = "Sorry, this is incorrect. Try again."
$questions[2] = "I\'m not the planning committee."
$feedbacktxt[2] = "Sorry, not the right answer. Try again."
$questions[3] = "You were supposed to make the plan!"
$feedbacktxt[3] = "Sorry, that was the wrong choice. Try again."
$displayformat = "vert"
$noshuffle = "all"
$answer = 0

$feedback = getfeedbacktxt($stuanswers[$thisq], $feedbacktxt, $answer)',
        'qcontrol' => '',
        'qtext' => '<p>Was that the plan?</p>
<p>$answerbox</p>
<p>$feedback</p>',
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

    private array $imasQuestionSet_dbRow_multans_basicfeedback = [
        'id' => '3623',
        'uniqueid' => '1670955967303564',
        'adddate' => '1670955967',
        'lastmoddate' => '1671133493',
        'ownerid' => '1',
        'author' => '<h1>AdminLastName</h1>,<h1>AdminFirstName</h1>',
        'userights' => '0',
        'license' => '1',
        'description' => '1.3 L1 - QID 646 in staging',
        'qtype' => 'multans',
        'control' => 'loadlibrary("ohm_macros")

$a = "0 - How tall is the tallest mountain in the United States?"
$b = "1 - Do standing heart rates tend to be higher than sitting heartrates?"
$c = "2 - What is the sum of all the whole numbers between 0 and 10?"
$d = "3 - What is your favorite subject in school?"
$e = "4 - What proportion of college students live on campus?"
$f = "5 - How many members does your household have (including pets)?"

$questions = array($a,$b,$c,$d,$e,$f)
$answers = "1,4"

$hints[1] = "Remember that all statistical investigative questions anticipate variability and could lead to data collection and analysis."

$feedback = ohm_getfeedbackbasic($thisq, "Excellent! You are able to distinguish the statstical investigative questions from the rest.", "A statistical investigative question would require data collection and analysis. Does the question account for variability?  Questions with a single mathematical answer are not considered statistical investigative questions.", $answers)

// As of AST-275, using ohm_getfeedbackbasic or feedbacktxt in a multans
// type question requires shuffling to be disabled.
$noshuffle = "all"

$hinttext[0] = "Remember that all statistical investigative questions anticipate variability and could lead to data collection and analysis."

$hinttext_a=forminlinebutton("Hint",$hinttext[0])
',
        'qcontrol' => '',
        'qtext' => 'Which of the following are statistical investigative questions? <em>There may be more than one correct answer.</em>
<p>$hinttext_a
  $answerbox
  $feedback
  $hintloc
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
     * getQuestionDisplay
     */

    public function testGetQuestionDisplay_ohm1_macro(): void
    {
        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn($this->imasQuestionSet_dbRow_with_ohm1_macro);

        $responseData = $this->questionController->getQuestionDisplay([
            'questionSetId' => 3607,
            'seed' => 3469,
        ]);

        // OHM1 macros return a string of feedback with HTML, which is
        // not usable by the Question API.
        $this->assertContains(
            'Warning: Feedback may be available but is suppressed due to the usage of OHMv1 macros!',
            $responseData['errors']
        );
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
        $this->assertEquals('10', $responseData['correctAnswers'][0]);
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
        $this->assertEquals('10', $scoreResponse['correctAnswers'][0]);
    }

    public function testGetScore_multiPart_with_multans_feedback(): void
    {
        $inputState = json_decode('{
            "request": {
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
            }
        }', true);

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn($this->imasQuestionSet_dbRow_multipart_multans);

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
        $inputState = json_decode('{
            "request": {
                "post": [
                    {
                        "name": "qn0",
                        "value": [1,4]
                    }
                ],
                "questionSetId": 3623,
                "seed": 4136,
                "studentAnswers": ["1", "4"],
                "studentAnswerValues": [1, 4]
            }
        }', true);

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn($this->imasQuestionSet_dbRow_multans_basicfeedback);

        // Set the method to public.
        $class = new ReflectionClass(QuestionController::class);
        $method = $class->getMethod('getScore');
        $method->setAccessible(true);

        $scoreResponse = $method->invokeArgs($this->questionController, $inputState);

        $this->assertEquals(3623, $scoreResponse['questionSetId']);
        $this->assertEquals('multans', $scoreResponse['questionType']);
        $this->assertEquals(4136, $scoreResponse['seed']);
        $this->assertEquals([1.0], $scoreResponse['scores']);
        $this->assertEquals([1], $scoreResponse['raw']);
        $this->assertEquals(["1,4"], $scoreResponse['correctAnswers']);
        $this->assertEquals([], $scoreResponse['errors']);

        $this->assertCount(1, $scoreResponse['feedback']);

        $this->assertEquals('correct', $scoreResponse['feedback']['qn0']['correctness']);
        $this->assertEquals('Excellent! You are able to distinguish the statstical investigative questions from the rest.',
            $scoreResponse['feedback']['qn0']['feedback']);
    }

    /**
     * @group noshuffle_all
     */
    public function testGetScore_global_shuffling_disabled(): void
    {
        $inputState = json_decode('{
            "request": {
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
                        "value": "0,2,4"
                    }
                ],
                "questionSetId": 3618,
                "seed": 4120,
                "studentAnswers": ["","true","false"],
                "studentAnswerValues": [22,7,0]
            }
        }', true);

        $this->assertEquals('all', getenv('NOSHUFFLE_ANSWERS'));

        // Setup mocks.
        $this->questionSetRepository
            ->shouldReceive('getById')
            ->andReturn($this->imasQuestionSet_dbRow_multipart_multans);

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
        $this->assertEquals([42, "0,2,4"], $scoreResponse['correctAnswers']);
        $this->assertEquals([], $scoreResponse['errors']);

        $this->assertCount(4, $scoreResponse['feedback']);

        $this->assertEquals('correct', $scoreResponse['feedback']['qn1000']['correctness']);
        $this->assertEquals('Good answer.', $scoreResponse['feedback']['qn1000']['feedback']);

        $this->assertEquals('correct', $scoreResponse['feedback']['qn1001-0']['correctness']);
        $this->assertEquals('You chose well.', $scoreResponse['feedback']['qn1001-0']['feedback']);

        $this->assertEquals('correct', $scoreResponse['feedback']['qn1001-2']['correctness']);
        $this->assertEquals('You chose correctly.', $scoreResponse['feedback']['qn1001-2']['feedback']);

        $this->assertEquals('correct', $scoreResponse['feedback']['qn1001-4']['correctness']);
        $this->assertEquals('This is correct.', $scoreResponse['feedback']['qn1001-4']['feedback']);
    }

    /**
     * @group noshuffle_all
     *
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
            ->andReturn($this->imasQuestionSet_dbRow_with_ohm1_macro);

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
        $this->assertContains('Warning: OHM1 feedback is an empty string.', $responseData['errors']);
    }

    /*
     * getQuestion
     */

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
            ->andReturn($this->imasQuestionSet_dbRow_multipart_choices);

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

        $this->assertCount(11, $responseData['feedback']);

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
        $this->assertNull($responseData['feedback']);
    }

    public function testReIndexMultipartMultansAnswers(): void
    {
        $inputPostVars = [
            'qn0' => '',
            'qn1000' =>
                [
                    0 => 1,
                    1 => 2,
                ],
            'qn1001' => 0,
            'qn1002' => 1,
        ];

        $expectedOutputPostVars = [
            'qn0' => '',
            'qn1000' =>
                array (
                    1 => 1,
                    2 => 2,
                ),
            'qn1001' => 0,
            'qn1002' => 1,
        ];

        // The many different ways $anstypes could be defined in question code.
        $questionControls = [
            "\$anstypes = \"multans,choices,choices\") // [rest of question code]...",
            "\$anstypes=\"multans,choices,choices\") // [rest of question code]...",
            "\$anstypes = array(\"multans\",\"choices\",\"choices\") // [rest of question code]...",
            "\$anstypes=array(\"multans\",\"choices\",\"choices\") // [rest of question code]...",
            "\$anstypes = [\"multans\",\"choices\",\"choices\"] // [rest of question code]...",
            "\$anstypes=[\"multans\",\"choices\",\"choices\"] // [rest of question code]..."
        ];

        foreach ($questionControls as $questionControl) {

            $outputPostVars = $this->reIndexMultipartMultansAnswers($inputPostVars, $questionControl, 42);

            $this->assertEquals($outputPostVars, $expectedOutputPostVars);
        }

    }
}
