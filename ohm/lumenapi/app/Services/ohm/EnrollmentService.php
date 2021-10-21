<?php

namespace App\Services\ohm;

use App\Dtos\EnrollmentDto;
use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Services\Interfaces\EnrollmentServiceInterface;

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

    public function getAll(int $pageNum, int $pageSize): array
    {
        $enrollments = $this->enrollmentRepository->getAll($pageSize, $pageSize * $pageNum);
        return $this->enrollmentsToArray($enrollments);
    }

    /**
     * Get an enrollment record by its ID.
     *
     * @param int $id The enrollment ID.
     * @return array An associative array with the enrollment record.
     */
    public function getById(int $id): ?array
    {
        $enrollment = $this->enrollmentRepository->getById($id);

        if (empty($enrollment)) {
            return null;
        } else {
            $enrollmentDto = new EnrollmentDto($enrollment->toArray());
            return $enrollmentDto->toArray();
        }
    }

    /**
     * Convert an array of Enrollments to one associative array.
     *
     * @param Enrollment[] $enrollments An array of Enrollment objects.
     * @return array An associative array of Enrollment data.
     */
    private function enrollmentsToArray($enrollments): array
    {
        $enrollmentsArray = [];
        foreach ($enrollments as $enrollment) {
            $enrollmentDto = new EnrollmentDto($enrollment->toArray());
            $enrollmentsArray[] = $enrollmentDto->toArray();
        }

        return $enrollmentsArray;
    }
}
