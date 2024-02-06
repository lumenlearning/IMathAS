<?php

namespace OHM\health;

use PDO;

class HealthCheckController
{
    private HealthCheckSources $healthCheckSources;

    public function __construct(PDO $dbh, ?HealthCheckSources $healthCheckSources = null)
    {
        $this->healthCheckSources = $healthCheckSources ?? new HealthCheckSources($dbh);
    }

    /**
     * The response to return when no health check item was requested.
     *
     * @return array An associative array with an error message.
     */
    public function no_check_requested(): array
    {
        http_response_code(404);
        $responseData = [
            'errors' => ['No health check item specified.']
        ];
        echo json_encode($responseData);

        return $responseData;
    }

    /**
     * Return the number of grades waiting to be passed back in imas_ltiqueue.
     *
     * Our Pingdom monitoring can only react to HTTP status codes.
     *
     * HTTP response codes and their meaning:
     * - 200 = Queue size is under 1,000
     * - 210 = Queue size is between 1,000 and 4,000
     * - 220 = Queue size is between 4,000 and 7,000
     * - 230 = Queue size is between 7,000 and 10,000
     * - 240 = Queue size is over 10,000
     *
     * @return array An associative array with the number of grades
     *               waiting to be returned.
     */
    public function check_grade_passback_queue_size(): array
    {
        $queueSize = $this->healthCheckSources->fetch_grade_passback_queue_size();

        $statusCode = -1;
        $statusDescription = null;
        if (1_000 > $queueSize) {
            $statusCode = 200;
            $statusDescription = 'Queue size is under 1,000 items.';
        } else if (1_000 <= $queueSize && 4_000 > $queueSize) {
            $statusCode = 210;
            $statusDescription = 'Queue size is between 1,000 and 4,000 items.';
        } else if (4_000 <= $queueSize && 7_000 > $queueSize) {
            $statusCode = 220;
            $statusDescription = 'Queue size is between 4,000 and 7,000 items.';
        } else if (7_000 <= $queueSize && 10_000 > $queueSize) {
            $statusCode = 230;
            $statusDescription = 'Queue size is between 7,000 and 10,000 items.';
        } else if (10_000 <= $queueSize) {
            $statusCode = 240;
            $statusDescription = 'Queue size is over 10,000 items.';
        }

        http_response_code($statusCode);
        $responseData = [
            'grade_passback_queue_size' => $queueSize,
            'status_code' => $statusCode,
            'status_description' => $statusDescription,
        ];
        echo json_encode($responseData);

        return $responseData;
    }
}
