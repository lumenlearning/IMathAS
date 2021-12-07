<?php

namespace Tests\Unit\Controllers;

use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Repositories\ohm\EnrollmentRepository;
use App\Services\Interfaces\EnrollmentServiceInterface;
use App\Services\ohm\EnrollmentService;

class EnrollmentServiceTest extends TestCase
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

    const ENROLLMENT_COLLECTION = [
        [
            "id" => 2,
            "user_id" => 8,
            "course_id" => 1,
            "lti_course_id" => 0,
            "last_access" => 1491866005,
            "has_valid_access_code" => true,
            "is_opted_out_of_assessments" => false,
            "created_at" => null
        ],
        [
            "id" => 4,
            "user_id" => 21,
            "course_id" => 0,
            "lti_course_id" => 0,
            "last_access" => 0,
            "has_valid_access_code" => false,
            "is_opted_out_of_assessments" => false,
            "created_at" => null
        ],
    ];

    /* @var EnrollmentServiceInterface */
    private $enrollmentService;

    /* @var EnrollmentRepositoryInterface */
    private $enrollmentRepository;

    private $enrollment;
    private $enrollmentCollection;

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }

        $this->setupEnrollmentModels();

        $this->enrollmentRepository = Mockery::mock(EnrollmentRepository::class);
        $this->enrollmentService = new EnrollmentService($this->enrollmentRepository);
    }

    private function setupEnrollmentModels(): void
    {
        $enrollment = new Enrollment();
        $enrollment->id = 2;
        $enrollment->userid = 8;
        $enrollment->courseid = 1;
        $enrollment->lticourseid = 0;
        $enrollment->lastaccess = 1491866005;
        $enrollment->has_valid_access_code = true;
        $enrollment->is_opted_out_assessments = false;

        $enrollment2 = new Enrollment();
        $enrollment2->id = 4;
        $enrollment2->userid = 21;
        $enrollment2->courseid = 0;
        $enrollment2->lticourseid = 0;
        $enrollment2->lastaccess = 0;
        $enrollment2->has_valid_access_code = false;
        $enrollment2->is_opted_out_assessments = false;

        $this->enrollment = $enrollment;
        $this->enrollmentCollection = [$enrollment, $enrollment2];
    }

    /*
     * getAll
     */

    public function testGetAll(): void
    {
        $this->enrollmentRepository
            ->shouldReceive('getAll')
            ->withAnyArgs()
            ->andReturn($this->enrollmentCollection);

        $enrollmentsAsArray = $this->enrollmentService->getAll(0, 10, []);

        $this->assertEquals(2, sizeof($enrollmentsAsArray));
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['id'], $enrollmentsAsArray[0]['id']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['user_id'], $enrollmentsAsArray[0]['user_id']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['course_id'], $enrollmentsAsArray[0]['course_id']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['lti_course_id'], $enrollmentsAsArray[0]['lti_course_id']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['last_access'], $enrollmentsAsArray[0]['last_access']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['has_valid_access_code'], $enrollmentsAsArray[0]['has_valid_access_code']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['is_opted_out_of_assessments'], $enrollmentsAsArray[0]['is_opted_out_of_assessments']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[1]['user_id'], $enrollmentsAsArray[1]['user_id']);
    }

    /*
     * getById
     */

    public function testGetById(): void
    {
        $this->enrollmentRepository
            ->shouldReceive('getById')
            ->with(42)
            ->andReturn($this->enrollment);

        $enrollmentAsArray = $this->enrollmentService->getById(42);

        $this->assertEquals(self::ENROLLMENT_SINGLE['id'], $enrollmentAsArray['id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['user_id'], $enrollmentAsArray['user_id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['course_id'], $enrollmentAsArray['course_id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['lti_course_id'], $enrollmentAsArray['lti_course_id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['last_access'], $enrollmentAsArray['last_access']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['has_valid_access_code'], $enrollmentAsArray['has_valid_access_code']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['is_opted_out_of_assessments'], $enrollmentAsArray['is_opted_out_of_assessments']);
    }

    /*
     * updateById
     */

    public function testUpdateById(): void
    {
        $this->enrollmentRepository
            ->shouldReceive('getById')
            ->with(42)
            ->andReturn($this->enrollment);
        $this->enrollmentRepository
            ->shouldReceive('update')
            ->withAnyArgs()
            ->andReturn($this->enrollment);

        $enrollmentAsArray = $this->enrollmentService->updateById(42, self::ENROLLMENT_SINGLE);

        $this->assertEquals(self::ENROLLMENT_SINGLE['id'], $enrollmentAsArray['id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['user_id'], $enrollmentAsArray['user_id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['course_id'], $enrollmentAsArray['course_id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['lti_course_id'], $enrollmentAsArray['lti_course_id']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['last_access'], $enrollmentAsArray['last_access']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['has_valid_access_code'], $enrollmentAsArray['has_valid_access_code']);
        $this->assertEquals(self::ENROLLMENT_SINGLE['is_opted_out_of_assessments'], $enrollmentAsArray['is_opted_out_of_assessments']);
    }

    public function testUpdateById_EnrollmentNotFound(): void
    {
        $this->enrollmentRepository
            ->shouldReceive('getById')
            ->with(42)
            ->andReturn([]);

        $this->expectException(RelationNotFoundException::class);

        $this->enrollmentService->updateById(42, self::ENROLLMENT_SINGLE);
    }

    /*
     * enrollmentsToArray
     */

    public function testEnrollmentsToArray(): void
    {
        $reflector = new ReflectionClass(EnrollmentService::class);
        $method = $reflector->getMethod('enrollmentsToArray');
        $method->setAccessible(true);

        $enrollmentAsArray = $method->invokeArgs($this->enrollmentService, [$this->enrollmentCollection]);

        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['id'], $enrollmentAsArray[0]['id']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['user_id'], $enrollmentAsArray[0]['user_id']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['course_id'], $enrollmentAsArray[0]['course_id']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['lti_course_id'], $enrollmentAsArray[0]['lti_course_id']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['last_access'], $enrollmentAsArray[0]['last_access']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['has_valid_access_code'], $enrollmentAsArray[0]['has_valid_access_code']);
        $this->assertEquals(self::ENROLLMENT_COLLECTION[0]['is_opted_out_of_assessments'], $enrollmentAsArray[0]['is_opted_out_of_assessments']);
    }

    /*
     * mapFilterNames
     */

    public function testMapFilterNames(): void
    {
        $reflector = new ReflectionClass(EnrollmentService::class);
        $method = $reflector->getMethod('mapFilterNames');
        $method->setAccessible(true);

        $filtersFromController = ['user_id_filter' => 42, 'course_id_filter' => 123, 'meow' => 'nyan'];
        $filters = $method->invokeArgs($this->enrollmentService, [$filtersFromController]);

        $this->assertEquals(['userid' => 42, 'courseid' => 123], $filters);
    }

    /*
     * mapFilterNames
     */

    public function testMapFieldNames(): void
    {
        $reflector = new ReflectionClass(EnrollmentService::class);
        $method = $reflector->getMethod('mapFieldNames');
        $method->setAccessible(true);

        $fieldsFromController = ['has_valid_access_code' => true, 'is_opted_out_of_assessments' => false];
        $entityData = $method->invokeArgs($this->enrollmentService, [$fieldsFromController]);

        $this->assertEquals(['has_valid_access_code' => true, 'is_opted_out_assessments' => false], $entityData);
    }
}
