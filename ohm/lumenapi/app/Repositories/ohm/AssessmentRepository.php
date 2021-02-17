<?php
namespace App\Repositories\ohm;

use App\Repositories\Interfaces\AssessmentRepositoryInterface;

class AssessmentRepository extends BaseRepository implements AssessmentRepositoryInterface
{
    public function getAllByFilter($assessmentId, $courseId)
    {
        $result = app('db')->select(
            "SELECT * FROM imas_assessments WHERE id = :assessmentId AND courseid = :courseId;",
            ['assessmentId' => $assessmentId, 'courseId' => $courseId]);

        return $this->toAssoc($result[0]);
    }

    public function getAllByCourseId($courseIds, $placeholders)
    {
        $result = app('db')->select(
            "SELECT id,name FROM imas_assessments WHERE id IN (:placeholders) AND courseid = :courseId;",
            ['placeholders' => $placeholders, 'courseId' => $courseIds]);

        return $this->toAssoc($result);
    }
}