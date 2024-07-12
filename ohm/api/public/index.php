<?php

use DI\Container;
use Slim\Factory\AppFactory;

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require_once __DIR__ . '/../../../vendor/autoload.php';

session_start();

// Create Container using PHP-DI
$container = new Container();

// Add settings to container.
$settings = require __DIR__ . '/../src/configs/settings.php';
$container->set('settings', $settings);

// Set up dependencies
require __DIR__ . '/../src/configs/dependencies.php';

/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 */
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register middleware
require __DIR__ . '/../src/configs/middleware.php';

// Add route callbacks
require_once __DIR__ . '/../src/configs/routes.php';

/**
 * Set the base path so that the router can match the URL from the browser with the path set
 * in the route registration. This is done with the setBasePath() method.
 */
$app->setBasePath(API_BASE_PATH);

// Run app
$app->run();
