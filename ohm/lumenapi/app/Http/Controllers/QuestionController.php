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
use PDO;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use function FastRoute\TestFixtures\empty_options_cached;

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
     * @OA\Post(
     *     path="/question",
     *     summary="Retrieves question HTML for given question set id and seed.",
     *     tags={"Display"},
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
     *                     property="seeds",
     *                     type="array",
     *                     @OA\Items(
     *                        type="int"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="qsid",
     *                     type="array",
     *                     @OA\Items(
     *                        type="int"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="rawscores",
     *                     type="array",
     *                     @OA\Items(
     *                        type="int"
     *                     )
     *                 ),
     *                 example={
     *                   "seeds": {
     *                     "0": 8076
     *                   },
     *                   "qsid": {
     *                     "0": 16208
     *                   },
     *                   "rawscores": {
     *                     "0": "[]"
     *                   },
     *                   "submitall": false
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
     *          @OA\Examples(example=200, summary="", value={"tbd":"tbd"}),
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
                'qsid' => 'required|array',
                'seeds' => 'required|array',
                'rawscores' => 'present|array'
            ]);
            $inputState = $request->all();

            $question = $this->getQuestionDisplay($inputState);
            $questionWithIds = array_merge(
                [
                    'questionSetId' => $inputState['qsid'][$this->questionId],
                    'seed' => $inputState['seeds'][$this->questionId]
                ],
                $question);

            return response()->json($questionWithIds);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/questions",
     *     summary="Retrieves question HTML a given list of question set ids and seeds.",
     *     tags={"Display"},
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
            $inputState = $request->all();

            $questions = [];
            foreach($inputState as $question) {
                $questionDisplay = $this->getQuestionDisplay($question);
                // So that questionSetId and seed show up at the top
                $questionWithIds = array_merge(
                    [
                        'questionSetId' => $question['qsid'][$this->questionId],
                        'seed' => $question['seeds'][$this->questionId]
                    ],
                    $questionDisplay);
                array_push($questions, $questionWithIds);
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
     *     tags={"Scoring"},
     *     @OA\Response(
     *         response="200",
     *         description="OK",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             @OA\Property(
     *               property="scores",
     *               type="int",
     *               description="Contains weighted score for given question"
     *             ),
     *             @OA\Property(
     *               property="raw",
     *               type="Contains raw score for given question"
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
     *               type="bool"
     *             )
     *          ),
     *          @OA\Examples(
     *            example=200,
     *            summary="Multi-part question",
     *            value={"scores":"[0.5,0.5]","raw":"[1,1]","errors":"[]","allans":false}
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
                'qsid' => 'required|array',
                'seeds' => 'required|array'
            ]);
            $validator->after(function($validator) {
                // $scoreQuestionParams->->setGivenAnswer($_POST['qn'.$qn]) around line 279 of AssessStandalone
                // requires a post parameter with the name 'qn' followed by some number. To make it easy, always
                // pass in a 'qn0' param with any value for multi-part and matching type questions even though
                // it will not be used.
                $requiredPostElement = 'qn0';
                $post = $validator->getData()['post'];
                if (!in_array($requiredPostElement, array_column($post,'name'), true)) {
                    $validator->errors()->add('post.*.name', 'Must contain one qn0 element with any value');
                }
            });
            if ($validator->fails()) {
                $this->throwValidationException($request, $validator);
            }
            $inputState = $request->all();
            $score = $this->getScore($inputState);

            $scoreWithIds = array_merge(
                [
                    'questionSetId' => $inputState['qsid'][$this->questionId],
                    'seed' => $inputState['seeds'][$this->questionId]
                ],
                $score);

            return response()->json($scoreWithIds);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/questions/score",
     *     summary="Scores student reponse to a given list of questions.",
     *     tags={"Scoring"},
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

            $inputState = $request->all();

            foreach($inputState as $question) {
                $score = $this->getScore($question);
                // So that questionSetId and seed show up at the top
                $scoreWithIds = array_merge(
                    [
                        'questionSetId' => $question['qsid'][$this->questionId],
                        'seed' => $question['seeds'][$this->questionId]
                    ],
                    $score);
                array_push($scores, $scoreWithIds);
            }

            return response()->json($scores);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * Retrieves question set list and initializes AssessStandalone
     * @param array $inputState
     * @return AssessStandalone
     */
    protected function getAssessStandalone(array $inputState): AssessStandalone
    {
        // Use questionSetId from incoming request to retrieve list of questions from db
        $questionSetId = $inputState['qsid'][$this->questionId];
        $questionSet = $this->questionSetRepository->getById($questionSetId);
        if (!$questionSet) throw new BadRequestException('Unable to locate question set');

        $assessStandalone = new AssessStandalone($this->DBH);
        $assessStandalone->setQuestionData($questionSet['id'], $questionSet);
        $assessStandalone->setState($inputState);
        return $assessStandalone;
    }

    /**
     * Scores a single question using AssessStandalone
     * @param array $inputState
     * @return array
     */
    protected function getScore(array $inputState): array
    {
        // Score is calculated against form POST parameters. Since there will be no form post,
        // answers are passed in request body then removed so as not to interfere with normal
        // scoring operation.
        $postParams = $inputState['post'];
        foreach ($postParams as $postParam) {
            $_POST[$postParam['name']] = $postParam['value'];
        }
        unset($inputState['post']);

        $assessStandalone = $this->getAssessStandalone($inputState);
        return $assessStandalone->scoreQuestion($this->questionId, [0 => true]);
    }

    /**
     * @param array $inputState
     * @return array
     */
    public function getQuestionDisplay(array $inputState): array
    {
        $assessStandalone = $this->getAssessStandalone($inputState);

        $overrides = [];
        if (!empty($inputState['options'])) {
            $overrides = $inputState['options'];
        }

        return $assessStandalone->displayQuestion($this->questionId, $overrides);
    }
}
