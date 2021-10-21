<?php

namespace App\Dtos;

class EnrollmentDto extends AbstractDto implements DtoInterface
{
    private $id;
    private $userId;
    private $courseId;
    private $ltiCourseId;
    private $lastAccess;
    private $hasValidAccessCode;
    private $isOptedOutOfAssessments;
    private $createdAt;

    /**
     * Return all fields as an associative array.
     *
     * @return array An associative array containing all DTO fields.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'course_id' => $this->courseId,
            'lti_course_id' => $this->ltiCourseId,
            'last_access' => $this->lastAccess,
            'has_valid_access_code' => $this->hasValidAccessCode,
            'is_opted_out_of_assessments' => $this->isOptedOutOfAssessments,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * Map an associative array to DTO fields.
     *
     * @param array $data An associative array. (representing a single database row)
     * @return bool True if mapping to DTO fields was successful.
     */
    protected function map(array $data): bool
    {
        $this->id = $data['id'] ?? null;
        $this->userId = $data['userid'] ?? null;
        $this->courseId = $data['courseid'] ?? null;
        $this->ltiCourseId = $data['lticourseid'] ?? null;
        $this->lastAccess = $data['lastaccess'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;

        if (isset($data['has_valid_access_code'])) {
            $this->hasValidAccessCode = (bool)$data['has_valid_access_code'];
        } else {
            $this->hasValidAccessCode = false;
        }

        if (isset($data['is_opted_out_assessments'])) {
            $this->isOptedOutOfAssessments = (bool)$data['is_opted_out_assessments'];
        } else {
            $this->isOptedOutOfAssessments = false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return EnrollmentDto
     */
    public function setId(int $id): EnrollmentDto
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return EnrollmentDto
     */
    public function setUserId(int $userId): EnrollmentDto
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCourseId(): int
    {
        return $this->courseId;
    }

    /**
     * @param int $courseId
     * @return EnrollmentDto
     */
    public function setCourseId(int $courseId): EnrollmentDto
    {
        $this->courseId = $courseId;
        return $this;
    }

    /**
     * @return int
     */
    public function getLtiCourseId(): int
    {
        return $this->ltiCourseId;
    }

    /**
     * @param int $ltiCourseId
     * @return EnrollmentDto
     */
    public function setLtiCourseId(int $ltiCourseId): EnrollmentDto
    {
        $this->ltiCourseId = $ltiCourseId;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastAccess(): int
    {
        return $this->lastAccess;
    }

    /**
     * @param int $lastAccess
     * @return EnrollmentDto
     */
    public function setLastAccess(int $lastAccess): EnrollmentDto
    {
        $this->lastAccess = $lastAccess;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasValidAccessCode(): bool
    {
        return $this->hasValidAccessCode;
    }

    /**
     * @param bool $hasValidAccessCode
     * @return EnrollmentDto
     */
    public function setHasValidAccessCode(bool $hasValidAccessCode): EnrollmentDto
    {
        $this->hasValidAccessCode = $hasValidAccessCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsOptedOutOfAssessments(): bool
    {
        return $this->isOptedOutOfAssessments;
    }

    /**
     * @param bool $isOptedOutOfAssessments
     * @return EnrollmentDto
     */
    public function setIsOptedOutOfAssessments(bool $isOptedOutOfAssessments): EnrollmentDto
    {
        $this->isOptedOutOfAssessments = $isOptedOutOfAssessments;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     * @return EnrollmentDto
     */
    public function setCreatedAt(int $createdAt): EnrollmentDto
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
