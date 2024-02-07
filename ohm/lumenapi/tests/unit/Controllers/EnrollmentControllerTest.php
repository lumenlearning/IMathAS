<?php

namespace Tests\Unit\Controllers;

use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

use App\Http\Controllers\EnrollmentController;
use App\Services\ohm\EnrollmentService;
use App\Services\Interfaces\EnrollmentServiceInterface;

class EnrollmentControllerTest extends TestCase
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

    /* @var EnrollmentController */
    private $enrollmentController;

    /* @var EnrollmentServiceInterface */
    private $enrollmentService;

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }

        $this->enrollmentService = Mockery::mock(EnrollmentService::class);
        $this->enrollmentController = new EnrollmentController($this->enrollmentService);
    }

    /*
     * getAllEnrollments
     */

    public function testGetAllEnrollments(): void
    {
        $this->enrollmentService
            ->shouldReceive('getAll')
            ->andReturn(self::ENROLLMENT_COLLECTION);

        $request = Request::create('/api/v1/enrollments', 'GET');
        $jsonResponse = $this->enrollmentController->getAllEnrollments($request);
        $jsonData = $jsonResponse->getData();

        $this->assertEquals(200, $jsonResponse->getStatusCode());
        // A valid Enrollment collection is returned.
        $this->assertEquals(2, $jsonData[0]->id);
        $this->assertEquals(8, $jsonData[0]->user_id);
        $this->assertEquals(1, $jsonData[0]->course_id);
        $this->assertEquals(0, $jsonData[0]->lti_course_id);
        $this->assertEquals(1491866005, $jsonData[0]->last_access);
        $this->assertEquals(true, $jsonData[0]->has_valid_access_code);
        $this->assertEquals(false, $jsonData[0]->is_opted_out_of_assessments);
        $this->assertEquals(null, $jsonData[0]->created_at);
        $this->assertEquals(21, $jsonData[1]->user_id);
    }

    public function testGetAllEnrollments_Pagination(): void
    {
        $this->enrollmentService
            ->shouldReceive('getAll')
            ->with(1, 2, [])
            ->andReturn(self::ENROLLMENT_COLLECTION);

        $request = Request::create('/api/v1/enrollments', 'GET', [
            'page' => '1', // pages are zero-indexed
            'per_page' => 2,
        ]);
        $jsonResponse = $this->enrollmentController->getAllEnrollments($request);
        $jsonData = $jsonResponse->getData();

        $this->assertEquals(200, $jsonResponse->getStatusCode());
        // A valid Enrollment collection is returned.
        $this->assertEquals(2, $jsonData[0]->id);
        $this->assertEquals(8, $jsonData[0]->user_id);
        $this->assertEquals(1, $jsonData[0]->course_id);
        $this->assertEquals(0, $jsonData[0]->lti_course_id);
        $this->assertEquals(1491866005, $jsonData[0]->last_access);
        $this->assertEquals(true, $jsonData[0]->has_valid_access_code);
        $this->assertEquals(false, $jsonData[0]->is_opted_out_of_assessments);
        $this->assertEquals(null, $jsonData[0]->created_at);
        $this->assertEquals(21, $jsonData[1]->user_id);
    }

    public function testGetAllEnrollments_NoneFound(): void
    {
        $this->enrollmentService
            ->shouldReceive('getAll')
            ->andReturn([]);

        $request = Request::create('/api/v1/enrollments', 'GET');
        $jsonResponse = $this->enrollmentController->getAllEnrollments($request);
        $jsonData = $jsonResponse->getData();

        $this->assertEquals(200, $jsonResponse->getStatusCode());
        // A valid Enrollment collection is returned.
        $this->assertEquals(0, sizeof($jsonData));
    }

    /*
     * getEnrollmentById
     */

    public function testGetEnrollmentById(): void
    {
        $this->enrollmentService
            ->shouldReceive('getById')
            ->with(42)
            ->andReturn(self::ENROLLMENT_COLLECTION);

        $request = Request::create('/api/v1/enrollments/42', 'GET');
        $jsonResponse = $this->enrollmentController->getEnrollmentById($request, 42);
        $jsonData = $jsonResponse->getData();

        $this->assertEquals(200, $jsonResponse->getStatusCode());
        // A valid Enrollment is returned.
        $this->assertEquals(2, $jsonData[0]->id);
        $this->assertEquals(8, $jsonData[0]->user_id);
        $this->assertEquals(1, $jsonData[0]->course_id);
        $this->assertEquals(0, $jsonData[0]->lti_course_id);
        $this->assertEquals(1491866005, $jsonData[0]->last_access);
        $this->assertEquals(true, $jsonData[0]->has_valid_access_code);
        $this->assertEquals(false, $jsonData[0]->is_opted_out_of_assessments);
        $this->assertEquals(null, $jsonData[0]->created_at);
    }

    public function testGetEnrollmentById_NotFound(): void
    {
        $this->enrollmentService
            ->shouldReceive('getById')
            ->with(42)
            ->andReturn(null);

        $request = Request::create('/api/v1/enrollments/42', 'GET');
        $jsonResponse = $this->enrollmentController->getEnrollmentById($request, 42);

        $this->assertEquals(404, $jsonResponse->getStatusCode());
    }

    /*
     * updateEnrollmentById
     */

    public function testUpdateEnrollmentById(): void
    {
        $this->enrollmentService
            ->shouldReceive('updateById')
            ->withAnyArgs()
            ->andReturn(self::ENROLLMENT_SINGLE);

        $request = Request::create('/api/v1/enrollments/42', 'PUT', [
            'has_valid_access_code' => true,
            'is_opted_out_of_assessments' => false,
        ]);
        $jsonResponse = $this->enrollmentController->updateEnrollmentById($request, 42);
        $jsonData = $jsonResponse->getData();

        $this->assertEquals(200, $jsonResponse->getStatusCode());
        // A valid Enrollment is returned.
        $this->assertEquals(2, $jsonData->id);
        $this->assertEquals(8, $jsonData->user_id);
        $this->assertEquals(1, $jsonData->course_id);
        $this->assertEquals(0, $jsonData->lti_course_id);
        $this->assertEquals(1491866005, $jsonData->last_access);
        $this->assertEquals(true, $jsonData->has_valid_access_code);
        $this->assertEquals(false, $jsonData->is_opted_out_of_assessments);
        $this->assertEquals(null, $jsonData->created_at);
    }

    public function testUpdateEnrollmentById_missingAccessCode(): void
    {
        $this->enrollmentService
            ->shouldReceive('updateById')
            ->withAnyArgs()
            ->andReturn(self::ENROLLMENT_SINGLE);

        $request = Request::create('/api/v1/enrollments/42', 'PUT', [
            'is_opted_out_of_assessments' => false,
        ]);

        $jsonResponse = null;
        try {
            /*
             * - In a GitHub Actions environment:
             *   - This is throwing a ValidationException during testing.
             *   - $this->expectException(ValidationException::class) is
             *     failing to catch the exception.
             * - In local development:
             *   - Calling enrollmentController() directly _should_ throw a
             *     ValidationException in local testing, but it's not.
             *   - A response object is being returned with the correct
             *     data and assertions are working.
             *
             * - This try/catch handles testing in GHA, while the rest of this
             *   test runs assertions in local testing.
             */
            $jsonResponse = $this->enrollmentController->updateEnrollmentById($request, 42);
        } catch (ValidationException $e) {
            return;
        }

        $jsonData = $jsonResponse->getData();

        $this->assertEquals(400, $jsonResponse->getStatusCode());
        $this->assertEquals('The given data was invalid.', $jsonData->errors[0]);
    }

    public function testUpdateEnrollmentById_missingOptedOutAssessments(): void
    {
        $this->enrollmentService
            ->shouldReceive('updateById')
            ->withAnyArgs()
            ->andReturn(self::ENROLLMENT_SINGLE);

        $request = Request::create('/api/v1/enrollments/42', 'PUT', [
            'has_valid_access_code' => true,
        ]);

        $jsonResponse = null;
        try {
            /*
             * - In a GitHub Actions environment:
             *   - This is throwing a ValidationException during testing.
             *   - $this->expectException(ValidationException::class) is
             *     failing to catch the exception.
             * - In local development:
             *   - Calling enrollmentController() directly _should_ throw a
             *     ValidationException in local testing, but it's not.
             *   - A response object is being returned with the correct
             *     data and assertions are working.
             *
             * - This try/catch handles testing in GHA, while the rest of this
             *   test runs assertions in local testing.
             */
            $jsonResponse = $this->enrollmentController->updateEnrollmentById($request, 42);
        } catch (ValidationException $e) {
            return;
        }

        $jsonData = $jsonResponse->getData();

        $this->assertEquals(400, $jsonResponse->getStatusCode());
        $this->assertEquals('The given data was invalid.', $jsonData->errors[0]);
    }

    public function testUpdateEnrollmentById_NotFound(): void
    {
        $this->enrollmentService
            ->shouldReceive('updateById')
            ->withAnyArgs()
            ->andThrow(RelationNotFoundException::class);

        $request = Request::create('/api/v1/enrollments/42', 'PUT', [
            'has_valid_access_code' => true,
            'is_opted_out_of_assessments' => false,
        ]);
        $jsonResponse = $this->enrollmentController->updateEnrollmentById($request, 42);
        $jsonData = $jsonResponse->getData();

        $this->assertEquals(404, $jsonResponse->getStatusCode());
    }

    /*
     * getSearchFilters
     */

    public function testGetSearchFilters(): void
    {
        $reflector = new ReflectionClass(EnrollmentController::class);
        $method = $reflector->getMethod('getSearchFilters');
        $method->setAccessible(true);

        $request = Request::create('/nyan/kitty', 'MEOW', [
            'user_id_filter' => 42,
            'course_id_filter' => 123,
            'is_floofy' => true,
        ]);

        $result = $method->invokeArgs($this->enrollmentController, [$request]);

        // Only allowed filters should be returned.
        $this->assertEquals(['user_id_filter' => 42, 'course_id_filter' => 123], $result);
    }
}
