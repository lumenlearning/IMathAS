<?php
/*
 * Application middleware
 *
 * Note: Middleware order of execution is from bottom to top of this file.
 */

// OHM user session validation
$app->add(new \OHM\Api\Middleware\ValidateUser($app->getContainer()));

// JWT validation
// Note: This middleware prevents access to the OHM user session validator.
$jwtSettings = $app->getContainer()->get('settings')['jwt'];
$app->add(new \Slim\Middleware\JwtAuthentication([
	"secure" => !$jwtSettings['allowInsecureHttp'],
	"path" => $jwtSettings['securedPaths'],
//	"passthrough" => $jwtSettings['ignoredPaths'],
	"secret" => $jwtSettings['secret'],
	"algorithm" => $jwtSettings['allowedAlgorithms'],
	"attribute" => "jwt",  // $jwt = $request->getAttribute('jwt')
	"error" => function ($request, $response, $arguments) {
		$content["errors"] = array(
			"Error while parsing API token",
			$arguments["message"],
		);
		return $response->withStatus(401)->withJson($content);
	}
]));

