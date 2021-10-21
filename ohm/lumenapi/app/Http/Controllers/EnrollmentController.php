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

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

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
        // TODO: Implement me!
        return response()->json(['message' => 'Not implemented yet!']);
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
}
