<?php
namespace App\Repositories\Interfaces;

interface AssessmentRepositoryInterface
{
    public function getAllByFilter($assessmentId, $courseId);

    public function getAllByCourseId($courseIds, $placeholders);
}