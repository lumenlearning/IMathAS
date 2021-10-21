<?php

namespace App\Services\ohm;

use App\Dtos\EnrollmentDto;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Services\Interfaces\EnrollmentServiceInterface;
use Illuminate\Validation\ValidationException;

class EnrollmentService extends BaseService implements EnrollmentServiceInterface
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
        $this->enrollmentRepository = $enrollmentRepository;
    }

    /**
     * Get an enrollment record by its ID.
     *
     * @param int $id The enrollment ID.
     * @return array An associative array with the enrollment record.
     * @throws ValidationException
     */
    public function getById(int $id): array
    {
        $enrollment = $this->enrollmentRepository->getById($id);

        if (empty($enrollment)) {
            return [];
        } else {
            $enrollmentDto = new EnrollmentDto($enrollment);
            return $enrollmentDto->toArray();
        }
    }
}
