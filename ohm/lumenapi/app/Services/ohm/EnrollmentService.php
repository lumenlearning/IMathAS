<?php

namespace App\Services\ohm;

use App\Dtos\EnrollmentDto;
use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Services\Interfaces\EnrollmentServiceInterface;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class EnrollmentService extends BaseService implements EnrollmentServiceInterface
{
    // Mapping of filter keys (as provided by Administrate) to DB column names.
    const ALLOWED_FILTERS = [
        'user_id_filter' => 'userid',
        'course_id_filter' => 'courseid',
    ];

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
     * Get all Enrollments.
     *
     * @param int $pageNum Pagination. The page number to return.
     * @param int $pageSize Pagination. The size of the page to return.
     * @param array $filters An associative array of filters to search by.
     * @return array An associative array of Enrollment data.
     */
    public function getAll(int $pageNum, int $pageSize, array $filters): array
    {
        $columnFilters = $this->mapFilterNames($filters);

        $enrollments = $this->enrollmentRepository->getAll($pageSize, $pageSize * $pageNum, $columnFilters);
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
     * Update an enrollment.
     *
     * @param int $id The enrollment ID.
     * @param array $input An associative array with updated data.
     * @return array The updated enrollment as an associative array.
     */
    public function updateById(int $id, array $input): array
    {
        $enrollment = $this->enrollmentRepository->getById($id);
        if (empty($enrollment)) {
            throw new RelationNotFoundException(
                sprintf('Enrollment ID %d was not found.', $id));
        }

        $enrollment->fill($input); // See Enrollment model for allowed fillable fields.
        $enrollment = $this->enrollmentRepository->update($enrollment);

        $enrollmentDto = new EnrollmentDto($enrollment->toArray());
        return $enrollmentDto->toArray();
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

    /**
     * Map filter names provided by EnrollmentController to DB column names.
     *
     * @param array $inputFilters The array of filters provided by EnrollmentController.
     * @return array An associative array of filters by DB column name.
     * @see self::ALLOWED_FILTERS
     */
    private function mapFilterNames(array $inputFilters): array
    {
        $columnFilters = [];
        foreach (self::ALLOWED_FILTERS as $ALLOWED_FILTER_BEFORE => $ALLOWED_FILTER_AFTER) {
            if (isset($inputFilters[$ALLOWED_FILTER_BEFORE])) {
                $dbColumnName = self::ALLOWED_FILTERS[$ALLOWED_FILTER_BEFORE];
                $columnFilters[$dbColumnName] = $inputFilters[$ALLOWED_FILTER_BEFORE];
            }
        }
        return $columnFilters;
    }
}
