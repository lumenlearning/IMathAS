<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;

use App\Dtos\EnrollmentDto;
use App\Models\Enrollment;

class EnrollmentDtoTest extends TestCase
{
    const ENROLLMENT_SINGLE = [
        "id" => 2,
        "user_id" => 8,
        "course_id" => 1,
        "lti_course_id" => 0,
        "last_access" => 1491866005,
        "has_valid_access_code" => true,
        "is_opted_out_of_assessments" => false,
        "created_at" => null
    ];

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }
    }

    private function fillEnrollment(Enrollment $enrollment): Enrollment
    {
        $enrollment->id = self::ENROLLMENT_SINGLE['id'];
        $enrollment->userid = self::ENROLLMENT_SINGLE['user_id'];
        $enrollment->courseid = self::ENROLLMENT_SINGLE['course_id'];
        $enrollment->lticourseid = self::ENROLLMENT_SINGLE['lti_course_id'];
        $enrollment->lastaccess = self::ENROLLMENT_SINGLE['last_access'];
        $enrollment->has_valid_access_code = self::ENROLLMENT_SINGLE['has_valid_access_code'];
        $enrollment->is_opted_out_of_assessments = self::ENROLLMENT_SINGLE['is_opted_out_of_assessments'];

        return $enrollment;
    }

    /*
     * map
     */

    public function testMap(): void
    {
        $enrollment = new Enrollment();
        $enrollment = $this->fillEnrollment($enrollment);

        $enrollmentDto = new EnrollmentDto($enrollment->toArray());

        $this->assertEquals(self::ENROLLMENT_SINGLE['id'], $enrollmentDto->getId());
        $this->assertEquals(self::ENROLLMENT_SINGLE['user_id'], $enrollmentDto->getUserId());
        $this->assertEquals(self::ENROLLMENT_SINGLE['course_id'], $enrollmentDto->getCourseId());
        $this->assertEquals(self::ENROLLMENT_SINGLE['lti_course_id'], $enrollmentDto->getLtiCourseId());
        $this->assertEquals(self::ENROLLMENT_SINGLE['last_access'], $enrollmentDto->getLastAccess());
        $this->assertEquals(self::ENROLLMENT_SINGLE['has_valid_access_code'], $enrollmentDto->getHasValidAccessCode());
        $this->assertEquals(self::ENROLLMENT_SINGLE['is_opted_out_of_assessments'], $enrollmentDto->getIsOptedOutOfAssessments());
    }

    /*
     * toArray
     */

    public function testToArray(): void
    {
        $enrollment = new Enrollment();
        $enrollment = $this->fillEnrollment($enrollment);
        $enrollmentDto = new EnrollmentDto($enrollment->toArray());

        $enrollmentDtoAsArray = $enrollmentDto->toArray();

        $this->assertEquals(self::ENROLLMENT_SINGLE['id'], $enrollmentDtoAsArray['id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['user_id'], $enrollmentDtoAsArray['user_id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['course_id'], $enrollmentDtoAsArray['course_id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['lti_course_id'], $enrollmentDtoAsArray['lti_course_id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['last_access'], $enrollmentDtoAsArray['last_access']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['has_valid_access_code'], $enrollmentDtoAsArray['has_valid_access_code']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['is_opted_out_of_assessments'], $enrollmentDtoAsArray['is_opted_out_of_assessments']);
    }
}
