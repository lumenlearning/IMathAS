<?php

namespace OHM\tests\integration;

use Illuminate\Database\Capsule\Manager;
use Monolog\Logger;
use OHM\Api\Services\ModelAuditService;
use PHPUnit\Framework\TestCase;
use DI\Container;

/**
 * This class provides a SlimPHP v3 Container for running tests
 * that need dependencies provided by Slim\Container.
 *
 * Mocked dependencies:
 * - Logger
 * - ModelAuditService
 *
 * Real dependencies:
 * - DB connection
 */
class SlimPhp4TestCase extends TestCase
{
    protected Container $container;

    function setUp(): void
    {
        // Create SlimPHP Container.
        $this->container = $this->createMock(Container::class);

        // Add Container dependency mocks.
        $logger = $this->createMock(Logger::class);
        $modelAuditService = $this->createMock(ModelAuditService::class);

        $this->container->method('get')
//            ->withConsecutive(['logger'], ['modelAuditService']) // deprecated in phpunit 9.6
            ->willReturnOnConsecutiveCalls($logger, $modelAuditService);

        // Add a real DB connection.
        $capsule = new Manager;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => getenv('DB_SERVER'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'latin1',
            'collation' => 'latin1_swedish_ci',
            'prefix' => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $this->container->set('db', function (Container $container) use ($capsule) {
            return $capsule;
        });
    }
}
