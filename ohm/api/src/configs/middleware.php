<?php

use Psr\Log\LoggerInterface;

/*
 * Application middleware
 *
 * Note: Middleware order of execution is from bottom to top of this file.
 */


/**
 * The routing middleware should be added earlier than the ErrorMiddleware
 * Otherwise exceptions thrown from it will not be handled by the middleware
 */

/* @var Slim\App $app */
$app->addRoutingMiddleware();

// JSON request body parser
$app->add(new \OHM\Api\Middleware\JsonBodyParserMiddleware());

// OHM user session validation
$app->add(new \OHM\Api\Middleware\ValidateUser($app->getContainer()));

// JWT validation
// Note: This middleware prevents access to the OHM user session validator.
$jwtSettings = $app->getContainer()->get('settings')['jwt'];
$app->add(new \Tuupola\Middleware\JwtAuthentication([
	"secure" => !$jwtSettings['allowInsecureHttp'],
	"path" => $jwtSettings['securedPaths'],
//	"passthrough" => $jwtSettings['ignoredPaths'],
	"secret" => $jwtSettings['secret'],
	"algorithm" => $jwtSettings['allowedAlgorithms'],
	"attribute" => "jwt",  // $jwt = $request->getAttribute('jwt')
    "logger" => $app->getContainer()->get('logger'),
	"error" => function ($response, $arguments) {
		$content["errors"] = array(
			"Error while parsing API token",
			$arguments["message"],
		);
        $response->getBody()->write(
            json_encode($content, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        return $response->withHeader("Content-Type", "application/json");
	}
]));

/**
 * Add Error Middleware
 *
 * @param bool                  $displayErrorDetails -> Should be set to false in production
 * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool                  $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger
 *
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$app->addErrorMiddleware(
    $settings['slim']['displayErrorDetails'],
    $settings['slim']['logErrors'],
    $settings['slim']['logErrorDetails']
);
