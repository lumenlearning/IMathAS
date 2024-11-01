<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\QuestionImportServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionImportController extends ApiBaseController
{
    private QuestionImportServiceInterface $questionImportService;

    /**
     * Controller constructor.
     * @param QuestionImportServiceInterface $questionImportService
     */
    public function __construct(QuestionImportServiceInterface $questionImportService)
    {
        parent::__construct();
        $this->questionImportService = $questionImportService;
    }

    /**
     * @OA\Post(
     *     path="/questions/mga_imports",
     *     summary="Create questions from MGA question data.",
     *     tags={"Question MGA import"},
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
     *                     property="owner_id",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="questions",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/mga_question")
     *                 ),
     *                 example={
     *                     "owner_id": 42,
     *                     "questions": {
     *                         {
     *                             "source_id": "3d056d98-e0b2-4939-af4c-fe5396bdae98",
     *                             "source_type": "mga_file",
     *                             "type": "multiple_choice",
     *                             "is_summative": false,
     *                             "description": "Anthony wants to make sure he has a good credit score.  Which of the following actions will have the largest impact on his score?",
     *                             "text": "Anthony wants to make sure he has a good credit score.  Which of the following actions will have the largest impact on his score?",
     *                             "choices": {
     *                                 "Making on time payments",
     *                                 "Waiting as long as possible to get a credit card",
     *                                 "Applying for premier credit cards",
     *                                 "Establishing a few different types of credit between loans and credit cards"
     *                             },
     *                             "correct_answer": 0,
     *                             "feedback": null
     *                         },
     *                         {
     *                             "source_id": "b7568530-f70b-4810-bf5b-af608951da38",
     *                             "source_type": "mga_file",
     *                             "type": "multiple_choice",
     *                             "is_summative": false,
     *                             "description": "Maya is concerned about her credit problems and is worried about the debt she’s accumulated throughout college. Which of the following strategies is the most advisable for Maya?",
     *                             "text": "Maya is concerned about her credit problems and is worried about the debt she’s accumulated throughout college. Which of the following strategies is the most advisable for Maya?",
     *                             "choices": {
     *                                 "Defer payments to a later date",
     *                                 "Look for a reputable credit counselor",
     *                                 "Immediately consolidate her loans",
     *                                 "Automatically apply for bankruptcy"
     *                             },
     *                             "correct_answer": 1,
     *                             "feedback": {
     *                                 "type": "per_answer",
     *                                 "feedbacks": {
     *                                     "Incorrect. While some student loans allow deferment, Maya must have an accepted reason to defer payments. Credit card debt is much harder and costs more to defer.",
     *                                     "Correct! Credit counselors offer debt management plans in which they work with credit card and loan companies to arrange a deal and ask you for monthly deposits so that they can help you pay off your debts.",
     *                                     "Incorrect. While this may give her more time to pay off her debt, consolidating to one payment can cost more and accrue a higher interest rate.",
     *                                     "Incorrect. Bankruptcy is an official status obtained through court procedures meaning that you are unable to pay off your debts. Bankruptcy damages your credit score, and the fees for filing paperwork and hiring an attorney are costly, so it should be used only as a last resort."
     *                                 }
     *                             }
     *                         }
     *                     }
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Created",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             @OA\Property(
     *               property="question_mappings",
     *               type="array",
     *               description="Mappings of MGA file GUIDs to newly created OHM question IDs.",
     *               @OA\Items(ref="#/components/schemas/ohm_question_id_mapping")
     *             )
     *           ),
     *           @OA\Examples(
     *             example=201,
     *             summary="",
     *             value={
     *               "question_mapping": {
     *                 {
     *                     "source_id": "3d056d98-e0b2-4939-af4c-fe5396bdae98",
     *                     "status": "created",
     *                     "questionset_id": 5449,
     *                     "errors": {}
     *                 },
     *                 {
     *                     "source_id": "b7568530-f70b-4810-bf5b-af608951da38",
     *                     "status": "created",
     *                     "questionset_id": 5450,
     *                     "errors": {}
     *                 }
     *               }
     *             }
     *           )
     *         )
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
    public function importMgaQuestions(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'owner_id' => 'required|int',
                'question_import_mode' => 'required|string|in:quiz,practice',
                'questions' => 'required|array',
                'questions.*.source_id' => 'required|string',
                'questions.*.type' => 'required|string',
                'questions.*.description' => 'required|string',
                'questions.*.text' => 'required|string',
                'questions.*.choices' => 'required_if:questions.*.type,==,multiple_choice|nullable|array',
                'questions.*.correct_answer' => 'required',
                // "sometimes" really means "optional"
                'questions.*.feedback' => 'sometimes|nullable|array',
            ]);

            $questionImportMode = $request['question_import_mode'];
            $mgaQuestionData = $request['questions'];
            $ownerId = $request['owner_id'];

            $questionIdMapping = $this->questionImportService
                ->createMultipleQuestions($questionImportMode, $mgaQuestionData, $ownerId);

            $responseData = ["question_mappings" => $questionIdMapping];
            return response()->json($responseData, JsonResponse::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /*
     * Swagger refs
     */

    /**
     * @OA\Schema(
     *   schema="mga_question",
     *   @OA\Property(
     *     property="source_id",
     *     type="string",
     *     description="The question's MGA file GUID."
     *   ),
     *   @OA\Property(
     *     property="type",
     *     type="string",
     *     enum={"per_answer"},
     *     description="The question's type."
     *   ),
     *   @OA\Property(
     *     property="description",
     *     type="string",
     *     description="The question's description."
     *   ),
     *   @OA\Property(
     *     property="text",
     *     type="string",
     *     description="The question's text. (also known as question prompt)"
     *   ),
     *   @OA\Property(
     *     property="correct_answer",
     *     type="mixed",
     *     description="The question's correct answer"
     *   ),
     *   @OA\Property(
     *     property="choices",
     *     type="array",
     *     description="All available choices for a multiple choice question. Omit for other question types.",
     *     @OA\Items(
     *       type="string"
     *     )
     *   ),
     *   @OA\Property(
     *     property="feedback",
     *     type="object",
     *     description="Feedback for the question. Nullable.",
     *     @OA\Property(
     *       property="type",
     *       type="string",
     *       enum={"per_answer"},
     *       description="The type of feedback being provided."
     *     ),
     *     @OA\Property(
     *       property="feedbacks",
     *       type="array",
     *       description="Per-answer feedback. This array index should match the choices index.",
     *       @OA\Items(
     *         type="string"
     *       )
     *     )
     *   ),
     * )
     */

    /**
     * @OA\Schema(
     *   schema="ohm_question_id_mapping",
     *   @OA\Property(
     *     property="source_id",
     *     type="string",
     *     description="The question's MGA file GUID."
     *   ),
     *   @OA\Property(
     *     property="status",
     *     type="string",
     *     enum={"created", "error"},
     *     description="The status of OHM question creation."
     *   ),
     *   @OA\Property(
     *     property="questionset_id",
     *     type="int32",
     *     description="The question's ID in OHM."
     *   ),
     *   @OA\Property(
     *     property="errors",
     *     type="array",
     *     description="Errors encountered during question creation, if any.",
     *     @OA\Items(
     *       type="string"
     *     )
     *   ),
     * )
     */
}