<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('[/]', function (Request $request, Response $response, array $args) {
	return $response->withRedirect('/');
});

$app->group('/v1', function () {
	$this->group('/users', function () {
		$this->get('[/]', \OHM\Api\Controllers\UserController::class . ':findAll');
	});

	$this->group('/groups', function () {
		$this->get('[/]', \OHM\Api\Controllers\GroupController::class . ':findAll');
		$this->get('/{id}', \OHM\Api\Controllers\GroupController::class . ':find');
		$this->post('[/]', \OHM\Api\Controllers\GroupController::class . ':create');
		$this->delete('/{id}', \OHM\Api\Controllers\GroupController::class . ':delete');
		$this->put('[/{id}]', \OHM\Api\Controllers\GroupController::class . ':update');

		$this->group('/{groupId}/lti_credentials', function () {
			$this->get('[/]', \OHM\Api\Controllers\LtiCredentialController::class . ':findAll');
			$this->get('/{id}', \OHM\Api\Controllers\LtiCredentialController::class . ':find');
			$this->post('[/]', \OHM\Api\Controllers\LtiCredentialController::class . ':create');
			$this->delete('/{id}', \OHM\Api\Controllers\LtiCredentialController::class . ':delete');
			$this->put('[/{id}]', \OHM\Api\Controllers\LtiCredentialController::class . ':update');
		});
	});
});
