<?php
// DIC configuration

use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use OHM\Api\Handlers\ErrorHandler;
use OHM\Api\Handlers\PhpErrorHandler;
use OHM\Api\Services\GroupService;
use OHM\Api\Services\ModelAuditService;
use Slim\Views\PhpRenderer;

/* @var Container $container */

// view renderer
$container->set('renderer', function () use ($settings) {
    $templatePath = $settings['renderer']['template_path'];
    return new PhpRenderer($templatePath);
});

// monolog
$container->set('logger', function () use ($settings) {
    $loggerName = $settings['logger']['name'];
    $loggerPath = $settings['logger']['path'];
    $loggerLevel = $settings['logger']['level'];

    $logger = new Logger($loggerName);
    $logger->pushProcessor(new UidProcessor());
    $logger->pushHandler(new StreamHandler($loggerPath, $loggerLevel));
    return $logger;
});

// Service factory for the ORM
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($settings['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container->set('db', function (Container $container) use ($capsule) {
	return $capsule;
});

// PHP exception handler
$container->set('errorLogger', function (Container $container) {
    return new ErrorHandler($container);
});

// PHP 7 exception handler
$container->set('phpErrorLogger', function (Container $container) {
    return new PhpErrorHandler($container);
});

// Factory for ModelAuditService.
$container->set('modelAuditService', function (Container $container) {
    return new ModelAuditService($container);
});

// Factory for GroupService.
$container->set('groupService', function (Container $container) {
    return new GroupService($container);
});
