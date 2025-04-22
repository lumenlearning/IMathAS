<?php
/**
 * @OA\Info(title="API", version="1.0")
 */

namespace App\Http\Controllers;

use AssessStandalone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Repositories\Interfaces\AssessmentRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;
use App\Services\Interfaces\QuestionServiceInterface;

use Illuminate\Support\Facades\Validator;
use App\Dtos\QuestionBaseDto;
use App\Dtos\QuestionDto;
use App\Dtos\QuestionScoreDto;
use Illuminate\Validation\ValidationException;
use IMathAS\assess2\questions\models\Question;
use PDO;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class QuestionController extends ApiBaseController
{
    /**
     * @var AssessmentRepositoryInterface
     */
    private $assessmentRepository;
    /**
     * @var QuestionSetRepositoryInterface
     */
    private $questionSetRepository;

    private QuestionServiceInterface $questionService;

    /**
     * @var PDO DBH
     */
    private $DBH;

    /**
     * @var int Index of incoming question. Should always be 0.
     */
    private $questionId = 0;

    /**
     * @var array Stores question type and control from question set data
     */
    private $questionType;

    /**
     * Controller constructor.
     * @param AssessmentRepositoryInterface $assessmentRepository
     * @param QuestionSetRepositoryInterface $questionSetRepository
     * @param QuestionServiceInterface $questionService
     */
    public function __construct(AssessmentRepositoryInterface $assessmentRepository,
                                QuestionSetRepositoryInterface $questionSetRepository,
                                QuestionServiceInterface $questionService)
    {
        parent::__construct();
        $this->assessmentRepository = $assessmentRepository;
        $this->questionSetRepository = $questionSetRepository;
        $this->questionService = $questionService;

        // AssessStandalone requires PDO connection. This uses Lumen's existing connection to provide PDO.
        $this->DBH = app('db')->getPdo();

        // Allows the API to act as an admin user. Initially, Skeletor will be the only client however, when
        // end users begin to use the API, some form of Skeletor to MOM user map should be used here instead.
        $GLOBALS['myrights'] = 100;

        // Sets preferences manually, similarly to how it is done with embedded questions,
        // so that graphs can be displayed through Catra as if in OHM.
        $_SESSION = array();
        $_SESSION['graphdisp'] = 1;
        $_SESSION['mathdisp'] = 3;
    }

    /**
     * Set the PDO object to use for DB interaction. Used by unit tests.
     *
     * @param PDO $dbh
     */
    public function setPdo(PDO $dbh) {
        $this->dbh = $dbh;
    }

    /**
     * @OA\Post(
     *     path="/question",
     *     summary="Retrieves question HTML for given question set id and seed.",
     *     tags={"Question Display"},
     *     @OA\Parameter(
     *       name="Authorization",
     *       in="header",
     *       required=true,
     *       example="Bearer <token>"
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="questionSetId",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="ohmUniqueId",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="seed",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="rawScores",
     *                     type="array",
     *                     @OA\Items(
     *                        type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="partialAttemptNumber",
     *                     type="array",
     *                     @OA\Items(
     *                        type="int"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="options",
     *                     type="array",
     *                     @OA\Items(
     *                        type="int"
     *                     )
     *                 ),
     *                 example={
     *                   "questionSetId": 16208,
     *                   "ohmUniqueId": "1fk4km8c030",
     *                   "seed": 8076,
     *                   "rawScores": {},
     *                   "partialAttemptNumber": {},
     *                   "options": {
     *                     "maxtries": 1,
     *                     "showansafter": "",
     *                     "hidescoremarkers": false,
     *                     "showallparts": "",
     *                     "showans": false,
     *                     "showhints": 3,
     *                     "includeans": false
     *                   }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             @OA\Property(
     *               property="questionSetId",
     *               type="int",
     *               description="Question Set Id of scored item"
     *             ),
     *             @OA\Property(
     *               property="ohmUniqueId",
     *               type="string",
     *               description="Unique Question Id of scored item"
     *             ),
     *             @OA\Property(
     *               property="seed",
     *               type="int",
     *               description="Seed of scored item"
     *             ),
     *             @OA\Property(
     *               property="html",
     *               type="string",
     *               description="Contains html for question rendering"
     *             ),
     *             @OA\Property(
     *               property="jsparams",
     *               type="object"
     *             ),
     *             @OA\Property(
     *               property="errors",
     *               type="array",
     *               @OA\Items(
     *                 type="string"
     *               )
     *             )
     *          ),
     *          @OA\Examples(example=200, summary="", value={
     *              "questionSetId": 123,
     *              "ohmUniqueId": "1fk4km8c030",
     *              "seed": 999,
     *              "html": "<div></div>",
     *              "jsparams": {
     *                  "0": {
     *                     "tip": "Enter math expression",
     *                     "longtip": "",
     *                     "preview": 2,
     *                     "calcformat": "",
     *                     "qtype": "calculated",
     *                  },
     *                  "ans": {},
     *                  "maxtries": {},
     *                  "partatt": {},
     *                  "disabled": {},
     *                  "helps": {}
     *              },
     *              "errors": {}
     *            }
     *          ),
     *       )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getQuestion(Request $request): JsonResponse
    {
        try {
            $this->validate($request,[
                'questionSetId' => 'required_without:ohmUniqueId|int',
                'ohmUniqueId' => 'required_without:questionSetId|string',
                'seed' => 'required|int'
            ]);

            $question = $this->getQuestionDisplay($request->all());

            return response()->json($question);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/questions",
     *     summary="Retrieves question HTML a given list of question set ids and seeds.",
     *     tags={"Question Display"},
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *         @OA\MediaType(
     *           mediaType="application/json"
     *       )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getAllQuestions(Request $request): JsonResponse
    {
        try {
            $questions = [];
            foreach($request->all() as $questionInput) {
                $question = $this->getQuestionDisplay($questionInput);
                array_push($questions, $question);
            }

            return response()->json($questions);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/questions/answers",
     *     summary="Retrieves questions and their correct answers, given a list of question set ids and seeds.",
     *     tags={"Question Display"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="questions",
     *                     type="array",
     *                     @OA\Items(
     *                        type="object",
     *                        @OA\Property(
     *                            property="questionSetId",
     *                            type="int"
     *                        ),
     *                        @OA\Property(
     *                            property="seed",
     *                            type="int"
     *                        ),
     *                     )
     *                 ),
     *                 example={
     *                   "questions": {
     *                     {
     *                       "questionSetId": 16208,
     *                       "seed": 1234,
     *                     },
     *                     {
     *                       "questionSetId": 7361,
     *                       "seed": 642,
     *                     }
     *                   }
     *                 }
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *         @OA\MediaType(
     *           mediaType="application/json"
     *       )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getQuestionsWithAnswers(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'questions' => 'required|array',
                'questions.*.questionSetId' => 'required|int',
                'questions.*.seed' => 'required|int',
            ]);
            try {
                $validator->validate();
            } catch (ValidationException $e) {
                $response = $this->BadRequest([
                    $validator->errors()
                ]);
                return $response;
            }

            $requestPayload = $request->all();
            $requestedQuestions = $requestPayload['questions'];

            $questionsWithAnswers = $this->questionService->getQuestionsWithAnswers($requestedQuestions);

            return response()->json($questionsWithAnswers);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/question/score",
     *     summary="Scores student reponse to a given question.",
     *     tags={"Question Scoring"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="questionSetId",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="ohmUniqueId",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="seed",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="post",
     *                     type="array",
     *                     @OA\Items(
     *                        type="object"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="studentAnswers",
     *                     type="array",
     *                     @OA\Items(
     *                        type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="studentAnswerValues",
     *                     type="array",
     *                     @OA\Items(
     *                        type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="partAttemptNumber",
     *                     description="(optional)",
     *                     type="array",
     *                     @OA\Items(
     *                        type="int"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="partsToScore",
     *                     description="(optional)",
     *                     type="array",
     *                     @OA\Items(
     *                        type="int"
     *                     )
     *                 ),
     *                 example={
     *                   "questionSetId": 16208,
     *                   "ohmUniqueId": "1fk4km8c030",
     *                   "seed": 8076,
     *                   "post": {
     *                      {
     *                        "name": "qn0",
     *                        "value": "1"
     *                      }
     *                   },
     *                   "studentAnswers": { "1" },
     *                   "studentAnswerValues": { 1 },
     *                   "partsToScore": { 1 },
     *                   "partAttemptNumber": { 1 }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             @OA\Property(
     *               property="questionSetId",
     *               type="int",
     *               description="Question Set Id of scored item"
     *             ),
     *             @OA\Property(
     *               property="ohmUniqueId",
     *               type="string",
     *               description="Unique Question Id of scored item"
     *             ),
     *             @OA\Property(
     *                 property="questionType",
     *                 type="string",
     *                 description="The question type (number, multans, etc)"
     *             ),
     *             @OA\Property(
     *               property="seed",
     *               type="int",
     *               description="Seed of scored item"
     *             ),
     *             @OA\Property(
     *               property="scores",
     *               type="array",
     *               @OA\Items(
     *                 type="int"
     *               ),
     *               description="Contains weighted scores for given question"
     *             ),
     *             @OA\Property(
     *               property="raw",
     *               type="array",
     *               @OA\Items(
     *                 type="int"
     *               ),
     *               description="Contains raw scores for given question"
     *             ),
     *             @OA\Property(
     *               property="errors",
     *               type="array",
     *               @OA\Items(
     *                 type="string"
     *               )
     *             ),
     *             @OA\Property(
     *               property="allans",
     *               type="bool",
     *               description="True if all answer parts of question are provided otherwise false"
     *             ),
     *             @OA\Property(
     *               property="feedback",
     *               type="object",
     *               description="Contains all correct answers"
     *             ),
     *             @OA\Property(
     *               property="partTypes",
     *               type="array",
     *               @OA\Items(
     *                 type="string"
     *               ),
     *               description="For multi-part questions, contains the question part types (choices, number, etc)"
     *             ),
     *             @OA\Property(
     *               property="correctAnswers",
     *               type="array",
     *               @OA\Items(
     *                 type="string|int|float"
     *               ),
     *               description="Contains all correct answers"
     *             ),
     *          ),
     *          @OA\Examples(
     *            example=200,
     *            summary="Single part - number",
     *            value={
     *                "questionSetId": 3618,
     *                "ohmUniqueId": "1fk4km8c030",
     *                "questionType": "number",
     *                "seed": 482936,
     *                "scores": {
     *                    1,
     *                },
     *                "raw": {
     *                    1,
     *                },
     *                "errors": {},
     *                "allans": true,
     *                "feedback": {
     *                    "qn0": {
     *                        "correctness": "correct",
     *                        "feedback": "Correct."
     *                    }
     *                },
     *                "correctAnswers": {
     *                    42,
     *                }
     *            }
     *          ),
     *          @OA\Examples(
     *            example=201,
     *            summary="Single part - multiple choice",
     *            value={
     *                "questionSetId": 3618,
     *                "ohmUniqueId": "1fk4km8c030",
     *                "questionType": "choices",
     *                "seed": 482936,
     *                "scores": {
     *                    1,
     *                },
     *                "raw": {
     *                    1,
     *                },
     *                "errors": {},
     *                "allans": true,
     *                "feedback": {
     *                    "qn0-4": {
     *                        "correctness": "correct",
     *                        "feedback": "Correct."
     *                    }
     *                },
     *                "correctAnswers": {
     *                    "4",
     *                }
     *            }
     *          ),
     *          @OA\Examples(
     *            example=202,
     *            summary="Single part - multiple answer",
     *            value={
     *                "questionSetId": 3618,
     *                "ohmUniqueId": "1fk4km8c030",
     *                "questionType": "multans",
     *                "seed": 482936,
     *                "scores": {
     *                    1,
     *                },
     *                "raw": {
     *                    1,
     *                },
     *                "errors": {},
     *                "allans": true,
     *                "feedback": {
     *                    "qn0-1": {
     *                        "correctness": "correct",
     *                        "feedback": "Correct."
     *                    },
     *                    "qn0-3": {
     *                        "correctness": "correct",
     *                        "feedback": "Correct."
     *                    }
     *                },
     *                "correctAnswers": {
     *                    "1,3",
     *                }
     *            }
     *          ),
     *          @OA\Examples(
     *            example=203,
     *            summary="Single part - matching",
     *            value={
     *                "questionSetId": 3618,
     *                "ohmUniqueId": "1fk4km8c030",
     *                "questionType": "matching",
     *                "seed": 482936,
     *                "scores": {
     *                    1,
     *                },
     *                "raw": {
     *                    1,
     *                },
     *                "errors": {},
     *                "allans": true,
     *                "feedback": {
     *                    "qn0": {
     *                        "correctness": "correct",
     *                        "feedback": "Correct."
     *                    }
     *                },
     *                "correctAnswers": {
     *                    {1,2,3,4,5,6}
     *                }
     *            }
     *          ),
     *          @OA\Examples(
     *            example=204,
     *            summary="Single part - string",
     *            value={
     *                "questionSetId": 3618,
     *                "ohmUniqueId": "1fk4km8c030",
     *                "questionType": "string",
     *                "seed": 482936,
     *                "scores": {
     *                    1,
     *                },
     *                "raw": {
     *                    1,
     *                },
     *                "errors": {},
     *                "allans": true,
     *                "feedback": {
     *                    "qn0": {
     *                        "correctness": "correct",
     *                        "feedback": "Correct."
     *                    }
     *                },
     *                "correctAnswers": {
     *                    "Cats and meows"
     *                }
     *            }
     *          ),
     *          @OA\Examples(
     *            example=205,
     *            summary="Single part - draw",
     *            value={
     *                "questionSetId": 3618,
     *                "ohmUniqueId": "1fk4km8c030",
     *                "questionType": "draw",
     *                "seed": 482936,
     *                "scores": {
     *                    1,
     *                },
     *                "raw": {
     *                    1,
     *                },
     *                "errors": {},
     *                "allans": true,
     *                "feedback": {
     *                    "qn0": {
     *                        "correctness": "correct",
     *                        "feedback": "Correct."
     *                    }
     *                },
     *                "correctAnswers": {
     *                    "7.65,3.583",
     *                    "7.8,3.526",
     *                    "7.9,3.552",
     *                    "8.05,3.758",
     *                    "8.05,3.313",
     *                    "8.15,3.318",
     *                    "8.3,2.961",
     *                    "8.5,2.913",
     *                    "8.65,3.253",
     *                    "8.75,3.468"
     *                }
     *            }
     *          ),
     *          @OA\Examples(
     *            example=206,
     *            summary="Single part - essay",
     *            value={
     *                "questionSetId": 3618,
     *                "ohmUniqueId": "1fk4km8c030",
     *                "questionType": "essay",
     *                "seed": 482936,
     *                "scores": {
     *                    1,
     *                },
     *                "raw": {
     *                    1,
     *                },
     *                "errors": {},
     *                "allans": true,
     *                "feedback": {
     *                    "qn0": {
     *                        "correctness": "correct",
     *                        "feedback": "Sample answer: Cats are meowsome."
     *                    }
     *                },
     *                "correctAnswers": {
     *                    null
     *                }
     *            }
     *          ),
     *          @OA\Examples(
     *            example=207,
     *            summary="Multi-part question",
     *            value={
     *                "questionSetId": 3618,
     *                "ohmUniqueId": "1fk4km8c030",
     *                "questionType": "multipart",
     *                "seed": 482936,
     *                "scores": {
     *                    0.3333,
     *                    0.3333,
     *                    0.3333
     *                },
     *                "raw": {
     *                    1,
     *                    1,
     *                    1
     *                },
     *                "errors": {},
     *                "allans": true,
     *                "feedback": {
     *                    "qn1000": {
     *                        "correctness": "correct",
     *                        "feedback": "Correct."
     *                    },
     *                    "qn1001-0": {
     *                        "correctness": "correct",
     *                        "feedback": "Yes, apples are fruit."
     *                    },
     *                    "qn1001-1": {
     *                        "correctness": "correct",
     *                        "feedback": "Yes, bananas are fruit."
     *                    },
     *                    "qn1001-2": {
     *                        "correctness": "correct",
     *                        "feedback": "Yes, strawberries are fruit."
     *                    },
     *                    "qn1001-4": {
     *                        "correctness": "correct",
     *                        "feedback": "Yes, tomatoes are fruit."
     *                    },
     *                    "qn1002-0": {
     *                        "correctness": "correct",
     *                        "feedback": "Correct. Cats are the answer."
     *                    }
     *                },
     *                "partTypes": {
     *                    "number",
     *                    "multans",
     *                    "choices"
     *                },
     *                "correctAnswers": {
     *                    42,
     *                    "0,1,2,4",
     *                    "0"
     *                }
     *            }
     *         ),
     *       )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function scoreQuestion(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'post' => 'required|array|min:1',
                'post.*.name' => 'required|distinct|string',
                'post.*.value' => 'present',
                'questionSetId' => 'required_without:ohmUniqueId|int',
                'ohmUniqueId' => 'required_without:questionSetId|string',
                'seed' => 'required|int',
                'studentAnswers' => 'required|array',
                'studentAnswerValues' => 'required|array',
            ]);
            try {
                $validator->validate();
            } catch (ValidationException $e) {
                return response()->json($validator->errors());
            }
            $validator->after(function($validator) {
                // $scoreQuestionParams->->setGivenAnswer($_POST['qn'.$qn]) around line 279 of AssessStandalone
                // requires a post parameter with the name 'qn' followed by some number. To make it easy, always
                // pass in a 'qn0' param with any value for multi-part and matching type questions even though
                // it will not be used.
                $requiredPostElement = 'qn0';
                $post = $validator->getData()['post'];
                if (!in_array($requiredPostElement, array_column($post,'name'), true)) {
                    $validator->errors()->add('post.*.name', 'Must contain one qn0 element with any value.');
                }
            });
            if ($validator->fails()) {
                $this->throwValidationException($request, $validator);
            }

            $score = $this->getScore($request->all());

            return response()->json($score);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/questions/score",
     *     summary="Scores student reponse to a given list of questions.",
     *     tags={"Question Scoring"},
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *         @OA\MediaType(
     *           mediaType="application/json"
     *       )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function scoreAllQuestions(Request $request): JsonResponse
    {
        try {
            $scores = [];
            foreach($request->all() as $question) {
                $score = $this->getScore($question);
                array_push($scores, $score);
            }

            return response()->json($scores);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * Retrieves question set list and initializes AssessStandalone
     * @param QuestionBaseDto $questionBaseDto
     * @param array $state
     * @return AssessStandalone
     */
    protected function getAssessStandalone(QuestionBaseDto $questionBaseDto, array $state): AssessStandalone
    {
        // Use question IDs from the incoming request to retrieve the a question from the DB.
        // If a uniqueId is available, this will be used instead of the questionSetId.
        $questionSet = null;
        if ($questionBaseDto->getUniqueId()) {
            $questionSet = $this->questionSetRepository->getByUniqueId($questionBaseDto->getUniqueId());
            $questionBaseDto->setQuestionSetId($questionSet['id']);
            $state['qsid'][0] = $questionSet['id']; // QuestionSetRepository only returns one question.
        } else if ($questionBaseDto->getQuestionSetId()) {
            $questionSet = $this->questionSetRepository->getById($questionBaseDto->getQuestionSetId());
            $questionBaseDto->setUniqueId($questionSet['uniqueid']);
        }
        if (!$questionSet) throw new BadRequestException('Unable to locate question set');

        // Store question type and control for use later in scoring
        $this->questionType = ['questionType' => $questionSet['qtype'],
                               'questionControl' => $questionSet['control']];

        $assessStandalone = new AssessStandalone($this->DBH);
        $assessStandalone->setQuestionData($questionSet['id'], $questionSet);
        $assessStandalone->setState($state);
        return $assessStandalone;
    }

    /**
     * Scores a single question using AssessStandalone
     * @param array $inputState
     * @return array
     */
    protected function getScore(array $inputState): array
    {
        // Convert the provided OHM unique ID to an IMathAS unique ID.
        if (!empty($inputState['ohmUniqueId'])) {
            $inputState['uniqueId'] = $this->uniqueId32ToBase10($inputState['ohmUniqueId']);
        }

        $scoreDto = new QuestionScoreDto($inputState);

        $assessStandalone = $this->getAssessStandalone($scoreDto, $scoreDto->getState());

        // For "multans" questions, if a student selects answers [0, 2, 4], the array
        // of answers passed to the scoring engine should be: [0, null, 2, null, 4]
        if ("multans" == $this->questionType['questionType']) {
            $_POST['qn0'] = $this->reIndexMultansAnswers($_POST['qn0']);
        }
        // We need to check multipart questions for "multans" parts and apply the
        // same re-indexing of answers.
        if ("multipart" == $this->questionType['questionType']) {
            $_POST = $this->reIndexMultipartMultansAnswers($scoreDto->getPostParams(),
                $this->questionType['questionControl'], $scoreDto->getQuestionSetId());
        }

        $score = $assessStandalone->scoreQuestion($this->questionId, $scoreDto->getPartsToScore());

        // Get the question's unique ID.
        $questionDbData = $assessStandalone->getQuestionData($scoreDto->getQuestionSetId());
        $score['uniqueid'] = $questionDbData['uniqueid'];
        $score['isAlgorithmic'] = 1 == $questionDbData['isrand'];

        // Get question feedback.
        $questionFeedback = $score['feedback'] ?? null;

        return $scoreDto->getScoreResponse($score, $this->questionType, $assessStandalone->getState(), $questionFeedback);
    }

    /**
     * Get a single question for display using AssessStandalone.
     *
     * @param array $inputState
     * @return array
     */
    public function getQuestionDisplay(array $inputState): array
    {
        // Convert the provided unique ID to an IMathAS unique ID.
        if (!empty($inputState['ohmUniqueId'])) {
            $inputState['uniqueId'] = $this->uniqueId32ToBase10($inputState['ohmUniqueId']);
        }

        $questionDto = new QuestionDto($inputState);
        $assessStandalone = $this->getAssessStandalone($questionDto, $questionDto->getState());

        // Get question HTML and "JS params".
        $questionDisplayData = $assessStandalone->displayQuestion($this->questionId, $questionDto->getOptions());

        // Get the question's unique ID.
        $questionDbData = $assessStandalone->getQuestionData($questionDto->getQuestionSetId());
        $questionDisplayData['uniqueid'] = $questionDbData['uniqueid'];
        $questionDisplayData['isAlgorithmic'] = 1 == $questionDbData['isrand'];

        // Get question feedback.
        $question = $assessStandalone->getQuestion();
        $questionFeedback = $this->getQuestionFeedback($question);

        if (
            isset($questionDisplayData['errors'])
            && 'array' == gettype($questionDisplayData['errors'])
            && !empty($question->getErrors())
        ) {
            $questionDisplayData['errors'] = array_merge($question->getErrors(), $questionDisplayData['errors']);
        }

        return $questionDto->getQuestionResponse($questionDisplayData, $this->questionType['questionType'],
            $assessStandalone->getState(), $questionFeedback);
    }

    /**
     * Check for and return question feedback.
     *
     * If no question feedback is found, null is returned.
     *
     * @param Question $question A reference to an instance of Question.
     * @return array|null An associative array of question feedback.
     */
    private function getQuestionFeedback(Question &$question): ?array
    {
        if (empty($question->getExtraData())) return null;
        if (empty($question->getExtraData()['lumenlearning'])) return null;
        if (empty($question->getExtraData()['lumenlearning']['feedback'])) return null;

        $feedback = $question->getExtraData()['lumenlearning']['feedback'];

        /*
         * If feedback is a string, then an original OHM (from IMathAS) macro
         * was used. Those macros return strings containing HTML and all feedback
         * in a string, to be rendered by the assess2 client.
         *
         * Catra expects feedback as an array and without HTML, so we return null.
         */
        if ('array' != gettype($feedback)) {
            $question->addErrors([
                'Warning: Feedback may be available but is suppressed due to the usage of OHMv1 macros!'
            ]);
            return null;
        }

        return $feedback;
    }

    /**
     * Given an array of integers, return a new array with:
     * - The same integers but indexed into positions indicated by their value.
     * - No values for any gaps in sequence.
     *
     * Example:
     *     Input: [0, 2, 3, 5, 7]
     *     Output: [0, null, 2, 3, null, 5, null, 7]
     *
     * @param int[] $answers An array of integers.
     * @return array An array of integers with nulls.
     */
    private function reIndexMultansAnswers(array $answers): array
    {
        $reIndexedAnswers = [];
        foreach ($answers as $i) {
            $reIndexedAnswers[$i] = $i;
        }

        return $reIndexedAnswers;
    }

    /**
     * For multipart questions, apply reIndexMultansAnswers() to "multans" parts.
     *
     * This methods requires the entire $_POST variable as input and will
     * return a replacement. It will not modify the original value of $_POST.
     *
     * @param array $postVars The entire value of $_POST, after
     *                        QuestionScoreDto->setPostParams() has been called.
     * @param string $questionControl The question control. (question code)
     * @param int $questionsetId The question ID from imas_questionset.
     * @return array A new array of part answers, suitable for replacing $_POST.
     */
    private function reIndexMultipartMultansAnswers(array $postVars,
                                                    string $questionControl,
                                                    int $questionsetId): array
    {
        /*
         * Each part of a multipart question is a question.
         * Example: A two-part multipart question contains two questions.
         *
         * Each part type is defined in a variable named $anstypes, which
         * lives in the question control. (question control contains PHP)
         *
         * $anstypes is declared when the question code (PHP) is eval'd, when
         * displaying or scoring a question. We'll extract it with a regex
         * instead.
         *
         * The format of $anstypes is a comma-separated string of question types or an array
         * of question type strings.
         */
        $anstypes = null;
        // Expected regex pattern for strings: "$anstypes = "choices,number,multans"" - with 0 or more spaces next to =.
        if (preg_match('/\$anstypes\s?=\s?\"(.*)\"/', $questionControl, $matches)) {
            $anstypes = explode(',', $matches[1]);
        // Expected regex patterns for arrays: "$anstypes = ["multans","choices","number"]"
        // OR "$anstypes = array("multans","file","essay")" - both with 0 or more spaces next to =.
        } elseif (preg_match ('/\$anstypes\s?=\s?\[(.*)\]/', $questionControl, $matches) || preg_match ('/\$anstypes\s?=\s?array\((.*)\)/', $questionControl, $matches)) {
            $anstypes = explode(',', str_replace("\"", "", $matches[1]));
        } else {
            // If we can't find $anstypes, we can't check for "multans" question types.
            // We shouldn't get this far as multipart questions require $anstypes to be
            // defined to function properly.
            error_log('WARNING: Unable to find $anstypes in question control for multipart question!'
                . ' This may result in incorrect scoring if this question contains a "multans" type part!'
                . ' imas_questionset ID: ' . $questionsetId);
            return $postVars;
        }

        $postVarsWithMultansReindexed = [];
        $arrayPosition = 0;
        foreach ($postVars as $partIndex => $answer) {
            if ('qn0' == $partIndex) {
                $postVarsWithMultansReindexed[$partIndex] = $answer;
                continue; // "qn0" has no matching $anstype.
            } elseif ('multans' == $anstypes[$arrayPosition]) { // Each part's question type is found in $anstypes.
                if (!is_array($answer)) {
                    $answer = explode(',', $answer);
                }
                $postVarsWithMultansReindexed[$partIndex] = $this->reIndexMultansAnswers($answer);
            } else {
                // Copy non-multans answers as-is.
                $postVarsWithMultansReindexed[$partIndex] = $answer;
            }
            $arrayPosition++;
        }

        return $postVarsWithMultansReindexed;
    }

    /**
     * Convert a base 32 unique ID to base 10.
     *
     * @param string $uniqueId The unique ID.
     * @return string The unique ID in base 10.
     *                Unchanged if already in base 10.
     */
    private function uniqueId32ToBase10(string $uniqueId): string {
        $uniqueId10 = trim($uniqueId);

        // Unique IDs in the DB (in base 10) are always at least 16 digits.
        // Example: 1679612045929581
        $containsLetters = $this->containsLetters($uniqueId10);
        if ($containsLetters) {
            $uniqueId10 = base_convert($uniqueId, 32, 10);
        }

        return $uniqueId10;
    }

    /**
     * Determine if a string contains numbers.
     *
     * @param string $subject The string to check.
     * @return bool True if the string contains letters. False if not.
     */
    private function containsLetters(string $subject): bool {
        for ($i = 0; $i < strlen($subject); $i++) {
            $char = $subject[$i];
            if (!is_numeric($char)) return true;
        }

        return false;
    }
}
