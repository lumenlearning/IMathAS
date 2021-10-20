<?php
/**
 * @OA\Info(title="API", version="1.0")
 */

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Dtos\EnrollmentDto;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class EnrollmentController extends ApiBaseController
{
    /**
     * @var EnrollmentRepositoryInterface
     */
    private $enrollmentRepository;

    /**
     * Controller constructor.
     * @param EnrollmentRepositoryInterface $enrollmentRepository
     */
    public function __construct(EnrollmentRepositoryInterface $enrollmentRepository)
    {
        parent::__construct();
        $this->enrollmentRepository = $enrollmentRepository;
    }

    public function getAllEnrollments(Request $request): JsonResponse
    {
        // TODO: Implement me!
        return response()->json(['message' => 'Not implemented yet!']);
    }

    public function getEnrollment(Request $request, int $id): JsonResponse
    {
        try {
            $enrollment = $this->enrollmentRepository->getById($id);

            if (empty($enrollment)) {
                return response()->json($enrollment);
            } else {
                $enrollmentDto = new EnrollmentDto($enrollment);
                return response()->json($enrollmentDto->toArray());
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }
}
