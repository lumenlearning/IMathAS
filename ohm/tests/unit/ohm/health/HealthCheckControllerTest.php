<?php

namespace OHM\tests\unit\ohm\health;

use OHM\health\HealthCheckController;
use OHM\health\HealthCheckSources;
use PDO;
use PHPUnit\Framework\TestCase;

class HealthCheckControllerTest extends TestCase
{
    private HealthCheckSources $healthCheckSources;

    function setUp(): void
    {
        /** @var PDO $dbh */
        $dbh = $this->createMock(PDO::class);
        $this->healthCheckSources = $this->createMock(HealthCheckSources::class);

        $this->healthCheckController = new HealthCheckController($dbh, $this->healthCheckSources);
    }

    /*
     * no_check_requested
     */

    function test_no_check_requested(): void
    {
        $responseData = $this->healthCheckController->no_check_requested();

        $expectedResponseData = [
            'errors' => ['No health check item specified.']
        ];
        $this->assertEquals($expectedResponseData, $responseData);
    }

    /*
     * check_grade_passback_queue_size
     */

    function test_fetch_grade_passback_queue_size_status_200(): void
    {
        $this->healthCheckSources->expects($this->once())
            ->method('fetch_grade_passback_queue_size')
            ->willReturn(999);

        $responseData = $this->healthCheckController->check_grade_passback_queue_size();

        $expectedResponseData = [
            'grade_passback_queue_size' => 999,
            'status_code' => 200,
            'status_description' => 'Queue size is under 1,000 items.',
        ];
        $this->assertEquals($expectedResponseData, $responseData);
    }

    function test_fetch_grade_passback_queue_size_status_210_min(): void
    {
        $this->healthCheckSources->expects($this->once())
            ->method('fetch_grade_passback_queue_size')
            ->willReturn(1000);

        $responseData = $this->healthCheckController->check_grade_passback_queue_size();

        $expectedResponseData = [
            'grade_passback_queue_size' => 1000,
            'status_code' => 210,
            'status_description' => 'Queue size is between 1,000 and 4,000 items.',
        ];
        $this->assertEquals($expectedResponseData, $responseData);
    }

    function test_fetch_grade_passback_queue_size_status_210_max(): void
    {
        $this->healthCheckSources->expects($this->once())
            ->method('fetch_grade_passback_queue_size')
            ->willReturn(3999);

        $responseData = $this->healthCheckController->check_grade_passback_queue_size();

        $expectedResponseData = [
            'grade_passback_queue_size' => 3999,
            'status_code' => 210,
            'status_description' => 'Queue size is between 1,000 and 4,000 items.',
        ];
        $this->assertEquals($expectedResponseData, $responseData);
    }

    function test_fetch_grade_passback_queue_size_status_220_min(): void
    {
        $this->healthCheckSources->expects($this->once())
            ->method('fetch_grade_passback_queue_size')
            ->willReturn(4000);

        $responseData = $this->healthCheckController->check_grade_passback_queue_size();

        $expectedResponseData = [
            'grade_passback_queue_size' => 4000,
            'status_code' => 220,
            'status_description' => 'Queue size is between 4,000 and 7,000 items.',
        ];
        $this->assertEquals($expectedResponseData, $responseData);
    }

    function test_fetch_grade_passback_queue_size_status_220_max(): void
    {
        $this->healthCheckSources->expects($this->once())
            ->method('fetch_grade_passback_queue_size')
            ->willReturn(6999);

        $responseData = $this->healthCheckController->check_grade_passback_queue_size();

        $expectedResponseData = [
            'grade_passback_queue_size' => 6999,
            'status_code' => 220,
            'status_description' => 'Queue size is between 4,000 and 7,000 items.',
        ];
        $this->assertEquals($expectedResponseData, $responseData);
    }

    function test_fetch_grade_passback_queue_size_status_230_min(): void
    {
        $this->healthCheckSources->expects($this->once())
            ->method('fetch_grade_passback_queue_size')
            ->willReturn(7000);

        $responseData = $this->healthCheckController->check_grade_passback_queue_size();

        $expectedResponseData = [
            'grade_passback_queue_size' => 7000,
            'status_code' => 230,
            'status_description' => 'Queue size is between 7,000 and 10,000 items.',
        ];
        $this->assertEquals($expectedResponseData, $responseData);
    }

    function test_fetch_grade_passback_queue_size_status_230_max(): void
    {
        $this->healthCheckSources->expects($this->once())
            ->method('fetch_grade_passback_queue_size')
            ->willReturn(9999);

        $responseData = $this->healthCheckController->check_grade_passback_queue_size();

        $expectedResponseData = [
            'grade_passback_queue_size' => 9999,
            'status_code' => 230,
            'status_description' => 'Queue size is between 7,000 and 10,000 items.',
        ];
        $this->assertEquals($expectedResponseData, $responseData);
    }

    function test_fetch_grade_passback_queue_size_status_240(): void
    {
        $this->healthCheckSources->expects($this->once())
            ->method('fetch_grade_passback_queue_size')
            ->willReturn(10000);

        $responseData = $this->healthCheckController->check_grade_passback_queue_size();

        $expectedResponseData = [
            'grade_passback_queue_size' => 10000,
            'status_code' => 240,
            'status_description' => 'Queue size is over 10,000 items.',
        ];
        $this->assertEquals($expectedResponseData, $responseData);
    }
}
