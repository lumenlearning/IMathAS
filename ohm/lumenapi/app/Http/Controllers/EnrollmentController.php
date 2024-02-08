<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Services\Interfaces\EnrollmentServiceInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class EnrollmentController extends ApiBaseController
{
    // These fields may be written to by API clients.
    const WRITE_ALLOWED_FIELDS = [
        'has_valid_access_code',
        'is_opted_out_of_assessments'
    ];

    /**
     * @var EnrollmentServiceInterface
     */
    private $enrollmentService;

    /**
     * Controller constructor.
     * @param EnrollmentServiceInterface $enrollmentService
     */
    public function __construct(EnrollmentServiceInterface $enrollmentService)
    {
        parent::__construct();
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * @OA\Get(
     *     path="/enrollments",
     *     tags={"Enrollment"},
     *     summary="Get all enrollments.",
     *     description="Returns all enrollment.",
     *     operationId="getAllEnrollments",
     *     @OA\Parameter(
     *         name="user_id_filter",
     *         in="query",
     *         description="Filter by user ID.",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="course_id_filter",
     *         in="query",
     *         description="Filter by course ID.",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="int32"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(ref="#/components/schemas/enrollment_collection"),
     *           @OA\Examples(example=200, summary="", value={
     *              {
     *                  "id": 2,
     *                  "user_id": 8,
     *                  "course_id": 1,
     *                  "lti_course_id": 0,
     *                  "last_access": 1491866005,
     *                  "has_valid_access_code": false,
     *                  "is_opted_out_of_assessments": false,
     *                  "created_at": null
     *              },
     *              {
     *                  "id": 4,
     *                  "user_id": 21,
     *                  "course_id": 0,
     *                  "lti_course_id": 0,
     *                  "last_access": 0,
     *                  "has_valid_access_code": false,
     *                  "is_opted_out_of_assessments": false,
     *                  "created_at": null
     *              }
     *            }
     *           ),
     *         ),
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
    public function getAllEnrollments(Request $request): JsonResponse
    {
        try {
            list($pageNum, $pageSize) = $this->getPaginationArgs($request);
            $filters = $this->getSearchFilters($request);

            $enrollments = $this->enrollmentService->getAll($pageNum, $pageSize, $filters);
            return response()->json($enrollments);
        } catch (Exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/enrollments/{id}",
     *     tags={"Enrollment"},
     *     summary="Get an enrollment by ID.",
     *     description="Returns an enrollment.",
     *     operationId="getEnrollmentById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the enrollment to return.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(ref="#/components/schemas/enrollment"),
     *           @OA\Examples(example=200, summary="", value={
     *              "id": 2,
     *              "user_id": 8,
     *              "course_id": 1,
     *              "lti_course_id": 0,
     *              "last_access": 1491866005,
     *              "has_valid_access_code": false,
     *              "is_opted_out_of_assessments": false,
     *              "created_at": null
     *             }
     *           ),
     *         ),
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
    public function getEnrollmentById(Request $request, int $id): JsonResponse
    {
        try {
            $enrollment = $this->enrollmentService->getById($id);
            if (empty($enrollment)) {
                return response()->json(null)->setStatusCode(404);
            } else {
                return response()->json($enrollment);
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    /**
     * @OA\Put(
     *     path="/enrollments/{id}",
     *     tags={"Enrollment"},
     *     summary="Update an enrollment by ID.",
     *     description="Updates an enrollment.",
     *     operationId="updateEnrollmentById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the enrollment to update.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *             @OA\Property(
     *               property="has_valid_access_code",
     *               type="boolean",
     *               description="Does the user have a valid access code?"
     *             ),
     *             @OA\Property(
     *               property="is_opted_out_of_assessments",
     *               type="boolean",
     *               description="Is the user opted out of assessments?"
     *             ),
     *          ),
     *          example={
     *              "has_valid_access_code": false,
     *              "is_opted_out_of_assessments": false,
     *            }
     *          ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(ref="#/components/schemas/enrollment"),
     *           @OA\Examples(example=200, summary="", value={
     *              "id": 2,
     *              "user_id": 8,
     *              "course_id": 1,
     *              "lti_course_id": 0,
     *              "last_access": 1491866005,
     *              "has_valid_access_code": false,
     *              "is_opted_out_of_assessments": false,
     *              "created_at": null
     *             }
     *           ),
     *         ),
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
    public function updateEnrollmentById(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'has_valid_access_code' => 'required|boolean',
            'is_opted_out_of_assessments' => 'required|boolean',
        ]);
        try {
            $validator->validate();
        } catch (ValidationException $e) {
            return response()->json(['errors' => $validator->errors()])
                ->setStatusCode(400);
        }

        try {
            $input = $request->only(self::WRITE_ALLOWED_FIELDS);
            $enrollment = $this->enrollmentService->updateById($id, $input);
            return response()->json($enrollment);
        } catch (RelationNotFoundException $e) {
            return response()->json(null)->setStatusCode(404);
        } catch (Exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    private function getSearchFilters(Request $request): array
    {
        // These filter names are provided by Lumenistration's Administrate library.
        $filters = [];
        if (!empty($request->get('user_id_filter'))) {
            $filters['user_id_filter'] = $request->get('user_id_filter');
        }
        if (!empty($request->get('course_id_filter'))) {
            $filters['course_id_filter'] = $request->get('course_id_filter');
        }
        return $filters;
    }

    /*
     * Swagger refs
     */

    /**
     * @OA\Schema(
     *   schema="enrollment",
     *   @OA\Property(
     *     property="id",
     *     type="int32",
     *     description="Enrollment ID."
     *   ),
     *   @OA\Property(
     *     property="user_id",
     *     type="int32",
     *     description="The user's ID."
     *   ),
     *   @OA\Property(
     *     property="course_id",
     *     type="int32",
     *     description="The course ID."
     *   ),
     *   @OA\Property(
     *     property="lti_course_id",
     *     type="int32",
     *     description="The LTI course ID."
     *   ),
     *   @OA\Property(
     *     property="last_access",
     *     type="int32",
     *     description="The user's last access time."
     *   ),
     *   @OA\Property(
     *     property="has_valid_access_code",
     *     type="boolean",
     *     description="Does the user have a valid access code?"
     *   ),
     *   @OA\Property(
     *     property="is_opted_out_of_assessments",
     *     type="int32",
     *     description="Is the user opted out of assessments?"
     *   ),
     *   @OA\Property(
     *     property="created_at",
     *     type="int32",
     *     description="The creation timestamp for the enrollment."
     *   ),
     * )
     */

    /**
     * @OA\Schema(
     *   schema="enrollment_collection",
     *   @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/enrollment")
     *   ),
     * )
     */
}
