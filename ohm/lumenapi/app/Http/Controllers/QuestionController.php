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

use Illuminate\Support\Facades\Validator;
use App\Dtos\QuestionDto;
use App\Dtos\QuestionScoreDto;
use Illuminate\Validation\ValidationException;
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
     */
    public function __construct(AssessmentRepositoryInterface $assessmentRepository,
                                QuestionSetRepositoryInterface $questionSetRepository)
    {
        parent::__construct();
        $this->assessmentRepository = $assessmentRepository;
        $this->questionSetRepository = $questionSetRepository;

        // AssessStandalone requires PDO connection. This uses Lumen's existing connection to provide PDO.
        $this->DBH = app('db')->getPdo();

        // Allows the API to act as an admin user. Initially, Skeletor will be the only client however, when
        // end users begin to use the API, some form of Skeletor to MOM user map should be used here instead.
        $GLOBALS['myrights'] = 100;
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
                'questionSetId' => 'required|int',
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
     *               property="seed",
     *               type="int",
     *               description="Seed of scored item"
     *             ),
     *             @OA\Property(
     *               property="scores",
     *               type="int",
     *               description="Contains weighted score for given question"
     *             ),
     *             @OA\Property(
     *               property="raw",
     *               type="array",
     *               @OA\Items(
     *                 type="int"
     *               ),
     *               description="Contains raw score for given question"
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
     *               property="answerWeights",
     *               type="array",
     *               @OA\Items(
     *                 type="int"
     *               ),
     *               description="(optional) Returns score weights for multi-part questions"
     *             ),
     *          ),
     *          @OA\Examples(
     *            example=200,
     *            summary="Multi-part question",
     *            value={"scores": {0.5,0.5},"raw":{1,1},"errors": {} ,"allans":false}
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
                'questionSetId' => 'required|int',
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
     * @param int $questionSetId
     * @param array $state
     * @return AssessStandalone
     */
    protected function getAssessStandalone(int $questionSetId, array $state): AssessStandalone
    {
        // Use questionSetId from incoming request to retrieve list of questions from db
        $questionSet = $this->questionSetRepository->getById($questionSetId);
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
        $scoreDto = new QuestionScoreDto($inputState);

        $assessStandalone = $this->getAssessStandalone($scoreDto->getQuestionSetId(), $scoreDto->getState());
        $score = $assessStandalone->scoreQuestion($this->questionId, $scoreDto->getPartsToScore());

        return $scoreDto->getScoreResponse($score, $this->questionType, $assessStandalone->getState());
    }

    /**
     * @param array $inputState
     * @return array
     */
    public function getQuestionDisplay(array $inputState): array
    {
        $questionDto = new QuestionDto($inputState);
        $assessStandalone = $this->getAssessStandalone($questionDto->getQuestionSetId(), $questionDto->getState());

        $question = $assessStandalone->displayQuestion($this->questionId, $questionDto->getOptions());

        return $questionDto->getQuestionResponse($question, $this->questionType['questionType'],
            $assessStandalone->getState());
    }
}
