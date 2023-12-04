<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Service factory for the ORM
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container['db'] = function ($container) use ($capsule) {
	return $capsule;
};

// PHP exception handler
$container['errorLogger'] = function ($c) {
	return new \OHM\Api\Handlers\ErrorHandler($c);
};

// PHP 7 exception handler
$container['phpErrorLogger'] = function ($c) {
	return new \OHM\Api\Handlers\PhpErrorHandler($c);
};

// Factory for ModelAuditService.
$container['modelAuditService'] = function ($c) {
    return new \OHM\Api\Services\ModelAuditService($c);
};

// Factory for GroupService.
$container['groupService'] = function ($c) {
    return new \OHM\Api\Services\GroupService($c);
};
