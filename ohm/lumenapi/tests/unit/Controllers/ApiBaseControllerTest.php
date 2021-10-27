<?php

namespace Tests\Unit\Controllers;

use Illuminate\Http\Request;
use ReflectionClass;
use Tests\TestCase;

use App\Http\Controllers\ApiBaseController;

class ApiBaseControllerTest extends TestCase
{
    /* @var ApiBaseController */
    private $apiBaseController;

    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }

        $this->apiBaseController = new ApiBaseController();
    }

    /*
     * getAllEnrollments
     */

    public function testGetPaginationArgs(): void
    {
        $reflector = new ReflectionClass(ApiBaseController::class);
        $method = $reflector->getMethod('getPaginationArgs');
        $method->setAccessible(true);

        $request = Request::create('/api/v1/enrollments?page=2&per_page=20', 'GET');

        list($pageNumber, $pageSize) = $method->invokeArgs($this->apiBaseController, [$request]);

        $this->assertEquals(2, $pageNumber);
        $this->assertEquals(20, $pageSize);
    }

    public function testGetPaginationArgs_NegativePageNum(): void
    {
        $reflector = new ReflectionClass(ApiBaseController::class);
        $method = $reflector->getMethod('getPaginationArgs');
        $method->setAccessible(true);

        $request = Request::create('/api/v1/enrollments?page=-42&per_page=20', 'GET');

        list($pageNumber, $pageSize) = $method->invokeArgs($this->apiBaseController, [$request]);

        $this->assertEquals(0, $pageNumber);
        $this->assertEquals(20, $pageSize);
    }

    public function testGetPaginationArgs_NegativePageSize(): void
    {
        $reflector = new ReflectionClass(ApiBaseController::class);
        $method = $reflector->getMethod('getPaginationArgs');
        $method->setAccessible(true);

        $request = Request::create('/api/v1/enrollments?page=2&per_page=-20', 'GET');

        list($pageNumber, $pageSize) = $method->invokeArgs($this->apiBaseController, [$request]);

        $this->assertEquals(2, $pageNumber);
        $this->assertEquals(ApiBaseController::DEFAULT_PAGE_SIZE, $pageSize);
    }

    public function testGetPaginationArgs_PageSizeTooLarge(): void
    {
        $reflector = new ReflectionClass(ApiBaseController::class);
        $method = $reflector->getMethod('getPaginationArgs');
        $method->setAccessible(true);

        $request = Request::create('/api/v1/enrollments?page=2&per_page=100000', 'GET');

        list($pageNumber, $pageSize) = $method->invokeArgs($this->apiBaseController, [$request]);

        $this->assertEquals(2, $pageNumber);
        $this->assertEquals(ApiBaseController::MAX_PAGE_SIZE, $pageSize);
    }
}
