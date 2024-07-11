<?php

use Monolog\Level;

$CONFIG_ENV = getenv('CONFIG_ENV') ? getenv('CONFIG_ENV') : 'development';
$LOG_PATH = in_array($CONFIG_ENV, ['production', 'staging']) ?
	'/var/log/httpd/payment_api' : __DIR__ . '/../../logs';

const API_BASE_PATH = '/ohm/api';

return [
    // SlimPHP settings
    'slim' => [
        'displayErrorDetails' => false, // set to true in development
        'logErrors' => true,
        'logErrorDetails' => true,
    ],

    // API general settings
    'api' => [
        'defaultPageSize' => 10,
        'maxPageSize' => 100,
        'secureUrls' => ['/'],
        'nonSecureUrls' => [],
    ],

    // JWT settings
    "jwt" => [
        // Requests after AWS load balancers are HTTP only
        'allowInsecureHttp' => true,
        'secret' => getenv('OHM_API_JWT_SECRET') ?
            getenv('OHM_API_JWT_SECRET') : 'development_jwt_secret',
        'issuer' => 'ohm-api',
        'signingAlgorithm' => 'HS512',
        'allowedAlgorithms' => ["HS512"],
        'securedPaths' => [API_BASE_PATH . "/v1/"],
        'ignoredPaths' => ["/v1/health"],
    ],

    // Renderer settings
    'renderer' => [
        'template_path' => __DIR__ . '/../../templates/',
    ],

    // Monolog settings
    'logger' => [
        'name' => 'ohm-api',
        'path' => $LOG_PATH . '/ohm-api.log',
        'level' => Level::Debug,
        'maxFiles' => 10,
        'bubbleErrors' => true,
        'filePermission' => 0600,
    ],
    'securityLogger' => [
        'name' => 'ohm-api',
        'path' => $LOG_PATH . '/ohm-api-security.log',
        'level' => Level::Debug,
        'maxFiles' => 10,
        'bubbleErrors' => true,
        'filePermission' => 0600,
    ],
    'errorLogger' => [
        'name' => 'ohm-api',
        'path' => $LOG_PATH . '/ohm-api-errors.log',
        'level' => Level::Debug,
        'maxFiles' => 10,
        'bubbleErrors' => true,
        'filePermission' => 0600,
    ],

    // Database settings
    'determineRouteBeforeAppMiddleware' => false,
    'db' => [
        'driver' => 'mysql',
        'host' => getenv('DB_SERVER'),
        'database' => getenv('DB_NAME'),
        'username' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD'),
        'charset' => 'latin1',
        'collation' => 'latin1_swedish_ci',
        'prefix' => '',
    ]
];
