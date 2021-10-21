<?php
/**
 * @OA\Info(title="API", version="1.0")
 */

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Services\Interfaces\EnrollmentServiceInterface;

class EnrollmentController extends ApiBaseController
{
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

    public function getEnrollment(Request $request, int $id): JsonResponse
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
}
